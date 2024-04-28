<div id="characterComponents" class="hide">
    <div class="submission-character mb-3 card">
        <div class="card-body">
            <div class="text-right"><a href="#" class="remove-character text-muted"><i class="fas fa-times"></i></a>
            </div>
            <div class="row">
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
                    <div class="col-md-10">
                        <a href="#" class="float-right fas fa-close"></a>
                        <div class="character-rewards">
                            <h4>Pool</h4>
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th width="100%">Trait</th>
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
        </div>
    </div>
    <table>
        <tr class="character-reward-row">
            <td class="lootDivs">
                <div class="character-currencies">{!! Form::select('trait_id[]', $traits, 0, [
                    'class' => 'form-control reward-id',
                ]) !!}</div>
            </td>
            <td class="d-flex align-items-center">
                <a href="#" class="remove-reward d-block"><i class="fas fa-times text-muted"></i></a>
            </td>
        </tr>
    </table>
</div>
