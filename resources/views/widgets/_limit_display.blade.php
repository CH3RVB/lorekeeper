<hr class="my-4 w-75" />
<h4>{{ ucfirst($type) }} Limits</h4>

<p>You must obtain all of the following in order to complete this action.</p>
<table class="table table-sm">
    <thead>
        <tr>
            <th width="70%">Limit</th>
            <th width="30%">Amount</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($object->objectLimits as $limit)
            <tr>
                <td>{!! $limit->limit->displayName !!} ({{ $limit->limit_type }})</td>
                <td>{{ $limit->quantity }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
<hr class="my-4 w-75" />
