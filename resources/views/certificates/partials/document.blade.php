@php
    $planter = $certificate->planter;
    $category = \App\Models\Planter::registrationCategories()[$planter->registration_category] ?? null;
    $categoryName = $category['en'] ?? $planter->categoryStyle()['label'];
    $products = collect($planter->crops_products ?? [])
        ->map(fn ($crop) => is_array($crop) ? ($crop['name'] ?? null) : null)
        ->filter()
        ->implode(', ');
    $bodies = collect(config('app.governing_bodies'));
    $ministry = $bodies->firstWhere('logo', 'logo-ministry.png');
    $signatories = [
        $bodies->firstWhere('logo', 'logo-rrisl.png'),
        $bodies->firstWhere('logo', 'logo-rdd.png'),
        $ministry,
    ];
    $badge = $certificate->isRevoked()
        ? 'Revoked'
        : ($certificate->outcome === \App\Models\Certificate::OUTCOME_CONDITIONAL ? 'Conditional' : 'Certified');
    $embedImages = $embedImages ?? false;
    $image = function (string $relative) use ($embedImages): string {
        if (! $embedImages) {
            return asset($relative);
        }

        $full = public_path($relative);

        return 'data:image/png;base64,'.base64_encode((string) file_get_contents($full));
    };
    $rows = array_filter([
        ['SPPNR ID', $planter->identification_number],
        ['Certificate No.', $certificate->certificate_number],
        ['Registration', $categoryName],
        ['Business', $planter->businessTypeLabel()],
        ['Holding', $planter->farm_name],
        ['Organisation', $planter->group_name],
        ['District', $planter->district],
        ['RDO division', $planter->rdoDivision?->name ?: $planter->rdd_division],
        ['Address', $planter->address],
        ['Products', $products !== '' ? $products : null],
        ['Outcome', $certificate->outcomeLabel()],
        ['Date of issue', $certificate->issued_at ? sl_date($certificate->issued_at) : null],
    ], fn ($row) => filled($row[1]));
@endphp

