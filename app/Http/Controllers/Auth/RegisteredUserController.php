<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\EmailHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\EmailVerificationCode;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $emailStatus = EmailHelper::ensureEmailConfigured();

        if (! $emailStatus['ready']) {
            if (in_array($emailStatus['type'], ['gmail', 'mailpit'], true)) {
                return redirect()->route('email-setup')
                    ->with('info', 'Configure email delivery first so we can verify your address.');
            }

            return back()
                ->withInput()
                ->withErrors([
                    'email' => 'Email delivery is not configured. Verification is required to create an account.',
                ]);
        }

        $user = DB::transaction(function () use ($request) {
            if (User::where('email', $request->email)->exists()) {
                throw ValidationException::withMessages([
                    'email' => 'This email is already registered. Sign in instead.',
                ]);
            }
            if (User::where('student_id', $request->student_id)->exists()) {
                throw ValidationException::withMessages([
                    'student_id' => 'This student ID is already registered. One ID = one account.',
                ]);
            }
            if (User::where('phone', $request->phone)->exists()) {
                throw ValidationException::withMessages([
                    'phone' => 'This phone number is already linked to another account.',
                ]);
            }

            return User::create([
                'name' => $request->first_name . ' ' . $request->last_name,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'university' => $request->university,
                'student_id' => $request->student_id,
                'password' => Hash::make($request->password),
            ]);
        });

        $this->sendVerificationCode($user);

        Auth::login($user);
        $request->session()->regenerate();

        $success = match ($emailStatus['type'] ?? '') {
            'log' => 'Account created. Open storage/logs/laravel.log for your 6-digit verification code (local log mailer).',
            'mailpit' => 'Account created. Check Mailpit for your verification code.',
            'gmail', 'smtp' => 'Account created. We sent a 6-digit verification code to ' . $user->email . '.',
            default => 'Account created. Check your email for a 6-digit verification code.',
        };

        return redirect()
            ->route('verification.notice')
            ->with('success', $success);
    }

    private function sendVerificationCode(User $user): void
    {
        try {
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

            Mail::send('emails.verification-code', [
                'code' => $code,
                'user' => $user,
            ], function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('StudentMove — Email verification code');
            });
        } catch (\Throwable $e) {
            Log::error('Registration verification email failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
