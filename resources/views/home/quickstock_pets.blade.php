@extends('home.layout')

@section('home-title')
    Quickstock
@endsection

@section('home-content')
    {!! breadcrumbs(['Pets' => 'pets', 'Quickstock' => 'quickstock']) !!}

    <h1>
        Quickstock
    </h1>

    <p>This is your inventory's quickstock. You can quickly mass-transfer pets to your shop here.</p>
    <p>If a pet is grayed out, you will not be able to transfer it.</p>
    @if (Auth::user()->shops->count())
        {!! Form::open(['url' => 'pets/quickstock']) !!}
        <div class="form-group">
            {!! Form::select('shop_id', $shopOptions, null, [
                'class' => 'form-control mr-2 default shop-select',
                'placeholder' => 'Select Shop',
            ]) !!}
        </div>
        @include('widgets._pet_select', ['user' => Auth::user(), 'petinventory' => $petinventory, 'pet' => $pet, 'page' => 'quickstock'])

        <div class="text-right">
            {!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    @else
        <div class="alert alert-warning text-center">
            You can't stock a shop if you <a href="{{ url('usershops/create') }}">don't have one...</a>
        </div>
    @endif
@endsection

@section('scripts')
    @parent

    @include('widgets._pet_select_js', ['readOnly' => true])
@endsection
