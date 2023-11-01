{!! Form::hidden('showcase_id', $showcase->id) !!}
<table class="table table-sm" id="stockTable">
    <thead>
        <tr>
            <th width="15%">Item</th>
            <th width="15%">Visible?</th>
            <th width="20%">Removal Quantity</th>
        </tr>
    </thead>
    <tbody id="stockTableBody">
        @foreach ($showcase->stock->where('quantity', '>', 0) as $stock)
            <tr class="stock-row">
                {!! Form::hidden('stock_id[]', $stock->id) !!}
                <td>
                    @if (isset($stock->item->image_url))
                        <img class="small-icon" src="{{ $stock->item->image_url }}" alt="{{ $stock->item->name }}">
                    @endif
                    {!! $stock->item->name !!}
                    @if (!$stock->is_visible)
                        <i class="fas fa-eye-slash mr-1"></i>
                    @endif
                </td>
                <td>{!! Form::checkbox('is_visible[]', 1, $stock->is_visible, [
                    'class' => 'form-check-input',
                    'data-toggle' => 'toggle',
                ]) !!}
                </td>
                <td class="col-5">{!! Form::selectRange('quantity[]', 0, $stock->quantity, 0, [
                    'class' => 'quantity-select',
                    'type' => 'number',
                    'style' => 'min-width:40px;',
                ]) !!} /{{ $stock->quantity }} </td>
            </tr>
        @endforeach
    </tbody>
</table>
