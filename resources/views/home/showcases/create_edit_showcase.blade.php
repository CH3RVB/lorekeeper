@extends('home.showcases.layout')

@section('home.showcases-title')
    My {{ ucfirst(__('showcase.showcases')) }}
@endsection

@section('home.showcases-content')
    {!! breadcrumbs([
        'My ' . ucfirst(__('showcase.showcases')) => __('showcase.showcases'),
        ($showcase->id ? 'Edit ' : 'Create ') . ucfirst(__('showcase.showcase')) => $showcase->id
            ? __('showcase.showcases') . '/edit/' . $showcase->id
            : __('showcase.showcases') . '/create',
    ]) !!}

    <h1>{{ $showcase->id ? 'Edit' : 'Create' }} {{ ucfirst(__('showcase.showcase')) }}
        @if ($showcase->id)
            ({!! $showcase->displayName !!})
            <a href="#" class="btn btn-danger float-right delete-showcase-button">Delete
                {{ ucfirst(__('showcase.showcase')) }}</a>
        @endif
    </h1>

    {!! Form::open([
        'url' => $showcase->id ? __('showcase.showcases') . '/edit/' . $showcase->id : __('showcase.showcases') . '/create',
        'files' => true,
    ]) !!}

    <h3>Basic Information</h3>

    <div class="form-group">
        {!! Form::label('Name') !!}
        {!! Form::text('name', $showcase->name, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label(ucfirst(__('showcase.showcase')) . ' Image (Optional)') !!} {!! add_help(
            'This image is used on the ' .
                __('showcase.showcase') .
                ' index and on the ' .
                __('showcase.showcase') .
                ' page as a header.',
        ) !!}
        <div>{!! Form::file('image') !!}</div>
        <div class="text-muted">Recommended size: None (Choose a standard size for all {{ __('showcase.showcase') }} images)
        </div>
        @if ($showcase->has_image)
            <div class="form-check">
                {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
                {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
            </div>
        @endif
    </div>

    <div class="form-group">
        {!! Form::label('Description (Optional)') !!}
        {!! Form::textarea('description', $showcase->description, ['class' => 'form-control wysiwyg']) !!}
    </div>

    <div class="form-group">
        {!! Form::checkbox('is_active', 1, $showcase->id ? $showcase->is_active : 1, [
            'class' => 'form-check-input',
            'data-toggle' => 'toggle',
        ]) !!}
        {!! Form::label('is_active', 'Set Active', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned off, the ' . __('showcase.showcase') . ' will not be visible to regular users.') !!}
    </div>

    <div class="text-right">
        {!! Form::submit($showcase->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    @if ($showcase->id)
        <h3>{{ ucfirst(__('showcase.showcase')) }} Items</h3>

        @if ($showcase->stock->where('quantity', '>', 0)->count())
            <p class="text-center">Quick edit your showcase's stock here. Please keep in mind that any quantity set above 0
                will
                REMOVE
                stock from your showcase. You don't need to set a quantity to edit stock.</p>
            <h3>Items <a class="small inventory-collapse-toggle collapse-toggle collapsed" href="#userInventory"
                    data-toggle="collapse">Show</a></h3>
            <hr>
            <div class="collapse" id="userInventory">
                {!! Form::open(['url' => 'showcases/quickstock/' . $showcase->id]) !!}
                @include('widgets._showcase_select')

                <div class="text-right">
                    {!! Form::submit('Edit Stock', ['class' => 'btn btn-primary']) !!}
                </div>
                {!! Form::close() !!}
            </div>
        @else
            <div class="alert alert-warning text-center">Add stock to your showcase from your inventory.</div>
        @endif

        <hr>
        <h3> Preview </h3>
        <br>
        <h1>
            {{ $showcase->name }}
        </h1>
        <div class="mb-3">
            Owned by {!! $showcase->user->displayName !!}
        </div>

        <div class="text-center">
            <img src="{{ $showcase->showcaseImageUrl }}" style="max-width:100%" alt="{{ $showcase->name }}" />
            <p>{!! $showcase->parsed_description !!}</p>
        </div>
    @endif

@endsection

@section('scripts')
    @parent
    <script>
        $('.delete-showcase-button').on('click', function(e) {
            e.preventDefault();
            loadModal("{{ url(__('showcase.showcases') . '/delete') }}/{{ $showcase->id }}",
                'Delete {{ ucfirst(__('showcase.showcase')) }}');
        });
    </script>
@endsection
