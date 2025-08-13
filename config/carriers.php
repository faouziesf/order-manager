<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuration des Transporteurs CORRIGÉE ET STANDARDISÉE
    |--------------------------------------------------------------------------
    */

    'jax_delivery' => [
        'name' => 'JAX Delivery',
        'slug' => 'jax_delivery',
        'description' => 'Service de livraison JAX Delivery en Tunisie',
        
        // 🔧 CORRECTION : Configuration clarifiée pour JAX
        'config_fields' => [
            [
                'name' => 'username',
                'type' => 'text',
                'label' => 'Numéro de Compte JAX', // 🔧 CORRECTION : Libellé clarifié
                'required' => true,
                'help' => 'Votre numéro de compte JAX Delivery (ex: 2304)',
            ],
            [
                'name' => 'password',
                'type' => 'password',
                'label' => 'Token JWT JAX', // 🔧 CORRECTION : Libellé clarifié
                'required' => true,
                'help' => 'Token d\'authentification JWT fourni par JAX Delivery',
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
        
        // 🆕 CORRECTION : Configuration corrigée pour Mes Colis
        'config_fields' => [
            [
                'name' => 'password', // 🔧 CORRECTION : Changé de 'username' à 'password'
                'type' => 'password',
                'label' => 'Token d\'Accès Mes Colis', // 🔧 CORRECTION : Libellé modifié
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
    | Configuration système
    |--------------------------------------------------------------------------
    */
    'system' => [
        'default_carrier' => 'jax_delivery',
        'default_timeout' => 30,
        'debug_mode' => env('CARRIERS_DEBUG_MODE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Actions d'historique pour les commandes
    |--------------------------------------------------------------------------
    */
    'history_actions' => [
        'shipment_created' => 'Expédition créée',
        'shipment_validated' => 'Expédition validée',
        'picked_up_by_carrier' => 'Récupéré par transporteur',
        'in_transit' => 'En transit',
        'delivery_attempted' => 'Tentative de livraison',
        'delivery_failed' => 'Échec de livraison',
        'livraison' => 'Livré',
        'in_return' => 'En retour',
        'delivery_anomaly' => 'Anomalie de livraison',
        'tracking_updated' => 'Suivi mis à jour',
        'pickup_created' => 'Pickup créé',
        'pickup_validated' => 'Pickup validé',
        'pickup_cancelled' => 'Pickup annulé',
    ],
];