<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>SCSNR Rubber Planter Registration Form</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #2a2118; font-size: 12px; }
        h1 { font-size: 18px; margin: 0 0 4px; color: #123826; }
        h2 { font-size: 13px; margin: 0 0 16px; color: #6b4423; }
        .header { border-bottom: 2px solid #1b4d32; padding-bottom: 12px; margin-bottom: 18px; }
        .logo { width: 72px; height: 72px; }
        .field { margin-bottom: 16px; }
        .label { font-size: 11px; font-weight: bold; color: #4a2e18; margin-bottom: 6px; }
        .line { border-bottom: 1px solid #c4a574; height: 22px; }
        .box { border: 1px solid #c4a574; height: 70px; }
        .note { font-size: 10px; color: #6f6256; margin-top: 18px; }
        table { width: 100%; }
        td { vertical-align: top; }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td width="90">
                <img class="logo" src="{{ $logo }}" alt="SCSNR">
            </td>
            <td>
                <h1>{{ config('app.full_name') }} ({{ config('app.short_name') }})</h1>
                <h2>Rubber Planter Registration — Offline Form</h2>
                <div>Print this form, complete it by hand, then upload a scan or photo on the registration page.</div>
            </td>
        </tr>
    </table>

    <div class="field">
        <div class="label">Full name</div>
        <div class="line"></div>
    </div>
    <div class="field">
        <div class="label">NIC number</div>
        <div class="line"></div>
    </div>
    <div class="field">
        <div class="label">Email</div>
        <div class="line"></div>
    </div>
    <div class="field">
        <div class="label">Phone</div>
        <div class="line"></div>
    </div>
    <div class="field">
        <div class="label">District</div>
        <div class="line"></div>
    </div>
    <div class="field">
        <div class="label">Address</div>
        <div class="box"></div>
    </div>
    <div class="field">
        <div class="label">Applicant signature and date (dd/mm/yyyy, Sri Lanka time)</div>
        <div class="box"></div>
    </div>

    <p class="note">
        After filling this form, scan or photograph every page and upload the PDF or image on the SCSNR planter registration page.
        A temporary unique ID will be issued and the application will enter the approval process.
        After approval, create a password using your temporary ID and NIC.
        All SCSNR dates and times use Sri Lanka Standard Time (Asia/Colombo, UTC+05:30).
    </p>
</body>
</html>
