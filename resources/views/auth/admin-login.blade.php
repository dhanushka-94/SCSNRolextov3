@extends('layouts.guest')

@section('title', 'Administrator Portal')

@section('content')
    <div class="relative flex min-h-screen flex-col items-center justify-center px-4 py-8 sm:py-12">
        <div class="grid w-full max-w-5xl overflow-hidden rounded-3xl border border-sand bg-paper shadow-[0_24px_60px_rgba(27,77,50,0.12)] lg:grid-cols-[1.05fr_0.95fr]">
            <div class="relative bg-forest-dark px-8 py-10 text-white sm:px-12 sm:py-12 lg:flex lg:flex-col lg:justify-between">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(76,175,103,0.22),transparent_42%)]"></div>
                <div class="relative">
                    <img src="{{ asset('SCSNR-logo.png') }}" alt="{{ config('app.short_name') }} logo" class="h-20 w-20 rounded-full bg-white object-contain p-1 shadow-lg sm:h-24 sm:w-24">
                    <p class="mt-8 inline-flex items-center rounded-full border border-tan/40 bg-white/5 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-tan">
                        Administrator Portal
                    </p>
                    <p class="mt-5 text-sm font-semibold tracking-[0.22em] text-tan">{{ config('app.short_name') }}</p>
                    <h1 class="mt-2 max-w-md text-2xl font-semibold leading-snug sm:text-3xl">
                        {{ config('app.full_name') }}
                    </h1>
                </div>
                <div class="relative mt-10 lg:mt-0">
                    <x-governing-logos size="sm" class="!justify-start gap-3" />
                    <p class="mt-3 max-w-sm text-xs leading-5 text-tan">
                        {{ config('app.powered_by') }}
                    </p>
                </div>
            </div>

            <div class="px-6 py-10 sm:px-10 lg:px-12">
                <div class="mb-8 lg:hidden">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-leaf">Administrator Portal</p>
                    <p class="mt-2 text-xs font-semibold tracking-[0.18em] text-bark">{{ config('app.short_name') }}</p>
                    <h2 class="mt-1 text-xl font-semibold leading-snug text-forest-dark">{{ config('app.full_name') }}</h2>
                </div>

                <h2 class="hidden text-2xl font-semibold text-forest-dark lg:block">Administrator Portal</h2>
                <p class="mt-1 hidden text-sm text-muted lg:block">Sign in with your administrator account.</p>

                @if ($errors->any())
                    <div class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.login.store') }}" class="mt-8 space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="label-field">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="input-field">
                    </div>

                    <div>
                        <label for="password" class="label-field">Password</label>
                        <div class="relative">
                            <input id="password" name="password" type="password" required autocomplete="current-password" class="input-field pr-12">
                            <button type="button" data-password-toggle="password" class="absolute inset-y-0 right-0 px-3 text-xs font-semibold text-bark">Show</button>
                        </div>
                    </div>

                    <label class="flex items-center gap-2 text-sm text-muted">
                        <input type="checkbox" name="remember" value="1" class="rounded border-line text-leaf focus:ring-leaf">
                        Remember this device
                    </label>

                    <button type="submit" class="btn-primary w-full py-3">Enter portal</button>
                </form>
            </div>
        </div>

        <x-app-footer class="relative mt-6 w-full max-w-5xl" />
    </div>
@endsection
