<h4>Populate Default Criteria</h4>
<p>You can populate this {{$type}} with the selected defaults.</p>
@php
    $defaults = \App\Models\Criteria\CriterionDefault::orderBy('name')->get();
@endphp
<div class="row">
    @foreach ($defaults as $default)
        <div class="col-md form-group">
            {!! Form::checkbox('default_criteria['.$default->id.']', 1, 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('default_criteria['.$default->id.']', $default->name, ['class' => 'form-check-label ml-3']) !!} {!! add_help('Toggle on to populate this criterion set.') !!}
        </div>
    @endforeach
</div>
