@extends('admin.layout')

@section('admin-title') Item Subcategories @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Item Subcategories' => 'admin/data/item-subcategories', ($subcategory->id ? 'Edit' : 'Create').' Subcategory' => $subcategory->id ? 'admin/data/item-subcategories/edit/'.$subcategory->id : 'admin/data/item-subcategories/create']) !!}

<h1>{{ $subcategory->id ? 'Edit' : 'Create' }} Subcategory
    @if($subcategory->id)
        <a href="#" class="btn btn-danger float-right delete-subcategory-button">Delete Subcategory</a>
    @endif
</h1>

{!! Form::open(['url' => $subcategory->id ? 'admin/data/item-subcategories/edit/'.$subcategory->id : 'admin/data/item-subcategories/create', 'files' => true]) !!}

<h3>Basic Information</h3>

<div class="form-group">
    {!! Form::label('Name') !!}
    {!! Form::text('name', $subcategory->name, ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('World Page Image (Optional)') !!} {!! add_help('This image is used only on the world information pages.') !!}
    <div>{!! Form::file('image') !!}</div>
    <div class="text-muted">Recommended size: 200px x 200px</div>
    @if($subcategory->has_image)
        <div class="form-check">
            {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
            {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
        </div>
    @endif
</div>

<div class="form-group">
    {!! Form::label('Description (Optional)') !!}
    {!! Form::textarea('description', $subcategory->description, ['class' => 'form-control wysiwyg']) !!}
</div>


<div class="text-right">
    {!! Form::submit($subcategory->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
</div>

{!! Form::close() !!}

@if($subcategory->id)
    <h3>Preview</h3>
    <div class="card mb-3">
        <div class="card-body">
            @include('world._item_subcategory_entry', ['imageUrl' => $subcategory->subcategoryImageUrl, 'name' => $subcategory->displayName, 'description' => $subcategory->parsed_description, 'subcategory'=>$subcategory])
        </div>
    </div>
@endif

@endsection

@section('scripts')
@parent
<script>
$( document ).ready(function() {    
    $('.delete-subcategory-button').on('click', function(e) {
        e.preventDefault();
        loadModal("{{ url('admin/data/item-subcategories/delete') }}/{{ $subcategory->id }}", 'Delete Subcategory');
    });
});
    
</script>
@endsection