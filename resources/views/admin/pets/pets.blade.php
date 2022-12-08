@extends('admin.layout')

@section('admin-title') Pets @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Pets' => 'admin/data/pets']) !!}

<h1>Pets</h1>

<p>This is a list of pets in the game. Specific details about pets can be added when they are granted to users (e.g. reason for grant). By default, pets are merely collectibles and any additional functionality must be manually processed, or custom coded in for the specific pet.</p>

<div class="text-right mb-3">
    <a class="btn btn-primary" href="{{ url('admin/data/pet-categories') }}"><i class="fas fa-folder mr-1"></i> Pet Categories</a>
    <a class="btn btn-primary" href="{{ url('admin/data/pet-drops') }}"><i class="fas fa-egg mr-1"></i> Pet Drops</a>
    <a class="btn btn-primary" href="{{ url('admin/data/pets/create') }}"><i class="fas fa-plus mr-1"></i> Create New Pet</a>
</div>

<div>
    {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
        <div class="form-group mr-3 mb-3">
            {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
        </div>
        <div class="form-group mr-3 mb-3">
            {!! Form::select('pet_category_id', $categories, Request::get('name'), ['class' => 'form-control']) !!}
        </div>
        <div class="form-group mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
    {!! Form::close() !!}
</div>

@if(!count($pets))
    <p>No pets found.</p>
@else
    {!! $pets->render() !!}
    <div class="row ml-md-2">
        <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 ubt-bottom">
            <div class="col-5 col-md-6 font-weight-bold">Name</div>
            <div class="col col-md font-weight-bold">Category</div>
        </div>
        @foreach($pets as $pet)
            <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 ubt-top">
                <div class="col-5 col-md-6"> {{ $pet->name }} </div>
                <div class="col-5 col-md-5"> {{ $pet->category ? $pet->category->name : '' }} </div>
                <div class="col-2 col-md-1 text-right"> <a href="{{ url('admin/data/pets/edit/'.$pet->id) }}" class="btn btn-primary py-0">Edit</a> </div>
            </div>
        @endforeach
    </div>
    {!! $pets->render() !!}
@endif

<div class="text-center mt-4 small text-muted">{{ $pets->total() }} result{{ $pets->total() == 1 ? '' : 's' }} found.</div>

@endsection

@section('scripts')
@parent
@endsection
