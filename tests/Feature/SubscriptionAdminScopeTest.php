<?php

namespace Tests\Feature;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionAdminScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_currently_active_scope_matches_completed_unexpired_plans(): void
    {
        $user = User::factory()->create();

        Subscription::create([
            'user_id' => $user->id,
            'plan_type' => 'weekly',
            'amount' => 350,
            'payment_method' => 'mobile_banking',
            'status' => 'completed',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(6),
        ]);

        Subscription::create([
            'user_id' => $user->id,
            'plan_type' => 'single',
            'amount' => 30,
            'payment_method' => 'mobile_banking',
            'status' => 'cancelled',
            'starts_at' => now()->subDays(10),
            'ends_at' => now()->subDays(9),
        ]);

        $this->assertSame(1, Subscription::currentlyActive()->count());
    }

    public function test_future_start_subscription_is_not_currently_active(): void
    {
        $user = User::factory()->create();

        Subscription::create([
            'user_id' => $user->id,
            'plan_type' => 'weekly',
            'amount' => 350,
            'payment_method' => 'mobile_banking',
            'status' => 'completed',
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDays(8),
        ]);

        $this->assertSame(0, Subscription::currentlyActive()->count());
        $this->assertFalse(Subscription::first()->isActive());
    }

    public function test_expired_subscription_is_excluded_from_currently_active(): void
    {
        $user = User::factory()->create();

        Subscription::withoutEvents(function () use ($user) {
            Subscription::create([
                'user_id' => $user->id,
                'plan_type' => 'single',
                'amount' => 30,
                'payment_method' => 'mobile_banking',
                'status' => 'completed',
                'starts_at' => now()->subDays(10),
                'ends_at' => now()->subDay(),
            ]);
        });

        $this->assertSame(0, Subscription::currentlyActive()->count());
    }

    public function test_report_income_uses_currently_active_scope_only(): void
    {
        $user = User::factory()->create();

        Subscription::create([
            'user_id' => $user->id,
            'plan_type' => 'weekly',
            'amount' => 350,
            'payment_method' => 'mobile_banking',
            'status' => 'completed',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(6),
        ]);

        Subscription::create([
            'user_id' => $user->id,
            'plan_type' => 'single',
            'amount' => 30,
            'payment_method' => 'mobile_banking',
            'status' => 'cancelled',
            'starts_at' => now()->subDays(20),
            'ends_at' => now()->subDays(10),
        ]);

        $this->assertSame(350.0, (float) Subscription::currentlyActive()->sum('amount'));
    }

    public function test_banned_user_cannot_log_in(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
            'is_banned' => true,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
