@extends('world.layout')

@section('title') Enchantment @endsection

@section('content')
{!! breadcrumbs(['World' => 'world', 'Enchantment' => 'world/enchantment']) !!}
<h1>Enchantment</h1>

<div>
    {!! Form::open(['method' => 'GET', 'class' => '']) !!}
        <div class="form-inline justify-content-end">
            <div class="form-group ml-3 mb-3">
                {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
            </div>
            <div class="form-group ml-3 mb-3">
                {!! Form::select('enchantment_category_id', $categories, Request::get('enchantment_category_id'), ['class' => 'form-control']) !!}
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

{!! $enchantments->render() !!}
@foreach($enchantments as $enchantment)
    <div class="card mb-3">
        <div class="card-body">
        @include('world._enchantment_entry', ['enchantment' => $enchantment, 'imageUrl' => $enchantment->imageUrl, 'name' => $enchantment->displayName, 'description' => $enchantment->description, 'idUrl' => $enchantment->idUrl])
        </div>
    </div>
@endforeach
{!! $enchantments->render() !!}

<div class="text-center mt-4 small text-muted">{{ $enchantments->total() }} result{{ $enchantments->total() == 1 ? '' : 's' }} found.</div>

@endsection