<style>
    .cert-inline.sheet {
        width: 210mm;
        height: 297mm;
        margin: 0 auto;
        background: #fffdf8;
        box-shadow: 0 16px 40px rgba(18, 56, 38, 0.14);
        color: #2a2118;
        font-family: "Plus Jakarta Sans", "Noto Sans Sinhala", "Noto Sans Tamil", sans-serif;
    }
    .cert-inline .frame { height: 100%; padding: 6mm; border: 7px solid #123826; box-sizing: border-box; }
    .cert-inline .inner { height: 100%; border: 1.5px solid #c4a574; padding: 5mm 6mm 4mm; display: flex; flex-direction: column; box-sizing: border-box; }
    .cert-inline .mast { text-align: center; }
    .cert-inline .mast img { display: block; height: 30mm; width: auto; margin: 0 auto; object-fit: contain; }
    .cert-inline .mast p { margin: 1.5mm auto 0; font-size: 8pt; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #4a2e18; }
    .cert-inline .mast .country { margin-top: 0.6mm; font-size: 7.5pt; letter-spacing: 0.12em; }
    .cert-inline .rule { width: 42mm; height: 0; margin: 2mm auto 0; border: 0; border-top: 1px solid #c4a574; }
    .cert-inline .mark { display: block; width: 14mm; height: 14mm; margin: 2mm auto 0; object-fit: contain; }
    .cert-inline .title-copy { text-align: center; }
    .cert-inline .kicker { margin: 1mm 0 0; font-size: 8pt; letter-spacing: 0.18em; text-transform: uppercase; color: #6f6256; }
    .cert-inline h1 { margin: 0.5mm 0 0; font-family: Fraunces, Georgia, serif; font-size: 18pt; font-weight: 700; line-height: 1.1; color: #123826; }
    .cert-inline .scheme { margin: 1mm 0 0; font-size: 7.5pt; letter-spacing: 0.04em; text-transform: uppercase; color: #6b4423; }
    .cert-inline .number { margin: 2mm 0 0; text-align: center; font-size: 9pt; font-weight: 700; letter-spacing: 0.04em; color: #1b4d32; }
    .cert-inline .certifies { margin: 2.5mm 0 0; text-align: center; font-size: 9pt; color: #6f6256; }
    .cert-inline .holder { margin: 0.5mm 0 0; text-align: center; font-family: Fraunces, Georgia, serif; font-size: 18pt; font-weight: 700; line-height: 1.15; color: #123826; }
    .cert-inline .holder-meta { margin: 0.5mm 0 0; text-align: center; font-size: 9pt; color: #4a2e18; }
    .cert-inline .statement { margin: 2mm 0 0; text-align: center; font-size: 8.5pt; line-height: 1.4; color: #2a2118; }
    .cert-inline table.facts { width: 100%; margin-top: 2.5mm; border-collapse: collapse; }
    .cert-inline table.facts th, .cert-inline table.facts td { padding: 1.15mm 2mm; border-bottom: 1px solid #e8dcc8; vertical-align: top; text-align: left; font-weight: 400; }
    .cert-inline table.facts th { width: 30mm; font-size: 7pt; letter-spacing: 0.04em; text-transform: uppercase; color: #6f6256; }
    .cert-inline table.facts td { font-size: 9pt; color: #2a2118; }
    .cert-inline .revoked { margin-top: 2mm; padding: 1.5mm 3mm; border: 1.5px solid #b91c1c; background: #fef2f2; color: #991b1b; text-align: center; font-size: 8.5pt; font-weight: 700; letter-spacing: 0.06em; }
    .cert-inline .foot { margin-top: auto; padding-top: 3mm; display: grid; grid-template-columns: 28mm 1fr; gap: 4mm; align-items: end; }
    .cert-inline .qr { text-align: center; }
    .cert-inline .qr img { width: 24mm; height: 24mm; object-fit: contain; }
    .cert-inline .qr p { margin: 1mm 0 0; font-size: 7pt; line-height: 1.3; color: #6f6256; }
    .cert-inline .sign-block { display: flex; flex-direction: column; align-items: flex-end; }
    .cert-inline .signs { display: flex; justify-content: space-between; gap: 4mm; width: 100%; }
    .cert-inline .sign { flex: 1; text-align: center; }
    .cert-inline .sign .line { margin-top: 9mm; border-top: 1px solid #123826; padding-top: 1.2mm; font-size: 7pt; letter-spacing: 0.06em; text-transform: uppercase; color: #6f6256; }
    .cert-inline .sign strong { display: block; margin-top: 0.6mm; font-size: 8pt; color: #123826; }
    .cert-inline .award { position: relative; width: 26mm; height: 26mm; margin: 0 2mm 1mm 0; }
    .cert-inline .award img { width: 100%; height: 100%; object-fit: contain; }
    .cert-inline .award span { position: absolute; left: 18%; right: 18%; top: 34%; bottom: 28%; display: flex; align-items: center; justify-content: center; text-align: center; color: #fff8ea; font-family: Fraunces, Georgia, serif; font-size: 7.5pt; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; line-height: 1.1; }
    .cert-inline .bottom-logos { margin-top: 1.5mm; display: flex; justify-content: center; align-items: center; gap: 7mm; }
    .cert-inline .bottom-logos img { height: 9mm; width: auto; max-width: 18mm; object-fit: contain; }
    .cert-inline .closing { margin: 2mm 0 0; text-align: center; font-size: 7pt; line-height: 1.35; color: #6f6256; }
    @media print {
        .cert-inline.sheet { margin: 0; box-shadow: none; }
    }
</style>

<article class="cert-inline sheet">
    <div class="frame">
        <div class="inner">
            <div class="mast">
                <img src="{{ $image($ministry['logo']) }}" alt="{{ $ministry['name'] }}">
                <p>{{ $ministry['name'] }}</p>
                <p class="country">Democratic Socialist Republic of Sri Lanka</p>
                <hr class="rule">
            </div>

            <img class="mark" src="{{ $image('SPPNR-logo.png') }}" alt="{{ config('app.short_name') }}">
            <div class="title-copy">
                <p class="kicker">{{ config('app.short_name') }}</p>
                <h1>Certificate of Sustainability</h1>
                <p class="scheme">{{ config('app.full_name') }}</p>
            </div>

            <p class="number">{{ $certificate->certificate_number }}</p>
            <p class="certifies">This is to certify that</p>
            <p class="holder">{{ $planter->name }}</p>
            @if ($planter->group_name && $planter->group_name !== $planter->name)
                <p class="holder-meta">{{ $planter->group_name }}</p>
            @endif
            <p class="holder-meta">{{ $categoryName }} · {{ $planter->district ?: 'Sri Lanka' }}</p>

            <p class="statement">
                has met the requirements of the {{ config('app.short_name') }} scheme for the sustainable
                production and processing of natural rubber, and is recorded as
                <strong>{{ $certificate->outcomeLabel() }}</strong>.
            </p>

            <table class="facts">
                @foreach (array_chunk($rows, 2) as $pair)
                    <tr>
                        @foreach ($pair as [$label, $value])
                            <th scope="row">{{ $label }}</th>
                            <td>{{ $value }}</td>
                        @endforeach
                        @if (count($pair) === 1)
                            <th></th>
                            <td></td>
                        @endif
                    </tr>
                @endforeach
            </table>

            @if ($certificate->isRevoked())
                <p class="revoked">
                    REVOKED {{ $certificate->revoked_at ? sl_date($certificate->revoked_at) : '' }}
                    @if ($certificate->revoke_reason)
                        — {{ $certificate->revoke_reason }}
                    @endif
                </p>
            @endif

            <div class="foot">
                <div class="qr">
                    <img src="{{ $qrUrl }}" alt="Verification QR code">
                    <p>Scan to verify</p>
                </div>
                <div class="sign-block">
                    <div class="award">
                        <img src="{{ $image('certificate-badge.png') }}" alt="">
                        <span>{{ $badge }}</span>
                    </div>
                    <div class="signs">
                        @foreach ($signatories as $body)
                            <div class="sign">
                                <div class="line">Signature</div>
                                <strong>{{ $body['short'] }}</strong>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="bottom-logos">
                @foreach (config('app.governing_bodies') as $body)
                    <img src="{{ $image($body['logo']) }}" alt="{{ $body['name'] }}">
                @endforeach
                <img src="{{ $image('SPPNR-logo.png') }}" alt="{{ config('app.short_name') }}">
            </div>

            <p class="closing">
                {{ config('app.powered_by') }}. Verify at {{ $certificate->verifyUrl() }}
                <br>&copy; {{ now()->year }} {{ config('app.copyright') }}. All rights reserved.
            </p>
        </div>
    </div>
</article>
