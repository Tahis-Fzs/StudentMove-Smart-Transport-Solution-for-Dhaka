<x-app-layout>
    @push('styles')
    <link rel="stylesheet" href="/css/dashboard.css">
    @endpush

    <div class="dashboard-container">
        <!-- Greeting/Profile Section -->
        <section class="main-greeting-section">
            <div class="profile-row">
                <div class="main-greeting">
                    <div class="greeting-text">
                        Welcome back, <span class="blue-text">{{ Auth::user()->first_name ?? 'Student' }}!</span>
                    </div>
                    <p class="dashboard-subtitle">What do you want to do today?</p>
                </div>
                <div class="profile-pic">
                    <a href="{{ route('profile.edit') }}" style="text-decoration: none; display: block;">
                        <img src="{{ Auth::user()->profile_image ? asset('storage/' . Auth::user()->profile_image) : 'https://randomuser.me/api/portraits/men/32.jpg' }}" alt="Profile" />
                    </a>
                </div>
            </div>
        </section>

        <!-- Active Notifications Section -->
        @php
            $activeNotifications = \App\Models\Notification::with('offer')->active()->orderBy('sort_order')->orderBy('created_at', 'desc')->take(3)->get();
        @endphp
        @if($activeNotifications->count() > 0)
        <section style="margin: 24px auto; max-width: 1200px; padding: 0 20px;">
            <h2 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 16px; color: #1f2937;">
                <i class="bi bi-bell-fill" style="color: #ef4444; margin-right: 8px;"></i> Latest Notifications
            </h2>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                @foreach($activeNotifications as $notification)
                <div style="background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%); border-left: 4px solid {{ $notification->icon_color === 'blue' ? '#3b82f6' : ($notification->icon_color === 'green' ? '#10b981' : ($notification->icon_color === 'red' ? '#ef4444' : '#f59e0b')) }}; padding: 16px 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 16px; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)'" onmouseout="this.style.transform=''; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.1)'">
                    <div style="width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, {{ $notification->icon_color === 'blue' ? '#3b82f6' : ($notification->icon_color === 'green' ? '#10b981' : ($notification->icon_color === 'red' ? '#ef4444' : '#f59e0b')) }}, {{ $notification->icon_color === 'blue' ? '#2563eb' : ($notification->icon_color === 'green' ? '#059669' : ($notification->icon_color === 'red' ? '#dc2626' : '#d97706')) }}); display: flex; align-items: center; justify-content: center; color: white; font-size: 20px; flex-shrink: 0;">
                        <i class="bi {{ $notification->icon ?? 'bi-bell' }}"></i>
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-size: 1rem; font-weight: 500; color: #1f2937; word-wrap: break-word; margin-bottom: 4px;">{{ $notification->message }}</div>
                        
                        @if($notification->offer)
                        <div style="margin-top: 8px; padding: 10px; background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(37, 99, 235, 0.05) 100%); border-radius: 8px; border-left: 3px solid #3b82f6;">
                            <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 4px; flex-wrap: wrap;">
                                <i class="bi bi-gift-fill" style="color: #3b82f6; font-size: 0.95rem;"></i>
                                <strong style="color: #1f2937; font-size: 0.9rem;">{{ $notification->offer->title }}</strong>
                                @if($notification->offer->discount_percentage > 0)
                                <span style="background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 3px 8px; border-radius: 10px; font-size: 0.75rem; font-weight: 600;">
                                    {{ $notification->offer->discount_percentage }}% OFF
                                </span>
                                @endif
                            </div>
                            @if($notification->offer->description)
                            <p style="color: #4b5563; font-size: 0.85rem; margin: 4px 0 0 0; line-height: 1.4;">{{ Str::limit($notification->offer->description, 80) }}</p>
                            @endif
                        </div>
                        @endif
                        
                        <small style="color: #6b7280; font-size: 0.875rem; margin-top: 6px; display: block;">{{ $notification->created_at->diffForHumans() }}</small>
                    </div>
                </div>
                @endforeach
                @if(\App\Models\Notification::active()->count() > 3)
                <a href="{{ route('notifications') }}" style="text-align: center; padding: 12px; color: #3b82f6; font-weight: 500; text-decoration: none; border-radius: 8px; transition: background 0.2s;" onmouseover="this.style.background='#eff6ff'" onmouseout="this.style.background='transparent'">
                    View All Notifications <i class="bi bi-arrow-right" style="margin-left: 4px;"></i>
                </a>
                @endif
            </div>
        </section>
        @endif
        
        <!-- Promo Banner Carousel (single large card with dots) -->
        <section class="promo-carousel-section">
            <div class="promo-carousel" id="promoCarousel">
                <div class="promo-track">
                    <div class="promo-slide gradient-bluegreen">
                        <div class="promo-copy">
                            <div class="promo-title">Save on Student Pass</div>
                            <div class="promo-sub">Flat daily rates for city routes</div>
                            <div class="promo-sub">Best for regular commuters</div>
                            <a href="{{ route('subscription') }}" class="promo-btn">Get Pass <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                    <div class="promo-slide gradient-teal">
                        <div class="promo-copy">
                            <div class="promo-title">Live Bus Tracking</div>
                            <div class="promo-sub">See arrivals in real-time</div>
                            <a href="{{ route('next-bus-arrival') }}" class="promo-btn">Track Now <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                    <div class="promo-slide gradient-purple">
                        <div class="promo-copy">
                            <div class="promo-title">Plan Your Route</div>
                            <div class="promo-sub">Personalized suggestions</div>
                            <a href="{{ route('route-suggestion') }}" class="promo-btn">Plan Route <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
                <div class="promo-dots" id="promoDots"></div>
            </div>
        </section>

        <!-- Moved subscription CTA under the ads section -->
        <div class="dashboard-cta-row">
            <a href="{{ route('subscription') }}" class="dashboard-cta-btn">
                <i class="bi bi-star-fill"></i> Subscribe Now
            </a>
        </div>

        <!-- Cards Grid -->
        <main>
            <div class="dashboard-section">
                <h2>Dashboard</h2>
                <div class="dashboard-cards">
                    <a href="{{ route('next-bus-arrival') }}" class="dashboard-card blue" style="text-decoration:none;">
                        <div class="card-title">Next Bus Arrival</div>
                        <div class="card-desc">upcoming bus arrivals</div>
                        <span class="arrow">&rarr;</span>
                    </a>
                    <a href="{{ route('route-suggestion') }}" class="dashboard-card green" style="text-decoration:none;">
                        <div class="card-title">Personalized Route Suggestion</div>
                        <div class="card-desc">Recommend routes</div>
                        <span class="arrow">&rarr;</span>
                    </a>
                    <div class="dashboard-card">
                        <div class="card-title">Past Routes Taken</div>
                        <div class="card-desc">
                            <a href="#" class="route-link" onclick="showRoute('Uttara to DSC')">Uttara to DSC</a><br>
                            <a href="#" class="route-link" onclick="showRoute('Uttara to DU')">Uttara to DU</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="recent-activity-section">
                <h2>Recent Activity</h2>
                <div class="dashboard-cards">
                    <div class="dashboard-card green" onclick="showFeedback()">
                        <div class="card-title">Feedback Submitted</div>
                        <div class="card-desc">Your feedback</div>
                    </div>
                    <a href="{{ route('offers') }}" class="dashboard-card red" style="text-decoration:none;">
                        <div class="card-title">Click to know about the offer</div>
                    </a>
                </div>
            </div>
        </main>
    </div>

    @push('scripts')
    <script>
        // Lightweight promo carousel
        (function() {
            const root = document.getElementById('promoCarousel');
            if(!root) return;
            const track = root.querySelector('.promo-track');
            const slides = Array.from(root.querySelectorAll('.promo-slide'));
            const dotsRoot = document.getElementById('promoDots');
            let index = 0; let timer;
            function renderDots(){
                dotsRoot.innerHTML = slides.map((_,i)=>`<button class="dot${i===index?' active':''}" data-i="${i}"></button>`).join('');
                dotsRoot.querySelectorAll('.dot').forEach(btn=>btn.addEventListener('click',()=>{ index=+btn.dataset.i; move(); reset(); }));
            }
            function move(){ track.style.transform = `translateX(-${index*100}%)`; renderDots(); }
            function next(){ index = (index+1)%slides.length; move(); }
            function reset(){ clearInterval(timer); timer = setInterval(next, 4000); }
            renderDots(); move(); reset();
        })();

        function showRoute(route) {
            alert(`Route Details: ${route}\n\nDistance: 12.5 km\nDuration: 35 minutes\nFare: ৳25\n\nBus Schedule:\n- Every 15 minutes\n- Last bus: 10:00 PM`);
        }

        function showFeedback() {
            alert('Feedback Submitted:\n\nRating: ⭐⭐⭐⭐⭐\nService: Excellent\nComments: "Very helpful app, accurate timing!"\n\nThank you for your feedback!');
        }
    </script>
    @endpush
</x-app-layout>

