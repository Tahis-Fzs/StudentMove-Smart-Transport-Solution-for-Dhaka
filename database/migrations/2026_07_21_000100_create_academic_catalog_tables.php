<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('universities', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('short_name')->nullable();
            // bi = 2 semesters/year, tri = 3 terms/year, both = user can pick
            $table->string('calendar_type', 10)->default('bi');
            $table->boolean('is_custom')->default(false);
            $table->timestamps();
        });

        Schema::create('faculties', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_custom')->default(false);
            $table->timestamps();
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_custom')->default(false);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'faculty')) {
                $table->string('faculty')->nullable()->after('department');
            }
            if (!Schema::hasColumn('users', 'semester')) {
                $table->string('semester')->nullable()->after('year_of_study');
            }
            if (!Schema::hasColumn('users', 'semester_system')) {
                $table->string('semester_system', 10)->nullable()->after('semester');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['faculty', 'semester', 'semester_system'] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::dropIfExists('departments');
        Schema::dropIfExists('faculties');
        Schema::dropIfExists('universities');
    }
};
