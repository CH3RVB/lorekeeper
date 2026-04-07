@extends('user_sales.layout')

@section('user_sales-title')
    {{ $sales->title }}
@endsection

@section('user_sales-content')
    @php $authUser = Auth::check() ? Auth::user()->with('rank', 'rank.powers')->first() : null; @endphp
    {!! breadcrumbs(['User Sales' => 'user-sales', $sales->title => $sales->url, ucfirst($bid->type(true)) . ' by ' . $bidder->name => 'user-sales/bids' . $bid->id]) !!}


    <div class="col-lg mb-2">
        @include('sales._character', ['character' => $character, 'page' => true, 'authUser' => $authUser])
    </div>
    <hr class="mt-5" />

    @include('sales._bid')

    @if ($sales->allow_comments)
        <hr class="mb-5" />

        <div class="text-muted text-center">Comments below are for this {{ $bid->type(true) }} and not the sale itself.</div>
        @comments(['model' => $bid, 'perPage' => 5])
    @endif
@endsection
