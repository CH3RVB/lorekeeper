<h3>Gear Slot Tag</h3>

This allows a user to add a number of slots to a gear. The amount of given slots depends on the number inputted. 

    <div class="form-group">
        {!! Form::label('slotcount', 'Slots') !!}
        {!! Form::number('slotcount', ($tag->getData()['slotcount']), ['class' => 'form-control', 'placeholder' => 'Input slot number']) !!} 
    </div>