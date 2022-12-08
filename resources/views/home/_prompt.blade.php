<div class="card">
    <div class="card-body">
        <h4>Default Prompt Rewards</h4>
        @if(isset($staffView) && $staffView)
            <p>For reference, these are the default rewards for this prompt. The editable section above is <u>inclusive</u> of these rewards.</p>
            @if($count)
                <p>This user has completed this prompt <strong>{{ $count }}</strong> time{{ $count == 1 ? '' : 's' }}.</p>
            @endif
        @else
            <p>These are the default rewards for this prompt. The actual rewards you receive may be edited by a staff member during the approval process.</p>
            @if($count)
                <p>You have completed this prompt <strong>{{ $count }}</strong> time{{ $count == 1 ? '' : 's' }}.</p>
            @endif
        @endif
        <table class="table table-sm mb-0">
            <thead>
                <tr>
                    <th width="70%">Reward</th>
                    <th width="30%">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($prompt->rewards as $reward)
                    <tr>
                        <td>{!! $reward->reward->displayName !!}</td>
                        <td>{{ $reward->quantity }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <hr>
        <h4>Default Skill Rewards</h4>
        <table class="table table-sm mb-0">
            <thead>
                <tr>
                    <th width="70%">Skill</th>
                    <th width="30%">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($prompt->skills as $skill)
                    <tr>
                        <td>{!! $skill->skill->displayName !!}</td>
                        <td>{{ $skill->quantity }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <hr>
        <h4>Default Stat & Level Rewards</h4>
        <hr>
        <div class="row">
            <div class="col-md">
                <h5>User Rewards</h5>
                @if(!$prompt->expreward->user_exp && !$prompt->expreward->user_points)
                No user rewards.
                @else
                {{ $prompt->expreward->user_exp ? $prompt->expreward->user_exp : 0  }} user EXP
                    <br>
                {{ $prompt->expreward->user_points ? $prompt->expreward->user_points : 0  }} user points
                @endif
            </div>
            <div class="col-md">
                <h5>Character Rewards</h5>
                @if(!$prompt->expreward->chara_exp && !$prompt->expreward->chara_points)
                No character rewards.
                @else
                {{ $prompt->expreward->chara_exp ? $prompt->expreward->chara_exp : 0  }} character EXP
                    <br>
                {{ $prompt->expreward->chara_points ? $prompt->expreward->chara_points : 0  }} character points
                @endif
            </div>
        </div>
    </div>
</div>