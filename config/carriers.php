<?php

// config/carriers.php
// Configuration centralisée des transporteurs supportés - JaxDelivery uniquement

return [
    'jax_delivery' => [
        'display_name' => 'Jax Delivery Services',
        'logo' => 'jax_delivery.png',
        'website' => 'https://jax-delivery.com',
        'description' => 'Service de livraison avec configuration simplifiée',
        
        // Capacités simplifiées pour Jax
        'supports_pickup_address' => false,    // Utilise l'adresse du compte Jax
        'supports_bl_templates' => false,      // BL générés par Jax
        'supports_mass_labels' => false,       // Étiquettes individuelles uniquement
        'supports_drop_points' => false,       // Pas de points de dépôt
        'supports_payment_methods' => false,   // Méthodes fixes
        
        // Endpoints API
        'api_endpoints' => [
            'test' => 'https://core.jax-delivery.com/api',
            'prod' => 'https://core.jax-delivery.com/api'
        ],
        
        // Configuration simplifiée avec token direct
        'required_fields' => ['token'],
        'optional_fields' => ['environment'],
        
        // Mapping des statuts Jax vers statuts internes
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
        
        // Limites
        'limits' => [
            'max_weight' => 25,
            'max_dimensions' => [
                'length' => 80,
                'width' => 80,
                'height' => 80,
            ],
            'max_value' => 5000,
        ],
        
        // Configuration par défaut
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
            'notification_emails' => [],
        ]
    ],
];