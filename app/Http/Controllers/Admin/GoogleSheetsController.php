<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GoogleSheetsIntegration;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GoogleSheetsController extends Controller
{
    public function index()
    {
        $admin = Auth::guard('admin')->user();
        $effectiveAdminId = $admin->getEffectiveAdminId();

        $integration = GoogleSheetsIntegration::where('admin_id', $effectiveAdminId)->first();
        
        if (!$integration) {
            $integration = new GoogleSheetsIntegration(['admin_id' => $effectiveAdminId]);
        }

        $stats = $integration->getStats();

        return view('admin.google-sheets.index', compact('integration', 'stats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'import_type' => 'required|in:published_csv,oauth2',
            'spreadsheet_id' => 'nullable|string|max:100',
            'sheet_name' => 'nullable|string|max:100',
            'sheet_range' => 'nullable|string|max:50',
            'csv_url' => 'nullable|url',
            'first_sync_date' => 'nullable|date',
            'resync_day_of_week' => 'nullable|integer|between:0,6',
            'resync_time' => 'nullable|date_format:H:i',
            'auto_sync' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $admin = Auth::guard('admin')->user();
        $effectiveAdminId = $admin->getEffectiveAdminId();

        $integration = GoogleSheetsIntegration::where('admin_id', $effectiveAdminId)
            ->firstOrCreate(['admin_id' => $effectiveAdminId]);

        // Validation du type d'import
        if ($request->import_type === 'published_csv' && !$request->csv_url) {
            return back()->withInput()->with('error', 'URL CSV requise pour l\'import via CSV');
        }

        // Mettre à jour les paramètres
        $integration->update([
            'import_type' => $request->import_type,
            'spreadsheet_id' => $request->spreadsheet_id,
            'sheet_name' => $request->sheet_name ?? 'Orders',
            'sheet_range' => $request->sheet_range ?? 'A:Z',
            'csv_url' => $request->csv_url,
            'first_sync_date' => $request->first_sync_date,
            'resync_day_of_week' => $request->resync_day_of_week,
            'resync_time' => $request->resync_time ?? '03:00:00',
            'auto_sync' => $request->has('auto_sync') && $request->boolean('auto_sync'),
            'is_active' => $request->has('is_active') && $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.google-sheets.index')
            ->with('success', 'Configuration Google Sheets sauvegardée');
    }

    public function syncNow()
    {
        $admin = Auth::guard('admin')->user();
        $effectiveAdminId = $admin->getEffectiveAdminId();

        $integration = GoogleSheetsIntegration::where('admin_id', $effectiveAdminId)->first();

        if (!$integration || !$integration->is_active) {
            return back()->with('error', 'Google Sheets non configuré ou inactif');
        }

        try {
            $result = $this->performSync($integration);
            return back()->with('success', $result['message']);
        } catch (\Exception $e) {
            Log::error('Google Sheets sync error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Erreur sync: ' . $e->getMessage());
        }
    }

    public function stats()
    {
        $admin = Auth::guard('admin')->user();
        $effectiveAdminId = $admin->getEffectiveAdminId();

        $integration = GoogleSheetsIntegration::where('admin_id', $effectiveAdminId)->first();
        $stats = $integration?->getStats() ?? [];

        return response()->json(['success' => true, 'data' => $stats]);
    }

    private function performSync(GoogleSheetsIntegration $integration): array
    {
        $startTime = microtime(true);
        $imported = 0;
        $updated = 0;
        $errors = [];

        try {
            // Récupérer les données
            $result = $integration->fetchCsvData();

            if (!$result['success']) {
                throw new \Exception($result['message']);
            }

            $rows = $result['rows'] ?? [];

            foreach ($rows as $row) {
                try {
                    // Mapping des colonnes CSV aux champs Order
                    $externalId = $row['order_id'] ?? $row['id'] ?? null;
                    if (!$externalId) continue;

                    $existing = Order::where('admin_id', $integration->admin_id)
                        ->where('external_id', $externalId)
                        ->where('external_source', 'google_sheets')
                        ->first();

                    $data = [
                        'admin_id' => $integration->admin_id,
                        'external_id' => $externalId,
                        'external_source' => 'google_sheets',
                        'customer_name' => $row['customer_name'] ?? $row['name'] ?? null,
                        'customer_phone' => $row['customer_phone'] ?? $row['phone'] ?? null,
                        'customer_email' => $row['customer_email'] ?? $row['email'] ?? null,
                        'customer_address' => $row['customer_address'] ?? $row['address'] ?? null,
                        'customer_city' => $row['customer_city'] ?? $row['city'] ?? null,
                        'total_price' => (float) ($row['total_price'] ?? $row['amount'] ?? 0),
                        'status' => 'nouvelle',
                        'notes' => 'Importé de Google Sheets — ID: ' . $externalId,
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
                'message' => "Google Sheets: $imported importée(s), $updated mise(s) à jour en {$duration}s",
            ];
        } catch (\Exception $e) {
            Log::error('Google Sheets sync failed', ['error' => $e->getMessage()]);
            $integration->update([
                'last_sync_at' => now(),
                'last_sync_status' => 'error',
                'last_sync_error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
