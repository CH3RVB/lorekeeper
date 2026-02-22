<h2>{{ $character->bidType() }}</h2>
@if ($bids->count() < 1)
    <div class="alert alert-warning">There are no {{ $character->bidType() }} yet.</div>
@endif
<div class="d-flex mw-100 row mx-0" style="overflow:hidden;">

    @foreach ($bids as $bid)
        @include('sales._bid', [
            'bid' => $bid,
        ])
    @endforeach
</div>
