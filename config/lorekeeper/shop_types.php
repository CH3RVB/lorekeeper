<?php

/*
    |--------------------------------------------------------------------------
    | Queue Types
    |--------------------------------------------------------------------------
    |
    */

return [

    'trade'  => [
        'name'      => 'Trade-in',
        'use_items' => true,
        'non_cost'  => false,
        'icon'  => '<i class="fas fa-share"></i>',
    ],
    'resell' => [
        'name'      => 'Resale',
        'use_items' => true,
        'non_cost'  => true,
        'icon'  => '<i class="fas fa-shopping-cart"></i>',
    ],
];
