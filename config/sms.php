<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SMS message templates (Notify.lk)
    |--------------------------------------------------------------------------
    |
    | Body supports {placeholders}. Keep each body ≤ 320 characters.
    | status: active = currently sent by the app; planned = catalogued only.
    |
    */

    'templates' => [

        'registration_submitted' => [
            'name' => 'Registration submitted',
            'trigger' => 'Planter completes online or offline registration',
            'status' => 'active',
            'body' => '{brand}: Your planter registration was received. Submission reference: {reference}. Keep this for tracking. A permanent number is issued on approval.',
            'placeholders' => ['brand', 'reference'],
            'sample' => [
                'brand' => 'SPPNR',
                'reference' => 'RUB/SUS/KEGA/AMIT/TMP01',
            ],
        ],

        'registration_approved' => [
            'name' => 'Registration approved',
            'trigger' => 'Staff approves a pending planter application',
            'status' => 'planned',
            'body' => '{brand}: Your registration is approved. Permanent ID: {registration_number}. Use this ID with your NIC to create a password and sign in.',
            'placeholders' => ['brand', 'registration_number'],
            'sample' => [
                'brand' => 'SPPNR',
                'registration_number' => 'RUB/SUS/KEGA/AMIT/00001',
            ],
        ],

        'registration_rejected' => [
            'name' => 'Registration rejected',
            'trigger' => 'Staff rejects a pending planter application',
            'status' => 'planned',
            'body' => '{brand}: Your registration application was not approved. Reference: {reference}. Contact RRISL for details if needed.',
            'placeholders' => ['brand', 'reference'],
            'sample' => [
                'brand' => 'SPPNR',
                'reference' => 'RUB/SUS/KEGA/AMIT/TMP01',
            ],
        ],

    ],

];
