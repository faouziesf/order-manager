<?php

namespace App\Services\Delivery;

use App\Services\Delivery\Contracts\CarrierServiceInterface;
use App\Services\Delivery\Contracts\CarrierServiceException;
use Illuminate\Support\Facades\Log;

/**
 * Factory corrigée pour créer les services de transporteurs
 */
class SimpleCarrierFactory
{
    /**
     * 🆕 CORRECTION : Créer un service transporteur avec validation de config
     */
    public static function create(string $carrierSlug, array $config): CarrierServiceInterface
    {
        Log::info('🏭 [FACTORY] Création service transporteur', [
            'carrier' => $carrierSlug,
            'config_keys' => array_keys($config),
            'has_api_token' => !empty($config['api_token']),
        ]);

        // Valider la configuration de base
        self::validateConfig($carrierSlug, $config);

        switch ($carrierSlug) {
            case 'jax_delivery':
                // Valider config spécifique JAX
                self::validateJaxConfig($config);
                return new JaxDeliveryService($config);
                
            case 'mes_colis':
                // Valider config spécifique Mes Colis
                self::validateMesColisConfig($config);
                return new MesColisService($config);
                
            default:
                throw new CarrierServiceException("Transporteur non supporté: {$carrierSlug}");
        }
    }

    /**
     * 🆕 NOUVELLE MÉTHODE : Validation générale de configuration
     */
    protected static function validateConfig(string $carrierSlug, array $config): void
    {
        if (empty($config)) {
            throw new CarrierServiceException("Configuration vide pour le transporteur {$carrierSlug}");
        }

        if (empty($config['api_token'])) {
            throw new CarrierServiceException("Token API manquant pour le transporteur {$carrierSlug}");
        }

        Log::debug('✅ [FACTORY] Configuration de base validée', [
            'carrier' => $carrierSlug,
            'token_length' => strlen($config['api_token']),
        ]);
    }

    /**
     * 🆕 NOUVELLE MÉTHODE : Validation spécifique JAX
     */
    protected static function validateJaxConfig(array $config): void
    {
        if (empty($config['username'])) {
            throw new CarrierServiceException("Numéro de compte JAX manquant (username requis)");
        }

        // Vérifier que le token ressemble à un JWT
        $token = $config['api_token'];
        if (substr_count($token, '.') !== 2) {
            Log::warning('⚠️ [FACTORY] Token JAX ne ressemble pas à un JWT', [
                'token_preview' => substr($token, 0, 20) . '...',
                'dots_count' => substr_count($token, '.'),
            ]);
        }

        Log::debug('✅ [FACTORY] Configuration JAX validée', [
            'username' => $config['username'],
            'token_preview' => substr($token, 0, 20) . '...',
        ]);
    }

    /**
     * 🆕 NOUVELLE MÉTHODE : Validation spécifique Mes Colis
     */
    protected static function validateMesColisConfig(array $config): void
    {
        $token = $config['api_token'];
        
        // Vérifier que le token a une longueur raisonnable
        if (strlen($token) < 10) {
            throw new CarrierServiceException("Token Mes Colis trop court (minimum 10 caractères)");
        }

        Log::debug('✅ [FACTORY] Configuration Mes Colis validée', [
            'token_length' => strlen($token),
            'token_preview' => substr($token, 0, 8) . '...',
        ]);
    }

    /**
     * Obtenir les transporteurs supportés
     */
    public static function getSupportedCarriers(): array
    {
        return [
            'jax_delivery' => 'JAX Delivery',
            'mes_colis' => 'Mes Colis Express',
        ];
    }

    /**
     * Vérifier si un transporteur est supporté
     */
    public static function isSupported(string $carrierSlug): bool
    {
        return array_key_exists($carrierSlug, self::getSupportedCarriers());
    }

    /**
     * 🆕 NOUVELLE MÉTHODE : Obtenir les exigences de configuration pour un transporteur
     */
    public static function getConfigRequirements(string $carrierSlug): array
    {
        switch ($carrierSlug) {
            case 'jax_delivery':
                return [
                    'required_fields' => ['api_token', 'username'],
                    'field_descriptions' => [
                        'api_token' => 'Token JWT fourni par JAX Delivery',
                        'username' => 'Numéro de compte JAX (ex: 2304)',
                    ],
                    'validation_rules' => [
                        'api_token' => 'JWT avec 3 parties séparées par des points',
                        'username' => 'Numéro de compte numérique',
                    ],
                ];
                
            case 'mes_colis':
                return [
                    'required_fields' => ['api_token'],
                    'field_descriptions' => [
                        'api_token' => 'Token d\'accès fourni par Mes Colis Express',
                    ],
                    'validation_rules' => [
                        'api_token' => 'Chaîne alphanumérique de minimum 10 caractères',
                    ],
                ];
                
            default:
                return [
                    'required_fields' => [],
                    'field_descriptions' => [],
                    'validation_rules' => [],
                ];
        }
    }

    /**
     * 🆕 NOUVELLE MÉTHODE : Test rapide de factory avec configurations d'exemple
     */
    public static function testFactory(): array
    {
        $results = [];
        
        foreach (self::getSupportedCarriers() as $slug => $name) {
            try {
                // Configuration de test minimale
                $testConfig = self::getTestConfig($slug);
                $service = self::create($slug, $testConfig);
                
                $results[$slug] = [
                    'success' => true,
                    'service_class' => get_class($service),
                    'name' => $name,
                ];
                
            } catch (\Exception $e) {
                $results[$slug] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                    'name' => $name,
                ];
            }
        }
        
        return $results;
    }

    /**
     * 🆕 NOUVELLE MÉTHODE : Obtenir une configuration de test pour un transporteur
     */
    protected static function getTestConfig(string $carrierSlug): array
    {
        switch ($carrierSlug) {
            case 'jax_delivery':
                return [
                    'api_token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.test.token',
                    'username' => 'TEST_ACCOUNT',
                    'environment' => 'test',
                ];
                
            case 'mes_colis':
                return [
                    'api_token' => 'TEST_TOKEN_MESCOLIS_123',
                    'environment' => 'test',
                ];
                
            default:
                return [
                    'api_token' => 'TEST_TOKEN',
                    'environment' => 'test',
                ];
        }
    }
}