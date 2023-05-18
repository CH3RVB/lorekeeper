@extends('home.showcases.layout')

@section('home.showcases-title') {{__('showcase.showcase')}} Index @endsection

@section('home.showcases-content')
{!! breadcrumbs([ucfirst(__('showcase.showcases')) => __('showcase.showcases').'/'.__('showcase.showcase').'-index', $showcase->name => __('showcase.showcases').'/'.__('showcase.showcase').'/1']) !!}

@if(Auth::check() && Auth::user()->id === $showcase->user_id || Auth::user()->hasPower('edit_inventories'))
    <a data-toggle="tooltip" title="Edit {{ucfirst(__('showcase.showcase'))}}" href="{{ url(__('showcase.showcases').'/edit').'/'.$showcase->id }}" class="mb-2 float-right"><h3><i class="fas fa-pencil-alt"></i></h3></a>
@endif

<h1>
   {{ $showcase->name }} <a href="{{ url('reports/new?url=') . $showcase->url }}"><i class="fas fa-exclamation-triangle fa-xs" data-toggle="tooltip" title="Click here to report this {{__('showcase.showcase')}}." style="opacity: 50%; font-size:0.5em;"></i></a>
</h1>
<div class="mb-3">
    Owned by {!! $showcase->user->displayName !!}
</div>

<div class="text-center">
    @if($showcase->showcaseImageUrl)
    <img src="{{ $showcase->showcaseImageUrl }}" style="max-width:100%" alt="{{ $showcase->name }}" />
    @endif
    <p>{!! $showcase->parsed_description !!}</p>
</div>
@if(count($items))
<h3> Items <a class="small inventory-collapse-toggle collapse-toggle collapsed" href="#itemstockcollapsible" data-toggle="collapse">Collapse View</a></h3>
<div class="card mb-3 inventory-category collapse show" id="itemstockcollapsible">
            <div class="card-body inventory-body">
                <div class="mb-3">
        <ul class="nav nav-tabs card-header-tabs">
            @foreach($items as $categoryId=>$categoryItems)
                <li class="nav-item">
                    <a class="nav-link {{ $loop->first ? 'active' : '' }}" id="categoryTab-{{ isset($categories[$categoryId]) ? $categoryId : 'misc'}}" data-toggle="tab" href="#category-{{ isset($categories[$categoryId]) ? $categoryId : 'misc'}}" role="tab">
                        {!! isset($categories[$categoryId]) ? $categories[$categoryId]->name : 'Miscellaneous' !!}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
    <div class="card-body tab-content">
        @foreach($items as $categoryId=>$categoryItems)
            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="category-{{ isset($categories[$categoryId]) ? $categoryId : 'misc'}}">
                @foreach($categoryItems->chunk(4) as $chunk)
                    <div class="row mb-3">
                        @foreach($chunk as $item)
                        <div class="col-sm-3 col-6 text-center inventory-item" data-id="{{ $item->pivot->id }}">
                            <div class="mb-1">
                                <img src="{{ $item->imageUrl }}" class="inventory-stack" alt="{{ $item->name }}" />
                            </div>
                            <div>
                                <strong class="inventory-stack inventory-stack-name">{{ $item->name }}</strong>
                                <div>Held: {{ $item->pivot->quantity }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
</div>
</div>
@endif

@endsection