@if($stock)
    {!! Form::open(['url' => 'usershops/stock/removepet/'.$stock->id]) !!}
    {{ Form::hidden('user_shop_id', $shop->id) }}
    
    <p>You are about to remove the stock <strong>{{ $stock->item->name }}</strong>.</p>
    <p>Are you sure you want to remove <strong>{{ $stock->item->name }}</strong>? This pet will be returned to your inventory.</p>

    <div class="text-right">
        {!! Form::submit('Remove Stock', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}

    <script>
    function updateQuantities($checkbox) {
        var $rowId = "#stock" + $checkbox.value
        $($rowId).find('.quantity-select').prop('name', $checkbox.checked ? 'quantities[]' : '')
    }
</script>
@else 
    Invalid stock selected.
@endif


