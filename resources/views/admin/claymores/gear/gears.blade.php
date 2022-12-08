@extends('admin.layout')

@section('admin-title') Gear @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Gear' => 'admin/gear']) !!}

<h1>Gear</h1>

<p>This is a list of gear in the game. Specific details about gear can be added when they are granted to users (e.g. reason for grant). By default, gear are merely collectibles and any additional functionality must be manually processed, or custom coded in for the specific gear.</p>

<div class="text-right mb-3">
    <a class="btn btn-primary" href="{{ url('admin/gear/gear-categories') }}"><i class="fas fa-folder"></i> Gear Categories</a>
    <a class="btn btn-primary" href="{{ url('admin/gear/create') }}"><i class="fas fa-plus"></i> Create New Gear</a>
</div>

<div>
    {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
        <div class="form-group mr-3 mb-3">
            {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
        </div>
        <div class="form-group mr-3 mb-3">
            {!! Form::select('gear_category_id', $categories, Request::get('name'), ['class' => 'form-control']) !!}
        </div>
        <div class="form-group mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
    {!! Form::close() !!}
</div>

@if(!count($gears))
    <p>No gear found.</p>
@else
    {!! $gears->render() !!}

        <div class="row ml-md-2 mb-4">
          <div class="d-flex row flex-wrap col-12 pb-1 px-0 ubt-bottom">
            <div class="col-5 col-md-6 font-weight-bold">Name</div>
            <div class="col-5 col-md-5 font-weight-bold">Category</div>
          </div>
          @foreach($gears as $gear)
          <div class="d-flex row flex-wrap col-12 mt-1 pt-2 px-0 ubt-top">
            <div class="col-5 col-md-6"> {{ $gear->name }} </div>
            <div class="col-4 col-md-5"> {{ $gear->category ? $gear->category->name : '' }} </div>
            <div class="col-3 col-md-1 text-right">
              <a href="{{ url('admin/gear/edit/'.$gear->id) }}"  class="btn btn-primary py-0 px-2">Edit</a>
            </div>
          </div>
          @endforeach
        </div>

    {!! $gears->render() !!}
@endif

@endsection

@section('scripts')
@parent
@endsection
