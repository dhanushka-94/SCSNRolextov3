@extends('layouts.guest')

@section('title', 'Planter Registration')

@section('content')
    <div class="relative flex min-h-screen flex-col items-center justify-center px-4 py-8 sm:py-12">
        <div class="w-full max-w-3xl space-y-6">
            <div class="overflow-hidden rounded-3xl border border-sand bg-paper shadow-[0_24px_60px_rgba(27,77,50,0.12)]">
                <div class="border-b border-sand bg-forest-dark px-6 py-8 text-white sm:px-10">
                    <div class="flex items-center gap-4">
                        <img src="{{ asset('SCSNR-logo.png') }}" alt="{{ config('app.short_name') }} logo" class="h-16 w-16 rounded-full bg-white object-contain p-1">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-tan">Planter Portal</p>
                            <h1 class="mt-1 text-2xl font-semibold">Rubber Planter Registration</h1>
                            <p class="mt-1 text-sm text-sand/80">No password is needed now. After approval you will create one with your temporary ID.</p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('planter.register.store') }}" class="space-y-5 p-6 sm:p-10">
                    @csrf

                    @if ($errors->any() && ! $errors->has('application_document'))
                        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="name" class="label-field">Full name</label>
                            <input id="name" name="name" type="text" value="{{ old('name') }}" required class="input-field">
                            @error('name')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="nic" class="label-field">NIC number</label>
                            <input id="nic" name="nic" type="text" value="{{ old('nic') }}" required class="input-field">
                            @error('nic')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="phone" class="label-field">Phone</label>
                            <input id="phone" name="phone" type="text" value="{{ old('phone') }}" required class="input-field">
                            @error('phone')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="email" class="label-field">Email</label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" required class="input-field">
                            @error('email')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="district" class="label-field">District</label>
                            <select id="district" name="district" required class="input-field">
                                <option value="">Select district</option>
                                @foreach (\App\Models\Planter::districts() as $district)
                                    <option value="{{ $district }}" @selected(old('district') === $district)>{{ $district }}</option>
                                @endforeach
                            </select>
                            @error('district')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="address" class="label-field">Address</label>
                            <textarea id="address" name="address" rows="3" required class="input-field">{{ old('address') }}</textarea>
                            @error('address')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <button type="submit" class="btn-primary w-full py-3">Submit for approval</button>
                </form>
            </div>

            <div class="overflow-hidden rounded-3xl border border-sand bg-paper shadow-[0_16px_40px_rgba(27,77,50,0.08)]">
                <div class="border-b border-sand px-6 py-5 sm:px-10">
                    <h2 class="text-lg font-semibold text-forest-dark">Offline register</h2>
                    <p class="mt-1 text-sm text-muted">Download the paper form, complete it by hand, then upload a scan or photo. It will enter the same approval process.</p>
                </div>

                <div class="space-y-6 p-6 sm:p-10">
                    <a href="{{ route('planter.register.form') }}" class="btn-secondary w-full py-3">
                        Download registration PDF
                    </a>

                    <form method="POST" action="{{ route('planter.register.offline') }}" enctype="multipart/form-data" class="space-y-5">
                        @csrf

                        @if ($errors->has('application_document') || old('_offline'))
                            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <input type="hidden" name="_offline" value="1">

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label for="offline_name" class="label-field">Full name</label>
                                <input id="offline_name" name="name" type="text" value="{{ old('_offline') ? old('name') : '' }}" required class="input-field">
                            </div>
                            <div>
                                <label for="offline_nic" class="label-field">NIC number</label>
                                <input id="offline_nic" name="nic" type="text" value="{{ old('_offline') ? old('nic') : '' }}" required class="input-field">
                            </div>
                            <div>
                                <label for="offline_phone" class="label-field">Phone</label>
                                <input id="offline_phone" name="phone" type="text" value="{{ old('_offline') ? old('phone') : '' }}" required class="input-field">
                            </div>
                            <div>
                                <label for="offline_email" class="label-field">Email (optional)</label>
                                <input id="offline_email" name="email" type="email" value="{{ old('_offline') ? old('email') : '' }}" class="input-field">
                            </div>
                            <div class="sm:col-span-2">
                                <label for="application_document" class="label-field">Upload completed form (PDF or image)</label>
                                <input id="application_document" name="application_document" type="file" required accept=".pdf,image/png,image/jpeg,image/webp" class="input-field file:mr-3 file:rounded-lg file:border-0 file:bg-leaf file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-white">
                                <p class="mt-1 text-xs text-muted">Accepted: PDF, JPG, PNG, WEBP. Maximum 10 MB.</p>
                                @error('application_document')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <button type="submit" class="btn-primary w-full py-3">Upload and submit for approval</button>
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
