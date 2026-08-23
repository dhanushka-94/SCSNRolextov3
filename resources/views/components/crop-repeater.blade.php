@props([
    'values' => [],
    'required' => false,
    'max' => 10,
    'placeholder' => 'නිෂ්පාදනය / Product',
    'addLabel' => 'එකතු කරන්න / Add field',
    'maxHint' => null,
])

@php
    $items = collect($values)
        ->filter(fn ($item) => filled($item))
        ->values()
        ->all();

    if ($items === []) {
        $items = [''];
    }

    $maxHint = $maxHint ?? ('උපරිම '.$max.' ක් · Maximum '.$max.' items');
@endphp

<div
    data-crop-repeater
    data-max="{{ $max }}"
    {{ $attributes->merge(['class' => 'space-y-3']) }}
>
    <div data-crop-list class="space-y-3">
        @foreach ($items as $index => $value)
            <div class="flex items-start gap-2" data-crop-row>
                <span class="mt-2.5 w-6 shrink-0 text-xs font-semibold text-muted" data-crop-index>{{ $index + 1 }}.</span>
                <input
                    type="text"
                    name="crops_products[]"
                    value="{{ is_array($value) ? ($value['name'] ?? '') : $value }}"
                    class="input-field"
                    placeholder="{{ $placeholder }}"
                    @if ($required && $index === 0) required @endif
                >
                <button
                    type="button"
                    data-crop-remove
                    class="mt-0.5 inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-line bg-paper text-lg font-semibold text-bark transition hover:bg-sand {{ count($items) < 2 ? 'invisible' : '' }}"
                    aria-label="Remove field"
                    title="Remove"
                >−</button>
            </div>
        @endforeach
    </div>

    <button
        type="button"
        data-crop-add
        class="inline-flex items-center gap-2 rounded-xl border border-dashed border-leaf/50 bg-leaf/5 px-4 py-2.5 text-sm font-semibold text-forest transition hover:bg-leaf/10"
    >
        <span class="inline-flex h-6 w-6 items-center justify-center rounded-lg bg-leaf text-base font-bold text-white">+</span>
        {{ $addLabel }}
    </button>

    <p class="text-xs text-muted">{{ $maxHint }}</p>

    <template data-crop-template>
        <div class="flex items-start gap-2" data-crop-row>
            <span class="mt-2.5 w-6 shrink-0 text-xs font-semibold text-muted" data-crop-index>1.</span>
            <input
                type="text"
                name="crops_products[]"
                value=""
                class="input-field"
                placeholder="{{ $placeholder }}"
            >
            <button
                type="button"
                data-crop-remove
                class="mt-0.5 inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-line bg-paper text-lg font-semibold text-bark transition hover:bg-sand"
                aria-label="Remove field"
                title="Remove"
            >−</button>
        </div>
    </template>
</div>
