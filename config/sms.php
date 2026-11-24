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
            'body' => '{brand}: Registration received. Ref: {reference}. Keep this for tracking. Permanent ID is issued on approval.',
            'placeholders' => ['brand', 'reference'],
            'sample' => [
                'brand' => 'SPPNR',
                'reference' => 'RUB/SUS/KEGA/AMIT/TMP01',
            ],
        ],

        'registration_approved' => [
            'name' => 'Registration approved',
            'trigger' => 'RRISL/RDD/Admin approves a pending application',
            'status' => 'active',
            'body' => '{brand}: Approved. Your ID: {registration_number}. Use this ID with your NIC at set-password to create a login.',
            'placeholders' => ['brand', 'registration_number'],
            'sample' => [
                'brand' => 'SPPNR',
                'registration_number' => 'RUB/SUS/KEGA/AMIT/00001',
            ],
        ],

        'registration_rejected' => [
            'name' => 'Registration rejected',
            'trigger' => 'RRISL/RDD/Admin rejects a pending application',
            'status' => 'active',
            'body' => '{brand}: Application not approved. ID: {registration_number}. Reason: {reason}',
            'placeholders' => ['brand', 'registration_number', 'reason'],
            'sample' => [
                'brand' => 'SPPNR',
                'registration_number' => 'RUB/SUS/KEGA/AMIT/00002',
                'reason' => 'Incomplete documents',
            ],
        ],

        'first_audit_queued' => [
            'name' => 'First Audit queued',
            'trigger' => 'Officer sends planter to First Audit',
            'status' => 'active',
            'body' => '{brand}: First Audit queued for {registration_number}. An auditor will contact you / visit as scheduled.',
            'placeholders' => ['brand', 'registration_number'],
            'sample' => [
                'brand' => 'SPPNR',
                'registration_number' => 'RUB/SUS/KEGA/AMIT/00001',
            ],
        ],

        'first_audit_result' => [
            'name' => 'First Audit result',
            'trigger' => 'First Auditor completes the First Audit round',
            'status' => 'active',
            'body' => '{brand}: First Audit result for {registration_number}: {outcome}. {notes}',
            'placeholders' => ['brand', 'registration_number', 'outcome', 'notes'],
            'sample' => [
                'brand' => 'SPPNR',
                'registration_number' => 'RUB/SUS/KEGA/AMIT/00001',
                'outcome' => 'PASSED',
                'notes' => 'Next: Second Audit.',
            ],
        ],

        'second_audit_queued' => [
            'name' => 'Second Audit queued',
            'trigger' => 'Officer sends planter to Second Audit (Final round)',
            'status' => 'active',
            'body' => '{brand}: Second Audit queued for {registration_number}. Please be available for the assessment.',
            'placeholders' => ['brand', 'registration_number'],
            'sample' => [
                'brand' => 'SPPNR',
                'registration_number' => 'RUB/SUS/KEGA/AMIT/00001',
            ],
        ],

        'second_audit_result' => [
            'name' => 'Second Audit result',
            'trigger' => 'Second Auditor completes the Second Audit round',
            'status' => 'active',
            'body' => '{brand}: Second Audit result for {registration_number}: {outcome}. {notes}',
            'placeholders' => ['brand', 'registration_number', 'outcome', 'notes'],
            'sample' => [
                'brand' => 'SPPNR',
                'registration_number' => 'RUB/SUS/KEGA/AMIT/00001',
                'outcome' => 'PASSED',
                'notes' => 'Certificate may be issued next.',
            ],
        ],

        'certificate_issued' => [
            'name' => 'Certificate issued',
            'trigger' => 'RRISL/Admin issues an SPPNR certificate',
            'status' => 'active',
            'body' => '{brand}: Certificate issued. No: {certificate_number}. Verify: {verify_url}',
            'placeholders' => ['brand', 'certificate_number', 'verify_url'],
            'sample' => [
                'brand' => 'SPPNR',
                'certificate_number' => 'SPPNR/CERT/2026/00001',
                'verify_url' => 'http://127.0.0.1:8000/verify',
            ],
        ],

        'certificate_revoked' => [
            'name' => 'Certificate revoked',
            'trigger' => 'RRISL/Admin revokes a certificate',
            'status' => 'active',
            'body' => '{brand}: Certificate {certificate_number} revoked. Reason: {reason}',
            'placeholders' => ['brand', 'certificate_number', 'reason'],
            'sample' => [
                'brand' => 'SPPNR',
                'certificate_number' => 'SPPNR/CERT/2026/00001',
                'reason' => 'Compliance review',
            ],
        ],

    ],

];
