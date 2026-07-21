<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatAssistantTest extends TestCase
{
    use RefreshDatabase;

    public function test_assistant_chat_returns_reply_for_hello(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/chat', [
            'message' => 'hello',
            'channel' => 'assistant',
        ]);

        $response->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonCount(2, 'messages');

        $this->assertDatabaseHas('chat_messages', [
            'role' => 'assistant',
        ]);
    }

    public function test_support_chat_accepts_message_without_assistant_reply(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/chat', [
            'message' => 'hello',
            'channel' => 'support',
        ]);

        $response->assertOk()->assertJsonCount(1, 'messages');
    }
}
