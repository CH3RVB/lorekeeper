@extends('user_sales.layout')

@section('user_sales-title')
    {{ $sales->title }}
@endsection

@section('user_sales-content')
    {!! breadcrumbs(['User Sales' => 'user-sales', $sales->title => $sales->url, 'Bid by'$user->name => 'user-sales/bids'.$bid->id]) !!}
    @include('user_sales._sales', ['sales' => $sales, 'page' => true])

    <div>
        <div class="d-flex mw-100 row mx-0" style="overflow:hidden;">
            @if ($bids->count() < 1)
                <div class="alert alert-warning">There are no offers yet.</div>
            @else
                @foreach ($bids as $bid)
                    dfhdfh
                @endforeach
            @endif
        </div>

    </div>

    <hr class="mb-5" />

    <hr>
    <br><br>
    @comments(['model' => $sales, 'perPage' => 5])
@endsection
