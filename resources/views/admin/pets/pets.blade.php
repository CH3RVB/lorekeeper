@extends('admin.layout')

@section('admin-title') Pets @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Pets' => 'admin/data/pets']) !!}

<h1>Pets</h1>

<p>This is a list of pets in the game. Specific details about pets can be added when they are granted to users (e.g. reason for grant). By default, pets are merely collectibles and any additional functionality must be manually processed, or custom coded in for the specific pet.</p> 

<div class="text-right mb-3">
    <a class="btn btn-primary" href="{{ url('admin/data/pet-categories') }}"><i class="fas fa-folder"></i> Pet Categories</a>
    <a class="btn btn-primary" href="{{ url('admin/data/pets/create') }}"><i class="fas fa-plus"></i> Create New Pet</a>
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
    <table class="table table-sm category-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Category</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($pets as $pet)
                <tr class="sort-item" data-id="{{ $pet->id }}">
                    <td>
                        {{ $pet->name }}
                    </td>
                    <td>{{ $pet->category ? $pet->category->name : '' }}</td>
                    <td class="text-right">
                        <a href="{{ url('admin/data/pets/edit/'.$pet->id) }}" class="btn btn-primary">Edit</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {!! $pets->render() !!}
@endif

@endsection

@section('scripts')
@parent
@endsection