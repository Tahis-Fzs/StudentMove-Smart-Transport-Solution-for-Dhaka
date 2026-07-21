<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mailer\Exception\TransportException;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $request->user()->redirectAfterAuth();
        }

        try {
            $request->user()->sendEmailVerificationNotification();

            return redirect()->back()->with('status', 'verification-link-sent');
        } catch (TransportException $e) {
            $errorMsg = $e->getMessage();
            Log::error('Verification email resend failed: ' . $errorMsg);

            if (strpos($errorMsg, 'authentication') !== false || strpos($errorMsg, '535') !== false || strpos($errorMsg, 'BadCredentials') !== false) {
                return redirect()->back()->with('error', 'Failed to send verification email!<br><br><strong>Gmail Authentication Error:</strong> Your Gmail App Password may be incorrect or expired.<br><br>Please:<br>1. Go to <a href="https://myaccount.google.com/apppasswords" target="_blank">https://myaccount.google.com/apppasswords</a><br>2. Generate a new App Password<br>3. Run: <code>php artisan email:configure-gmail your-email@gmail.com new-app-password</code><br>4. Then try resending the verification email again.');
            }

            return redirect()->back()->with('error', 'Failed to send verification email: ' . $errorMsg);
        } catch (\Exception $e) {
            Log::error('Verification email resend failed: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Failed to send verification email. Please try again later.');
        }
    }
}
