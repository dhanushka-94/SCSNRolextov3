@extends('layouts.app')

@section('title', $user->name)
@section('heading', 'User details')
@section('subheading', $user->email)

@section('actions')
    <a href="{{ route('admin.users.edit', $user) }}" class="btn-primary">Edit</a>
@endsection

@section('content')
    <article class="card mx-auto max-w-3xl overflow-hidden">
        <div class="flex items-center gap-4 border-b border-sand bg-cream px-5 py-6 sm:px-8">
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-forest text-lg font-semibold text-white">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div>
                <h2 class="text-xl font-semibold text-forest-dark">{{ $user->name }}</h2>
                <p class="text-sm text-muted">{{ \App\Models\User::roles()[$user->role] }}</p>
            </div>
        </div>

        <dl class="grid gap-5 p-5 sm:grid-cols-2 sm:p-8">
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Email</dt>
                <dd class="mt-1 text-sm">{{ $user->email }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Phone</dt>
                <dd class="mt-1 text-sm">{{ $user->phone ?: '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Status</dt>
                <dd class="mt-1">
                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $user->isActive() ? 'bg-leaf/10 text-forest' : 'bg-sand text-bark' }}">
                        {{ \App\Models\User::statuses()[$user->status] }}
                    </span>
                </dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Last login</dt>
                <dd class="mt-1 text-sm">{{ sl_datetime($user->last_login_at, 'Never') }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Created</dt>
                <dd class="mt-1 text-sm">{{ sl_datetime($user->created_at) }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Updated</dt>
                <dd class="mt-1 text-sm">{{ sl_datetime($user->updated_at) }}</dd>
            </div>
        </dl>
    </article>
@endsection
