<?php

namespace App\Http\Controllers\Planter;

use App\Http\Controllers\Controller;
use App\Services\PlanterIdentityService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(PlanterIdentityService $identity): View
    {
        $planter = $identity->ensureIssued(auth('planter')->user())->load(['firstAudit', 'finalAudit']);

        return view('planter.dashboard', [
            'planter' => $planter,
        ]);
    }
}
