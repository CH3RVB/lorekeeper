@extends('admin.layout')

@section('admin-title') Grant Pets @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Grant Pets' => 'admin/grants/pets']) !!}

<h1>Grant Pets</h1>

{!! Form::open(['url' => 'admin/grants/pets']) !!}

<h3>Basic Information</h3>

<div class="form-group">
    {!! Form::label('names[]', 'Username(s)') !!} {!! add_help('You can select up to 10 users at once.') !!}
    {!! Form::select('names[]', $users, null, ['id' => 'usernameList', 'class' => 'form-control', 'multiple']) !!}
</div>

<div class="form-group">
    {!! Form::label('Pet(s)') !!} {!! add_help('Must have at least 1 pet and Quantity must be at least 1.') !!}
    <div id="petList">
        <div class="d-flex mb-2">
            {!! Form::select('pet_ids[]', $pets, null, ['class' => 'form-control mr-2 default pet-select', 'placeholder' => 'Select Pet']) !!}
            {!! Form::text('quantities[]', 1, ['class' => 'form-control mr-2', 'placeholder' => 'Quantity']) !!}
            <a href="#" class="remove-pet btn btn-danger mb-2 disabled">×</a>
        </div>
    </div>
    <div><a href="#" class="btn btn-primary" id="add-pet">Add Pet</a></div>
    <div class="pet-row hide mb-2">
        {!! Form::select('pet_ids[]', $pets, null, ['class' => 'form-control mr-2 pet-select', 'placeholder' => 'Select Pet']) !!}
        {!! Form::text('quantities[]', 1, ['class' => 'form-control mr-2', 'placeholder' => 'Quantity']) !!}
        <a href="#" class="remove-pet btn btn-danger mb-2">×</a>
    </div>
</div>

<div class="form-group">
    {!! Form::label('data', 'Reason (Optional)') !!} {!! add_help('A reason for the grant. This will be noted in the logs and in the inventory description.') !!}
    {!! Form::text('data', null, ['class' => 'form-control', 'maxlength' => 400]) !!}
</div>

<h3>Additional Data</h3>

<div class="form-group">
    {!! Form::label('notes', 'Notes (Optional)') !!} {!! add_help('Additional notes for the pet. This will appear in the pet\'s description, but not in the logs.') !!}
    {!! Form::text('notes', null, ['class' => 'form-control', 'maxlength' => 400]) !!}
</div>

<div class="form-group">
    {!! Form::checkbox('disallow_transfer', 1, 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
    {!! Form::label('disallow_transfer', 'Account-bound', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is on, the recipient(s) will not be able to transfer this pet to other users. Pets that disallow transfers by default will still not be transferrable.') !!}
</div>

<div class="text-right">
    {!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
</div>

{!! Form::close() !!}

<script>
    $(document).ready(function() {
        $('#usernameList').selectize({
            maxPets: 10
        });
        $('.default.pet-select').selectize();
        $('#add-pet').on('click', function(e) {
            e.preventDefault();
            addPetRow();
        });
        $('.remove-pet').on('click', function(e) {
            e.preventDefault();
            removePetRow($(this));
        })
        function addPetRow() {
            var $rows = $("#petList > div")
            if($rows.length === 1) {
                $rows.find('.remove-pet').removeClass('disabled')
            }
            var $clone = $('.pet-row').clone();
            $('#petList').append($clone);
            $clone.removeClass('hide pet-row');
            $clone.addClass('d-flex');
            $clone.find('.remove-pet').on('click', function(e) {
                e.preventDefault();
                removePetRow($(this));
            })
            $clone.find('.pet-select').selectize();
        }
        function removePetRow($trigger) {
            $trigger.parent().remove();
            var $rows = $("#petList > div")
            if($rows.length === 1) {
                $rows.find('.remove-pet').addClass('disabled')
            }
        }
    });

</script>

@endsection