@if($gear)
    {!! Form::open(['url' => 'admin/gear/delete/'.$gear->id]) !!}

    <p>You are about to delete the gear <strong>{{ $gear->name }}</strong>. This is not reversible. If this gear exists in at least one user's possession, you will not be able to delete this gear.</p>
    <p>Are you sure you want to delete <strong>{{ $gear->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete Gear', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else 
    Invalid gear selected.
@endif