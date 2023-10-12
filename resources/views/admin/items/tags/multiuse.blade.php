<h3>Multi-use Items</h3>

<h4>Usage Amount</h4>
<p>Input discount percent. You can select what coupons can be used in each shop on the shop edit page.</p>

    <div class="form-group">
        {!! Form::label('uses', 'Number of Uses') !!}
        {!! Form::number('uses', ($tag->getData()['uses']), ['class' => 'form-control', 'placeholder' => 'Use Number', 'min' => 1 ]) !!} 
    </div>

    <div class="form-group">
        {!! Form::checkbox('infinite', 1, ($tag->getData()['infinite']), ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!} 
        {!! Form::label('infinite', 'Should this item be unlimited use?', ['class' => 'ml-3 form-check-label']) !!}
    </div>