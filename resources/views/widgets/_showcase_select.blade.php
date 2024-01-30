<div id="stockTable">
    <div class="row border-bottom">
        <div class="col-6 col-md-3">Item</div>
        <div class="col-6 col-md-3 order-3 order-md-2">Visible?</div>
        <div class="col-6 col-md-3">Removal Quantity</div>
    </div>

    {!! $stocks->render() !!}
    @foreach ($stocks as $stock)
        <div class="row flex-wrap border-bottom" id="stockTableBody">
            {!! Form::hidden('stock_id[]', $stock->id) !!}

            <div class="col-6 col-md-3">
                @if (isset($stock->item->image_url))
                    <img class="small-icon" src="{{ $stock->item->image_url }}" alt="{{ $stock->item->name }}">
                @endif
                {!! $stock->item->name !!} - {{ $stock->stock_type }}
                @if (!$stock->is_visible)
                    <i class="fas fa-eye-slash mr-1"></i>
                @endif
            </div>

            <div class="col-6 col-md-3 order-3 order-md-2">
                {!! Form::checkbox('is_visible[' . $stock->id . ']', 1, $stock->is_visible ?? 1, [
                    'class' => 'form-check-input',
                    'data-toggle' => 'toggle',
                ]) !!}
            </div>

            <div class="col-6 col-md-3">
                {!! Form::selectRange('quantity[]', 0, $stock->quantity, 0, [
                    'class' => 'quantity-select',
                    'type' => 'number',
                    'style' => 'min-width:40px;',
                ]) !!} /{{ $stock->quantity }}
            </div>
        </div>
    @endforeach
    {!! $stocks->render() !!}
</div>
