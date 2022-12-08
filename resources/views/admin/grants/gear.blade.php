@extends('admin.layout')

@section('admin-title') Grant Gear @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Grant Gear' => 'admin/grants/gear']) !!}

<h1>Grant Gear</h1>

{!! Form::open(['url' => 'admin/grants/gear']) !!}

<h3>Basic Information</h3>

<div class="form-group">
    {!! Form::label('names[]', 'Username(s)') !!} {!! add_help('You can select up to 10 users at once.') !!}
    {!! Form::select('names[]', $users, null, ['id' => 'usernameList', 'class' => 'form-control', 'multiple']) !!}
</div>

<div class="form-group">
    {!! Form::label('Gear(s)') !!} {!! add_help('Must have at least 1 gear and Quantity must be at least 1.') !!}
    <div id="gearList">
        <div class="d-flex mb-2">
            {!! Form::select('gear_ids[]', $gears, null, ['class' => 'form-control mr-2 default gear-select', 'placeholder' => 'Select Gear']) !!}
            {!! Form::text('quantities[]', 1, ['class' => 'form-control mr-2', 'placeholder' => 'Quantity']) !!}
            <a href="#" class="remove-gear btn btn-danger mb-2 disabled">×</a>
        </div>
    </div>
    <div><a href="#" class="btn btn-primary" id="add-gear">Add Gear</a></div>
    <div class="gear-row hide mb-2">
        {!! Form::select('gear_ids[]', $gears, null, ['class' => 'form-control mr-2 gear-select', 'placeholder' => 'Select Gear']) !!}
        {!! Form::text('quantities[]', 1, ['class' => 'form-control mr-2', 'placeholder' => 'Quantity']) !!}
        <a href="#" class="remove-gear btn btn-danger mb-2">×</a>
    </div>
</div>

<div class="form-group">
    {!! Form::label('data', 'Reason (Optional)') !!} {!! add_help('A reason for the grant. This will be noted in the logs and in the inventory description.') !!}
    {!! Form::text('data', null, ['class' => 'form-control', 'maxlength' => 400]) !!}
</div>

<h3>Additional Data</h3>

<div class="form-group">
    {!! Form::label('notes', 'Notes (Optional)') !!} {!! add_help('Additional notes for the gear. This will appear in the gear\'s description, but not in the logs.') !!}
    {!! Form::text('notes', null, ['class' => 'form-control', 'maxlength' => 400]) !!}
</div>

<div class="form-group">
    {!! Form::checkbox('disallow_transfer', 1, 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
    {!! Form::label('disallow_transfer', 'Account-bound', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is on, the recipient(s) will not be able to transfer this gear to other users. Gear that disallow transfers by default will still not be transferrable.') !!}
</div>

<div class="text-right">
    {!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
</div>

{!! Form::close() !!}

<script>
    $(document).ready(function() {
        $('#usernameList').selectize({
            maxGear: 10
        });
        $('.default.gear-select').selectize();
        $('#add-gear').on('click', function(e) {
            e.preventDefault();
            addGearRow();
        });
        $('.remove-gear').on('click', function(e) {
            e.preventDefault();
            removeGearRow($(this));
        })
        function addGearRow() {
            var $rows = $("#gearList > div")
            if($rows.length === 1) {
                $rows.find('.remove-gear').removeClass('disabled')
            }
            var $clone = $('.gear-row').clone();
            $('#gearList').append($clone);
            $clone.removeClass('hide gear-row');
            $clone.addClass('d-flex');
            $clone.find('.remove-gear').on('click', function(e) {
                e.preventDefault();
                removeGearRow($(this));
            })
            $clone.find('.gear-select').selectize();
        }
        function removeGearRow($trigger) {
            $trigger.parent().remove();
            var $rows = $("#gearList > div")
            if($rows.length === 1) {
                $rows.find('.remove-gear').addClass('disabled')
            }
        }
    });

</script>

@endsection