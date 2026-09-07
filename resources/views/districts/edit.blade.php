@extends('layouts.app')

@section('title', 'Edit district')
@section('heading', 'Edit district')
@section('subheading', $district->name)

@section('actions')
    <a href="{{ route('admin.districts.show', $district) }}" class="btn-secondary">View</a>
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.districts.update', $district) }}" class="card mx-auto max-w-3xl p-5 sm:p-8">
        @csrf
        @method('PUT')
        @include('districts._form')
        <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('admin.districts.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Update district</button>
        </div>
    </form>
@endsection
