@extends('layouts.app')

@section('title', 'Add RDO division')
@section('heading', 'Add RDO division')
@section('subheading', 'Create a Rubber Development Officer division')

@section('actions')
    <a href="{{ route('admin.rdo-divisions.index') }}" class="btn-secondary">Back</a>
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.rdo-divisions.store') }}" class="card mx-auto max-w-3xl p-5 sm:p-8">
        @csrf
        @include('rdo-divisions._form')
        <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('admin.rdo-divisions.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Save division</button>
        </div>
    </form>
@endsection
