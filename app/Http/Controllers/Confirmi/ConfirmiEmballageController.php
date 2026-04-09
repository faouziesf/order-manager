<?php

namespace App\Http\Controllers\Confirmi;

use App\Http\Controllers\Controller;
use App\Traits\HasOrderProcessing;
use App\Models\Admin;
use App\Models\CompanyKolixyConfig;
use App\Models\EmballageTask;
use App\Models\MasafaConfiguration;
use App\Services\KolixyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ConfirmiEmballageController extends Controller
{
    use HasOrderProcessing;

    protected function resolveAdminId(): int
    {
        return 0; // Agent context — not tied to a single admin
    }

    private function user()
    {
        return Auth::guard('confirmi')->user();
    }
    /**
     * Agent emballage interface
     */
    public function interface()
    {
        return view('confirmi.agent.interface');
    }

    /**
     * API: Get tasks for a specific tab (pending, received, packed)
     */
    public function getTasks(Request $request, string $tab)
    {
        $user = $this->user();

        $query = EmballageTask::where('assigned_to', $user->id)
            ->with(['order.items.product', 'admin:id,name,store_name']);

        switch ($tab) {
            case 'pending':
                $query->where('status', 'pending');
                break;
            case 'received':
                $query->where('status', 'received');
                break;
            case 'packed':
                $query->where('status', 'packed');
                break;
            case 'shipped':
                $query->where('status', 'shipped');
                break;
            default:
                return response()->json(['error' => 'Tab invalide'], 400);
        }

        $tasks = $query->latest()->get()->map(function ($task) {
            $order = $task->order;
            return [
                'id' => $task->id,
                'order_id' => $order->id,
                'customer_name' => $order->customer_name,
                'customer_phone' => $order->customer_phone,
                'customer_phone_2' => $order->customer_phone_2,
                'region' => $order->region,
                'city' => $order->city,
                'address' => $order->address,
                'total_price' => $order->total_price,
                'admin_name' => $task->admin->store_name ?? $task->admin->name ?? '-',
                'status' => $task->status,
                'tracking_number' => $task->tracking_number,
                'notes' => $task->notes,
                'created_at' => $task->created_at->format('d/m H:i'),
                'items' => $order->items->map(fn($item) => [
                    'product_name' => $item->product->name ?? $item->product_name ?? 'Produit',
                    'variant' => $item->variant_description ?? '',
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                ]),
            ];
        });

        return response()->json(['tasks' => $tasks]);
    }

    /**
     * API: Get count per tab
     */
    public function getCounts()
    {
        $user = $this->user();

        return response()->json([
            'pending' => EmballageTask::where('assigned_to', $user->id)->where('status', 'pending')->count(),
            'received' => EmballageTask::where('assigned_to', $user->id)->where('status', 'received')->count(),
            'packed' => EmballageTask::where('assigned_to', $user->id)->where('status', 'packed')->count(),
            'shipped' => EmballageTask::where('assigned_to', $user->id)->where('status', 'shipped')->count(),
        ]);
    }

    /**
     * Mark product as received at depot
     */
    public function markReceived(EmballageTask $task)
    {
        $user = $this->user();
        if ($task->assigned_to !== $user->id) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }
        if ($task->status !== 'pending') {
            return response()->json(['error' => 'Tâche déjà traitée'], 422);
        }

        $task->update([
            'status' => 'received',
            'received_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Produit marqué comme reçu.']);
    }

    /**
     * Mark product as packed
     */
    public function markPacked(EmballageTask $task)
    {
        $user = $this->user();
        if ($task->assigned_to !== $user->id) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }
        if ($task->status !== 'received') {
            return response()->json(['error' => 'Le produit doit être reçu avant emballage'], 422);
        }

        $task->update([
            'status' => 'packed',
            'packed_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Produit emballé.']);
    }

    /**
     * Create BL via Kolixy and mark as shipped
     */
    public function createBL(EmballageTask $task)
    {
        $user = $this->user();
        if ($task->assigned_to !== $user->id) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }
        if ($task->status !== 'packed') {
            return response()->json(['error' => 'Le produit doit être emballé avant expédition'], 422);
        }

        // Use company kolixy config
        $companyConfig = CompanyKolixyConfig::active();
        if (!$companyConfig) {
            return response()->json(['error' => 'Configuration Kolixy de la société non configurée. Contactez un commercial.'], 422);
        }

        // Build a MasafaConfiguration-compatible object for KolixyService
        $masafaConfig = new MasafaConfiguration([
            'api_token' => $companyConfig->api_token,
            'masafa_client_id' => $companyConfig->pickup_address_id,
            'is_active' => true,
        ]);

        $kolixy = new KolixyService($masafaConfig);
        $order = $task->order;

        try {
            $result = $kolixy->createPackage($order);

            if ($result['success']) {
                $task->update([
                    'status' => 'shipped',
                    'tracking_number' => $result['tracking_number'] ?? null,
                    'shipped_at' => now(),
                ]);

                // Update order status
                $order->update([
                    'status' => 'expédiée',
                    'tracking_number' => $result['tracking_number'] ?? $order->tracking_number,
                    'carrier_name' => 'Kolixy',
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'BL créé avec succès.',
                    'tracking_number' => $result['tracking_number'] ?? null,
                ]);
            }

            return response()->json(['error' => $result['message'] ?? 'Erreur création BL'], 422);
        } catch (\Exception $e) {
            Log::error('[Emballage] BL creation failed: ' . $e->getMessage(), ['task_id' => $task->id]);
            return response()->json(['error' => 'Erreur de connexion Kolixy: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Print BL (HTML print page)
     */
    public function printBL(EmballageTask $task)
    {
        $user = $this->user();
        if ($task->assigned_to !== $user->id) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }
        if (!$task->tracking_number) {
            return response()->json(['error' => 'Pas de numéro de suivi'], 422);
        }

        $companyConfig = CompanyKolixyConfig::active();
        $companyName = $companyConfig->company_name ?? 'Confirmi';

        $task->load(['order.items.product', 'order.region', 'order.city', 'admin']);

        return view('confirmi.agent.print-bl', [
            'task' => $task,
            'companyName' => $companyName,
        ]);
    }

    /**
     * Bulk receive multiple tasks
     */
    public function bulkReceive(Request $request)
    {
        $user = $this->user();
        $validated = $request->validate(['task_ids' => 'required|array', 'task_ids.*' => 'integer']);

        $count = EmballageTask::where('assigned_to', $user->id)
            ->where('status', 'pending')
            ->whereIn('id', $validated['task_ids'])
            ->update(['status' => 'received', 'received_at' => now()]);

        return response()->json(['success' => true, 'message' => "$count tâche(s) marquée(s) comme reçue(s)."]);
    }
}
