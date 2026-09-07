<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Certificates\IssueCertificateRequest;
use App\Http\Requests\Certificates\RevokeCertificateRequest;
use App\Models\Certificate;
use App\Models\Planter;
use App\Services\CertificateService;
use App\Services\PlanterIdentityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class CertificateController extends Controller
{
    public function issued(Request $request): View
    {
        return $this->index($request, Certificate::STATUS_ISSUED);
    }

    public function revoked(Request $request): View
    {
        return $this->index($request, Certificate::STATUS_REVOKED);
    }

    private function index(Request $request, string $status): View
    {
        abort_unless($request->user('web')->canManageCertificates(), 403);

        $query = Certificate::query()
            ->with(['planter', 'issuer', 'revoker'])
            ->where('status', $status)
            ->when($request->filled('q'), function ($builder) use ($request) {
                $search = '%'.$request->string('q')->trim().'%';

                $builder->where(function ($inner) use ($search) {
                    $inner->where('certificate_number', 'like', $search)
                        ->orWhereHas('planter', function ($planter) use ($search) {
                            $planter->where('name', 'like', $search)
                                ->orWhere('email', 'like', $search)
                                ->orWhere('nic', 'like', $search)
                                ->orWhere('identification_number', 'like', $search)
                                ->orWhere('temporary_id', 'like', $search);
                        });
                });
            })
            ->when($request->filled('district'), function ($builder) use ($request) {
                $builder->whereHas('planter', fn ($planter) => $planter->where('district', $request->string('district')));
            })
            ->when(
                $status === Certificate::STATUS_ISSUED,
                fn ($builder) => $builder->latest('issued_at'),
                fn ($builder) => $builder->latest('revoked_at'),
            );

        $certificates = $query->paginate(10)->withQueryString();

        return view('certificates.index', [
            'certificates' => $certificates,
            'status' => $status,
            'filters' => $request->only(['q', 'district']),
            'stats' => [
                'issued' => Certificate::query()->where('status', Certificate::STATUS_ISSUED)->count(),
                'revoked' => Certificate::query()->where('status', Certificate::STATUS_REVOKED)->count(),
            ],
            'listRoute' => $status === Certificate::STATUS_REVOKED
                ? 'admin.certificates.revoked'
                : 'admin.certificates.issued',
            'canManage' => $request->user('web')->canManageCertificates(),
        ]);
    }

    public function show(Request $request, Certificate $certificate): View
    {
        abort_unless($request->user('web')->canManageCertificates(), 403);

        $certificate->load(['planter', 'issuer', 'revoker']);

        return view('certificates.show', [
            'certificate' => $certificate,
            'canManage' => true,
        ]);
    }

    public function print(Request $request, Certificate $certificate): View
    {
        abort_unless($request->user('web')->canManageCertificates(), 403);

        $certificate->load(['planter', 'issuer']);

        return view('certificates.print', [
            'certificate' => $certificate,
            'qrUrl' => route('admin.certificates.qr', $certificate),
        ]);
    }

    public function qr(Certificate $certificate, PlanterIdentityService $identity): Response
    {
        $certificate->loadMissing('planter.currentCertificate');

        return response($identity->png($certificate->planter), 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'private, max-age=300',
        ]);
    }

    public function issue(
        IssueCertificateRequest $request,
        Planter $planter,
        CertificateService $certificates,
    ): RedirectResponse {
        try {
            $certificate = $certificates->issue(
                $planter->loadMissing('finalAudit'),
                $request->user('web'),
                $request->validated('notes'),
            );
        } catch (\InvalidArgumentException $exception) {
            return back()->withErrors(['certificate' => $exception->getMessage()]);
        }

        return redirect()
            ->route('admin.certificates.show', $certificate)
            ->with('success', 'Certificate issued.');
    }

    public function revoke(
        RevokeCertificateRequest $request,
        Certificate $certificate,
        CertificateService $certificates,
    ): RedirectResponse {
        try {
            $certificates->revoke(
                $certificate,
                $request->user('web'),
                $request->validated('revoke_reason'),
            );
        } catch (\InvalidArgumentException $exception) {
            return back()->withErrors(['certificate' => $exception->getMessage()]);
        }

        return redirect()
            ->route('admin.certificates.show', $certificate)
            ->with('success', 'Certificate revoked.');
    }
}
