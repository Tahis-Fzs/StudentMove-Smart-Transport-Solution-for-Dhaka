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
                <p class="auth-aside__text">Choose a strong password — mixed case and a number required.</p>
            </div>
        </aside>

        <section class="auth-panel sm-reveal">
            <p class="auth-kicker">Password</p>
            <h1 class="auth-title">Set a new password</h1>
            <p class="auth-sub">This link works once. After reset, sign in with your new credentials.</p>

            <form class="auth-form" method="POST" action="{{ route('password.store') }}" novalidate>
                @csrf
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div class="auth-field">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus>
                    @error('email')<div class="auth-error">{{ $message }}</div>@enderror
                </div>

                <div class="auth-field">
                    <label for="password">New password</label>
                    <input id="password" type="password" name="password" required>
                    @error('password')<div class="auth-error">{{ $message }}</div>@enderror
                </div>

                <div class="auth-field">
                    <label for="password_confirmation">Confirm password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required>
                </div>

                <button type="submit" class="auth-submit">Update password</button>
            </form>
        </section>
    </div>
</x-guest-layout>
