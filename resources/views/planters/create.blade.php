@extends('layouts.app')

@section('title', 'Add planter')
@section('heading', 'Add planter')
@section('subheading', 'Create a planter registration')

@section('actions')
    <a href="{{ route('admin.planters.index') }}" class="btn-secondary">Back</a>
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.planters.store') }}" class="card mx-auto max-w-4xl p-5 sm:p-8">
        @csrf
        @include('planters._form')
        <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('admin.planters.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Save planter</button>
        </div>
    </form>
@endsection
