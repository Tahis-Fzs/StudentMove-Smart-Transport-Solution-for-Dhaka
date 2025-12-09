<x-app-layout>
    @push('styles')
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
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

