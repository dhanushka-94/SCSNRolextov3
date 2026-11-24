@extends('layouts.app')

@section('title', 'API documentation')
@section('heading', 'API documentation')
@section('subheading', 'Mobile app API · Super Admin (Developer) only')

@section('actions')
    <a href="{{ route('admin.settings.index') }}" class="btn-secondary">Back to settings</a>
@endsection

@php
    $methodClasses = [
        'GET' => 'bg-leaf/15 text-forest',
        'POST' => 'bg-sky-100 text-sky-800',
        'PUT' => 'bg-amber-100 text-amber-900',
        'PUT|PATCH' => 'bg-amber-100 text-amber-900',
        'PATCH' => 'bg-amber-100 text-amber-900',
        'DELETE' => 'bg-red-100 text-red-800',
    ];
    $accessLabels = [
        'public' => 'Public',
        'any' => 'Any signed-in account',
        'planter' => 'Planter token',
        'staff' => 'Staff token',
        'admin' => 'Administrator / Developer',
        'developer' => 'Developer only',
    ];
    $accessClasses = [
        'public' => 'bg-sand text-bark',
        'any' => 'bg-sand text-bark',
        'planter' => 'bg-emerald-100 text-emerald-900',
        'staff' => 'bg-indigo-100 text-indigo-900',
        'admin' => 'bg-purple-100 text-purple-900',
        'developer' => 'bg-rose-100 text-rose-900',
    ];
@endphp

