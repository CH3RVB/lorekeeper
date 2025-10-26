<div class="alert alert-warning my-2 text-center">
    <p>This shop is a trade-in shop. You can't buy items directly, but you can trade for its stock by using
        {{ $shop->alt_data['alt_amount'] }} items from
        {!! $shop->tradeCategory() ? 'the ' . $shop->tradeCategory()->displayName : 'any' !!} category.
    </p>
    @if (isset($shop->alt_data['alt_cooldown']))
        <p>This shop's cooldown between trades is {{ $shop->alt_data['alt_cooldown'] }} minutes.</p>
    @endif
</div>

@if (Auth::check() && isset($shop->alt_data['alt_cooldown']) && Auth::user()->altShopCooldown($shop))
    <div class="alert alert-danger my-2 text-center">
        You can make a trade {!! pretty_date(Auth::user()->altShopCooldown($shop)) !!}!</div>
@elseif(Auth::check())
    @if (($stock->is_fto && Auth::user()->settings->is_fto) || !$stock->is_fto)
        <h5>
            Trade
            <span class="float-right">
                In Inventory: {{ $userOwned }}
            </span>
        </h5>
        @if ($stock->is_limited_stock && $stock->quantity == 0)
            <div class="alert alert-warning mb-0">This item is out of stock.</div>
        @elseif($purchaseLimitReached)
            <div class="alert alert-warning mb-0">
                You have already traded for the limit of {{ $stock->purchase_limit }} of this item
                @if ($stock->purchase_limit_timeframe !== 'lifetime')
                    within the {{ $stock->purchase_limit_timeframe }} reset
                @endif.
            </div>
        @else
            @if ($stock->purchase_limit)
                <div class="alert alert-warning mb-3">
                    You have traded for this item {{ $userPurchaseCount }} times
                    @if ($stock->purchase_limit_timeframe !== 'lifetime')
                        within the {{ $stock->purchase_limit_timeframe }} reset
                    @endif
                </div>
            @endif
            {!! Form::open(['url' => 'shops/buy']) !!}
            {!! Form::hidden('shop_id', $shop->id) !!}
            {!! Form::hidden('stock_id', $stock->id) !!}
            {!! Form::label('quantity', 'Quantity') !!}
            {!! Form::hidden('bank', 'user') !!}
            {!! Form::selectRange('quantity', 1, $quantityLimit, 1, ['class' => 'form-control mb-3']) !!}

            @include('widgets._inventory_select', ['user' => Auth::user(), 'inventory' => $tradeInventory, 'categories' => $categories, 'selected' => [], 'hideCollapse' => true])
            @include('widgets._inventory_select_js')

            <div class="text-right">
                {!! Form::submit('Trade', ['class' => 'btn btn-primary']) !!}
            </div>
            {!! Form::close() !!}
        @endif
    @else
        <div class="alert alert-danger">You must be a FTO to trade for this item.</div>
    @endif
@else
    <div class="alert alert-danger">You must be logged in to trade for this item.</div>
@endif
