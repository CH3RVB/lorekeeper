<div class="row world-entry">
    @if ($milestone->imageUrl)
        <div class="col-md-3 world-entry-image"><a href="{{ $milestone->imageUrl }}" data-lightbox="entry" data-title="{{ $milestone->displayname }}"><img src="{{ $milestone->imageUrl }}" class="world-entry-image" alt="{{ $milestone->displayname }}" /></a>
        </div>
    @endif
    <div class="{{ $milestone->imageUrl ? 'col-md-9' : 'col-12' }}">
        <h1>
            @if (!$milestone->is_visible)
                <i class="fas fa-eye-slash mr-1"></i>
            @endif
            {!! $milestone->displayname !!}<x-admin-edit title="Milestone" :object="$milestone" />
        </h1>
        <div class="row">
            @if (isset($milestone->category) && $milestone->category)
                <div class="col-md">
                    <p>
                        <strong>Category:</strong>
                        @if (!$milestone->category->is_visible)
                            <i class="fas fa-eye-slash mx-1 text-danger"></i>
                        @endif
                        <a href="{!! $milestone->category->url !!}">
                            {!! $milestone->category->name !!}
                        </a>
                    </p>
                </div>
            @endif
        </div>
        <div class="world-entry-text">
            @if ($milestone->summary)
                <span class="text-muted">{{ $milestone->summary }}</span>
            @endif
            {!! $milestone->description !!}
            @include('widgets._reward_display', [
                'object' => $milestone,
                'type' => 'milestone',
                'reward_key' => 'objectRewards',
                'recipient' => 'User',
            ])
            @if ($milestone->canClaim(Auth::user()))
                {!! Form::open(['url' => 'milestones/claim/' . $milestone->id]) !!}
                <div class="text-right">
                    {!! Form::submit('Claim', ['class' => 'btn btn-primary']) !!}
                </div>

                {!! Form::close() !!}
            @else
                <div class="text-right">
                    <button class="btn btn-primary disabled">N/A</button>
                </div>
            @endif
        </div>
    </div>
</div>
