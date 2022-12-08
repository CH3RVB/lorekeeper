@extends('world.layout')

@section('title') Gear @endsection

@section('content')
{!! breadcrumbs(['World' => 'world', 'Gear' => 'world/gear']) !!}
<h1>Gear</h1>

<div>
    {!! Form::open(['method' => 'GET', 'class' => '']) !!}
        <div class="form-inline justify-content-end">
            <div class="form-group ml-3 mb-3">
                {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
            </div>
            <div class="form-group ml-3 mb-3">
                {!! Form::select('gear_category_id', $categories, Request::get('gear_category_id'), ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="form-inline justify-content-end">
            <div class="form-group ml-3 mb-3">
                {!! Form::select('sort', [
                    'alpha'          => 'Sort Alphabetically (A-Z)',
                    'alpha-reverse'  => 'Sort Alphabetically (Z-A)',
                    'category'       => 'Sort by Category',
                    'newest'         => 'Newest First',
                    'oldest'         => 'Oldest First'
                ], Request::get('sort') ? : 'category', ['class' => 'form-control']) !!}
            </div>
            <div class="form-group ml-3 mb-3">
                {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
            </div>
        </div>
    {!! Form::close() !!}
</div>

{!! $gears->render() !!}
@foreach($gears as $gear)
    <div class="card mb-3">
        <div class="card-body">
        @include('world._claymore_entry', ['item' => $gear, 'imageUrl' => $gear->imageUrl, 'name' => $gear->displayName, 'description' => $gear->description, 'idUrl' => $gear->idUrl])
        </div>
    </div>
@endforeach
{!! $gears->render() !!}

<div class="text-center mt-4 small text-muted">{{ $gears->total() }} result{{ $gears->total() == 1 ? '' : 's' }} found.</div>

@endsection
