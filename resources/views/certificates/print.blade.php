@php
    $forPdf = $forPdf ?? false;
    $forEmbed = $forEmbed ?? false;
    $downloadUrl = $downloadUrl ?? null;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $certificate->certificate_number }} · {{ config('app.short_name') }}</title>
    <x-app-fonts :display="true" />
    @vite(['resources/css/app.css'])
    <style>
        @page { size: A4 portrait; margin: 0; }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }
        body {
            background: #efe8dc;
            color: #2a2118;
            font-family: "Plus Jakarta Sans", "Noto Sans Sinhala", "Noto Sans Tamil", sans-serif;
        }
        .toolbar {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            width: 210mm;
            margin: 8mm auto 0;
        }
        .print-stage .cert-inline.sheet { margin: 6mm auto 10mm; }
        body.embed { background: #efe8dc; }
        body.embed .cert-inline.sheet { margin: 0 auto; }
        @media print {
            .toolbar { display: none !important; }
            body { background: white; }
            .print-stage .cert-inline.sheet { margin: 0; box-shadow: none; }
        }
    </style>
</head>
<body class="{{ $forEmbed ? 'embed' : '' }}">
    @unless ($forPdf || $forEmbed)
        <div class="toolbar">
            @if ($downloadUrl)
                <a href="{{ $downloadUrl }}" class="btn-primary">Download PDF</a>
            @endif
            <button type="button" onclick="window.print()" class="btn-secondary">Print</button>
            <button type="button" onclick="window.close()" class="btn-secondary">Close</button>
        </div>
    @endunless

    <div class="print-stage">
        @include('certificates.partials.document')
    </div>
</body>
</html>
