<?php

return [
    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        'super-admin' => [
            'driver' => 'session',
            'provider' => 'super-admins',
        ],

        'admin' => [
            'driver' => 'session',
            'provider' => 'admins',
        ],

        'confirmi' => [
            'driver' => 'session',
            'provider' => 'confirmi-users',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],

        'super-admins' => [
            'driver' => 'eloquent',
            'model' => App\Models\SuperAdmin::class,
        ],

        'admins' => [
            'driver' => 'eloquent',
            'model' => App\Models\Admin::class,
        ],

        'confirmi-users' => [
            'driver' => 'eloquent',
            'model' => App\Models\ConfirmiUser::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],

        'super-admins' => [
            'provider' => 'super-admins',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],

        'admins' => [
            'provider' => 'admins',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],

        'confirmi-users' => [
            'provider' => 'confirmi-users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 525600, // 1 an en minutes

    // Durée du remember token (en minutes) - 1 an
    'remember_token_lifetime' => 525600,
];
