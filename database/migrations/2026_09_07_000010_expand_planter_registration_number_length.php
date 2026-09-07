<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE planters MODIFY temporary_id VARCHAR(80) NOT NULL');
            DB::statement('ALTER TABLE planters MODIFY identification_number VARCHAR(80) NULL');

            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE planters ALTER COLUMN temporary_id TYPE VARCHAR(80)');
            DB::statement('ALTER TABLE planters ALTER COLUMN identification_number TYPE VARCHAR(80)');

            return;
        }

        // SQLite and others: recreate is heavy; lengths are not strictly enforced.
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE planters MODIFY temporary_id VARCHAR(24) NOT NULL');
            DB::statement('ALTER TABLE planters MODIFY identification_number VARCHAR(40) NULL');

            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE planters ALTER COLUMN temporary_id TYPE VARCHAR(24)');
            DB::statement('ALTER TABLE planters ALTER COLUMN identification_number TYPE VARCHAR(40)');
        }
    }
};
