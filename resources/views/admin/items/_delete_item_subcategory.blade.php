@if($subcategory)
    {!! Form::open(['url' => 'admin/data/item-subcategories/delete/'.$subcategory->id]) !!}

    <p>You are about to delete the subcategory <strong>{{ $subcategory->name }}</strong>. This is not reversible. If items in this subcategory exist, you will not be able to delete this subcategory.</p>
    <p>Are you sure you want to delete <strong>{{ $subcategory->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete Subcategory', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else 
    Invalid subcategory selected.
@endif