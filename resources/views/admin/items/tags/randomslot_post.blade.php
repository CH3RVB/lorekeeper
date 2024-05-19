<div id="characterComponents" class="hide">
    <div class="submission-character mb-3 card">
        <div class="card-body">
            <div class="text-right"><a href="#" class="remove-character text-muted"><i class="fas fa-times"></i></a>
            </div>
            <div class="col-md-10">
                <div class="form-group character-info">
                    {!! Form::label('group_name[]', 'Group name') !!}{!! add_help('For reference purposes admin-side.') !!}
                    {!! Form::text('group_name[]', null, ['class' => 'form-control group-name']) !!}
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('trait_min[]', 'Minimum') !!}
                            {!! Form::number('trait_min[]', 1, [
                                'class' => 'form-control mr-2 character-rewardable-min',
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('trait_max[]', 'Maximum') !!}
                            {!! Form::number('trait_max[]', 1, [
                                'class' => 'form-control mr-2 character-rewardable-min',
                            ]) !!}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-10">
                <a href="#" class="float-right fas fa-close"></a>
                <div class="character-rewards">
                    <h4>Rolling Pool</h4>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th width="35%">Trait</th>
                                <th width="10%">Weight {!! add_help('A higher weight means a trait is more likely to be rolled. Weights have to be integers above 0 (round positive number, no decimals) and do not have to add up to be a particular number.') !!}</th>
                                <th width="10%">Chance</th>
                                <th width="10%"></th>
                            </tr>
                        </thead>
                        <tbody class="character-rewards">
                        </tbody>
                    </table>
                    <div class="text-right">
                        <a href="#" class="btn btn-outline-primary btn-sm add-reward">Add Trait</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <table>
        <tr class="character-reward-row">
            <td class="lootDivs">
                {!! Form::select('trait_id[]', $traits, 0, [
                    'class' => 'form-control reward-id',
                ]) !!}
            </td>
            <td class="loot-row-weight">{!! Form::text('weight[]', 1, ['class' => 'form-control loot-weight']) !!}</td>
            <td class="loot-row-chance"></td>
            </td>
            <td class="d-flex align-items-center">
                <a href="#" class="remove-reward d-block"><i class="fas fa-times text-muted"></i></a>
            </td>
        </tr>
    </table>
</div>

<div id="subtypeRowData" class="hide">
    <table class="table table-sm">
        <tbody id="subtypeRow">
            <tr class="subtype-row">
                <td>{!! Form::select('subtype_type[]', ['Subtype' => 'Subtype', 'None' => 'None'], null, ['class' => 'form-control subtype-type', 'placeholder' => 'Select Subtype Type']) !!}</td>
                <td class="subtype-row-select"></td>
                <td class="loot-row-weight">{!! Form::text('sub_weight[]', 1, ['class' => 'form-control loot-weight']) !!}</td>
                <td class="loot-row-chance"></td>
                <td class="text-right"><a href="#" class="btn btn-danger remove-subtype-button">Remove</a></td>
            </tr>
        </tbody>
    </table>
    {!! Form::select('subtype_id[]', $subtypes, null, ['class' => 'form-control sub-select', 'placeholder' => 'Select Subtype']) !!}
    {!! Form::select('subtype_id[]', [1 => 'No subtype.'], null, ['class' => 'form-control none-select']) !!}
</div>
