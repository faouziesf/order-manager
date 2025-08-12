<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Services\Delivery\SimpleCarrierFactory;
use App\Services\Delivery\Contracts\CarrierServiceException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Pickup extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'carrier_slug',
        'delivery_configuration_id',
        'status',
        'pickup_date',
        'validated_at',
    ];

    protected $casts = [
        'pickup_date' => 'date',
        'validated_at' => 'datetime',
    ];

    // ========================================
    // CONSTANTES DE STATUTS
    // ========================================

    const STATUS_DRAFT = 'draft';
    const STATUS_VALIDATED = 'validated';
    const STATUS_PICKED_UP = 'picked_up';
    const STATUS_PROBLEM = 'problem';

    // ========================================
    // RELATIONS
    // ========================================

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function deliveryConfiguration()
    {
        return $this->belongsTo(DeliveryConfiguration::class);
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }

    public function orders()
    {
        return $this->hasManyThrough(Order::class, Shipment::class, 'pickup_id', 'id', 'id', 'order_id');
    }

    // ========================================
    // ACCESSORS CORRIGÉS
    // ========================================

    /**
     * 🔧 CORRECTION : Vérifier si le pickup peut être validé - VERSION CORRIGÉE
     */
    public function getCanBeValidatedAttribute()
    {
        // Vérifications de base
        if ($this->status !== self::STATUS_DRAFT) {
            return false;
        }

        // Vérifier qu'il y a des shipments
        if (!$this->shipments()->exists()) {
            return false;
        }

        // 🆕 CORRECTION : Charger la relation si pas déjà fait et vérifier proprement
        if (!$this->relationLoaded('deliveryConfiguration')) {
            $this->load('deliveryConfiguration');
        }

        // Vérifier que la configuration existe
        if (!$this->deliveryConfiguration) {
            Log::warning("❌ [PICKUP CAN_BE_VALIDATED] Pickup #{$this->id} n'a pas de configuration", [
                'pickup_id' => $this->id,
                'delivery_configuration_id' => $this->delivery_configuration_id
            ]);
            return false;
        }

        // Vérifier que la configuration est active
        if (!$this->deliveryConfiguration->is_active) {
            Log::warning("❌ [PICKUP CAN_BE_VALIDATED] Configuration inactive", [
                'pickup_id' => $this->id,
                'config_id' => $this->deliveryConfiguration->id
            ]);
            return false;
        }

        // 🆕 CORRECTION : Utiliser une méthode simplifiée pour vérifier la validité
        if (!$this->isConfigurationValidForApi()) {
            Log::warning("❌ [PICKUP CAN_BE_VALIDATED] Configuration invalide pour API", [
                'pickup_id' => $this->id,
                'config_id' => $this->deliveryConfiguration->id
            ]);
            return false;
        }

        return true;
    }

    /**
     * 🆕 NOUVELLE MÉTHODE : Vérifier si la configuration est valide pour les appels API
     */
    private function isConfigurationValidForApi(): bool
    {
        $config = $this->deliveryConfiguration;
        
        if (!$config) {
            return false;
        }

        // Pour JAX Delivery : username (numéro de compte) + password (token) requis
        if ($config->carrier_slug === 'jax_delivery') {
            return !empty($config->username) && !empty($config->password);
        }

        // Pour Mes Colis : seulement username (token) requis
        if ($config->carrier_slug === 'mes_colis') {
            return !empty($config->username);
        }

        // Pour d'autres transporteurs futurs
        return !empty($config->password) || !empty($config->username);
    }

    /**
     * Obtenir le nom du transporteur
     */
    public function getCarrierNameAttribute()
    {
        $carriers = config('carriers');
        return $carriers[$this->carrier_slug]['name'] ?? ucfirst(str_replace('_', ' ', $this->carrier_slug));
    }

    /**
     * Obtenir la couleur du badge de statut
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'secondary',
            self::STATUS_VALIDATED => 'success',
            self::STATUS_PICKED_UP => 'info',
            self::STATUS_PROBLEM => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Obtenir le libellé du statut
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'Brouillon',
            self::STATUS_VALIDATED => 'Validé',
            self::STATUS_PICKED_UP => 'Récupéré',
            self::STATUS_PROBLEM => 'Problème',
            default => 'Inconnu',
        };
    }

    public function getCanBeEditedAttribute()
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function getCanBeDeletedAttribute()
    {
        return $this->status === self::STATUS_DRAFT;
    }

    // ========================================
    // MÉTHODE VALIDATE CORRIGÉE ET SIMPLIFIÉE
    // ========================================

    /**
     * 🔧 CORRECTION : Valider le pickup - VERSION ULTRA SIMPLIFIÉE
     */
    public function validate()
    {
        Log::info('🚀 [PICKUP VALIDATE] Début validation', [
            'pickup_id' => $this->id,
            'carrier' => $this->carrier_slug,
            'can_be_validated' => $this->can_be_validated,
        ]);

        // Vérifications préliminaires
        if (!$this->can_be_validated) {
            $error = 'Ce pickup ne peut pas être validé';
            Log::error("❌ [PICKUP VALIDATE] {$error}", [
                'pickup_id' => $this->id,
                'status' => $this->status,
                'has_shipments' => $this->shipments()->exists(),
                'has_config' => !!$this->deliveryConfiguration,
                'config_active' => $this->deliveryConfiguration?->is_active,
            ]);
            throw new \Exception($error);
        }

        try {
            DB::beginTransaction();

            $successfulShipments = 0;
            $errors = [];
            $trackingNumbers = [];

            // 🆕 NOUVELLE MÉTHODE : Préparer la configuration selon le transporteur
            $apiConfig = $this->prepareApiConfig();
            
            Log::info('✅ [PICKUP VALIDATE] Configuration préparée', [
                'carrier' => $this->carrier_slug,
                'has_username' => !empty($apiConfig['username']),
                'has_api_key' => !empty($apiConfig['api_key']),
            ]);

            // Créer le service transporteur
            $carrierService = SimpleCarrierFactory::create($this->carrier_slug, $apiConfig);

            // Traiter chaque shipment
            foreach ($this->shipments as $shipment) {
                try {
                    Log::info('📦 [PICKUP VALIDATE] Traitement shipment', [
                        'shipment_id' => $shipment->id,
                        'order_id' => $shipment->order_id,
                    ]);

                    // 🆕 DONNÉES SIMPLIFIÉES pour l'API
                    $shipmentData = $this->prepareShipmentData($shipment);

                    // Appel à l'API du transporteur
                    $result = $carrierService->createShipment($shipmentData);

                    if ($result['success'] && !empty($result['tracking_number'])) {
                        // Mettre à jour le shipment
                        $shipment->update([
                            'status' => 'validated',
                            'pos_barcode' => $result['tracking_number'],
                            'pos_reference' => $result['tracking_number'],
                            'carrier_response' => $result['response'] ?? null,
                            'carrier_last_status_update' => now(),
                        ]);

                        $trackingNumbers[] = $result['tracking_number'];
                        $successfulShipments++;

                        Log::info('✅ [PICKUP VALIDATE] Shipment envoyé avec succès', [
                            'shipment_id' => $shipment->id,
                            'tracking_number' => $result['tracking_number'],
                        ]);

                        // Mettre à jour la commande si elle existe
                        if ($shipment->order) {
                            $shipment->order->markAsShipped(
                                $result['tracking_number'],
                                $this->carrier_name,
                                "Expédié via pickup #{$this->id}"
                            );
                        }

                    } else {
                        $errorMsg = "Erreur API pour shipment #{$shipment->id}: " . ($result['error'] ?? 'Réponse invalide');
                        $errors[] = $errorMsg;
                        Log::error('❌ [PICKUP VALIDATE] ' . $errorMsg, ['result' => $result]);
                    }

                } catch (\Exception $e) {
                    $errorMsg = "Erreur shipment #{$shipment->id}: {$e->getMessage()}";
                    $errors[] = $errorMsg;
                    Log::error('❌ [PICKUP VALIDATE] ' . $errorMsg, [
                        'shipment_id' => $shipment->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Mettre à jour le statut du pickup
            if ($successfulShipments > 0) {
                $this->update([
                    'status' => self::STATUS_VALIDATED,
                    'validated_at' => now(),
                ]);

                DB::commit();

                Log::info('🎉 [PICKUP VALIDATE] Pickup validé avec succès', [
                    'pickup_id' => $this->id,
                    'successful_shipments' => $successfulShipments,
                    'total_shipments' => $this->shipments->count(),
                ]);

                return [
                    'success' => true,
                    'successful_shipments' => $successfulShipments,
                    'total_shipments' => $this->shipments->count(),
                    'errors' => $errors,
                    'tracking_numbers' => $trackingNumbers,
                ];

            } else {
                $this->update(['status' => self::STATUS_PROBLEM]);
                DB::rollBack();

                return [
                    'success' => false,
                    'errors' => $errors,
                    'successful_shipments' => 0,
                    'total_shipments' => $this->shipments->count(),
                ];
            }

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('❌ [PICKUP VALIDATE] Erreur fatale', [
                'pickup_id' => $this->id,
                'error' => $e->getMessage(),
            ]);
            
            $this->update(['status' => self::STATUS_PROBLEM]);
            throw $e;
        }
    }

    /**
     * 🆕 NOUVELLE MÉTHODE : Préparer la configuration API selon le transporteur
     */
    private function prepareApiConfig(): array
    {
        $config = $this->deliveryConfiguration;

        if ($config->carrier_slug === 'jax_delivery') {
            return [
                'api_key' => $config->password, // Token JWT
                'username' => $config->username, // Numéro de compte
                'environment' => $config->environment ?? 'test',
            ];
        }

        if ($config->carrier_slug === 'mes_colis') {
            return [
                'api_key' => $config->username, // Token API
                'environment' => $config->environment ?? 'test',
            ];
        }

        // Configuration générique
        return [
            'api_key' => $config->password ?? $config->username,
            'username' => $config->username,
            'environment' => $config->environment ?? 'test',
        ];
    }

    /**
     * 🆕 NOUVELLE MÉTHODE : Préparer les données du shipment pour l'API
     */
    private function prepareShipmentData($shipment): array
    {
        $recipientInfo = $shipment->recipient_info ?: [];
        
        return [
            'external_reference' => "PICKUP_{$this->id}_SHIP_{$shipment->id}",
            'recipient_name' => $recipientInfo['name'] ?? 'Client',
            'recipient_phone' => $recipientInfo['phone'] ?? '',
            'recipient_phone_2' => $recipientInfo['phone_2'] ?? '',
            'recipient_address' => $recipientInfo['address'] ?? 'Adresse non renseignée',
            'recipient_governorate' => $recipientInfo['governorate'] ?? 'Tunis',
            'recipient_city' => $recipientInfo['city'] ?? 'Tunis',
            'cod_amount' => $shipment->cod_amount ?: 0,
            'content_description' => $shipment->content_description ?: "Commande #{$shipment->order_id}",
            'weight' => $shipment->weight ?: 1.0,
            'notes' => "Pickup #{$this->id}",
        ];
    }

    // ========================================
    // AUTRES MÉTHODES (INCHANGÉES)
    // ========================================

    public function markAsPickedUp()
    {
        if ($this->status !== self::STATUS_VALIDATED) {
            throw new \Exception('Seuls les pickups validés peuvent être marqués comme récupérés');
        }

        $this->update(['status' => self::STATUS_PICKED_UP]);
        $this->shipments()->update(['status' => 'picked_up_by_carrier']);

        foreach ($this->orders as $order) {
            $order->updateDeliveryStatus('en_transit', null, null, "Pickup #{$this->id} récupéré par le transporteur");
        }
    }

    public static function createForCarrier($adminId, $carrierSlug, $configurationId, $pickupDate = null)
    {
        return static::create([
            'admin_id' => $adminId,
            'carrier_slug' => $carrierSlug,
            'delivery_configuration_id' => $configurationId,
            'status' => self::STATUS_DRAFT,
            'pickup_date' => $pickupDate ?: now()->addDay()->format('Y-m-d'),
        ]);
    }
}