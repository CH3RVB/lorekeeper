@if(!$stack)
    <div class="text-center">Invalid enchantment selected.</div>
@else
    <div class="text-center">
        <div class="mb-1"><a href="{{ $stack->enchantment->url }}"><img src="{{ $stack->enchantment->imageUrl }}" /></a></div>
        <div class="mb-1"><a href="{{ $stack->enchantment->url }}">{{ $stack->enchantment->name }}</a></div>
    </div>

    
@if(isset($stack->data['notes']) || isset($stack->data['data']))
        <div class="card mt-3">
            <ul class="list-group list-group-flush">
                @if(isset($stack->data['notes']))
                    <li class="list-group-item">
                        <h5 class="card-title">Notes</h5>
                        <div>{!! $stack->data['notes'] !!}</div>
                    </li>
                @endif
                @if(isset($stack->data['data']))
                    <li class="list-group-item">
                        <h5 class="card-title">Source</h5>
                        <div>{!! $stack->data['data'] !!}</div>
                    </li>
                @endif
            </ul>
        </div>
    @endif

    @if($stack->enchantment->stats->count())
    <div class="card mb-3 inventory-category">
        <h5 class="card-header inventory-header">
            Stat Bonuses
            <a class="small inventory-collapse-toggle collapse-toggle collapsed" href="#showenchantment" data-toggle="collapse">View</a></h3>
        </h5>
        <div class="card-body inventory-body collapse" id="showenchantment">
        <i>Apply this enchantment to a gear or weapon to gain its benefits</i>
                <div class="row mb-3">
                        <ul>
                            @foreach($stack->enchantment->stats as $stat)
                            <div class="ml-3 mr-3">
                                <li>{{$stat->stat->name}} + {{ $stat->count }}</li>
                            </div>
                            @endforeach
                        </ul>   
                </div>
        </div>
    </div>
    @endif

    @if($user && !$readOnly && ($stack->user_id == $user->id || $user->hasPower('edit_inventories')))
        <div class="card mt-3">
            <ul class="list-group list-group-flush">
            
            <li class="list-group-item">
                    @if($stack->gear_stack_id)  
                    <a class="card-title h5 collapse-title"  data-toggle="collapse" href="#attachForm">@if($stack->user_id != $user->id) [ADMIN] @endif Detach Enchantment from gear</a>
                        @if(Settings::get('enchantments_freely_detach'))
                            {!! Form::open(['url' => 'enchantments/detach/'.$stack->id, 'id' => 'attachForm', 'class' => 'collapse']) !!}
                                <p>This enchantment is currently attached to {!! $stack->gear->gear->displayName !!}, do you want to detach it?</p>
                                <div class="text-right">
                                    {!! Form::submit('Detach', ['class' => 'btn btn-primary']) !!}
                                </div>
                            {!! Form::close() !!}
                        @else
                            {!! Form::open(['url' => 'enchantments/unenchant/'.$stack->id, 'id' => 'attachForm', 'class' => 'collapse']) !!}
                            <p>This enchantment is currently attached to {!! $stack->gear->gear->displayName !!}, do you want to detach it?</p>
                            <p>This will require an unenchantment item.</p>
                                @if($user && $unenchants->count() || Auth::user()->isStaff)
                                    <div class="form-group">
                                        {!! Form::select('stack_id', $unenchants, null, ['class'=>'form-control']) !!}
                                    </div>
                                    <div class="text-right">
                                        {!! Form::submit('Detach Enchantment', ['class' => 'btn btn-primary']) !!}
                                    </div>
                                @else
                                    <p class="alert alert-info my-2">You don't have any valid items.</p>
                                @endif
                            {!! Form::close() !!}
                        @endif
                    @elseif(!$stack->gear_stack_id && !$stack->weapon_stack_id)
                    <a class="card-title h5 collapse-title"  data-toggle="collapse" href="#attachForm">@if($stack->user_id != $user->id) [ADMIN] @endif Attach Enchantment to gear</a>
                    {!! Form::open(['url' => 'enchantments/attach/'.$stack->id, 'id' => 'attachForm', 'class' => 'collapse']) !!}
                        <p>Attach this enchantment to a gear you own! They'll appear on the gear's page and any stat bonuses will automatically be applied.</p>
                        <p>Enchantments can be detached.</p>
                        <div class="text-center"><i>The first number is how many enchantments that gear has equipped. <br>The second number is how many total slots it has.</i></div>
                        <div class="form-group">
                        {!! Form::hidden('type', 'gear') !!}
                        {!! Form::label('id', 'Gear') !!} {!! add_help('Select your gear.') !!}
                            {!! Form::select('id', $gearOptions, null, ['class'=>'form-control']) !!}
                        </div>
                        <div class="text-right">
                            {!! Form::submit('Attach', ['class' => 'btn btn-primary']) !!}
                        </div>
                    {!! Form::close() !!}
                    @elseif(!$stack->gear_stack_id && $stack->weapon_stack_id)
                    <h5 class="card-title mb-0 text-muted"><i class="fas fa-lock mr-2"></i>Detach this enchantment to attach it to a gear instead.</h5>
                    @else
                    <a class="card-title h5">You cannot currently attach / detach this enchantment! It is under cooldown.</a>
                    @endif
                </li>
                <li class="list-group-item">
                @if($stack->weapon_stack_id)
                    <a class="card-title h5 collapse-title"  data-toggle="collapse" href="#attachForm2">@if($stack->user_id != $user->id) [ADMIN] @endif Detach Enchantment from weapon</a>
                        @if(Settings::get('enchantments_freely_detach'))
                            {!! Form::open(['url' => 'enchantments/detach/'.$stack->id, 'id' => 'attachForm2', 'class' => 'collapse']) !!}
                                <p>This enchantment is currently attached to {!! $stack->weapon->weapon->displayName !!}, do you want to detach it?</p>
                                <div class="text-right">
                                    {!! Form::submit('Detach', ['class' => 'btn btn-primary']) !!}
                                </div>
                            {!! Form::close() !!}
                        @else
                            {!! Form::open(['url' => 'enchantments/unenchant/'.$stack->id, 'id' => 'attachForm2', 'class' => 'collapse']) !!}
                            <p>This enchantment is currently attached to {!! $stack->weapon->weapon->displayName !!}, do you want to detach it?</p>
                            <p>This will require an unenchantment item.</p>
                                @if($user && $unenchants->count() || Auth::user()->isStaff)
                                    <div class="form-group">
                                        {!! Form::select('stack_id', $unenchants, null, ['class'=>'form-control']) !!}
                                    </div>
                                    <div class="text-right">
                                        {!! Form::submit('Detach Enchantment', ['class' => 'btn btn-primary']) !!}
                                    </div>
                                @else
                                    <p class="alert alert-info my-2">You don't have any valid items.</p>
                                @endif
                            {!! Form::close() !!}
                        @endif
                    @elseif(!$stack->weapon_stack_id && !$stack->gear_stack_id)
                    <a class="card-title h5 collapse-title"  data-toggle="collapse" href="#attachForm2">@if($stack->user_id != $user->id) [ADMIN] @endif Attach Enchantment to weapon</a>
                    {!! Form::open(['url' => 'enchantments/attach/'.$stack->id, 'id' => 'attachForm2', 'class' => 'collapse']) !!}
                        <p>Attach this enchantment to a weapon you own! They'll appear on the weapon's page and any stat bonuses will automatically be applied.</p>
                        <p>Enchantments can be detached.</p>
                        <div class="text-center"><i>The first number is how many enchantments that weapon has equipped. <br>The second number is how many total slots it has.</i></div>
                        <div class="form-group">
                            {!! Form::hidden('type', 'weapon') !!}
                            {!! Form::label('id', 'Weapon') !!} {!! add_help('Select your weapon.') !!}
                            {!! Form::select('id', $weaponOptions, null, ['class'=>'form-control']) !!}
                        </div>
                        <div class="text-right">
                            {!! Form::submit('Attach', ['class' => 'btn btn-primary']) !!}
                        </div>
                    {!! Form::close() !!}
                    @elseif(!$stack->weapon_stack_id && $stack->gear_stack_id)
                    <h5 class="card-title mb-0 text-muted"><i class="fas fa-lock mr-2"></i>Detach this enchantment to attach it to a weapon instead.</h5>
                    @else
                    <a class="card-title h5">You cannot currently attach / detach this enchantment! It is under cooldown.</a>
                    @endif
                </li>
                @if($stack->enchantment->parent_id && $stack->enchantment->cost && $stack->enchantment->currency_id >= 0)
                <li class="list-group-item">
                    <a class="card-title h5 collapse-title"  data-toggle="collapse" href="#upgradeForm">@if($stack->user_id != $user->id) [ADMIN] @endif Upgrade Enchantment</a>
                    {!! Form::open(['url' => 'enchantments/upgrade/'.$stack->id, 'id' => 'upgradeForm', 'class' => 'collapse']) !!}
                        <p class="alert alert-info my-2">This enchantment can be upgraded to {!!$stack->enchantment->parent->displayName !!}!</p>
                        <p>Upgrade costs {{ $stack->enchantment->cost }} 
                        @if($stack->enchantment->currency_id != 0) <img src="{!! $stack->enchantment->currency->iconurl !!}"> {!! $stack->enchantment->currency->displayName !!}. @else stat points. @endif
                        The upgrade cannot be reversed.</p>
                        <div class="text-right">
                            {!! Form::submit('Upgrade', ['class' => 'btn btn-primary']) !!}
                        </div>
                    {!! Form::close() !!}
                </li>
                @endif
                @if($stack->isTransferrable || $user->hasPower('edit_inventories'))
                    @if(!$stack->gear_stack_id && !$stack->weapon_stack_id)
                    <li class="list-group-item">
                        <a class="card-title h5 collapse-title"  data-toggle="collapse" href="#transferForm">@if($stack->user_id != $user->id) [ADMIN] @endif Transfer Enchantment</a>
                        {!! Form::open(['url' => 'enchantments/transfer/'.$stack->id, 'id' => 'transferForm', 'class' => 'collapse']) !!}
                            @if(!$stack->isTransferrable)
                                <p class="alert alert-warning my-2">This enchantment is account-bound, but your rank allows you to transfer it to another user.</p>
                            @endif
                            <div class="form-group">
                                {!! Form::label('user_id', 'Recipient') !!} {!! add_help('You can only transfer enchantments to verified users.') !!}
                                {!! Form::select('user_id', $userOptions, null, ['class'=>'form-control']) !!}
                            </div>
                            <div class="text-right">
                                {!! Form::submit('Transfer', ['class' => 'btn btn-primary']) !!}
                            </div>
                        {!! Form::close() !!}
                    </li>
                    @else
                    <li class="list-group-item bg-light">
                        <h5 class="card-title mb-0 text-muted"><i class="fas fa-lock mr-2"></i> Currently attached to a claymore</h5>
                    </li>
                    @endif
                @else
                    <li class="list-group-item bg-light">
                        <h5 class="card-title mb-0 text-muted"><i class="fas fa-lock mr-2"></i> Account-bound</h5>
                    </li>
                @endif
                <li class="list-group-item">
                    <a class="card-title h5 collapse-title"  data-toggle="collapse" href="#deleteForm">@if($stack->user_id != $user->id) [ADMIN] @endif Delete Enchantment</a>
                    {!! Form::open(['url' => 'enchantments/delete/'.$stack->id, 'id' => 'deleteForm', 'class' => 'collapse']) !!}
                        <p>This action is not reversible. Are you sure you want to delete this enchantment?</p>
                        <div class="text-right">
                            {!! Form::submit('Delete', ['class' => 'btn btn-danger']) !!}
                        </div>
                    {!! Form::close() !!}
                </li>
            </ul>
        </div>
    @endif
@endif