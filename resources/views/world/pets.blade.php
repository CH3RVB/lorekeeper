@extends('world.layout')

@section('title') Pets @endsection

@section('content')
{!! breadcrumbs(['World' => 'world', 'Pets' => 'world/pets']) !!}
<h1>Pets</h1>

<div>
    {!! Form::open(['method' => 'GET', 'class' => '']) !!}
        <div class="form-inline justify-content-end">
            <div class="form-group ml-3 mb-3">
                {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
            </div>
            <div class="form-group ml-3 mb-3">
                {!! Form::select('pet_category_id', $categories, Request::get('name'), ['class' => 'form-control']) !!}
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

{!! $pets->render() !!}
@foreach($pets as $pet)
    <div class="card mb-3">
        <div class="card-body">
            @include('world._entry', ['imageUrl' => $pet->imageUrl, 'name' => $pet->displayName, 'description' => $pet->parsed_description, 'searchUrl' => $pet->searchUrl])
            <div class="container mt-2">
                <h5 class="pl-2">Variants</h5>
                @foreach($pet->variants as $variant)
                    <div class="row world-entry p-2">
                        @if($variant->imageurl)
                            <div class="col-md-3 world-entry-image"><a href="{{ $variant->imageurl }}" data-lightbox="entry" data-title="{{ $variant->variant_name }}"><img src="{{ $variant->imageurl }}" class="world-entry-image" style="width:50%;" /></a></div>
                        @endif
                        <div class="{{ $variant->imageurl ? 'col-md-9' : 'col-12' }} my-auto">
                            <small>{!! $variant->variant_name !!} </small>
                        </div>
                    </div>
                @endforeach
            </div>    
        </div>   
    </div>
@endforeach
{!! $pets->render() !!}

<div class="text-center mt-4 small text-muted">{{ $pets->total() }} result{{ $pets->total() == 1 ? '' : 's' }} found.</div>

@endsection
