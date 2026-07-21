<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class EmailSetupController extends Controller
{
    /**
     * Show email setup form
     */
    public function create(): View
    {
        return view('auth.email-setup');
    }

    /**
     * Save Gmail configuration automatically
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'gmail_email' => ['required', 'email'],
            'gmail_app_password' => ['required', 'string', 'min:16'],
        ]);
        
        // Remove spaces from App Password (Gmail App Passwords often have spaces when copied)
        $appPassword = str_replace(' ', '', $request->gmail_app_password);

        $envPath = base_path('.env');
        
        if (!File::exists($envPath)) {
            return back()->withErrors(['gmail_email' => '.env file not found. Please create it first.']);
        }

        // Read current .env
        $envContent = File::get($envPath);
        
        // Backup .env
        $backupPath = $envPath . '.backup.' . time();
        File::put($backupPath, $envContent);

        // Update mail configuration - use more robust replacement
        // IMPORTANT: Also set MAIL_MAILER to smtp (was missing!)
        $updates = [
            'MAIL_MAILER' => 'smtp',
            'MAIL_HOST' => 'smtp.gmail.com',
            'MAIL_PORT' => '587',
            'MAIL_USERNAME' => $request->gmail_email,
            'MAIL_PASSWORD' => $appPassword, // Use space-removed password
            'MAIL_ENCRYPTION' => 'tls',
            'MAIL_FROM_ADDRESS' => '"' . $request->gmail_email . '"',
            'MAIL_FROM_NAME' => '"StudentMove"',
        ];

        // Split .env into lines for easier manipulation
        $lines = explode("\n", $envContent);
        $updated = false;
        $newLines = [];
        
        foreach ($lines as $line) {
            $lineUpdated = false;
            foreach ($updates as $key => $value) {
                // Check if this line contains the key (with or without quotes, with or without spaces)
                if (preg_match("/^{$key}\s*=\s*/", $line)) {
                    $newLines[] = "{$key}={$value}";
                    $lineUpdated = true;
                    $updated = true;
                    break;
                }
            }
            if (!$lineUpdated) {
                $newLines[] = $line;
            }
        }
        
        // Add any missing keys
        foreach ($updates as $key => $value) {
            $found = false;
            foreach ($newLines as $line) {
                if (preg_match("/^{$key}\s*=/", $line)) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $newLines[] = "{$key}={$value}";
            }
        }

        // Rejoin lines
        $envContent = implode("\n", $newLines);

        // Save updated .env
        $writeResult = File::put($envPath, $envContent);
if (!$writeResult) {
            return back()->withErrors(['gmail_email' => 'Failed to save configuration. Please check file permissions.']);
        }

        // Clear ALL caches
        Artisan::call('config:clear');
        Artisan::call('cache:clear');

        // Verify by reading .env file directly (more reliable than config() which may be cached)
        $verifyContent = File::get($envPath);
        $verifyHost = null;
        $verifyUsername = null;
        $verifyPassword = null;
        
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
        }
        
        // Also check config() for comparison (may still show old values in same process)
        $configHost = config('mail.mailers.smtp.host');
        $configUsername = config('mail.mailers.smtp.username');
        $configPassword = config('mail.mailers.smtp.password');
// Verify .env file was written correctly (primary check - most reliable)
        $fileCorrect = ($verifyHost === 'smtp.gmail.com' && $verifyUsername === $request->gmail_email && !empty($verifyPassword));
        
        if (!$fileCorrect) {
return back()->withErrors(['gmail_email' => 'Configuration was saved but could not be verified. Please try again or manually update .env file.']);
        }

        return redirect()->route('register')
            ->with('success', '✅ Gmail configured successfully! You can now register and emails will be sent to your Gmail inbox: <strong>' . $request->gmail_email . '</strong>');
    }
}
