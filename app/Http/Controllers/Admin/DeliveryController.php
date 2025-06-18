<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryConfiguration;
use App\Models\PickupAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Exception;

class DeliveryController extends Controller
{
    /**
     * Page principale de configuration des transporteurs
     */
    public function configuration()
    {
        $admin = auth('admin')->user();
        
        // Récupérer les configurations existantes
        $configurations = DeliveryConfiguration::where('admin_id', $admin->id)
            ->latest()
            ->get();

        // Récupérer les adresses d'enlèvement
        $pickupAddresses = PickupAddress::where('admin_id', $admin->id)
            ->where('is_active', true)
            ->orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get();

        // Transporteurs supportés
        $supportedCarriers = $this->getSupportedCarriers();
        $availableCarriers = $this->getAvailableCarriers();

        // Statistiques rapides
        $stats = [
            'total_configs' => $configurations->count(),
            'active_configs' => $configurations->where('is_active', true)->count(),
            'total_addresses' => $pickupAddresses->count(),
            'expired_tokens' => $configurations->where('is_active', true)->filter(function($config) {
                return !$this->hasValidToken($config);
            })->count(),
        ];

        return view('admin.delivery.configuration', compact(
            'configurations', 
            'pickupAddresses', 
            'supportedCarriers',
            'availableCarriers',
            'stats'
        ));
    }

    /**
     * Créer une nouvelle configuration de transporteur (SANS test automatique)
     */
    public function storeConfiguration(Request $request)
    {
        try {
            $admin = auth('admin')->user();

            $validator = Validator::make($request->all(), [
                'carrier_slug' => 'required|string|in:fparcel',
                'integration_name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('delivery_configurations')
                        ->where('admin_id', $admin->id)
                        ->where('carrier_slug', $request->carrier_slug),
                ],
                'username' => 'required|string|min:3|max:255',
                'password' => 'required|string|min:6',
                'environment' => 'required|in:test,prod',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error', 'Erreur de validation des données.');
            }

            // Créer la configuration DIRECTEMENT sans test
            $configuration = DeliveryConfiguration::create([
                'admin_id' => $admin->id,
                'carrier_slug' => $request->carrier_slug,
                'integration_name' => $request->integration_name,
                'username' => $request->username,
                'password' => $request->password, // Sera chiffré automatiquement
                'environment' => $request->environment,
                'token' => null, // Sera défini lors du test de connexion
                'expires_at' => null,
                'is_active' => true,
            ]);

            Log::info('Configuration créée avec succès', [
                'admin_id' => $admin->id,
                'config_id' => $configuration->id,
                'carrier' => $request->carrier_slug
            ]);

            return redirect()->route('admin.delivery.configuration')
                ->with('success', 'Configuration du transporteur créée avec succès. Vous pouvez maintenant tester la connexion.');

        } catch (Exception $e) {
            Log::error('Erreur lors de la création de configuration', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'admin_id' => auth('admin')->id(),
                'request_data' => $request->except(['password'])
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la création : ' . $e->getMessage());
        }
    }

    /**
     * Créer une adresse d'enlèvement (VERSION CORRIGÉE)
     */
    public function storePickupAddress(Request $request)
    {
        DB::beginTransaction();
        
        try {
            $admin = auth('admin')->user();

            Log::info('Tentative de création d\'adresse', [
                'admin_id' => $admin->id,
                'request_data' => $request->all()
            ]);

            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('pickup_addresses')
                        ->where('admin_id', $admin->id),
                ],
                'contact_name' => 'required|string|max:255',
                'address' => 'required|string|max:1000',
                'postal_code' => 'nullable|string|max:20',
                'city' => 'nullable|string|max:255',
                'phone' => 'required|string|max:20',
                'email' => 'nullable|email|max:255',
                'is_default' => 'nullable',
            ]);

            if ($validator->fails()) {
                Log::warning('Validation échouée pour adresse', [
                    'errors' => $validator->errors()->toArray(),
                    'admin_id' => $admin->id
                ]);

                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error', 'Erreur de validation des données.');
            }

            // Vérifier s'il existe déjà une adresse par défaut
            $isDefault = $request->has('is_default') && $request->is_default;
            
