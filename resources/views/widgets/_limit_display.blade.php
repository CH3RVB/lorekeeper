<hr class="my-4 w-75" />
<h4>{{ ucfirst($type) }} Limits</h4>

<p>You must obtain all of the following in order to complete this action.</p>
@if ($object->limitSettings && $object->limitSettings->debit_limits)
    <p class="text-danger">Requirements will be consumed when you complete this action.</p>
@endif
<table class="table table-sm">
    <thead>
        <tr>
            <th width="70%">Limit</th>
            <th width="30%">Amount</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($object->objectLimits->groupBy('limit_type') as $type => $group)
            <tr>
                <td colspan="2"><strong>{!! strtoupper($type) !!}S</strong></td>
            </tr>
            @foreach ($group as $limit)
                <tr>
                    <td>{!! $limit->limit->displayName !!} ({{ $limit->limit_type }})</td>
                    <td>{{ $limit->quantity }}</td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>
<hr class="my-4 w-75" />
