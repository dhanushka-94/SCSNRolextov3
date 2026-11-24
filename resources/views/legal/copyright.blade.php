@extends('layouts.partner')

@section('content')
    <div class="min-h-screen bg-cream">
        <header class="border-b border-sand bg-forest-dark text-white">
            <div class="mx-auto flex max-w-3xl items-center justify-between gap-4 px-4 py-4 sm:px-6">
                <a href="{{ route('partner.home') }}" class="flex items-center gap-3">
                    <img src="{{ asset('SPPNR-logo.png') }}" alt="" class="h-10 w-10 rounded-full bg-white object-contain p-1">
                    <span class="text-sm font-semibold">{{ config('app.short_name') }}</span>
                </a>
                <a href="{{ route('partner.home') }}" class="text-sm font-semibold text-sand hover:text-white">Back to home</a>
            </div>
        </header>

        <main class="mx-auto max-w-3xl px-4 py-10 sm:px-6">
            @include('legal.partials.copyright-notice')
        </main>

        <x-app-footer class="mx-auto max-w-3xl px-4 py-8 sm:px-6" />
    </div>
@endsection
