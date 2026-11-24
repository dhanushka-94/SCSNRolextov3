<?php

namespace App\Http\Controllers\Api\V1\Planter;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\Planters\UpdatePlanterProfileRequest;
use App\Http\Resources\Api\CertificateResource;
use App\Http\Resources\Api\PlanterResource;
use App\Models\Planter;
use App\Services\PlanterIdentityService;
use App\Support\PlanterLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PortalController extends ApiController
{
    public function dashboard(Request $request, PlanterIdentityService $identity): JsonResponse
    {
        /** @var Planter $planter */
        $planter = $identity->ensureIssued($request->user())
            ->load(['firstAudit', 'finalAudit', 'currentCertificate']);

        return response()->json([
            'planter' => new PlanterResource($planter),
            'audit_timeline' => $planter->auditTimelineTree(),
            'certificate' => $planter->currentCertificate
                ? new CertificateResource($planter->currentCertificate)
                : null,
            'qr_payload' => $identity->qrPayload($planter),
        ]);
    }

    public function profile(Request $request): JsonResponse
    {
        return response()->json(['planter' => new PlanterResource($request->user())]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $planter = $request->user();
        $planter->update(PlanterLocation::hydrateNames($this->validatedFrom(UpdatePlanterProfileRequest::class, $request)));

        return response()->json(['planter' => new PlanterResource($planter->fresh())]);
    }

    public function certificate(Request $request): JsonResponse
    {
        $certificate = $request->user()->currentCertificate()->first();

        if (! $certificate) {
            return response()->json(['message' => 'No active certificate yet.'], 404);
        }

        return response()->json(['certificate' => new CertificateResource($certificate)]);
    }
}
