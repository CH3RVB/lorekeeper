<div class="row world-entry">
    @if($imageUrl)
        <div class="col-md-3 world-entry-image"><a href="{{ $imageUrl }}" data-lightbox="entry" data-title="{{ $name }}"><img src="{{ $imageUrl }}" class="world-entry-image" /></a></div>
    @endif
    <div class="{{ $imageUrl ? 'col-md-9' : 'col-12' }}">
        <h3>{!! $name !!} @if(isset($idUrl) && $idUrl) <a href="{{ $idUrl }}" class="world-entry-search text-muted"><i class="fas fa-search"></i></a>  @endif</h3>
        <div class="row">
            @if(isset($skill->category) && $skill->category)
                <div class="col-md">
                    <p><strong>Category:</strong> {!! $skill->category->name !!}</p>
                </div>
            @endif
            @if(isset($skill->parent_id) && $skill->parent)
                <div class="col-md">
                    <p><strong>Parent:</strong> {!! $skill->parent->displayname !!}</p>
                </div>
            @endif
            @if(isset($skill->prerequisite_id) && $skill->prerequisite)
                <div class="col-md">
                    <p><strong>Prerequisite:</strong> {!! $skill->prerequisite->displayname !!}</p>
                </div>
            @endif
        </div>
        <div class="world-entry-text">
            {!! $description !!}
        </div>
    </div>
</div>
