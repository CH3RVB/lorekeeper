@extends('user.layout')

@section('profile-title') {{ $user->name }}'s Armoury @endsection

@section('profile-content')
{!! breadcrumbs(['Users' => 'users', $user->name => $user->url, 'Armoury' => $user->url . '/armoury']) !!}

<h1>
    Armoury
</h1>

<h3>Weapons</h3>
@foreach($weapons as $categoryId=>$categoryWeapons)
    <div class="card mb-3 inventory-category">
        <h5 class="card-header inventory-header">
            {!! isset($weaponCategories[$categoryId]) ? '<a href="'.$weaponCategories[$categoryId]->searchUrl.'">'.$weaponCategories[$categoryId]->name.'</a>' : 'Miscellaneous' !!}
        </h5>
        <div class="card-body inventory-body">
            @foreach($categoryWeapons->chunk(4) as $chunk)
                <div class="row mb-3">
                    @foreach($chunk as $weapon) 
                        <div class="col-sm-3 col-6 text-center inventory-item" data-id="{{ $weapon->pivot->id }}" data-name="{{ $user->name }}'s {{ $weapon->name }}">
                            <div class="mb-1">
                                <a href="#" class="weapon-stack">@if($weapon->pivot->has_image)<img src="{{ url('images/data/user-weapons/'.$weapon->pivot->id.'-image.png') }}">@else<img src="{{ $weapon->imageUrl }}" />@endif</a>
                            </div>
                            <div>
                                <a href="#" class="weapon-stack inventory-stack-name">{{ $weapon->name }}</a>
                                @if($weapon->pivot->character_id) <p class="small">Attached to a character</p> @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
@endforeach

<h3>Gear</h3>
@foreach($gears as $categoryId=>$categoryGears)
    <div class="card mb-3 inventory-category">
        <h5 class="card-header inventory-header">
            {!! isset($gearCategories[$categoryId]) ? '<a href="'.$gearCategories[$categoryId]->searchUrl.'">'.$gearCategories[$categoryId]->name.'</a>' : 'Miscellaneous' !!}
        </h5>
        <div class="card-body inventory-body">
            @foreach($categoryGears->chunk(4) as $chunk)
                <div class="row mb-3">
                    @foreach($chunk as $gear) 
                        <div class="col-sm-3 col-6 text-center inventory-item" data-id="{{ $gear->pivot->id }}" data-name="{{ $user->name }}'s {{ $gear->name }}">
                            <div class="mb-1">
                                <a href="#" class="gear-stack">@if($gear->pivot->has_image)<img src="{{ url('images/data/user-gears/'.$gear->pivot->id.'-image.png') }}">@else<img src="{{ $gear->imageUrl }}" />@endif</a>
                            </div>
                            <div>
                                <a href="#" class="gear-stack inventory-stack-name">{{ $gear->name }}</a>
                                @if($gear->pivot->character_id) <p class="small">Attached to a character</p> @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
@endforeach

<h3>Latest Activity</h3>

<h5>Gear</h5>
<div class="row ml-md-2 mb-4">
    <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 ubt-bottom">
      <div class="col-6 col-md-2 font-weight-bold">Sender</div>
      <div class="col-6 col-md-2 font-weight-bold">Recipient</div>
      <div class="col-6 col-md-2 font-weight-bold">Gear</div>
      <div class="col-6 col-md-4 font-weight-bold">Log</div>
      <div class="col-6 col-md-2 font-weight-bold">Date</div>
    </div>
        @foreach($gearLogs as $log)
            @include('user._gear_log_row', ['log' => $log, 'owner' => $user])
        @endforeach
    </div>
<div class="text-right">
    <a href="{{ url($user->url.'/gear-logs') }}">View all...</a>
</div>

<h5>Weapon</h5>
<div class="row ml-md-2 mb-4">
    <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 ubt-bottom">
      <div class="col-6 col-md-2 font-weight-bold">Sender</div>
      <div class="col-6 col-md-2 font-weight-bold">Recipient</div>
      <div class="col-6 col-md-2 font-weight-bold">Weapon</div>
      <div class="col-6 col-md-4 font-weight-bold">Log</div>
      <div class="col-6 col-md-2 font-weight-bold">Date</div>
    </div>
        @foreach($weaponLogs as $log)
            @include('user._weapon_log_row', ['log' => $log, 'owner' => $user])
        @endforeach
    </div>
<div class="text-right">
    <a href="{{ url($user->url.'/weapon-logs') }}">View all...</a>
</div>

@endsection

@section('scripts')
<script>

$( document ).ready(function() {
    $('.weapon-stack').on('click', function(e) {
        e.preventDefault();
        var $parent = $(this).parent().parent();
        loadModal("{{ url('weapons') }}/" + $parent.data('id'), $parent.data('name'));
    });
    $('.gear-stack').on('click', function(e) {
        e.preventDefault();
        var $parent = $(this).parent().parent();
        loadModal("{{ url('gears') }}/" + $parent.data('id'), $parent.data('name'));
    });
});

</script>
@endsection