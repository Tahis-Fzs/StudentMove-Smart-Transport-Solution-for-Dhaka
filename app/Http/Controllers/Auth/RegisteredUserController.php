<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\EmailHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'string', 'max:20'],
            'university' => ['required', 'string', 'max:255'],
            'student_id' => ['required', 'string', 'max:50'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'terms' => ['required', 'accepted'],
        ], [
            'first_name.required' => 'First name is required.',
            'first_name.max' => 'First name cannot exceed 255 characters.',
            'last_name.required' => 'Last name is required.',
            'last_name.max' => 'Last name cannot exceed 255 characters.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already registered. Please use a different email or <a href="' . route('login') . '">sign in</a> instead.',
            'email.max' => 'Email address cannot exceed 255 characters.',
            'phone.required' => 'Phone number is required.',
            'phone.max' => 'Phone number cannot exceed 20 characters.',
            'university.required' => 'University name is required.',
            'university.max' => 'University name cannot exceed 255 characters.',
            'student_id.required' => 'Student ID is required.',
            'student_id.max' => 'Student ID cannot exceed 50 characters.',
            'password.required' => 'Password is required.',
            'password.confirmed' => 'Passwords do not match. Please make sure both password fields are identical.',
            'password.min' => 'Password must be at least 8 characters long.',
            'terms.required' => 'You must agree to the Terms of Service and Privacy Policy.',
            'terms.accepted' => 'You must agree to the Terms of Service and Privacy Policy.',
        ]);

        // #region agent log
        $dbg = function($payload) {
            $line = json_encode([
                'sessionId' => 'debug-session',
                'runId' => 'registration',
                'hypothesisId' => $payload['h'] ?? 'REG1',
                'location' => $payload['loc'] ?? 'RegisteredUserController',
                'message' => $payload['msg'] ?? '',
                'data' => $payload['data'] ?? [],
                'timestamp' => round(microtime(true) * 1000),
            ]);
            @file_put_contents(base_path('.cursor/debug.log'), $line . PHP_EOL, FILE_APPEND | LOCK_EX);
        };
        // #endregion
        
        // AUTOMATIC EMAIL CONFIGURATION: Ensure email is ready before registration
        $dbg(['h' => 'REG1', 'loc' => 'store.start', 'msg' => 'registration started', 'data' => ['email' => $request->email]]);
        
        $emailStatus = EmailHelper::ensureEmailConfigured();
        $dbg(['h' => 'REG2', 'loc' => 'email-check', 'msg' => 'email configuration check', 'data' => $emailStatus]);
        
        if (!$emailStatus['ready']) {
            $dbg(['h' => 'REG3', 'loc' => 'email-not-ready', 'msg' => 'email not configured, redirecting to setup', 'data' => $emailStatus]);
            
            // AUTOMATIC REDIRECT: If Gmail not configured, redirect to automatic setup page
            if ($emailStatus['type'] === 'gmail' || ($emailStatus['type'] === 'mailpit' && !$emailStatus['ready'])) {
                return redirect()->route('email-setup')
                    ->with('info', 'Please configure your email to receive verification emails in your inbox. This will only take a moment!');
            }
            
            // Fallback error message
            $errorMsg = '❌ Email is not configured. Registration requires email verification for security.<br><br>';
            if ($emailStatus['type'] === 'mailpit') {
                $errorMsg .= '<strong>Quick Setup (Recommended for Local Development):</strong><br>';
                $errorMsg .= '1. Open Terminal and run: <code>brew install axllent/mailpit/mailpit</code><br>';
                $errorMsg .= '2. Then start Mailpit: <code>mailpit</code><br>';
                $errorMsg .= '3. View emails at: <a href="http://127.0.0.1:8025" target="_blank">http://127.0.0.1:8025</a><br><br>';
                $errorMsg .= '<strong>Or configure Gmail:</strong> <a href="' . route('email-setup') . '">Click here for automatic Gmail setup</a>';
            } else {
                $errorMsg .= $emailStatus['message'];
            }
            
            return back()->withInput()->withErrors(['email' => $errorMsg]);
        }

        $user = User::create([
            'name' => $request->first_name . ' ' . $request->last_name,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'university' => $request->university,
            'student_id' => $request->student_id,
            'password' => Hash::make($request->password),
        ]);
        
        $dbg(['h' => 'REG4', 'loc' => 'user.created', 'msg' => 'user created', 'data' => ['user_id' => $user->id, 'email' => $user->email]]);

        try {
            // Send 6-digit verification code instead of link
            $dbg(['h' => 'REG5a', 'loc' => 'before-code-send', 'msg' => 'about to send 6-digit verification code', 'data' => [
                'email' => $user->email, 
                'email_type' => $emailStatus['type'],
                'mail_host' => config('mail.mailers.smtp.host'),
                'mail_port' => config('mail.mailers.smtp.port'),
                'mail_username' => config('mail.mailers.smtp.username')
            ]]);
            
            // Generate and send 6-digit code
            \App\Models\EmailVerificationCode::where('user_id', $user->id)
                ->where('used', false)
                ->update(['used' => true]);
            
            $code = \App\Models\EmailVerificationCode::generateCode();
            $dbg(['h' => 'REG5b', 'loc' => 'code-generated', 'msg' => 'generated 6-digit code', 'data' => ['code' => $code]]);
            
            $verificationCode = \App\Models\EmailVerificationCode::create([
                'user_id' => $user->id,
                'code' => $code,
                'email' => $user->email,
                'expires_at' => now()->addMinutes(15),
                'used' => false,
            ]);
            
            // Send email with code
            Mail::send('emails.verification-code', [
                'code' => $code,
                'user' => $user,
            ], function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('StudentMove - Email Verification Code');
            });
            
            $dbg(['h' => 'REG5', 'loc' => 'code.sent', 'msg' => 'verification code sent', 'data' => [
                'email' => $user->email, 
                'email_type' => $emailStatus['type'],
                'code_id' => $verificationCode->id,
                'method' => '6-digit code'
            ]]);
            
            // Verify email was actually sent
            if ($emailStatus['type'] === 'mailpit') {
                sleep(2); // Give Mailpit more time to receive the email
                $mailpitCheck = @file_get_contents('http://127.0.0.1:8025/api/v1/messages');
                $mailpitData = $mailpitCheck ? json_decode($mailpitCheck, true) : null;
                $emailCount = $mailpitData['total'] ?? 0;
                $latestEmail = $mailpitData['messages'][0] ?? null;
                $dbg(['h' => 'REG7', 'loc' => 'mailpit-verify', 'msg' => 'verified email in Mailpit', 'data' => [
                    'email_count' => $emailCount, 
                    'user_email' => $user->email,
                    'latest_subject' => $latestEmail['Subject'] ?? null,
                    'latest_to' => $latestEmail['To'][0]['Address'] ?? null
                ]]);
            } else if ($emailStatus['type'] === 'gmail') {
                $dbg(['h' => 'REG8', 'loc' => 'gmail-sent', 'msg' => 'email should be sent to Gmail', 'data' => [
                    'user_email' => $user->email, 
                    'gmail_config' => config('mail.mailers.smtp.username'),
                    'mail_host' => config('mail.mailers.smtp.host'),
                    'mail_port' => config('mail.mailers.smtp.port')
                ]]);
            }
        } catch (\Symfony\Component\Mailer\Exception\TransportException $e) {
            $errorMsg = $e->getMessage();
            $dbg(['h' => 'REG6', 'loc' => 'email.transport-exception', 'msg' => 'email transport failed', 'data' => [
                'error' => $errorMsg, 
                'error_code' => $e->getCode(),
                'mail_host' => config('mail.mailers.smtp.host'),
                'mail_username' => config('mail.mailers.smtp.username'),
                'has_password' => !empty(config('mail.mailers.smtp.password'))
            ]]);
            \Log::error('Registration email transport failed: ' . $errorMsg);
            
            // Check for authentication errors
            if (strpos($errorMsg, 'authentication') !== false || strpos($errorMsg, '535') !== false || strpos($errorMsg, 'BadCredentials') !== false) {
                $dbg(['h' => 'REG6a', 'loc' => 'email.auth-failed', 'msg' => 'Gmail authentication failed', 'data' => ['error' => $errorMsg]]);
                // Show user-friendly error about Gmail credentials
                Auth::login($user);
                return redirect(RouteServiceProvider::HOME)
                    ->with('error', 'Registration successful, but email verification failed!<br><br><strong>Gmail Authentication Error:</strong> Your Gmail App Password may be incorrect or expired.<br><br>Please:<br>1. Go to <a href="https://myaccount.google.com/apppasswords" target="_blank">https://myaccount.google.com/apppasswords</a><br>2. Generate a new App Password<br>3. Run: <code>php artisan email:configure-gmail your-email@gmail.com new-app-password</code><br>4. Then request a new verification email from your profile.');
            }
            
            // Don't fail registration for other transport errors
            Auth::login($user);
        } catch (\Exception $e) {
            $dbg(['h' => 'REG6', 'loc' => 'email.failed', 'msg' => 'registration email failed', 'data' => [
                'error' => $e->getMessage(), 
                'error_type' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => substr($e->getTraceAsString(), 0, 500)
            ]]);
            \Log::error('Registration email failed: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // Don't fail registration, but log the error
            // User is still created and logged in
            Auth::login($user);
        }

        Auth::login($user);

            // Show success message based on email type
            $redirect = redirect(RouteServiceProvider::HOME);
            if ($emailStatus['type'] === 'mailpit') {
                $redirect = $redirect->with('success', 'Registration successful! A 6-digit verification code was sent to <a href="http://127.0.0.1:8025" target="_blank" style="color: #22c55e; font-weight: bold; text-decoration: underline;">Mailpit</a> (local testing).<br><br><strong>Want emails in your Gmail inbox?</strong> <a href="' . route('email-setup') . '" style="color: #2563eb; font-weight: bold;">Configure Gmail here</a> - it only takes 30 seconds!');
            } else if ($emailStatus['type'] === 'gmail') {
                $redirect = $redirect->with('success', 'Registration successful! A 6-digit verification code was sent to your Gmail inbox (<strong>' . $user->email . '</strong>). Please check your email and enter the code to verify your account. Also check your spam folder if you don\'t see it.');
            } else {
                $redirect = $redirect->with('success', 'Registration successful! A 6-digit verification code was sent to your email. Please check your inbox and enter the code to verify your account.');
            }

        return $redirect;
    }
}