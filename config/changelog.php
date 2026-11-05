<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Release history
    |--------------------------------------------------------------------------
    |
    | Newest release first. The top version is the running app version
    | everywhere (footer, sidebar, changelog “current”, etc.).
    | Bump the top entry to update the whole system — no APP_VERSION needed.
    |
    */

    'releases' => [

        [
            'version' => '0.5.3',
            'date' => '2026-11-05',
            'title' => 'Admin SMS template catalogue',
            'changes' => [
                ['type' => 'added', 'text' => 'Admin SMS templates page lists active and planned Notify.lk message texts with sample previews.'],
                ['type' => 'added', 'text' => 'Staff can send sample SMS messages for one or all templates to a test mobile number.'],
            ],
        ],

        [
            'version' => '0.5.2',
            'date' => '2026-11-05',
            'title' => 'Registration SMS via Notify.lk',
            'changes' => [
                ['type' => 'added', 'text' => 'Notify.lk SMS gateway sends a confirmation with the submission reference when a planter registers (online or offline).'],
                ['type' => 'added', 'text' => 'NOTIFY_* environment settings control User ID, API key, sender ID, and enable/disable.'],
            ],
        ],

        [
            'version' => '0.5.1',
            'date' => '2026-10-22',
            'title' => 'System renamed to SPPNR',
            'changes' => [
                ['type' => 'changed', 'text' => 'System name is now SUSTAINABLE PRODUCTION AND PROCESSING OF NATURAL RUBBER (SPPNR).'],
                ['type' => 'changed', 'text' => 'Certificate numbers use the SPPNR prefix.'],
            ],
        ],

        [
            'version' => '0.5.0',
            'date' => '2026-09-08',
            'title' => 'Certificate issuance & public QR verify',
            'changes' => [
                ['type' => 'added', 'text' => 'Issue SPPNR certificates after Final Audit pass or conditional, with printable certificate pages.'],
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
                ['type' => 'added', 'text' => 'SPPNR application foundation and branding.'],
                ['type' => 'added', 'text' => 'Base authentication for admin and planter portals.'],
            ],
        ],

    ],

];
