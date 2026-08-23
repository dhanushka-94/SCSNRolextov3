<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class DemoLoginsFileSeeder extends Seeder
{
    public function run(): void
    {
        $generated = sl_datetime(now());

        $content = <<<TEXT
SCSNR Demo Login Reference
Generated: {$generated}
========================================

SYSTEM USERS (Admin portal: /admin)
------------------------------------
Administrator
  Email   : admin@rrisl.gov.lk
  Password: Admin@12345

Primary staff officer
  Email   : staff@rrisl.gov.lk
  Password: Staff@12345

Inactive staff sample
  Email   : inactive.staff@rrisl.gov.lk
  Password: Staff@12345
  Status  : Inactive — cannot sign in

Staff accounts (100 samples)
  Email   : staff001@rrisl.gov.lk  to  staff100@rrisl.gov.lk
  Password: Staff@12345  (same for all staff accounts)

Total system users seeded: 103


PLANTER USERS (Planter portal: /login)
---------------------------------------
Total planter records seeded: 120

Pending online (30)
  Email   : sunil.001@sample.lk  to  (planter records 001–030)
  Status  : Pending approval — SCSNR ID issued, cannot sign in

Pending offline (15)
  Email   : records 031–045
  Status  : Pending approval — offline registration

Approved with password (55)
  Email   : records 046–100
  Password: Planter@12345
  Notes   : Covers all audit stages and certification outcomes
  Examples:
    malith.046@sample.lk / Planter@12345
    sanjeewa.050@sample.lk / Planter@12345
    sachini.100@sample.lk / Planter@12345
  Alt login: SCSNR ID (see admin planter list)

Approved without password (10)
  Email   : records 101–110
  Action  : Use /set-password (SCSNR ID + NIC)

Rejected (10)
  Email   : records 111–120
  Status  : Cannot sign in — SCSNR ID retained for reference

Coverage
  • All 25 districts represented
  • All business types (individual, partnership, company, limited, society, other)
  • All audit stages: not started, open, in progress, in review, result
  • All audit outcomes: certified, conditional, not certified
  • Online and offline registration types


QUICK TEST ACCOUNTS
-------------------
Admin     : admin@rrisl.gov.lk / Admin@12345
Staff     : staff001@rrisl.gov.lk / Staff@12345
Planter   : sanjeewa.050@sample.lk / Planter@12345

Refresh all demo data:
  php artisan db:seed

TEXT;

        File::put(base_path('DEMO-LOGINS.txt'), $content);
    }
}
