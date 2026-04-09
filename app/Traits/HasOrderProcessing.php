<?php

namespace App\Traits;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\AdminSetting;
use App\Models\ConfirmiBilling;
use App\Models\ConfirmiOrderAssignment;
use App\Models\EmballageTask;
use App\Models\CompanyKolixyConfig;
use App\Models\MasafaConfiguration;
use App\Services\KolixyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Trait unifié de traitement des commandes.
 *
 * Utilisé par Admin\ProcessController, Confirmi\ConfirmiProcessController
 * et Confirmi\ConfirmiEmballageController pour garantir des règles identiques
 * de décrémentation de stock, de confirmation, d'annulation et d'envoi Kolixy.
 *
 * Le contrôleur hôte DOIT implémenter :
 *   - resolveAdminId(): int  → l'admin_id propriétaire de la commande courante
 */
trait HasOrderProcessing
{
    // ------------------------------------------------------------------
    //  SETTINGS
    // ------------------------------------------------------------------

    protected function getProcessingSetting(string $key, $default = null)
    {
        $adminId = $this->resolveAdminId();
        if (!$adminId) {
            return $default;
        }
        return AdminSetting::getForAdmin($adminId, $key, $default);
    }

    // ------------------------------------------------------------------
    //  DAILY RESET
    // ------------------------------------------------------------------

    protected function resetDailyCounters($admin): void
    {
        $adminId = is_object($admin) ? $admin->id : $admin;
        $lastReset = AdminSetting::getForAdmin($adminId, 'last_daily_reset');
        $today = now()->format('Y-m-d');

        if (!$lastReset || $lastReset !== $today) {
            Order::where('admin_id', $adminId)->update(['daily_attempts_count' => 0]);
            AdminSetting::setForAdmin($adminId, 'last_daily_reset', $today);
        }
    }

    // ------------------------------------------------------------------
    //  STOCK VERIFICATION
    // ------------------------------------------------------------------

    protected function orderHasStockIssues(Order $order): bool
    {
        if (!$order->relationLoaded('items')) {
            $order->load(['items.product']);
        }

        if (!$order->items || $order->items->isEmpty()) {
            return true;
        }

        foreach ($order->items as $item) {
            if (!$item->product_id || !$item->quantity) {
                return true;
            }
            if (!$item->product || !$item->product->is_active) {
                return true;
            }
            if ((int) $item->product->stock < (int) $item->quantity) {
                return true;
            }
        }

        return false;
    }

    protected function analyzeStockIssues(Order $order): array
    {
        $analysis = [
            'hasIssues' => false,
            'availableItems' => collect(),
            'unavailableItems' => collect(),
            'issues' => [],
        ];

        if (!$order->items || $order->items->isEmpty()) {
            $analysis['hasIssues'] = true;
            $analysis['issues'][] = ['type' => 'no_items', 'message' => 'Commande sans produits'];
            return $analysis;
        }

        foreach ($order->items as $item) {
            $itemIssues = [];

            if (!$item->product) {
                $itemIssues[] = 'Produit supprimé';
            } else {
                if (!$item->product->is_active) {
                    $itemIssues[] = 'Produit inactif';
                }
                if ((int) ($item->product->stock ?? 0) < (int) $item->quantity) {
                    $itemIssues[] = "Stock insuffisant (besoin: {$item->quantity}, dispo: {$item->product->stock})";
                }
            }

            if ($itemIssues) {
                $analysis['unavailableItems']->push($item);
                $analysis['issues'][] = [
                    'product_name' => $item->product->name ?? "Produit #{$item->product_id}",
                    'reasons' => $itemIssues,
                ];
                $analysis['hasIssues'] = true;
            } else {
                $analysis['availableItems']->push($item);
            }
        }

        return $analysis;
    }

