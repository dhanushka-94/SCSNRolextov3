<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('planters', function (Blueprint $table) {
            $table->string('audit_status', 40)->default('not_started')->after('status');
            $table->string('audit_result_outcome', 30)->nullable()->after('audit_status');
            $table->text('audit_result_notes')->nullable()->after('audit_result_outcome');
            $table->timestamp('audit_status_updated_at')->nullable()->after('audit_result_notes');
        });
    }

    public function down(): void
    {
        Schema::table('planters', function (Blueprint $table) {
            $table->dropColumn([
                'audit_status',
                'audit_result_outcome',
                'audit_result_notes',
                'audit_status_updated_at',
            ]);
        });
    }
};
