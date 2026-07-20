<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Notification;
use App\Models\Offer;
use App\Models\University;
use App\Services\InboxService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(protected InboxService $inbox)
    {
    }
    public function settings()
    {
        $user = Auth::user();

        return view('notification_settings', compact('user'));
    }

    public function index(): View
    {
        $notifications = Notification::with('offer')->latest()->paginate(15);

        return view('admin.notifications.index', compact('notifications'));
    }

    public function create(): View
    {
        return view('admin.notifications.create', $this->formExtras());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $announcement = Notification::create($data);

        if ($announcement->is_active) {
            try {
                $this->inbox->fanOutAnnouncement($announcement);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return redirect()->route('admin.notifications.index')->with('success', 'Announcement created successfully!');
    }

    public function edit(Notification $notification): View
    {
        return view('admin.notifications.edit', array_merge(
            ['notification' => $notification],
            $this->formExtras()
        ));
    }

    public function update(Request $request, Notification $notification): RedirectResponse
    {
        $notification->update($this->validated($request));

        return redirect()->route('admin.notifications.index')->with('success', 'Announcement updated successfully!');
    }

    public function destroy(Notification $notification): RedirectResponse
    {
        $notification->delete();

        return redirect()->route('admin.notifications.index')->with('success', 'Announcement deleted successfully!');
    }

    protected function formExtras(): array
    {
        return [
            'offers' => Offer::orderBy('title')->get(),
            'universities' => University::orderBy('name')->get(['id', 'name', 'short_name']),
            'departments' => Department::orderBy('name')->pluck('name'),
        ];
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:120'],
            'message' => ['required', 'string', 'max:500'],
            'icon' => ['nullable', 'string', 'max:50'],
            'icon_color' => ['nullable', 'string', 'max:50'],
            'type' => ['nullable', 'string', 'in:info,success,warning,error'],
            'audience' => ['required', 'in:all,university,department,route'],
            'target_value' => ['nullable', 'string', 'max:255', 'required_unless:audience,all'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer'],
            'published_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'offer_id' => ['nullable', 'exists:offers,id'],
        ], [
            'target_value.required_unless' => 'Enter a target value for this audience (university, department, or route).',
        ]);

        $audience = $data['audience'];
        $target = trim((string) ($data['target_value'] ?? ''));

        if ($audience !== Notification::AUDIENCE_ALL && $target === '') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'target_value' => 'Enter a target value for this audience (university, department, or route).',
            ]);
        }

        if (!empty($data['published_at']) && !empty($data['expires_at'])
            && strtotime($data['expires_at']) <= strtotime($data['published_at'])) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'expires_at' => 'Expiry must be after the publish time.',
            ]);
        }

        // Non-ALL audiences always keep a non-empty target; ALL clears it.
        $targetValue = $audience === Notification::AUDIENCE_ALL ? null : $target;
        if ($audience !== Notification::AUDIENCE_ALL && ($targetValue === null || $targetValue === '')) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'target_value' => 'Enter a target value for this audience (university, department, or route).',
            ]);
        }

        return [
            'title' => $data['title'] ?? null,
            'message' => $data['message'],
            'icon' => $data['icon'] ?? 'bi-bell',
            'icon_color' => $data['icon_color'] ?? 'blue',
            'type' => $data['type'] ?? 'info',
            'audience' => $audience,
            'target_value' => $targetValue,
            'is_active' => $request->has('is_active'),
            'sort_order' => $data['sort_order'] ?? 0,
            'published_at' => $data['published_at'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
            'offer_id' => $data['offer_id'] ?? null,
        ];
    }
}
