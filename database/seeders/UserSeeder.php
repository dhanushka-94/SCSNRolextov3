<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public const ADMIN_EMAIL = 'admin@rrisl.gov.lk';

    public const ADMIN_PASSWORD = 'Admin@12345';

    public const STAFF_PASSWORD = 'Staff@12345';

    public const STAFF_COUNT = 100;

    public function run(): void
    {
        User::query()->create([
            'name' => 'System Administrator',
            'email' => self::ADMIN_EMAIL,
            'phone' => '0112600000',
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
            'password' => self::ADMIN_PASSWORD,
            'email_verified_at' => now(),
        ]);

        User::query()->create([
            'name' => 'SCSNR Review Officer',
            'email' => 'staff@rrisl.gov.lk',
            'phone' => '0112600001',
            'role' => User::ROLE_STAFF,
            'status' => User::STATUS_ACTIVE,
            'password' => self::STAFF_PASSWORD,
            'email_verified_at' => now(),
        ]);

        User::query()->create([
            'name' => 'Inactive Staff Account',
            'email' => 'inactive.staff@rrisl.gov.lk',
            'phone' => '0112600002',
            'role' => User::ROLE_STAFF,
            'status' => User::STATUS_INACTIVE,
            'password' => self::STAFF_PASSWORD,
            'email_verified_at' => now(),
        ]);

        User::query()->create([
            'name' => 'First Audit Officer',
            'email' => 'first.auditor@rrisl.gov.lk',
            'phone' => '0112600003',
            'role' => User::ROLE_FIRST_AUDITOR,
            'status' => User::STATUS_ACTIVE,
            'password' => self::STAFF_PASSWORD,
            'email_verified_at' => now(),
        ]);

        User::query()->create([
            'name' => 'Final Audit Officer',
            'email' => 'final.auditor@rrisl.gov.lk',
            'phone' => '0112600004',
            'role' => User::ROLE_FINAL_AUDITOR,
            'status' => User::STATUS_ACTIVE,
            'password' => self::STAFF_PASSWORD,
            'email_verified_at' => now(),
        ]);

        for ($i = 1; $i <= self::STAFF_COUNT; $i++) {
            $number = str_pad((string) $i, 3, '0', STR_PAD_LEFT);

            User::query()->create([
                'name' => 'SCSNR Staff '.$number,
                'email' => 'staff'.$number.'@rrisl.gov.lk',
                'phone' => '0112'.str_pad((string) (600000 + $i), 6, '0', STR_PAD_LEFT),
                'role' => User::ROLE_STAFF,
                'status' => User::STATUS_ACTIVE,
                'password' => self::STAFF_PASSWORD,
                'email_verified_at' => now(),
            ]);
        }
    }
}
