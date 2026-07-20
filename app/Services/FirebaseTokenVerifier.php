<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class FirebaseTokenVerifier
{
    /**
     * Verify a Firebase ID token via Identity Toolkit and return the account payload.
     *
     * @return array{uid:string,email:?string,email_verified:bool,name:?string,picture:?string,provider:string}
     */
    public function verify(string $idToken): array
    {
        $apiKey = config('services.firebase.api_key');

        if (empty($apiKey)) {
            throw ValidationException::withMessages([
                'firebase' => 'Firebase is not configured. Add FIREBASE_API_KEY to .env.',
            ]);
        }

        $response = Http::timeout(12)->post(
            'https://identitytoolkit.googleapis.com/v1/accounts:lookup?key=' . urlencode($apiKey),
            ['idToken' => $idToken]
        );

        if (! $response->successful()) {
            throw ValidationException::withMessages([
                'firebase' => 'Firebase token is invalid or expired. Try signing in again.',
            ]);
        }

        $user = $response->json('users.0');

        if (! is_array($user) || empty($user['localId'])) {
            throw ValidationException::withMessages([
                'firebase' => 'Could not read Firebase account details.',
            ]);
        }

        $provider = 'firebase';
        $providers = $user['providerUserInfo'] ?? [];
        if (is_array($providers) && isset($providers[0]['providerId'])) {
            $raw = (string) $providers[0]['providerId'];
            $provider = match (true) {
                str_contains($raw, 'google') => 'google',
                str_contains($raw, 'facebook') => 'facebook',
                str_contains($raw, 'github') => 'github',
                str_contains($raw, 'apple') => 'apple',
                str_contains($raw, 'password') => 'password',
                default => $raw,
            };
        }

        return [
            'uid' => (string) $user['localId'],
            'email' => isset($user['email']) ? strtolower(trim((string) $user['email'])) : null,
            'email_verified' => (bool) ($user['emailVerified'] ?? false),
            'name' => $user['displayName'] ?? ($providers[0]['displayName'] ?? null),
            'picture' => $user['photoUrl'] ?? ($providers[0]['photoUrl'] ?? null),
            'provider' => $provider,
        ];
    }
}
