@extends('world.layout')

@section('title') Item Subcategories @endsection

@section('content')
{!! breadcrumbs(['World' => 'world', 'Item Subcategories' => 'world/item-subcategories']) !!}
<h1>Item Subcategories</h1>

<div>
    {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
        <div class="form-group mr-3 mb-3">
            {!! Form::text('name', Request::get('name'), ['class' => 'form-control']) !!}
        </div>
        <div class="form-group mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
    {!! Form::close() !!}
</div>

{!! $subcategories->render() !!}
@foreach($subcategories as $subcategory)
    <div class="card mb-3">
        <div class="card-body">
        @include('world._item_subcategory_entry', ['imageUrl' => $subcategory->subcategoryImageUrl, 'name' => $subcategory->displayName, 'description' => $subcategory->parsed_description, 'searchUrl' => $subcategory->searchUrl, 'subcategory' => $subcategory])
        </div>
    </div>
@endforeach
{!! $subcategories->render() !!}

<div class="text-center mt-4 small text-muted">{{ $subcategories->total() }} result{{ $subcategories->total() == 1 ? '' : 's' }} found.</div>

@endsection
