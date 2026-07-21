<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileCompletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_incomplete_profile_is_redirected_from_dashboard(): void
    {
        $user = User::factory()->incompleteProfile()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('profile.edit', ['complete' => 1]));
    }

    public function test_incomplete_profile_can_open_profile_edit(): void
    {
        $user = User::factory()->incompleteProfile()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('profile.edit'));

        $response->assertOk();
    }

    public function test_complete_profile_can_open_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
    }

    public function test_post_auth_redirect_sends_incomplete_users_to_profile(): void
    {
        $user = User::factory()->incompleteProfile()->create([
            'email_verified_at' => now(),
        ]);

        $this->assertSame(
            route('profile.edit', ['complete' => 1]),
            $user->postAuthRedirect()
        );
    }

    public function test_email_login_redirects_incomplete_user_to_profile(): void
    {
        $user = User::factory()->incompleteProfile()->create([
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('profile.edit', ['complete' => 1]));
    }

    public function test_intended_url_does_not_bypass_profile_completion(): void
    {
        $user = User::factory()->incompleteProfile()->create([
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $response = $this->withSession(['url.intended' => route('dashboard')])
            ->post('/login', [
                'email' => $user->email,
                'password' => 'password123',
            ]);

        $response->assertRedirect(route('profile.edit', ['complete' => 1]));
    }

    public function test_incomplete_profile_is_redirected_from_subscription(): void
    {
        $user = User::factory()->incompleteProfile()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('subscription'));

        $response->assertRedirect(route('profile.edit', ['complete' => 1]));
    }

    public function test_guest_can_view_subscription_plans(): void
    {
        $response = $this->get(route('subscription'));

        $response->assertOk();
    }

    public function test_authenticated_home_redirects_to_profile_completion(): void
    {
        $user = User::factory()->incompleteProfile()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertRedirect(route('profile.edit', ['complete' => 1]));
    }

    public function test_redirect_after_auth_preserves_query_for_incomplete_profile(): void
    {
        $user = User::factory()->incompleteProfile()->create([
            'email_verified_at' => now(),
        ]);

        $response = $user->redirectAfterAuth(['verified' => 1]);

        $this->assertTrue($response->isRedirect(route('profile.edit', ['complete' => 1, 'verified' => 1])));
    }

    public function test_redirect_after_auth_preserves_query_on_intended_url(): void
    {
        $user = User::factory()->create();
        $intended = route('bookings.index');

        session(['url.intended' => $intended]);

        $response = $user->redirectAfterAuth(['verified' => 1]);

        $this->assertTrue($response->isRedirect($intended . '?verified=1'));
        $this->assertNull(session('url.intended'));
    }

    public function test_authenticated_user_visiting_login_is_redirected_to_post_auth_destination(): void
    {
        $user = User::factory()->incompleteProfile()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('login'));

        $response->assertRedirect(route('profile.edit', ['complete' => 1]));
    }
}
