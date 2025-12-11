<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailVerificationCode;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Symfony\Component\Mailer\Exception\TransportException;

class EmailVerificationCodeController extends Controller
{
    /**
     * Show the verification code entry form
     */
    public function show(): View
    {
        // #region agent log
        $dbg = function($payload) {
            $line = json_encode([
                'sessionId' => 'debug-session',
                'runId' => 'email-code-verify',
                'hypothesisId' => $payload['h'] ?? 'EC1',
                'location' => $payload['loc'] ?? 'EmailVerificationCodeController',
                'message' => $payload['msg'] ?? '',
                'data' => $payload['data'] ?? [],
                'timestamp' => round(microtime(true) * 1000),
            ]);
            @file_put_contents(base_path('.cursor/debug.log'), $line . PHP_EOL, FILE_APPEND | LOCK_EX);
        };
        // #endregion

        $dbg(['h' => 'EC1', 'loc' => 'show', 'msg' => 'showing verification code form', 'data' => ['user_id' => auth()->id(), 'email' => auth()->user()->email]]);
        
        return view('auth.verify-email-code');
    }

    /**
     * Send a new 6-digit verification code
     */
    public function send(Request $request): RedirectResponse
    {
        // #region agent log
        $dbg = function($payload) {
            $line = json_encode([
                'sessionId' => 'debug-session',
                'runId' => 'email-code-send',
                'hypothesisId' => $payload['h'] ?? 'EC2',
                'location' => $payload['loc'] ?? 'EmailVerificationCodeController',
                'message' => $payload['msg'] ?? '',
                'data' => $payload['data'] ?? [],
                'timestamp' => round(microtime(true) * 1000),
            ]);
            @file_put_contents(base_path('.cursor/debug.log'), $line . PHP_EOL, FILE_APPEND | LOCK_EX);
        };
        // #endregion

        $user = $request->user();
        $dbg(['h' => 'EC2', 'loc' => 'send.start', 'msg' => 'sending verification code', 'data' => ['user_id' => $user->id, 'email' => $user->email]]);

        if ($user->hasVerifiedEmail()) {
            $dbg(['h' => 'EC2a', 'loc' => 'already-verified', 'msg' => 'email already verified']);
            return redirect()->intended(RouteServiceProvider::HOME);
        }

        // Invalidate old codes for this user
        EmailVerificationCode::where('user_id', $user->id)
            ->where('used', false)
            ->update(['used' => true]);

        // Generate new 6-digit code
        $code = EmailVerificationCode::generateCode();
        $dbg(['h' => 'EC3', 'loc' => 'code-generated', 'msg' => 'generated verification code', 'data' => ['code' => $code]]);

        // Create verification code record
        $verificationCode = EmailVerificationCode::create([
            'user_id' => $user->id,
            'code' => $code,
            'email' => $user->email,
            'expires_at' => now()->addMinutes(15), // Code valid for 15 minutes
            'used' => false,
        ]);

        $dbg(['h' => 'EC4', 'loc' => 'code-saved', 'msg' => 'verification code saved to database', 'data' => ['code_id' => $verificationCode->id]]);

        // Send email with code
        try {
            Mail::send('emails.verification-code', [
                'code' => $code,
                'user' => $user,
            ], function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('StudentMove - Email Verification Code');
            });

            $dbg(['h' => 'EC5', 'loc' => 'email-sent', 'msg' => 'verification code email sent', 'data' => ['email' => $user->email]]);
            
            return redirect()->back()->with('status', 'verification-code-sent');
        } catch (TransportException $e) {
            $errorMsg = $e->getMessage();
            $dbg(['h' => 'EC6', 'loc' => 'email-transport-exception', 'msg' => 'failed to send verification code email', 'data' => [
                'error' => $errorMsg,
                'mail_host' => config('mail.mailers.smtp.host'),
                'mail_username' => config('mail.mailers.smtp.username')
            ]]);
            \Log::error('Verification code email failed: ' . $errorMsg);
            
            if (strpos($errorMsg, 'authentication') !== false || strpos($errorMsg, '535') !== false) {
                return redirect()->back()->with('error', 'Failed to send verification code!<br><br><strong>Gmail Authentication Error:</strong> Please check your email configuration.');
            }
            
            return redirect()->back()->with('error', 'Failed to send verification code. Please try again later.');
        } catch (\Exception $e) {
            $dbg(['h' => 'EC7', 'loc' => 'email-exception', 'msg' => 'unexpected error sending code', 'data' => ['error' => $e->getMessage()]]);
            \Log::error('Verification code email error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to send verification code. Please try again later.');
        }
    }

    /**
     * Verify the 6-digit code
     */
    public function verify(Request $request): RedirectResponse
    {
        // #region agent log
        $dbg = function($payload) {
            $line = json_encode([
                'sessionId' => 'debug-session',
                'runId' => 'email-code-verify',
                'hypothesisId' => $payload['h'] ?? 'EC8',
                'location' => $payload['loc'] ?? 'EmailVerificationCodeController',
                'message' => $payload['msg'] ?? '',
                'data' => $payload['data'] ?? [],
                'timestamp' => round(microtime(true) * 1000),
            ]);
            @file_put_contents(base_path('.cursor/debug.log'), $line . PHP_EOL, FILE_APPEND | LOCK_EX);
        };
        // #endregion

        $request->validate([
            'code' => ['required', 'string', 'size:6', 'regex:/^[0-9]{6}$/'],
        ]);

        $user = $request->user();
        $code = $request->input('code');
        
        $dbg(['h' => 'EC8', 'loc' => 'verify.start', 'msg' => 'verifying code', 'data' => [
            'user_id' => $user->id,
            'email' => $user->email,
            'code_entered' => $code
        ]]);

        if ($user->hasVerifiedEmail()) {
            $dbg(['h' => 'EC8a', 'loc' => 'already-verified', 'msg' => 'email already verified']);
            return redirect()->intended(RouteServiceProvider::HOME.'?verified=1');
        }

        // Find valid verification code
        $verificationCode = EmailVerificationCode::where('user_id', $user->id)
            ->where('code', $code)
            ->where('email', $user->email)
            ->valid()
            ->latest()
            ->first();

        if (!$verificationCode) {
            $dbg(['h' => 'EC9', 'loc' => 'code-invalid', 'msg' => 'verification code not found or invalid', 'data' => ['code' => $code]]);
            return redirect()->back()->withErrors(['code' => 'Invalid or expired verification code. Please request a new code.']);
        }

        $dbg(['h' => 'EC10', 'loc' => 'code-valid', 'msg' => 'verification code is valid', 'data' => ['code_id' => $verificationCode->id]]);

        // Mark code as used
        $verificationCode->markAsUsed();
        $dbg(['h' => 'EC11', 'loc' => 'code-used', 'msg' => 'marked code as used']);

        // Verify user's email
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
            $dbg(['h' => 'EC12', 'loc' => 'email-verified', 'msg' => 'email verified successfully', 'data' => ['user_id' => $user->id]]);
        }

        return redirect()->intended(RouteServiceProvider::HOME.'?verified=1');
    }
}
