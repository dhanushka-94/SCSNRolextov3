<?php

namespace App\Http\Controllers\Api\V1\Staff;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\Certificates\IssueCertificateRequest;
use App\Http\Requests\Certificates\RevokeCertificateRequest;
use App\Http\Resources\Api\CertificateResource;
use App\Models\Certificate;
use App\Models\Planter;
use App\Services\CertificateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class CertificateController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->canManageCertificates(), 403);

        $status = $request->string('status')->toString() ?: Certificate::STATUS_ISSUED;
        abort_unless(in_array($status, [Certificate::STATUS_ISSUED, Certificate::STATUS_REVOKED], true), 422, 'Invalid status.');

        $certificates = Certificate::query()
            ->with('planter')
            ->where('status', $status)
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = '%'.$request->string('q')->trim().'%';

                $query->where(function ($inner) use ($search) {
                    $inner->where('certificate_number', 'like', $search)
                        ->orWhereHas('planter', fn ($planter) => $planter->where('name', 'like', $search)
                            ->orWhere('nic', 'like', $search)
                            ->orWhere('identification_number', 'like', $search));
                });
            })
            ->when($status === Certificate::STATUS_ISSUED, fn ($query) => $query->latest('issued_at'), fn ($query) => $query->latest('revoked_at'))
            ->paginate(min($request->integer('per_page', 20), 100))
            ->withQueryString();

        return CertificateResource::collection($certificates)->response();
    }

    public function show(Request $request, Certificate $certificate): JsonResponse
    {
        abort_unless($request->user()->canManageCertificates(), 403);

        return response()->json([
            'certificate' => new CertificateResource($certificate->load('planter')),
        ]);
    }

    public function issue(Request $request, Planter $planter, CertificateService $certificates): JsonResponse
    {
        abort_unless($request->user()->canManageCertificates(), 403);

        $data = $this->validatedFrom(IssueCertificateRequest::class, $request);

        try {
            $certificate = $certificates->issue($planter, $request->user(), $data['notes'] ?? null);
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json(['certificate' => new CertificateResource($certificate->load('planter'))], 201);
    }

    public function revoke(Request $request, Certificate $certificate, CertificateService $certificates): JsonResponse
    {
        abort_unless($request->user()->canManageCertificates(), 403);

        $data = $this->validatedFrom(RevokeCertificateRequest::class, $request);

        $certificate = $certificates->revoke($certificate, $request->user(), $data['revoke_reason']);

        return response()->json(['certificate' => new CertificateResource($certificate->load('planter'))]);
    }
}
