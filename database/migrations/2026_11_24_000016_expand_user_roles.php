<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE users MODIFY role VARCHAR(32) NOT NULL DEFAULT 'rrisl_officer'");
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE users ALTER COLUMN role TYPE VARCHAR(32)');
            DB::statement("ALTER TABLE users ALTER COLUMN role SET DEFAULT 'rrisl_officer'");
        }

        DB::table('users')->where('role', 'staff')->update(['role' => 'rrisl_officer']);
        DB::table('users')->where('role', 'final_auditor')->update(['role' => 'second_auditor']);
    }

    public function down(): void
    {
        DB::table('users')->where('role', 'rrisl_officer')->update(['role' => 'staff']);
        DB::table('users')->where('role', 'second_auditor')->update(['role' => 'final_auditor']);

        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE users MODIFY role VARCHAR(20) NOT NULL DEFAULT 'staff'");
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE users ALTER COLUMN role TYPE VARCHAR(20)');
            DB::statement("ALTER TABLE users ALTER COLUMN role SET DEFAULT 'staff'");
        }
    }
};
