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
