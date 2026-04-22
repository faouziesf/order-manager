<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasafaConfiguration;
use App\Models\Order;
use App\Services\KolixyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class KolixyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    // ========================================
    // DASHBOARD
    // ========================================
    public function dashboard()
    {
        $admin           = Auth::guard('admin')->user();
        $effectiveAdminId = $admin->getEffectiveAdminId();
        $config          = MasafaConfiguration::where('admin_id', $effectiveAdminId)->first();

        $stats      = null;
        $connected  = false;

        if ($config && $config->is_active && $config->api_token) {
            $service = new KolixyService($config);
            $result  = $service->getStats();
            if ($result['success']) {
                $stats     = $result['data'];
                $connected = true;
            }
        }

        $localStats = [
            'total_sent'   => Order::where('admin_id', $effectiveAdminId)->where('carrier_name', 'Kolixy')->whereNotNull('tracking_number')->count(),
            'en_cours'     => Order::where('admin_id', $effectiveAdminId)->where('carrier_name', 'Kolixy')->whereIn('status', ['expédiée', 'en_transit', 'tentative_livraison'])->count(),
            'livrees'      => Order::where('admin_id', $effectiveAdminId)->where('carrier_name', 'Kolixy')->where('status', 'livrée')->count(),
            'en_retour'    => Order::where('admin_id', $effectiveAdminId)->where('carrier_name', 'Kolixy')->whereIn('status', ['en_retour', 'échec_livraison'])->count(),
            'pret_envoyer' => Order::where('admin_id', $effectiveAdminId)->where('status', 'confirmée')->whereNull('tracking_number')->count(),
        ];

        return view('admin.kolixy.dashboard', compact('config', 'stats', 'connected', 'localStats'));
    }

    // ========================================
    // CONFIGURATION
    // ========================================
    public function configuration()
    {
        $admin           = Auth::guard('admin')->user();
        $effectiveAdminId = $admin->getEffectiveAdminId();
        $config          = MasafaConfiguration::where('admin_id', $effectiveAdminId)->first();

        return view('admin.kolixy.configuration', compact('config'));
    }

    public function connect(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string|min:4',
        ]);

        $admin            = Auth::guard('admin')->user();
        $effectiveAdminId = $admin->getEffectiveAdminId();
        $service          = new KolixyService();
        $result           = $service->connect(
            $request->email,
            $request->password,
            'Order Manager — ' . ($admin->name ?? 'Admin')
        );

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 422);
        }

        MasafaConfiguration::updateOrCreate(
            ['admin_id' => $effectiveAdminId],
            [
                'api_token'         => $result['token'],
                'masafa_user_name'  => $result['user']['name']  ?? null,
                'masafa_user_email' => $result['user']['email'] ?? $request->email,
                'masafa_user_id'    => $result['user']['id']    ?? null,
                'is_active'         => true,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Compte Kolixy lié avec succès !',
            'user'    => $result['user'] ?? [],
        ]);
    }

    public function saveConfig(Request $request)
    {
        $admin            = Auth::guard('admin')->user();
        $effectiveAdminId = $admin->getEffectiveAdminId();

        $request->validate([
            'kolixy_pickup_address_id' => 'nullable|string|max:100',
            'pickup_name'              => 'nullable|string|max:100',
            'auto_send'                => 'nullable|boolean',
        ]);

        $config = MasafaConfiguration::where('admin_id', $effectiveAdminId)->first();
        if (!$config) {
            return response()->json(['success' => false, 'message' => 'Veuillez d\'abord connecter votre compte Kolixy.'], 422);
        }

        $config->update([
            'masafa_client_id' => $request->kolixy_pickup_address_id,
            'pickup_name'      => $request->pickup_name,
            'auto_send'        => $request->boolean('auto_send'),
        ]);

        return response()->json(['success' => true, 'message' => 'Configuration sauvegardée.']);
    }

    public function testConnection()
    {
        $admin            = Auth::guard('admin')->user();
        $effectiveAdminId = $admin->getEffectiveAdminId();
        $config           = MasafaConfiguration::where('admin_id', $effectiveAdminId)->first();

        if (!$config || !$config->api_token) {
            return response()->json(['success' => false, 'message' => 'Aucune configuration trouvée.']);
        }

        $service = new KolixyService($config);
        return response()->json($service->testConnection());
    }

    public function getPickupAddresses()
    {
        $admin            = Auth::guard('admin')->user();
        $effectiveAdminId = $admin->getEffectiveAdminId();
        $config           = MasafaConfiguration::where('admin_id', $effectiveAdminId)->first();

        if (!$config || !$config->api_token) {
            return response()->json(['success' => false, 'data' => [], 'message' => 'Non configuré.']);
        }

        $service = new KolixyService($config);
        return response()->json($service->getPickupAddresses());
    }

    public function deleteConfig()
    {
        $admin            = Auth::guard('admin')->user();
        $effectiveAdminId = $admin->getEffectiveAdminId();
        MasafaConfiguration::where('admin_id', $effectiveAdminId)->delete();
        return response()->json(['success' => true, 'message' => 'Configuration supprimée.']);
    }

    // ========================================
    // VERIFICATION
    // ========================================
    public function verification(Request $request)
    {
        $admin            = Auth::guard('admin')->user();
        $effectiveAdminId = $admin->getEffectiveAdminId();
        $config           = MasafaConfiguration::where('admin_id', $effectiveAdminId)->first();

        $packages  = [];
        $connected = false;

        // Statuts disponibles pour filtrage
        $availableStatuses = [
            'AWAITING_RETURN' => 'En attente de retour',
            'UNAVAILABLE'     => 'Indisponible',
            'REFUSED'         => 'Refusé',
            'DELIVERED'       => 'Livré',
            'IN_TRANSIT'      => 'En transit',
        ];

        $selectedStatuses = $request->get('statuses', ['AWAITING_RETURN', 'UNAVAILABLE', 'REFUSED']);
        if (is_string($selectedStatuses)) {
            $selectedStatuses = explode(',', $selectedStatuses);
        }

        if ($config && $config->is_active && $config->api_token) {
            $connected = true;
            $service   = new KolixyService($config);
            $result    = $service->listPackages([
                'status' => implode(',', $selectedStatuses),
            ]);
            if ($result['success']) {
                $packages = $result['data']['packages'] ?? $result['data'] ?? [];
            }
        }

        return view('admin.kolixy.verification', compact('packages', 'connected', 'config', 'availableStatuses', 'selectedStatuses'));
    }

    public function importFromVerification(Request $request)
    {
        $request->validate([
            'packages'   => 'required|array|min:1',
            'packages.*' => 'required|array',
        ]);

        $admin            = Auth::guard('admin')->user();
        $effectiveAdminId = $admin->getEffectiveAdminId();
        $config           = MasafaConfiguration::where('admin_id', $effectiveAdminId)->first();

        if (!$config || !$config->api_token) {
            return response()->json(['success' => false, 'message' => 'Kolixy non configuré.'], 422);
        }

        $imported = 0;
        $skipped  = 0;
        $errors   = [];

        foreach ($request->packages as $pkg) {
            try {
                $tracking = $pkg['tracking_number'] ?? $pkg['barcode'] ?? null;
                if (!$tracking) {
                    $skipped++;
                    continue;
                }

                // Éviter les doublons
                $existing = Order::where('admin_id', $effectiveAdminId)
                    ->where('tracking_number', $tracking)
                    ->first();

                if ($existing) {
                    $skipped++;
                    continue;
                }

                Order::create([
                    'admin_id'           => $effectiveAdminId,
                    'external_id'        => $tracking,
                    'external_source'    => 'kolixy_verification',
                    'tracking_number'    => $tracking,
                    'carrier_name'       => 'Kolixy',
                    'customer_name'      => $pkg['recipient_name']    ?? $pkg['customer_name']  ?? null,
                    'customer_phone'     => $pkg['recipient_phone']   ?? $pkg['customer_phone'] ?? null,
                    'customer_address'   => $pkg['recipient_address'] ?? $pkg['address']        ?? null,
                    'customer_city'      => $pkg['recipient_city']    ?? $pkg['city']           ?? null,
                    'total_price'        => (float) ($pkg['cod_amount'] ?? $pkg['total'] ?? 0),
                    'status'             => 'nouvelle',
                    'carrier_status_code'  => $pkg['status'] ?? null,
                    'carrier_status_label' => $pkg['status_label'] ?? $pkg['status'] ?? null,
                    'notes'              => 'Importé depuis Kolixy Vérification — statut: ' . ($pkg['status'] ?? '?'),
                ]);

                $imported++;
            } catch (\Exception $e) {
                Log::error('Kolixy verification import error', ['error' => $e->getMessage(), 'pkg' => $pkg]);
                $errors[] = $e->getMessage();
            }
        }

        return response()->json([
            'success'  => true,
            'imported' => $imported,
            'skipped'  => $skipped,
            'errors'   => $errors,
            'message'  => "{$imported} commande(s) importée(s), {$skipped} ignorée(s).",
        ]);
    }

    public function getPackageDetails(string $trackingNumber)
    {
        $admin            = Auth::guard('admin')->user();
        $effectiveAdminId = $admin->getEffectiveAdminId();
        $config           = MasafaConfiguration::where('admin_id', $effectiveAdminId)->first();

        if (!$config || !$config->api_token) {
            return response()->json(['success' => false, 'message' => 'Non configuré.']);
        }

        $service = new KolixyService($config);
        return response()->json($service->getPackageStatus($trackingNumber));
    }

    // ========================================
    // IMPRIMER BL
    // ========================================
    public function imprimerBL()
    {
        $admin            = Auth::guard('admin')->user();
        $effectiveAdminId = $admin->getEffectiveAdminId();
        $config           = MasafaConfiguration::where('admin_id', $effectiveAdminId)->first();

        $sentOrders = Order::where('admin_id', $effectiveAdminId)
            ->where('carrier_name', 'Kolixy')
            ->whereNotNull('tracking_number')
            ->latest('shipped_at')
            ->paginate(30);

        $connected = $config && $config->is_active && $config->api_token;

        return view('admin.kolixy.imprimer-bl', compact('sentOrders', 'connected', 'config'));
    }

    public function downloadLabels(Request $request)
    {
        $request->validate([
            'tracking_numbers'   => 'required|array|min:1|max:100',
            'tracking_numbers.*' => 'required|string',
        ]);

        $admin  = Auth::guard('admin')->user();
        $config = $admin->kolixyConfiguration;

        if (!$config || !$config->api_token) {
            return response()->json(['success' => false, 'message' => 'Non configuré.']);
        }

        $service = new KolixyService($config);
        $result  = $service->generateLabels($request->tracking_numbers);

        if ($result['success']) {
            return response($result['content'])
                ->header('Content-Type', $result['content_type'] ?? 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $result['filename'] . '"');
        }

        return response()->json(['success' => false, 'message' => $result['message'] ?? 'Erreur génération PDF.']);
    }

    // ========================================
    // PRINT BL (HTML)
    // ========================================
    public function printBL(Order $order)
    {
        $admin            = Auth::guard('admin')->user();
        $effectiveAdminId = $admin->getEffectiveAdminId();

        if ($order->admin_id !== $effectiveAdminId) {
            abort(403, 'Non autorisé');
        }

        if (!$order->tracking_number) {
            abort(422, 'Pas de numéro de suivi');
        }

        $order->load(['items.product', 'region', 'city']);

        return view('admin.kolixy.print-bl', [
            'order' => $order,
            'admin' => $admin,
        ]);
    }

    public function printBLBulk(Request $request)
    {
        $admin            = Auth::guard('admin')->user();
        $effectiveAdminId = $admin->getEffectiveAdminId();
        $ids = explode(',', $request->query('ids', ''));
        $ids = array_filter(array_map('intval', $ids));

        if (empty($ids)) {
            abort(422, 'Aucune commande sélectionnée');
        }

        $orders = Order::where('admin_id', $effectiveAdminId)
            ->whereIn('id', $ids)
            ->whereNotNull('tracking_number')
            ->with(['items.product', 'region', 'city'])
            ->get();

        if ($orders->isEmpty()) {
            abort(404, 'Aucune commande trouvée');
        }

        return view('admin.kolixy.print-bl-bulk', [
            'orders' => $orders,
            'admin' => $admin,
        ]);
    }

    // ========================================
    // ENVOI COMMANDE
    // ========================================
    public function envoyerCommande()
    {
        $admin            = Auth::guard('admin')->user();
        $effectiveAdminId = $admin->getEffectiveAdminId();
        $config           = MasafaConfiguration::where('admin_id', $effectiveAdminId)->first();

        $readyOrders = Order::where('admin_id', $effectiveAdminId)
            ->where('status', 'confirmée')
            ->whereNull('tracking_number')
            ->latest()
            ->get();

        $sentOrders = Order::where('admin_id', $effectiveAdminId)
            ->where('carrier_name', 'Kolixy')
            ->whereNotNull('tracking_number')
            ->latest('shipped_at')
            ->paginate(20);

        $connected = $config && $config->is_active && $config->api_token;

        return view('admin.kolixy.envoyer-commande', compact('readyOrders', 'sentOrders', 'connected', 'config'));
    }

    public function sendOrder(Order $order)
    {
        $admin            = Auth::guard('admin')->user();
        $effectiveAdminId = $admin->getEffectiveAdminId();

        // Eager load region/city for Kolixy zone name resolution
        $order->loadMissing(['region', 'city']);

        if ($order->admin_id !== $effectiveAdminId) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé.'], 403);
        }

        if ($order->tracking_number) {
            return response()->json(['success' => false, 'message' => 'Cette commande a déjà un numéro de suivi.']);
        }

        $config = MasafaConfiguration::where('admin_id', $effectiveAdminId)->first();
        if (!$config || !$config->is_active || !$config->api_token) {
            return response()->json(['success' => false, 'message' => 'Configuration Kolixy inactive ou manquante.']);
        }

        $service = new KolixyService($config);
        $result  = $service->createPackage($order);

        if ($result['success']) {
            $order->markAsShipped($result['tracking_number'] ?? null, 'Kolixy');
            Log::info('[Kolixy] Colis envoyé', ['order_id' => $order->id, 'tracking' => $result['tracking_number'] ?? null]);
        }

        return response()->json($result);
    }

    public function sendBulk(Request $request)
    {
        $admin            = Auth::guard('admin')->user();
        $effectiveAdminId = $admin->getEffectiveAdminId();
        $config           = MasafaConfiguration::where('admin_id', $effectiveAdminId)->first();

        if (!$config || !$config->is_active || !$config->api_token) {
            return response()->json(['success' => false, 'message' => 'Configuration Kolixy inactive ou manquante.']);
        }

        $orderIds = $request->input('order_ids', []);
        if (empty($orderIds)) {
            return response()->json(['success' => false, 'message' => 'Aucune commande sélectionnée.']);
        }

        $service = new KolixyService($config);
        $results = ['success' => 0, 'errors' => 0, 'details' => []];

        $orders = Order::where('admin_id', $effectiveAdminId)
            ->whereIn('id', $orderIds)
            ->whereNull('tracking_number')
            ->with(['region', 'city'])
            ->get();

        foreach ($orders as $order) {
            $result = $service->createPackage($order);
            if ($result['success']) {
                $order->markAsShipped($result['tracking_number'] ?? null, 'Kolixy');
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

    public function syncStatus(Order $order)
    {
        $admin            = Auth::guard('admin')->user();
        $effectiveAdminId = $admin->getEffectiveAdminId();

        if ($order->admin_id !== $effectiveAdminId) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé.'], 403);
        }

        if (!$order->tracking_number) {
            return response()->json(['success' => false, 'message' => 'Aucun numéro de suivi.']);
        }

        $config = MasafaConfiguration::where('admin_id', $effectiveAdminId)->first();
        if (!$config || !$config->api_token) {
            return response()->json(['success' => false, 'message' => 'Configuration Kolixy manquante.']);
        }

        $service = new KolixyService($config);
        $result  = $service->getPackageStatus($order->tracking_number);

        if ($result['success'] && !empty($result['data']['status'])) {
            $kolixyStatus = strtolower($result['data']['status']);
            $statusMap = [
                'delivered'        => 'livrée',
                'paid'             => 'livrée',
                'in_transit'       => 'en_transit',
                'out_for_delivery' => 'en_transit',
                'at_depot'         => 'expédiée',
                'returned'         => 'en_retour',
                'return_confirmed' => 'en_retour',
                'return_in_progress' => 'en_retour',
                'awaiting_return'  => 'en_retour',
                'refused'          => 'échec_livraison',
                'unavailable'      => 'tentative_livraison',
                'created'          => 'expédiée',
                'available'        => 'expédiée',
                'picked_up'        => 'expédiée',
            ];
            $newStatus = $statusMap[$kolixyStatus] ?? null;
            if ($newStatus && $order->status !== $newStatus) {
                $order->updateDeliveryStatus($newStatus, $result['data']['status'], $result['data']['status_label'] ?? null);
            }
        }

        return response()->json(array_merge($result, ['order_status' => $order->fresh()->status]));
    }
}
