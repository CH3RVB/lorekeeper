{!! Form::open(['url' => 'admin/data/limit-maker/edit/' . base64_encode(urlencode(get_class($object))) . '/' . $object->id]) !!}

@php
    // This file represents a common source and definition for assets used in loot_select
    // While it is not per se as tidy as defining these in the controller(s),
    // doing so this way enables better compatibility across disparate extensions
    $items = \App\Models\Item\Item::orderBy('name')->pluck('name', 'id');
    $currencies = \App\Models\Currency\Currency::where('is_user_owned', 1)->orderBy('name')->pluck('name', 'id');
@endphp

<hr style="margin-top: 3em;">

<div class="card mb-3">
    <div class="card-header h2">
        <a href="#" class="btn btn-outline-info float-right" id="addLimit">Add Limit</a>
        {{ ucfirst($type) }} Limits
    </div>
    <div class="card-body" style="clear:both;">
        <p>You can add limits to this {{ $type }} here. A user must obtain all requirements in order to complete this action.</p>

        <h3>Settings</h3>
        <div class="row">
            <div class="col-md-6 form-group">
                {!! Form::checkbox('debit_limits', 1, $object->limitSettings ? $object->limitSettings->debit_limits : 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                {!! Form::label('debit_limits', 'Remove Limits?', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is set, after the limits have been checked, they will then be removed from the ownership of the player/character.') !!}
            </div>
            <div class="col-md-6 form-group">
                {!! Form::checkbox('use_characters', 1, $object->limitSettings ? $object->limitSettings->use_characters : 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                {!! Form::label('use_characters', 'Use Characters?', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is set, this will check for limits of a specific character. Make sure the character\'s information is actually being checked!') !!}
            </div>
        </div>
        <div class="mb-3">
            <table class="table table-sm" id="limitTable">
                <thead>
                    <tr>
                        <th width="35%">Limit Type</th>
                        <th width="35%">Limit</th>
                        <th width="35%">Quantity</th>
                        <th width="10%"></th>
                    </tr>
                </thead>
                <tbody id="limitTableBody">
                    @if ($limits)
                        @foreach ($limits as $limit)
                            <tr class="limit-row">
                                <td>{!! Form::select('limit_type[]', ['Item' => 'Item', 'Currency' => 'Currency'], $limit->limit_type, ['class' => 'form-control limit-type', 'placeholder' => 'Select limit Type']) !!}</td>
                                <td class="limit-row-select">
                                    @if ($limit->limit_type == 'Item')
                                        {!! Form::select('limit_id[]', $items, $limit->limit_id, ['class' => 'form-control item-select selectize', 'placeholder' => 'Select Item']) !!}
                                    @elseif($limit->limit_type == 'Currency')
                                        {!! Form::select('limit_id[]', $currencies, $limit->limit_id, ['class' => 'form-control currency-select selectize', 'placeholder' => 'Select Currency']) !!}
                                    @endif
                                </td>
                                <td>
                                    {!! Form::number('quantity[]', $limit->quantity, ['class' => 'form-control', 'placeholder' => 'Set Quantity', 'min' => 1]) !!}
                                </td>
                                <td class="text-right"><a href="#" class="btn btn-danger remove-limit-button">Remove</a></td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>


<div class="text-right">
    {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
</div>

{!! Form::close() !!}

<hr style="margin-bottom: 3em;">

<div id="limitRowData" class="hide">
    <table class="table table-sm">
        <tbody id="limitRow">
            <tr class="limit-row">
                <td>{!! Form::select('limit_type[]', ['Item' => 'Item', 'Currency' => 'Currency'], null, ['class' => 'form-control limit-type', 'placeholder' => 'Select limit Type']) !!}</td>
                <td class="limit-row-select"></td>
                <td>{!! Form::number('quantity[]', 1, ['class' => 'form-control', 'placeholder' => 'Set Quantity', 'min' => 1]) !!} </td>
                <td class="text-right"><a href="#" class="btn btn-danger remove-limit-button">Remove</a></td>
            </tr>
        </tbody>
    </table>
    {!! Form::select('limit_id[]', $items, null, ['class' => 'form-control item-select', 'placeholder' => 'Select Item']) !!}
    {!! Form::select('limit_id[]', $currencies, null, ['class' => 'form-control currency-select', 'placeholder' => 'Select Currency']) !!}
</div>


<script>
    $(document).ready(function() {
        var $limitTable = $('#limitTableBody');
        var $limitRow = $('#limitRow').find('.limit-row');
        var $itemSelect = $('#limitRowData').find('.item-select');
        var $currencySelect = $('#limitRowData').find('.currency-select');


        $('#limitTableBody .selectize').selectize();
        attachLimitTypeListener($('#limitTableBody .limit-type'));
        attachRemoveListener($('#limitTableBody .remove-limit-button'));

        $('#addLimit').on('click', function(e) {
            e.preventDefault();
            var $clone = $limitRow.clone();
            $limitTable.append($clone);
            attachLimitTypeListener($clone.find('.limit-type'));
            attachRemoveListener($clone.find('.remove-limit-button'));
        });

        $('.limit-type').on('change', function(e) {
            var val = $(this).val();
            var $cell = $(this).parent().find('.limit-row-select');

            var $clone = null;
            if (val == 'Item') $clone = $itemSelect.clone();
            else if (val == 'Currency') $clone = $currencySelect.clone();

            $cell.html('');
            $cell.append($clone);
        });

        function attachLimitTypeListener(node) {
            node.on('change', function(e) {
                var val = $(this).val();
                var $cell = $(this).parent().parent().find('.limit-row-select');

                var $clone = null;
                if (val == 'Item') $clone = $itemSelect.clone();
                else if (val == 'Currency') $clone = $currencySelect.clone();

                $cell.html('');
                $cell.append($clone);
                $clone.selectize();
            });
        }

        function attachRemoveListener(node) {
            node.on('click', function(e) {
                e.preventDefault();
                $(this).parent().parent().remove();
            });
        }

    });
</script>
