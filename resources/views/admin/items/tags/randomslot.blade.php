<h1>Random MYO Slot Settings</h1>
<p>This tag operates like a normal MYO slot, except that its traits can be assigned randomly based on what you set. The
    rest of this tag operates as normal.</p>
<div class="alert alert-danger text-center">
    <p>Be careful when selecting quantities.</p>
    <p>Don't select a minimum or maximum in a category that is below, or exceeds, the min and max that you chose.</p>
</div>

<h3>Basic Information</h3>
<div class="form-group">
    {!! Form::label('Name') !!} {!! add_help('Enter a descriptive name for the type of character this slot can create, e.g. Rare MYO Slot. This will be listed on the MYO slot masterlist.') !!}
    {!! Form::text('name', $tag->getData()['name'], ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('Description (Optional)') !!}
    @if ($isMyo)
        {!! add_help('This section is for making additional notes about the MYO slot. If there are restrictions for the character that can be created by this slot that cannot be expressed with the options below, use this section to describe them.') !!}
    @else
        {!! add_help('This section is for making additional notes about the character and is separate from the character\'s profile (this is not editable by the user).') !!}
    @endif
    {!! Form::textarea('description', $tag->getData()['description'], ['class' => 'form-control wysiwyg']) !!}
</div>

<div class="form-group">
    {!! Form::checkbox('is_visible', 1, $tag->getData()['is_visible'], [
        'class' => 'form-check-input',
        'data-toggle' => 'toggle',
    ]) !!}
    {!! Form::label('is_visible', 'Is Visible', ['class' => 'form-check-label ml-3']) !!} {!! add_help(
        'Turn this off to hide the ' . ($isMyo ? 'MYO slot' : 'character') . '. Only mods with the Manage Masterlist power (that\'s you!) can view it - the owner will also not be able to see the ' . ($isMyo ? 'MYO slot' : 'character') . '\'s page.',
    ) !!}
</div>

<h3>Transfer Information</h3>

<div class="alert alert-info">
    These are displayed on the MYO slot's profile, but don't have any effect on site functionality except for the
    following:
    <ul>
        <li>If all switches are off, the MYO slot cannot be transferred by the user (directly or through trades).</li>
        <li>If a transfer cooldown is set, the MYO slot also cannot be transferred by the user (directly or through
            trades) until the cooldown is up.</li>
    </ul>
</div>
<div class="form-group">
    {!! Form::checkbox('is_giftable', 1, $tag->getData()['is_giftable'], [
        'class' => 'form-check-input',
        'data-toggle' => 'toggle',
    ]) !!}
    {!! Form::label('is_giftable', 'Is Giftable', ['class' => 'form-check-label ml-3']) !!}
</div>
<div class="form-group">
    {!! Form::checkbox('is_tradeable', 1, $tag->getData()['is_tradeable'], [
        'class' => 'form-check-input',
        'data-toggle' => 'toggle',
    ]) !!}
    {!! Form::label('is_tradeable', 'Is Tradeable', ['class' => 'form-check-label ml-3']) !!}
</div>
<div class="form-group">
    {!! Form::checkbox('is_sellable', 1, $tag->getData()['is_sellable'], [
        'class' => 'form-check-input',
        'data-toggle' => 'toggle',
        'id' => 'resellable',
    ]) !!}
    {!! Form::label('is_sellable', 'Is Resellable', ['class' => 'form-check-label ml-3']) !!}
</div>
<div class="card mb-3" id="resellOptions">
    <div class="card-body">
        {!! Form::label('Resale Value') !!} {!! add_help('This value is publicly displayed on the MYO slot\'s page.') !!}
        {!! Form::text('sale_value', $tag->getData()['sale_value'], ['class' => 'form-control']) !!}
    </div>
</div>

<h3>Traits</h3>

<div class="form-group">
    {!! Form::label('Character Rarity') !!} {!! add_help('This will lock the slot into a particular rarity. Leave it blank if you would like to give the user more choices.') !!}
    {!! Form::select('rarity_id', $rarities, $tag->getData()['rarity_id'], ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('Species') !!} {!! add_help('This will lock the slot into a particular species. Leave it blank if you would like to give the user a choice.') !!}
    {!! Form::select('species_id', $specieses, $tag->getData()['species_id'], [
        'class' => 'form-control',
        'id' => 'species',
    ]) !!}
</div>

<div class="card mb-3">
    <div class="card-header h2">
        Subtype Options
    </div>
    <div class="card-body" style="clear:both;">
        <p>You can randomize subtypes here.</p>
        <p>A "no subtype" option is included if you wish to weight the rolling of subtypes as well as the option to not have one.</p>
        <p>Select "None" for type in that case, and you can weight the option to roll nothing. If you wish to not use subtypes at all, then you can safely ignore this section.</p>
        <div class="text-right mb-3">
            <a href="#" class="btn btn-info" id="addSubtype">Add Subtype</a>
        </div>
        <table class="table table-sm" id="subtypeTable">
            <thead>
                <tr>
                    <th width="25%">Subtype Type</th>
                    <th width="35%">Subtype</th>
                    <th width="10%">Weight {!! add_help('A higher weight means a subtype is more likely to be rolled. Weights have to be integers above 0 (round positive number, no decimals) and do not have to add up to be a particular number.') !!}</th>
                    <th width="10%">Chance</th>
                    <th width="10%"></th>
                </tr>
            </thead>
            <tbody id="subtypeTableBody">
                @if (isset($tag->data['subtypes']) && is_array($tag->data['subtypes']))
                    @foreach ($tag->data['subtypes'] as $subtype)
                        <tr class="subtype-row">
                            <td>{!! Form::select('subtype_type[]', ['Subtype' => 'Subtype', 'None' => 'None'], isset($subtype['subtype_type']) ? $subtype['subtype_type'] : null, ['class' => 'form-control subtype-type', 'placeholder' => 'Select Subtype Type']) !!}</td>
                            <td class="subtype-row-select">
                                @if ($subtype['subtype_type'] == 'Subtype')
                                    {!! Form::select('subtype_id[]', $subtypes, $subtype['subtype_id'], ['class' => 'form-control sub-select selectize', 'placeholder' => 'Select Subtype']) !!}
                                @elseif($subtype['subtype_type'] == 'None')
                                    {!! Form::select('subtype_id[]', [1 => 'No subtype.'], $subtype['subtype_id'], ['class' => 'form-control']) !!}
                                @endif
                            </td>
                            <td class="loot-row-weight">{!! Form::text('sub_weight[]', $subtype['weight'], ['class' => 'form-control loot-weight']) !!}</td>
                            <td class="loot-row-chance"></td>
                            <td class="text-right"><a href="#" class="btn btn-danger remove-subtype-button">Remove</a></td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
</div>
<br>


<div class="card mb-3">
    <div class="card-header h2">
        <a href="#" class="btn btn-outline-info float-right" id="addCharacter">Add Group</a>
        Rolling Group
    </div>
    <div class="card-body" style="clear:both;">
        <p>Create a rolling group (or multiple) to assign traits to. You can set a max and min to roll between, as well
            as a pool of
            traits to roll.</p>
        <p>Avoid species-specific traits unless a species is chosen.</p>
        <p>The group name is for admin reference purposes, but is also used to group the traits, so try to keep a unique name between them.</p>
        <div id="characters" class="mb-3">
            @if (isset($tag->data['groups']) && is_array($tag->data['groups']))
                @foreach ($tag->data['groups'] as $group)
                    <div class="submission-character mb-3 card">
                        <div class="card-body">
                            <div class="text-right"><a href="#" class="remove-character text-muted"><i class="fas fa-times"></i></a></div>
                            <div class="row">
                                <div class="col-md-10">
                                    <a href="#" class="float-right fas fa-close"></a>
                                    <div class="form-group">
                                        {!! Form::label('group_name[]', 'Group name') !!}{!! add_help('For reference purposes admin-side.') !!}
                                        {!! Form::text('group_name[]', $group['group_name'], ['class' => 'form-control group-name']) !!}
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                {!! Form::label('trait_min[]', 'Minimum') !!}
                                                {!! Form::number('trait_min[]', $group['trait_min'], [
                                                    'class' => 'form-control mr-2 character-rewardable-min',
                                                ]) !!}
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                {!! Form::label('trait_max[]', 'Maximum') !!}
                                                {!! Form::number('trait_max[]', $group['trait_max'], [
                                                    'class' => 'form-control mr-2 character-rewardable-min',
                                                ]) !!}
                                            </div>
                                        </div>
                                    </div>
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
                                                @foreach ($group['traits'] ?? [] as $trait)
                                                    <tr class="character-reward-row">
                                                        <td class="lootDivs">
                                                            {!! Form::select('trait_id[' . $group['group_name'] . '][]', $traits, $trait['trait_id'], [
                                                                'class' => 'form-control reward-id',
                                                            ]) !!}
                                                        </td>
                                                        <td class="loot-row-weight">{!! Form::text('weight[' . $group['group_name'] . '][]', isset($trait['weight']) ? $trait['weight'] : 1, ['class' => 'form-control loot-weight']) !!}</td>
                                                        </td>
                                                        <td class="loot-row-chance"></td>
                                                        <td class="d-flex align-items-center">

                                                            <a href="#" class="remove-reward d-block"><i class="fas fa-times text-muted"></i></a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        <div class="text-right">
                                            <a href="#" class="btn btn-outline-primary btn-sm add-reward">Add
                                                Trait</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>
<hr>
<div class="card mb-3">
    <div class="card-header h2">
        Test Roller
    </div>
    <div class="card-body" style="clear:both;">
        <p>You can test the MYO generation here.</p>
        <p>If you have made any modifications to the slot contents above, be sure to save it (click the Edit button) before testing.</p>
        <p>Please note that due to the nature of probability, as long as there is a chance, there will always be the possibility of rolling improbably good or bad results. <i>This is not indicative of the code being buggy or poor game balance.</i> Be
            cautious when adjusting values based on a small sample size, including but not limited to test rolls and a small amount of user reports.</p>
        <div class="form-group">
            {!! Form::label('quantity', 'Number of Rolls') !!}
            {!! Form::text('quantity', 1, ['class' => 'form-control', 'id' => 'rollQuantity']) !!}
        </div>
        <div class="text-right">
            <a href="#" class="btn btn-success" id="rollerSubmit">Roll!</a>
        </div>
        <div class="mt-3" id="results"></div>

    </div>
</div>
<br>


<!-- oh god dont look here it's so ugly because i tried to change the class/js names to be less related to characters and it broke everything and im too scared to change it back lmao -->
@section('scripts')
    @parent
    @include('widgets._character_create_options_js')
    <script>
        $(document).ready(function() {

            let $results = $('#results');

            $(document).on('click', '#rollerSubmit', function(e) {
                e.preventDefault();
                $results.html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i></div>');
                $.ajax({
                    url: "{{ url('admin/data/items/tag/' . $tag->item->id . '/' . $tag->tag . '/roller') }}",
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: $("#rollQuantity").serialize(),
                    success: function(response) {
                        $results.html(response);
                    },
                    error: function(response) {
                        $results.html(response);
                    }
                });
            });

            $('#species').selectize();
            var $subtypeTable = $('#subtypeTableBody');
            var $subtypeRow = $('#subtypeRow').find('.subtype-row');
            var $subSelect = $('#subtypeRowData').find('.sub-select');
            var $noneSelect = $('#subtypeRowData').find('.none-select');

            refreshChances($subtypeTable);
            $('#subtypeTableBody .selectize').selectize();
            attachRemoveListener($('#subtypeTableBody .remove-subtype-button'));

            $('#addSubtype').on('click', function(e) {
                e.preventDefault();
                var $clone = $subtypeRow.clone();
                $subtypeTable.append($clone);
                attachSubtypeTypeListener($clone.find('.subtype-type'));
                attachRemoveListener($clone.find('.remove-subtype-button'));
                attachWeightListener($clone.find('.loot-weight'));
                refreshChances($clone.parent());
            });

            $('.subtype-type').on('change', function(e) {
                var val = $(this).val();
                var $cell = $(this).parent().find('.subtype-row-select');

                var $clone = null;
                if (val == 'Subtype') $clone = $subSelect.clone();
                else if (val == 'None') $clone = $noneSelect.clone();

                $cell.html('');
                $cell.append($clone);
            });

            function attachSubtypeTypeListener(node) {
                node.on('change', function(e) {
                    var val = $(this).val();
                    var $cell = $(this).parent().parent().find('.subtype-row-select');

                    var $clone = null;
                    if (val == 'Subtype') $clone = $subSelect.clone();
                    else if (val == 'None') $clone = $noneSelect.clone();

                    $cell.html('');
                    $cell.append($clone);
                    $clone.selectize();
                });
            }

            var $addCharacter = $('#addCharacter');
            var $components = $('#characterComponents');
            var $rewards = $('#rewards');
            var $characters = $('#characters');
            var count = 0;

            $('.submission-character').each(function() {
                refreshChances($(this));
            });

            $('#characters .reward-id').selectize();
            attachRemoveListener($('.character-rewards .remove-reward'));
            $('#characters .submission-character').each(function(index) {
                attachListeners($(this));
            });

            $addCharacter.on('click', function(e) {
                e.preventDefault();
                $clone = $components.find('.submission-character').clone();
                attachListeners($clone);
                $characters.append($clone);
                count++;
            });

            function attachListeners(node) {
                node.find('.group-name').on('change', function(e) {
                    updateRewardNames(node, node.find('.group-name').val());
                });
                node.find('.remove-character').on('click', function(e) {
                    e.preventDefault();
                    $(this).parent().parent().parent().remove();
                });
                node.find('.add-reward').on('click', function(e) {
                    e.preventDefault();
                    $clone = $components.find('.character-reward-row').clone();
                    attachRemoveListener($clone.find('.remove-reward'));
                    updateRewardNames($clone, node.find('.group-name').val());
                    $(this).parent().parent().find('.character-rewards').append($clone);
                    $clone.find('.reward-id').selectize();
                    attachWeightListener($clone.find('.loot-weight'));
                    refreshChances(node);
                });
            }

            function updateRewardNames(node, $input) {
                node.find('.reward-id').attr('name', 'trait_id[' + $input + '][]');
                node.find('.loot-weight').attr('name', 'weight[' + $input + '][]');
            }

            function attachRemoveListener(node) {
                node.on('click', function(e) {
                    e.preventDefault();
                    $getval = $(this).parent().parent().parent().parent().parent();
                    $(this).parent().parent().remove();
                    refreshChances($getval);
                });
            }

            function attachWeightListener(node) {
                node.on('change', function(e) {
                    refreshChances($(this).parent().parent().parent());
                });
            }

            function refreshChances(node) {
                var total = 0;
                var weights = [];
                node.find('.loot-weight').each(function(index) {
                    var current = parseInt($(this).val());
                    total += current;
                    weights.push(current);
                });


                node.find('.loot-row-chance').each(function(index) {
                    var current = (weights[index] / total) * 100;
                    $(this).html(current.toString() + '%');
                });
            }

        });
    </script>
@endsection
