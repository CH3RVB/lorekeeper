@if($showcase)
    {!! Form::open(['url' => __('showcase.showcases').'/delete/'.$showcase->id]) !!}

    <p>You are about to delete the {{__('showcase.showcase')}} <strong>{{ $showcase->name }}</strong>. This is not reversible. If you would like to hide the {{__('showcase.showcase')}} from users, you can set it as inactive from the {{__('showcase.showcase')}} settings page.</p>
    <p>Are you sure you want to delete <strong>{{ $showcase->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete '.__('showcase.showcase'), ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else 
    Invalid {{__('showcase.showcase')}} selected.
@endif