<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\Planter;
use App\Models\RdoDivision;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class DemoResetSeeder extends Seeder
{
    public function run(): void
    {
        Planter::query()->each(function (Planter $planter) {
            foreach (['application_document', 'prior_certificate_document'] as $field) {
                $path = $planter->{$field};

                if ($path && Storage::disk('local')->exists($path)) {
                    Storage::disk('local')->delete($path);
                }
            }
        });

        foreach (['planter-applications', 'planter-certificates'] as $directory) {
            if (Storage::disk('local')->exists($directory)) {
                Storage::disk('local')->deleteDirectory($directory);
            }
        }

        Schema::disableForeignKeyConstraints();
        \App\Models\PlanterAuditChecklistAnswer::query()->truncate();
        \App\Models\PlanterAudit::query()->truncate();
        \App\Models\AuditChecklistItem::query()->truncate();
        Planter::query()->truncate();
        User::query()->truncate();
        RdoDivision::query()->truncate();
        District::query()->truncate();
        Schema::enableForeignKeyConstraints();
    }
}
