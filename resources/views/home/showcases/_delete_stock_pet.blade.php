@if($stock)
    {!! Form::open(['url' =>  __('showcase.showcases').'/stock/removepet/'.$stock->id]) !!}
    {{ Form::hidden('showcase_id', $showcase->id) }}

    <p>You are about to remove the pet <strong>{{ $stock->item->name }}</strong>.</p>
    <p>Are you sure you want to remove <strong>{{ $stock->item->name }}</strong>? This pet will be returned to your inventory.</p>

    <div class="text-right">
        {!! Form::submit('Remove Pet', ['class' => 'btn btn-danger']) !!}
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