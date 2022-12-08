@if($drop)
    {!! Form::open(['url' => 'admin/data/pet-drops/delete/'.$drop->id]) !!}

    <p>You are about to delete this pet drop data. This is not reversible. If pets are in groups or have drops associated with this data, you will not be able to delete it. Consider setting this data to inactive instead.</p>
    <p>Are you sure you want to delete this drop?</p>

    <div class="text-right">
        {!! Form::submit('Delete Drop Data', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid drop selected.
@endif