            if ($isDefault) {
                // Désactiver les autres adresses par défaut
                PickupAddress::where('admin_id', $admin->id)
                    ->update(['is_default' => false]);
                    
                Log::info('Autres adresses par défaut désactivées', ['admin_id' => $admin->id]);
            }

            // Créer l'adresse
            $address = PickupAddress::create([
                'admin_id' => $admin->id,
                'name' => trim($request->name),
                'contact_name' => trim($request->contact_name),
                'address' => trim($request->address),
                'postal_code' => $request->postal_code ? trim($request->postal_code) : null,
                'city' => $request->city ? trim($request->city) : null,
                'phone' => trim($request->phone),
                'email' => $request->email ? trim($request->email) : null,
                'is_default' => $isDefault,
                'is_active' => true,
            ]);

            Log::info('Adresse créée avec succès', [
                'admin_id' => $admin->id,
                'address_id' => $address->id,
                'name' => $address->name
            ]);

            DB::commit();

            return redirect()->route('admin.delivery.configuration')
                ->with('success', 'Adresse d\'enlèvement créée avec succès.');

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Erreur lors de la création d\'adresse', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'admin_id' => auth('admin')->id(),
                'request_data' => $request->all()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de l\'enregistrement : ' . $e->getMessage());
        }
    }

    /**
     * Importer les adresses depuis Fparcel
     */
    public function importFparcelAddresses(DeliveryConfiguration $config)
    {
        try {
            Log::info('Début import adresses Fparcel', ['config_id' => $config->id]);

            // Vérifier que la configuration a un token valide
            if (!$this->hasValidToken($config)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token invalide. Veuillez d\'abord tester la connexion.'
                ], 400);
            }

            // Récupérer les drop points depuis Fparcel
            $dropPoints = $this->getFparcelDropPoints($config);
            
            if (!$dropPoints['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la récupération des adresses : ' . $dropPoints['message']
                ]);
            }

            $imported = 0;
            $skipped = 0;
            $admin = auth('admin')->user();

            DB::beginTransaction();

            foreach ($dropPoints['data'] as $dropPoint) {
                // Vérifier si l'adresse existe déjà
                $exists = PickupAddress::where('admin_id', $admin->id)
                    ->where('name', $dropPoint['name'])
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                // Créer l'adresse
                PickupAddress::create([
                    'admin_id' => $admin->id,
                    'name' => $dropPoint['name'],
                    'contact_name' => $dropPoint['contact_name'] ?? 'Contact Fparcel',
                    'address' => $dropPoint['address'],
                    'postal_code' => $dropPoint['postal_code'] ?? null,
                    'city' => $dropPoint['city'] ?? null,
                    'phone' => $dropPoint['phone'] ?? '00000000',
                    'email' => $dropPoint['email'] ?? null,
                    'is_default' => false,
                    'is_active' => true,
                ]);

                $imported++;
            }

            DB::commit();

            Log::info('Import adresses Fparcel terminé', [
                'config_id' => $config->id,
                'imported' => $imported,
                'skipped' => $skipped
            ]);

            return response()->json([
                'success' => true,
                'message' => "Import terminé : {$imported} adresses importées, {$skipped} ignorées (déjà existantes).",
                'data' => [
                    'imported' => $imported,
                    'skipped' => $skipped
                ]
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Erreur lors de l\'import adresses Fparcel', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'config_id' => $config->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'import : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les drop points depuis Fparcel
     */
    private function getFparcelDropPoints(DeliveryConfiguration $config): array
    {
        try {
            $baseUrl = $config->environment === 'prod' 
                ? 'https://admin.fparcel.net/WebServiceExterne' 
                : 'http://fparcel.net:59/WebServiceExterne';

            Log::info('Récupération drop points Fparcel', [
                'url' => $baseUrl . '/droppoint_list'
            ]);

            $response = Http::timeout(30)
                ->get($baseUrl . '/droppoint_list');

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => 'Erreur HTTP ' . $response->status()
                ];
            }

            $data = $response->json();
            
            if (!is_array($data)) {
                return [
                    'success' => false,
                    'message' => 'Format de réponse invalide'
                ];
            }

            // Transformer les données Fparcel en format standard
            $addresses = [];
            foreach ($data as $item) {
                $addresses[] = [
                    'name' => $item['NOM'] ?? ($item['DESIGNATION'] ?? 'Agence Fparcel'),
                    'contact_name' => $item['CONTACT'] ?? ($item['NOM'] ?? 'Contact'),
                    'address' => $item['ADRESSE'] ?? ($item['ADDRESS'] ?? ''),
                    'postal_code' => $item['CODE_POSTAL'] ?? ($item['POSTAL_CODE'] ?? null),
                    'city' => $item['VILLE'] ?? ($item['CITY'] ?? null),
                    'phone' => $item['TELEPHONE'] ?? ($item['PHONE'] ?? null),
                    'email' => $item['EMAIL'] ?? ($item['MAIL'] ?? null),
                ];
            }

            Log::info('Drop points récupérés avec succès', [
                'count' => count($addresses)
            ]);

            return [
                'success' => true,
                'data' => $addresses
            ];

        } catch (Exception $e) {
            Log::error('Erreur récupération drop points', [
                'error' => $e->getMessage(),
                'config_id' => $config->id
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Tester la connexion d'une configuration (action séparée)
     */
    public function testConnection(DeliveryConfiguration $config)
    {
        try {
            $result = $this->testFparcelConnection([
                'username' => $config->username,
                'password' => $this->getDecryptedPassword($config),
                'environment' => $config->environment,
            ]);

            if ($result['success']) {
                // Mettre à jour le token
                $config->update([
                    'token' => $result['data']['token'],
                    'expires_at' => $result['data']['expires_at'],
                ]);

                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'token_expires_at' => $result['data']['expires_at'],
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 400);
            }

        } catch (Exception $e) {
            Log::error('Erreur lors du test de connexion', [
                'error' => $e->getMessage(),
                'config_id' => $config->id,
                'admin_id' => auth('admin')->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du test : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Rafraîchir le token d'une configuration
     */
    public function refreshToken(DeliveryConfiguration $config)
    {
        try {
            $result = $this->testFparcelConnection([
                'username' => $config->username,
                'password' => $this->getDecryptedPassword($config),
                'environment' => $config->environment,
            ]);

            if ($result['success']) {
                $config->update([
                    'token' => $result['data']['token'],
                    'expires_at' => $result['data']['expires_at'],
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Token rafraîchi avec succès.',
                    'expires_at' => $result['data']['expires_at'],
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de rafraîchir le token : ' . $result['message'],
                ], 400);
            }

        } catch (Exception $e) {
            Log::error('Erreur lors du rafraîchissement de token', [
                'error' => $e->getMessage(),
                'config_id' => $config->id,
                'admin_id' => auth('admin')->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du rafraîchissement : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Activer/désactiver une configuration
     */
    public function toggleConfiguration(DeliveryConfiguration $config)
    {
        try {
            $config->update(['is_active' => !$config->is_active]);

            $status = $config->is_active ? 'activée' : 'désactivée';

            return response()->json([
                'success' => true,
                'message' => "Configuration {$status} avec succès.",
                'is_active' => $config->is_active,
            ]);

        } catch (Exception $e) {
            Log::error('Erreur lors du changement de statut', [
                'error' => $e->getMessage(),
                'config_id' => $config->id,
                'admin_id' => auth('admin')->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du changement de statut : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Supprimer une configuration
     */
    public function deleteConfiguration(DeliveryConfiguration $config)
    {
        try {
            $config->delete();

            return response()->json([
                'success' => true,
                'message' => 'Configuration supprimée avec succès.'
            ]);

        } catch (Exception $e) {
            Log::error('Erreur lors de la suppression de configuration', [
                'error' => $e->getMessage(),
                'config_id' => $config->id,
                'admin_id' => auth('admin')->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer une adresse d'enlèvement
     */
    public function deletePickupAddress(PickupAddress $address)
    {
        try {
            $address->delete();

            return response()->json([
                'success' => true,
                'message' => 'Adresse d\'enlèvement supprimée avec succès.'
            ]);

        } catch (Exception $e) {
            Log::error('Erreur lors de la suppression d\'adresse', [
                'error' => $e->getMessage(),
                'address_id' => $address->id,
                'admin_id' => auth('admin')->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Définir une adresse comme par défaut
     */
    public function setDefaultAddress(PickupAddress $address)
    {
        try {
            $admin = auth('admin')->user();
            
            DB::beginTransaction();
            
            // Désactiver toutes les autres adresses par défaut
            PickupAddress::where('admin_id', $admin->id)
                ->update(['is_default' => false]);
            
            // Activer celle-ci
            $address->update(['is_default' => true, 'is_active' => true]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Adresse définie comme par défaut avec succès.'
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Erreur lors de la définition d\'adresse par défaut', [
                'error' => $e->getMessage(),
                'address_id' => $address->id,
                'admin_id' => auth('admin')->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tester la connexion avec l'API Fparcel - VERSION SIMPLE ET DÉFINITIVE
     */
    private function testFparcelConnection(array $credentials): array
    {
        try {
            $baseUrl = $credentials['environment'] === 'prod' 
                ? 'https://admin.fparcel.net/WebServiceExterne' 
                : 'http://fparcel.net:59/WebServiceExterne';

            $response = Http::timeout(30)
                ->asForm()
                ->post($baseUrl . '/get_token', [
                    'USERNAME' => $credentials['username'],
                    'PASSWORD' => $credentials['password'],
                ]);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => 'Erreur HTTP ' . $response->status()
                ];
            }

            $responseBody = trim($response->body());
            
            // Log pour debug
            Log::info('Réponse Fparcel', ['body' => $responseBody]);
            
            // Extraire le token de toutes les façons possibles
            $token = null;
            
            // Cas 1: JSON avec structure {"TOKEN": "..."}
            $data = $response->json();
            if (is_array($data) && isset($data['TOKEN'])) {
                $token = $data['TOKEN'];
            }
            // Cas 2: JSON string directe "token_value"
            elseif (is_string($data) && strlen($data) > 10) {
                $token = $data;
            }
            // Cas 3: String JSON quoted dans le body brut
            elseif (preg_match('/^"([^"]+)"$/', $responseBody, $matches)) {
                $token = $matches[1];
            }
            // Cas 4: Texte brut qui ressemble à un token
            elseif (strlen($responseBody) > 10 && !str_contains(strtolower($responseBody), 'error')) {
                $token = $responseBody;
            }
            
            // Si on a trouvé un token
            if ($token) {
                // Nettoyer le token (enlever les échappements)
                $token = str_replace(['\\/', '\\"'], ['/', '"'], $token);
                
                Log::info('Token Fparcel extrait avec succès', ['token' => $token]);
                
                return [
                    'success' => true,
                    'message' => 'Connexion réussie avec Fparcel',
                    'data' => [
                        'token' => $token,
                        'expires_at' => now()->addHour(),
                    ]
                ];
            }
            
            // Échec d'extraction
            return [
                'success' => false,
                'message' => 'Impossible d\'extraire le token de la réponse: ' . $responseBody
            ];

        } catch (Exception $e) {
            Log::error('Erreur test connexion Fparcel', [
                'error' => $e->getMessage(),
                'username' => $credentials['username'],
                'environment' => $credentials['environment']
            ]);

            return [
                'success' => false,
                'message' => 'Erreur de connexion : ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtenir le mot de passe déchiffré
     */
    private function getDecryptedPassword(DeliveryConfiguration $config): string
    {
        try {
            return \Illuminate\Support\Facades\Crypt::decryptString($config->password);
        } catch (Exception $e) {
            // Si le déchiffrement échoue, retourner le mot de passe tel quel
            return $config->password;
        }
    }

    /**
     * Vérifier si le token est valide
     */
    private function hasValidToken(DeliveryConfiguration $config): bool
    {
        return $config->token && $config->expires_at && $config->expires_at->isFuture();
    }

    /**
     * Obtenir les transporteurs supportés
     */
    private function getSupportedCarriers(): array
    {
        return [
            'fparcel' => [
                'name' => 'Fparcel',
                'display_name' => 'Fparcel Tunisia',
                'supports_pickup_address' => true,
                'supports_tracking' => true,
                'supports_mass_labels' => true,
                'features' => ['cod', 'tracking', 'mass_labels', 'pickup_scheduling']
            ]
        ];
    }

    /**
     * Obtenir les transporteurs disponibles
     */
    private function getAvailableCarriers(): array
    {
        return [
            'fparcel' => [
                'name' => 'Fparcel',
                'display_name' => 'Fparcel Tunisia',
                'description' => 'Service de livraison tunisien',
                'website' => 'https://fparcel.com',
                'environments' => ['test', 'prod']
            ]
        ];
    }
}