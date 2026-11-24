# SCSNR / SPPNR mobile API (v1) documentation

Generated from `app/Support/ApiDocumentation.php` on 2026-11-24 21:54. Copy this file as-is.

## Overview

### Base Url

https://scsnr.olexto.com/api/v1

### Headers

```json
[
    "Accept",
    "application/json",
    "Always. Errors are then returned as JSON."
]
```
```json
[
    "Authorization",
    "Bearer <token>",
    "On every endpoint except the public ones. The token comes from a login endpoint."
]
```
```json
[
    "Content-Type",
    "application/json or multipart/form-data",
    "JSON for most calls. Use multipart only when uploading a file."
]
```

### Errors

```json
[
    200,
    "OK",
    "Request succeeded."
]
```
```json
[
    201,
    "Created",
    "A record was created (registration, planter, user, certificate, audit dispatch)."
]
```
```json
[
    401,
    "Unauthenticated",
    "{\"message\":\"Unauthenticated.\"} — missing, wrong or revoked token. Sign in again."
]
```
```json
[
    403,
    "Forbidden",
    "The account is the wrong type (planter vs staff), is inactive, or lacks the role/permission. {\"message\":\"...\"}"
]
```
```json
[
    404,
    "Not found",
    "The record does not exist, or you are not allowed to know it exists."
]
```
```json
[
    422,
    "Validation / business rule",
    "{\"message\":\"...\",\"errors\":{\"field\":[\"...\"]}}. Business-rule failures (for example \"Complete every checklist item first\") use {\"message\":\"...\"} only."
]
```
```json
[
    429,
    "Too many requests",
    "Rate limit reached. Wait for the Retry-After seconds."
]
```
```json
[
    503,
    "Maintenance",
    "{\"message\":\"...\",\"maintenance\":true} with a Retry-After header. See Maintenance mode below."
]
```

### Pagination

List endpoints return {"data":[...],"links":{...},"meta":{"current_page","last_page","per_page","total",...}}. Use ?page=2 and ?per_page=20 (maximum 100).

### Dates

Every timestamp is ISO 8601 in Sri Lanka time (UTC+05:30, Asia/Colombo), for example 2026-11-24T12:30:23+05:30. Show it to users as dd/mm/yyyy, HH:mm (SLST).

### Auth Steps

