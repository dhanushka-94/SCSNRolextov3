@extends('layouts.guest')

@section('title', 'Choose registration type')

@section('content')
    <div class="relative flex min-h-screen flex-col items-center justify-center px-4 py-8 sm:py-12">
        <div class="w-full max-w-5xl space-y-8">
            <div class="overflow-hidden rounded-3xl border border-sand bg-forest-dark px-6 py-8 text-white shadow-[0_24px_60px_rgba(27,77,50,0.12)] sm:px-10">
                <div class="flex items-center gap-4">
                    <img src="{{ asset('SPPNR-logo.png') }}" alt="{{ config('app.short_name') }} logo" class="h-16 w-16 rounded-full bg-white object-contain p-1">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-tan">Planter Portal</p>
                        <h1 class="mt-1 text-xl font-semibold sm:text-2xl">Choose your registration type</h1>
                        <p class="mt-1 text-sm text-sand/85">ඔබගේ ලියාපදිංචි වර්ගය තෝරන්න · உங்கள் பதிவு வகையைத் தேர்ந்தெடுக்கவும்</p>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                @foreach ($categories as $key => $category)
                    <a
                        href="{{ route('planter.register.apply', $key) }}"
                        class="group flex flex-col rounded-3xl border border-sand bg-paper p-6 shadow-[0_16px_40px_rgba(27,77,50,0.08)] transition hover:-translate-y-0.5 hover:border-leaf hover:shadow-[0_20px_48px_rgba(27,77,50,0.16)] focus:outline-none focus-visible:ring-2 focus-visible:ring-leaf sm:p-7"
                        data-registration-category="{{ $key }}"
                    >
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-leaf/15 text-forest">
                            @if ($key === 'solo')
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0"/></svg>
                            @elseif ($key === 'group')
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"/></svg>
                            @else
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
                            @endif
                        </span>

                        <h2 class="mt-5 text-lg font-semibold text-forest-dark">{{ $category['en'] }}</h2>
                        <p class="mt-1 text-sm font-medium text-bark">{{ $category['si'] }}</p>
                        <p class="text-sm font-medium text-bark">{{ $category['ta'] }}</p>

                        <p class="mt-4 text-sm text-muted">{{ $category['description_en'] }}</p>
                        <p class="mt-1 text-xs text-muted">{{ $category['description_si'] }}</p>
                        <p class="mt-1 text-xs text-muted">{{ $category['description_ta'] }}</p>

                        <span class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-leaf group-hover:text-forest">
                            Continue · ඉදිරියට · தொடரவும்
                            <svg class="h-4 w-4 transition group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                        </span>
                    </a>
                @endforeach
            </div>

            <p class="text-center text-sm text-muted">
                Already registered?
                <a href="{{ route('planter.login') }}" class="font-semibold text-leaf hover:text-forest">Sign in · ඇතුල් වන්න · உள்நுழைக</a>
            </p>
        </div>
    </div>
@endsection
