@extends('character.layout', ['isMyo' => $character->is_myo_slot])

@section('profile-title') Editing {{ $character->fullName }}'s Profile @endsection

@section('meta-img') {{ $character->image->thumbnailUrl }} @endsection

@section('profile-content')
@if($character->is_myo_slot)
{!! breadcrumbs(['MYO Slot Masterlist' => 'myos', $character->fullName => $character->url, 'Editing Profile' => $character->url . '/profile/edit']) !!}
@else
{!! breadcrumbs([($character->category->masterlist_sub_id ? $character->category->sublist->name.' Masterlist' : 'Character masterlist') => ($character->category->masterlist_sub_id ? 'sublist/'.$character->category->sublist->key : 'masterlist' ), $character->fullName => $character->url, 'Editing Profile' => $character->url . '/profile/edit']) !!}
@endif

@include('character._header', ['character' => $character])

@if($character->user_id != Auth::user()->id)
    <div class="alert alert-warning">
        You are editing this character as a staff member.
    </div>
@endif

{!! Form::open(['url' => $character->url . '/profile/edit']) !!}
@if(!$character->is_myo_slot)
    <div class="form-group">
        {!! Form::label('name', 'Name') !!}
        {!! Form::text('name', $character->name, ['class' => 'form-control']) !!}
    </div>
    @if(Config::get('lorekeeper.extensions.character_TH_profile_link'))
        <div class="form-group">
            {!! Form::label('link', 'Profile Link') !!}
            {!! Form::text('link', $character->profile->link, ['class' => 'form-control']) !!}
        </div>
    @endif
@endif
<div class="form-group">
    {!! Form::label('text', 'Profile Content') !!}
    {!! Form::textarea('text', $character->profile->text, ['class' => 'wysiwyg form-control']) !!}
</div>

@if($character->user_id == Auth::user()->id)
    @if(!$character->is_myo_slot)
        <div class="row">
            <div class="col-md form-group">
                {!! Form::label('is_gift_art_allowed', 'Allow Gift Art', ['class' => 'form-check-label mb-3']) !!} {!! add_help('This will place the character on the list of characters that can be drawn for gift art. This does not have any other functionality, but allow users looking for characters to draw to find your character easily.') !!}
                {!! Form::select('is_gift_art_allowed', [0 => 'No', 1 => 'Yes', 2 => 'Ask First'], $character->is_gift_art_allowed, ['class' => 'form-control user-select']) !!}
            </div>
            <div class="col-md form-group">
                {!! Form::label('is_gift_writing_allowed', 'Allow Gift Writing', ['class' => 'form-check-label mb-3']) !!} {!! add_help('This will place the character on the list of characters that can be written about for gift writing. This does not have any other functionality, but allow users looking for characters to write about to find your character easily.') !!}
                {!! Form::select('is_gift_writing_allowed', [0 => 'No', 1 => 'Yes', 2 => 'Ask First'], $character->is_gift_writing_allowed, ['class' => 'form-control user-select']) !!}
            </div>
        </div>
    @endif
    @if($character->is_tradeable ||  $character->is_sellable)
        <div class="form-group disabled">
            {!! Form::checkbox('is_trading', 1, $character->is_trading, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('is_trading', 'Up For Trade', ['class' => 'form-check-label ml-3']) !!} {!! add_help('This will place the character on the list of characters that are currently up for trade. This does not have any other functionality, but allow users looking for trades to find your character easily.') !!}
        </div>
    @else 
        <div class="alert alert-secondary">Cannot be set to "Up for Trade" as character cannot be traded or sold.</div>
    @endif
@endif
@if($character->user_id != Auth::user()->id)
    <div class="form-group">
        {!! Form::checkbox('alert_user', 1, true, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'data-onstyle' => 'danger']) !!}
        {!! Form::label('alert_user', 'Notify User', ['class' => 'form-check-label ml-3']) !!} {!! add_help('This will send a notification to the user that their character profile has been edited. A notification will not be sent if the character is not visible.') !!}
    </div>
@endif
<div class="text-right">
    {!! Form::submit('Edit Profile', ['class' => 'btn btn-primary']) !!}
</div>
{!! Form::close() !!}

@if (Auth::check() &&
        (($character->user_id == Auth::user()->id && Settings::get('custom_character_icon')) ||
            Auth::user()->hasPower('manage_characters')))
    <hr>
    <h5>Change Icon</h5>
    {!! Form::open(['url' => $character->url . '/icon', 'files' => true]) !!}
    <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('Icon') !!}
                    <div>{!! Form::file('icon') !!}</div>
                    <div class="text-muted">Recommended size: 100px x 100px</div>
                    @if ($character->has_icon)
                        <div class="form-check">
                            {!! Form::checkbox('remove_icon', 1, false, ['class' => 'form-check-input']) !!}
                            {!! Form::label('remove_icon', 'Remove current icon', ['class' => 'form-check-label']) !!}
                        </div>
                    @endif
                </div>
                @if ($character->has_icon)
                    <div class="form-group">
                        <h5>Current</h5>
                        <img src="{{ $character->imageUrl }}" class="img-thumbnail"
                            alt="Thumbnail for {{ $character->fullName }}" />
                        <br>
                    </div>
                @endif
            </div>
        <div class="col-md-6">
            {!! Form::label('Icon Artist (Optional)') !!} {!! add_help('Provide the artist\'s username if they are on
                        site or, failing that, a link.') !!}
            <div class="row">
                <div class="col-md">
                    <div class="form-group">
                        {!! Form::select('artist_id', $userOptions, $character->artist_id ? $character->artist_id : null, [
                            'class' => 'form-control mr-2 selectize',
                        ]) !!}
                    </div>
                </div>
                <div class="col-md">
                    <div class="form-group">
                        {!! Form::text('artist_url', $character->artist_url ? $character->artist_url : '', [
                            'class' => 'form-control
                                                mr-2',
                            'placeholder' => 'Artist URL',
                        ]) !!}
                    </div>
                </div>
            </div>
            @if ($character->has_icon)
                <div class="form-check">
                    {!! Form::checkbox('remove_credit', 1, false, ['class' => 'form-check-input']) !!}
                    {!! Form::label('remove_credit', 'Remove current credits', ['class' => 'form-check-label']) !!}
                </div>
            @endif
        </div>
    </div>
    <div class="text-right">
        {!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
    </div>
    {!! Form::close() !!}
@endif


@endsection