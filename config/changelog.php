<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Release history
    |--------------------------------------------------------------------------
    |
    | Newest release first. The top version becomes the running app version
    | unless APP_VERSION is set in the environment.
    |
    */

    'releases' => [

        [
            'version' => '0.5.0',
            'date' => '2026-09-08',
            'title' => 'Certificate issuance & public QR verify',
            'changes' => [
                ['type' => 'added', 'text' => 'Issue SCSNR certificates after Final Audit pass or conditional, with printable certificate pages.'],
                ['type' => 'added', 'text' => 'Public certificate verification via QR scan (/verify/{token}) or reference lookup (/verify).'],
                ['type' => 'added', 'text' => 'Certificates menu with Issued and Revoked lists; staff can revoke with reason.'],
                ['type' => 'changed', 'text' => 'Planter QR encodes the public verify URL once a certificate is issued.'],
            ],
        ],

        [
            'version' => '0.4.2',
            'date' => '2026-09-08',
            'title' => 'Audits menu: Ongoing, Passed, Rejected',
            'changes' => [
                ['type' => 'added', 'text' => 'Separate Audits sidebar section with Ongoing, Passed, and Rejected lists.'],
                ['type' => 'changed', 'text' => 'Audit queues keep First/Final tabs inside each list.'],
            ],
        ],

        [
            'version' => '0.4.1',
            'date' => '2026-09-07',
            'title' => 'Audit attempt history',
            'changes' => [
                ['type' => 'added', 'text' => 'Failed and completed First/Final Audit attempts are archived when starting a new attempt.'],
                ['type' => 'added', 'text' => 'Attempt history on the audit checklist page with full checklist answers preserved.'],
            ],
        ],

        [
            'version' => '0.4.0',
            'date' => '2026-09-07',
            'title' => 'First & Final Audit checklists',
            'changes' => [
                ['type' => 'added', 'text' => 'Separate First Audit and Final Audit rounds with Send to First/Final Audit actions after approval.'],
                ['type' => 'added', 'text' => 'Auditor module checklists for PSM, EMM, WHSWM, PMM, and GRM.'],
                ['type' => 'added', 'text' => 'First Auditor and Final Auditor system roles with round-scoped access.'],
                ['type' => 'changed', 'text' => 'Audit workspace supports First/Final tabs and per-audit module checklists.'],
                ['type' => 'changed', 'text' => 'Planter timeline shows Registration → First Audit → Final Audit → Result.'],
            ],
        ],

        [
            'version' => '0.3.0',
            'date' => '2026-09-07',
            'title' => 'Audit workflow & farm map pins',
            'changes' => [
                ['type' => 'added', 'text' => 'Audit Lobby for advancing sustainability audits on approved registry entries.'],
                ['type' => 'added', 'text' => 'Admin audit controls to open, progress, review, publish, update, or reopen results.'],
                ['type' => 'added', 'text' => 'Farm map pin picker on registration, admin CRUD, and planter profile (Sri Lanka bounds).'],
                ['type' => 'changed', 'text' => 'Dashboard now surfaces active and completed audit counts.'],
            ],
        ],

        [
            'version' => '0.2.0',
            'date' => '2026-09-07',
            'title' => 'Registry workflow',
            'changes' => [
                ['type' => 'added', 'text' => 'Admin Changelog page with versioned release notes.'],
                ['type' => 'added', 'text' => 'Separate Registry (approved) and Rejected lists in admin.'],
                ['type' => 'added', 'text' => 'District and RDO division master data with 4-character codes.'],
                ['type' => 'added', 'text' => 'About System page explaining registration number format.'],
                ['type' => 'changed', 'text' => 'Renamed Rubber planter registration to Registry in admin UI.'],
                ['type' => 'changed', 'text' => 'Registration Lobby replaces the old approval lobby for pending applications.'],
                ['type' => 'changed', 'text' => 'Permanent registration numbers are issued only on approve or reject.'],
            ],
        ],

        [
            'version' => '0.1.0',
            'date' => '2026-08-15',
            'title' => 'Partner portal & registration',
            'changes' => [
                ['type' => 'added', 'text' => 'Partner homepage with planter registration and sign-in entry points.'],
                ['type' => 'added', 'text' => 'Online and offline planter registration flows.'],
                ['type' => 'added', 'text' => 'Admin dashboard, approval actions, and planter profile with QR identity.'],
                ['type' => 'added', 'text' => 'Staff and system user management for administrators.'],
            ],
        ],

        [
            'version' => '0.0.1',
            'date' => '2026-07-01',
            'title' => 'Initial release',
            'changes' => [
                ['type' => 'added', 'text' => 'SCSNR application foundation and branding.'],
                ['type' => 'added', 'text' => 'Base authentication for admin and planter portals.'],
            ],
        ],

    ],

];
