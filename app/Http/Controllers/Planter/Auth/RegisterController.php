<?php

namespace App\Http\Controllers\Planter\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Planters\OfflineRegisterPlanterRequest;
use App\Http\Requests\Planters\RegisterPlanterRequest;
use App\Models\Planter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('planter.auth.register');
    }

    public function store(RegisterPlanterRequest $request): RedirectResponse
    {
        $planter = $this->createPendingPlanter($request->validated(), Planter::TYPE_ONLINE);

        return redirect()
            ->route('planter.register.submitted')
            ->with('temporary_id', $planter->temporary_id);
    }

    public function storeOffline(OfflineRegisterPlanterRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('application_document');
        $data['application_document'] = $request->file('application_document')->store('planter-applications', 'local');

        $planter = $this->createPendingPlanter($data, Planter::TYPE_OFFLINE);

        return redirect()
            ->route('planter.register.submitted')
            ->with('temporary_id', $planter->temporary_id);
    }

    public function submitted(): View|RedirectResponse
    {
        $temporaryId = session('temporary_id');

        if (blank($temporaryId)) {
            return redirect()->route('planter.register');
        }

        return view('planter.auth.submitted', [
            'temporaryId' => $temporaryId,
        ]);
    }

    public function formPdf(): Response
    {
        $pdf = Pdf::loadView('planter.pdf.registration-form', [
            'logo' => 'file:///'.str_replace('\\', '/', public_path('SCSNR-logo.png')),
        ])->setPaper('a4');

        return $pdf->download('SCSNR-planter-registration-form.pdf');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function createPendingPlanter(array $data, string $type): Planter
    {
        $data['temporary_id'] = Planter::generateTemporaryId();
        $data['status'] = Planter::STATUS_PENDING;
        $data['registration_type'] = $type;
        unset($data['password']);

        return Planter::query()->create($data);
    }
}
