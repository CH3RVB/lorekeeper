@extends('admin.layout')

@section('admin-title') Enchantment Categories @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Enchantment Categories' => 'admin/enchantment/enchantment-categories']) !!}

<h1>Enchantment Categories</h1>

<p>This is a list of enchantment categories that will be used to sort enchantments in the inventory. Creating enchantment categories is entirely optional, but recommended if you have a lot of enchantments in the game.</p> 
<p>The sorting order reflects the order in which the enchantment categories will be displayed in the inventory, as well as on the world pages.</p>

<div class="text-right mb-3"><a class="btn btn-primary" href="{{ url('admin/enchantment/enchantment-categories/create') }}"><i class="fas fa-plus"></i> Create New Enchantment Category</a></div>
@if(!count($categories))
    <p>No enchantment categories found.</p>
@else 
    <table class="table table-sm category-table">
        <tbody id="sortable" class="sortable">
            @foreach($categories as $category)
                <tr class="sort-enchantment" enchantment-id="{{ $category->id }}">
                    <td>
                        <a class="fas fa-arrows-alt-v handle mr-3" href="#"></a>
                        {!! $category->displayName !!}
                    </td>
                    <td class="text-right">
                        <a href="{{ url('admin/enchantment/enchantment-categories/edit/'.$category->id) }}" class="btn btn-primary">Edit</a>
                    </td>
                </tr>
            @endforeach
        </tbody>

    </table>
    <div class="mb-4">
        {!! Form::open(['url' => 'admin/enchantment/enchantment-categories/sort']) !!}
        {!! Form::hidden('sort', '', ['id' => 'sortableOrder']) !!}
        {!! Form::submit('Save Order', ['class' => 'btn btn-primary']) !!}
        {!! Form::close() !!}
    </div>
@endif

@endsection

@section('scripts')
@parent
<script>

$( document ).ready(function() {
    $('.handle').on('click', function(e) {
        e.preventDefault();
    });
    $( "#sortable" ).sortable({
        enchantments: '.sort-enchantment',
        handle: ".handle",
        placeholder: "sortable-placeholder",
        stop: function( event, ui ) {
            $('#sortableOrder').val($(this).sortable("toArray", {attribute:"enchantment-id"}));
        },
        create: function() {
            $('#sortableOrder').val($(this).sortable("toArray", {attribute:"enchantment-id"}));
        }
    });
    $( "#sortable" ).disableSelection();
});
</script>
@endsection