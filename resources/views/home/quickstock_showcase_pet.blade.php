@extends('home.layout')

@section('home-title')
    Quickstock {{ ucfirst(__('showcase.showcase')) }}
@endsection

@section('home-content')
    {!! breadcrumbs([
        'Inventory' => 'inventory',
        'Quickstock ' . ucfirst(__('showcase.showcases')) => 'quickstock-' . __('showcase.showcase'),
    ]) !!}

    <h1>
        Quickstock {{ ucfirst(__('showcase.showcase')) }}
    </h1>

    <p>This is your inventory's quickstock. You can quickly mass-transfer items to your {{ __('showcase.showcase') }} here.
    </p>
    @if (Auth::user()->showcases->count())
        {!! Form::open(['url' => 'pets/quickstock-' . __('showcase.showcase'). '-pets']) !!}
        <div class="form-group">
            {!! Form::select('showcase_id', $showcaseOptions, null, [
                'class' => 'form-control mr-2 default showcase-select',
                'placeholder' => 'Select ' . ucfirst(__('showcase.showcases')),
            ]) !!}
        </div>
       @include('widgets._pet_select', ['user' => Auth::user(), 'petinventory' => $petinventory, 'pet' => $pet, 'page' => 'quickstock-showcase'])

        <div class="text-right">
            {!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    @else
        <div class="alert alert-warning text-center">
            You can't stock a {{ __('showcase.showcase') }} if you <a href="{{ url('showcases/create') }}">don't have
                one...</a>
        </div>
    @endif
@endsection

@section('scripts')
    @parent

    @include('widgets._inventory_select_js', ['readOnly' => true])
@endsection
