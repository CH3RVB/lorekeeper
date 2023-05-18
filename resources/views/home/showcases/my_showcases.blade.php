@extends('home.showcases.layout')

@section('home.showcases-title') My {{ucfirst(__('showcase.showcases'))}} @endsection

@section('home.showcases-content')
{!! breadcrumbs(['My '.ucfirst(__('showcase.showcases')) => __('showcase.showcases')]) !!}

<h1>My {{ucfirst(__('showcase.showcases'))}}</h1>

<p>Here is a list of your {{__('showcase.showcases')}}. </p> 
<p>The sorting order reflects the order in which the {{__('showcase.showcases')}} will be listed on the {{__('showcase.showcase')}} index.</p>
@if(Settings::get('user_showcase_limit') > 0)
<p> You may make a maximum of <b>{{Settings::get('user_showcase_limit')}}</b> {{__('showcase.showcases')}}.</p>
@endif

<div class="text-right mb-3"><a class="btn btn-primary" href="{{ url(__('showcase.showcases').'/create') }}"><i class="fas fa-plus"></i> Create New {{ucfirst(__('showcase.showcase'))}}</a></div>
@if(!count($showcases))
    <p>No {{__('showcase.showcases')}} found.</p>
@else 
    <table class="table table-sm showcase-table">
        <tbody id="sortable" class="sortable">
            @foreach($showcases as $showcase)
                <tr class="sort-item" data-id="{{ $showcase->id }}">
                    <td>
                        <a class="fas fa-arrows-alt-v handle mr-3" href="#"></a>
                        {!! $showcase->displayName !!}
                    </td>
                    <td class="text-right">
                        <a href="{{ url(__('showcase.showcases').'/edit/'.$showcase->id) }}" class="btn btn-primary">Edit</a>
                    </td>
                </tr>
            @endforeach
        </tbody>

    </table>
    <div class="mb-4">
        {!! Form::open(['url' => __('showcase.showcases').'/sort']) !!}
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