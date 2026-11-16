{!! Form::label($type . ' Type') !!}
{!! Form::select($prefix . 'limit_type[]', $limitTypes, null, [
    'class' => 'form-control limit-selectize limit-type',
    'placeholder' => 'Select ' . $type . ' Type',
]) !!}
