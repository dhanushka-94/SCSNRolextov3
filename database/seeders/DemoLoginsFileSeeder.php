<?php

namespace Database\Seeders;

use App\Models\Certificate;
use App\Models\PlanterAudit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class DemoLoginsFileSeeder extends Seeder
{
    public function run(): void
    {
        $generated = sl_datetime(now());
        $issuedCerts = Certificate::query()->where('status', Certificate::STATUS_ISSUED)->count();
        $revokedCerts = Certificate::query()->where('status', Certificate::STATUS_REVOKED)->count();
        $sampleCert = Certificate::query()->where('status', Certificate::STATUS_ISSUED)->orderBy('id')->first();
        $verifyHint = $sampleCert
            ? $sampleCert->certificate_number.'  →  /verify  or  '.$sampleCert->verifyUrl()
            : '(issue a certificate first)';

        $firstQueued = PlanterAudit::query()->where('round', 'first')->where('is_current', true)->where('status', 'queued')->count();
        $ongoing = PlanterAudit::query()->where('is_current', true)->whereIn('status', ['queued', 'in_progress', 'in_review'])->count();
        $passedAudits = PlanterAudit::query()->whereIn('status', ['passed', 'conditional'])->count();
        $failedAudits = PlanterAudit::query()->where('status', 'failed')->count();

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

First Auditor
  Email   : first.auditor@rrisl.gov.lk
  Password: Staff@12345
  Access  : First Audit checklist queue only

Final Auditor
  Email   : final.auditor@rrisl.gov.lk
  Password: Staff@12345
  Access  : Final Audit checklist queue only

Staff accounts (100 samples)
  Email   : staff001@rrisl.gov.lk  to  staff100@rrisl.gov.lk
  Password: Staff@12345  (same for all staff accounts)

Total system users seeded: 105


PLANTER USERS (Planter portal: /login)
---------------------------------------
Total planter records seeded: 200

Pending online (45)
  Email   : sunil.001@sample.lk  to  (planter records 001–045)
  Status  : Pending approval — temporary submission reference only (no RUB/SUS number yet)

Pending offline (25)
  Email   : records 046–070
  Status  : Pending approval — offline registration

Approved with password (90)
  Email   : records 071–160
  Password: Planter@12345
  Notes   : Registration number issued; many already have demo audits/certificates
  Alt login: Registration ID RUB/SUS/{district}/{division}/{#####} (see admin planter list)

Approved without password (20)
  Email   : records 161–180
  Action  : Use /set-password (registration ID + NIC)

Rejected (20)
  Email   : records 181–200
  Status  : Cannot sign in — registration number still issued on reject for reference


SAMPLE AUDITS (from approved planters with password)
----------------------------------------------------
Ongoing audits (current) : {$ongoing}
First queued (sample)    : {$firstQueued}
Passed/conditional rows  : {$passedAudits}
Failed audit attempts    : {$failedAudits}

Includes: First queued / in progress / failed, Final queued / in progress / failed,
Final passed+certificate, Final conditional+certificate, revoked certificates.


SAMPLE CERTIFICATES
-------------------
Issued  : {$issuedCerts}
Revoked : {$revokedCerts}
Verify  : {$verifyHint}
Public form: /verify  (certificate No. or SCSNR ID)


Coverage
  • Kegalle, Kalutara, Galle, Ratnapura, Monaragala regions
  • Full RDO division lists per region
  • All business types (individual, partnership, company, limited, society, other)
  • Audit path + certificates with public QR verify
  • Online and offline registration types
  • Registration IDs: RUB/SUS/{district_code}/{division_code}/00001+
  • Farm map pins (latitude/longitude) on sample records


QUICK TEST ACCOUNTS
-------------------
Admin     : admin@rrisl.gov.lk / Admin@12345
Staff     : staff001@rrisl.gov.lk / Staff@12345
First auditor : first.auditor@rrisl.gov.lk / Staff@12345
Final auditor : final.auditor@rrisl.gov.lk / Staff@12345
Planter   : sanjeewa.086@sample.lk / Planter@12345

Refresh all demo data:
  php artisan db:seed

TEXT;

        File::put(base_path('DEMO-LOGINS.txt'), $content);
    }
}
