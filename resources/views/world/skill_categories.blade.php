@extends('world.layout')

@section('title') Skill Categories @endsection

@section('content')
{!! breadcrumbs(['World' => 'world', 'Skill Categories' => 'world/skill-categories']) !!}
<h1>Skill Categories</h1>

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

{!! $categories->render() !!}
@foreach($categories as $category)
    <div class="card mb-3">
        <div class="card-body">
        @include('world._skill_category_entry', ['imageUrl' => $category->categoryImageUrl, 'name' => $category->displayName, 'description' => $category->description, 'searchUrl' => $category->searchUrl, 'category' => $category])
        </div>
    </div>
@endforeach
{!! $categories->render() !!}

<div class="text-center mt-4 small text-muted">{{ $categories->total() }} result{{ $categories->total() == 1 ? '' : 's' }} found.</div>

@endsection
