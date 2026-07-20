<?php

namespace App\Console\Commands;

use App\Services\SslCommerzService;
use Illuminate\Console\Command;

class CheckSslCommerzCommand extends Command
{
    protected $signature = 'sslcommerz:check {--probe : Start a sandbox session to verify store credentials}';

    protected $description = 'Verify SSLCommerz configuration and optional gateway connectivity';

    public function handle(SslCommerzService $ssl): int
    {
        $summary = $ssl->configSummary();

        $this->info('SSLCommerz configuration');
        $this->line('────────────────────────');

        if (!$summary['configured']) {
            $this->warn('Not configured — subscription checkout uses demo mode.');
            $this->line('');
            $this->line('Add to .env (sandbox defaults for local testing):');
            $this->line('  SSLCOMMERZ_STORE_ID=testbox');
            $this->line('  SSLCOMMERZ_STORE_PASSWORD=qwerty');
            $this->line('  SSLCOMMERZ_SANDBOX=true');
            $this->line('');
            $this->line('Register: https://developer.sslcommerz.com/registration/');

            return self::FAILURE;
        }

        $mode = $summary['sandbox'] ? 'sandbox' : 'LIVE production';
        $this->line('Store ID:     ' . ($summary['store_id_hint'] ?? '—'));
        $this->line('Mode:         ' . $mode);
        $this->line('Currency:     ' . $summary['currency']);
        $this->line('Init URL:     ' . $summary['init_url']);
        $this->line('APP_URL:      ' . config('app.url'));
        $this->line('Success URL:  ' . route('subscription.ssl.success'));
        $this->line('IPN URL:      ' . route('subscription.ssl.ipn'));

        if (!$summary['sandbox']) {
            $this->newLine();
            $this->warn('Production mode — real charges apply. Ensure SSLCOMMERZ_SANDBOX=false only on HTTPS.');
        }

        $appUrl = (string) config('app.url');
        if (!str_starts_with($appUrl, 'https://') && !$summary['sandbox']) {
            $this->newLine();
            $this->error('APP_URL must be HTTPS in production so SSLCommerz callbacks work.');
        }

        if (!$this->option('probe')) {
            $this->newLine();
            $this->comment('Run with --probe to verify credentials against the gateway.');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->info('Probing gateway (no charge)...');

        $probe = $ssl->probe();
        if ($probe['ok']) {
            $this->info('Gateway OK — session created successfully.');
            if (!empty($probe['gateway_url'])) {
                $this->line('Sample checkout URL: ' . $probe['gateway_url']);
            }

            return self::SUCCESS;
        }

        $this->error('Probe failed: ' . ($probe['error'] ?? 'unknown error'));

        return self::FAILURE;
    }
}
