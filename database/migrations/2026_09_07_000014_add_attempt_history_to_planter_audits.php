<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('planter_audits', 'attempt_number')) {
            Schema::table('planter_audits', function (Blueprint $table) {
                $table->unsignedInteger('attempt_number')->default(1)->after('round');
                $table->boolean('is_current')->default(true)->after('attempt_number');
            });
        }

        DB::table('planter_audits')->whereNull('attempt_number')->orWhere('attempt_number', 0)->update([
            'attempt_number' => 1,
        ]);
        DB::table('planter_audits')->update(['is_current' => true]);

        Schema::table('planter_audits', function (Blueprint $table) {
            $table->dropForeign(['planter_id']);
        });

        Schema::table('planter_audits', function (Blueprint $table) {
            $table->dropUnique(['planter_id', 'round']);
            $table->unique(['planter_id', 'round', 'attempt_number'], 'planter_audit_round_attempt_unique');
            $table->index(['planter_id', 'round', 'is_current'], 'planter_audit_current_round_index');
            $table->foreign('planter_id')->references('id')->on('planters')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('planter_audits', function (Blueprint $table) {
            $table->dropForeign(['planter_id']);
            $table->dropUnique('planter_audit_round_attempt_unique');
            $table->dropIndex('planter_audit_current_round_index');
        });

        $duplicates = DB::table('planter_audits')
            ->select('planter_id', 'round')
            ->groupBy('planter_id', 'round')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $group) {
            $keepId = DB::table('planter_audits')
                ->where('planter_id', $group->planter_id)
                ->where('round', $group->round)
                ->orderByDesc('is_current')
                ->orderByDesc('attempt_number')
                ->value('id');

            DB::table('planter_audits')
                ->where('planter_id', $group->planter_id)
                ->where('round', $group->round)
                ->where('id', '!=', $keepId)
                ->delete();
        }

        Schema::table('planter_audits', function (Blueprint $table) {
            $table->dropColumn(['attempt_number', 'is_current']);
            $table->unique(['planter_id', 'round']);
            $table->foreign('planter_id')->references('id')->on('planters')->cascadeOnDelete();
        });
    }
};
