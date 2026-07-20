<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('origin');
            $table->string('destination');
            $table->string('title')->nullable();
            $table->string('duration_label')->nullable();
            $table->string('cost_label')->nullable();
            $table->unsignedTinyInteger('transfers')->nullable();
            $table->json('buses')->nullable();
            $table->text('description')->nullable();
            $table->string('comfort')->nullable();
            $table->decimal('rating', 3, 1)->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'origin', 'destination', 'title'], 'saved_routes_user_path_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_routes');
    }
};
