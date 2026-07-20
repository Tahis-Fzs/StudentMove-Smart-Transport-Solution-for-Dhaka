<x-guest-layout>
    @push('styles')
        @vite(['resources/css/auth.css'])
    @endpush

    <div class="auth-shell">
        <aside class="auth-aside" aria-hidden="true">
            <img class="auth-aside__img" src="{{ asset('images/auth-city.jpg') }}" alt="" onerror="this.remove()">
            <div class="auth-aside__shade"></div>
            <div class="auth-aside__copy">
                <p class="auth-aside__brand">StudentMove</p>
                <p class="auth-aside__text">Reset access securely — only registered emails receive a link.</p>
            </div>
        </aside>

        <section class="auth-panel sm-reveal">
            <p class="auth-kicker">Password</p>
            <h1 class="auth-title">Forgot password</h1>
            <p class="auth-sub">Enter your account email. If it exists, we’ll send a reset link.</p>

            @if (session('status'))
                <div class="auth-alert auth-alert--ok">{!! session('status') !!}</div>
            @endif

            <form class="auth-form" method="POST" action="{{ route('password.email') }}" novalidate>
                @csrf
                <div class="auth-field">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
                    @error('email')<div class="auth-error">{!! $message !!}</div>@enderror
                </div>
                <button type="submit" class="auth-submit">Send reset link</button>
            </form>

            <div class="auth-foot">
                <a class="auth-link" href="{{ route('login') }}">Back to sign in</a>
            </div>
        </section>
    </div>
</x-guest-layout>
