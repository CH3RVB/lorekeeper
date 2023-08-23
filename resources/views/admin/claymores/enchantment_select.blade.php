<h3>Enchantments</h3>
<p>Here, you can add enchantments to this {{ $type }}. These enchantments are different in that they do not count against a user's enchantments/slots for this {{ $type }}.</p>
<p>All user {{ $type }}s will automatically have these enchantments applied to them.</p>
{!! Form::open(['url' => 'admin/'.$type.'/enchantments/'.$claymore->id]) !!}
<div class="text-right mb-3">
    <a href="#" class="btn btn-outline-info" id="addLimit">Add Enchantment</a>
</div>
<table class="table table-sm" id="enchantmentTable">
    <thead>
        <tr>
            <th width="35%">Enchantment</th>
            <th width="20%">Quantity</th>
            <th width="10%"></th>
        </tr>
    </thead>
    <tbody id="enchantmentTableBody">
        @if($claymore->enchantments)
            @foreach($claymore->enchantments as $enchantment)
                <tr class="enchantment-row">
                    <td class="enchantment-row-select">
                        {!! Form::select('enchantment_id[]', $enchantments, $enchantment->enchantment_id, ['class' => 'form-control enchantment-select', 'placeholder' => 'Select Enchantment']) !!}
                    </td>
                    <td>{!! Form::text('quantity[]', $enchantment->quantity, ['class' => 'form-control']) !!}</td>
                    <td class="text-right"><a href="#" class="btn btn-danger remove-enchantment-button">Remove</a></td>
                </tr>
            @endforeach
        @endif
    </tbody>
</table>

<div class="text-right">
    {!! Form::submit('Edit Enchantments', ['class' => 'btn btn-primary']) !!}
</div>

{!! Form::close() !!}

<div id="enchantmentRowData" class="hide">
    <table class="table table-sm">
        <tbody id="enchantmentRow">
            <tr class="enchantment-row">
                <td class="loot-row-select">{!! Form::select('enchantment_id[]', $enchantments, null, ['class' => 'form-control enchantment-select', 'placeholder' => 'Select Enchantment']) !!}</td>
                <td>{!! Form::text('quantity[]', 1, ['class' => 'form-control']) !!}</td>
                <td class="text-right"><a href="#" class="btn btn-danger remove-enchantment-button">Remove</a></td>
            </tr>
        </tbody>
    </table>
</div>

<script>
    var $enchantmentTable  = $('#enchantmentTableBody');
    var $enchantmentRow = $('#enchantmentRow').find('.enchantment-row');
    $('#enchantmentTableBody .selectize').selectize();
    attachRemoveListener($('#enchantmentTableBody .remove-enchantment-button'));
    $('#addLimit').on('click', function(e) {
        e.preventDefault();
        var $clone = $enchantmentRow.clone();
        $enchantmentTable.append($clone);
        attachRemoveListener($clone.find('.remove-enchantment-button'));
    });
    function attachRemoveListener(node) {
        node.on('click', function(e) {
            e.preventDefault();
            $(this).parent().parent().remove();
        });
    }
</script>