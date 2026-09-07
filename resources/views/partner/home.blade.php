@extends('layouts.partner')

@section('content')
    <div class="partner-page">
        <header class="partner-nav" data-partner-nav>
            <div class="partner-nav-inner">
                <a href="{{ route('partner.home') }}" class="partner-brand">
                    <img src="{{ asset('SCSNR-logo.png') }}" alt="{{ config('app.short_name') }}" class="h-10 w-10 shrink-0 rounded-full bg-white object-contain p-1 shadow-sm sm:h-11 sm:w-11">
                    <span class="min-w-0">
                        <span class="block text-sm font-semibold tracking-wide text-white">{{ config('app.short_name') }}</span>
                        <span class="hidden text-[11px] text-sand/80 sm:block">Partner Portal</span>
                    </span>
                </a>

                <nav class="partner-nav-links" aria-label="Main sections">
                    <a href="#what-is-certification">What is certification</a>
                    <a href="#name-of-certification">Name</a>
                    <a href="#to-whom">To whom</a>
                    <a href="#why-certification">Why certification</a>
                    <a href="#benefits">Benefits</a>
                    <a href="#governing-modules">Governing modules</a>
                    <a href="#certification-pathway">Pathway</a>
                </nav>

                <div class="partner-nav-actions">
                    <a href="{{ route('planter.login') }}" class="partner-nav-login">Login</a>
                    <a href="{{ route('planter.register') }}" class="partner-nav-register">Register</a>
                    <button type="button" class="partner-nav-toggle" data-partner-menu-toggle aria-expanded="false" aria-controls="partner-mobile-menu" aria-label="Open menu">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
                    </button>
                </div>
            </div>

            <div id="partner-mobile-menu" class="partner-mobile-menu hidden" data-partner-mobile-menu>
                <nav class="partner-mobile-links" aria-label="Mobile sections">
                    <a href="#what-is-certification">What is certification</a>
                    <a href="#name-of-certification">Name of certification</a>
                    <a href="#to-whom">To whom</a>
                    <a href="#why-certification">Why certification</a>
                    <a href="#benefits">Benefits</a>
                    <a href="#governing-modules">Governing modules</a>
                    <a href="#certification-pathway">Certification pathway</a>
                    <a href="#powered-by">Implemented by</a>
                    <a href="#access">Partner access</a>
                </nav>
                <div class="partner-mobile-actions">
                    <a href="{{ route('planter.register') }}" class="partner-nav-register w-full text-center">Register</a>
                    <a href="{{ route('planter.login') }}" class="partner-nav-login text-center">Login</a>
                </div>
            </div>
        </header>

        <section class="partner-hero">
            <div
                class="partner-hero-media"
                style="background-image: url('{{ asset('partner-hero.jpg') }}')"
                aria-hidden="true"
            ></div>
            <div class="partner-hero-shade"></div>

            <div class="partner-hero-content">
                <p class="partner-kicker">{{ config('app.short_name') }} · National Framework</p>
                <h1 class="partner-title">{{ config('app.full_name') }}</h1>
                <p class="partner-lead">
                    A national sustainability framework for natural rubber — covering product safety, environmental, social and economic responsibility, with digital registration and QR-based farm-to-market traceability.
                </p>
                <div class="partner-cta">
                    <a href="{{ route('planter.register') }}" class="partner-cta-primary">Register now</a>
                    <a href="{{ route('planter.login') }}" class="partner-cta-login">Already registered? Login</a>
                </div>

                <div class="partner-hero-powered">
                    <x-governing-logos size="sm" class="!justify-start gap-3" />
                </div>
            </div>
        </section>

        <section id="what-is-certification" class="partner-section">
            <div class="partner-section-inner">
                <p class="partner-section-kicker">What is certification?</p>
                <h2 class="partner-section-title">A verified standard for sustainable rubber</h2>
                <p class="partner-section-lead">
                    Certification is the formal process of assessing farms and plantations against agreed sustainability criteria — covering on-farm production and post-production practices — so produce is safe, traceable and responsible across economic, social and environmental dimensions.
                </p>
                <p class="mt-6 max-w-3xl text-sm leading-7 text-muted">
                    As guided by FAO / UNSDG / EU principles, SCSNR applies a collection of requirements that producers implement and auditors verify, resulting in sustainable natural rubber that can be trusted by markets, manufacturers and consumers.
                </p>
            </div>
        </section>

        <section id="name-of-certification" class="partner-section bg-cream">
            <div class="partner-section-inner">
                <p class="partner-section-kicker">Name of certification</p>
                <h2 class="partner-section-title">{{ config('app.full_name') }}</h2>
                <p class="partner-section-lead">
                    Short name: <span class="font-semibold text-forest-dark">{{ config('app.short_name') }}</span> — Sri Lanka’s national sustainability certification system for natural rubber.
                </p>
                <div class="partner-features mt-10">
                    <article class="partner-feature-card">
                        <span class="partner-feature-icon">SCSNR</span>
                        <h3>Official short name</h3>
                        <p>Used across registration, QR codes, certificates and the digital partner portal.</p>
                    </article>
                    <article class="partner-feature-card">
                        <span class="partner-feature-icon">01</span>
                        <h3>National framework</h3>
                        <p>Designed for smallholders, plantations, processors, manufacturers and exporters.</p>
                    </article>
                    <article class="partner-feature-card">
                        <span class="partner-feature-icon">02</span>
                        <h3>Aligned globally</h3>
                        <p>Supports readiness for GPSNR, EUDR, FAO and international trading requirements.</p>
                    </article>
                </div>
            </div>
        </section>

        <section id="to-whom" class="partner-section">
            <div class="partner-section-inner">
                <p class="partner-section-kicker">To whom</p>
                <h2 class="partner-section-title">Who SCSNR is for</h2>
                <p class="partner-section-lead">SCSNR serves every stage of the natural rubber value chain in Sri Lanka.</p>

                <ul class="partner-audience">
                    <li>
                        <span class="partner-audience-num">01</span>
                        <div>
                            <h3>Smallholders</h3>
                            <p>Individual and small-farm rubber growers who produce most of Sri Lanka’s natural rubber.</p>
                        </div>
                    </li>
                    <li>
                        <span class="partner-audience-num">02</span>
                        <div>
                            <h3>Planters</h3>
                            <p>Medium and large-scale plantation operators seeking certified, documented production.</p>
                        </div>
                    </li>
                    <li>
                        <span class="partner-audience-num">03</span>
                        <div>
                            <h3>Manufacturers</h3>
                            <p>Rubber product manufacturers who need sustainable, traceable raw material supply.</p>
                        </div>
                    </li>
                    <li>
                        <span class="partner-audience-num">04</span>
                        <div>
                            <h3>Processors</h3>
                            <p>Latex and rubber processors handling farm produce for industry and further manufacture.</p>
                        </div>
                    </li>
                    <li>
                        <span class="partner-audience-num">05</span>
                        <div>
                            <h3>Exporters</h3>
                            <p>Exporters supplying local and international markets that require certified, QR-traceable rubber.</p>
                        </div>
                    </li>
                </ul>
            </div>
        </section>

        <section id="why-certification" class="partner-section bg-cream">
            <div class="partner-section-inner">
                <p class="partner-section-kicker">Why certification?</p>
                <h2 class="partner-section-title">Why SCSNR is required</h2>
                <p class="partner-section-lead">
                    Markets and consumers demand sustainable, deforestation-free and traceable rubber. Certification closes the gap between local production and global trade expectations.
                </p>
                <div class="partner-features">
                    <article class="partner-feature-card">
                        <span class="partner-feature-icon">01</span>
                        <h3>Consumer confidence</h3>
                        <p>Present-day buyers expect product safety, quality and transparent sourcing.</p>
                    </article>
                    <article class="partner-feature-card">
                        <span class="partner-feature-icon">02</span>
                        <h3>International trade</h3>
                        <p>Supports EUDR and paperless marketing with QR codes, mobile access and digital records.</p>
                    </article>
                    <article class="partner-feature-card">
                        <span class="partner-feature-icon">03</span>
                        <h3>Stay connected globally</h3>
                        <p>Avoid isolation from international standards and upcoming market requirements.</p>
                    </article>
                    <article class="partner-feature-card">
                        <span class="partner-feature-icon">04</span>
                        <h3>Value-chain responsibility</h3>
                        <p>Secures product, social, economic and environmental duties with traceability and transparency.</p>
                    </article>
                    <article class="partner-feature-card">
                        <span class="partner-feature-icon">05</span>
                        <h3>Future market access</h3>
                        <p>Positions growers for compulsory and strategic pathways toward 2027/28 market expectations.</p>
                    </article>
                    <article class="partner-feature-card">
                        <span class="partner-feature-icon">06</span>
                        <h3>Worker &amp; community care</h3>
                        <p>Strengthens worker health, welfare and community infrastructure alongside productivity.</p>
                    </article>
                </div>
            </div>
        </section>

        <section id="benefits" class="partner-section">
            <div class="partner-section-inner">
                <p class="partner-section-kicker">Benefits</p>
                <h2 class="partner-section-title">Who benefits from SCSNR</h2>
                <p class="partner-section-lead">Built for smallholder growers (about 85%), large planters, processors, manufacturers and exporters.</p>

                <div class="mt-12 grid gap-8 lg:grid-cols-2">
                    <div>
                        <h3 class="text-lg font-semibold text-forest-dark">For producers</h3>
                        <ul class="partner-bullet-list">
                            <li>Improved personal hygiene and worker safety</li>
                            <li>Easier management and forecasting</li>
                            <li>Business diversification opportunities</li>
                            <li>Higher profit margin and more market opportunities</li>
                            <li>Continuous supply and efficient use of resources</li>
                            <li>Reduced post-harvest losses and stronger consumer trust</li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-forest-dark">For consumers &amp; industry</h3>
                        <ul class="partner-bullet-list">
                            <li>Greater confidence and trust in the product</li>
                            <li>Lower biological and chemical contamination risk</li>
                            <li>Product traceability from farm to market</li>
                            <li>Timelier availability and reduced price fluctuation</li>
                            <li>Less wastage and higher industrial value</li>
                            <li>Easier access to safe, quality rubber products</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <section id="governing-modules" class="partner-section bg-cream">
            <div class="partner-section-inner">
                <p class="partner-section-kicker">Governing modules</p>
                <h2 class="partner-section-title">Five modules of SCSNR</h2>
                <p class="partner-section-lead">In accordance with the Global Platform for Sustainable Natural Rubber (GPSNR) policy framework.</p>

                <div class="partner-features">
                    <article class="partner-feature-card">
                        <span class="partner-feature-icon">01</span>
                        <h3>Product Safety Module (PSM)</h3>
                        <p>Safe and quality rubber through responsible on-farm production and post-harvest handling.</p>
                    </article>
                    <article class="partner-feature-card">
                        <span class="partner-feature-icon">02</span>
                        <h3>Environmental Management (EMM)</h3>
                        <p>Environmental responsibility and sustainable management across plantation operations.</p>
                    </article>
                    <article class="partner-feature-card">
                        <span class="partner-feature-icon">03</span>
                        <h3>Worker Health &amp; Welfare (WHSWM)</h3>
                        <p>Social responsibility — worker health, safety, welfare and labour-law compliance.</p>
                    </article>
                    <article class="partner-feature-card">
                        <span class="partner-feature-icon">04</span>
                        <h3>Product Marketing (PMM)</h3>
                        <p>Economic responsibility — market access, documentation and value-chain transparency.</p>
                    </article>
                    <article class="partner-feature-card">
                        <span class="partner-feature-icon">05</span>
                        <h3>General Requirements (GRM)</h3>
                        <p>Registration, documentation and digital traceability for farms and plantations.</p>
                    </article>
                    <article class="partner-feature-card">
                        <span class="partner-feature-icon">QR</span>
                        <h3>QR traceability</h3>
                        <p>Compulsory crop traceability with QR codes from application and audit through certificate and market.</p>
                    </article>
                </div>
            </div>
        </section>

        <section id="certification-pathway" class="partner-section">
            <div class="partner-section-inner">
                <p class="partner-section-kicker">Certification pathway</p>
                <h2 class="partner-section-title">From application to certificate</h2>
                <p class="partner-section-lead">A clear path for rubber planters joining SCSNR through this digital partner portal.</p>

                <ol class="partner-pathway">
                    <li>
                        <span class="partner-pathway-num">01</span>
                        <div>
                            <h3>Register</h3>
                            <p>Submit plantation details online, or download the form and upload a completed scan for offline registration. Your permanent registration number is issued when the application is approved or rejected.</p>
                        </div>
                    </li>
                    <li>
                        <span class="partner-pathway-num">02</span>
                        <div>
                            <h3>Application &amp; field audit</h3>
                            <p>Governing authorities review your application. Field audit and documentation checks progress under the SCSNR scheme.</p>
                        </div>
                    </li>
                    <li>
                        <span class="partner-pathway-num">03</span>
                        <div>
                            <h3>Approvals</h3>
                            <p>After review, create your password, sign in to your dashboard, and track audit status through opening, progress and result stages.</p>
                        </div>
                    </li>
                    <li>
                        <span class="partner-pathway-num">04</span>
                        <div>
                            <h3>Certificate issued</h3>
                            <p>Receive certification outcomes with your SCSNR ID and QR code for farm-to-market traceability in local and export markets.</p>
                        </div>
                    </li>
                </ol>
            </div>
        </section>

        <section id="powered-by" class="partner-powered">
            <div class="partner-powered-inner">
                <p class="partner-section-kicker">Institutional framework</p>
                <h2 class="partner-section-title">Implemented by</h2>
                <p class="partner-section-lead">Scheme ownership under the Ministry of Plantation &amp; Community Infrastructure, with RRISL for certification and RDD for field implementation.</p>

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
                    <p class="partner-access-lead">New planters register first. Approved planters sign in to continue certification and traceability.</p>
                </div>
                <div class="partner-access-actions">
                    <a href="{{ route('planter.register') }}" class="partner-cta-primary w-full sm:w-auto">Register now</a>
                    <a href="{{ route('planter.login') }}" class="partner-link">Already registered? Login</a>
                    <a href="{{ route('planter.password.create') }}" class="partner-link">Approved but no password yet? Create password</a>
                </div>
            </div>
        </section>

        <footer class="partner-footer">
            <x-app-footer class="mx-auto max-w-6xl px-4 py-8 sm:px-6" />
        </footer>

        <a
            href="{{ route('planter.register') }}"
            class="partner-fab-register"
            data-partner-fab
            data-partner-fab-hide="#access"
        >
            <span>Register now</span>
            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
            </svg>
        </a>
    </div>
@endsection
