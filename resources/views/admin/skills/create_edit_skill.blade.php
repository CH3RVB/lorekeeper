@extends('admin.layout')

@section('admin-title') Skills @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Skills' => 'admin/data/skills', ($skill->id ? 'Edit' : 'Create').' Skill' => $skill->id ? 'admin/data/skills/edit/'.$skill->id : 'admin/data/skills/create']) !!}

<h1>{{ $skill->id ? 'Edit' : 'Create' }} Skill
    @if($skill->id)
        <a href="#" class="btn btn-outline-danger float-right delete-skill-button">Delete Skill</a>
    @endif
</h1>

{!! Form::open(['url' => $skill->id ? 'admin/data/skills/edit/'.$skill->id : 'admin/data/skills/create', 'files' => true]) !!}

<h3>Basic Information</h3>

<div class="form-group">
    {!! Form::label('Name') !!}
    {!! Form::text('name', $skill->name, ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('World Page Image (Optional)') !!} {!! add_help('This image is used only on the world information pages.') !!}
    <div>{!! Form::file('image') !!}</div>
    <div class="text-muted">Recommended size: 100px x 100px</div>
    @if($skill->has_image)
        <div class="form-check">
            {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
            {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
        </div>
    @endif
</div>

<div class="form-group">
    {!! Form::label('Description (Optional)') !!}
    {!! Form::textarea('description', $skill->description, ['class' => 'form-control wysiwyg']) !!}
</div>

<div class="form-group">
    {!! Form::label('Skill Category (Optional)') !!}
    {!! Form::select('skill_category_id', $categories, $skill->skill_category_id, ['class' => 'form-control']) !!}
</div>

<div class="row">
    <div class="col-md">
        <div class="form-group">
            {!! Form::label('Parent (Optional)') !!} {!! add_help('Related skill that transforms into this skill.') !!}
            {!! Form::select('parent_id', $skills, $skill->parent_id, ['class' => 'form-control mb-1']) !!}
            <p>A parent locks this skill and all prompts associated with this skill until the parent level is reached. It is also in the same tree as the skill.</p>
        </div>
    </div>
    <div class="col-md">
        <div class="form-group">
            {!! Form::label('Parent Level (Optional)') !!} {!! add_help('Related skill that transforms into this skill.') !!}
            {!! Form::number('parent_level', $skill->parent_level ? $skill->parent_level : 1, ['class' => 'form-control', 'min' => 1]) !!}
        </div>
    </div>
</div>

<div class="form-group">
    {!! Form::label('Prerequisite (Optional)') !!} {!! add_help('Unrelated skill required to have before the character can learn this skill.') !!}
    {!! Form::select('prerequisite_id', $skills, $skill->prerequisite_id, ['class' => 'form-control mb-1']) !!}
    <p>A prerequisite is required to have at least level 1 in to enter any prompts with this skill reward.</p>
</div>

<div class="text-right">
    {!! Form::submit($skill->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
</div>

{!! Form::close() !!}

@if($skill->id)
<h3>Preview</h3>
<div class="card mb-3">
    <div class="card-body">
        @include('world._skill_entry', ['imageUrl' => $skill->imageUrl, 'name' => $skill->displayName, 'description' => $skill->description, 'searchUrl' => $skill->searchUrl])
    </div>
</div>
@endif

@endsection

@section('scripts')
@parent
<script>
$( document ).ready(function() {
    $('.selectize').selectize();

    $('.delete-skill-button').on('click', function(e) {
        e.preventDefault();
        loadModal("{{ url('admin/data/skills/delete') }}/{{ $skill->id }}", 'Delete Skill');
    });
});

</script>
@endsection
