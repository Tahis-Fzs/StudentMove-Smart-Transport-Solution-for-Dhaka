<x-app-layout>
    @push('styles')
    <link rel="stylesheet" href="{{ asset('css/notification.css') }}">
    @endpush

    <div class="dashboard-container">
        <section class="notification-section">
            <h2 class="section-title"><i class="bi bi-sliders"></i> Notification preferences</h2>
            <p style="color: #6b7280; margin-bottom: 1.5rem;">Choose which alerts you want to receive.</p>

            @if (session('success'))
                <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 12px 16px; border-radius: 8px; margin-bottom: 1rem;">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('notifications.update') }}" class="notification-list" style="max-width: 520px;">
                @csrf
                <label class="notification-card blue" style="display: flex; align-items: center; gap: 12px; cursor: pointer; padding: 16px;">
                    <input type="checkbox" name="bus_delay_notifications" value="1" @checked(old('bus_delay_notifications', $user->bus_delay_notifications ?? false))>
                    <span>Bus delay notifications</span>
                </label>
                <label class="notification-card green" style="display: flex; align-items: center; gap: 12px; cursor: pointer; padding: 16px; margin-top: 12px;">
                    <input type="checkbox" name="route_change_alerts" value="1" @checked(old('route_change_alerts', $user->route_change_alerts ?? false))>
                    <span>Route change alerts</span>
                </label>
                <label class="notification-card blue" style="display: flex; align-items: center; gap: 12px; cursor: pointer; padding: 16px; margin-top: 12px;">
                    <input type="checkbox" name="promotional_offers" value="1" @checked(old('promotional_offers', $user->promotional_offers ?? false))>
                    <span>Promotional offers</span>
                </label>
                <button type="submit" class="btn btn-primary" style="margin-top: 1.5rem; padding: 10px 20px; border-radius: 8px; border: none; background: #2563eb; color: white; font-weight: 600; cursor: pointer;">
                    Save preferences
                </button>
            </form>
        </section>
    </div>
</x-app-layout>
