<x-app-layout>
    @push('styles')
    <link rel="stylesheet" href="{{ asset('css/notification.css') }}">
    @endpush

    <div class="dashboard-container">
        <section class="notification-section">
            <h2 class="section-title"><i class="bi bi-bell"></i> Notifications</h2>
            <div class="notification-list">
                @forelse($notifications as $notification)
                @php
                    $colorClass = $notification->icon_color ?? 'blue';
                @endphp
                <div class="notification-card {{ $colorClass }}" style="border-left-color: {{ $colorClass === 'blue' ? '#3b82f6' : ($colorClass === 'green' ? '#10b981' : ($colorClass === 'red' ? '#ef4444' : '#f59e0b')) }};">
                    <div class="notification-icon {{ $colorClass }}"><i class="bi {{ $notification->icon ?? 'bi-bell' }}"></i></div>
                    <div style="flex: 1; min-width: 0;">
                        <div class="notification-message">{{ $notification->message }}</div>
                        
                        @if($notification->offer)
                        <div style="margin-top: 12px; padding: 12px; background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(37, 99, 235, 0.05) 100%); border-radius: 8px; border-left: 3px solid #3b82f6;">
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                                <i class="bi bi-gift-fill" style="color: #3b82f6; font-size: 1.1rem;"></i>
                                <strong style="color: #1f2937; font-size: 1rem;">{{ $notification->offer->title }}</strong>
                                @if($notification->offer->discount_percentage > 0)
                                <span style="background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 4px 10px; border-radius: 12px; font-size: 0.85rem; font-weight: 600; margin-left: auto;">
                                    {{ $notification->offer->discount_percentage }}% OFF
                                </span>
                                @endif
                            </div>
                            @if($notification->offer->description)
                            <p style="color: #4b5563; font-size: 0.9rem; margin: 6px 0 0 0; line-height: 1.5;">{{ $notification->offer->description }}</p>
                            @endif
                            @if($notification->offer->valid_until)
                            <div style="margin-top: 8px; display: flex; align-items: center; gap: 12px; font-size: 0.85rem; color: #6b7280;">
                                <span><i class="bi bi-calendar-check" style="margin-right: 4px;"></i>Valid until: {{ $notification->offer->valid_until->format('M d, Y') }}</span>
                            </div>
                            @endif
                        </div>
                        @endif
                        
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

