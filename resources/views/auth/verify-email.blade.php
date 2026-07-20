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
                <p class="auth-aside__text">Verify once — then live routes, ETAs, and your student plan unlock.</p>
            </div>
        </aside>

        <section class="auth-panel sm-reveal">
            <p class="auth-kicker">Email</p>
            <h1 class="auth-title">Verify your email</h1>
            <p class="auth-sub">
                We sent a verification link to
                <strong style="color: var(--ink, #12161c);">{{ Auth::user()->email }}</strong>.
                Open it to continue.
            </p>

            @php
                $mailHost = config('mail.mailers.smtp.host');
                $mailPort = config('mail.mailers.smtp.port');
                $isMailpit = ($mailHost === '127.0.0.1' || $mailHost === 'localhost') && $mailPort == 1025;
            @endphp

            @if($isMailpit)
                <div class="auth-alert auth-alert--ok">
                    Local testing: open Mailpit to read the message.
                    <a class="auth-link" href="http://127.0.0.1:8025" target="_blank" rel="noopener">Open Mailpit</a>
                    · Prefer Gmail?
                    <a class="auth-link" href="{{ route('email-setup') }}">Configure SMTP</a>
                </div>
            @else
                <div class="auth-alert auth-alert--ok">
                    Check your inbox (and spam). The link expires after a short window.
                </div>
            @endif

            @if (session('status') == 'verification-link-sent')
                <div class="auth-alert auth-alert--ok">A new verification link has been sent.</div>
            @endif

            <div class="auth-options" style="margin-top:1.25rem; justify-content:flex-start; gap:0.75rem; flex-wrap:wrap;">
                <form method="POST" action="{{ route('verification.send') }}" style="margin:0;">
                    @csrf
                    <button type="submit" class="auth-submit" style="width:auto; padding-inline:1.5rem;">Resend link</button>
                </form>
                <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                    @csrf
                    <button type="submit" class="auth-link" style="background:none; border:1px solid rgba(18,22,28,0.12); padding:0.75rem 1.25rem; border-radius:0.5rem; cursor:pointer;">
                        Log out
                    </button>
                </form>
            </div>
        </section>
    </div>
</x-guest-layout>
