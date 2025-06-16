<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryConfiguration;
use App\Models\PickupAddress;
use App\Services\Shipping\ShippingServiceFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DeliveryController extends Controller
{
    private ShippingServiceFactory $shippingFactory;

    public function __construct(ShippingServiceFactory $shippingFactory)
    {
        $this->shippingFactory = $shippingFactory;
    }

    /**
     * Page principale de configuration des transporteurs
     */
    public function configuration()
    {
        $admin = auth('admin')->user();
        
        // Récupérer les configurations existantes
        $configurations = $admin->deliveryConfigurations()
            ->with(['pickups' => function($query) {
                $query->latest()->take(5);
            }])
            ->latest()
            ->get();

        // Récupérer les adresses d'enlèvement
        $pickupAddresses = $admin->pickupAddresses()
            ->where('is_active', true)
            ->orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get();

        // Transporteurs supportés
        $supportedCarriers = $this->shippingFactory->getSupportedCarriers();

        // Statistiques rapides
        $stats = [
            'total_configs' => $configurations->count(),
            'active_configs' => $configurations->where('is_active', true)->count(),
            'total_addresses' => $pickupAddresses->count(),
            'expired_tokens' => $configurations->where('is_active', true)->filter(function($config) {
                return !$config->hasValidToken();
            })->count(),
        ];

        return view('admin.delivery.configuration', compact(
            'configurations', 
            'pickupAddresses', 
            'supportedCarriers', 
            'stats'
        ));
    }

    /**
     * Créer une nouvelle configuration de transporteur
     */
    public function storeConfiguration(Request $request)
    {
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

        try {
            // Tester la connexion avant de sauvegarder
            $testResult = $this->shippingFactory->testCarrierConnection(
                $request->carrier_slug,
                $request->only(['username', 'password', 'environment'])
            );

            if (!$testResult['success']) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Impossible de se connecter au transporteur : ' . $testResult['message']);
            }

            // Créer la configuration
            $configuration = DeliveryConfiguration::create([
                'admin_id' => $admin->id,
                'carrier_slug' => $request->carrier_slug,
                'integration_name' => $request->integration_name,
                'username' => $request->username,
                'password' => $request->password, // Sera chiffré automatiquement
                'environment' => $request->environment,
                'token' => $testResult['data']['token_obtained'] ?? null,
                'expires_at' => $testResult['data']['token_expires_at'] ?? null,
                'is_active' => true,
            ]);

            return redirect()->route('admin.delivery.configuration')
                ->with('success', 'Configuration du transporteur créée avec succès.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la création : ' . $e->getMessage());
        }
    }

    /**
     * Mettre à jour une configuration existante
     */
    public function updateConfiguration(Request $request, DeliveryConfiguration $config)
    {
        $this->authorize('update', $config);

        $validator = Validator::make($request->all(), [
            'integration_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('delivery_configurations')
                    ->where('admin_id', $config->admin_id)
                    ->where('carrier_slug', $config->carrier_slug)
                    ->ignore($config->id),
            ],
            'username' => 'required|string|min:3|max:255',
            'password' => 'nullable|string|min:6',
            'environment' => 'required|in:test,prod',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->with('error', 'Erreur de validation des données.');
        }

        try {
            $updateData = [
                'integration_name' => $request->integration_name,
                'username' => $request->username,
                'environment' => $request->environment,
                'is_active' => $request->has('is_active'),
            ];

            // Mettre à jour le mot de passe si fourni
            if ($request->filled('password')) {
                $updateData['password'] = $request->password;
                
                // Tester la nouvelle connexion
                $testResult = $this->shippingFactory->testCarrierConnection(
                    $config->carrier_slug,
                    [
                        'username' => $request->username,
                        'password' => $request->password,
                        'environment' => $request->environment,
                    ]
                );

                if (!$testResult['success']) {
                    return redirect()->back()
                        ->with('error', 'Impossible de se connecter avec les nouveaux identifiants : ' . $testResult['message']);
                }

                $updateData['token'] = $testResult['data']['token_obtained'] ?? null;
                $updateData['expires_at'] = $testResult['data']['token_expires_at'] ?? null;
            }

            $config->update($updateData);

            return redirect()->route('admin.delivery.configuration')
                ->with('success', 'Configuration mise à jour avec succès.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
        }
    }

    /**
     * Supprimer une configuration
     */
    public function deleteConfiguration(DeliveryConfiguration $config)
    {
        $this->authorize('delete', $config);

        try {
            // Vérifier qu'il n'y a pas d'enlèvements actifs
            $activePickups = $config->pickups()
                ->whereIn('status', ['draft', 'validated'])
                ->count();

            if ($activePickups > 0) {
                return redirect()->back()
                    ->with('error', 'Impossible de supprimer cette configuration car elle a des enlèvements en cours.');
            }

            $config->delete();

            return redirect()->route('admin.delivery.configuration')
                ->with('success', 'Configuration supprimée avec succès.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }

    /**
     * Tester la connexion d'une configuration
     */
    public function testConnection(DeliveryConfiguration $config)
    {
        $this->authorize('view', $config);

        try {
            $result = $config->testConnection();

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'token_expires_at' => $result['token_expires_at'] ?? null,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 400);
            }

        } catch (\Exception $e) {
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
        $this->authorize('update', $config);

        try {
            if ($config->refreshToken()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Token rafraîchi avec succès.',
                    'expires_at' => $config->expires_at,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de rafraîchir le token.',
                ], 400);
            }

        } catch (\Exception $e) {
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
        $this->authorize('update', $config);

        try {
            // Si on désactive, vérifier qu'il n'y a pas d'enlèvements actifs
            if ($config->is_active) {
                $activePickups = $config->pickups()
                    ->whereIn('status', ['draft', 'validated'])
                    ->count();

                if ($activePickups > 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Impossible de désactiver cette configuration car elle a des enlèvements en cours.',
                    ], 400);
                }
            }

            $config->update(['is_active' => !$config->is_active]);

            $status = $config->is_active ? 'activée' : 'désactivée';

            return response()->json([
                'success' => true,
                'message' => "Configuration {$status} avec succès.",
                'is_active' => $config->is_active,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du changement de statut : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtenir les transporteurs supportés
     */
    public function getCarriers()
    {
        try {
            $carriers = $this->shippingFactory->getSupportedCarriers();
            
            return response()->json([
                'success' => true,
                'carriers' => $carriers,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des transporteurs : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques de livraison
     */
    public function getStats()
    {
        try {
            $admin = auth('admin')->user();
            $stats = $this->shippingFactory->getCarrierStats($admin->id);
            
            return response()->json([
                'success' => true,
                'stats' => $stats,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Créer une adresse d'enlèvement
     */
    public function storePickupAddress(Request $request)
    {
        $admin = auth('admin')->user();

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('pickup_addresses')
                    ->where('admin_id', $admin->id),
            ],
            'contact_name' => 'required|string|max:255',
            'address' => 'required|string',
            'postal_code' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'is_default' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $address = PickupAddress::create([
                'admin_id' => $admin->id,
                'name' => $request->name,
                'contact_name' => $request->contact_name,
                'address' => $request->address,
                'postal_code' => $request->postal_code,
                'city' => $request->city,
                'phone' => $request->phone,
                'email' => $request->email,
                'is_default' => $request->has('is_default'),
                'is_active' => true,
            ]);

            // Si marquée comme par défaut, désactiver les autres
            if ($address->is_default) {
                $address->setAsDefault();
            }

            return redirect()->route('admin.delivery.configuration')
                ->with('success', 'Adresse d\'enlèvement créée avec succès.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la création : ' . $e->getMessage());
        }
    }

    /**
     * Supprimer une adresse d'enlèvement
     */
    public function deletePickupAddress(PickupAddress $address)
    {
        $this->authorize('delete', $address);

        try {
            if (!$address->canBeDeleted()) {
                return redirect()->back()
                    ->with('error', 'Impossible de supprimer cette adresse car elle est utilisée par des enlèvements.');
            }

            $address->delete();

            return redirect()->route('admin.delivery.configuration')
                ->with('success', 'Adresse d\'enlèvement supprimée avec succès.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }
}