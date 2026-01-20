<?php

return [

    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    'guards' => [

        // Web (Filament Dashboard) - Users
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        // Customer API (Mobile) - Sanctum Tokens
        'customer' => [
            'driver' => 'sanctum',
            'provider' => 'customers',
        ],

        // Staff API (Mobile) - Sanctum Tokens
        'staff' => [
            'driver' => 'sanctum',
            'provider' => 'restaurant_staff',
        ],

    ],

    'providers' => [

        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],

        'customers' => [
            'driver' => 'eloquent',
            'model' => App\Models\Customer::class,
        ],

        'restaurant_staff' => [
            'driver' => 'eloquent',
            'model' => App\Models\RestaurantStaff::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,
];
