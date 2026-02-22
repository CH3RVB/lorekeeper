@extends('sales.layout')

@section('sales-title')
    {{ $sales->title }}
@endsection

@section('sales-content')
    @if ($admin)
        {!! breadcrumbs(['Sales' => 'sales', $sales->title => $sales->url, $character->character->displayName . '\'s Bids ' => $character->url]) !!}
    @else
        {!! breadcrumbs(['User Sales' => 'user-sales', $sales->title => $sales->url, $character->character->displayName . '\'s Bids ' => 'user-sales/character/bids' . $character->id]) !!}
    @endif


    <div class="col-lg mb-2">
        @include('sales._character', ['character' => $character, 'page' => true])
    </div>

    <hr class="mb-5" />
    @include('sales._bids')
@endsection