    protected function autoSuspendForStock(Order $order): bool
    {
        if (!$this->orderHasStockIssues($order)) {
            return false;
        }

        $analysis = $this->analyzeStockIssues($order);
        $reasons = [];
        foreach ($analysis['issues'] as $issue) {
            $reasons[] = ($issue['product_name'] ?? '') . ': ' . implode(', ', $issue['reasons'] ?? [$issue['message'] ?? '']);
        }

        $order->is_suspended = true;
        $order->suspension_reason = 'Auto-suspension stock: ' . implode(' | ', array_slice($reasons, 0, 3));
        $order->save();

        $order->recordHistory('suspension', 'Suspension auto — stock insuffisant');

        return true;
    }

    // ------------------------------------------------------------------
    //  CALL ATTEMPT
    // ------------------------------------------------------------------

    protected function recordCallAttempt(Order $order, string $notes): void
    {
        $order->increment('attempts_count');
        $order->increment('daily_attempts_count');
        $order->last_attempt_at = now();
        $order->save();

        $order->recordHistory('tentative', $notes);

        $this->checkAutoTransition($order);
    }

    protected function checkAutoTransition(Order $order): bool
    {
        if ($order->status !== 'nouvelle') {
            return false;
        }

        $max = (int) $this->getProcessingSetting('standard_max_total_attempts', 9);

        if ($order->attempts_count >= $max) {
            $prev = $order->status;
            $order->status = 'ancienne';
            $order->save();

            $order->recordHistory(
                'modification',
                "Auto-transition vers file ancienne après {$order->attempts_count} tentatives",
                ['previous_status' => $prev, 'new_status' => 'ancienne', 'auto_transition' => true],
                $prev,
                'ancienne'
            );

            return true;
        }

        return false;
    }

    // ------------------------------------------------------------------
    //  CONFIRM ORDER  (single source of truth for stock decrement)
    // ------------------------------------------------------------------

    /**
     * Confirme une commande, met à jour client/items/stock dans une transaction.
     *
     * @param  Order   $order
     * @param  Request $request    doit contenir confirmed_price, customer_*, cart_items[]
     * @param  string  $actor      description courte de l'acteur (ex: "Admin #3", "Confirmi Marie")
     * @param  ConfirmiOrderAssignment|null $assignment  si appelé depuis Confirmi
     */
    protected function confirmOrder(Order $order, Request $request, string $actor, ?ConfirmiOrderAssignment $assignment = null): void
    {
        // —— 1. Pre-flight stock check
        $cartItems = $request->input('cart_items', []);
        $this->assertStockAvailable($order, $cartItems);

        // —— 2. Update customer info
        $order->status = 'confirmée';
        $order->total_price = $request->confirmed_price;
        $order->customer_name = $request->customer_name;
        $order->customer_phone_2 = $request->customer_phone_2;
        $order->customer_governorate = $request->customer_governorate;
        $order->customer_city = $request->customer_city;
        $order->customer_address = $request->customer_address;
        $order->is_suspended = false;
        $order->suspension_reason = null;
        $order->save();

        // —— 3. Replace items & decrement stock (atomic)
        $this->replaceOrderItemsAndDecrementStock($order, $cartItems);

        // —— 4. History
        $order->recordHistory('confirmation', "Confirmée par {$actor} — {$request->confirmed_price} TND");

        // —— 5. Confirmi-specific: assignment, billing, emballage task
        if ($assignment) {
            $assignment->update([
                'status' => 'confirmed',
                'completed_at' => now(),
                'last_result' => 'confirmed',
            ]);
            $assignment->increment('attempts');

            $this->createConfirmiBilling($order);
            $this->createEmballageTaskIfNeeded($order, $assignment);
        }

        // —— 6. Auto-route to delivery carrier
        $this->routeToDelivery($order);
    }

    /**
     * Assert every cart item has enough stock — throws on failure.
     */
    private function assertStockAvailable(Order $order, array $cartItems): void
    {
        foreach ($cartItems as $item) {
            $product = Product::where('id', $item['product_id'])
                ->where('admin_id', $order->admin_id)
                ->where('is_active', true)
                ->first();

            if (!$product) {
                throw new \RuntimeException("Produit #{$item['product_id']} introuvable ou inactif.");
            }

            if ((int) $product->stock < (int) $item['quantity']) {
                throw new \RuntimeException("Stock insuffisant pour {$product->name} (dispo: {$product->stock}, demandé: {$item['quantity']}).");
            }
        }
    }

