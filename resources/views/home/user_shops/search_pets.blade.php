@extends('home.user_shops.layout')

@section('home.user_shops-title') User Shop Search @endsection

@section('home.user_shops-content')
{!! breadcrumbs(['User Shops' => 'usershops/shop-index', 'Pet Search' => 'usershops/pet-search']) !!}

<h1>User Shop Pet Search</h1>

<p>Select an pet that you are looking to buy from other users, and you will be able to see if any shops are currently stocking it, as well as the cost of each user's pets.</p>

{!! Form::open(['method' => 'GET', 'class' => '']) !!}
<div class="form-inline justify-content-end">
    <div class="form-group ml-3 mb-3">
        {!! Form::select('pet_id', $pets, Request::get('pet_id'), ['class' => 'form-control selectize', 'placeholder' => 'Select a Pet', 'style' => 'width: 25em; max-width: 100%;']) !!}
    </div>
    <div class="form-group ml-3 mb-3">
        {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
    </div>
</div>
{!! Form::close() !!}

@if($pet)
    <h3>{{ $pet->name }}</h3>
@if($shopPets->pluck('quantity')->count() > 0)
    <div class="row ml-md-2">
    <div class="d-flex row flex-wrap col-12 pb-1 px-0 ubt-bottom">
      <div class="col-12 col-md-3 font-weight-bold">Shop</div>
      <div class="col-4 col-md-3 font-weight-bold">Shop Owner</div> 
      <div class="col-4 col-md-3 font-weight-bold">Quantity</div> 
      <div class="col-4 col-md-3 font-weight-bold">Cost</div> 
    </div>
    @foreach($shops as $shop)
    @php 
    $petStock = $shop->stock->where('user_shop_id', $shop->id)->where('item_id', $pet->id)->where('stock_type', 'Pet')->first();
    @endphp
    <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 ubt-top">
      <div class="col-12 col-md-3 ">{!! $shop->displayName !!}</div>
      <div class="col-4 col-md-3">{!! $shop->user->displayName !!}</div> 
      <div class="col-4 col-md-3">{!! $petStock->quantity !!}</div> 
      <div class="col-4 col-md-3">{!! $petStock->cost !!} {!! $petStock->currency->name !!}</div> 
    </div>
    @endforeach
  </div>
  @else
  No shops are currently stocking this pet.
  @endif
@endif

<script>
    $(document).ready(function() {
        $('.selectize').selectize();
    });
</script>

@endsection