- Sign in with POST auth/planter/login (planters) or POST auth/staff/login (all staff roles).
- Store the returned token securely (Keychain / Keystore). A token expires 30 days after it was issued; when a call returns 401 Unauthenticated, sign in again. It also stops working on logout, on "sign out other devices", on password change (other devices only), or when a user account is deactivated or deleted.
- Send it as Authorization: Bearer <token>. Planter tokens work only on /planter/* and shared /auth/* routes; staff tokens only on /staff/*, /admin/* and shared /auth/* routes.
- GET auth/me returns the account and, for staff, a permissions object. Use it to show or hide screens instead of hard-coding roles.

### Rate Limits

Login and registration endpoints allow 10 requests per minute per IP. Login also locks an account/IP pair for a minute after 5 wrong passwords.

### Maintenance

When the Developer turns maintenance mode on, every endpoint returns 503 (planters and staff are locked out). Still available: GET meta (read maintenance.enabled and maintenance.message), staff login for administrators, and all calls made with an administrator or Developer token. Show the message to the user and retry later.

### Offline

Field officers and auditors work offline. Record each action locally with a unique client_id (a UUID), then upload the queue with POST staff/sync when online. Each operation is applied once; resending the same client_id is safe. Reference data (GET reference/*), audit details and planter details should be downloaded and cached while online.

## Roles and access

```json
{
    "columns": [
        "view_registry",
        "review_registrations",
        "manage_planter_records",
        "delete_planters",
        "dispatch_audits",
        "work_first_audit",
        "work_second_audit",
        "view_audits",
        "manage_certificates",
        "resend_planter_sms",
        "send_sms_samples",
        "manage_master_data",
        "manage_users",
        "manage_settings",
        "view_api_docs"
    ],
    "rows": [
        {
            "role": "developer",
            "label": "Super Admin (Developer)",
            "permissions": {
                "view_registry": true,
                "review_registrations": true,
                "manage_planter_records": true,
                "delete_planters": true,
                "dispatch_audits": true,
                "work_first_audit": true,
                "work_second_audit": true,
                "view_audits": true,
                "manage_certificates": true,
                "resend_planter_sms": true,
                "send_sms_samples": true,
                "manage_master_data": true,
                "manage_users": true,
                "manage_settings": true,
                "view_api_docs": true
            }
        },
        {
            "role": "admin",
            "label": "Administrator",
            "permissions": {
                "view_registry": true,
                "review_registrations": true,
                "manage_planter_records": true,
                "delete_planters": true,
                "dispatch_audits": true,
                "work_first_audit": true,
                "work_second_audit": true,
                "view_audits": true,
                "manage_certificates": true,
                "resend_planter_sms": true,
                "send_sms_samples": true,
                "manage_master_data": true,
                "manage_users": true,
                "manage_settings": false,
                "view_api_docs": false
            }
        },
        {
            "role": "rrisl_officer",
            "label": "RRISL Officer",
            "permissions": {
                "view_registry": true,
                "review_registrations": true,
                "manage_planter_records": true,
                "delete_planters": true,
                "dispatch_audits": true,
                "work_first_audit": false,
                "work_second_audit": false,
                "view_audits": true,
                "manage_certificates": true,
                "resend_planter_sms": true,
                "send_sms_samples": true,
                "manage_master_data": false,
                "manage_users": false,
                "manage_settings": false,
                "view_api_docs": false
            }
        },
        {
            "role": "rdd_officer",
            "label": "RDD Officer",
            "permissions": {
                "view_registry": true,
                "review_registrations": true,
                "manage_planter_records": true,
                "delete_planters": false,
                "dispatch_audits": true,
                "work_first_audit": false,
                "work_second_audit": false,
                "view_audits": true,
                "manage_certificates": false,
                "resend_planter_sms": true,
                "send_sms_samples": false,
                "manage_master_data": false,
                "manage_users": false,
                "manage_settings": false,
                "view_api_docs": false
            }
        },
        {
            "role": "ministry_officer",
            "label": "Ministry Officer",
            "permissions": {
                "view_registry": true,
                "review_registrations": false,
                "manage_planter_records": false,
                "delete_planters": false,
                "dispatch_audits": false,
                "work_first_audit": false,
                "work_second_audit": false,
                "view_audits": true,
                "manage_certificates": false,
                "resend_planter_sms": false,
                "send_sms_samples": false,
                "manage_master_data": false,
                "manage_users": false,
                "manage_settings": false,
                "view_api_docs": false
            }
        },
        {
            "role": "field_officer",
            "label": "Field Officer",
            "permissions": {
                "view_registry": true,
                "review_registrations": false,
                "manage_planter_records": true,
                "delete_planters": false,
                "dispatch_audits": false,
                "work_first_audit": false,
                "work_second_audit": false,
                "view_audits": true,
                "manage_certificates": false,
                "resend_planter_sms": false,
                "send_sms_samples": false,
                "manage_master_data": false,
                "manage_users": false,
                "manage_settings": false,
                "view_api_docs": false
            }
        },
        {
            "role": "first_auditor",
            "label": "First Auditor",
            "permissions": {
                "view_registry": true,
                "review_registrations": false,
                "manage_planter_records": false,
                "delete_planters": false,
                "dispatch_audits": false,
                "work_first_audit": true,
                "work_second_audit": false,
                "view_audits": true,
                "manage_certificates": false,
                "resend_planter_sms": false,
                "send_sms_samples": false,
                "manage_master_data": false,
                "manage_users": false,
                "manage_settings": false,
                "view_api_docs": false
            }
        },
        {
            "role": "second_auditor",
            "label": "Second Auditor",
            "permissions": {
                "view_registry": true,
                "review_registrations": false,
                "manage_planter_records": false,
                "delete_planters": false,
                "dispatch_audits": false,
                "work_first_audit": false,
                "work_second_audit": true,
                "view_audits": true,
                "manage_certificates": false,
                "resend_planter_sms": false,
                "send_sms_samples": false,
                "manage_master_data": false,
                "manage_users": false,
                "manage_settings": false,
                "view_api_docs": false
            }
        }
    ]
}
```

## Enumerations

### 0

```json
{
    "name": "Planter status (planters.status)",
    "values": {
        "pending": "Pending approval",
        "approved": "Approved",
        "rejected": "Rejected"
    }
}
```

### 1

```json
{
    "name": "Registration type (registration_type)",
    "values": {
        "online": "Online",
        "offline": "Offline"
    }
}
```

### 2

```json
{
    "name": "Audit rounds (audits.round) — \"first\" is shown as First Audit, \"final\" as Second Audit",
    "values": {
        "first": "First Audit",
        "final": "Second Audit"
    }
}
```

### 3

```json
{
    "name": "Audit status (audits.status)",
    "values": {
        "queued": "Queued",
        "in_progress": "In progress",
        "in_review": "In review",
        "passed": "Passed",
        "conditional": "Conditional",
        "failed": "Failed"
    }
}
```

### 4

```json
{
    "name": "Audit result on the planter (audit_result_outcome)",
    "values": {
        "certified": "Certified",
        "conditional": "Conditional certification",
        "not_certified": "Not certified"
    }
}
```

### 5

```json
{
    "name": "Checklist answer result (answers.*.result)",
    "values": {
        "pending": "Pending",
        "pass": "Pass",
        "fail": "Fail",
        "na": "N/A"
    }
}
```

### 6

```json
{
    "name": "Checklist modules",
    "values": {
        "psm": "Product Safety Module (PSM)",
        "emm": "Environmental Management (EMM)",
        "whswm": "Worker Health & Welfare (WHSWM)",
        "pmm": "Product Marketing (PMM)",
        "grm": "General Requirements (GRM)"
    }
}
```

### 7

```json
{
    "name": "Certificate status",
    "values": {
        "issued": "Issued",
        "revoked": "Revoked"
    }
}
```

### 8

```json
{
    "name": "Certificate outcome",
    "values": {
        "certified": "Certified",
        "conditional": "Conditional certification"
    }
}
```

### 9

```json
{
    "name": "Nature of business (business_type)",
    "values": {
        "individual": "Individual",
        "partnership": "Partnership",
        "company": "Company",
        "limited": "Limited",
        "society": "Society",
        "other": "Other"
    }
}
```

### 10

```json
{
    "name": "Process plan (has_process_plan)",
    "values": {
        "yes": "Yes",
        "no": "No",
        "na": "Not applicable"
    }
}
```

### 11

```json
{
    "name": "User roles (users.role)",
    "values": {
        "developer": "Super Admin (Developer)",
        "admin": "Administrator",
        "rrisl_officer": "RRISL Officer",
        "rdd_officer": "RDD Officer",
        "ministry_officer": "Ministry Officer",
        "field_officer": "Field Officer",
        "first_auditor": "First Auditor",
        "second_auditor": "Second Auditor"
    }
}
```

### 12

```json
{
    "name": "User status",
    "values": {
        "active": "Active",
        "inactive": "Inactive"
    }
}
```

## Schemas

### 0

```json
{
    "name": "Planter",
    "description": "Returned by the planter and registry endpoints.",
    "fields": [
        [
            "id",
            "integer",
            "Database id. Used in /staff/planters/{id}."
        ],
        [
            "temporary_id",
            "string",
            "Reference given at registration, e.g. TMP-2026-000071."
        ],
        [
            "identification_number",
            "string|null",
            "SPPNR ID once approved, e.g. RUB/SUS/KEGA/REDE/00001. Null before approval."
        ],
        [
            "status",
            "string",
            "pending, approved or rejected."
        ],
        [
            "status_label",
            "string",
            "Display text for status."
        ],
        [
            "registration_type",
            "string",
            "online or offline (paper form)."
        ],
        [
            "name, nic, email, phone, whatsapp, fax",
            "string|null",
            "Contact details."
        ],
        [
            "district_id, district, rdo_division_id, rdd_division",
            "integer|string",
            "Location ids and names."
        ],
        [
            "farm_name, address",
            "string",
            "Farm details."
        ],
        [
            "latitude, longitude, has_map_pin",
            "string|bool",
            "Farm map pin (Sri Lanka bounds)."
        ],
        [
            "registration_category",
            "string|null",
            "solo, group or company (colour-coded in the admin: sky, amber, violet)."
        ],
        [
            "business_type, business_type_label",
            "string",
            "Nature of business."
        ],
        [
            "already_certified, certification_standard",
            "bool|string|null",
            "Existing certification."
        ],
        [
            "certification_rejected_or_suspended, certification_issue_reason",
            "bool|string|null",
            "Past rejection or suspension."
        ],
        [
            "crops_products",
            "string[]",
            "Products to be certified."
        ],
        [
            "aware_of_certification, has_certification_leaflet",
            "bool",
            "Awareness answers."
        ],
        [
            "processes_rubber_on_farm, has_process_plan",
            "bool|string|null",
            "On-farm processing and process plan (yes/no/na)."
        ],
        [
            "group_name, group_address",
            "string|null",
            "Plantation company or group."
        ],
        [
            "rejection_reason",
            "string|null",
            "Why a registration was rejected."
        ],
        [
            "audit_status, audit_result_outcome, audit_result_label, audit_result_notes",
            "string|null",
            "Summary of the audit journey."
        ],
        [
            "has_application_document, has_prior_certificate_document",
            "bool",
            "Whether uploaded files exist (download via staff endpoints)."
        ],
        [
            "approved_at, last_login_at, created_at",
            "datetime|null",
            "ISO 8601, Sri Lanka time."
        ]
    ]
}
```

### 1

```json
{
    "name": "Audit",
    "description": "One audit attempt for a planter in one round.",
    "fields": [
        [
            "id, planter_id",
            "integer",
            "Audit and planter ids."
        ],
        [
            "round, round_label",
            "string",
            "first/final and its label (First Audit / Second Audit)."
        ],
        [
            "attempt_number, is_current",
            "integer|bool",
            "Re-audits create a new attempt; only the current attempt can be worked on."
        ],
        [
            "status, status_label",
            "string",
            "queued, in_progress, in_review, passed, conditional or failed."
        ],
        [
            "is_open, is_complete",
            "bool",
            "Open = still being worked on. Complete = passed, conditional or failed."
        ],
        [
            "outcome_notes",
            "string|null",
            "Auditor notes."
        ],
        [
            "is_signed, signed_by_name, signed_at",
            "bool|string|datetime",
            "Digital signature details (set on completion)."
        ],
        [
            "sent_at, started_at, completed_at",
            "datetime|null",
            "Timeline."
        ],
        [
            "assignee, completer, planter",
            "string|object",
            "Only present when loaded (list and detail responses)."
        ]
    ]
}
```

### 2

```json
{
    "name": "Certificate",
    "description": "Issued after a passed Second Audit.",
    "fields": [
        [
            "id, planter_id, certificate_number",
            "integer|string",
            "Identity."
        ],
        [
            "status, status_label",
            "string",
            "issued or revoked."
        ],
        [
            "outcome, outcome_label",
            "string",
            "certified or conditional."
        ],
        [
            "is_current",
            "bool",
            "The active certificate for the planter."
        ],
        [
            "issued_at, revoked_at, revoke_reason",
            "datetime|string|null",
            "Lifecycle."
        ],
        [
            "verify_url",
            "string",
            "Public verification page; also the QR code content."
        ],
        [
            "planter",
            "object",
            "Only when loaded: id, name, identification_number, district."
        ]
    ]
}
```

### 3

```json
{
    "name": "User (staff)",
    "description": "A staff account returned by login, auth/me and admin/users.",
    "fields": [
        [
            "id, name, email, phone",
            "integer|string",
            "Profile."
        ],
        [
            "role, role_label",
            "string",
            "Role key and display label."
        ],
        [
            "status",
            "string",
            "active or inactive."
        ],
        [
            "last_login_at",
            "datetime|null",
            "Last sign-in (Sri Lanka time)."
        ],
        [
            "permissions",
            "object",
            "Booleans such as review_registrations, work_first_audit, manage_certificates, manage_settings, view_api_docs."
        ]
    ]
}
```

## Endpoints

### Reference data

Public endpoints for app start-up. Download and cache them so registration forms work offline.

#### `GET meta`

**App details and maintenance state**

- **Access:** public
- **Description:** Call on every app start. If maintenance.enabled is true, show maintenance.message instead of the login screen.

Status: `200`

Example response:

```json
{
    "app": {
        "name": "SPPNR",
        "short_name": "SPPNR",
        "full_name": "SUSTAINABLE PRODUCTION AND PROCESSING OF NATURAL RUBBER",
        "version": "0.8.0",
        "copyright": "...",
        "powered_by": "..."
    },
    "governing_bodies": [
        {
            "name": "Rubber Development Department",
            "short": "Rubber Development Department",
            "logo_url": "https://example.lk/logo-rdd.png"
        }
    ],
    "maintenance": {
        "enabled": false,
        "message": null
    }
}
```


#### `GET reference/locations`

**Districts and RDO divisions**

- **Access:** public
- **Description:** Active districts, each with its Rubber Development Officer divisions. Use the ids in registration forms.

Status: `200`

Example response:

```json
{
    "districts": [
        {
            "id": 1,
            "name": "Kegalle",
            "code": "KEGA",
            "rdo_divisions": [
                {
                    "id": 25,
                    "name": "Redeegama",
                    "code": "REDE"
                }
            ]
        }
    ]
}
```


#### `GET reference/options`

**Dropdown values**

- **Access:** public
- **Description:** Business types, process-plan options, statuses, checklist results and modules, so the app never hard-codes them.

Status: `200`

Example response:

```json
{
    "business_types": [
        {
            "value": "individual",
            "label": "Individual",
            "label_si": "තනි පුද්ගල"
        }
    ],
    "process_plan_options": [
        {
            "value": "yes",
            "label": "Yes",
            "label_si": "ඔව්"
        }
    ],
    "registration_types": [
        {
            "value": "online",
            "label": "Online"
        }
    ],
    "registration_categories": [
        {
            "value": "solo",
            "label": "Sole Proprietor Registration",
            "label_si": "තනි හිමිකරු ලියාපදිංචිය",
            "label_ta": "தனி உரிமையாளர் பதிவு",
            "description": "For an individual who owns and runs the farm.",
            "business_type": "individual"
        }
    ],
    "planter_statuses": [
        {
            "value": "pending",
            "label": "Pending approval"
        }
    ],
    "audit_result_outcomes": [
        {
            "value": "certified",
            "label": "Certified"
        }
    ],
    "checklist_results": [
        {
            "value": "pass",
            "label": "Pass"
        }
    ],
    "checklist_modules": [
        {
            "value": "psm",
            "label": "Product Safety Module (PSM)"
        }
    ]
}
```


### Authentication

Sign in, identify the signed-in account, sign out and change the password. Planters and staff use separate login endpoints.

#### `POST auth/planter/login`

**Planter sign-in**

- **Access:** public
- **Rate limit:** 10 requests / minute; 5 wrong passwords lock the login for a minute
- **Description:** Sign in with the SPPNR ID (e.g. RUB/SUS/KEGA/REDE/00001) or the registered email. Only approved planters who have created a password can sign in.

Request body (json):

```json
[
    {
        "name": "login",
        "type": "string",
        "required": true,
        "description": "SPPNR ID or email."
    },
    {
        "name": "password",
        "type": "string",
        "required": true,
        "description": ""
    },
    {
        "name": "device_name",
        "type": "string",
        "required": true,
        "description": "Label for this device, e.g. \"Pradeep Android\". Shown when listing tokens."
    }
]
```

Example request:

```json
{
    "login": "RUB/SUS/KEGA/REDE/00001",
    "password": "your-password",
    "device_name": "Pradeep Android"
}
```

Status: `200`

Example response:

```json
{
    "token": "3|Q1b0r4n2xM9ExampleTokenValue",
    "token_type": "Bearer",
    "account_type": "planter",
    "user": {
        "id": 71,
        "temporary_id": "TMP-2026-000071",
        "identification_number": "RUB/SUS/KEGA/REDE/00001",
        "status": "approved",
        "status_label": "Approved",
        "registration_type": "online",
        "name": "Pradeep Peiris",
        "nic": "198000000071",
        "email": "pradeep.071@sample.lk",
        "phone": "0773000071",
        "whatsapp": "0773000071",
        "fax": null,
        "district_id": 1,
        "district": "Kegalle",
        "rdo_division_id": 25,
        "rdd_division": "Redeegama",
        "farm_name": "Pradeep Peiris Rubber Estate",
        "address": "Estate Junction, Kegalle",
        "latitude": "7.1913000",
        "longitude": "80.3464000",
        "has_map_pin": true,
        "business_type": "individual",
        "business_type_label": "Individual",
        "already_certified": false,
        "certification_standard": null,
        "certification_rejected_or_suspended": false,
        "certification_issue_reason": null,
        "crops_products": [
            "Rubber sheets"
        ],
        "aware_of_certification": true,
        "has_certification_leaflet": true,
        "processes_rubber_on_farm": false,
        "has_process_plan": null,
        "group_name": null,
        "group_address": null,
        "rejection_reason": null,
        "audit_status": "not_started",
        "audit_result_outcome": null,
        "audit_result_label": null,
        "audit_result_notes": null,
        "has_application_document": false,
        "has_prior_certificate_document": false,
        "approved_at": "2026-11-23T09:15:00+05:30",
        "last_login_at": "2026-11-24T12:30:23+05:30",
        "created_at": "2026-11-22T10:02:41+05:30"
    }
}
```

Errors:

```json
[
    [
        422,
        "Wrong credentials; registration still pending, rejected, or no password created yet (message says which); or too many attempts."
    ],
    [
        503,
        "Maintenance mode is on."
    ]
]
```


#### `POST auth/staff/login`

**Staff sign-in**

- **Access:** public
- **Rate limit:** 10 requests / minute; 5 wrong passwords lock the login for a minute
- **Description:** For administrators, officers, field officers and auditors. The user object includes the permissions the app should follow.

Request body (json):

```json
[
    {
        "name": "email",
        "type": "string",
        "required": true,
        "description": ""
    },
    {
        "name": "password",
        "type": "string",
        "required": true,
        "description": ""
    },
    {
        "name": "device_name",
        "type": "string",
        "required": true,
        "description": "Label for this device."
    }
]
```

Example request:

```json
{
    "email": "first.auditor@rrisl.gov.lk",
    "password": "your-password",
    "device_name": "Auditor tablet"
}
```

Status: `200`

Example response:

```json
{
    "token": "4|Rk2xExampleStaffTokenValue",
    "token_type": "Bearer",
    "account_type": "staff",
    "user": {
        "id": 5,
        "name": "First Auditor",
        "email": "first.auditor@rrisl.gov.lk",
        "phone": "0112600003",
        "role": "first_auditor",
        "role_label": "First Auditor",
        "status": "active",
        "last_login_at": "2026-11-24T12:30:23+05:30",
        "permissions": {
            "view_registry": true,
            "review_registrations": false,
            "manage_planter_records": false,
            "delete_planters": false,
            "dispatch_audits": false,
            "work_first_audit": true,
            "work_second_audit": false,
            "view_audits": true,
            "manage_certificates": false,
            "resend_planter_sms": false,
            "send_sms_samples": false,
            "manage_master_data": false,
            "manage_users": false,
            "manage_settings": false,
            "view_api_docs": false
        }
    }
}
```

Errors:

```json
[
    [
        422,
        "Wrong credentials, inactive account, or too many attempts."
    ],
    [
        503,
        "Maintenance mode is on and the user is not an administrator."
    ]
]
```


#### `GET auth/me`

**Current account**

- **Access:** any
- **Description:** Returns the signed-in account. account_type tells you whether it is a planter or staff member.

Status: `200`

Example response:

```json
{
    "account_type": "staff",
    "user": {
        "id": 5,
        "name": "First Auditor",
        "email": "first.auditor@rrisl.gov.lk",
        "phone": "0112600003",
        "role": "first_auditor",
        "role_label": "First Auditor",
        "status": "active",
        "last_login_at": "2026-11-24T12:30:23+05:30",
        "permissions": {
            "view_registry": true,
            "review_registrations": false,
            "manage_planter_records": false,
            "delete_planters": false,
            "dispatch_audits": false,
            "work_first_audit": true,
            "work_second_audit": false,
            "view_audits": true,
            "manage_certificates": false,
            "resend_planter_sms": false,
            "send_sms_samples": false,
            "manage_master_data": false,
            "manage_users": false,
            "manage_settings": false,
            "view_api_docs": false
        }
    }
}
```


#### `POST auth/logout`

**Sign out**

- **Access:** any
- **Description:** Revokes the token used for this request.

Status: `200`

Example response:

```json
{
    "message": "Signed out."
}
```


#### `POST auth/logout-others`

**Sign out other devices**

- **Access:** any
- **Description:** Revokes every token except the one used for this request. Use it for a "sign out everywhere else" button. Other devices must sign in again.

Status: `200`

Example response:

```json
{
    "message": "Other devices have been signed out.",
    "revoked": 2
}
```


#### `POST auth/password`

**Change password**

- **Access:** any
- **Description:** Changes the password and signs out every other device. This device stays signed in.

Request body (json):

```json
[
    {
        "name": "current_password",
        "type": "string",
        "required": true,
        "description": ""
    },
    {
        "name": "password",
        "type": "string",
        "required": true,
        "description": "At least 10 characters, with upper and lower case letters and a number."
    },
    {
        "name": "password_confirmation",
        "type": "string",
        "required": true,
        "description": "Must match password."
    }
]
```

Example request:

```json
{
    "current_password": "your-current-password",
    "password": "NewPassw0rd!2026",
    "password_confirmation": "NewPassw0rd!2026"
}
```

Status: `200`

Example response:

```json
{
    "message": "Password changed. Other devices have been signed out."
}
```

Errors:

```json
[
    [
        422,
        "Current password is wrong or the new password is too weak."
    ]
]
```


### Planter registration

Public endpoints for new planters. There is no reCAPTCHA in the API: a hidden honeypot field (`company_website`) and the rate limit protect it. Never send the honeypot field.

#### `POST registrations`

**Register online**

- **Access:** public
- **Rate limit:** 10 requests / minute per IP
- **Description:** Submits an online application. Send JSON, or multipart/form-data when attaching prior_certificate_document. The planter receives an SMS and waits for approval. First, the app shows the registration type choice (solo, group, company); the choice is sent as registration_category and sets business_type on the server (solo → individual, group → society, company → company).

Request body (json):

```json
[
    {
        "name": "registration_category",
        "type": "enum",
        "required": true,
        "description": "solo (sole proprietor), group or company. Sets business_type automatically. Use reference/options → registration_categories."
    },
    {
        "name": "name",
        "type": "string",
        "required": true,
        "description": "Applicant name (max 120)."
    },
    {
        "name": "nic",
        "type": "string",
        "required": true,
        "description": "National ID, unique (max 20)."
    },
    {
        "name": "email",
        "type": "string",
        "required": true,
        "description": "Unique email."
    },
    {
        "name": "phone",
        "type": "string",
        "required": true,
        "description": "Mobile number (max 30)."
    },
    {
        "name": "whatsapp",
        "type": "string",
        "required": false,
        "description": ""
    },
    {
        "name": "fax",
        "type": "string",
        "required": false,
        "description": ""
    },
    {
        "name": "district_id",
        "type": "integer",
        "required": true,
        "description": "From reference/locations."
    },
    {
        "name": "rdo_division_id",
        "type": "integer",
        "required": true,
        "description": "Must belong to the district."
    },
    {
        "name": "farm_name",
        "type": "string",
        "required": true,
        "description": "Max 160."
    },
    {
        "name": "address",
        "type": "string",
        "required": true,
        "description": "Farm address (max 500)."
    },
    {
        "name": "latitude",
        "type": "number",
        "required": true,
        "description": "Farm pin, between 5.8 and 10.0."
    },
    {
        "name": "longitude",
        "type": "number",
        "required": true,
        "description": "Farm pin, between 79.4 and 82.1."
    },
    {
        "name": "business_type",
        "type": "enum",
        "required": true,
        "description": "See reference/options → business_types."
    },
    {
        "name": "already_certified",
        "type": "boolean",
        "required": true,
        "description": ""
    },
    {
        "name": "certification_standard",
        "type": "string",
        "required": false,
        "description": "Required when already_certified is true."
    },
    {
        "name": "prior_certificate_document",
        "type": "file",
        "required": false,
        "description": "Required when already_certified is true. pdf, jpg, jpeg, png or webp, up to 10 MB."
    },
    {
        "name": "certification_rejected_or_suspended",
        "type": "boolean",
        "required": true,
        "description": ""
    },
    {
        "name": "certification_issue_reason",
        "type": "string",
        "required": false,
        "description": "Required when certification_rejected_or_suspended is true."
    },
    {
        "name": "crops_products",
        "type": "string[]",
        "required": true,
        "description": "1 to 10 products."
    },
    {
        "name": "aware_of_certification",
        "type": "boolean",
        "required": true,
        "description": ""
    },
    {
        "name": "has_certification_leaflet",
        "type": "boolean",
        "required": true,
        "description": ""
    },
    {
        "name": "processes_rubber_on_farm",
        "type": "boolean",
        "required": true,
        "description": ""
    },
    {
        "name": "has_process_plan",
        "type": "enum",
        "required": false,
        "description": "yes, no or na. Required when processes_rubber_on_farm is true."
    },
    {
        "name": "group_name",
        "type": "string",
        "required": false,
        "description": "Required for group and company. Company name or group/society name."
    },
    {
        "name": "group_address",
        "type": "string",
        "required": false,
        "description": ""
    }
]
```

Example request:

```json
{
    "registration_category": "solo",
    "name": "Asanka Alwis",
    "nic": "900000180V",
    "email": "asanka@example.lk",
    "phone": "0773000180",
    "district_id": 1,
    "rdo_division_id": 29,
    "farm_name": "Alwis Rubber Estate",
    "address": "Estate Junction, Kegalle",
    "latitude": 7.2513,
    "longitude": 80.3464,
    "business_type": "individual",
    "already_certified": false,
    "certification_rejected_or_suspended": false,
    "crops_products": [
        "Rubber sheets"
    ],
    "aware_of_certification": true,
    "has_certification_leaflet": true,
    "processes_rubber_on_farm": false
}
```

Status: `201`

Example response:

```json
{
    "message": "Registration submitted. Please wait for approval; you will receive an SMS.",
    "temporary_id": "TMP-2026-000181",
    "status": "pending"
}
```

Errors:

```json
[
    [
        422,
        "Validation errors (the same rules as the website form)."
    ]
]
```


#### `POST registrations/offline`

**Register from a paper form**

- **Access:** public
- **Rate limit:** 10 requests / minute per IP
- **Description:** For planters who filled the paper registration form. Choose the registration type first (solo, group or company) and send it as registration_category. Upload a photo or PDF of the form. Staff complete the remaining details later.

Request body (multipart):

```json
[
    {
        "name": "registration_category",
        "type": "enum",
        "required": true,
        "description": "solo, group or company."
    },
    {
        "name": "name",
        "type": "string",
        "required": true,
        "description": ""
    },
    {
        "name": "nic",
        "type": "string",
        "required": true,
        "description": "Unique."
    },
    {
        "name": "email",
        "type": "string",
        "required": false,
        "description": "Unique when given."
    },
    {
        "name": "phone",
        "type": "string",
        "required": true,
        "description": ""
    },
    {
        "name": "district_id",
        "type": "integer",
        "required": true,
        "description": ""
    },
    {
        "name": "rdo_division_id",
        "type": "integer",
        "required": true,
        "description": ""
    },
    {
        "name": "address",
        "type": "string",
        "required": false,
        "description": ""
    },
    {
        "name": "application_document",
        "type": "file",
        "required": true,
        "description": "pdf, jpg, jpeg, png or webp, up to 10 MB."
    }
]
```

Example request:

```json
{
    "name": "Kamal Perera",
    "nic": "851234567V",
    "phone": "0712345678",
    "district_id": 1,
    "rdo_division_id": 25,
    "application_document": "@form.jpg"
}
```

Status: `201`

Example response:

```json
{
    "message": "Registration submitted. Please wait for approval; you will receive an SMS.",
    "temporary_id": "TMP-2026-000182",
    "status": "pending"
}
```


#### `POST registrations/status`

**Check registration progress**

- **Access:** public
- **Rate limit:** 10 requests / minute per IP
- **Description:** Lets a planter see whether the application is approved. Needs both the reference from registration and the NIC, so one number alone reveals nothing.

Request body (json):

```json
[
    {
        "name": "temporary_id",
        "type": "string",
        "required": true,
        "description": "Reference returned by the registration call."
    },
    {
        "name": "nic",
        "type": "string",
        "required": true,
        "description": ""
    }
]
```

Example request:

```json
{
    "temporary_id": "TMP-2026-000071",
    "nic": "198000000071"
}
```

Status: `200`

Example response:

```json
{
    "temporary_id": "TMP-2026-000071",
    "status": "approved",
    "status_label": "Approved",
    "identification_number": "RUB/SUS/KEGA/REDE/00001",
    "rejection_reason": null,
    "can_set_password": true
}
```

Errors:

```json
[
    [
        404,
        "No registration matches these details."
    ]
]
```


#### `POST registrations/password`

**Create password after approval**

- **Access:** public
- **Rate limit:** 10 requests / minute per IP
- **Description:** After approval the planter creates a password once, using the SPPNR ID and NIC, then signs in.

Request body (json):

```json
[
    {
        "name": "identification_number",
        "type": "string",
        "required": true,
        "description": "The SPPNR ID."
    },
    {
        "name": "nic",
        "type": "string",
        "required": true,
        "description": ""
    },
    {
        "name": "password",
        "type": "string",
        "required": true,
        "description": "At least 10 characters, with upper and lower case letters and a number."
    },
    {
        "name": "password_confirmation",
        "type": "string",
        "required": true,
        "description": ""
    }
]
```

Example request:

```json
{
    "identification_number": "RUB/SUS/KEGA/REDE/00001",
    "nic": "198000000071",
    "password": "Passw0rd!2026",
    "password_confirmation": "Passw0rd!2026"
}
```

Status: `200`

Example response:

```json
{
    "message": "Password created. You can now sign in with your SPPNR ID."
}
```

Errors:

```json
[
    [
        422,
        "No match, still pending, rejected, or a password already exists."
    ]
]
```


### Planter portal

For signed-in planters. Planter tokens cannot call /staff/* or /admin/* endpoints.

#### `GET planter/dashboard`

**Dashboard**

- **Access:** planter
- **Description:** Everything the home screen needs: profile summary, the audit timeline, the current certificate and two QR payloads: qr_payload is the planter's tracking QR (the reference number, fixed from registration to the end) and certificate_qr_payload is the public verification URL of the current certificate (null until a certificate is issued).

Status: `200`

Example response:

```json
{
    "planter": {
        "id": 71,
        "temporary_id": "TMP-2026-000071",
        "identification_number": "RUB/SUS/KEGA/REDE/00001",
        "status": "approved",
        "status_label": "Approved",
        "registration_type": "online",
        "name": "Pradeep Peiris",
        "nic": "198000000071",
        "email": "pradeep.071@sample.lk",
        "phone": "0773000071",
        "whatsapp": "0773000071",
        "fax": null,
        "district_id": 1,
        "district": "Kegalle",
        "rdo_division_id": 25,
        "rdd_division": "Redeegama",
        "farm_name": "Pradeep Peiris Rubber Estate",
        "address": "Estate Junction, Kegalle",
        "latitude": "7.1913000",
        "longitude": "80.3464000",
        "has_map_pin": true,
        "business_type": "individual",
        "business_type_label": "Individual",
        "already_certified": false,
        "certification_standard": null,
        "certification_rejected_or_suspended": false,
        "certification_issue_reason": null,
        "crops_products": [
            "Rubber sheets"
        ],
        "aware_of_certification": true,
        "has_certification_leaflet": true,
        "processes_rubber_on_farm": false,
        "has_process_plan": null,
        "group_name": null,
        "group_address": null,
        "rejection_reason": null,
        "audit_status": "not_started",
        "audit_result_outcome": null,
        "audit_result_label": null,
        "audit_result_notes": null,
        "has_application_document": false,
        "has_prior_certificate_document": false,
        "approved_at": "2026-11-23T09:15:00+05:30",
        "last_login_at": "2026-11-24T12:30:23+05:30",
        "created_at": "2026-11-22T10:02:41+05:30"
    },
    "audit_timeline": [
        {
            "key": "registration_approved",
            "label": "Registration approved",
            "description": "Planter registration verified and SPPNR identification issued.",
            "state": "completed",
            "children": []
        },
        {
            "key": "first_audit",
            "label": "First Audit",
            "description": "... Attempt #1. Status: Queued.",
            "state": "current",
            "children": []
        },
        {
            "key": "final_audit",
            "label": "Final Audit",
            "description": "...",
            "state": "upcoming",
            "children": []
        },
        {
            "key": "audit_result",
            "label": "Audit result",
            "description": "...",
            "state": "upcoming",
            "children": []
        }
    ],
    "certificate": null,
    "qr_payload": "TMP-2026-000001",
    "certificate_qr_payload": null
}
```

Timeline state is completed, current or upcoming. certificate is a Certificate object once issued.


#### `GET planter/profile`

**Profile**

- **Access:** planter

Status: `200`

Example response:

```json
{
    "planter": {
        "id": 71,
        "temporary_id": "TMP-2026-000071",
        "identification_number": "RUB/SUS/KEGA/REDE/00001",
        "status": "approved",
        "status_label": "Approved",
        "registration_type": "online",
        "name": "Pradeep Peiris",
        "nic": "198000000071",
        "email": "pradeep.071@sample.lk",
        "phone": "0773000071",
        "whatsapp": "0773000071",
        "fax": null,
        "district_id": 1,
        "district": "Kegalle",
        "rdo_division_id": 25,
        "rdd_division": "Redeegama",
        "farm_name": "Pradeep Peiris Rubber Estate",
        "address": "Estate Junction, Kegalle",
        "latitude": "7.1913000",
        "longitude": "80.3464000",
        "has_map_pin": true,
        "business_type": "individual",
        "business_type_label": "Individual",
        "already_certified": false,
        "certification_standard": null,
        "certification_rejected_or_suspended": false,
        "certification_issue_reason": null,
        "crops_products": [
            "Rubber sheets"
        ],
        "aware_of_certification": true,
        "has_certification_leaflet": true,
        "processes_rubber_on_farm": false,
        "has_process_plan": null,
        "group_name": null,
        "group_address": null,
        "rejection_reason": null,
        "audit_status": "not_started",
        "audit_result_outcome": null,
        "audit_result_label": null,
        "audit_result_notes": null,
        "has_application_document": false,
        "has_prior_certificate_document": false,
        "approved_at": "2026-11-23T09:15:00+05:30",
        "last_login_at": "2026-11-24T12:30:23+05:30",
        "created_at": "2026-11-22T10:02:41+05:30"
    }
}
```


#### `PUT planter/profile`

**Update profile**

- **Access:** planter
- **Description:** Approved planters can update contact details and farm location.

Request body (json):

```json
[
    {
        "name": "name",
        "type": "string",
        "required": true,
        "description": ""
    },
    {
        "name": "email",
        "type": "string",
        "required": false,
        "description": "Unique among planters."
    },
    {
        "name": "phone",
        "type": "string",
        "required": true,
        "description": ""
    },
    {
        "name": "district_id",
        "type": "integer",
        "required": true,
        "description": ""
    },
    {
        "name": "rdo_division_id",
        "type": "integer",
        "required": true,
        "description": "Must belong to the district."
    },
    {
        "name": "address",
        "type": "string",
        "required": true,
        "description": ""
    },
    {
        "name": "latitude",
        "type": "number",
        "required": true,
        "description": "5.8 – 10.0"
    },
    {
        "name": "longitude",
        "type": "number",
        "required": true,
        "description": "79.4 – 82.1"
    }
]
```

Example request:

```json
{
    "name": "Pradeep Peiris",
    "email": "pradeep.071@sample.lk",
    "phone": "0773000071",
    "district_id": 1,
    "rdo_division_id": 25,
    "address": "Estate Junction, Kegalle",
    "latitude": 7.1913,
    "longitude": 80.3464
}
```

Status: `200`

Example response:

```json
{
    "planter": {
        "id": 71,
        "temporary_id": "TMP-2026-000071",
        "identification_number": "RUB/SUS/KEGA/REDE/00001",
        "status": "approved",
        "status_label": "Approved",
        "registration_type": "online",
        "name": "Pradeep Peiris",
        "nic": "198000000071",
        "email": "pradeep.071@sample.lk",
        "phone": "0773000071",
        "whatsapp": "0773000071",
        "fax": null,
        "district_id": 1,
        "district": "Kegalle",
        "rdo_division_id": 25,
        "rdd_division": "Redeegama",
        "farm_name": "Pradeep Peiris Rubber Estate",
        "address": "Estate Junction, Kegalle",
        "latitude": "7.1913000",
        "longitude": "80.3464000",
        "has_map_pin": true,
        "business_type": "individual",
        "business_type_label": "Individual",
        "already_certified": false,
        "certification_standard": null,
        "certification_rejected_or_suspended": false,
        "certification_issue_reason": null,
        "crops_products": [
            "Rubber sheets"
        ],
        "aware_of_certification": true,
        "has_certification_leaflet": true,
        "processes_rubber_on_farm": false,
        "has_process_plan": null,
        "group_name": null,
        "group_address": null,
        "rejection_reason": null,
        "audit_status": "not_started",
        "audit_result_outcome": null,
        "audit_result_label": null,
        "audit_result_notes": null,
        "has_application_document": false,
        "has_prior_certificate_document": false,
        "approved_at": "2026-11-23T09:15:00+05:30",
        "last_login_at": "2026-11-24T12:30:23+05:30",
        "created_at": "2026-11-22T10:02:41+05:30"
    }
}
```

Errors:

```json
[
    [
        403,
        "The registration is not approved."
    ],
    [
        422,
        "Validation errors."
    ]
]
```


#### `GET planter/certificate`

**Current certificate**

- **Access:** planter

Status: `200`

Example response:

```json
{
    "certificate": {
        "id": 4,
        "planter_id": 71,
        "certificate_number": "SPPNR/CERT/2026/00004",
        "status": "issued",
        "status_label": "Issued",
        "outcome": "certified",
        "outcome_label": "Certified",
        "is_current": true,
        "issued_at": "2026-11-24T15:00:00+05:30",
        "revoked_at": null,
        "revoke_reason": null,
        "verify_url": "https://example.lk/verify/1f0c9b2e7a"
    }
}
```

Errors:

```json
[
    [
        404,
        "No active certificate yet."
    ]
]
```


### Staff dashboard

For every staff role. Staff tokens cannot call /planter/* endpoints.

#### `GET staff/dashboard`

**Dashboard counters**

- **Access:** staff

Status: `200`

Example response:

```json
{
    "registrations": {
        "pending": 70,
        "pending_online": 45,
        "pending_offline": 25
    },
    "registry": {
        "approved": 110,
        "rejected": 20
    },
    "audits": {
        "ongoing_first": 18,
        "ongoing_second": 18,
        "passed": 99,
        "failed": 11
    },
    "certificates": {
        "issued": 15,
        "revoked": 4
    },
    "user": {
        "id": 5,
        "name": "First Auditor",
        "email": "first.auditor@rrisl.gov.lk",
        "phone": "0112600003",
        "role": "first_auditor",
        "role_label": "First Auditor",
        "status": "active",
        "last_login_at": "2026-11-24T12:30:23+05:30",
        "permissions": {
            "view_registry": true,
            "review_registrations": false,
            "manage_planter_records": false,
            "delete_planters": false,
            "dispatch_audits": false,
            "work_first_audit": true,
            "work_second_audit": false,
            "view_audits": true,
            "manage_certificates": false,
            "resend_planter_sms": false,
            "send_sms_samples": false,
            "manage_master_data": false,
            "manage_users": false,
            "manage_settings": false,
            "view_api_docs": false
        }
    }
}
```


### Staff: planters and registrations

Registration lobby, registry, approval and audit dispatch. Each action checks the role; the planter detail response lists what the current user may do in permissions.

#### `GET staff/planters`

**List planters**

- **Access:** staff
- **Description:** Defaults to approved planters. Use status=pending for the registration lobby (oldest first) or status=rejected.

Query parameters:

```json
[
    {
        "name": "status",
        "type": "enum",
        "required": false,
        "description": "pending, approved (default) or rejected."
    },
    {
        "name": "q",
        "type": "string",
        "required": false,
        "description": "Search name, email, NIC, phone, temporary id or SPPNR ID."
    },
    {
        "name": "district_id",
        "type": "integer",
        "required": false,
        "description": ""
    },
    {
        "name": "registration_type",
        "type": "enum",
        "required": false,
        "description": "online or offline."
    },
    {
        "name": "page",
        "type": "integer",
        "required": false,
        "description": ""
    },
    {
        "name": "per_page",
        "type": "integer",
        "required": false,
        "description": "Default 20, maximum 100."
    }
]
```

Status: `200`

Example response:

```json
{
    "data": [
        {
            "id": 71,
            "temporary_id": "TMP-2026-000071",
            "identification_number": "RUB/SUS/KEGA/REDE/00001",
            "status": "approved",
            "status_label": "Approved",
            "registration_type": "online",
            "name": "Pradeep Peiris",
            "nic": "198000000071",
            "email": "pradeep.071@sample.lk",
            "phone": "0773000071",
            "whatsapp": "0773000071",
            "fax": null,
            "district_id": 1,
            "district": "Kegalle",
            "rdo_division_id": 25,
            "rdd_division": "Redeegama",
            "farm_name": "Pradeep Peiris Rubber Estate",
            "address": "Estate Junction, Kegalle",
            "latitude": "7.1913000",
            "longitude": "80.3464000",
            "has_map_pin": true,
            "business_type": "individual",
            "business_type_label": "Individual",
            "already_certified": false,
            "certification_standard": null,
            "certification_rejected_or_suspended": false,
            "certification_issue_reason": null,
            "crops_products": [
                "Rubber sheets"
            ],
            "aware_of_certification": true,
            "has_certification_leaflet": true,
            "processes_rubber_on_farm": false,
            "has_process_plan": null,
            "group_name": null,
            "group_address": null,
            "rejection_reason": null,
            "audit_status": "not_started",
            "audit_result_outcome": null,
            "audit_result_label": null,
            "audit_result_notes": null,
            "has_application_document": false,
            "has_prior_certificate_document": false,
            "approved_at": "2026-11-23T09:15:00+05:30",
            "last_login_at": "2026-11-24T12:30:23+05:30",
            "created_at": "2026-11-22T10:02:41+05:30"
        }
    ],
    "links": {
        "first": "...",
        "last": "...",
        "prev": null,
        "next": "..."
    },
    "meta": {
        "current_page": 1,
        "last_page": 6,
        "per_page": 20,
        "total": 110
    }
}
```


#### `POST staff/planters`

**Create a planter**

- **Access:** staff
- **Description:** Creates a record directly (for example a field officer registering a planter). Setting status to approved issues the SPPNR ID and sends the approval SMS.
- **Note:** Needs: Administrator, RRISL Officer, RDD Officer or Field Officer.

Request body (json):

```json
[
    {
        "name": "name",
        "type": "string",
        "required": true,
        "description": "Max 120."
    },
    {
        "name": "nic",
        "type": "string",
        "required": true,
        "description": "Unique (max 20)."
    },
    {
        "name": "email",
        "type": "string",
        "required": true,
        "description": "Unique."
    },
    {
        "name": "phone",
        "type": "string",
        "required": true,
        "description": ""
    },
    {
        "name": "district_id",
        "type": "integer",
        "required": true,
        "description": ""
    },
    {
        "name": "rdo_division_id",
        "type": "integer",
        "required": true,
        "description": "Must belong to the district."
    },
    {
        "name": "address",
        "type": "string",
        "required": true,
        "description": ""
    },
    {
        "name": "status",
        "type": "enum",
        "required": true,
        "description": "pending, approved or rejected."
    },
    {
        "name": "rejection_reason",
        "type": "string",
        "required": false,
        "description": "Required when status is rejected."
    },
    {
        "name": "password",
        "type": "string",
        "required": false,
        "description": "Optional initial password with password_confirmation. At least 10 characters, with upper and lower case letters and a number."
    },
    {
        "name": "latitude",
        "type": "number",
        "required": true,
        "description": "5.8 – 10.0"
    },
    {
        "name": "longitude",
        "type": "number",
        "required": true,
        "description": "79.4 – 82.1"
    },
    {
        "name": "farm_name",
        "type": "string",
        "required": false,
        "description": ""
    },
    {
        "name": "whatsapp",
        "type": "string",
        "required": false,
        "description": ""
    },
    {
        "name": "fax",
        "type": "string",
        "required": false,
        "description": ""
    },
    {
        "name": "registration_category",
        "type": "enum",
        "required": true,
        "description": "solo, group or company. Required when creating; optional when updating (older records may not have one). Sets business_type automatically."
    },
    {
        "name": "business_type",
        "type": "enum",
        "required": false,
        "description": "See reference/options. Ignored when registration_category is sent."
    },
    {
        "name": "already_certified",
        "type": "boolean",
        "required": false,
        "description": ""
    },
    {
        "name": "certification_standard",
        "type": "string",
        "required": false,
        "description": "Required when already_certified is true."
    },
    {
        "name": "certification_rejected_or_suspended",
        "type": "boolean",
        "required": false,
        "description": ""
    },
    {
        "name": "certification_issue_reason",
        "type": "string",
        "required": false,
        "description": "Required when certification_rejected_or_suspended is true."
    },
    {
        "name": "crops_products",
        "type": "string[]",
        "required": false,
        "description": "Up to 10."
    },
    {
        "name": "aware_of_certification",
        "type": "boolean",
        "required": false,
        "description": ""
    },
    {
        "name": "has_certification_leaflet",
        "type": "boolean",
        "required": false,
        "description": ""
    },
    {
        "name": "processes_rubber_on_farm",
        "type": "boolean",
        "required": false,
        "description": ""
    },
    {
        "name": "has_process_plan",
        "type": "enum",
        "required": false,
        "description": "yes, no or na. Required when processes_rubber_on_farm is true."
    },
    {
        "name": "group_name",
        "type": "string",
        "required": false,
        "description": ""
    },
    {
        "name": "group_address",
        "type": "string",
        "required": false,
        "description": ""
    }
]
```

Example request:

```json
{
    "name": "Nimal Silva",
    "nic": "880000999V",
    "email": "nimal@example.lk",
    "phone": "0771234567",
    "district_id": 1,
    "rdo_division_id": 25,
    "address": "Hill Road, Kegalle",
    "status": "pending",
    "farm_name": "Silva Estate",
    "latitude": 7.25,
    "longitude": 80.34,
    "business_type": "individual",
    "already_certified": false,
    "certification_rejected_or_suspended": false,
    "crops_products": [
        "Rubber sheets"
    ],
    "aware_of_certification": true,
    "has_certification_leaflet": false,
    "processes_rubber_on_farm": false
}
```

Status: `201`

Example response:

```json
{
    "planter": {
        "id": 71,
        "temporary_id": "TMP-2026-000071",
        "identification_number": "RUB/SUS/KEGA/REDE/00001",
        "status": "approved",
        "status_label": "Approved",
        "registration_type": "online",
        "name": "Pradeep Peiris",
        "nic": "198000000071",
        "email": "pradeep.071@sample.lk",
        "phone": "0773000071",
        "whatsapp": "0773000071",
        "fax": null,
        "district_id": 1,
        "district": "Kegalle",
        "rdo_division_id": 25,
        "rdd_division": "Redeegama",
        "farm_name": "Pradeep Peiris Rubber Estate",
        "address": "Estate Junction, Kegalle",
        "latitude": "7.1913000",
        "longitude": "80.3464000",
        "has_map_pin": true,
        "business_type": "individual",
        "business_type_label": "Individual",
        "already_certified": false,
        "certification_standard": null,
        "certification_rejected_or_suspended": false,
        "certification_issue_reason": null,
        "crops_products": [
            "Rubber sheets"
        ],
        "aware_of_certification": true,
        "has_certification_leaflet": true,
        "processes_rubber_on_farm": false,
        "has_process_plan": null,
        "group_name": null,
        "group_address": null,
        "rejection_reason": null,
        "audit_status": "not_started",
        "audit_result_outcome": null,
        "audit_result_label": null,
        "audit_result_notes": null,
        "has_application_document": false,
        "has_prior_certificate_document": false,
        "approved_at": "2026-11-23T09:15:00+05:30",
        "last_login_at": "2026-11-24T12:30:23+05:30",
        "created_at": "2026-11-22T10:02:41+05:30"
    }
}
```

Errors:

```json
[
    [
        403,
        "Role cannot manage planter records."
    ],
    [
        422,
        "Validation errors."
    ]
]
```


#### `GET staff/scan/{reference}`

**Scan a planter tracking QR**

- **Access:** staff
- **Description:** Call this with the text read from a planter's tracking QR code (the reference number, for example TMP-2026-000001). The SPPNR ID is accepted too. Returns the same data as Planter detail plus the planter's audits, each with can_work for the signed-in user. Every scan is written to the audit log. The tracking QR never changes, so the same code works at every stage. This is not the certificate QR: certificate QR codes are public verify links (/verify/{token}) and are checked on the website, not here.

Status: `200`

Example response:

```json
{
    "planter": {
        "id": 71,
        "temporary_id": "TMP-2026-000071",
        "identification_number": "RUB/SUS/KEGA/REDE/00001",
        "status": "approved",
        "status_label": "Approved",
        "registration_type": "online",
        "name": "Pradeep Peiris",
        "nic": "198000000071",
        "email": "pradeep.071@sample.lk",
        "phone": "0773000071",
        "whatsapp": "0773000071",
        "fax": null,
        "district_id": 1,
        "district": "Kegalle",
        "rdo_division_id": 25,
        "rdd_division": "Redeegama",
        "farm_name": "Pradeep Peiris Rubber Estate",
        "address": "Estate Junction, Kegalle",
        "latitude": "7.1913000",
        "longitude": "80.3464000",
        "has_map_pin": true,
        "business_type": "individual",
        "business_type_label": "Individual",
        "already_certified": false,
        "certification_standard": null,
        "certification_rejected_or_suspended": false,
        "certification_issue_reason": null,
        "crops_products": [
            "Rubber sheets"
        ],
        "aware_of_certification": true,
        "has_certification_leaflet": true,
        "processes_rubber_on_farm": false,
        "has_process_plan": null,
        "group_name": null,
        "group_address": null,
        "rejection_reason": null,
        "audit_status": "not_started",
        "audit_result_outcome": null,
        "audit_result_label": null,
        "audit_result_notes": null,
        "has_application_document": false,
        "has_prior_certificate_document": false,
        "approved_at": "2026-11-23T09:15:00+05:30",
        "last_login_at": "2026-11-24T12:30:23+05:30",
        "created_at": "2026-11-22T10:02:41+05:30"
    },
    "audit_timeline": [
        {
            "key": "registration_approved",
            "label": "Registration approved",
            "description": "...",
            "state": "completed",
            "children": []
        }
    ],
    "qr_payload": "TMP-2026-000001",
    "certificate_qr_payload": null,
    "sms_resend_actions": [
        {
            "key": "registration_submitted",
            "label": "Registration submitted"
        }
    ],
    "permissions": {
        "can_edit": true,
        "can_delete": false,
        "can_approve": false,
        "can_reject": false,
        "can_dispatch_first_audit": true,
        "can_dispatch_second_audit": false,
        "can_issue_certificate": false,
        "can_resend_sms": true
    },
    "audits": [
        {
            "id": 12,
            "round": "first",
            "status": "in_progress",
            "is_current": true,
            "can_work": true
        }
    ]
}
```

Errors:

```json
[
    [
        404,
        "No planter found for that QR code."
    ]
]
```


#### `GET staff/planters/{planter}`

**Planter detail**

- **Access:** staff
- **Description:** Full record, audit timeline, tracking QR payload (qr_payload), certificate QR payload (certificate_qr_payload, null until a certificate is issued), the SMS steps that can be resent and what the current user may do.

Status: `200`

Example response:

```json
{
    "planter": {
        "id": 71,
        "temporary_id": "TMP-2026-000071",
        "identification_number": "RUB/SUS/KEGA/REDE/00001",
        "status": "approved",
        "status_label": "Approved",
        "registration_type": "online",
        "name": "Pradeep Peiris",
        "nic": "198000000071",
        "email": "pradeep.071@sample.lk",
        "phone": "0773000071",
        "whatsapp": "0773000071",
        "fax": null,
        "district_id": 1,
        "district": "Kegalle",
        "rdo_division_id": 25,
        "rdd_division": "Redeegama",
        "farm_name": "Pradeep Peiris Rubber Estate",
        "address": "Estate Junction, Kegalle",
        "latitude": "7.1913000",
        "longitude": "80.3464000",
        "has_map_pin": true,
        "business_type": "individual",
        "business_type_label": "Individual",
        "already_certified": false,
        "certification_standard": null,
        "certification_rejected_or_suspended": false,
        "certification_issue_reason": null,
        "crops_products": [
            "Rubber sheets"
        ],
        "aware_of_certification": true,
        "has_certification_leaflet": true,
        "processes_rubber_on_farm": false,
        "has_process_plan": null,
        "group_name": null,
        "group_address": null,
        "rejection_reason": null,
        "audit_status": "not_started",
        "audit_result_outcome": null,
        "audit_result_label": null,
        "audit_result_notes": null,
        "has_application_document": false,
        "has_prior_certificate_document": false,
        "approved_at": "2026-11-23T09:15:00+05:30",
        "last_login_at": "2026-11-24T12:30:23+05:30",
        "created_at": "2026-11-22T10:02:41+05:30"
    },
    "audit_timeline": [
        {
            "key": "registration_approved",
            "label": "Registration approved",
            "description": "...",
            "state": "completed",
            "children": []
        }
    ],
    "qr_payload": "TMP-2026-000001",
    "certificate_qr_payload": null,
    "sms_resend_actions": [
        {
            "key": "registration_submitted",
            "label": "Registration submitted"
        },
        {
            "key": "registration_approved",
            "label": "Registration approved"
        }
    ],
    "permissions": {
        "can_edit": true,
        "can_delete": true,
        "can_approve": false,
        "can_reject": false,
        "can_dispatch_first_audit": true,
        "can_dispatch_second_audit": false,
        "can_issue_certificate": false,
        "can_resend_sms": true
    }
}
```


#### `PUT|PATCH staff/planters/{planter}`

**Update a planter**

- **Access:** staff
- **Description:** Same fields as create (NIC and email stay unique except for this planter). Changing status to approved or rejected runs the same approval/rejection steps and SMS as the website; moving back to pending clears the approval.
- **Note:** Needs: Administrator, RRISL Officer, RDD Officer or Field Officer.

Request body (json):

```json
[
    {
        "name": "name",
        "type": "string",
        "required": true,
        "description": "Max 120."
    },
    {
        "name": "nic",
        "type": "string",
        "required": true,
        "description": "Unique (max 20)."
    },
    {
        "name": "email",
        "type": "string",
        "required": true,
        "description": "Unique."
    },
    {
        "name": "phone",
        "type": "string",
        "required": true,
        "description": ""
    },
    {
        "name": "district_id",
        "type": "integer",
        "required": true,
        "description": ""
    },
    {
        "name": "rdo_division_id",
        "type": "integer",
        "required": true,
        "description": "Must belong to the district."
    },
    {
        "name": "address",
        "type": "string",
        "required": true,
        "description": ""
    },
    {
        "name": "status",
        "type": "enum",
        "required": true,
        "description": "pending, approved or rejected."
    },
    {
        "name": "rejection_reason",
        "type": "string",
        "required": false,
        "description": "Required when status is rejected."
    },
    {
        "name": "password",
        "type": "string",
        "required": false,
        "description": "Optional initial password with password_confirmation. At least 10 characters, with upper and lower case letters and a number."
    },
    {
        "name": "latitude",
        "type": "number",
        "required": true,
        "description": "5.8 – 10.0"
    },
    {
        "name": "longitude",
        "type": "number",
        "required": true,
        "description": "79.4 – 82.1"
    },
    {
        "name": "farm_name",
        "type": "string",
        "required": false,
        "description": ""
    },
    {
        "name": "whatsapp",
        "type": "string",
        "required": false,
        "description": ""
    },
    {
        "name": "fax",
        "type": "string",
        "required": false,
        "description": ""
    },
    {
        "name": "registration_category",
        "type": "enum",
        "required": true,
        "description": "solo, group or company. Required when creating; optional when updating (older records may not have one). Sets business_type automatically."
    },
    {
        "name": "business_type",
        "type": "enum",
        "required": false,
        "description": "See reference/options. Ignored when registration_category is sent."
    },
    {
        "name": "already_certified",
        "type": "boolean",
        "required": false,
        "description": ""
    },
    {
        "name": "certification_standard",
        "type": "string",
        "required": false,
        "description": "Required when already_certified is true."
    },
    {
        "name": "certification_rejected_or_suspended",
        "type": "boolean",
        "required": false,
        "description": ""
    },
    {
        "name": "certification_issue_reason",
        "type": "string",
        "required": false,
        "description": "Required when certification_rejected_or_suspended is true."
    },
    {
        "name": "crops_products",
        "type": "string[]",
        "required": false,
        "description": "Up to 10."
    },
    {
        "name": "aware_of_certification",
        "type": "boolean",
        "required": false,
        "description": ""
    },
    {
        "name": "has_certification_leaflet",
        "type": "boolean",
        "required": false,
        "description": ""
    },
    {
        "name": "processes_rubber_on_farm",
        "type": "boolean",
        "required": false,
        "description": ""
    },
    {
        "name": "has_process_plan",
        "type": "enum",
        "required": false,
        "description": "yes, no or na. Required when processes_rubber_on_farm is true."
    },
    {
        "name": "group_name",
        "type": "string",
        "required": false,
        "description": ""
    },
    {
        "name": "group_address",
        "type": "string",
        "required": false,
        "description": ""
    }
]
```

Status: `200`

Example response:

```json
{
    "planter": {
        "id": 71,
        "temporary_id": "TMP-2026-000071",
        "identification_number": "RUB/SUS/KEGA/REDE/00001",
        "status": "approved",
        "status_label": "Approved",
        "registration_type": "online",
        "name": "Pradeep Peiris",
        "nic": "198000000071",
        "email": "pradeep.071@sample.lk",
        "phone": "0773000071",
        "whatsapp": "0773000071",
        "fax": null,
        "district_id": 1,
        "district": "Kegalle",
        "rdo_division_id": 25,
        "rdd_division": "Redeegama",
        "farm_name": "Pradeep Peiris Rubber Estate",
        "address": "Estate Junction, Kegalle",
        "latitude": "7.1913000",
        "longitude": "80.3464000",
        "has_map_pin": true,
        "business_type": "individual",
        "business_type_label": "Individual",
        "already_certified": false,
        "certification_standard": null,
        "certification_rejected_or_suspended": false,
        "certification_issue_reason": null,
        "crops_products": [
            "Rubber sheets"
        ],
        "aware_of_certification": true,
        "has_certification_leaflet": true,
        "processes_rubber_on_farm": false,
        "has_process_plan": null,
        "group_name": null,
        "group_address": null,
        "rejection_reason": null,
        "audit_status": "not_started",
        "audit_result_outcome": null,
        "audit_result_label": null,
        "audit_result_notes": null,
        "has_application_document": false,
        "has_prior_certificate_document": false,
        "approved_at": "2026-11-23T09:15:00+05:30",
        "last_login_at": "2026-11-24T12:30:23+05:30",
        "created_at": "2026-11-22T10:02:41+05:30"
    }
}
```

Errors:

```json
[
    [
        403,
        "Role cannot manage planter records."
    ],
    [
        422,
        "Validation errors."
    ]
]
```


#### `DELETE staff/planters/{planter}`

**Delete a planter**

- **Access:** staff
- **Description:** Deletes the record and its uploaded documents. This cannot be undone.
- **Note:** Needs: Administrator or RRISL Officer.

Status: `200`

Example response:

```json
{
    "message": "Registry entry deleted."
}
```


#### `POST staff/planters/{planter}/approve`

**Approve a registration**

- **Access:** staff
- **Description:** Issues the SPPNR ID and sends the approval SMS. No body.
- **Note:** Needs: Administrator, RRISL Officer or RDD Officer.

Status: `200`

Example response:

```json
{
    "planter": {
        "id": 71,
        "temporary_id": "TMP-2026-000071",
        "identification_number": "RUB/SUS/KEGA/REDE/00001",
        "status": "approved",
        "status_label": "Approved",
        "registration_type": "online",
        "name": "Pradeep Peiris",
        "nic": "198000000071",
        "email": "pradeep.071@sample.lk",
        "phone": "0773000071",
        "whatsapp": "0773000071",
        "fax": null,
        "district_id": 1,
        "district": "Kegalle",
        "rdo_division_id": 25,
        "rdd_division": "Redeegama",
        "farm_name": "Pradeep Peiris Rubber Estate",
        "address": "Estate Junction, Kegalle",
        "latitude": "7.1913000",
        "longitude": "80.3464000",
        "has_map_pin": true,
        "business_type": "individual",
        "business_type_label": "Individual",
        "already_certified": false,
        "certification_standard": null,
        "certification_rejected_or_suspended": false,
        "certification_issue_reason": null,
        "crops_products": [
            "Rubber sheets"
        ],
        "aware_of_certification": true,
        "has_certification_leaflet": true,
        "processes_rubber_on_farm": false,
        "has_process_plan": null,
        "group_name": null,
        "group_address": null,
        "rejection_reason": null,
        "audit_status": "not_started",
        "audit_result_outcome": null,
        "audit_result_label": null,
        "audit_result_notes": null,
        "has_application_document": false,
        "has_prior_certificate_document": false,
        "approved_at": "2026-11-23T09:15:00+05:30",
        "last_login_at": "2026-11-24T12:30:23+05:30",
        "created_at": "2026-11-22T10:02:41+05:30"
    }
}
```


#### `POST staff/planters/{planter}/reject`

**Reject a registration**

- **Access:** staff
- **Note:** Needs: Administrator, RRISL Officer or RDD Officer.

Request body (json):

```json
[
    {
        "name": "rejection_reason",
        "type": "string",
        "required": true,
        "description": "Shown to the planter (max 500)."
    }
]
```

Example request:

```json
{
    "rejection_reason": "NIC photo is not readable."
}
```

Status: `200`

Example response:

```json
{
    "planter": {
        "id": 71,
        "temporary_id": "TMP-2026-000071",
        "identification_number": "RUB/SUS/KEGA/REDE/00001",
        "status": "approved",
        "status_label": "Approved",
        "registration_type": "online",
        "name": "Pradeep Peiris",
        "nic": "198000000071",
        "email": "pradeep.071@sample.lk",
        "phone": "0773000071",
        "whatsapp": "0773000071",
        "fax": null,
        "district_id": 1,
        "district": "Kegalle",
        "rdo_division_id": 25,
        "rdd_division": "Redeegama",
        "farm_name": "Pradeep Peiris Rubber Estate",
        "address": "Estate Junction, Kegalle",
        "latitude": "7.1913000",
        "longitude": "80.3464000",
        "has_map_pin": true,
        "business_type": "individual",
        "business_type_label": "Individual",
        "already_certified": false,
        "certification_standard": null,
        "certification_rejected_or_suspended": false,
        "certification_issue_reason": null,
        "crops_products": [
            "Rubber sheets"
        ],
        "aware_of_certification": true,
        "has_certification_leaflet": true,
        "processes_rubber_on_farm": false,
        "has_process_plan": null,
        "group_name": null,
        "group_address": null,
        "rejection_reason": null,
        "audit_status": "not_started",
        "audit_result_outcome": null,
        "audit_result_label": null,
        "audit_result_notes": null,
        "has_application_document": false,
        "has_prior_certificate_document": false,
        "approved_at": "2026-11-23T09:15:00+05:30",
        "last_login_at": "2026-11-24T12:30:23+05:30",
        "created_at": "2026-11-22T10:02:41+05:30"
    }
}
```


#### `GET staff/planters/{planter}/application-document`

**Download registration form**

- **Access:** staff
- **Description:** Streams the uploaded paper form (pdf/image). Send the bearer token; do not put it in the URL.

Status: `200`

Returns a binary file (not JSON).

Errors:

```json
[
    [
        404,
        "No registration form on file."
    ]
]
```


#### `GET staff/planters/{planter}/prior-certificate-document`

**Download prior certificate**

- **Access:** staff

Status: `200`

Returns a binary file (not JSON).

Errors:

```json
[
    [
        404,
        "No prior certificate on file."
    ]
]
```


#### `POST staff/planters/{planter}/sms/resend`

**Resend a step SMS**

- **Access:** staff
- **Note:** Needs: Administrator, RRISL Officer or RDD Officer.

Request body (json):

```json
[
    {
        "name": "template",
        "type": "string",
        "required": true,
        "description": "A key from planter detail → sms_resend_actions."
    }
]
```

Example request:

```json
{
    "template": "registration_approved"
}
```

Status: `200`

Example response:

```json
{
    "message": "SMS resent to 0773000071."
}
```

Errors:

```json
[
    [
        422,
        "Not an allowed step for this planter, or the SMS gateway failed."
    ]
]
```


#### `POST staff/planters/{planter}/audits/first`

**Send to First Audit**

- **Access:** staff
- **Description:** Queues the First Audit for an approved planter and sends the SMS.
- **Note:** Needs: Administrator, RRISL Officer or RDD Officer.

Status: `201`

Example response:

```json
{
    "audit_id": 12,
    "round": "first",
    "status": "queued"
}
```

Errors:

```json
[
    [
        422,
        "The planter is not eligible (not approved, or already audited)."
    ]
]
```


#### `POST staff/planters/{planter}/audits/final`

**Send to Second Audit**

- **Access:** staff
- **Description:** Allowed only after the First Audit passed. The API round key is "final".
- **Note:** Needs: Administrator, RRISL Officer or RDD Officer.

Status: `201`

Example response:

```json
{
    "audit_id": 31,
    "round": "final",
    "status": "queued"
}
```

Errors:

```json
[
    [
        422,
        "First Audit has not passed."
    ]
]
```


### Staff: audits

The auditor workflow: start → fill the checklist (saved item by item) → submit for review → complete with outcome and digital signature. Everything here can also be queued offline through the sync endpoint.

#### `GET staff/audits`

**List audits**

- **Access:** staff
- **Description:** Defaults: round = your own round (First Auditor → first, Second Auditor → final, others → first) and bucket = ongoing.

Query parameters:

```json
[
    {
        "name": "round",
        "type": "enum",
        "required": false,
        "description": "first or final."
    },
    {
        "name": "bucket",
        "type": "enum",
        "required": false,
        "description": "ongoing (queued, in progress, in review), passed (passed or conditional) or rejected (failed)."
    },
    {
        "name": "q",
        "type": "string",
        "required": false,
        "description": "Planter name, NIC, phone, temporary id or SPPNR ID."
    },
    {
        "name": "district_id",
        "type": "integer",
        "required": false,
        "description": ""
    },
    {
        "name": "page",
        "type": "integer",
        "required": false,
        "description": ""
    },
    {
        "name": "per_page",
        "type": "integer",
        "required": false,
        "description": "Default 20, maximum 100."
    }
]
```

Status: `200`

Example response:

```json
{
    "data": [
        {
            "id": 12,
            "planter_id": 71,
            "round": "first",
            "round_label": "First Audit",
            "attempt_number": 1,
            "is_current": true,
            "status": "queued",
            "status_label": "Queued",
            "is_open": true,
            "is_complete": false,
            "outcome_notes": null,
            "is_signed": false,
            "signed_by_name": null,
            "signed_at": null,
            "sent_at": "2026-11-23T19:51:27+05:30",
            "started_at": null,
            "completed_at": null,
            "assignee": null,
            "completer": null,
            "planter": {
                "id": 71,
                "name": "Pradeep Peiris",
                "identification_number": "RUB/SUS/KEGA/REDE/00001",
                "temporary_id": "TMP-2026-000071",
                "district": "Kegalle",
                "phone": "0773000071"
            }
        }
    ],
    "links": {
        "first": "..."
    },
    "meta": {
        "current_page": 1,
        "last_page": 1,
        "per_page": 20,
        "total": 18
    }
}
```


#### `GET staff/audits/{audit}`

**Audit detail**

- **Access:** staff
- **Description:** The audit, the checklist grouped by module, progress, every attempt for this planter and round, and what the user may do. Cache this for offline work.

Status: `200`

Example response:

```json
{
    "audit": {
        "id": 12,
        "planter_id": 71,
        "round": "first",
        "round_label": "First Audit",
        "attempt_number": 1,
        "is_current": true,
        "status": "in_progress",
        "status_label": "In_progress",
        "is_open": true,
        "is_complete": false,
        "outcome_notes": null,
        "is_signed": false,
        "signed_by_name": null,
        "signed_at": null,
        "sent_at": "2026-11-23T19:51:27+05:30",
        "started_at": "2026-11-24T10:00:00+05:30",
        "completed_at": null,
        "assignee": null,
        "completer": null,
        "planter": {
            "id": 71,
            "name": "Pradeep Peiris",
            "identification_number": "RUB/SUS/KEGA/REDE/00001",
            "temporary_id": "TMP-2026-000071",
            "district": "Kegalle",
            "phone": "0773000071"
        }
    },
    "signature": null,
    "progress": {
        "total": 15,
        "answered": 3,
        "percent": 20
    },
    "checklist": [
        {
            "module": "psm",
            "label": "Product Safety Module (PSM)",
            "items": [
                {
                    "answer_id": 101,
                    "item_id": 1,
                    "code": "PSM-01",
                    "label": "Approved crop protection products only",
                    "description": "Confirm only approved products are used on the plantation.",
                    "result": "pass",
                    "comment": "Records verified."
                }
            ]
        }
    ],
    "attempts": [
        {
            "id": 12,
            "attempt_number": 1,
            "is_current": true,
            "status": "in_progress",
            "status_label": "In_progress",
            "outcome_notes": null,
            "completed_at": null,
            "completed_by": null
        }
    ],
    "permissions": {
        "can_work": true,
        "can_dispatch": false
    }
}
```


#### `POST staff/audits/{audit}/start`

**Start the audit**

- **Access:** staff
- **Description:** Moves queued → in_progress. Returns the audit detail.
- **Note:** Needs: the auditor for that round, or an Administrator.

Status: `200`

Example response:

```json
{
    "audit": {
        "id": 12,
        "planter_id": 71,
        "round": "first",
        "round_label": "First Audit",
        "attempt_number": 1,
        "is_current": true,
        "status": "in_progress",
        "status_label": "In_progress",
        "is_open": true,
        "is_complete": false,
        "outcome_notes": null,
        "is_signed": false,
        "signed_by_name": null,
        "signed_at": null,
        "sent_at": "2026-11-23T19:51:27+05:30",
        "started_at": "2026-11-24T10:00:00+05:30",
        "completed_at": null,
        "assignee": null,
        "completer": null,
        "planter": {
            "id": 71,
            "name": "Pradeep Peiris",
            "identification_number": "RUB/SUS/KEGA/REDE/00001",
            "temporary_id": "TMP-2026-000071",
            "district": "Kegalle",
            "phone": "0773000071"
        }
    },
    "signature": null,
    "progress": {
        "total": 15,
        "answered": 3,
        "percent": 20
    },
    "checklist": [
        {
            "module": "psm",
            "label": "Product Safety Module (PSM)",
            "items": [
                {
                    "answer_id": 101,
                    "item_id": 1,
                    "code": "PSM-01",
                    "label": "Approved crop protection products only",
                    "description": "Confirm only approved products are used on the plantation.",
                    "result": "pass",
                    "comment": "Records verified."
                }
            ]
        }
    ],
    "attempts": [
        {
            "id": 12,
            "attempt_number": 1,
            "is_current": true,
            "status": "in_progress",
            "status_label": "In_progress",
            "outcome_notes": null,
            "completed_at": null,
            "completed_by": null
        }
    ],
    "permissions": {
        "can_work": true,
        "can_dispatch": false
    }
}
```

Errors:

```json
[
    [
        403,
        "Not allowed to work this round."
    ],
    [
        422,
        "Only the current attempt can be started."
    ]
]
```


#### `PUT staff/audits/{audit}/checklist`

**Save checklist answers**

- **Access:** staff
- **Description:** Send any subset of items. Keys of answers are the checklist item_id values from the audit detail. Starts the audit automatically if it is still queued.
- **Note:** Needs: the auditor for that round, or an Administrator.

Request body (json):

```json
[
    {
        "name": "answers",
        "type": "object",
        "required": true,
        "description": "Map of item_id → answer."
    },
    {
        "name": "answers.{item_id}.result",
        "type": "enum",
        "required": true,
        "description": "pending, pass, fail or na."
    },
    {
        "name": "answers.{item_id}.comment",
        "type": "string",
        "required": false,
        "description": "Max 1000."
    }
]
```

Example request:

```json
{
    "answers": {
        "1": {
            "result": "pass",
            "comment": "Records verified."
        },
        "2": {
            "result": "fail",
            "comment": "No waste log."
        }
    }
}
```

Status: `200`

Example response:

```json
{
    "audit": {
        "id": 12,
        "planter_id": 71,
        "round": "first",
        "round_label": "First Audit",
        "attempt_number": 1,
        "is_current": true,
        "status": "in_progress",
        "status_label": "In_progress",
        "is_open": true,
        "is_complete": false,
        "outcome_notes": null,
        "is_signed": false,
        "signed_by_name": null,
        "signed_at": null,
        "sent_at": "2026-11-23T19:51:27+05:30",
        "started_at": "2026-11-24T10:00:00+05:30",
        "completed_at": null,
        "assignee": null,
        "completer": null,
        "planter": {
            "id": 71,
            "name": "Pradeep Peiris",
            "identification_number": "RUB/SUS/KEGA/REDE/00001",
            "temporary_id": "TMP-2026-000071",
            "district": "Kegalle",
            "phone": "0773000071"
        }
    },
    "signature": null,
    "progress": {
        "total": 15,
        "answered": 3,
        "percent": 20
    },
    "checklist": [
        {
            "module": "psm",
            "label": "Product Safety Module (PSM)",
            "items": [
                {
                    "answer_id": 101,
                    "item_id": 1,
                    "code": "PSM-01",
                    "label": "Approved crop protection products only",
                    "description": "Confirm only approved products are used on the plantation.",
                    "result": "pass",
                    "comment": "Records verified."
                }
            ]
        }
    ],
    "attempts": [
        {
            "id": 12,
            "attempt_number": 1,
            "is_current": true,
            "status": "in_progress",
            "status_label": "In_progress",
            "outcome_notes": null,
            "completed_at": null,
            "completed_by": null
        }
    ],
    "permissions": {
        "can_work": true,
        "can_dispatch": false
    }
}
```

Errors:

```json
[
    [
        422,
        "The attempt is not the current one, or it is already complete."
    ]
]
```


#### `POST staff/audits/{audit}/submit`

**Submit for review**

- **Access:** staff
- **Description:** Moves in_progress → in_review. Every checklist item must be answered.
- **Note:** Needs: the auditor for that round, or an Administrator.

Status: `200`

Example response:

```json
{
    "audit": {
        "id": 12,
        "planter_id": 71,
        "round": "first",
        "round_label": "First Audit",
        "attempt_number": 1,
        "is_current": true,
        "status": "in_progress",
        "status_label": "In_progress",
        "is_open": true,
        "is_complete": false,
        "outcome_notes": null,
        "is_signed": false,
        "signed_by_name": null,
        "signed_at": null,
        "sent_at": "2026-11-23T19:51:27+05:30",
        "started_at": "2026-11-24T10:00:00+05:30",
        "completed_at": null,
        "assignee": null,
        "completer": null,
        "planter": {
            "id": 71,
            "name": "Pradeep Peiris",
            "identification_number": "RUB/SUS/KEGA/REDE/00001",
            "temporary_id": "TMP-2026-000071",
            "district": "Kegalle",
            "phone": "0773000071"
        }
    },
    "signature": null,
    "progress": {
        "total": 15,
        "answered": 3,
        "percent": 20
    },
    "checklist": [
        {
            "module": "psm",
            "label": "Product Safety Module (PSM)",
            "items": [
                {
                    "answer_id": 101,
                    "item_id": 1,
                    "code": "PSM-01",
                    "label": "Approved crop protection products only",
                    "description": "Confirm only approved products are used on the plantation.",
                    "result": "pass",
                    "comment": "Records verified."
                }
            ]
        }
    ],
    "attempts": [
        {
            "id": 12,
            "attempt_number": 1,
            "is_current": true,
            "status": "in_progress",
            "status_label": "In_progress",
            "outcome_notes": null,
            "completed_at": null,
            "completed_by": null
        }
    ],
    "permissions": {
        "can_work": true,
        "can_dispatch": false
    }
}
```

Errors:

```json
[
    [
        422,
        "Complete every checklist item before submitting for review."
    ]
]
```


#### `POST staff/audits/{audit}/complete`

**Complete with outcome and signature**

- **Access:** staff
- **Description:** Records the result and the auditor's digital signature, updates the planter summary and sends the result SMS. All checklist items must be answered.
- **Note:** Needs: the auditor for that round, or an Administrator.

Request body (json):

```json
[
    {
        "name": "status",
        "type": "enum",
        "required": true,
        "description": "passed, conditional or failed."
    },
    {
        "name": "outcome_notes",
        "type": "string",
        "required": false,
        "description": "Max 2000."
    },
    {
        "name": "signature",
        "type": "string",
        "required": true,
        "description": "PNG as a data URL: data:image/png;base64,.... Max about 450 KB."
    }
]
```

Example request:

```json
{
    "status": "passed",
    "outcome_notes": "All requirements met.",
    "signature": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUg..."
}
```

Status: `200`

Example response:

```json
{
    "audit": {
        "id": 12,
        "planter_id": 71,
        "round": "first",
        "round_label": "First Audit",
        "attempt_number": 1,
        "is_current": true,
        "status": "passed",
        "status_label": "Passed",
        "is_open": false,
        "is_complete": true,
        "outcome_notes": "All requirements met.",
        "is_signed": true,
        "signed_by_name": "First Auditor",
        "signed_at": "2026-11-24T14:05:10+05:30",
        "sent_at": "2026-11-23T19:51:27+05:30",
        "started_at": "2026-11-24T10:00:00+05:30",
        "completed_at": "2026-11-24T14:05:10+05:30",
        "assignee": null,
        "completer": "First Auditor",
        "planter": {
            "id": 71,
            "name": "Pradeep Peiris",
            "identification_number": "RUB/SUS/KEGA/REDE/00001",
            "temporary_id": "TMP-2026-000071",
            "district": "Kegalle",
            "phone": "0773000071"
        }
    },
    "signature": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUg...",
    "progress": {
        "total": 15,
        "answered": 15,
        "percent": 100
    },
    "checklist": [
        {
            "module": "psm",
            "label": "Product Safety Module (PSM)",
            "items": [
                {
                    "answer_id": 101,
                    "item_id": 1,
                    "code": "PSM-01",
                    "label": "Approved crop protection products only",
                    "description": "Confirm only approved products are used on the plantation.",
                    "result": "pass",
                    "comment": "Records verified."
                }
            ]
        }
    ],
    "attempts": [
        {
            "id": 12,
            "attempt_number": 1,
            "is_current": true,
            "status": "passed",
            "status_label": "Passed",
            "outcome_notes": null,
            "completed_at": null,
            "completed_by": null
        }
    ],
    "permissions": {
        "can_work": false,
        "can_dispatch": false
    }
}
```

Errors:

```json
[
    [
        422,
        "Checklist incomplete, invalid signature image, or invalid outcome."
    ]
]
```


#### `POST staff/audits/{audit}/reopen`

**Reopen (start a new attempt)**

- **Access:** staff
- **Description:** Keeps the previous result in history and creates a new queued attempt. The Second Audit must be reopened before the First.
- **Note:** Needs: Administrator, RRISL Officer, RDD Officer, or the auditor for that round.

Status: `201`

Example response:

```json
{
    "audit": {
        "id": 12,
        "planter_id": 71,
        "round": "first",
        "round_label": "First Audit",
        "attempt_number": 1,
        "is_current": true,
        "status": "queued",
        "status_label": "Queued",
        "is_open": true,
        "is_complete": false,
        "outcome_notes": null,
        "is_signed": false,
        "signed_by_name": null,
        "signed_at": null,
        "sent_at": "2026-11-23T19:51:27+05:30",
        "started_at": null,
        "completed_at": null,
        "assignee": null,
        "completer": null,
        "planter": {
            "id": 71,
            "name": "Pradeep Peiris",
            "identification_number": "RUB/SUS/KEGA/REDE/00001",
            "temporary_id": "TMP-2026-000071",
            "district": "Kegalle",
            "phone": "0773000071"
        }
    }
}
```

Errors:

```json
[
    [
        422,
        "Only the current, completed attempt can be reopened."
    ]
]
```


### Staff: certificates

Visible to Administrators and RRISL Officers only.

#### `GET staff/certificates`

**List certificates**

- **Access:** staff
- **Note:** Needs: Administrator or RRISL Officer.

Query parameters:

```json
[
    {
        "name": "status",
        "type": "enum",
        "required": false,
        "description": "issued (default) or revoked."
    },
    {
        "name": "q",
        "type": "string",
        "required": false,
        "description": "Certificate number, planter name, NIC or SPPNR ID."
    },
    {
        "name": "page",
        "type": "integer",
        "required": false,
        "description": ""
    },
    {
        "name": "per_page",
        "type": "integer",
        "required": false,
        "description": "Default 20, maximum 100."
    }
]
```

Status: `200`

Example response:

```json
{
    "data": [
        {
            "id": 4,
            "planter_id": 71,
            "certificate_number": "SPPNR/CERT/2026/00004",
            "status": "issued",
            "status_label": "Issued",
            "outcome": "certified",
            "outcome_label": "Certified",
            "is_current": true,
            "issued_at": "2026-11-24T15:00:00+05:30",
            "revoked_at": null,
            "revoke_reason": null,
            "verify_url": "https://example.lk/verify/1f0c9b2e7a",
            "planter": {
                "id": 71,
                "name": "Pradeep Peiris",
                "identification_number": "RUB/SUS/KEGA/REDE/00001",
                "district": "Kegalle"
            }
        }
    ],
    "links": {
        "first": "..."
    },
    "meta": {
        "current_page": 1,
        "last_page": 1,
        "per_page": 20,
        "total": 15
    }
}
```


#### `GET staff/certificates/{certificate}`

**Certificate detail**

- **Access:** staff
- **Note:** Needs: Administrator or RRISL Officer.

Status: `200`

Example response:

```json
{
    "certificate": {
        "id": 4,
        "planter_id": 71,
        "certificate_number": "SPPNR/CERT/2026/00004",
        "status": "issued",
        "status_label": "Issued",
        "outcome": "certified",
        "outcome_label": "Certified",
        "is_current": true,
        "issued_at": "2026-11-24T15:00:00+05:30",
        "revoked_at": null,
        "revoke_reason": null,
        "verify_url": "https://example.lk/verify/1f0c9b2e7a",
        "planter": {
            "id": 71,
            "name": "Pradeep Peiris",
            "identification_number": "RUB/SUS/KEGA/REDE/00001",
            "district": "Kegalle"
        }
    }
}
```


#### `POST staff/planters/{planter}/certificates`

**Issue a certificate**

- **Access:** staff
- **Description:** Allowed when the planter is approved, the current Second Audit is passed or conditional, and no active certificate exists. Sends the certificate SMS.
- **Note:** Needs: Administrator or RRISL Officer.

Request body (json):

```json
[
    {
        "name": "notes",
        "type": "string",
        "required": false,
        "description": "Internal notes (max 2000)."
    }
]
```

Example request:

```json
{
    "notes": "Issued at the board meeting."
}
```

Status: `201`

Example response:

```json
{
    "certificate": {
        "id": 4,
        "planter_id": 71,
        "certificate_number": "SPPNR/CERT/2026/00004",
        "status": "issued",
        "status_label": "Issued",
        "outcome": "certified",
        "outcome_label": "Certified",
        "is_current": true,
        "issued_at": "2026-11-24T15:00:00+05:30",
        "revoked_at": null,
        "revoke_reason": null,
        "verify_url": "https://example.lk/verify/1f0c9b2e7a",
        "planter": {
            "id": 71,
            "name": "Pradeep Peiris",
            "identification_number": "RUB/SUS/KEGA/REDE/00001",
            "district": "Kegalle"
        }
    }
}
```

Errors:

```json
[
    [
        422,
        "The planter is not eligible, or already holds an active certificate."
    ]
]
```


#### `POST staff/certificates/{certificate}/revoke`

**Revoke a certificate**

- **Access:** staff
- **Note:** Needs: Administrator or RRISL Officer.

Request body (json):

```json
[
    {
        "name": "revoke_reason",
        "type": "string",
        "required": true,
        "description": "Shown on the verification page (max 2000)."
    }
]
```

Example request:

```json
{
    "revoke_reason": "Non-compliance found during a spot check."
}
```

Status: `200`

Example response:

```json
{
    "certificate": {
        "id": 4,
        "planter_id": 71,
        "certificate_number": "SPPNR/CERT/2026/00004",
        "status": "revoked",
        "status_label": "Revoked",
        "outcome": "certified",
        "outcome_label": "Certified",
        "is_current": false,
        "issued_at": "2026-11-24T15:00:00+05:30",
        "revoked_at": "2026-11-25T09:30:00+05:30",
        "revoke_reason": "Non-compliance found during a spot check.",
        "verify_url": "https://example.lk/verify/1f0c9b2e7a",
        "planter": {
            "id": 71,
            "name": "Pradeep Peiris",
            "identification_number": "RUB/SUS/KEGA/REDE/00001",
            "district": "Kegalle"
        }
    }
}
```


### Staff: SMS templates

Read-only template catalogue and sample sending.

#### `GET staff/sms-templates`

**List SMS templates**

- **Access:** staff

Status: `200`

Example response:

```json
{
    "templates": [
        {
            "key": "registration_approved",
            "name": "Registration approved",
            "trigger": "When a registration is approved",
            "status": "active",
            "body": "Your SPPNR registration is approved...",
            "sample": "...",
            "placeholders": [
                "name",
                "identification_number"
            ],
            "length": 120
        }
    ],
    "gateway_enabled": true,
    "sender_id": "RRISL"
}
```


#### `POST staff/sms-templates/send-samples`

**Send sample SMS**

- **Access:** staff
- **Description:** Sends one template (template given) or every template (omitted) to a phone number, filled with sample data.
- **Note:** Needs: Administrator or RRISL Officer.

Request body (json):

```json
[
    {
        "name": "phone",
        "type": "string",
        "required": true,
        "description": ""
    },
    {
        "name": "template",
        "type": "string",
        "required": false,
        "description": "A template key. Omit to send all."
    }
]
```

Example request:

```json
{
    "phone": "0771234567",
    "template": "registration_approved"
}
```

Status: `200`

Example response:

```json
{
    "message": "Sample SMS sent."
}
```

Errors:

```json
[
    [
        422,
        "The gateway rejected the message."
    ]
]
```


### Offline sync

Upload the offline queue in one call. Operations run in the order sent, each on its own: one failure never blocks the rest. Use a fresh UUID as client_id for every recorded action.

#### `POST staff/sync`

**Upload offline operations**

- **Access:** staff
- **Description:** Up to 100 operations per call. Supported types and payloads are listed below the example. Results come back in the same order. For paper-form registrations with an attached file use POST registrations/offline directly while online.

Request body (json):

```json
[
    {
        "name": "operations",
        "type": "array",
        "required": true,
        "description": "1–100 operations."
    },
    {
        "name": "operations[].client_id",
        "type": "string",
        "required": true,
        "description": "Unique per action (max 120). Idempotency key."
    },
    {
        "name": "operations[].type",
        "type": "string",
        "required": true,
        "description": "See the table below."
    },
    {
        "name": "operations[].payload",
        "type": "object",
        "required": false,
        "description": "Fields for that type."
    }
]
```

Example request:

```json
{
    "operations": [
        {
            "client_id": "7b1e7c52-4d0a-4a6f-9b31-0c5ad0c1e001",
            "type": "audit.start",
            "payload": {
                "audit_id": 12
            }
        },
        {
            "client_id": "7b1e7c52-4d0a-4a6f-9b31-0c5ad0c1e002",
            "type": "audit.checklist",
            "payload": {
                "audit_id": 12,
                "answers": {
                    "1": {
                        "result": "pass",
                        "comment": "OK"
                    }
                }
            }
        },
        {
            "client_id": "7b1e7c52-4d0a-4a6f-9b31-0c5ad0c1e003",
            "type": "audit.submit",
            "payload": {
                "audit_id": 12
            }
        }
    ]
}
```

Status: `200`

Example response:

```json
{
    "results": [
        {
            "client_id": "7b1e7c52-4d0a-4a6f-9b31-0c5ad0c1e001",
            "type": "audit.start",
            "status": "applied",
            "replayed": false,
            "data": {
                "audit_id": 12,
                "planter_id": 71,
                "round": "first",
                "status": "in_progress",
                "is_complete": false
            }
        },
        {
            "client_id": "7b1e7c52-4d0a-4a6f-9b31-0c5ad0c1e002",
            "type": "audit.checklist",
            "status": "applied",
            "replayed": false,
            "data": {
                "audit_id": 12,
                "status": "in_progress"
            }
        },
        {
            "client_id": "7b1e7c52-4d0a-4a6f-9b31-0c5ad0c1e003",
            "type": "audit.submit",
            "status": "rejected",
            "message": "Complete every checklist item before submitting for review."
        }
    ]
}
```

- **Offline sync operation types:** ```json
[
    [
        "planter.create",
        "Same fields as POST staff/planters.",
        "Administrator, RRISL Officer, RDD Officer, Field Officer"
    ],
    [
        "planter.update",
        "planter_id + the fields of PUT staff/planters/{planter}.",
        "Administrator, RRISL Officer, RDD Officer, Field Officer"
    ],
    [
        "audit.start",
        "audit_id.",
        "Auditor for that round, Administrator"
    ],
    [
        "audit.checklist",
        "audit_id + answers (same as PUT checklist).",
        "Auditor for that round, Administrator"
    ],
    [
        "audit.submit",
        "audit_id.",
        "Auditor for that round, Administrator"
    ],
    [
        "audit.complete",
        "audit_id + status, outcome_notes, signature (same as POST complete).",
        "Auditor for that round, Administrator"
    ]
]
```
- **Offline sync result statuses:** ```json
[
    [
        "applied",
        "Saved. replayed:true means this client_id was already applied earlier and nothing changed. Remove it from the queue."
    ],
    [
        "rejected",
        "Invalid or not allowed (errors or message explain). Do not resend unchanged: show the problem to the user, fix the data, then queue a new operation with a new client_id."
    ],
    [
        "failed",
        "Temporary server error. Keep it queued and resend the same client_id later."
    ]
]
```

### Administration

User management is for Administrators and the Developer. The maintenance switch is Developer only, like the Settings page. Administrators cannot see or change Developer accounts.

#### `GET admin/users`

**List users**

- **Access:** admin

Query parameters:

```json
[
    {
        "name": "q",
        "type": "string",
        "required": false,
        "description": "Name, email or phone."
    },
    {
        "name": "role",
        "type": "enum",
        "required": false,
        "description": "A role key."
    },
    {
        "name": "status",
        "type": "enum",
        "required": false,
        "description": "active or inactive."
    },
    {
        "name": "page",
        "type": "integer",
        "required": false,
        "description": ""
    },
    {
        "name": "per_page",
        "type": "integer",
        "required": false,
        "description": "Default 20, maximum 100."
    }
]
```

Status: `200`

Example response:

```json
{
    "data": [
        {
            "id": 5,
            "name": "First Auditor",
            "email": "first.auditor@rrisl.gov.lk",
            "phone": "0112600003",
            "role": "first_auditor",
            "role_label": "First Auditor",
            "status": "active",
            "last_login_at": "2026-11-24T12:30:23+05:30",
            "permissions": {
                "view_registry": true,
                "review_registrations": false,
                "manage_planter_records": false,
                "delete_planters": false,
                "dispatch_audits": false,
                "work_first_audit": true,
                "work_second_audit": false,
                "view_audits": true,
                "manage_certificates": false,
                "resend_planter_sms": false,
                "send_sms_samples": false,
                "manage_master_data": false,
                "manage_users": false,
                "manage_settings": false,
                "view_api_docs": false
            }
        }
    ],
    "links": {
        "first": "..."
    },
    "meta": {
        "current_page": 1,
        "last_page": 1,
        "per_page": 20,
        "total": 25
    }
}
```


#### `POST admin/users`

**Create a user**

- **Access:** admin

Request body (json):

```json
[
    {
        "name": "name",
        "type": "string",
        "required": true,
        "description": ""
    },
    {
        "name": "email",
        "type": "string",
        "required": true,
        "description": "Unique."
    },
    {
        "name": "phone",
        "type": "string",
        "required": false,
        "description": ""
    },
    {
        "name": "role",
        "type": "enum",
        "required": true,
        "description": "A role key. Only the Developer may assign developer."
    },
    {
        "name": "status",
        "type": "enum",
        "required": true,
        "description": "active or inactive."
    },
    {
        "name": "password",
        "type": "string",
        "required": true,
        "description": "At least 10 characters, with upper and lower case letters and a number."
    },
    {
        "name": "password_confirmation",
        "type": "string",
        "required": true,
        "description": ""
    }
]
```

Example request:

```json
{
    "name": "Sunil Fernando",
    "email": "sunil@rrisl.gov.lk",
    "phone": "0112600999",
    "role": "field_officer",
    "status": "active",
    "password": "Passw0rd!2026",
    "password_confirmation": "Passw0rd!2026"
}
```

Status: `201`

Example response:

```json
{
    "user": {
        "id": 5,
        "name": "First Auditor",
        "email": "first.auditor@rrisl.gov.lk",
        "phone": "0112600003",
        "role": "first_auditor",
        "role_label": "First Auditor",
        "status": "active",
        "last_login_at": "2026-11-24T12:30:23+05:30",
        "permissions": {
            "view_registry": true,
            "review_registrations": false,
            "manage_planter_records": false,
            "delete_planters": false,
            "dispatch_audits": false,
            "work_first_audit": true,
            "work_second_audit": false,
            "view_audits": true,
            "manage_certificates": false,
            "resend_planter_sms": false,
            "send_sms_samples": false,
            "manage_master_data": false,
            "manage_users": false,
            "manage_settings": false,
            "view_api_docs": false
        }
    }
}
```


#### `PUT admin/users/{user}`

**Update a user**

- **Access:** admin
- **Description:** Same fields as create; leave password empty to keep it. Deactivating a user signs them out of every device. The last active administrator cannot be demoted or deactivated, and you cannot deactivate yourself.

Status: `200`

Example response:

```json
{
    "user": {
        "id": 5,
        "name": "First Auditor",
        "email": "first.auditor@rrisl.gov.lk",
        "phone": "0112600003",
        "role": "first_auditor",
        "role_label": "First Auditor",
        "status": "active",
        "last_login_at": "2026-11-24T12:30:23+05:30",
        "permissions": {
            "view_registry": true,
            "review_registrations": false,
            "manage_planter_records": false,
            "delete_planters": false,
            "dispatch_audits": false,
            "work_first_audit": true,
            "work_second_audit": false,
            "view_audits": true,
            "manage_certificates": false,
            "resend_planter_sms": false,
            "send_sms_samples": false,
            "manage_master_data": false,
            "manage_users": false,
            "manage_settings": false,
            "view_api_docs": false
        }
    }
}
```

Errors:

```json
[
    [
        403,
        "Target is a Developer account and you are not the Developer."
    ],
    [
        422,
        "Validation or last-administrator rule."
    ]
]
```


#### `DELETE admin/users/{user}`

**Delete a user**

- **Access:** admin
- **Description:** Removes the account and its tokens. You cannot delete yourself or the last administrator.

Status: `200`

Example response:

```json
{
    "message": "User account deleted."
}
```


#### `GET admin/maintenance`

**Read maintenance mode**

- **Access:** developer

Status: `200`

Example response:

```json
{
    "enabled": false,
    "message": "Upgrading the system. Back at 6 PM."
}
```


#### `PUT admin/maintenance`

**Switch maintenance mode**

- **Access:** developer
- **Description:** The same switch as Settings → Maintenance mode. While on, only administrators and the Developer can use the system.

Request body (json):

```json
[
    {
        "name": "enabled",
        "type": "boolean",
        "required": true,
        "description": ""
    },
    {
        "name": "message",
        "type": "string",
        "required": false,
        "description": "Shown to everyone else (max 500)."
    }
]
```

Example request:

```json
{
    "enabled": true,
    "message": "Upgrading the system. Back at 6 PM."
}
```

Status: `200`

Example response:

```json
{
    "enabled": true,
    "message": "Upgrading the system. Back at 6 PM."
}
```



