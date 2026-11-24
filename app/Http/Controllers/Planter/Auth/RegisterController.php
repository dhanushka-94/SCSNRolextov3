<?php

namespace App\Http\Controllers\Planter\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Planters\OfflineRegisterPlanterRequest;
use App\Http\Requests\Planters\RegisterPlanterRequest;
use App\Models\Planter;
use App\Services\NotifySmsService;
use App\Services\PlanterIdentityService;
use App\Services\RegistrationFormPdf;
use App\Services\RegistrationSpamGuard;
use App\Support\PlanterLocation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class RegisterController extends Controller
{
    /** Step 1: choose the registration type (sole proprietor, group or company). */
    public function create(): View
    {
        return view('planter.auth.categories', [
            'categories' => Planter::registrationCategories(),
        ]);
    }

    /** Step 2: the application form for the chosen registration type. */
    public function apply(string $category, RegistrationSpamGuard $spamGuard): View
    {
        $categories = Planter::registrationCategories();

        abort_unless(array_key_exists($category, $categories), 404);

        $spamGuard->markFormStarted();

        return view('planter.auth.register', [
            'category' => $category,
            'categoryLabel' => $categories[$category],
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
        app(NotifySmsService::class)->sendRegistrationSubmitted($planter);

        session()->forget('planter_registration_started_at');

        return redirect()
            ->route('planter.register.submitted')
            ->with('submission_reference', $planter->temporary_id);
    }

    public function storeOffline(OfflineRegisterPlanterRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('application_document');
        $data['application_document'] = $request->file('application_document')->store('planter-applications', 'local');

        $planter = $this->createPendingPlanter($data, Planter::TYPE_OFFLINE);
        app(NotifySmsService::class)->sendRegistrationSubmitted($planter);

        session()->forget('planter_registration_started_at');

        return redirect()
            ->route('planter.register.submitted')
            ->with('submission_reference', $planter->temporary_id);
    }

    public function submitted(): View|RedirectResponse
    {
        $submissionReference = session('submission_reference');

        if (blank($submissionReference)) {
            return redirect()->route('planter.register');
        }

        return view('planter.auth.submitted', [
            'submissionReference' => $submissionReference,
        ]);
    }

    public function formPdf(): Response
    {
        // Preferred: the pre-built file (correct Sinhala and Tamil shaping), see `php artisan sppnr:build-registration-form`.
        if (RegistrationFormPdf::isBuilt()) {
            return response()->download(RegistrationFormPdf::builtPath(), RegistrationFormPdf::DOWNLOAD_NAME, [
                'Content-Type' => 'application/pdf',
            ]);
        }

        // Fallback: DomPDF cannot shape complex scripts, so vowel signs may be misplaced.
        $pdf = Pdf::loadView('planter.pdf.registration-form', RegistrationFormPdf::viewData())->setPaper('a4');

        $pdf->setOption('isRemoteEnabled', true);
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('defaultFont', 'NotoSinhala');

        return $pdf->download(RegistrationFormPdf::DOWNLOAD_NAME);
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
            $data = PlanterLocation::hydrateNames($data);

            $data = app(PlanterIdentityService::class)->assignTemporaryId($data);

            return Planter::query()->create($data);
        });
    }
}
