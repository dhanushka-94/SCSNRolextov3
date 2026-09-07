@extends('layouts.guest')

@section('title', 'Planter Registration')

@section('content')
    <div class="relative flex min-h-screen flex-col items-center justify-center px-4 py-8 sm:py-12">
        <div class="w-full max-w-4xl space-y-6">
            <div class="overflow-hidden rounded-3xl border border-sand bg-paper shadow-[0_24px_60px_rgba(27,77,50,0.12)]">
                <div class="border-b border-sand bg-forest-dark px-6 py-8 text-white sm:px-10">
                    <div class="flex items-center gap-4">
                        <img src="{{ asset('SCSNR-logo.png') }}" alt="{{ config('app.short_name') }} logo" class="h-16 w-16 rounded-full bg-white object-contain p-1">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-tan">Planter Portal</p>
                            <h1 class="mt-1 text-xl font-semibold sm:text-2xl">ස්වභාවික රබර් සඳහා තිරසාර සහතිකරණය අයදුම්පත</h1>
                            <p class="mt-1 text-sm text-sand/85">Application for Sustainability Certification for Natural Rubber</p>
                        </div>
                    </div>
                </div>

                <form
                    method="POST"
                    action="{{ route('planter.register.store') }}"
                    enctype="multipart/form-data"
                    class="relative space-y-8 p-6 sm:p-10"
                    data-certification-form
                >
                    @csrf
                    <x-registration-honeypot />

                    @if ($errors->has('registration'))
                        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                            {{ $errors->first('registration') }}
                        </div>
                    @endif

                    @if ($errors->any() && ! $errors->has('application_document') && ! $errors->has('registration'))
                        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <section class="space-y-5">
                        <h2 class="border-b border-sand pb-2 text-base font-semibold text-forest-dark">
                            අයදුම්කරුගේ විස්තර / Applicant details
                        </h2>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <x-bilingual-label si="අයදුම්කරුගේ නම" en="Applicant name" for="name" />
                                <input id="name" name="name" type="text" value="{{ old('name') }}" required class="input-field">
                                @error('name')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <x-bilingual-label si="ජා. හැ. අංකය" en="NIC number" for="nic" />
                                <input id="nic" name="nic" type="text" value="{{ old('nic') }}" required class="input-field">
                                @error('nic')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                            </div>
                            <x-district-rdo-fields bilingual="true" />
                        </div>
                    </section>

                    <section class="space-y-5">
                        <h2 class="border-b border-sand pb-2 text-base font-semibold text-forest-dark">
                            ගොවිපල පිළිබඳ සාමාන්‍ය තොරතුරු / General farm information
                        </h2>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <x-bilingual-label si="ගොවිපලේ නම" en="Farm name" for="farm_name" />
                                <input id="farm_name" name="farm_name" type="text" value="{{ old('farm_name') }}" required class="input-field">
                                @error('farm_name')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                            </div>
                            <div class="sm:col-span-2">
                                <x-bilingual-label si="ගොවිපලේ ලිපිනය" en="Farm address" for="address" />
                                <textarea id="address" name="address" rows="3" required class="input-field">{{ old('address') }}</textarea>
                                @error('address')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <x-bilingual-label si="දුරකථන අංකය" en="Phone number" for="phone" />
                                <input id="phone" name="phone" type="text" value="{{ old('phone') }}" required class="input-field">
                                @error('phone')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <x-bilingual-label si="WhatsApp අංකය" en="WhatsApp number" for="whatsapp" />
                                <input id="whatsapp" name="whatsapp" type="text" value="{{ old('whatsapp') }}" class="input-field">
                                @error('whatsapp')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <x-bilingual-label si="ෆැක්ස් අංකය" en="Fax number" for="fax" />
                                <input id="fax" name="fax" type="text" value="{{ old('fax') }}" class="input-field">
                                @error('fax')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <x-bilingual-label si="ඊ මේල්" en="Email" for="email" />
                                <input id="email" name="email" type="email" value="{{ old('email') }}" required class="input-field">
                                @error('email')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div>
                            <x-bilingual-label si="ව්‍යාපාරයේ ස්වභාවය" en="Nature of business" />
                            <div class="mt-2 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach (\App\Models\Planter::businessTypes() as $value => $label)
                                    <label class="flex cursor-pointer items-start gap-2 rounded-xl border border-line bg-cream/60 px-3 py-2.5 text-sm">
                                        <input
                                            type="radio"
                                            name="business_type"
                                            value="{{ $value }}"
                                            class="mt-1"
                                            @checked(old('business_type') === $value)
                                            required
                                        >
                                        <span>
                                            <span class="block font-medium text-bark-dark">{{ $label['si'] }}</span>
                                            <span class="block text-xs text-muted">{{ $label['en'] }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            @error('business_type')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                        </div>
                    </section>

                    <section class="space-y-5">
                        <h2 class="border-b border-sand pb-2 text-base font-semibold text-forest-dark">
                            සහතිකකරණ ඉතිහාසය / Certification history
                        </h2>

                        <div>
                            <x-bilingual-label si="දැනටමත් ඔබගේ ගොවිපළ සහතිකකරණය කර තිබේද?" en="Is your farm already certified?" />
                            <div class="mt-2 flex flex-wrap gap-4">
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="radio" name="already_certified" value="1" data-toggle-group="already-certified" @checked(old('already_certified') === '1') required>
                                    ඔව් / Yes
                                </label>
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="radio" name="already_certified" value="0" data-toggle-group="already-certified" @checked(old('already_certified', '0') === '0')>
                                    නැත / No
                                </label>
                            </div>
                            @error('already_certified')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2 {{ old('already_certified') === '1' ? '' : 'hidden' }}" data-panel="already-certified" data-show-when="1">
                            <div class="sm:col-span-2">
                                <x-bilingual-label si="කුමන ප්‍රමිතිකරණය යටතේද?" en="Under which standard?" for="certification_standard" />
                                <input id="certification_standard" name="certification_standard" type="text" value="{{ old('certification_standard') }}" class="input-field">
                                @error('certification_standard')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                            </div>
                            <div class="sm:col-span-2">
                                <x-bilingual-label si="සහතිකයේ ඡායා පිටපත" en="Photocopy of certificate" for="prior_certificate_document" />
                                <input id="prior_certificate_document" name="prior_certificate_document" type="file" accept=".pdf,image/png,image/jpeg,image/webp" class="input-field file:mr-3 file:rounded-lg file:border-0 file:bg-leaf file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-white">
                                <p class="mt-1 text-xs text-muted">PDF, JPG, PNG, WEBP · max 10 MB</p>
                                @error('prior_certificate_document')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div>
                            <x-bilingual-label si="මෙයට පෙර සහතිකකරණය ප්‍රතික්ෂේප වී හෝ අත්හිටුවා ඇතිද?" en="Was certification previously rejected or suspended?" />
                            <div class="mt-2 flex flex-wrap gap-4">
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="radio" name="certification_rejected_or_suspended" value="1" data-toggle-group="cert-issue" @checked(old('certification_rejected_or_suspended') === '1') required>
                                    ඔව් / Yes
                                </label>
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="radio" name="certification_rejected_or_suspended" value="0" data-toggle-group="cert-issue" @checked(old('certification_rejected_or_suspended', '0') === '0')>
                                    නැත / No
                                </label>
                            </div>
                            @error('certification_rejected_or_suspended')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                        </div>

                        <div class="{{ old('certification_rejected_or_suspended') === '1' ? '' : 'hidden' }}" data-panel="cert-issue" data-show-when="1">
                            <x-bilingual-label si="ප්‍රතික්ෂේප වීමට හෝ අත්හිටුවීමට හේතු" en="Reason for rejection or suspension" for="certification_issue_reason" />
                            <textarea id="certification_issue_reason" name="certification_issue_reason" rows="3" class="input-field">{{ old('certification_issue_reason') }}</textarea>
                            @error('certification_issue_reason')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                        </div>
                    </section>

                    <section class="space-y-5">
                        <h2 class="border-b border-sand pb-2 text-base font-semibold text-forest-dark">
                            සහතිකකරණය සඳහා නිෂ්පාදන / Products for certification
                        </h2>

                        <x-crop-repeater :values="old('crops_products', [])" :required="true" />
                        @error('crops_products')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                        @error('crops_products.*')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                    </section>

                    <section class="space-y-5">
                        <h2 class="border-b border-sand pb-2 text-base font-semibold text-forest-dark">
                            දැනුවත්භාවය සහ සැකසුම් / Awareness and processing
                        </h2>

                        <div>
                            <x-bilingual-label si="දැනට භාවිතා වන සහතිකකරණය පිළිබඳව ඔබ දැනුවත්ද?" en="Are you aware of currently used certification?" />
                            <div class="mt-2 flex flex-wrap gap-4">
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="radio" name="aware_of_certification" value="1" @checked(old('aware_of_certification') === '1') required>
                                    ඔව් / Yes
                                </label>
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="radio" name="aware_of_certification" value="0" @checked(old('aware_of_certification') === '0')>
                                    නැත / No
                                </label>
                            </div>
                            @error('aware_of_certification')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <x-bilingual-label si="ඔබ සතුව සහතිකකරණය පිළිබඳව විස්තර පත්‍රිකාවක් තිබේද?" en="Do you have an information leaflet about certification?" />
                            <div class="mt-2 flex flex-wrap gap-4">
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="radio" name="has_certification_leaflet" value="1" @checked(old('has_certification_leaflet') === '1') required>
                                    ඔව් / Yes
                                </label>
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="radio" name="has_certification_leaflet" value="0" @checked(old('has_certification_leaflet') === '0')>
                                    නැත / No
                                </label>
                            </div>
                            @error('has_certification_leaflet')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <x-bilingual-label si="ගොවිපල තුළ දී ස්වභාවික රබර් සකස් කිරීමේ කටයුතු සිදු කරයිද?" en="Is natural rubber processed on the farm?" />
                            <div class="mt-2 flex flex-wrap gap-4">
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="radio" name="processes_rubber_on_farm" value="1" data-toggle-group="processing" @checked(old('processes_rubber_on_farm') === '1') required>
                                    ඔව් / Yes
                                </label>
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="radio" name="processes_rubber_on_farm" value="0" data-toggle-group="processing" @checked(old('processes_rubber_on_farm', '0') === '0')>
                                    නැත / No
                                </label>
                            </div>
                            @error('processes_rubber_on_farm')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                        </div>

                        <div class="{{ old('processes_rubber_on_farm') === '1' ? '' : 'hidden' }}" data-panel="processing" data-show-when="1">
                            <x-bilingual-label si="ක්‍රියාවලිය සඳහා සැලැස්මක් තිබේද?" en="Is there a plan for the process?" />
                            <div class="mt-2 flex flex-wrap gap-4">
                                @foreach (\App\Models\Planter::processPlanOptions() as $value => $label)
                                    <label class="inline-flex items-center gap-2 text-sm">
                                        <input type="radio" name="has_process_plan" value="{{ $value }}" @checked(old('has_process_plan') === $value)>
                                        {{ $label['si'] }} / {{ $label['en'] }}
                                    </label>
                                @endforeach
                            </div>
                            @error('has_process_plan')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                        </div>
                    </section>

                    <section class="space-y-5">
                        <h2 class="border-b border-sand pb-2 text-base font-semibold text-forest-dark">
                            වැවිලි සමාගම / කාණ්ඩය / Plantation company or group
                        </h2>
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <x-bilingual-label si="වැවිලි සමාගම / වැවිලිකරුවන්ගේ / වැවිලි කාණ්ඩායමේ නම" en="Plantation company / planters / group name" for="group_name" />
                                <input id="group_name" name="group_name" type="text" value="{{ old('group_name') }}" class="input-field">
                                @error('group_name')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                            </div>
                            <div class="sm:col-span-2">
                                <x-bilingual-label si="ලිපිනය" en="Address" for="group_address" />
                                <textarea id="group_address" name="group_address" rows="2" class="input-field">{{ old('group_address') }}</textarea>
                                @error('group_address')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </section>

                    <x-registration-recaptcha :site-key="$recaptchaSiteKey ?? null" />

                    <button type="submit" class="btn-primary w-full py-3">
                        අනුමැතිය සඳහා ඉදිරිපත් කරන්න / Submit for approval
                    </button>
                </form>
            </div>

            <div class="overflow-hidden rounded-3xl border border-sand bg-paper shadow-[0_16px_40px_rgba(27,77,50,0.08)]">
                <div class="border-b border-sand px-6 py-5 sm:px-10">
                    <h2 class="text-lg font-semibold text-forest-dark">නොබැඳි ලියාපදිංචිය / Offline register</h2>
                    <p class="mt-1 text-sm text-muted">පෝරමය බාගත කර පුරවා, ස්කෑන් හෝ ඡායාරූපයක් උඩුගත කරන්න. / Download the form, complete it, then upload a scan or photo.</p>
                </div>

                <div class="space-y-6 p-6 sm:p-10">
                    <a href="{{ route('planter.register.form') }}" class="btn-secondary w-full py-3">
                        ලියාපදිංචි PDF බාගත කරන්න / Download registration PDF
                    </a>

                    <form method="POST" action="{{ route('planter.register.offline') }}" enctype="multipart/form-data" class="relative space-y-5">
                        @csrf
                        <x-registration-honeypot />

                        @if ($errors->has('registration'))
                            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                                {{ $errors->first('registration') }}
                            </div>
                        @elseif ($errors->has('application_document') || old('_offline'))
                            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <input type="hidden" name="_offline" value="1">

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <x-bilingual-label si="සම්පූර්ණ නම" en="Full name" for="offline_name" />
                                <input id="offline_name" name="name" type="text" value="{{ old('_offline') ? old('name') : '' }}" required class="input-field">
                            </div>
                            <div>
                                <x-bilingual-label si="ජා. හැ. අංකය" en="NIC number" for="offline_nic" />
                                <input id="offline_nic" name="nic" type="text" value="{{ old('_offline') ? old('nic') : '' }}" required class="input-field">
                            </div>
                            <div>
                                <x-bilingual-label si="දුරකථන" en="Phone" for="offline_phone" />
                                <input id="offline_phone" name="phone" type="text" value="{{ old('_offline') ? old('phone') : '' }}" required class="input-field">
                            </div>
                            <div>
                                <x-bilingual-label si="ඊ මේල් (විකල්ප)" en="Email (optional)" for="offline_email" />
                                <input id="offline_email" name="email" type="email" value="{{ old('_offline') ? old('email') : '' }}" class="input-field">
                            </div>
                            <x-district-rdo-fields
                                bilingual="true"
                                id-prefix="offline_"
                                :district-id="old('_offline') ? old('district_id') : null"
                                :rdo-division-id="old('_offline') ? old('rdo_division_id') : null"
                            />
                            <div class="sm:col-span-2">
                                <x-bilingual-label si="පුරවා අවසන් පෝරමය උඩුගත කරන්න" en="Upload completed form (PDF or image)" for="application_document" />
                                <input id="application_document" name="application_document" type="file" required accept=".pdf,image/png,image/jpeg,image/webp" class="input-field file:mr-3 file:rounded-lg file:border-0 file:bg-leaf file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-white">
                                <p class="mt-1 text-xs text-muted">Accepted: PDF, JPG, PNG, WEBP. Maximum 10 MB.</p>
                                @error('application_document')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <x-registration-recaptcha :site-key="$recaptchaSiteKey ?? null" />

                        <button type="submit" class="btn-primary w-full py-3">
                            උඩුගත කර අනුමැතියට ඉදිරිපත් කරන්න / Upload and submit for approval
                        </button>
                    </form>
                </div>
            </div>

            <p class="text-center text-sm text-muted">
                Already registered?
                <a href="{{ route('planter.login') }}" class="font-semibold text-leaf hover:text-forest">Sign in</a>
                <span class="mx-2 text-tan">·</span>
                Approved?
                <a href="{{ route('planter.password.create') }}" class="font-semibold text-leaf hover:text-forest">Create password</a>
            </p>

            <x-app-footer class="relative w-full" />
        </div>
    </div>
@endsection
