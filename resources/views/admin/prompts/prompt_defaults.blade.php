@extends('admin.layout')

@section('admin-title')
   Default Prompt Rewards
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Default Prompt Rewards' => 'admin/data/prompt-defaults']) !!}

    <div class="text-right mb-3">
        <a class="btn btn-primary" href="{{ url('admin/data/prompt-defaults/create') }}"><i class="fas fa-plus"></i> Create New Default</a>
    </div>

    <h2>Default Prompt Rewards</h2>
    <p>
        These are default prompt reward groups that you can auto-populate into prompts. You can have as many default groups as you want, and they can even contain the same rewards as another group-- just with different preset values. 
    </p>

    <div>
        @foreach ($defaults as $default)
            <div class="card p-3 mb-2 pl-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="flex-grow-1">
                        <h4 class="pb-0 mb-0">

                            {{ $default->name }}
                        </h4>
                        <span class="text-secondary">{{ $default->summary }}</span>
                    </div>
                    <div>
                        <a href="{{ url('admin/data/prompt-defaults/edit/' . $default->id) }}" class="btn btn-info text-white mr-2"><i class="fas fa-pencil-alt"></i></a>

                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection