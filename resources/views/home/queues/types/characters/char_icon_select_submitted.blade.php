<div class="submission-character-row mb-2">
    <div class="submission-character-thumbnail">
        <a href="{{ $character->character->url }}"><img src="{{ $character->character->image->thumbnailUrl }}" class="img-thumbnail" alt="Thumbnail for {{ $character->character->fullName }}" /></a>
    </div>
    <div class="submission-character-info card ml-2">
        <div class="card-body">
            <div class="submission-character-info-content">
                <h3 class="mb-2 submission-character-info-header"><a href="{{ $character->character->url }}">{{ $character->character->fullName }}</a></h3>
                <div class="submission-character-info-body">
                    <h5>Custom Icon:</h5>
                    @if ($queue->customImageExists($submission->id . '-' . $character->character->id))
                        <img src="{{ $queue->customImageUrl($submission->id . '-' . $character->character->id) }}" class="img-fluid" style="max-height:{{ Config::get('lorekeeper.settings.masterlist_thumbnails.width') }}px">
                        <br>
                        <h5>Credits</h5>
                        <div class="row">
                            <div class="col-md">
                                <strong>Onsite</strong>
                                <div class="form-group">
                                    {!! isset($character->data['artist_id']) ? $character->iconArtist : 'None given.'!!}
                                </div>
                            </div>
                            <div class="col-md">
                                <strong>Offsite</strong>
                                <div class="form-group">
                                    {!!isset($character->data['artist_url']) ? prettyProfileLink($character->data['artist_url']) : 'None given.'!!}
                                </div>
                            </div>
                        </div>
                    @else
                        <p class="text-danger">There is no icon given. Something wrong has occurred.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
