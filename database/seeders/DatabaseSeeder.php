<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        if (app()->environment('production')) {
            throw new \RuntimeException('Demo seeding is blocked in production. Use migrate and the reference seeders only.');
        }

        $this->call([
            DemoResetSeeder::class,
            DistrictSeeder::class,
            UserSeeder::class,
            AuditChecklistItemSeeder::class,
            PlanterSeeder::class,
            AuditCertificateDemoSeeder::class,
            DemoLoginsFileSeeder::class,
        ]);
    }
}
