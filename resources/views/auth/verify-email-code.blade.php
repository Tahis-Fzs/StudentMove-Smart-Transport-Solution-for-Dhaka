<x-guest-layout>
    @push('styles')
    <link rel="stylesheet" href="/css/base.css">
    @endpush

    <div class="container" style="max-width: 600px; margin: 40px auto; padding: 20px;">
        <div style="background: linear-gradient(180deg, rgba(255,255,255,0.10), rgba(255,255,255,0.04)); border: 1px solid rgba(255,255,255,0.12); border-radius: 14px; padding: 30px; box-shadow: 0 12px 24px rgba(0,0,0,0.14);">
            <h1 style="color: #e0f2fe; font-size: 24px; margin-bottom: 16px;">🔐 Verify Your Email</h1>
            
            <div style="color: #cbd5e1; margin-bottom: 20px; line-height: 1.6;">
                <p>We've sent a 6-digit verification code to:</p>
                <p style="font-weight: bold; color: #e0f2fe; margin: 10px 0; font-size: 18px;">{{ Auth::user()->email }}</p>
                <p style="font-size: 14px; color: #94a3b8;">Please enter the code below to verify your email address.</p>
            </div>

            @php
                $mailHost = config('mail.mailers.smtp.host');
                $mailPort = config('mail.mailers.smtp.port');
                $isMailpit = ($mailHost === '127.0.0.1' || $mailHost === 'localhost') && $mailPort == 1025;
            @endphp

            @if($isMailpit)
                <div style="background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.3); border-radius: 8px; padding: 15px; margin-bottom: 20px;">
                    <p style="color: #86efac; margin: 0 0 10px 0; font-weight: 600;">📍 Your email is in Mailpit (local testing)</p>
                    <p style="color: #cbd5e1; margin: 0 0 10px 0; font-size: 14px;">Click here to view your verification code:</p>
                    <a href="http://127.0.0.1:8025" target="_blank" style="display: inline-block; background: linear-gradient(90deg, #2563eb, #22c55e); color: #0f172a; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 700; margin-top: 8px;">Open Mailpit →</a>
                </div>
            @else
                <div style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); border-radius: 8px; padding: 15px; margin-bottom: 20px;">
                    <p style="color: #93c5fd; margin: 0 0 10px 0; font-weight: 600;">📬 Check your Gmail inbox</p>
                    <p style="color: #cbd5e1; margin: 0; font-size: 14px;">The verification code was sent to <strong>{{ Auth::user()->email }}</strong>. Also check your spam folder if you don't see it.</p>
                </div>
            @endif

            @if (session('status') == 'verification-code-sent')
                <div style="background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.3); border-radius: 8px; padding: 12px; margin-bottom: 20px;">
                    <p style="color: #86efac; margin: 0; font-weight: 600;">✅ A new verification code has been sent!</p>
                </div>
            @endif

            @if (session('error'))
                <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 8px; padding: 12px; margin-bottom: 20px;">
                    <p style="color: #fca5a5; margin: 0; font-weight: 600;">❌ {!! session('error') !!}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('verification.code.verify') }}" style="margin-top: 25px;">
                @csrf
                
                <div style="margin-bottom: 20px;">
                    <label style="display: block; color: #cbd5e1; margin-bottom: 8px; font-weight: 600;">Enter 6-Digit Code</label>
                    <input 
                        type="text" 
                        name="code" 
                        id="code"
                        maxlength="6" 
                        pattern="[0-9]{6}"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        style="width: 100%; padding: 15px; font-size: 24px; text-align: center; letter-spacing: 8px; border: 2px solid rgba(255,255,255,0.2); border-radius: 10px; background: rgba(255,255,255,0.05); color: #e0f2fe; font-family: 'Courier New', monospace; font-weight: bold;"
                        placeholder="000000"
                        required
                        autofocus
                    >
                    @error('code')
                        <div style="color: #fca5a5; margin-top: 8px; font-size: 14px;">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" style="width: 100%; background: linear-gradient(90deg, #2563eb, #22c55e); color: #0f172a; border: none; padding: 15px 24px; border-radius: 10px; font-weight: 700; font-size: 16px; cursor: pointer; transition: transform 0.1s ease, box-shadow 0.2s ease; margin-bottom: 15px;">
                    ✅ Verify Email
                </button>
            </form>

            <div style="display: flex; gap: 15px; align-items: center; margin-top: 20px; flex-wrap: wrap;">
                <form method="POST" action="{{ route('verification.code.send') }}" style="margin: 0;">
                    @csrf
                    <button type="submit" style="background: transparent; color: #60a5fa; border: 1px solid rgba(96, 165, 250, 0.3); padding: 12px 24px; border-radius: 10px; cursor: pointer; transition: all 0.2s ease; font-weight: 600;">
                        🔄 Resend Code
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                    @csrf
                    <button type="submit" style="background: transparent; color: #94a3b8; border: 1px solid rgba(255,255,255,0.2); padding: 12px 24px; border-radius: 10px; cursor: pointer; transition: all 0.2s ease;">
                        Log Out
                    </button>
                </form>
            </div>

            <p style="color: #94a3b8; font-size: 12px; text-align: center; margin-top: 20px;">
                Code expires in 15 minutes
            </p>
        </div>
    </div>

    <script>
        // Auto-format code input (numbers only, 6 digits)
        document.getElementById('code').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
        });

        // Auto-submit when 6 digits are entered
        document.getElementById('code').addEventListener('input', function(e) {
            if (this.value.length === 6) {
                // Small delay for better UX
                setTimeout(() => {
                    this.form.submit();
                }, 300);
            }
        });
    </script>
</x-guest-layout>


