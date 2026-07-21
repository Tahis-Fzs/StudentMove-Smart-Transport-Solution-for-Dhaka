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
try {
            Mail::raw('Diagnostic test email', function ($message) use ($username) {
                $message->to($username)
                        ->subject('Email Diagnostic Test');
            });
            $this->info('✅ Email sent successfully!');
            $this->info("   Check your inbox: {$username}");
            return 0;
        } catch (\Symfony\Component\Mailer\Exception\TransportException $e) {
            $error = $e->getMessage();
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
$this->error('❌ Unexpected error: ' . $e->getMessage());
            return 1;
        }
    }
}

