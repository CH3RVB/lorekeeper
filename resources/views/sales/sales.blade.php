@extends('sales.layout')

@section('sales-title')
    {{ $sales->title }}
@endsection

@section('sales-content')
    @php $authUser = Auth::check() ? Auth::user()->with('rank', 'rank.powers')->first() : null; @endphp
    {!! breadcrumbs(['Site Sales' => 'sales', $sales->title => $sales->url]) !!}
    @include('sales._sales', ['sales' => $sales, 'page' => true, 'authUser' => $authUser])

    <hr class="mb-5" />
    @if ((isset($sales->comments_open_at) && $sales->comments_open_at < Carbon\Carbon::now()) || ($authUser && ($authUser->hasPower('manage_sales') || $authUser->hasPower('comment_on_sales'))) || !isset($sales->comments_open_at))
        @comments(['model' => $sales, 'perPage' => 5])
    @else
        <div class="alert alert-warning text-center">
            <p>Comments for this sale aren't open yet! They will open {!! pretty_date($sales->comments_open_at) !!}.</p>
        </div>
    @endif
@endsection
