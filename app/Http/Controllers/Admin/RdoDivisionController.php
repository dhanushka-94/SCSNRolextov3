<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RdoDivisions\StoreRdoDivisionRequest;
use App\Http\Requests\RdoDivisions\UpdateRdoDivisionRequest;
use App\Models\District;
use App\Models\RdoDivision;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RdoDivisionController extends Controller
{
    public function index(Request $request): View
    {
        $divisions = RdoDivision::query()
            ->with('district')
            ->withCount('planters')
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = '%'.$request->string('q')->trim().'%';

                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', $search)
                        ->orWhere('code', 'like', $search)
                        ->orWhereHas('district', function ($district) use ($search) {
                            $district->where('name', 'like', $search)
                                ->orWhere('code', 'like', $search);
                        });
                });
            })
            ->when($request->filled('district_id'), fn ($query) => $query->where('district_id', $request->integer('district_id')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('rdo-divisions.index', [
            'divisions' => $divisions,
            'districts' => District::query()->orderBy('name')->get(['id', 'name', 'code']),
            'filters' => $request->only(['q', 'district_id', 'status']),
        ]);
    }

    public function create(): View
    {
        return view('rdo-divisions.create', [
            'division' => new RdoDivision(['status' => RdoDivision::STATUS_ACTIVE]),
            'districts' => District::query()->active()->orderBy('name')->get(['id', 'name', 'code']),
        ]);
    }

    public function store(StoreRdoDivisionRequest $request): RedirectResponse
    {
        RdoDivision::query()->create($request->validated());

        return redirect()
            ->route('admin.rdo-divisions.index')
            ->with('success', 'Rubber Development Officer division created successfully.');
    }

    public function show(RdoDivision $rdoDivision): View
    {
        $rdoDivision->load('district');
        $rdoDivision->loadCount('planters');

        return view('rdo-divisions.show', [
            'division' => $rdoDivision,
        ]);
    }

    public function edit(RdoDivision $rdoDivision): View
    {
        return view('rdo-divisions.edit', [
            'division' => $rdoDivision,
            'districts' => District::query()->orderBy('name')->get(['id', 'name', 'code']),
        ]);
    }

    public function update(UpdateRdoDivisionRequest $request, RdoDivision $rdoDivision): RedirectResponse
    {
        $rdoDivision->update($request->validated());

        return redirect()
            ->route('admin.rdo-divisions.index')
            ->with('success', 'Rubber Development Officer division updated successfully.');
    }

    public function destroy(RdoDivision $rdoDivision): RedirectResponse
    {
        if ($rdoDivision->planters()->exists()) {
            return back()->withErrors([
                'division' => 'This division has linked planters and cannot be deleted. Mark it inactive instead.',
            ]);
        }

        $rdoDivision->delete();

        return redirect()
            ->route('admin.rdo-divisions.index')
            ->with('success', 'Rubber Development Officer division deleted successfully.');
    }
}
