@extends('admin.layout')

@section('admin-title')
    {{ $milestone->id ? 'Edit' : 'Create' }} Milestone
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Milestones' => 'admin/data/milestones', ($milestone->id ? 'Edit' : 'Create') . ' Milestone' => $milestone->id ? 'admin/data/milestones/edit/' . $milestone->id : 'admin/data/milestones/create']) !!}

    <h1>{{ $milestone->id ? 'Edit' : 'Create' }} Milestone
        @if ($milestone->id)
            <a href="#" class="btn btn-outline-danger float-right delete-milestone-button">Delete Milestone</a>
        @endif
    </h1>

    {!! Form::open(['url' => $milestone->id ? 'admin/data/milestones/edit/' . $milestone->id : 'admin/data/milestones/create', 'files' => true]) !!}

    <h3>Basic Information</h3>

    <div class="form-group">
        {!! Form::label('Milestone Amount') !!}{!! add_help('Number to be collected to earn rewards. Milestones check for everything a user has ever owned, not just what they currently own.') !!}
        {!! Form::number('milestone', $milestone->milestone, ['class' => 'form-control', 'min' => 1]) !!}
    </div>

    <div class="form-group">
        {!! Form::label('World Page Image (Optional)') !!} {!! add_help('This image is used only on the world information pages.') !!}
        <div class="custom-file">
            {!! Form::label('image', 'Choose file...', ['class' => 'custom-file-label']) !!}
            {!! Form::file('image', ['class' => 'custom-file-input']) !!}
        </div>
        <div class="text-muted">Recommended size: 100px x 100px</div>
        @if ($milestone->has_image)
            <div class="form-check">
                {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
                {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
            </div>
        @endif
    </div>

    <div class="row">
        <div class="col-md form-group" id="milestone_category">
            {!! Form::label('Milestone Category (Optional)') !!}{!! add_help('If there is a category set, this milestone will check only for items the user owns that are part of this category.') !!}
            {!! Form::select('category_id', $categories, $milestone->category_id, ['class' => 'form-control']) !!}
        </div>
        <div class="col-md form-group">
            {!! Form::label('Milestone Type') !!}
            {!! Form::select('milestone_type', $milestone_types, $milestone->milestone_type, ['class' => 'form-control', 'id' => 'milestone_type']) !!}
        </div>
    </div>

    <div class="row">
        <div class="col-md form-group">
            {!! Form::checkbox('is_active', 1, $milestone->id ? $milestone->is_active : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('is_active', 'Is Active/Can Be Claimed', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is off, users will not be able to claim rewards for this milestone.') !!}
        </div>
        <div class="col-md form-group">
            {!! Form::checkbox('is_visible', 1, $milestone->id ? $milestone->is_visible : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('is_visible', 'Is Visible', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is off, users will not be able to view information for the milestone/it will be hidden from view.') !!}
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('Summary (Optional)') !!} {!! add_help('This is a short blurb that shows up on the consolidated milestones page. HTML cannot be used here.') !!}
        {!! Form::text('summary', $milestone->summary, ['class' => 'form-control', 'maxLength' => 250]) !!}
    </div>

    <div class="form-group">
        {!! Form::label('Description (Optional)') !!}
        {!! Form::textarea('description', $milestone->description, ['class' => 'form-control wysiwyg']) !!}
    </div>

    <div class="text-right">
        {!! Form::submit($milestone->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    @if ($milestone->id)
        @include('widgets._reward_maker', [
            'object' => $milestone,
            'type' => 'milestone',
            'recipient' => 'User',
            'reward_key' => 'objectRewards',
        ])

        <h3>Preview</h3>
        @include('home.milestones.milestone_card')
    @endif
@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            var $typeSelect = $('#milestone_type');
            var type = $('#milestone_type').val();
            var cat = '<?php echo $milestone->category_id; ?>';
            $('.delete-milestone-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/milestones/delete') }}/{{ $milestone->id }}", 'Delete Milestone');
            });
            updateOptions();
            $typeSelect.change(function() {
                type = $typeSelect.val();
                updateOptions();
            });

            function updateOptions() {
                $.ajax({
                    type: "GET",
                    url: "{{ url('world/milestone/category') }}?type=" + type + "&create=" + true + "&cat=" + cat,
                    dataType: "text"
                }).done(function(res) {
                    $("#milestone_category").html(res);
                }).fail(function(jqXHR, textStatus, errorThrown) {
                    alert("AJAX call failed: " + textStatus + ", " + errorThrown);
                });
            }
        });
    </script>
@endsection
