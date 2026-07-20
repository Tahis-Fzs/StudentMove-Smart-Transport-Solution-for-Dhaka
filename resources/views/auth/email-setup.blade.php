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
                <p class="auth-aside__text">Point outbound mail at Gmail so verification lands in a real inbox.</p>
            </div>
        </aside>

        <section class="auth-panel auth-panel--wide sm-reveal">
            <p class="auth-kicker">Local setup</p>
            <h1 class="auth-title">Configure Gmail SMTP</h1>
            <p class="auth-sub">Enable 2-Step Verification, create an App Password named StudentMove, then paste it below.</p>

            <div class="auth-alert auth-alert--ok" style="margin-bottom:1.25rem;">
                <ol style="margin:0; padding-left:1.1rem; line-height:1.55;">
                    <li><a class="auth-link" href="https://myaccount.google.com/security" target="_blank" rel="noopener">Google Account Security</a> → enable 2-Step Verification</li>
                    <li><a class="auth-link" href="https://myaccount.google.com/apppasswords" target="_blank" rel="noopener">App Passwords</a> → Mail → custom name “StudentMove”</li>
                    <li>Copy the 16-character password into the form</li>
                </ol>
                <p style="margin:0.85rem 0 0; font-size:0.9rem;">
                    Or run:
                    <code style="display:block; margin-top:0.4rem; padding:0.65rem 0.75rem; background:rgba(18,22,28,0.05); border-radius:0.4rem; font-size:0.8rem; word-break:break-all;">
                        php artisan email:configure-gmail your-email@gmail.com your-app-password
                    </code>
                </p>
            </div>

            @if(session('success'))
                <div class="auth-alert auth-alert--ok">{!! session('success') !!}</div>
            @endif

            <form class="auth-form" method="POST" action="{{ route('email-setup.store') }}" novalidate>
                @csrf
                <div class="auth-field">
                    <label for="gmail_email">Gmail address</label>
                    <input id="gmail_email" type="email" name="gmail_email" value="{{ old('gmail_email') }}" placeholder="yourname@gmail.com" required autofocus>
                    @error('gmail_email')<div class="auth-error">{{ $message }}</div>@enderror
                </div>
                <div class="auth-field">
                    <label for="gmail_app_password">App password</label>
                    <input id="gmail_app_password" type="text" name="gmail_app_password" value="{{ old('gmail_app_password') }}" placeholder="16 characters" required>
                    <p class="auth-hint">From Google App Passwords — not your normal Gmail password.</p>
                    @error('gmail_app_password')<div class="auth-error">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="auth-submit">Save Gmail SMTP</button>
            </form>

            <div class="auth-foot">
                Prefer Mailpit? <a class="auth-link" href="{{ route('register') }}">Continue registration</a>
            </div>
        </section>
    </div>
</x-guest-layout>
