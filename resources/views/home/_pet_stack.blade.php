@if(!$stack)
    <div class="text-center">Invalid pet selected.</div>
@else
    <div class="text-center">
        <div class="mb-1"><a href="{{ $stack->pet->url }}"><img src="{{ $stack->pet->variantimage($stack->variant_id) }}" class="img-fluid" style="width:50%;"/></a></div>
        <div class="mb-1"><a href="{{ $stack->pet->url }}">{{ $stack->pet->name }}</a></div>
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

    @if($user && !$readOnly && ($stack->user_id == $user->id || $user->hasPower('edit_inventories')))
        <div class="card mt-3">
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <a class="card-title h5 collapse-title"  data-toggle="collapse" href="#nameForm">@if($stack->user_id != $user->id) [ADMIN] @endif Name Pet</a>
                    {!! Form::open(['url' => 'pets/name/'.$stack->id, 'id' => 'nameForm', 'class' => 'collapse']) !!}
                        <p>Enter a name to display for the pet!</p>
                        <div class="form-group">
                            {!! Form::label('name', 'Name') !!} {!! add_help('If your name is not appropriate you can be banned.') !!}
                            {!! Form::text('name', null, ['class'=>'form-control']) !!}
                        </div>
                        <div class="text-right">
                            {!! Form::submit('Name', ['class' => 'btn btn-primary']) !!}
                        </div>
                    {!! Form::close() !!}
                </li>
                <li class="list-group-item">
                    @php 
                    $now = Carbon\Carbon::parse($stack->attached_at);
                    $diff = $now->addDays(Settings::get('claymore_cooldown'));
                    @endphp
                    @if($stack->chara_id != NULL && $diff < Carbon\Carbon::now())
                    <a class="card-title h5 collapse-title"  data-toggle="collapse" href="#attachForm">@if($stack->user_id != $user->id) [ADMIN] @endif Detach Pet from Character</a>
                    {!! Form::open(['url' => 'pets/detach/'.$stack->id, 'id' => 'attachForm', 'class' => 'collapse']) !!}
                        <p>This pet is currently attached to {!! $stack->character->displayName !!}, do you want to detach them?</p>
                        <div class="text-right">
                            {!! Form::submit('Detach', ['class' => 'btn btn-primary']) !!}
                        </div>
                    {!! Form::close() !!}
                    @elseif($stack->chara_id == NULL || $diff < Carbon\Carbon::now())
                    <a class="card-title h5 collapse-title"  data-toggle="collapse" href="#attachForm">@if($stack->user_id != $user->id) [ADMIN] @endif Attach Pet to Character</a>
                    {!! Form::open(['url' => 'pets/attach/'.$stack->id, 'id' => 'attachForm', 'class' => 'collapse']) !!}
                        <p>Attach this pet to a character you own! They'll appear on the character's page and any stat bonuses will automatically be applied.</p>
                        <p>Pets can be detached.</p>
                        <div class="form-group">
                            {!! Form::label('id', 'Slug') !!} {!! add_help('Insert your character\'s slug.') !!}
                            {!! Form::select('id', $chara, null, ['class'=>'form-control']) !!}
                        </div>
                        <div class="text-right">
                            {!! Form::submit('Attach', ['class' => 'btn btn-primary']) !!}
                        </div>
                    {!! Form::close() !!}
                    @else
                    <a class="card-title h5">You cannot currently attach / detach this pet! It is under cooldown.</a>
                    @endif
                </li>
                @if($user && $splices && $user->id == $stack->user_id)
                <li class="list-group-item">
                    <a class="card-title h5 collapse-title"  data-toggle="collapse" href="#userVariantForm">Change Pet Variant</a>
                    {!! Form::open(['url' => 'pets/variant/'.$stack->id, 'id' => 'userVariantForm', 'class' => 'collapse']) !!}
                    <p>This will use a splice item!</p>
                        <div class="form-group">
                            {!! Form::select('stack_id', $splices, null, ['class'=>'form-control']) !!}
                        </div>
                        <div class="form-group">
                            @php $variants = ['0' => 'Default'] + $stack->pet->variants()->pluck('variant_name', 'id')->toArray() @endphp
                            {!! Form::select('variant_id', $variants, $stack->variant_id, ['class'=>'form-control']) !!}
                        </div>
                        <div class="text-right">
                            {!! Form::submit('Change Variant', ['class' => 'btn btn-primary']) !!}
                        </div>
                    {!! Form::close() !!}
                </li>
                @endif
                @if($user->hasPower('edit_inventories'))
                    <li class="list-group-item">
                        <a class="card-title h5 collapse-title"  data-toggle="collapse" href="#variantForm">[ADMIN] Change Pet Variant</a>
                        {!! Form::open(['url' => 'pets/variant/'.$stack->id, 'id' => 'variantForm', 'class' => 'collapse']) !!}
                            <div class="form-group">
                                @php $variants = ['0' => 'Default'] + $stack->pet->variants()->pluck('variant_name', 'id')->toArray() @endphp
                                {!! Form::select('variant_id', $variants, $stack->variant_id, ['class'=>'form-control']) !!}
                            </div>
                            <div class="text-right">
                                {!! Form::submit('Change Variant', ['class' => 'btn btn-primary']) !!}
                            </div>
                        {!! Form::close() !!}
                    </li>
                @endif
                @if($stack->isTransferrable || $user->hasPower('edit_inventories'))
                    @if(!$stack->chara_id)
                    <li class="list-group-item">
                        <a class="card-title h5 collapse-title"  data-toggle="collapse" href="#transferForm">@if($stack->user_id != $user->id) [ADMIN] @endif Transfer Pet</a>
                        {!! Form::open(['url' => 'pets/transfer/'.$stack->id, 'id' => 'transferForm', 'class' => 'collapse']) !!}
                            @if(!$stack->isTransferrable)
                                <p class="alert alert-warning my-2">This pet is account-bound, but your rank allows you to transfer it to another user.</p>
                            @endif
                            <div class="form-group">
                                {!! Form::label('user_id', 'Recipient') !!} {!! add_help('You can only transfer pets to verified users.') !!}
                                {!! Form::select('user_id', $userOptions, null, ['class'=>'form-control']) !!}
                            </div>
                            <div class="text-right">
                                {!! Form::submit('Transfer', ['class' => 'btn btn-primary']) !!}
                            </div>
                        {!! Form::close() !!}
                    </li>
                    @else
                    <li class="list-group-item bg-light">
                        <h5 class="card-title mb-0 text-muted"><i class="fas fa-lock mr-2"></i> Currently attached to a character</h5>
                    </li>
                    @endif
                @else
                    <li class="list-group-item bg-light">
                        <h5 class="card-title mb-0 text-muted"><i class="fas fa-lock mr-2"></i> Account-bound</h5>
                    </li>
                @endif
                <li class="list-group-item">
                    <a class="card-title h5 collapse-title"  data-toggle="collapse" href="#deleteForm">@if($stack->user_id != $user->id) [ADMIN] @endif Delete Pet</a>
                    {!! Form::open(['url' => 'pets/delete/'.$stack->id, 'id' => 'deleteForm', 'class' => 'collapse']) !!}
                        <p>This action is not reversible. Are you sure you want to delete this pet?</p>
                        <div class="text-right">
                            {!! Form::submit('Delete', ['class' => 'btn btn-danger']) !!}
                        </div>
                    {!! Form::close() !!}
                </li>
            </ul>
        </div>
    @endif
@endif