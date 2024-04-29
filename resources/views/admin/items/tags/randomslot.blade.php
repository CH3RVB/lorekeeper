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
    {!! Form::label('Species') !!} {!! add_help('This will lock the slot into a particular species. Leave it blank if you would like to give the user a choice.') !!}
    {!! Form::select('species_id', $specieses, $tag->getData()['species_id'], [
        'class' => 'form-control',
        'id' => 'species',
    ]) !!}
</div>

<div class="form-group">
    {!! Form::label('Subtype (Optional)') !!} {!! add_help(
        'This will lock the slot into a particular subtype. Leave it blank if you would like to give the user a choice, or not select a subtype. The subtype must match the species selected above, and if no species is specified, the subtype will not be applied.',
    ) !!}
    {!! Form::select('subtype_id', $subtypes, $tag->getData()['subtype_id'], [
        'class' => 'form-control',
        'id' => 'subtype',
    ]) !!}
</div>

<div class="form-group">
    {!! Form::label('Character Rarity') !!} {!! add_help('This will lock the slot into a particular rarity. Leave it blank if you would like to give the user more choices.') !!}
    {!! Form::select('rarity_id', $rarities, $tag->getData()['rarity_id'], ['class' => 'form-control']) !!}
</div>


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
                                            <tbody class="character-rewards">
                                                @foreach ($group['traits'] ?? [] as $trait)
                                                    <tr class="character-reward-row">
                                                        <td class="lootDivs">
                                                            {!! Form::select('trait_id[' . $group['group_name'] . '][]', $traits, $trait['trait_id'], [
                                                                'class' => 'form-control reward-id',
                                                            ]) !!}
                                                        </td>
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

<!-- oh god dont look here it's so ugly because i tried to change the class/js names to be less related to characters and it broke everything and im too scared to change it back lmao -->
@section('scripts')
    @parent
    @include('widgets._character_create_options_js')
    <script>
        $(document).ready(function() {
            var $addCharacter = $('#addCharacter');
            var $components = $('#characterComponents');
            var $rewards = $('#rewards');
            var $characters = $('#characters');
            var count = 0;


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
                });
            }

            function updateRewardNames(node, $input) {
                node.find('.reward-id').attr('name', 'trait_id[' + $input + '][]');
            }

            function attachRemoveListener(node) {
                node.on('click', function(e) {
                    e.preventDefault();
                    $(this).parent().parent().remove();
                });
            }

        });
    </script>
@endsection
