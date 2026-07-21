<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\EmailHelper;
use App\Http\Controllers\Controller;
use App\Models\EmailVerificationCode;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        return view('auth.verify-email-code');
    }

    /**
     * Send a new 6-digit verification code
     */
    public function send(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return $user->redirectAfterAuth();
        }

        $emailStatus = EmailHelper::ensureEmailConfigured();

        if (!$emailStatus['ready']) {
            $errorMsg = '❌ ' . $emailStatus['message'];
            if ($emailStatus['type'] === 'mailpit') {
                $errorMsg .= '<br><br>Please install Mailpit: <code>brew install axllent/mailpit/mailpit</code><br>Then start it: <code>mailpit</code><br><br>View emails at: <a href="http://127.0.0.1:8025" target="_blank">http://127.0.0.1:8025</a>';
            } else if ($emailStatus['type'] === 'gmail' || $emailStatus['type'] === 'smtp') {
                $errorMsg .= '<br><br>For Gmail:<br><code>MAIL_MAILER=smtp</code><br><code>MAIL_HOST=smtp.gmail.com</code><br><code>MAIL_USERNAME=your-email@gmail.com</code><br><code>MAIL_PASSWORD=your-app-password</code><br><br>Get Gmail App Password: <a href="https://myaccount.google.com/apppasswords" target="_blank">https://myaccount.google.com/apppasswords</a>';
            }

            return redirect()->back()->with('error', $errorMsg);
        }

        EmailVerificationCode::where('user_id', $user->id)
            ->where('used', false)
            ->update(['used' => true]);

        $code = EmailVerificationCode::generateCode();

        EmailVerificationCode::create([
            'user_id' => $user->id,
            'code' => $code,
            'email' => $user->email,
            'expires_at' => now()->addMinutes(15),
            'used' => false,
        ]);

        try {
            Mail::send('emails.verification-code', [
                'code' => $code,
                'user' => $user,
            ], function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('StudentMove - Email Verification Code');
            });

            return redirect()->back()->with('status', 'verification-code-sent');
        } catch (TransportException $e) {
            $errorMsg = $e->getMessage();
            Log::error('Verification code email failed: ' . $errorMsg);

            if (strpos($errorMsg, 'authentication') !== false || strpos($errorMsg, '535') !== false) {
                return redirect()->back()->with('error', 'Failed to send verification code!<br><br><strong>Gmail Authentication Error:</strong> Please check your email configuration.');
            }

            return redirect()->back()->with('error', 'Failed to send verification code. Please try again later.');
        } catch (\Exception $e) {
            Log::error('Verification code email error: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Failed to send verification code. Please try again later.');
        }
    }

    /**
     * Verify the 6-digit code
     */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6', 'regex:/^[0-9]{6}$/'],
        ]);

        $user = $request->user();
        $code = $request->input('code');

        if ($user->hasVerifiedEmail()) {
            return $user->redirectAfterAuth(['verified' => 1]);
        }

        $verificationCode = EmailVerificationCode::where('user_id', $user->id)
            ->where('code', $code)
            ->where('email', $user->email)
            ->valid()
            ->latest()
            ->first();

        if (!$verificationCode) {
            return redirect()->back()->withErrors(['code' => 'Invalid or expired verification code. Please request a new code.']);
        }

        $verificationCode->markAsUsed();

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return $user->fresh()->redirectAfterAuth(['verified' => 1]);
    }
}
