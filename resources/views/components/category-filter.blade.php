@props(['route', 'current' => null, 'counts' => []])

{{-- Colour legend + quick filter for the three registration types. Keeps the other filters in the URL. --}}
<nav class="mb-5 flex flex-wrap items-center gap-2" aria-label="Filter by registration type">
    <span class="mr-1 text-xs font-semibold uppercase tracking-wide text-muted">Type</span>

    <a href="{{ route($route, request()->except('category', 'page')) }}"
       class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-semibold {{ blank($current) ? 'border-forest bg-forest text-white' : 'border-sand bg-paper text-bark hover:bg-sand' }}">
        All
        @if (! empty($counts))
            <span class="rounded-full bg-black/10 px-1.5 text-[11px]">{{ array_sum($counts) }}</span>
        @endif
    </a>

    @foreach (\App\Models\Planter::categoryStyles() as $key => $style)
        <a href="{{ route($route, ['category' => $key] + request()->except('category', 'page')) }}"
           class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-semibold {{ $current === $key ? 'ring-2 ring-offset-1 ring-forest' : '' }} {{ $style['chip'] }}"
           @if ($current === $key) aria-current="true" @endif>
            <span class="h-2 w-2 rounded-full {{ $style['dot'] }}" aria-hidden="true"></span>
            {{ $style['label'] }}
            @if (! empty($counts))
                <span class="rounded-full bg-white/70 px-1.5 text-[11px]">{{ $counts[$key] ?? 0 }}</span>
            @endif
        </a>
    @endforeach
</nav>
