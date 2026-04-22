<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Http;

class GoogleSheetsIntegration extends Model
{
    protected $fillable = [
        'admin_id',
        'spreadsheet_id',
        'sheet_name',
        'sheet_range',
        'import_type',
        'csv_url',
        'oauth_token',
        'first_sync_date',
        'resync_day_of_week',
        'resync_time',
        'auto_sync',
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
        'auto_sync' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Récupère le CSV depuis l'URL publique Google Sheets
     */
    public function fetchCsvData(): array
    {
        if (!$this->csv_url || $this->import_type !== 'published_csv') {
            return ['success' => false, 'message' => 'Pas d\'URL CSV configurée'];
        }

        try {
            $response = Http::timeout(10)->get($this->csv_url);
            
            if (!$response->successful()) {
                return ['success' => false, 'message' => 'Erreur HTTP: ' . $response->status()];
            }

            $csv = $response->body();
            $lines = array_filter(explode("\n", $csv));
            
            if (empty($lines)) {
                return ['success' => false, 'message' => 'Feuille vide'];
            }

            // Parser le CSV
            $rows = [];
            $headers = null;
            
            foreach ($lines as $line) {
                $values = str_getcsv($line);
                if (!$headers) {
                    $headers = $values;
                } else {
                    $rows[] = array_combine($headers, $values);
                }
            }

            return ['success' => true, 'rows' => $rows];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Erreur: ' . $e->getMessage()];
        }
    }

    /**
     * Récupère les statistiques
     */
    public function getStats(): array
    {
        return [
            'total_imported' => $this->total_imported,
            'total_updated' => $this->total_updated,
            'last_sync' => $this->last_sync_at?->diffForHumans() ?? 'Jamais',
            'is_active' => $this->is_active,
            'auto_sync' => $this->auto_sync,
        ];
    }
}
