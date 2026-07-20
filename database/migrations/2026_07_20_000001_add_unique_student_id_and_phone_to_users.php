<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        $users = DB::table('users')->select('id', 'email', 'student_id', 'phone')->get();
        foreach ($users as $user) {
            $updates = [];
            if (! empty($user->email)) {
                $updates['email'] = strtolower(trim($user->email));
            }
            if (! empty($user->student_id)) {
                $updates['student_id'] = strtoupper(preg_replace('/\s+/', '', $user->student_id));
            }
            if (! empty($updates)) {
                DB::table('users')->where('id', $user->id)->update($updates);
            }
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $this->ensureSqliteUnique('users_student_id_unique', 'student_id');
            $this->ensureSqliteUnique('users_phone_unique', 'phone');
            return;
        }

        // MySQL / others
        $this->ensureMysqlUnique('users', 'student_id', 'users_student_id_unique');
        $this->ensureMysqlUnique('users', 'phone', 'users_phone_unique');
    }

    private function ensureSqliteUnique(string $indexName, string $column): void
    {
        $exists = DB::selectOne(
            "SELECT name FROM sqlite_master WHERE type = 'index' AND name = ?",
            [$indexName]
        );
        if (! $exists) {
            DB::statement(
                "CREATE UNIQUE INDEX {$indexName} ON users ({$column}) WHERE {$column} IS NOT NULL AND {$column} != ''"
            );
        }
    }

    private function ensureMysqlUnique(string $table, string $column, string $indexName): void
    {
        $row = DB::selectOne(
            'SELECT COUNT(1) AS c FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?',
            [$table, $indexName]
        );
        if (($row->c ?? 0) == 0) {
            DB::statement("ALTER TABLE `{$table}` ADD UNIQUE `{$indexName}` (`{$column}`)");
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'sqlite') {
            DB::statement('DROP INDEX IF EXISTS users_student_id_unique');
            DB::statement('DROP INDEX IF EXISTS users_phone_unique');
            return;
        }

        try {
            DB::statement('ALTER TABLE `users` DROP INDEX `users_student_id_unique`');
        } catch (\Throwable $e) {
        }
        try {
            DB::statement('ALTER TABLE `users` DROP INDEX `users_phone_unique`');
        } catch (\Throwable $e) {
        }
    }
};
