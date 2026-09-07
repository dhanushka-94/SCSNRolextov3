@props(['planter'])

@php
    $user = auth('web')->user();
    $canDispatch = $user?->canDispatchAudits() === true;
@endphp

<section class="space-y-5 rounded-2xl border border-sand bg-cream/60 p-5 sm:p-6">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h3 class="text-sm font-semibold text-forest-dark">Audit workflow</h3>
            <p class="mt-1 text-sm text-muted">First Audit and Final Audit are handled by separate auditor teams with module checklists.</p>
        </div>
        <a href="{{ route('admin.audits.ongoing') }}" class="text-sm font-semibold text-leaf hover:text-forest">Open Ongoing audits</a>
    </div>

    @error('audit')
        <p class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800">{{ $message }}</p>
    @enderror

    <x-planter-audit-tree :planter="$planter" class="!shadow-none" />

    <div class="grid gap-4 sm:grid-cols-2">
        <article class="rounded-xl border border-sand bg-paper p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-muted">First Audit</p>
            @if ($planter->firstAudit)
                <p class="mt-2 font-semibold text-forest-dark">{{ $planter->firstAudit->statusLabel() }}</p>
                <p class="mt-1 text-xs text-muted">Attempt #{{ $planter->firstAudit->attempt_number }}</p>
                <a href="{{ route('admin.audits.show', $planter->firstAudit) }}" class="mt-3 inline-flex text-sm font-semibold text-leaf hover:text-forest">Open checklist</a>
            @else
                <p class="mt-2 text-sm text-muted">Not sent yet.</p>
                @if ($canDispatch && $planter->canSendToFirstAudit())
                    <form method="POST" action="{{ route('admin.planters.audits.send-first', $planter) }}" class="mt-3">
                        @csrf
                        <button type="submit" class="btn-primary">Send to First Audit</button>
                    </form>
                @endif
            @endif
        </article>

        <article class="rounded-xl border border-sand bg-paper p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-muted">Final Audit</p>
            @if ($planter->finalAudit)
                <p class="mt-2 font-semibold text-forest-dark">{{ $planter->finalAudit->statusLabel() }}</p>
                <p class="mt-1 text-xs text-muted">Attempt #{{ $planter->finalAudit->attempt_number }}</p>
                <a href="{{ route('admin.audits.show', $planter->finalAudit) }}" class="mt-3 inline-flex text-sm font-semibold text-leaf hover:text-forest">Open checklist</a>
            @else
                <p class="mt-2 text-sm text-muted">
                    @if ($planter->firstAudit?->canProceedToFinal())
                        Ready to send after First Audit pass/conditional.
                    @elseif ($planter->firstAudit?->status === \App\Models\PlanterAudit::STATUS_FAILED)
                        Blocked — First Audit failed.
                    @else
                        Waiting for First Audit to pass.
                    @endif
                </p>
                @if ($canDispatch && $planter->canSendToFinalAudit())
                    <form method="POST" action="{{ route('admin.planters.audits.send-final', $planter) }}" class="mt-3">
                        @csrf
                        <button type="submit" class="btn-primary">Send to Final Audit</button>
                    </form>
                @endif
            @endif
        </article>
    </div>
</section>
