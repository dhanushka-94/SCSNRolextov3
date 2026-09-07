<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80)->unique();
            $table->string('code', 2)->unique();
            $table->string('status', 20)->default('active')->index();
            $table->timestamps();
        });

        Schema::create('rdo_divisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('district_id')->constrained('districts')->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('status', 20)->default('active')->index();
            $table->timestamps();

            $table->unique(['district_id', 'name']);
        });

        $now = now();
        $districts = [
            'Ampara' => 'Ap',
            'Anuradhapura' => 'An',
            'Badulla' => 'Bd',
            'Batticaloa' => 'Bt',
            'Colombo' => 'Co',
            'Galle' => 'Gl',
            'Gampaha' => 'Gp',
            'Hambantota' => 'Hb',
            'Jaffna' => 'Jf',
            'Kalutara' => 'Kt',
            'Kandy' => 'Kd',
            'Kegalle' => 'Kg',
            'Kilinochchi' => 'Kl',
            'Kurunegala' => 'Kr',
            'Mannar' => 'Mn',
            'Matale' => 'Mt',
            'Matara' => 'Mr',
            'Monaragala' => 'Mg',
            'Mullaitivu' => 'Mu',
            'Nuwara Eliya' => 'Ne',
            'Polonnaruwa' => 'Po',
            'Puttalam' => 'Pu',
            'Ratnapura' => 'Rt',
            'Trincomalee' => 'Tr',
            'Vavuniya' => 'Vv',
        ];

        foreach ($districts as $name => $code) {
            $districtId = DB::table('districts')->insertGetId([
                'name' => $name,
                'code' => $code,
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('rdo_divisions')->insert([
                'district_id' => $districtId,
                'name' => $name.' RDO Division',
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        Schema::table('planters', function (Blueprint $table) {
            $table->foreignId('district_id')->nullable()->after('district')->constrained('districts')->nullOnDelete();
            $table->foreignId('rdo_division_id')->nullable()->after('rdd_division')->constrained('rdo_divisions')->nullOnDelete();
        });

        $districtRows = DB::table('districts')->get(['id', 'name']);
        $divisionRows = DB::table('rdo_divisions')->get(['id', 'district_id', 'name']);

        foreach ($districtRows as $district) {
            DB::table('planters')
                ->where('district', $district->name)
                ->whereNull('district_id')
                ->update(['district_id' => $district->id]);

            $defaultDivision = $divisionRows->firstWhere('district_id', $district->id);

            if (! $defaultDivision) {
                continue;
            }

            DB::table('planters')
                ->where('district_id', $district->id)
                ->whereNull('rdo_division_id')
                ->update(['rdo_division_id' => $defaultDivision->id]);

            DB::table('planters')
                ->where('district_id', $district->id)
                ->where(function ($query) {
                    $query->whereNull('rdd_division')->orWhere('rdd_division', '');
                })
                ->update(['rdd_division' => $defaultDivision->name]);
        }
    }

    public function down(): void
    {
        Schema::table('planters', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rdo_division_id');
            $table->dropConstrainedForeignId('district_id');
        });

        Schema::dropIfExists('rdo_divisions');
        Schema::dropIfExists('districts');
    }
};
