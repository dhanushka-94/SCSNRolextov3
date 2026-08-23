<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Planters\RejectPlanterRequest;
use App\Http\Requests\Planters\StorePlanterRequest;
use App\Http\Requests\Planters\UpdatePlanterRequest;
use App\Models\Planter;
use App\Services\PlanterIdentityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PlanterController extends Controller
{
    public function index(Request $request): View
    {
        $planters = Planter::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = '%'.$request->string('q')->trim().'%';

                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', $search)
                        ->orWhere('email', 'like', $search)
                        ->orWhere('nic', 'like', $search)
                        ->orWhere('phone', 'like', $search)
                        ->orWhere('temporary_id', 'like', $search)
                        ->orWhere('identification_number', 'like', $search);
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('district'), fn ($query) => $query->where('district', $request->string('district')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('planters.index', [
            'planters' => $planters,
            'filters' => $request->only(['q', 'status', 'district']),
        ]);
    }

    public function approvalLobby(Request $request): View
    {
        $pendingQuery = Planter::query()->where('status', Planter::STATUS_PENDING);

        $planters = (clone $pendingQuery)
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = '%'.$request->string('q')->trim().'%';

                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', $search)
                        ->orWhere('email', 'like', $search)
                        ->orWhere('nic', 'like', $search)
                        ->orWhere('phone', 'like', $search)
                        ->orWhere('identification_number', 'like', $search);
                });
            })
            ->when($request->filled('district'), fn ($query) => $query->where('district', $request->string('district')))
            ->when($request->filled('registration_type'), fn ($query) => $query->where('registration_type', $request->string('registration_type')))
            ->oldest()
            ->paginate(10)
            ->withQueryString();

        return view('planters.approval-lobby', [
            'planters' => $planters,
            'filters' => $request->only(['q', 'district', 'registration_type']),
            'stats' => [
                'total' => (clone $pendingQuery)->count(),
                'online' => (clone $pendingQuery)->where('registration_type', Planter::TYPE_ONLINE)->count(),
                'offline' => (clone $pendingQuery)->where('registration_type', Planter::TYPE_OFFLINE)->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('planters.create', [
            'planter' => new Planter(['status' => Planter::STATUS_PENDING]),
        ]);
    }

    public function store(StorePlanterRequest $request, PlanterIdentityService $identity): RedirectResponse
    {
        $planter = DB::transaction(function () use ($request, $identity) {
            $data = $request->validated();
            $data['registration_type'] = Planter::TYPE_ONLINE;

            if (blank($data['password'] ?? null)) {
                unset($data['password']);
            }

            if ($data['status'] !== Planter::STATUS_REJECTED) {
                $data['rejection_reason'] = null;
            }

            $data = $identity->assignRegistrationNumbers($data);

            $planter = Planter::query()->create($data);

            if ($planter->status === Planter::STATUS_APPROVED) {
                $planter = $identity->approve($planter, $request->user('web')->id);
            }

            return $planter;
        });

        return redirect()
            ->route('admin.planters.index')
            ->with('success', 'Planter registration saved successfully. SCSNR ID: '.$planter->identification_number);
    }

    public function show(Planter $planter, PlanterIdentityService $identity): View
    {
        $planter = $identity->ensureIssued($planter);
        $planter->load('approver');

        return view('planters.show', compact('planter'));
    }

    public function edit(Planter $planter): View
    {
        return view('planters.edit', compact('planter'));
    }

    public function update(UpdatePlanterRequest $request, Planter $planter): RedirectResponse
    {
        $data = $request->validated();

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        if ($data['status'] === Planter::STATUS_PENDING) {
            $data['approved_by'] = null;
            $data['approved_at'] = null;
            $data['rejection_reason'] = null;
        }

        if ($data['status'] !== Planter::STATUS_REJECTED) {
            $data['rejection_reason'] = null;
        }

        $wasApproved = $planter->isApproved();
        $planter->update($data);

        if ($planter->status === Planter::STATUS_APPROVED && ! $wasApproved) {
            app(PlanterIdentityService::class)->approve($planter, $request->user('web')->id);
        }

        return redirect()
            ->route('admin.planters.index')
            ->with('success', 'Planter record updated successfully.');
    }

    public function destroy(Planter $planter): RedirectResponse
    {
        if ($planter->application_document) {
            Storage::disk('local')->delete($planter->application_document);
        }

        if ($planter->prior_certificate_document) {
            Storage::disk('local')->delete($planter->prior_certificate_document);
        }

        $planter->delete();

        return redirect()
            ->route('admin.planters.index')
            ->with('success', 'Planter record deleted successfully.');
    }

    public function approve(Request $request, Planter $planter, PlanterIdentityService $identity): RedirectResponse
    {
        $planter = $identity->approve($planter, $request->user('web')->id);

        return back()->with('success', 'Planter approved. SCSNR ID: '.$planter->identification_number);
    }

    public function qr(Planter $planter, PlanterIdentityService $identity): Response
    {
        $planter = $identity->ensureIssued($planter);
        abort_unless($planter->isApproved() && filled($planter->identification_number), 404);

        return response($identity->png($planter, 480), 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'private, max-age=300',
        ]);
    }

    public function reject(RejectPlanterRequest $request, Planter $planter): RedirectResponse
    {
        $planter->update([
            'status' => Planter::STATUS_REJECTED,
            'approved_by' => $request->user('web')->id,
            'approved_at' => now(),
            'rejection_reason' => $request->validated('rejection_reason'),
        ]);

        return back()->with('success', 'Planter registration rejected.');
    }

    public function document(Planter $planter)
    {
        abort_unless($planter->hasApplicationDocument() && Storage::disk('local')->exists($planter->application_document), 404);

        return Storage::disk('local')->download(
            $planter->application_document,
            str_replace('/', '-', $planter->identification_number).'-registration-form.'.pathinfo($planter->application_document, PATHINFO_EXTENSION)
        );
    }

    public function certificateDocument(Planter $planter)
    {
        abort_unless($planter->hasPriorCertificateDocument() && Storage::disk('local')->exists($planter->prior_certificate_document), 404);

        return Storage::disk('local')->download(
            $planter->prior_certificate_document,
            str_replace('/', '-', $planter->identification_number).'-prior-certificate.'.pathinfo($planter->prior_certificate_document, PATHINFO_EXTENSION)
        );
    }
}
