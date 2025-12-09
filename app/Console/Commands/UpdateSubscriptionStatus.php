<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use Illuminate\Console\Command;
use Carbon\Carbon;

class UpdateSubscriptionStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscription:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update subscription status based on expiration dates (FR-23)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Updating subscription statuses...');

        // Update expired subscriptions
        $expiredCount = Subscription::where('status', 'completed')
            ->where('ends_at', '<', now())
            ->update(['status' => 'expired']);

        $this->info("Updated {$expiredCount} expired subscriptions.");

        // Update active subscriptions that are about to expire (within 7 days)
        $expiringSoon = Subscription::where('status', 'completed')
            ->where('ends_at', '>', now())
            ->where('ends_at', '<=', now()->addDays(7))
            ->get();

        foreach ($expiringSoon as $subscription) {
            // You can send notification here if needed
            $this->line("Subscription #{$subscription->id} expires on {$subscription->ends_at->format('Y-m-d')}");
        }

        $this->info('Subscription status update completed!');
        return 0;
    }
}

