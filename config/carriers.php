<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuration des Transporteurs Multi-Carriers
    |--------------------------------------------------------------------------
    |
    | Configuration centralisée pour JAX Delivery et Mes Colis Express
    | Support multi-compte par transporteur avec mapping des gouvernorats
    |
    */

    'jax_delivery' => [
        'name' => 'JAX Delivery',
        'slug' => 'jax_delivery', 
        'logo' => '/images/carriers/jax-delivery.png',
        'description' => 'Service de livraison rapide en Tunisie',
        'website' => 'https://jax-delivery.com',
        'support_phone' => '+216 70 000 000',
        'support_email' => 'support@jax-delivery.com',
        
        // Configuration API
        'api' => [
            'base_url' => 'https://core.jax-delivery.com/api',
            'timeout' => 30,
            'retry_attempts' => 3,
            'retry_delay' => 2, // secondes
        ],
        
        // Authentification
        'auth' => [
            'type' => 'bearer_token',
            'header_name' => 'Authorization',
            'header_prefix' => 'Bearer ',
            'requires_account_number' => true,
            'requires_token' => true,
        ],
        
        // Endpoints
        'endpoints' => [
            'create_shipment' => '/user/colis/add',
            'track_shipment' => '/user/colis/getstatubyean/{ean}',
            'test_connection' => '/user/colis/add', // Pour test avec données fictives
        ],
        
        // Mapping des champs de configuration dans delivery_configurations
        'config_mapping' => [
            'account_number' => 'username', // Numéro de compte JAX
            'api_token' => 'password',      // Token API JAX
        ],
        
        // Mapping des gouvernorats (ID région → code JAX)
        'governorate_mapping' => [
            1 => '1',   // Tunis
            2 => '2',   // Ariana  
            3 => '3',   // Ben Arous
            4 => '4',   // Manouba
            5 => '5',   // Nabeul
            6 => '6',   // Zaghouan
            7 => '7',   // Bizerte
            8 => '8',   // Béja
            9 => '9',   // Jendouba
            10 => '10', // Le Kef
            11 => '11', // Siliana
            12 => '12', // Kairouan
            13 => '13', // Kasserine
            14 => '14', // Sidi Bouzid
            15 => '15', // Sousse
            16 => '16', // Monastir
            17 => '17', // Mahdia
            18 => '18', // Sfax
            19 => '19', // Gafsa
            20 => '20', // Tozeur
            21 => '21', // Kebili
            22 => '22', // Gabès
            23 => '23', // Medenine
            24 => '24', // Tataouine
        ],
        
        // Structure des données pour création de colis
        'shipment_structure' => [
            'required_fields' => [
                'account_number',
                'recipient_name',
                'recipient_phone', 
                'recipient_address',
                'governorate_code', // Code numérique (1-24)
                'delegation',       // = city
                'cod_amount',
                'content_description',
            ],
            'optional_fields' => [
                'recipient_phone_2',
                'weight',
                'dimensions',
                'pickup_date',
                'delivery_notes',
            ]
        ],
        
        // Mapping des statuts JAX → statuts internes
        'status_mapping' => [
            'CREATED' => 'created',
            'VALIDATED' => 'validated', 
            'PICKED_UP' => 'picked_up_by_carrier',
            'IN_TRANSIT' => 'in_transit',
            'OUT_FOR_DELIVERY' => 'in_transit',
            'DELIVERY_ATTEMPTED' => 'delivery_attempted',
            'DELIVERED' => 'delivered',
            'DELIVERY_FAILED' => 'delivery_failed',
            'RETURNED' => 'in_return',
            'CANCELLED' => 'cancelled',
            'ANOMALY' => 'anomaly',
        ],
        
        // Configuration par défaut
        'defaults' => [
            'weight' => 1.0,
            'nb_pieces' => 1,
            'content_description' => 'Colis e-commerce',
            'pickup_date' => null, // Utiliser date du jour
        ],
        
        // Limites et contraintes
        'limits' => [
            'max_weight' => 30.0, // kg
            'max_cod_amount' => 5000.0, // TND
            'max_content_length' => 255,
            'max_address_length' => 500,
        ],
        
        // Configuration du tracking automatique
        'tracking' => [
            'enabled' => true,
            'frequency_minutes' => 30,    // Tracking normal
            'express_frequency_minutes' => 15, // Tracking express pour livraisons récentes
            'express_duration_hours' => 48,   // Durée du tracking express
            'batch_size' => 50,           // Nombre de colis à tracker par batch
        ],
    ],

    'mes_colis' => [
        'name' => 'Mes Colis Express',
        'slug' => 'mes_colis',
        'logo' => '/images/carriers/mes-colis.png', 
        'description' => 'Service de livraison express en Tunisie',
        'website' => 'https://mescolis.tn',
        'support_phone' => '+216 71 000 000',
        'support_email' => 'support@mescolis.tn',
        
        // Configuration API
        'api' => [
            'base_url' => 'https://api.mescolis.tn/api',
            'timeout' => 30,
            'retry_attempts' => 3,
            'retry_delay' => 2, // secondes
        ],
        
        // Authentification
        'auth' => [
            'type' => 'header_token',
            'header_name' => 'x-access-token',
            'header_prefix' => '',
            'requires_account_number' => false,
            'requires_token' => true,
        ],
        
        // Endpoints
        'endpoints' => [
            'create_shipment' => '/orders/Create',
            'track_shipment' => '/orders/GetOrder',
            'test_connection' => '/orders/Create', // Pour test avec données fictives
        ],
        
        // Mapping des champs de configuration dans delivery_configurations
        'config_mapping' => [
            'api_token' => 'username',     // Token API Mes Colis
            'unused' => 'password',        // Non utilisé (vide)
        ],
        
        // Mapping des gouvernorats (ID région → nom complet)
        'governorate_mapping' => [
            1 => 'Tunis',
            2 => 'Ariana',
            3 => 'Ben Arous', 
            4 => 'Manouba',
            5 => 'Nabeul',
            6 => 'Zaghouan',
            7 => 'Bizerte',
            8 => 'Béja',
            9 => 'Jendouba',
            10 => 'Le Kef',
            11 => 'Siliana',
            12 => 'Kairouan',
            13 => 'Kasserine',
            14 => 'Sidi Bouzid',
            15 => 'Sousse',
            16 => 'Monastir',
            17 => 'Mahdia',
            18 => 'Sfax',
            19 => 'Gafsa',
            20 => 'Tozeur',
            21 => 'Kebili',
            22 => 'Gabès',
            23 => 'Medenine',
            24 => 'Tataouine',
        ],
        
        // Structure des données pour création de commande
        'shipment_structure' => [
            'required_fields' => [
                'recipient_name',
                'recipient_phone',
                'recipient_address', 
                'governorate_name',  // Nom complet du gouvernorat
                'location',          // = city
                'cod_amount',
                'content_description',
            ],
            'optional_fields' => [
                'recipient_phone_2',
                'weight',
                'dimensions',
                'pickup_date',
                'delivery_notes',
            ]
        ],
        
        // Mapping des statuts Mes Colis → statuts internes
        'status_mapping' => [
            'NEW' => 'created',
            'CONFIRMED' => 'validated',
            'PICKED_UP' => 'picked_up_by_carrier', 
            'IN_TRANSIT' => 'in_transit',
            'OUT_FOR_DELIVERY' => 'in_transit',
            'ATTEMPTED' => 'delivery_attempted',
            'DELIVERED' => 'delivered',
            'FAILED' => 'delivery_failed',
            'RETURNED' => 'in_return',
            'CANCELLED' => 'cancelled',
            'PROBLEM' => 'anomaly',
        ],
        
        // Configuration par défaut
        'defaults' => [
            'weight' => 1.0,
            'nb_pieces' => 1,
            'content_description' => 'Commande e-commerce',
            'pickup_date' => null, // Utiliser date du jour
        ],
        
        // Limites et contraintes
        'limits' => [
            'max_weight' => 25.0, // kg
            'max_cod_amount' => 3000.0, // TND
            'max_content_length' => 200,
            'max_address_length' => 400,
        ],
        
        // Configuration du tracking automatique
        'tracking' => [
            'enabled' => true,
            'frequency_minutes' => 30,    // Tracking normal
            'express_frequency_minutes' => 15, // Tracking express pour livraisons récentes
            'express_duration_hours' => 48,   // Durée du tracking express
            'batch_size' => 40,           // Nombre de colis à tracker par batch
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuration globale du système de livraison
    |--------------------------------------------------------------------------
    */
    
    'system' => [
        // Paramètres généraux
        'default_carrier' => 'jax_delivery',
        'allow_multiple_carriers' => true,
        'require_pickup_address' => false, // Simplifié selon les contraintes
        
        // Configuration des jobs de tracking
        'tracking_jobs' => [
            'enabled' => true,
            'schedule' => '*/30 * * * *', // Toutes les 30 minutes
            'express_schedule' => '*/15 * * * *', // Toutes les 15 minutes pour livraisons récentes
            'batch_size' => 100,
            'timeout' => 60, // secondes
            'max_retries' => 3,
        ],
        
        // Configuration des statuts internes
        'internal_statuses' => [
            'created' => [
                'label' => 'Créé',
                'color' => 'primary',
                'icon' => 'fa-plus',
                'order_status' => 'expédiée',
            ],
            'validated' => [
                'label' => 'Validé',
                'color' => 'success', 
                'icon' => 'fa-check',
                'order_status' => 'expédiée',
            ],
            'picked_up_by_carrier' => [
                'label' => 'Récupéré par transporteur',
                'color' => 'warning',
                'icon' => 'fa-truck-pickup',
                'order_status' => 'en_transit',
            ],
            'in_transit' => [
                'label' => 'En transit',
                'color' => 'info',
                'icon' => 'fa-truck-moving',
                'order_status' => 'en_transit',
            ],
            'delivery_attempted' => [
                'label' => 'Tentative de livraison',
                'color' => 'warning',
                'icon' => 'fa-door-open',
                'order_status' => 'tentative_livraison',
            ],
            'delivered' => [
                'label' => 'Livré',
                'color' => 'success',
                'icon' => 'fa-check-circle',
                'order_status' => 'livrée',
            ],
            'delivery_failed' => [
                'label' => 'Échec de livraison',
                'color' => 'danger',
                'icon' => 'fa-exclamation-triangle',
                'order_status' => 'échec_livraison',
            ],
            'in_return' => [
                'label' => 'En retour',
                'color' => 'warning',
                'icon' => 'fa-undo',
                'order_status' => 'en_retour',
            ],
            'cancelled' => [
                'label' => 'Annulé',
                'color' => 'secondary',
                'icon' => 'fa-times',
                'order_status' => 'annulée',
            ],
            'anomaly' => [
                'label' => 'Anomalie',
                'color' => 'danger',
                'icon' => 'fa-exclamation-circle',
                'order_status' => 'anomalie_livraison',
            ],
        ],
        
        // Messages d'erreur standardisés
        'error_messages' => [
            'connection_failed' => 'Impossible de se connecter au transporteur',
            'invalid_credentials' => 'Identifiants invalides',
            'api_error' => 'Erreur API du transporteur',
            'invalid_address' => 'Adresse de livraison invalide',
            'weight_exceeded' => 'Poids maximum dépassé',
            'cod_amount_exceeded' => 'Montant COD maximum dépassé',
            'unknown_governorate' => 'Gouvernorat non reconnu',
            'shipment_not_found' => 'Expédition non trouvée',
            'tracking_failed' => 'Échec du suivi',
        ],
        
        // Configuration de l'historique
        'history' => [
            'record_all_changes' => true,
            'include_api_responses' => true,
            'max_response_length' => 2000,
        ],
        
        // Validation des données
        'validation' => [
            'phone_regex' => '/^(\+216|216|0)?[0-9]{8}$/',
            'address_min_length' => 10,
            'name_min_length' => 2,
            'required_fields' => [
                'customer_name',
                'customer_phone', 
                'customer_address',
                'customer_governorate',
                'customer_city',
                'total_price',
            ],
        ],
        
        // Environnement de test
        'test_mode' => [
            'enabled' => env('DELIVERY_TEST_MODE', false),
            'mock_responses' => true,
            'fake_tracking_numbers' => true,
            'simulate_delays' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuration des actions d'historique
    |--------------------------------------------------------------------------
    */
    
    'history_actions' => [
        'shipment_created' => 'Expédition créée',
        'shipment_validated' => 'Expédition validée', 
        'pickup_created' => 'Enlèvement créé',
        'pickup_validated' => 'Enlèvement validé',
        'picked_up_by_carrier' => 'Récupéré par transporteur',
        'in_transit' => 'En transit',
        'delivery_attempted' => 'Tentative de livraison',
        'delivery_failed' => 'Échec de livraison',
        'livraison' => 'Livré',
        'in_return' => 'En retour',
        'delivery_anomaly' => 'Anomalie de livraison',
        'tracking_updated' => 'Suivi mis à jour',
        'carrier_connection_test' => 'Test de connexion transporteur',
        'carrier_configuration_updated' => 'Configuration transporteur mise à jour',
    ],
];