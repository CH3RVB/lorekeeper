@extends('world.layout')

@section('title') Skills @endsection

@section('content')
{!! breadcrumbs(['World' => 'world', 'Skills' => 'world/skills']) !!}
<h1>Skills</h1>

<div>
    {!! Form::open(['method' => 'GET', 'class' => '']) !!}
        <div class="form-inline justify-content-end">
            <div class="form-group ml-3 mb-3">
                {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
            </div>
            <div class="form-group ml-3 mb-3">
                {!! Form::select('skill_category_id', $categories, Request::get('skill_category_id'), ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="form-inline justify-content-end">
            <div class="form-group ml-3 mb-3">
                {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
            </div>
        </div>
    {!! Form::close() !!}
</div>

{!! $skills->render() !!}
@foreach($skills as $skill)
    <div class="card mb-3">
        <div class="card-body">
        @include('world._skill_entry', ['imageUrl' => $skill->imageUrl, 'name' => $skill->displayName, 'description' => $skill->description, 'idUrl' => $skill->idUrl])
        </div>
    </div>
@endforeach
{!! $skills->render() !!}

<div class="text-center mt-4 small text-muted">{{ $skills->total() }} result{{ $skills->total() == 1 ? '' : 's' }} found.</div>

@endsection
