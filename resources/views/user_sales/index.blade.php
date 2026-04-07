@extends('user_sales.layout')

@section('title')
    User Sales
@endsection

@section('user_sales-content')
    @php $authUser = Auth::check() ? Auth::user()->with('rank', 'rank.powers')->first() : null; @endphp
    {!! breadcrumbs(['User Sales' => 'user-sales']) !!}
    <h1>User Sales</h1>
    <p>Here is a list of all currently active character sales made by users.</p>
    <div class="text-right mb-3">
        <a class="btn btn-primary" href="{{ url('user-sales/my-sales') }}"> My Sales</a>
        @if ($queue)
            <a class="btn btn-primary" href="{{ $queue->url }}"><i class="fas fa-plus"></i> Submit New Sales Post</a>
        @endif
    </div>

    <div>
        {!! Form::open(['method' => 'GET', 'class' => '']) !!}
        <div class="form-inline justify-content-end">
            <div class="form-group ml-3 mb-3">
                {!! Form::text('title', Request::get('title'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
            </div>
            <div class="form-group ml-3 mb-3">
                {!! Form::select('is_open', ['1' => 'Open', '0' => 'Closed'], Request::get('is_open'), ['class' => 'form-control', 'placeholder' => 'Status']) !!}
            </div>
        </div>
        <div class="form-inline justify-content-end">
            <div class="form-group ml-3 mb-3">
                {!! Form::select(
                    'sort',
                    [
                        'bump-reverse' => 'Updated Newest',
                        'bump' => 'Updated Oldest',
                        'newest' => 'Created Newest',
                        'oldest' => 'Created Oldest',
                        'alpha' => 'Sort Alphabetically (A-Z)',
                        'alpha-reverse' => 'Sort Alphabetically (Z-A)',
                    ],
                    Request::get('sort') ?: 'Updated Newest',
                    ['class' => 'form-control'],
                ) !!}
            </div>
            <div class="form-group ml-3 mb-3">
                {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
            </div>
        </div>
        {!! Form::close() !!}
    </div>

    @if (count($saleses))
        {!! $saleses->render() !!}
        @foreach ($saleses as $sales)
            @include('sales._sales', ['sales' => $sales, 'page' => false, 'authUser' => $authUser])
        @endforeach
        {!! $saleses->render() !!}
    @else
        <div>No sales posts yet.</div>
    @endif
@endsection
