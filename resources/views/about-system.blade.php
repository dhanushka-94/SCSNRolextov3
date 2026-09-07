@extends('layouts.app')

@section('title', 'About System')
@section('heading', 'About System')
@section('subheading', 'How registration numbers are created in the registry')

@section('content')
    <div class="mx-auto max-w-5xl space-y-6">
        <section class="card overflow-hidden">
            <div class="border-b border-sand bg-forest-dark px-5 py-6 text-white sm:px-8">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-tan">Registration identity</p>
                <h2 class="mt-2 font-display text-2xl font-semibold sm:text-3xl">One unique number per registry entry</h2>
                <p class="mt-2 max-w-2xl text-sm text-sand/85 sm:text-base">
                    Every registration receives a structured ID at submission. The final sequence is counted separately for each Rubber Development Officer division, so numbering never overlaps between divisions.
                </p>
            </div>

            <div class="px-5 py-8 sm:px-8">
                <p class="text-center text-xs font-semibold uppercase tracking-[0.18em] text-muted">Live format example</p>
                <div class="mt-4 flex flex-wrap items-center justify-center gap-2 sm:gap-3">
                    @foreach ([
                        ['label' => 'Scheme', 'value' => 'RUB'],
                        ['label' => 'Programme', 'value' => 'SUS'],
                        ['label' => 'District', 'value' => $districtCode],
                        ['label' => 'RDO division', 'value' => $divisionCode],
                        ['label' => 'Sequence', 'value' => '00001'],
                    ] as $index => $part)
                        @if ($index > 0)
                            <span class="text-2xl font-light text-tan sm:text-3xl">/</span>
                        @endif
                        <div class="min-w-[4.5rem] rounded-2xl border border-sand bg-cream px-3 py-3 text-center shadow-sm sm:min-w-[5.5rem] sm:px-4">
                            <p class="font-mono text-lg font-bold tracking-wide text-forest-dark sm:text-xl">{{ $part['value'] }}</p>
                            <p class="mt-1 text-[10px] font-semibold uppercase tracking-wide text-muted">{{ $part['label'] }}</p>
                        </div>
                    @endforeach
                </div>
                <p class="mt-5 text-center font-mono text-sm font-semibold text-forest sm:text-base">{{ $exampleNumber }}</p>
            </div>
        </section>

        <section class="grid gap-5 lg:grid-cols-2">
            <article class="card p-5 sm:p-6">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-leaf/15 text-sm font-bold text-forest">01</div>
                <h3 class="mt-4 text-lg font-semibold text-forest-dark">District code</h3>
                <p class="mt-2 text-sm leading-6 text-muted">
                    Taken from the selected district master record. Codes are short 4-character uppercase values derived from the district name (example: Kegalle → <span class="font-mono font-semibold text-ink">{{ $districtCode }}</span>).
                </p>
                @if ($exampleDistrict)
                    <p class="mt-4 rounded-xl bg-cream px-3 py-2 text-sm text-bark">
                        Current example: <strong>{{ $exampleDistrict->name }}</strong>
                    </p>
                @endif
            </article>

            <article class="card p-5 sm:p-6">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-leaf/15 text-sm font-bold text-forest">02</div>
                <h3 class="mt-4 text-lg font-semibold text-forest-dark">RDO division code</h3>
                <p class="mt-2 text-sm leading-6 text-muted">
                    Taken from the selected Rubber Development Officer division under that district. Also a short 4-character uppercase code from the division name (example: Amithirigala → <span class="font-mono font-semibold text-ink">{{ $divisionCode }}</span>).
                </p>
                @if ($exampleDivision)
                    <p class="mt-4 rounded-xl bg-cream px-3 py-2 text-sm text-bark">
                        Current example: <strong>{{ $exampleDivision->name }}</strong>
                    </p>
                @endif
            </article>

            <article class="card p-5 sm:p-6">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-leaf/15 text-sm font-bold text-forest">03</div>
                <h3 class="mt-4 text-lg font-semibold text-forest-dark">Per-division sequence</h3>
                <p class="mt-2 text-sm leading-6 text-muted">
                    The last segment is a {{ $sequencePad }}-digit counter. The first planter in a division gets <span class="font-mono font-semibold text-ink">00001</span>, then <span class="font-mono font-semibold text-ink">00002</span>, and so on.
                </p>
                <p class="mt-4 text-sm leading-6 text-muted">
                    Each division starts again from <span class="font-mono font-semibold text-ink">00001</span>. Two planters in different divisions can both be <span class="font-mono">…/00001</span> because the district and division codes keep the full ID unique.
                </p>
            </article>

            <article class="card p-5 sm:p-6">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-leaf/15 text-sm font-bold text-forest">04</div>
                <h3 class="mt-4 text-lg font-semibold text-forest-dark">When it is issued</h3>
                <p class="mt-2 text-sm leading-6 text-muted">
                    The permanent registration number is created only when staff <strong class="text-ink">approve</strong> or <strong class="text-ink">reject</strong> an application — not when the planter first submits. Until then, the record keeps a temporary submission reference only.
                </p>
                <div class="mt-4 grid grid-cols-3 gap-2 text-center">
                    <div class="rounded-xl bg-cream px-2 py-3">
                        <p class="text-lg font-bold text-forest-dark">{{ $districtCount }}</p>
                        <p class="text-[11px] text-muted">Districts</p>
                    </div>
                    <div class="rounded-xl bg-cream px-2 py-3">
                        <p class="text-lg font-bold text-forest-dark">{{ $divisionCount }}</p>
                        <p class="text-[11px] text-muted">RDO divisions</p>
                    </div>
                    <div class="rounded-xl bg-cream px-2 py-3">
                        <p class="text-lg font-bold text-forest-dark">{{ $planterCount }}</p>
                        <p class="text-[11px] text-muted">Registry</p>
                    </div>
                </div>
            </article>
        </section>

        <section class="card p-5 sm:p-8">
            <h3 class="text-lg font-semibold text-forest-dark">Worked example</h3>
            <ol class="mt-4 space-y-3 text-sm leading-6 text-muted">
                <li class="flex gap-3">
                    <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-forest text-[11px] font-bold text-white">1</span>
                    <span>Planter selects district <strong class="text-ink">{{ $exampleDistrict?->name ?: 'Kegalle' }}</strong> → code <span class="font-mono font-semibold text-forest">{{ $districtCode }}</span></span>
                </li>
                <li class="flex gap-3">
                    <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-forest text-[11px] font-bold text-white">2</span>
                    <span>Planter selects RDO division <strong class="text-ink">{{ $exampleDivision?->name ?: 'Amithirigala' }}</strong> → code <span class="font-mono font-semibold text-forest">{{ $divisionCode }}</span></span>
                </li>
                <li class="flex gap-3">
                    <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-forest text-[11px] font-bold text-white">3</span>
                    <span>Staff approve or reject the application → system assigns the next free sequence for that division only → <span class="font-mono font-semibold text-forest">00001</span></span>
                </li>
                <li class="flex gap-3">
                    <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-forest text-[11px] font-bold text-white">4</span>
                    <span>Final registration number → <span class="font-mono font-semibold text-forest">{{ $exampleNumber }}</span></span>
                </li>
            </ol>
        </section>
    </div>
@endsection
