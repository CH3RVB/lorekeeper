@extends('home.layout')

@section('home-title') Enchantments @endsection

@section('home-content')
{!! breadcrumbs(['Enchantment' => 'enchantments']) !!}

<h1>
    Enchantment
</h1>

<p>This is your enchantment armoury. Click on an enchantment to view more details and actions you can perform on it.</p>
@foreach($enchantments as $categoryId=>$categoryEnchantments)
    <div class="card mb-3 inventory-category">
        <h5 class="card-header inventory-header">
            {!! isset($categories[$categoryId]) ? '<a href="'.$categories[$categoryId]->searchUrl.'">'.$categories[$categoryId]->name.'</a>' : 'Miscellaneous' !!}
        </h5>
        <div class="card-body inventory-body">
            @foreach($categoryEnchantments->chunk(4) as $chunk)
                <div class="row mb-3">
                    @foreach($chunk as $enchantmentId=>$stack)
                        <div class="col-sm-3 col-6 text-center inventory-item" data-id="{{ $stack->pivot->id }}" data-name="{{ $user->name }}'s {{ $stack->name }}">
                            <div class="mb-1">
                                <a href="#" class="inventory-enchantment"><img src="{{ $stack->imageUrl }}" /></a>
                            </div>
                            <?php $stack->gear_stack_id = $stack->pivot->pluck('gear_stack_id', 'id')->toArray()[$stack->pivot->id]; ?>
                        <?php $stack->weapon_stack_id = $stack->pivot->pluck('weapon_stack_id', 'id')->toArray()[$stack->pivot->id]; ?>
                            <div>
                                <a href="#" class="inventory-enchantment inventory-enchantment-name">{{ $stack->name }}</a> @if($stack->gear_stack_id) <i class="fas fa-lock mr-2"  data-toggle="tooltip" title="Attached to a gear"></i> @endif  @if($stack->weapon_stack_id) <i class="fas fa-lock mr-2"  data-toggle="tooltip" title="Attached to a weapon"></i> @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
@endforeach
<div class="text-right mb-4">
    <a href="{{ url(Auth::user()->url.'/enchantment-logs') }}">View logs...</a>
</div>

@endsection
@section('scripts')
<script>

$( document ).ready(function() {
    $('.inventory-enchantment').on('click', function(e) {
        e.preventDefault();
        var $parent = $(this).parent().parent();
        loadModal("{{ url('enchantments') }}/" + $parent.data('id'), $parent.data('name'));
    });
});

</script>
@endsection