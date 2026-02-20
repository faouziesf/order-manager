<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\MasafaConfiguration;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MasafaExpressService
{
    private ?MasafaConfiguration $config;
    private string $baseUrl;

    public function __construct(?MasafaConfiguration $config = null)
    {
        $this->config = $config;
        $this->baseUrl = config('services.masafa.base_url', 'http://127.0.0.1:8001');
    }

    /**
     * Créer une instance pour un admin donné
     */
    public static function forAdmin(Admin $admin): ?self
    {
        $config = $admin->masafaConfiguration;
        if (!$config || !$config->is_active || !$config->api_token) {
            return null;
        }
        return new self($config);
    }

    /**
     * Tester la connexion à l'API Masafa Express
     */
    public function testConnection(): array
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(10)
                ->get("{$this->baseUrl}/api/v1/client/stats");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Connexion réussie à Masafa Express',
                    'data' => $response->json('data'),
                ];
            }

            return [
                'success' => false,
                'message' => 'Erreur de connexion: ' . $response->status(),
                'error' => $response->json('message') ?? 'Erreur inconnue',
            ];
        } catch (\Exception $e) {
            Log::error('[MasafaExpress] Test connexion échoué', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Impossible de contacter Masafa Express',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Créer un colis sur Masafa Express à partir d'une commande Order Manager
     */
    public function createPackage(Order $order): array
    {
        try {
            $packageData = $this->mapOrderToPackage($order);

            $response = Http::withHeaders($this->getHeaders())
                ->timeout(30)
                ->post("{$this->baseUrl}/api/v1/client/packages", [
                    'packages' => [$packageData],
                ]);

            if ($response->successful()) {
                $data = $response->json('data');
                $created = $data['packages'][0] ?? null;

                if ($created) {
                    // Mettre à jour la commande avec le tracking number Masafa
                    $order->update([
                        'tracking_number' => $created['tracking_number'],
                        'carrier_name' => 'Masafa Express',
                    ]);

                    Log::info('[MasafaExpress] Colis créé', [
                        'order_id' => $order->id,
                        'tracking' => $created['tracking_number'],
                    ]);
                }

                return [
                    'success' => true,
                    'message' => 'Colis créé sur Masafa Express',
                    'tracking_number' => $created['tracking_number'] ?? null,
                    'data' => $created,
                ];
            }

            $errorData = $response->json();
            Log::warning('[MasafaExpress] Échec création colis', [
                'order_id' => $order->id,
                'status' => $response->status(),
                'response' => $errorData,
            ]);

            return [
                'success' => false,
                'message' => $errorData['message'] ?? 'Erreur lors de la création',
                'errors' => $errorData['errors'] ?? [],
            ];
        } catch (\Exception $e) {
            Log::error('[MasafaExpress] Exception création colis', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            return [
                'success' => false,
                'message' => 'Erreur de communication avec Masafa Express',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Récupérer le statut d'un colis
     */
    public function getPackageStatus(string $trackingNumber): array
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(10)
                ->get("{$this->baseUrl}/api/v1/client/packages/{$trackingNumber}");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json('data.package'),
                ];
            }

            return [
                'success' => false,
                'message' => 'Colis non trouvé',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Récupérer les statistiques
     */
    public function getStats(): array
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(10)
                ->get("{$this->baseUrl}/api/v1/client/stats");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json('data'),
                ];
            }

            return ['success' => false, 'message' => 'Erreur ' . $response->status()];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Lister les colis
     */
    public function listPackages(array $filters = []): array
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(15)
                ->get("{$this->baseUrl}/api/v1/client/packages", $filters);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json('data'),
                ];
            }

            return ['success' => false, 'message' => 'Erreur ' . $response->status()];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Mapper une commande Order Manager vers le format Masafa Express API
     */
    private function mapOrderToPackage(Order $order): array
    {
        $config = $this->config;

        return [
            'pickup_address_id' => $config->masafa_client_id ?? null,
            'recipient_name' => $order->customer_name,
            'recipient_phone' => $order->customer_phone,
            'recipient_phone_2' => $order->customer_phone_2,
            'recipient_gouvernorat' => $order->customer_governorate,
            'recipient_delegation' => $order->customer_city,
            'recipient_address' => $order->customer_address ?? 'Non spécifiée',
            'content_description' => $this->buildContentDescription($order),
            'cod_amount' => (float)$order->total_price,
            'payment_method' => 'COD',
            'is_fragile' => false,
            'is_exchange' => false,
            'allow_opening' => true,
            'notes' => $order->notes,
            'comment' => 'Commande #' . $order->id . ' - ' . ($order->admin->shop_name ?? 'Confirmi'),
        ];
    }

    /**
     * Construire la description du contenu depuis les items de la commande
     */
    private function buildContentDescription(Order $order): string
    {
        $items = $order->items;
        if ($items && $items->count() > 0) {
            $descriptions = $items->map(function ($item) {
                $name = $item->product->name ?? $item->product_name ?? 'Article';
                return "{$name} x{$item->quantity}";
            });
            return $descriptions->implode(', ');
        }
        return 'Commande #' . $order->id;
    }

    /**
     * Headers d'authentification pour l'API Masafa Express
     */
    private function getHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . ($this->config->api_token ?? ''),
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }
}
