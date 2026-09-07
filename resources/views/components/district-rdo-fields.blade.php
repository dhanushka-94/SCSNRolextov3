@props([
    'districtId' => null,
    'rdoDivisionId' => null,
    'required' => true,
    'bilingual' => false,
    'idPrefix' => '',
])

@php
    $districts = \App\Models\District::query()->active()->orderBy('name')->get(['id', 'name', 'code']);
    $divisionMap = \App\Support\PlanterLocation::activeDivisionMap();
    $selectedDistrictId = old('district_id', $districtId);
    $selectedDivisionId = old('rdo_division_id', $rdoDivisionId);
    $districtFieldId = $idPrefix.'district_id';
    $rdoFieldId = $idPrefix.'rdo_division_id';
    $selectedDistrict = $districts->firstWhere('id', (int) $selectedDistrictId);
    $selectedDistrictLabel = $selectedDistrict
        ? $selectedDistrict->name.' ('.$selectedDistrict->code.')'
        : '';
@endphp

<div class="contents" data-district-rdo>
    <div>
        @if ($bilingual)
            <x-bilingual-label si="දිස්ත්‍රික්කය" en="District" :for="$districtFieldId" />
        @else
            <label for="{{ $districtFieldId }}" class="label-field">District</label>
        @endif

        <div
            class="search-select"
            data-search-select
            data-search-placeholder="Search district..."
            data-search-empty="No districts found"
        >
            <input
                type="hidden"
                id="{{ $districtFieldId }}"
                name="district_id"
                value="{{ $selectedDistrictId }}"
                data-district-select
                data-search-value
                @required($required)
            >
            <div class="search-select-control">
                <input
                    type="text"
                    class="input-field search-select-input"
                    data-search-input
                    value="{{ $selectedDistrictLabel }}"
                    placeholder="Search district..."
                    autocomplete="off"
                    spellcheck="false"
                    role="combobox"
                    aria-expanded="false"
                    aria-controls="{{ $districtFieldId }}-list"
                >
                <span class="search-select-chevron" aria-hidden="true">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                </span>
            </div>
            <ul id="{{ $districtFieldId }}-list" class="search-select-menu hidden" data-search-menu role="listbox">
                @foreach ($districts as $district)
                    <li
                        class="search-select-option"
                        role="option"
                        data-value="{{ $district->id }}"
                        data-label="{{ $district->name }} ({{ $district->code }})"
                        aria-selected="{{ (string) $selectedDistrictId === (string) $district->id ? 'true' : 'false' }}"
                        @class(['is-active' => (string) $selectedDistrictId === (string) $district->id])
                    >
                        <span>{{ $district->name }}</span>
                        <span class="search-select-meta">{{ $district->code }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
        @error('district_id')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
        @error('district')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
    </div>

    <div>
        @if ($bilingual)
            <x-bilingual-label si="රබර් සංවර්ධන නිලධාරි කොට්ඨාසය" en="Rubber Development Officer division" :for="$rdoFieldId" />
        @else
            <label for="{{ $rdoFieldId }}" class="label-field">Rubber Development Officer division</label>
        @endif

        <div
            class="search-select"
            data-search-select
            data-search-placeholder="{{ blank($selectedDistrictId) ? 'Select district first' : 'Search division...' }}"
            data-search-empty="No divisions found"
            data-rdo-search
        >
            <input
                type="hidden"
                id="{{ $rdoFieldId }}"
                name="rdo_division_id"
                value="{{ $selectedDivisionId }}"
                data-rdo-select
                data-search-value
                data-selected="{{ $selectedDivisionId }}"
                @required($required)
            >
            <div class="search-select-control">
                <input
                    type="text"
                    class="input-field search-select-input"
                    data-search-input
                    data-rdo-input
                    value=""
                    placeholder="{{ blank($selectedDistrictId) ? 'Select district first' : 'Search division...' }}"
                    autocomplete="off"
                    spellcheck="false"
                    role="combobox"
                    aria-expanded="false"
                    aria-controls="{{ $rdoFieldId }}-list"
                    @disabled(blank($selectedDistrictId))
                >
                <span class="search-select-chevron" aria-hidden="true">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                </span>
            </div>
            <ul id="{{ $rdoFieldId }}-list" class="search-select-menu hidden" data-search-menu role="listbox"></ul>
        </div>
        @error('rdo_division_id')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
        @error('rdd_division')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
    </div>

    <script type="application/json" data-rdo-map>{!! json_encode($divisionMap, JSON_UNESCAPED_UNICODE) !!}</script>
</div>
