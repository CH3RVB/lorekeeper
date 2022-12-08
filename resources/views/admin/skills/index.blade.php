@extends('admin.layout')

@section('admin-title') Skills @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Skills' => 'admin/data/skills']) !!}

<h1>Skills</h1>

<p>Skills are arbitrary limiters for prompts and any other mechanism you can think to add it to. They are visual only outside of their built-in prompt ties.</p>

<div class="text-right mb-3">
    <a class="btn btn-primary" href="{{ url('admin/data/skill-categories') }}"><i class="fas fa-folder"></i> Skill Categories</a>
    <a class="btn btn-primary" href="{{ url('admin/data/skills/create') }}"><i class="fas fa-plus"></i> Create New Skill</a>
</div>
@if(!count($skills))
    <p>No skills found.</p>
@else 
    <table class="table table-sm skill-table">
        <tbody class="sortable">
            @foreach($skills as $skill)
                <tr class="sort-item" data-id="{{ $skill->id }}">
                    <td>
                        {!! $skill->displayName !!}
                    </td>
                    <td class="text-right">
                        <a href="{{ url('admin/data/skills/edit/'.$skill->id) }}" class="btn btn-primary">Edit</a>
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

</script>
@endsection