<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify · {{ $certificate->certificate_number }}</title>
    <x-app-fonts :display="true" />
    @vite(['resources/css/app.css'])
    <style>
        .verify-screen {
            position: fixed;
            inset: 0;
            z-index: 40;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            background:
                radial-gradient(circle at top, rgba(255, 255, 255, 0.7), transparent 42%),
                #efe8dc;
            transition: opacity 0.45s ease, visibility 0.45s ease;
        }
        .verify-screen.is-done {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }
        .verify-panel {
            width: min(100%, 28rem);
            border-radius: 1.25rem;
            background: #fffdf8;
            box-shadow: 0 24px 60px rgba(18, 56, 38, 0.16);
            padding: 2rem 1.75rem 1.5rem;
            text-align: center;
        }
        .verify-logo {
            width: 4.5rem;
            height: 4.5rem;
            margin: 0 auto;
            object-fit: contain;
        }
        .verify-kicker {
            margin: 1rem 0 0;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #8a6a45;
        }
        .verify-panel h1 {
            margin: 0.35rem 0 0;
            font-family: Fraunces, Georgia, serif;
            font-size: 1.65rem;
            color: #123826;
        }
        .verify-ref {
            margin: 0.45rem 0 0;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.85rem;
            color: #1b4d32;
        }
        .verify-track {
            height: 0.4rem;
            margin: 1.4rem 0 0;
            overflow: hidden;
            border-radius: 999px;
            background: #efe4d2;
        }
        .verify-bar {
            height: 100%;
            width: 0;
            border-radius: inherit;
            background: linear-gradient(90deg, #1b4d32, #2f6b45);
            transition: width 0.45s ease;
        }
        .verify-steps {
            margin: 1.25rem 0 0;
            padding: 0;
            list-style: none;
            text-align: left;
        }
        .verify-steps li {
            display: flex;
            align-items: center;
            gap: 0.7rem;
            padding: 0.45rem 0;
            font-size: 0.9rem;
            color: #8a7b6c;
        }
        .verify-steps li.is-active { color: #123826; font-weight: 600; }
        .verify-steps li.is-done { color: #1b4d32; }
        .verify-mark {
            display: inline-flex;
            width: 1.15rem;
            height: 1.15rem;
            flex: none;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            border: 1.5px solid #d7c7b0;
            font-size: 0.7rem;
        }
        .verify-steps li.is-active .verify-mark {
            border-color: #1b4d32;
            box-shadow: 0 0 0 4px rgba(27, 77, 50, 0.12);
        }
        .verify-steps li.is-done .verify-mark {
            border-color: #1b4d32;
            background: #1b4d32;
            color: #fffdf8;
        }
        .verify-result.is-pending { opacity: 0; transform: translateY(8px); }
        .verify-result.is-shown { opacity: 1; transform: none; transition: opacity 0.45s ease, transform 0.45s ease; }
        @media (prefers-reduced-motion: reduce) {
            .verify-screen, .verify-bar, .verify-result { transition: none; }
        }
    </style>
</head>
<body class="flex min-h-screen flex-col bg-cream text-ink">
    <div id="verify-screen" class="verify-screen" role="status" aria-live="polite" hidden>
        <div class="verify-panel">
            <img class="verify-logo" src="{{ asset('SPPNR-logo.png') }}" alt="">
            <p class="verify-kicker">{{ config('app.short_name') }}</p>
            <h1>Verifying certificate</h1>
            <p class="verify-ref">{{ $certificate->certificate_number }}</p>
            <div class="verify-track" aria-hidden="true"><div id="verify-bar" class="verify-bar"></div></div>
            <ol id="verify-steps" class="verify-steps">
                <li><span class="verify-mark"></span>Reading the certificate number</li>
                <li><span class="verify-mark"></span>Matching the registry record</li>
                <li><span class="verify-mark"></span>Checking issue and revocation</li>
                <li><span class="verify-mark"></span>Preparing the result</li>
            </ol>
        </div>
    </div>

    <main id="verify-result" class="verify-result mx-auto w-full max-w-lg flex-1 px-4 py-10">
        <div class="text-center">
            <img src="{{ asset('SPPNR-logo.png') }}" alt="" class="mx-auto h-16 w-16 object-contain">
            <p class="mt-3 text-xs font-semibold uppercase tracking-[0.2em] text-tan">{{ config('app.short_name') }}</p>
            <h1 class="mt-2 font-display text-3xl text-forest-dark">Certificate verification</h1>
        </div>

        <article class="card mt-8 overflow-hidden">
            <div class="border-b border-sand px-5 py-5 {{ $certificate->isRevoked() ? 'bg-red-50' : 'bg-cream' }}">
                <p class="text-xs font-semibold uppercase tracking-wide text-muted">Result</p>
                <p class="mt-1 text-xl font-semibold {{ $certificate->isRevoked() ? 'text-red-800' : 'text-forest-dark' }}">
                    {{ $certificate->publicVerificationLabel() }}
                </p>
                <p class="mt-1 font-mono text-sm text-forest">{{ $certificate->certificate_number }}</p>
            </div>

            <dl class="grid gap-4 p-5">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Planter</dt>
                    <dd class="mt-1 text-sm font-semibold">{{ $planter->name }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-muted">SPPNR ID</dt>
                    <dd class="mt-1 font-mono text-sm">{{ $planter->identification_number }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-muted">District / Farm</dt>
                    <dd class="mt-1 text-sm">{{ $planter->district ?: '—' }} · {{ $planter->farm_name ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Outcome</dt>
                    <dd class="mt-1 text-sm">{{ $certificate->outcomeLabel() }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Issued</dt>
                    <dd class="mt-1 text-sm">{{ sl_date($certificate->issued_at) }}</dd>
                </div>
                @if ($certificate->isRevoked())
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Revoked</dt>
                        <dd class="mt-1 text-sm">{{ sl_date($certificate->revoked_at) }}</dd>
                    </div>
                @endif
            </dl>

            <div class="flex justify-center border-t border-sand px-5 py-5">
                <img src="{{ route('verify.qr', $certificate->public_token) }}" alt="Certificate QR" class="h-32 w-32 rounded-xl bg-white p-2 ring-1 ring-sand">
            </div>
        </article>

        <p class="mt-6 text-center text-xs text-muted">
            <a href="{{ route('verify.form') }}" class="font-semibold text-leaf hover:text-forest">Verify by reference number</a>
            ·
            <a href="{{ route('partner.home') }}" class="font-semibold text-leaf hover:text-forest">{{ config('app.short_name') }} partner portal</a>
        </p>
    </main>

    <x-app-footer class="px-4 py-8" />

    <script>
        (function () {
            var screen = document.getElementById('verify-screen');
            var result = document.getElementById('verify-result');
            var bar = document.getElementById('verify-bar');
            var steps = Array.prototype.slice.call(document.querySelectorAll('#verify-steps li'));
            var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            function reveal() {
                result.classList.add('is-shown');
                screen.classList.add('is-done');
                screen.setAttribute('aria-hidden', 'true');
            }

            if (reduce) {
                result.classList.add('is-shown');
                return;
            }

            result.classList.add('is-pending');
            screen.hidden = false;

            var index = 0;
            function tick() {
                steps.forEach(function (step, i) {
                    step.classList.toggle('is-done', i < index);
                    step.classList.toggle('is-active', i === index);
                    step.querySelector('.verify-mark').textContent = i < index ? '✓' : '';
                });
                bar.style.width = Math.round((index / steps.length) * 100) + '%';
                if (index >= steps.length) {
                    window.setTimeout(reveal, 280);
                    return;
                }
                index += 1;
                window.setTimeout(tick, 700);
            }
            tick();
        })();
    </script>
</body>
</html>
