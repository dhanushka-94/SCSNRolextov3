<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rdo_divisions', function (Blueprint $table) {
            $table->string('code', 20)->nullable()->after('name');
        });

        $divisions = DB::table('rdo_divisions')
            ->join('districts', 'districts.id', '=', 'rdo_divisions.district_id')
            ->orderBy('rdo_divisions.district_id')
            ->orderBy('rdo_divisions.id')
            ->get([
                'rdo_divisions.id',
                'rdo_divisions.district_id',
                'districts.code as district_code',
            ]);

        $counters = [];

        foreach ($divisions as $division) {
            $counters[$division->district_id] = ($counters[$division->district_id] ?? 0) + 1;
            $code = $division->district_code.str_pad((string) $counters[$division->district_id], 2, '0', STR_PAD_LEFT);

            DB::table('rdo_divisions')
                ->where('id', $division->id)
                ->update(['code' => $code]);
        }

        Schema::table('rdo_divisions', function (Blueprint $table) {
            $table->unique('code');
        });
    }

    public function down(): void
    {
        Schema::table('rdo_divisions', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->dropColumn('code');
        });
    }
};
