@extends('admin.layout')

@section('admin-title') Grant Enchantment @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Grant Enchantment' => 'admin/grants/enchantment']) !!}

<h1>Grant Enchantment</h1>

{!! Form::open(['url' => 'admin/grants/enchantment']) !!}

<h3>Basic Information</h3>

<div class="form-group">
    {!! Form::label('names[]', 'Username(s)') !!} {!! add_help('You can select up to 10 users at once.') !!}
    {!! Form::select('names[]', $users, null, ['id' => 'usernameList', 'class' => 'form-control', 'multiple']) !!}
</div>

<div class="form-group">
    {!! Form::label('Enchantment(s)') !!} {!! add_help('Must have at least 1 enchantment and Quantity must be at least 1.') !!}
    <div id="enchantmentList">
        <div class="d-flex mb-2">
            {!! Form::select('enchantment_ids[]', $enchantments, null, ['class' => 'form-control mr-2 default enchantment-select', 'placeholder' => 'Select Enchantment']) !!}
            {!! Form::text('quantities[]', 1, ['class' => 'form-control mr-2', 'placeholder' => 'Quantity']) !!}
            <a href="#" class="remove-enchantment btn btn-danger mb-2 disabled">×</a>
        </div>
    </div>
    <div><a href="#" class="btn btn-primary" id="add-enchantment">Add Enchantment</a></div>
    <div class="enchantment-row hide mb-2">
        {!! Form::select('enchantment_ids[]', $enchantments, null, ['class' => 'form-control mr-2 enchantment-select', 'placeholder' => 'Select Enchantment']) !!}
        {!! Form::text('quantities[]', 1, ['class' => 'form-control mr-2', 'placeholder' => 'Quantity']) !!}
        <a href="#" class="remove-enchantment btn btn-danger mb-2">×</a>
    </div>
</div>

<div class="form-group">
    {!! Form::label('data', 'Reason (Optional)') !!} {!! add_help('A reason for the grant. This will be noted in the logs and in the inventory description.') !!}
    {!! Form::text('data', null, ['class' => 'form-control', 'maxlength' => 400]) !!}
</div>

<h3>Additional Data</h3>

<div class="form-group">
    {!! Form::label('notes', 'Notes (Optional)') !!} {!! add_help('Additional notes for the enchantment. This will appear in the enchantment\'s description, but not in the logs.') !!}
    {!! Form::text('notes', null, ['class' => 'form-control', 'maxlength' => 400]) !!}
</div>

<div class="form-group">
    {!! Form::checkbox('disallow_transfer', 1, 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
    {!! Form::label('disallow_transfer', 'Account-bound', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is on, the recipient(s) will not be able to transfer this enchantment to other users. Enchantment that disallow transfers by default will still not be transferrable.') !!}
</div>

<div class="text-right">
    {!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
</div>

{!! Form::close() !!}

<script>
    $(document).ready(function() {
        $('#usernameList').selectize({
            maxEnchantment: 10
        });
        $('.default.enchantment-select').selectize();
        $('#add-enchantment').on('click', function(e) {
            e.preventDefault();
            addEnchantmentRow();
        });
        $('.remove-enchantment').on('click', function(e) {
            e.preventDefault();
            removeEnchantmentRow($(this));
        })
        function addEnchantmentRow() {
            var $rows = $("#enchantmentList > div")
            if($rows.length === 1) {
                $rows.find('.remove-enchantment').removeClass('disabled')
            }
            var $clone = $('.enchantment-row').clone();
            $('#enchantmentList').append($clone);
            $clone.removeClass('hide enchantment-row');
            $clone.addClass('d-flex');
            $clone.find('.remove-enchantment').on('click', function(e) {
                e.preventDefault();
                removeEnchantmentRow($(this));
            })
            $clone.find('.enchantment-select').selectize();
        }
        function removeEnchantmentRow($trigger) {
            $trigger.parent().remove();
            var $rows = $("#enchantmentList > div")
            if($rows.length === 1) {
                $rows.find('.remove-enchantment').addClass('disabled')
            }
        }
    });

</script>

@endsection