<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuration des Transporteurs SIMPLIFIÉE
    |--------------------------------------------------------------------------
    */

    'jax_delivery' => [
        'name' => 'JAX Delivery',
        'slug' => 'jax_delivery',
        'description' => 'Service de livraison JAX Delivery en Tunisie',
        
        // Configuration pour l'interface de création
        'config_fields' => [
            [
                'name' => 'username',
                'type' => 'text',
                'label' => 'Numéro de Compte JAX',
                'required' => true,
                'help' => 'Votre numéro de compte JAX Delivery',
            ],
            [
                'name' => 'password',
                'type' => 'password',
                'label' => 'Token API',
                'required' => true,
                'help' => 'Token d\'authentification fourni par JAX Delivery',
            ],
            [
                'name' => 'environment',
                'type' => 'select',
                'label' => 'Environnement',
                'required' => true,
                'options' => [
                    'test' => 'Test/Sandbox',
                    'production' => 'Production',
                ],
                'default' => 'test',
            ],
        ],
        
        'api' => [
            'base_url' => 'https://core.jax-delivery.com/api',
            'timeout' => 30,
        ],
        
        'endpoints' => [
            'create_shipment' => '/user/colis/add',
            'create_pickup' => '/client/createByean',
            'track_shipment' => '/user/colis/getstatubyean/{ean}',
            'test_connection' => '/gouvernorats',
        ],
        
        'features' => [
            'create_shipment' => true,
            'create_pickup' => true,
            'track_shipment' => true,
        ],
    ],

    'mes_colis' => [
        'name' => 'Mes Colis Express',
        'slug' => 'mes_colis',
        'description' => 'Service de livraison Mes Colis Express en Tunisie',
        
        // Configuration pour l'interface de création
        'config_fields' => [
            [
                'name' => 'username',
                'type' => 'text',
                'label' => 'Token d\'accès (x-access-token)',
                'required' => true,
                'help' => 'Token d\'authentification fourni par Mes Colis Express',
            ],
            [
                'name' => 'environment',
                'type' => 'select',
                'label' => 'Environnement',
                'required' => true,
                'options' => [
                    'test' => 'Test/Sandbox',
                    'production' => 'Production',
                ],
                'default' => 'test',
            ],
        ],
        
        'api' => [
            'base_url' => 'https://api.mescolis.tn/api',
            'timeout' => 30,
        ],
        
        'endpoints' => [
            'create_shipment' => '/orders/Create',
            'track_shipment' => '/orders/GetOrder',
            'test_connection' => '/orders/GetOrder',
        ],
        
        'features' => [
            'create_shipment' => true,
            'create_pickup' => false, // Pas d'API pickup dédiée
            'track_shipment' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuration système simplifiée
    |--------------------------------------------------------------------------
    */
    'system' => [
        'default_carrier' => 'jax_delivery',
        'default_timeout' => 30,
        'debug_mode' => env('CARRIERS_DEBUG_MODE', false),
    ],
];