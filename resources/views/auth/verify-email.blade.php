<x-guest-layout>
    @push('styles')
    <link rel="stylesheet" href="/css/base.css">
    @endpush

    <div class="container" style="max-width: 600px; margin: 40px auto; padding: 20px;">
        <div style="background: linear-gradient(180deg, rgba(255,255,255,0.10), rgba(255,255,255,0.04)); border: 1px solid rgba(255,255,255,0.12); border-radius: 14px; padding: 30px; box-shadow: 0 12px 24px rgba(0,0,0,0.14);">
            <h1 style="color: #e0f2fe; font-size: 24px; margin-bottom: 16px;">📧 Verify Your Email</h1>
            
            <div style="color: #cbd5e1; margin-bottom: 20px; line-height: 1.6;">
                <p>Thanks for signing up! Before getting started, please verify your email address by clicking the link we sent to:</p>
                <p style="font-weight: bold; color: #e0f2fe; margin: 10px 0;">{{ Auth::user()->email }}</p>
            </div>

            @php
                $mailHost = config('mail.mailers.smtp.host');
                $mailPort = config('mail.mailers.smtp.port');
                $isMailpit = ($mailHost === '127.0.0.1' || $mailHost === 'localhost') && $mailPort == 1025;
            @endphp

            @if($isMailpit)
                <div style="background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.3); border-radius: 8px; padding: 15px; margin-bottom: 20px;">
                    <p style="color: #86efac; margin: 0 0 10px 0; font-weight: 600;">📍 Your email is in Mailpit (local testing)</p>
                    <p style="color: #cbd5e1; margin: 0 0 10px 0; font-size: 14px;">Click here to view your verification email:</p>
                    <a href="http://127.0.0.1:8025" target="_blank" style="display: inline-block; background: linear-gradient(90deg, #2563eb, #22c55e); color: #0f172a; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 700; margin-top: 8px;">Open Mailpit →</a>
                    <p style="color: #94a3b8; margin: 10px 0 0 0; font-size: 12px;">Want emails in your Gmail inbox? <a href="{{ route('email-setup') }}" style="color: #60a5fa;">Configure Gmail here</a></p>
                </div>
            @else
                <div style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); border-radius: 8px; padding: 15px; margin-bottom: 20px;">
                    <p style="color: #93c5fd; margin: 0 0 10px 0; font-weight: 600;">📬 Check your Gmail inbox</p>
                    <p style="color: #cbd5e1; margin: 0; font-size: 14px;">The verification email was sent to <strong>{{ Auth::user()->email }}</strong>. Also check your spam folder if you don't see it.</p>
                </div>
            @endif

            @if (session('status') == 'verification-link-sent')
                <div style="background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.3); border-radius: 8px; padding: 12px; margin-bottom: 20px;">
                    <p style="color: #86efac; margin: 0; font-weight: 600;">✅ A new verification link has been sent!</p>
                </div>
            @endif

            <div style="display: flex; gap: 15px; align-items: center; margin-top: 25px;">
                <form method="POST" action="{{ route('verification.send') }}" style="margin: 0;">
                    @csrf
                    <button type="submit" style="background: linear-gradient(90deg, #2563eb, #22c55e); color: #0f172a; border: none; padding: 12px 24px; border-radius: 10px; font-weight: 700; cursor: pointer; transition: transform 0.1s ease, box-shadow 0.2s ease;">
                        🔄 Resend Verification Email
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                    @csrf
                    <button type="submit" style="background: transparent; color: #94a3b8; border: 1px solid rgba(255,255,255,0.2); padding: 12px 24px; border-radius: 10px; cursor: pointer; transition: all 0.2s ease;">
                        Log Out
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
