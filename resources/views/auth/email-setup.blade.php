<x-guest-layout>
    @push('styles')
    <link rel="stylesheet" href="{{ asset('css/signin.css') }}">
    @endpush

    <div class="signin-container">
        <div class="signin-header">
            <h1 class="signin-title"><i class="bi bi-envelope-check"></i> Email Setup</h1>
            <p class="signin-subtitle">Configure Gmail to receive emails in your inbox</p>
        </div>

        <!-- Instructions -->
        <div class="demo-info" style="background: #e0f2fe; color: #0c4a6e; border: 1px solid #7dd3fc; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <strong style="display: block; margin-bottom: 10px;">📧 Quick Setup Steps:</strong>
            <ol style="margin: 0; padding-left: 20px;">
                <li>Go to <a href="https://myaccount.google.com/security" target="_blank" style="color: #0284c7;">Google Account Security</a> and enable 2-Step Verification</li>
                <li>Go to <a href="https://myaccount.google.com/apppasswords" target="_blank" style="color: #0284c7;">App Passwords</a></li>
                <li>Select "Mail" → "Other (Custom name)" → Enter "StudentMove"</li>
                <li>Click "Generate" and copy the 16-character password</li>
            </ol>
            <div style="margin-top: 15px; padding: 15px; background: linear-gradient(135deg, rgba(34, 197, 94, 0.15), rgba(16, 185, 129, 0.1)); border: 2px solid rgba(34, 197, 94, 0.4); border-radius: 8px;">
                <strong style="color: #16a34a; font-size: 16px; display: block; margin-bottom: 10px;">💡 EASIER METHOD: Use Terminal Command</strong>
                <p style="margin: 0 0 10px 0; font-size: 14px; color: #166534;">If the form doesn't work, use this command in Terminal (it's more reliable):</p>
                <div style="background: rgba(0,0,0,0.2); padding: 12px; border-radius: 6px; margin-top: 8px;">
                    <code style="color: #e0f2fe; font-size: 13px; font-family: 'Monaco', 'Courier New', monospace; word-break: break-all; display: block;">
                        php artisan email:configure-gmail your-email@gmail.com your-app-password
                    </code>
                </div>
                <p style="margin: 10px 0 0 0; font-size: 12px; color: #166534; font-style: italic;">💡 Copy the command, replace with your email and App Password, then paste in Terminal</p>
            </div>
        </div>

        @if(session('success'))
            <div class="success-message">
                <i class="bi bi-check-circle"></i> {!! session('success') !!}
            </div>
        @endif

        <form class="signin-form" method="POST" action="{{ route('email-setup.store') }}">
            @csrf
            
            <div class="form-group">
                <label class="form-label">Gmail Address</label>
                <input type="email" name="gmail_email" class="form-input" placeholder="yourname@gmail.com" value="{{ old('gmail_email') }}" required autofocus>
                @error('gmail_email')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Gmail App Password</label>
                <input type="text" name="gmail_app_password" class="form-input" placeholder="abcd efgh ijkl mnop (16 characters)" value="{{ old('gmail_app_password') }}" required>
                <small style="color: #64748b; font-size: 12px; display: block; margin-top: 5px;">Get your App Password from: <a href="https://myaccount.google.com/apppasswords" target="_blank" style="color: #0284c7;">https://myaccount.google.com/apppasswords</a></small>
                @error('gmail_app_password')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="signin-btn">
                <i class="bi bi-check-circle"></i> Configure Gmail Automatically
            </button>
        </form>

        <div class="divider">
            <span>or</span>
        </div>

        <div class="signup-link">
            <a href="{{ route('register') }}">Continue with Mailpit (Local Testing)</a>
        </div>
    </div>

    <style>
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
        }

        .signin-title i {
            color: #007BFF;
        }

        .signin-btn {
            background: linear-gradient(135deg, #007BFF, #0056b3);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 25px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            margin-top: 20px;
        }

        .signin-btn:hover {
            background: linear-gradient(135deg, #0056b3, #007BFF);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.4);
        }
    </style>
</x-guest-layout>

