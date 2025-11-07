<div class="col-md-3 mb-1">
    <div class="card h-100">
        @if ($milestone->has_image)
            <div class="card-header text-center">
                <img src="{{ $milestone->imageUrl }}" class="img-fluid">
            </div>
        @endif
        <div class="card-body text-center">
            <h5>
                {!! $milestone->displayName !!}
                <x-admin-edit title="Milestone" :object="$milestone" />
            </h5>
            @if ($milestone->category)
                ({!! $milestone->category->displayName !!})
            @endif
            <br>
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
