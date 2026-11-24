<?php

namespace App\Support;

use App\Http\Resources\Api\UserResource;
use App\Models\AuditChecklistItem;
use App\Models\Certificate;
use App\Models\Planter;
use App\Models\PlanterAudit;
use App\Models\PlanterAuditChecklistAnswer;
use App\Models\User;

/**
 * Content for the Developer-only API documentation page (Settings → API documentation).
 * Keep this in step with routes/api.php whenever an endpoint changes.
 */
class ApiDocumentation
{
    public static function baseUrl(): string
    {
        return rtrim(url('/api/v1'), '/');
    }

    // ---------------------------------------------------------------------
    // Overview material
    // ---------------------------------------------------------------------

    /**
     * @return array<string, mixed>
     */
    public static function overview(): array
    {
        return [
            'base_url' => self::baseUrl(),
            'headers' => [
                ['Accept', 'application/json', 'Always. Errors are then returned as JSON.'],
                ['Authorization', 'Bearer <token>', 'On every endpoint except the public ones. The token comes from a login endpoint.'],
                ['Content-Type', 'application/json or multipart/form-data', 'JSON for most calls. Use multipart only when uploading a file.'],
            ],
            'errors' => [
                [200, 'OK', 'Request succeeded.'],
                [201, 'Created', 'A record was created (registration, planter, user, certificate, audit dispatch).'],
                [401, 'Unauthenticated', '{"message":"Unauthenticated."} — missing, wrong or revoked token. Sign in again.'],
                [403, 'Forbidden', 'The account is the wrong type (planter vs staff), is inactive, or lacks the role/permission. {"message":"..."}'],
                [404, 'Not found', 'The record does not exist, or you are not allowed to know it exists.'],
                [422, 'Validation / business rule', '{"message":"...","errors":{"field":["..."]}}. Business-rule failures (for example "Complete every checklist item first") use {"message":"..."} only.'],
                [429, 'Too many requests', 'Rate limit reached. Wait for the Retry-After seconds.'],
                [503, 'Maintenance', '{"message":"...","maintenance":true} with a Retry-After header. See Maintenance mode below.'],
            ],
            'pagination' => 'List endpoints return {"data":[...],"links":{...},"meta":{"current_page","last_page","per_page","total",...}}. Use ?page=2 and ?per_page=20 (maximum 100).',
            'dates' => 'Every timestamp is ISO 8601 in Sri Lanka time (UTC+05:30, Asia/Colombo), for example 2026-11-24T12:30:23+05:30. Show it to users as dd/mm/yyyy, HH:mm (SLST).',
            'auth_steps' => [
                'Sign in with POST auth/planter/login (planters) or POST auth/staff/login (all staff roles).',
                'Store the returned token securely (Keychain / Keystore). A token expires 30 days after it was issued; when a call returns 401 Unauthenticated, sign in again. It also stops working on logout, on "sign out other devices", on password change (other devices only), or when a user account is deactivated or deleted.',
                'Send it as Authorization: Bearer <token>. Planter tokens work only on /planter/* and shared /auth/* routes; staff tokens only on /staff/*, /admin/* and shared /auth/* routes.',
                'GET auth/me returns the account and, for staff, a permissions object. Use it to show or hide screens instead of hard-coding roles.',
            ],
            'rate_limits' => 'Login and registration endpoints allow 10 requests per minute per IP. Login also locks an account/IP pair for a minute after 5 wrong passwords.',
            'maintenance' => 'When the Developer turns maintenance mode on, every endpoint returns 503 (planters and staff are locked out). Still available: GET meta (read maintenance.enabled and maintenance.message), staff login for administrators, and all calls made with an administrator or Developer token. Show the message to the user and retry later.',
            'offline' => 'Field officers and auditors work offline. Record each action locally with a unique client_id (a UUID), then upload the queue with POST staff/sync when online. Each operation is applied once; resending the same client_id is safe. Reference data (GET reference/*), audit details and planter details should be downloaded and cached while online.',
        ];
    }

    /**
     * Role → permission matrix, generated from the real permission code so it can never drift.
     *
     * @return array{columns: list<string>, rows: list<array<string, mixed>>}
     */
    public static function roleMatrix(): array
    {
        $rows = [];
        $columns = [];

        foreach (User::roles() as $role => $label) {
            $permissions = UserResource::permissions(new User(['role' => $role]));
            $columns = array_keys($permissions);
            $rows[] = ['role' => $role, 'label' => $label, 'permissions' => $permissions];
        }

        return ['columns' => $columns, 'rows' => $rows];
    }

    /**
     * Allowed values used by request and response fields.
     *
     * @return list<array{name: string, values: array<string, string>}>
     */
    public static function enums(): array
    {
        return [
            ['name' => 'Planter status (planters.status)', 'values' => Planter::statuses()],
            ['name' => 'Registration type (registration_type)', 'values' => Planter::registrationTypes()],
            ['name' => 'Audit rounds (audits.round) — "first" is shown as First Audit, "final" as Second Audit', 'values' => PlanterAudit::rounds()],
            ['name' => 'Audit status (audits.status)', 'values' => PlanterAudit::statuses()],
            ['name' => 'Audit result on the planter (audit_result_outcome)', 'values' => Planter::auditResultOutcomes()],
            ['name' => 'Checklist answer result (answers.*.result)', 'values' => PlanterAuditChecklistAnswer::results()],
            ['name' => 'Checklist modules', 'values' => AuditChecklistItem::modules()],
            ['name' => 'Certificate status', 'values' => Certificate::statuses()],
            ['name' => 'Certificate outcome', 'values' => Certificate::outcomes()],
            ['name' => 'Nature of business (business_type)', 'values' => collect(Planter::businessTypes())->map(fn (array $label) => $label['en'])->all()],
            ['name' => 'Process plan (has_process_plan)', 'values' => collect(Planter::processPlanOptions())->map(fn (array $label) => $label['en'])->all()],
            ['name' => 'User roles (users.role)', 'values' => User::roles()],
            ['name' => 'User status', 'values' => User::statuses()],
        ];
    }

