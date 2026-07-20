<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('title')->nullable()->after('id');
            $table->string('audience', 20)->default('all')->after('type');
            $table->string('target_value')->nullable()->after('audience');
            $table->timestamp('published_at')->nullable()->after('sort_order');
            $table->timestamp('expires_at')->nullable()->after('published_at');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn(['title', 'audience', 'target_value', 'published_at', 'expires_at']);
        });
    }
};
