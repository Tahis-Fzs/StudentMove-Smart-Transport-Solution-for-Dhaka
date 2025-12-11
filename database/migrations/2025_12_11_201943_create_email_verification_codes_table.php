<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_verification_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('code', 6); // 6-digit code
            $table->string('email'); // Email to verify
            $table->timestamp('expires_at'); // Code expiration (15 minutes)
            $table->boolean('used')->default(false); // Track if code was used
            $table->timestamps();
            
            $table->index(['user_id', 'code', 'used']);
            $table->index(['email', 'code', 'used']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('email_verification_codes');
    }
};
