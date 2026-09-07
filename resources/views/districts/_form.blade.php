@php
    $isEdit = $district->exists;
@endphp

<div class="grid gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label for="name" class="label-field">District name</label>
        <input id="name" name="name" type="text" value="{{ old('name', $district->name) }}" required class="input-field">
        @error('name')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="code" class="label-field">District code</label>
        <input id="code" name="code" type="text" maxlength="4" value="{{ old('code', $district->code) }}" required class="input-field uppercase" placeholder="e.g. KEGA" style="text-transform: uppercase">
        <p class="mt-1 text-xs text-muted">Exactly 4 uppercase characters from the district name (used in registration numbers).</p>
        @error('code')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="status" class="label-field">Status</label>
        <select id="status" name="status" class="input-field">
            @foreach (\App\Models\District::statuses() as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $district->status) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
    </div>
</div>
