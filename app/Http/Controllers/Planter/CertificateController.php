<?php

namespace App\Http\Controllers\Planter;

use App\Http\Controllers\Controller;
use App\Services\PlanterIdentityService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class CertificateController extends Controller
{
    public function show(Request $request): View
    {
        $planter = $request->user('planter');
        $certificate = $planter->currentCertificate()->with(['issuer'])->first();

        abort_unless($certificate, 404);

        return view('planter.certificate', [
            'planter' => $planter,
            'certificate' => $certificate,
        ]);
    }

    public function qr(Request $request, PlanterIdentityService $identity): Response
    {
        $certificate = $request->user('planter')->currentCertificate()->first();

        abort_unless($certificate, 404);

        return response($identity->certificatePng($certificate, 480), 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'private, max-age=300',
        ]);
    }

    public function downloadQr(Request $request, PlanterIdentityService $identity, string $format): Response
    {
        $certificate = $request->user('planter')->currentCertificate()->first();

        abort_unless($certificate, 404);
        abort_unless(in_array($format, ['png', 'svg'], true), 404);

        if ($format === 'svg') {
            return response($identity->certificateSvg($certificate, 2000), 200, [
                'Content-Type' => 'image/svg+xml',
                'Content-Disposition' => 'attachment; filename="'.$certificate->qrDownloadName('svg').'"',
            ]);
        }

        return response($identity->certificatePng($certificate, 2000), 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="'.$certificate->qrDownloadName('png').'"',
        ]);
    }

    public function print(Request $request): View
    {
        $planter = $request->user('planter');
        $certificate = $planter->currentCertificate()->with(['issuer', 'planter'])->first();

        abort_unless($certificate, 404);

        return view('certificates.print', [
            'certificate' => $certificate,
            'qrUrl' => route('verify.qr', $certificate->public_token),
        ]);
    }
}
