@extends('user_sales.layout')

@section('user_sales-title')
    {{ $sales->title }}
@endsection

@section('user_sales-content')
    {!! breadcrumbs(['User Sales' => 'user-sales', $sales->title => $sales->url, ucfirst($bid->type(true)).' by ' . $bidder->name => 'user-sales/bids' . $bid->id]) !!}


    <div class="col-lg mb-2">
        @include('sales._character', ['character' => $character, 'page' => true])
    </div>
    <hr class="mt-5" />

    @include('sales._bid')

    <hr class="mb-5" />

    <div class="text-muted text-center">Comments below are for this {{ $bid->type(true) }} and not the sale itself.</div>
    @comments(['model' => $bid, 'perPage' => 5])
@endsection
