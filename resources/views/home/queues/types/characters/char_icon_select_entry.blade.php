<div class="submission-character mb-3 card">
    <div class="card-body">
        <div class="text-right"><a href="#" class="remove-character text-muted"><i class="fas fa-times"></i></a></div>
        <div class="row">
            <div class="col-md-2 align-items-stretch d-flex">
                <div class="d-flex text-center align-items-center">
                    <div class="character-image-blank">Enter character code.</div>
                    <div class="character-image-loaded">
                        @include('home.queues._character', ['character' => $character->character ? $character->character : $character])
                    </div>
                </div>
            </div>
            <div class="col-md-10">
                <div class="form-group">
                    {!! Form::label('slug[]', 'Character Code') !!}
                    {!! Form::select('slug[]', $charSelect, $character->character ? $character->character->slug : $character->slug, ['class' => 'form-control character-code', 'placeholder' => 'Select Character']) !!}
                </div>
                <div class="character-rewards">
                    {!! Form::label('Custom Icon') !!}
                    <div>{!! Form::file('icon[' . $character->character_id . '][]', [
                        'class' => 'add-icon',
                    ]) !!}</div>
                    <div class="text-muted">Recommended size: {{ Config::get('lorekeeper.settings.masterlist_thumbnails.width') }}px x {{ Config::get('lorekeeper.settings.masterlist_thumbnails.height') }}px</div>
                    @if ($queue->customImageExists($submission->id . '-' . $character->character->id))
                        <h5>Current Image</h5>
                        <img src="{{ $queue->customImageUrl($submission->id . '-' . $character->character->id) }}" class="img-fluid mb-2" style="max-height:{{ Config::get('lorekeeper.settings.masterlist_thumbnails.width') }}px">
                        <div class="col-md-6">
                            {!! Form::label('Icon Artist (Optional)') !!} {!! add_help('Provide the artist\'s username if they are on site or, failing that, a link.') !!}
                            <div class="row">
                                <div class="col-md">
                                    <div class="form-group">
                                        {!! Form::select('artist_id[' . $character->character_id . '][]', $userOptions, isset($character->data['artist_id']) ? $character->data['artist_id'] : null, [
                                            'class' => 'form-control mr-2 add-artist selectize',
                                        ]) !!}
                                    </div>
                                </div>
                                <div class="col-md">
                                    <div class="form-group">
                                        {!! Form::text('artist_url[' . $character->character_id . '][]', isset($character->data['artist_url']) ? $character->data['artist_url'] : '', [
                                            'class' => 'form-control add-offsite mr-2',
                                            'placeholder' => 'Artist URL',
                                        ]) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
