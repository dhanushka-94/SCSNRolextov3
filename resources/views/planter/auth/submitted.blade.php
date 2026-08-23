@extends('layouts.guest')

@section('title', 'Registration submitted')

@section('content')
    <div class="relative flex min-h-screen flex-col items-center justify-center px-4 py-8 sm:py-12">
        <div class="w-full max-w-xl overflow-hidden rounded-3xl border border-sand bg-paper p-8 text-center shadow-[0_24px_60px_rgba(27,77,50,0.12)] sm:p-10">
            <img src="{{ asset('SCSNR-logo.png') }}" alt="{{ config('app.short_name') }}" class="mx-auto h-16 w-16 rounded-full bg-white object-contain p-1">
            <p class="mt-6 text-[11px] font-semibold uppercase tracking-[0.18em] text-leaf">Approval process</p>
            <h1 class="mt-2 text-2xl font-semibold text-forest-dark">Registration submitted</h1>
            <p class="mt-3 text-sm text-muted">Your SCSNR registration number has been issued. Keep this number. After approval, use it with your NIC to create a password and sign in.</p>
            <p class="mt-6 font-mono text-xl font-semibold tracking-wide text-forest sm:text-2xl">{{ $identificationNumber }}</p>
            <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center">
                <a href="{{ route('planter.login') }}" class="btn-secondary">Go to login</a>
                <a href="{{ route('planter.password.create') }}" class="btn-primary">Create password after approval</a>
            </div>
        </div>
        <x-app-footer class="relative mt-6 w-full max-w-xl" />
    </div>
@endsection
