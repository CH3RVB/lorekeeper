<?php

/*
    |--------------------------------------------------------------------------
    | Queue Types
    |--------------------------------------------------------------------------
    |

    */

return [

    'vanilla'    => [
        'name'             => 'Vanilla',
        'item_consume'     => false,
        'character_submit' => false,
        'image_upload'     => false,
    ],

    'chara_sale' => [
        'name'             => 'Character Sale',
        'item_consume'     => false,
        'character_submit' => true,
        'image_upload'     => false,
    ],
];
