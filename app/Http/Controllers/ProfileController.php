<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        // Separate validation for file uploads - only validate if file is present
        $validationRules = [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $request->user()->id],
            'phone' => ['required', 'string', 'max:20'],
            'university' => ['nullable', 'string', 'max:255'],
            'student_id' => ['nullable', 'string', 'max:50'],
            'date_of_birth' => ['nullable', 'date'],
            'department' => ['nullable', 'string', 'max:255'],
            'year_of_study' => ['nullable', 'string', 'max:10'],
            'current_address' => ['nullable', 'string', 'max:500'],
            'home_address' => ['nullable', 'string', 'max:500'],
            'preferred_language' => ['nullable', 'string', 'max:10'],
            'bus_delay_notifications' => ['nullable', 'boolean'],
            'route_change_alerts' => ['nullable', 'boolean'],
            'promotional_offers' => ['nullable', 'boolean'],
        ];

        // Only validate profile_image if a file was uploaded
        if ($request->hasFile('profile_image')) {
            // Allow largest files supported by server settings; no size cap here
            $validationRules['profile_image'] = ['required', 'image', 'mimes:jpeg,png,jpg,gif'];
        }

        $request->validate($validationRules, [
            'profile_image.image' => 'The profile image must be an image file.',
            'profile_image.mimes' => 'The profile image must be a file of type: jpeg, png, jpg, gif.',
            'profile_image.required' => 'Please select an image file to upload.',
        ]);

        $user = $request->user();
        
        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            \Log::info('Profile image upload started', [
                'user_id' => $user->id,
                'file_name' => $request->file('profile_image')->getClientOriginalName(),
                'file_size' => $request->file('profile_image')->getSize(),
            ]);
            
            try {
                $image = $request->file('profile_image');
                
                // Check if file upload was successful
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

                // Delete old profile image if it exists
                if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
                    Storage::disk('public')->delete($user->profile_image);
                }

                // Generate unique filename with original extension
                $extension = $image->getClientOriginalExtension();
                $imageName = 'profile_' . $user->id . '_' . time() . '.' . $extension;

                // Ensure directory exists on public disk
                Storage::disk('public')->makeDirectory('profile_images');

                // Store the new image
                $storedPath = $image->storeAs('profile_images', $imageName, 'public');
                
                if ($storedPath) {
                    $user->profile_image = 'profile_images/' . $imageName;
                    \Log::info('Profile image saved successfully', [
                        'user_id' => $user->id,
                        'image_path' => $user->profile_image,
                    ]);
                } else {
                    \Log::error('Profile image storage failed', ['user_id' => $user->id]);
                    return back()->withErrors(['profile_image' => 'Failed to save profile image. Please try again.'])->withInput();
                }
            } catch (\Exception $e) {
                \Log::error('Profile image upload error: ' . $e->getMessage(), [
                    'user_id' => $user->id,
                    'trace' => $e->getTraceAsString(),
                ]);
                return back()->withErrors(['profile_image' => 'An error occurred while uploading your image: ' . $e->getMessage()])->withInput();
            }
        } else {
            \Log::info('No profile image file in request', ['user_id' => $user->id]);
        }

        // Update user data
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->name = $request->first_name . ' ' . $request->last_name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->university = $request->university;
        $user->student_id = $request->student_id;
        $user->date_of_birth = $request->date_of_birth;
        $user->department = $request->department;
        $user->year_of_study = $request->year_of_study;
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

        // Refresh user to ensure latest data is loaded
        $user->refresh();

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
