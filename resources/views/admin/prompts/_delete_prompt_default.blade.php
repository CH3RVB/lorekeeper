@if($default)
    {!! Form::open(['url' => 'admin/data/prompt-defaults/delete/'.$default->id]) !!}

    <p>You are about to delete the prompt reward default <strong>"{{ $default->name }}"</strong>. This is not reversible.</p>
    <p>Are you sure you want to delete <strong>"{{ $default->name }}"</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete '.$default->name, ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else 
    Invalid default selected.
@endif 