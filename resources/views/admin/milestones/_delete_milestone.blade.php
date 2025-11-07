@if ($milestone)
    {!! Form::open(['url' => 'admin/data/milestones/delete/' . $milestone->id]) !!}

    <p>You are about to delete the milestone <strong>{{ $milestone->name }}</strong>. This is not reversible. If this milestone exists in at least one user's possession, you will not be able to delete this milestone.</p>
    <p>Are you sure you want to delete <strong>{{ $milestone->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete Milestone', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid milestone selected.
@endif
