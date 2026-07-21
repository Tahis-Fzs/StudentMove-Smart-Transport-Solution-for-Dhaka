<?php

namespace App\Helpers;

class EmailHelper
{
    /**
     * Automatically ensure email is configured and Mailpit is running
     * Returns true if email is ready, false otherwise
     */
    public static function ensureEmailConfigured(): array
    {        
        $mailDriver = config('mail.default');
        $mailHost = config('mail.mailers.smtp.host');
        $mailPort = config('mail.mailers.smtp.port');
        $mailUsername = config('mail.mailers.smtp.username');
        $mailPassword = config('mail.mailers.smtp.password');
        $isMailpit = ($mailHost === '127.0.0.1' || $mailHost === 'localhost') && $mailPort == 1025;
// Log / array drivers need no SMTP — emails are written to laravel.log or held in memory (tests).
        if (in_array($mailDriver, ['log', 'array'], true)) {

            return [
                'ready' => true,
                'type' => $mailDriver,
                'message' => $mailDriver === 'log'
                    ? 'Mail driver is log (see storage/logs/laravel.log for messages).'
                    : 'Mail driver is array (in-memory; for testing).',
            ];
        }

            // If using Mailpit, ensure it's running
        if ($isMailpit && empty($mailUsername) && empty($mailPassword)) {
            
            // Check if Mailpit is running
            $mailpitRunning = @fsockopen('127.0.0.1', 1025, $errno, $errstr, 1);
            if ($mailpitRunning) {
                @fclose($mailpitRunning);
                return ['ready' => true, 'type' => 'mailpit', 'message' => 'Mailpit is running'];
            }
            
            
            // Try to auto-start Mailpit
            $mailpitPaths = [
                '/opt/homebrew/opt/mailpit/bin/mailpit', // Standard Homebrew installation path
                '/opt/homebrew/bin/mailpit', // If symlinked to bin
                '/usr/local/bin/mailpit', // Intel Mac Homebrew
                '/usr/local/opt/mailpit/bin/mailpit', // Intel Mac Homebrew opt path
                str_replace('~', $_SERVER['HOME'] ?? getenv('HOME'), '~/mailpit'),
                str_replace('~', $_SERVER['HOME'] ?? getenv('HOME'), '~/.local/bin/mailpit'),
                'mailpit' // In PATH
            ];
            
            $mailpitFound = false;
            $foundPath = null;
            
            foreach ($mailpitPaths as $path) {
                $exists = file_exists($path);
                $executable = $exists ? is_executable($path) : false;
                
                // Verify it's actually a valid executable by trying to get version
                $isValid = false;
                if ($exists && $executable) {
                    $testOutput = [];
                    $testReturn = 0;
                    @exec(escapeshellarg($path) . ' --version 2>&1', $testOutput, $testReturn);
                    // If it returns 0 or has valid output, it's likely a real executable
                    $isValid = ($testReturn === 0 || (count($testOutput) > 0 && !preg_match('/command not found|No such file/i', implode(' ', $testOutput))));
                }
if ($exists && $executable && $isValid) {
                    $mailpitFound = true;
                    $foundPath = $path;
                    break;
                }
            }
            
            if (!$mailpitFound) {
            }
            
            // If Mailpit not found, try to auto-install it
            if (!$mailpitFound) {
                
                // Check if brew is available
                $brewPaths = ['/usr/local/bin/brew', '/opt/homebrew/bin/brew', 'brew'];
                $brewFound = false;
                $brewPath = null;
                
                foreach ($brewPaths as $bp) {
                    $exists = file_exists($bp);
                    $executable = $exists ? is_executable($bp) : false;
                    
                    if ($exists && $executable) {
                        $brewFound = true;
                        $brewPath = $bp;
                        break;
                    }
                }
                
                if ($brewFound) {
                    // Note: This might take a while and might require user interaction
                    // For now, we'll skip auto-install during web requests as it's too slow
                    // Instead, we'll provide clear instructions
                } else {
                }
            }
            
            // If Mailpit is found (either already installed or just installed), start it
            if ($mailpitFound && $foundPath) {
                
                // Start Mailpit in background using nohup to ensure it stays running
                // Use nohup and redirect output to ensure process detaches properly
                $command = 'nohup ' . escapeshellarg($foundPath) . ' > /dev/null 2>&1 & echo $!';
                $output = [];
                $returnVar = 0;
                $pid = exec($command, $output, $returnVar);
// Wait and check multiple times (Mailpit can take a few seconds to start)
                $maxAttempts = 6;
                $attemptDelay = 1; // seconds
                
                for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
                    sleep($attemptDelay);
                    $check = @fsockopen('127.0.0.1', 1025, $errno, $errstr, 1);
                    if ($check) {
                        @fclose($check);
                        return ['ready' => true, 'type' => 'mailpit', 'message' => 'Mailpit auto-started successfully'];
                    }
                }
                
                // If still not running, check if process is actually running
                if ($pid) {
                    $processCheck = exec("ps -p $pid 2>&1", $psOutput, $psReturn);
                }
            }
            
            // If we get here, Mailpit couldn't be started
            $installInstructions = 'Please install Mailpit: <code>brew install axllent/mailpit/mailpit</code><br>Then start it: <code>mailpit</code>';
            if (!$mailpitFound) {
                $installInstructions = 'Mailpit is not installed. ' . $installInstructions;
            } else {
                $installInstructions = 'Mailpit was found but could not be started. ' . $installInstructions;
            }
            
            return [
                'ready' => false, 
                'type' => 'mailpit', 
                'message' => $installInstructions
            ];
        }
        
        // Check if real SMTP is configured (Gmail or other SMTP)
        if ($mailDriver === 'smtp' && !$isMailpit) {
            $isGmail = strpos($mailHost, 'gmail.com') !== false;
            
            if (empty($mailUsername) || $mailUsername === 'null' || empty($mailPassword) || $mailPassword === 'null') {
                
                if ($isGmail) {
                    return [
                        'ready' => false,
                        'type' => 'gmail',
                        'message' => 'Gmail SMTP not configured. Please set MAIL_USERNAME and MAIL_PASSWORD in .env. Get App Password: https://myaccount.google.com/apppasswords'
                    ];
                }
                
                return [
                    'ready' => false,
                    'type' => 'smtp',
                    'message' => 'SMTP credentials not configured. Please set MAIL_USERNAME and MAIL_PASSWORD in .env'
                ];
            }
            
            return ['ready' => true, 'type' => $isGmail ? 'gmail' : 'smtp', 'message' => $isGmail ? 'Gmail SMTP configured' : 'SMTP configured'];
        }
        
        return ['ready' => false, 'type' => 'unknown', 'message' => 'Email not configured'];
    }
}