    /**
     * Shape of the objects returned by several endpoints.
     *
     * @return list<array{name: string, description: string, fields: list<array{0: string, 1: string, 2: string}>}>
     */
    public static function schemas(): array
    {
        return [
            [
                'name' => 'Planter',
                'description' => 'Returned by the planter and registry endpoints.',
                'fields' => [
                    ['id', 'integer', 'Database id. Used in /staff/planters/{id}.'],
                    ['temporary_id', 'string', 'Reference given at registration, e.g. TMP-2026-000071.'],
                    ['identification_number', 'string|null', 'SPPNR ID once approved, e.g. RUB/SUS/KEGA/REDE/00001. Null before approval.'],
                    ['status', 'string', 'pending, approved or rejected.'],
                    ['status_label', 'string', 'Display text for status.'],
                    ['registration_type', 'string', 'online or offline (paper form).'],
                    ['name, nic, email, phone, whatsapp, fax', 'string|null', 'Contact details.'],
                    ['district_id, district, rdo_division_id, rdd_division', 'integer|string', 'Location ids and names.'],
                    ['farm_name, address', 'string', 'Farm details.'],
                    ['latitude, longitude, has_map_pin', 'string|bool', 'Farm map pin (Sri Lanka bounds).'],
                    ['business_type, business_type_label', 'string', 'Nature of business.'],
                    ['already_certified, certification_standard', 'bool|string|null', 'Existing certification.'],
                    ['certification_rejected_or_suspended, certification_issue_reason', 'bool|string|null', 'Past rejection or suspension.'],
                    ['crops_products', 'string[]', 'Products to be certified.'],
                    ['aware_of_certification, has_certification_leaflet', 'bool', 'Awareness answers.'],
                    ['processes_rubber_on_farm, has_process_plan', 'bool|string|null', 'On-farm processing and process plan (yes/no/na).'],
                    ['group_name, group_address', 'string|null', 'Plantation company or group.'],
                    ['rejection_reason', 'string|null', 'Why a registration was rejected.'],
                    ['audit_status, audit_result_outcome, audit_result_label, audit_result_notes', 'string|null', 'Summary of the audit journey.'],
                    ['has_application_document, has_prior_certificate_document', 'bool', 'Whether uploaded files exist (download via staff endpoints).'],
                    ['approved_at, last_login_at, created_at', 'datetime|null', 'ISO 8601, Sri Lanka time.'],
                ],
            ],
            [
                'name' => 'Audit',
                'description' => 'One audit attempt for a planter in one round.',
                'fields' => [
                    ['id, planter_id', 'integer', 'Audit and planter ids.'],
                    ['round, round_label', 'string', 'first/final and its label (First Audit / Second Audit).'],
                    ['attempt_number, is_current', 'integer|bool', 'Re-audits create a new attempt; only the current attempt can be worked on.'],
                    ['status, status_label', 'string', 'queued, in_progress, in_review, passed, conditional or failed.'],
                    ['is_open, is_complete', 'bool', 'Open = still being worked on. Complete = passed, conditional or failed.'],
                    ['outcome_notes', 'string|null', 'Auditor notes.'],
                    ['is_signed, signed_by_name, signed_at', 'bool|string|datetime', 'Digital signature details (set on completion).'],
                    ['sent_at, started_at, completed_at', 'datetime|null', 'Timeline.'],
                    ['assignee, completer, planter', 'string|object', 'Only present when loaded (list and detail responses).'],
                ],
            ],
            [
                'name' => 'Certificate',
                'description' => 'Issued after a passed Second Audit.',
                'fields' => [
                    ['id, planter_id, certificate_number', 'integer|string', 'Identity.'],
                    ['status, status_label', 'string', 'issued or revoked.'],
                    ['outcome, outcome_label', 'string', 'certified or conditional.'],
                    ['is_current', 'bool', 'The active certificate for the planter.'],
                    ['issued_at, revoked_at, revoke_reason', 'datetime|string|null', 'Lifecycle.'],
                    ['verify_url', 'string', 'Public verification page; also the QR code content.'],
                    ['planter', 'object', 'Only when loaded: id, name, identification_number, district.'],
                ],
            ],
            [
                'name' => 'User (staff)',
                'description' => 'A staff account returned by login, auth/me and admin/users.',
                'fields' => [
                    ['id, name, email, phone', 'integer|string', 'Profile.'],
                    ['role, role_label', 'string', 'Role key and display label.'],
                    ['status', 'string', 'active or inactive.'],
                    ['last_login_at', 'datetime|null', 'Last sign-in (Sri Lanka time).'],
                    ['permissions', 'object', 'Booleans such as review_registrations, work_first_audit, manage_certificates, manage_settings, view_api_docs.'],
                ],
            ],
        ];
    }

    // ---------------------------------------------------------------------
    // Endpoint catalogue
    // ---------------------------------------------------------------------

    /**
     * @return list<array<string, mixed>>
     */
    public static function sections(): array
    {
        return [
            self::referenceSection(),
            self::authSection(),
            self::registrationSection(),
            self::planterPortalSection(),
            self::staffDashboardSection(),
            self::staffPlantersSection(),
            self::staffAuditsSection(),
            self::staffCertificatesSection(),
            self::staffSmsSection(),
            self::syncSection(),
            self::adminSection(),
        ];
    }

