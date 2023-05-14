@extends('admin.layout')

@section('admin-title') Pets @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Pets' => 'admin/data/pets', ($pet->id ? 'Edit' : 'Create').' Pet' => $pet->id ? 'admin/data/pets/edit/'.$pet->id : 'admin/data/pets/create']) !!}

<h1>{{ $pet->id ? 'Edit' : 'Create' }} Pet
    @if($pet->id)
        <a href="#" class="btn btn-outline-danger float-right delete-pet-button">Delete Pet</a>
    @endif
</h1>

{!! Form::open(['url' => $pet->id ? 'admin/data/pets/edit/'.$pet->id : 'admin/data/pets/create', 'files' => true]) !!}

@if(!$pet->id)<p>You can create variants once the pet is made.<p>@endif

<h3>Basic Information</h3>

<div class="form-group">
    {!! Form::label('Name') !!}
    {!! Form::text('name', $pet->name, ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('World Page Image (Optional)') !!} {!! add_help('This image is used only on the world information pages.') !!}
    <div>{!! Form::file('image') !!}</div>
    <div class="text-muted">Recommended size: 100px x 100px</div>
    @if($pet->has_image)
        <div class="form-check">
            {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
            {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
        </div>
    @endif
</div>
<div class="form-group">
    {!! Form::label('Pet Category (Optional)') !!}
    {!! Form::select('pet_category_id', $categories, $pet->pet_category_id, ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('Description (Optional)') !!}
    {!! Form::textarea('description', $pet->description, ['class' => 'form-control wysiwyg']) !!}
</div>

{!! Form::checkbox('allow_transfer', 1, $pet->id ? $pet->allow_transfer : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
{!! Form::label('allow_transfer', 'Allow User → User Transfer', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is off, users will not be able to transfer this pet to other users. Non-account-bound pets can be account-bound when granted to users directly.') !!}

<div class="text-right">
    {!! Form::submit($pet->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
</div>

{!! Form::close() !!}

@if($pet->id)
    <h3>Variants</h3>
    {!! Form::open(['url' => 'admin/data/pets/variants/'.$pet->id, 'files' => true]) !!}
    <div class="card mb-3">
        <div class="card-body">

            <div class="mb-2">
                <a href="#" class="btn btn-primary" id="add-feature">Add Variant</a>
            </div>

            <div id="featureList">
                @foreach($pet->variants as $variant)
                    <div class="form-group d-flex mb-2">
                        {!! Form::text('variant_names[]', $variant->variant_name, ['class' => 'form-control mr-2 feature-select original', 'placeholder' => 'Variant Name']) !!}                  
                        {!! Form::file('variant_images[]') !!}
                        <a href="#" class="remove-feature btn btn-danger mb-2">×</a>
                    </div>
                @endforeach
            </div>
            <div class="text-right">
                {!! Form::submit('Edit Variants', ['class' => 'btn btn-primary']) !!}
            </div>
            
            {!! Form::close() !!}
        </div>
    </div>

    <div class="feature-row hide mb-2">
        {!! Form::text('variant_names[]', null, ['class' => 'form-control mr-2 feature-select', 'placeholder' => 'Variant Name']) !!}                  
        {!! Form::file('variant_images[]') !!}
        <a href="#" class="remove-feature btn btn-danger mb-2">×</a>
    </div>

    <h3>Preview</h3>
    <div class="card mb-3">
        <div class="card-body">
            @include('world._entry', ['imageUrl' => $pet->imageUrl, 'name' => $pet->displayName, 'description' => $pet->parsed_description, 'searchUrl' => $pet->searchUrl])
            <div class="container mt-2">
                <h5 class="pl-2">Variants</h5>
                @foreach($pet->variants as $variant)
                    <div class="row world-entry p-2">
                        @if($variant->imageurl)
                            <div class="col-md-3 world-entry-image"><a href="{{ $variant->imageurl }}" data-lightbox="entry" data-title="{{ $variant->variant_name }}"><img src="{{ $variant->imageurl }}" class="world-entry-image" style="width:50%;" /></a></div>
                        @endif
                        <div class="{{ $variant->imageurl ? 'col-md-9' : 'col-12' }} my-auto">
                            <small>{!! $variant->variant_name !!} </small>
                        </div>
                    </div>
                @endforeach
            </div>    
        </div>
    </div>
@endif

@endsection

@section('scripts')
@parent
<script>
$( document ).ready(function() {    
    $('.delete-pet-button').on('click', function(e) {
        e.preventDefault();
        loadModal("{{ url('admin/data/pets/delete') }}/{{ $pet->id }}", 'Delete Pet');
    });

    $('#add-feature').on('click', function(e) {
        e.preventDefault();
        addFeatureRow();
    });
    $('.remove-feature').on('click', function(e) {
        e.preventDefault();
        removeFeatureRow($(this));
    })
    function addFeatureRow() {
        var $clone = $('.feature-row').clone();
        $('#featureList').append($clone);
        $clone.removeClass('hide feature-row');
        $clone.addClass('d-flex');
        $clone.find('.remove-feature').on('click', function(e) {
            e.preventDefault();
            removeFeatureRow($(this));
        })
    }
    function removeFeatureRow($trigger) {
        $trigger.parent().remove();
    }
});
    
</script>
@endsection