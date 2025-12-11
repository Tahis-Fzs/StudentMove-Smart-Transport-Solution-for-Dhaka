<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\Mailer\Exception\TransportException;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        // #region agent log
        $dbg = function($payload) {
            $line = json_encode([
                'sessionId' => 'debug-session',
                'runId' => 'email-verification-resend',
                'hypothesisId' => $payload['h'] ?? 'EV1',
                'location' => $payload['loc'] ?? 'EmailVerificationNotificationController',
                'message' => $payload['msg'] ?? '',
                'data' => $payload['data'] ?? [],
                'timestamp' => round(microtime(true) * 1000),
            ]);
            @file_put_contents(base_path('.cursor/debug.log'), $line . PHP_EOL, FILE_APPEND | LOCK_EX);
        };
        // #endregion

        $dbg(['h' => 'EV1', 'loc' => 'store.start', 'msg' => 'resend verification email requested', 'data' => ['user_id' => $request->user()->id, 'email' => $request->user()->email]]);

        if ($request->user()->hasVerifiedEmail()) {
            $dbg(['h' => 'EV2', 'loc' => 'already-verified', 'msg' => 'email already verified']);
            return redirect()->intended(RouteServiceProvider::HOME);
        }

        try {
            $request->user()->sendEmailVerificationNotification();
            $dbg(['h' => 'EV3', 'loc' => 'email-sent', 'msg' => 'verification email resent', 'data' => ['email' => $request->user()->email]]);
            return redirect()->back()->with('status', 'verification-link-sent');
        } catch (\Symfony\Component\Mailer\Exception\TransportException $e) {
            $errorMsg = $e->getMessage();
            $dbg(['h' => 'EV4', 'loc' => 'email.transport-exception', 'msg' => 'verification email transport failed', 'data' => [
                'error' => $errorMsg,
                'mail_host' => config('mail.mailers.smtp.host'),
                'mail_username' => config('mail.mailers.smtp.username')
            ]]);
            \Log::error('Verification email resend failed: ' . $errorMsg);
            
            // Check for authentication errors
            if (strpos($errorMsg, 'authentication') !== false || strpos($errorMsg, '535') !== false || strpos($errorMsg, 'BadCredentials') !== false) {
                $dbg(['h' => 'EV4a', 'loc' => 'email.auth-failed', 'msg' => 'Gmail authentication failed on resend']);
                return redirect()->back()->with('error', 'Failed to send verification email!<br><br><strong>Gmail Authentication Error:</strong> Your Gmail App Password may be incorrect or expired.<br><br>Please:<br>1. Go to <a href="https://myaccount.google.com/apppasswords" target="_blank">https://myaccount.google.com/apppasswords</a><br>2. Generate a new App Password<br>3. Run: <code>php artisan email:configure-gmail your-email@gmail.com new-app-password</code><br>4. Then try resending the verification email again.');
            }
            
            return redirect()->back()->with('error', 'Failed to send verification email: ' . $errorMsg);
        } catch (\Exception $e) {
            $dbg(['h' => 'EV4', 'loc' => 'email-failed', 'msg' => 'failed to resend verification email', 'data' => ['error' => $e->getMessage()]]);
            \Log::error('Verification email resend failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to send verification email. Please try again later.');
        }
    }
}
