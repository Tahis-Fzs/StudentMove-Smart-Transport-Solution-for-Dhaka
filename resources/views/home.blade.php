<x-landing-layout title="StudentMove — Smart Transport for Dhaka">
    <nav class="l-nav" aria-label="Primary">
        <div class="l-nav__inner">
            <a class="l-nav__brand" href="{{ route('home') }}">StudentMove</a>
            <div class="l-nav__links">
                <a class="l-nav__link" href="{{ route('next-bus-arrival') }}">Live map</a>
                @auth
                    <a class="l-nav__link" href="{{ route('dashboard') }}">Dashboard</a>
                    <form method="POST" action="{{ route('logout') }}" style="display:inline;margin:0;">
                        @csrf
                        <button type="submit" class="l-nav__link l-nav__link--solid" style="cursor:pointer;font:inherit;border:0;">Log out</button>
                    </form>
                @else
                    <a class="l-nav__link" href="{{ route('login') }}">Sign in</a>
                    <a class="l-nav__link l-nav__link--solid" href="{{ route('register') }}">Create account</a>
                @endauth
            </div>
        </div>
    </nav>

    <header class="l-hero">
        <div class="l-hero__media" aria-hidden="true">
            <img
                class="l-hero__img"
                src="https://images.unsplash.com/photo-1544620341-1adc1baa5c40?auto=format&fit=crop&w=2400&q=80"
                alt=""
                width="2400"
                height="1600"
                fetchpriority="high"
            >
            <div class="l-hero__shade"></div>
            <div class="l-hero__grain"></div>
        </div>

        <div class="l-hero__content">
            <p class="l-hero__brand">StudentMove</p>
            <h1 class="l-hero__headline">Move through Dhaka with intent.</h1>
            <p class="l-hero__lede">Live buses, ranked routes, and student plans — designed for the city that never sits still.</p>
            <div class="l-hero__cta">
                @auth
                    <a class="l-btn l-btn--primary" href="{{ route('dashboard') }}">Open dashboard</a>
                    <a class="l-btn l-btn--ghost" href="{{ route('next-bus-arrival') }}">Track live</a>
                @else
                    <a class="l-btn l-btn--primary" href="{{ route('register') }}">Start free</a>
                    <a class="l-btn l-btn--ghost" href="{{ route('next-bus-arrival') }}">Track live</a>
                @endauth
            </div>
        </div>
    </header>

    <section class="l-stage" aria-labelledby="stage-title">
        <div class="l-stage__grid">
            <div data-reveal>
                <p class="l-stage__eyebrow">Real-time network</p>
                <h2 id="stage-title" class="l-stage__title">A living map of every ride.</h2>
                <p class="l-stage__copy">GPS updates, ETAs, and route intelligence that feel like a product — not a campus prototype.</p>
                <a class="l-btn l-btn--dark" href="{{ route('route-suggestion') }}">Explore routes</a>
            </div>

            <div class="l-scene" data-reveal aria-hidden="true">
                <div class="l-scene__world">
                    <div class="l-scene__plane">
                        <div class="l-scene__lane l-scene__lane--a"></div>
                        <div class="l-scene__lane l-scene__lane--b"></div>
                        <div class="l-scene__road"></div>
                        <div class="l-scene__dash"></div>
                        <span class="l-scene__node l-scene__node--1"></span>
                        <span class="l-scene__node l-scene__node--2"></span>
                        <span class="l-scene__node l-scene__node--3"></span>
                        <span class="l-scene__bus"></span>
                        <span class="l-scene__ring"></span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="l-strip" aria-labelledby="strip-title">
        <div class="l-strip__inner">
            <div data-reveal>
                <h2 id="strip-title" class="l-strip__title">Built for students. Tuned for Dhaka.</h2>
                <p class="l-strip__copy">Subscriptions, delay alerts, and driver-fed locations — one system from campus gate to home stop.</p>
            </div>
            <div class="l-strip__actions" data-reveal>
                @guest
                    <a class="l-btn l-btn--primary" href="{{ route('register') }}">Join StudentMove</a>
                    <a class="l-btn l-btn--ghost" href="{{ route('driver.login') }}">Driver access</a>
                @else
                    <a class="l-btn l-btn--primary" href="{{ route('subscription') }}">View plans</a>
                    <a class="l-btn l-btn--ghost" href="{{ route('next-bus-arrival') }}">Open live map</a>
                @endguest
            </div>
        </div>
    </section>

    <footer class="l-foot">
        <div class="l-foot__inner">
            <span>StudentMove · Dhaka</span>
            <span>
                <a href="{{ route('admin.login') }}">Admin</a>
                ·
                <a href="{{ route('driver.login') }}">Driver</a>
            </span>
        </div>
    </footer>
</x-landing-layout>
