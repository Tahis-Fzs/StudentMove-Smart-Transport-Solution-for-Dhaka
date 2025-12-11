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
        // #region agent log
        $dbg = function($payload) {
            $line = json_encode([
                'sessionId' => 'debug-session',
                'runId' => 'email-helper',
                'hypothesisId' => $payload['h'] ?? 'EH1',
                'location' => $payload['loc'] ?? 'EmailHelper',
                'message' => $payload['msg'] ?? '',
                'data' => $payload['data'] ?? [],
                'timestamp' => round(microtime(true) * 1000),
            ]);
            @file_put_contents(base_path('.cursor/debug.log'), $line . PHP_EOL, FILE_APPEND | LOCK_EX);
        };
        // #endregion
        
        $mailDriver = config('mail.default');
        $mailHost = config('mail.mailers.smtp.host');
        $mailPort = config('mail.mailers.smtp.port');
        $mailUsername = config('mail.mailers.smtp.username');
        $mailPassword = config('mail.mailers.smtp.password');
        $isMailpit = ($mailHost === '127.0.0.1' || $mailHost === 'localhost') && $mailPort == 1025;
        
        $dbg(['h' => 'EH1', 'loc' => 'ensureEmailConfigured.start', 'msg' => 'checking email config', 'data' => [
            'mail_driver' => $mailDriver,
            'mail_host' => $mailHost,
            'mail_port' => $mailPort,
            'is_mailpit' => $isMailpit,
            'has_username' => !empty($mailUsername),
            'has_password' => !empty($mailPassword)
        ]]);
        
            // If using Mailpit, ensure it's running
        if ($isMailpit && empty($mailUsername) && empty($mailPassword)) {
            $dbg(['h' => 'EH2', 'loc' => 'mailpit-mode', 'msg' => 'using Mailpit mode']);
            
            // Check if Mailpit is running
            $mailpitRunning = @fsockopen('127.0.0.1', 1025, $errno, $errstr, 1);
            if ($mailpitRunning) {
                @fclose($mailpitRunning);
                $dbg(['h' => 'EH3', 'loc' => 'mailpit-running', 'msg' => 'Mailpit already running']);
                return ['ready' => true, 'type' => 'mailpit', 'message' => 'Mailpit is running'];
            }
            
            $dbg(['h' => 'EH4', 'loc' => 'mailpit-not-running', 'msg' => 'Mailpit not running, checking installation']);
            
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
                
                $dbg(['h' => 'EH5', 'loc' => 'check-path', 'msg' => 'checking Mailpit path', 'data' => [
                    'path' => $path, 
                    'exists' => $exists, 
                    'executable' => $executable,
                    'is_valid' => $isValid,
                    'test_output' => $testOutput ?? []
                ]]);
                
                if ($exists && $executable && $isValid) {
                    $mailpitFound = true;
                    $foundPath = $path;
                    $dbg(['h' => 'EH6', 'loc' => 'mailpit-found', 'msg' => 'Mailpit found and validated', 'data' => ['path' => $path]]);
                    break;
                }
            }
            
            if (!$mailpitFound) {
                $dbg(['h' => 'EH7', 'loc' => 'mailpit-not-found', 'msg' => 'Mailpit not found in any path', 'data' => ['checked_paths' => $mailpitPaths]]);
            }
            
            // If Mailpit not found, try to auto-install it
            if (!$mailpitFound) {
                $dbg(['h' => 'EH8', 'loc' => 'try-auto-install', 'msg' => 'attempting auto-install']);
                
                // Check if brew is available
                $brewPaths = ['/usr/local/bin/brew', '/opt/homebrew/bin/brew', 'brew'];
                $brewFound = false;
                $brewPath = null;
                
                foreach ($brewPaths as $bp) {
                    $exists = file_exists($bp);
                    $executable = $exists ? is_executable($bp) : false;
                    $dbg(['h' => 'EH9', 'loc' => 'check-brew', 'msg' => 'checking brew path', 'data' => ['path' => $bp, 'exists' => $exists, 'executable' => $executable]]);
                    
                    if ($exists && $executable) {
                        $brewFound = true;
                        $brewPath = $bp;
                        $dbg(['h' => 'EH10', 'loc' => 'brew-found', 'msg' => 'brew found', 'data' => ['path' => $bp]]);
                        break;
                    }
                }
                
                if ($brewFound) {
                    $dbg(['h' => 'EH11', 'loc' => 'installing-mailpit', 'msg' => 'installing Mailpit via brew']);
                    // Note: This might take a while and might require user interaction
                    // For now, we'll skip auto-install during web requests as it's too slow
                    // Instead, we'll provide clear instructions
                    $dbg(['h' => 'EH12', 'loc' => 'skip-install', 'msg' => 'skipping auto-install (too slow for web request)']);
                } else {
                    $dbg(['h' => 'EH13', 'loc' => 'brew-not-found', 'msg' => 'brew not found, cannot auto-install']);
                }
            }
            
            // If Mailpit is found (either already installed or just installed), start it
            if ($mailpitFound && $foundPath) {
                $dbg(['h' => 'EH14', 'loc' => 'starting-mailpit', 'msg' => 'attempting to start Mailpit', 'data' => ['path' => $foundPath]]);
                
                // Start Mailpit in background using nohup to ensure it stays running
                // Use nohup and redirect output to ensure process detaches properly
                $command = 'nohup ' . escapeshellarg($foundPath) . ' > /dev/null 2>&1 & echo $!';
                $output = [];
                $returnVar = 0;
                $pid = exec($command, $output, $returnVar);
                
                $dbg(['h' => 'EH15', 'loc' => 'mailpit-start-command', 'msg' => 'executed start command', 'data' => [
                    'command' => $command,
                    'pid' => $pid,
                    'return_var' => $returnVar,
                    'output' => $output
                ]]);
                
                // Wait and check multiple times (Mailpit can take a few seconds to start)
                $maxAttempts = 6;
                $attemptDelay = 1; // seconds
                
                for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
                    sleep($attemptDelay);
                    $check = @fsockopen('127.0.0.1', 1025, $errno, $errstr, 1);
                    if ($check) {
                        @fclose($check);
                        $dbg(['h' => 'EH16', 'loc' => 'mailpit-started-success', 'msg' => 'Mailpit started successfully', 'data' => ['attempt' => $attempt, 'pid' => $pid]]);
                        return ['ready' => true, 'type' => 'mailpit', 'message' => 'Mailpit auto-started successfully'];
                    }
                    $dbg(['h' => 'EH17', 'loc' => 'mailpit-check-attempt', 'msg' => 'checking if Mailpit started', 'data' => ['attempt' => $attempt, 'max_attempts' => $maxAttempts, 'error' => $errstr ?? 'Connection failed']]);
                }
                
                // If still not running, check if process is actually running
                if ($pid) {
                    $processCheck = exec("ps -p $pid 2>&1", $psOutput, $psReturn);
                    $dbg(['h' => 'EH18', 'loc' => 'process-check', 'msg' => 'checking if Mailpit process is running', 'data' => ['pid' => $pid, 'ps_output' => $psOutput, 'ps_return' => $psReturn]]);
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
                $dbg(['h' => 'EH19', 'loc' => 'smtp-not-configured', 'msg' => 'SMTP credentials missing', 'data' => ['mail_host' => $mailHost, 'is_gmail' => $isGmail]]);
                
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
            
            $dbg(['h' => 'EH20', 'loc' => 'smtp-configured', 'msg' => 'SMTP configured', 'data' => ['mail_host' => $mailHost, 'is_gmail' => $isGmail, 'username' => $mailUsername]]);
            return ['ready' => true, 'type' => $isGmail ? 'gmail' : 'smtp', 'message' => $isGmail ? 'Gmail SMTP configured' : 'SMTP configured'];
        }
        
        return ['ready' => false, 'type' => 'unknown', 'message' => 'Email not configured'];
    }
}

