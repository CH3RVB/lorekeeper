@extends('admin.layout')

@section('admin-title') Items @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Items' => 'admin/data/items']) !!}

<h1>Items</h1>

<p>This is a list of items in the game. Specific details about items can be added when they are granted to users (e.g. reason for grant). By default, items are merely collectibles and any additional functionality must be manually processed, or custom coded in for the specific item.</p>

<div class="text-right mb-3">
    @if(Auth::user()->hasPower('edit_inventories'))
        <a class="btn btn-primary" href="{{ url('admin/grants/item-search') }}"><i class="fas fa-search"></i> Item Search</a>
    @endif
    <a class="btn btn-primary" href="{{ url('admin/data/item-categories') }}"><i class="fas fa-folder"></i> Item Categories</a>
    <a class="btn btn-primary" href="{{ url('admin/data/item-subcategories') }}"><i class="fas fa-folder"></i> Item Subcategories</a>
    <a class="btn btn-primary" href="{{ url('admin/data/items/create') }}"><i class="fas fa-plus"></i> Create New Item</a>
</div>

<div>
    {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
        <div class="form-group mr-3 mb-3">
            {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
        </div>
        <div class="form-group mr-3 mb-3">
            {!! Form::select('item_category_id', $categories, Request::get('name'), ['class' => 'form-control']) !!}
        </div>
        <div class="form-group mr-3 mb-3">
            {!! Form::select('item_subcategory_id', $subcategories, Request::get('name'), ['class' => 'form-control']) !!}
        </div>
        <div class="form-group mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
    {!! Form::close() !!}
</div>

@if(!count($items))
    <p>No items found.</p>
@else
{!! $items->render() !!}
    <table class="table table-sm category-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Category</th>
                <th>Subcategory</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
          @foreach($items as $item)
          <tr class="sort-item" data-id="{{ $item->id }}">
                    <td>
                        {{ $item->name }}
                    </td>
                    <td>{{ $item->category ? $item->category->name : '' }}</td>
                    <td>{{ $item->subcategory ? $item->subcategory->name : '' }}</td>
                    <td class="text-right">
                        <a href="{{ url('admin/data/items/edit/'.$item->id) }}" class="btn btn-primary">Edit</a>
                    </td>
                </tr>
          @endforeach
        </tbody>
    </table>
    {!! $items->render() !!}
@endif

@endsection

@section('scripts')
@parent
@endsection
