<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Faculty;
use App\Models\University;
use App\Services\AcademicCatalogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(protected AcademicCatalogService $academicCatalog)
    {
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
            'completingProfile' => $request->user()->needsProfileCompletion() || $request->boolean('complete'),
            'universities' => University::orderBy('name')->get(['id', 'name', 'short_name', 'calendar_type']),
            'faculties' => Faculty::orderBy('name')->pluck('name'),
            'departments' => Department::orderBy('name')->pluck('name'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $completingProfile = $user->needsProfileCompletion();

        $validationRules = [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $request->user()->id],
            'phone' => [
                'required',
                'string',
                'max:20',
                'unique:users,phone,' . $request->user()->id,
            ],
            'university_select' => ['nullable', 'string', 'max:255'],
            'university_other' => ['nullable', 'string', 'max:255'],
            'faculty_select' => ['nullable', 'string', 'max:255'],
            'faculty_other' => ['nullable', 'string', 'max:255'],
            'department_select' => ['nullable', 'string', 'max:255'],
            'department_other' => ['nullable', 'string', 'max:255'],
            'student_id' => [
                $completingProfile ? 'required' : 'nullable',
                'string',
                'max:50',
                'unique:users,student_id,' . $request->user()->id,
            ],
            'date_of_birth' => ['nullable', 'date'],
            'year_of_study' => ['nullable', 'string', 'max:20'],
            'semester' => ['nullable', 'string', 'max:20'],
            'semester_system' => ['nullable', 'in:bi,tri'],
            'current_address' => ['nullable', 'string', 'max:500'],
            'home_address' => ['nullable', 'string', 'max:500'],
            'preferred_language' => ['nullable', 'string', 'max:10'],
            'bus_delay_notifications' => ['nullable', 'boolean'],
            'route_change_alerts' => ['nullable', 'boolean'],
            'promotional_offers' => ['nullable', 'boolean'],
        ];

        if ($request->hasFile('profile_image')) {
            $validationRules['profile_image'] = ['required', 'image', 'mimes:jpeg,png,jpg,gif'];
        }

        $request->validate($validationRules, [
            'profile_image.image' => 'The profile image must be an image file.',
            'profile_image.mimes' => 'The profile image must be a file of type: jpeg, png, jpg, gif.',
            'profile_image.required' => 'Please select an image file to upload.',
        ]);

        if ($request->university_select === '__other__' && !trim((string) $request->university_other)) {
            return back()->withErrors(['university_other' => 'Please type your university name.'])->withInput();
        }
        if ($request->faculty_select === '__other__' && !trim((string) $request->faculty_other)) {
            return back()->withErrors(['faculty_other' => 'Please type your faculty name.'])->withInput();
        }
        if ($request->department_select === '__other__' && !trim((string) $request->department_other)) {
            return back()->withErrors(['department_other' => 'Please type your department name.'])->withInput();
        }

        $user = $request->user();

        if ($completingProfile && blank($this->academicCatalog->resolveUniversity(
            $request->university_select,
            $request->university_other
        )) && blank($user->university)) {
            return back()->withErrors(['university_select' => 'Please select your university.'])->withInput();
        }

        if ($request->hasFile('profile_image')) {
            try {
                $image = $request->file('profile_image');

                if (!$image->isValid()) {
                    $errorCode = $image->getError();
                    $errorMessages = [
                        UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the server upload limit.',
                        UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive.',
                        UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
                        UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
                        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
                        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                        UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',
                    ];

                    $errorMessage = $errorMessages[$errorCode] ?? 'File upload failed with error code: ' . $errorCode;
                    return back()->withErrors(['profile_image' => $errorMessage])->withInput();
                }

                if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
                    Storage::disk('public')->delete($user->profile_image);
                }

                $extension = $image->getClientOriginalExtension();
                $imageName = 'profile_' . $user->id . '_' . time() . '.' . $extension;
                Storage::disk('public')->makeDirectory('profile_images');
                $storedPath = $image->storeAs('profile_images', $imageName, 'public');

                if ($storedPath) {
                    $user->profile_image = 'profile_images/' . $imageName;
                } else {
                    return back()->withErrors(['profile_image' => 'Failed to save profile image. Please try again.'])->withInput();
                }
            } catch (\Throwable $e) {
                report($e);

                return back()->withErrors(['profile_image' => 'An error occurred while uploading your image. Please try again.'])->withInput();
            }
        }

        // resolve* returns null when both select/other are empty — preserve existing
        // academic fields so a partial profile save cannot wipe them.
        $university = $this->academicCatalog->resolveUniversity(
            $request->university_select,
            $request->university_other
        ) ?? $user->university;
        $faculty = $this->academicCatalog->resolveFaculty(
            $request->faculty_select,
            $request->faculty_other
        ) ?? $user->faculty;
        $department = $this->academicCatalog->resolveDepartment(
            $request->department_select,
            $request->department_other
        ) ?? $user->department;

        $calendar = $request->semester_system
            ?: $this->academicCatalog->calendarForUniversity($university);
        if ($calendar === 'both') {
            $calendar = $request->semester_system ?: 'bi';
        }

        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->name = $request->first_name . ' ' . $request->last_name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->university = $university;
        $user->faculty = $faculty;
        $user->department = $department;
        $user->student_id = $request->student_id;
        $user->date_of_birth = $request->date_of_birth;
        $user->year_of_study = $request->year_of_study;
        $user->semester = $request->semester;
        $user->semester_system = in_array($calendar, ['bi', 'tri'], true) ? $calendar : 'bi';
        $user->current_address = $request->current_address;
        $user->home_address = $request->home_address;
        $user->preferred_language = $request->preferred_language;
        $user->bus_delay_notifications = $request->has('bus_delay_notifications');
        $user->route_change_alerts = $request->has('route_change_alerts');
        $user->promotional_offers = $request->has('promotional_offers');

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();
        $user->refresh();

        if ($completingProfile && !$user->needsProfileCompletion()) {
            return Redirect::route('dashboard')
                ->with('status', 'profile-updated')
                ->with('success', 'Profile complete — welcome to StudentMove!');
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated')->with('user', $user);
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current-password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
