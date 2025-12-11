<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class DiagnoseEmail extends Command
{
    protected $signature = 'email:diagnose';
    protected $description = 'Diagnose email configuration issues';

    public function handle()
    {
        // #region agent log
        $dbg = function($payload) {
            $line = json_encode([
                'sessionId' => 'debug-session',
                'runId' => 'email-diagnose',
                'hypothesisId' => $payload['h'] ?? 'ED1',
                'location' => $payload['loc'] ?? 'DiagnoseEmail',
                'message' => $payload['msg'] ?? '',
                'data' => $payload['data'] ?? [],
                'timestamp' => round(microtime(true) * 1000),
            ]);
            @file_put_contents(base_path('.cursor/debug.log'), $line . PHP_EOL, FILE_APPEND | LOCK_EX);
        };
        // #endregion

        $dbg(['h' => 'ED1', 'loc' => 'handle.start', 'msg' => 'diagnostic started']);

        $this->info('🔍 Email Configuration Diagnostics');
        $this->info('================================');
        $this->info('');

        // Check config
        $host = config('mail.mailers.smtp.host');
        $port = config('mail.mailers.smtp.port');
        $username = config('mail.mailers.smtp.username');
        $password = config('mail.mailers.smtp.password');
        $fromAddress = config('mail.from.address');
        $mailer = config('mail.default');

        $dbg(['h' => 'ED2', 'loc' => 'config-read', 'msg' => 'read mail configuration', 'data' => [
            'mailer' => $mailer,
            'host' => $host,
            'port' => $port,
            'username' => $username,
            'password_length' => strlen($password ?? ''),
            'password_first_char' => !empty($password) ? substr($password, 0, 1) : null,
            'password_last_char' => !empty($password) ? substr($password, -1) : null,
            'password_has_spaces' => !empty($password) && strpos($password, ' ') !== false,
            'password_has_quotes' => !empty($password) && (strpos($password, '"') !== false || strpos($password, "'") !== false),
            'from_address' => $fromAddress
        ]]);

        $this->info('Configuration:');
        $this->line("  Mailer: {$mailer}");
        $this->line("  Host: {$host}");
        $this->line("  Port: {$port}");
        $this->line("  Username: {$username}");
        $this->line("  Password: " . (empty($password) ? '❌ NOT SET' : '✅ Set (' . strlen($password) . ' chars)'));
        $this->line("  From Address: {$fromAddress}");
        $this->info('');

        // Check if password looks like App Password
        if (!empty($password)) {
            $hasSpaces = strpos($password, ' ') !== false;
            $length = strlen($password);
            
            if ($hasSpaces) {
                $this->warn('⚠️  WARNING: App Password contains spaces!');
                $this->warn('   Gmail App Passwords should NOT have spaces.');
                $this->warn('   Remove all spaces from your App Password.');
            }
            
            if ($length < 16) {
                $this->warn("⚠️  WARNING: Password is only {$length} characters.");
                $this->warn('   Gmail App Passwords are usually 16 characters.');
            } elseif ($length > 16) {
                $this->warn("⚠️  WARNING: Password is {$length} characters (expected 16).");
            }
        }

        // Test connection
        $this->info('Testing Gmail connection...');
        $dbg(['h' => 'ED3', 'loc' => 'before-mail-send', 'msg' => 'about to send test email', 'data' => [
            'username' => $username,
            'host' => $host,
            'port' => $port
        ]]);

        try {
            Mail::raw('Diagnostic test email', function ($message) use ($username, $dbg) {
                $dbg(['h' => 'ED4', 'loc' => 'mail-callback', 'msg' => 'inside mail callback', 'data' => ['to' => $username]]);
                $message->to($username)
                        ->subject('Email Diagnostic Test');
            });
            $dbg(['h' => 'ED5', 'loc' => 'mail-sent', 'msg' => 'email sent successfully']);
            $this->info('✅ Email sent successfully!');
            $this->info("   Check your inbox: {$username}");
            return 0;
        } catch (\Symfony\Component\Mailer\Exception\TransportException $e) {
            $error = $e->getMessage();
            $dbg(['h' => 'ED6', 'loc' => 'transport-exception', 'msg' => 'email transport failed', 'data' => [
                'error_message' => $error,
                'error_code' => $e->getCode(),
                'error_class' => get_class($e),
                'has_535' => strpos($error, '535') !== false,
                'has_bad_credentials' => strpos($error, 'BadCredentials') !== false,
                'has_authentication' => strpos($error, 'authentication') !== false
            ]]);
            $this->error('❌ Email sending failed!');
            $this->error('');
            $this->error('Error: ' . substr($error, 0, 200));
            $this->error('');

            if (strpos($error, '535') !== false || strpos($error, 'BadCredentials') !== false) {
                $this->warn('🔑 Gmail Authentication Failed!');
                $this->warn('');
                $this->warn('Possible causes:');
                $this->warn('1. App Password is incorrect or expired');
                $this->warn('2. App Password was generated for a different Gmail account');
                $this->warn('3. 2-Step Verification is not enabled');
                $this->warn('');
                $this->warn('Solution:');
                $this->warn('1. Go to: https://myaccount.google.com/apppasswords');
                $this->warn("2. Make sure you're logged in as: {$username}");
                $this->warn('3. Generate a NEW App Password for "Mail"');
                $this->warn('4. Copy it EXACTLY (remove spaces if any)');
                $this->warn("5. Run: php artisan email:configure-gmail {$username} NEW-APP-PASSWORD");
            }

            return 1;
        } catch (\Exception $e) {
            $dbg(['h' => 'ED7', 'loc' => 'general-exception', 'msg' => 'unexpected error', 'data' => [
                'error_message' => $e->getMessage(),
                'error_class' => get_class($e),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine()
            ]]);
            $this->error('❌ Unexpected error: ' . $e->getMessage());
            return 1;
        }
    }
}

