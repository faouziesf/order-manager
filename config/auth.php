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

        'manager' => [
            'driver' => 'session',
            'provider' => 'managers',
        ],

        'employee' => [
            'driver' => 'session',
            'provider' => 'employees',
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

        'managers' => [
            'driver' => 'eloquent',
            'model' => App\Models\Manager::class,
        ],

        'employees' => [
            'driver' => 'eloquent',
            'model' => App\Models\Employee::class,
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
        
        'managers' => [
            'provider' => 'managers',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
        
        'employees' => [
            'provider' => 'employees',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,
];