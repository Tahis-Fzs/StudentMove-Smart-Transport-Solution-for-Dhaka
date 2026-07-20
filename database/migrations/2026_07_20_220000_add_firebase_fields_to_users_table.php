<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'firebase_uid')) {
                $table->string('firebase_uid')->nullable()->unique()->after('email');
            }
            if (! Schema::hasColumn('users', 'auth_provider')) {
                $table->string('auth_provider', 40)->nullable()->after('firebase_uid');
            }
            if (! Schema::hasColumn('users', 'avatar_url')) {
                $table->string('avatar_url', 500)->nullable()->after('profile_image');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $cols = array_filter(['firebase_uid', 'auth_provider', 'avatar_url'], fn ($c) => Schema::hasColumn('users', $c));
            if ($cols) {
                $table->dropColumn($cols);
            }
        });
    }
};
