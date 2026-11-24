<!DOCTYPE html>
<html lang="si">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ config('app.full_name') }}</title>
    <style>
        @font-face {
            font-family: 'NotoSinhala';
            font-style: normal;
            font-weight: normal;
            src: url('{{ $fontRegular }}') format('truetype');
        }
        @font-face {
            font-family: 'NotoSinhala';
            font-style: normal;
            font-weight: bold;
            src: url('{{ $fontBold }}') format('truetype');
        }
        @if (! empty($fontTamilRegular))
        @font-face {
            font-family: 'NotoTamil';
            font-style: normal;
            font-weight: normal;
            src: url('{{ $fontTamilRegular }}') format('truetype');
        }
        @font-face {
            font-family: 'NotoTamil';
            font-style: normal;
            font-weight: bold;
            src: url('{{ $fontTamilBold ?? $fontTamilRegular }}') format('truetype');
        }
        @endif
        @if (! empty($browser))
        /* Browser engine: real OpenType shaping and per-glyph font fallback (Latin, Sinhala, Tamil). */
        @page { size: A4; margin: 12mm; }
        * { font-family: 'Segoe UI', Arial, 'Noto Sans', 'NotoSinhala', 'NotoTamil', sans-serif; }
        body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        @else
        * {
            font-family: 'NotoSinhala' !important;
        }
        body { color: #2a2118; font-size: 11px; line-height: 1.4; font-family: 'NotoSinhala' !important; }
        h1, h2, h3, .label, .note, .checks, .tick-label, .powered-text, .header-logo-caption {
            font-family: 'NotoSinhala' !important;
        }
        /* Tamil runs: DomPDF cannot fall back per glyph, so they use their own family. */
        .ta, .ta * { font-family: 'NotoTamil' !important; }
        @endif
        body { color: #2a2118; font-size: 11px; line-height: 1.4; }
        h1 { font-size: 14px; margin: 0 0 2px; color: #123826; font-weight: bold; }
        h2 { font-size: 10px; margin: 0 0 6px; color: #6b4423; font-weight: bold; }
        h3 { font-size: 11px; margin: 12px 0 8px; color: #123826; border-bottom: 1px solid #c4a574; padding-bottom: 3px; font-weight: bold; }
        .header { border-bottom: 2px solid #1b4d32; padding-bottom: 10px; margin-bottom: 10px; }
        .logo { width: 52px; height: 52px; }
        .header-logos td { text-align: center; vertical-align: middle; padding: 0 4px; }
        .header-logo { width: 40px; height: 40px; }
        .header-logo-caption { font-size: 7px; color: #6f6256; margin-top: 2px; line-height: 1.2; }
        .field { margin-bottom: 9px; }
        .label { font-size: 10px; font-weight: bold; color: #4a2e18; margin-bottom: 4px; }
        .line { border-bottom: 1px solid #c4a574; height: 18px; }
        .box { border: 1px solid #c4a574; height: 44px; }
        .box-sm { border: 1px solid #c4a574; height: 28px; }
        .note { font-size: 9px; color: #6f6256; margin-top: 10px; }
        .checks { margin-top: 4px; font-size: 10px; }
        .tick {
            display: inline-block;
            margin: 0 12px 7px 0;
            vertical-align: middle;
        }
        .tick-box {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 1.2px solid #2a2118;
            vertical-align: middle;
            margin-right: 5px;
        }
        .tick-label { vertical-align: middle; }
        .type-card { border: 1px solid #c4a574; padding: 6px 8px; height: 62px; }
        .type-name { font-size: 10px; font-weight: bold; color: #123826; }
        .type-desc { font-size: 8px; color: #6f6256; margin-top: 2px; }
        table { width: 100%; border-collapse: collapse; }
        td { vertical-align: top; }
        .product-table td { width: 50%; padding: 0 6px 6px 0; }
        .powered-footer {
            margin-top: 14px;
            padding-top: 10px;
            border-top: 1.5px solid #1b4d32;
            text-align: center;
        }
        .powered-logo { width: 42px; height: 42px; }
        .powered-text {
            margin: 8px 0 0;
            font-size: 9px;
            color: #2a2118;
            line-height: 1.4;
        }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td width="60" style="vertical-align: middle;">
                <img class="logo" src="{{ $logo }}" alt="SPPNR">
            </td>
            <td style="vertical-align: middle;">
                <h1>{{ config('app.full_name') }} ({{ config('app.short_name') }})</h1>
                <h2>
                    ස්වභාවික රබර් සඳහා තිරසාර සහතිකරණය අයදුම්පත<br>
                    {{ config('app.full_name') }}<br>
                    <span class="ta">நிலையான இயற்கை இறப்பர் சான்றளிப்பு விண்ணப்பம்</span>
                </h2>
                <div style="font-size: 9px; color: #6f6256;">
                    මුද්‍රණය කර පුරවා, ස්කෑන්/ඡායාරූපය උඩුගත කරන්න. Print, complete by hand, then upload a scan.
                    <span class="ta">அச்சிட்டு, கையால் நிரப்பி, ஸ்கேன் அல்லது புகைப்படத்தைப் பதிவேற்றவும்.</span>
                </div>
            </td>
            <td width="160" style="vertical-align: middle;">
                <table class="header-logos">
                    <tr>
                        @foreach ($governingBodies as $body)
                            <td>
                                <img class="header-logo" src="{{ $body['logo_path'] }}" alt="{{ $body['name'] }}">
                            </td>
                        @endforeach
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <h3>ලියාපදිංචි වර්ගය / Registration type <span class="ta">/ பதிவு வகை</span></h3>
    <div class="note" style="margin: 0 0 6px;">
        එක් වර්ගයක් පමණක් සලකුණු කරන්න. Tick one only. <span class="ta">ஒன்றை மட்டும் தெரிவு செய்யவும்.</span>
    </div>
    <table>
        <tr>
            @foreach (\App\Models\Planter::registrationCategories() as $category)
                <td style="width: 33%; padding-right: 6px;">
                    <div class="type-card">
                        <span class="tick-box"></span>
                        <span class="type-name">{{ $category['si'] }} / {{ $category['en'] }} <span class="ta">/ {{ $category['ta'] }}</span></span>
                        <div class="type-desc">{{ $category['description_en'] }}</div>
                    </div>
                </td>
            @endforeach
        </tr>
    </table>

    <h3>අයදුම්කරුගේ විස්තර / Applicant details <span class="ta">/ விண்ணப்பதாரர் விவரங்கள்</span></h3>
    <div class="field">
        <div class="label">අයදුම්කරුගේ නම / Applicant name <span class="ta">/ விண்ணப்பதாரர் பெயர்</span></div>
        <div class="line"></div>
    </div>
    <table>
        <tr>
            <td width="50%" style="padding-right:8px;">
                <div class="field">
                    <div class="label">ජා. හැ. අංකය / NIC number <span class="ta">/ தேசிய அடையாள அட்டை எண்</span></div>
                    <div class="line"></div>
                </div>
            </td>
            <td width="50%">
                <div class="field">
                    <div class="label">දිස්ත්‍රික්කය / District <span class="ta">/ மாவட்டம்</span></div>
                    <div class="line"></div>
                </div>
            </td>
        </tr>
    </table>
    <div class="field">
        <div class="label">රබර් සංවර්ධන නිලධාරි කොට්ඨාසය / Rubber Development Officer division <span class="ta">/ இறப்பர் அபிவிருத்தி அலுவலர் பிரிவு</span></div>
        <div class="line"></div>
    </div>

    <h3>ගොවිපල පිළිබඳ සාමාන්‍ය තොරතුරු / General farm information <span class="ta">/ பண்ணை பற்றிய பொதுத் தகவல்</span></h3>
    <div class="field">
        <div class="label">ගොවිපලේ නම / Farm name <span class="ta">/ பண்ணையின் பெயர்</span></div>
        <div class="line"></div>
    </div>
    <div class="field">
        <div class="label">ගොවිපලේ ලිපිනය / Farm address <span class="ta">/ பண்ணையின் முகவரி</span></div>
        <div class="box"></div>
    </div>
    <table>
        <tr>
            <td width="50%" style="padding-right:8px;">
                <div class="field">
                    <div class="label">දුරකථන අංකය / Phone <span class="ta">/ தொலைபேசி எண்</span></div>
                    <div class="line"></div>
                </div>
            </td>
            <td width="50%">
                <div class="field">
                    <div class="label">WhatsApp අංකය / WhatsApp <span class="ta">/ வாட்ஸ்அப் எண்</span></div>
                    <div class="line"></div>
                </div>
            </td>
        </tr>
        <tr>
            <td width="50%" style="padding-right:8px;">
                <div class="field">
                    <div class="label">ෆැක්ස් අංකය / Fax <span class="ta">/ தொலைநகல் எண்</span></div>
                    <div class="line"></div>
                </div>
            </td>
            <td width="50%">
                <div class="field">
                    <div class="label">ඊ මේල් / Email <span class="ta">/ மின்னஞ்சல்</span></div>
                    <div class="line"></div>
                </div>
            </td>
        </tr>
    </table>

    <h3>සහතිකකරණ ඉතිහාසය / Certification history <span class="ta">/ சான்றளிப்பு வரலாறு</span></h3>
    <div class="field">
        <div class="label">දැනටමත් ගොවිපළ සහතිකකරණය කර තිබේද? / Is the farm already certified? <span class="ta">/ பண்ணை ஏற்கனவே சான்றளிக்கப்பட்டதா?</span></div>
        <div class="checks">
            <span class="tick"><span class="tick-box"></span><span class="tick-label">ඔව් / Yes <span class="ta">/ ஆம்</span></span></span>
            <span class="tick"><span class="tick-box"></span><span class="tick-label">නැත / No <span class="ta">/ இல்லை</span></span></span>
        </div>
    </div>
    <div class="field">
        <div class="label">ඔව් නම්, කුමන ප්‍රමිතිකරණය යටතේද? / If yes, under which standard? (attach photocopy) <span class="ta">/ ஆம் எனில், எந்தத் தரத்தின் கீழ்? (நகலை இணைக்கவும்)</span></div>
        <div class="line"></div>
    </div>
    <div class="field">
        <div class="label">පෙර සහතිකකරණය ප්‍රතික්ෂේප වී හෝ අත්හිටුවා ඇතිද? / Previously rejected or suspended? <span class="ta">/ முன்பு சான்றளிப்பு நிராகரிக்கப்பட்டதா அல்லது இடைநிறுத்தப்பட்டதா?</span></div>
        <div class="checks">
            <span class="tick"><span class="tick-box"></span><span class="tick-label">ඔව් / Yes <span class="ta">/ ஆம்</span></span></span>
            <span class="tick"><span class="tick-box"></span><span class="tick-label">නැත / No <span class="ta">/ இல்லை</span></span></span>
        </div>
    </div>
    <div class="field">
        <div class="label">හේතු / Reason <span class="ta">/ காரணம்</span></div>
        <div class="box-sm"></div>
    </div>

    <h3>සහතිකකරණය සඳහා නිෂ්පාදන / Products for certification <span class="ta">/ சான்றளிப்புக்கான தயாரிப்புகள்</span></h3>
    <table class="product-table">
        @for ($i = 1; $i <= 10; $i++)
            @if ($i % 2 === 1)<tr>@endif
                <td>
                    <div class="label">{{ $i }}.</div>
                    <div class="line"></div>
                </td>
            @if ($i % 2 === 0)</tr>@endif
        @endfor
    </table>

    <h3>දැනුවත්භාවය සහ සැකසුම් / Awareness and processing <span class="ta">/ விழிப்புணர்வு மற்றும் பதப்படுத்தல்</span></h3>
    <div class="field">
        <div class="label">දැනට භාවිතා වන සහතිකකරණය පිළිබඳව දැනුවත්ද? / Aware of currently used certification? <span class="ta">/ தற்போது பயன்பாட்டில் உள்ள சான்றளிப்பு பற்றி அறிவீர்களா?</span></div>
        <div class="checks">
            <span class="tick"><span class="tick-box"></span><span class="tick-label">ඔව් / Yes <span class="ta">/ ஆம்</span></span></span>
            <span class="tick"><span class="tick-box"></span><span class="tick-label">නැත / No <span class="ta">/ இல்லை</span></span></span>
        </div>
    </div>
    <div class="field">
        <div class="label">විස්තර පත්‍රිකාවක් තිබේද? / Have an information leaflet? <span class="ta">/ தகவல் துண்டுப்பிரசுரம் உள்ளதா?</span></div>
        <div class="checks">
            <span class="tick"><span class="tick-box"></span><span class="tick-label">ඔව් / Yes <span class="ta">/ ஆம்</span></span></span>
            <span class="tick"><span class="tick-box"></span><span class="tick-label">නැත / No <span class="ta">/ இல்லை</span></span></span>
        </div>
    </div>
    <div class="field">
        <div class="label">ගොවිපල තුළ ස්වභාවික රබර් සකස් කරයිද? / Natural rubber processed on farm? <span class="ta">/ பண்ணையில் இயற்கை இறப்பர் பதப்படுத்தப்படுகிறதா?</span></div>
        <div class="checks">
            <span class="tick"><span class="tick-box"></span><span class="tick-label">ඔව් / Yes <span class="ta">/ ஆம்</span></span></span>
            <span class="tick"><span class="tick-box"></span><span class="tick-label">නැත / No <span class="ta">/ இல்லை</span></span></span>
        </div>
    </div>
    <div class="field">
        <div class="label">ඔව් නම්, ක්‍රියාවලි සැලැස්මක් තිබේද? / If yes, is there a process plan? <span class="ta">/ ஆம் எனில், செயல்முறைத் திட்டம் உள்ளதா?</span></div>
        <div class="checks">
            <span class="tick"><span class="tick-box"></span><span class="tick-label">ඔව් / Yes <span class="ta">/ ஆம்</span></span></span>
            <span class="tick"><span class="tick-box"></span><span class="tick-label">නැත / No <span class="ta">/ இல்லை</span></span></span>
            <span class="tick"><span class="tick-box"></span><span class="tick-label">අදාළ නැත / N/A <span class="ta">/ பொருந்தாது</span></span></span>
        </div>
    </div>

    <h3>වැවිලි සමාගම / කාණ්ඩය / Plantation company or group <span class="ta">/ தோட்ட நிறுவனம் / குழு</span></h3>
    <div class="field">
        <div class="label">නම / Name <span class="ta">/ பெயர்</span></div>
        <div class="line"></div>
    </div>
    <div class="field">
        <div class="label">ලිපිනය / Address <span class="ta">/ முகவரி</span></div>
        <div class="box-sm"></div>
    </div>
    <div class="field">
        <div class="label">අයදුම්කරුගේ ජා. හැ. අංකය සහ අත්සන / Applicant NIC and signature <span class="ta">/ விண்ணப்பதாரரின் அடையாள அட்டை எண் மற்றும் கையொப்பம்</span></div>
        <div class="box"></div>
    </div>

    <p class="note">
        අනුමැතියෙන් පසු SPPNR ලියාපදිංචි අංකය සහ ජා. හැ. අංකය භාවිතයෙන් මුරපදය සාදන්න.
        After approval, create a password using your registration number and NIC.
        <span class="ta">அனுமதிக்குப் பின் பதிவு எண்ணையும் தேசிய அடையாள அட்டை எண்ணையும் பயன்படுத்தி கடவுச்சொல்லை உருவாக்கவும்.</span>
        All SPPNR dates use Sri Lanka Standard Time (Asia/Colombo, UTC+05:30).
    </p>

    <div class="powered-footer">
        <table>
            <tr>
                @foreach ($governingBodies as $body)
                    <td style="width: 33%; text-align: center; padding: 0 6px;">
                        <img class="powered-logo" src="{{ $body['logo_path'] }}" alt="{{ $body['name'] }}">
                        <div class="header-logo-caption">{{ $body['short'] ?? $body['name'] }}</div>
                    </td>
                @endforeach
            </tr>
        </table>
        <p class="powered-text">{{ config('app.powered_by') }}</p>
        <p class="powered-text">&copy; {{ now()->year }} {{ config('app.copyright') }}. All rights reserved.</p>
    </div>
</body>
</html>
