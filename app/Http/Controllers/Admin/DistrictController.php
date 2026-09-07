<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Districts\StoreDistrictRequest;
use App\Http\Requests\Districts\UpdateDistrictRequest;
use App\Models\District;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DistrictController extends Controller
{
    public function index(Request $request): View
    {
        $districts = District::query()
            ->withCount(['rdoDivisions', 'planters'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = '%'.$request->string('q')->trim().'%';

                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', $search)
                        ->orWhere('code', 'like', $search);
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('districts.index', [
            'districts' => $districts,
            'filters' => $request->only(['q', 'status']),
        ]);
    }

    public function create(): View
    {
        return view('districts.create', [
            'district' => new District(['status' => District::STATUS_ACTIVE]),
        ]);
    }

    public function store(StoreDistrictRequest $request): RedirectResponse
    {
        District::query()->create($request->validated());

        return redirect()
            ->route('admin.districts.index')
            ->with('success', 'District created successfully.');
    }

    public function show(District $district): View
    {
        $district->loadCount(['rdoDivisions', 'planters']);
        $district->load(['rdoDivisions' => fn ($query) => $query->orderBy('name')]);

        return view('districts.show', compact('district'));
    }

    public function edit(District $district): View
    {
        return view('districts.edit', compact('district'));
    }

    public function update(UpdateDistrictRequest $request, District $district): RedirectResponse
    {
        $district->update($request->validated());

        return redirect()
            ->route('admin.districts.index')
            ->with('success', 'District updated successfully.');
    }

    public function destroy(District $district): RedirectResponse
    {
        if ($district->planters()->exists() || $district->rdoDivisions()->exists()) {
            return back()->withErrors([
                'district' => 'This district has linked RDO divisions or planters and cannot be deleted. Mark it inactive instead.',
            ]);
        }

        $district->delete();

        return redirect()
            ->route('admin.districts.index')
            ->with('success', 'District deleted successfully.');
    }
}
