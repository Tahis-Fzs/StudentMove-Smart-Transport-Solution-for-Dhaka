<x-guest-layout>
    @push('styles')
        @vite(['resources/css/auth.css'])
    @endpush

    <div class="auth-shell">
        <aside class="auth-aside" aria-hidden="true">
            <img class="auth-aside__img" src="{{ asset('images/hero-bus.jpg') }}" alt="" onerror="this.remove()">
            <div class="auth-aside__shade"></div>
            <div class="auth-aside__copy">
                <p class="auth-aside__brand">StudentMove</p>
                <p class="auth-aside__text">Sign in to live routes, ETAs, and your student plan.</p>
            </div>
        </aside>

        <section class="auth-panel sm-reveal">
            <p class="auth-kicker">Sign in</p>
            <h1 class="auth-title">Welcome back</h1>
            <p class="auth-sub">Use the email you registered with. Sessions are rate-limited against brute force.</p>

            @if (session('status'))
                <div class="auth-alert auth-alert--ok">{{ session('status') }}</div>
            @endif

            <form class="auth-form" method="POST" action="{{ route('login') }}" novalidate>
                @csrf

                <div class="auth-field">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
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

                <button type="submit" class="auth-submit">Sign in</button>
            </form>

            @include('partials.firebase-auth', ['intent' => 'login'])

            <div class="auth-foot">
                New here? <a class="auth-link" href="{{ route('register') }}">Create an account</a>
            </div>
        </section>
    </div>
</x-guest-layout>
