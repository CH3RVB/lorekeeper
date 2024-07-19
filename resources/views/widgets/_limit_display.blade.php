<hr class="my-4 w-75" />
<h4>{{ ucfirst($type) }} Limits
    @if ($object->limitSettings)
        @if ($object->limitSettings->use_characters)
            <i class="fas fa-paw" data-toggle="tooltip" title="Checks the character's items"></i>
        @else
            <i class="fas fa-user" data-toggle="tooltip" title="Checks the user's items"></i>
        @endif
        @if ($object->limitSettings->debit_limits)
            <i class="fas fa-times-circle text-danger" data-toggle="tooltip" title="Requirements will be removed from your inventory if possible."></i>
        @endif
    @endif
</h4>

<p>You must obtain all of the following in order to complete this action.</p>
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
                    <td>{!! $limit->limit->displayName !!}</td>
                    <td>{{ $limit->quantity }}</td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>
<hr class="my-4 w-75" />
