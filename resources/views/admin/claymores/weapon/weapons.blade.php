@extends('admin.layout')

@section('admin-title') Weapons @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Weapons' => 'admin/weapons']) !!}

<h1>Weapons</h1>

<p>This is a list of weapons in the game. Specific details about weapons can be added when they are granted to users (e.g. reason for grant). By default, weapons are merely collectibles and any additional functionality must be manually processed, or custom coded in for the specific weapon.</p>

<div class="text-right mb-3">
    <a class="btn btn-primary" href="{{ url('admin/weapon/weapon-categories') }}"><i class="fas fa-folder"></i> Weapon Categories</a>
    <a class="btn btn-primary" href="{{ url('admin/weapon/create') }}"><i class="fas fa-plus"></i> Create New Weapon</a>
</div>

<div>
    {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
        <div class="form-group mr-3 mb-3">
            {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
        </div>
        <div class="form-group mr-3 mb-3">
            {!! Form::select('weapon_category_id', $categories, Request::get('name'), ['class' => 'form-control']) !!}
        </div>
        <div class="form-group mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
    {!! Form::close() !!}
</div>

@if(!count($weapons))
    <p>No weapons found.</p>
@else
    {!! $weapons->render() !!}

        <div class="row ml-md-2 mb-4">
          <div class="d-flex row flex-wrap col-12 pb-1 px-0 ubt-bottom">
            <div class="col-5 col-md-6 font-weight-bold">Name</div>
            <div class="col-5 col-md-5 font-weight-bold">Category</div>
          </div>
          @foreach($weapons as $weapon)
          <div class="d-flex row flex-wrap col-12 mt-1 pt-2 px-0 ubt-top">
            <div class="col-5 col-md-6"> {{ $weapon->name }} </div>
            <div class="col-4 col-md-5"> {{ $weapon->category ? $weapon->category->name : '' }} </div>
            <div class="col-3 col-md-1 text-right">
              <a href="{{ url('admin/weapon/edit/'.$weapon->id) }}"  class="btn btn-primary py-0 px-2">Edit</a>
            </div>
          </div>
          @endforeach
        </div>

    {!! $weapons->render() !!}
@endif

@endsection

@section('scripts')
@parent
@endsection
