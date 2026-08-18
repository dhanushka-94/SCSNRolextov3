@props(['planter'])

@php
    $tree = $planter->auditTimelineTree();
@endphp

<article {{ $attributes->merge(['class' => 'card overflow-hidden']) }}>
    <div class="border-b border-sand bg-cream px-5 py-5 sm:px-8">
        <h2 class="text-lg font-semibold text-forest-dark">Sustainability audit status</h2>
        <p class="mt-1 text-sm text-muted">Track your certification audit from opening through to the final result.</p>
    </div>

    <div class="p-5 sm:p-8">
        <ol class="space-y-0">
            @foreach ($tree as $index => $stage)
                @php
                    $isLast = $index === count($tree) - 1;
                    $state = $stage['state'];
                    $nodeClass = match ($state) {
                        'completed' => 'border-leaf bg-leaf text-white',
                        'current' => 'border-tan bg-tan/20 text-forest-dark ring-2 ring-tan/40',
                        default => 'border-line bg-paper text-muted',
                    };
                    $lineClass = $state === 'completed' ? 'bg-leaf' : 'bg-line';
                @endphp

                <li class="relative flex gap-4 pb-6 {{ $isLast ? 'pb-0' : '' }}">
                    @unless ($isLast)
                        <span class="absolute left-[15px] top-8 h-[calc(100%-0.5rem)] w-0.5 {{ $lineClass }}" aria-hidden="true"></span>
                    @endunless

                    <div class="relative z-[1] flex h-8 w-8 shrink-0 items-center justify-center rounded-full border-2 {{ $nodeClass }}">
                        @if ($state === 'completed')
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        @elseif ($state === 'current')
                            <span class="h-2.5 w-2.5 rounded-full bg-tan"></span>
                        @else
                            <span class="h-2 w-2 rounded-full bg-line"></span>
                        @endif
                    </div>

                    <div class="min-w-0 flex-1 pt-0.5">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="font-semibold {{ $state === 'upcoming' ? 'text-muted' : 'text-forest-dark' }}">
                                {{ $stage['label'] }}
                            </p>
                            @if ($state === 'current')
                                <span class="rounded-full bg-tan/30 px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-bark-dark">Current</span>
                            @elseif ($state === 'completed')
                                <span class="rounded-full bg-leaf/10 px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-forest">Completed</span>
                            @endif
                        </div>
                        <p class="mt-1 text-sm {{ $state === 'upcoming' ? 'text-muted/80' : 'text-muted' }}">{{ $stage['description'] }}</p>

                        @if ($stage['key'] === \App\Models\Planter::AUDIT_RESULT && $planter->audit_status === \App\Models\Planter::AUDIT_RESULT && $planter->audit_result_outcome)
                            <div class="mt-3 rounded-xl border border-sand bg-cream px-4 py-3">
                                <p class="text-xs font-semibold uppercase tracking-wide text-muted">Result</p>
                                <p class="mt-1 font-semibold text-forest-dark">
                                    {{ \App\Models\Planter::auditResultOutcomes()[$planter->audit_result_outcome] ?? $planter->audit_result_outcome }}
                                </p>
                                @if ($planter->audit_result_notes)
                                    <p class="mt-1 text-sm text-muted">{{ $planter->audit_result_notes }}</p>
                                @endif
                            </div>
                        @endif

                        @if ($state === 'current' && $stage['key'] === \App\Models\Planter::AUDIT_OPEN && ($planter->audit_status ?? \App\Models\Planter::AUDIT_NOT_STARTED) === \App\Models\Planter::AUDIT_NOT_STARTED)
                            <p class="mt-2 text-sm text-bark">Your audit file will open once RRISL schedules the first sustainability assessment.</p>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>

        @if ($planter->audit_status_updated_at)
            <p class="mt-6 border-t border-sand pt-4 text-xs text-muted">
                Last audit update: {{ sl_datetime($planter->audit_status_updated_at) }}
            </p>
        @endif
    </div>
</article>
