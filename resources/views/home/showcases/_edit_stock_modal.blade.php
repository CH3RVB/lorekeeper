<div class="p-2">
    {!! Form::open(['url' => __('showcase.showcases').'/stock/edit/'.$stock->id]) !!}
    <div>
        <div class="form-group">
            {!! Form::checkbox('is_visible', 1, $stock->is_visible ?? 1, ['class' => 'form-check-input stock-limited stock-toggle stock-field']) !!}
            {!! Form::label('is_visible', 'Set Visibility', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned off it will not appear in the '.__('showcase.showcase').'.') !!}
        </div>
    </div>


<div class="text-right mt-1">
    {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
</div>
{!! Form::close() !!}
</div>