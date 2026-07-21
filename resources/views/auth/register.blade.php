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
                <p class="auth-aside__text">One student. One ID. One verified email. Built for the commute that runs Dhaka.</p>
            </div>
        </aside>

        <section class="auth-panel auth-panel--wide sm-reveal">
            <p class="auth-kicker">Email signup</p>
            <h1 class="auth-title">Register with student verification</h1>
            <p class="auth-sub">
                Use this path if you want a password and verified campus email upfront.
                Faster? <a class="auth-link" href="{{ route('login') }}">Continue with Google on sign in</a>.
                Already registered? <a class="auth-link" href="{{ route('login') }}">Sign in</a>.
            </p>

            @if ($errors->any())
                <div class="auth-alert auth-alert--err">
                    Fix the highlighted fields. Duplicate email, phone, or student ID accounts are not allowed.
                </div>
            @endif

            <form class="auth-form" method="POST" action="{{ route('register') }}" novalidate>
                @csrf

                <div class="auth-row">
                    <div class="auth-field">
                        <label for="first_name">First name</label>
                        <input id="first_name" type="text" name="first_name" value="{{ old('first_name') }}" autocomplete="given-name" required>
                        @error('first_name')<div class="auth-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="auth-field">
                        <label for="last_name">Last name</label>
                        <input id="last_name" type="text" name="last_name" value="{{ old('last_name') }}" autocomplete="family-name" required>
                        @error('last_name')<div class="auth-error">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="auth-field">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
                    <p class="auth-hint">Must be a real domain (MX check). Disposable addresses are blocked.</p>
                    @error('email')<div class="auth-error">{!! $message !!}</div>@enderror
                </div>

                <div class="auth-field">
                    <label for="phone">Mobile (Bangladesh)</label>
                    <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" placeholder="017XXXXXXXX" autocomplete="tel" required>
                    <p class="auth-hint">One phone number per account.</p>
                    @error('phone')<div class="auth-error">{{ $message }}</div>@enderror
                </div>

                <div class="auth-row">
                    <div class="auth-field">
                        <label for="university">University</label>
                        <input id="university" type="text" name="university" value="{{ old('university') }}" required>
                        @error('university')<div class="auth-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="auth-field">
                        <label for="student_id">Student ID</label>
                        <input id="student_id" type="text" name="student_id" value="{{ old('student_id') }}" required>
                        <p class="auth-hint">Unique — cannot be reused.</p>
                        @error('student_id')<div class="auth-error">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="auth-field">
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" autocomplete="new-password" required>
                    <p class="auth-hint">Min 8 characters, mixed case + a number.</p>
                    @error('password')<div class="auth-error">{{ $message }}</div>@enderror
                </div>

                <div class="auth-field">
                    <label for="password_confirmation">Confirm password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required>
                </div>

                <label class="auth-check">
                    <input type="checkbox" name="terms" value="1" {{ old('terms') ? 'checked' : '' }} required>
                    <span>I agree to the Terms of Service and Privacy Policy, and to receive route notifications.</span>
                </label>
                @error('terms')<div class="auth-error">{{ $message }}</div>@enderror

                <button type="submit" class="auth-submit">Create verified account</button>
            </form>

            @include('partials.firebase-auth', ['intent' => 'register'])

            <div class="auth-foot">
                Already registered? <a class="auth-link" href="{{ route('login') }}">Sign in</a>
            </div>
        </section>
    </div>
</x-guest-layout>
