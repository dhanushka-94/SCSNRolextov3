@extends('layouts.app')

@section('title', 'Edit RDO division')
@section('heading', 'Edit RDO division')
@section('subheading', $division->name)

@section('actions')
    <a href="{{ route('admin.rdo-divisions.show', $division) }}" class="btn-secondary">View</a>
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.rdo-divisions.update', $division) }}" class="card mx-auto max-w-3xl p-5 sm:p-8">
        @csrf
        @method('PUT')
        @include('rdo-divisions._form')
        <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('admin.rdo-divisions.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Update division</button>
        </div>
    </form>
@endsection
