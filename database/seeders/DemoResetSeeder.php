<?php

namespace Database\Seeders;

use App\Models\Planter;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class DemoResetSeeder extends Seeder
{
    public function run(): void
    {
        Planter::query()->each(function (Planter $planter) {
            if ($planter->application_document && Storage::disk('local')->exists($planter->application_document)) {
                Storage::disk('local')->delete($planter->application_document);
            }
        });

        if (Storage::disk('local')->exists('planter-applications')) {
            Storage::disk('local')->deleteDirectory('planter-applications');
        }

        Schema::disableForeignKeyConstraints();
        Planter::query()->truncate();
        User::query()->truncate();
        Schema::enableForeignKeyConstraints();
    }
}
