@if(isset($totals))
    @if(Auth::user()->hasPower('manage_submissions') && isset($totals))
        <p class="text-center">
            <h4>Calculated Totals:</h4>
            @forEach($totals as $total)
                <div class="d-flex">
                    <h5 class="mr-2">{{ $total['name'] }}: </h5>
                    <span>
                        {!! $total['currency']->display($total['value']) !!}
                        @if($collaboratorsCount && Settings::get('gallery_rewards_divided') === "1")
                            <br/>Divided Among {{ $collaboratorsCount }} Collaborator(s):</strong> {!! $total['currency']->display(round($total['value'] / $collaboratorsCount)) !!}
                        @endif
                    </span>
                </div>
            @endforEach
        </p>
    @endif
@else
    <p>This submission does not have form data associated with it.</p>
@endif