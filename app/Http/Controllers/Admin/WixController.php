<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Order;
use App\Models\WixIntegration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WixController extends Controller
{
    public function index()
    {
        $admin = Auth::guard('admin')->user();
        $effectiveAdminId = $admin->getEffectiveAdminId();

        $integrations = WixIntegration::where('admin_id', $effectiveAdminId)->get();
        $newIntegration = new WixIntegration(['admin_id' => $effectiveAdminId]);

        $syncStats = [
            'total_orders' => Order::where('admin_id', $effectiveAdminId)
                ->where('external_source', 'wix')
                ->count(),
            'active_integrations' => $integrations->where('is_active', true)->count(),
            'total_integrations' => $integrations->count(),
        ];

        return view('admin.wix.index', compact('newIntegration', 'integrations', 'syncStats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'account_id' => 'required|string|max:100',
            'api_key' => 'required|string|min:10',
            'site_display_name' => 'nullable|string|max:255',
            'first_sync_date' => 'nullable|date',
            'resync_day_of_week' => 'nullable|integer|between:0,6',
            'resync_time' => 'nullable|date_format:H:i',
            'is_active' => 'nullable|boolean',
        ]);

        $admin = Auth::guard('admin')->user();
        $effectiveAdminId = $admin->getEffectiveAdminId();

        $integration = WixIntegration::where('admin_id', $effectiveAdminId)
            ->where('account_id', $request->account_id)
            ->first();

        if (!$integration) {
            $integration = new WixIntegration(['admin_id' => $effectiveAdminId]);
        }

        $integration->account_id = $request->account_id;
        $integration->api_key = $request->api_key;
        $integration->site_display_name = $request->site_display_name;
        $integration->first_sync_date = $request->first_sync_date;
        $integration->resync_day_of_week = $request->resync_day_of_week;
        $integration->resync_time = $request->resync_time ?? '02:00:00';
        $integration->is_active = $request->has('is_active') && $request->boolean('is_active');

        // Test connection
        $testResult = $integration->testConnection();
        if (!$testResult['success']) {
            return back()->withInput()->with('error', 'Erreur connexion Wix: ' . $testResult['message']);
        }

        $integration->save();

        return redirect()->route('admin.wix.index')
            ->with('success', 'Intégration Wix sauvegardée avec succès');
    }

    public function sync($id)
    {
        $admin = Auth::guard('admin')->user();
        $effectiveAdminId = $admin->getEffectiveAdminId();

        $integration = WixIntegration::where('admin_id', $effectiveAdminId)->findOrFail($id);

        if (!$integration->is_active) {
            return back()->with('error', 'Intégration Wix non active');
        }

        try {
            $result = $this->performSync($integration);
            return back()->with('success', $result['message']);
        } catch (\Exception $e) {
            Log::error('Wix sync error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Erreur sync Wix: ' . $e->getMessage());
        }
    }

    public function testConnection(Request $request, $id)
    {
        $admin = Auth::guard('admin')->user();
        $effectiveAdminId = $admin->getEffectiveAdminId();

        $integration = WixIntegration::where('admin_id', $effectiveAdminId)->findOrFail($id);
        $result = $integration->testConnection();

        return response()->json($result);
    }

    public function toggleIntegration($id)
    {
        $admin = Auth::guard('admin')->user();
        $effectiveAdminId = $admin->getEffectiveAdminId();

        $integration = WixIntegration::where('admin_id', $effectiveAdminId)->findOrFail($id);
        $integration->is_active = !$integration->is_active;
        $integration->save();

        return response()->json([
            'success' => true,
            'is_active' => $integration->is_active,
            'message' => 'Statut mis à jour',
        ]);
    }

    public function deleteIntegration($id)
    {
        $admin = Auth::guard('admin')->user();
        $effectiveAdminId = $admin->getEffectiveAdminId();

        WixIntegration::where('admin_id', $effectiveAdminId)->findOrFail($id)->delete();

        return response()->json(['success' => true, 'message' => 'Intégration supprimée']);
    }

    public function syncStats($id)
    {
        $admin = Auth::guard('admin')->user();
        $effectiveAdminId = $admin->getEffectiveAdminId();

        $integration = WixIntegration::where('admin_id', $effectiveAdminId)->findOrFail($id);
        $stats = $integration->getStats();

        return response()->json(['success' => true, 'data' => $stats]);
    }

    private function performSync(WixIntegration $integration): array
    {
        $startTime = microtime(true);
        $imported = 0;
        $updated = 0;
        $errors = [];

        try {
            // Récupérer les commandes depuis Wix API
            $response = \Http::withHeaders([
                'Authorization' => 'Bearer ' . $integration->api_key,
            ])->get('https://www.wixapis.com/v1/orders', [
                'limit' => 100,
            ]);

            if (!$response->successful()) {
                throw new \Exception('Erreur API Wix: ' . $response->status());
            }

            $orders = $response->json('orders', []);

            foreach ($orders as $order) {
                try {
                    $externalId = $order['id'] ?? null;
                    if (!$externalId) continue;

                    $existing = Order::where('admin_id', $integration->admin_id)
                        ->where('external_id', $externalId)
                        ->where('external_source', 'wix')
                        ->first();

                    $data = [
                        'admin_id' => $integration->admin_id,
                        'external_id' => $externalId,
                        'external_source' => 'wix',
                        'customer_name' => $order['buyerInfo']['firstName'] . ' ' . $order['buyerInfo']['lastName'] ?? null,
                        'customer_phone' => $order['buyerInfo']['phone'] ?? null,
                        'customer_email' => $order['buyerInfo']['email'] ?? null,
                        'customer_address' => $order['shippingAddress']['address1'] ?? null,
                        'customer_city' => $order['shippingAddress']['city'] ?? null,
                        'customer_governorate' => $order['shippingAddress']['region'] ?? null,
                        'total_price' => $order['totals']['total'] ?? 0,
                        'status' => 'nouvelle',
                        'notes' => 'Importé de Wix — Commande: ' . $externalId,
                    ];

                    if ($existing) {
                        $existing->update($data);
                        $updated++;
                    } else {
                        Order::create($data);
                        $imported++;
                    }
                } catch (\Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }

            $duration = round(microtime(true) - $startTime, 2);
            $integration->update([
                'last_sync_at' => now(),
                'last_sync_status' => 'success',
                'last_sync_error' => null,
                'total_imported' => $integration->total_imported + $imported,
                'total_updated' => $integration->total_updated + $updated,
            ]);

            return [
                'success' => true,
                'imported' => $imported,
                'updated' => $updated,
                'duration' => $duration,
                'message' => "Sync Wix: $imported importée(s), $updated mise(s) à jour en {$duration}s",
            ];
        } catch (\Exception $e) {
            Log::error('Wix sync failed', ['error' => $e->getMessage()]);
            $integration->update([
                'last_sync_at' => now(),
                'last_sync_status' => 'error',
                'last_sync_error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
