@extends('home.user_shops.layout')

@section('home.user_shops-title') User Shop Search @endsection

@section('home.user_shops-content')
{!! breadcrumbs(['User Shops' => 'user-shops/shop-index', 'Pet Search' => 'user-shops/pet-search']) !!}

    <h1>User Shop Pet Search</h1>

    <p>Select an pet that you are looking to buy from other users, and you will be able to see if any shops are currently stocking it, as well as the cost of each user's pets.</p>
    <p>Pets that are not currently stocked by any shops will not be shown.</p>
    <p>Selecting a category will limit the search to only pets in that category, unless they have been specifically added to the search.</p>

    {!! Form::open(['method' => 'GET', 'class' => '']) !!}
    <div class="form-inline justify-content-end">
        <div class="form-group ml-3 mb-3">
            {!! Form::select('pet_ids[]', $pets, Request::get('pet_ids'), [
                'id' => 'petList',
                'class' => 'form-control',
                'placeholder' => 'Select Pets',
                'style' => 'width: 25em; max-width: 100%;',
                'multiple'
                ])
            !!}
        </div>
        <div class="form-group ml-3 mb-3">
            {!! Form::select('pet_category_id', $categories, Request::get('pet_category_id'), ['class' => 'form-control', 'placeholder' => 'Search by Category']) !!}
        </div>
        <div class="form-group ml-3 mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
    </div>
    {!! Form::close() !!}

    @if($searched_pets)
        <h3>Search Results</h3>
        <p><b>Searching for: </b>{!! $searched_pets->pluck('name')->implode(', ') !!}</p>
        @if($category)
            <p>
                <b>Category: </b>{!! $category->displayName !!}
                <br><small>Note that pets listed also include pets from the chosen category.</small>
            </p>
        @endif
        @if(count($shopPets) && $shopPets->pluck('quantity')->count() > 0)
            <div class="row ml-md-2">
                <div class="d-flex row flex-wrap col-12 pb-1 px-0 ubt-bottom">
                    <div class="col col-md-3 font-weight-bold">Pet</div>
                    <div class="col col-md-3 font-weight-bold">Shop</div>
                    <div class="col col-md-2 font-weight-bold">Shop Owner</div>
                    <div class="col col-md-2 font-weight-bold">Quantity</div>
                    <div class="col col-md-2 font-weight-bold">Cost</div>
                </div>
                @foreach($shopPets as $petStock)
                    @php
                        $shop = $petStock->shop;
                    @endphp
                    <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 ubt-top">
                        <div class="col col-md-3">{{ $petStock->item->VariantName($petStock->variant_id) }}</div>
                        <div class="col col-md-3">{!! $shop->displayName !!}</div>
                        <div class="col col-md-2">{!! $shop->user->displayName !!}</div>
                        <div class="col col-md-2">{!! $petStock->quantity !!}</div>
                        <div class="col col-md-2">{!! $petStock->cost !!} {!! $petStock->currency->name !!}</div>
                    </div>
                @endforeach
            </div>
        @else
            No shops are currently stocking the selected pets.
        @endif
    @endif

<script>
    $(document).ready(function() {
        $('#petList').selectize({
            maxPets: 10
        });
    });
</script>

@endsection