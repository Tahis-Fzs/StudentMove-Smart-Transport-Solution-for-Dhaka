<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class RealEmailDomain implements Rule
{
    private string $error = 'Enter a valid email address.';

    /** Common disposable / throwaway email domains. */
    private const DISPOSABLE = [
        'mailinator.com', 'guerrillamail.com', 'guerrillamail.net', '10minutemail.com',
        'tempmail.com', 'temp-mail.org', 'throwaway.email', 'yopmail.com', 'sharklasers.com',
        'trashmail.com', 'fakeinbox.com', 'getnada.com', 'maildrop.cc', 'discard.email',
        'mailnesia.com', 'tempail.com', 'moakt.com', 'emailondeck.com',
    ];

    public function passes($attribute, $value): bool
    {
        $email = strtolower(trim((string) $value));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error = 'Enter a valid email address.';
            return false;
        }

        $domain = substr(strrchr($email, '@') ?: '', 1);
        if ($domain === '' || ! str_contains($domain, '.')) {
            $this->error = 'Enter a valid email domain.';
            return false;
        }

        if (in_array($domain, self::DISPOSABLE, true)) {
            $this->error = 'Disposable email addresses are not allowed. Use your university or personal email.';
            return false;
        }

        $hasMx = @checkdnsrr($domain, 'MX');
        $hasA = @checkdnsrr($domain, 'A') || @checkdnsrr($domain, 'AAAA');

        if (! $hasMx && ! $hasA) {
            $this->error = 'This email domain does not appear to exist. Check for typos (e.g. gmail.com).';
            return false;
        }

        return true;
    }

    public function message(): string
    {
        return $this->error;
    }
}
