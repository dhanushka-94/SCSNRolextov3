@php
    $isEdit = $planter->exists;
@endphp

<div class="grid gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label for="name" class="label-field">Full name</label>
        <input id="name" name="name" type="text" value="{{ old('name', $planter->name) }}" required class="input-field">
        @error('name')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="nic" class="label-field">NIC number</label>
        <input id="nic" name="nic" type="text" value="{{ old('nic', $planter->nic) }}" required class="input-field">
        @error('nic')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="phone" class="label-field">Phone</label>
        <input id="phone" name="phone" type="text" value="{{ old('phone', $planter->phone) }}" required class="input-field">
        @error('phone')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="email" class="label-field">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email', $planter->email) }}" required class="input-field">
        @error('email')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="district" class="label-field">District</label>
        <select id="district" name="district" required class="input-field">
            <option value="">Select district</option>
            @foreach (\App\Models\Planter::districts() as $district)
                <option value="{{ $district }}" @selected(old('district', $planter->district) === $district)>{{ $district }}</option>
            @endforeach
        </select>
        @error('district')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
    </div>
    <div class="sm:col-span-2">
        <label for="address" class="label-field">Address</label>
        <textarea id="address" name="address" rows="3" required class="input-field">{{ old('address', $planter->address) }}</textarea>
        @error('address')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="status" class="label-field">Approval status</label>
        <select id="status" name="status" class="input-field">
            @foreach (\App\Models\Planter::statuses() as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $planter->status) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="rejection_reason" class="label-field">Rejection reason</label>
        <input id="rejection_reason" name="rejection_reason" type="text" value="{{ old('rejection_reason', $planter->rejection_reason) }}" class="input-field" placeholder="Required if rejected">
        @error('rejection_reason')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="password" class="label-field">{{ $isEdit ? 'New password' : 'Password' }}</label>
        <div class="relative">
            <input id="password" name="password" type="password" autocomplete="new-password" class="input-field pr-12">
            <button type="button" data-password-toggle="password" class="absolute inset-y-0 right-0 px-3 text-xs font-semibold text-bark">Show</button>
        </div>
        <p class="mt-1 text-xs text-muted">{{ $isEdit ? 'Leave blank to keep the current password.' : 'Optional. The planter can create a password after approval.' }}</p>
        @error('password')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="password_confirmation" class="label-field">Confirm password</label>
        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="input-field">
    </div>
</div>
