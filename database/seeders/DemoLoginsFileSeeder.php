<?php

namespace Database\Seeders;

use App\Models\Certificate;
use App\Models\Planter;
use App\Models\PlanterAudit;
use App\Models\User;
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
        $secondInProgress = PlanterAudit::query()->where('round', 'final')->where('is_current', true)->whereIn('status', ['queued', 'in_progress', 'in_review'])->count();
        $ongoing = PlanterAudit::query()->where('is_current', true)->whereIn('status', ['queued', 'in_progress', 'in_review'])->count();
        $passedAudits = PlanterAudit::query()->whereIn('status', ['passed', 'conditional'])->count();
        $failedAudits = PlanterAudit::query()->where('status', 'failed')->count();
        $userCount = User::query()->count();
        $pending = Planter::query()->where('status', Planter::STATUS_PENDING)->count();
        $readyForCert = Planter::query()
            ->where('status', Planter::STATUS_APPROVED)
            ->whereHas('finalAudit', function ($query) {
                $query->where('is_current', true)
                    ->whereIn('status', [PlanterAudit::STATUS_PASSED, PlanterAudit::STATUS_CONDITIONAL]);
            })
            ->whereDoesntHave('currentCertificate')
            ->count();

        $pwd = UserSeeder::DEMO_PASSWORD;

        $content = <<<TEXT
SPPNR Demo Login Reference
Generated: {$generated}
========================================

ALL DEMO PASSWORDS = {$pwd}
(Admin, officers, auditors, and planters with passwords)

SYSTEM USERS (Admin portal: /admin)
------------------------------------
Administrator
  Email   : admin@rrisl.gov.lk
  Password: {$pwd}
  Access  : Full system (both audit rounds + certificates)

RRISL Officer
  Email   : rrisl@rrisl.gov.lk
  Password: {$pwd}
  Alias   : staff@rrisl.gov.lk / {$pwd}

RDD Officer
  Email   : rdd@rdd.gov.lk
  Password: {$pwd}

Ministry Officer
  Email   : ministry@plantation.gov.lk
  Password: {$pwd}
  Access  : Read-only

Field Officer
  Email   : field@rrisl.gov.lk
  Password: {$pwd}

First Auditor
  Email   : first.auditor@rrisl.gov.lk
  Password: {$pwd}
  Access  : Work First Audit checklists; view Second Audit status

Second Auditor
  Email   : second.auditor@rrisl.gov.lk
  Password: {$pwd}
  Access  : Work Second Audit checklists; view First Audit status
  Alias   : final.auditor@rrisl.gov.lk / {$pwd}

Inactive RRISL sample
  Email   : inactive.staff@rrisl.gov.lk
  Password: {$pwd}
  Status  : Inactive — cannot sign in

Extra samples (password {$pwd})
  rrisl001@sample.gov.lk … rrisl005@sample.gov.lk
  rdd001@sample.gov.lk … rdd005@sample.gov.lk
  field001@sample.gov.lk … field005@sample.gov.lk

Total system users seeded: {$userCount}


PLANTER USERS (Planter portal: /login)
---------------------------------------
Password for approved planters with login: {$pwd}

Pending approval (lobby) : {$pending}
Ready for certificate    : {$readyForCert}  (Second Audit passed/conditional, no cert yet)

Approved with password (90) — e.g. sanjeewa.086@sample.lk / {$pwd}
Approved without password (20) — use /set-password
Rejected (20)


SAMPLE AUDITS
-------------
Ongoing audits (current) : {$ongoing}
First queued             : {$firstQueued}
Second ongoing           : {$secondInProgress}
Passed/conditional rows  : {$passedAudits}
Failed audit attempts    : {$failedAudits}

Dummy checklists included on in-progress First/Second audits.
Admin can open and work both rounds.


SAMPLE CERTIFICATES
-------------------
Issued  : {$issuedCerts}
Revoked : {$revokedCerts}
Verify  : {$verifyHint}

Resend step SMS: open any planter → “Resend step SMS” buttons.


QUICK TEST ACCOUNTS (password for all: {$pwd})
---------------------------------------------
Admin           : admin@rrisl.gov.lk
RRISL Officer   : rrisl@rrisl.gov.lk
RDD Officer     : rdd@rdd.gov.lk
Ministry        : ministry@plantation.gov.lk
Field Officer   : field@rrisl.gov.lk
First Auditor   : first.auditor@rrisl.gov.lk
Second Auditor  : second.auditor@rrisl.gov.lk
Planter         : sanjeewa.086@sample.lk

Refresh all demo data:
  php artisan db:seed

TEXT;

        File::put(base_path('DEMO-LOGINS.txt'), $content);
    }
}
