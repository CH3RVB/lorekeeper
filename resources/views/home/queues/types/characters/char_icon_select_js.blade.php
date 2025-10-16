<script>
    $(document).ready(function() {
        var $addCharacter = $('#addCharacter');
        var $components = $('#characterComponents');
        var $characters = $('#characters');
        var count = 0;

        $('#characters .submission-character').each(function(index) {
            attachListeners($(this));
        });

        $addCharacter.on('click', function(e) {
            e.preventDefault();
            $clone = $components.find('.submission-character').clone();
            attachListeners($clone);
            $characters.append($clone);
            $clone.find('.character-code').selectize();
            $clone.find('.selectize').selectize();
            count++;
        });

        function attachListeners(node) {
            node.find('.character-code').on('change', function(e) {
                var $parent = $(this).parent().parent().parent().parent();
               $parent.find('.character-image-loaded').load('{{ url('queue-submissions/new/character') }}/'+$(this).val(), function(response, status, xhr) {
                    $parent.find('.character-image-blank').addClass('hide');
                    $parent.find('.character-image-loaded').removeClass('hide');
                    $parent.find('.character-rewards').removeClass('hide');
                    updateRewardNames(node, node.find('.character-info').data('id'));
                });
            });
            node.find('.remove-character').on('click', function(e) {
                e.preventDefault();
                $(this).parent().parent().parent().remove();
            });
        }

        function updateRewardNames(node, id) {
            node.find('.add-icon').attr('name', 'icon[' + id + '][]');
            node.find('.add-artist').attr('name', 'artist_id[' + id + '][]');
            node.find('.add-offsite').attr('name', 'artist_url[' + id + '][]');
        }

    });
</script>

