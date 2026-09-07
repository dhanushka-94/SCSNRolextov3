<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Services\PlanterIdentityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class CertificateVerifyController extends Controller
{
    public function form(Request $request): View|RedirectResponse
    {
        $ref = trim((string) $request->query('ref', ''));

        if ($ref !== '') {
            $certificate = $this->findByReference($ref);

            if ($certificate) {
                return redirect()->route('verify.show', $certificate->public_token);
            }

            return view('certificates.verify-form', [
                'ref' => $ref,
                'error' => 'No certificate found for that reference number.',
            ]);
        }

        return view('certificates.verify-form', [
            'ref' => '',
            'error' => null,
        ]);
    }

    public function lookup(Request $request): RedirectResponse
    {
        $ref = trim((string) $request->input('ref', ''));

        if ($ref === '') {
            return redirect()
                ->route('verify.form')
                ->withErrors(['ref' => 'Enter a certificate number or SCSNR ID.']);
        }

        $certificate = $this->findByReference($ref);

        if (! $certificate) {
            return redirect()
                ->route('verify.form', ['ref' => $ref])
                ->withErrors(['ref' => 'No certificate found for that reference number.']);
        }

        return redirect()->route('verify.show', $certificate->public_token);
    }

    public function show(string $token): View
    {
        $certificate = Certificate::query()
            ->with(['planter'])
            ->where('public_token', $token)
            ->firstOrFail();

        return view('certificates.verify', [
            'certificate' => $certificate,
            'planter' => $certificate->planter,
        ]);
    }

    public function qr(string $token, PlanterIdentityService $identity): Response
    {
        $certificate = Certificate::query()
            ->with(['planter.currentCertificate'])
            ->where('public_token', $token)
            ->firstOrFail();

        return response($identity->png($certificate->planter), 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=300',
        ]);
    }

    private function findByReference(string $ref): ?Certificate
    {
        $normalized = preg_replace('/\s+/', '', $ref) ?? $ref;
        $normalized = strtoupper($normalized);

        $byNumber = Certificate::query()
            ->with(['planter'])
            ->whereRaw('UPPER(REPLACE(certificate_number, " ", "")) = ?', [$normalized])
            ->orderByDesc('is_current')
            ->orderByDesc('issued_at')
            ->first();

        if ($byNumber) {
            return $byNumber;
        }

        return Certificate::query()
            ->with(['planter'])
            ->whereHas('planter', function ($planter) use ($normalized, $ref) {
                $planter->whereRaw('UPPER(REPLACE(identification_number, " ", "")) = ?', [$normalized])
                    ->orWhere('identification_number', $ref)
                    ->orWhere('temporary_id', $ref);
            })
            ->orderByDesc('is_current')
            ->orderByDesc('issued_at')
            ->first();
    }
}
