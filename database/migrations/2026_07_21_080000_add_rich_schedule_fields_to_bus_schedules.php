<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bus_schedules', function (Blueprint $table) {
            $table->json('run_days')->nullable()->after('departure_time');
            $table->text('schedule_note')->nullable()->after('run_days');
            $table->json('university_tags')->nullable()->after('schedule_note');
        });
    }

    public function down(): void
    {
        Schema::table('bus_schedules', function (Blueprint $table) {
            $table->dropColumn(['run_days', 'schedule_note', 'university_tags']);
        });
    }
};
