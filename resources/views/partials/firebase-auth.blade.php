{{-- Shared Firebase social auth buttons (login + register) --}}
@php
    $firebaseReady = filled(config('services.firebase.api_key'))
        && filled(config('services.firebase.auth_domain'))
        && filled(config('services.firebase.project_id'))
        && filled(config('services.firebase.app_id'));
    $providers = config('services.firebase.providers', ['google']);
    $socialFirst = $socialFirst ?? false;
@endphp

<div class="auth-social" data-firebase-auth data-intent="{{ $intent ?? 'login' }}">
    @if (! $socialFirst)
        <div class="auth-social__divider"><span>or continue with</span></div>
    @endif

    @if (! $firebaseReady)
        <div class="auth-alert auth-alert--err" style="margin-bottom:0.75rem;">
            Firebase is not configured yet. Add <code>FIREBASE_*</code> keys to <code>.env</code> to enable Google and other social logins.
        </div>
    @endif

    <div class="auth-alert" data-firebase-status hidden style="margin-bottom:0.75rem;"></div>

    <div class="auth-social__grid">
        @if (in_array('google', $providers, true))
            <button type="button" class="auth-social__btn auth-social__btn--google" data-firebase-provider="google" @disabled(! $firebaseReady)>
                <svg width="18" height="18" viewBox="0 0 48 48" aria-hidden="true"><path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3C33.7 32.9 29.3 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.8 1.2 7.9 3.1l5.7-5.7C34.2 6.1 29.4 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.3-.1-2.5-.4-3.5z"/><path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.7 15.1 19 12 24 12c3.1 0 5.8 1.2 7.9 3.1l5.7-5.7C34.2 6.1 29.4 4 24 4 16.1 4 9.2 8.5 6.3 14.7z"/><path fill="#4CAF50" d="M24 44c5.2 0 9.9-2 13.4-5.2l-6.2-5.2C29.2 35.3 26.7 36 24 36c-5.3 0-9.7-3.1-11.3-7.5l-6.5 5C9.1 39.5 16 44 24 44z"/><path fill="#1976D2" d="M43.6 20.5H42V20H24v8h11.3c-.8 2.2-2.2 4.1-4.1 5.5l.0.0 6.2 5.2C39.2 36.3 44 31.5 44 24c0-1.3-.1-2.5-.4-3.5z"/></svg>
                Continue with Google
            </button>
        @endif

        @if (in_array('facebook', $providers, true))
            <button type="button" class="auth-social__btn auth-social__btn--facebook" data-firebase-provider="facebook" @disabled(! $firebaseReady)>
                Facebook
            </button>
        @endif

        @if (in_array('github', $providers, true))
            <button type="button" class="auth-social__btn auth-social__btn--github" data-firebase-provider="github" @disabled(! $firebaseReady)>
                GitHub
            </button>
        @endif
    </div>

    @if ($socialFirst)
        <p class="auth-hint" style="margin-top:0.75rem;">
            First time with Google? We create your account automatically, then you add student ID and phone on the next screen. Same email always links to one account.
        </p>
    @else
        <p class="auth-hint" style="margin-top:0.75rem;">
            Social sign-in creates or links your StudentMove account. One email = one account — no duplicates.
        </p>
    @endif
</div>
