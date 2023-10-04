@extends('admin.layout')

@section('admin-title')
    Sort Shop Stock
@endsection

@section('admin-content')
    {!! breadcrumbs([
        'Admin Panel' => 'admin',
        'Shops' => 'admin/data/shops',
        $shop->name => 'admin/data/shops/edit/' . $shop->id,
        'Sort Stock' => 'admin/data/shops/stock/sort',
    ]) !!}

    <h1>
        Sort {{ $shop->name }}'s Stock</h1>

    <p>This is the order in which the stock will appear in the shop.</p>

    @if (!count($stock))
        <p>No stock found.</p>
    @else
        <table class="table table-sm stock-table">
            <tbody id="stockSortable" class="sortable">
                @foreach ($stock as $stock)
                    <tr class="sort-item" data-id="{{ $stock->id }}">
                        <td>
                            <a class="fas fa-arrows-alt-v handle mr-3" href="#"></a>
                            @if ($stock->item->has_image)
                                <img src="{{ $stock->item->imageUrl }}" class="img-fluid mr-2" style="height: 2em;" />
                            @endif
                            <a href="{{ $stock->item->idUrl }}"><strong>{{ $stock->item->name }}</strong></a>
                        </td>
                        <td>
                            <strong>Cost: </strong> {!! $stock->currency->display($stock->cost) !!}
                        </td>
                    </tr>
                @endforeach
            </tbody>

        </table>
        <div>
            {!! Form::open(['url' => 'admin/data/shops/stock/sort/' . $shop->id]) !!}
            {!! Form::hidden('sort', '', ['id' => 'stockSortableOrder']) !!}
            {!! Form::submit('Save Order', ['class' => 'btn btn-primary']) !!}
            {!! Form::close() !!}
        </div>
    @endif

@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.handle').on('click', function(e) {
                e.preventDefault();
            });
            $("#stockSortable").sortable({
                items: '.sort-item',
                handle: ".handle",
                placeholder: "sortable-placeholder",
                stop: function(event, ui) {
                    $('#stockSortableOrder').val($(this).sortable("toArray", {
                        attribute: "data-id"
                    }));
                },
                create: function() {
                    $('#stockSortableOrder').val($(this).sortable("toArray", {
                        attribute: "data-id"
                    }));
                }
            });
            $("#stockSortable").disableSelection();
        });
    </script>
@endsection
