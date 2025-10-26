<h5>Trade Shop Settings</h5>
<p><strong>Trade-in</strong> shops allow users to take an item of their choice by trading one of their items for the selected item.</p>
<div class="row">
    <div class="col form-group">
        {!! Form::label('Amount') !!}{!! add_help('Amount of a trade item required to trade in.') !!}
        {!! Form::number('alt_amount', isset($data['alt_amount']) ? $data['alt_amount'] : null, ['class' => 'form-control cooldown-field']) !!}
    </div>
</div>
