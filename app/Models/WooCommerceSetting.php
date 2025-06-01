<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WooCommerceSetting extends Model
{
    use HasFactory;

    protected $table = 'woocommerce_settings';

    protected $fillable = [
        'admin_id',
        'store_url',
        'consumer_key',
        'consumer_secret',
        'is_active',
        'sync_status',
        'sync_error',
        'last_sync_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_sync_at' => 'datetime',
    ];

    // Relations
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    // Méthodes utilitaires
    public function getClient()
    {
        if (!$this->store_url || !$this->consumer_key || !$this->consumer_secret) {
            return null;
        }

        try {
            return new \Automattic\WooCommerce\Client(
                $this->store_url,
                $this->consumer_key,
                $this->consumer_secret,
                [
                    'wp_api' => true,
                    'version' => 'wc/v3',
                    'timeout' => 60,
                    'verify_ssl' => true,
                ]
            );
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la création du client WooCommerce: ' . $e->getMessage());
            return null;
        }
    }

    public function testConnection()
    {
        try {
            $client = $this->getClient();
            if (!$client) {
                return [
                    'success' => false,
                    'message' => 'Paramètres de connexion incomplets'
                ];
            }

            // Essayer de récupérer les infos de la boutique
            $response = $client->get('');
            
            return [
                'success' => true,
                'message' => 'Connexion établie avec succès',
                'store_info' => [
                    'name' => $response->name ?? 'Boutique WooCommerce',
                    'version' => $response->version ?? 'Non spécifiée'
                ]
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur de connexion: ' . $e->getMessage()
            ];
        }
    }
}