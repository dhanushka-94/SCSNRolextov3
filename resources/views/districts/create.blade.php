@extends('layouts.app')

@section('title', 'Add district')
@section('heading', 'Add district')
@section('subheading', 'Create a district for registration selection')

@section('actions')
    <a href="{{ route('admin.districts.index') }}" class="btn-secondary">Back</a>
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.districts.store') }}" class="card mx-auto max-w-3xl p-5 sm:p-8">
        @csrf
        @include('districts._form')
        <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('admin.districts.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Save district</button>
        </div>
    </form>
@endsection
