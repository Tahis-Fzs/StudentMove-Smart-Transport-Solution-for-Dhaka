<x-app-layout>
    @push('styles')
    <link rel="stylesheet" href="{{ asset('css/notification.css') }}">
    @endpush

    <div class="dashboard-container">
        <section class="notification-section">
            <h2 class="section-title"><i class="bi bi-bell"></i> Notifications</h2>
            <div class="notification-list">
                @forelse($notifications as $notification)
                <div class="notification-card">
                    <div class="notification-icon {{ $notification->icon_color }}"><i class="bi {{ $notification->icon }}"></i></div>
                    <div>
                        <div class="notification-message">{{ $notification->message }}</div>
                        <small class="notification-time">{{ $notification->created_at->diffForHumans() }}</small>
                    </div>
                </div>
                @empty
                <div class="no-notifications">
                    <p>No notifications at the moment.</p>
                </div>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>

