<div class="card h-100">
    <div class="m-1">
        <div class="row">
            @php
                $char = $character->character;
                $characterImage = $character->image;
                $currentUserId = $authUserId ?? ($authUser?->id ?? Auth::check()) ? Auth::user()->id : null;
            @endphp
            <div class="col-md-6 text-center align-self-center">
                <a href="{{ $char->url }}"><img src="{{ isset($page) || $loop->count == 1 ? $characterImage->imageUrl : $characterImage->thumbnailUrl }}" class="mw-100 img-thumbnail" alt="{{ $character->fullName }}" /></a>
            </div>
            <div class="col-md text-center">
                <div class="mt-2">
                    <h5>
                        @if (isset($character->data['is_fto']))
                            <span class="badge badge-success float-right" data-toggle="tooltip" title="You may only offer on this character if you are a FTO.">FTO Only</span>
                        @endif {{ $character->displayType }}: <a href="{{ $char->url }}">{!! $char->slug !!}</a> ・ <span class="{{ $character->isActive ? 'text-success' : '' }}">[{{ $character->isActive ? 'Open' : 'Closed' }}]</span><br />
                        <small>
                            {!! $characterImage->species->displayName !!} ・ {!! $characterImage->rarity->displayName !!}<br />
                        </small>
                    </h5>

                    @if (!isset($page) && $loop->count == 1)
                        <div class="mb-2">
                            @if (Config::get('lorekeeper.extensions.traits_by_category'))
                                <div>
                                    @php $traitgroup = $characterImage->features()->get()->groupBy('feature_category_id') @endphp
                                    @if ($characterImage->features()->count())
                                        @foreach ($traitgroup as $key => $group)
                                            <div>
                                                @if ($group->count() > 1)
                                                    <div>
                                                        <strong>{!! $key ? $group->first()->feature->category->displayName : 'Miscellaneous' !!}:</strong>
                                                        @foreach ($group as $feature)
                                                            {!! $feature->feature->displayName !!}@if ($feature->data)
                                                                ({{ $feature->data }})
                                                            @endif{{ !$loop->last ? ', ' : '' }}
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <strong>{!! $key ? $group->first()->feature->category->displayName : 'Miscellaneous' !!}:</strong>
                                                    {!! $group->first()->feature->displayName !!}
                                                    @if ($group->first()->data)
                                                        ({{ $group->first()->data }})
                                                    @endif
                                                @endif
                                            </div>
                                        @endforeach
                                    @else
                                        <div>No traits listed.</div>
                                    @endif
                                </div>
                            @else
                                <div>
                                    <?php $features = $characterImage->features()->with('feature.category')->get(); ?>
                                    @if ($features->count())
                                        @foreach ($features as $feature)
                                            <div>
                                                @if ($feature->feature->feature_category_id)
                                                    <strong>{!! $feature->feature->category->displayName !!}:</strong>
                                                    @endif {!! $feature->feature->displayName !!} @if ($feature->data)
                                                        ({{ $feature->data }})
                                                    @endif
                                            </div>
                                        @endforeach
                                    @else
                                        <div>No traits listed.</div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endif

                    <h6>
                        <div class="mb-2">
                            Design:
                            @foreach ($characterImage->designers as $designer)
                                {!! $designer->displayLink() !!}{{ !$loop->last ? ', ' : '' }}
                            @endforeach ・
                            Art:
                            @foreach ($characterImage->artists as $artist)
                                {!! $artist->displayLink() !!}{{ !$loop->last ? ', ' : '' }}
                            @endforeach
                        </div>
                        <h4>Price </h4>
                        @if ($character->type == 'auction')
                            <h5>
                                <strong class="text-success">Latest Bid:</strong>
                                @if ($character->lastBid)
                                    {!! $character->lastBid->user->displayName !!} for
                                    {!! $character->costFormat($character->lastBid->data['bid']) !!}
                                @else
                                    None
                                @endif

                                <br>
                            </h5>
                        @endif
                        {!! $character->price !!}

                        {!! isset($character->link) || isset($character->data['end_point']) ? '<br/>' : '' !!}
                        {{-- @dd($character->data['end_point']) --}}
                        @php $validator = isset($character->data['end_point']) ? Validator::make(['date' => $character->data['end_point']], ['date' => 'date']) : null; @endphp
                        @if (isset($character->data['end_point']) && $validator && $validator->passes())
                            <strong>Ends:</strong> {!! Carbon\Carbon::parse($character->data['end_point']) ? pretty_date(Carbon\Carbon::parse($character->data['end_point'])) : $character->data['end_point'] !!}
                        @endif
                        @if (isset($character->data['snipe_guard']) && isset($character->data['end_point']) && $validator && $validator->passes())
                            <hr>
                            <p>
                                <span class="text-danger">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Snipe guard is in effect.
                                </span>
                                Each bid within <b>{!! $character->properGuard($character->data['snipe_period']) !!}</b> of the end time will extend the auction by <b>{!! $character->properGuard($character->data['snipe_guard'])  !!}</b> until no more bids are made.
                            </p>
                            <strong>Current Guarded Endpoint:</strong> {!! pretty_date($character->auctionEnd) !!}
                            <hr>
                        @endif
                    </h6>

                    <p>{!! $character->description !!}</p>
                </div>
                @if ($character->canBid())
                    {!! Form::open(['url' => 'sales/bid/' . $character->id, 'id' => 'submissionForm' . $character->id]) !!}

                    <div class="text-right">
                        <a href="#" class="btn btn-primary" id="submitButton{{ $character->id }}">{{ $character->typeLink }}</a>
                    </div>
                    {!! Form::close() !!}

                    <script>
                        $(document).ready(function() {
                            $('#submitButton{{ $character->id }}').on('click', function(e) {
                                e.preventDefault();
                                loadModal("{{ url('sales/bid/') }}/{{ $character->id }}", 'Make Offer');
                            });
                        });
                    </script>
                @else
                    <p>{{ $character->canBid(true) }}</p>

                    @if ($character->is_rolled && $character->winner_id)
                    <p>The winner of the raffle is <b>{!! $character->winner->displayName !!}</b>.</p>
                    @endif
                    @php
                        $userRaffleBid = null;
                        if (($character->type == 'flaffle' || $character->type == 'raffle') && $character->isActive && $character->sales->is_open && $currentUserId) {
                            $userRaffleBid = $character->bids->firstWhere('user_id', $currentUserId);
                        }
                    @endphp
                    @if ($userRaffleBid)
                        <p>You may remove your entry
                            @if ($character->type == 'flaffle')
                                , and your entry cost will be refunded if paid
                            @endif
                            .
                        </p>
                        {!! Form::open(['url' => $userRaffleBid->url . '/leave']) !!}
                        {!! Form::submit('Leave Raffle', ['class' => 'btn btn-danger']) !!}

                        {!! Form::close() !!}
                    @endif
                @endif
                <div class="text-right mb-2 mr-2">
                    <span class="btn"><i class="fas fa-store"></i>
                        @if ($character->canViewBid)
                            <a href="{{ $character->url }}">{{ $character->bids->count() }} {{ ucfirst($character->bidType()) }}</a>
                        @else
                            {{ $character->bids->count() }} {{ ucfirst($character->bidType()) }}
                        @endif
                    </span>
                    @if (($character->type == 'flaffle' || $character->type == 'raffle') && $character->canManageBid)
                        <a href="#" class="btn btn-info" id="rollButton{{ $character->id }}">Roll/Reroll</a>
                        <script>
                            $(document).ready(function() {
                                $('#rollButton{{ $character->id }}').on('click', function(e) {
                                    e.preventDefault();
                                    loadModal("{{ $character->url }}/roll", 'Roll');
                                });
                            });
                        </script>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