    /**
     * Remove old items, create new ones, and atomically decrement stock.
     * Uses SELECT … FOR UPDATE to prevent race conditions.
     */
    private function replaceOrderItemsAndDecrementStock(Order $order, array $cartItems): void
    {
        // Delete previous items — don't restock: order was not confirmed before
        $order->items()->delete();

        // Aggregate quantities per product (handles duplicate rows)
        $aggregated = [];
        foreach ($cartItems as $item) {
            $pid = $item['product_id'];
            if (!isset($aggregated[$pid])) {
                $aggregated[$pid] = ['quantity' => 0, 'unit_price' => $item['unit_price'] ?? null];
            }
            $aggregated[$pid]['quantity'] += (int) $item['quantity'];
            // keep last unit_price if set
            if (isset($item['unit_price'])) {
                $aggregated[$pid]['unit_price'] = (float) $item['unit_price'];
            }
        }

        foreach ($aggregated as $productId => $data) {
            $product = Product::where('id', $productId)
                ->where('admin_id', $order->admin_id)
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();

            if (!$product) {
                throw new \RuntimeException("Produit #{$productId} introuvable lors de la décrémentation.");
            }

            $qty = $data['quantity'];
            $unitPrice = $data['unit_price'] ?? $product->price;

            if ((int) $product->stock < $qty) {
                throw new \RuntimeException("Stock insuffisant pour {$product->name}.");
            }

            $order->items()->create([
                'product_id' => $product->id,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'total_price' => $qty * $unitPrice,
            ]);

            $oldStock = $product->stock;
            $product->decrement('stock', $qty);

            Log::info("STOCK ▼ Produit {$product->id} ({$product->name}): {$oldStock}→" . ($oldStock - $qty) . " (-{$qty}) | Commande #{$order->id}");
        }
    }

    // ------------------------------------------------------------------
    //  CANCEL ORDER
    // ------------------------------------------------------------------

    protected function cancelOrder(Order $order, string $notes, string $actor, ?ConfirmiOrderAssignment $assignment = null): void
    {
        $order->status = 'annulée';
        $order->is_suspended = false;
        $order->suspension_reason = null;
        $order->save();

        $order->recordHistory('annulation', "Annulée par {$actor}: {$notes}");

        if ($assignment) {
            $assignment->update([
                'status' => 'cancelled',
                'completed_at' => now(),
                'last_result' => 'cancelled',
            ]);
        }
    }

    // ------------------------------------------------------------------
    //  SCHEDULE ORDER
    // ------------------------------------------------------------------

    protected function scheduleOrder(Order $order, string $date, ?string $notes, string $actor): void
    {
        $order->status = 'datée';
        $order->scheduled_date = $date;
        $order->attempts_count = 0;
        $order->daily_attempts_count = 0;
        $order->last_attempt_at = null;
        $order->save();

        $msg = "Datée pour {$date} par {$actor}";
        if ($notes) {
            $msg .= " : {$notes}";
        }
        $order->recordHistory('datation', $msg);
    }

    // ------------------------------------------------------------------
    //  REACTIVATE (RESTOCK)
    // ------------------------------------------------------------------

    protected function reactivateOrder(Order $order, string $actor): void
    {
        if ($this->orderHasStockIssues($order)) {
            throw new \RuntimeException('Stock toujours insuffisant.');
        }

        $order->is_suspended = false;
        $order->suspension_reason = null;
        $order->status = 'nouvelle';
        $order->attempts_count = 0;
        $order->daily_attempts_count = 0;
        $order->last_attempt_at = null;
        $order->save();

        $order->recordHistory('réactivation', "Réactivée depuis restock par {$actor}");
    }

    // ------------------------------------------------------------------
    //  CONFIRMI BILLING
    // ------------------------------------------------------------------

