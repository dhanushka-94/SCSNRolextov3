<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SMS message templates (Notify.lk)
    |--------------------------------------------------------------------------
    |
    | Each template has the same message in three languages:
    |   body / name / sample            English (en)
    |   body_si / name_si / sample_si   Sinhala (si)
    |   body_ta / name_ta / sample_ta   Tamil (ta)
    |
    | Body supports {placeholders}. Keep each body ≤ 320 characters.
    | Sinhala and Tamil texts are sent as Unicode SMS, which fits about 70
    | characters per segment, so they cost more than English.
    | status: active = currently sent by the app; planned = catalogued only.
    |
    */

    'templates' => [

        'registration_submitted' => [
            'name' => 'Registration submitted',
            'name_si' => 'ලියාපදිංචිය ඉදිරිපත් කළා',
            'name_ta' => 'பதிவு சமர்ப்பிக்கப்பட்டது',
            'trigger' => 'Planter completes online or offline registration',
            'status' => 'active',
            'body' => '{brand}: Registration received. Ref: {reference}. Keep this for tracking. Permanent ID is issued on approval.',
            'body_si' => '{brand}: ලියාපදිංචි අයදුම්පත ලැබුණි. යොමු අංකය: {reference}. ලුහුබැඳීම සඳහා මෙය තබාගන්න. අනුමැතියෙන් පසු ස්ථිර ID ලැබේ.',
            'body_ta' => '{brand}: பதிவு விண்ணப்பம் பெறப்பட்டது. குறிப்பு எண்: {reference}. கண்காணிப்புக்கு இதை வைத்திருங்கள். அங்கீகாரத்துக்குப் பின் நிரந்தர ID வழங்கப்படும்.',
            'placeholders' => ['brand', 'reference'],
            'sample' => [
                'brand' => 'SPPNR',
                'reference' => 'RUB/SUS/KEGA/AMIT/TMP01',
            ],
        ],

        'registration_approved' => [
            'name' => 'Registration approved',
            'name_si' => 'ලියාපදිංචිය අනුමත කළා',
            'name_ta' => 'பதிவு அங்கீகரிக்கப்பட்டது',
            'trigger' => 'RRISL/RDD/Admin approves a pending application',
            'status' => 'active',
            'body' => '{brand}: Approved. Your ID: {registration_number}. Use this ID with your NIC at set-password to create a login.',
            'body_si' => '{brand}: අනුමත විය. ඔබේ ID: {registration_number}. පිවිසුම සෑදීමට set-password හිදී මෙම ID සහ ජා.හැ. අංකය භාවිතා කරන්න.',
            'body_ta' => '{brand}: அங்கீகரிக்கப்பட்டது. உங்கள் ID: {registration_number}. உள்நுழைவை உருவாக்க set-password பக்கத்தில் இந்த ID மற்றும் தேசிய அடையாள அட்டை எண்ணைப் பயன்படுத்துங்கள்.',
            'placeholders' => ['brand', 'registration_number'],
            'sample' => [
                'brand' => 'SPPNR',
                'registration_number' => 'RUB/SUS/KEGA/AMIT/00001',
            ],
        ],

        'registration_rejected' => [
            'name' => 'Registration rejected',
            'name_si' => 'ලියාපදිංචිය ප්‍රතික්ෂේප කළා',
            'name_ta' => 'பதிவு நிராகரிக்கப்பட்டது',
            'trigger' => 'RRISL/RDD/Admin rejects a pending application',
            'status' => 'active',
            'body' => '{brand}: Application not approved. ID: {registration_number}. Reason: {reason}',
            'body_si' => '{brand}: අයදුම්පත අනුමත නොවීය. ID: {registration_number}. හේතුව: {reason}',
            'body_ta' => '{brand}: விண்ணப்பம் அங்கீகரிக்கப்படவில்லை. ID: {registration_number}. காரணம்: {reason}',
            'placeholders' => ['brand', 'registration_number', 'reason'],
            'sample' => [
                'brand' => 'SPPNR',
                'registration_number' => 'RUB/SUS/KEGA/AMIT/00002',
                'reason' => 'Incomplete documents',
            ],
            'sample_si' => [
                'reason' => 'ලේඛන අසම්පූර්ණයි',
            ],
            'sample_ta' => [
                'reason' => 'ஆவணங்கள் முழுமையற்றவை',
            ],
        ],

        'first_audit_queued' => [
            'name' => 'First Audit queued',
            'name_si' => 'පළමු විගණනය සැලසුම් කළා',
            'name_ta' => 'முதல் தணிக்கை வரிசையில்',
            'trigger' => 'Officer sends planter to First Audit',
            'status' => 'active',
            'body' => '{brand}: First Audit queued for {registration_number}. An auditor will contact you / visit as scheduled.',
            'body_si' => '{brand}: {registration_number} සඳහා පළමු විගණනය සැලසුම් කර ඇත. විගණකයෙක් නියමිත වේලාවට ඔබව සම්බන්ධ කරගනු ඇත.',
            'body_ta' => '{brand}: {registration_number} க்கான முதல் தணிக்கை வரிசையில் உள்ளது. தணிக்கையாளர் திட்டமிட்டபடி உங்களைத் தொடர்புகொள்வார்.',
            'placeholders' => ['brand', 'registration_number'],
            'sample' => [
                'brand' => 'SPPNR',
                'registration_number' => 'RUB/SUS/KEGA/AMIT/00001',
            ],
        ],

        'first_audit_result' => [
            'name' => 'First Audit result',
            'name_si' => 'පළමු විගණන ප්‍රතිඵලය',
            'name_ta' => 'முதல் தணிக்கை முடிவு',
            'trigger' => 'First Auditor completes the First Audit round',
            'status' => 'active',
            'body' => '{brand}: First Audit result for {registration_number}: {outcome}. {notes}',
            'body_si' => '{brand}: {registration_number} හි පළමු විගණන ප්‍රතිඵලය: {outcome}. {notes}',
            'body_ta' => '{brand}: {registration_number} இன் முதல் தணிக்கை முடிவு: {outcome}. {notes}',
            'placeholders' => ['brand', 'registration_number', 'outcome', 'notes'],
            'sample' => [
                'brand' => 'SPPNR',
                'registration_number' => 'RUB/SUS/KEGA/AMIT/00001',
                'outcome' => 'PASSED',
                'notes' => 'Next: Second Audit.',
            ],
            'sample_si' => [
                'outcome' => 'සමත්',
                'notes' => 'ඊළඟට: දෙවන විගණනය.',
            ],
            'sample_ta' => [
                'outcome' => 'தேர்ச்சி',
                'notes' => 'அடுத்து: இரண்டாம் தணிக்கை.',
            ],
        ],

        'second_audit_queued' => [
            'name' => 'Second Audit queued',
            'name_si' => 'දෙවන විගණනය සැලසුම් කළා',
            'name_ta' => 'இரண்டாம் தணிக்கை வரிசையில்',
            'trigger' => 'Officer sends planter to Second Audit (Final round)',
            'status' => 'active',
            'body' => '{brand}: Second Audit queued for {registration_number}. Please be available for the assessment.',
            'body_si' => '{brand}: {registration_number} සඳහා දෙවන විගණනය සැලසුම් කර ඇත. කරුණාකර තක්සේරුව සඳහා ලබාගත හැකි වන්න.',
            'body_ta' => '{brand}: {registration_number} க்கான இரண்டாம் தணிக்கை வரிசையில் உள்ளது. மதிப்பீட்டுக்கு கிடைக்கும்படி இருங்கள்.',
            'placeholders' => ['brand', 'registration_number'],
            'sample' => [
                'brand' => 'SPPNR',
                'registration_number' => 'RUB/SUS/KEGA/AMIT/00001',
            ],
        ],

        'second_audit_result' => [
            'name' => 'Second Audit result',
            'name_si' => 'දෙවන විගණන ප්‍රතිඵලය',
            'name_ta' => 'இரண்டாம் தணிக்கை முடிவு',
            'trigger' => 'Second Auditor completes the Second Audit round',
            'status' => 'active',
            'body' => '{brand}: Second Audit result for {registration_number}: {outcome}. {notes}',
            'body_si' => '{brand}: {registration_number} හි දෙවන විගණන ප්‍රතිඵලය: {outcome}. {notes}',
            'body_ta' => '{brand}: {registration_number} இன் இரண்டாம் தணிக்கை முடிவு: {outcome}. {notes}',
            'placeholders' => ['brand', 'registration_number', 'outcome', 'notes'],
            'sample' => [
                'brand' => 'SPPNR',
                'registration_number' => 'RUB/SUS/KEGA/AMIT/00001',
                'outcome' => 'PASSED',
                'notes' => 'Certificate may be issued next.',
            ],
            'sample_si' => [
                'outcome' => 'සමත්',
                'notes' => 'ඊළඟට සහතිකය නිකුත් කළ හැක.',
            ],
            'sample_ta' => [
                'outcome' => 'தேர்ச்சி',
                'notes' => 'அடுத்து சான்றிதழ் வழங்கப்படலாம்.',
            ],
        ],

        'certificate_issued' => [
            'name' => 'Certificate issued',
            'name_si' => 'සහතිකය නිකුත් කළා',
            'name_ta' => 'சான்றிதழ் வழங்கப்பட்டது',
            'trigger' => 'RRISL/Admin issues an SPPNR certificate',
            'status' => 'active',
            'body' => '{brand}: Certificate issued. No: {certificate_number}. Verify: {verify_url}',
            'body_si' => '{brand}: සහතිකය නිකුත් කරන ලදී. අංකය: {certificate_number}. තහවුරු කිරීම: {verify_url}',
            'body_ta' => '{brand}: சான்றிதழ் வழங்கப்பட்டது. எண்: {certificate_number}. சரிபார்க்க: {verify_url}',
            'placeholders' => ['brand', 'certificate_number', 'verify_url'],
            'sample' => [
                'brand' => 'SPPNR',
                'certificate_number' => 'SPPNR/CERT/2026/00001',
                'verify_url' => 'http://127.0.0.1:8000/verify',
            ],
        ],

        'certificate_revoked' => [
            'name' => 'Certificate revoked',
            'name_si' => 'සහතිකය අවලංගු කළා',
            'name_ta' => 'சான்றிதழ் ரத்து செய்யப்பட்டது',
            'trigger' => 'RRISL/Admin revokes a certificate',
            'status' => 'active',
            'body' => '{brand}: Certificate {certificate_number} revoked. Reason: {reason}',
            'body_si' => '{brand}: සහතිකය {certificate_number} අවලංගු කරන ලදී. හේතුව: {reason}',
            'body_ta' => '{brand}: சான்றிதழ் {certificate_number} ரத்து செய்யப்பட்டது. காரணம்: {reason}',
            'placeholders' => ['brand', 'certificate_number', 'reason'],
            'sample' => [
                'brand' => 'SPPNR',
                'certificate_number' => 'SPPNR/CERT/2026/00001',
                'reason' => 'Compliance review',
            ],
            'sample_si' => [
                'reason' => 'අනුකූලතා සමාලෝචනය',
            ],
            'sample_ta' => [
                'reason' => 'இணக்க மதிப்பாய்வு',
            ],
        ],

    ],

];
