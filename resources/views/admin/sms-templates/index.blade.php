@extends('layouts.app')

@section('title', 'SMS templates')
@section('heading', 'SMS templates')
@section('subheading', 'Messages sent to planters through Notify.lk')

@section('content')
    <div class="mx-auto max-w-5xl space-y-6">
        @if (session('success'))
            <div class="rounded-2xl border border-leaf/30 bg-leaf/10 px-4 py-3 text-sm text-forest-dark">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                {{ $errors->first() }}
            </div>
        @endif

        <section class="card overflow-hidden">
            <div class="border-b border-sand bg-forest-dark px-5 py-6 text-white sm:px-8">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-tan">Notify.lk</p>
                <h2 class="mt-2 font-display text-2xl font-semibold sm:text-3xl">Outbound SMS catalogue</h2>
                <p class="mt-2 max-w-2xl text-sm text-sand/85 sm:text-base">
                    Active templates are sent by the system today. Planned templates are listed for wording review and can be enabled later.
                </p>
                <div class="mt-4 flex flex-wrap gap-3 text-xs">
                    <span class="rounded-full bg-white/10 px-3 py-1">
                        Gateway: <strong>{{ $gatewayEnabled ? 'Enabled' : 'Disabled' }}</strong>
                    </span>
                    <span class="rounded-full bg-white/10 px-3 py-1">
                        Sender ID: <strong>{{ $senderId ?: '—' }}</strong>
                    </span>
                    <span class="rounded-full bg-white/10 px-3 py-1">
                        Templates: <strong>{{ $templates->count() }}</strong>
                    </span>
                </div>
            </div>

            <div class="px-5 py-6 sm:px-8">
                <form method="POST" action="{{ route('admin.sms-templates.send-samples') }}" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                    @csrf
                    <div class="flex-1">
                        <label for="sample-phone" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-muted">Send all samples to</label>
                        <input
                            id="sample-phone"
                            type="text"
                            name="phone"
                            value="{{ old('phone', '+94714245192') }}"
                            class="w-full rounded-xl border border-sand bg-cream px-3 py-2.5 text-sm text-ink"
                            placeholder="+9471XXXXXXX"
                            required
                        >
                    </div>
                    <button type="submit" class="btn-primary shrink-0">Send all samples</button>
                </form>
                <p class="mt-2 text-xs text-muted">Each sample is prefixed with <span class="font-mono">[SAMPLE …]</span> so it is easy to recognise on the phone.</p>
            </div>
        </section>

        <div class="space-y-4">
            @foreach ($templates as $template)
                <article class="card overflow-hidden">
                    <div class="flex flex-col gap-3 border-b border-sand px-5 py-4 sm:flex-row sm:items-start sm:justify-between sm:px-6">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-lg font-semibold text-forest-dark">{{ $template['name'] }}</h3>
                                @if ($template['status'] === 'active')
                                    <span class="rounded-full bg-leaf/15 px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-forest">Active</span>
                                @else
                                    <span class="rounded-full bg-sand/60 px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-muted">Planned</span>
                                @endif
                            </div>
                            <p class="mt-1 text-sm text-muted">{{ $template['trigger'] }}</p>
                            <p class="mt-1 font-mono text-[11px] text-bark/70">{{ $template['key'] }} · {{ $template['length'] }} chars (template)</p>
                        </div>
                        <form method="POST" action="{{ route('admin.sms-templates.send-samples') }}">
                            @csrf
                            <input type="hidden" name="phone" value="{{ old('phone', '+94714245192') }}">
                            <input type="hidden" name="template" value="{{ $template['key'] }}">
                            <button type="submit" class="btn-secondary text-sm">Send this sample</button>
                        </form>
                    </div>
                    <div class="grid gap-4 px-5 py-5 sm:px-6 lg:grid-cols-2">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-muted">Template body</p>
                            <pre class="mt-2 whitespace-pre-wrap rounded-2xl border border-sand bg-cream p-4 font-sans text-sm leading-6 text-ink">{{ $template['body'] }}</pre>
                            @if (! empty($template['placeholders']))
                                <p class="mt-2 text-xs text-muted">
                                    Placeholders:
                                    @foreach ($template['placeholders'] as $placeholder)
                                        <span class="font-mono text-bark">{{ '{'.$placeholder.'}' }}</span>@if (! $loop->last), @endif
                                    @endforeach
                                </p>
                            @endif
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-muted">Sample preview</p>
                            <pre class="mt-2 whitespace-pre-wrap rounded-2xl border border-leaf/20 bg-leaf/5 p-4 font-sans text-sm leading-6 text-forest-dark">{{ $template['sample'] }}</pre>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
@endsection
