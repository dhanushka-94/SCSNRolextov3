@php
    $isEdit = $division->exists;
    $selectedDistrictId = old('district_id', $division->district_id);
    $selectedDistrict = collect($districts)->firstWhere('id', (int) $selectedDistrictId);
    $selectedDistrictLabel = $selectedDistrict
        ? $selectedDistrict->name.' ('.$selectedDistrict->code.')'
        : '';
@endphp

<div class="grid gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label for="district_id" class="label-field">District</label>
        <div
            class="search-select"
            data-search-select
            data-search-placeholder="Search district..."
            data-search-empty="No districts found"
        >
            <input
                type="hidden"
                id="district_id"
                name="district_id"
                value="{{ $selectedDistrictId }}"
                data-search-value
                required
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
                    aria-controls="district_id-list"
                >
                <span class="search-select-chevron" aria-hidden="true">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                </span>
            </div>
            <ul id="district_id-list" class="search-select-menu hidden" data-search-menu role="listbox">
                @foreach ($districts as $district)
                    <li
                        class="search-select-option {{ (string) $selectedDistrictId === (string) $district->id ? 'is-active' : '' }}"
                        role="option"
                        data-value="{{ $district->id }}"
                        data-label="{{ $district->name }} ({{ $district->code }})"
                        aria-selected="{{ (string) $selectedDistrictId === (string) $district->id ? 'true' : 'false' }}"
                    >
                        <span>{{ $district->name }}</span>
                        <span class="search-select-meta">{{ $district->code }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
        @error('district_id')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
    </div>

    <div class="sm:col-span-2">
        <label for="name" class="label-field">Rubber Development Officer division</label>
        <input id="name" name="name" type="text" value="{{ old('name', $division->name) }}" required class="input-field">
        @error('name')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="code" class="label-field">RDO division code</label>
        <input id="code" name="code" type="text" maxlength="4" value="{{ old('code', $division->code) }}" required class="input-field uppercase" placeholder="e.g. AMIT" style="text-transform: uppercase">
        <p class="mt-1 text-xs text-muted">Exactly 4 uppercase characters from the division name (unique across all divisions).</p>
        @error('code')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="status" class="label-field">Status</label>
        <select id="status" name="status" class="input-field">
            @foreach (\App\Models\RdoDivision::statuses() as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $division->status) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
    </div>
</div>
