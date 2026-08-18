<?php

namespace App\Http\Controllers\Planter\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Planters\SetPlanterPasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SetPasswordController extends Controller
{
    public function create(): View
    {
        return view('planter.auth.set-password');
    }

    public function store(SetPlanterPasswordRequest $request): RedirectResponse
    {
        $planter = $request->planter();
        $planter->update([
            'password' => $request->validated('password'),
        ]);

        return redirect()
            ->route('planter.login')
            ->with('success', 'Password created. You can now sign in and update your profile.');
    }
}
