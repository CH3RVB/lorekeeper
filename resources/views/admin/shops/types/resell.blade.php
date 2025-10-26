<h3>Resale Shop Settings</h3>
<p><strong>Resale</strong> shops allow users to sell items to this shop for currency, and the resold items will appear in the shop for other users to purchase.</p>
<p>Items will be sold for their currency and price according to their alt shop resale value (set when creating/editing the item itself). You can further add some variance with the below settings.</p>
<ul>
    <li><strong>Grant Amount</strong> will let you give some variability with the amount the user will be granted when they resell their items. The result will be added onto or removed from the base currency amount, and given to the user.
    </li>
    <ul>
        <li>The variables can get a little too... expansive if not using decimals, suggested value for a very minor min/max variance might be something like -0.2 & 0.2.</li>
        @if (isset($data['alt_min_grant']) && isset($data['alt_max_grant']))
            <li><strong>Current Variance:</strong> {{ $data['alt_min_grant'] }} ~ {{ $data['alt_max_grant'] }}
            </li>
            <li><strong>Test value</strong>: {{ $testbase }} x {{ $testmultiplier }} = {{ $stockbase }} ({{ $payment }})</li>
        @else
            <li>Set the values to get a basic test.</li>
        @endif
    </ul>
    <div class="form-group alt_makes_stock {{ isset($data['alt_makes_stock']) ? '' : 'hide' }}">
        <li><strong>Shop Price</strong> is the final price the item will be when restocked into the shop (This will be ignored if you disable "alt_makes_stock").</li>
        <ul>
            <li>The variables can get a little too... expansive if not using decimals, suggested value for a very minor min/max variance might be something like -0.2 & 0.2.</li>
            @if (isset($data['alt_stock_price_min']) && isset($data['alt_stock_price_max']))
                <li><strong>Current Variance:</strong> {{ $data['alt_stock_price_min'] }} ~ {{ $data['alt_stock_price_max'] }}
                </li>
                <li><strong>Test value</strong>: {{ $stockbase }} x {{ $stock_multiplier }} = {{ $stockbase + $stockprice }} ({{ $stockprice }})</li>
            @else
                <li>Set the values to get a basic test.</li>
            @endif
        </ul>
    </div>
</ul>
<div class="row">
    <div class="col form-group">
        {!! Form::label('Grant Amount (MIN)') !!}
        {!! Form::number('alt_min_grant', isset($data['alt_min_grant']) ? $data['alt_min_grant'] : null, ['class' => 'form-control', 'step' => 'any']) !!}
    </div>
    <div class="col form-group">
        {!! Form::label('Grant Amount (MAX)') !!}
        {!! Form::number('alt_max_grant', isset($data['alt_max_grant']) ? $data['alt_max_grant'] : null, ['class' => 'form-control', 'step' => 'any']) !!}
    </div>
</div>
<div class="row alt_makes_stock {{ isset($data['alt_makes_stock']) ? '' : 'hide' }}">
    <div class="col form-group">
        {!! Form::label('Shop Price (MIN)') !!}{!! add_help('This will be multiplied by the amount of currency the user gets, and will be the final price for the item in the shop!') !!}
        {!! Form::number('alt_stock_price_min', isset($data['alt_stock_price_min']) ? $data['alt_stock_price_min'] : null, ['class' => 'form-control', 'step' => 'any']) !!}
    </div>
    <div class="col form-group">
        {!! Form::label('Shop Price (MAX)') !!}{!! add_help('This will be multiplied by the amount of currency the user gets, and will be the final price for the item in the shop!') !!}
        {!! Form::number('alt_stock_price_max', isset($data['alt_stock_price_max']) ? $data['alt_stock_price_max'] : null, ['class' => 'form-control', 'step' => 'any']) !!}
    </div>
</div>
