<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bus_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('route_name')->nullable();
            $table->string('departure_time')->nullable();
            $table->string('departure_location')->nullable();
            $table->string('arrival_location')->nullable();
            $table->string('bus_number')->nullable();
            $table->decimal('price', 8, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->decimal('current_lat', 10, 7)->default(23.8103000);
            $table->decimal('current_lng', 10, 7)->default(90.4125000);
            $table->string('heading')->nullable();
            $table->string('status')->default('on_time');
            $table->integer('delay_minutes')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bus_schedules');
    }
};

