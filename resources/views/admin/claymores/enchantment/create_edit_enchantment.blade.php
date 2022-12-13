@extends('admin.layout')

@section('admin-title') Enchantment @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Enchantment' => 'admin/enchantment', ($enchantment->id ? 'Edit' : 'Create').' Enchantment' => $enchantment->id ? 'admin/enchantment/edit/'.$enchantment->id : 'admin/enchantment/create']) !!}

<h1>{{ $enchantment->id ? 'Edit' : 'Create' }} Enchantment
    @if($enchantment->id)
        <a href="#" class="btn btn-outline-danger float-right delete-enchantment-button">Delete Enchantment</a>
    @endif
</h1>

{!! Form::open(['url' => $enchantment->id ? 'admin/enchantment/edit/'.$enchantment->id : 'admin/enchantment/create', 'files' => true]) !!}

<h3>Basic Information</h3>

<div class="form-group">
    {!! Form::label('Name') !!}
    {!! Form::text('name', $enchantment->name, ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('World Page Image (Optional)') !!} {!! add_help('This image is used only on the world information pages.') !!}
    <div>{!! Form::file('image') !!}</div>
    <div class="text-muted">Recommended size: 100px x 100px</div>
    @if($enchantment->has_image)
        <div class="form-check">
            {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
            {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
        </div>
    @endif
</div>

<div class="row">
    <div class="col-md">
        <div class="form-group">
            {!! Form::label('Enchantment Category (Optional)') !!}
            {!! Form::select('enchantment_category_id', $categories, $enchantment->enchantment_category_id, ['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="col-md">
        <div class="form-group">
            {!! Form::label('Enchantment Parent (Optional)') !!} {!! add_help('This should be a number.') !!}
            {!! Form::select('parent_id', $enchantments, $enchantment->parent_id, ['class' => 'form-control']) !!}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md">
        <div class="form-group">
            {!! Form::label('Enchantment -> Parent Currency Type (Optional)') !!} {!! add_help('If you want this enchantment to be able to turn into its parent.') !!}
            {!! Form::select('currency_id', $currencies, $enchantment->currency_id, ['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="col-md">
        <div class="form-group">
            {!! Form::label('Enchantment -> Parent Currency Cost (Optional)') !!} {!! add_help('This should be a number.') !!}
            {!! Form::number('cost', $enchantment->cost, ['class' => 'form-control']) !!}
        </div>
    </div>
</div>

<div class="form-group">
    {!! Form::label('Description (Optional)') !!}
    {!! Form::textarea('description', $enchantment->description, ['class' => 'form-control wysiwyg']) !!}
</div>

<div class="row">
    <div class="col-md form-group">
        {!! Form::checkbox('allow_transfer', 1, $enchantment->id ? $enchantment->allow_transfer : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
        {!! Form::label('allow_transfer', 'Allow User → User Transfer', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is off, users will not be able to transfer this enchantment to other users. Non-account-bound enchantments can be account-bound when granted to users directly.') !!}
    </div>
</div>

<div class="text-right">
    {!! Form::submit($enchantment->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
</div>

{!! Form::close() !!}

@if($enchantment->id)
    @if($stats)
    {!! Form::open(['url' => 'admin/enchantment/stats/'.$enchantment->id]) !!}
    <h3>Stats {!! add_help('Leave empty to have no effect on stat.') !!}</h3>
    <div class="form-group">
        @foreach($stats as $stat)
        @php if($enchantment->stats->where('stat_id', $stat->id)->first()) $base = $enchantment->stats->where('stat_id', $stat->id)->first()->count; else $base = null; @endphp
            {!! Form::label($stat->name) !!}
            {!! Form::number('stats['.$stat->id.']', $base, ['class' => 'form-control m-1',]) !!}
        @endforeach
    </div>
    <div class="text-right">
        {!! Form::submit('Edit Stats', ['class' => 'btn btn-primary']) !!}
    </div>
    
    {!! Form::close() !!}
    @endif

    <h3>Preview</h3>
    <div class="card mb-3">
        <div class="card-body">
            @include('world._enchantment_entry', ['imageUrl' => $enchantment->imageUrl, 'name' => $enchantment->displayName, 'description' => $enchantment->description, 'searchUrl' => $enchantment->searchUrl])
        </div>
    </div>
@endif

@endsection

@section('scripts')
@parent
<script>
$( document ).ready(function() {
    $('.selectize').selectize();

    $('.delete-enchantment-button').on('click', function(e) {
        e.preventDefault();
        loadModal("{{ url('admin/enchantment/delete') }}/{{ $enchantment->id }}", 'Delete Enchantment');
    });
});

</script>
@endsection
