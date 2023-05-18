@extends('user.layout')

@section('profile-title') {{ $user->name }}'s {{__('showcase.showcases')}} @endsection

@section('profile-content')
{!! breadcrumbs(['Users' => 'users', $user->name => $user->url, __('showcase.showcases') => $user->url . '/'.__('showcase.showcases')]) !!}

<h1>
    {!! $user->displayName !!}'s {{ucfirst(__('showcase.showcases'))}}
</h1>


@if($showcases->count())
<div class="row showcases-row">
    @foreach($showcases as $showcase)
        <div class="col-md-3 col-6 mb-3 text-center">
            <div class="showcase-image">
                <a href="{{ $showcase->url }}"><img src="{{ $showcase->showcaseImageUrl }}" alt="{{ $showcase->name }}" /></a>
            </div>
            <div class="showcase-name mt-1">
                <a href="{{ $showcase->url }}" class="h5 mb-0">{{ $showcase->name }}</a>
            </div>
        </div>
    @endforeach
</div>
@else
    <p>No showcases found.</p>
@endif

@endsection