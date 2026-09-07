<?php

namespace App\Http\Controllers\Planter;

use App\Http\Controllers\Controller;
use App\Http\Requests\Planters\UpdatePlanterProfileRequest;
use App\Services\PlanterIdentityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(PlanterIdentityService $identity): View
    {
        return view('planter.profile', [
            'planter' => $identity->ensureIssued(auth('planter')->user()),
        ]);
    }

    public function update(UpdatePlanterProfileRequest $request): RedirectResponse
    {
        $request->user('planter')->update(
            \App\Support\PlanterLocation::hydrateNames($request->validated())
        );

        return back()->with('success', 'Your profile has been updated.');
    }

    public function qrPng(PlanterIdentityService $identity): Response
    {
        $planter = $identity->ensureIssued(auth('planter')->user());
        abort_unless($planter->isApproved() && filled($planter->identification_number), 404);

        return response($identity->png($planter, 480), 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'private, max-age=300',
        ]);
    }

    public function downloadQr(PlanterIdentityService $identity, string $format): Response
    {
        $planter = $identity->ensureIssued(auth('planter')->user());
        abort_unless($planter->isApproved() && filled($planter->identification_number), 404);
        abort_unless(in_array($format, ['png', 'svg'], true), 404);

        if ($format === 'svg') {
            return response($identity->svg($planter, 2000), 200, [
                'Content-Type' => 'image/svg+xml',
                'Content-Disposition' => 'attachment; filename="'.$planter->qrDownloadName('svg').'"',
            ]);
        }

        return response($identity->png($planter, 2000), 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="'.$planter->qrDownloadName('png').'"',
        ]);
    }
}
