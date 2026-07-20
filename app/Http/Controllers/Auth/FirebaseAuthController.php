<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Services\FirebaseTokenVerifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FirebaseAuthController extends Controller
{
    public function __construct(private FirebaseTokenVerifier $verifier)
    {
    }

    /**
     * Exchange a Firebase ID token for a local Laravel session.
     * Creates the user if needed, or links firebase_uid to an existing email.
     */
    public function sync(Request $request): JsonResponse
    {
        $request->validate([
            'id_token' => ['required', 'string'],
            'intent' => ['nullable', 'in:login,register'],
        ]);

        $firebase = $this->verifier->verify($request->input('id_token'));

        if (empty($firebase['email'])) {
            throw ValidationException::withMessages([
                'firebase' => 'Your social account has no email. Use an account that shares an email address.',
            ]);
        }

        if (! filter_var($firebase['email'], FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::withMessages([
                'firebase' => 'Firebase returned an invalid email address.',
            ]);
        }

        $user = DB::transaction(function () use ($firebase) {
            // 1) Already linked by Firebase UID
            $byUid = User::where('firebase_uid', $firebase['uid'])->first();
            if ($byUid) {
                return $this->refreshFromFirebase($byUid, $firebase);
            }

            // 2) Existing local account with same email → link (no duplicate signup)
            $byEmail = User::whereRaw('LOWER(email) = ?', [$firebase['email']])->first();
            if ($byEmail) {
                if ($byEmail->firebase_uid && $byEmail->firebase_uid !== $firebase['uid']) {
                    throw ValidationException::withMessages([
                        'firebase' => 'This email is already linked to another social account. Sign in with email/password or the original provider.',
                    ]);
                }

                $byEmail->firebase_uid = $firebase['uid'];
                $byEmail->auth_provider = $firebase['provider'];

                return $this->refreshFromFirebase($byEmail, $firebase);
            }

            // 3) Brand-new account synced into local DB
            $name = trim((string) ($firebase['name'] ?: 'Student'));
            $parts = preg_split('/\s+/', $name, 2) ?: ['Student'];
            $first = $parts[0] ?: 'Student';
            $last = $parts[1] ?? 'User';

            return User::create([
                'name' => $name,
                'first_name' => $first,
                'last_name' => $last,
                'email' => $firebase['email'],
                'firebase_uid' => $firebase['uid'],
                'auth_provider' => $firebase['provider'],
                'avatar_url' => $firebase['picture'],
                'password' => Hash::make(Str::random(48)),
                'email_verified_at' => $firebase['email_verified'] ? now() : null,
                // Social users fill these later in profile — unique nullable student_id/phone ok
                'university' => null,
                'student_id' => null,
                'phone' => null,
            ]);
        });

        Auth::login($user, true);
        $request->session()->regenerate();

        $redirect = $user->hasVerifiedEmail()
            ? url(RouteServiceProvider::HOME)
            : route('verification.notice');

        // If social email is verified by provider, mark verified locally
        if ($firebase['email_verified'] && ! $user->hasVerifiedEmail()) {
            $user->forceFill(['email_verified_at' => now()])->save();
            $redirect = url(RouteServiceProvider::HOME);
        }

        return response()->json([
            'ok' => true,
            'redirect' => $redirect,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'provider' => $user->auth_provider,
            ],
        ]);
    }

    private function refreshFromFirebase(User $user, array $firebase): User
    {
        $updates = [
            'firebase_uid' => $firebase['uid'],
            'auth_provider' => $firebase['provider'],
        ];

        if (! empty($firebase['picture'])) {
            $updates['avatar_url'] = $firebase['picture'];
        }

        if ($firebase['email_verified'] && ! $user->email_verified_at) {
            $updates['email_verified_at'] = now();
        }

        // Keep name if user already customized; fill if empty
        if (empty($user->first_name) && ! empty($firebase['name'])) {
            $parts = preg_split('/\s+/', trim($firebase['name']), 2) ?: [];
            $updates['name'] = $firebase['name'];
            $updates['first_name'] = $parts[0] ?? $user->first_name;
            $updates['last_name'] = $parts[1] ?? ($user->last_name ?: 'User');
        }

        $user->fill($updates)->save();

        return $user->fresh();
    }
}
