<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Align plan_type keys with product names/durations:
 *   monthly (was mislabeled Weekly)  → weekly  (7 days)
 *   6months (was mislabeled Monthly) → monthly (30 days)
 *   yearly  (was mislabeled Single)  → single  (1 day)
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            // Expand enum so both old and new values are valid during remap
            DB::statement("ALTER TABLE subscriptions MODIFY plan_type ENUM('monthly','6months','yearly','weekly','single') NOT NULL");
        }

        $this->remap('subscriptions');
        $this->remap('payment_attempts');
        $this->remap('invoices');

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE subscriptions MODIFY plan_type ENUM('weekly','monthly','single') NOT NULL");
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE subscriptions MODIFY plan_type ENUM('weekly','monthly','single','6months','yearly') NOT NULL");
        }

        // Reverse remap (order matters: monthly→6months before weekly→monthly)
        foreach (['subscriptions', 'payment_attempts', 'invoices'] as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'plan_type')) {
                continue;
            }
            DB::table($table)->where('plan_type', 'monthly')->update(['plan_type' => '6months']);
            DB::table($table)->where('plan_type', 'weekly')->update(['plan_type' => 'monthly']);
            DB::table($table)->where('plan_type', 'single')->update(['plan_type' => 'yearly']);
        }

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE subscriptions MODIFY plan_type ENUM('monthly','6months','yearly') NOT NULL");
        }
    }

    protected function remap(string $table): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'plan_type')) {
            return;
        }

        // Order matters: rewrite `monthly` before introducing the new `monthly` from `6months`
        DB::table($table)->where('plan_type', 'monthly')->update(['plan_type' => 'weekly']);
        DB::table($table)->where('plan_type', '6months')->update(['plan_type' => 'monthly']);
        DB::table($table)->where('plan_type', 'yearly')->update(['plan_type' => 'single']);
    }
};
