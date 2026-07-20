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
                <p class="auth-aside__text">Verify ownership of your inbox before the dashboard unlocks.</p>
            </div>
        </aside>

        <section class="auth-panel sm-reveal">
            <p class="auth-kicker">Verification</p>
            <h1 class="auth-title">Enter your code</h1>
            <p class="auth-sub">
                We sent a 6-digit code to
                <strong>{{ Auth::user()->email }}</strong>.
                It expires in 15 minutes.
            </p>

            @if (session('success'))
                <div class="auth-alert auth-alert--ok">{!! session('success') !!}</div>
            @endif
            @if (session('status') == 'verification-code-sent')
                <div class="auth-alert auth-alert--ok">A new code was sent.</div>
            @endif
            @if (session('error'))
                <div class="auth-alert auth-alert--err">{!! session('error') !!}</div>
            @endif

            <form class="auth-form" method="POST" action="{{ route('verification.code.verify') }}">
                @csrf
                <div class="auth-field">
                    <label for="code">6-digit code</label>
                    <input
                        class="auth-code"
                        id="code"
                        type="text"
                        name="code"
                        maxlength="6"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        placeholder="••••••"
                        required
                        autofocus
                    >
                    @error('code')<div class="auth-error">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="auth-submit">Verify email</button>
            </form>

            <div class="auth-actions">
                <form method="POST" action="{{ route('verification.code.send') }}">
                    @csrf
                    <button type="submit" class="auth-ghost">Resend code</button>
                </form>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="auth-ghost">Log out</button>
                </form>
            </div>
        </section>
    </div>

    <script>
        const code = document.getElementById('code');
        code.addEventListener('input', function () {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
            if (this.value.length === 6) {
                setTimeout(() => this.form.submit(), 200);
            }
        });
    </script>
</x-guest-layout>
