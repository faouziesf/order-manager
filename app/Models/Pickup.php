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
            Log::debug('❌ [PICKUP CAN_BE_VALIDATED] Statut incorrect', [
                'pickup_id' => $this->id,
                'current_status' => $this->status,
                'required_status' => self::STATUS_DRAFT,
            ]);
            return false;
        }

        // Vérifier qu'il y a des shipments
        if (!$this->shipments()->exists()) {
            Log::debug('❌ [PICKUP CAN_BE_VALIDATED] Aucun shipment', [
                'pickup_id' => $this->id,
            ]);
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

        Log::debug('✅ [PICKUP CAN_BE_VALIDATED] Pickup peut être validé', [
            'pickup_id' => $this->id,
            'carrier' => $this->carrier_slug,
        ]);

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
            $valid = !empty($config->username) && !empty($config->password);
            Log::debug('🔍 [PICKUP CONFIG CHECK] JAX Delivery', [
                'has_username' => !empty($config->username),
                'has_password' => !empty($config->password),
                'valid' => $valid,
            ]);
            return $valid;
        }

        // Pour Mes Colis : seulement username (token) requis
        if ($config->carrier_slug === 'mes_colis') {
            $valid = !empty($config->username);
            Log::debug('🔍 [PICKUP CONFIG CHECK] Mes Colis', [
                'has_username' => !empty($config->username),
                'valid' => $valid,
            ]);
            return $valid;
        }

        // Pour d'autres transporteurs futurs
        $valid = !empty($config->password) || !empty($config->username);
        Log::debug('🔍 [PICKUP CONFIG CHECK] Générique', [
            'has_username' => !empty($config->username),
            'has_password' => !empty($config->password),
            'valid' => $valid,
        ]);
        
        return $valid;
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

    /**
     * Vérifier si le pickup peut être marqué comme récupéré
     */
    public function getCanBePickedUpAttribute()
    {
        return $this->status === self::STATUS_VALIDATED;
    }

    /**
     * Obtenir le nombre total de colis
     */
    public function getTotalShipmentsAttribute()
    {
        return $this->shipments()->count();
    }

    /**
     * Obtenir le poids total
     */
    public function getTotalWeightAttribute()
    {
        return $this->shipments()->sum('weight') ?: 0;
    }

    /**
     * Obtenir le montant COD total
     */
    public function getTotalCodAmountAttribute()
    {
        return $this->shipments()->sum('cod_amount') ?: 0;
    }

    /**
     * Obtenir le nombre total de pièces
     */
    public function getTotalPiecesAttribute()
    {
        return $this->shipments()->sum('nb_pieces') ?: 0;
    }

    // ========================================
    // MÉTHODE VALIDATE CORRIGÉE ET SIMPLIFIÉE
    // ========================================

    /**
     * 🔧 CORRECTION COMPLÈTE : Valider le pickup et créer les colis dans le compte transporteur
     */
    public function validate()
    {
        Log::info('🚀 [PICKUP VALIDATE] Début validation pickup', [
            'pickup_id' => $this->id,
            'carrier' => $this->carrier_slug,
            'can_be_validated' => $this->can_be_validated,
            'shipments_count' => $this->shipments()->count(),
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

        if ($this->shipments->isEmpty()) {
            throw new \Exception('Aucune expédition à valider');
        }

        if (!$this->deliveryConfiguration || !$this->deliveryConfiguration->is_active) {
            throw new \Exception('Configuration transporteur inactive ou manquante');
        }

        try {
            DB::beginTransaction();

            $successfulShipments = 0;
            $errors = [];
            $trackingNumbers = [];

            // 🆕 CORRECTION : Préparer la configuration API selon le transporteur
            $apiConfig = $this->prepareCarrierApiConfig();
            
            Log::info('✅ [PICKUP VALIDATE] Configuration API préparée', [
                'carrier' => $this->carrier_slug,
                'config_keys' => array_keys($apiConfig),
                'has_token' => !empty($apiConfig['api_token']),
                'has_username' => !empty($apiConfig['username']),
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

                    // 🆕 CORRECTION : Préparer les données selon le format API requis
                    $shipmentData = $this->prepareShipmentDataForApi($shipment);

                    Log::info('📤 [PICKUP VALIDATE] Données shipment préparées', [
                        'shipment_id' => $shipment->id,
                        'recipient_name' => $shipmentData['recipient_name'],
                        'cod_amount' => $shipmentData['cod_amount'],
                        'governorate' => $shipmentData['recipient_governorate'],
                    ]);

                    // 🔥 APPEL CRITIQUE : Créer le colis dans le compte transporteur
                    $result = $carrierService->createShipment($shipmentData);

                    if ($result['success'] && !empty($result['tracking_number'])) {
                        // Mettre à jour le shipment avec le numéro de suivi
                        $shipment->update([
                            'status' => 'validated',
                            'pos_barcode' => $result['tracking_number'],
                            'pos_reference' => $result['tracking_number'],
                            'carrier_response' => $result['response'] ?? null,
                            'carrier_last_status_update' => now(),
                        ]);

                        $trackingNumbers[] = $result['tracking_number'];
                        $successfulShipments++;

                        Log::info('✅ [PICKUP VALIDATE] Colis créé avec succès dans le compte transporteur', [
                            'shipment_id' => $shipment->id,
                            'tracking_number' => $result['tracking_number'],
                            'carrier' => $this->carrier_slug,
                        ]);

                        // Mettre à jour la commande si elle existe
                        if ($shipment->order) {
                            $shipment->order->markAsShipped(
                                $result['tracking_number'],
                                $this->carrier_name,
                                "Expédié via pickup #{$this->id} - Transporteur: {$this->carrier_name}"
                            );
                        }

                    } else {
                        $errorMsg = "Erreur API pour shipment #{$shipment->id}: " . ($result['error'] ?? 'Réponse invalide du transporteur');
                        $errors[] = $errorMsg;
                        Log::error('❌ [PICKUP VALIDATE] ' . $errorMsg, [
                            'shipment_id' => $shipment->id,
                            'carrier_response' => $result,
                        ]);
                    }

                } catch (CarrierServiceException $e) {
                    $errorMsg = "Erreur transporteur shipment #{$shipment->id}: {$e->getMessage()}";
                    $errors[] = $errorMsg;
                    Log::error('❌ [PICKUP VALIDATE] ' . $errorMsg, [
                        'shipment_id' => $shipment->id,
                        'carrier_response' => $e->getCarrierResponse(),
                    ]);
                } catch (\Exception $e) {
                    $errorMsg = "Erreur technique shipment #{$shipment->id}: {$e->getMessage()}";
                    $errors[] = $errorMsg;
                    Log::error('❌ [PICKUP VALIDATE] ' . $errorMsg, [
                        'shipment_id' => $shipment->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
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
                    'tracking_numbers' => $trackingNumbers,
                    'errors_count' => count($errors),
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

                Log::error('❌ [PICKUP VALIDATE] Aucun shipment validé', [
                    'pickup_id' => $this->id,
                    'errors' => $errors,
                ]);

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
                'trace' => $e->getTraceAsString(),
            ]);
            
            $this->update(['status' => self::STATUS_PROBLEM]);
            throw $e;
        }
    }

    /**
     * 🆕 NOUVELLE MÉTHODE : Préparer la configuration API selon le transporteur
     */
    private function prepareCarrierApiConfig(): array
    {
        $config = $this->deliveryConfiguration;

        Log::info('🔧 [PICKUP] Préparation config API', [
            'carrier' => $config->carrier_slug,
            'has_username' => !empty($config->username),
            'has_password' => !empty($config->password),
            'environment' => $config->environment,
        ]);

        if ($config->carrier_slug === 'jax_delivery') {
            // JAX : username = numéro de compte, password = token JWT
            return [
                'api_token' => $config->password,     // Token JWT
                'username' => $config->username,      // Numéro de compte (ex: 2304)
                'account_number' => $config->username, // Alias pour clarté
                'environment' => $config->environment ?? 'test',
            ];
        }

        if ($config->carrier_slug === 'mes_colis') {
            // Mes Colis : username = token API
            return [
                'api_token' => $config->username,     // Token API
                'environment' => $config->environment ?? 'test',
            ];
        }

        // Configuration générique pour futurs transporteurs
        return [
            'api_token' => $config->password ?? $config->username,
            'username' => $config->username,
            'environment' => $config->environment ?? 'test',
        ];
    }

    /**
     * 🆕 NOUVELLE MÉTHODE : Préparer les données shipment pour l'API transporteur
     */
    private function prepareShipmentDataForApi($shipment): array
    {
        $recipientInfo = $shipment->recipient_info ?: [];
        
        // Valeurs par défaut sécurisées
        $data = [
            'external_reference' => "PICKUP_{$this->id}_SHIP_{$shipment->id}",
            'recipient_name' => $recipientInfo['name'] ?? 'Client',
            'recipient_phone' => $recipientInfo['phone'] ?? '',
            'recipient_phone_2' => $recipientInfo['phone_2'] ?? '',
            'recipient_address' => $recipientInfo['address'] ?? 'Adresse non renseignée',
            'recipient_governorate' => $recipientInfo['governorate'] ?? 'Tunis',
            'recipient_city' => $recipientInfo['city'] ?? 'Tunis',
            'cod_amount' => $shipment->cod_amount ?: 0,
            'content_description' => $shipment->content_description ?: "Commande e-commerce #{$shipment->order_id}",
            'weight' => $shipment->weight ?: 1.0,
            'notes' => "Pickup #{$this->id} - Admin: {$this->admin->name}",
        ];

        Log::info('📋 [PICKUP] Données shipment préparées', [
            'shipment_id' => $shipment->id,
            'recipient' => $data['recipient_name'],
            'phone' => $data['recipient_phone'],
            'governorate' => $data['recipient_governorate'],
            'cod' => $data['cod_amount'],
        ]);

        return $data;
    }

    // ========================================
    // AUTRES MÉTHODES MÉTIER
    // ========================================

    /**
     * Marquer le pickup comme récupéré par le transporteur
     */
    public function markAsPickedUp()
    {
        if ($this->status !== self::STATUS_VALIDATED) {
            throw new \Exception('Seuls les pickups validés peuvent être marqués comme récupérés');
        }

        Log::info('🚛 [PICKUP] Marquage comme récupéré', [
            'pickup_id' => $this->id,
            'carrier' => $this->carrier_slug,
        ]);

        $this->update(['status' => self::STATUS_PICKED_UP]);
        
        // Mettre à jour tous les shipments associés
        $this->shipments()->update(['status' => 'picked_up_by_carrier']);

        // Mettre à jour les commandes associées
        foreach ($this->orders as $order) {
            $order->updateDeliveryStatus(
                'en_transit', 
                null, 
                null, 
                "Pickup #{$this->id} récupéré par le transporteur {$this->carrier_name}"
            );
        }

        Log::info('✅ [PICKUP] Marqué comme récupéré', [
            'pickup_id' => $this->id,
            'shipments_updated' => $this->shipments()->count(),
        ]);
    }

    /**
     * Annuler le pickup
     */
    public function cancel($reason = null)
    {
        if ($this->status === self::STATUS_PICKED_UP) {
            throw new \Exception('Un pickup déjà récupéré ne peut pas être annulé');
        }

        Log::info('❌ [PICKUP] Annulation pickup', [
            'pickup_id' => $this->id,
            'reason' => $reason,
        ]);

        $this->update(['status' => self::STATUS_PROBLEM]);
        
        // Remettre les shipments en status 'created'
        $this->shipments()->update(['status' => 'created']);

        // Remettre les commandes en status 'confirmée'
        foreach ($this->orders as $order) {
            $order->update(['status' => 'confirmée']);
            $order->recordHistory(
                'pickup_cancelled',
                "Pickup #{$this->id} annulé" . ($reason ? ": {$reason}" : ''),
                ['pickup_id' => $this->id, 'reason' => $reason]
            );
        }
    }

    /**
     * Obtenir un résumé du pickup
     */
    public function getSummary()
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'carrier_slug' => $this->carrier_slug,
            'carrier_name' => $this->carrier_name,
            'pickup_date' => $this->pickup_date?->format('d/m/Y'),
            'validated_at' => $this->validated_at?->format('d/m/Y H:i'),
            'created_at' => $this->created_at->format('d/m/Y H:i'),
            'shipments_count' => $this->total_shipments,
            'total_weight' => $this->total_weight,
            'total_cod_amount' => $this->total_cod_amount,
            'total_pieces' => $this->total_pieces,
            'can_be_validated' => $this->can_be_validated,
            'can_be_edited' => $this->can_be_edited,
            'can_be_deleted' => $this->can_be_deleted,
            'can_be_picked_up' => $this->can_be_picked_up,
        ];
    }

    /**
     * Obtenir les numéros de suivi de tous les shipments
     */
    public function getTrackingNumbers()
    {
        return $this->shipments()
            ->whereNotNull('pos_barcode')
            ->pluck('pos_barcode')
            ->filter()
            ->toArray();
    }

    /**
     * Vérifier si tous les shipments sont validés
     */
    public function areAllShipmentsValidated()
    {
        $totalShipments = $this->shipments()->count();
        $validatedShipments = $this->shipments()->where('status', 'validated')->count();
        
        return $totalShipments > 0 && $totalShipments === $validatedShipments;
    }

    // ========================================
    // SCOPES
    // ========================================

    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeValidated($query)
    {
        return $query->where('status', self::STATUS_VALIDATED);
    }

    public function scopePickedUp($query)
    {
        return $query->where('status', self::STATUS_PICKED_UP);
    }

    public function scopeProblem($query)
    {
        return $query->where('status', self::STATUS_PROBLEM);
    }

    public function scopeForCarrier($query, $carrierSlug)
    {
        return $query->where('carrier_slug', $carrierSlug);
    }

    public function scopeForAdmin($query, $adminId)
    {
        return $query->where('admin_id', $adminId);
    }

    public function scopeScheduledFor($query, $date)
    {
        return $query->whereDate('pickup_date', $date);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('pickup_date', today());
    }

    public function scopeTomorrow($query)
    {
        return $query->whereDate('pickup_date', tomorrow());
    }

    public function scopeOverdue($query)
    {
        return $query->where('pickup_date', '<', today())
            ->whereNotIn('status', [self::STATUS_PICKED_UP]);
    }

    // ========================================
    // MÉTHODES STATIQUES
    // ========================================

    /**
     * Créer un nouveau pickup pour un transporteur
     */
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

    /**
     * Obtenir les pickups qui peuvent être validés
     */
    public static function getValidatable($adminId = null)
    {
        $query = static::draft()
            ->whereHas('shipments')
            ->whereHas('deliveryConfiguration', function($q) {
                $q->where('is_active', true);
            });
            
        if ($adminId) {
            $query->where('admin_id', $adminId);
        }
        
        return $query->get()->filter(function($pickup) {
            return $pickup->can_be_validated;
        });
    }

    /**
     * Obtenir les statistiques des pickups pour un admin
     */
    public static function getStatsForAdmin($adminId)
    {
        $pickups = static::where('admin_id', $adminId);
        
        return [
            'total' => $pickups->count(),
            'draft' => $pickups->where('status', self::STATUS_DRAFT)->count(),
            'validated' => $pickups->where('status', self::STATUS_VALIDATED)->count(),
            'picked_up' => $pickups->where('status', self::STATUS_PICKED_UP)->count(),
            'problem' => $pickups->where('status', self::STATUS_PROBLEM)->count(),
            'today' => $pickups->whereDate('pickup_date', today())->count(),
            'overdue' => $pickups->where('pickup_date', '<', today())
                ->whereNotIn('status', [self::STATUS_PICKED_UP])->count(),
        ];
    }

    /**
     * Obtenir tous les statuts disponibles
     */
    public static function getAvailableStatuses()
    {
        return [
            self::STATUS_DRAFT => 'Brouillon',
            self::STATUS_VALIDATED => 'Validé',
            self::STATUS_PICKED_UP => 'Récupéré',
            self::STATUS_PROBLEM => 'Problème',
        ];
    }

    /**
     * Nettoyer les pickups anciens sans shipments
     */
    public static function cleanupEmpty($daysOld = 7)
    {
        return static::where('created_at', '<', now()->subDays($daysOld))
            ->where('status', self::STATUS_DRAFT)
            ->whereDoesntHave('shipments')
            ->delete();
    }

    // ========================================
    // ÉVÉNEMENTS DU MODÈLE
    // ========================================

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($pickup) {
            Log::info('🗑️ [PICKUP] Suppression pickup', [
                'pickup_id' => $pickup->id,
                'shipments_count' => $pickup->shipments()->count(),
            ]);
            
            // Supprimer tous les shipments associés
            $pickup->shipments()->delete();
        });

        static::updating(function ($pickup) {
            $originalStatus = $pickup->getOriginal('status');
            $newStatus = $pickup->status;
            
            if ($originalStatus !== $newStatus) {
                Log::info('🔄 [PICKUP] Changement de statut', [
                    'pickup_id' => $pickup->id,
                    'from' => $originalStatus,
                    'to' => $newStatus,
                ]);
            }
        });
    }
}