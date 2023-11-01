<h3>Your Pets <a class="small pet-collapse-toggle collapse-toggle" href="#userPet" data-toggle="collapse">Show</a></h3>
<hr>
<div class="card mb-3 collapse collapsed" id="userPet">
    <div class="card-body">
        <div class="text-right mb-3">
            <div class="d-inline-block">
                {!! Form::label('pet_category_id', 'Filter:', ['class' => 'mr-2']) !!}
                <select class="form-control d-inline-block w-auto" id="userItemCategory">
                    <option value="all">All Categories</option>
                    <option value="selected">Selected Items</option>
                    <option disabled>&#9472;&#9472;&#9472;&#9472;&#9472;&#9472;&#9472;&#9472;&#9472;&#9472;</option>
                    <option value="0">Miscellaneous</option>
                    @foreach ($petcategories as $petcategory)
                        <option value="{{ $petcategory->id }}">{{ $petcategory->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="d-inline-block">
                {!! Form::label('pet_category_id', 'Action:', ['class' => 'ml-2 mr-2']) !!}
                <a href="#" class="btn btn-primary pet-select-all">Select All Visible</a>
                <a href="#" class="btn btn-primary pet-clear-selection">Clear Visible Selection</a>
            </div>
        </div>
        <div id="userItems" class="user-items">
            <div class="row">
                @foreach ($pet as $item)
                    @if ($page == 'quickstock')
                        <div class="col-lg-2 col-sm-3 col-6 mb-3 user-item category-all  category-{{ $item->pet->pet_category_id ?: 0 }} {{ isset($selected) && array_key_exists('pet' . $item->id, $selected) ? 'category-selected' : '' }}"
                            data-id="{{ $item->id }}" data-name="{{ $user->name }}'s {{ $item->pet->name }}"
                            style="{{ $item->isTransferrable && $item->pet->canUserSell && !$item->chara_id ? '' : 'opacity: 50%;' }}">
                        @else
                            <div class="col-lg-2 col-sm-3 col-6 mb-3 user-item category-all {{ $item->isTransferrable }} category-{{ $item->pet->pet_category_id ?: 0 }} {{ isset($selected) && array_key_exists('pet' . $item->id, $selected) ? 'category-selected' : '' }}"
                                data-id="{{ $item->id }}"
                                data-name="{{ $user->name }}'s {{ $item->pet->name }}">
                    @endif
                    <div class="text-center pet-item">
                        <div class="mb-1">
                            <a class="pet-stack"><img src="{{ $item->pet->variantimage($item->variant_id) }}" /></a>
                        </div>

                        <div class="{{ $item->pet_name ? 'btn-dark' : 'btn-primary' }} btn btn-sm my-1">
                            {!! $item->pet_name ?? $item->pet->name !!}
                            @if ($item->chara_id)
                                <span data-toggle="tooltip" title="Attached to a character."><i
                                        class="fas fa-link ml-1"></i></span>
                            @endif
                        </div>
                        <p>
                            {!! Form::checkbox(
                                isset($fieldName) && $fieldName ? $fieldName : 'pet_stack_id[]',
                                $item->id,
                                isset($selected) && array_key_exists('pet' . $item->id, $selected) ? true : false,
                                ['class' => 'pet-checkbox'],
                            ) !!}
                        </p>
                        <div>
                            <a href="#" class="btn btn-xs btn-outline-info pet-info">Info</a>
                        </div>
                    </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
</div>
