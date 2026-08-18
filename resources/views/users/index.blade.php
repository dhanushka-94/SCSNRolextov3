@extends('layouts.app')

@section('title', 'System users')
@section('heading', 'System users')
@section('subheading', 'Create, update, and manage CMS accounts')

@section('actions')
    <a href="{{ route('admin.users.create') }}" class="btn-primary">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
        <span class="hidden sm:inline">Add user</span>
    </a>
@endsection

@section('content')
    <form method="GET" action="{{ route('admin.users.index') }}" class="card mb-5 grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-4">
        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search name, email, or phone" class="input-field sm:col-span-2">
        <select name="role" class="input-field">
            <option value="">All roles</option>
            @foreach (\App\Models\User::roles() as $value => $label)
                <option value="{{ $value }}" @selected(($filters['role'] ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <div class="flex gap-2">
            <select name="status" class="input-field">
                <option value="">All statuses</option>
                @foreach (\App\Models\User::statuses() as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-secondary shrink-0">Filter</button>
        </div>
    </form>

    <section class="card overflow-hidden">
        <div class="hidden overflow-x-auto md:block">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-cream text-muted">
                    <tr>
                        <th class="px-5 py-3 font-medium">User</th>
                        <th class="px-5 py-3 font-medium">Phone</th>
                        <th class="px-5 py-3 font-medium">Role</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 font-medium">Last login</th>
                        <th class="px-5 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr class="border-t border-sand">
                            <td class="px-5 py-4">
                                <p class="font-semibold text-ink">{{ $user->name }}</p>
                                <p class="text-xs text-muted">{{ $user->email }}</p>
                            </td>
                            <td class="px-5 py-4 text-muted">{{ $user->phone ?: '—' }}</td>
                            <td class="px-5 py-4">{{ \App\Models\User::roles()[$user->role] }}</td>
                            <td class="px-5 py-4">
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $user->isActive() ? 'bg-leaf/10 text-forest' : 'bg-sand text-bark' }}">
                                    {{ \App\Models\User::statuses()[$user->status] }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-muted">{{ sl_datetime($user->last_login_at, 'Never') }}</td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.users.show', $user) }}" class="btn-secondary px-3 py-1.5">View</a>
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn-secondary px-3 py-1.5">Edit</a>
                                    @if (! $user->is(auth('web')->user()))
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" data-delete-form data-delete-name="{{ $user->name }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-danger px-3 py-1.5">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-muted">No users match the current filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="space-y-3 p-4 md:hidden">
            @forelse ($users as $user)
                <article class="rounded-2xl border border-sand bg-cream p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-semibold">{{ $user->name }}</p>
                            <p class="text-sm text-muted">{{ $user->email }}</p>
                        </div>
                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $user->isActive() ? 'bg-leaf/10 text-forest' : 'bg-sand text-bark' }}">
                            {{ \App\Models\User::statuses()[$user->status] }}
                        </span>
                    </div>
                    <p class="mt-2 text-xs text-bark">{{ \App\Models\User::roles()[$user->role] }} · {{ $user->phone ?: 'No phone' }}</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('admin.users.show', $user) }}" class="btn-secondary px-3 py-1.5">View</a>
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn-secondary px-3 py-1.5">Edit</a>
                        @if (! $user->is(auth('web')->user()))
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" data-delete-form data-delete-name="{{ $user->name }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-danger px-3 py-1.5">Delete</button>
                            </form>
                        @endif
                    </div>
                </article>
            @empty
                <p class="py-6 text-center text-sm text-muted">No users match the current filters.</p>
            @endforelse
        </div>

        @if ($users->hasPages())
            <div class="border-t border-sand px-4 py-3">
                {{ $users->links() }}
            </div>
        @endif
    </section>
@endsection
