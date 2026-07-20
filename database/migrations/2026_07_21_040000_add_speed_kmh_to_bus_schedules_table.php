<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bus_schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('bus_schedules', 'speed_kmh')) {
                $table->decimal('speed_kmh', 6, 1)->nullable()->after('heading');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bus_schedules', function (Blueprint $table) {
            if (Schema::hasColumn('bus_schedules', 'speed_kmh')) {
                $table->dropColumn('speed_kmh');
            }
        });
    }
};
