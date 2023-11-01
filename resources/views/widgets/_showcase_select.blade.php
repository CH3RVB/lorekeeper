<div id="stockTable">
    <div class="row border-bottom">
        <div class="col-6 col-md-3">Item</div>
        <div class="col-6 col-md-3 order-3 order-md-2">Visible?</div>
        <div class="col-6 col-md-3">Removal Quantity</div>
    </div>

    @foreach ($showcase->stock->where('quantity', '>', 0) as $stock)
        <div class="row flex-wrap border-bottom" id="stockTableBody">
            {!! Form::hidden('stock_id[]', $stock->id) !!}

            <div class="col-6 col-md-3">
                @if (isset($stock->item->image_url))
                    @if ($stock->stock_type == 'Pet')
                        <img class="small-icon" src="{{ $stock->item->VariantImage($stock->variant_id) }}"
                            alt="{{ $stock->item->name }}">
                    @else
                        <img class="small-icon" src="{{ $stock->item->image_url }}" alt="{{ $stock->item->name }}">
                    @endif
                @endif
                @if ($stock->stock_type == 'Pet')
                    {{ $stock->item->VariantName($stock->variant_id) }} - {{ $stock->stock_type }}
                @else
                    {!! $stock->item->name !!} - {{ $stock->stock_type }}
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
</div>
