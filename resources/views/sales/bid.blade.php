@extends('sales.layout')

@section('sales-title')
    {{ $sales->title }}
@endsection

@section('sales-content')
    @if ($admin)
        {!! breadcrumbs(['Sales' => 'sales', $sales->title => $sales->url, ucfirst($bid->type(true)) . ' by ' . $bidder->name => $bid->url]) !!}
    @else
        {!! breadcrumbs(['User Sales' => 'user-sales', $sales->title => $sales->url, ucfirst($bid->type(true)) . ' by ' . $bidder->name => $bid->url]) !!}
    @endif


    <div class="col-lg mb-2">
        @include('sales._character', ['character' => $character, 'page' => true])
    </div>
    <hr class="mt-5" />

    @include('sales._bid')

    @if ($bid->canManageBid && $sales->allow_comments)
        <hr class="mb-5" />

        <div class="text-muted text-center">Comments below are for this {{ $bid->type(true) }} and not the sale itself.</div>
        @comments(['model' => $bid, 'perPage' => 5])
    @endif
@endsection
