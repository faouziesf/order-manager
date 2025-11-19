<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShopifySetting extends Model
{
    use HasFactory;

    protected $table = 'shopify_settings';

    protected $fillable = [
        'admin_id',
        'shop_url',
        'api_key',
        'api_secret',
        'access_token',
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
     * Obtenir l'URL de base de la boutique Shopify
     */
    public function getBaseUrl()
    {
        if (!$this->shop_url) {
            return null;
        }

        // Nettoyer l'URL et s'assurer qu'elle se termine par .myshopify.com
        $url = strtolower(trim($this->shop_url));

        // Retirer le protocole s'il existe
        $url = preg_replace('#^https?://#', '', $url);

        // Retirer le trailing slash
        $url = rtrim($url, '/');

        // Ajouter .myshopify.com si ce n'est pas déjà présent
        if (!str_ends_with($url, '.myshopify.com')) {
            // Si c'est juste le nom de la boutique
            if (!str_contains($url, '.')) {
                $url .= '.myshopify.com';
            }
        }

        return 'https://' . $url;
    }

    /**
     * Tester la connexion à Shopify
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

            if (!$this->access_token && (!$this->api_key || !$this->api_secret)) {
                return [
                    'success' => false,
                    'message' => 'Paramètres de connexion incomplets'
                ];
            }

            // Tester avec l'access token si disponible
            if ($this->access_token) {
                $response = Http::withHeaders([
                    'X-Shopify-Access-Token' => $this->access_token,
                    'Content-Type' => 'application/json',
                ])->timeout(30)->get($baseUrl . '/admin/api/2024-01/shop.json');

                if ($response->successful()) {
                    $shop = $response->json('shop');
                    return [
                        'success' => true,
                        'message' => 'Connexion établie avec succès',
                        'store_info' => [
                            'name' => $shop['name'] ?? 'Boutique Shopify',
                            'email' => $shop['email'] ?? '',
                            'domain' => $shop['domain'] ?? '',
                            'currency' => $shop['currency'] ?? '',
                        ]
                    ];
                }

                return [
                    'success' => false,
                    'message' => 'Erreur de connexion: ' . ($response->json('errors') ?? 'Token invalide')
                ];
            }

            // Sinon, tester avec API Key/Secret (méthode basique)
            $response = Http::withBasicAuth($this->api_key, $this->api_secret)
                ->timeout(30)
                ->get($baseUrl . '/admin/api/2024-01/shop.json');

            if ($response->successful()) {
                $shop = $response->json('shop');
                return [
                    'success' => true,
                    'message' => 'Connexion établie avec succès',
                    'store_info' => [
                        'name' => $shop['name'] ?? 'Boutique Shopify',
                        'email' => $shop['email'] ?? '',
                        'domain' => $shop['domain'] ?? '',
                    ]
                ];
            }

            return [
                'success' => false,
                'message' => 'Erreur de connexion: Vérifiez vos identifiants API'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur test connexion Shopify: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erreur de connexion: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Récupérer les commandes depuis Shopify
     */
    public function fetchOrders($limit = 50, $since_id = null)
    {
        try {
            $baseUrl = $this->getBaseUrl();

            if (!$baseUrl || !$this->access_token) {
                return [
                    'success' => false,
                    'message' => 'Configuration invalide'
                ];
            }

            $params = [
                'limit' => $limit,
                'status' => 'any',
            ];

            if ($since_id) {
                $params['since_id'] = $since_id;
            }

            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $this->access_token,
                'Content-Type' => 'application/json',
            ])->timeout(30)->get($baseUrl . '/admin/api/2024-01/orders.json', $params);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'orders' => $response->json('orders', [])
                ];
            }

            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des commandes'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur fetch orders Shopify: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Récupérer les produits depuis Shopify
     */
    public function fetchProducts($limit = 50)
    {
        try {
            $baseUrl = $this->getBaseUrl();

            if (!$baseUrl || !$this->access_token) {
                return [
                    'success' => false,
                    'message' => 'Configuration invalide'
                ];
            }

            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $this->access_token,
                'Content-Type' => 'application/json',
            ])->timeout(30)->get($baseUrl . '/admin/api/2024-01/products.json', [
                'limit' => $limit,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'products' => $response->json('products', [])
                ];
            }

            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des produits'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur fetch products Shopify: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
