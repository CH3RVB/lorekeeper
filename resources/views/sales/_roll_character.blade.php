<div class="text-center">
    @if ($character->is_rolled && $character->bids->where('user_id', $character->winner_id)->where('status', 'Accepted')->first())
        <p><b>{{ $character->character->name }}</b> has already been rolled, the current winner is <b>{!! $character->winner->displayName !!}</b>. Would you like to reroll?</p>
        <p>Otherwise, accept the winner to finalize the raffle.</p>
        <div class="row">
            <Div class="col">
                {!! Form::open(['url' => $character->url . '/roll']) !!}
                {!! Form::submit('Roll!', ['class' => 'btn btn-primary']) !!}
                {!! Form::close() !!}
            </div>
            <Div class="col">
                {!! Form::open(['url' => $character->bids->where('user_id', $character->winner_id)->where('status', 'Accepted')->first()->url . '/accept']) !!}
                {!! Form::submit('Accept', ['class' => 'btn btn-success']) !!}
                {!! Form::close() !!}
            </div>
        </div>
    @else
        <p>This will roll a winner for the character <b>{{ $character->character->name }}</b>.</p>
        {!! Form::open(['url' => $character->url . '/roll']) !!}
        {!! Form::submit('Roll!', ['class' => 'btn btn-primary']) !!}
        {!! Form::close() !!}
    @endif
</div>
