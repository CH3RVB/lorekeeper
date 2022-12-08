@extends('admin.layout')

@section('admin-title') Skill Categories @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Skill Categories' => 'admin/data/skill-categories']) !!}

<h1>Skill Categories</h1>

<p></p>

<div class="text-right mb-3">
    <a class="btn btn-primary" href="{{ url('admin/data/skills') }}"><i class="fas fa-undo"></i> Return to Skills</a>
    <a class="btn btn-primary" href="{{ url('admin/data/skill-categories/create') }}"><i class="fas fa-plus"></i> Create New Skill Category</a>
</div>
@if(!count($categories))
    <p>No skill categories found.</p>
@else 
    <table class="table table-sm category-table">
        <tbody class="sortable">
            @foreach($categories as $category)
                <tr class="sort-skill" data-id="{{ $category->id }}">
                    <td>
                        {!! $category->displayName !!}
                    </td>
                    <td class="text-right">
                        <a href="{{ url('admin/data/skill-categories/edit/'.$category->id) }}" class="btn btn-primary">Edit</a>
                    </td>
                </tr>
            @endforeach
        </tbody>

    </table>
@endif

@endsection

@section('scripts')
@parent
<script>

$( document ).ready(function() {

});
</script>
@endsection