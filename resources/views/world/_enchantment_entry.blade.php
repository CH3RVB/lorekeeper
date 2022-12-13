<div class="row world-entry">
    @if($imageUrl)
        <div class="col-md-3 world-entry-image"><a href="{{ $imageUrl }}" data-lightbox="entry" data-title="{{ $name }}"><img src="{{ $imageUrl }}" class="world-entry-image" /></a></div>
    @endif
    <div class="{{ $imageUrl ? 'col-md-9' : 'col-12' }}">
        <h3>{!! $name !!} @if(isset($idUrl) && $idUrl) <a href="{{ $idUrl }}" class="world-entry-search text-muted"><i class="fas fa-search"></i></a>  @endif</h3>
        <div class="row">
            @if($enchantment && isset($enchantment->category) && $enchantment->category)
                <div class="col-md">
                    <p><strong>Category:</strong> {!! $enchantment->category->displayName !!}</p>
                </div>
            @endif
        </div>
            <div class="world-entry-text">
            {!! $description !!}
                    <div class="row">
                        <div class="col-6">
                        @if($enchantment->stats->count())
                        <h5>Enchantment Stats</h5>
                            <div class="col-md">
                                @foreach($enchantment->stats as $stat)
                                <h5>{{$stat->stat->name}}</h5>
                                <p>+ {{ $stat->count }}</p>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>

                    @if($enchantment)
            <div class="row">
                <div class="col-6">
                @if($enchantment->parent_id && $enchantment->parent)
                    <h5>Parent</h5>
                    <div class="row">
                            <div class="col-md">
                                {!! $enchantment->parent->displayName !!} @if($enchantment->cost && $enchantment->currency_id >= 0) <small>(Upgrade costs {{ $enchantment->cost }} @if($enchantment->currency_id != 0)<img src="{!! $enchantment->currency->iconurl !!}">{!! $enchantment->currency->displayName !!}.)</small> @elseif($enchantment->currency_id == 0) stat points.)</small>@endif @else <small>(No upgrade cost set.)</small> @endif
                            </div>
                    </div>
                    @endif
                </div>
                <div class="col-6">
                @if($enchantment->children->count())
                    <h5>Children</h5>
                    <div class="row">
                        
                            @foreach($enchantment->children as $child)
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
</div>
