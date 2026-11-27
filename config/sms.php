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
            'body' => "{brand}: Registration received.\nName: {name}\nSPPNR ID: {registration_number}\nRef: {reference}\nKeep this for tracking.",
            'body_si' => "{brand}: ලියාපදිංචිය ලැබුණි.\nනම: {name}\nSPPNR ID: {registration_number}\nයොමුව: {reference}\nලුහුබැඳීමට තබාගන්න.",
            'body_ta' => "{brand}: பதிவு பெறப்பட்டது.\nபெயர்: {name}\nSPPNR ID: {registration_number}\nகுறிப்பு: {reference}\nகண்காணிப்புக்கு வைத்திருங்கள்.",
            'placeholders' => ['brand', 'name', 'registration_number', 'reference'],
            'sample' => [
                'brand' => 'SPPNR',
                'name' => 'Nimal Perera',
                'registration_number' => 'Pending',
                'reference' => 'TMP-2026-000001',
            ],
        ],

        'registration_approved' => [
            'name' => 'Registration approved',
            'name_si' => 'ලියාපදිංචිය අනුමත කළා',
            'name_ta' => 'பதிவு அங்கீகரிக்கப்பட்டது',
            'trigger' => 'RRISL/RDD/Admin approves a pending application',
            'status' => 'active',
            'body' => "{brand}: Registration approved.\nName: {name}\nSPPNR ID: {registration_number}\nUse this ID with your NIC at set-password to create a login.",
            'body_si' => "{brand}: ලියාපදිංචිය අනුමත විය.\nනම: {name}\nSPPNR ID: {registration_number}\nපිවිසුම සෑදීමට set-password හිදී මෙම ID සහ ජා.හැ. අංකය භාවිතා කරන්න.",
            'body_ta' => "{brand}: பதிவு அங்கீகரிக்கப்பட்டது.\nபெயர்: {name}\nSPPNR ID: {registration_number}\nஉள்நுழைவை உருவாக்க set-password பக்கத்தில் இந்த ID மற்றும் தேசிய அடையாள அட்டை எண்ணைப் பயன்படுத்துங்கள்.",
            'placeholders' => ['brand', 'name', 'registration_number'],
            'sample' => [
                'brand' => 'SPPNR',
                'name' => 'Nimal Perera',
                'registration_number' => 'RUB/SUS/KEGA/AMIT/00001',
            ],
        ],

        'registration_rejected' => [
            'name' => 'Registration rejected',
            'name_si' => 'ලියාපදිංචිය ප්‍රතික්ෂේප කළා',
            'name_ta' => 'பதிவு நிராகரிக்கப்பட்டது',
            'trigger' => 'RRISL/RDD/Admin rejects a pending application',
            'status' => 'active',
            'body' => "{brand}: Application not approved.\nName: {name}\nSPPNR ID: {registration_number}\nReason: {reason}",
            'body_si' => "{brand}: අයදුම්පත අනුමත නොවීය.\nනම: {name}\nSPPNR ID: {registration_number}\nහේතුව: {reason}",
            'body_ta' => "{brand}: விண்ணப்பம் அங்கீகரிக்கப்படவில்லை.\nபெயர்: {name}\nSPPNR ID: {registration_number}\nகாரணம்: {reason}",
            'placeholders' => ['brand', 'name', 'registration_number', 'reason'],
            'sample' => [
                'brand' => 'SPPNR',
                'name' => 'Nimal Perera',
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
            'body' => "{brand}: First Audit queued.\nName: {name}\nSPPNR ID: {registration_number}\nAn auditor will contact you.",
            'body_si' => "{brand}: පළමු විගණනය සැලසුම් කළා.\nනම: {name}\nSPPNR ID: {registration_number}\nවිගණකයෙක් ඔබව සම්බන්ධ කරගනී.",
            'body_ta' => "{brand}: முதல் தணிக்கை வரிசையில்.\nபெயர்: {name}\nSPPNR ID: {registration_number}\nதணிக்கையாளர் உங்களைத் தொடர்புகொள்வார்.",
            'placeholders' => ['brand', 'name', 'registration_number'],
            'sample' => [
                'brand' => 'SPPNR',
                'name' => 'Nimal Perera',
                'registration_number' => 'RUB/SUS/KEGA/AMIT/00001',
            ],
        ],

        'first_audit_result' => [
            'name' => 'First Audit result',
            'name_si' => 'පළමු විගණන ප්‍රතිඵලය',
            'name_ta' => 'முதல் தணிக்கை முடிவு',
            'trigger' => 'First Auditor completes the First Audit round',
            'status' => 'active',
            'body' => "{brand}: First Audit result.\nName: {name}\nSPPNR ID: {registration_number}\nResult: {outcome}\n{notes}",
            'body_si' => "{brand}: පළමු විගණන ප්‍රතිඵලය.\nනම: {name}\nSPPNR ID: {registration_number}\nප්‍රතිඵලය: {outcome}\n{notes}",
            'body_ta' => "{brand}: முதல் தணிக்கை முடிவு.\nபெயர்: {name}\nSPPNR ID: {registration_number}\nமுடிவு: {outcome}\n{notes}",
            'placeholders' => ['brand', 'name', 'registration_number', 'outcome', 'notes'],
            'sample' => [
                'brand' => 'SPPNR',
                'name' => 'Nimal Perera',
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
            'body' => "{brand}: Second Audit queued.\nName: {name}\nSPPNR ID: {registration_number}\nPlease be available for the assessment.",
            'body_si' => "{brand}: දෙවන විගණනය සැලසුම් කළා.\nනම: {name}\nSPPNR ID: {registration_number}\nතක්සේරුවට ලබාගත හැකි වන්න.",
            'body_ta' => "{brand}: இரண்டாம் தணிக்கை வரிசையில்.\nபெயர்: {name}\nSPPNR ID: {registration_number}\nமதிப்பீட்டுக்கு கிடைக்கும்படி இருங்கள்.",
            'placeholders' => ['brand', 'name', 'registration_number'],
            'sample' => [
                'brand' => 'SPPNR',
                'name' => 'Nimal Perera',
                'registration_number' => 'RUB/SUS/KEGA/AMIT/00001',
            ],
        ],

        'second_audit_result' => [
            'name' => 'Second Audit result',
            'name_si' => 'දෙවන විගණන ප්‍රතිඵලය',
            'name_ta' => 'இரண்டாம் தணிக்கை முடிவு',
            'trigger' => 'Second Auditor completes the Second Audit round',
            'status' => 'active',
            'body' => "{brand}: Second Audit result.\nName: {name}\nSPPNR ID: {registration_number}\nResult: {outcome}\n{notes}",
            'body_si' => "{brand}: දෙවන විගණන ප්‍රතිඵලය.\nනම: {name}\nSPPNR ID: {registration_number}\nප්‍රතිඵලය: {outcome}\n{notes}",
            'body_ta' => "{brand}: இரண்டாம் தணிக்கை முடிவு.\nபெயர்: {name}\nSPPNR ID: {registration_number}\nமுடிவு: {outcome}\n{notes}",
            'placeholders' => ['brand', 'name', 'registration_number', 'outcome', 'notes'],
            'sample' => [
                'brand' => 'SPPNR',
                'name' => 'Nimal Perera',
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
            'body' => "{brand}: Certificate issued.\nName: {name}\nSPPNR ID: {registration_number}\nNo: {certificate_number}\nVerify: {verify_url}",
            'body_si' => "{brand}: සහතිකය නිකුත් කළා.\nනම: {name}\nSPPNR ID: {registration_number}\nඅංකය: {certificate_number}\nතහවුරු: {verify_url}",
            'body_ta' => "{brand}: சான்றிதழ் வழங்கப்பட்டது.\nபெயர்: {name}\nSPPNR ID: {registration_number}\nஎண்: {certificate_number}\nசரிபார்க்க: {verify_url}",
            'placeholders' => ['brand', 'name', 'registration_number', 'certificate_number', 'verify_url'],
            'sample' => [
                'brand' => 'SPPNR',
                'name' => 'Nimal Perera',
                'registration_number' => 'RUB/SUS/KEGA/AMIT/00001',
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
            'body' => "{brand}: Certificate revoked.\nName: {name}\nSPPNR ID: {registration_number}\nNo: {certificate_number}\nReason: {reason}",
            'body_si' => "{brand}: සහතිකය අවලංගු කළා.\nනම: {name}\nSPPNR ID: {registration_number}\nඅංකය: {certificate_number}\nහේතුව: {reason}",
            'body_ta' => "{brand}: சான்றிதழ் ரத்து செய்யப்பட்டது.\nபெயர்: {name}\nSPPNR ID: {registration_number}\nஎண்: {certificate_number}\nகாரணம்: {reason}",
            'placeholders' => ['brand', 'name', 'registration_number', 'certificate_number', 'reason'],
            'sample' => [
                'brand' => 'SPPNR',
                'name' => 'Nimal Perera',
                'registration_number' => 'RUB/SUS/KEGA/AMIT/00001',
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
