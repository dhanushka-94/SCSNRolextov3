<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('planter_audits', function (Blueprint $table) {
            $table->longText('signature')->nullable()->after('outcome_notes');
            $table->string('signed_by_name')->nullable()->after('signature');
            $table->timestamp('signed_at')->nullable()->after('signed_by_name');
        });
    }

    public function down(): void
    {
        Schema::table('planter_audits', function (Blueprint $table) {
            $table->dropColumn(['signature', 'signed_by_name', 'signed_at']);
        });
    }
};
