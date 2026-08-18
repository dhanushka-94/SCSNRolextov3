<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('planters', function (Blueprint $table) {
            $table->string('identification_number', 40)->nullable()->unique()->after('temporary_id');
        });
    }

    public function down(): void
    {
        Schema::table('planters', function (Blueprint $table) {
            $table->dropUnique(['identification_number']);
            $table->dropColumn('identification_number');
        });
    }
};
