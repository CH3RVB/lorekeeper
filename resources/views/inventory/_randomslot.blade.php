<li class="list-group-item">
    <a class="card-title h5 collapse-title" data-toggle="collapse" href="#openRandomSlotForm"> Use Randomized Slot</a>
    <div id="openRandomSlotForm" class="collapse">
        {!! Form::hidden('tag', $tag->tag) !!}
        <div class="alert alert-info text-center">
            <p>This slot's traits will be randomized each time.</p>
        </div>
        <p>This action is not reversible. Are you sure you want to use this item?</p>
        <div class="text-right">
            {!! Form::button('Open', [
                'class' => 'btn btn-primary',
                'name' => 'action',
                'value' => 'act',
                'type' => 'submit',
            ]) !!}
        </div>
    </div>
</li>
