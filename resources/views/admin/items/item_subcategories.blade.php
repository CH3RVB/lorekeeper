@extends('admin.layout')

@section('admin-title') Item Subcategories @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Item Subcategories' => 'admin/data/item-subcategories']) !!}
<div class="text-right mb-3">
<a class="btn btn-primary" href="{{ url('admin/data/items') }}"> Items Home</a>
</div>
<h1>Item Subcategories</h1>

<p>This is a list of item subcategories that will be used to sort items on the site. Creating item subcategories is entirely optional, but recommended if you have a lot of items in the game.</p> 
<p>The sorting order reflects the order in which the item categories will be displayed on the world pages.</p>

<div class="text-right mb-3"><a class="btn btn-primary" href="{{ url('admin/data/item-subcategories/create') }}"><i class="fas fa-plus"></i> Create New Item Subcategory</a></div>
@if(!count($subcategories))
    <p>No item subcategories found.</p>
@else 
    <table class="table table-sm subcategory-table">
        <tbody id="sortable" class="sortable">
            @foreach($subcategories as $subcategory)
                <tr class="sort-item" data-id="{{ $subcategory->id }}">
                    <td>
                        <a class="fas fa-arrows-alt-v handle mr-3" href="#"></a>
                        {!! $subcategory->displayName !!}
                    </td>
                    <td class="text-right">
                        <a href="{{ url('admin/data/item-subcategories/edit/'.$subcategory->id) }}" class="btn btn-primary">Edit</a>
                    </td>
                </tr>
            @endforeach
        </tbody>

    </table>
    <div class="mb-4">
        {!! Form::open(['url' => 'admin/data/item-subcategories/sort']) !!}
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
        items: '.sort-item',
        handle: ".handle",
        placeholder: "sortable-placeholder",
        stop: function( event, ui ) {
            $('#sortableOrder').val($(this).sortable("toArray", {attribute:"data-id"}));
        },
        create: function() {
            $('#sortableOrder').val($(this).sortable("toArray", {attribute:"data-id"}));
        }
    });
    $( "#sortable" ).disableSelection();
});
</script>
@endsection