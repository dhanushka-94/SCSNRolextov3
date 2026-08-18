@extends('layouts.app')

@section('title', 'Edit user')
@section('heading', 'Edit user')
@section('subheading', $user->name)

@section('actions')
    <a href="{{ route('admin.users.show', $user) }}" class="btn-secondary">View</a>
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="card mx-auto max-w-3xl p-5 sm:p-8">
        @csrf
        @method('PUT')
        @include('users._form')
        <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('admin.users.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Update user</button>
        </div>
    </form>
@endsection
