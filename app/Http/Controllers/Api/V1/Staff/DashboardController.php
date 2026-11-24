<?php

namespace App\Http\Controllers\Api\V1\Staff;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Api\UserResource;
use App\Models\Certificate;
use App\Models\Planter;
use App\Models\PlanterAudit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends ApiController
{
    public function __invoke(Request $request): JsonResponse
    {
        $pending = Planter::query()->where('status', Planter::STATUS_PENDING);
        $ongoingStatuses = [PlanterAudit::STATUS_QUEUED, PlanterAudit::STATUS_IN_PROGRESS, PlanterAudit::STATUS_IN_REVIEW];

        return response()->json([
            'registrations' => [
                'pending' => (clone $pending)->count(),
                'pending_online' => (clone $pending)->where('registration_type', Planter::TYPE_ONLINE)->count(),
                'pending_offline' => (clone $pending)->where('registration_type', Planter::TYPE_OFFLINE)->count(),
            ],
            'registry' => [
                'approved' => Planter::query()->where('status', Planter::STATUS_APPROVED)->count(),
                'rejected' => Planter::query()->where('status', Planter::STATUS_REJECTED)->count(),
            ],
            'audits' => [
                'ongoing_first' => PlanterAudit::query()->where('round', PlanterAudit::ROUND_FIRST)
                    ->where('is_current', true)->whereIn('status', $ongoingStatuses)->count(),
                'ongoing_second' => PlanterAudit::query()->where('round', PlanterAudit::ROUND_FINAL)
                    ->where('is_current', true)->whereIn('status', $ongoingStatuses)->count(),
                'passed' => PlanterAudit::query()
                    ->whereIn('status', [PlanterAudit::STATUS_PASSED, PlanterAudit::STATUS_CONDITIONAL])->count(),
                'failed' => PlanterAudit::query()->where('status', PlanterAudit::STATUS_FAILED)->count(),
            ],
            'certificates' => [
                'issued' => Certificate::query()->where('status', Certificate::STATUS_ISSUED)->count(),
                'revoked' => Certificate::query()->where('status', Certificate::STATUS_REVOKED)->count(),
            ],
            'user' => new UserResource($request->user()),
        ]);
    }
}
