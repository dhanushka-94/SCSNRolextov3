@props([
    'size' => 'md',
    'showNames' => false,
    'nameLayout' => 'below',
    'layout' => 'row',
])

@php
    $bodies = config('app.governing_bodies', []);
    $logoClass = match ($size) {
        'sm' => 'h-8 w-8 sm:h-9 sm:w-9',
        'lg' => 'h-20 w-20 sm:h-24 sm:w-24',
        default => 'h-14 w-14 sm:h-16 sm:w-16',
    };
    $wrapClass = $layout === 'stack'
        ? 'flex flex-col items-center gap-4'
        : 'flex flex-wrap items-center justify-center gap-3 sm:gap-5';
@endphp

<div {{ $attributes->merge(['class' => $wrapClass]) }}>
    @foreach ($bodies as $body)
        @php
            $itemClass = match (true) {
                $showNames && $nameLayout === 'inline' => 'flex max-w-[16rem] items-center gap-2 text-left',
                $showNames => 'flex max-w-[11rem] flex-col items-center gap-2 text-center',
                default => '',
            };
        @endphp
        <div class="{{ $itemClass }}">
            <img
                src="{{ asset($body['logo']) }}"
                alt="{{ $body['name'] }}"
                title="{{ $body['name'] }}"
                class="{{ $logoClass }} shrink-0 rounded-lg bg-white object-contain p-0.5 shadow-sm ring-1 ring-sand/80"
            >
            @if ($showNames)
                <span class="text-[11px] font-medium leading-snug text-bark sm:text-xs">
                    {{ $body['short'] ?? $body['name'] }}
                </span>
            @endif
        </div>
    @endforeach
</div>
