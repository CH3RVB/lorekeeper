@extends('world.layout')

@section('world-title')
    Milestones
@endsection

@section('content')
    {!! breadcrumbs(['World' => 'world', 'Milestones' => 'world/milestones']) !!}
    <h1>Milestones</h1>

    @if (Auth::check())
        <div class="text-right mb-3">
            <a class="btn btn-primary" href="{{ url('milestones') }}">View Incompleted</a>
        </div>
    @endif

    <div>
        {!! Form::open(['method' => 'GET', 'class' => '']) !!}
        <div class="form-inline justify-content-end">
            <div class="form-group ml-3 mb-3">
                {!! Form::number('milestone', Request::get('milestone'), ['class' => 'form-control', 'placeholder' => 'Quantity']) !!}
            </div>
            <div class="form-group ml-3 mb-3" id="milestone_category">
                {!! Form::select('category_id', $categories, Request::get('category_id'), ['class' => 'form-control', 'placeholder' => 'Any Category']) !!}
            </div>
            <div class="form-group ml-3 mb-3">
                {!! Form::select('milestone_type', $milestone_types, Request::get('milestone_type'), ['class' => 'form-control', 'placeholder' => 'Any Type', 'id' => 'milestone_type']) !!}
            </div>
        </div>
        <div class="form-inline justify-content-end">
            <div class="form-group ml-3 mb-3">
                {!! Form::select(
                    'sort',
                    [
                        'alpha' => 'Sort Quantity (Hi-Lo)',
                        'alpha-reverse' => 'Sort Quantity (Lo-Hi)',
                        'category' => 'Sort by Category',
                        'newest' => 'Newest First',
                        'oldest' => 'Oldest First',
                    ],
                    Request::get('sort') ?: 'category',
                    ['class' => 'form-control'],
                ) !!}
            </div>
            <div class="form-group ml-3 mb-3">
                {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
            </div>
        </div>
        {!! Form::close() !!}
    </div>

    {!! $milestones->render() !!}
    @foreach ($milestones as $milestone)
        <div class="card mb-3">
            <div class="card-body">
                @include('world._milestone_entry')
            </div>
        </div>
    @endforeach
    {!! $milestones->render() !!}

    <div class="text-center mt-4 small text-muted">{{ $milestones->total() }} result{{ $milestones->total() == 1 ? '' : 's' }} found.</div>
@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('#milestone_type').change(function() {
                var type = $(this).val();
                $.ajax({
                    type: "GET",
                    url: "{{ url('world/milestone/category') }}?type=" + type,
                    dataType: "text"
                }).done(function(res) {
                    $("#milestone_category").html(res);
                }).fail(function(jqXHR, textStatus, errorThrown) {
                    alert("AJAX call failed: " + textStatus + ", " + errorThrown);
                });
            });
        });
    </script>
@endsection
