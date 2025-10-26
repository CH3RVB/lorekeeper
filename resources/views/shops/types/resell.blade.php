<div class="alert alert-warning my-2 text-center">
    <p>This shop is a resell shop. You can resell certain items from
        {!! $shop->tradeCategory() ? 'the ' . $shop->tradeCategory()->displayName : 'any' !!} category to it in exchange for currency.
    </p>
    @if (isset($data['alt_makes_stock']))
        <p>Your resold items to this shop will be added to the stock for other users to purchase.</p>
    @endif
    @if (isset($data['alt_cooldown']))
        <p>This shop's cooldown between resales is {{ $data['alt_cooldown'] }} minutes.</p>
        @if (Auth::check() && Auth::user()->altShopCooldown($shop))
            <p class="text-danger">You can resell again {!! pretty_date(Auth::user()->altShopCooldown($shop)) !!}!</p>
        @endif
    @endif
</div>
