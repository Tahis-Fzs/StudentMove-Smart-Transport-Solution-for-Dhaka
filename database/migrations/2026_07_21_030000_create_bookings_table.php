<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bus_schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('bus_schedules', 'seats_total')) {
                $table->unsignedSmallInteger('seats_total')->default(40)->after('price');
            }
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bus_schedule_id')->nullable()->constrained('bus_schedules')->nullOnDelete();
            $table->string('booking_code', 16)->unique();
            $table->string('route_name');
            $table->string('bus_number')->nullable();
            $table->string('origin');
            $table->string('destination');
            $table->date('travel_date');
            $table->string('departure_time', 20)->nullable();
            $table->unsignedTinyInteger('seats')->default(1);
            $table->string('seat_preference', 20)->default('any');
            $table->decimal('fare', 8, 2)->default(0);
            $table->string('status', 20)->default('confirmed');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'travel_date']);
            $table->index(['bus_schedule_id', 'travel_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');

        Schema::table('bus_schedules', function (Blueprint $table) {
            if (Schema::hasColumn('bus_schedules', 'seats_total')) {
                $table->dropColumn('seats_total');
            }
        });
    }
};
