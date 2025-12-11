<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable()->after('student_id');
            }
            if (!Schema::hasColumn('users', 'department')) {
                $table->string('department')->nullable()->after('date_of_birth');
            }
            if (!Schema::hasColumn('users', 'year_of_study')) {
                $table->string('year_of_study')->nullable()->after('department');
            }
            if (!Schema::hasColumn('users', 'current_address')) {
                $table->string('current_address')->nullable()->after('year_of_study');
            }
            if (!Schema::hasColumn('users', 'home_address')) {
                $table->string('home_address')->nullable()->after('current_address');
            }
            if (!Schema::hasColumn('users', 'preferred_language')) {
                $table->string('preferred_language')->default('en')->nullable()->after('home_address');
            }
            if (!Schema::hasColumn('users', 'profile_image')) {
                $table->string('profile_image')->nullable()->after('preferred_language');
            }
            if (!Schema::hasColumn('users', 'bus_delay_notifications')) {
                $table->boolean('bus_delay_notifications')->default(false)->after('profile_image');
            }
            if (!Schema::hasColumn('users', 'route_change_alerts')) {
                $table->boolean('route_change_alerts')->default(false)->after('bus_delay_notifications');
            }
            if (!Schema::hasColumn('users', 'promotional_offers')) {
                $table->boolean('promotional_offers')->default(false)->after('route_change_alerts');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach ([
                'date_of_birth',
                'department',
                'year_of_study',
                'current_address',
                'home_address',
                'preferred_language',
                'profile_image',
                'bus_delay_notifications',
                'route_change_alerts',
                'promotional_offers',
            ] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

