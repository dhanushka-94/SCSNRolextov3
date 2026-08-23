@extends('layouts.app')

@section('title', 'Edit planter')
@section('heading', 'Edit planter')
@section('subheading', $planter->identification_number)

@section('actions')
    <a href="{{ route('admin.planters.show', $planter) }}" class="btn-secondary">View</a>
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.planters.update', $planter) }}" class="card mx-auto max-w-4xl p-5 sm:p-8">
        @csrf
        @method('PUT')
        @include('planters._form')
        <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('admin.planters.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Update planter</button>
        </div>
    </form>
@endsection
