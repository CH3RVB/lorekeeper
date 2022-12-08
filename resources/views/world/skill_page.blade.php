@extends('world.layout')

@section('title') {{ $skill->name }} @endsection

@section('meta-img') {{ $imageUrl }} @endsection

@section('meta-desc')
@if(isset($skill->category) && $skill->category) <p><strong>Category:</strong> {{ $skill->category->name }}</p> @endif
 :: {!! substr(str_replace('"','&#39;',$skill->description),0,69) !!}
@endsection

@section('content')
{!! breadcrumbs(['World' => 'world', 'Skills' => 'world/skills', $skill->name => $skill->idUrl]) !!}

<div class="row">
    <div class="col-sm">
    </div>
    <div class="col-lg-6 col-lg-10">
        <div class="card mb-3">
            <div class="card-body">
                <div class="row world-entry">
                    @if($imageUrl)
                        <div class="col-md-3 world-entry-image"><a href="{{ $imageUrl }}" data-lightbox="entry" data-title="{{ $name }}"><img src="{{ $imageUrl }}" class="world-entry-image" /></a></div>
                    @endif
                    <div class="{{ $imageUrl ? 'col-md-9' : 'col-12' }}">
                        <h1>{!! $name !!}</h1>
                        <div class="row">
                        @if(isset($skill->category) && $skill->category)
                            <div class="col-md">
                                <p><strong>Category:</strong> {!! $skill->category->name !!}</p>
                            </div>
                        @endif
                        </div>
                        <div class="world-entry-text">
                            {!! $description !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm">
    </div>
</div>
@endsection
