<?php

namespace App\Http\Controllers\Confirmi;

use App\Http\Controllers\Controller;
use App\Models\ConfirmiBilling;
use App\Models\ConfirmiOrderAssignment;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeOrderController extends Controller
{
    private function user()
    {
        return Auth::guard('confirmi')->user();
    }

    // ───── Dashboard (rich stats) ─────

    public function dashboard()
    {
        $user = $this->user();

        $activeAssignments = ConfirmiOrderAssignment::where('assigned_to', $user->id)
            ->whereIn('status', ['assigned', 'in_progress'])
            ->with(['order', 'admin'])
            ->latest('assigned_at')
            ->get();

        $completedToday = ConfirmiOrderAssignment::where('assigned_to', $user->id)
            ->whereIn('status', ['confirmed', 'delivered', 'cancelled'])
            ->whereDate('completed_at', today())
            ->get();

        $totalCompleted = ConfirmiOrderAssignment::where('assigned_to', $user->id)
            ->whereIn('status', ['confirmed', 'delivered'])
            ->count();

        $totalCancelled = ConfirmiOrderAssignment::where('assigned_to', $user->id)
            ->where('status', 'cancelled')
            ->count();

        $totalAttempts = ConfirmiOrderAssignment::where('assigned_to', $user->id)
            ->sum('attempts');

        // Taux de succès
        $finished = $totalCompleted + $totalCancelled;
        $successRate = $finished > 0 ? round(($totalCompleted / $finished) * 100, 1) : 0;

        // Performance 7 jours
        $weeklyStats = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dayCompleted = ConfirmiOrderAssignment::where('assigned_to', $user->id)
                ->whereIn('status', ['confirmed', 'delivered'])
                ->whereDate('completed_at', $date)
                ->count();
            $dayCancelled = ConfirmiOrderAssignment::where('assigned_to', $user->id)
                ->where('status', 'cancelled')
                ->whereDate('completed_at', $date)
                ->count();
            $weeklyStats[] = [
                'label' => $date->translatedFormat('D'),
                'date'  => $date->format('d/m'),
                'confirmed' => $dayCompleted,
                'cancelled' => $dayCancelled,
            ];
        }

        $stats = [
            'pending'          => $activeAssignments->where('status', 'assigned')->count(),
            'in_progress'      => $activeAssignments->where('status', 'in_progress')->count(),
            'completed_today'  => $completedToday->whereIn('status', ['confirmed', 'delivered'])->count(),
            'cancelled_today'  => $completedToday->where('status', 'cancelled')->count(),
            'total_completed'  => $totalCompleted,
            'total_cancelled'  => $totalCancelled,
            'total_attempts'   => $totalAttempts,
            'success_rate'     => $successRate,
            'scheduled_callbacks' => $activeAssignments->where('last_result', 'callback')->count(),
        ];

        // Daily target set by the commercial (default 20)
        $dailyTarget = 20;

        return view('confirmi.employee.dashboard', compact('stats', 'activeAssignments', 'weeklyStats', 'dailyTarget'));
    }

    // ───── Orders index ─────

    public function index(Request $request)
    {
        $user = $this->user();

        $query = ConfirmiOrderAssignment::where('assigned_to', $user->id)
            ->with(['order', 'admin']);

        $tab = $request->get('tab', 'active');

        if ($tab === 'confirmed') {
            $query->where('status', 'confirmed');
        } elseif ($tab === 'cancelled') {
            $query->where('status', 'cancelled');
        } elseif ($tab === 'all') {
            // no filter
        } else {
            $query->whereIn('status', ['assigned', 'in_progress']);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('order', function ($q) use ($search) {
                $q->where('customer_name', 'LIKE', "%{$search}%")
                  ->orWhere('customer_phone', 'LIKE', "%{$search}%")
                  ->orWhere('id', $search);
            });
        }

        $assignments = $query->latest('assigned_at')->paginate(20)->withQueryString();

        // Tab counts
        $counts = [
            'active'    => ConfirmiOrderAssignment::where('assigned_to', $user->id)->whereIn('status', ['assigned', 'in_progress'])->count(),
            'confirmed' => ConfirmiOrderAssignment::where('assigned_to', $user->id)->where('status', 'confirmed')->count(),
            'cancelled' => ConfirmiOrderAssignment::where('assigned_to', $user->id)->where('status', 'cancelled')->count(),
        ];

        return view('confirmi.employee.orders.index', compact('assignments', 'tab', 'counts'));
    }

    // ───── Show order detail ─────

    public function show(ConfirmiOrderAssignment $assignment)
    {
        $user = $this->user();

        if ($assignment->assigned_to !== $user->id) {
            abort(403);
        }

        $assignment->load(['order.items.product', 'admin']);

        return view('confirmi.employee.orders.show', compact('assignment'));
    }

    // ───── Start processing ─────

    public function startProcessing(ConfirmiOrderAssignment $assignment)
    {
        $user = $this->user();

        if ($assignment->assigned_to !== $user->id || $assignment->status !== 'assigned') {
            return back()->with('error', 'Action non autorisée.');
        }

        $assignment->update([
            'status' => 'in_progress',
            'first_attempt_at' => $assignment->first_attempt_at ?? now(),
        ]);

        return back()->with('success', 'Traitement démarré.');
    }

    // ───── Record attempt ─────

    public function recordAttempt(Request $request, ConfirmiOrderAssignment $assignment)
    {
        $user = $this->user();

        if ($assignment->assigned_to !== $user->id || !$assignment->canBeManaged()) {
            return back()->with('error', 'Action non autorisée.');
        }

        $request->validate([
            'result' => 'required|in:confirmed,no_answer,callback,cancelled',
            'notes' => 'nullable|string|max:500',
        ]);

        $assignment->increment('attempts');
        $assignment->update([
            'last_attempt_at' => now(),
            'last_result' => $request->result,
            'notes' => $request->notes,
        ]);

        if ($assignment->status === 'assigned') {
            $assignment->update(['status' => 'in_progress', 'first_attempt_at' => now()]);
        }

        $result = $request->result;

        if ($result === 'confirmed') {
            $assignment->update([
                'status' => 'confirmed',
                'completed_at' => now(),
            ]);

            if ($assignment->order) {
                $assignment->order->update(['status' => 'confirmée']);
            }

            $admin = $assignment->admin;
            if ($admin && $admin->confirmi_rate_confirmed > 0) {
                ConfirmiBilling::create([
                    'admin_id' => $admin->id,
                    'order_id' => $assignment->order_id,
                    'billing_type' => 'confirmed',
                    'amount' => $admin->confirmi_rate_confirmed,
                    'billed_at' => now(),
                ]);
            }

            return redirect()->route('confirmi.employee.orders.show', $assignment)
                    ->with('success', '✅ Commande confirmée !');
        }

        if ($result === 'cancelled') {
            $assignment->update([
                'status' => 'cancelled',
                'completed_at' => now(),
            ]);

            if ($assignment->order) {
                $assignment->order->update(['status' => 'annulée']);
            }

            return redirect()->route('confirmi.employee.orders.show', $assignment)
                ->with('success', 'Commande annulée.');
        }

        // no_answer / callback → stay on same order
        return back()->with('success', 'Tentative enregistrée (' . $assignment->attempts . ' tentative(s))');
    }

    // ───── History ─────

    public function history(Request $request)
    {
        $user = $this->user();

        $query = ConfirmiOrderAssignment::where('assigned_to', $user->id)
            ->whereIn('status', ['confirmed', 'delivered', 'cancelled'])
            ->with(['order', 'admin']);

        if ($request->filled('date')) {
            $query->whereDate('completed_at', $request->date);
        }

        if ($request->filled('result')) {
            $query->where('status', $request->result);
        }

        $assignments = $query->latest('completed_at')->paginate(25)->withQueryString();

        // Summary stats
        $todayConfirmed = ConfirmiOrderAssignment::where('assigned_to', $user->id)
            ->where('status', 'confirmed')
            ->whereDate('completed_at', today())
            ->count();
        $todayCancelled = ConfirmiOrderAssignment::where('assigned_to', $user->id)
            ->where('status', 'cancelled')
            ->whereDate('completed_at', today())
            ->count();

        return view('confirmi.employee.orders.history', compact('assignments', 'todayConfirmed', 'todayCancelled'));
    }

    // ───── Profile & Performance ─────

    public function profile()
    {
        $user = $this->user();

        // Global stats
        $totalConfirmed = ConfirmiOrderAssignment::where('assigned_to', $user->id)
            ->whereIn('status', ['confirmed', 'delivered'])->count();
        $totalCancelled = ConfirmiOrderAssignment::where('assigned_to', $user->id)
            ->where('status', 'cancelled')->count();
        $totalAttempts  = ConfirmiOrderAssignment::where('assigned_to', $user->id)->sum('attempts');
        $finished       = $totalConfirmed + $totalCancelled;
        $successRate    = $finished > 0 ? round(($totalConfirmed / $finished) * 100, 1) : 0;

        // Today stats
        $todayConfirmed = ConfirmiOrderAssignment::where('assigned_to', $user->id)
            ->whereIn('status', ['confirmed', 'delivered'])
            ->whereDate('completed_at', today())->count();
        $todayCancelled = ConfirmiOrderAssignment::where('assigned_to', $user->id)
            ->where('status', 'cancelled')
            ->whereDate('completed_at', today())->count();
        $todayAttempts  = ConfirmiOrderAssignment::where('assigned_to', $user->id)
            ->whereDate('last_attempt_at', today())->sum('attempts');

        // Monthly stats (last 30 days)
        $monthlyConfirmed = ConfirmiOrderAssignment::where('assigned_to', $user->id)
            ->whereIn('status', ['confirmed', 'delivered'])
            ->where('completed_at', '>=', now()->subDays(30))->count();
        $monthlyCancelled = ConfirmiOrderAssignment::where('assigned_to', $user->id)
            ->where('status', 'cancelled')
            ->where('completed_at', '>=', now()->subDays(30))->count();

        // Performance 30 jours (journalier)
        $dailyStats = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dayConfirmed = ConfirmiOrderAssignment::where('assigned_to', $user->id)
                ->whereIn('status', ['confirmed', 'delivered'])
                ->whereDate('completed_at', $date)->count();
            $dayCancelled = ConfirmiOrderAssignment::where('assigned_to', $user->id)
                ->where('status', 'cancelled')
                ->whereDate('completed_at', $date)->count();
            $dailyStats[] = [
                'label' => $date->format('d/m'),
                'confirmed' => $dayConfirmed,
                'cancelled' => $dayCancelled,
            ];
        }

        // Dernières commandes traitées (journal)
        $recentActivity = ConfirmiOrderAssignment::where('assigned_to', $user->id)
            ->whereIn('status', ['confirmed', 'delivered', 'cancelled'])
            ->with(['order', 'admin'])
            ->latest('completed_at')
            ->take(20)
            ->get();

        // Moyennes journalières (sur les 7 derniers jours actifs)
        $avgDaily = $finished > 0
            ? round(ConfirmiOrderAssignment::where('assigned_to', $user->id)
                ->whereIn('status', ['confirmed', 'delivered', 'cancelled'])
                ->where('completed_at', '>=', now()->subDays(7))
                ->count() / 7, 1)
            : 0;

        return view('confirmi.employee.profile', compact(
            'user', 'totalConfirmed', 'totalCancelled', 'totalAttempts', 'successRate',
            'todayConfirmed', 'todayCancelled', 'todayAttempts',
            'monthlyConfirmed', 'monthlyCancelled', 'dailyStats', 'recentActivity', 'avgDaily'
        ));
    }

    // ───── Search unassigned orders (read-only) ─────

    public function search(Request $request)
    {
        $query = ConfirmiOrderAssignment::where('status', 'pending')
            ->whereNull('assigned_to')
            ->with(['order.items.product', 'admin']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('order', function ($oq) use ($search) {
                    $oq->where('customer_name', 'LIKE', "%{$search}%")
                       ->orWhere('customer_phone', 'LIKE', "%{$search}%")
                       ->orWhere('id', $search);
                })->orWhereHas('admin', function ($aq) use ($search) {
                    $aq->where('name', 'LIKE', "%{$search}%")
                       ->orWhere('shop_name', 'LIKE', "%{$search}%");
                });
            });
        }

        if ($request->filled('admin_id')) {
            $query->where('admin_id', $request->admin_id);
        }

        $assignments = $query->latest('created_at')->paginate(20)->withQueryString();

        // Admin list for filter
        $admins = \App\Models\Admin::where('confirmi_status', 'active')->orderBy('shop_name')->get(['id', 'name', 'shop_name']);

        return view('confirmi.employee.orders.search', compact('assignments', 'admins'));
    }

    // ───── Products browsing (from admins) ─────

    public function products(Request $request)
    {
        // Get admin IDs from orders assigned to this employee
        $user = $this->user();
        $adminIds = ConfirmiOrderAssignment::where('assigned_to', $user->id)
            ->whereIn('status', ['assigned', 'in_progress'])
            ->distinct()
            ->pluck('admin_id');

        $admins = \App\Models\Admin::whereIn('id', $adminIds)->orderBy('shop_name')->get(['id', 'name', 'shop_name']);

        // Active assignments for the "add to order" dropdown
        $activeAssignments = ConfirmiOrderAssignment::where('assigned_to', $user->id)
            ->whereIn('status', ['assigned', 'in_progress'])
            ->with('order')
            ->latest('assigned_at')
            ->get();

        return view('confirmi.employee.products', compact('admins', 'activeAssignments'));
    }

    public function productsApi(Request $request)
    {
        $user = $this->user();

        $adminIds = ConfirmiOrderAssignment::where('assigned_to', $user->id)
            ->whereIn('status', ['assigned', 'in_progress'])
            ->distinct()
            ->pluck('admin_id');

        $query = Product::whereIn('admin_id', $adminIds)->where('is_active', true);

        if ($request->filled('admin_id')) {
            $query->where('admin_id', $request->admin_id);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($pq) use ($q) {
                $pq->where('name', 'LIKE', "%{$q}%")
                   ->orWhere('reference', 'LIKE', "%{$q}%");
            });
        }

        $products = $query->orderBy('name')->limit(50)->get(['id', 'admin_id', 'name', 'reference', 'price', 'stock', 'image']);

        return response()->json($products);
    }

    // ───── Add item to assigned order ─────

    public function addItem(Request $request, ConfirmiOrderAssignment $assignment)
    {
        $user = $this->user();

        if ($assignment->assigned_to !== $user->id || !$assignment->canBeManaged()) {
            return back()->with('error', 'Action non autorisée.');
        }

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);

        // Check product belongs to the assignment's admin
        if ($product->admin_id !== $assignment->admin_id) {
            return back()->with('error', 'Ce produit n\'appartient pas au client de cette commande.');
        }

        $order = $assignment->order;

        // Check if item already exists → increment quantity
        $existing = OrderItem::where('order_id', $order->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existing) {
            $existing->update([
                'quantity' => $existing->quantity + $request->quantity,
                'total_price' => ($existing->quantity + $request->quantity) * $existing->unit_price,
            ]);
        } else {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'unit_price' => $product->price,
                'total_price' => $product->price * $request->quantity,
            ]);
        }

        // Recalculate order total
        $order->total_price = $order->items()->sum('total_price');
        $order->save();

        return back()->with('success', 'Produit ajouté à la commande #' . $order->id);
    }

    // ───── Remove item from assigned order ─────

    public function removeItem(ConfirmiOrderAssignment $assignment, OrderItem $item)
    {
        $user = $this->user();

        if ($assignment->assigned_to !== $user->id || !$assignment->canBeManaged()) {
            return back()->with('error', 'Action non autorisée.');
        }

        if ($item->order_id !== $assignment->order_id) {
            return back()->with('error', 'Article non lié à cette commande.');
        }

        $item->delete();

        // Recalculate order total
        $order = $assignment->order;
        $order->total_price = $order->items()->sum('total_price');
        $order->save();

        return back()->with('success', 'Article supprimé de la commande.');
    }
}
