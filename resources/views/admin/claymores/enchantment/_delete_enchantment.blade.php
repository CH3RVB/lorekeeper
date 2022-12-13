@if($enchantment)
    {!! Form::open(['url' => 'admin/enchantment/delete/'.$enchantment->id]) !!}

    <p>You are about to delete the enchantment <strong>{{ $enchantment->name }}</strong>. This is not reversible. If this enchantment exists in at least one user's possession, you will not be able to delete this enchantment.</p>
    <p>Are you sure you want to delete <strong>{{ $enchantment->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete Enchantment', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else 
    Invalid enchantment selected.
@endif