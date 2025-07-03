<?php

// config/carriers.php
// Configuration centralisée des transporteurs supportés

return [
    'fparcel' => [
        'display_name' => 'Fparcel Tunisia',
        'logo' => 'fparcel.png',
        'website' => 'https://fparcel.com',
        'description' => 'Service de livraison tunisien avec fonctionnalités complètes',
        
        // Capacités supportées
        'supports_pickup_address' => true,
        'supports_bl_templates' => true,
        'supports_mass_labels' => true,
        'supports_drop_points' => true,
        'supports_payment_methods' => true,
        
        // Endpoints API
        'api_endpoints' => [
            'test' => 'http://fparcel.net:59/WebServiceExterne',
            'prod' => 'https://admin.fparcel.net/WebServiceExterne'
        ],
        
        // Champs requis pour la configuration
        'required_fields' => ['username', 'password'],
        'optional_fields' => ['environment'],
        
        // Mapping des statuts Fparcel vers statuts internes
        'status_mapping' => [
            '1' => 'created',
            '3' => 'picked_up_by_carrier',
            '6' => 'in_transit',
            '7' => 'delivered',
            '9' => 'in_return',
            '11' => 'anomaly'
        ],
        
        // Fonctionnalités avancées
        'features' => [
            'cod' => true,               // Cash on delivery
            'tracking' => true,          // Suivi des colis
            'scheduling' => true,        // Planification d'enlèvement
            'insurance' => false,        // Assurance
            'signature_required' => false, // Signature obligatoire
            'return_labels' => true,     // Étiquettes de retour
        ],
        
        // Limites techniques
        'limits' => [
            'max_weight' => 30,          // kg
            'max_dimensions' => [
                'length' => 100,         // cm
                'width' => 100,
                'height' => 100,
            ],
            'max_value' => 10000,        // TND
        ],
        
        // Configuration par défaut
        'default_settings' => [
            'default_payment_method' => 'ESP',
            'default_weight' => 0.5,
            'auto_validate_positions' => false,
            'notification_emails' => [],
        ]
    ],

    'jax_delivery' => [
        'display_name' => 'Jax Delivery Services',
        'logo' => 'jax_delivery.png',
        'website' => 'https://jax-delivery.com',
        'description' => 'Service de livraison avec configuration simplifiée',
        
        // Capacités limitées pour Jax
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
                // ... autres gouvernorats
            ],
            'notification_emails' => [],
        ]
    ],

    // Futurs transporteurs...
    /*
    'aramex' => [
        'display_name' => 'Aramex Tunisia',
        'logo' => 'aramex.png',
        'website' => 'https://aramex.com',
        'description' => 'Service de livraison international',
        
        'supports_pickup_address' => true,
        'supports_bl_templates' => false,
        'supports_mass_labels' => true,
        'supports_drop_points' => false,
        'supports_payment_methods' => false,
        
        'api_endpoints' => [
            'test' => 'https://ws.dev.aramex.net',
            'prod' => 'https://ws.aramex.net'
        ],
        
        'required_fields' => ['username', 'password', 'account_number'],
        'optional_fields' => ['country_code'],
        
        'status_mapping' => [
            // À définir selon l'API Aramex
        ],
        
        'features' => [
            'cod' => false,
            'tracking' => true,
            'scheduling' => true,
            'insurance' => true,
            'signature_required' => true,
            'return_labels' => true,
        ],
        
        'limits' => [
            'max_weight' => 50,
            'max_dimensions' => [
                'length' => 120,
                'width' => 120,
                'height' => 120,
            ],
            'max_value' => 20000,
        ],
        
        'default_settings' => [
            'country_code' => 'TN',
            'currency' => 'TND',
            'weight_unit' => 'KG',
            'dimension_unit' => 'CM',
        ]
    ],
    */
];