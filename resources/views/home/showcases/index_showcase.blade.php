@extends('home.showcases.layout')

@section('home.showcases-title')
    {{ ucfirst(__('showcase.showcase')) }} Index
@endsection

@section('home.showcases-content')
    {!! breadcrumbs([
        ucfirst(__('showcase.showcases')) => __('showcase.showcases') . '/' . __('showcase.showcase') . '-index',
    ]) !!}

    <h1>
        All {{ ucfirst(__('showcase.showcases')) }}
    </h1>
    <p>These are user-owned {{ __('showcase.showcases') }} that show off user's item collections.</p>

    @if (Auth::user()->isStaff)
        <div class="alert alert-info text-center">
            You can see hidden {{ __('showcase.showcases') }} and {{ __('showcase.showcases') }} from banned users because
            you are staff.
        </div>
    @endif

    <div>
        {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
        <div class="form-group mr-3 mb-3">
            {!! Form::text('name', Request::get('name'), ['class' => 'form-control']) !!}
        </div>
        <div class="form-group mr-3 mb-3">
            {!! Form::select(
                'sort',
                [
                    'alpha' => 'Sort Alphabetically (A-Z)',
                    'alpha-reverse' => 'Sort Alphabetically (Z-A)',
                    'newest' => 'Newest First',
                    'oldest' => 'Oldest First',
                ],
                Request::get('sort') ?: 'category',
                ['class' => 'form-control'],
            ) !!}
        </div>
        <div class="form-group mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    </div>
    {!! $showcases->render() !!}
    <div class="row shops-row">
        @foreach ($showcases as $showcase)
            <div class="col-md-3 col-6 mb-3 text-center">
                @if ($showcase->has_image)
                    <div class="shop-image container">
                        <a href="{{ $showcase->url }}">
                            <img src="{{ $showcase->showcaseImageUrl }}"
                                style="max-width: 200px !important; max-height: 200px !important;"
                                alt="{{ $showcase->name }}" />
                        </a>
                    </div>
                @endif
                <div class="showcase-name mt-1">
                    <a href="{{ $showcase->url }}" class="h5 mb-0">{{ $showcase->name }}</a>
                    <br>
                    Owned by <a href="{{ $showcase->user->url }}">{!! $showcase->user->displayName !!}</a>
                </div>
                <div class="shop-name mt-1">
                    <strong>Stock</strong>: {{ $showcase->visibleStock->count() }}
                </div>
            </div>
        @endforeach
    </div>
    {!! $showcases->render() !!}

    <div class="text-center mt-4 small text-muted">{{ $showcases->total() }}
        result{{ $showcases->total() == 1 ? '' : 's' }}
        found.</div>
@endsection
