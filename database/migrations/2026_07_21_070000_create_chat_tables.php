<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_threads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20); // assistant | support
            $table->string('status', 20)->default('open'); // open | closed
            $table->string('subject')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamp('admin_read_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'type']);
            $table->index(['type', 'last_message_at']);
        });

        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_thread_id')->constrained()->cascadeOnDelete();
            $table->string('role', 20); // user | assistant | admin
            $table->text('body');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['chat_thread_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_threads');
    }
};
