<?php

namespace App\Http\Controllers\Planter;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use Illuminate\Http\Request;
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
