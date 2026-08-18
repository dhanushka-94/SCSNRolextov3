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
        return view('dashboard', [
            'totalUsers' => User::query()->count(),
            'activeUsers' => User::query()->where('status', User::STATUS_ACTIVE)->count(),
            'pendingPlanters' => Planter::query()->where('status', Planter::STATUS_PENDING)->count(),
            'approvedPlanters' => Planter::query()->where('status', Planter::STATUS_APPROVED)->count(),
            'recentPlanters' => Planter::query()->latest()->limit(6)->get(),
        ]);
    }
}
