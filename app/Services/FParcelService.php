<?php

namespace App\Services\Shipping\Fparcel;

use App\Models\DeliveryConfiguration;
use App\Models\Order;
use App\Models\PickupAddress;
use App\Services\Shipping\ShippingServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FparcelService implements ShippingServiceInterface
{
    private DeliveryConfiguration $config;
    private string $baseUrl;
    private int $timeout = 30;

    public function __construct(DeliveryConfiguration $config)
    {
        $this->config = $config;
        $this->baseUrl = $config->environment === 'prod' 
            ? 'https://api.fparcel.com' 
            : 'https://test-api.fparcel.com';
    }

    public function createShipment(Order $order, ?PickupAddress $pickupAddress): array
    {
        $this->ensureValidToken();

        $payload = $this->buildShipmentPayload($order, $pickupAddress);
        
        Log::info('Fparcel createShipment payload', $payload);

        $response = Http::timeout($this->timeout)
            ->post($this->baseUrl . '/pos_create', $payload);

        if (!$response->successful()) {
            throw new \Exception('Erreur API Fparcel: ' . $response->body());
        }

        $data = $response->json();
        
        if (!isset($data['POSBARCODE'])) {
            throw new \Exception('Réponse invalide de l\'API Fparcel: ' . json_encode($data));
        }

        return [
            'pos_barcode' => $data['POSBARCODE'],
            'pos_reference' => $data['POSREFERENCE'] ?? null,
            'tracking_url' => $this->buildTrackingUrl($data['POSBARCODE']),
            'estimated_delivery' => $this->calculateEstimatedDelivery($order),
            'response_data' => $data,
        ];
    }

    public function getMassLabels(array $posBarcodes): array
    {
        $this->ensureValidToken();

        if (empty($posBarcodes)) {
            throw new \Exception('Aucun code-barres fourni');
        }

        $payload = [
            'USERNAME' => $this->config->username,
            'PASSWORD' => $this->getDecryptedPassword(),
            'TOKEN' => $this->config->token,
            'POSLIST' => implode(',', $posBarcodes),
        ];

        $response = Http::timeout($this->timeout)
            ->post($this->baseUrl . '/get_bl_en_masse', $payload);

        if (!$response->successful()) {
            throw new \Exception('Erreur API Fparcel get_bl_en_masse: ' . $response->body());
        }

        return [
            'labels' => $response->body(),
            'content_type' => 'application/pdf',
            'filename' => 'etiquettes_' . date('Y-m-d_H-i-s') . '.pdf',
        ];
    }

    public function trackShipment(string $trackingCode): ?array
    {
        $this->ensureValidToken();

        $payload = [
            'USERNAME' => $this->config->username,
            'PASSWORD' => $this->getDecryptedPassword(),
            'TOKEN' => $this->config->token,
            'POSBARCODE' => $trackingCode,
        ];

        $response = Http::timeout($this->timeout)
            ->post($this->baseUrl . '/tracking_position', $payload);

        if (!$response->successful()) {
            Log::warning('Fparcel tracking failed', [
                'tracking_code' => $trackingCode,
                'response' => $response->body(),
            ]);
            return null;
        }

        $data = $response->json();
        
        if (!isset($data['EVENT_ID'])) {
            return null;
        }

        return [
            'status' => $data['EVENT_ID'],
            'status_label' => $this->getStatusLabel($data['EVENT_ID']),
            'carrier_status_label' => $data['EVENT_LABEL'] ?? null,
            'date' => $data['EVENT_DATE'] ?? null,
            'location' => $data['EVENT_LOCATION'] ?? null,
            'tracking_url' => $this->buildTrackingUrl($trackingCode),
            'raw_data' => $data,
        ];
    }

    public function getToken(): array
    {
        $payload = [
            'USERNAME' => $this->config->username,
            'PASSWORD' => $this->getDecryptedPassword(),
        ];

        $response = Http::timeout($this->timeout)
            ->post($this->baseUrl . '/get_token', $payload);

        if (!$response->successful()) {
            throw new \Exception('Échec de l\'authentification Fparcel: ' . $response->body());
        }

        $data = $response->json();
        
        if (!isset($data['TOKEN'])) {
            throw new \Exception('Réponse de token invalide de Fparcel: ' . json_encode($data));
        }

        return [
            'token' => $data['TOKEN'],
            'expires_at' => now()->addHours(1), // Les tokens expirent généralement après 1 heure
        ];
    }

    public function supportsPickupAddressSelection(): bool
    {
        return true;
    }

    public function getPaymentMethods(): array
    {
        return Cache::remember(
            "fparcel_payment_methods_{$this->config->id}",
            3600,
            function () {
                $this->ensureValidToken();

                $payload = [
                    'USERNAME' => $this->config->username,
                    'PASSWORD' => $this->getDecryptedPassword(),
                    'TOKEN' => $this->config->token,
                ];

                $response = Http::timeout($this->timeout)
                    ->post($this->baseUrl . '/mr_list', $payload);

                if (!$response->successful()) {
                    Log::warning('Fparcel payment methods fetch failed: ' . $response->body());
                    return [];
                }

                return $response->json() ?? [];
            }
        );
    }

    public function getDropPoints(?string $city = null): array
    {
        $cacheKey = "fparcel_drop_points_{$this->config->id}" . ($city ? "_{$city}" : '');
        
        return Cache::remember($cacheKey, 3600, function () use ($city) {
            $this->ensureValidToken();

            $payload = [
                'USERNAME' => $this->config->username,
                'PASSWORD' => $this->getDecryptedPassword(),
                'TOKEN' => $this->config->token,
            ];

            if ($city) {
                $payload['VILLE'] = $city;
            }

            $response = Http::timeout($this->timeout)
                ->post($this->baseUrl . '/droppoint_list', $payload);

            if (!$response->successful()) {
                Log::warning('Fparcel drop points fetch failed: ' . $response->body());
                return [];
            }

            return $response->json() ?? [];
        });
    }

    public function getAnomalyReasons(): array
    {
        return Cache::remember(
            "fparcel_anomaly_reasons_{$this->config->id}",
            3600,
            function () {
                $this->ensureValidToken();

                $payload = [
                    'USERNAME' => $this->config->username,
                    'PASSWORD' => $this->getDecryptedPassword(),
                    'TOKEN' => $this->config->token,
                ];

                $response = Http::timeout($this->timeout)
                    ->post($this->baseUrl . '/motif_ano_list', $payload);

                if (!$response->successful()) {
                    Log::warning('Fparcel anomaly reasons fetch failed: ' . $response->body());
                    return [];
                }

                return $response->json() ?? [];
            }
        );
    }

    public function validateShipmentData(array $data): array
    {
        $errors = [];

        // Validation des champs obligatoires
        $requiredFields = [
            'customer_name' => 'Nom du client',
            'customer_phone' => 'Téléphone du client',
            'customer_address' => 'Adresse du client',
            'total_price' => 'Prix total',
        ];

        foreach ($requiredFields as $field => $label) {
            if (empty($data[$field])) {
                $errors[] = "Le champ '{$label}' est obligatoire";
            }
        }

        // Validation du téléphone
        if (!empty($data['customer_phone'])) {
            $phone = preg_replace('/[^0-9]/', '', $data['customer_phone']);
            if (strlen($phone) < 8) {
                $errors[] = 'Le numéro de téléphone doit contenir au moins 8 chiffres';
            }
        }

        // Validation du prix
        if (!empty($data['total_price']) && !is_numeric($data['total_price'])) {
            $errors[] = 'Le prix total doit être numérique';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    public function calculateShippingCost(array $shipmentData): ?float
    {
        // Fparcel ne fournit pas d'API de calcul de coût dans la documentation fournie
        // Retourner null pour indiquer que cette fonctionnalité n'est pas disponible
        return null;
    }

    public function getCarrierInfo(): array
    {
        return [
            'name' => 'Fparcel',
            'display_name' => 'Fparcel Tunisia',
            'website' => 'https://fparcel.com',
            'support_email' => 'support@fparcel.com',
            'tracking_url_template' => 'https://tracking.fparcel.com/{tracking_code}',
            'features' => [
                'pickup_address_selection' => true,
                'mass_labels' => true,
                'tracking' => true,
                'drop_points' => true,
                'cod' => true,
                'insurance' => false,
                'scheduling' => true,
            ],
            'environments' => [
                'test' => 'https://test-api.fparcel.com',
                'prod' => 'https://api.fparcel.com',
            ],
        ];
    }

    /**
     * S'assurer d'avoir un token valide
     */
    private function ensureValidToken(): void
    {
        if (!$this->config->hasValidToken()) {
            $tokenData = $this->getToken();
            $this->config->update([
                'token' => $tokenData['token'],
                'expires_at' => $tokenData['expires_at'],
            ]);
        }
    }

    /**
     * Construire le payload pour la création d'expédition
     */
    private function buildShipmentPayload(Order $order, ?PickupAddress $pickupAddress): array
    {
        $payload = [
            'USERNAME' => $this->config->username,
            'PASSWORD' => $this->getDecryptedPassword(),
            'TOKEN' => $this->config->token,
            
            // Informations du destinataire
            'DES_CONTACT_NOM' => $order->customer_name,
            'DES_TELEPHONE' => $this->formatPhoneNumber($order->customer_phone),
            'DES_ADRESSE' => $order->customer_address,
            'DES_GOUVERNORAT' => $order->customer_governorate,
            'DES_VILLE' => $order->customer_city,
            
            // Informations du colis
            'VALEUR' => $order->total_price,
            'NB_PIECE' => 1,
            'POIDS' => $this->calculateWeight($order),
            'MR_CODE' => $this->getDefaultPaymentMethod(),
        ];

        // Ajouter les informations d'adresse d'enlèvement si fournie
        if ($pickupAddress) {
            $payload = array_merge($payload, [
                'ENL_CONTACT_NOM' => $pickupAddress->contact_name,
                'ENL_ADRESSE' => $pickupAddress->address,
                'ENL_TELEPHONE' => $pickupAddress->phone,
                'ENL_CODE_POSTAL' => $pickupAddress->postal_code,
                'ENL_VILLE' => $pickupAddress->city,
            ]);
        }

        // Ajouter les paramètres administrateur
        $adminSettings = $this->getAdminDeliverySettings();
        $payload = array_merge($payload, $adminSettings);

        return $payload;
    }

    /**
     * Obtenir le mot de passe déchiffré
     */
    private function getDecryptedPassword(): string
    {
        return $this->config->decrypted_password ?? $this->config->password;
    }

    /**
     * Formater le numéro de téléphone
     */
    private function formatPhoneNumber(string $phone): string
    {
        // Nettoyer le numéro de téléphone
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        
        // Ajouter l'indicatif tunisien si nécessaire
        if (strlen($cleaned) === 8) {
            $cleaned = '216' . $cleaned;
        }
        
        return $cleaned;
    }

    /**
     * Calculer le poids approximatif
     */
    private function calculateWeight(Order $order): float
    {
        // Logique de calcul du poids basée sur les articles de la commande
        $totalWeight = 0;
        
        foreach ($order->orderItems as $item) {
            // Poids par défaut ou poids du produit si disponible
            $itemWeight = $item->product->weight ?? 0.5; // 500g par défaut
            $totalWeight += $itemWeight * $item->quantity;
        }
        
        return max($totalWeight, 0.1); // Minimum 100g
    }

    /**
     * Obtenir la méthode de paiement par défaut
     */
    private function getDefaultPaymentMethod(): string
    {
        return Cache::get("admin_setting_{$this->config->admin_id}_delivery_param_default_mr_code", 'ESP');
    }

    /**
     * Obtenir les paramètres de livraison de l'administrateur
     */
    private function getAdminDeliverySettings(): array
    {
        $adminId = $this->config->admin_id;
        
        return [
            'POS_VALID' => Cache::get("admin_setting_{$adminId}_delivery_param_default_pos_valid", '1'),
            'TIME_FROM' => Cache::get("admin_setting_{$adminId}_delivery_param_default_time_from", '08:00'),
            'TIME_TO' => Cache::get("admin_setting_{$adminId}_delivery_param_default_time_to", '18:00'),
            'POS_ALLOW_OPEN' => Cache::get("admin_setting_{$adminId}_delivery_param_default_pos_allow_open", '0'),
            'POS_LINK_IMG' => Cache::get("admin_setting_{$adminId}_delivery_param_default_pos_link_img", ''),
        ];
    }

    /**
     * Construire l'URL de tracking
     */
    private function buildTrackingUrl(string $trackingCode): string
    {
        return "https://tracking.fparcel.com/{$trackingCode}";
    }

    /**
     * Calculer la date de livraison estimée
     */
    private function calculateEstimatedDelivery(Order $order): ?string
    {
        // Logique simple d'estimation : 2-3 jours ouvrables
        $estimatedDays = 2;
        
        // Ajouter un jour si c'est une région éloignée
        $remoteGovernorates = ['Tataouine', 'Kébili', 'Tozeur', 'Gafsa'];
        if (in_array($order->customer_governorate, $remoteGovernorates)) {
            $estimatedDays = 3;
        }
        
        return now()->addWeekdays($estimatedDays)->format('Y-m-d');
    }

    /**
     * Obtenir le libellé du statut
     */
    private function getStatusLabel(string $eventId): string
    {
        $statusLabels = [
            '1' => 'Créé',
            '3' => 'Récupéré par le transporteur',
            '6' => 'En transit',
            '7' => 'Livré',
            '9' => 'En retour',
            '11' => 'Anomalie',
        ];

        return $statusLabels[$eventId] ?? 'Statut inconnu';
    }
}