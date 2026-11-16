@php
    if (!isset($type)) {
        $type = 'Limit';
    }
    if (!isset($prefix)) {
        $prefix = '';
    }

@endphp

<script>
    $(document).ready(function() {
        let $limitLabel = $('#{{ $prefix }}rows').find('.{{ $prefix }}limit-label');
        var $limitRow = $('#{{ $prefix }}limitRow').find('.{{ $prefix }}limit-row');
        var $rows = $('#{{ $prefix }}rows');
        var debitableLimits = ("{{ implode(',', $debitableLimits) }}").split(',');
        var countableLimits = ("{{ implode(',', $countableLimits) }}").split(',');

        $('.limit-selectize').selectize();

        $('#{{ $prefix }}add-limit').on('click', function(e) {
            e.preventDefault();
            var $clone = $limitRow.clone();
            $('#{{ $prefix }}limits').append($clone);
            $clone.removeClass('hide');
            attachLimitTypeListener($clone.find('.limit-type'));
            $clone.find('.limit-type').selectize();
            attachRemoveListener($clone.find('.remove-limit'));
            attachLimitRecipientListener($clone.find('.recipient-type'));
        });

        $('.limit-type').on('change', function() {
            var $recipient = $(this).parent().parent().find('.recipient-type').val();
            cloneLimitId($(this), $recipient);

        });

        // attach remove listener to all .remove-limit
        $('.{{ $prefix }}remove-limit').each(function() {
            attachRemoveListener($(this));
        });


        function attachLimitTypeListener(node) {
            node.on('change', function(e) {
                var $recipient = $(this).parent().parent().find('.recipient-type').val();
                cloneLimitId($(this), $recipient);
            });
        }

        function attachRemoveListener(node) {
            node.on('click', function(e) {
                e.preventDefault();
                $(this).closest('.{{ $prefix }}limit-row').remove();
            });
        }

        function cloneLimitId(node, recipient = null) {
            let val = node.val();
            let $limit = node.closest('.{{ $prefix }}limit-row').find('.{{ $prefix }}limit-select');

            let $clone = null;
            if (recipient != null) {
                $clone = $rows.find('#limit-ids-' + recipient.toLowerCase()).find('.' + val.toLowerCase() + '-select').clone();
            } else {
                $clone = $rows.find('.' + val.toLowerCase() + '-select').clone();
            }

            $limit.html('');
            $limit.append($limitLabel.clone());
            $limit.append($clone);
            $clone.selectize();

            // remove hide on debit/count if type is debitable/countable, otherwise hide it
            var debitable = debitableLimits.includes(val);
            var countable = countableLimits.includes(val);
            node.closest('.{{ $prefix }}limit-row').find('.limit-delete').removeClass('col-md-1');
            node.closest('.{{ $prefix }}limit-row').find('.limit-delete').removeClass('col-md-5');
            if (debitable || countable) {
                node.closest('.{{ $prefix }}limit-row').find('.limit-modifiers').removeClass('hide');
                node.closest('.{{ $prefix }}limit-row').find('.limit-delete').addClass('col-md-1');
                if (debitable) {
                    node.closest('.{{ $prefix }}limit-row').find('.debit').removeClass('hide');
                } else {
                    node.closest('.{{ $prefix }}limit-row').find('.debit').addClass('hide');
                }
                if (countable) {
                    node.closest('.{{ $prefix }}limit-row').find('.quantity').removeClass('hide');
                } else {
                    node.closest('.{{ $prefix }}limit-row').find('.quantity').addClass('hide');
                }
            } else {
                node.closest('.{{ $prefix }}limit-row').find('.limit-modifiers').addClass('hide');
                node.closest('.{{ $prefix }}limit-row').find('.limit-delete').addClass('col-md-5');
            }
        }

        $('.recipient-type').on('change', function(e) {
            var $limitTypeCell = $(this).parent().parent().find('.{{ $prefix }}limit-row-type');
            var $limitIdsCell = $(this).parent().parent().find('.{{ $prefix }}limit-select');
            var $recipient = $(this).val();

            if ($recipient === '') {
                $limitTypeCell.html('');
                $limitIdsCell.html('');
                return;
            }

            //Update the lootRow with the new types
            $.ajax({
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                url: "{{ url('limits/types') }}",
                data: {
                    recipient: $recipient,
                    prefix: '{{ $prefix }}',
                    type: '{{ $type }}',
                },
                dataType: "text"
            }).done(function(res) {
                $limitTypeCell.html(res);
                attachLimitTypeListener($limitTypeCell.find('.limit-type'));
                $limitIdsCell.html('');
                $limitTypeCell.find('.limit-type').selectize();
            }).fail(function(jqXHR, textStatus, errorThrown) {
                alert("AJAX call failed: " + textStatus + ", " + errorThrown);
            });
        });

        function attachLimitRecipientListener(node) {
            node.on('change', function(e) {
                var $limitTypeCell = $(this).parent().parent().find('.{{ $prefix }}limit-row-type');
                var $limitIdsCell = $(this).parent().parent().find('.{{ $prefix }}limit-select');
                var $recipient = $(this).val();

                //Update the lootRow with the new types
                $.ajax({
                    type: "POST",
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    url: "{{ url('limits/types') }}",
                    data: {
                        recipient: $recipient,
                        prefix: '{{ $prefix }}',
                        type: '{{ $type }}',
                    },
                    dataType: "text"
                }).done(function(res) {
                    $limitTypeCell.html(res);
                    attachLimitTypeListener($limitTypeCell.find('.limit-type'));
                    $limitIdsCell.html('');
                $limitTypeCell.find('.limit-type').selectize();
                }).fail(function(jqXHR, textStatus, errorThrown) {
                    alert("AJAX call failed: " + textStatus + ", " + errorThrown);
                });
            });
        }
    });
</script>
