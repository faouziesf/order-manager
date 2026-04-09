<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\MasafaConfiguration;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KolixyService
{
    private ?MasafaConfiguration $config;
    private string $baseUrl;

    public function __construct(?MasafaConfiguration $config = null)
    {
        $this->config = $config;
        $this->baseUrl = rtrim(config('services.kolixy.base_url', 'https://kolixy.com'), '/');
    }

    public static function forAdmin(Admin $admin): ?self
    {
        $config = $admin->kolixyConfiguration;
        if (!$config || !$config->is_active || !$config->api_token) {
            return null;
        }
        return new self($config);
    }

    /**
     * Connect with email/password to get API token
     */
    public function connect(string $email, string $password, string $appName = 'Order Manager'): array
    {
        try {
            $response = Http::timeout(12)
                ->acceptJson()
                ->post("{$this->baseUrl}/api/connect/token", [
                    'email'    => $email,
                    'password' => $password,
                    'app_name' => $appName,
                ]);

            $data = $response->json();

            if ($response->successful() && !empty($data['success'])) {
                return [
                    'success' => true,
                    'token'   => $data['token'],
                    'user'    => $data['user'] ?? [],
                    'message' => $data['message'] ?? 'Connexion réussie.',
                ];
            }

            return [
                'success' => false,
                'message' => $data['message'] ?? 'Identifiants incorrects ou compte inactif.',
            ];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return [
                'success' => false,
                'message' => 'Impossible de joindre le serveur Kolixy. Vérifiez la connexion.',
            ];
        } catch (\Exception $e) {
            Log::error('[Kolixy] Connect error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erreur inattendue : ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Test API connection
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
                    'message' => 'Connexion réussie à Kolixy',
                    'data'    => $response->json('data'),
                ];
            }

            return [
                'success' => false,
                'message' => 'Erreur de connexion: ' . $response->status(),
            ];
        } catch (\Exception $e) {
            Log::error('[Kolixy] Test connexion échoué', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Impossible de contacter Kolixy',
            ];
        }
    }

    /**
     * Get dashboard stats
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
                    'data'    => $response->json('data'),
                ];
            }

            return ['success' => false, 'message' => 'Erreur ' . $response->status()];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * List packages with optional filters
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
                    'data'    => $response->json('data'),
                ];
            }

            return ['success' => false, 'message' => 'Erreur ' . $response->status()];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get single package details by tracking number
     */
    public function getPackageStatus(string $trackingNumber): array
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(10)
                ->get("{$this->baseUrl}/api/v1/client/packages/" . urlencode($trackingNumber));

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data'    => $response->json('data.package') ?? $response->json('data'),
                ];
            }

            return ['success' => false, 'message' => 'Colis non trouvé'];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Create package(s) on Kolixy from order(s)
     */
    public function createPackage(Order $order): array
    {
        try {
            $packageData = $this->mapOrderToPackage($order);

            Log::info('[Kolixy] Envoi colis', [
                'order_id' => $order->id,
                'url'      => "{$this->baseUrl}/api/v1/client/packages",
                'data'     => $packageData,
            ]);

            $response = Http::withHeaders($this->getHeaders())
                ->timeout(30)
                ->post("{$this->baseUrl}/api/v1/client/packages", [
                    'packages' => [$packageData],
                ]);

            if ($response->successful()) {
                $data    = $response->json('data');
                $created = $data['packages'][0] ?? null;

                if ($created) {
                    $order->update([
                        'tracking_number' => $created['tracking_number'],
                        'carrier_name'    => 'Kolixy',
                    ]);

                    Log::info('[Kolixy] Colis créé', [
                        'order_id' => $order->id,
                        'tracking' => $created['tracking_number'],
                    ]);
                }

                return [
                    'success'         => true,
                    'message'         => 'Colis créé sur Kolixy',
                    'tracking_number' => $created['tracking_number'] ?? null,
                    'data'            => $created,
                ];
            }

            $errorData = $response->json();
            Log::warning('[Kolixy] Échec création colis', [
                'order_id' => $order->id,
                'status'   => $response->status(),
                'response' => $errorData,
            ]);

            // Build a detailed error message
            $errorMsg = $errorData['message'] ?? 'Erreur lors de la création';
            if (!empty($errorData['errors'])) {
                $errorDetails = collect($errorData['errors'])->flatten()->implode(', ');
                $errorMsg .= ' : ' . $errorDetails;
            }
            if (!empty($errorData['error'])) {
                $errorMsg .= ' (' . $errorData['error'] . ')';
            }

            return [
                'success' => false,
                'message' => $errorMsg,
                'errors'  => $errorData['errors'] ?? [],
            ];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('[Kolixy] Erreur connexion', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
                'url'      => "{$this->baseUrl}/api/v1/client/packages",
            ]);
            return [
                'success' => false,
                'message' => 'Impossible de joindre le serveur Kolixy. Vérifiez votre connexion internet et l\'URL de configuration.',
            ];
        } catch (\Exception $e) {
            Log::error('[Kolixy] Exception création colis', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);
            return [
                'success' => false,
                'message' => 'Erreur interne : ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Generate PDF labels for given tracking numbers
     */
    public function generateLabels(array $trackingNumbers): array
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(30)
                ->post("{$this->baseUrl}/api/v1/client/packages/labels", [
                    'tracking_numbers' => $trackingNumbers,
                ]);

            if ($response->successful()) {
                return [
                    'success'      => true,
                    'content'      => $response->body(),
                    'content_type' => $response->header('Content-Type'),
                    'filename'     => 'etiquettes-' . now()->format('Y-m-d-His') . '.pdf',
                ];
            }

            return [
                'success' => false,
                'message' => $response->json('message') ?? 'Erreur génération étiquettes',
            ];
        } catch (\Exception $e) {
            Log::error('[Kolixy] Labels error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Erreur de communication avec Kolixy',
            ];
        }
    }

    /**
     * Get pickup addresses for the authenticated client
     */
    public function getPickupAddresses(): array
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(8)
                ->acceptJson()
                ->get("{$this->baseUrl}/api/v1/client/pickup-addresses");

            if ($response->successful()) {
                return $response->json();
            }

            return ['success' => false, 'data' => [], 'message' => 'Impossible de charger les adresses.'];
        } catch (\Exception $e) {
            return ['success' => false, 'data' => [], 'message' => 'Serveur Kolixy inaccessible.'];
        }
    }

    /**
     * Map an Order Manager order to Kolixy package format
     */
    private function mapOrderToPackage(Order $order): array
    {
        // Resolve region/city names from IDs (order stores IDs, Kolixy expects names)
        $regionName = $order->region->name ?? null;
        $cityName   = $order->city->name ?? null;

        // Convert region name to Kolixy zone format (UPPERCASE with underscores)
        $gouvernorat = $regionName ? $this->toKolixyZone($regionName) : $order->customer_governorate;
        $delegation  = $cityName ?? $order->customer_city;

        return [
            'pickup_address_id'     => $this->config->kolixy_pickup_address_id ?? $this->config->masafa_client_id ?? null,
            'recipient_name'        => $order->customer_name,
            'recipient_phone'       => $order->customer_phone,
            'recipient_phone_2'     => $order->customer_phone_2,
            'recipient_gouvernorat' => $gouvernorat,
            'recipient_delegation'  => $delegation,
            'recipient_address'     => $order->customer_address ?? 'Non spécifiée',
            'content_description'   => $this->buildContentDescription($order),
            'cod_amount'            => (float) $order->total_price,
            'payment_method'        => 'COD',
            'is_fragile'            => false,
            'is_exchange'           => false,
            'allow_opening'         => true,
            'notes'                 => $order->notes,
            'comment'               => 'Commande #' . $order->id . ' - ' . ($order->admin->shop_name ?? 'Order Manager'),
        ];
    }

    /**
     * Convert order-manager region name to Kolixy zone format (UPPERCASE, underscores)
     * Handles naming differences between the two systems
     */
    private function toKolixyZone(string $regionName): string
    {
        // Special mappings for names that differ between systems
        $specialMappings = [
            'Le Kef'     => 'KEF',
            'Sidi Bouzid' => 'SIDI_BOUZID',
            'Ben Arous'  => 'BEN_AROUS',
        ];

        if (isset($specialMappings[$regionName])) {
            return $specialMappings[$regionName];
        }

        // General rule: uppercase + replace spaces with underscores
        return mb_strtoupper(str_replace(' ', '_', $regionName));
    }

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

    private function getHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . ($this->config->api_token ?? ''),
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
        ];
    }
}
