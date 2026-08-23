@extends('layouts.planter')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <article class="card p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-muted">Registration</p>
            <p class="mt-2 text-lg font-semibold text-forest-dark">
                {{ \App\Models\Planter::statuses()[$planter->status] }}
            </p>
            <p class="mt-1 text-sm text-muted">Submitted {{ sl_datetime($planter->created_at) }}</p>
        </article>

        <article class="card p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-muted">SCSNR ID</p>
            <p class="mt-2 font-mono text-sm font-semibold text-forest">{{ $planter->identification_number }}</p>
            <p class="mt-1 text-sm text-muted">{{ $planter->district ?: 'District not set' }}</p>
        </article>

        @if ($planter->isApproved())
            <article class="card p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-muted">Audit stage</p>
                <p class="mt-2 text-lg font-semibold text-forest-dark">
                    {{ \App\Models\Planter::auditStages()[$planter->currentAuditStageKey()]['label'] ?? 'Not started' }}
                </p>
                @if ($planter->audit_status_updated_at)
                    <p class="mt-1 text-sm text-muted">Updated {{ sl_datetime($planter->audit_status_updated_at) }}</p>
                @endif
            </article>
        @else
            <article class="card p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-muted">Next step</p>
                <p class="mt-2 text-sm text-muted">
                    @if ($planter->isPending())
                        Wait for approval, then create your password to sign in.
                    @else
                        Contact SCSNR administration if you need assistance.
                    @endif
                </p>
            </article>
        @endif
    </div>

    @if ($planter->isPending())
        <article class="card mt-6 overflow-hidden">
            <div class="border-b border-sand bg-cream px-5 py-5 sm:px-8">
                <h2 class="text-lg font-semibold text-forest-dark">Awaiting approval</h2>
                <p class="mt-1 text-sm text-muted">Your application is in the review queue. Use your SCSNR ID for correspondence.</p>
            </div>
            <dl class="grid gap-5 p-5 sm:grid-cols-2 sm:p-8">
                <div><dt class="text-xs font-semibold uppercase tracking-wide text-muted">Name</dt><dd class="mt-1 text-sm">{{ $planter->name }}</dd></div>
                <div><dt class="text-xs font-semibold uppercase tracking-wide text-muted">NIC</dt><dd class="mt-1 text-sm">{{ $planter->nic }}</dd></div>
                <div><dt class="text-xs font-semibold uppercase tracking-wide text-muted">Phone</dt><dd class="mt-1 text-sm">{{ $planter->phone }}</dd></div>
                <div><dt class="text-xs font-semibold uppercase tracking-wide text-muted">Email</dt><dd class="mt-1 text-sm">{{ $planter->email ?: '—' }}</dd></div>
            </dl>
        </article>
    @elseif ($planter->isRejected())
        <article class="card mt-6 overflow-hidden">
            <div class="border-b border-red-200 bg-red-50 px-5 py-5 sm:px-8">
                <h2 class="text-lg font-semibold text-red-800">Registration rejected</h2>
                <p class="mt-1 text-sm text-red-800/80">{{ $planter->rejection_reason ?: 'Please contact SCSNR administration for more information.' }}</p>
            </div>
        </article>
    @else
        <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.2fr)]">
            <article class="card overflow-hidden">
                <div class="border-b border-sand bg-cream px-5 py-5 sm:px-8">
                    <h2 class="text-lg font-semibold text-forest-dark">Your QR code</h2>
                    <p class="mt-1 text-sm text-muted">Share or print this code with your SCSNR identification number.</p>
                </div>
                <div class="flex flex-col items-center gap-4 p-6 sm:p-8">
                    @if ($planter->identification_number)
                        <img src="{{ route('planter.profile.qr') }}" alt="Planter QR code" class="h-36 w-36 rounded-xl bg-white p-2 shadow-sm ring-1 ring-sand">
                    @endif
                    <div class="flex w-full flex-col gap-2 sm:flex-row sm:justify-center">
                        <a href="{{ route('planter.profile') }}" class="btn-primary">Open profile</a>
                        <a href="{{ route('planter.profile.qr.download', 'png') }}" class="btn-secondary">Download QR</a>
                    </div>
                </div>
            </article>

            <article class="card overflow-hidden">
                <div class="border-b border-sand bg-cream px-5 py-5 sm:px-8">
                    <h2 class="text-lg font-semibold text-forest-dark">Account summary</h2>
                    <p class="mt-1 text-sm text-muted">Approved {{ sl_datetime($planter->approved_at) }}</p>
                </div>
                <dl class="grid gap-4 p-5 sm:grid-cols-2 sm:p-8">
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-muted">Farm</dt><dd class="mt-1 text-sm">{{ $planter->farm_name ?: '—' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-muted">Business type</dt><dd class="mt-1 text-sm">{{ $planter->businessTypeLabel() }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-xs font-semibold uppercase tracking-wide text-muted">Address</dt><dd class="mt-1 text-sm">{{ $planter->address ?: '—' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-muted">Phone</dt><dd class="mt-1 text-sm">{{ $planter->phone }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-muted">Email</dt><dd class="mt-1 text-sm">{{ $planter->email ?: '—' }}</dd></div>
                </dl>
            </article>
        </div>

        <x-planter-audit-tree :planter="$planter" class="mt-6" />
    @endif
@endsection
