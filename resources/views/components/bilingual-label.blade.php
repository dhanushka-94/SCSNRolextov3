@props([
    'si',
    'en',
    'for' => null,
    'required' => false,
])

<label @if ($for) for="{{ $for }}" @endif {{ $attributes->merge(['class' => 'label-field']) }}>
    <span class="block leading-snug">{{ $si }}</span>
    <span class="mt-0.5 block text-xs font-normal text-muted">{{ $en }}</span>
    @if ($required)
        <span class="sr-only">required</span>
    @endif
</label>
