@extends('layouts.app')

@section('title', $planter->name)
@section('heading', 'Planter details')
@section('subheading', $planter->identification_number ?: $planter->temporary_id)

@section('actions')
    @if ($planter->isPending())
        <a href="{{ route('admin.planters.approval-lobby') }}" class="btn-secondary">Back to lobby</a>
    @endif
    <a href="{{ route('admin.planters.edit', $planter) }}" class="btn-primary">Edit</a>
@endsection

@section('content')
    <article class="card mx-auto max-w-3xl overflow-hidden">
        <div class="flex flex-col gap-4 border-b border-sand bg-cream px-5 py-6 sm:flex-row sm:items-center sm:justify-between sm:px-8">
            <div>
                @if ($planter->identification_number)
                    <p class="font-mono text-sm font-semibold text-forest">{{ $planter->identification_number }}</p>
                    <p class="mt-1 text-xs text-muted">Temporary ID {{ $planter->temporary_id }}</p>
                @else
                    <p class="font-mono text-sm font-semibold text-forest">{{ $planter->temporary_id }}</p>
                @endif
                <h2 class="mt-1 text-xl font-semibold text-forest-dark">{{ $planter->name }}</h2>
                <p class="text-sm text-muted">{{ \App\Models\Planter::statuses()[$planter->status] }}</p>
            </div>
            @if ($planter->isPending())
                <div class="flex flex-wrap gap-2">
                    <form method="POST" action="{{ route('admin.planters.approve', $planter) }}">
                        @csrf
                        <button type="submit" class="btn-primary">Approve</button>
                    </form>
                </div>
            @elseif ($planter->isApproved() && $planter->identification_number)
                <img src="{{ route('admin.planters.qr', $planter) }}" alt="Planter QR code" class="h-28 w-28 rounded-xl bg-white p-2 shadow-sm">
            @endif
        </div>

        <dl class="grid gap-5 p-5 sm:grid-cols-2 sm:p-8">
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Identification number</dt>
                <dd class="mt-1 font-mono text-sm">{{ $planter->identification_number ?: 'Issued on approval' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Registration type</dt>
                <dd class="mt-1 text-sm">{{ \App\Models\Planter::registrationTypes()[$planter->registration_type] ?? 'Online' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Password</dt>
                <dd class="mt-1 text-sm">{{ $planter->hasPassword() ? 'Created' : 'Not created yet' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-muted">NIC</dt>
                <dd class="mt-1 text-sm">{{ $planter->nic }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Email</dt>
                <dd class="mt-1 text-sm">{{ $planter->email ?: '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Phone</dt>
                <dd class="mt-1 text-sm">{{ $planter->phone }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-muted">District</dt>
                <dd class="mt-1 text-sm">{{ $planter->district ?: '—' }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Address</dt>
                <dd class="mt-1 text-sm">{{ $planter->address ?: '—' }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Uploaded form</dt>
                <dd class="mt-1 text-sm">
                    @if ($planter->hasApplicationDocument())
                        <a href="{{ route('admin.planters.document', $planter) }}" class="font-semibold text-leaf hover:text-forest">Download submitted PDF / image</a>
                    @else
                        —
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Submitted</dt>
                <dd class="mt-1 text-sm">{{ sl_datetime($planter->created_at) }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Reviewed</dt>
                <dd class="mt-1 text-sm">{{ sl_datetime($planter->approved_at) }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Reviewed by</dt>
                <dd class="mt-1 text-sm">{{ $planter->approver?->name ?? '—' }}</dd>
            </div>
            @if ($planter->rejection_reason)
                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Rejection reason</dt>
                    <dd class="mt-1 text-sm">{{ $planter->rejection_reason }}</dd>
                </div>
            @endif
        </dl>

        @if ($planter->isPending() || $planter->isApproved())
            <form method="POST" action="{{ route('admin.planters.reject', $planter) }}" class="border-t border-sand px-5 py-6 sm:px-8">
                @csrf
                <label for="rejection_reason" class="label-field">Reject with reason</label>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <input id="rejection_reason" name="rejection_reason" type="text" required class="input-field" placeholder="Reason for rejection">
                    <button type="submit" class="btn-danger shrink-0">Reject</button>
                </div>
                @error('rejection_reason')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </form>
        @endif
    </article>
@endsection
