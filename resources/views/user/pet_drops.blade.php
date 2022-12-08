@extends('user.layout')

@section('profile-title') {{ $user->name }}'s {{ $pet->pet->name }} {{ isset($pet->drops->dropData->data['drop_name']) ? strtolower($pet->drops->dropData->data['drop_name']).'s' : 'drops' }} @endsection

@section('profile-content')


@if(!Auth::check()  || (Auth::check() && Auth::user()->id != $pet->user_id))
    {!! breadcrumbs(['Users' => 'users', $user->name => $user->url, 'Pets' => $user->url . '/pets', $user->name .'\'s '. $pet->pet->name.' '.(isset($pet->drops->dropData->data['drop_name']) ? $pet->drops->dropData->data['drop_name'].'s' : 'drops') => 'pets/pet/'.$pet->id ]) !!}
@else
    {!! breadcrumbs(['Pets' => 'pets', $pet->pet->name.' '.(isset($pet->drops->dropData->data['drop_name']) ? $pet->drops->dropData->data['drop_name'].'s' : 'drops') => 'pets/pet/'.$pet->id ]) !!}
@endif

@if(!$pet->drops->dropData->isActive)
    <div class="alert alert-warning">This pet's drops are currently inactive. Because you are staff, you can see this page anyways.</div>
@endif

<h1>
    {{ $pet->pet_id && $pet->pet->hasDrops ? $pet->drops->group : '' }}
    {{ $pet->pet->name }}
    {{ isset($pet->drops->dropData->data['drop_name']) ? strtolower($pet->drops->dropData->data['drop_name']).'s' : 'drops' }}
</h1>


<div class="row">
    <div class="col-md-6">
        <div class="text-center">
            <div class="mb-1">
                <img src="{{ $pet->pet->VariantImage($pet->variant_id) }}" class="img-fluid"/>
            </div>
        </div>
    </div>
    <div class="col-md-6 text-center">
        <h2>
            Collect {{ isset($pet->drops->dropData->data['drop_name']) ? $pet->drops->dropData->data['drop_name'].'s' : 'Drops' }}
            @if(Auth::check() && Auth::user()->hasPower('edit_inventories'))
                <a href="#" class="float-right btn btn-outline-info btn-sm" id="paramsButton" data-toggle="modal" data-target="#paramsModal"><i class="fas fa-cog"></i> Admin</a>
            @endif
        </h2>

        <div class="card card-body mb-4">
            @if($drops->petItem || $drops->variantItem)
                <p>This pet produces these {{ isset($pet->drops->dropData->data['drop_name']) ? strtolower($pet->drops->dropData->data['drop_name']).'s' : 'drops' }}, based on their type of pet and/or variant:</p>
                @if($drops->petItem)
                    <div class="row">
                    <div class="col-md align-self-center">
                            <h5>{{ $pet->pet->name }}</h5>
                        </div>
                        <div class="col-md align-self-center">
                            @if($drops->petItem->has_image) <img src="{{ $drops->petItem->imageUrl }}"><br/> @endif
                            {!! $drops->petItem->displayName !!} ({{ $drops->petQuantity }}/{{ $drops->dropData->data['frequency']['interval']}})
                        </div>
                    </div>
                @endif
                {!! $drops->petItem && $drops->variantItem ? '<hr/>' : null !!}
                @if($drops->variantItem)
                    <div class="row">
                        <div class="col-md align-self-center">
                            <h5>{{ $pet->variant->variant_name }} (Variant)</h5>
                        </div>
                        <div class="col-md align-self-center">
                            @if($drops->variantItem->has_image) <img src="{{ $drops->variantItem->imageUrl }}"><br/> @endif
                            {!! $drops->variantItem->displayName !!} ({{ $drops->variantQuantity }} every {{ $drops->dropData->data['frequency']['frequency'] > 1 ? $drops->dropData->data['frequency']['frequency'].' '.$drops->dropData->data['frequency']['interval'].'s' : $drops->dropData->data['frequency']['interval']}})
                        </div>
                    </div>
                @endif
            @else
                <p>This pet {{ isset($pet->drops->dropData->data['drop_name']) ? 'doesn\'t produce any '.strtolower($pet->drops->dropData->data['drop_name']).'s' : 'isn\'t eligible for any drops' }}.</p>
            @endif
        </div>

        @if($drops->petItem || $drops->variantItem)
            <div class="text-center">
                <p>
                    This pet has {{ $drops->drops_available }} batch{{ $drops->drops_available == 1 ? '' : 'es' }} of {{ isset($pet->drops->dropData->data['drop_name']) ? strtolower($pet->drops->dropData->data['drop_name']) : 'drop' }}s available.<br/>
                    @if(isset($drops->dropData->cap) && $drops->dropData->cap > 0)
                        This pet can manage a maximum of {{ $drops->dropData->cap }} batch{{ $drops->dropData->cap == 1 ? '' : 'es' }} of {{ isset($pet->drops->dropData->data['drop_name']) ? strtolower($pet->drops->dropData->data['drop_name']) : 'drop' }}s at once!
                        @if($drops->drops_available >= $drops->dropData->cap)
                             Until these {{ isset($pet->drops->dropData->data['drop_name']) ? strtolower($pet->drops->dropData->data['drop_name']) : 'drop' }}s are collected, this pet won't produce any more.
                        @else
                             This pet's next {{ isset($pet->drops->dropData->data['drop_name']) ? strtolower($pet->drops->dropData->data['drop_name']) : 'drop' }}(s) will be available to collect {!! pretty_date($drops->next_day) !!}.
                        @endif
                    @else
                        This pet's next {{ isset($pet->drops->dropData->data['drop_name']) ? strtolower($pet->drops->dropData->data['drop_name']) : 'drop' }}(s) will be available to collect {!! pretty_date($drops->next_day) !!}.
                    @endif
                </p>
            </div>
            @if(Auth::check() && Auth::user()->id == $pet->user_id && $drops->drops_available > 0)
                {!! Form::open(['url' => 'pets/pet/'.$pet->id]) !!}
                    {!! Form::submit('Collect '.(isset($pet->drops->dropData->data['drop_name']) ? $pet->drops->dropData->data['drop_name'] : 'Drop').($drops->drops_available > 1 ? 's' : ''), ['class' => 'btn btn-primary']) !!}
                {!! Form::close() !!}
            @endif
        @endif
    </div>
</div>

@if(Auth::check() && Auth::user()->hasPower('edit_inventories'))
    <div class="modal fade" id="paramsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <span class="modal-title h5 mb-0">[ADMIN] Adjust Drop</span>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    {!! Form::open(['url' => 'admin/pets/pet/'.$pet->id]) !!}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('parameters', 'Group') !!}
                                    {!! Form::select('parameters', $drops->dropData->parameterArray, $drops->parameters, ['class' => 'form-control']) !!}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('drops_available', 'Drops Available') !!}
                                    {!! Form::number('drops_available', $drops->drops_available, ['class' => 'form-control']) !!}
                                </div>
                            </div>
                        </div>

                        <div class="text-right">
                            {!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
                        </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@endif

@endsection
