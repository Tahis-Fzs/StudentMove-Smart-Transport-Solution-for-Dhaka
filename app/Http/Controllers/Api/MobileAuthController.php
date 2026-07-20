<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BusSchedule;
use App\Models\User;
use App\Services\FirebaseTokenVerifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Mobile / Flutter bridge — same Firebase project, Laravel MySQL/SQLite as source of truth for web+API.
 */
class MobileAuthController extends Controller
{
    public function __construct(private FirebaseTokenVerifier $verifier)
    {
    }

    public function firebase(Request $request): JsonResponse
    {
        $request->validate(['id_token' => ['required', 'string']]);

        $firebase = $this->verifier->verify($request->input('id_token'));

        if (empty($firebase['email'])) {
            throw ValidationException::withMessages([
                'firebase' => 'Social account must include an email.',
            ]);
        }

        $user = DB::transaction(function () use ($firebase) {
            $byUid = User::where('firebase_uid', $firebase['uid'])->first();
            if ($byUid) {
                return $this->touch($byUid, $firebase);
            }

            $byEmail = User::whereRaw('LOWER(email) = ?', [$firebase['email']])->first();
            if ($byEmail) {
                $byEmail->firebase_uid = $firebase['uid'];
                $byEmail->auth_provider = $firebase['provider'];
                return $this->touch($byEmail, $firebase);
            }

            $name = trim((string) ($firebase['name'] ?: 'Student'));
            $parts = preg_split('/\s+/', $name, 2) ?: ['Student'];

            return User::create([
                'name' => $name,
                'first_name' => $parts[0] ?: 'Student',
                'last_name' => $parts[1] ?? 'User',
                'email' => $firebase['email'],
                'firebase_uid' => $firebase['uid'],
                'auth_provider' => $firebase['provider'],
                'avatar_url' => $firebase['picture'],
                'password' => Hash::make(Str::random(48)),
                'email_verified_at' => $firebase['email_verified'] ? now() : now(),
            ]);
        });

        $user->tokens()->where('name', 'mobile')->delete();
        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'ok' => true,
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'firebase_uid' => $user->firebase_uid,
                'provider' => $user->auth_provider,
                'avatar_url' => $user->avatar_url,
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['user' => $request->user()]);
    }

    private function touch(User $user, array $firebase): User
    {
        $user->fill([
            'firebase_uid' => $firebase['uid'],
            'auth_provider' => $firebase['provider'],
            'avatar_url' => $firebase['picture'] ?: $user->avatar_url,
            'email_verified_at' => $user->email_verified_at ?: now(),
        ])->save();

        return $user->fresh();
    }
}
