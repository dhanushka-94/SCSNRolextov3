@extends('layouts.planter')

@section('title', 'My profile')
@section('heading', 'Planter profile')

@section('content')
    @if ($planter->isApproved() && $planter->identification_number)
        <article class="card mb-5 overflow-hidden">
            <div class="grid gap-6 p-5 sm:grid-cols-[auto_1fr] sm:p-8">
                <div class="rounded-2xl border border-sand bg-white p-3">
                    <img src="{{ route('planter.profile.qr') }}" alt="Planter QR code" class="h-40 w-40">
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-muted">SCSNR identification number</p>
                    <p class="mt-2 font-mono text-xl font-semibold tracking-wide text-forest-dark sm:text-2xl">{{ $planter->identification_number }}</p>
                    <p class="mt-2 text-sm text-muted">This unique ID is encoded in your QR code. Keep a printed copy with plantation records.</p>
                    <div class="mt-5 flex flex-wrap gap-2">
                        <a href="{{ route('planter.profile.qr.download', 'png') }}" class="btn-primary">Download high quality PNG</a>
                        <a href="{{ route('planter.profile.qr.download', 'svg') }}" class="btn-secondary">Download vector SVG</a>
                    </div>
                </div>
            </div>
        </article>

        <x-planter-audit-tree :planter="$planter" class="mb-5" />
    @endif

    <article class="card overflow-hidden">
        <div class="border-b border-sand bg-cream px-5 py-5 sm:px-8">
            <h2 class="text-lg font-semibold text-forest-dark">Update profile</h2>
            <p class="mt-1 text-sm text-muted">NIC and identification numbers cannot be changed from this portal.</p>
        </div>

        <form method="POST" action="{{ route('planter.profile.update') }}" class="grid gap-5 p-5 sm:grid-cols-2 sm:p-8">
            @csrf
            @method('PUT')

            <div class="sm:col-span-2">
                <label for="name" class="label-field">Full name</label>
                <input id="name" name="name" type="text" value="{{ old('name', $planter->name) }}" required class="input-field">
                @error('name')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <p class="label-field">NIC number</p>
                <p class="mt-2 text-sm text-ink">{{ $planter->nic }}</p>
            </div>
            <div>
                <p class="label-field">SCSNR registration number</p>
                <p class="mt-2 font-mono text-sm text-ink">{{ $planter->identification_number }}</p>
            </div>
            <div>
                <label for="email" class="label-field">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email', $planter->email) }}" class="input-field">
                @error('email')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="phone" class="label-field">Phone</label>
                <input id="phone" name="phone" type="text" value="{{ old('phone', $planter->phone) }}" required class="input-field">
                @error('phone')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <x-district-rdo-fields
                :district-id="$planter->district_id"
                :rdo-division-id="$planter->rdo_division_id"
            />
            <div class="sm:col-span-2">
                <label for="address" class="label-field">Address</label>
                <textarea id="address" name="address" rows="3" required class="input-field">{{ old('address', $planter->address) }}</textarea>
                @error('address')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <x-map-pin-fields
                    :latitude="$planter->latitude"
                    :longitude="$planter->longitude"
                />
            </div>
            <div class="sm:col-span-2">
                <button type="submit" class="btn-primary">Save profile</button>
            </div>
        </form>
    </article>
@endsection
