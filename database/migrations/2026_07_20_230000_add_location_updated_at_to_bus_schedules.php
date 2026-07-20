<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bus_schedules', function (Blueprint $table) {
            if (! Schema::hasColumn('bus_schedules', 'location_updated_at')) {
                $table->timestamp('location_updated_at')->nullable()->after('current_lng');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bus_schedules', function (Blueprint $table) {
            if (Schema::hasColumn('bus_schedules', 'location_updated_at')) {
                $table->dropColumn('location_updated_at');
            }
        });
    }
};
