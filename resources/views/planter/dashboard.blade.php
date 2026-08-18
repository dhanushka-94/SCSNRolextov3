@extends('layouts.planter')

@section('title', 'Planter dashboard')
@section('heading', 'Registration status')

@section('content')
    <article class="card overflow-hidden">
        <div class="border-b border-sand bg-cream px-5 py-6 sm:px-8">
            @if ($planter->isApproved() && $planter->identification_number)
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-muted">SCSNR identification number</p>
                <p class="mt-2 font-mono text-2xl font-semibold tracking-wide text-forest-dark sm:text-3xl">{{ $planter->identification_number }}</p>
                <p class="mt-2 text-sm text-muted">Temporary ID {{ $planter->temporary_id }} is retained for application history.</p>
            @else
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-muted">Temporary unique ID</p>
                <p class="mt-2 font-mono text-2xl font-semibold tracking-wide text-forest-dark sm:text-3xl">{{ $planter->temporary_id }}</p>
                <p class="mt-2 text-sm text-muted">Keep this ID for all SCSNR correspondence until a permanent number is issued.</p>
            @endif
        </div>

        <div class="space-y-5 p-5 sm:p-8">
            @if ($planter->isPending())
                <div class="rounded-2xl border border-tan/50 bg-sand/40 px-4 py-4">
                    <p class="font-semibold text-bark-dark">Awaiting approval</p>
                    <p class="mt-1 text-sm text-muted">Your planter registration has been submitted and is in the approval process. You will be able to continue once an administrator reviews it.</p>
                </div>
            @elseif ($planter->isApproved())
                <div class="rounded-2xl border border-leaf/20 bg-leaf/10 px-4 py-4">
                    <p class="font-semibold text-forest">Registration approved</p>
                    <p class="mt-1 text-sm text-muted">Your planter account is active. Approved {{ sl_datetime($planter->approved_at) }}.</p>
                </div>

                @if ($planter->identification_number)
                    <div class="flex flex-col gap-4 rounded-2xl border border-sand bg-cream p-4 sm:flex-row sm:items-center">
                        <img src="{{ route('planter.profile.qr') }}" alt="Planter QR code" class="h-28 w-28 rounded-xl bg-white p-2">
                        <div>
                            <p class="font-semibold text-forest-dark">Your planter QR code</p>
                            <p class="mt-1 text-sm text-muted">Scan this code to read your unique identification number. Download a high-quality copy from your profile.</p>
                            <a href="{{ route('planter.profile') }}" class="mt-3 inline-flex font-semibold text-leaf hover:text-forest">Open profile and download QR</a>
                        </div>
                    </div>
                @endif
            @else
                <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-4">
                    <p class="font-semibold text-red-800">Registration rejected</p>
                    <p class="mt-1 text-sm text-red-800/80">{{ $planter->rejection_reason ?: 'Please contact SCSNR administration for more information.' }}</p>
                </div>
            @endif

            <dl class="grid gap-5 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Name</dt>
                    <dd class="mt-1 text-sm">{{ $planter->name }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-muted">NIC</dt>
                    <dd class="mt-1 text-sm">{{ $planter->nic }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Email</dt>
                    <dd class="mt-1 text-sm">{{ $planter->email }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Phone</dt>
                    <dd class="mt-1 text-sm">{{ $planter->phone }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-muted">District</dt>
                    <dd class="mt-1 text-sm">{{ $planter->district }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Address</dt>
                    <dd class="mt-1 text-sm">{{ $planter->address }}</dd>
                </div>
            </dl>
        </div>
    </article>
@endsection
