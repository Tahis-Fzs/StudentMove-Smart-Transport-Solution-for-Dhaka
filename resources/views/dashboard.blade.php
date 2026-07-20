<x-app-layout>
    @push('styles')
    <link rel="stylesheet" href="/css/dashboard.css">
    @endpush

    <div class="dashboard-container">
        <header class="dash-masthead sm-reveal">
            <div class="dash-masthead__glow" aria-hidden="true"></div>
            <div class="dash-masthead__top">
                <div>
                    <p class="dash-masthead__eyebrow">StudentMove · Dhaka</p>
                    @php
                        $hour = (int) now()->format('G');
                        $greet = $hour < 12 ? 'morning' : ($hour < 17 ? 'afternoon' : 'evening');
                    @endphp
                    <h1 class="dash-masthead__title">
                        Good {{ $greet }},
                        <span>{{ Auth::user()->first_name ?? 'Student' }}</span>
                    </h1>
                    <p class="dash-masthead__lede">Your commute network — live buses, ranked routes, student passes.</p>
                </div>
                <a href="{{ route('profile.edit') }}" class="dash-masthead__avatar" title="Profile">
                    <img src="{{ Auth::user()->avatarUrl() }}" alt=""
                         onerror="this.onerror=null;this.src='{{ Auth::user()->avatarFallbackUrl() }}';" />
                </a>
            </div>

            <div class="dash-masthead__corridor" aria-hidden="true">
                @include('partials.auth-corridor')
            </div>

            <div class="dash-masthead__rail">
                <a href="{{ route('next-bus-arrival') }}" class="dash-rail__btn dash-rail__btn--signal">Live map</a>
                <a href="{{ route('route-suggestion') }}" class="dash-rail__btn">Routes</a>
                <a href="{{ route('bookings.index') }}" class="dash-rail__btn">Book</a>
                <a href="{{ route('subscription') }}" class="dash-rail__btn">Plans</a>
                <a href="{{ route('notifications') }}" class="dash-rail__btn">Alerts</a>
            </div>
        </header>

        @php
            $visibleAlerts = \App\Models\Notification::visibleFor(auth()->user());
            $activeNotifications = $visibleAlerts->take(3);
            $moreAlerts = $visibleAlerts->count() > 3;
        @endphp
        @if($activeNotifications->count() > 0)
        <section class="dash-notify" data-reveal>
            <div class="dash-section-head">
                <p class="sm-eyebrow">Network</p>
                <h2 class="dash-section-head__title">Latest alerts</h2>
            </div>
            <div>
                @foreach($activeNotifications as $notification)
                <div class="dash-notify__item">
                    <div class="dash-notify__icon">
                        <i class="bi {{ $notification->icon ?? 'bi-bell' }}"></i>
                    </div>
                    <div style="flex:1;min-width:0;">
                        @if($notification->title)
                        <div style="font-weight:700;color:#0b4f4c;font-size:0.82rem;margin-bottom:0.15rem;">{{ $notification->title }}</div>
                        @endif
                        <div style="font-weight:600;color:#1e2630;margin-bottom:0.25rem;">{{ $notification->message }}</div>
                        @if(($notification->audience ?? 'all') !== 'all')
                        <div style="font-size:0.75rem;color:#0b6e6a;margin-bottom:0.25rem;">{{ $notification->audienceLabel() }}</div>
                        @endif
                        @if($notification->offer)
                        <div style="margin-top:0.5rem;padding:0.65rem 0.75rem;background:rgba(11,110,106,0.08);border-radius:0.5rem;border-left:3px solid #0b6e6a;">
                            <strong style="color:#0b4f4c;font-size:0.88rem;">{{ $notification->offer->title }}</strong>
                            @if($notification->offer->discount_percentage > 0)
                            <span style="background:#0b6e6a;color:#fff;padding:0.15rem 0.45rem;border-radius:0.35rem;font-size:0.72rem;font-weight:700;margin-left:0.35rem;">
                                {{ $notification->offer->discount_percentage }}% OFF
                            </span>
                            @endif
                            @if($notification->offer->description)
                            <p style="color:#5b6572;font-size:0.82rem;margin:0.3rem 0 0;">{{ Str::limit($notification->offer->description, 80) }}</p>
                            @endif
                        </div>
                        @endif
                        <small style="color:#7a8490;font-size:0.8rem;margin-top:0.35rem;display:block;">{{ $notification->created_at->diffForHumans() }}</small>
                    </div>
                </div>
                @endforeach
                @if($moreAlerts)
                <a href="{{ route('notifications') }}" class="dash-notify__link">
                    View all alerts <i class="bi bi-arrow-right"></i>
                </a>
                @endif
            </div>
        </section>
        @endif

        <section class="promo-carousel-section" data-reveal>
            <div class="promo-carousel" id="promoCarousel">
                <div class="promo-track">
                    <div class="promo-slide gradient-bluegreen">
                        <div class="promo-copy">
                            <div class="promo-kicker">Pass</div>
                            <div class="promo-title">Student weekly & monthly</div>
                            <div class="promo-sub">Flat rates across city routes — built for the commute you actually take.</div>
                            <a href="{{ route('subscription') }}" class="promo-btn">Get a pass <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                    <div class="promo-slide gradient-teal">
                        <div class="promo-copy">
                            <div class="promo-kicker">Live</div>
                            <div class="promo-title">Track the next bus</div>
                            <div class="promo-sub">Driver-fed GPS and ETAs that update while you wait.</div>
                            <a href="{{ route('next-bus-arrival') }}" class="promo-btn">Open live map <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                    <div class="promo-slide gradient-graphite">
                        <div class="promo-copy">
                            <div class="promo-kicker">Routes</div>
                            <div class="promo-title">Ranked for your day</div>
                            <div class="promo-sub">From campus gate to home stop — ranked suggestions, not guesswork.</div>
                            <a href="{{ route('route-suggestion') }}" class="promo-btn">Plan a route <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
                <div class="promo-dots" id="promoDots"></div>
            </div>
        </section>

        <section class="dash-ai sm-panel" data-reveal>
            <div class="dash-section-head" style="margin-bottom:0.85rem;">
                <p class="sm-eyebrow">Assistant</p>
                <h2 class="dash-section-head__title">Ask AI</h2>
                <p class="dash-section-head__lede">Routes, schedules, delays — ask in plain language.</p>
            </div>
            <form id="ai-form" class="dash-ai__form" onsubmit="return sendAi(event);">
                <input type="text" id="ai-prompt" name="prompt" class="form-input" placeholder="e.g. Best way from Uttara to DSC after 5pm?" required autocomplete="off">
                <button type="submit" id="ai-send-btn" class="sm-btn sm-btn--route">Send</button>
            </form>
            <div id="ai-status" class="dash-ai__status" style="display:none;">Thinking…</div>
            <div id="ai-output" class="dash-ai__output" style="display:none;"></div>
        </section>

        <section class="dashboard-section" data-reveal>
            <div class="dash-section-head">
                <p class="sm-eyebrow">Shortcuts</p>
                <h2 class="dash-section-head__title">Move now</h2>
            </div>
            <div class="dashboard-cards">
                <a href="{{ route('next-bus-arrival') }}" class="dashboard-card blue" style="text-decoration:none;">
                    <div class="card-title">Next bus</div>
                    <div class="card-desc">Live arrivals & ETAs</div>
                    <span class="arrow">&rarr;</span>
                </a>
                <a href="{{ route('route-suggestion') }}" class="dashboard-card green" style="text-decoration:none;">
                    <div class="card-title">Route planner</div>
                    <div class="card-desc">Ranked suggestions</div>
                    <span class="arrow">&rarr;</span>
                </a>
                <a href="{{ route('bookings.index') }}" class="dashboard-card" style="text-decoration:none;">
                    <div class="card-title">Book a ride</div>
                    <div class="card-desc">Reserve seats on a trip</div>
                    <span class="arrow">&rarr;</span>
                </a>
                <a href="{{ route('subscription') }}" class="dashboard-card dash-card--signal" style="text-decoration:none;">
                    <div class="card-title">Student passes</div>
                    <div class="card-desc">Weekly · Monthly · Single</div>
                    <span class="arrow">&rarr;</span>
                </a>
            </div>
        </section>

        <section class="recent-activity-section" data-reveal>
            <div class="dash-section-head">
                <p class="sm-eyebrow">Activity</p>
                <h2 class="dash-section-head__title">Recent</h2>
            </div>
            @php
                $latestFeedback = Auth::user()->feedbacks()->latest()->first();
                $rating = (int) ($latestFeedback->rating ?? 0);
            @endphp
            <div class="dashboard-cards">
                <button type="button" class="dashboard-card green" id="open-feedback-modal" style="border:none; width:100%; text-align:left; cursor:pointer; font:inherit;">
                    <div class="card-title">Feedback</div>
                    <div class="card-desc">{{ $latestFeedback ? 'View your latest review' : 'Share how your ride went' }}</div>
                </button>
                <a href="{{ route('offers') }}" class="dashboard-card" style="text-decoration:none;">
                    <div class="card-title">Offers</div>
                    <div class="card-desc">Current student promotions</div>
                    <span class="arrow">&rarr;</span>
                </a>
            </div>
        </section>
    </div>

    <div class="sm-modal" id="feedback-modal" hidden>
        <div class="sm-modal__backdrop" data-close-feedback></div>
        <div class="sm-modal__panel" role="dialog" aria-modal="true" aria-labelledby="feedback-modal-title">
            <div class="sm-modal__accent"></div>
            <div class="sm-modal__icon" aria-hidden="true">
                <i class="bi bi-chat-heart-fill"></i>
            </div>
            <h3 id="feedback-modal-title" class="sm-modal__title">
                {{ $latestFeedback ? 'Your latest feedback' : 'No feedback yet' }}
            </h3>
            <p class="sm-modal__subtitle">
                {{ $latestFeedback ? 'Thanks for helping improve StudentMove.' : 'Tell us about your commute — routes, timing, or service.' }}
            </p>

            @if($latestFeedback)
                <div class="sm-modal__rating" aria-label="Rating {{ $rating }} out of 5">
                    @for($i = 1; $i <= 5; $i++)
                        <span class="{{ $i <= $rating ? 'is-on' : '' }}">★</span>
                    @endfor
                </div>
                <div class="sm-modal__meta">
                    <span>{{ $latestFeedback->subject }}</span>
                    <span>{{ $latestFeedback->created_at->format('d M Y') }}</span>
                </div>
                <blockquote class="sm-modal__quote">{{ $latestFeedback->message }}</blockquote>
                @if($latestFeedback->admin_response)
                    <div class="sm-modal__reply">
                        <strong>Team reply</strong>
                        <p>{{ $latestFeedback->admin_response }}</p>
                    </div>
                @endif
            @endif

            <div class="sm-modal__actions">
                <a href="{{ route('feedback.index') }}" class="sm-modal__btn sm-modal__btn--primary">
                    {{ $latestFeedback ? 'View all feedback' : 'Write feedback' }}
                </a>
                <button type="button" class="sm-modal__btn sm-modal__btn--ghost" data-close-feedback>Close</button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        (function() {
            const root = document.getElementById('promoCarousel');
            if(!root) return;
            const track = root.querySelector('.promo-track');
            const slides = Array.from(root.querySelectorAll('.promo-slide'));
            const dotsRoot = document.getElementById('promoDots');
            let index = 0; let timer;
            function renderDots(){
                dotsRoot.innerHTML = slides.map((_,i)=>`<button class="dot${i===index?' active':''}" data-i="${i}" aria-label="Slide ${i+1}"></button>`).join('');
                dotsRoot.querySelectorAll('.dot').forEach(btn=>btn.addEventListener('click',()=>{ index=+btn.dataset.i; move(); reset(); }));
            }
            function move(){ track.style.transform = `translateX(-${index*100}%)`; renderDots(); }
            function next(){ index = (index+1)%slides.length; move(); }
            function reset(){ clearInterval(timer); timer = setInterval(next, 5200); }
            move(); reset();
        })();

        async function sendAi(e){
            e.preventDefault();
            const prompt = document.getElementById('ai-prompt').value.trim();
            const out = document.getElementById('ai-output');
            const status = document.getElementById('ai-status');
            const btn = document.getElementById('ai-send-btn');
            if(!prompt) return false;
            status.style.display = 'block';
            out.style.display = 'none';
            btn.disabled = true;
            try {
                const res = await fetch('{{ route('ai.generate') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ prompt }),
                });
                const data = await res.json();
                status.style.display = 'none';
                out.style.display = 'block';
                out.textContent = data.output || data.error || 'No response';
            } catch (err) {
                status.style.display = 'none';
                out.style.display = 'block';
                out.textContent = 'Could not reach AI right now.';
            }
            btn.disabled = false;
            return false;
        }

        (function(){
            const modal = document.getElementById('feedback-modal');
            const openBtn = document.getElementById('open-feedback-modal');
            if(!modal || !openBtn) return;
            const open = () => { modal.hidden = false; document.body.style.overflow = 'hidden'; };
            const close = () => { modal.hidden = true; document.body.style.overflow = ''; };
            openBtn.addEventListener('click', open);
            modal.querySelectorAll('[data-close-feedback]').forEach(el => el.addEventListener('click', close));
            document.addEventListener('keydown', (ev) => { if(ev.key === 'Escape' && !modal.hidden) close(); });
        })();
    </script>
    @endpush
</x-app-layout>
