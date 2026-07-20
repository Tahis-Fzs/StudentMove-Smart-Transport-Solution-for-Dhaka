<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustHosts as Middleware;

class TrustHosts extends Middleware
{
    /**
     * Always apply host checks (including local tunnel previews).
     * Laravel's default skips this in the "local" environment.
     */
    protected function shouldSpecifyTrustedHosts()
    {
        return ! $this->app->runningUnitTests();
    }

    /**
     * Trusted host regex patterns (Symfony expects regex, not glob wildcards).
     *
     * @return array<int, string|null>
     */
    public function hosts(): array
    {
        $extra = [];
        foreach (explode(',', (string) env('ALLOWED_HOSTS', '')) as $host) {
            $host = strtolower(trim($host));
            if ($host === '') {
                continue;
            }
            $extra[] = '^' . preg_quote($host, '/') . '$';
        }

        return array_values(array_filter([
            $this->allSubdomainsOfApplicationUrl(),
            '^localhost$',
            '^127\.0\.0\.1$',
            // Suffix-anchored — blocks evil.trycloudflare.com.attacker.test
            '^(?:.+\.)?trycloudflare\.com$',
            '^(?:.+\.)?onrender\.com$',
            ...$extra,
        ]));
    }
}
