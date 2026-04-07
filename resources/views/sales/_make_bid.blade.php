@if ($sales)
    {!! Form::open(['url' => 'sales/bid/' . $character->id]) !!}

    <h5>{{ $character->displayType }}: <a href="{{ $character->character->url }}">{!! $character->character->slug !!}</a></h5>

    <p>You are about to make an offer for <strong>{!! $character->character->displayName !!}</strong>. Once you have made an offer, <strong>you will not be able to delete or modify it</strong>, and any onsite currency will be held until the offer is accepted or
        rejected.</p>
    <p>If you are unsure about what the other party is seeking or wish to discuss terms or ask questions, then consider leaving a comment on the sales post instead of making an offer outright.</p>
    <p class="text-danger">Ensure all information is correct and that you wish to make this offer.</p>
    <p>Are you sure you want to make an offer for <strong>{!! $character->character->displayName !!}</strong>?</p>

    {!! $character->price !!}
    <hr>
    @if ($character->type == 'ota' || $character->type == 'xta')
    @elseif($character->type == 'flatsale')
        <p>This character is listed for flatsale. </p>
    @elseif($character->type == 'raffle')
        <p>This character is up for raffle. You may enter for free.</p>
    @elseif($character->type == 'auction')
        @if ($character->lastBid)
            <p>You must either meet or exceed the price of the last bid + minimum increment.</p>
        @endif
        <p>This window has autofilled the minimum price you must bid, but you may always exceed it if desired.</p>

        <div class="row">

            <div class="col form-group">
                @if ($character->lastBid && $character->lastBid->user_id == Auth::user()->id)
                    <p class="text-danger">You are the current latest bid. Please wait until a new user bids
                        @if (isset($character->data['autobuy']))
                            , or you may autobuy instead
                        @endif.
                    </p>
                @else
                    {!! Form::label(ucfirst($character->bidType(true)) . ' Amount') !!}
                    {!! Form::number('price', $character->minBid, ['class' => 'form-control', 'placeholder' => 'Enter a Cost']) !!}
                @endif
            </div>
            <div class="col form-group">
                @if (isset($character->data['autobuy']))
                    {!! Form::checkbox('autobuy', 1, 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'id' => 'userOwned']) !!}
                    {!! Form::label('autobuy', 'Autobuy', ['class' => 'form-check-label ml-3']) !!}
                    <div class="hide" id="userOptions">
                        <br>
                        {!! Form::checkbox('confirm_ab', 1, 0, ['class' => 'form-check-input danger', 'data-toggle' => 'toggle']) !!}
                        {!! Form::label('confirm_ab', 'Confirm AB', ['class' => 'form-check-label ml-3']) !!} {!! add_help('Are you sure you want to AB? This MUST also be checked in order to AB.') !!}
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if (isset($data['irl_currency']))
        <p>This sale is only accepting {{ $character->bidType(true) }} for {{ $data['irl_currency'] }}. Please provide a paypal email for an invoice.</p>
        <div class="form-group">
            {!! Form::label('Paypal Email') !!}
            {!! Form::text('email', Auth::user()->settings->paypal_email ?? null, ['class' => 'form-control']) !!}
        </div>
    @endif
    @if ($character->sales->allow_comments)
        <h3><a class="small inventory-collapse-toggle collapse-toggle collapsed" data-toggle="collapse" href="#comm-{{ $character->id }}" class="text-primary"><strong>Add Comments</strong></a></h3>

        <div class="collapse" id="comm-{{ $character->id }}">
            <div class="form-group">
                {!! Form::label('Comments (Optional)') !!}{!! add_help('Any comments relevant to the sale that the seller or admins should know.') !!}
                {!! Form::textarea('comments', null, ['class' => 'form-control wysiwyg']) !!}
            </div>
        </div>
    @endif

    <div class="text-right">
        {!! Form::submit($character->typeLink, ['class' => 'btn btn-success']) !!}
    </div>

    {!! Form::close() !!}

    <script>
        $(document).ready(function() {
            @include('js._modal_wysiwyg')
            @if ($character->type == 'auction')
                var $userOwned = $('#userOwned');
                var $userOptions = $('#userOptions');

                var userOwned = $userOwned.is(':checked');

                $userOwned.on('change', function(e) {
                    userOwned = $userOwned.is(':checked');

                    if (userOwned) $userOptions.removeClass('hide');
                    else $userOptions.addClass('hide');
                });
            @endif

        });
    </script>
@else
    Invalid post selected.
@endif
