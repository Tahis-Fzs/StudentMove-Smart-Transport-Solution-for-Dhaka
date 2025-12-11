<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('plan_type');
            $table->integer('amount');
            $table->string('payment_method');
            $table->string('payment_provider')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('card_last_four')->nullable();
            $table->string('status')->default('pending'); // pending, success, failed
            $table->string('gateway_ref')->nullable();
            $table->string('checksum')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_attempts');
    }
};

