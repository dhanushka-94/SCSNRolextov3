@extends('layouts.app')

@section('title', $planter->name)
@section('heading', 'Planter details')
@section('subheading', $planter->identification_number)

@section('actions')
    @if ($planter->isPending())
        <a href="{{ route('admin.planters.approval-lobby') }}" class="btn-secondary">Back to lobby</a>
    @endif
    <a href="{{ route('admin.planters.edit', $planter) }}" class="btn-primary">Edit</a>
@endsection

@section('content')
    <article class="card mx-auto max-w-4xl overflow-hidden">
        <div class="flex flex-col gap-4 border-b border-sand bg-cream px-5 py-6 sm:flex-row sm:items-center sm:justify-between sm:px-8">
            <div>
                <p class="font-mono text-sm font-semibold text-forest">{{ $planter->identification_number }}</p>
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

        <div class="space-y-8 p-5 sm:p-8">
            <section>
                <h3 class="border-b border-sand pb-2 text-sm font-semibold text-forest-dark">Registration</h3>
                <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted">SCSNR ID</dt>
                        <dd class="mt-1 font-mono text-sm">{{ $planter->identification_number }}</dd>
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
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Submitted</dt>
                        <dd class="mt-1 text-sm">{{ sl_datetime($planter->created_at) }}</dd>
                    </div>
                </dl>
            </section>

            <section>
                <h3 class="border-b border-sand pb-2 text-sm font-semibold text-forest-dark">Applicant details</h3>
                <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted">NIC number</dt>
                        <dd class="mt-1 text-sm">{{ $planter->nic }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted">District</dt>
                        <dd class="mt-1 text-sm">{{ $planter->district ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted">RDD officer division</dt>
                        <dd class="mt-1 text-sm">{{ $planter->rdd_division ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Phone</dt>
                        <dd class="mt-1 text-sm">{{ $planter->phone }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Email</dt>
                        <dd class="mt-1 text-sm">{{ $planter->email ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted">WhatsApp</dt>
                        <dd class="mt-1 text-sm">{{ $planter->whatsapp ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Fax</dt>
                        <dd class="mt-1 text-sm">{{ $planter->fax ?: '—' }}</dd>
                    </div>
                </dl>
            </section>

            <section>
                <h3 class="border-b border-sand pb-2 text-sm font-semibold text-forest-dark">Farm information</h3>
                <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Farm name</dt>
                        <dd class="mt-1 text-sm">{{ $planter->farm_name ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Nature of business</dt>
                        <dd class="mt-1 text-sm">{{ $planter->businessTypeLabel() }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Farm address</dt>
                        <dd class="mt-1 text-sm">{{ $planter->address ?: '—' }}</dd>
                    </div>
                </dl>
            </section>

            <section>
                <h3 class="border-b border-sand pb-2 text-sm font-semibold text-forest-dark">Certification history</h3>
                <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Already certified</dt>
                        <dd class="mt-1 text-sm">{{ $planter->yesNoLabel($planter->already_certified) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Certification standard</dt>
                        <dd class="mt-1 text-sm">{{ $planter->certification_standard ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Previously rejected or suspended</dt>
                        <dd class="mt-1 text-sm">{{ $planter->yesNoLabel($planter->certification_rejected_or_suspended) }}</dd>
                    </div>
                    @if ($planter->certification_issue_reason)
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Reason</dt>
                            <dd class="mt-1 text-sm">{{ $planter->certification_issue_reason }}</dd>
                        </div>
                    @endif
                </dl>
            </section>

            <section>
                <h3 class="border-b border-sand pb-2 text-sm font-semibold text-forest-dark">Products for certification</h3>
                <div class="mt-4 text-sm">
                    @if (! empty($planter->crops_products))
                        <ol class="list-decimal space-y-1 pl-5">
                            @foreach ($planter->crops_products as $product)
                                <li>
                                    @if (is_array($product))
                                        {{ collect([$product['name'] ?? null, isset($product['area']) ? $product['area'].' ha' : null, isset($product['quantity']) ? $product['quantity'].' kg' : null])->filter()->implode(' · ') }}
                                    @else
                                        {{ $product }}
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    @else
                        —
                    @endif
                </div>
            </section>

            <section>
                <h3 class="border-b border-sand pb-2 text-sm font-semibold text-forest-dark">Awareness and processing</h3>
                <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Aware of certification</dt>
                        <dd class="mt-1 text-sm">{{ $planter->yesNoLabel($planter->aware_of_certification) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Information leaflet</dt>
                        <dd class="mt-1 text-sm">{{ $planter->yesNoLabel($planter->has_certification_leaflet) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted">On-farm processing</dt>
                        <dd class="mt-1 text-sm">{{ $planter->yesNoLabel($planter->processes_rubber_on_farm) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Process plan</dt>
                        <dd class="mt-1 text-sm">{{ $planter->processPlanLabel() }}</dd>
                    </div>
                </dl>
            </section>

            <section>
                <h3 class="border-b border-sand pb-2 text-sm font-semibold text-forest-dark">Plantation company or group</h3>
                <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Group name</dt>
                        <dd class="mt-1 text-sm">{{ $planter->group_name ?: '—' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Group address</dt>
                        <dd class="mt-1 text-sm">{{ $planter->group_address ?: '—' }}</dd>
                    </div>
                </dl>
            </section>

            <section>
                <h3 class="border-b border-sand pb-2 text-sm font-semibold text-forest-dark">Documents</h3>
                <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Application form</dt>
                        <dd class="mt-1 text-sm">
                            @if ($planter->hasApplicationDocument())
                                <a href="{{ route('admin.planters.document', $planter) }}" class="font-semibold text-leaf hover:text-forest">Download submitted form</a>
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Prior certificate</dt>
                        <dd class="mt-1 text-sm">
                            @if ($planter->hasPriorCertificateDocument())
                                <a href="{{ route('admin.planters.certificate-document', $planter) }}" class="font-semibold text-leaf hover:text-forest">Download certificate</a>
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                </dl>
            </section>

            <section>
                <h3 class="border-b border-sand pb-2 text-sm font-semibold text-forest-dark">Review</h3>
                <dl class="mt-4 grid gap-4 sm:grid-cols-2">
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
            </section>
        </div>

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
