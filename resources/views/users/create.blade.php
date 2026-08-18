@extends('layouts.app')

@section('title', 'Add user')
@section('heading', 'Add user')
@section('subheading', 'Create a new system account')

@section('actions')
    <a href="{{ route('admin.users.index') }}" class="btn-secondary">Back</a>
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.users.store') }}" class="card mx-auto max-w-3xl p-5 sm:p-8">
        @csrf
        @include('users._form')
        <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('admin.users.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Save user</button>
        </div>
    </form>
@endsection
