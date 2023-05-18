@extends('home.showcases.layout')

@section('home.showcases-title') {{ucfirst(__('showcase.showcase'))}} Index @endsection

@section('home.showcases-content')
{!! breadcrumbs([ucfirst(__('showcase.showcases')) => __('showcase.showcases').'/'.__('showcase.showcase').'-index']) !!}

<h1>
    All {{ucfirst(__('showcase.showcases'))}}
</h1>
<p>These are user-owned showcases that show off user's item collections..</p>

<div>
    {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
        <div class="form-group mr-3 mb-3">
            {!! Form::text('name', Request::get('name'), ['class' => 'form-control']) !!}
        </div> 
        <div class="form-group mr-3 mb-3">
            {!! Form::select('sort', [
                'alpha'          => 'Sort Alphabetically (A-Z)',
                'alpha-reverse'  => 'Sort Alphabetically (Z-A)',
                'newest'         => 'Newest First',
                'oldest'         => 'Oldest First'    
            ], Request::get('sort') ? : 'category', ['class' => 'form-control']) !!}
        </div>
        <div class="form-group mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
    {!! Form::close() !!}
</div>
{!! $showcases->render() !!}
  <div class="row ml-md-2">
    <div class="d-flex row flex-wrap col-12 pb-1 px-0 ubt-bottom">
      <div class="col-12 col-md-4 font-weight-bold">Name</div>
      <div class="col-4 col-md-3 font-weight-bold">Owner</div> 
    </div>
    @foreach($showcases as $showcase)
    <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 ubt-top">
      <div class="col-12 col-md-4 ">{!! $showcase->displayName !!}</div>
      <div class="col-4 col-md-3">{!! $showcase->user->displayName !!}</div> 
    </div>
    @endforeach
  </div>
{!! $showcases->render() !!}

<div class="text-center mt-4 small text-muted">{{ $showcases->total() }} result{{ $showcases->total() == 1 ? '' : 's' }} found.</div>

@endsection