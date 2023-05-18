@extends('home.showcases.layout')

@section('home.showcases-title') My {{ucfirst(__('showcase.showcases'))}} @endsection

@section('home.showcases-content')
{!! breadcrumbs(['My '.ucfirst(__('showcase.showcases')) => __('showcase.showcases'), ($showcase->id ? 'Edit ' : 'Create ').(ucfirst(__('showcase.showcase'))) => $showcase->id ? __('showcase.showcases').'/edit/'.$showcase->id : __('showcase.showcases').'/create']) !!}

<h1>{{ $showcase->id ? 'Edit' : 'Create' }} {{ucfirst(__('showcase.showcase'))}}
    @if($showcase->id)
        ({!! $showcase->displayName !!})
        <a href="#" class="btn btn-danger float-right delete-showcase-button">Delete {{ucfirst(__('showcase.showcase'))}}</a>
    @endif
</h1>

{!! Form::open(['url' => $showcase->id ? __('showcase.showcases').'/edit/'.$showcase->id : __('showcase.showcases').'/create', 'files' => true]) !!}

<h3>Basic Information</h3>

<div class="form-group">
    {!! Form::label('Name') !!}
    {!! Form::text('name', $showcase->name, ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label(ucfirst(__('showcase.showcase')).' Image (Optional)') !!} {!! add_help('This image is used on the '.__('showcase.showcase').' index and on the '.__('showcase.showcase').' page as a header.') !!}
    <div>{!! Form::file('image') !!}</div>
    <div class="text-muted">Recommended size: None (Choose a standard size for all {{__('showcase.showcase')}} images)</div>
    @if($showcase->has_image)
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
    {!! Form::checkbox('is_active', 1, $showcase->id ? $showcase->is_active : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
    {!! Form::label('is_active', 'Set Active', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned off, the '.__('showcase.showcase').' will not be visible to regular users.') !!}
</div>

<div class="text-right">
    {!! Form::submit($showcase->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
</div>

{!! Form::close() !!}

@if($showcase->id)

<h3>{{ucfirst(__('showcase.showcase'))}} Items</h3> 

<div class="alert alert-warning text-center">Other users cannot see items until the items are set to visible. </div>
@if($showcase->stock->where('quantity', '>', 0)->where('stock_type', '==', 'Item')->count())
<h3> Items <a class="small inventory-collapse-toggle collapse-toggle collapsed" href="#itemstockcollapsible" data-toggle="collapse">Collapse View</a></h3>
<div class="card-body inventory-body collapse show" id="itemstockcollapsible">
    <div id="showcaseStock">
        <div class="row col-12">
        @foreach($showcase->stock->where('quantity', '>', 0)->where('stock_type', '==', 'Item') as $stock)
        <div class="col-md-4">
            <div class="card p-3 my-1">
                <div class="row">
                    @if($stock->item->has_image)
                        <div class="col-2">
                            <img src="{{ $stock->item->imageUrl }}" style="width: 100%;" alt="{{ $stock->item->name }}">
                        </div>
                    @endif
                    <div class="col-{{ $stock->item->has_image ? '8' : '10' }}">
                        <div><a href="{{ $stock->item->idUrl }}"><strong>{{ $stock->item->name }} - {{ $stock->stock_type }}</strong></a></div>
                        <div><strong>Quantity: </strong> {!! $stock->quantity !!}</div>
                    </div>
                    @if(!$stock->is_visible)<div class="col-2"> <i class="fas fa-eye-slash"></i></div>@endif
                </div> 
                @include('home.showcases._edit_stock_modal', ['stock' => $stock])
                <div class="text-right">

                    <div class="btn btn-danger" onclick="removeShowcaseStock({{$stock->id}})">
                        {{-- trash icon --}}
                        <i class="fas fa-trash"></i>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    </div>
    </div>
@endif

@if($showcase->stock->where('quantity', '>', 0)->where('stock_type', '==', 'Pet')->count())
<h3> Pets <a class="small inventory-collapse-toggle collapse-toggle collapsed" href="#petstockcollapsible" data-toggle="collapse">Collapse View</a></h3>
<div class="card-body inventory-body collapse show" id="petstockcollapsible">
    <div id="showcaseStock">
        <div class="row col-12">
        @foreach($showcase->stock->where('quantity', '>', 0)->where('stock_type', '==', 'Pet') as $stock)
        <div class="col-md-4">
            <div class="card p-3 my-1">
                <div class="row">
                        <div class="col-2">
                            <img src="{{ $stock->item->variantimage($stock->variant_id) }}" class="img-fluid" style="width:50%;"/>
                        </div>
                    <div class="col-{{ $stock->item->has_image ? '8' : '10' }}">
                        <div><a href="{{ $stock->item->idUrl }}"><strong>{{ $stock->item->name }} - {{ $stock->stock_type }}</strong></a></div>
                        <div><strong>Quantity: </strong> {!! $stock->quantity !!}</div>
                        <span class="text-light badge badge-dark" style="font-size:95%;">{{ $stock->pet_name }}</span> 
                    </div>
                    @if(!$stock->is_visible)<div class="col-2"> <i class="fas fa-eye-slash"></i></div>@endif
                </div> 
                @include('home.showcases._edit_stock_modal', ['stock' => $stock])
                <div class="text-right">

                    <div class="btn btn-danger" onclick="removePetShowcaseStock({{$stock->id}})">
                        {{-- trash icon --}}
                        <i class="fas fa-trash"></i>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    </div>
    </div>
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
    function removeShowcaseStock(id) {
        loadModal("{{ url(__('showcase.showcases').'/stock/remove') }}/" + id, 'Remove Item');
    }

    function removePetShowcaseStock(id) {
        loadModal("{{ url(__('showcase.showcases').'/stock/removepet') }}/" + id, 'Remove Pet');
    }
    
    $('.delete-showcase-button').on('click', function(e) {
        e.preventDefault();
        loadModal("{{ url(__('showcase.showcases').'/delete') }}/{{ $showcase->id }}", 'Delete {{ucfirst(__('showcase.showcase'))}}');
    });

</script>
@endsection