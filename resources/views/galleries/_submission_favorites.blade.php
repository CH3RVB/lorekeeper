@if ($submission)
    <ul>
        @foreach ($favorites as $favorite)
            <li>{!! $favorite->user->displayName !!}</li>
        @endforeach
    </ul>
@else
    Invalid submission selected.
@endif
