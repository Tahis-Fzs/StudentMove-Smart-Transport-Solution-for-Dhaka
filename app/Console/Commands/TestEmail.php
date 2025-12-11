<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmail extends Command
{
    protected $signature = 'email:test {to}';
    protected $description = 'Test email sending with current Gmail configuration';

    public function handle()
    {
        $to = $this->argument('to');

        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email address!');
            return 1;
        }

        $this->info('Testing email configuration...');
        $this->info('Host: ' . config('mail.mailers.smtp.host'));
        $this->info('Username: ' . config('mail.mailers.smtp.username'));
        $this->info('Port: ' . config('mail.mailers.smtp.port'));
        $this->info('');

        try {
            Mail::raw('This is a test email from StudentMove. If you receive this, your Gmail configuration is working correctly!', function ($message) use ($to) {
                $message->to($to)
                        ->subject('StudentMove Test Email');
            });

            $this->info('✅ Test email sent successfully!');
            $this->info("Check your inbox: {$to}");
            $this->info('(Also check spam folder)');
            return 0;

        } catch (\Symfony\Component\Mailer\Exception\TransportException $e) {
            $errorMsg = $e->getMessage();
            $this->error('❌ Email sending failed!');
            $this->error('');
            $this->error('Error: ' . $errorMsg);
            $this->error('');

            if (strpos($errorMsg, 'authentication') !== false || strpos($errorMsg, '535') !== false || strpos($errorMsg, 'BadCredentials') !== false) {
                $this->warn('Gmail Authentication Error!');
                $this->warn('');
                $this->warn('Your Gmail App Password may be incorrect or expired.');
                $this->warn('');
                $this->warn('To fix:');
                $this->warn('1. Go to: https://myaccount.google.com/apppasswords');
                $this->warn('2. Generate a new App Password');
                $this->warn('3. Run: php artisan email:configure-gmail your-email@gmail.com new-app-password');
            }

            return 1;
        } catch (\Exception $e) {
            $this->error('❌ Unexpected error: ' . $e->getMessage());
            return 1;
        }
    }
}
