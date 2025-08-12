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
        
        // 🆕 CONFIGURATION POUR L'INTERFACE DE CRÉATION
        'config_fields' => [
            [
                'name' => 'username',
                'type' => 'text',
                'label' => 'Numéro de Compte JAX',
                'required' => true,
                'help' => 'Votre numéro de compte JAX Delivery',
                'placeholder' => 'Ex: JAX123456',
            ],
            [
                'name' => 'password',
                'type' => 'password',
                'label' => 'Token API',
                'required' => true,
                'help' => 'Token d\'authentification fourni par JAX Delivery',
                'placeholder' => 'Votre token API...',
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
                'help' => 'Choisissez l\'environnement de test ou production',
            ],
        ],
        
        // 🆕 SERVICES SUPPORTÉS
        'supported_services' => [
            'create_shipment' => true,
            'create_pickup' => true,
            'track_shipment' => true,
            'webhooks' => true,
            'bulk_tracking' => true,
        ],
        
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
            'create_pickup' => '/client/createByean',
            'track_shipment' => '/user/colis/getstatubyean/{ean}',
            'get_statuses' => '/statuts',
            'get_governorates' => '/gouvernorats',
            'test_connection' => '/gouvernorats', // 🆕 Pour test de connexion
        ],
        
        // Mapping des champs de configuration dans delivery_configurations
        'config_mapping' => [
            'account_number' => 'username', // Numéro de compte JAX
            'api_token' => 'password',      // Token API JAX
        ],
        
        // 🆕 MAPPING GOUVERNORATS ÉTENDU (compatible avec votre système)
        'governorate_mapping' => [
            // ID région → Code JAX
            1 => '11',   // Tunis → 11
            2 => '12',   // Ariana → 12
            3 => '13',   // Ben Arous → 13
            4 => '14',   // Manouba → 14
            5 => '21',   // Nabeul → 21
            6 => '22',   // Zaghouan → 22
            7 => '23',   // Bizerte → 23
            8 => '31',   // Béja → 31
            9 => '32',   // Jendouba → 32
            10 => '33',  // Le Kef → 33
            11 => '34',  // Siliana → 34
            12 => '41',  // Kairouan → 41
            13 => '42',  // Kasserine → 42
            14 => '43',  // Sidi Bouzid → 43
            15 => '51',  // Sousse → 51
            16 => '52',  // Monastir → 52
            17 => '53',  // Mahdia → 53
            18 => '61',  // Sfax → 61
            19 => '71',  // Gafsa → 71
            20 => '72',  // Tozeur → 72
            21 => '73',  // Kebili → 73
            22 => '81',  // Gabès → 81
            23 => '82',  // Medenine → 82
            24 => '83',  // Tataouine → 83
            
            // 🆕 MAPPING PAR NOM (fallback)
            'Tunis' => '11',
            'Ariana' => '12',
            'Ben Arous' => '13',
            'Manouba' => '14',
            'La Mannouba' => '14',
            'Nabeul' => '21',
            'Zaghouan' => '22',
            'Bizerte' => '23',
            'Béja' => '31',
            'Beja' => '31',
            'Jendouba' => '32',
            'Kef' => '33',
            'Le Kef' => '33',
            'Siliana' => '34',
            'Kairouan' => '41',
            'Kasserine' => '42',
            'Sidi Bouzid' => '43',
            'Sousse' => '51',
            'Monastir' => '52',
            'Mahdia' => '53',
            'Sfax' => '61',
            'Gafsa' => '71',
            'Tozeur' => '72',
            'Kebili' => '73',
            'Kébili' => '73',
            'Gabès' => '81',
            'Gabes' => '81',
            'Medenine' => '82',
            'Médenine' => '82',
            'Tataouine' => '83',
        ],
        
        // Structure des données pour création de colis
        'shipment_structure' => [
            'required_fields' => [
                'account_number',
                'recipient_name',
                'recipient_phone', 
                'recipient_address',
                'governorate_code', // Code numérique (11-83)
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
                'external_reference',
                'exchange',
            ]
        ],
        
        // 🆕 MAPPING STATUTS JAX DÉTAILLÉ → statuts internes
        'status_mapping' => [
            // Statuts numériques JAX
            '1' => 'created',
            '2' => 'validated', 
            '3' => 'picked_up_by_carrier',
            '4' => 'in_transit',
            '5' => 'delivered',
            '6' => 'delivery_failed',
            '7' => 'in_return',
            '8' => 'returned',
            '9' => 'anomaly',
            '10' => 'created',
            
            // Statuts textuels JAX (fallback)
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
            'exchange' => 0,
        ],
        
        // Limites et contraintes
        'limits' => [
            'max_weight' => 30.0, // kg
            'max_cod_amount' => 5000.0, // TND
            'max_content_length' => 255,
            'max_address_length' => 500,
        ],
        
        // 🆕 FONCTIONNALITÉS DISPONIBLES
        'features' => [
            'cod_support' => true,
            'weight_based_pricing' => true,
            'multiple_pieces' => true,
            'address_validation' => false,
            'pickup_scheduling' => true,
            'real_time_tracking' => true,
            'bulk_creation' => true,
            'webhooks' => true,
        ],
        
        // 🆕 COUVERTURE GÉOGRAPHIQUE
        'coverage' => [
            'national' => true,
            'international' => false,
            'same_day' => false,
            'next_day' => true,
            'express' => true,
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
        
        // 🆕 CONFIGURATION POUR L'INTERFACE DE CRÉATION
        'config_fields' => [
            [
                'name' => 'username',
                'type' => 'text',
                'label' => 'Token d\'accès (x-access-token)',
                'required' => true,
                'help' => 'Token d\'authentification fourni par Mes Colis Express',
                'placeholder' => 'Votre token x-access-token...',
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
                'help' => 'Choisissez l\'environnement de test ou production',
            ],
        ],
        
        // 🆕 SERVICES SUPPORTÉS
        'supported_services' => [
            'create_shipment' => true,
            'create_pickup' => false, // Pas d'API pickup dédiée
            'track_shipment' => true,
            'webhooks' => false,
            'bulk_tracking' => true,
        ],
        
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
            'test_connection' => '/orders/GetOrder', // 🆕 Pour test avec barcode fictif
        ],
        
        // Mapping des champs de configuration dans delivery_configurations
        'config_mapping' => [
            'api_token' => 'username',     // Token API Mes Colis
            'unused' => 'password',        // Non utilisé (vide)
        ],
        
        // 🆕 MAPPING GOUVERNORATS ÉTENDU (compatible avec votre système)
        'governorate_mapping' => [
            // ID région → Nom complet
            1 => 'Tunis',
            2 => 'Ariana',
            3 => 'Ben Arous', 
            4 => 'La Mannouba',
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
            21 => 'Kébili',
            22 => 'Gabès',
            23 => 'Médenine',
            24 => 'Tataouine',
            
            // 🆕 MAPPING PAR NOM (fallback)
            'Tunis' => 'Tunis',
            'Ariana' => 'Ariana',
            'Ben Arous' => 'Ben Arous',
            'Manouba' => 'La Mannouba',
            'La Mannouba' => 'La Mannouba',
            'Nabeul' => 'Nabeul',
            'Zaghouan' => 'Zaghouan',
            'Bizerte' => 'Bizerte',
            'Béja' => 'Béja',
            'Beja' => 'Béja',
            'Jendouba' => 'Jendouba',
            'Kef' => 'Le Kef',
            'Le Kef' => 'Le Kef',
            'Siliana' => 'Siliana',
            'Kairouan' => 'Kairouan',
            'Kasserine' => 'Kasserine',
            'Sidi Bouzid' => 'Sidi Bouzid',
            'Sousse' => 'Sousse',
            'Monastir' => 'Monastir',
            'Mahdia' => 'Mahdia',
            'Sfax' => 'Sfax',
            'Gafsa' => 'Gafsa',
            'Tozeur' => 'Tozeur',
            'Kebili' => 'Kébili',
            'Kébili' => 'Kébili',
            'Gabès' => 'Gabès',
            'Gabes' => 'Gabès',
            'Medenine' => 'Médenine',
            'Médenine' => 'Médenine',
            'Tataouine' => 'Tataouine',
        ],
        
        // 🆕 GOUVERNORATS VALIDES POUR VALIDATION
        'valid_governorates' => [
            'Ariana', 'Ben Arous', 'Bizerte', 'Béja', 'Gabès', 'Gafsa', 'Jendouba',
            'Kairouan', 'Kasserine', 'Kébili', 'La Mannouba', 'Le Kef', 'Mahdia',
            'Monastir', 'Médenine', 'Nabeul', 'Sfax', 'Sidi Bouzid', 'Siliana',
            'Sousse', 'Tataouine', 'Tozeur', 'Tunis', 'Zaghouan'
        ],
        
        // Structure des données pour création de commande
        'shipment_structure' => [
            'required_fields' => [
                'recipient_name',
                'recipient_phone',
                'recipient_address', 
                'governorate_name',  // Nom complet du gouvernorat
                'city',              // = location
                'cod_amount',
                'content_description',
            ],
            'optional_fields' => [
                'recipient_phone_2',
                'weight',
                'dimensions',
                'pickup_date',
                'delivery_notes',
                'exchange',
                'open_order',
            ]
        ],
        
        // 🆕 MAPPING STATUTS MES COLIS DÉTAILLÉ → statuts internes
        'status_mapping' => [
            // Statuts Mes Colis (français)
            'En attente' => 'created',
            'En cours' => 'validated',
            'Au magasin' => 'picked_up_by_carrier',
            'Retour au dépôt' => 'in_return',
            'Livré' => 'delivered',
            'Retour client/agence' => 'in_return',
            'Retour définitif' => 'returned',
            'Retour reçu' => 'returned',
            'Retour payé' => 'returned',
            'Retour expéditeur' => 'in_return',
            'À vérifier' => 'anomaly',
            'Échange' => 'in_transit',
            'À enlever' => 'created',
            'Enlevé' => 'picked_up_by_carrier',
            'Non reçu' => 'delivery_failed',
            'Supprimé' => 'cancelled',
            'Inconnu' => 'unknown',
            
            // Statuts anglais (fallback)
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
            'exchange' => '0',
            'open_order' => '0',
        ],
        
        // Limites et contraintes
        'limits' => [
            'max_weight' => 25.0, // kg
            'max_cod_amount' => 3000.0, // TND
            'max_content_length' => 200,
            'max_address_length' => 400,
        ],
        
        // 🆕 FONCTIONNALITÉS DISPONIBLES
        'features' => [
            'cod_support' => true,
            'weight_based_pricing' => true,
            'multiple_pieces' => true,
            'address_validation' => false,
            'pickup_scheduling' => false, // Pas d'API pickup
            'real_time_tracking' => true,
            'bulk_creation' => false,
            'webhooks' => false,
        ],
        
        // 🆕 COUVERTURE GÉOGRAPHIQUE
        'coverage' => [
            'national' => true,
            'international' => false,
            'same_day' => false,
            'next_day' => true,
            'express' => true,
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
        'default_timeout' => 30,
        'max_retries' => 3,
        'retry_delay' => 2, // seconds
        'enable_webhooks' => env('CARRIERS_ENABLE_WEBHOOKS', true),
        'webhook_secret' => env('CARRIERS_WEBHOOK_SECRET'),
        'debug_mode' => env('CARRIERS_DEBUG_MODE', false),
        'cache_ttl' => 3600, // 1 hour
        
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
            'returned' => [
                'label' => 'Retourné',
                'color' => 'secondary',
                'icon' => 'fa-reply',
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
            'unknown' => [
                'label' => 'Statut inconnu',
                'color' => 'secondary',
                'icon' => 'fa-question',
                'order_status' => null,
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
            'validation_failed' => 'Validation des données échouée',
            'timeout' => 'Délai d\'attente dépassé',
            'service_unavailable' => 'Service temporairement indisponible',
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
        'shipment_created' => 'Colis créé chez le transporteur',
        'shipment_validated' => 'Colis validé et envoyé',
        'pickup_created' => 'Enlèvement créé',
        'pickup_validated' => 'Enlèvement validé',
        'picked_up_by_carrier' => 'Récupéré par le transporteur',
        'in_transit' => 'En transit',
        'delivery_attempted' => 'Tentative de livraison',
        'delivery_failed' => 'Échec de livraison',
        'livraison' => 'Livré',
        'in_return' => 'En retour',
        'returned' => 'Retourné',
        'delivery_anomaly' => 'Anomalie de livraison',
        'tracking_updated' => 'Suivi mis à jour',
        'carrier_connection_test' => 'Test de connexion transporteur',
        'carrier_configuration_updated' => 'Configuration transporteur mise à jour',
    ],
];