    private function createConfirmiBilling(Order $order): void
    {
        $admin = $order->admin;
        if (!$admin || !($admin->confirmi_rate_confirmed > 0)) {
            return;
        }

        ConfirmiBilling::create([
            'admin_id' => $admin->id,
            'order_id' => $order->id,
            'billing_type' => 'confirmed',
            'amount' => $admin->confirmi_rate_confirmed,
            'billed_at' => now(),
        ]);
    }

    // ------------------------------------------------------------------
    //  EMBALLAGE TASK CREATION
    // ------------------------------------------------------------------

    private function createEmballageTaskIfNeeded(Order $order, ConfirmiOrderAssignment $assignment): void
    {
        $admin = $order->admin;
        if (!$admin || !$admin->emballage_enabled) {
            return;
        }

        $taskData = [
            'order_id' => $order->id,
            'admin_id' => $admin->id,
            'assignment_id' => $assignment->id,
            'status' => 'pending',
            'assigned_by' => $assignment->assigned_to,
        ];

        if ($admin->confirmi_default_agent_id) {
            $taskData['assigned_to'] = $admin->confirmi_default_agent_id;
            $taskData['assigned_at'] = now();
        }

        EmballageTask::create($taskData);
    }

    // ------------------------------------------------------------------
    //  AUTOMATED DELIVERY ROUTING
    // ------------------------------------------------------------------

    /**
     * Route a confirmed order to the correct Kolixy account:
     *   - If admin has emballage_enabled → Kolixy Société (CompanyKolixyConfig)
     *   - Otherwise → Kolixy Personnel de l'admin (MasafaConfiguration)
     *
     * This is called automatically after confirmation.
     * If no config exists, silently skip (admin may send manually later).
     */
    protected function routeToDelivery(Order $order): void
    {
        $admin = $order->admin;
        if (!$admin) {
            return;
        }

        try {
            if ($admin->emballage_enabled) {
                // Emballage active → order goes to agent pipeline → BL created later
                // No immediate API call — the EmballageTask will handle it
                Log::info("[Delivery] Commande #{$order->id} → pipeline emballage (Kolixy Société)");
                return;
            }

            // Direct send via admin's personal Kolixy config
            $config = $admin->kolixyConfiguration;
            if (!$config || !$config->is_active || !$config->api_token) {
                Log::info("[Delivery] Commande #{$order->id} — pas de config Kolixy personnelle, envoi manuel requis.");
                return;
            }

            $kolixy = new KolixyService($config);
            $result = $kolixy->createPackage($order);

            if ($result['success']) {
                $order->update([
                    'status' => 'expédiée',
                    'tracking_number' => $result['tracking_number'] ?? $order->tracking_number,
                    'carrier_name' => 'Kolixy',
                ]);
                $order->recordHistory('expédition', "Envoyée auto vers Kolixy — Tracking: " . ($result['tracking_number'] ?? 'N/A'));
                Log::info("[Delivery] Commande #{$order->id} → Kolixy Personnel OK, tracking: " . ($result['tracking_number'] ?? 'N/A'));
            } else {
                Log::warning("[Delivery] Commande #{$order->id} → Kolixy Personnel ECHEC: " . ($result['message'] ?? 'erreur inconnue'));
            }
        } catch (\Exception $e) {
            Log::error("[Delivery] Exception routeToDelivery commande #{$order->id}: " . $e->getMessage());
        }
    }

    // ------------------------------------------------------------------
    //  VALIDATION RULES
    // ------------------------------------------------------------------

    protected function validateProcessAction(Request $request, string $action): void
    {
        $rules = [];
        $messages = [];

        switch ($action) {
            case 'call':
                $rules = ['notes' => 'required|string|min:3|max:1000'];
                $messages = ['notes.required' => 'Une note est obligatoire'];
                break;
            case 'confirm':
                $rules = [
                    'confirmed_price' => 'required|numeric|min:0.001',
                    'customer_name' => 'required|string|min:2|max:255',
                    'customer_governorate' => 'required|string|max:255',
                    'customer_city' => 'required|string|max:255',
                    'customer_address' => 'required|string|min:5|max:500',
                    'cart_items' => 'required|array|min:1',
                    'cart_items.*.product_id' => 'required|exists:products,id',
                    'cart_items.*.quantity' => 'required|integer|min:1',
                    'cart_items.*.unit_price' => 'required|numeric|min:0',
                ];
                break;
            case 'cancel':
                $rules = ['notes' => 'required|string|min:3|max:1000'];
                break;
            case 'schedule':
                $rules = ['scheduled_date' => 'required|date|after_or_equal:today'];
                break;
            case 'reactivate':
                $rules = ['queue' => 'required|in:restock'];
                break;
        }

        $request->validate($rules, $messages);
    }

