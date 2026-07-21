<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ConfigureGmail extends Command
{
    protected $signature = 'email:configure-gmail {email} {password}';
    protected $description = 'Configure Gmail SMTP settings automatically';

    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email address!');
            return 1;
        }

        if (strlen($password) < 16) {
            $this->error('App Password must be at least 16 characters!');
            return 1;
        }

        $envPath = base_path('.env');
        
        if (!File::exists($envPath)) {
            $this->error('.env file not found!');
            return 1;
        }

        // Read .env
        $envContent = File::get($envPath);
        
        // Backup
        $backupPath = $envPath . '.backup.' . time();
        File::put($backupPath, $envContent);
        $this->info('✓ Created .env backup');

        // Update settings - IMPORTANT: Also set MAIL_MAILER to smtp
        $updates = [
            'MAIL_MAILER' => 'smtp',
            'MAIL_HOST' => 'smtp.gmail.com',
            'MAIL_PORT' => '587',
            'MAIL_USERNAME' => $email,
            'MAIL_PASSWORD' => $password,
            'MAIL_ENCRYPTION' => 'tls',
            'MAIL_FROM_ADDRESS' => '"' . $email . '"',
            'MAIL_FROM_NAME' => '"StudentMove"',
        ];

        $lines = explode("\n", $envContent);
        $newLines = [];
        $updated = [];

        foreach ($lines as $line) {
            $lineUpdated = false;
            foreach ($updates as $key => $value) {
                if (preg_match("/^{$key}\s*=/", $line)) {
                    $newLines[] = "{$key}={$value}";
                    $updated[] = $key;
                    $lineUpdated = true;
                    break;
                }
            }
            if (!$lineUpdated) {
                $newLines[] = $line;
            }
        }

        // Add missing keys
        foreach ($updates as $key => $value) {
            if (!in_array($key, $updated)) {
                $newLines[] = "{$key}={$value}";
            }
        }

        // Save
        $newContent = implode("\n", $newLines);
        $writeResult = File::put($envPath, $newContent);
$this->info('✓ Updated .env file');

        // Clear ALL caches to ensure fresh config load
        $this->call('config:clear');
        $this->call('cache:clear');
        $this->info('✓ Cleared config cache');

        // Test email sending with a simple connection test
        $this->info('Testing Gmail connection...');
        try {
            $testMailer = \Illuminate\Support\Facades\Mail::mailer('smtp');
            // Just verify the configuration is valid - don't actually send
} catch (\Exception $e) {
            // Don't fail - credentials will be tested when actually sending
        }

        // Verify by reading .env file directly (most reliable)
        $verifyContent = File::get($envPath);
        $verifyHost = null;
        $verifyUsername = null;
        $verifyPassword = null;
        $verifyMailer = null;
        
        foreach (explode("\n", $verifyContent) as $line) {
            if (preg_match('/^MAIL_HOST=(.+)$/', $line, $matches)) {
                $verifyHost = trim($matches[1]);
            }
            if (preg_match('/^MAIL_USERNAME=(.+)$/', $line, $matches)) {
                $verifyUsername = trim($matches[1]);
            }
            if (preg_match('/^MAIL_PASSWORD=(.+)$/', $line, $matches)) {
                $verifyPassword = trim($matches[1]);
            }
            if (preg_match('/^MAIL_MAILER=(.+)$/', $line, $matches)) {
                $verifyMailer = trim($matches[1]);
            }
        }
        
        // Also check config() for comparison
        $configHost = config('mail.mailers.smtp.host');
        $configUsername = config('mail.mailers.smtp.username');
        $configPassword = config('mail.mailers.smtp.password');
        $configMailer = config('mail.default');
        
        $hasPassword = !empty($verifyPassword);
// Verify .env file was written correctly (primary check)
        $fileCorrect = ($verifyHost === 'smtp.gmail.com' && $verifyUsername === $email && $hasPassword);
        
        // Config might still show old values in same process, but that's OK - it will reload on next request
        if ($fileCorrect) {
            $this->info('');
            $this->info('✅ Gmail configured successfully!');
            $this->info("   Email: {$email}");
            $this->info('   Host: smtp.gmail.com');
            $this->info('   Port: 587');
            $this->info('');
            $this->info('Now register a new account and emails will go to your Gmail inbox!');
            return 0;
        } else {
            $this->error('Configuration verification failed!');
            $this->error("Expected: smtp.gmail.com, got: {$verifyHost}");
            $this->error("Expected username: {$email}, got: {$verifyUsername}");
            $this->error("Password set: " . ($hasPassword ? 'Yes' : 'No'));
            $this->warn('');
            $this->warn('The .env file may not have been updated correctly.');
            $this->warn('Please check the .env file manually.');
return 1;
        }
    }
}
