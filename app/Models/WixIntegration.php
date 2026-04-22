<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WixIntegration extends Model
{
    protected $fillable = [
        'admin_id',
        'account_id',
        'api_key',
        'site_display_name',
        'first_sync_date',
        'resync_day_of_week',
        'resync_time',
        'is_active',
        'last_sync_at',
        'last_sync_status',
        'last_sync_error',
        'total_imported',
        'total_updated',
    ];

    protected $casts = [
        'first_sync_date' => 'datetime',
        'last_sync_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Test la connexion à l'API Wix
     */
    public function testConnection(): array
    {
        if (!$this->api_key || !$this->account_id) {
            return ['success' => false, 'message' => 'Clés API manquantes'];
        }

        try {
            $response = \Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->api_key,
            ])->get('https://www.wixapis.com/v1/contacts', [
                'limit' => 1,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Connexion Wix réussie',
                ];
            }

            return [
                'success' => false,
                'message' => 'Erreur API: ' . $response->status(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Récupère les statistiques Wix
     */
    public function getStats(): array
    {
        if (!$this->is_active) {
            return ['total' => 0, 'imported' => $this->total_imported, 'updated' => $this->total_updated];
        }

        $orders = Order::where('admin_id', $this->admin_id)
            ->where('external_source', 'wix')
            ->count();

        return [
            'total' => $orders,
            'imported' => $this->total_imported,
            'updated' => $this->total_updated,
            'last_sync' => $this->last_sync_at?->diffForHumans() ?? 'Jamais',
        ];
    }
}
