<x-app-layout>
    @push('styles')
    <link rel="stylesheet" href="{{ asset('css/notification.css') }}">
    @endpush

    <div class="dashboard-container">
        <section class="notification-section">
            <div class="inbox-header">
                <h2 class="section-title"><i class="bi bi-inbox"></i> Notification center</h2>
                <div class="inbox-header__actions">
                    @if ($unreadCount > 0)
                        <form method="POST" action="{{ route('notifications.inbox.readAll') }}">
                            @csrf
                            <button type="submit" class="inbox-btn">Mark all read</button>
                        </form>
                    @endif
                    <a href="{{ route('notifications.settings') }}" class="inbox-btn inbox-btn--ghost">Preferences</a>
                </div>
            </div>

            <div class="inbox-tabs">
                <a href="{{ route('notifications', ['tab' => 'inbox']) }}"
                   class="inbox-tab {{ $tab === 'inbox' ? 'is-active' : '' }}">
                    Inbox
                    @if ($unreadCount > 0)
                        <span class="inbox-count">{{ $unreadCount }}</span>
                    @endif
                </a>
                <a href="{{ route('notifications', ['tab' => 'announcements']) }}"
                   class="inbox-tab {{ $tab === 'announcements' ? 'is-active' : '' }}">
                    Network alerts
                </a>
            </div>

            @if ($tab === 'inbox')
            <div class="notification-list">
                @forelse ($inbox as $message)
                <div class="notification-card {{ $message->isUnread() ? 'is-unread' : '' }}">
                    <div class="notification-icon"><i class="bi {{ $message->iconClass() }}"></i></div>
                    <div style="flex:1;min-width:0;">
                        <div class="inbox-row-top">
                            <div>
                                <span class="inbox-type">{{ ucfirst($message->type) }}</span>
                                <div class="notification-message" style="margin-top:2px;">{{ $message->title }}</div>
                            </div>
                            @if ($message->isUnread())
                                <span class="inbox-dot" title="Unread"></span>
                            @endif
                        </div>
                        <p class="inbox-body">{{ $message->body }}</p>
                        <small class="notification-time">{{ $message->created_at->diffForHumans() }}</small>
                        <div class="inbox-actions">
                            @if ($message->isUnread())
                                <form method="POST" action="{{ route('notifications.inbox.read', $message) }}">
                                    @csrf
                                    <button type="submit" class="inbox-link">Mark read</button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('notifications.inbox.destroy', $message) }}"
                                  onsubmit="return confirm('Remove this message?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inbox-link inbox-link--danger">Remove</button>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <div class="no-notifications">
                    <p>Your inbox is empty. Booking and delay updates will appear here.</p>
                </div>
                @endforelse
            </div>
            @else
            <div class="notification-list">
                @forelse ($announcements as $notification)
                @php $colorClass = $notification->icon_color ?? 'blue'; @endphp
                <div class="notification-card" style="border-left-color:#0b6e6a;">
                    <div class="notification-icon"><i class="bi {{ $notification->icon ?? 'bi-bell' }}"></i></div>
                    <div style="flex: 1; min-width: 0;">
                        @if($notification->title)
                        <div style="font-weight:700;color:#0b4f4c;margin-bottom:4px;">{{ $notification->title }}</div>
                        @endif
                        <div class="notification-message">{{ $notification->message }}</div>
                        @if(($notification->audience ?? 'all') !== 'all')
                        <div style="margin-top:6px;font-size:0.8rem;color:#0b6e6a;">
                            <i class="bi bi-funnel"></i> {{ $notification->audienceLabel() }}
                        </div>
                        @endif
                        @if($notification->offer)
                        <div style="margin-top: 12px; padding: 12px; background: rgba(11,110,106,0.08); border-radius: 8px; border-left: 3px solid #0b6e6a;">
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px; flex-wrap: wrap;">
                                <i class="bi bi-gift-fill" style="color: #0b6e6a; font-size: 1.1rem;"></i>
                                <strong style="color: #12161c; font-size: 1rem;">{{ $notification->offer->title }}</strong>
                                @if($notification->offer->discount_percentage > 0)
                                <span style="background: #0b6e6a; color: white; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 700; margin-left: auto;">
                                    {{ $notification->offer->discount_percentage }}% OFF
                                </span>
                                @endif
                            </div>
                            @if($notification->offer->description)
                            <p style="color: #5b6572; font-size: 0.9rem; margin: 6px 0 0 0; line-height: 1.5;">{{ $notification->offer->description }}</p>
                            @endif
                        </div>
                        @endif
                        <small class="notification-time">{{ $notification->created_at->diffForHumans() }}</small>
                    </div>
                </div>
                @empty
                <div class="no-notifications">
                    <p>No network alerts right now.</p>
                </div>
                @endforelse
            </div>
            @endif
        </section>
    </div>
</x-app-layout>
