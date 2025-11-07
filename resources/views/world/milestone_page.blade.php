@extends('world.layout')

@section('world-title')
    {{ $milestone->milestone }}
@endsection

@section('meta-img')
    {{ $milestone->imageUrl }}
@endsection

@section('meta-desc')
    @if (isset($milestone->category) && $milestone->category)
        <p><strong>Category:</strong> {{ $milestone->category->name }}</p>
    @endif
@endsection

@section('content')
    <x-admin-edit title="Milestone" :object="$milestone" />
    {!! breadcrumbs(['World' => 'world', 'Milestones' => 'world/milestones', $milestone->displayName => $milestone->idUrl]) !!}

    <div class="card mb-3">
        <div class="card-body">
            @include('world._milestone_entry')
        </div>
    </div>
@endsection
