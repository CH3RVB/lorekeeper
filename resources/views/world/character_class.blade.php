@extends('world.layout')

@section('title') Character Classes @endsection

@section('content')
{!! breadcrumbs(['World' => 'world', 'Character Classes' => 'world/character-classes']) !!}
<h1>Character Classes</h1>

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

{!! $classes->render() !!}
@foreach($classes as $class)
    <div class="card mb-3">
        <div class="card-body">
        @include('world._entry', ['imageUrl' => $class->classImageUrl, 'name' => $class->displayName, 'description' => $class->description, 'searchUrl' => $class->searchUrl])
        </div>
    </div>
@endforeach
{!! $classes->render() !!}

<div class="text-center mt-4 small text-muted">{{ $classes->total() }} result{{ $classes->total() == 1 ? '' : 's' }} found.</div>

@endsection
