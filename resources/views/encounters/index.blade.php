@extends('encounters.layout')

@section('title')
    Encounter Areas
@endsection

@section('content')
    {!! breadcrumbs(['Encounter Areas' => 'encounter-areas']) !!}
    <p id="display_error"></p>
    <div class="text-center">
        <h1>Encounter Areas</h1>
        <p>Here is a list of areas that you can venture into. You will recieve a randomized encounter and options of
            what to do
            in it.</p>
        <p>You have limited energy to explore each day, so spend it wisely.</p>
    </div>
    <hr>

    @if (!count($areas))
        <div class="alert alert-info">No areas found. Check back later!</div>
    @else
        <div class="row shops-row">

            @foreach ($areas as $area)
                @include('encounters._area_entry')
            @endforeach
        </div>

        <div id="encounter-area"></div>
    @endif

    <script>
        $(document).on('click', '.initiate-explore', function() {
            var $btn = $(this);
            // ignore repeat clicks while a roll is in flight, so a double-click can't charge twice
            if ($btn.hasClass('disabled')) {
                return;
            }
            $btn.addClass('disabled');
            var areaId = $btn.data('area-id');
            $('#display_error').removeClass('alert alert-danger').html('');
            $.ajax({
                type: "GET",
                url: "{{ url('encounter-areas') }}/" + areaId,
            }).done(function(res) {
                $("#encounter-area").fadeOut(500, function() {
                    $("#encounter-area").html(res);
                    $("#encounter-area").fadeIn(500);
                });
            }).fail(function(jqXHR) {
                var msg = (jqXHR.responseJSON && jqXHR.responseJSON.error) ? jqXHR.responseJSON.error : 'Something went wrong. Please try again.';
                $('#display_error').addClass('alert alert-danger').html(msg);
            }).always(function() {
                $btn.removeClass('disabled');
            });
        });

        // prevent a double-clicked action button from submitting the same encounter twice
        $(document).on('submit', '#encounter-area form', function() {
            $(this).find('[type=submit]').prop('disabled', true);
        });
    </script>
@endsection
