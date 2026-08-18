@php
    $isEdit = $user->exists;
@endphp

<div class="grid gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label for="name" class="label-field">Full name</label>
        <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required class="input-field">
        @error('name')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="email" class="label-field">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required class="input-field">
        @error('email')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="phone" class="label-field">Phone</label>
        <input id="phone" name="phone" type="text" value="{{ old('phone', $user->phone) }}" class="input-field">
        @error('phone')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="role" class="label-field">Role</label>
        <select id="role" name="role" class="input-field">
            @foreach (\App\Models\User::roles() as $value => $label)
                <option value="{{ $value }}" @selected(old('role', $user->role) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('role')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="status" class="label-field">Status</label>
        <select id="status" name="status" class="input-field">
            @foreach (\App\Models\User::statuses() as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $user->status) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="password" class="label-field">{{ $isEdit ? 'New password' : 'Password' }}</label>
        <div class="relative">
            <input id="password" name="password" type="password" autocomplete="new-password" class="input-field pr-12" @required(! $isEdit)>
            <button type="button" data-password-toggle="password" class="absolute inset-y-0 right-0 px-3 text-xs font-semibold text-bark">Show</button>
        </div>
        @if ($isEdit)
            <p class="mt-1 text-xs text-muted">Leave blank to keep the current password.</p>
        @endif
        @error('password')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="password_confirmation" class="label-field">Confirm password</label>
        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="input-field" @required(! $isEdit)>
    </div>
</div>
