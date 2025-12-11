<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\EmailHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        try {
            // #region agent log
            $dbg = function($payload) {
                $line = json_encode([
                    'sessionId' => 'debug-session',
                    'runId' => 'password-reset',
                    'hypothesisId' => $payload['h'] ?? 'PR1',
                    'location' => $payload['loc'] ?? 'PasswordResetLinkController',
                    'message' => $payload['msg'] ?? '',
                    'data' => $payload['data'] ?? [],
                    'timestamp' => round(microtime(true) * 1000),
                ]);
                @file_put_contents(base_path('.cursor/debug.log'), $line . PHP_EOL, FILE_APPEND | LOCK_EX);
            };
            // #endregion
            
            $mailDriver = config('mail.default');
            $mailUsername = config('mail.mailers.smtp.username');
            $mailPassword = config('mail.mailers.smtp.password');
            
            $dbg(['h' => 'PR1', 'loc' => 'store.start', 'msg' => 'password reset requested', 'data' => [
                'email' => $request->email, 
                'mail_driver' => $mailDriver,
                'mail_username' => $mailUsername,
                'mail_password_set' => !empty($mailPassword) && $mailPassword !== 'null'
            ]]);
            
            // AUTOMATIC EMAIL CONFIGURATION: Ensure email is ready before sending reset link
            $emailStatus = EmailHelper::ensureEmailConfigured();
            $dbg(['h' => 'PR1a', 'loc' => 'email-check', 'msg' => 'email configuration check', 'data' => $emailStatus]);
            
            if (!$emailStatus['ready']) {
                $errorMsg = '❌ ' . $emailStatus['message'];
                if ($emailStatus['type'] === 'mailpit') {
                    $errorMsg .= '<br><br>Please install Mailpit: <code>brew install axllent/mailpit/mailpit</code><br>Then start it: <code>mailpit</code><br><br>View emails at: <a href="http://127.0.0.1:8025" target="_blank">http://127.0.0.1:8025</a>';
                } else if ($emailStatus['type'] === 'smtp') {
                    $errorMsg .= '<br><br>For Gmail:<br><code>MAIL_USERNAME=your-email@gmail.com</code><br><code>MAIL_PASSWORD=your-app-password</code><br><br>Get Gmail App Password: <a href="https://myaccount.google.com/apppasswords" target="_blank">https://myaccount.google.com/apppasswords</a>';
                }
                $errorMsg .= '<br><br>For security, password reset links are only sent via email.';
                return back()->withInput($request->only('email'))->withErrors(['email' => $errorMsg]);
            }
            
            // SECURITY: Always send via email - never show links on page
            $mailHost = config('mail.mailers.smtp.host');
            $dbg(['h' => 'PR2', 'loc' => 'sending-email', 'msg' => 'sending password reset email', 'data' => ['mail_driver' => $mailDriver, 'mail_host' => $mailHost]]);
            
            // We will send the password reset link to this user via email ONLY
            // This ensures security and privacy - the link is never exposed on the page
            $status = Password::sendResetLink(
                $request->only('email')
            );
            
            $dbg(['h' => 'PR2', 'loc' => 'sendResetLink.result', 'msg' => 'password reset link sent', 'data' => ['status' => $status, 'email' => $request->email]]);

            if ($status == Password::RESET_LINK_SENT) {
                $mailDriver = config('mail.default');
                $mailHost = config('mail.mailers.smtp.host');
                $isMailpit = ($mailHost === '127.0.0.1' || $mailHost === 'localhost') && config('mail.mailers.smtp.port') == 1025;
                
                $dbg(['h' => 'PR3', 'loc' => 'email-sent', 'msg' => 'password reset email sent successfully', 'data' => [
                    'mail_driver' => $mailDriver, 
                    'mail_host' => $mailHost, 
                    'is_mailpit' => $isMailpit,
                    'email_sent_to' => $request->email
                ]]);
                
                // SECURITY: Never show the reset link - it's only in the email
                if ($isMailpit) {
                    $message = '✅ <strong>Password reset email sent!</strong>';
                    $message .= '<br><br>Check your email in Mailpit: <a href="http://127.0.0.1:8025" target="_blank" style="color: #007bff; font-weight: bold;">http://127.0.0.1:8025</a>';
                    $message .= '<br><small>The reset link has been sent securely to your email address.</small>';
                } else {
                    $message = '✅ <strong>Password reset email sent!</strong>';
                    $message .= '<br><br>Please check your email inbox (and spam folder) for the password reset link.';
                    $message .= '<br><small>For security, the reset link is only sent via email and never displayed on this page.</small>';
                }
                
                return back()->with('status', $message);
            }
            
            // Handle throttle error with better message
            if ($status == Password::RESET_THROTTLED) {
                $throttleSeconds = config('auth.passwords.users.throttle', 60);
                $throttleMinutes = ceil($throttleSeconds / 60);
                return back()->withInput($request->only('email'))
                    ->withErrors(['email' => "Please wait {$throttleMinutes} minute(s) before requesting another password reset link."]);
            }
            
            return back()->withInput($request->only('email'))
                    ->withErrors(['email' => __($status)]);
        } catch (\Symfony\Component\Mailer\Exception\TransportException $e) {
            // Mail server connection error - show user-friendly message
            $errorMsg = $e->getMessage();
            \Log::error('Password reset email failed: ' . $errorMsg);
            
            $dbg(['h' => 'PR4', 'loc' => 'transport-exception', 'msg' => 'email transport failed', 'data' => [
                'error' => $errorMsg,
                'mail_driver' => config('mail.default'),
                'mail_host' => config('mail.mailers.smtp.host'),
                'mail_username' => config('mail.mailers.smtp.username')
            ]]);
            
            // Check if it's a credentials/authentication issue
            if (strpos($errorMsg, 'authentication') !== false || strpos($errorMsg, 'login') !== false || strpos($errorMsg, '535') !== false) {
                $message = '❌ Email authentication failed! Please check your Gmail credentials in .env file.';
                $message .= '<br><br>Make sure:';
                $message .= '<br>1. MAIL_USERNAME is your Gmail address';
                $message .= '<br>2. MAIL_PASSWORD is a Gmail App Password (not your regular password)';
                $message .= '<br>3. You\'ve enabled 2-Step Verification';
                $message .= '<br><br>Get App Password: <a href="https://myaccount.google.com/apppasswords" target="_blank">https://myaccount.google.com/apppasswords</a>';
                
                return back()->withInput($request->only('email'))
                    ->withErrors(['email' => $message]);
            }
            
            // Check if it's a connection issue
            if (strpos($errorMsg, 'Connection') !== false || strpos($errorMsg, 'could not be established') !== false) {
                $message = '❌ Could not connect to email server. Please check your MAIL_HOST and MAIL_PORT settings in .env';
                $message .= '<br><br>For Gmail, use:';
                $message .= '<br>MAIL_HOST=smtp.gmail.com';
                $message .= '<br>MAIL_PORT=587';
                
                return back()->withInput($request->only('email'))
                    ->withErrors(['email' => $message]);
            }
            
            // Generic error
            $message = '❌ Failed to send email: ' . $errorMsg;
            $message .= '<br><br>Please check your email configuration in .env file.';
            
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => $message]);
        } catch (\Exception $e) {
            // Catch any other mail-related exceptions
            $errorMsg = $e->getMessage();
            \Log::error('Password reset error: ' . $errorMsg);
            
            $mailHost = config('mail.mailers.smtp.host'); // Define here to avoid undefined variable
            $dbg(['h' => 'PR5', 'loc' => 'general-exception', 'msg' => 'unexpected error', 'data' => ['error' => $errorMsg, 'mail_host' => $mailHost]]);
            
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'An error occurred: ' . $errorMsg]);
        }
    }
}