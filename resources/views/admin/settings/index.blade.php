@extends('layouts.app')

@section('title', 'Settings')
@section('heading', 'Settings')
@section('subheading', 'System-wide options · Super Admin (Developer) only')

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">
        @if (session('error'))
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ $errors->first() }}</div>
        @endif
        <section class="card overflow-hidden">
            <div class="flex items-start justify-between gap-4 border-b border-sand bg-cream px-5 py-4 sm:px-6">
                <div>
                    <h2 class="font-semibold text-forest-dark">Maintenance mode</h2>
                    <p class="mt-1 text-sm text-muted">
                        When on, planters, officers and auditors see a maintenance page. Only administrators can sign in and use the system.
                    </p>
                </div>
                <span class="shrink-0 rounded-full px-3 py-1 text-xs font-semibold {{ $maintenanceOn ? 'bg-amber-100 text-amber-900' : 'bg-leaf/15 text-forest' }}">
                    {{ $maintenanceOn ? 'ON' : 'OFF' }}
                </span>
            </div>

            <form method="POST" action="{{ route('admin.settings.maintenance') }}" class="space-y-5 p-5 sm:p-6"
                  onsubmit="return !document.getElementById('maintenance_toggle').checked || {{ $maintenanceOn ? 'true' : 'false' }} || confirm('Turn maintenance mode ON? Everyone except administrators will be locked out.');">
                @csrf
                @method('PUT')

                <input type="hidden" name="maintenance_mode" id="maintenance_mode" value="{{ $maintenanceOn ? '1' : '0' }}">

                <label for="maintenance_toggle" class="flex cursor-pointer items-center justify-between gap-4 rounded-xl border border-sand bg-paper px-4 py-3">
                    <span>
                        <span class="block text-sm font-semibold text-ink">System under maintenance</span>
                        <span class="block text-xs text-muted" id="maintenance_hint">{{ $maintenanceOn ? 'Switch off to reopen the system.' : 'Switch on to close the system for non-administrators.' }}</span>
                    </span>
                    <span class="relative inline-flex h-7 w-12 shrink-0 items-center">
                        <input id="maintenance_toggle" type="checkbox" class="peer sr-only" @checked($maintenanceOn)
                               onchange="document.getElementById('maintenance_mode').value = this.checked ? '1' : '0'">
                        <span class="h-7 w-12 rounded-full bg-line transition peer-checked:bg-amber-500 peer-focus-visible:ring-2 peer-focus-visible:ring-leaf"></span>
                        <span class="pointer-events-none absolute left-1 h-5 w-5 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
                    </span>
                </label>

                <div>
                    <label for="maintenance_message" class="label-field">Message shown to visitors <span class="font-normal text-muted">(optional)</span></label>
                    <textarea id="maintenance_message" name="maintenance_message" rows="3" maxlength="500" class="input-field" placeholder="e.g. We are upgrading the system and will be back at 6 PM.">{{ old('maintenance_message', $maintenanceMessage) }}</textarea>
                    @error('maintenance_message')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3">
                    <p class="text-xs text-muted">
                        @if ($maintenanceChangedAt)
                            Last changed {{ sl_datetime($maintenanceChangedAt) }}@if ($maintenanceChangedBy) by {{ $maintenanceChangedBy }}@endif.
                        @else
                            Never changed.
                        @endif
                    </p>
                    <button type="submit" class="btn-primary">Save settings</button>
                </div>
            </form>
        </section>

        <section class="card overflow-hidden" aria-labelledby="sms-templates-heading">
            <div class="flex flex-wrap items-start justify-between gap-4 border-b border-sand bg-cream px-5 py-4 sm:px-6">
                <div class="min-w-0">
                    <h2 id="sms-templates-heading" class="font-semibold text-forest-dark">SMS message templates</h2>
                    <p class="mt-1 text-sm text-muted">
                        Every message the system can send, in English, Sinhala and Tamil. Placeholders such as
                        <code class="break-words">{reference}</code> are filled in automatically.
                    </p>
                    <p class="mt-2 text-xs text-muted">
                        Gateway:
                        <span class="font-semibold {{ $smsGatewayEnabled ? 'text-forest' : 'text-amber-800' }}">{{ $smsGatewayEnabled ? 'on' : 'off' }}</span>
                        · Sender ID: <span class="font-mono">{{ $smsSenderId }}</span>
                    </p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.settings.sms-samples') }}">
                @csrf
                <div class="flex flex-col gap-3 border-b border-sand px-5 py-4 sm:flex-row sm:items-end sm:px-6">
                    <div class="min-w-0 flex-1">
                        <label for="sample-phone" class="label-field">Send a test to</label>
                        <input id="sample-phone" type="text" name="phone" value="{{ old('phone') }}" class="input-field" placeholder="+9471XXXXXXX" required>
                    </div>
                    <div class="sm:w-44">
                        <label for="sample-language" class="label-field">Language</label>
                        <select id="sample-language" name="language" class="input-field">
                            <option value="all" @selected(old('language', 'all') === 'all')>All three</option>
                            <option value="en" @selected(old('language') === 'en')>English</option>
                            <option value="si" @selected(old('language') === 'si')>Sinhala</option>
                            <option value="ta" @selected(old('language') === 'ta')>Tamil</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-primary shrink-0">Send all samples</button>
                </div>
                <p class="border-b border-sand px-5 py-2 text-xs text-muted sm:px-6">The language above applies to every send button. Each test starts with <span class="font-mono">[SAMPLE …]</span>.</p>

            <ul class="divide-y divide-sand">
                @foreach ($smsTemplates as $template)
                    <li class="space-y-3 px-5 py-5 sm:px-6" id="sms-{{ $template['key'] }}">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="font-semibold text-ink">{{ $template['name']['en'] }}</h3>
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $template['status'] === 'active' ? 'bg-leaf/15 text-forest' : 'bg-sand text-bark' }}">
                                    {{ $template['status'] === 'active' ? 'Active' : 'Planned' }}
                                </span>
                            </div>
                            <button type="submit" name="template" value="{{ $template['key'] }}" class="btn-secondary shrink-0 text-sm">Send this sample</button>
                        </div>
                        <p class="text-xs text-muted">
                            {{ $template['trigger'] }}
                            @if (! empty($template['placeholders']))
                                · Placeholders: <span class="font-mono break-words">{{ implode(', ', array_map(fn ($p) => '{'.$p.'}', $template['placeholders'])) }}</span>
                            @endif
                        </p>

                        <dl class="grid gap-2">
                            @foreach ([['en', 'English'], ['si', 'Sinhala · සිංහල'], ['ta', 'Tamil · தமிழ்']] as [$lang, $langLabel])
                                <div class="rounded-xl border border-sand bg-paper px-4 py-3">
                                    <dt class="flex flex-wrap items-center justify-between gap-2 text-xs font-semibold text-forest-dark">
                                        <span>{{ $langLabel }} <span class="font-normal text-muted">· {{ $template['name'][$lang] }}</span></span>
                                        <span class="font-normal text-muted">{{ $template['bodies'][$lang]['length'] }} / 320</span>
                                    </dt>
                                    <dd class="mt-1 break-words text-sm text-bark" lang="{{ $lang }}">{{ $template['bodies'][$lang]['text'] }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </li>
                @endforeach
            </ul>
            </form>
        </section>

        <section class="card overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-4 px-5 py-4 sm:px-6">
                <div>
                    <h2 class="font-semibold text-forest-dark">API documentation</h2>
                    <p class="mt-1 text-sm text-muted">
                        Every endpoint of the mobile app API: authentication, roles, requests, example responses, errors and offline sync.
                    </p>
                </div>
                <a href="{{ route('admin.settings.api-docs') }}" class="btn-primary shrink-0">Open documentation</a>
            </div>
        </section>
    </div>
@endsection
