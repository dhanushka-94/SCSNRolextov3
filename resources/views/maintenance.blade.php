@extends('layouts.guest')

@section('title', 'Under maintenance')

@section('content')
    <div class="relative flex min-h-screen flex-col items-center justify-center px-4 py-8">
        <div class="w-full max-w-lg rounded-3xl border border-sand bg-paper p-8 text-center shadow-[0_24px_60px_rgba(27,77,50,0.12)] sm:p-10">
            <img src="{{ asset('SPPNR-logo.png') }}" alt="{{ config('app.short_name') }} logo" class="mx-auto h-20 w-20 rounded-full bg-white object-contain p-1 shadow">
            <p class="mt-6 inline-flex rounded-full bg-tan/30 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-forest-dark">Scheduled maintenance</p>
            <h1 class="mt-4 text-2xl font-semibold text-forest-dark">We’ll be back soon</h1>
            <p class="mt-3 text-sm leading-6 text-muted">
                {{ $message ?: config('app.short_name').' is temporarily unavailable while we carry out maintenance. Please try again later.' }}
            </p>

            @if (auth('web')->check())
                <form method="POST" action="{{ route('admin.logout') }}" class="mt-6">
                    @csrf
                    <button type="submit" class="btn-secondary">Sign out</button>
                </form>
            @else
                <a href="{{ route('admin.login') }}" class="mt-6 inline-block text-xs font-semibold text-leaf hover:text-forest">Administrator sign in</a>
            @endif
        </div>
        <x-app-footer class="relative mt-6 w-full max-w-lg" />
    </div>
@endsection
