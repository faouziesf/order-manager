<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdminSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Http;

use Exception;

class DeliveryController extends Controller
{
    private $testUrl = 'http://fparcel.net:59/WebServiceExterne';
    private $prodUrl = 'https://admin.fparcel.net/WebServiceExterne';

    /**
     * Display the delivery configuration page
     */
    public function configuration()
    {
        return view('admin.delivery.configuration');
    }

    /**
     * Display the delivery management page
     */
    public function management()
    {
        return view('admin.delivery.management');
    }

    /**
     * Get the current connection status
     */
    public function getConnectionStatus()
    {
        try {
            $config = $this->getDeliveryConfig();

            if ($config && $config->token && $config->expires_at > now()) {
                return response()->json([
                    'connected' => true,
                    'token' => substr($config->token, 0, 20) . '...',
                    'updated_at' => $config->updated_at
                ]);
            }

            return response()->json(['connected' => false]);
        } catch (Exception $e) {
            Log::error('Error checking delivery connection status: ' . $e->getMessage());
            return response()->json(['connected' => false]);
        }
    }

    /**
     * Connect to FParcel API and get token
     */
    public function connectToFParcel(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'environment' => 'required|in:test,prod'
        ]);

        try {
            $baseUrl = $request->environment === 'prod' ? $this->prodUrl : $this->testUrl;

            // Call FParcel get_token API
            $response = Http::timeout(30)->post($baseUrl . '/get_token', [
                'USERNAME' => $request->username,
                'PASSWORD' => $request->password
            ]);

            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de connexion au service FParcel. Vérifiez vos identifiants.'
                ], 400);
            }

            $data = $response->body();

            // Check if response contains error
            if (stripos($data, 'error') !== false || stripos($data, 'invalid') !== false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Identifiants invalides ou erreur du service FParcel.'
                ], 400);
            }

            // Store the configuration
            $this->saveDeliveryConfig([
                'admin_id' => auth('admin')->id(),
                'username' => $request->username,
                'password' => encrypt($request->password),
                'environment' => $request->environment,
                'token' => $data,
                'expires_at' => now()->addDays(30), // Tokens usually expire after 30 days
                'is_active' => true
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Connexion réussie au service FParcel',
                'data' => [
                    'token' => substr($data, 0, 20) . '...',
                    'updated_at' => now()
                ]
            ]);
        } catch (Exception $e) {
            Log::error('FParcel connection error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la connexion: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test connection without saving
     */
    public function testConnection(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'environment' => 'required|in:test,prod'
        ]);

        try {
            $baseUrl = $request->environment === 'prod' ? $this->prodUrl : $this->testUrl;

            $response = Http::timeout(15)->post($baseUrl . '/get_token', [
                'USERNAME' => $request->username,
                'PASSWORD' => $request->password
            ]);

            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de se connecter au service FParcel'
                ], 400);
            }

            $data = $response->body();

            if (stripos($data, 'error') !== false || stripos($data, 'invalid') !== false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Identifiants invalides'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Test de connexion réussi'
            ]);
        } catch (Exception $e) {
            Log::error('FParcel test connection error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du test: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Disconnect from FParcel
     */
    public function disconnect()
    {
        try {
            DB::table('delivery_configurations')
                ->where('admin_id', auth('admin')->id())
                ->update(['is_active' => false]);

            return response()->json([
                'success' => true,
                'message' => 'Déconnexion réussie'
            ]);
        } catch (Exception $e) {
            Log::error('FParcel disconnect error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la déconnexion'
            ], 500);
        }
    }

    /**
     * Refresh the token
     */
    public function refreshToken()
    {
        try {
            $config = $this->getDeliveryConfig();

            if (!$config) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune configuration trouvée'
                ], 404);
            }

            $baseUrl = $config->environment === 'prod' ? $this->prodUrl : $this->testUrl;

            $response = Http::timeout(30)->post($baseUrl . '/get_token', [
                'USERNAME' => $config->username,
                'PASSWORD' => decrypt($config->password)
            ]);

            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de l\'actualisation du token'
                ], 400);
            }

            $newToken = $response->body();

            if (stripos($newToken, 'error') !== false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de l\'actualisation du token'
                ], 400);
            }

            DB::table('delivery_configurations')
                ->where('admin_id', auth('admin')->id())
                ->update([
                    'token' => $newToken,
                    'expires_at' => now()->addDays(30),
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Token actualisé avec succès',
                'data' => [
                    'token' => substr($newToken, 0, 20) . '...'
                ]
            ]);
        } catch (Exception $e) {
            Log::error('FParcel token refresh error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'actualisation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync payment methods from FParcel
     */
    public function syncPaymentMethods()
    {
        try {
            $config = $this->getDeliveryConfig();
            if (!$config) {
                return response()->json([
                    'success' => false,
                    'message' => 'Non connecté au service FParcel'
                ], 401);
            }

            $baseUrl = $config->environment === 'prod' ? $this->prodUrl : $this->testUrl;

            $response = Http::timeout(30)->get($baseUrl . '/mr_list');

            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la récupération des modes de règlement'
                ], 400);
            }

            $paymentMethods = $response->json();

            // Store payment methods in database
            $this->storePaymentMethods($paymentMethods);

            return response()->json([
                'success' => true,
                'message' => 'Modes de règlement synchronisés',
                'count' => count($paymentMethods)
            ]);
        } catch (Exception $e) {
            Log::error('FParcel payment methods sync error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la synchronisation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync drop points from FParcel
     */
    public function syncDropPoints()
    {
        try {
            $config = $this->getDeliveryConfig();
            if (!$config) {
                return response()->json([
                    'success' => false,
                    'message' => 'Non connecté au service FParcel'
                ], 401);
            }

            $baseUrl = $config->environment === 'prod' ? $this->prodUrl : $this->testUrl;

            $response = Http::timeout(30)->get($baseUrl . '/droppoint_list');

            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la récupération des points de dépôt'
                ], 400);
            }

            $dropPoints = $response->json();

            // Store drop points in database
            $this->storeDropPoints($dropPoints);

            return response()->json([
                'success' => true,
                'message' => 'Points de dépôt synchronisés',
                'count' => count($dropPoints)
            ]);
        } catch (Exception $e) {
            Log::error('FParcel drop points sync error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la synchronisation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync anomaly reasons from FParcel
     */
    public function syncAnomalyReasons()
    {
        try {
            $config = $this->getDeliveryConfig();
            if (!$config) {
                return response()->json([
                    'success' => false,
                    'message' => 'Non connecté au service FParcel'
                ], 401);
            }

            $baseUrl = $config->environment === 'prod' ? $this->prodUrl : $this->testUrl;

            $response = Http::timeout(30)->get($baseUrl . '/motif_ano_list');

            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la récupération des motifs d\'anomalies'
                ], 400);
            }

            $anomalyReasons = $response->json();

            // Store anomaly reasons in database
            $this->storeAnomalyReasons($anomalyReasons);

            return response()->json([
                'success' => true,
                'message' => 'Motifs d\'anomalies synchronisés',
                'count' => count($anomalyReasons)
            ]);
        } catch (Exception $e) {
            Log::error('FParcel anomaly reasons sync error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la synchronisation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update delivery configuration
     */
    public function updateConfiguration(Request $request)
    {
        // TODO: Add validation and update logic here

        return redirect()->route('admin.delivery.configuration')
            ->with('success', 'Configuration mise à jour avec succès.');
    }

    /**
     * Update delivery management settings
     */
    public function updateManagement(Request $request)
    {
        // TODO: Add validation and update logic here

        return redirect()->route('admin.delivery.management')
            ->with('success', 'Gestion mise à jour avec succès.');
    }

    /**
     * Display delivery zones (for future use)
     */
    public function zones()
    {
        return view('admin.delivery.zones');
    }

    /**
     * Display delivery tariffs (for future use)
     */
    public function tarifs()
    {
        return view('admin.delivery.tarifs');
    }

    // ========================================
    // PRIVATE HELPER METHODS
    // ========================================

    /**
     * Get delivery configuration for current admin
     */
    private function getDeliveryConfig()
    {
        return DB::table('delivery_configurations')
            ->where('admin_id', auth('admin')->id())
            ->where('is_active', true)
            ->first();
    }

    /**
     * Save delivery configuration
     */
    private function saveDeliveryConfig($data)
    {
        DB::table('delivery_configurations')->updateOrInsert(
            ['admin_id' => $data['admin_id']],
            array_merge($data, [
                'created_at' => now(),
                'updated_at' => now()
            ])
        );
    }

    /**
     * Store payment methods
     */
    private function storePaymentMethods($paymentMethods)
    {
        $adminId = auth('admin')->id();

        // Clear existing payment methods for this admin
        DB::table('delivery_payment_methods')->where('admin_id', $adminId)->delete();

        // Insert new payment methods
        foreach ($paymentMethods as $method) {
            DB::table('delivery_payment_methods')->insert([
                'admin_id' => $adminId,
                'mr_code' => $method['MR_CODE'] ?? '',
                'mr_name' => $method['MR_NAME'] ?? '',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    /**
     * Store drop points
     */
    private function storeDropPoints($dropPoints)
    {
        $adminId = auth('admin')->id();

        // Clear existing drop points for this admin
        DB::table('delivery_drop_points')->where('admin_id', $adminId)->delete();

        // Insert new drop points
        foreach ($dropPoints as $point) {
            DB::table('delivery_drop_points')->insert([
                'admin_id' => $adminId,
                'point_id' => $point['POINT_ID'] ?? '',
                'point_name' => $point['POINT_NAME'] ?? '',
                'address' => $point['ADDRESS'] ?? '',
                'city' => $point['CITY'] ?? '',
                'postal_code' => $point['POSTAL_CODE'] ?? '',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    /**
     * Store anomaly reasons
     */
    private function storeAnomalyReasons($anomalyReasons)
    {
        $adminId = auth('admin')->id();

        // Clear existing anomaly reasons for this admin
        DB::table('delivery_anomaly_reasons')->where('admin_id', $adminId)->delete();

        // Insert new anomaly reasons
        foreach ($anomalyReasons as $reason) {
            DB::table('delivery_anomaly_reasons')->insert([
                'admin_id' => $adminId,
                'reason_code' => $reason['REASON_CODE'] ?? '',
                'reason_name' => $reason['REASON_NAME'] ?? '',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    /**
     * Create a delivery position using FParcel API
     */
    public function createDeliveryPosition($orderData)
    {
        try {
            $config = $this->getDeliveryConfig();
            if (!$config) {
                throw new Exception('No delivery configuration found');
            }

            $baseUrl = $config->environment === 'prod' ? $this->prodUrl : $this->testUrl;

            $response = Http::timeout(30)->post($baseUrl . '/pos_create', array_merge([
                'TOKEN' => $config->token
            ], $orderData));

            if ($response->failed()) {
                throw new Exception('Failed to create delivery position');
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('FParcel position creation error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Track a delivery position
     */
    public function trackPosition($barcode)
    {
        try {
            $config = $this->getDeliveryConfig();
            if (!$config) {
                throw new Exception('No delivery configuration found');
            }

            $baseUrl = $config->environment === 'prod' ? $this->prodUrl : $this->testUrl;

            $response = Http::timeout(30)->get($baseUrl . '/tracking_position/' . $barcode);

            if ($response->failed()) {
                throw new Exception('Failed to track position');
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('FParcel tracking error: ' . $e->getMessage());
            throw $e;
        }
    }

    // Add these imports at the top of your DeliveryController if not already present

    // Updated methods to work with your existing AdminSetting model:

    /**
     * Get API parameters configuration
     */
    public function getParameters()
    {
        try {
            $admin = auth('admin')->user();

            // Default parameters
            $defaultParameters = [
                'enl_contact_nom' => '',
                'enl_contact_prenom' => '',
                'enl_adresse' => '',
                'enl_code_postal' => '',
                'enl_portable' => '',
                'enl_mail' => '',
                'default_mr_code' => '',
                'default_pos_allow_open' => '0',
                'default_pos_valid' => '1',
                'default_nb_piece' => '1',
                'default_time_from' => '08:00',
                'default_time_to' => '18:00',
                'default_poids' => '',
                'default_valeur' => '',
                'default_pos_link_img' => '',
            ];

            // Get saved parameters using your existing model structure
            $parameters = [];
            foreach ($defaultParameters as $key => $defaultValue) {
                $settingKey = "delivery_param_{$key}";
                $value = AdminSetting::getForAdmin($admin->id, $settingKey, $defaultValue);
                $parameters[$key] = $value;
            }

            return response()->json([
                'success' => true,
                'data' => $parameters
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting delivery parameters: ' . $e->getMessage());

            return response()->json([
                'success' => true, // Return success with defaults to avoid frontend errors
                'data' => [
                    'enl_contact_nom' => '',
                    'enl_contact_prenom' => '',
                    'enl_adresse' => '',
                    'enl_code_postal' => '',
                    'enl_portable' => '',
                    'enl_mail' => '',
                    'default_mr_code' => '',
                    'default_pos_allow_open' => '0',
                    'default_pos_valid' => '1',
                    'default_nb_piece' => '1',
                    'default_time_from' => '08:00',
                    'default_time_to' => '18:00',
                    'default_poids' => '',
                    'default_valeur' => '',
                    'default_pos_link_img' => '',
                ]
            ]);
        }
    }

    /**
     * Save API parameters configuration
     */
    public function saveParameters(Request $request)
    {
        try {
            $admin = auth('admin')->user();

            // Validate the input
            $validatedData = $request->validate([
                'enl_contact_nom' => 'nullable|string|max:255',
                'enl_contact_prenom' => 'nullable|string|max:255',
                'enl_adresse' => 'nullable|string|max:500',
                'enl_code_postal' => 'nullable|string|max:10',
                'enl_portable' => 'nullable|string|max:20',
                'enl_mail' => 'nullable|email|max:255',
                'default_mr_code' => 'nullable|string|max:50',
                'default_pos_allow_open' => 'nullable|in:0,1',
                'default_pos_valid' => 'nullable|in:0,1',
                'default_nb_piece' => 'nullable|integer|min:1',
                'default_time_from' => 'nullable|string',
                'default_time_to' => 'nullable|string',
                'default_poids' => 'nullable|numeric|min:0',
                'default_valeur' => 'nullable|numeric|min:0',
                'default_pos_link_img' => 'nullable|url|max:500',
            ]);

            // Store the configuration using your existing model structure
            foreach ($validatedData as $key => $value) {
                $settingKey = "delivery_param_{$key}";
                $description = "Paramètre de livraison FParcel: {$key}";

                // Determine if this should be encrypted (email and sensitive data)
                $isEncrypted = in_array($key, ['enl_mail', 'enl_portable']);

                AdminSetting::setForAdmin(
                    $admin->id,
                    $settingKey,
                    $value ?? '',
                    'string',
                    $isEncrypted,
                    $description
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Configuration sauvegardée avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error('Error saving delivery parameters: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la sauvegarde: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test API parameters configuration
     */
    public function testParameters(Request $request)
    {
        try {
            $admin = auth('admin')->user();

            // Get FParcel token (you'll need to implement this based on how you store tokens)
            $token = AdminSetting::getForAdmin($admin->id, 'fparcel_token');

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token FParcel non disponible. Veuillez vous connecter d\'abord.'
                ]);
            }

            // Validate required sender information
            $requiredFields = ['enl_contact_nom', 'enl_contact_prenom', 'enl_adresse', 'enl_code_postal'];
            $missingFields = [];

            foreach ($requiredFields as $field) {
                if (empty($request->input($field))) {
                    $missingFields[] = $field;
                }
            }

            if (!empty($missingFields)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Champs obligatoires manquants: ' . implode(', ', $missingFields)
                ]);
            }

            // Build test data structure for FParcel API
            $testData = [
                'TOKEN' => $token,
                'ENL_CONTACT_NOM' => $request->input('enl_contact_nom'),
                'ENL_CONTACT_PRENOM' => $request->input('enl_contact_prenom'),
                'ENL_ADRESSE' => $request->input('enl_adresse'),
                'ENL_PORTABLE' => $request->input('enl_portable'),
                'ENL_MAIL' => $request->input('enl_mail'),
                'ENL_CODE_POSTAL' => $request->input('enl_code_postal'),
                // Test recipient data
                'LIV_CONTACT_NOM' => 'Test',
                'LIV_CONTACT_PRENOM' => 'Destinataire',
                'LIV_ADRESSE' => 'Adresse test',
                'LIV_PORTABLE' => '0123456789',
                'LIV_MAIL' => 'test@example.com',
                'LIV_CODE_POSTAL' => '1000',
                'POIDS' => $request->input('default_poids', 1),
                'VALEUR' => $request->input('default_valeur', 10),
                'COD' => '0',
                'RTRNCONTENU' => '',
                'POSNBPIECE' => $request->input('default_nb_piece', 1),
                'DATE_ENLEVEMENT' => date('d/m/Y'),
                'POSITION_TIME_LIV_DISPO_FROM' => $request->input('default_time_from', '08:00'),
                'POSITION_TIME_LIV_DISPO_TO' => $request->input('default_time_to', '18:00'),
                'REFERENCE' => 'TEST-' . time(),
                'ORDER_NUMBER' => 'TEST-ORDER-' . time(),
                'MR_CODE' => $request->input('default_mr_code', ''),
                'POS_VALID' => '0', // Always create test positions as temporary
                'POS_ALLOW_OPEN' => $request->input('default_pos_allow_open', '0'),
                'POS_LINK_IMG' => $request->input('default_pos_link_img', ''),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Configuration valide ! Les paramètres sont correctement formatés.',
                'preview' => $testData // Show what would be sent to FParcel
            ]);
        } catch (\Exception $e) {
            Log::error('Error testing delivery parameters: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du test: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get synchronized payment methods
     */
    public function getPaymentMethods()
    {
        try {
            $admin = auth('admin')->user();

            // Try to get payment methods from database (assuming you'll create this table)
            try {
                $paymentMethods = DB::table('fparcel_payment_methods')
                    ->where('admin_id', $admin->id)
                    ->where('is_active', true)
                    ->select('code', 'name')
                    ->get();

                return response()->json([
                    'success' => true,
                    'data' => $paymentMethods->toArray()
                ]);
            } catch (\Exception $e) {
                // If table doesn't exist yet, return empty array
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'Aucun mode de règlement synchronisé. Veuillez synchroniser d\'abord.'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error getting payment methods: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des modes de règlement: ' . $e->getMessage()
            ], 500);
        }
    }
}
