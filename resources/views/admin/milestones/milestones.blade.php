@extends('admin.layout')

@section('admin-title')
    Milestones
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Milestones' => 'admin/data/milestones']) !!}

    <h1>Milestones</h1>

    <p>This is a list of milestones in the game that users can complete for rewards.</p>

    <div class="text-right mb-3">
        <a class="btn btn-primary" href="{{ url('admin/data/milestones/create') }}"><i class="fas fa-plus"></i> Create New Milestone</a>
    </div>

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
                    'visibility',
                    [
                        'visibleOnly' => 'Visible Only',
                        'hiddenOnly' => 'Hidden Only',
                    ],
                    Request::get('visibility'),
                    ['class' => 'form-control', 'placeholder' => 'Any Visibility'],
                ) !!}
            </div>
            <div class="form-group ml-3 mb-3">
                {!! Form::select(
                    'is_active',
                    [
                        'activeOnly' => 'Active Only',
                        'inactiveOnly' => 'Inactive Only',
                    ],
                    Request::get('is_active'),
                    ['class' => 'form-control', 'placeholder' => 'Any Activity'],
                ) !!}
            </div>
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
                    Request::get('sort') ?: 'oldest',
                    ['class' => 'form-control'],
                ) !!}
            </div>
            <div class="form-group ml-3 mb-3">
                {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
            </div>
        </div>
        {!! Form::close() !!}
    </div>

    @if (!count($milestones))
        <p>No milestones found.</p>
    @else
        {!! $milestones->render() !!}
        <div class="mb-4 logs-table">
            <div class="logs-table-header">
                <div class="row">
                    <div class="col-5 col-md-6">
                        <div class="logs-table-cell">Milestone</div>
                    </div>
                    <div class="col-5 col-md-5">
                        <div class="logs-table-cell">Category</div>
                    </div>
                </div>
            </div>
            <div class="logs-table-body">
                @foreach ($milestones as $milestone)
                    <div class="logs-table-row">
                        <div class="row flex-wrap">
                            <div class="col-5 col-md-6">
                                <div class="logs-table-cell">
                                    @if (!$milestone->is_visible)
                                        <i class="fas fa-eye-slash mr-1"></i>
                                    @endif
                                    {!! $milestone->displayName !!}
                                </div>
                            </div>
                            <div class="col-4 col-md-5">
                                <div class="logs-table-cell">{!! $milestone->category ? $milestone->category->displayName : '' !!}</div>
                            </div>
                            <div class="col-3 col-md-1 text-right">
                                <div class="logs-table-cell">
                                    <a href="{{ url('admin/data/milestones/edit/' . $milestone->id) }}" class="btn btn-primary py-0 px-2">Edit</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        {!! $milestones->render() !!}
    @endif
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
