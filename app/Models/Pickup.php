<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Services\Delivery\SimpleCarrierFactory;
//use App\Services\Delivery\ShippingServiceFactory;
use App\Services\Delivery\Contracts\CarrierServiceException;
//use App\Services\Delivery\Contracts\CarrierValidationException;
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

    /**
     * L'admin propriétaire de ce pickup
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * La configuration de livraison utilisée
     */
    public function deliveryConfiguration()
    {
        return $this->belongsTo(DeliveryConfiguration::class);
    }

    /**
     * Les expéditions incluses dans ce pickup
     */
    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }

    /**
     * Les commandes incluses via les expéditions
     */
    public function orders()
    {
        return $this->hasManyThrough(Order::class, Shipment::class, 'pickup_id', 'id', 'id', 'order_id');
    }

    // ========================================
    // ACCESSORS & MUTATORS
    // ========================================

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

    /**
     * Obtenir l'icône du statut
     */
    public function getStatusIconAttribute()
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'fa-edit',
            self::STATUS_VALIDATED => 'fa-check',
            self::STATUS_PICKED_UP => 'fa-truck',
            self::STATUS_PROBLEM => 'fa-exclamation-triangle',
            default => 'fa-question',
        };
    }

    /**
     * Vérifier si le pickup peut être modifié
     */
    public function getCanBeEditedAttribute()
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Vérifier si le pickup peut être validé
     */
    public function getCanBeValidatedAttribute()
    {
        return $this->status === self::STATUS_DRAFT && 
               $this->shipments()->exists() &&
               $this->deliveryConfiguration &&
               $this->deliveryConfiguration->is_active &&
               $this->deliveryConfiguration->isValidForApiCalls();
    }

    /**
     * Vérifier si le pickup peut être supprimé
     */
    public function getCanBeDeletedAttribute()
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Obtenir le nombre total d'expéditions
     */
    public function getShipmentsCountAttribute()
    {
        return $this->shipments()->count();
    }

    /**
     * Obtenir le nombre total de commandes
     */
    public function getOrdersCountAttribute()
    {
        return $this->orders()->count();
    }

    /**
     * Obtenir la valeur totale COD
     */
    public function getTotalCodAmountAttribute()
    {
        return $this->shipments()->sum('cod_amount');
    }

    /**
     * Obtenir le poids total
     */
    public function getTotalWeightAttribute()
    {
        return $this->shipments()->sum('weight');
    }

    /**
     * Obtenir le nombre total de pièces
     */
    public function getTotalPiecesAttribute()
    {
        return $this->shipments()->sum('nb_pieces');
    }

    /**
     * Vérifier si le pickup est en retard
     */
    public function getIsOverdueAttribute()
    {
        if (!$this->pickup_date) {
            return false;
        }
        
        return $this->pickup_date->isPast() && 
               in_array($this->status, [self::STATUS_DRAFT, self::STATUS_VALIDATED]);
    }

    /**
     * Obtenir les jours de retard
     */
    public function getDaysOverdueAttribute()
    {
        if (!$this->is_overdue) {
            return 0;
        }
        
        return $this->pickup_date->diffInDays(now());
    }

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Scope pour les pickups en brouillon
     */
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /**
     * Scope pour les pickups validés
     */
    public function scopeValidated($query)
    {
        return $query->where('status', self::STATUS_VALIDATED);
    }

    /**
     * Scope pour les pickups récupérés
     */
    public function scopePickedUp($query)
    {
        return $query->where('status', self::STATUS_PICKED_UP);
    }

    /**
     * Scope pour les pickups avec problème
     */
    public function scopeProblem($query)
    {
        return $query->where('status', self::STATUS_PROBLEM);
    }

    /**
     * Scope pour un transporteur spécifique
     */
    public function scopeForCarrier($query, $carrierSlug)
    {
        return $query->where('carrier_slug', $carrierSlug);
    }

    /**
     * Scope pour un admin spécifique
     */
    public function scopeForAdmin($query, $adminId)
    {
        return $query->where('admin_id', $adminId);
    }

    /**
     * Scope pour les pickups d'aujourd'hui
     */
    public function scopeToday($query)
    {
        return $query->whereDate('pickup_date', today());
    }

    /**
     * Scope pour les pickups en retard
     */
    public function scopeOverdue($query)
    {
        return $query->where('pickup_date', '<', today())
            ->whereIn('status', [self::STATUS_DRAFT, self::STATUS_VALIDATED]);
    }

    /**
     * Scope pour les pickups prêts à être validés
     */
    public function scopeReadyToValidate($query)
    {
        return $query->where('status', self::STATUS_DRAFT)
            ->whereHas('shipments')
            ->whereHas('deliveryConfiguration', function($q) {
                $q->where('is_active', true);
            });
    }

    // ========================================
    // MÉTHODES PRINCIPALES
    // ========================================

    /**
     * Valider le pickup (envoi vers l'API transporteur) - VERSION SIMPLIFIÉE CORRIGÉE
     * 
     * 🚨 REMPLACEZ ENTIÈREMENT la méthode validate() dans app/Models/Pickup.php
     */
    public function validate()
    {
        if (!$this->can_be_validated) {
            throw new \Exception('Ce pickup ne peut pas être validé');
        }

        Log::info('🚀 [PICKUP VALIDATE] Début validation simplifiée', [
            'pickup_id' => $this->id,
            'carrier' => $this->carrier_slug,
            'shipments_count' => $this->shipments->count(),
        ]);

        try {
            DB::beginTransaction();

            $successfulShipments = 0;
            $errors = [];
            $trackingNumbers = [];

            // Obtenir la configuration déchiffrée
            if (method_exists($this->deliveryConfiguration, 'getDecryptedConfig')) {
                $config = $this->deliveryConfiguration->getDecryptedConfig();
            } else {
                // Fallback si la méthode n'existe pas
                $config = [
                    'api_key' => $this->deliveryConfiguration->password,
                    'username' => $this->deliveryConfiguration->username,
                    'environment' => $this->deliveryConfiguration->environment ?? 'test',
                ];
            }

            // 🆕 UTILISER LA NOUVELLE FACTORY SIMPLIFIÉE
            $carrierService = \App\Services\Delivery\SimpleCarrierFactory::create($this->carrier_slug, $config);

            Log::info('✅ [PICKUP VALIDATE] Service transporteur créé', [
                'carrier' => $this->carrier_slug,
                'service_class' => get_class($carrierService)
            ]);

            // Traiter chaque shipment
            foreach ($this->shipments as $shipment) {
                try {
                    Log::info('📦 [PICKUP VALIDATE] Traitement shipment', [
                        'shipment_id' => $shipment->id,
                        'order_id' => $shipment->order_id,
                    ]);

                    // Préparer les données pour l'API
                    $shipmentData = [
                        'external_reference' => "ORDER_{$shipment->order_id}_SHIP_{$shipment->id}",
                        'recipient_name' => $shipment->recipient_info['name'] ?? 'Client',
                        'recipient_phone' => $shipment->recipient_info['phone'] ?? '',
                        'recipient_phone_2' => $shipment->recipient_info['phone_2'] ?? '',
                        'recipient_address' => $shipment->recipient_info['address'] ?? '',
                        'recipient_governorate' => $shipment->recipient_info['governorate'] ?? 'Tunis',
                        'recipient_city' => $shipment->recipient_info['city'] ?? '',
                        'cod_amount' => $shipment->cod_amount,
                        'content_description' => $shipment->content_description ?? "Commande #{$shipment->order_id}",
                        'weight' => $shipment->weight,
                        'notes' => "Pickup #{$this->id}",
                    ];

                    // Appel à l'API du transporteur
                    $result = $carrierService->createShipment($shipmentData);

                    if ($result['success']) {
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

                        // Mettre à jour la commande
                        if ($shipment->order) {
                            $shipment->order->update([
                                'status' => 'expédiée',
                                'shipped_at' => now(),
                                'tracking_number' => $result['tracking_number'],
                                'carrier_name' => $this->carrier_name,
                            ]);

                            // Historique de la commande
                            $shipment->order->recordHistory(
                                'shipment_validated',
                                "Commande expédiée via {$this->carrier_name} dans le pickup #{$this->id}",
                                [
                                    'pickup_id' => $this->id,
                                    'tracking_number' => $result['tracking_number'],
                                ],
                                'confirmée',
                                'expédiée',
                                null,
                                'Expédié via pickup',
                                $result['tracking_number'],
                                $this->carrier_name
                            );
                        }

                    } else {
                        $errorMsg = "Erreur shipment #{$shipment->id}: Réponse API invalide";
                        $errors[] = $errorMsg;
                        Log::error('❌ [PICKUP VALIDATE] ' . $errorMsg, ['result' => $result]);
                    }

                } catch (\Exception $e) {
                    $errorMsg = "Erreur shipment #{$shipment->id}: {$e->getMessage()}";
                    $errors[] = $errorMsg;
                    Log::error('❌ [PICKUP VALIDATE] ' . $errorMsg, [
                        'shipment_id' => $shipment->id,
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            // Créer le pickup chez le transporteur (si des shipments ont réussi)
            $pickupResult = null;
            if (!empty($trackingNumbers)) {
                try {
                    Log::info('🚛 [PICKUP VALIDATE] Création pickup chez transporteur');
                    
                    $pickupData = [
                        'tracking_numbers' => $trackingNumbers,
                        'address' => 'Adresse de collecte',
                    ];

                    $pickupResult = $carrierService->createPickup($pickupData);
                    
                    if ($pickupResult['success']) {
                        Log::info('✅ [PICKUP VALIDATE] Pickup créé chez transporteur', [
                            'carrier_pickup_id' => $pickupResult['pickup_id'],
                        ]);
                    }

                } catch (\Exception $e) {
                    Log::warning('⚠️ [PICKUP VALIDATE] Erreur création pickup (non bloquant)', [
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
                    'pickup_result' => $pickupResult,
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
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->update(['status' => self::STATUS_PROBLEM]);
            throw $e;
        }
    }

    /**
     * Marquer comme récupéré par le transporteur
     */
    public function markAsPickedUp()
    {
        if ($this->status !== self::STATUS_VALIDATED) {
            throw new \Exception('Seuls les pickups validés peuvent être marqués comme récupérés');
        }

        Log::info('🚛 [PICKUP PICKED UP] Marquage récupération', [
            'pickup_id' => $this->id,
            'carrier' => $this->carrier_slug
        ]);

        $this->update(['status' => self::STATUS_PICKED_UP]);

        // Mettre à jour le statut des expéditions
        $this->shipments()->update(['status' => 'picked_up_by_carrier']);

        // Enregistrer dans l'historique des commandes
        foreach ($this->orders as $order) {
            $order->recordHistory(
                'picked_up_by_carrier',
                "Pickup #{$this->id} récupéré par le transporteur {$this->carrier_name}",
                [
                    'pickup_id' => $this->id,
                    'carrier_slug' => $this->carrier_slug,
                ],
                $order->status,
                'en_transit',
                null,
                'Récupéré par transporteur',
                null,
                $this->carrier_name
            );

            // Mettre à jour le statut de la commande
            $order->update(['status' => 'en_transit']);
        }

        Log::info('✅ [PICKUP PICKED UP] Pickup marqué récupéré avec mise à jour des commandes', [
            'pickup_id' => $this->id,
            'orders_updated' => $this->orders->count()
        ]);
    }

    /**
     * Marquer comme ayant un problème
     */
    public function markAsProblem($reason = null)
    {
        Log::info('⚠️ [PICKUP PROBLEM] Marquage problème', [
            'pickup_id' => $this->id,
            'reason' => $reason,
            'carrier' => $this->carrier_slug
        ]);

        $this->update(['status' => self::STATUS_PROBLEM]);

        // Enregistrer dans l'historique des commandes
        foreach ($this->orders as $order) {
            $order->recordHistory(
                'pickup_problem',
                "Problème avec pickup #{$this->id}: " . ($reason ?: 'Raison non spécifiée'),
                [
                    'pickup_id' => $this->id,
                    'carrier_slug' => $this->carrier_slug,
                    'problem_reason' => $reason,
                ],
                $order->status,
                $order->status,
                null,
                'Problème pickup',
                null,
                $this->carrier_name
            );
        }
    }

    /**
     * Ajouter une expédition au pickup
     */
    public function addShipment(Shipment $shipment)
    {
        if (!$this->can_be_edited) {
            throw new \Exception('Ce pickup ne peut plus être modifié');
        }

        if ($shipment->pickup_id && $shipment->pickup_id !== $this->id) {
            throw new \Exception('Cette expédition est déjà assignée à un autre pickup');
        }

        Log::info('➕ [PICKUP ADD SHIPMENT] Ajout expédition au pickup', [
            'pickup_id' => $this->id,
            'shipment_id' => $shipment->id,
            'order_id' => $shipment->order_id
        ]);

        $shipment->update(['pickup_id' => $this->id]);
        
        return $this;
    }

    /**
     * Retirer une expédition du pickup
     */
    public function removeShipment(Shipment $shipment)
    {
        if (!$this->can_be_edited) {
            throw new \Exception('Ce pickup ne peut plus être modifié');
        }

        if ($shipment->pickup_id === $this->id) {
            Log::info('➖ [PICKUP REMOVE SHIPMENT] Retrait expédition du pickup', [
                'pickup_id' => $this->id,
                'shipment_id' => $shipment->id,
                'order_id' => $shipment->order_id
            ]);

            $shipment->update(['pickup_id' => null, 'status' => 'created']);
        }
        
        return $this;
    }

    /**
     * Obtenir le résumé du pickup
     */
    public function getSummary()
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'status_color' => $this->status_color,
            'status_icon' => $this->status_icon,
            'carrier_name' => $this->carrier_name,
            'carrier_slug' => $this->carrier_slug,
            'pickup_date' => $this->pickup_date?->format('d/m/Y'),
            'pickup_date_iso' => $this->pickup_date?->toDateString(),
            'validated_at' => $this->validated_at?->toISOString(),
            'shipments_count' => $this->shipments_count,
            'orders_count' => $this->orders_count,
            'total_cod_amount' => $this->total_cod_amount,
            'total_weight' => $this->total_weight,
            'total_pieces' => $this->total_pieces,
            'is_overdue' => $this->is_overdue,
            'days_overdue' => $this->days_overdue,
            'can_be_validated' => $this->can_be_validated,
            'can_be_edited' => $this->can_be_edited,
            'can_be_deleted' => $this->can_be_deleted,
            'configuration' => [
                'id' => $this->deliveryConfiguration?->id,
                'name' => $this->deliveryConfiguration?->integration_name,
                'is_active' => $this->deliveryConfiguration?->is_active ?? false,
                'is_valid_for_api' => $this->deliveryConfiguration?->isValidForApiCalls() ?? false,
            ],
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }

    /**
     * Obtenir les détails d'un pickup avec ses relations
     */
    public function getFullDetails()
    {
        $this->load(['shipments.order', 'deliveryConfiguration']);
        
        $summary = $this->getSummary();
        
        $summary['shipments'] = $this->shipments->map(function($shipment) {
            return [
                'id' => $shipment->id,
                'order_id' => $shipment->order_id,
                'status' => $shipment->status,
                'pos_barcode' => $shipment->pos_barcode,
                'weight' => $shipment->weight,
                'cod_amount' => $shipment->cod_amount,
                'nb_pieces' => $shipment->nb_pieces,
                'order' => $shipment->order ? [
                    'id' => $shipment->order->id,
                    'customer_name' => $shipment->order->customer_name,
                    'customer_phone' => $shipment->order->customer_phone,
                    'customer_city' => $shipment->order->customer_city,
                    'total_price' => $shipment->order->total_price,
                    'status' => $shipment->order->status,
                ] : null,
            ];
        });
        
        return $summary;
    }

    /**
     * Test de connexion avec le transporteur configuré
     
    *public function testCarrierConnection()
    *{
    *    if (!$this->deliveryConfiguration || !$this->deliveryConfiguration->is_active) {
    *        return [
    *            'success' => false,
    *            'error' => 'Configuration transporteur inactive ou manquante',
    *        ];
    *    }

    *    try {
    *        $shippingFactory = app(ShippingServiceFactory::class);
    *        $carrierService = $shippingFactory->create(
    *            $this->carrier_slug, 
    *            $this->deliveryConfiguration->getDecryptedConfig()
    *        );

    *        return $carrierService->testConnection();

    *    } catch (\Exception $e) {
    *        Log::error('❌ [PICKUP TEST CONNECTION] Erreur test connexion', [
    *            'pickup_id' => $this->id,
    *            'carrier' => $this->carrier_slug,
    *            'error' => $e->getMessage()
    *        ]);

    *        return [
    *            'success' => false,
    *            'error' => 'Erreur test connexion: ' . $e->getMessage(),
    *        ];
    *    }
    *}
        */

    // ========================================
    // MÉTHODES STATIQUES
    // ========================================

    /**
     * Créer un nouveau pickup pour un admin et transporteur
     */
    public static function createForCarrier($adminId, $carrierSlug, $configurationId, $pickupDate = null)
    {
        Log::info('🆕 [PICKUP CREATE] Création nouveau pickup', [
            'admin_id' => $adminId,
            'carrier_slug' => $carrierSlug,
            'configuration_id' => $configurationId,
            'pickup_date' => $pickupDate
        ]);

        return static::create([
            'admin_id' => $adminId,
            'carrier_slug' => $carrierSlug,
            'delivery_configuration_id' => $configurationId,
            'status' => self::STATUS_DRAFT,
            'pickup_date' => $pickupDate ?: now()->addDay()->format('Y-m-d'),
        ]);
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
            'overdue' => static::where('admin_id', $adminId)->overdue()->count(),
            'today' => static::where('admin_id', $adminId)->today()->count(),
        ];
    }

    /**
     * Obtenir les pickups récents pour un admin
     */
    public static function getRecentForAdmin($adminId, $limit = 10)
    {
        return static::where('admin_id', $adminId)
            ->with(['deliveryConfiguration', 'shipments'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
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
     * Obtenir les pickups prêts pour validation en masse
     */
    public static function getReadyForBulkValidation($adminId, $carrierSlug = null)
    {
        $query = static::where('admin_id', $adminId)
            ->where('status', self::STATUS_DRAFT)
            ->whereHas('shipments')
            ->whereHas('deliveryConfiguration', function($q) {
                $q->where('is_active', true);
            });

        if ($carrierSlug) {
            $query->where('carrier_slug', $carrierSlug);
        }

        return $query->with(['deliveryConfiguration', 'shipments'])->get();
    }

    /**
     * Obtenir les performances par transporteur
     */
    public static function getCarrierPerformance($adminId, $days = 30)
    {
        $startDate = now()->subDays($days);
        
        return static::where('admin_id', $adminId)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('
                carrier_slug,
                COUNT(*) as total_pickups,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as validated_pickups,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as picked_up_pickups,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as problem_pickups,
                AVG(CASE WHEN validated_at IS NOT NULL THEN TIMESTAMPDIFF(MINUTE, created_at, validated_at) END) as avg_validation_time_minutes
            ', [self::STATUS_VALIDATED, self::STATUS_PICKED_UP, self::STATUS_PROBLEM])
            ->groupBy('carrier_slug')
            ->get()
            ->map(function($row) {
                $row->success_rate = $row->total_pickups > 0 
                    ? round(($row->validated_pickups + $row->picked_up_pickups) / $row->total_pickups * 100, 2)
                    : 0;
                return $row;
            });
    }

    /**
     * Nettoyer les anciens pickups brouillons
     */
    public static function cleanupOldDrafts($days = 7)
    {
        $cutoffDate = now()->subDays($days);
        
        $deletedCount = static::where('status', self::STATUS_DRAFT)
            ->where('created_at', '<', $cutoffDate)
            ->whereDoesntHave('shipments') // Seulement ceux sans expéditions
            ->delete();

        Log::info('🧹 [PICKUP CLEANUP] Nettoyage anciens brouillons', [
            'deleted_count' => $deletedCount,
            'cutoff_date' => $cutoffDate->toDateString()
        ]);

        return $deletedCount;
    }
}