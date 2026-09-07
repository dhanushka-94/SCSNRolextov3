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
            DB::statement('ALTER TABLE districts MODIFY code VARCHAR(10) NOT NULL');

            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE districts ALTER COLUMN code TYPE VARCHAR(10)');
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE districts MODIFY code VARCHAR(2) NOT NULL');

            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE districts ALTER COLUMN code TYPE VARCHAR(2)');
        }
    }
};
