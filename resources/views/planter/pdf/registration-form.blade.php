<!DOCTYPE html>
<html lang="si">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>SCSNR Sustainability Certification Application</title>
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
        * {
            font-family: 'NotoSinhala' !important;
        }
        body { color: #2a2118; font-size: 11px; line-height: 1.4; font-family: 'NotoSinhala' !important; }
        h1, h2, h3, .label, .note, .checks, .tick-label, .powered-text, .header-logo-caption {
            font-family: 'NotoSinhala' !important;
        }
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
                <img class="logo" src="{{ $logo }}" alt="SCSNR">
            </td>
            <td style="vertical-align: middle;">
                <h1>{{ config('app.full_name') }} ({{ config('app.short_name') }})</h1>
                <h2>ස්වභාවික රබර් සඳහා තිරසාර සහතිකරණය අයදුම්පත<br>Application for Sustainability Certification for Natural Rubber</h2>
                <div style="font-size: 9px; color: #6f6256;">මුද්‍රණය කර පුරවා, ස්කෑන්/ඡායාරූපය උඩුගත කරන්න. Print, complete by hand, then upload a scan.</div>
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

    <h3>අයදුම්කරුගේ විස්තර / Applicant details</h3>
    <div class="field">
        <div class="label">අයදුම්කරුගේ නම / Applicant name</div>
        <div class="line"></div>
    </div>
    <table>
        <tr>
            <td width="50%" style="padding-right:8px;">
                <div class="field">
                    <div class="label">ජා. හැ. අංකය / NIC number</div>
                    <div class="line"></div>
                </div>
            </td>
            <td width="50%">
                <div class="field">
                    <div class="label">දිස්ත්‍රික්කය / District</div>
                    <div class="line"></div>
                </div>
            </td>
        </tr>
    </table>
    <div class="field">
        <div class="label">රබර් සංවර්ධන නිලධාරි කොට්ඨාසය / Rubber Development Officer division</div>
        <div class="line"></div>
    </div>

    <h3>ගොවිපල පිළිබඳ සාමාන්‍ය තොරතුරු / General farm information</h3>
    <div class="field">
        <div class="label">ගොවිපලේ නම / Farm name</div>
        <div class="line"></div>
    </div>
    <div class="field">
        <div class="label">ගොවිපලේ ලිපිනය / Farm address</div>
        <div class="box"></div>
    </div>
    <table>
        <tr>
            <td width="50%" style="padding-right:8px;">
                <div class="field">
                    <div class="label">දුරකථන අංකය / Phone</div>
                    <div class="line"></div>
                </div>
            </td>
            <td width="50%">
                <div class="field">
                    <div class="label">WhatsApp අංකය / WhatsApp</div>
                    <div class="line"></div>
                </div>
            </td>
        </tr>
        <tr>
            <td width="50%" style="padding-right:8px;">
                <div class="field">
                    <div class="label">ෆැක්ස් අංකය / Fax</div>
                    <div class="line"></div>
                </div>
            </td>
            <td width="50%">
                <div class="field">
                    <div class="label">ඊ මේල් / Email</div>
                    <div class="line"></div>
                </div>
            </td>
        </tr>
    </table>

    <div class="field">
        <div class="label">ව්‍යාපාරයේ ස්වභාවය / Nature of business</div>
        <div class="checks">
            <span class="tick"><span class="tick-box"></span><span class="tick-label">තනි පුද්ගල / Individual</span></span>
            <span class="tick"><span class="tick-box"></span><span class="tick-label">හවුල් ව්‍යාපාර / Partnership</span></span>
            <span class="tick"><span class="tick-box"></span><span class="tick-label">සමාගම් / Company</span></span>
            <span class="tick"><span class="tick-box"></span><span class="tick-label">සීමාකාරී / Limited</span></span>
            <span class="tick"><span class="tick-box"></span><span class="tick-label">සමිති / Society</span></span>
            <span class="tick"><span class="tick-box"></span><span class="tick-label">අනෙකුත් / Other</span></span>
        </div>
    </div>

    <h3>සහතිකකරණ ඉතිහාසය / Certification history</h3>
    <div class="field">
        <div class="label">දැනටමත් ගොවිපළ සහතිකකරණය කර තිබේද? / Is the farm already certified?</div>
        <div class="checks">
            <span class="tick"><span class="tick-box"></span><span class="tick-label">ඔව් / Yes</span></span>
            <span class="tick"><span class="tick-box"></span><span class="tick-label">නැත / No</span></span>
        </div>
    </div>
    <div class="field">
        <div class="label">ඔව් නම්, කුමන ප්‍රමිතිකරණය යටතේද? / If yes, under which standard? (attach photocopy)</div>
        <div class="line"></div>
    </div>
    <div class="field">
        <div class="label">පෙර සහතිකකරණය ප්‍රතික්ෂේප වී හෝ අත්හිටුවා ඇතිද? / Previously rejected or suspended?</div>
        <div class="checks">
            <span class="tick"><span class="tick-box"></span><span class="tick-label">ඔව් / Yes</span></span>
            <span class="tick"><span class="tick-box"></span><span class="tick-label">නැත / No</span></span>
        </div>
    </div>
    <div class="field">
        <div class="label">හේතු / Reason</div>
        <div class="box-sm"></div>
    </div>

    <h3>සහතිකකරණය සඳහා නිෂ්පාදන / Products for certification</h3>
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

    <h3>දැනුවත්භාවය සහ සැකසුම් / Awareness and processing</h3>
    <div class="field">
        <div class="label">දැනට භාවිතා වන සහතිකකරණය පිළිබඳව දැනුවත්ද? / Aware of currently used certification?</div>
        <div class="checks">
            <span class="tick"><span class="tick-box"></span><span class="tick-label">ඔව් / Yes</span></span>
            <span class="tick"><span class="tick-box"></span><span class="tick-label">නැත / No</span></span>
        </div>
    </div>
    <div class="field">
        <div class="label">විස්තර පත්‍රිකාවක් තිබේද? / Have an information leaflet?</div>
        <div class="checks">
            <span class="tick"><span class="tick-box"></span><span class="tick-label">ඔව් / Yes</span></span>
            <span class="tick"><span class="tick-box"></span><span class="tick-label">නැත / No</span></span>
        </div>
    </div>
    <div class="field">
        <div class="label">ගොවිපල තුළ ස්වභාවික රබර් සකස් කරයිද? / Natural rubber processed on farm?</div>
        <div class="checks">
            <span class="tick"><span class="tick-box"></span><span class="tick-label">ඔව් / Yes</span></span>
            <span class="tick"><span class="tick-box"></span><span class="tick-label">නැත / No</span></span>
        </div>
    </div>
    <div class="field">
        <div class="label">ඔව් නම්, ක්‍රියාවලි සැලැස්මක් තිබේද? / If yes, is there a process plan?</div>
        <div class="checks">
            <span class="tick"><span class="tick-box"></span><span class="tick-label">ඔව් / Yes</span></span>
            <span class="tick"><span class="tick-box"></span><span class="tick-label">නැත / No</span></span>
            <span class="tick"><span class="tick-box"></span><span class="tick-label">අදාළ නැත / N/A</span></span>
        </div>
    </div>

    <h3>වැවිලි සමාගම / කාණ්ඩය / Plantation company or group</h3>
    <div class="field">
        <div class="label">නම / Name</div>
        <div class="line"></div>
    </div>
    <div class="field">
        <div class="label">ලිපිනය / Address</div>
        <div class="box-sm"></div>
    </div>
    <div class="field">
        <div class="label">අයදුම්කරුගේ ජා. හැ. අංකය සහ අත්සන / Applicant NIC and signature</div>
        <div class="box"></div>
    </div>

    <p class="note">
        අනුමැතියෙන් පසු SCSNR ලියාපදිංචි අංකය සහ ජා. හැ. අංකය භාවිතයෙන් මුරපදය සාදන්න.
        After approval, create a password using your SCSNR registration number and NIC.
        All SCSNR dates use Sri Lanka Standard Time (Asia/Colombo, UTC+05:30).
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
    </div>
</body>
</html>
