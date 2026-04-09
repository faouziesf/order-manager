<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\HasOrderProcessing;
use App\Models\Order;
use App\Models\AdminSetting;
use App\Models\ConfirmiOrderAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ProcessController extends Controller
{
    use HasOrderProcessing;

    private static $countsCache = null;
    private static $cacheTime = null;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth('admin')->check()) {
                abort(401, 'Non authentifié');
            }
            return $next($request);
        });
    }

    // ───── HasOrderProcessing contract ─────

    protected function resolveAdminId(): int
    {
        $user = auth('admin')->user();
        return $user ? $user->getEffectiveAdminId() : 0;
    }

    // ───── Views ─────

    public function interface()
    {
        return view('admin.process.interface');
    }

    // ───── Queue API ─────

    public function getQueueApi($queue)
    {
        try {
            $admin = auth('admin')->user();
            if (!$admin) {
                return response()->json(['error' => 'Non authentifié', 'hasOrder' => false], 401);
            }
            if (!in_array($queue, ['standard', 'dated', 'old', 'restock'])) {
                return response()->json(['error' => 'File invalide', 'hasOrder' => false], 400);
            }

            $this->resetDailyCounters($admin);
            $order = $this->findOrderForQueue($admin, $queue);

            if ($order) {
                if ($this->orderHasStockIssues($order)) {
                    if (!$order->is_suspended && in_array($queue, ['standard', 'dated'])) {
                        $this->autoSuspendForStock($order);
                    }
                    return $this->getQueueApi($queue);
                }

                return response()->json(['hasOrder' => true, 'order' => $this->formatOrderData($order)]);
            }

            return response()->json(['hasOrder' => false, 'message' => 'Aucune commande disponible']);
        } catch (\Exception $e) {
            Log::error('getQueueApi: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur interne', 'hasOrder' => false], 500);
        }
    }

    // ───── Process Action ─────

    public function processAction(Request $request, Order $order)
    {
        try {
            $admin = auth('admin')->user();

            // Scope check
            $effectiveId = $admin->getEffectiveAdminId();
            if ((int) $order->admin_id !== $effectiveId) {
                return response()->json(['error' => 'Accès refusé'], 403);
            }

            // If managed by Confirmi, admin takes over — auto-cancel assignment
            $confirmiAssignment = ConfirmiOrderAssignment::where('order_id', $order->id)
                ->whereNotIn('status', ['cancelled'])
                ->first();
            if ($confirmiAssignment) {
                $confirmiAssignment->update(['status' => 'cancelled', 'completed_at' => now()]);
                $order->recordHistory('modification', "Reprise manuelle par {$admin->name} — assignation Confirmi annulée");
            }

            DB::beginTransaction();

            $action = $request->action;
            $this->validateProcessAction($request, $action);
            $actor = $admin->name;

            switch ($action) {
                case 'call':
                    $this->recordCallAttempt($order, $request->notes);
                    $order->recordHistory('tentative', "{$actor} a tenté d'appeler : {$request->notes}");
                    break;
                case 'confirm':
                    $this->confirmOrder($order, $request, $actor);
                    break;
                case 'cancel':
                    $this->cancelOrder($order, $request->notes, $actor);
                    break;
                case 'schedule':
                    $this->scheduleOrder($order, $request->scheduled_date, $request->notes, $actor);
                    break;
                case 'reactivate':
                    $this->reactivateOrder($order, $actor);
                    break;
                default:
                    return response()->json(['error' => 'Action non reconnue'], 400);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Action traitée', 'order_id' => $order->id, 'action' => $action]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['error' => 'Erreur de validation', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('processAction: ' . $e->getMessage(), ['order_id' => $order->id ?? null, 'action' => $request->action ?? null]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ───── Counts ─────

    public function getCounts()
    {
        try {
            if (self::$countsCache && self::$cacheTime && now()->diffInSeconds(self::$cacheTime) < 10) {
                $cached = self::$countsCache;
                $cached['timestamp'] = now()->toISOString();
                return response()->json($cached);
            }

            $admin = auth('admin')->user();
            if (!$admin) {
                return response()->json(['error' => 'Non authentifié'], 401);
            }

            $this->resetDailyCounters($admin);

            $counts = [
                'standard' => $this->getQueueCount($admin, 'standard'),
                'dated' => $this->getQueueCount($admin, 'dated'),
                'old' => $this->getQueueCount($admin, 'old'),
                'examination' => $this->getExaminationCount($admin),
                'suspended' => $this->getSuspendedCount($admin),
                'restock' => $this->getQueueCount($admin, 'restock'),
                'timestamp' => now()->toISOString(),
            ];

            self::$countsCache = $counts;
            self::$cacheTime = now();

            return response()->json($counts);
        } catch (\Exception $e) {
            Log::error('getCounts: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur', 'standard' => 0, 'dated' => 0, 'old' => 0, 'examination' => 0, 'suspended' => 0, 'restock' => 0], 500);
        }
    }

    public function test()
    {
        $admin = auth('admin')->user();
        return response()->json([
            'success' => true,
            'admin' => $admin ? "{$admin->name} (ID: {$admin->id})" : 'Non authentifié',
            'timestamp' => now()->toISOString(),
        ]);
    }

    // ───── Private: queue finding ─────

    private function findOrderForQueue($admin, $queue)
    {
        return match ($queue) {
            'standard' => $this->findQueueOrder($admin, 'nouvelle', false),
            'dated' => $this->findQueueOrder($admin, 'datée', false),
            'old' => $this->findQueueOrder($admin, 'ancienne', false),
            'restock' => $this->findQueueOrder($admin, 'restock', true),
            default => null,
        };
    }

    private function findQueueOrder($admin, string $status, bool $isRestock)
    {
        $effectiveId = $admin->getEffectiveAdminId();

        $query = Order::with(['items.product' => fn($q) => $q->where('is_active', true)]);

        if ($admin->isEmployee()) {
            $query->where('employee_id', $admin->id);
        } else {
            $query->where('admin_id', $effectiveId);
        }

        if ($isRestock) {
            $max = (int) $this->getProcessingSetting('restock_max_total_attempts', 5);
            $daily = (int) $this->getProcessingSetting('restock_max_daily_attempts', 2);
            $delay = (float) $this->getProcessingSetting('restock_delay_hours', 1);

            $query->where('is_suspended', true)
                ->whereIn('status', ['nouvelle', 'datée'])
                ->where(fn($q) => $q->where('suspension_reason', 'like', '%stock%')
                    ->orWhere('suspension_reason', 'like', '%rupture%'))
                ->where('attempts_count', '<', $max)
                ->where('daily_attempts_count', '<', $daily)
                ->where(fn($q) => $q->whereNull('last_attempt_at')
                    ->orWhere('last_attempt_at', '<=', now()->subHours($delay)));
        } elseif ($status === 'ancienne') {
            $daily = (int) $this->getProcessingSetting('old_max_daily_attempts', 2);
            $delay = (float) $this->getProcessingSetting('old_delay_hours', 6);
            $max = (int) $this->getProcessingSetting('old_max_total_attempts', 0);

            $query->where('status', 'ancienne')
                ->where('daily_attempts_count', '<', $daily)
                ->where(fn($q) => $q->whereNull('last_attempt_at')
                    ->orWhere('last_attempt_at', '<=', now()->subHours($delay)));

            if ($max > 0) {
                $query->where('attempts_count', '<', $max);
            }
        } else {
            $prefix = $status === 'nouvelle' ? 'standard' : 'dated';
            $max = (int) $this->getProcessingSetting("{$prefix}_max_total_attempts", $status === 'nouvelle' ? 9 : 5);
            $daily = (int) $this->getProcessingSetting("{$prefix}_max_daily_attempts", $status === 'nouvelle' ? 3 : 2);
            $delay = (float) $this->getProcessingSetting("{$prefix}_delay_hours", $status === 'nouvelle' ? 2.5 : 3.5);

            $query->where('status', $status)
                ->where(fn($q) => $q->where('is_suspended', false)->orWhereNull('is_suspended'))
                ->where('attempts_count', '<', $max)
                ->where('daily_attempts_count', '<', $daily)
                ->where(fn($q) => $q->whereNull('last_attempt_at')
                    ->orWhere('last_attempt_at', '<=', now()->subHours($delay)));

            if ($status === 'datée') {
                $query->whereDate('scheduled_date', '<=', now());
            }
        }

        $orders = $query->orderByDesc('priority')
            ->orderBy('attempts_count')
            ->orderBy('created_at')
            ->limit(50)
            ->get();

        foreach ($orders as $order) {
            if (!$this->orderHasStockIssues($order)) {
                return $order;
            }
            if (!$isRestock && !$order->is_suspended) {
                $this->autoSuspendForStock($order);
            }
        }

        return null;
    }

    private function getQueueCount($admin, string $queue): int
    {
        $effectiveId = $admin->getEffectiveAdminId();

        $query = Order::where('admin_id', $effectiveId)
            ->with(['items.product' => fn($q) => $q->where('is_active', true)]);

        switch ($queue) {
            case 'standard':
                $max = (int) $this->getProcessingSetting('standard_max_total_attempts', 9);
                $daily = (int) $this->getProcessingSetting('standard_max_daily_attempts', 3);
                $delay = (float) $this->getProcessingSetting('standard_delay_hours', 2.5);

                $query->where('status', 'nouvelle')
                    ->where(fn($q) => $q->where('is_suspended', false)->orWhereNull('is_suspended'))
                    ->where('attempts_count', '<', $max)
                    ->where('daily_attempts_count', '<', $daily)
                    ->where(fn($q) => $q->whereNull('last_attempt_at')->orWhere('last_attempt_at', '<=', now()->subHours($delay)));
                break;
            case 'dated':
                $max = (int) $this->getProcessingSetting('dated_max_total_attempts', 5);
                $daily = (int) $this->getProcessingSetting('dated_max_daily_attempts', 2);
                $delay = (float) $this->getProcessingSetting('dated_delay_hours', 3.5);

                $query->where('status', 'datée')
                    ->whereDate('scheduled_date', '<=', now())
                    ->where(fn($q) => $q->where('is_suspended', false)->orWhereNull('is_suspended'))
                    ->where('attempts_count', '<', $max)
                    ->where('daily_attempts_count', '<', $daily)
                    ->where(fn($q) => $q->whereNull('last_attempt_at')->orWhere('last_attempt_at', '<=', now()->subHours($delay)));
                break;
            case 'old':
                $daily = (int) $this->getProcessingSetting('old_max_daily_attempts', 2);
                $delay = (float) $this->getProcessingSetting('old_delay_hours', 6);
                $max = (int) $this->getProcessingSetting('old_max_total_attempts', 0);

                $query->where('status', 'ancienne')
                    ->where('daily_attempts_count', '<', $daily)
                    ->where(fn($q) => $q->whereNull('last_attempt_at')->orWhere('last_attempt_at', '<=', now()->subHours($delay)));
                if ($max > 0) $query->where('attempts_count', '<', $max);
                break;
            case 'restock':
                $max = (int) $this->getProcessingSetting('restock_max_total_attempts', 5);
                $daily = (int) $this->getProcessingSetting('restock_max_daily_attempts', 2);
                $delay = (float) $this->getProcessingSetting('restock_delay_hours', 1);

                $query->where('is_suspended', true)
                    ->whereIn('status', ['nouvelle', 'datée'])
                    ->where(fn($q) => $q->where('suspension_reason', 'like', '%stock%')->orWhere('suspension_reason', 'like', '%rupture%'))
                    ->where('attempts_count', '<', $max)
                    ->where('daily_attempts_count', '<', $daily)
                    ->where(fn($q) => $q->whereNull('last_attempt_at')->orWhere('last_attempt_at', '<=', now()->subHours($delay)));
                break;
        }

        $count = 0;
        $isRestock = $queue === 'restock';
        foreach ($query->get() as $order) {
            if ($isRestock ? !$this->orderHasStockIssues($order) : !$this->orderHasStockIssues($order)) {
                $count++;
            }
        }
        return $count;
    }

    private function getExaminationCount($admin): int
    {
        $orders = Order::where('admin_id', $admin->getEffectiveAdminId())
            ->with(['items.product' => fn($q) => $q->where('is_active', true)])
            ->where(fn($q) => $q->where('is_suspended', false)->orWhereNull('is_suspended'))
            ->whereIn('status', ['nouvelle', 'confirmée', 'datée'])
            ->get();

        $count = 0;
        foreach ($orders as $order) {
            if ($this->orderHasStockIssues($order)) $count++;
        }
        return $count;
    }

    private function getSuspendedCount($admin): int
    {
        return Order::where('admin_id', $admin->getEffectiveAdminId())
            ->where('is_suspended', true)
            ->whereNotIn('status', ['annulée', 'livrée'])
            ->count();
    }
}
