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
            'recentPlanters' => Planter::query()->latest()->limit(6)->get(),
        ]);
    }
}
