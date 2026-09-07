<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Planter;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $pendingQuery = Planter::query()->where('status', Planter::STATUS_PENDING);

        return view('dashboard', [
            'totalUsers' => User::query()->count(),
            'activeUsers' => User::query()->where('status', User::STATUS_ACTIVE)->count(),
            'pendingPlanters' => (clone $pendingQuery)->count(),
            'pendingOnline' => (clone $pendingQuery)->where('registration_type', Planter::TYPE_ONLINE)->count(),
            'pendingOffline' => (clone $pendingQuery)->where('registration_type', Planter::TYPE_OFFLINE)->count(),
            'approvedPlanters' => Planter::query()->where('status', Planter::STATUS_APPROVED)->count(),
            'rejectedPlanters' => Planter::query()->where('status', Planter::STATUS_REJECTED)->count(),
            'activeAudits' => \App\Models\PlanterAudit::query()
                ->whereIn('status', [
                    \App\Models\PlanterAudit::STATUS_QUEUED,
                    \App\Models\PlanterAudit::STATUS_IN_PROGRESS,
                    \App\Models\PlanterAudit::STATUS_IN_REVIEW,
                ])
                ->count(),
            'completedAudits' => \App\Models\PlanterAudit::query()
                ->whereIn('status', [
                    \App\Models\PlanterAudit::STATUS_PASSED,
                    \App\Models\PlanterAudit::STATUS_CONDITIONAL,
                    \App\Models\PlanterAudit::STATUS_FAILED,
                ])
                ->count(),
            'issuedCertificates' => \App\Models\Certificate::query()
                ->where('status', \App\Models\Certificate::STATUS_ISSUED)
                ->count(),
            'recentPlanters' => Planter::query()->where('status', Planter::STATUS_APPROVED)->latest()->limit(6)->get(),
        ]);
    }
}
