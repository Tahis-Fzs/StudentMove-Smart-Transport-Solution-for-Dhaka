<x-guest-layout>
    @push('styles')
        @vite(['resources/css/auth.css'])
    @endpush

    <div class="auth-shell">
        <aside class="auth-aside" aria-hidden="true">
            <img class="auth-aside__img" src="{{ asset('images/hero-bus.jpg') }}" alt="" onerror="this.remove()">
            <div class="auth-aside__shade"></div>
            @include('partials.auth-corridor')
            <div class="auth-aside__copy">
                <p class="auth-aside__brand">StudentMove</p>
                <p class="auth-aside__text">One account for live buses, bookings, and your student pass.</p>
            </div>
        </aside>

        <section class="auth-panel sm-reveal">
            <p class="auth-kicker">Get started</p>
            <h1 class="auth-title">Sign in or create account</h1>
            <p class="auth-sub">
                <strong>New here?</strong> Continue with Google — we create your account, then ask for your student ID on the next screen.
                <strong>Returning?</strong> Use Google again or sign in with email below.
            </p>

            @if (session('status'))
                <div class="auth-alert auth-alert--ok">{{ session('status') }}</div>
            @endif

            @include('partials.firebase-auth', ['intent' => 'login', 'socialFirst' => true])

            <div class="auth-social__divider" style="margin:1.25rem 0;"><span>or sign in with email</span></div>

            <form class="auth-form" method="POST" action="{{ route('login') }}" novalidate>
                @csrf

                <div class="auth-field">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="username" required>
                    @error('email')<div class="auth-error">{{ $message }}</div>@enderror
                </div>

                <div class="auth-field">
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" autocomplete="current-password" required>
                    @error('password')<div class="auth-error">{{ $message }}</div>@enderror
                </div>

                <div class="auth-options">
                    <label class="auth-check" style="margin:0;">
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        <span>Remember me</span>
                    </label>
                    @if (Route::has('password.request'))
                        <a class="auth-link" href="{{ route('password.request') }}">Forgot password?</a>
                    @endif
                </div>

                <button type="submit" class="auth-submit">Sign in with email</button>
            </form>

            <div class="auth-foot">
                Want email verification and password upfront?
                <a class="auth-link" href="{{ route('register') }}">Register with student ID</a>
            </div>
        </section>
    </div>
</x-guest-layout>
