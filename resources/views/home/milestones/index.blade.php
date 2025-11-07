@extends('home.layout')

@section('home-title')
    Milestones
@endsection

@section('home-content')
    {!! breadcrumbs(['Milestones' => 'milestones']) !!}


    <h1>
        Milestones
    </h1>
    <p> This is a list of all milestones that you have not completed. </p>

    <div class="text-right mb-3">
        <a class="btn btn-primary" href="{{ url('world/milestones') }}">View All</a>
    </div>

    <hr>

    <!-- todo find a way to hide this. since it's getting the type key it still shows blank cards even if all are completed. -->
    @if (count($incompleted))
        <div class="card character-bio">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs">
                    @foreach ($incompleted as $type => $milestones)
                        @if (count($milestones))
                            <li class="nav-item">
                                <a class="nav-link {{ $loop->first ? 'active' : '' }}" id="typetab{{ $type }}-inc" data-toggle="tab" href="#{{ $type }}tab-inc" role="tab">
                                    {{ $type }}s
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>
            <div class="card-body tab-content">
                @foreach ($incompleted as $type => $milestones)
                    @if (count($milestones))
                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="{{ $type }}tab-inc">
                            <p>Your unique {{ strtolower($type) }} count:
                                <strong>
                                    @if (!isset(config('lorekeeper.milestones.' . $type)['override_key']))
                                        {{ count($iC[$type]->get()->unique(strtolower($type . '_id'))) }}
                                    @else
                                        @if ($type == 'Currency')
                                            {{ $iC[$type]->get()->pluck('quantity')->sum() }}
                                        @else
                                            {{ count($iC[$type]->get()) }}
                                        @endif
                                    @endif

                                </strong>
                                <span class="text-muted">{{ config('lorekeeper.milestones.' . $type)['desc'] }}</span>
                            </p>
                            <div class="card character-bio {{ $loop->last ? '' : 'mb-3' }}">
                                <div class="card-header">
                                    <ul class="nav nav-tabs card-header-tabs">
                                        @foreach ($milestones as $categoryId => $categoryItems)
                                            <li class="nav-item">
                                                <a class="nav-link {{ $loop->first ? 'active' : '' }}" id="inc-categoryTab-{{ isset($categoryItems->first()->category) ? $categoryItems->first()->category->id : 'misc' }}" data-toggle="tab"
                                                    href="#inc-{{ $type }}category-{{ isset($categoryItems->first()->category) ? $categoryItems->first()->category->id : 'misc' }}" role="tab">
                                                    {!! isset($categoryItems->first()->category) ? $categoryItems->first()->category->name : 'Miscellaneous' !!}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                <div class="card-body tab-content">
                                    @foreach ($milestones as $categoryId => $categoryItems)
                                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="inc-{{ $type }}category-{{ isset($categoryItems->first()->category) ? $categoryItems->first()->category->id : 'misc' }}">
                                            @if (isset($categoryItems->first()->category))
                                                <p>Your unique {{ strtolower($type) }} count for {!! $categoryItems->first()->category->displayName !!}:
                                                    <strong>
                                                        @if (!config('lorekeeper.milestones.' . $type)['override_key'])
                                                            {{ count($iC[$type]->whereRelation(strtolower($type), strtolower($type) . '_category_id', $categoryItems->first()->category->id)->get()->unique(strtolower($type . '_id'))) }}
                                                        @else
                                                            @if ($type == 'Currency')
                                                                {{ $iC[$type]->get()->pluck('quantity')->sum() }}
                                                            @else
                                                                {{ count($iC[$type]->get()) }}
                                                            @endif
                                                        @endif
                                                    </strong>
                                                    <span class="text-muted">{{ config('lorekeeper.milestones.' . $type)['desc'] }}</span>
                                                </p>
                                            @endif
                                            @foreach ($categoryItems->chunk(4) as $chunk)
                                                <div class="row mb-3">
                                                    @foreach ($chunk as $milestone)
                                                        @include('home.milestones.milestone_card')
                                                    @endforeach
                                                </div>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @else
        You've completed all current milestones!
    @endif
    <hr>

@endsection
