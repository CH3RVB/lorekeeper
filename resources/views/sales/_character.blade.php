<div class="card h-100">
    <div class="m-1">
        <div class="row">
            <div class="col-md-6 text-center align-self-center">
                <a href="{{ $character->character->url }}"><img src="{{ isset($page) || $loop->count == 1 ? $character->image->imageUrl : $character->image->thumbnailUrl }}" class="mw-100 img-thumbnail" alt="{{ $character->fullName }}" /></a>
            </div>
            <div class="col-md text-center">
                <div class="mt-2">
                    <h5>
                        @if (isset($character->data['is_fto']))
                            <span class="badge badge-success float-right" data-toggle="tooltip" title="You may only offer on this character if you are a FTO.">FTO Only</span>
                        @endif {{ $character->displayType }}: <a href="{{ $character->character->url }}">{!! $character->character->slug !!}</a> ・ <span
                            class="{{ $character->isActive ? 'text-success' : '' }}">[{{ $character->isActive ? 'Open' : 'Closed' }}]</span><br />
                        <small>
                            {!! $character->image->species->displayName !!} ・ {!! $character->image->rarity->displayName !!}<br />
                        </small>
                    </h5>

                    @if (!isset($page) && $loop->count == 1)
                        <div class="mb-2">
                            @if (Config::get('lorekeeper.extensions.traits_by_category'))
                                <div>
                                    @php $traitgroup = $character->image->features()->get()->groupBy('feature_category_id') @endphp
                                    @if ($character->image->features()->count())
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
                                    <?php $features = $character->image->features()->with('feature.category')->get(); ?>
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
                            @foreach ($character->image->designers as $designer)
                                {!! $designer->displayLink() !!}{{ !$loop->last ? ', ' : '' }}
                            @endforeach ・
                            Art:
                            @foreach ($character->image->artists as $artist)
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
                        @if (isset($character->data['end_point']) &&  Carbon\Carbon::createFromFormat('d/m/Y', $character->data['end_point']))
                            <strong>Ends:</strong> {!! Carbon\Carbon::parse($character->data['end_point']) ? pretty_date(Carbon\Carbon::parse($character->data['end_point'])) : $character->data['end_point'] !!}
                        @endif
                        @if (isset($character->data['snipe_guard']) && isset($character->data['end_point']) && Carbon\Carbon::createFromFormat('d/m/Y', $character->data['end_point']))
                            <hr>
                            <p>
                                <span class="text-danger">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Snipe guard is in effect.
                                </span>
                                Each bid within {!! $character->data['snipe_period'] !!} minutes of the end time will extend the auction by {!! $character->data['snipe_guard'] !!} minutes until no more bids are made.
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
                    @if (($character->type == 'flaffle' || $character->type == 'raffle') && $character->isActive && $character->sales->is_open && Auth::check() &&  $character->bids->where('user_id', Auth::user()->id)->first())
                        <p>You may remove your entry
                            @if ($character->type == 'flaffle')
                                , and your entry cost will be refunded if paid
                            @endif
                            .
                        </p>
                        {!! Form::open(['url' => $character->bids->where('user_id', Auth::user()->id)->first()->url . '/leave']) !!}
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
