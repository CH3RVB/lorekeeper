@extends('admin.layout')

@section('admin-title') Enchantment @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Enchantment' => 'admin/enchantment']) !!}

<h1>Enchantment</h1>

<p>This is a list of enchantment in the game. Specific details about enchantment can be added when they are granted to users (e.g. reason for grant). By default, enchantment are merely collectibles and any additional functionality must be manually processed, or custom coded in for the specific enchantment.</p>

<div class="text-right mb-3">
    <a class="btn btn-primary" href="{{ url('admin/enchantment/enchantment-categories') }}"><i class="fas fa-folder"></i> Enchantment Categories</a>
    <a class="btn btn-primary" href="{{ url('admin/enchantment/create') }}"><i class="fas fa-plus"></i> Create New Enchantment</a>
</div>

<div>
    {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
        <div class="form-group mr-3 mb-3">
            {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
        </div>
        <div class="form-group mr-3 mb-3">
            {!! Form::select('enchantment_category_id', $categories, Request::get('name'), ['class' => 'form-control']) !!}
        </div>
        <div class="form-group mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
    {!! Form::close() !!}
</div>

@if(!count($enchantments))
    <p>No enchantment found.</p>
@else
    {!! $enchantments->render() !!}

        <div class="row ml-md-2 mb-4">
          <div class="d-flex row flex-wrap col-12 pb-1 px-0 ubt-bottom">
            <div class="col-5 col-md-6 font-weight-bold">Name</div>
            <div class="col-5 col-md-5 font-weight-bold">Category</div>
          </div>
          @foreach($enchantments as $enchantment)
          <div class="d-flex row flex-wrap col-12 mt-1 pt-2 px-0 ubt-top">
            <div class="col-5 col-md-6"> {{ $enchantment->name }} </div>
            <div class="col-4 col-md-5"> {{ $enchantment->category ? $enchantment->category->name : '' }} </div>
            <div class="col-3 col-md-1 text-right">
              <a href="{{ url('admin/enchantment/edit/'.$enchantment->id) }}"  class="btn btn-primary py-0 px-2">Edit</a>
            </div>
          </div>
          @endforeach
        </div>

    {!! $enchantments->render() !!}
@endif

@endsection

@section('scripts')
@parent
@endsection
