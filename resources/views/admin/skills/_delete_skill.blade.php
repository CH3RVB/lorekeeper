@if($skill)
    {!! Form::open(['url' => 'admin/data/skills/delete/'.$skill->id]) !!}

    <p>You are about to delete the skill <strong>{{ $skill->name }}</strong>. This is not reversible.</p>
    <p>Are you sure you want to delete <strong>{{ $skill->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete Skill', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else 
    Invalid skill selected.
@endif