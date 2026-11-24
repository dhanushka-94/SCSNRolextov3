<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public const DEVELOPER_EMAIL = 'developer@rrisl.gov.lk';

    public const ADMIN_EMAIL = 'admin@rrisl.gov.lk';

    public const ADMIN_PASSWORD = 'password';

    public const OFFICER_PASSWORD = 'password';

    /** Shared demo password for every system user. */
    public const DEMO_PASSWORD = 'password';

    /** @deprecated */
    public const STAFF_PASSWORD = self::DEMO_PASSWORD;

    public function run(): void
    {
        $accounts = [
            [
                'name' => 'System Developer',
                'email' => self::DEVELOPER_EMAIL,
                'phone' => '0112600900',
                'role' => User::ROLE_DEVELOPER,
                'status' => User::STATUS_ACTIVE,
                'password' => self::DEMO_PASSWORD,
            ],
            [
                'name' => 'System Administrator',
                'email' => self::ADMIN_EMAIL,
                'phone' => '0112600000',
                'role' => User::ROLE_ADMIN,
                'status' => User::STATUS_ACTIVE,
                'password' => self::ADMIN_PASSWORD,
            ],
            [
                'name' => 'RRISL Officer',
                'email' => 'rrisl@rrisl.gov.lk',
                'phone' => '0112600100',
                'role' => User::ROLE_RRISL_OFFICER,
                'status' => User::STATUS_ACTIVE,
                'password' => self::OFFICER_PASSWORD,
            ],
            [
                'name' => 'SPPNR Review Officer (RRISL)',
                'email' => 'staff@rrisl.gov.lk',
                'phone' => '0112600001',
                'role' => User::ROLE_RRISL_OFFICER,
                'status' => User::STATUS_ACTIVE,
                'password' => self::OFFICER_PASSWORD,
            ],
            [
                'name' => 'RDD Officer',
                'email' => 'rdd@rdd.gov.lk',
                'phone' => '0112600200',
                'role' => User::ROLE_RDD_OFFICER,
                'status' => User::STATUS_ACTIVE,
                'password' => self::OFFICER_PASSWORD,
            ],
            [
                'name' => 'Ministry Officer',
                'email' => 'ministry@plantation.gov.lk',
                'phone' => '0112600300',
                'role' => User::ROLE_MINISTRY_OFFICER,
                'status' => User::STATUS_ACTIVE,
                'password' => self::OFFICER_PASSWORD,
            ],
            [
                'name' => 'Field Officer',
                'email' => 'field@rrisl.gov.lk',
                'phone' => '0112600400',
                'role' => User::ROLE_FIELD_OFFICER,
                'status' => User::STATUS_ACTIVE,
                'password' => self::OFFICER_PASSWORD,
            ],
            [
                'name' => 'First Auditor',
                'email' => 'first.auditor@rrisl.gov.lk',
                'phone' => '0112600003',
                'role' => User::ROLE_FIRST_AUDITOR,
                'status' => User::STATUS_ACTIVE,
                'password' => self::OFFICER_PASSWORD,
            ],
            [
                'name' => 'Second Auditor',
                'email' => 'second.auditor@rrisl.gov.lk',
                'phone' => '0112600004',
                'role' => User::ROLE_SECOND_AUDITOR,
                'status' => User::STATUS_ACTIVE,
                'password' => self::OFFICER_PASSWORD,
            ],
            [
                'name' => 'Second Auditor (legacy email)',
                'email' => 'final.auditor@rrisl.gov.lk',
                'phone' => '0112600005',
                'role' => User::ROLE_SECOND_AUDITOR,
                'status' => User::STATUS_ACTIVE,
                'password' => self::OFFICER_PASSWORD,
            ],
            [
                'name' => 'Inactive RRISL Officer',
                'email' => 'inactive.staff@rrisl.gov.lk',
                'phone' => '0112600002',
                'role' => User::ROLE_RRISL_OFFICER,
                'status' => User::STATUS_INACTIVE,
                'password' => self::OFFICER_PASSWORD,
            ],
        ];

        foreach ($accounts as $account) {
            User::query()->create([
                ...$account,
                'email_verified_at' => now(),
            ]);
        }

        foreach ([
            [User::ROLE_RRISL_OFFICER, 'rrisl', 'RRISL Officer', 5, 611000],
            [User::ROLE_RDD_OFFICER, 'rdd', 'RDD Officer', 5, 621000],
            [User::ROLE_FIELD_OFFICER, 'field', 'Field Officer', 5, 641000],
        ] as [$role, $prefix, $label, $count, $phoneBase]) {
            for ($i = 1; $i <= $count; $i++) {
                $number = str_pad((string) $i, 3, '0', STR_PAD_LEFT);

                User::query()->create([
                    'name' => $label.' '.$number,
                    'email' => $prefix.$number.'@sample.gov.lk',
                    'phone' => '011'.str_pad((string) ($phoneBase + $i), 7, '0', STR_PAD_LEFT),
                    'role' => $role,
                    'status' => User::STATUS_ACTIVE,
                    'password' => self::OFFICER_PASSWORD,
                    'email_verified_at' => now(),
                ]);
            }
        }
    }
}
