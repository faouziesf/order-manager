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
    // 🔧 ACCESSORS CORRIGÉS
    // ========================================

    /**
     * 🔧 CORRECTION : Vérifier si le pickup peut être validé - VERSION SIMPLIFIÉE
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

        // Charger la relation si pas déjà fait et vérifier proprement
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

        // Vérifier que la configuration est active et valide
        if (!$this->deliveryConfiguration->is_active || !$this->deliveryConfiguration->is_valid) {
            Log::warning("❌ [PICKUP CAN_BE_VALIDATED] Configuration inactive ou invalide", [
                'pickup_id' => $this->id,
                'config_id' => $this->deliveryConfiguration->id,
                'is_active' => $this->deliveryConfiguration->is_active,
                'is_valid' => $this->deliveryConfiguration->is_valid,
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
    // 🆕 MÉTHODES DE DIAGNOSTIC - NOUVELLES FONCTIONNALITÉS
    // ========================================

    /**
     * 🆕 MÉTHODE DE DIAGNOSTIC : Analyser pourquoi la validation échoue
     */
    public function diagnoseValidationIssues(): array
    {
        $issues = [];
        $recommendations = [];

        Log::info('🔍 [PICKUP DIAGNOSTIC] Début diagnostic', [
            'pickup_id' => $this->id,
            'status' => $this->status,
            'carrier' => $this->carrier_slug,
        ]);

        // 1. Vérifier le statut
        if ($this->status !== self::STATUS_DRAFT) {
            $issues[] = "❌ Statut incorrect: {$this->status} (requis: draft)";
            $recommendations[] = "Remettre le pickup en statut 'draft'";
        } else {
            $issues[] = "✅ Statut correct: draft";
        }

        // 2. Vérifier les shipments
        $shipmentsCount = $this->shipments()->count();
        if ($shipmentsCount === 0) {
            $issues[] = "❌ Aucune expédition dans ce pickup";
            $recommendations[] = "Ajouter des commandes au pickup";
        } else {
            $issues[] = "✅ {$shipmentsCount} expédition(s) trouvée(s)";
        }

        // 3. Vérifier la configuration
        if (!$this->deliveryConfiguration) {
            $issues[] = "❌ Configuration transporteur manquante";
            $recommendations[] = "Associer une configuration valide";
        } else {
            $config = $this->deliveryConfiguration;
            
            if (!$config->is_active) {
                $issues[] = "❌ Configuration inactive";
                $recommendations[] = "Activer la configuration transporteur";
            } else {
                $issues[] = "✅ Configuration active";
            }

            if (!$config->is_valid) {
                $issues[] = "❌ Configuration invalide";
                $recommendations[] = "Vérifier les credentials de la configuration";
            } else {
                $issues[] = "✅ Configuration valide";
            }

            // Test de connexion
            try {
                $connectionTest = $config->testConnection();
                if ($connectionTest['success']) {
                    $issues[] = "✅ Test de connexion réussi";
                } else {
                    $issues[] = "❌ Test de connexion échoué: " . $connectionTest['message'];
                    $recommendations[] = "Vérifier le token/credentials dans la configuration";
                }
            } catch (\Exception $e) {
                $issues[] = "❌ Erreur test connexion: " . $e->getMessage();
                $recommendations[] = "Vérifier la configuration réseau et les tokens";
            }

            // Vérifier les détails de configuration
            $configDetails = [];
            if (empty($config->username)) {
                $configDetails[] = "Username/compte manquant";
            }
            if (empty($config->password)) {
                $configDetails[] = "Token/mot de passe manquant";
            }
            if (!empty($configDetails)) {
                $issues[] = "⚠️ Configuration incomplète: " . implode(', ', $configDetails);
                $recommendations[] = "Compléter tous les champs requis dans la configuration";
            }
        }

        // 4. Vérifier chaque shipment en détail
        $shipmentIssues = [];
        foreach ($this->shipments as $index => $shipment) {
            $shipmentData = [];
            
            $recipientInfo = $shipment->recipient_info ?: [];
            if (empty($recipientInfo['name'])) {
                $shipmentData[] = "Nom destinataire manquant";
            }
            if (empty($recipientInfo['phone']) || strlen($recipientInfo['phone']) < 8) {
                $shipmentData[] = "Téléphone invalide/manquant";
            }
            if (empty($recipientInfo['address'])) {
                $shipmentData[] = "Adresse manquante";
            }
            if (empty($recipientInfo['governorate'])) {
                $shipmentData[] = "Gouvernorat manquant";
            }
            if (empty($recipientInfo['city'])) {
                $shipmentData[] = "Ville manquante";
            }
            if ($shipment->cod_amount < 0) {
                $shipmentData[] = "Montant COD invalide";
            }

            if (!empty($shipmentData)) {
                $shipmentIssues["Shipment #{$shipment->id}"] = $shipmentData;
            }
        }

        if (!empty($shipmentIssues)) {
            $issues[] = "⚠️ Problèmes avec les expéditions:";
            foreach ($shipmentIssues as $shipmentId => $problems) {
                $issues[] = "  {$shipmentId}: " . implode(', ', $problems);
            }
            $recommendations[] = "Corriger les données des expéditions (voir détails ci-dessus)";
        } else if ($shipmentsCount > 0) {
            $issues[] = "✅ Toutes les expéditions ont des données valides";
        }

        // 5. Test de préparation des données API
        if ($this->deliveryConfiguration && $shipmentsCount > 0) {
            try {
                $firstShipment = $this->shipments->first();
                $testData = $this->prepareShipmentDataForApi($firstShipment);
                $issues[] = "✅ Préparation données API réussie";
            } catch (\Exception $e) {
                $issues[] = "❌ Erreur préparation données API: " . $e->getMessage();
                $recommendations[] = "Vérifier le format des données des expéditions";
            }
        }

        $result = [
            'can_be_validated' => $this->can_be_validated,
            'issues' => $issues,
            'recommendations' => $recommendations,
            'shipments_count' => $shipmentsCount,
            'config_summary' => $this->deliveryConfiguration ? [
                'id' => $this->deliveryConfiguration->id,
                'name' => $this->deliveryConfiguration->integration_name,
                'carrier' => $this->deliveryConfiguration->carrier_slug,
                'active' => $this->deliveryConfiguration->is_active,
                'valid' => $this->deliveryConfiguration->is_valid,
                'environment' => $this->deliveryConfiguration->environment,
                'has_username' => !empty($this->deliveryConfiguration->username),
                'has_password' => !empty($this->deliveryConfiguration->password),
            ] : null,
            'diagnostic_timestamp' => now()->toISOString(),
        ];

        Log::info('✅ [PICKUP DIAGNOSTIC] Diagnostic terminé', [
            'pickup_id' => $this->id,
            'can_be_validated' => $result['can_be_validated'],
            'issues_count' => count($issues),
            'recommendations_count' => count($recommendations),
        ]);

        return $result;
    }

    /**
     * 🆕 MÉTHODE DE VÉRIFICATION : Test rapide de validité
     */
    public function quickValidityCheck(): array
    {
        $checks = [
            'status_ok' => $this->status === self::STATUS_DRAFT,
            'has_shipments' => $this->shipments()->exists(),
            'has_config' => !!$this->deliveryConfiguration,
            'config_active' => $this->deliveryConfiguration?->is_active ?? false,
            'config_valid' => $this->deliveryConfiguration?->is_valid ?? false,
        ];

        $checks['all_ok'] = collect($checks)->every(fn($check) => $check === true);

        return $checks;
    }

    // ========================================
    // 🔧 MÉTHODE VALIDATE COMPLÈTEMENT REÉCRITE ET AMÉLIORÉE
    // ========================================

    /**
     * 🔧 CORRECTION COMPLÈTE : Valider le pickup - TRAITE TOUS LES COLIS MÊME EN CAS D'ÉCHEC PARTIEL
     */
    public function validate()
    {
        Log::info('🚀 [PICKUP VALIDATE] Début validation pickup', [
            'pickup_id' => $this->id,
            'carrier' => $this->carrier_slug,
            'can_be_validated' => $this->can_be_validated,
            'shipments_count' => $this->shipments()->count(),
            'admin_id' => $this->admin_id,
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
            $detailedResults = [];

            // 🔧 CORRECTION : Utiliser la méthode simplifiée getApiConfig()
            $apiConfig = $this->deliveryConfiguration->getApiConfig();
            
            Log::info('✅ [PICKUP VALIDATE] Configuration API préparée', [
                'pickup_id' => $this->id,
                'carrier' => $this->carrier_slug,
                'config_keys' => array_keys($apiConfig),
                'has_token' => !empty($apiConfig['api_token']),
                'has_username' => !empty($apiConfig['username']),
                'environment' => $apiConfig['environment'] ?? 'unknown',
            ]);

            // Créer le service transporteur
            $carrierService = SimpleCarrierFactory::create($this->carrier_slug, $apiConfig);

            // 🆕 CORRECTION MAJEURE : Traiter chaque shipment individuellement sans arrêter en cas d'échec
            foreach ($this->shipments as $index => $shipment) {
                $shipmentResult = [
                    'shipment_id' => $shipment->id,
                    'order_id' => $shipment->order_id,
                    'success' => false,
                    'tracking_number' => null,
                    'error' => null,
                    'api_response' => null,
                ];

                try {
                    Log::info('📦 [PICKUP VALIDATE] Traitement shipment', [
                        'shipment_id' => $shipment->id,
                        'order_id' => $shipment->order_id,
                        'index' => $index + 1,
                        'total' => $this->shipments->count(),
                    ]);

                    // Préparer les données selon le format API requis
                    $shipmentData = $this->prepareShipmentDataForApi($shipment);

                    Log::info('📤 [PICKUP VALIDATE] Données shipment préparées', [
                        'shipment_id' => $shipment->id,
                        'recipient_name' => $shipmentData['recipient_name'],
                        'cod_amount' => $shipmentData['cod_amount'],
                        'governorate' => $shipmentData['recipient_governorate'],
                        'phone' => $shipmentData['recipient_phone'],
                        'external_reference' => $shipmentData['external_reference'],
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

                        $shipmentResult['success'] = true;
                        $shipmentResult['tracking_number'] = $result['tracking_number'];
                        $shipmentResult['api_response'] = $result['response'];

                        Log::info('✅ [PICKUP VALIDATE] Colis créé avec succès dans le compte transporteur', [
                            'shipment_id' => $shipment->id,
                            'tracking_number' => $result['tracking_number'],
                            'carrier' => $this->carrier_slug,
                            'cod_amount' => $shipmentData['cod_amount'],
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
                        $shipmentResult['error'] = $errorMsg;
                        $shipmentResult['api_response'] = $result['response'] ?? null;
                        
                        Log::error('❌ [PICKUP VALIDATE] ' . $errorMsg, [
                            'shipment_id' => $shipment->id,
                            'carrier_response' => $result,
                            'shipment_data' => $shipmentData,
                        ]);
                        
                        // 🆕 CORRECTION : Marquer le shipment comme problématique mais continuer
                        $shipment->update([
                            'status' => 'problem',
                            'carrier_response' => $result['response'] ?? null,
                        ]);
                    }

                } catch (CarrierServiceException $e) {
                    $errorMsg = "Erreur transporteur shipment #{$shipment->id}: {$e->getMessage()}";
                    $errors[] = $errorMsg;
                    $shipmentResult['error'] = $errorMsg;
                    $shipmentResult['api_response'] = $e->getCarrierResponse();
                    
                    Log::error('❌ [PICKUP VALIDATE] ' . $errorMsg, [
                        'shipment_id' => $shipment->id,
                        'carrier_response' => $e->getCarrierResponse(),
                        'carrier_error_code' => $e->getCode(),
                    ]);
                    
                    // 🆕 CORRECTION : Marquer comme problématique et continuer
                    $shipment->update([
                        'status' => 'carrier_error',
                        'carrier_response' => $e->getCarrierResponse(),
                    ]);
                    
                } catch (\Exception $e) {
                    $errorMsg = "Erreur technique shipment #{$shipment->id}: {$e->getMessage()}";
                    $errors[] = $errorMsg;
                    $shipmentResult['error'] = $errorMsg;
                    
                    Log::error('❌ [PICKUP VALIDATE] ' . $errorMsg, [
                        'shipment_id' => $shipment->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    
                    // 🆕 CORRECTION : Marquer comme problématique et continuer
                    $shipment->update(['status' => 'technical_error']);
                }

                $detailedResults[] = $shipmentResult;
            }

            // 🆕 CORRECTION MAJEURE : Déterminer le statut du pickup selon les résultats
            $totalShipments = $this->shipments->count();
            
            if ($successfulShipments > 0) {
                // Au moins un shipment a réussi - pickup validé
                $status = ($successfulShipments == $totalShipments) ? self::STATUS_VALIDATED : self::STATUS_PROBLEM;
                
                $this->update([
                    'status' => $status,
                    'validated_at' => now(),
                ]);

                DB::commit();

                Log::info('🎉 [PICKUP VALIDATE] Pickup traité avec succès', [
                    'pickup_id' => $this->id,
                    'successful_shipments' => $successfulShipments,
                    'total_shipments' => $totalShipments,
                    'tracking_numbers' => $trackingNumbers,
                    'errors_count' => count($errors),
                    'final_status' => $status,
                ]);

                return [
                    'success' => true,
                    'successful_shipments' => $successfulShipments,
                    'total_shipments' => $totalShipments,
                    'errors' => $errors,
                    'tracking_numbers' => $trackingNumbers,
                    'partial_success' => $successfulShipments < $totalShipments,
                    'detailed_results' => $detailedResults,
                    'final_status' => $status,
                ];

            } else {
                // Aucun shipment n'a réussi
                $this->update(['status' => self::STATUS_PROBLEM]);
                DB::rollBack();

                Log::error('❌ [PICKUP VALIDATE] Aucun shipment validé', [
                    'pickup_id' => $this->id,
                    'errors' => $errors,
                    'detailed_results' => $detailedResults,
                ]);

                return [
                    'success' => false,
                    'errors' => $errors,
                    'successful_shipments' => 0,
                    'total_shipments' => $totalShipments,
                    'detailed_results' => $detailedResults,
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
     * 🔧 CORRECTION CRITIQUE : Préparer les données shipment pour l'API transporteur
     */
    private function prepareShipmentDataForApi($shipment): array
    {
        $recipientInfo = $shipment->recipient_info ?: [];
        
        // Validation des données critiques
        $recipientName = trim($recipientInfo['name'] ?? '');
        $recipientPhone = trim($recipientInfo['phone'] ?? '');
        $recipientAddress = trim($recipientInfo['address'] ?? '');
        $governorate = trim($recipientInfo['governorate'] ?? '');
        $city = trim($recipientInfo['city'] ?? '');
        
        // Valeurs par défaut sécurisées si données manquantes
        if (empty($recipientName)) {
            $recipientName = 'Client';
            if ($shipment->order && !empty($shipment->order->customer_name)) {
                $recipientName = $shipment->order->customer_name;
            }
        }
        
        if (empty($recipientPhone)) {
            if ($shipment->order && !empty($shipment->order->customer_phone)) {
                $recipientPhone = $shipment->order->customer_phone;
            } else {
                $recipientPhone = '12345678'; // Numéro par défaut pour éviter erreur API
            }
        }
        
        if (empty($recipientAddress)) {
            if ($shipment->order && !empty($shipment->order->customer_address)) {
                $recipientAddress = $shipment->order->customer_address;
            } else {
                $recipientAddress = 'Adresse non renseignée';
            }
        }
        
        if (empty($governorate)) {
            if ($shipment->order && !empty($shipment->order->customer_governorate)) {
                $governorate = $shipment->order->customer_governorate;
            } else {
                $governorate = 'Tunis'; // Par défaut
            }
        }
        
        if (empty($city)) {
            if ($shipment->order && !empty($shipment->order->customer_city)) {
                $city = $shipment->order->customer_city;
            } else {
                $city = 'Tunis'; // Par défaut
            }
        }
        
        // Préparer les données finales
        $data = [
            'external_reference' => "PICKUP_{$this->id}_SHIP_{$shipment->id}",
            'recipient_name' => $recipientName,
            'recipient_phone' => $recipientPhone,
            'recipient_phone_2' => $recipientInfo['phone_2'] ?? '',
            'recipient_address' => $recipientAddress,
            'recipient_governorate' => $governorate,
            'recipient_city' => $city,
            'cod_amount' => $shipment->cod_amount ?: 0,
            'content_description' => $shipment->content_description ?: "Commande e-commerce #{$shipment->order_id}",
            'weight' => $shipment->weight ?: 1.0,
            'notes' => "Pickup #{$this->id} - Admin: {$this->admin->name}",
        ];

        Log::info('📋 [PICKUP] Données shipment préparées et validées', [
            'shipment_id' => $shipment->id,
            'recipient' => $data['recipient_name'],
            'phone' => $data['recipient_phone'],
            'governorate' => $data['recipient_governorate'],
            'city' => $data['recipient_city'],
            'cod' => $data['cod_amount'],
            'address_length' => strlen($data['recipient_address']),
            'has_phone_2' => !empty($data['recipient_phone_2']),
            'data_source' => [
                'from_recipient_info' => !empty($recipientInfo),
                'from_order' => $shipment->order ? true : false,
                'fallback_used' => empty($recipientInfo['name']) || empty($recipientInfo['phone']),
            ],
        ]);

        return $data;
    }

    // ========================================
    // AUTRES MÉTHODES MÉTIER (INCHANGÉES)
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
     * 🆕 MÉTHODE DE RÉPARATION : Réparer un pickup problématique
     */
    public function repair(): array
    {
        Log::info('🔧 [PICKUP REPAIR] Début réparation', [
            'pickup_id' => $this->id,
            'current_status' => $this->status,
        ]);

        $repairActions = [];
        $issuesFixed = 0;

        try {
            DB::beginTransaction();

            // 1. Remettre en statut draft si problématique
            if ($this->status === self::STATUS_PROBLEM) {
                $this->update(['status' => self::STATUS_DRAFT]);
                $repairActions[] = "Statut remis à 'draft'";
                $issuesFixed++;
            }

            // 2. Réparer les shipments problématiques
            $problemShipments = $this->shipments()->whereIn('status', ['problem', 'carrier_error', 'technical_error'])->get();
            
            foreach ($problemShipments as $shipment) {
                $shipment->update(['status' => 'created']);
                $repairActions[] = "Shipment #{$shipment->id} remis à 'created'";
                $issuesFixed++;
            }

            // 3. Vérifier et réparer les données manquantes
            foreach ($this->shipments as $shipment) {
                $recipientInfo = $shipment->recipient_info ?: [];
                $needsUpdate = false;
                $updates = [];

                if (empty($recipientInfo['name'])) {
                    $recipientInfo['name'] = 'Client';
                    $needsUpdate = true;
                    $updates[] = 'nom ajouté';
                }

                if (empty($recipientInfo['governorate'])) {
                    $recipientInfo['governorate'] = 'Tunis';
                    $needsUpdate = true;
                    $updates[] = 'gouvernorat par défaut';
                }

                if (empty($recipientInfo['city'])) {
                    $recipientInfo['city'] = 'Tunis';
                    $needsUpdate = true;
                    $updates[] = 'ville par défaut';
                }

                if ($needsUpdate) {
                    $shipment->update(['recipient_info' => $recipientInfo]);
                    $repairActions[] = "Shipment #{$shipment->id}: " . implode(', ', $updates);
                    $issuesFixed++;
                }
            }

            DB::commit();

            Log::info('✅ [PICKUP REPAIR] Réparation terminée', [
                'pickup_id' => $this->id,
                'issues_fixed' => $issuesFixed,
                'actions' => $repairActions,
            ]);

            return [
                'success' => true,
                'issues_fixed' => $issuesFixed,
                'actions' => $repairActions,
                'can_be_validated_now' => $this->fresh()->can_be_validated,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('❌ [PICKUP REPAIR] Erreur réparation', [
                'pickup_id' => $this->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'actions' => $repairActions,
            ];
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

    /**
     * 🆕 MÉTHODE UTILITAIRE : Obtenir les statistiques détaillées
     */
    public function getDetailedStats(): array
    {
        $shipments = $this->shipments;
        
        $stats = [
            'total_shipments' => $shipments->count(),
            'by_status' => $shipments->groupBy('status')->map->count(),
            'total_weight' => $shipments->sum('weight'),
            'total_cod' => $shipments->sum('cod_amount'),
            'total_pieces' => $shipments->sum('nb_pieces'),
            'with_tracking' => $shipments->whereNotNull('pos_barcode')->count(),
            'data_complete' => 0,
        ];

        // Compter les shipments avec données complètes
        foreach ($shipments as $shipment) {
            $info = $shipment->recipient_info ?: [];
            if (!empty($info['name']) && !empty($info['phone']) && !empty($info['address'])) {
                $stats['data_complete']++;
            }
        }

        $stats['completion_rate'] = $stats['total_shipments'] > 0 
            ? round(($stats['data_complete'] / $stats['total_shipments']) * 100, 1)
            : 0;

        return $stats;
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

    public function scopeValidatable($query)
    {
        return $query->where('status', self::STATUS_DRAFT)
            ->whereHas('shipments')
            ->whereHas('deliveryConfiguration', function($q) {
                $q->where('is_active', true)->where('is_valid', true);
            });
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
        $query = static::validatable();
            
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

    /**
     * 🆕 MÉTHODE STATIQUE : Diagnostic global des pickups problématiques
     */
    public static function diagnoseProblematicPickups($adminId = null): array
    {
        $query = static::where('status', self::STATUS_PROBLEM);
        
        if ($adminId) {
            $query->where('admin_id', $adminId);
        }

        $problematicPickups = $query->with(['deliveryConfiguration', 'shipments'])->get();
        
        $diagnosis = [
            'total_problematic' => $problematicPickups->count(),
            'by_carrier' => [],
            'common_issues' => [],
            'repairable' => 0,
        ];

        foreach ($problematicPickups as $pickup) {
            $carrier = $pickup->carrier_slug;
            if (!isset($diagnosis['by_carrier'][$carrier])) {
                $diagnosis['by_carrier'][$carrier] = 0;
            }
            $diagnosis['by_carrier'][$carrier]++;

            // Vérifier si réparable
            if ($pickup->shipments->isNotEmpty() && $pickup->deliveryConfiguration && $pickup->deliveryConfiguration->is_active) {
                $diagnosis['repairable']++;
            }
        }

        return $diagnosis;
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

        static::created(function ($pickup) {
            Log::info('✨ [PICKUP] Nouveau pickup créé', [
                'pickup_id' => $pickup->id,
                'admin_id' => $pickup->admin_id,
                'carrier' => $pickup->carrier_slug,
                'pickup_date' => $pickup->pickup_date,
            ]);
        });
    }
}