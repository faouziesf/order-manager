<?php

namespace App\Http\Controllers\Confirmi;

use App\Http\Controllers\Controller;
use App\Traits\HasOrderProcessing;
use App\Models\Order;
use App\Models\ConfirmiOrderAssignment;
use App\Models\Region;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ConfirmiProcessController extends Controller
{
    use HasOrderProcessing;

    /**
     * Contextual admin ID — each order belongs to a different admin.
     */
    private int $contextAdminId = 0;

    // ───── HasOrderProcessing contract ─────

    protected function resolveAdminId(): int
    {
        return $this->contextAdminId;
    }

    private function user()
    {
        return Auth::guard('confirmi')->user();
    }

    // ───── Views ─────

    public function interface()
    {
        return view('confirmi.employee.process.interface');
    }

    // ───── Queue API ─────

    public function getQueueApi($queue)
    {
        try {
            $user = $this->user();
            if (!$user) {
                return response()->json(['error' => 'Non authentifié', 'hasOrder' => false], 401);
            }
            if (!in_array($queue, ['standard', 'dated', 'old', 'restock'])) {
                return response()->json(['error' => 'File invalide', 'hasOrder' => false], 400);
            }

            $assignments = ConfirmiOrderAssignment::where('assigned_to', $user->id)
                ->whereIn('status', ['assigned', 'in_progress'])
                ->with(['order.items.product', 'order.admin'])
                ->get();

            foreach ($assignments as $assignment) {
                $order = $assignment->order;
                if (!$order) continue;

                $this->contextAdminId = $order->admin_id;
                $this->resetDailyCounters($order->admin);

                if ($this->matchesQueue($order, $queue) && !$this->orderHasStockIssues($order)) {
                    if ($assignment->status === 'assigned') {
                        $assignment->update([
                            'status' => 'in_progress',
                            'first_attempt_at' => $assignment->first_attempt_at ?? now(),
                        ]);
                    }

                    $data = $this->formatOrderData($order);
                    $data['assignment_id'] = $assignment->id;
                    $data['admin_name'] = $order->admin->shop_name ?? $order->admin->name ?? 'N/A';

                    return response()->json(['hasOrder' => true, 'order' => $data]);
                }
            }

            return response()->json(['hasOrder' => false, 'message' => 'Aucune commande disponible']);
        } catch (\Exception $e) {
            Log::error('Confirmi getQueueApi: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur interne', 'hasOrder' => false], 500);
        }
    }

    // ───── Counts ─────

    public function getCounts()
    {
        try {
            $user = $this->user();
            if (!$user) {
                return response()->json(['error' => 'Non authentifié'], 401);
            }

            $assignments = ConfirmiOrderAssignment::where('assigned_to', $user->id)
                ->whereIn('status', ['assigned', 'in_progress'])
                ->with(['order.items.product'])
                ->get();

            $counts = ['standard' => 0, 'dated' => 0, 'old' => 0, 'restock' => 0];

            foreach ($assignments as $assignment) {
                $order = $assignment->order;
                if (!$order) continue;

                $this->contextAdminId = $order->admin_id;

                foreach (['standard', 'dated', 'old', 'restock'] as $q) {
                    if ($this->matchesQueue($order, $q) && !$this->orderHasStockIssues($order)) {
                        $counts[$q]++;
                        break;
                    }
                }
            }

            $counts['timestamp'] = now()->toISOString();
            return response()->json($counts);
        } catch (\Exception $e) {
            Log::error('Confirmi getCounts: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur', 'standard' => 0, 'dated' => 0, 'old' => 0, 'restock' => 0], 500);
        }
    }

    // ───── Process Action ─────

    public function processAction(Request $request, Order $order)
    {
        try {
            $user = $this->user();

            $assignment = ConfirmiOrderAssignment::where('order_id', $order->id)
                ->where('assigned_to', $user->id)
                ->whereIn('status', ['assigned', 'in_progress'])
                ->first();

            if (!$assignment) {
                return response()->json(['error' => 'Commande non assignée à vous'], 403);
            }

            $this->contextAdminId = $order->admin_id;

            DB::beginTransaction();

            $action = $request->action;
            $this->validateProcessAction($request, $action);
            $actor = "[Confirmi] {$user->name}";

            switch ($action) {
                case 'call':
                    $this->recordCallAttempt($order, $request->notes);
                    $assignment->increment('attempts');
                    $assignment->update([
                        'last_attempt_at' => now(),
                        'last_result' => 'no_answer',
                        'notes' => $request->notes,
                    ]);
                    $order->recordHistory('tentative', "{$actor} a tenté d'appeler : {$request->notes}");
                    break;

                case 'confirm':
                    $this->confirmOrder($order, $request, $actor, $assignment);
                    break;

                case 'cancel':
                    $this->cancelOrder($order, $request->notes, $actor, $assignment);
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

            return response()->json([
                'success' => true,
                'message' => 'Action traitée',
                'order_id' => $order->id,
                'action' => $action,
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['error' => 'Erreur de validation', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Confirmi processAction: ' . $e->getMessage(), ['order_id' => $order->id ?? null]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ───── Product search (scoped) ─────

    public function searchProducts(Request $request)
    {
        $user = $this->user();
        $search = $request->get('search', '');
        $adminId = $request->get('admin_id');

        if (!$adminId || strlen($search) < 2) {
            return response()->json([]);
        }

        $hasAccess = ConfirmiOrderAssignment::where('assigned_to', $user->id)
            ->where('admin_id', $adminId)
            ->whereIn('status', ['assigned', 'in_progress'])
            ->exists();

        if (!$hasAccess) {
            return response()->json([]);
        }

        return response()->json(
            \App\Models\Product::where('admin_id', $adminId)
                ->where('is_active', true)
                ->where('name', 'like', "%{$search}%")
                ->where('stock', '>', 0)
                ->select('id', 'name', 'price', 'stock')
                ->limit(10)
                ->get()
        );
    }

    // ───── Location helpers ─────

    public function getRegions()
    {
        return response()->json(Region::orderBy('name')->get(['id', 'name']));
    }

    public function getCities(Request $request)
    {
        $regionId = $request->get('region_id');
        if (!$regionId) return response()->json([]);
        return response()->json(City::where('region_id', $regionId)->orderBy('name')->get(['id', 'name']));
    }
}
