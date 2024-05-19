@if (isset($testMyos))
    <div class="row no-gutters">
        @foreach ($testMyos as $myo)
            <div class="col-lg-4 p-2">
                <div class="card character-bio w-100 p-3">
                    <div class="row">
                        <div class="col-lg-4 col-md-6 col-4">
                            <h5>Species</h5>
                        </div>
                        <div class="col-lg-8 col-md-6 col-8">{!! $myo['species'] ? $myo['species']->displayName : 'None' !!}</div>
                    </div>
                    <div class="row">
                        <div class="col-lg-4 col-md-6 col-4">
                            <h5>Subtype</h5>
                        </div>
                        <div class="col-lg-8 col-md-6 col-8">{!! $myo['subtype'] ? $myo['subtype']->displayName : 'None' !!}</div>
                    </div>
                    <div class="row">
                        <div class="col-lg-4 col-md-6 col-4">
                            <h5>Rarity</h5>
                        </div>
                        <div class="col-lg-8 col-md-6 col-8">{!! $myo['rarity'] ? $myo['rarity']->displayName : 'None' !!}</div>
                    </div>
                    <div class="mb-3">
                        <div>
                            <h5>Traits</h5>
                        </div>

                        <div>
                            @if (count($myo['features']) > 0)
                                @foreach ($myo['features'] as $feature)
                                    <div>
                                        {!! App\Models\Feature\Feature::find($feature) ? App\Models\Feature\Feature::find($feature)->displayName : 'Deleted Trait' !!}
                                    </div>
                                @endforeach
                            @else
                                <div>No traits listed.</div>
                            @endif
                        </div>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <div>
                            <h5>Design Specifications</h5>
                        </div>

                        <div>
                            placeholder
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <p>No MYOs rolled.</p>
@endif
