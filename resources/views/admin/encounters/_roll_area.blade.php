<p>You rolled {{ $quantity }} time{{ $quantity != 1 ? 's' : '' }} for the following:</p>
<table class="table table-sm table-striped">
    <thead>
        <th>#</th>
        <th>Encounter Rolled</th>
    </thead>
    <tbody>
        <?php $count = 1; ?>
        @foreach ($results as $result)
            <tr>
                <td>{{ $count++ }}</td>
                <td>{!! $result && $result->encounter ? $result->encounter->name : 'Nothing (no valid encounter)' !!}</td>
            </tr>
        @endforeach
    </tbody>
</table>
