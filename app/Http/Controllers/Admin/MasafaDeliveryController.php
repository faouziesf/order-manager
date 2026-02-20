<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasafaConfiguration;
use App\Models\Order;
use App\Services\MasafaExpressService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MasafaDeliveryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * Main delivery page — Masafa Express only
     */
    public function index()
    {
        $admin = Auth::guard('admin')->user();
        $config = $admin->masafaConfiguration;

        $stats = [
            'total_sent'    => Order::where('admin_id', $admin->id)->whereNotNull('tracking_number')->where('carrier_name', 'Masafa Express')->count(),
            'en_cours'      => Order::where('admin_id', $admin->id)->where('carrier_name', 'Masafa Express')->whereIn('status', ['expédiée', 'en_transit', 'tentative_livraison'])->count(),
            'livrees'       => Order::where('admin_id', $admin->id)->where('carrier_name', 'Masafa Express')->where('status', 'livrée')->count(),
            'en_retour'     => Order::where('admin_id', $admin->id)->where('carrier_name', 'Masafa Express')->whereIn('status', ['en_retour', 'échec_livraison'])->count(),
            'pret_envoyer'  => Order::where('admin_id', $admin->id)->where('status', 'confirmée')->whereNull('tracking_number')->count(),
        ];

        $masafaStats = null;
        $connectionOk = false;
        if ($config && $config->is_active && $config->api_token) {
            $service = new MasafaExpressService($config);
            $result  = $service->getStats();
            if ($result['success']) {
                $masafaStats = $result['data'];
                $connectionOk = true;
            }
        }

        $sentOrders = Order::where('admin_id', $admin->id)
            ->where('carrier_name', 'Masafa Express')
            ->whereNotNull('tracking_number')
            ->latest('shipped_at')
            ->paginate(20);

        $readyOrders = Order::where('admin_id', $admin->id)
            ->where('status', 'confirmée')
            ->whereNull('tracking_number')
            ->latest()
            ->get();

        return view('admin.delivery.index', compact(
            'config', 'stats', 'masafaStats', 'connectionOk', 'sentOrders', 'readyOrders'
        ));
    }

    /**
     * Save / update Masafa Express configuration
     */
    public function saveConfig(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $request->validate([
            'api_token'          => 'required|string|min:10',
            'masafa_client_id'   => 'nullable|string|max:100',
            'pickup_name'        => 'nullable|string|max:100',
            'auto_send'          => 'nullable|boolean',
        ]);

        MasafaConfiguration::updateOrCreate(
            ['admin_id' => $admin->id],
            [
                'api_token'        => $request->api_token,
                'masafa_client_id' => $request->masafa_client_id,
                'pickup_name'      => $request->pickup_name,
                'auto_send'        => $request->boolean('auto_send'),
                'is_active'        => true,
            ]
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Configuration sauvegardée.']);
        }

        return back()->with('success', 'Configuration Masafa Express sauvegardée avec succès.');
    }

    /**
     * AJAX — Connect via email+password (OAuth-like flow)
     * Calls masafaexpress /api/connect/token, stores the returned token automatically.
     */
    public function connectWithCredentials(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string|min:4',
        ]);

        $admin      = Auth::guard('admin')->user();
        $masafaUrl  = config('services.masafa.base_url', 'http://127.0.0.1:8001');

        try {
            $response = Http::timeout(12)
                ->acceptJson()
                ->post("{$masafaUrl}/api/connect/token", [
                    'email'    => $request->email,
                    'password' => $request->password,
                    'app_name' => 'Order Manager — ' . ($admin->name ?? 'Admin'),
                ]);

            $data = $response->json();

            if (!$response->successful() || empty($data['success'])) {
                return response()->json([
                    'success' => false,
                    'message' => $data['message'] ?? 'Identifiants incorrects ou compte inactif.',
                ], $response->status() >= 400 ? $response->status() : 422);
            }

            // Store token + user info
            MasafaConfiguration::updateOrCreate(
                ['admin_id' => $admin->id],
                [
                    'api_token'         => $data['token'],
                    'masafa_user_name'  => $data['user']['name']  ?? null,
                    'masafa_user_email' => $data['user']['email'] ?? $request->email,
                    'masafa_user_id'    => $data['user']['id']    ?? null,
                    'is_active'         => true,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Compte Masafa Express lié avec succès !',
                'user'    => $data['user'] ?? [],
            ]);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de joindre le serveur Masafa Express. Vérifiez que le service est démarré.',
            ], 503);
        } catch (\Exception $e) {
            Log::error('Masafa connect error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur inattendue : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * AJAX — test API connection
     */
    public function testConnection()
    {
        $admin  = Auth::guard('admin')->user();
        $config = $admin->masafaConfiguration;

        if (!$config || !$config->api_token) {
            return response()->json(['success' => false, 'message' => 'Aucune configuration trouvée. Veuillez enregistrer votre Token API.']);
        }

        $service = new MasafaExpressService($config);
        $result  = $service->testConnection();

        return response()->json($result);
    }

    /**
     * AJAX — fetch client's pickup addresses from masafaexpress
     */
    public function getPickupAddresses()
    {
        $admin  = Auth::guard('admin')->user();
        $config = $admin->masafaConfiguration;

        if (!$config || !$config->api_token) {
            return response()->json(['success' => false, 'data' => [], 'message' => 'Non configuré.']);
        }

        $masafaUrl = config('services.masafa.base_url', 'http://127.0.0.1:8001');

        try {
            $response = Http::withHeaders(['Authorization' => 'Bearer ' . $config->api_token])
                ->timeout(8)
                ->acceptJson()
                ->get("{$masafaUrl}/api/v1/client/pickup-addresses");

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json(['success' => false, 'data' => [], 'message' => 'Impossible de charger les adresses.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'data' => [], 'message' => 'Serveur Masafa inaccessible.']);
        }
    }

    /**
     * AJAX — send a single confirmed order to Masafa Express
     */
    public function sendOrder(Order $order)
    {
        $admin = Auth::guard('admin')->user();

        if ($order->admin_id !== $admin->id) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé.'], 403);
        }

        if ($order->tracking_number) {
            return response()->json(['success' => false, 'message' => 'Cette commande a déjà un numéro de suivi.']);
        }

        $config = $admin->masafaConfiguration;
        if (!$config || !$config->is_active || !$config->api_token) {
            return response()->json(['success' => false, 'message' => 'Configuration Masafa Express inactive ou manquante.']);
        }

        $service = new MasafaExpressService($config);
        $result  = $service->createPackage($order);

        if ($result['success']) {
            $order->markAsShipped($result['tracking_number'] ?? null, 'Masafa Express');
            Log::info('[MasafaDelivery] Colis envoyé', ['order_id' => $order->id, 'tracking' => $result['tracking_number'] ?? null]);
        }

        return response()->json($result);
    }

    /**
     * AJAX — send multiple orders at once
     */
    public function sendBulk(Request $request)
    {
        $admin  = Auth::guard('admin')->user();
        $config = $admin->masafaConfiguration;

        if (!$config || !$config->is_active || !$config->api_token) {
            return response()->json(['success' => false, 'message' => 'Configuration Masafa Express inactive ou manquante.']);
        }

        $orderIds = $request->input('order_ids', []);
        if (empty($orderIds)) {
            return response()->json(['success' => false, 'message' => 'Aucune commande sélectionnée.']);
        }

        $service = new MasafaExpressService($config);
        $results = ['success' => 0, 'errors' => 0, 'details' => []];

        $orders = Order::where('admin_id', $admin->id)
            ->whereIn('id', $orderIds)
            ->whereNull('tracking_number')
            ->get();

        foreach ($orders as $order) {
            $result = $service->createPackage($order);
            if ($result['success']) {
                $order->markAsShipped($result['tracking_number'] ?? null, 'Masafa Express');
                $results['success']++;
                $results['details'][] = ['order_id' => $order->id, 'tracking' => $result['tracking_number'] ?? null, 'ok' => true];
            } else {
                $results['errors']++;
                $results['details'][] = ['order_id' => $order->id, 'error' => $result['message'] ?? 'Erreur inconnue', 'ok' => false];
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$results['success']} colis envoyé(s), {$results['errors']} erreur(s).",
            'results' => $results,
        ]);
    }

    /**
     * AJAX — sync tracking status for a given order
     */
    public function syncStatus(Order $order)
    {
        $admin = Auth::guard('admin')->user();

        if ($order->admin_id !== $admin->id) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé.'], 403);
        }

        if (!$order->tracking_number) {
            return response()->json(['success' => false, 'message' => 'Aucun numéro de suivi pour cette commande.']);
        }

        $config = $admin->masafaConfiguration;
        if (!$config || !$config->api_token) {
            return response()->json(['success' => false, 'message' => 'Configuration Masafa Express manquante.']);
        }

        $service = new MasafaExpressService($config);
        $result  = $service->getPackageStatus($order->tracking_number);

        if ($result['success'] && !empty($result['data']['status'])) {
            $masafaStatus = $result['data']['status'];
            $statusMap = [
                'delivered'     => 'livrée',
                'in_transit'    => 'en_transit',
                'returned'      => 'en_retour',
                'failed'        => 'échec_livraison',
                'pending'       => 'expédiée',
                'picked_up'     => 'expédiée',
            ];
            $newStatus = $statusMap[$masafaStatus] ?? null;
            if ($newStatus && $order->status !== $newStatus) {
                $order->updateDeliveryStatus($newStatus, $masafaStatus, $result['data']['status_label'] ?? null);
            }
        }

        return response()->json(array_merge($result, ['order_status' => $order->fresh()->status]));
    }

    /**
     * AJAX — disable / delete config
     */
    public function deleteConfig()
    {
        $admin = Auth::guard('admin')->user();
        MasafaConfiguration::where('admin_id', $admin->id)->delete();
        return response()->json(['success' => true, 'message' => 'Configuration supprimée.']);
    }
}
