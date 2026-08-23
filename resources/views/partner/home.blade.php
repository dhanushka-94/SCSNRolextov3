@extends('layouts.partner')

@section('content')
    <div class="partner-page">
        <header class="partner-nav">
            <div class="partner-nav-inner">
                <a href="{{ route('partner.home') }}" class="partner-brand">
                    <img src="{{ asset('SCSNR-logo.png') }}" alt="{{ config('app.short_name') }}" class="h-11 w-11 rounded-full bg-white object-contain p-1 shadow-sm">
                    <span>
                        <span class="block text-sm font-semibold tracking-wide text-white">{{ config('app.short_name') }}</span>
                        <span class="block text-[11px] text-sand/80">Partner Portal</span>
                    </span>
                </a>
                <div class="flex items-center gap-2 sm:gap-3">
                    <a href="{{ route('planter.login') }}" class="partner-nav-login">Login</a>
                    <a href="{{ route('planter.register') }}" class="partner-nav-register">Register</a>
                </div>
            </div>
        </header>

        <section class="partner-hero">
            <div class="partner-hero-media" aria-hidden="true"></div>
            <div class="partner-hero-shade"></div>

            <div class="partner-hero-content">
                <p class="partner-kicker">{{ config('app.short_name') }}</p>
                <h1 class="partner-title">{{ config('app.full_name') }}</h1>
                <p class="partner-lead">
                    Register your plantation, receive your SCSNR identification, and manage certification through the official partner portal.
                </p>
                <div class="partner-cta">
                    <a href="{{ route('planter.register') }}" class="partner-cta-primary">
                        Register
                    </a>
                    <a href="{{ route('planter.login') }}" class="partner-cta-secondary">
                        Login
                    </a>
                </div>

                <div class="partner-hero-powered">
                    <x-governing-logos size="sm" class="!justify-start gap-3" />
                    <p class="partner-hero-powered-text">{{ config('app.powered_by') }}</p>
                </div>
            </div>
        </section>

        <section id="features" class="partner-section bg-cream">
            <div class="partner-section-inner">
                <p class="partner-section-kicker">Portal features</p>
                <h2 class="partner-section-title">Everything in your dashboard</h2>
                <p class="partner-section-lead">After registration and approval, planters and staff use dedicated dashboards to track progress and manage records.</p>

                <div class="partner-features">
                    <article class="partner-feature-card">
                        <span class="partner-feature-icon">01</span>
                        <h3>Planter dashboard</h3>
                        <p>See registration status, your SCSNR ID, audit stage, QR code, and account summary in one place.</p>
                    </article>
                    <article class="partner-feature-card">
                        <span class="partner-feature-icon">02</span>
                        <h3>SCSNR ID &amp; QR</h3>
                        <p>Your official identification number is issued at registration. Download a high-quality QR code from your profile.</p>
                    </article>
                    <article class="partner-feature-card">
                        <span class="partner-feature-icon">03</span>
                        <h3>Audit tracking</h3>
                        <p>Follow sustainability audit progress from opening through review to the final certification result.</p>
                    </article>
                    <article class="partner-feature-card">
                        <span class="partner-feature-icon">04</span>
                        <h3>Staff dashboard</h3>
                        <p>Administrators review pending applications, approve registrations, and monitor planter records from the admin workspace.</p>
                    </article>
                    <article class="partner-feature-card">
                        <span class="partner-feature-icon">05</span>
                        <h3>Approval lobby</h3>
                        <p>Pending online and offline applications appear in a dedicated review queue with applicant details at a glance.</p>
                    </article>
                    <article class="partner-feature-card">
                        <span class="partner-feature-icon">06</span>
                        <h3>Profile management</h3>
                        <p>Update contact details, view your full application, and manage planter account settings after approval.</p>
                    </article>
                </div>
            </div>
        </section>

        <section id="how-it-works" class="partner-section">
            <div class="partner-section-inner">
                <p class="partner-section-kicker">How it works</p>
                <h2 class="partner-section-title">From registration to certification</h2>
                <p class="partner-section-lead">A clear path for rubber planters joining SCSNR.</p>

                <ol class="partner-steps">
                    <li>
                        <span class="partner-step-num">01</span>
                        <h3>Register</h3>
                        <p>Submit your planter details online, or download the form and upload a completed scan.</p>
                    </li>
                    <li>
                        <span class="partner-step-num">02</span>
                        <h3>Get approved</h3>
                        <p>Your application is reviewed by the governing authorities. Once approved, create your password and sign in.</p>
                    </li>
                    <li>
                        <span class="partner-step-num">03</span>
                        <h3>Use your dashboard</h3>
                        <p>Sign in to track audit progress, view your SCSNR ID and QR code, and manage your planter profile.</p>
                    </li>
                </ol>
            </div>
        </section>

        <section id="powered-by" class="partner-powered">
            <div class="partner-powered-inner">
                <p class="partner-section-kicker">Institutional framework</p>
                <h2 class="partner-section-title">Powered by</h2>
                <p class="partner-section-lead">{{ config('app.powered_by') }}</p>

                <div class="partner-powered-grid">
                    @foreach (config('app.governing_bodies') as $index => $body)
                        <article class="partner-powered-card">
                            <span class="partner-powered-rank">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                            <img
                                src="{{ asset($body['logo']) }}"
                                alt="{{ $body['name'] }}"
                                class="partner-powered-logo"
                            >
                            <h3>{{ $body['name'] }}</h3>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="access" class="partner-access">
            <div class="partner-access-inner">
                <div>
                    <p class="partner-section-kicker text-tan">Partner access</p>
                    <h2 class="partner-access-title">Ready to join SCSNR?</h2>
                    <p class="partner-access-lead">New planters register first. Approved planters sign in to continue.</p>
                </div>
                <div class="partner-access-actions">
                    <a href="{{ route('planter.register') }}" class="partner-cta-primary w-full sm:w-auto">Register</a>
                    <a href="{{ route('planter.login') }}" class="partner-cta-secondary w-full sm:w-auto">Login</a>
                    <a href="{{ route('planter.password.create') }}" class="partner-link">Approved but no password yet? Create password</a>
                </div>
            </div>
        </section>

        <footer class="partner-footer">
            <x-app-footer class="mx-auto max-w-6xl px-4 py-8 sm:px-6" />
        </footer>
    </div>
@endsection
