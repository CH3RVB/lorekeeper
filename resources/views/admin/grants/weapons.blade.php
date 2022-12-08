@extends('admin.layout')

@section('admin-title') Grant Weapons @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Grant Weapons' => 'admin/grants/weapons']) !!}

<h1>Grant Weapons</h1>

{!! Form::open(['url' => 'admin/grants/weapons']) !!}

<h3>Basic Information</h3>

<div class="form-group">
    {!! Form::label('names[]', 'Username(s)') !!} {!! add_help('You can select up to 10 users at once.') !!}
    {!! Form::select('names[]', $users, null, ['id' => 'usernameList', 'class' => 'form-control', 'multiple']) !!}
</div>

<div class="form-group">
    {!! Form::label('Weapon(s)') !!} {!! add_help('Must have at least 1 weapon and Quantity must be at least 1.') !!}
    <div id="weaponList">
        <div class="d-flex mb-2">
            {!! Form::select('weapon_ids[]', $weapons, null, ['class' => 'form-control mr-2 default weapon-select', 'placeholder' => 'Select Weapon']) !!}
            {!! Form::text('quantities[]', 1, ['class' => 'form-control mr-2', 'placeholder' => 'Quantity']) !!}
            <a href="#" class="remove-weapon btn btn-danger mb-2 disabled">×</a>
        </div>
    </div>
    <div><a href="#" class="btn btn-primary" id="add-weapon">Add Weapon</a></div>
    <div class="weapon-row hide mb-2">
        {!! Form::select('weapon_ids[]', $weapons, null, ['class' => 'form-control mr-2 weapon-select', 'placeholder' => 'Select Weapon']) !!}
        {!! Form::text('quantities[]', 1, ['class' => 'form-control mr-2', 'placeholder' => 'Quantity']) !!}
        <a href="#" class="remove-weapon btn btn-danger mb-2">×</a>
    </div>
</div>

<div class="form-group">
    {!! Form::label('data', 'Reason (Optional)') !!} {!! add_help('A reason for the grant. This will be noted in the logs and in the inventory description.') !!}
    {!! Form::text('data', null, ['class' => 'form-control', 'maxlength' => 400]) !!}
</div>

<h3>Additional Data</h3>

<div class="form-group">
    {!! Form::label('notes', 'Notes (Optional)') !!} {!! add_help('Additional notes for the weapon. This will appear in the weapon\'s description, but not in the logs.') !!}
    {!! Form::text('notes', null, ['class' => 'form-control', 'maxlength' => 400]) !!}
</div>

<div class="form-group">
    {!! Form::checkbox('disallow_transfer', 1, 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
    {!! Form::label('disallow_transfer', 'Account-bound', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is on, the recipient(s) will not be able to transfer this weapon to other users. Weapons that disallow transfers by default will still not be transferrable.') !!}
</div>

<div class="text-right">
    {!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
</div>

{!! Form::close() !!}

<script>
    $(document).ready(function() {
        $('#usernameList').selectize({
            maxWeapons: 10
        });
        $('.default.weapon-select').selectize();
        $('#add-weapon').on('click', function(e) {
            e.preventDefault();
            addWeaponRow();
        });
        $('.remove-weapon').on('click', function(e) {
            e.preventDefault();
            removeWeaponRow($(this));
        })
        function addWeaponRow() {
            var $rows = $("#weaponList > div")
            if($rows.length === 1) {
                $rows.find('.remove-weapon').removeClass('disabled')
            }
            var $clone = $('.weapon-row').clone();
            $('#weaponList').append($clone);
            $clone.removeClass('hide weapon-row');
            $clone.addClass('d-flex');
            $clone.find('.remove-weapon').on('click', function(e) {
                e.preventDefault();
                removeWeaponRow($(this));
            })
            $clone.find('.weapon-select').selectize();
        }
        function removeWeaponRow($trigger) {
            $trigger.parent().remove();
            var $rows = $("#weaponList > div")
            if($rows.length === 1) {
                $rows.find('.remove-weapon').addClass('disabled')
            }
        }
    });

</script>

@endsection