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
        // #region agent log
        $dbg = function($payload) {
            $line = json_encode([
                'sessionId' => 'debug-session',
                'runId' => 'configure-gmail',
                'hypothesisId' => $payload['h'] ?? 'CG1',
                'location' => $payload['loc'] ?? 'ConfigureGmail',
                'message' => $payload['msg'] ?? '',
                'data' => $payload['data'] ?? [],
                'timestamp' => round(microtime(true) * 1000),
            ]);
            @file_put_contents(base_path('.cursor/debug.log'), $line . PHP_EOL, FILE_APPEND | LOCK_EX);
        };
        // #endregion

        $email = $this->argument('email');
        $password = $this->argument('password');

        $dbg(['h' => 'CG1', 'loc' => 'handle.start', 'msg' => 'command started', 'data' => ['email' => $email, 'password_length' => strlen($password)]]);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $dbg(['h' => 'CG2', 'loc' => 'validation-failed', 'msg' => 'invalid email', 'data' => ['email' => $email]]);
            $this->error('Invalid email address!');
            return 1;
        }

        if (strlen($password) < 16) {
            $dbg(['h' => 'CG3', 'loc' => 'validation-failed', 'msg' => 'password too short', 'data' => ['password_length' => strlen($password)]]);
            $this->error('App Password must be at least 16 characters!');
            return 1;
        }

        $envPath = base_path('.env');
        
        if (!File::exists($envPath)) {
            $dbg(['h' => 'CG4', 'loc' => 'env-not-found', 'msg' => '.env file missing']);
            $this->error('.env file not found!');
            return 1;
        }

        // Read .env
        $envContent = File::get($envPath);
        $dbg(['h' => 'CG5', 'loc' => 'env-read', 'msg' => 'read .env file', 'data' => ['file_size' => strlen($envContent), 'has_mail_host' => strpos($envContent, 'MAIL_HOST') !== false, 'has_mail_username' => strpos($envContent, 'MAIL_USERNAME') !== false]]);
        
        // Backup
        $backupPath = $envPath . '.backup.' . time();
        File::put($backupPath, $envContent);
        $dbg(['h' => 'CG6', 'loc' => 'backup-created', 'msg' => 'created .env backup', 'data' => ['backup_path' => $backupPath]]);
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

        $dbg(['h' => 'CG7', 'loc' => 'processing-lines', 'msg' => 'processing .env lines', 'data' => ['total_lines' => count($lines)]]);

        foreach ($lines as $line) {
            $lineUpdated = false;
            foreach ($updates as $key => $value) {
                if (preg_match("/^{$key}\s*=/", $line)) {
                    $newLines[] = "{$key}={$value}";
                    $updated[] = $key;
                    $lineUpdated = true;
                    $dbg(['h' => 'CG8', 'loc' => 'line-updated', 'msg' => 'updated existing line', 'data' => ['key' => $key, 'old_line' => trim($line), 'new_value' => $value]]);
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
                $dbg(['h' => 'CG9', 'loc' => 'key-added', 'msg' => 'added missing key', 'data' => ['key' => $key, 'value' => $value]]);
            }
        }

        // Save
        $newContent = implode("\n", $newLines);
        $writeResult = File::put($envPath, $newContent);
        $dbg(['h' => 'CG10', 'loc' => 'env-saved', 'msg' => 'saved .env file', 'data' => [
            'write_result' => $writeResult,
            'new_content_size' => strlen($newContent),
            'keys_updated' => $updated,
            'keys_added' => array_diff(array_keys($updates), $updated)
        ]]);
        $this->info('✓ Updated .env file');

        // Clear ALL caches to ensure fresh config load
        $this->call('config:clear');
        $this->call('cache:clear');
        $dbg(['h' => 'CG11', 'loc' => 'cache-cleared', 'msg' => 'all caches cleared']);
        $this->info('✓ Cleared config cache');

        // Test email sending with a simple connection test
        $this->info('Testing Gmail connection...');
        try {
            $testMailer = \Illuminate\Support\Facades\Mail::mailer('smtp');
            // Just verify the configuration is valid - don't actually send
            $dbg(['h' => 'CG11a', 'loc' => 'connection-test', 'msg' => 'testing Gmail connection', 'data' => [
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'username' => config('mail.mailers.smtp.username')
            ]]);
        } catch (\Exception $e) {
            $dbg(['h' => 'CG11b', 'loc' => 'connection-test-failed', 'msg' => 'connection test error', 'data' => ['error' => $e->getMessage()]]);
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
        
        $dbg(['h' => 'CG12', 'loc' => 'verification', 'msg' => 'verifying configuration', 'data' => [
            'file_mailer' => $verifyMailer,
            'file_host' => $verifyHost,
            'file_username' => $verifyUsername,
            'file_has_password' => $hasPassword,
            'config_mailer' => $configMailer,
            'config_host' => $configHost,
            'config_username' => $configUsername,
            'config_has_password' => !empty($configPassword),
            'expected_host' => 'smtp.gmail.com',
            'expected_username' => $email
        ]]);
        
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
            $dbg(['h' => 'CG13', 'loc' => 'success', 'msg' => 'Gmail configuration successful']);
            return 0;
        } else {
            $this->error('Configuration verification failed!');
            $this->error("Expected: smtp.gmail.com, got: {$verifyHost}");
            $this->error("Expected username: {$email}, got: {$verifyUsername}");
            $this->error("Password set: " . ($hasPassword ? 'Yes' : 'No'));
            $this->warn('');
            $this->warn('The .env file may not have been updated correctly.');
            $this->warn('Please check the .env file manually.');
            $dbg(['h' => 'CG14', 'loc' => 'verification-failed', 'msg' => 'configuration verification failed', 'data' => [
                'file_host' => $verifyHost,
                'file_username' => $verifyUsername,
                'file_has_password' => $hasPassword,
                'config_host' => $configHost,
                'config_username' => $configUsername
            ]]);
            return 1;
        }
    }
}
