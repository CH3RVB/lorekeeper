<div class="submission-character-row mb-2">
    <div class="submission-character-thumbnail"><a href="{{ $character->character->url }}"><img src="{{ $character->character->image->thumbnailUrl }}" class="img-thumbnail" alt="Thumbnail for {{ $character->character->fullName }}" /></a></div>
    <div class="submission-character-info card ml-2">
        <div class="card-body">
            <div class="submission-character-info-content">
                <h3 class="mb-2 submission-character-info-header"><a href="{{ $character->character->url }}">{{ $character->character->fullName }}</a> • {{ $character->getData()['displayType'] }}</h3>
                <div class="submission-character-info-body">
                    <div class="mb-1">
                        <div class="row">
                            <div class="col-md-2 col-4">
                                <h5>Price</h5>
                            </div>
                            <div class="col-md-10 col-8">{!! $character->getData()['displayPrice'] !!} </div>
                        </div>
                        @if ($data['type'] == 'pwyw' || $data['type'] == 'ota' || $data['type'] == 'xta' || $data['type'] == 'raffle')
                            <div class="row">
                                <div class="col-md-2 col-4">
                                    <h5>End Point</h5>
                                </div>
                                <div class="col-md-10 col-8">{{ isset($data['data']['end_point']) ? $data['data']['end_point'] : 'N/A' }}</div>
                            </div>
                        @endif
                    </div>
                    <hr>
                    <h5>Description</h5>
                    {!! isset($data['description']) ? $data['description'] : '<p>None provided.</p>' !!}
                </div>
            </div>
        </div>
    </div>
</div>
