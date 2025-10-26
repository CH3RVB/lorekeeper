<div class="alert alert-warning my-2 text-center">
    <p>This shop is a trade-in shop. You can't buy items directly, but you can trade for its stock by using
        {{ $data['alt_amount'] }} items from
        {!! $shop->tradeCategory() ? 'the ' . $shop->tradeCategory()->displayName : 'any' !!} category.
    </p>
    @if (isset($data['alt_makes_stock']))
        <p>Your traded items to this shop will be added to the stock for other users to trade for.</p>
    @endif
    @if (isset($data['alt_cooldown']))
        <p>This shop's cooldown between trades is {{ $data['alt_cooldown'] }} minutes.</p>
        @if (Auth::check() && Auth::user()->altShopCooldown($shop))
            <p class="text-danger">You can make a trade {!! pretty_date(Auth::user()->altShopCooldown($shop)) !!}!</p>
        @endif
    @endif
</div>
