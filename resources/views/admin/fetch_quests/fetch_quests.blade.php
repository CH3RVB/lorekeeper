@extends('admin.layout')

@section('admin-title')
    Fetch Quests
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Fetch Quests' => 'admin/data/fetch-quests']) !!}

    <h1>Fetch Quests</h1>

    <div class="text-right mb-3">
        <a class="btn btn-primary" href="{{ url('admin/data/fetch-quests/create') }}"><i class="fas fa-plus"></i> Create New
            Fetch Quest</a>
    </div>

    <div>
        {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
        <div class="form-group mr-3 mb-3">
            {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
        </div>
        <div class="form-group mb-3">{!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}</div>
        {!! Form::close() !!}
    </div>

    @if (!count($fetchquests))
        <p>No fetch quests found.</p>
    @else
        {!! $fetchquests->render() !!}

        <div class="row ml-md-2">
            <div class="d-flex row flex-wrap col-12 pb-1 px-0 ubt-bottom">
                <div class="col-4 col-md-3 font-weight-bold">Name</div>
            </div>
            @foreach ($fetchquests as $fetchquest)
                <div class="d-flex row flex-wrap col-12 mt-1 pt-2 px-0 ubt-top">
                    <div class="col-5 col-md-3 text-truncate">
                        {!! $fetchquest->has_image
                            ? '<img src="' . $fetchquest->imageUrl . '" class="img-fluid mr-2" style="height: 2em;" />'
                            : '' !!}{{ $fetchquest->name }}
                    </div>
                    <div class="col-3 col-md-1 text-right">
                        <a href="{{ url('admin/data/fetch-quests/edit/' . $fetchquest->id) }}"
                            class="btn btn-primary py-0 px-2">Edit</a>
                    </div>
                </div>
            @endforeach
        </div>

        {!! $fetchquests->render() !!}

        <div class="text-center mt-4 small text-muted">{{ $fetchquests->total() }}
            result{{ $fetchquests->total() == 1 ? '' : 's' }} found.</div>
    @endif

@endsection
@section('scripts')
    @parent
@endsection