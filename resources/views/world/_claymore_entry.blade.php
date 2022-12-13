<div class="row world-entry">
    @if($imageUrl)
        <div class="col-md-3 world-entry-image"><a href="{{ $imageUrl }}" data-lightbox="entry" data-title="{{ $name }}"><img src="{{ $imageUrl }}" class="world-entry-image" /></a></div>
    @endif
    <div class="{{ $imageUrl ? 'col-md-9' : 'col-12' }}">
        <h3>{!! $name !!} @if(isset($idUrl) && $idUrl) <a href="{{ $idUrl }}" class="world-entry-search text-muted"><i class="fas fa-search"></i></a>  @endif</h3>
        <div class="row">
            @if($item && isset($item->category) && $item->category)
                <div class="col-md">
                    <p><strong>Category:</strong> {!! $item->category->name !!}</p>
                </div>
            @endif

            <div class="col-md">
                    <p><strong>Enchantment Slots:</strong> {!! $item->min !!} - {!! $item->max !!}</p>
                </div>
        </div>
        <div class="world-entry-text">
            {!! $description !!}
            @if($item)
            <div class="row">
                        <div class="col-6">
                        @if($item->stats->count())
                        <h5>item Stats</h5>
                            <div class="col-md">
                                @foreach($item->stats as $stat)
                                <h5>{{$stat->stat->name}}</h5>
                                <p>+ {{ $stat->count }}</p>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>

                    @if($item)
            <div class="row">
                <div class="col-6">
                @if($item->parent_id && $item->parent)
                    <h5>Parent</h5>
                    <div class="row">
                            <div class="col-md">
                            {!! $item->parent->displayName !!} @if($item->cost && $item->currency_id >= 0) <small>(Upgrade costs {{ $item->cost }} @if($item->currency_id != 0)<img src="{!! $item->currency->iconurl !!}">{!! $item->currency->displayName !!}.)</small> @elseif($item->currency_id == 0) stat points.)</small>@endif @else <small>(No upgrade cost set.)</small> @endif
                            </div>
                    </div>
                    @endif
                </div>
                <div class="col-6">
                @if($item->children->count())
                    <h5>Children</h5>
                    <div class="row">
                        
                            @foreach($item->children as $child)
                                <div class="col-md">
                                    {!! $child->displayName !!}
                                </div>
                            @endforeach
                        
                    </div>
                    @endif
                </div>
            </div>
            @endif
            </div>
    </div>
    @endif
</div>