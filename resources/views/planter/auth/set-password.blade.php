@extends('layouts.guest')

@section('title', 'Create password')

@section('content')
    <div class="relative flex min-h-screen flex-col items-center justify-center px-4 py-8 sm:py-12">
        <div class="w-full max-w-lg overflow-hidden rounded-3xl border border-sand bg-paper shadow-[0_24px_60px_rgba(27,77,50,0.12)]">
            <div class="border-b border-sand bg-forest-dark px-6 py-8 text-white">
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-tan">Planter Portal</p>
                <h1 class="mt-2 text-2xl font-semibold">Create password</h1>
                <p class="mt-2 text-sm text-sand/80">Use your SCSNR registration number and NIC after approval.</p>
            </div>

            <form method="POST" action="{{ route('planter.password.store') }}" class="space-y-5 p-6 sm:p-8">
                @csrf

                @if ($errors->any())
                    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div>
                    <x-bilingual-label si="SCSNR ලියාපදිංචි අංකය" en="SCSNR registration number" for="identification_number" />
                    <input id="identification_number" name="identification_number" type="text" value="{{ old('identification_number') }}" required class="input-field font-mono" placeholder="SCSNR/Ad/Kr/2026/0001">
                    @error('identification_number')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <x-bilingual-label si="ජා. හැ. අංකය" en="NIC number" for="nic" />
                    <input id="nic" name="nic" type="text" value="{{ old('nic') }}" required class="input-field">
                    @error('nic')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password" class="label-field">New password</label>
                    <div class="relative">
                        <input id="password" name="password" type="password" required autocomplete="new-password" class="input-field pr-12">
                        <button type="button" data-password-toggle="password" class="absolute inset-y-0 right-0 px-3 text-xs font-semibold text-bark">Show</button>
                    </div>
                    @error('password')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password_confirmation" class="label-field">Confirm password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="input-field">
                </div>

                <button type="submit" class="btn-primary w-full py-3">Save password</button>
                <p class="text-center text-sm text-muted">
                    <a href="{{ route('planter.login') }}" class="font-semibold text-leaf hover:text-forest">Back to login</a>
                </p>
            </form>
        </div>
        <x-app-footer class="relative mt-6 w-full max-w-lg" />
    </div>
@endsection