    /**
     * Builds a cURL command from an endpoint definition.
     *
     * @param  array<string, mixed>  $endpoint
     */
    public static function curl(array $endpoint): string
    {
        $url = self::baseUrl().'/'.$endpoint['path'];
        $lines = ['curl -X '.strtoupper(explode('|', $endpoint['method'])[0]).' "'.$url.'"'];
        $lines[] = '  -H "Accept: application/json"';

        if ($endpoint['access'] !== 'public') {
            $lines[] = '  -H "Authorization: Bearer <token>"';
        }

        $request = $endpoint['request'] ?? null;

        if ($request !== null && ($endpoint['body_type'] ?? 'json') === 'multipart') {
            foreach ($request as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $item) {
                        $lines[] = '  -F "'.$key.'[]='.$item.'"';
                    }
                } elseif (is_string($value) && str_starts_with($value, '@')) {
                    $lines[] = '  -F "'.$key.'='.$value.'"';
                } else {
                    $lines[] = '  -F "'.$key.'='.(is_bool($value) ? (int) $value : $value).'"';
                }
            }
        } elseif ($request !== null) {
            $lines[] = '  -H "Content-Type: application/json"';
            $lines[] = "  -d '".json_encode($request, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."'";
        }

        return implode(" \\\n", $lines);
    }

    public static function json(mixed $value): string
    {
        return (string) json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    // ---------------------------------------------------------------------
    // Section builders
    // ---------------------------------------------------------------------

    /** @return array<string, mixed> */
    private static function referenceSection(): array
    {
        return [
            'id' => 'reference',
            'title' => 'Reference data',
            'description' => 'Public endpoints for app start-up. Download and cache them so registration forms work offline.',
            'endpoints' => [
                self::ep('GET', 'meta', 'App details and maintenance state', 'public', [
                    'description' => 'Call on every app start. If maintenance.enabled is true, show maintenance.message instead of the login screen.',
                    'response' => [
                        'app' => [
                            'name' => 'SPPNR',
                            'short_name' => 'SPPNR',
                            'full_name' => 'SUSTAINABLE PRODUCTION AND PROCESSING OF NATURAL RUBBER',
                            'version' => '0.8.0',
                            'copyright' => '...',
                            'powered_by' => '...',
                        ],
                        'governing_bodies' => [['name' => 'Rubber Development Department', 'short' => 'Rubber Development Department', 'logo_url' => 'https://example.lk/logo-rdd.png']],
                        'maintenance' => ['enabled' => false, 'message' => null],
                    ],
                ]),
                self::ep('GET', 'reference/locations', 'Districts and RDO divisions', 'public', [
                    'description' => 'Active districts, each with its Rubber Development Officer divisions. Use the ids in registration forms.',
                    'response' => ['districts' => [['id' => 1, 'name' => 'Kegalle', 'code' => 'KEGA', 'rdo_divisions' => [['id' => 25, 'name' => 'Redeegama', 'code' => 'REDE']]]]],
                ]),
                self::ep('GET', 'reference/options', 'Dropdown values', 'public', [
                    'description' => 'Business types, process-plan options, statuses, checklist results and modules, so the app never hard-codes them.',
                    'response' => [
                        'business_types' => [['value' => 'individual', 'label' => 'Individual', 'label_si' => 'තනි පුද්ගල']],
                        'process_plan_options' => [['value' => 'yes', 'label' => 'Yes', 'label_si' => 'ඔව්']],
                        'registration_types' => [['value' => 'online', 'label' => 'Online']],
                        'planter_statuses' => [['value' => 'pending', 'label' => 'Pending approval']],
                        'audit_result_outcomes' => [['value' => 'certified', 'label' => 'Certified']],
                        'checklist_results' => [['value' => 'pass', 'label' => 'Pass']],
                        'checklist_modules' => [['value' => 'psm', 'label' => 'Product Safety Module (PSM)']],
                    ],
                ]),
            ],
        ];
    }

    /** @return array<string, mixed> */
    private static function authSection(): array
    {
        return [
            'id' => 'auth',
            'title' => 'Authentication',
            'description' => 'Sign in, identify the signed-in account, sign out and change the password. Planters and staff use separate login endpoints.',
            'endpoints' => [
                self::ep('POST', 'auth/planter/login', 'Planter sign-in', 'public', [
                    'throttle' => '10 requests / minute; 5 wrong passwords lock the login for a minute',
                    'description' => 'Sign in with the SPPNR ID (e.g. RUB/SUS/KEGA/REDE/00001) or the registered email. Only approved planters who have created a password can sign in.',
                    'body' => [
                        self::p('login', 'string', true, 'SPPNR ID or email.'),
                        self::p('password', 'string', true, ''),
                        self::p('device_name', 'string', true, 'Label for this device, e.g. "Pradeep Android". Shown when listing tokens.'),
                    ],
                    'request' => ['login' => 'RUB/SUS/KEGA/REDE/00001', 'password' => 'password', 'device_name' => 'Pradeep Android'],
                    'response' => ['token' => '3|Q1b0r4n2xM9ExampleTokenValue', 'token_type' => 'Bearer', 'account_type' => 'planter', 'user' => self::planter()],
                    'errors' => [
                        [422, 'Wrong credentials; registration still pending, rejected, or no password created yet (message says which); or too many attempts.'],
                        [503, 'Maintenance mode is on.'],
                    ],
                ]),
                self::ep('POST', 'auth/staff/login', 'Staff sign-in', 'public', [
                    'throttle' => '10 requests / minute; 5 wrong passwords lock the login for a minute',
                    'description' => 'For administrators, officers, field officers and auditors. The user object includes the permissions the app should follow.',
                    'body' => [
                        self::p('email', 'string', true, ''),
                        self::p('password', 'string', true, ''),
                        self::p('device_name', 'string', true, 'Label for this device.'),
                    ],
                    'request' => ['email' => 'first.auditor@rrisl.gov.lk', 'password' => 'password', 'device_name' => 'Auditor tablet'],
                    'response' => ['token' => '4|Rk2xExampleStaffTokenValue', 'token_type' => 'Bearer', 'account_type' => 'staff', 'user' => self::user()],
                    'errors' => [
                        [422, 'Wrong credentials, inactive account, or too many attempts.'],
                        [503, 'Maintenance mode is on and the user is not an administrator.'],
                    ],
                ]),
                self::ep('GET', 'auth/me', 'Current account', 'any', [
                    'description' => 'Returns the signed-in account. account_type tells you whether it is a planter or staff member.',
                    'response' => ['account_type' => 'staff', 'user' => self::user()],
                ]),
                self::ep('POST', 'auth/logout', 'Sign out', 'any', [
                    'description' => 'Revokes the token used for this request.',
                    'response' => ['message' => 'Signed out.'],
                ]),
                self::ep('POST', 'auth/logout-others', 'Sign out other devices', 'any', [
                    'description' => 'Revokes every token except the one used for this request. Use it for a "sign out everywhere else" button. Other devices must sign in again.',
                    'response' => ['message' => 'Other devices have been signed out.', 'revoked' => 2],
                ]),
                self::ep('POST', 'auth/password', 'Change password', 'any', [
                    'description' => 'Changes the password and signs out every other device. This device stays signed in.',
                    'body' => [
                        self::p('current_password', 'string', true, ''),
                        self::p('password', 'string', true, PasswordPolicy::description()),
                        self::p('password_confirmation', 'string', true, 'Must match password.'),
                    ],
                    'request' => ['current_password' => 'password', 'password' => 'NewPassw0rd!2026', 'password_confirmation' => 'NewPassw0rd!2026'],
                    'response' => ['message' => 'Password changed. Other devices have been signed out.'],
                    'errors' => [[422, 'Current password is wrong or the new password is too weak.']],
                ]),
            ],
        ];
    }

    /** @return array<string, mixed> */
    private static function registrationSection(): array
    {
        $honeypot = (string) config('registration.honeypot_field');

        return [
            'id' => 'registration',
            'title' => 'Planter registration',
            'description' => 'Public endpoints for new planters. There is no reCAPTCHA in the API: a hidden honeypot field'
                .($honeypot !== '' ? ' (`'.$honeypot.'`)' : '').' and the rate limit protect it. Never send the honeypot field.',
            'endpoints' => [
                self::ep('POST', 'registrations', 'Register online', 'public', [
                    'throttle' => '10 requests / minute per IP',
                    'description' => 'Submits an online application. Send JSON, or multipart/form-data when attaching prior_certificate_document. The planter receives an SMS and waits for approval.',
                    'body' => [
                        self::p('name', 'string', true, 'Applicant name (max 120).'),
                        self::p('nic', 'string', true, 'National ID, unique (max 20).'),
                        self::p('email', 'string', true, 'Unique email.'),
                        self::p('phone', 'string', true, 'Mobile number (max 30).'),
                        self::p('whatsapp', 'string', false, ''),
                        self::p('fax', 'string', false, ''),
                        self::p('district_id', 'integer', true, 'From reference/locations.'),
                        self::p('rdo_division_id', 'integer', true, 'Must belong to the district.'),
                        self::p('farm_name', 'string', true, 'Max 160.'),
                        self::p('address', 'string', true, 'Farm address (max 500).'),
                        self::p('latitude', 'number', true, 'Farm pin, between 5.8 and 10.0.'),
                        self::p('longitude', 'number', true, 'Farm pin, between 79.4 and 82.1.'),
                        self::p('business_type', 'enum', true, 'See reference/options → business_types.'),
                        self::p('already_certified', 'boolean', true, ''),
                        self::p('certification_standard', 'string', false, 'Required when already_certified is true.'),
                        self::p('prior_certificate_document', 'file', false, 'Required when already_certified is true. pdf, jpg, jpeg, png or webp, up to 10 MB.'),
                        self::p('certification_rejected_or_suspended', 'boolean', true, ''),
                        self::p('certification_issue_reason', 'string', false, 'Required when certification_rejected_or_suspended is true.'),
                        self::p('crops_products', 'string[]', true, '1 to 10 products.'),
                        self::p('aware_of_certification', 'boolean', true, ''),
                        self::p('has_certification_leaflet', 'boolean', true, ''),
                        self::p('processes_rubber_on_farm', 'boolean', true, ''),
                        self::p('has_process_plan', 'enum', false, 'yes, no or na. Required when processes_rubber_on_farm is true.'),
                        self::p('group_name', 'string', false, 'Plantation company / group.'),
                        self::p('group_address', 'string', false, ''),
                    ],
                    'request' => [
                        'name' => 'Asanka Alwis', 'nic' => '900000180V', 'email' => 'asanka@example.lk', 'phone' => '0773000180',
                        'district_id' => 1, 'rdo_division_id' => 29, 'farm_name' => 'Alwis Rubber Estate', 'address' => 'Estate Junction, Kegalle',
                        'latitude' => 7.2513, 'longitude' => 80.3464, 'business_type' => 'individual',
                        'already_certified' => false, 'certification_rejected_or_suspended' => false,
                        'crops_products' => ['Rubber sheets'], 'aware_of_certification' => true, 'has_certification_leaflet' => true,
                        'processes_rubber_on_farm' => false,
                    ],
                    'status' => 201,
                    'response' => ['message' => 'Registration submitted. Please wait for approval; you will receive an SMS.', 'temporary_id' => 'TMP-2026-000181', 'status' => 'pending'],
                    'errors' => [[422, 'Validation errors (the same rules as the website form).']],
                ]),
                self::ep('POST', 'registrations/offline', 'Register from a paper form', 'public', [
                    'throttle' => '10 requests / minute per IP',
                    'description' => 'For planters who filled the paper registration form. Upload a photo or PDF of the form. Staff complete the remaining details later.',
                    'body_type' => 'multipart',
                    'body' => [
                        self::p('name', 'string', true, ''),
                        self::p('nic', 'string', true, 'Unique.'),
                        self::p('email', 'string', false, 'Unique when given.'),
                        self::p('phone', 'string', true, ''),
                        self::p('district_id', 'integer', true, ''),
                        self::p('rdo_division_id', 'integer', true, ''),
                        self::p('address', 'string', false, ''),
                        self::p('application_document', 'file', true, 'pdf, jpg, jpeg, png or webp, up to 10 MB.'),
                    ],
                    'request' => ['name' => 'Kamal Perera', 'nic' => '851234567V', 'phone' => '0712345678', 'district_id' => 1, 'rdo_division_id' => 25, 'application_document' => '@form.jpg'],
                    'status' => 201,
                    'response' => ['message' => 'Registration submitted. Please wait for approval; you will receive an SMS.', 'temporary_id' => 'TMP-2026-000182', 'status' => 'pending'],
                ]),
                self::ep('POST', 'registrations/status', 'Check registration progress', 'public', [
                    'throttle' => '10 requests / minute per IP',
                    'description' => 'Lets a planter see whether the application is approved. Needs both the reference from registration and the NIC, so one number alone reveals nothing.',
                    'body' => [
                        self::p('temporary_id', 'string', true, 'Reference returned by the registration call.'),
                        self::p('nic', 'string', true, ''),
                    ],
                    'request' => ['temporary_id' => 'TMP-2026-000071', 'nic' => '198000000071'],
                    'response' => ['temporary_id' => 'TMP-2026-000071', 'status' => 'approved', 'status_label' => 'Approved', 'identification_number' => 'RUB/SUS/KEGA/REDE/00001', 'rejection_reason' => null, 'can_set_password' => true],
                    'errors' => [[404, 'No registration matches these details.']],
                ]),
                self::ep('POST', 'registrations/password', 'Create password after approval', 'public', [
                    'throttle' => '10 requests / minute per IP',
                    'description' => 'After approval the planter creates a password once, using the SPPNR ID and NIC, then signs in.',
                    'body' => [
                        self::p('identification_number', 'string', true, 'The SPPNR ID.'),
                        self::p('nic', 'string', true, ''),
                        self::p('password', 'string', true, PasswordPolicy::description()),
                        self::p('password_confirmation', 'string', true, ''),
                    ],
                    'request' => ['identification_number' => 'RUB/SUS/KEGA/REDE/00001', 'nic' => '198000000071', 'password' => 'Passw0rd!2026', 'password_confirmation' => 'Passw0rd!2026'],
                    'response' => ['message' => 'Password created. You can now sign in with your SPPNR ID.'],
                    'errors' => [[422, 'No match, still pending, rejected, or a password already exists.']],
                ]),
            ],
        ];
    }

    /** @return array<string, mixed> */
    private static function planterPortalSection(): array
    {
        return [
            'id' => 'planter-portal',
            'title' => 'Planter portal',
            'description' => 'For signed-in planters. Planter tokens cannot call /staff/* or /admin/* endpoints.',
            'endpoints' => [
                self::ep('GET', 'planter/dashboard', 'Dashboard', 'planter', [
                    'description' => 'Everything the home screen needs: profile summary, the audit timeline, the current certificate and the QR payload (verification URL once certified, otherwise the SPPNR ID).',
                    'response' => [
                        'planter' => self::planter(),
                        'audit_timeline' => [
                            ['key' => 'registration_approved', 'label' => 'Registration approved', 'description' => 'Planter registration verified and SPPNR identification issued.', 'state' => 'completed', 'children' => []],
                            ['key' => 'first_audit', 'label' => 'First Audit', 'description' => '... Attempt #1. Status: Queued.', 'state' => 'current', 'children' => []],
                            ['key' => 'final_audit', 'label' => 'Final Audit', 'description' => '...', 'state' => 'upcoming', 'children' => []],
                            ['key' => 'audit_result', 'label' => 'Audit result', 'description' => '...', 'state' => 'upcoming', 'children' => []],
                        ],
                        'certificate' => null,
                        'qr_payload' => 'RUB/SUS/KEGA/REDE/00001',
                    ],
                    'response_note' => 'Timeline state is completed, current or upcoming. certificate is a Certificate object once issued.',
                ]),
                self::ep('GET', 'planter/profile', 'Profile', 'planter', [
                    'response' => ['planter' => self::planter()],
                ]),
                self::ep('PUT', 'planter/profile', 'Update profile', 'planter', [
                    'description' => 'Approved planters can update contact details and farm location.',
                    'body' => [
                        self::p('name', 'string', true, ''),
                        self::p('email', 'string', false, 'Unique among planters.'),
                        self::p('phone', 'string', true, ''),
                        self::p('district_id', 'integer', true, ''),
                        self::p('rdo_division_id', 'integer', true, 'Must belong to the district.'),
                        self::p('address', 'string', true, ''),
                        self::p('latitude', 'number', true, '5.8 – 10.0'),
                        self::p('longitude', 'number', true, '79.4 – 82.1'),
                    ],
                    'request' => ['name' => 'Pradeep Peiris', 'email' => 'pradeep.071@sample.lk', 'phone' => '0773000071', 'district_id' => 1, 'rdo_division_id' => 25, 'address' => 'Estate Junction, Kegalle', 'latitude' => 7.1913, 'longitude' => 80.3464],
                    'response' => ['planter' => self::planter()],
                    'errors' => [[403, 'The registration is not approved.'], [422, 'Validation errors.']],
                ]),
                self::ep('GET', 'planter/certificate', 'Current certificate', 'planter', [
                    'response' => ['certificate' => self::certificate()],
                    'errors' => [[404, 'No active certificate yet.']],
                ]),
            ],
        ];
    }

    /** @return array<string, mixed> */
    private static function staffDashboardSection(): array
    {
        return [
            'id' => 'staff-dashboard',
            'title' => 'Staff dashboard',
            'description' => 'For every staff role. Staff tokens cannot call /planter/* endpoints.',
            'endpoints' => [
                self::ep('GET', 'staff/dashboard', 'Dashboard counters', 'staff', [
                    'response' => [
                        'registrations' => ['pending' => 70, 'pending_online' => 45, 'pending_offline' => 25],
                        'registry' => ['approved' => 110, 'rejected' => 20],
                        'audits' => ['ongoing_first' => 18, 'ongoing_second' => 18, 'passed' => 99, 'failed' => 11],
                        'certificates' => ['issued' => 15, 'revoked' => 4],
                        'user' => self::user(),
                    ],
                ]),
            ],
        ];
    }

    /** @return array<string, mixed> */
    private static function staffPlantersSection(): array
    {
        $planterBody = self::planterBody();

        return [
            'id' => 'staff-planters',
            'title' => 'Staff: planters and registrations',
            'description' => 'Registration lobby, registry, approval and audit dispatch. Each action checks the role; the planter detail response lists what the current user may do in permissions.',
            'endpoints' => [
                self::ep('GET', 'staff/planters', 'List planters', 'staff', [
                    'description' => 'Defaults to approved planters. Use status=pending for the registration lobby (oldest first) or status=rejected.',
                    'query' => [
                        self::p('status', 'enum', false, 'pending, approved (default) or rejected.'),
                        self::p('q', 'string', false, 'Search name, email, NIC, phone, temporary id or SPPNR ID.'),
                        self::p('district_id', 'integer', false, ''),
                        self::p('registration_type', 'enum', false, 'online or offline.'),
                        self::p('page', 'integer', false, ''),
                        self::p('per_page', 'integer', false, 'Default 20, maximum 100.'),
                    ],
                    'response' => ['data' => [self::planter()], 'links' => ['first' => '...', 'last' => '...', 'prev' => null, 'next' => '...'], 'meta' => ['current_page' => 1, 'last_page' => 6, 'per_page' => 20, 'total' => 110]],
                ]),
                self::ep('POST', 'staff/planters', 'Create a planter', 'staff', [
                    'note' => 'Needs: Administrator, RRISL Officer, RDD Officer or Field Officer.',
                    'description' => 'Creates a record directly (for example a field officer registering a planter). Setting status to approved issues the SPPNR ID and sends the approval SMS.',
                    'body' => $planterBody,
                    'request' => [
                        'name' => 'Nimal Silva', 'nic' => '880000999V', 'email' => 'nimal@example.lk', 'phone' => '0771234567',
                        'district_id' => 1, 'rdo_division_id' => 25, 'address' => 'Hill Road, Kegalle', 'status' => 'pending',
                        'farm_name' => 'Silva Estate', 'latitude' => 7.25, 'longitude' => 80.34, 'business_type' => 'individual',
                        'already_certified' => false, 'certification_rejected_or_suspended' => false, 'crops_products' => ['Rubber sheets'],
                        'aware_of_certification' => true, 'has_certification_leaflet' => false, 'processes_rubber_on_farm' => false,
                    ],
                    'status' => 201,
                    'response' => ['planter' => self::planter()],
                    'errors' => [[403, 'Role cannot manage planter records.'], [422, 'Validation errors.']],
                ]),
                self::ep('GET', 'staff/planters/{planter}', 'Planter detail', 'staff', [
                    'description' => 'Full record, audit timeline, QR payload, the SMS steps that can be resent and what the current user may do.',
                    'response' => [
                        'planter' => self::planter(),
                        'audit_timeline' => [['key' => 'registration_approved', 'label' => 'Registration approved', 'description' => '...', 'state' => 'completed', 'children' => []]],
                        'qr_payload' => 'RUB/SUS/KEGA/REDE/00001',
                        'sms_resend_actions' => [['key' => 'registration_submitted', 'label' => 'Registration submitted'], ['key' => 'registration_approved', 'label' => 'Registration approved']],
                        'permissions' => ['can_edit' => true, 'can_delete' => true, 'can_approve' => false, 'can_reject' => false, 'can_dispatch_first_audit' => true, 'can_dispatch_second_audit' => false, 'can_issue_certificate' => false, 'can_resend_sms' => true],
                    ],
                ]),
                self::ep('PUT|PATCH', 'staff/planters/{planter}', 'Update a planter', 'staff', [
                    'note' => 'Needs: Administrator, RRISL Officer, RDD Officer or Field Officer.',
                    'description' => 'Same fields as create (NIC and email stay unique except for this planter). Changing status to approved or rejected runs the same approval/rejection steps and SMS as the website; moving back to pending clears the approval.',
                    'body' => $planterBody,
                    'response' => ['planter' => self::planter()],
                    'errors' => [[403, 'Role cannot manage planter records.'], [422, 'Validation errors.']],
                ]),
                self::ep('DELETE', 'staff/planters/{planter}', 'Delete a planter', 'staff', [
                    'note' => 'Needs: Administrator or RRISL Officer.',
                    'description' => 'Deletes the record and its uploaded documents. This cannot be undone.',
                    'response' => ['message' => 'Registry entry deleted.'],
                ]),
                self::ep('POST', 'staff/planters/{planter}/approve', 'Approve a registration', 'staff', [
                    'note' => 'Needs: Administrator, RRISL Officer or RDD Officer.',
                    'description' => 'Issues the SPPNR ID and sends the approval SMS. No body.',
                    'response' => ['planter' => self::planter()],
                ]),
                self::ep('POST', 'staff/planters/{planter}/reject', 'Reject a registration', 'staff', [
                    'note' => 'Needs: Administrator, RRISL Officer or RDD Officer.',
                    'body' => [self::p('rejection_reason', 'string', true, 'Shown to the planter (max 500).')],
                    'request' => ['rejection_reason' => 'NIC photo is not readable.'],
                    'response' => ['planter' => self::planter()],
                ]),
                self::ep('GET', 'staff/planters/{planter}/application-document', 'Download registration form', 'staff', [
                    'description' => 'Streams the uploaded paper form (pdf/image). Send the bearer token; do not put it in the URL.',
                    'binary' => true,
                    'errors' => [[404, 'No registration form on file.']],
                ]),
                self::ep('GET', 'staff/planters/{planter}/prior-certificate-document', 'Download prior certificate', 'staff', [
                    'binary' => true,
                    'errors' => [[404, 'No prior certificate on file.']],
                ]),
                self::ep('POST', 'staff/planters/{planter}/sms/resend', 'Resend a step SMS', 'staff', [
                    'note' => 'Needs: Administrator, RRISL Officer or RDD Officer.',
                    'body' => [self::p('template', 'string', true, 'A key from planter detail → sms_resend_actions.')],
                    'request' => ['template' => 'registration_approved'],
                    'response' => ['message' => 'SMS resent to 0773000071.'],
                    'errors' => [[422, 'Not an allowed step for this planter, or the SMS gateway failed.']],
                ]),
                self::ep('POST', 'staff/planters/{planter}/audits/first', 'Send to First Audit', 'staff', [
                    'note' => 'Needs: Administrator, RRISL Officer or RDD Officer.',
                    'description' => 'Queues the First Audit for an approved planter and sends the SMS.',
                    'status' => 201,
                    'response' => ['audit_id' => 12, 'round' => 'first', 'status' => 'queued'],
                    'errors' => [[422, 'The planter is not eligible (not approved, or already audited).']],
                ]),
                self::ep('POST', 'staff/planters/{planter}/audits/final', 'Send to Second Audit', 'staff', [
                    'note' => 'Needs: Administrator, RRISL Officer or RDD Officer.',
                    'description' => 'Allowed only after the First Audit passed. The API round key is "final".',
                    'status' => 201,
                    'response' => ['audit_id' => 31, 'round' => 'final', 'status' => 'queued'],
                    'errors' => [[422, 'First Audit has not passed.']],
                ]),
            ],
        ];
    }

    /** @return array<string, mixed> */
    private static function staffAuditsSection(): array
    {
        return [
            'id' => 'staff-audits',
            'title' => 'Staff: audits',
            'description' => 'The auditor workflow: start → fill the checklist (saved item by item) → submit for review → complete with outcome and digital signature. Everything here can also be queued offline through the sync endpoint.',
            'endpoints' => [
                self::ep('GET', 'staff/audits', 'List audits', 'staff', [
                    'description' => 'Defaults: round = your own round (First Auditor → first, Second Auditor → final, others → first) and bucket = ongoing.',
                    'query' => [
                        self::p('round', 'enum', false, 'first or final.'),
                        self::p('bucket', 'enum', false, 'ongoing (queued, in progress, in review), passed (passed or conditional) or rejected (failed).'),
                        self::p('q', 'string', false, 'Planter name, NIC, phone, temporary id or SPPNR ID.'),
                        self::p('district_id', 'integer', false, ''),
                        self::p('page', 'integer', false, ''),
                        self::p('per_page', 'integer', false, 'Default 20, maximum 100.'),
                    ],
                    'response' => ['data' => [self::audit()], 'links' => ['first' => '...'], 'meta' => ['current_page' => 1, 'last_page' => 1, 'per_page' => 20, 'total' => 18]],
                ]),
                self::ep('GET', 'staff/audits/{audit}', 'Audit detail', 'staff', [
                    'description' => 'The audit, the checklist grouped by module, progress, every attempt for this planter and round, and what the user may do. Cache this for offline work.',
                    'response' => self::auditDetail(),
                ]),
                self::ep('POST', 'staff/audits/{audit}/start', 'Start the audit', 'staff', [
                    'note' => 'Needs: the auditor for that round, or an Administrator.',
                    'description' => 'Moves queued → in_progress. Returns the audit detail.',
                    'response' => self::auditDetail(),
                    'errors' => [[403, 'Not allowed to work this round.'], [422, 'Only the current attempt can be started.']],
                ]),
                self::ep('PUT', 'staff/audits/{audit}/checklist', 'Save checklist answers', 'staff', [
                    'note' => 'Needs: the auditor for that round, or an Administrator.',
                    'description' => 'Send any subset of items. Keys of answers are the checklist item_id values from the audit detail. Starts the audit automatically if it is still queued.',
                    'body' => [
                        self::p('answers', 'object', true, 'Map of item_id → answer.'),
                        self::p('answers.{item_id}.result', 'enum', true, 'pending, pass, fail or na.'),
                        self::p('answers.{item_id}.comment', 'string', false, 'Max 1000.'),
                    ],
                    'request' => ['answers' => ['1' => ['result' => 'pass', 'comment' => 'Records verified.'], '2' => ['result' => 'fail', 'comment' => 'No waste log.']]],
                    'response' => self::auditDetail(),
                    'errors' => [[422, 'The attempt is not the current one, or it is already complete.']],
                ]),
                self::ep('POST', 'staff/audits/{audit}/submit', 'Submit for review', 'staff', [
                    'note' => 'Needs: the auditor for that round, or an Administrator.',
                    'description' => 'Moves in_progress → in_review. Every checklist item must be answered.',
                    'response' => self::auditDetail(),
                    'errors' => [[422, 'Complete every checklist item before submitting for review.']],
                ]),
                self::ep('POST', 'staff/audits/{audit}/complete', 'Complete with outcome and signature', 'staff', [
                    'note' => 'Needs: the auditor for that round, or an Administrator.',
                    'description' => 'Records the result and the auditor\'s digital signature, updates the planter summary and sends the result SMS. All checklist items must be answered.',
                    'body' => [
                        self::p('status', 'enum', true, 'passed, conditional or failed.'),
                        self::p('outcome_notes', 'string', false, 'Max 2000.'),
                        self::p('signature', 'string', true, 'PNG as a data URL: data:image/png;base64,.... Max about 450 KB.'),
                    ],
                    'request' => ['status' => 'passed', 'outcome_notes' => 'All requirements met.', 'signature' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUg...'],
                    'response' => self::auditDetail('passed'),
                    'errors' => [[422, 'Checklist incomplete, invalid signature image, or invalid outcome.']],
                ]),
                self::ep('POST', 'staff/audits/{audit}/reopen', 'Reopen (start a new attempt)', 'staff', [
                    'note' => 'Needs: Administrator, RRISL Officer, RDD Officer, or the auditor for that round.',
                    'description' => 'Keeps the previous result in history and creates a new queued attempt. The Second Audit must be reopened before the First.',
                    'status' => 201,
                    'response' => ['audit' => self::audit()],
                    'errors' => [[422, 'Only the current, completed attempt can be reopened.']],
                ]),
            ],
        ];
    }

    /** @return array<string, mixed> */
    private static function staffCertificatesSection(): array
    {
        return [
            'id' => 'staff-certificates',
            'title' => 'Staff: certificates',
            'description' => 'Visible to Administrators and RRISL Officers only.',
            'endpoints' => [
                self::ep('GET', 'staff/certificates', 'List certificates', 'staff', [
                    'note' => 'Needs: Administrator or RRISL Officer.',
                    'query' => [
                        self::p('status', 'enum', false, 'issued (default) or revoked.'),
                        self::p('q', 'string', false, 'Certificate number, planter name, NIC or SPPNR ID.'),
                        self::p('page', 'integer', false, ''),
                        self::p('per_page', 'integer', false, 'Default 20, maximum 100.'),
                    ],
                    'response' => ['data' => [self::certificate(true)], 'links' => ['first' => '...'], 'meta' => ['current_page' => 1, 'last_page' => 1, 'per_page' => 20, 'total' => 15]],
                ]),
                self::ep('GET', 'staff/certificates/{certificate}', 'Certificate detail', 'staff', [
                    'note' => 'Needs: Administrator or RRISL Officer.',
                    'response' => ['certificate' => self::certificate(true)],
                ]),
                self::ep('POST', 'staff/planters/{planter}/certificates', 'Issue a certificate', 'staff', [
                    'note' => 'Needs: Administrator or RRISL Officer.',
                    'description' => 'Allowed when the planter is approved, the current Second Audit is passed or conditional, and no active certificate exists. Sends the certificate SMS.',
                    'body' => [self::p('notes', 'string', false, 'Internal notes (max 2000).')],
                    'request' => ['notes' => 'Issued at the board meeting.'],
                    'status' => 201,
                    'response' => ['certificate' => self::certificate(true)],
                    'errors' => [[422, 'The planter is not eligible, or already holds an active certificate.']],
                ]),
                self::ep('POST', 'staff/certificates/{certificate}/revoke', 'Revoke a certificate', 'staff', [
                    'note' => 'Needs: Administrator or RRISL Officer.',
                    'body' => [self::p('revoke_reason', 'string', true, 'Shown on the verification page (max 2000).')],
                    'request' => ['revoke_reason' => 'Non-compliance found during a spot check.'],
                    'response' => ['certificate' => self::certificate(true, revoked: true)],
                ]),
            ],
        ];
    }

    /** @return array<string, mixed> */
    private static function staffSmsSection(): array
    {
        return [
            'id' => 'staff-sms',
            'title' => 'Staff: SMS templates',
            'description' => 'Read-only template catalogue and sample sending.',
            'endpoints' => [
                self::ep('GET', 'staff/sms-templates', 'List SMS templates', 'staff', [
                    'response' => [
                        'templates' => [['key' => 'registration_approved', 'name' => 'Registration approved', 'trigger' => 'When a registration is approved', 'status' => 'active', 'body' => 'Your SPPNR registration is approved...', 'sample' => '...', 'placeholders' => ['name', 'identification_number'], 'length' => 120]],
                        'gateway_enabled' => true,
                        'sender_id' => 'RRISL',
                    ],
                ]),
                self::ep('POST', 'staff/sms-templates/send-samples', 'Send sample SMS', 'staff', [
                    'note' => 'Needs: Administrator or RRISL Officer.',
                    'description' => 'Sends one template (template given) or every template (omitted) to a phone number, filled with sample data.',
                    'body' => [
                        self::p('phone', 'string', true, ''),
                        self::p('template', 'string', false, 'A template key. Omit to send all.'),
                    ],
                    'request' => ['phone' => '0771234567', 'template' => 'registration_approved'],
                    'response' => ['message' => 'Sample SMS sent.'],
                    'errors' => [[422, 'The gateway rejected the message.']],
                ]),
            ],
        ];
    }

    /** @return array<string, mixed> */
    private static function syncSection(): array
    {
        return [
            'id' => 'sync',
            'title' => 'Offline sync',
            'description' => 'Upload the offline queue in one call. Operations run in the order sent, each on its own: one failure never blocks the rest. Use a fresh UUID as client_id for every recorded action.',
            'endpoints' => [
                self::ep('POST', 'staff/sync', 'Upload offline operations', 'staff', [
                    'description' => 'Up to 100 operations per call. Supported types and payloads are listed below the example. Results come back in the same order. For paper-form registrations with an attached file use POST registrations/offline directly while online.',
                    'body' => [
                        self::p('operations', 'array', true, '1–100 operations.'),
                        self::p('operations[].client_id', 'string', true, 'Unique per action (max 120). Idempotency key.'),
                        self::p('operations[].type', 'string', true, 'See the table below.'),
                        self::p('operations[].payload', 'object', false, 'Fields for that type.'),
                    ],
                    'request' => [
                        'operations' => [
                            ['client_id' => '7b1e7c52-4d0a-4a6f-9b31-0c5ad0c1e001', 'type' => 'audit.start', 'payload' => ['audit_id' => 12]],
                            ['client_id' => '7b1e7c52-4d0a-4a6f-9b31-0c5ad0c1e002', 'type' => 'audit.checklist', 'payload' => ['audit_id' => 12, 'answers' => ['1' => ['result' => 'pass', 'comment' => 'OK']]]],
                            ['client_id' => '7b1e7c52-4d0a-4a6f-9b31-0c5ad0c1e003', 'type' => 'audit.submit', 'payload' => ['audit_id' => 12]],
                        ],
                    ],
                    'response' => [
                        'results' => [
                            ['client_id' => '7b1e7c52-4d0a-4a6f-9b31-0c5ad0c1e001', 'type' => 'audit.start', 'status' => 'applied', 'replayed' => false, 'data' => ['audit_id' => 12, 'planter_id' => 71, 'round' => 'first', 'status' => 'in_progress', 'is_complete' => false]],
                            ['client_id' => '7b1e7c52-4d0a-4a6f-9b31-0c5ad0c1e002', 'type' => 'audit.checklist', 'status' => 'applied', 'replayed' => false, 'data' => ['audit_id' => 12, 'status' => 'in_progress']],
                            ['client_id' => '7b1e7c52-4d0a-4a6f-9b31-0c5ad0c1e003', 'type' => 'audit.submit', 'status' => 'rejected', 'message' => 'Complete every checklist item before submitting for review.'],
                        ],
                    ],
                    'sync_types' => [
                        ['planter.create', 'Same fields as POST staff/planters.', 'Administrator, RRISL Officer, RDD Officer, Field Officer'],
                        ['planter.update', 'planter_id + the fields of PUT staff/planters/{planter}.', 'Administrator, RRISL Officer, RDD Officer, Field Officer'],
                        ['audit.start', 'audit_id.', 'Auditor for that round, Administrator'],
                        ['audit.checklist', 'audit_id + answers (same as PUT checklist).', 'Auditor for that round, Administrator'],
                        ['audit.submit', 'audit_id.', 'Auditor for that round, Administrator'],
                        ['audit.complete', 'audit_id + status, outcome_notes, signature (same as POST complete).', 'Auditor for that round, Administrator'],
                    ],
                    'sync_statuses' => [
                        ['applied', 'Saved. replayed:true means this client_id was already applied earlier and nothing changed. Remove it from the queue.'],
                        ['rejected', 'Invalid or not allowed (errors or message explain). Do not resend unchanged: show the problem to the user, fix the data, then queue a new operation with a new client_id.'],
                        ['failed', 'Temporary server error. Keep it queued and resend the same client_id later.'],
                    ],
                ]),
            ],
        ];
    }

    /** @return array<string, mixed> */
    private static function adminSection(): array
    {
        return [
            'id' => 'admin',
            'title' => 'Administration',
            'description' => 'User management is for Administrators and the Developer. The maintenance switch is Developer only, like the Settings page. Administrators cannot see or change Developer accounts.',
            'endpoints' => [
                self::ep('GET', 'admin/users', 'List users', 'admin', [
                    'query' => [
                        self::p('q', 'string', false, 'Name, email or phone.'),
                        self::p('role', 'enum', false, 'A role key.'),
                        self::p('status', 'enum', false, 'active or inactive.'),
                        self::p('page', 'integer', false, ''),
                        self::p('per_page', 'integer', false, 'Default 20, maximum 100.'),
                    ],
                    'response' => ['data' => [self::user()], 'links' => ['first' => '...'], 'meta' => ['current_page' => 1, 'last_page' => 1, 'per_page' => 20, 'total' => 25]],
                ]),
                self::ep('POST', 'admin/users', 'Create a user', 'admin', [
                    'body' => [
                        self::p('name', 'string', true, ''),
                        self::p('email', 'string', true, 'Unique.'),
                        self::p('phone', 'string', false, ''),
                        self::p('role', 'enum', true, 'A role key. Only the Developer may assign developer.'),
                        self::p('status', 'enum', true, 'active or inactive.'),
                        self::p('password', 'string', true, PasswordPolicy::description()),
                        self::p('password_confirmation', 'string', true, ''),
                    ],
                    'request' => ['name' => 'Sunil Fernando', 'email' => 'sunil@rrisl.gov.lk', 'phone' => '0112600999', 'role' => 'field_officer', 'status' => 'active', 'password' => 'Passw0rd!2026', 'password_confirmation' => 'Passw0rd!2026'],
                    'status' => 201,
                    'response' => ['user' => self::user()],
                ]),
                self::ep('PUT', 'admin/users/{user}', 'Update a user', 'admin', [
                    'description' => 'Same fields as create; leave password empty to keep it. Deactivating a user signs them out of every device. The last active administrator cannot be demoted or deactivated, and you cannot deactivate yourself.',
                    'response' => ['user' => self::user()],
                    'errors' => [[403, 'Target is a Developer account and you are not the Developer.'], [422, 'Validation or last-administrator rule.']],
                ]),
                self::ep('DELETE', 'admin/users/{user}', 'Delete a user', 'admin', [
                    'description' => 'Removes the account and its tokens. You cannot delete yourself or the last administrator.',
                    'response' => ['message' => 'User account deleted.'],
                ]),
                self::ep('GET', 'admin/maintenance', 'Read maintenance mode', 'developer', [
                    'response' => ['enabled' => false, 'message' => 'Upgrading the system. Back at 6 PM.'],
                ]),
                self::ep('PUT', 'admin/maintenance', 'Switch maintenance mode', 'developer', [
                    'description' => 'The same switch as Settings → Maintenance mode. While on, only administrators and the Developer can use the system.',
                    'body' => [
                        self::p('enabled', 'boolean', true, ''),
                        self::p('message', 'string', false, 'Shown to everyone else (max 500).'),
                    ],
                    'request' => ['enabled' => true, 'message' => 'Upgrading the system. Back at 6 PM.'],
                    'response' => ['enabled' => true, 'message' => 'Upgrading the system. Back at 6 PM.'],
                ]),
            ],
        ];
    }

    // ---------------------------------------------------------------------
    // Builders and shared examples
    // ---------------------------------------------------------------------

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private static function ep(string $method, string $path, string $title, string $access, array $options = []): array
    {
        return array_merge([
            'method' => $method,
            'path' => $path,
            'title' => $title,
            'access' => $access,
            'description' => null,
            'note' => null,
            'throttle' => null,
            'query' => [],
            'body' => [],
            'body_type' => 'json',
            'request' => null,
            'status' => 200,
            'response' => null,
            'response_note' => null,
            'binary' => false,
            'errors' => [],
            'sync_types' => [],
            'sync_statuses' => [],
        ], $options);
    }

    /**
     * @return array{name: string, type: string, required: bool, description: string}
     */
    private static function p(string $name, string $type, bool $required, string $description): array
    {
        return ['name' => $name, 'type' => $type, 'required' => $required, 'description' => $description];
    }

    /**
     * @return list<array{name: string, type: string, required: bool, description: string}>
     */
    private static function planterBody(): array
    {
        return [
            self::p('name', 'string', true, 'Max 120.'),
            self::p('nic', 'string', true, 'Unique (max 20).'),
            self::p('email', 'string', true, 'Unique.'),
            self::p('phone', 'string', true, ''),
            self::p('district_id', 'integer', true, ''),
            self::p('rdo_division_id', 'integer', true, 'Must belong to the district.'),
            self::p('address', 'string', true, ''),
            self::p('status', 'enum', true, 'pending, approved or rejected.'),
            self::p('rejection_reason', 'string', false, 'Required when status is rejected.'),
            self::p('password', 'string', false, 'Optional initial password with password_confirmation. '.PasswordPolicy::description()),
            self::p('latitude', 'number', true, '5.8 – 10.0'),
            self::p('longitude', 'number', true, '79.4 – 82.1'),
            self::p('farm_name', 'string', false, ''),
            self::p('whatsapp', 'string', false, ''),
            self::p('fax', 'string', false, ''),
            self::p('business_type', 'enum', false, 'See reference/options.'),
            self::p('already_certified', 'boolean', false, ''),
            self::p('certification_standard', 'string', false, 'Required when already_certified is true.'),
            self::p('certification_rejected_or_suspended', 'boolean', false, ''),
            self::p('certification_issue_reason', 'string', false, 'Required when certification_rejected_or_suspended is true.'),
            self::p('crops_products', 'string[]', false, 'Up to 10.'),
            self::p('aware_of_certification', 'boolean', false, ''),
            self::p('has_certification_leaflet', 'boolean', false, ''),
            self::p('processes_rubber_on_farm', 'boolean', false, ''),
            self::p('has_process_plan', 'enum', false, 'yes, no or na. Required when processes_rubber_on_farm is true.'),
            self::p('group_name', 'string', false, ''),
            self::p('group_address', 'string', false, ''),
        ];
    }

    /** @return array<string, mixed> */
    private static function planter(): array
    {
        return [
            'id' => 71,
            'temporary_id' => 'TMP-2026-000071',
            'identification_number' => 'RUB/SUS/KEGA/REDE/00001',
            'status' => 'approved',
            'status_label' => 'Approved',
            'registration_type' => 'online',
            'name' => 'Pradeep Peiris',
            'nic' => '198000000071',
            'email' => 'pradeep.071@sample.lk',
            'phone' => '0773000071',
            'whatsapp' => '0773000071',
            'fax' => null,
            'district_id' => 1,
            'district' => 'Kegalle',
            'rdo_division_id' => 25,
            'rdd_division' => 'Redeegama',
            'farm_name' => 'Pradeep Peiris Rubber Estate',
            'address' => 'Estate Junction, Kegalle',
            'latitude' => '7.1913000',
            'longitude' => '80.3464000',
            'has_map_pin' => true,
            'business_type' => 'individual',
            'business_type_label' => 'Individual',
            'already_certified' => false,
            'certification_standard' => null,
            'certification_rejected_or_suspended' => false,
            'certification_issue_reason' => null,
            'crops_products' => ['Rubber sheets'],
            'aware_of_certification' => true,
            'has_certification_leaflet' => true,
            'processes_rubber_on_farm' => false,
            'has_process_plan' => null,
            'group_name' => null,
            'group_address' => null,
            'rejection_reason' => null,
            'audit_status' => 'not_started',
            'audit_result_outcome' => null,
            'audit_result_label' => null,
            'audit_result_notes' => null,
            'has_application_document' => false,
            'has_prior_certificate_document' => false,
            'approved_at' => '2026-11-23T09:15:00+05:30',
            'last_login_at' => '2026-11-24T12:30:23+05:30',
            'created_at' => '2026-11-22T10:02:41+05:30',
        ];
    }

    /** @return array<string, mixed> */
    private static function user(): array
    {
        return [
            'id' => 5,
            'name' => 'First Auditor',
            'email' => 'first.auditor@rrisl.gov.lk',
            'phone' => '0112600003',
            'role' => 'first_auditor',
            'role_label' => 'First Auditor',
            'status' => 'active',
            'last_login_at' => '2026-11-24T12:30:23+05:30',
            'permissions' => [
                'view_registry' => true, 'review_registrations' => false, 'manage_planter_records' => false, 'delete_planters' => false,
                'dispatch_audits' => false, 'work_first_audit' => true, 'work_second_audit' => false, 'view_audits' => true,
                'manage_certificates' => false, 'resend_planter_sms' => false, 'send_sms_samples' => false, 'manage_master_data' => false,
                'manage_users' => false, 'manage_settings' => false, 'view_api_docs' => false,
            ],
        ];
    }

    /** @return array<string, mixed> */
    private static function audit(string $status = 'queued'): array
    {
        $done = in_array($status, ['passed', 'conditional', 'failed'], true);

        return [
            'id' => 12,
            'planter_id' => 71,
            'round' => 'first',
            'round_label' => 'First Audit',
            'attempt_number' => 1,
            'is_current' => true,
            'status' => $status,
            'status_label' => ucfirst($status),
            'is_open' => ! $done,
            'is_complete' => $done,
            'outcome_notes' => $done ? 'All requirements met.' : null,
            'is_signed' => $done,
            'signed_by_name' => $done ? 'First Auditor' : null,
            'signed_at' => $done ? '2026-11-24T14:05:10+05:30' : null,
            'sent_at' => '2026-11-23T19:51:27+05:30',
            'started_at' => $status === 'queued' ? null : '2026-11-24T10:00:00+05:30',
            'completed_at' => $done ? '2026-11-24T14:05:10+05:30' : null,
            'assignee' => null,
            'completer' => $done ? 'First Auditor' : null,
            'planter' => ['id' => 71, 'name' => 'Pradeep Peiris', 'identification_number' => 'RUB/SUS/KEGA/REDE/00001', 'temporary_id' => 'TMP-2026-000071', 'district' => 'Kegalle', 'phone' => '0773000071'],
        ];
    }

    /** @return array<string, mixed> */
    private static function auditDetail(string $status = 'in_progress'): array
    {
        $done = $status === 'passed';

        return [
            'audit' => self::audit($status),
            'signature' => $done ? 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUg...' : null,
            'progress' => ['total' => 15, 'answered' => $done ? 15 : 3, 'percent' => $done ? 100 : 20],
            'checklist' => [[
                'module' => 'psm',
                'label' => 'Product Safety Module (PSM)',
                'items' => [[
                    'answer_id' => 101,
                    'item_id' => 1,
                    'code' => 'PSM-01',
                    'label' => 'Approved crop protection products only',
                    'description' => 'Confirm only approved products are used on the plantation.',
                    'result' => 'pass',
                    'comment' => 'Records verified.',
                ]],
            ]],
            'attempts' => [[
                'id' => 12, 'attempt_number' => 1, 'is_current' => true, 'status' => $status, 'status_label' => ucfirst($status),
                'outcome_notes' => null, 'completed_at' => null, 'completed_by' => null,
            ]],
            'permissions' => ['can_work' => ! $done, 'can_dispatch' => false],
        ];
    }

    /** @return array<string, mixed> */
    private static function certificate(bool $withPlanter = false, bool $revoked = false): array
    {
        $certificate = [
            'id' => 4,
            'planter_id' => 71,
            'certificate_number' => 'SPPNR/CERT/2026/00004',
            'status' => $revoked ? 'revoked' : 'issued',
            'status_label' => $revoked ? 'Revoked' : 'Issued',
            'outcome' => 'certified',
            'outcome_label' => 'Certified',
            'is_current' => ! $revoked,
            'issued_at' => '2026-11-24T15:00:00+05:30',
            'revoked_at' => $revoked ? '2026-11-25T09:30:00+05:30' : null,
            'revoke_reason' => $revoked ? 'Non-compliance found during a spot check.' : null,
            'verify_url' => 'https://example.lk/verify/1f0c9b2e7a',
        ];

        if ($withPlanter) {
            $certificate['planter'] = ['id' => 71, 'name' => 'Pradeep Peiris', 'identification_number' => 'RUB/SUS/KEGA/REDE/00001', 'district' => 'Kegalle'];
        }

        return $certificate;
    }
}
