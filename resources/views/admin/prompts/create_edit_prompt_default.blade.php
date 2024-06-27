@extends('admin.layout')

@section('admin-title')
    Default Prompt Rewards
@endsection

@section('admin-content')
    {!! breadcrumbs([
        'Admin Panel' => 'admin',
        'Default Prompt Rewards' => 'admin/data/prompt-defaults',
        ($default->id ? 'Edit' : 'Create') . ' Default' => $default->id ? 'admin/data/prompt-defaults/edit/' . $default->id : 'admin/data/prompt-defaults/create',
    ]) !!}

    <h1>{{ $default->id ? 'Edit' : 'Create' }} Default Prompt Reward
        @if ($default->id)
            <a href="#" class="btn btn-danger float-right delete-default-button">Delete Default Prompt Reward</a>
        @endif
    </h1>

    {!! Form::open(['url' => $default->id ? 'admin/data/prompt-defaults/edit/' . $default->id : 'admin/data/prompt-defaults/create']) !!}

    <h3>Basic Information</h3>
    <div class="form-group">
        {!! Form::label('Name') !!}
        {!! Form::text('name', $default->name, ['class' => 'form-control']) !!}
    </div>


    <div class="form-group">
        {!! Form::label('Summary (Optional)') !!}
        {!! Form::text('summary', $default->summary, ['class' => 'form-control']) !!}
    </div>

    <h3>Rewards</h3>
    <p>Rewards are credited on a per-user basis. Mods are able to modify the specific rewards granted at approval time.</p>
    <p>You can add loot tables containing any kind of currencies (both user- and character-attached), but be sure to keep track of which are being distributed! Character-only currencies cannot be given to users.</p>
    @include('widgets._loot_select', ['loots' => $default->rewards, 'showLootTables' => true, 'showRaffles' => true])

    <div class="text-right">
        {!! Form::submit($default->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    @include('widgets._loot_select_row', ['showLootTables' => true, 'showRaffles' => true])
@endsection

@section('scripts')
    @parent
    @include('js._loot_js', ['showLootTables' => true, 'showRaffles' => true])
    <script>
        $(document).ready(function() {
            $('.delete-default-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/prompt-defaults/delete') }}/{{ $default->id }}", 'Delete Default');
            });
        });
    </script>
@endsection
