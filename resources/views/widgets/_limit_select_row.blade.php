@php
    // This represents a common source and definition for assets used in limit_select
    // While it is not per se as tidy as defining these in the controller(s),
    // doing so this way enables better compatibility across disparate extensions

    if (!isset($type)) {
        $type = 'Limit';
    }
    if (!isset($prefix)) {
        $prefix = '';
    }
    if (!isset($showRecipient)) {
        $showRecipient = false;
    }
    if (!isset($isCharacter)) {
        $isCharacter = false;
    }

    $limitRecipients = ['Character' => 'Character', 'User' => 'User'];
    $recipient = $isCharacter ? 'Character' : 'User';

    // Fetch limit data, defined in Helpers
    if ($showRecipient) {
        foreach ($limitRecipients as $recipient) {
            $limitData[$recipient] = getLimitData($recipient);
            // Fetch valid limit types, defined in Helpers
            // This is also called individually for each pre-existing limit row, to fill out the table accurately
            // All available limit or asset types should now be added to getLimitTypes
            $limitTypes[$recipient] = getLimitTypes($recipient);
        }
    } else {
        $limitData = getLimitData($recipient);
        $limitTypes = getLimitTypes($recipient);
    }
@endphp
<div id="{{ $prefix }}limitRow">
    <div class="{{ $prefix }}limit-row row border-bottom mb-3 hide">
        @if ($showRecipient)
            <div class="col-md-2 form-group">
                {!! Form::label('Limit Type') !!}
                {!! Form::select($prefix . 'recipient_type[]', $limitRecipients, null, ['class' => 'form-control recipient-type', 'placeholder' => 'Select Recipient Type']) !!}
            </div>
        @endif
        <div class="col-md-2 form-group {{ $prefix }}limit-row-type">
            {!! Form::label('Limit Type') !!}
            {!! Form::select($prefix . 'limit_type[]', $limitTypes, $showRecipient ? [] : $limitTypes, ['class' => 'form-control limit-type', 'placeholder' => 'Select Limit Type']) !!}
        </div>
        <div class="col-md-4 form-group {{ $prefix }}limit-select">
        </div>
        <div class="col-md-3 limit-modifiers hide">
            <div class="form-group quantity">
                {!! Form::label('Quantity') !!}
                {!! Form::number($prefix . 'quantity[]', 0, ['class' => 'form-control', 'placeholder' => 'Enter Quantity', 'min' => 0, 'step' => 1]) !!}
            </div>
            <div class="form-group hide debit">
                {!! Form::label('Debit') !!}
                {!! Form::select($prefix . 'debit[]', [true => 'Debit', false => 'Don\'t Debit'], false, ['class' => 'form-control']) !!}
            </div>
        </div>
        @if (isset($extra_fields))
            @foreach ($extra_fields as $field => $data)
                <div class="col-md-3 form-group">
                    @php
                        $field_name = $prefix . $field . '[]';
                        $value = $data['default'] ?? null;
                        $attributes = $data['attributes'] ?? [];
                    @endphp
                    {!! Form::{$data['type']}($field_name, $value, array_merge(['class' => 'form-control ' . ($data['class'] ?? ''), 'placeholder' => $data['placeholder'] ?? $data['label']], $attributes)) !!}
                </div>
            @endforeach
        @endif
        <div class="limit-delete col-md-1 d-flex align-items-center">
            <div class="btn btn-danger {{ $prefix }}remove-limit mx-auto">X</div>
        </div>
    </div>
</div>


<div id="{{ $prefix }}rows" class="hide">
    {!! Form::label('limit_id[]', 'Limit', ['class' => $prefix . 'limit-label']) !!}
    @if ($showRecipient)
        @foreach ($limitRecipients as $recipient)
            <div id="limit-ids-{{ strtolower($recipient) }}">
                @foreach (getLimitTypes($recipient) as $limitKey => $limitType)
                    {!! Form::select($prefix . 'limit_id[]', $limitData[$recipient][$limitKey], null, ['class' => 'form-control dfds object-select ' . strtolower($limitKey) . '-select', 'placeholder' => 'Select ' . $limitType]) !!}
                @endforeach
            </div>
        @endforeach
    @else
        @foreach ($limitTypes as $limitKey => $limitType)
            {!! Form::select($prefix . 'limit_id[]', $limitData[$limitKey], null, ['class' => 'form-control object-select ' . strtolower($limitKey) . '-select', 'placeholder' => 'Select ' . $limitType]) !!}
        @endforeach
    @endif
</div>
