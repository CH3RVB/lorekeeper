@extends('admin.layout')

@section('admin-title') Pet Drops @endsection

@section('admin-content')

{!! breadcrumbs(['Admin Panel' => 'admin', 'Pets' => 'admin/data/pets', 'Pet Drops' => 'admin/data/pet-drops']) !!}

<h1>Pet Drop Data</h1>

<p>Pet drops are items that can be collected from pets at set intervals.</p>

<div class="text-right mb-3">
    <a class="btn btn-secondary" href="{{ url('admin/data/pets') }}"><i class="fas fa-undo-alt mr-1"></i> Return to Pets</a>
    <a class="btn btn-primary" href="{{ url('admin/data/pet-drops/create') }}"><i class="fas fa-plus mr-1"></i> Create New Pet Drop</a>
</div>

@if(!count($drops))
    <p>No pet drops found.</p>
@else
    {!! $drops->render() !!}

    <div class="row ml-md-2">
        <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 ubt-bottom">
            <div class="col-6 col-md-2 font-weight-bold">Active</div>
            <div class="col-12 col-md-3 font-weight-bold">Pet</div>
            <div class="col-6 col-md font-weight-bold">Groups</div>
        </div>

        @foreach($drops as $drop)
            <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 ubt-top">
                <div class="col-6 col-md-2">{!! $drop->isActive ? '<i class="text-success fas fa-check"></i>' : '' !!}</div>
                <div class="col-12 col-md-3">{!! $drop->pet->displayName !!}</div>
                <div class="col-6 col-md-2">{!! implode(', ',$drop->parameterArray) !!}</div>
                <div class="col-3 col-md text-right"><a href="{{ $drop->url }}" class="btn btn-primary py-0">Edit</a></div>
            </div>
        @endforeach

    </div>

    {!! $drops->render() !!}
@endif
<div class="text-center mt-4 small text-muted">{{ $drops->total() }} result{{ $drops->total() == 1 ? '' : 's' }} found.</div>

@endsection