@section('content')
    <div class="mx-auto flex max-w-7xl gap-8">
        {{-- Table of contents --}}
        <aside class="hidden w-56 shrink-0 xl:block">
            <nav class="sticky top-28 max-h-[calc(100vh-8rem)] space-y-1 overflow-y-auto pr-2 text-sm">
                <p class="pb-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-muted">Guide</p>
                <a href="#overview" class="block rounded-lg px-2 py-1 text-bark hover:bg-sand">Overview</a>
                <a href="#authentication-guide" class="block rounded-lg px-2 py-1 text-bark hover:bg-sand">Using tokens</a>
                <a href="#roles" class="block rounded-lg px-2 py-1 text-bark hover:bg-sand">Roles &amp; permissions</a>
                <a href="#values" class="block rounded-lg px-2 py-1 text-bark hover:bg-sand">Allowed values</a>
                <a href="#objects" class="block rounded-lg px-2 py-1 text-bark hover:bg-sand">Response objects</a>
                <p class="pb-1 pt-4 text-[11px] font-semibold uppercase tracking-[0.16em] text-muted">Endpoints</p>
                @foreach ($sections as $section)
                    <a href="#{{ $section['id'] }}" class="block rounded-lg px-2 py-1 text-bark hover:bg-sand">
                        {{ $section['title'] }}
                        <span class="text-xs text-muted">({{ count($section['endpoints']) }})</span>
                    </a>
                @endforeach
            </nav>
        </aside>

        <div class="min-w-0 flex-1 space-y-8">
            {{-- Overview --}}
            <section id="overview" class="card scroll-mt-28 space-y-5 p-5 sm:p-6">
                <div>
                    <h2 class="text-lg font-semibold text-forest-dark">Overview</h2>
                    <p class="mt-1 text-sm text-muted">
                        REST API (JSON) for the SPPNR mobile app. Planters and every staff role use the same API;
                        what each account can do depends on its token type and role.
                    </p>
                </div>

                <div>
                    <p class="label-field">Base URL</p>
                    <div class="flex items-center gap-2">
                        <code class="flex-1 overflow-x-auto rounded-xl bg-forest-dark px-4 py-2.5 text-sm text-sand">{{ $overview['base_url'] }}</code>
                        <button type="button" data-copy="{{ $overview['base_url'] }}" class="btn-secondary px-3 py-2 text-xs">Copy</button>
                    </div>
                    <p class="mt-2 text-xs text-muted">All paths below are relative to this URL, for example <code>auth/staff/login</code>.</p>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-forest-dark">Request headers</h3>
                    <div class="mt-2 overflow-x-auto rounded-xl border border-sand">
                        <table class="min-w-full text-sm">
                            <tbody class="divide-y divide-sand">
                                @foreach ($overview['headers'] as [$header, $value, $note])
                                    <tr>
                                        <td class="whitespace-nowrap px-4 py-2 font-mono text-xs font-semibold text-forest-dark">{{ $header }}</td>
                                        <td class="px-4 py-2 font-mono text-xs text-bark">{{ $value }}</td>
                                        <td class="px-4 py-2 text-muted">{{ $note }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 break-words md:grid-cols-2">
                    <div class="rounded-xl border border-sand bg-cream p-4">
                        <h3 class="text-sm font-semibold text-forest-dark">Date and time</h3>
                        <p class="mt-1 text-sm text-bark">{{ $overview['dates'] }}</p>
                    </div>
                    <div class="rounded-xl border border-sand bg-cream p-4">
                        <h3 class="text-sm font-semibold text-forest-dark">Pagination</h3>
                        <p class="mt-1 text-sm text-bark">{{ $overview['pagination'] }}</p>
                    </div>
                    <div class="rounded-xl border border-sand bg-cream p-4">
                        <h3 class="text-sm font-semibold text-forest-dark">Rate limits</h3>
                        <p class="mt-1 text-sm text-bark">{{ $overview['rate_limits'] }}</p>
                    </div>
                    <div class="rounded-xl border border-sand bg-cream p-4">
                        <h3 class="text-sm font-semibold text-forest-dark">Maintenance mode</h3>
                        <p class="mt-1 text-sm text-bark">{{ $overview['maintenance'] }}</p>
                    </div>
                    <div class="rounded-xl border border-sand bg-cream p-4 md:col-span-2">
                        <h3 class="text-sm font-semibold text-forest-dark">Working offline</h3>
                        <p class="mt-1 text-sm text-bark">{{ $overview['offline'] }}</p>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-forest-dark">Status codes and errors</h3>
                    <div class="mt-2 overflow-x-auto rounded-xl border border-sand">
                        <table class="min-w-full text-sm">
                            <tbody class="divide-y divide-sand">
                                @foreach ($overview['errors'] as [$code, $name, $text])
                                    <tr>
                                        <td class="whitespace-nowrap px-4 py-2 font-mono text-xs font-semibold text-forest-dark">{{ $code }}</td>
                                        <td class="whitespace-nowrap px-4 py-2 font-semibold text-bark">{{ $name }}</td>
                                        <td class="px-4 py-2 text-muted">{{ $text }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section id="authentication-guide" class="card scroll-mt-28 p-5 sm:p-6">
                <h2 class="text-lg font-semibold text-forest-dark">Using tokens</h2>
                <ol class="mt-3 list-decimal space-y-2 pl-5 text-sm text-bark">
                    @foreach ($overview['auth_steps'] as $step)
                        <li>{{ $step }}</li>
                    @endforeach
                </ol>
            </section>

            {{-- Roles --}}
            <section id="roles" class="card scroll-mt-28 p-5 sm:p-6">
                <h2 class="text-lg font-semibold text-forest-dark">Roles and permissions</h2>
                <p class="mt-1 text-sm text-muted">
                    Generated from the live permission rules. The same flags are returned in <code>user.permissions</code> at login and by <code>auth/me</code>.
                    The Super Admin (Developer) holds every administrator permission plus Settings and this documentation.
                </p>
                <div class="mt-3 overflow-x-auto rounded-xl border border-sand">
                    <table class="min-w-full text-xs">
                        <thead class="bg-cream text-left text-muted">
                            <tr>
                                <th class="sticky left-0 bg-cream px-3 py-2 font-semibold">Permission</th>
                                @foreach ($roleMatrix['rows'] as $row)
                                    <th class="px-3 py-2 text-center font-semibold">{{ $row['label'] }}<br><span class="font-mono font-normal">{{ $row['role'] }}</span></th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-sand">
                            @foreach ($roleMatrix['columns'] as $permission)
                                <tr>
                                    <td class="sticky left-0 bg-paper px-3 py-1.5 font-mono text-bark">{{ $permission }}</td>
                                    @foreach ($roleMatrix['rows'] as $row)
                                        <td class="px-3 py-1.5 text-center">
                                            @if ($row['permissions'][$permission])
                                                <span class="font-bold text-leaf">&#10003;</span>
                                            @else
                                                <span class="text-line">&ndash;</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            {{-- Allowed values --}}
            <section id="values" class="card scroll-mt-28 p-5 sm:p-6">
                <h2 class="text-lg font-semibold text-forest-dark">Allowed values</h2>
                <div class="mt-3 grid gap-4 md:grid-cols-2">
                    @foreach ($enums as $enum)
                        <div class="rounded-xl border border-sand p-4">
                            <h3 class="text-sm font-semibold text-forest-dark">{{ $enum['name'] }}</h3>
                            <ul class="mt-2 space-y-1 text-sm">
                                @foreach ($enum['values'] as $value => $label)
                                    <li class="flex gap-2">
                                        <code class="shrink-0 rounded bg-sand px-1.5 py-0.5 text-xs text-bark">{{ $value }}</code>
                                        <span class="text-muted">{{ $label }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Schemas --}}
            <section id="objects" class="card scroll-mt-28 p-5 sm:p-6">
                <h2 class="text-lg font-semibold text-forest-dark">Response objects</h2>
                <div class="mt-3 space-y-5">
                    @foreach ($schemas as $schema)
                        <div>
                            <h3 class="text-sm font-semibold text-forest-dark">{{ $schema['name'] }} <span class="font-normal text-muted">&mdash; {{ $schema['description'] }}</span></h3>
                            <div class="mt-2 overflow-x-auto rounded-xl border border-sand">
                                <table class="min-w-full text-sm">
                                    <tbody class="divide-y divide-sand">
                                        @foreach ($schema['fields'] as [$field, $type, $description])
                                            <tr>
                                                <td class="px-4 py-2 font-mono text-xs font-semibold text-forest-dark">{{ $field }}</td>
                                                <td class="whitespace-nowrap px-4 py-2 font-mono text-xs text-bark">{{ $type }}</td>
                                                <td class="px-4 py-2 text-muted">{{ $description }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Endpoints --}}
            <div class="card flex flex-wrap items-center gap-3 p-4">
                <input id="api-search" type="search" placeholder="Search endpoints (path, title, method)..." class="input-field max-w-md flex-1">
                <button type="button" id="api-expand" class="btn-secondary">Expand all</button>
                <button type="button" id="api-collapse" class="btn-secondary">Collapse all</button>
                <span id="api-count" class="text-xs text-muted"></span>
            </div>

            @foreach ($sections as $section)
                <section id="{{ $section['id'] }}" class="api-section scroll-mt-28 space-y-3">
                    <div>
                        <h2 class="text-lg font-semibold text-forest-dark">{{ $section['title'] }}</h2>
                        <p class="text-sm text-muted">{{ $section['description'] }}</p>
                    </div>

                    @foreach ($section['endpoints'] as $endpoint)
                        <details class="api-endpoint card overflow-hidden"
                                 id="{{ $section['id'] }}-{{ \Illuminate\Support\Str::slug($endpoint['method'].' '.$endpoint['path']) }}"
                                 data-search="{{ strtolower($endpoint['method'].' '.$endpoint['path'].' '.$endpoint['title'].' '.$endpoint['description']) }}">
                            <summary class="flex cursor-pointer flex-wrap items-center gap-3 px-4 py-3 hover:bg-cream">
                                <span class="w-24 shrink-0 rounded-lg px-2 py-1 text-center font-mono text-xs font-bold {{ $methodClasses[$endpoint['method']] ?? 'bg-sand text-bark' }}">{{ str_replace('|', ' / ', $endpoint['method']) }}</span>
                                <code class="min-w-0 break-all text-sm font-semibold text-forest-dark">/{{ $endpoint['path'] }}</code>
                                <span class="text-sm text-muted">{{ $endpoint['title'] }}</span>
                                <span class="ml-auto rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $accessClasses[$endpoint['access']] }}">{{ $accessLabels[$endpoint['access']] }}</span>
                            </summary>

                            <div class="space-y-5 border-t border-sand px-4 py-5 sm:px-6">
                                @if ($endpoint['description'])
                                    <p class="text-sm text-bark">{{ $endpoint['description'] }}</p>
                                @endif

                                @if ($endpoint['note'] || $endpoint['throttle'])
                                    <div class="flex flex-wrap gap-2 text-xs">
                                        @if ($endpoint['note'])
                                            <span class="rounded-lg bg-amber-50 px-2.5 py-1 font-medium text-amber-900">{{ $endpoint['note'] }}</span>
                                        @endif
                                        @if ($endpoint['throttle'])
                                            <span class="rounded-lg bg-sand px-2.5 py-1 font-medium text-bark">Rate limit: {{ $endpoint['throttle'] }}</span>
                                        @endif
                                    </div>
                                @endif

                                @foreach ([['Query parameters', $endpoint['query']], [($endpoint['body_type'] === 'multipart' ? 'Body (multipart/form-data)' : 'Body (JSON)'), $endpoint['body']]] as [$paramTitle, $params])
                                    @if (count($params))
                                        <div>
                                            <h4 class="text-xs font-semibold uppercase tracking-wide text-muted">{{ $paramTitle }}</h4>
                                            <div class="mt-2 overflow-x-auto rounded-xl border border-sand">
                                                <table class="min-w-full text-sm">
                                                    <thead class="bg-cream text-left text-xs text-muted">
                                                        <tr>
                                                            <th class="px-3 py-2 font-semibold">Name</th>
                                                            <th class="px-3 py-2 font-semibold">Type</th>
                                                            <th class="px-3 py-2 font-semibold">Required</th>
                                                            <th class="px-3 py-2 font-semibold">Description</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-sand">
                                                        @foreach ($params as $param)
                                                            <tr>
                                                                <td class="whitespace-nowrap px-3 py-1.5 font-mono text-xs font-semibold text-forest-dark">{{ $param['name'] }}</td>
                                                                <td class="whitespace-nowrap px-3 py-1.5 font-mono text-xs text-bark">{{ $param['type'] }}</td>
                                                                <td class="px-3 py-1.5 text-xs">
                                                                    @if ($param['required'])
                                                                        <span class="font-semibold text-red-700">required</span>
                                                                    @else
                                                                        <span class="text-muted">optional</span>
                                                                    @endif
                                                                </td>
                                                                <td class="px-3 py-1.5 text-muted">{{ $param['description'] }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach

                                <div>
                                    <div class="flex items-center justify-between">
                                        <h4 class="text-xs font-semibold uppercase tracking-wide text-muted">Example request</h4>
                                        <button type="button" data-copy-sibling class="text-xs font-semibold text-forest hover:underline">Copy</button>
                                    </div>
                                    <pre class="mt-2 overflow-x-auto rounded-xl bg-forest-dark p-4 text-xs leading-5 text-sand"><code>{{ \App\Support\ApiDocumentation::curl($endpoint) }}</code></pre>
                                </div>

                                @if ($endpoint['binary'])
                                    <div>
                                        <h4 class="text-xs font-semibold uppercase tracking-wide text-muted">Response</h4>
                                        <p class="mt-1 text-sm text-bark">200 with the file as the body (Content-Disposition: attachment). Save it as a file; it is not JSON.</p>
                                    </div>
                                @elseif ($endpoint['response'] !== null)
                                    <div>
                                        <div class="flex items-center justify-between">
                                            <h4 class="text-xs font-semibold uppercase tracking-wide text-muted">Example response &middot; {{ $endpoint['status'] }}</h4>
                                            <button type="button" data-copy-sibling class="text-xs font-semibold text-forest hover:underline">Copy</button>
                                        </div>
                                        <pre class="mt-2 max-h-96 overflow-auto rounded-xl bg-forest-dark p-4 text-xs leading-5 text-sand"><code>{{ \App\Support\ApiDocumentation::json($endpoint['response']) }}</code></pre>
                                        @if ($endpoint['response_note'])
                                            <p class="mt-2 text-xs text-muted">{{ $endpoint['response_note'] }}</p>
                                        @endif
                                    </div>
                                @endif

                                @if (count($endpoint['sync_types']))
                                    <div>
                                        <h4 class="text-xs font-semibold uppercase tracking-wide text-muted">Operation types</h4>
                                        <div class="mt-2 overflow-x-auto rounded-xl border border-sand">
                                            <table class="min-w-full text-sm">
                                                <tbody class="divide-y divide-sand">
                                                    @foreach ($endpoint['sync_types'] as [$type, $payload, $who])
                                                        <tr>
                                                            <td class="whitespace-nowrap px-3 py-1.5 font-mono text-xs font-semibold text-forest-dark">{{ $type }}</td>
                                                            <td class="px-3 py-1.5 text-bark">{{ $payload }}</td>
                                                            <td class="px-3 py-1.5 text-xs text-muted">{{ $who }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <h4 class="mt-4 text-xs font-semibold uppercase tracking-wide text-muted">Result status per operation</h4>
                                        <div class="mt-2 overflow-x-auto rounded-xl border border-sand">
                                            <table class="min-w-full text-sm">
                                                <tbody class="divide-y divide-sand">
                                                    @foreach ($endpoint['sync_statuses'] as [$status, $meaning])
                                                        <tr>
                                                            <td class="whitespace-nowrap px-3 py-1.5 font-mono text-xs font-semibold text-forest-dark">{{ $status }}</td>
                                                            <td class="px-3 py-1.5 text-bark">{{ $meaning }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endif

                                <div>
                                    <h4 class="text-xs font-semibold uppercase tracking-wide text-muted">Errors</h4>
                                    <ul class="mt-2 space-y-1 text-sm text-bark">
                                        @if ($endpoint['access'] !== 'public')
                                            <li><code class="rounded bg-sand px-1.5 py-0.5 text-xs">401</code> Missing or revoked token.</li>
                                        @endif
                                        @if ($endpoint['access'] === 'planter')
                                            <li><code class="rounded bg-sand px-1.5 py-0.5 text-xs">403</code> A staff token was used, or the planter is not approved.</li>
                                        @elseif ($endpoint['access'] === 'staff')
                                            <li><code class="rounded bg-sand px-1.5 py-0.5 text-xs">403</code> A planter token was used, the account is inactive, or the role is not allowed to do this.</li>
                                        @elseif ($endpoint['access'] === 'admin' || $endpoint['access'] === 'developer')
                                            <li><code class="rounded bg-sand px-1.5 py-0.5 text-xs">403</code> The account is not {{ $endpoint['access'] === 'developer' ? 'the Super Admin (Developer)' : 'an administrator' }}.</li>
                                        @endif
                                        @foreach ($endpoint['errors'] as [$code, $text])
                                            <li><code class="rounded bg-sand px-1.5 py-0.5 text-xs">{{ $code }}</code> {{ $text }}</li>
                                        @endforeach
                                        @if (count($endpoint['body']))
                                            <li><code class="rounded bg-sand px-1.5 py-0.5 text-xs">422</code> Validation errors: <code>{"message":"...","errors":{"field":["..."]}}</code></li>
                                        @endif
                                        <li><code class="rounded bg-sand px-1.5 py-0.5 text-xs">503</code> Maintenance mode (not for administrators or the Developer).</li>
                                    </ul>
                                </div>
                            </div>
                        </details>
                    @endforeach
                </section>
            @endforeach
        </div>
    </div>

    <script>
        (function () {
            var endpoints = Array.prototype.slice.call(document.querySelectorAll('.api-endpoint'));
            var sections = Array.prototype.slice.call(document.querySelectorAll('.api-section'));
            var search = document.getElementById('api-search');
            var counter = document.getElementById('api-count');

            function filter() {
                var term = search.value.trim().toLowerCase();
                var visible = 0;
                endpoints.forEach(function (el) {
                    var show = term === '' || el.getAttribute('data-search').indexOf(term) !== -1;
                    el.classList.toggle('hidden', !show);
                    if (show) { visible++; }
                    if (show && term !== '') { el.open = true; }
                });
                sections.forEach(function (section) {
                    section.classList.toggle('hidden', section.querySelectorAll('.api-endpoint:not(.hidden)').length === 0);
                });
                counter.textContent = term === '' ? endpoints.length + ' endpoints' : visible + ' of ' + endpoints.length + ' endpoints';
            }

            search.addEventListener('input', filter);
            document.getElementById('api-expand').addEventListener('click', function () {
                endpoints.forEach(function (el) { if (!el.classList.contains('hidden')) { el.open = true; } });
            });
            document.getElementById('api-collapse').addEventListener('click', function () {
                endpoints.forEach(function (el) { el.open = false; });
            });

            function copy(text, button) {
                var done = function () {
                    var label = button.textContent;
                    button.textContent = 'Copied';
                    setTimeout(function () { button.textContent = label; }, 1200);
                };
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(text).then(done);
                    return;
                }
                var area = document.createElement('textarea');
                area.value = text;
                document.body.appendChild(area);
                area.select();
                document.execCommand('copy');
                document.body.removeChild(area);
                done();
            }

            document.addEventListener('click', function (event) {
                var button = event.target.closest('[data-copy], [data-copy-sibling]');
                if (!button) { return; }
                if (button.hasAttribute('data-copy')) {
                    copy(button.getAttribute('data-copy'), button);
                    return;
                }
                var pre = button.closest('div').parentElement.querySelector('pre');
                if (pre) { copy(pre.textContent, button); }
            });

            // Open an endpoint when the page is opened with its #anchor.
            if (location.hash) {
                var target = document.querySelector(location.hash);
                if (target && target.tagName === 'DETAILS') { target.open = true; }
            }

            filter();
        })();
    </script>
@endsection
