<?php

/*
    |--------------------------------------------------------------------------
    | Queue Types
    |--------------------------------------------------------------------------
    |

    */

return [

    'vanilla'   => [
        'name'             => 'Vanilla',
        'item_consume'     => false,
        'character_submit' => false,
        'image_upload'     => false,
    ],

    'char_icon' => [
        'name'             => 'Character Icon',
        'item_consume'     => false,
        'character_submit' => true,
        'image_upload'     => true,
    ],
];
