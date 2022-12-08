@if($weapon)
    {!! Form::open(['url' => 'admin/weapon/delete/'.$weapon->id]) !!}

    <p>You are about to delete the weapon <strong>{{ $weapon->name }}</strong>. This is not reversible. If this weapon exists in at least one user's possession, you will not be able to delete this weapon.</p>
    <p>Are you sure you want to delete <strong>{{ $weapon->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete Weapon', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else 
    Invalid weapon selected.
@endif