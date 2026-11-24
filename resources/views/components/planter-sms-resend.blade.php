@props([
    'planter',
    'actions' => [],
    'enabled' => false,
])

@php
    $user = auth('web')->user();
    $canResend = $user?->canResendPlanterSms() === true;
@endphp

@if ($canResend && count($actions) > 0)
    <section {{ $attributes->merge(['class' => 'rounded-2xl border border-sand bg-cream/60 p-5']) }}>
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h3 class="text-sm font-semibold text-forest-dark">Resend step SMS</h3>
                <p class="mt-1 text-sm text-muted">
                    Re-send Notify.lk messages for steps this planter has already reached.
                    @if (! $enabled)
                        <span class="font-semibold text-red-800">Gateway disabled — enable NOTIFY_* in .env.</span>
                    @endif
                </p>
                <p class="mt-1 font-mono text-xs text-bark">{{ $planter->phone ?: 'No phone on file' }}</p>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap gap-2">
            @foreach ($actions as $action)
                <form method="POST" action="{{ route('admin.planters.sms.resend', $planter) }}">
                    @csrf
                    <input type="hidden" name="template" value="{{ $action['key'] }}">
                    <button type="submit" class="btn-secondary text-sm" @disabled(! $enabled || blank($planter->phone))>
                        {{ $action['label'] }}
                    </button>
                </form>
            @endforeach
        </div>
    </section>
@endif
