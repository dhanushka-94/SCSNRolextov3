<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Show the Changelog page
    |--------------------------------------------------------------------------
    |
    | Hidden by default. Set CHANGELOG_ENABLED=true in .env to show the
    | sidebar link and open the page again.
    */
    'enabled' => (bool) env('CHANGELOG_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Release history
    |--------------------------------------------------------------------------
    |
    | Newest release first. The top version is the running app version
    | everywhere (footer, sidebar, changelog “current”, etc.).
    | Bump the top entry to update the whole system — no APP_VERSION needed.
    |
    | Numbering follows Semantic Versioning: MAJOR.MINOR.PATCH.
    | MAJOR: a change that breaks existing use (for example a changed API).
    | MINOR: new features that stay compatible with what already works.
    | PATCH: bug fixes with no new features.
    | When a number goes up, the numbers to its right reset to 0.
    |
    */

    'releases' => [

        [
            'version' => '1.0.0',
            'date' => '2026-11-24',
            'title' => 'First release',
            'changes' => [
                ['type' => 'added', 'text' => 'Mobile API documentation with every endpoint, request and response example, roles and enums, published as part of the system.'],
                ['type' => 'changed', 'text' => 'The SMS template list is for the developer (Super Admin) only. Other staff roles receive 403.'],
                ['type' => 'removed', 'text' => 'The sample-SMS endpoint was removed from the API. Sample SMS can still be sent from the web Settings page by the developer.'],
                ['type' => 'security', 'text' => 'Cross-origin requests from other websites are blocked by default. Allowed origins are set with CORS_ALLOWED_ORIGINS.'],
                ['type' => 'security', 'text' => 'Every response, including errors such as 401, 403, 404 and 500, carries the security headers. The PHP version header is removed.'],
                ['type' => 'changed', 'text' => 'Short name is SPPNR, with the full name SUSTAINABLE PRODUCTION AND PROCESSING OF NATURAL RUBBER.'],
                ['type' => 'changed', 'text' => 'Version numbers follow Semantic Versioning (MAJOR.MINOR.PATCH).'],
            ],
        ],

        [
            'version' => '0.11.0',
            'date' => '2026-11-24',
            'title' => 'Colour-coded registration types, trilingual paper form, responsive fixes',
            'changes' => [
                ['type' => 'added', 'text' => 'Each registration type has its own colour in the admin: Solo (sky blue), Group (amber), Company (violet). It shows as a badge and a left bar on the registry, lobby, audits, certificates, dashboard and application page.'],
                ['type' => 'added', 'text' => 'Registry, Rejected and the Registration Lobby can be filtered by registration type, with a count for each type.'],
                ['type' => 'changed', 'text' => 'Staff choose the registration type when adding an entry (web and mobile API: registration_category). It sets the nature of business automatically; older records without a type stay as they are.'],
                ['type' => 'changed', 'text' => 'The printable paper registration form is now in Sinhala, English and Tamil, with a registration type section. It is built with correct Sinhala and Tamil letter shaping (php artisan sppnr:build-registration-form).'],
                ['type' => 'fixed', 'text' => 'English text was missing from the downloadable paper form; it now prints correctly.'],
                ['type' => 'fixed', 'text' => 'Page headers wrap on small phones, and the API documentation no longer scrolls sideways on narrow screens.'],
                ['type' => 'fixed', 'text' => 'Editing a pending entry that has no SPPNR ID yet no longer leaves the page header half-built.'],
            ],
        ],

        [
            'version' => '0.10.0',
            'date' => '2026-11-24',
            'title' => 'Registration type chooser (sole proprietor, group, company)',
            'changes' => [
                ['type' => 'added', 'text' => 'Before the application form, applicants choose Sole Proprietor, Group or Company registration. Labels are in English, Sinhala and Tamil.'],
                ['type' => 'changed', 'text' => 'The registration type sets the nature of business automatically: sole proprietor = individual, group = society, company = company. Group and company need a name.'],
                ['type' => 'added', 'text' => 'Registration type is saved on every application (online and paper) and shown on the admin registry page. The mobile API requires registration_category; reference/options lists the types.'],
                ['type' => 'changed', 'text' => 'Tamil font added to the website.'],
            ],
        ],

        [
            'version' => '0.9.0',
            'date' => '2026-11-24',
            'title' => 'Two QR codes: tracking and certificate',
            'changes' => [
                ['type' => 'changed', 'text' => 'Every applicant now has one tracking QR code that encodes the reference number given at registration. It never changes, from registration to the end of the process.'],
                ['type' => 'added', 'text' => 'Mobile app: officers and auditors scan the tracking QR (GET /api/v1/staff/scan/{reference}) to open the record, their audits and the actions their role allows. Each scan is logged.'],
                ['type' => 'changed', 'text' => 'Each issued certificate has its own unique QR code that opens the public verification page, showing Valid, Revoked or not found.'],
                ['type' => 'fixed', 'text' => 'A certificate QR image now always matches its own certificate, so an old or revoked certificate no longer shows the newer certificate link.'],
                ['type' => 'changed', 'text' => 'The Changelog page is hidden for now (set CHANGELOG_ENABLED=true to show it again).'],
            ],
        ],

        [
            'version' => '0.8.0',
            'date' => '2026-11-24',
            'title' => 'Mobile API, API documentation & Super Admin (Developer) role',
            'changes' => [
                ['type' => 'added', 'text' => 'Mobile app API (v1) for planters and every staff role: sign-in with tokens, registration, planter portal, registry, audits with digital signature, certificates, SMS templates, user management and an offline sync upload.'],
                ['type' => 'added', 'text' => 'New role: Super Admin (Developer). It has every administrator permission, and is the only role that can open Settings and the new API documentation page.'],
                ['type' => 'added', 'text' => 'API documentation page under Settings: every endpoint with requests, example responses, errors, roles and offline sync rules.'],
                ['type' => 'changed', 'text' => 'Settings (maintenance mode) is now limited to the Super Admin (Developer). Administrators can no longer open it, and cannot see or change Developer accounts.'],
                ['type' => 'changed', 'text' => 'Sri Lanka date and time everywhere: a live "Sri Lanka time" clock in the header, dd/mm/yyyy, HH:mm (SLST) on all pages, and API timestamps in +05:30.'],
            ],
        ],

        [
            'version' => '0.7.0',
            'date' => '2026-11-24',
            'title' => 'Audit digital signature & maintenance mode',
            'changes' => [
                ['type' => 'added', 'text' => 'Digital signature (drawn on screen) is now required to complete a First or Second Audit; shown with signer name and time on the audit page.'],
                ['type' => 'added', 'text' => 'Settings page with a Maintenance mode switch, visible to Administrators only. While on, everyone except administrators sees a maintenance page.'],
            ],
        ],

        [
            'version' => '0.6.1',
            'date' => '2026-11-24',
            'title' => 'Easy demo logins, SMS resend, cert-ready samples',
            'changes' => [
                ['type' => 'changed', 'text' => 'All demo accounts use password “password” (admin, officers, auditors, planters).'],
                ['type' => 'added', 'text' => 'Resend step SMS buttons on planter detail for every reached lifecycle step.'],
                ['type' => 'added', 'text' => 'Demo seeds include First/Second auditor checklists in progress; both auditor roles can view each other’s status; Admin can work both.'],
                ['type' => 'added', 'text' => 'Demo seeds include pending approvals and Second-Audit-passed planters ready for certificate issue.'],
            ],
        ],

        [
            'version' => '0.6.0',
            'date' => '2026-11-24',
            'title' => 'Officer roles & step SMS notifications',
            'changes' => [
                ['type' => 'added', 'text' => 'System roles: Administrator, RRISL Officer, RDD Officer, Ministry Officer, Field Officer, First Auditor, Second Auditor (+ Planter portal).'],
                ['type' => 'changed', 'text' => 'Former Staff maps to RRISL Officer; Final Auditor renamed Second Auditor (audit round still stored as final).'],
                ['type' => 'added', 'text' => 'Notify.lk SMS on registration submit/approve/reject, First/Second audit queue & result, certificate issue/revoke.'],
                ['type' => 'added', 'text' => 'Permission matrix: Ministry read-only; Field can edit records; RDD reviews & dispatches; RRISL also manages certificates.'],
            ],
        ],

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
