<script>
    $(document).ready(function() {
        var $addCharacter = $('#addCharacter');
        var $components = $('#characterComponents');
        var $characters = $('#characters');
        var count = 0;

        $('.post-datepicker').datetimepicker({
            dateFormat: "yy-mm-dd",
            timeFormat: 'HH:mm:ss',
        });

        $('#characters .sales-character-entry').each(function(index) {
            attachListeners($(this));
        });

        $addCharacter.on('click', function(e) {
            e.preventDefault();
            $clone = $components.find('.sales-character').clone();
            attachListeners($clone);
            $characters.append($clone);
            $clone.find('.character-code').selectize();
            $clone.find('.datepicker').datetimepicker({
                dateFormat: "yy-mm-dd",
                timeFormat: 'HH:mm:ss',
            });
            $clone.find('.selectize').selectize();
            count++;
        });

        function attachListeners(node) {
            node.find('.character-code').on('change', function(e) {
                var $parent = $(this).parent().parent().parent().parent();
                $parent.find('.character-image-loaded').load('{{ url('queue-submissions/new/character') }}/' + $(this).val(), function(response, status, xhr) {
                    $parent.find('.character-image-blank').addClass('hide');
                    $parent.find('.character-image-loaded').removeClass('hide');
                    $parent.find('.character-details').removeClass('hide');
                    updateRewardNames(node, node.find('.character-info').data('id'));
                });
            });
            node.find('.remove-character').on('click', function(e) {
                e.preventDefault();
                $(this).parent().parent().parent().remove();
            });
            attachSaleTypeListener(node.find('.character-sale-type'));
        }

        function attachSaleTypeListener(node) {
            node.on('change', function(e) {
                var val = $(this).val();
                var $cell = $(this).parent().parent().find('.saleType');

                $cell.children().addClass('hide');
                $cell.children().children().val(null);

                if (val == 'flatsale') {
                    $cell.children('.flatOptions').addClass('show');
                    $cell.children('.flatOptions').removeClass('hide');
                } else if (val == 'ota' || val == 'xta') {
                    $cell.children('.xtaOptions').addClass('show');
                    $cell.children('.xtaOptions').removeClass('hide');
                    $cell.children('.pwywOptions').addClass('show');
                    $cell.children('.pwywOptions').removeClass('hide');
                } else if (val == 'pwyw') {
                    $cell.children('.pwywOptions').addClass('show');
                    $cell.children('.pwywOptions').removeClass('hide');
                } else if (val == 'raffle') {
                    $cell.children('.raffleOptions').addClass('show');
                    $cell.children('.raffleOptions').removeClass('hide');
                }

                if (val != 'raffle') {
                    $cell.children('.currencyOptions').addClass('show');
                    $cell.children('.currencyOptions').removeClass('hide');
                } else {
                    $cell.children('.currencyOptions').addClass('hide');
                    $cell.children('.currencyOptions').removeClass('show');
                }
            });
        }

         function updateRewardNames(node, id) {
            node.find('.character-sale-type').attr('name', 'sale_type[' + id + ']');
            node.find('.currency_id').attr('name', 'currency_id[' + id + ']');
            node.find('.irl_currency').attr('name', 'irl_currency[' + id + ']');
            node.find('.price').attr('name', 'price[' + id + ']');
            node.find('.autobuy').attr('name', 'autobuy[' + id + ']');
            node.find('.end_point').attr('name', 'end_point[' + id + ']');
            node.find('.minimum').attr('name', 'minimum[' + id + ']');
            node.find('.description').attr('name', 'description[' + id + ']');
        }
    });
</script>
