@php
    // map the keys and the 'name' value of config('lorekeeper.limits.limit_types')

    $debitableLimits = array_keys(
        array_filter(config('lorekeeper.limits.limit_types'), function ($limit) {
            return $limit['debitable'] == true;
        }),
    );
    $countableLimits = array_keys(
        array_filter(config('lorekeeper.limits.limit_types'), function ($limit) {
            return $limit['countable'] == true;
        }),
    );

    $limits = hasLimits($object) ? getLimits($object) : null;

    // Hiding auto unlock options are good for cases where the user should not know the option exists for that object
    // Prompts are a good example--users shouldn't know they can auto-unlock prompts, as that would be confusing, since
// the UI for the prompt interactions occurs on submission...
// ex the limits are checked as part of submitting a prompt,
// requiring the user to manually unlock the prompt prior, especially if the limits are needed for every prompt submission, would be problematic.
// also as currently implemented having manual unlocking required would effectively prevent users from submitting the prompt
// as a refinement, this could be changed to allow for manual unlocking under certain conditions
if (!isset($hideAutoUnlock)) {
    $hideAutoUnlock = false;
}
// Hide "is unlocked" option when it makes sense for a limit to always be one or the other, much like the above
if (!isset($hideIsUnlocked)) {
    $hideIsUnlocked = false;
}
// Opinionated choice, if "Is Unlocked" is hidden, then the limit will be assume to always be a one-time unlock
if (!isset($isUnlocked)) {
    $isUnlocked = true;
}

// Set this to false to not use a form (for example, if the form is already open outside of this include)
// Useful for objects that have limit creation as part of their own creation
//or those that rely on having different "groups" of limits with different functions
if (!isset($useForm)) {
    $useForm = true;
}
if (!isset($type)) {
    $type = 'Limit';
    }

    // View options
    if (!isset($showRecipient)) {
        $showRecipient = false;
    }

@endphp

<div class="card p-4 mb-3 mt-3" id="limit-card">
    <h3>{{ isset($customHeader) ? $customHeader : 'Limits' }}</h3>

    <p>
        You can add requirements to this object by clicking "Add Limit" & selecting a requirement from the dropdown below.
        <br />
        Requirements are used to determine if a specific action can be performed on an object.
        <br /><b>Note that the checks for requirements are automatic, but their usage needs to be defined in the code.</b>
        <br /><b>Dynamic limits are created in the admin panel, but execute their logic in the code.</b>
    </p>
    {!! isset($info) ? '<p class="alert alert-info">' . $info . '</p>' : '' !!}

    @if ($useForm)
        {!! Form::open(['url' => 'admin/limits']) !!}
        {!! Form::hidden('object_model', get_class($object)) !!}
        {!! Form::hidden('object_id', $object->id) !!}

        @include('widgets._limit_select', [
            'prefix' => $prefix ?? '',
            'limits' => $limits,
            'showRecipient' => $showRecipient,
            'type' => $type,
        ])

        <div>
            @if ($limits)
                <i class="fas fa-trash text-danger float-right mt-2 mx-2 fa-2x" data-toggle="tooltip" title="To delete limits, simply remove all existing limits and click 'Edit Limits'"></i>
            @endif
            {!! Form::submit(($limits ? 'Edit' : 'Create') . ' Limits', ['class' => 'btn btn-primary float-right']) !!}
        </div>

        {!! Form::close() !!}
    @else
        @include('widgets._limit_select', [
            'prefix' => $prefix ?? '',
            'limits' => $limits,
            'showRecipient' => $showRecipient,
            'type' => $type,
        ])
    @endif

</div>

<fieldset disabled>
    @include('widgets._limit_select_row', [
        'prefix' => $prefix ?? '',
        'type' => $type,
    ])
    @include('js._limit_js', [
        'prefix' => $prefix ?? '',
    ])
</fieldset>
