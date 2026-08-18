<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('planters', function (Blueprint $table) {
            $table->string('registration_type', 20)->default('online')->after('status');
            $table->string('application_document')->nullable()->after('registration_type');
        });

        DB::statement('ALTER TABLE planters MODIFY password VARCHAR(255) NULL');
        DB::statement('ALTER TABLE planters MODIFY email VARCHAR(255) NULL');
        DB::statement('ALTER TABLE planters MODIFY district VARCHAR(60) NULL');
        DB::statement('ALTER TABLE planters MODIFY address TEXT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE planters MODIFY password VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE planters MODIFY email VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE planters MODIFY district VARCHAR(60) NOT NULL');
        DB::statement('ALTER TABLE planters MODIFY address TEXT NOT NULL');

        Schema::table('planters', function (Blueprint $table) {
            $table->dropColumn(['registration_type', 'application_document']);
        });
    }
};
