@extends('home.layout')

@section('home-title') Gears @endsection

@section('home-content')
{!! breadcrumbs(['Gear' => 'gears']) !!}

<h1>
    Gear
</h1>

<p>This is your gear armoury. Click on an gear to view more details and actions you can perform on it.</p>
@foreach($gears as $categoryId=>$categoryGears)
    <div class="card mb-3 inventory-category">
        <h5 class="card-header inventory-header">
            {!! isset($categories[$categoryId]) ? '<a href="'.$categories[$categoryId]->searchUrl.'">'.$categories[$categoryId]->name.'</a>' : 'Miscellaneous' !!}
        </h5>
        <div class="card-body inventory-body">
            @foreach($categoryGears->chunk(4) as $chunk)
                <div class="row mb-3">
                    @foreach($chunk as $gearId=>$stack)
                        <div class="col-sm-3 col-6 text-center inventory-item" data-id="{{ $stack->pivot->id }}" data-name="{{ $user->name }}'s {{ $stack->name }}">
                            <div class="mb-1">
                                <a href="#" class="inventory-gear"><img src="{{ $stack->imageUrl }}" /></a>
                            </div>
                            <div>
                                <a href="#" class="inventory-gear inventory-gear-name">{{ $stack->name }}</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
@endforeach
<div class="text-right mb-4">
    <a href="{{ url(Auth::user()->url.'/gear-logs') }}">View logs...</a>
</div>

@endsection
@section('scripts')
<script>

$( document ).ready(function() {
    $('.inventory-gear').on('click', function(e) {
        e.preventDefault();
        var $parent = $(this).parent().parent();
        loadModal("{{ url('gears') }}/" + $parent.data('id'), $parent.data('name'));
    });
});

</script>
@endsection