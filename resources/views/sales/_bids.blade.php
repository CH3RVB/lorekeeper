<h2>{{ $character->bidType() }}</h2>
@if ($bids->count() < 1)
    <div class="alert alert-warning">There are no {{ $character->bidType() }} yet.</div>
@endif
<div>
    {!! Form::open(['method' => 'GET', 'class' => '']) !!}
    <div class="form-inline justify-content-end">
        <div class="form-group ml-3 mb-3">
            {!! Form::label('bid', 'Bid Amount', ['class' => 'mr-2']) !!}
            {!! Form::number('bid', Request::get('bid'), ['class' => 'form-control', 'placeholder' => 'Bid']) !!}
        </div>
        <div class="form-group ml-3 mb-3">
            {!! Form::label('status', 'Status', ['class' => 'mr-2']) !!}
            {!! Form::select('status', $status, Request::get('status'), [
                'class' => 'form-control',
            ]) !!}
        </div>
        <div class="form-group ml-3 mb-3">
            {!! Form::label('featured', 'Feature Status', ['class' => 'mr-2']) !!}
            {!! Form::select('featured', $featured, Request::get('featured'), [
                'class' => 'form-control',
            ]) !!}
        </div>
    </div>
    <div class="form-inline justify-content-end">
        <div class="form-group ml-3 mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
    </div>
    {!! Form::close() !!}
</div>
{!! $bids->render() !!}
<div class="d-flex mw-100 row mx-0" style="overflow:hidden;">

    @foreach ($bids as $bid)
        @include('sales._bid', [
            'bid' => $bid,
        ])
    @endforeach
</div>
{!! $bids->render() !!}
