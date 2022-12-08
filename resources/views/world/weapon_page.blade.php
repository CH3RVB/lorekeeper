@extends('world.layout')

@section('title') {{ $weapon->name }} @endsection

@section('meta-img') {{ $imageUrl }} @endsection

@section('meta-desc')
@if(isset($weapon->category) && $weapon->category) <p><strong>Category:</strong> {{ $weapon->category->name }}</p> @endif
 :: {!! substr(str_replace('"','&#39;',$weapon->description),0,69) !!}
@endsection

@section('content')
{!! breadcrumbs(['World' => 'world', 'Weapons' => 'world/weapons', $weapon->name => $weapon->idUrl]) !!}

<div class="row">
    <div class="col-lg-6 col-lg-12">
        <div class="card mb-3">
            <div class="card-body">
                <div class="row world-entry">
                    @if($imageUrl)
                        <div class="col-md-3 world-entry-image"><a href="{{ $imageUrl }}" data-lightbox="entry" data-title="{{ $name }}"><img src="{{ $imageUrl }}" class="world-entry-image" /></a></div>
                    @endif
                    <div class="{{ $imageUrl ? 'col-md-9' : 'col-12' }}">
                        <h1>{!! $name !!}</h1>
                        <div class="row">
                        @if(isset($weapon->category) && $weapon->category)
                            <div class="col-md">
                                <p><strong>Category:</strong> {!! $weapon->category->name !!}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
