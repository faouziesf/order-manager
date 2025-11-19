<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PrestashopSetting extends Model
{
    use HasFactory;

    protected $table = 'prestashop_settings';

    protected $fillable = [
        'admin_id',
        'shop_url',
        'api_key',
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

    /**
     * Obtenir l'URL de base de la boutique PrestaShop
     */
    public function getBaseUrl()
    {
        if (!$this->shop_url) {
            return null;
        }

        // Nettoyer l'URL
        $url = trim($this->shop_url);

        // Ajouter https:// si pas de protocole
        if (!preg_match('#^https?://#', $url)) {
            $url = 'https://' . $url;
        }

        // Retirer le trailing slash
        $url = rtrim($url, '/');

        return $url;
    }

    /**
     * Tester la connexion à PrestaShop
     */
    public function testConnection()
    {
        try {
            $baseUrl = $this->getBaseUrl();

            if (!$baseUrl) {
                return [
                    'success' => false,
                    'message' => 'URL de boutique invalide'
                ];
            }

            if (!$this->api_key) {
                return [
                    'success' => false,
                    'message' => 'Clé API manquante'
                ];
            }

            // Tester l'API PrestaShop en récupérant les informations de la boutique
            $response = Http::withBasicAuth($this->api_key, '')
                ->withHeaders([
                    'Output-Format' => 'JSON',
                ])
                ->timeout(30)
                ->get($baseUrl . '/api/');

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Connexion établie avec succès',
                    'store_info' => [
                        'name' => 'Boutique PrestaShop',
                        'url' => $baseUrl,
                    ]
                ];
            }

            return [
                'success' => false,
                'message' => 'Erreur de connexion: Vérifiez votre clé API et l\'activation du Web Service'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur test connexion PrestaShop: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erreur de connexion: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Récupérer les commandes depuis PrestaShop
     */
    public function fetchOrders($limit = 50, $since_id = null)
    {
        try {
            $baseUrl = $this->getBaseUrl();

            if (!$baseUrl || !$this->api_key) {
                return [
                    'success' => false,
                    'message' => 'Configuration invalide'
                ];
            }

            $params = [
                'display' => 'full',
                'limit' => $limit,
                'sort' => '[id_DESC]',
            ];

            if ($since_id) {
                $params['filter[id]'] = '[' . $since_id . ',99999999]';
            }

            $response = Http::withBasicAuth($this->api_key, '')
                ->withHeaders([
                    'Output-Format' => 'JSON',
                ])
                ->timeout(30)
                ->get($baseUrl . '/api/orders', $params);

            if ($response->successful()) {
                $data = $response->json();
                $orders = $data['orders'] ?? [];

                return [
                    'success' => true,
                    'orders' => is_array($orders) ? $orders : [$orders]
                ];
            }

            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des commandes'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur fetch orders PrestaShop: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Récupérer les produits depuis PrestaShop
     */
    public function fetchProducts($limit = 50)
    {
        try {
            $baseUrl = $this->getBaseUrl();

            if (!$baseUrl || !$this->api_key) {
                return [
                    'success' => false,
                    'message' => 'Configuration invalide'
                ];
            }

            $response = Http::withBasicAuth($this->api_key, '')
                ->withHeaders([
                    'Output-Format' => 'JSON',
                ])
                ->timeout(30)
                ->get($baseUrl . '/api/products', [
                    'display' => 'full',
                    'limit' => $limit,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $products = $data['products'] ?? [];

                return [
                    'success' => true,
                    'products' => is_array($products) ? $products : [$products]
                ];
            }

            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des produits'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur fetch products PrestaShop: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
