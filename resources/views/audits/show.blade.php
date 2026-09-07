@extends('layouts.app')

@section('title', $audit->roundLabel())
@section('heading', $audit->roundLabel())
@section('subheading', ($audit->planter->identification_number ?: $audit->planter->temporary_id).' · '.$audit->planter->name)

@section('actions')
    @php
        $backRoute = match (true) {
            $audit->status === \App\Models\PlanterAudit::STATUS_FAILED => 'admin.audits.rejected',
            in_array($audit->status, [\App\Models\PlanterAudit::STATUS_PASSED, \App\Models\PlanterAudit::STATUS_CONDITIONAL], true) => 'admin.audits.passed',
            default => 'admin.audits.ongoing',
        };
    @endphp
    <a href="{{ route($backRoute, ['round' => $audit->round]) }}" class="btn-secondary">Back to list</a>
    <a href="{{ route('admin.planters.show', $audit->planter) }}" class="btn-secondary">Registry details</a>
@endsection

@section('content')
    <div class="mx-auto max-w-5xl space-y-6">
        @error('audit')
            <p class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ $message }}</p>
        @enderror

        @if (! $audit->is_current)
            <p class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                This is archived attempt #{{ $audit->attempt_number }}. Results are kept for history and cannot be edited.
                @if ($currentAttempt)
                    <a href="{{ route('admin.audits.show', $currentAttempt) }}" class="font-semibold underline">Open current attempt #{{ $currentAttempt->attempt_number }}</a>
                @endif
            </p>
        @endif

        <section class="card p-5 sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-muted">Status</p>
                    <p class="mt-1 text-xl font-semibold text-forest-dark">{{ $audit->statusLabel() }}</p>
                    <p class="mt-2 text-sm text-muted">
                        Attempt #{{ $audit->attempt_number }}
                        @if ($audit->is_current)
                            · current
                        @else
                            · archived
                        @endif
                        · Checklist progress: {{ $progress['answered'] }}/{{ $progress['total'] }} ({{ $progress['percent'] }}%)
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    @if ($canWork && $audit->status === \App\Models\PlanterAudit::STATUS_QUEUED)
                        <form method="POST" action="{{ route('admin.audits.start', $audit) }}">
                            @csrf
                            <button type="submit" class="btn-primary">Start audit</button>
                        </form>
                    @endif
                    @if (($canWork || $canDispatch) && $audit->isComplete() && $audit->is_current)
                        <form method="POST" action="{{ route('admin.audits.reopen', $audit) }}" onsubmit="return confirm('Archive this result and start a new attempt? Previous checklist answers stay in history.')">
                            @csrf
                            <button type="submit" class="btn-secondary">Start new attempt</button>
                        </form>
                    @endif
                </div>
            </div>
            @if ($audit->outcome_notes)
                <p class="mt-4 rounded-xl bg-cream px-4 py-3 text-sm text-ink">{{ $audit->outcome_notes }}</p>
            @endif
        </section>

        @if ($attemptHistory->count() > 1)
            <section class="card overflow-hidden">
                <div class="border-b border-sand bg-cream px-5 py-4">
                    <h2 class="font-semibold text-forest-dark">Attempt history</h2>
                    <p class="mt-1 text-sm text-muted">Failed and completed attempts are kept for this round.</p>
                </div>
                <div class="divide-y divide-sand">
                    @foreach ($attemptHistory as $attempt)
                        <div class="flex flex-col gap-2 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="font-medium text-ink">
                                    Attempt #{{ $attempt->attempt_number }}
                                    @if ($attempt->is_current)
                                        <span class="ml-2 rounded-full bg-leaf/15 px-2 py-0.5 text-xs font-semibold text-forest">Current</span>
                                    @endif
                                </p>
                                <p class="mt-1 text-sm text-muted">
                                    {{ $attempt->statusLabel() }}
                                    @if ($attempt->completed_at)
                                        · {{ $attempt->completed_at->format('Y-m-d H:i') }}
                                    @endif
                                    @if ($attempt->completer)
                                        · {{ $attempt->completer->name }}
                                    @endif
                                </p>
                                @if ($attempt->outcome_notes)
                                    <p class="mt-1 text-sm text-ink">{{ $attempt->outcome_notes }}</p>
                                @endif
                            </div>
                            <a href="{{ route('admin.audits.show', $attempt) }}" class="text-sm font-semibold text-leaf hover:text-forest">
                                {{ $attempt->id === $audit->id ? 'Viewing' : 'View checklist' }}
                            </a>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($canWork && $audit->isOpen())
            <form method="POST" action="{{ route('admin.audits.checklist.save', $audit) }}" class="space-y-6">
                @csrf

                @foreach ($groupedAnswers as $module => $answers)
                    <section class="card overflow-hidden">
                        <div class="border-b border-sand bg-cream px-5 py-4">
                            <h2 class="font-semibold text-forest-dark">
                                {{ \App\Models\AuditChecklistItem::modules()[$module] ?? strtoupper($module) }}
                            </h2>
                        </div>
                        <div class="divide-y divide-sand">
                            @foreach ($answers as $answer)
                                <div class="space-y-3 px-5 py-4">
                                    <div>
                                        <p class="font-medium text-ink">{{ $answer->item?->label }}</p>
                                        @if ($answer->item?->description)
                                            <p class="mt-1 text-sm text-muted">{{ $answer->item->description }}</p>
                                        @endif
                                        <p class="mt-1 font-mono text-[11px] text-muted">{{ $answer->item?->code }}</p>
                                    </div>
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <div>
                                            <label class="label-field" for="result_{{ $answer->id }}">Result</label>
                                            <select id="result_{{ $answer->id }}" name="answers[{{ $answer->audit_checklist_item_id }}][result]" class="input-field" required>
                                                @foreach (\App\Models\PlanterAuditChecklistAnswer::results() as $value => $label)
                                                    <option value="{{ $value }}" @selected(old('answers.'.$answer->audit_checklist_item_id.'.result', $answer->result) === $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="label-field" for="comment_{{ $answer->id }}">Comment</label>
                                            <input id="comment_{{ $answer->id }}" type="text" name="answers[{{ $answer->audit_checklist_item_id }}][comment]" value="{{ old('answers.'.$answer->audit_checklist_item_id.'.comment', $answer->comment) }}" class="input-field" placeholder="Optional note">
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endforeach

                <div class="flex flex-wrap gap-2">
                    <button type="submit" class="btn-secondary">Save checklist</button>
                </div>
            </form>

            <div class="flex flex-wrap gap-2">
                <form method="POST" action="{{ route('admin.audits.submit', $audit) }}">
                    @csrf
                    <button type="submit" class="btn-secondary">Submit for review</button>
                </form>
            </div>

            <form method="POST" action="{{ route('admin.audits.complete', $audit) }}" class="card space-y-4 p-5 sm:p-6">
                @csrf
                <h2 class="font-semibold text-forest-dark">Complete {{ strtolower($audit->roundLabel()) }}</h2>
                <div>
                    <label for="status" class="label-field">Outcome</label>
                    <select id="status" name="status" required class="input-field">
                        <option value="">Select outcome</option>
                        <option value="{{ \App\Models\PlanterAudit::STATUS_PASSED }}">Passed</option>
                        <option value="{{ \App\Models\PlanterAudit::STATUS_CONDITIONAL }}">Conditional</option>
                        <option value="{{ \App\Models\PlanterAudit::STATUS_FAILED }}">Failed</option>
                    </select>
                    @error('status')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="outcome_notes" class="label-field">Outcome notes</label>
                    <textarea id="outcome_notes" name="outcome_notes" rows="3" class="input-field">{{ old('outcome_notes') }}</textarea>
                </div>
                <button type="submit" class="btn-primary">Complete audit</button>
            </form>
        @else
            @foreach ($groupedAnswers as $module => $answers)
                <section class="card overflow-hidden">
                    <div class="border-b border-sand bg-cream px-5 py-4">
                        <h2 class="font-semibold text-forest-dark">
                            {{ \App\Models\AuditChecklistItem::modules()[$module] ?? strtoupper($module) }}
                        </h2>
                    </div>
                    <div class="divide-y divide-sand">
                        @foreach ($answers as $answer)
                            <div class="px-5 py-4">
                                <p class="font-medium text-ink">{{ $answer->item?->label }}</p>
                                <p class="mt-2 text-sm">
                                    <span class="rounded-full bg-sand px-2 py-0.5 text-xs font-semibold">
                                        {{ \App\Models\PlanterAuditChecklistAnswer::results()[$answer->result] ?? $answer->result }}
                                    </span>
                                    @if ($answer->comment)
                                        <span class="ml-2 text-muted">{{ $answer->comment }}</span>
                                    @endif
                                </p>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endforeach
        @endif
    </div>
@endsection
