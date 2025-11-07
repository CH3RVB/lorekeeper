<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Milestones
    |--------------------------------------------------------------------------
    |
    |
    |
    */

    'Item'      => [
        'name' => 'Items',
        'desc' => '(This count includes items you no longer possess.)',
    ],

    'Prompt'    => [
        'name' => 'Prompts',
        'path' => '\App\Models\Prompt\Prompt', //path for non asset helper things that still want a category
        'desc' => '(This count retroactively includes all previous submissions.)',
    ],
    'Character' => [
        'name' => 'Owned Characters',
        'desc' => '(This count includes characters you no longer possess.)',
    ],
    'Design'    => [
        'name'         => 'Designed Characters',
        'desc'         => '(This count includes any character onsite with an image that credits you.)',
        'override_key' => true, //abort doing all the filtering and such. we're not using categories here so it's fine
    ],
    'Currency'  => [
        'name' => 'Currencies',
        'desc' => '(This count includes currencies you no longer possess.)',
        'override_key' => true,
    ],
];
