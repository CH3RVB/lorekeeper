{!! Form::open(['url' => 'characters/class/edit/'.$character->id]) !!}
    <div class="form-group">
        {!! Form::label('Class') !!}
        {!! Form::select('class_id', $classes, $character->class_id, ['class' => 'form-control']) !!}
    </div>

    <div class="text-right">
        {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
    </div>
{!! Form::close() !!}

<script>

</script>
