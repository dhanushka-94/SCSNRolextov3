<?php

namespace App\Http\Controllers\Planter\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Planters\OfflineRegisterPlanterRequest;
use App\Http\Requests\Planters\RegisterPlanterRequest;
use App\Models\Planter;
use App\Services\PlanterIdentityService;
use App\Services\RegistrationSpamGuard;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class RegisterController extends Controller
{
    public function create(RegistrationSpamGuard $spamGuard): View
    {
        $spamGuard->markFormStarted();

        return view('planter.auth.register', [
            'recaptchaSiteKey' => $spamGuard->captchaEnabled()
                ? config('registration.recaptcha.site_key')
                : null,
        ]);
    }

    public function store(RegisterPlanterRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('prior_certificate_document');

        if ($request->hasFile('prior_certificate_document')) {
            $data['prior_certificate_document'] = $request->file('prior_certificate_document')
                ->store('planter-certificates', 'local');
        }

        $planter = $this->createPendingPlanter($data, Planter::TYPE_ONLINE);

        session()->forget('planter_registration_started_at');

        return redirect()
            ->route('planter.register.submitted')
            ->with('identification_number', $planter->identification_number);
    }

    public function storeOffline(OfflineRegisterPlanterRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('application_document');
        $data['application_document'] = $request->file('application_document')->store('planter-applications', 'local');

        $planter = $this->createPendingPlanter($data, Planter::TYPE_OFFLINE);

        session()->forget('planter_registration_started_at');

        return redirect()
            ->route('planter.register.submitted')
            ->with('identification_number', $planter->identification_number);
    }

    public function submitted(): View|RedirectResponse
    {
        $identificationNumber = session('identification_number');

        if (blank($identificationNumber)) {
            return redirect()->route('planter.register');
        }

        return view('planter.auth.submitted', [
            'identificationNumber' => $identificationNumber,
        ]);
    }

    public function formPdf(): Response
    {
        $regular = storage_path('fonts/NotoSansSinhala-Regular.ttf');
        $bold = storage_path('fonts/NotoSansSinhala-Bold.ttf');

        if (! is_file($bold)) {
            $bold = $regular;
        }

        // DomPDF loads local fonts/images from absolute filesystem paths (not file:// URLs).
        $pdf = Pdf::loadView('planter.pdf.registration-form', [
            'logo' => str_replace('\\', '/', public_path('SCSNR-logo.png')),
            'fontRegular' => str_replace('\\', '/', $regular),
            'fontBold' => str_replace('\\', '/', $bold),
            'governingBodies' => collect(config('app.governing_bodies'))->map(function (array $body) {
                $body['logo_path'] = str_replace('\\', '/', public_path($body['logo']));

                return $body;
            })->all(),
        ])->setPaper('a4');

        $pdf->setOption('isRemoteEnabled', true);
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('defaultFont', 'NotoSinhala');

        return $pdf->download('SCSNR-planter-registration-form.pdf');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function createPendingPlanter(array $data, string $type): Planter
    {
        return DB::transaction(function () use ($data, $type) {
            unset($data['password']);
            $data['status'] = Planter::STATUS_PENDING;
            $data['registration_type'] = $type;

            $data = app(PlanterIdentityService::class)->assignRegistrationNumbers($data);

            return Planter::query()->create($data);
        });
    }
}
