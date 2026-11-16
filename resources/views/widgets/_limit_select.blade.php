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

<div class="limit">
    <div id="{{ $prefix }}limits">
        @if ($limits)
            <h5>Limits for {!! $limits->first()->object->displayName !!}</h5>
        @endif
        <div class="row border-bottom mb-3">
            @if (!$hideIsUnlocked)
                <div class="col-md form-group">
                    {!! Form::label('is_unlocked', 'Is Unlocked?', ['class' => 'form-label font-weight-bold']) !!}
                    <p>
                        If this is set to "No", the object will continue to be locked until all requirements are met, every time the user attempts to use or interact with it.
                        <br />
                        If this is set to "Yes", the object will be unlocked for the user to interact with indefinitely after the requirements are met once.
                        <br />
                        The "Yes" option is good for one-time unlocks such as shops, locations, certain prompts, etc.
                    </p>
                    {!! Form::select($prefix . 'is_unlocked', [true => 'Yes', false => 'No'], $limits ? $limits->first()->is_unlocked : false, ['class' => 'form-control']) !!}
                </div>
            @else
                {!! Form::hidden($prefix . 'is_unlocked', $isUnlocked) !!}
            @endif
            @if (!$hideAutoUnlock)
                <div class="col-md form-group border-left">
                    {!! Form::label('is_auto_unlocked', 'Automatically Unlock?', ['class' => 'form-label font-weight-bold']) !!} {!! add_help("This only affects objects with 'Is Unlocked?' set to 'Yes'.") !!}
                    <p>
                        If this is set to "No", the user must manually unlock the object by interacting with it - ex. clicking on the "Unlock" button.
                    <div class="text-warning">
                        This will prevent the limits from being used as part of a series of actions, ex. prompt submissions.
                    </div>
                    <br />
                    If this is set to "Yes", the object will be automatically unlocked when the user attempts to access them - ex. when a user enters a shop.
                    <br />
                    This setting is good for preventing users from being debited before being certain they want to interact with the object.
                    <div class="text-danger">
                        This option is not suitable for objects that should have limits as part of an action workflow, ex. prompt submissions.
                    </div>
                    </p>
                    {!! Form::select($prefix . 'is_auto_unlocked', [true => 'Yes', false => 'No'], $limits ? $limits->first()->is_auto_unlocked : false, ['class' => 'form-control']) !!}
                </div>
            @else
                {!! Form::hidden($prefix . 'is_auto_unlocked', true) !!}
            @endif
        </div>

        @if ($limits)
            @foreach ($limits as $limit)
                <div class="{{ $prefix }}limit-row row border-bottom mb-3">
                    @if ($showRecipient)
                        <div class="col-md-2 form-group">
                            {!! Form::label('Limit Type') !!}
                            {!! Form::select($prefix . 'recipient_type[]', $limitRecipients, $limit->recipient_type ?? $recipient, ['class' => 'form-control recipient-type', 'placeholder' => 'Select Recipient Type']) !!}
                        </div>
                    @endif
                    <div class="col-md-2 form-group {{ $prefix }}limit-row-type">
                        {!! Form::label('Limit Type') !!}
                        {!! Form::select($prefix . 'limit_type[]', getLimitTypes($limit->recipient_type ?? $recipient), $limit->limit_type, ['class' => 'form-control limit-selectize limit-type', 'placeholder' => 'Select Limit Type']) !!}
                    </div>
                    <div class="col-md-4 form-group {{ $prefix }}limit-select">
                        {!! Form::label('limit_id[]', 'Limit') !!}
                        {!! Form::select($prefix . 'limit_id[]', $showRecipient ? $limitData[$limit->recipient_type ?? $recipient][$limit->limit_type] : $limitData[$limit->limit_type], $limit->limit_id, [
                            'class' => 'form-control limit-selectize ' . strtolower($limit->limit_type) . '-select',
                            'placeholder' => 'Select ' . ($limitTypes[$limit->limit_type] ?? 'Limit'),
                        ]) !!}
                    </div>
                    <div class="col-md-3 limit-modifiers {{ in_array($limit->limit_type, $debitableLimits) || in_array($limit->limit_type, $countableLimits) ? '' : 'hide' }}">
                        <div class="form-group quantity {{ in_array($limit->limit_type, $countableLimits) ? '' : 'hide' }}">
                            {!! Form::label('Quantity') !!}
                            {!! Form::number($prefix . 'quantity[]', $limit->quantity, ['class' => 'form-control', 'placeholder' => 'Enter Quantity', 'min' => 0, 'step' => 1]) !!}
                        </div>
                        <div class="form-group debit {{ in_array($limit->limit_type, $debitableLimits) ? '' : 'hide' }}">
                            {!! Form::label('Debit') !!}
                            {!! Form::select($prefix . 'debit[]', [true => 'Debit', false => 'Don\'t Debit'], $limit->debit, ['class' => 'form-control']) !!}
                        </div>
                    </div>
                    @if (isset($extra_fields))
                        @foreach ($extra_fields as $field => $data)
                            <td>
                                @php
                                    $field_name = $prefix . $field . '[]';
                                    $value = $limit->data[$field] ?? ($data['default'] ?? null);
                                    $attributes = $data['attributes'] ?? [];
                                @endphp
                                {!! Form::{$data['type']}($field_name, $value, array_merge(['class' => 'form-control ' . ($data['class'] ?? ''), 'placeholder' => $data['placeholder'] ?? $data['label']], $attributes)) !!}
                            </td>
                        @endforeach
                    @endif
                    <div class="limit-delete {{ in_array($limit->limit_type, $debitableLimits) || in_array($limit->limit_type, $countableLimits) ? 'col-md-1' : 'col-md-5' }} d-flex align-items-center">
                        <div class="btn btn-danger {{ $prefix }}remove-limit mx-auto">X</div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
    <div class="btn btn-secondary" id="{{ $prefix }}add-limit">Add Limit</div>
</div>