    // ------------------------------------------------------------------
    //  QUEUE MATCHING
    // ------------------------------------------------------------------

    protected function matchesQueue(Order $order, string $queue): bool
    {
        return match ($queue) {
            'standard' => $order->status === 'nouvelle' && !$order->is_suspended,
            'dated' => $order->status === 'datée' && !$order->is_suspended
                          && $order->scheduled_date && $order->scheduled_date <= now(),
            'old' => $order->status === 'ancienne',
            'restock' => $order->is_suspended
                          && in_array($order->status, ['nouvelle', 'datée'])
                          && preg_match('/stock|rupture/i', $order->suspension_reason ?? ''),
            default => false,
        };
    }

    // ------------------------------------------------------------------
    //  FORMAT ORDER DATA
    // ------------------------------------------------------------------

    protected function formatOrderData(Order $order): array
    {
        $duplicateInfo = $this->getDuplicateInfo($order);

        return [
            'id' => $order->id,
            'status' => $order->status,
            'priority' => $order->priority,
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'customer_phone_2' => $order->customer_phone_2,
            'customer_governorate' => $order->customer_governorate,
            'customer_city' => $order->customer_city,
            'customer_address' => $order->customer_address,
            'total_price' => $order->total_price,
            'attempts_count' => $order->attempts_count,
            'daily_attempts_count' => $order->daily_attempts_count,
            'last_attempt_at' => $order->last_attempt_at?->toISOString(),
            'scheduled_date' => $order->scheduled_date?->format('Y-m-d'),
            'is_assigned' => $order->is_assigned,
            'is_suspended' => $order->is_suspended,
            'suspension_reason' => $order->suspension_reason,
            'notes' => $order->notes,
            'created_at' => $order->created_at->toISOString(),
            'updated_at' => $order->updated_at->toISOString(),
            'duplicate_info' => $duplicateInfo,
            'items' => $order->items ? $order->items->map(fn($item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total_price' => $item->total_price,
                'product' => $item->product ? [
                    'id' => $item->product->id,
                    'name' => $item->product->name,
                    'price' => $item->product->price,
                    'stock' => $item->product->stock,
                    'is_active' => $item->product->is_active,
                ] : null,
            ]) : [],
        ];
    }

    protected function getDuplicateInfo(Order $order): array
    {
        if (!$order) {
            return ['has_duplicates' => false, 'duplicates_count' => 0, 'duplicates' => []];
        }

        $duplicates = Order::where('admin_id', $order->admin_id)
            ->where('id', '!=', $order->id)
            ->where(function ($q) use ($order) {
                $q->where('customer_phone', $order->customer_phone);
                if ($order->customer_phone_2) {
                    $q->orWhere('customer_phone', $order->customer_phone_2)
                      ->orWhere('customer_phone_2', $order->customer_phone)
                      ->orWhere('customer_phone_2', $order->customer_phone_2);
                }
            })
            ->with(['items.product'])
            ->orderByDesc('created_at')
            ->get();

        return [
            'has_duplicates' => $duplicates->isNotEmpty(),
            'duplicates_count' => $duplicates->count(),
            'duplicates' => $duplicates->map(fn($d) => [
                'id' => $d->id,
                'status' => $d->status,
                'customer_name' => $d->customer_name,
                'customer_phone' => $d->customer_phone,
                'total_price' => $d->total_price,
                'created_at' => $d->created_at->format('d/m/Y H:i'),
                'items' => $d->items->map(fn($item) => [
                    'product_name' => $item->product->name ?? 'Produit supprimé',
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                ]),
            ]),
        ];
    }
}
