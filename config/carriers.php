<?php

// config/carriers.php - Version MINIMALE mais extensible pour le prompt

return [
    'jax_delivery' => [
        'display_name' => 'Jax Delivery Services',
        'logo' => 'jax_delivery.png',
        'website' => 'https://jax-delivery.com',
        'description' => 'Service de livraison avec configuration simplifiée',
        
        // Capacités Jax (limitées comme actuellement)
        'supports_pickup_address' => false,
        'supports_bl_templates' => false,
        'supports_mass_labels' => false,
        'supports_drop_points' => false,
        'supports_payment_methods' => false,
        
        // Configuration API
        'api_endpoints' => [
            'test' => 'https://core.jax-delivery.com/api',
            'prod' => 'https://core.jax-delivery.com/api'
        ],
        
        'required_fields' => ['token'],
        'optional_fields' => ['environment'],
        
        // Mapping des statuts (votre mapping existant)
        'status_mapping' => [
            '10' => 'picked_up_by_carrier',
            '20' => 'in_transit',
            '30' => 'delivered',
            '40' => 'in_return',
            '50' => 'anomaly'
        ],
        
        // Fonctionnalités
        'features' => [
            'cod' => true,
            'tracking' => true,
            'scheduling' => false,
            'insurance' => false,
            'signature_required' => false,
            'return_labels' => false,
        ],
        
        // Configuration par défaut (votre config existante)
        'default_settings' => [
            'auto_create_pickup' => true,
            'default_governorate_mapping' => [
                'Tunis' => 'TUN',
                'Ariana' => 'ARI',
                'Ben Arous' => 'BEN',
                'Manouba' => 'MAN',
                'Nabeul' => 'NAB',
                'Zaghouan' => 'ZAG',
                'Bizerte' => 'BIZ',
                'Béja' => 'BEJ',
                'Jendouba' => 'JEN',
                'Kef' => 'KEF',
                'Siliana' => 'SIL',
                'Kairouan' => 'KAI',
                'Kasserine' => 'KAS',
                'Sidi Bouzid' => 'SID',
                'Sousse' => 'SOU',
                'Monastir' => 'MON',
                'Mahdia' => 'MAH',
                'Sfax' => 'SFA',
                'Gafsa' => 'GAF',
                'Tozeur' => 'TOZ',
                'Kebili' => 'KEB',
                'Gabès' => 'GAB',
                'Médenine' => 'MED',
                'Tataouine' => 'TAT',
            ],
        ]
    ],
    
    // PLACEHOLDER pour futurs transporteurs - Structure prête
    /*
    'fparcel' => [
        'display_name' => 'Fparcel Tunisia',
        'logo' => 'fparcel.png',
        'supports_pickup_address' => true,
        'supports_bl_templates' => true,
        'supports_mass_labels' => true,
        // ... config complète quand vous l'ajouterez
    ],
    
    'aramex' => [
        'display_name' => 'Aramex Tunisia', 
        'logo' => 'aramex.png',
        'supports_pickup_address' => true,
        'supports_bl_templates' => false,
        'supports_mass_labels' => true,
        // ... config complète quand vous l'ajouterez
    ]
    */
];