@props(['planter'])

@php $style = $planter->categoryStyle(); @endphp

<span {{ $attributes->class(['inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset', $style['badge']]) }} title="Registration type: {{ $style['label'] }}">
    <span class="h-2 w-2 rounded-full {{ $style['dot'] }}" aria-hidden="true"></span>
    {{ $style['label'] }}
</span>
