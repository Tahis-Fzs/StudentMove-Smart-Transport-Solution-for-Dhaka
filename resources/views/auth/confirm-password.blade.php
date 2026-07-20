<x-guest-layout>
    @push('styles')
        @vite(['resources/css/auth.css'])
    @endpush

    <div class="auth-shell">
        <aside class="auth-aside" aria-hidden="true">
            <img class="auth-aside__img" src="{{ asset('images/auth-city.jpg') }}" alt="" onerror="this.remove()">
            <div class="auth-aside__shade"></div>
            @include('partials.auth-corridor')
            <div class="auth-aside__copy">
                <p class="auth-aside__brand">StudentMove</p>
                <p class="auth-aside__text">Confirm your password before changing sensitive settings.</p>
            </div>
        </aside>

        <section class="auth-panel sm-reveal">
            <p class="auth-kicker">Security</p>
            <h1 class="auth-title">Confirm password</h1>
            <p class="auth-sub">This is a secure area. Re-enter your password to continue.</p>

            <form class="auth-form" method="POST" action="{{ route('password.confirm') }}" novalidate>
                @csrf
                <div class="auth-field">
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" required autocomplete="current-password" autofocus>
                    @error('password')<div class="auth-error">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="auth-submit">Confirm</button>
            </form>
        </section>
    </div>
</x-guest-layout>
