@props([
    'latitude' => null,
    'longitude' => null,
    'required' => true,
    'bilingual' => false,
    'readonly' => false,
    'height' => '18rem',
])

@php
    $lat = old('latitude', $latitude);
    $lng = old('longitude', $longitude);
    $hasPin = filled($lat) && filled($lng);
@endphp

<div
    {{ $attributes->class(['space-y-3']) }}
    data-map-pin
    data-readonly="{{ $readonly ? '1' : '0' }}"
    data-default-lat="{{ \App\Support\FarmCoordinates::DEFAULT_LATITUDE }}"
    data-default-lng="{{ \App\Support\FarmCoordinates::DEFAULT_LONGITUDE }}"
    data-default-zoom="{{ \App\Support\FarmCoordinates::DEFAULT_ZOOM }}"
    data-pinned-zoom="{{ \App\Support\FarmCoordinates::PINNED_ZOOM }}"
>
    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            @if ($bilingual)
                <p class="label-field">
                    <span class="block font-sinhala text-[0.95rem] leading-5">සිතියමෙන් ගොවිපල පිහිටීම</span>
                    <span class="block text-sm font-medium text-bark">Farm location on map</span>
                </p>
            @else
                <p class="label-field">Farm location on map</p>
            @endif
            <p class="mt-1 text-xs text-muted">
                @if ($readonly)
                    Map pin saved with this registration.
                @else
                    Click the map to drop a pin. Drag the pin to adjust. Coordinates must be inside Sri Lanka.
                @endif
            </p>
        </div>

        @unless ($readonly)
            <div class="flex flex-wrap gap-2">
                <button type="button" class="btn-secondary px-3 py-1.5 text-xs" data-map-locate>
                    Use my location
                </button>
                <button type="button" class="btn-secondary px-3 py-1.5 text-xs" data-map-clear>
                    Clear pin
                </button>
            </div>
        @endunless
    </div>

    <div
        data-map-canvas
        class="map-pin-canvas overflow-hidden rounded-2xl border border-line bg-sand/40 shadow-inner"
        style="height: {{ $height }};"
    ></div>

    <div class="grid gap-3 sm:grid-cols-2">
        <div>
            <label class="label-field" for="{{ $readonly ? 'latitude_display' : 'latitude' }}">Latitude</label>
            <input
                id="{{ $readonly ? 'latitude_display' : 'latitude' }}"
                @unless ($readonly) name="latitude" @endunless
                type="text"
                inputmode="decimal"
                value="{{ $lat }}"
                @if ($required && ! $readonly) required @endif
                @if ($readonly) readonly @endif
                data-map-lat
                class="input-field font-mono text-sm {{ $readonly ? 'bg-cream' : '' }}"
                placeholder="e.g. 7.2512345"
                autocomplete="off"
            >
            @error('latitude')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="label-field" for="{{ $readonly ? 'longitude_display' : 'longitude' }}">Longitude</label>
            <input
                id="{{ $readonly ? 'longitude_display' : 'longitude' }}"
                @unless ($readonly) name="longitude" @endunless
                type="text"
                inputmode="decimal"
                value="{{ $lng }}"
                @if ($required && ! $readonly) required @endif
                @if ($readonly) readonly @endif
                data-map-lng
                class="input-field font-mono text-sm {{ $readonly ? 'bg-cream' : '' }}"
                placeholder="e.g. 80.3412345"
                autocomplete="off"
            >
            @error('longitude')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
        </div>
    </div>

    <p class="text-xs text-muted" data-map-status>
        {{ $hasPin ? 'Pin set.' : ($readonly ? 'No map pin recorded.' : 'No pin yet — click the map to set one.') }}
    </p>
</div>
