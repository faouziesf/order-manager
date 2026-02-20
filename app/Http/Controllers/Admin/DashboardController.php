<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Affiche le tableau de bord administrateur
     */
    public function index()
    {
        $admin = Auth::guard('admin')->user();

        // Pour les managers/employés, résoudre l'ID de l'admin parent
        $parentAdminId = $admin->isAdmin() ? $admin->id : $admin->created_by;

        // Statistiques principales
        $stats = [
            'total_orders' => $admin->orders()->count(),
            'orders_today' => $admin->orders()->whereDate('created_at', today())->count(),
            'orders_this_week' => $admin->orders()->whereBetween('created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])->count(),
            'orders_this_month' => $admin->orders()->whereMonth('created_at', Carbon::now()->month)->count(),

            'total_products' => $admin->products()->count(),
            'active_products' => $admin->products()->where('is_active', true)->count(),
            'low_stock_products' => $admin->products()->where('stock', '<=', 10)->where('stock', '>', 0)->count(),
            'out_of_stock_products' => $admin->products()->where('stock', '<=', 0)->count(),

            'total_managers' => Admin::where('role', Admin::ROLE_MANAGER)
                ->where('created_by', $parentAdminId)
                ->count(),
            'active_managers' => Admin::where('role', Admin::ROLE_MANAGER)
                ->where('created_by', $parentAdminId)
                ->where('is_active', true)
                ->count(),
            'total_employees' => Admin::where('role', Admin::ROLE_EMPLOYEE)
                ->where('created_by', $parentAdminId)
                ->count(),
            'active_employees' => Admin::where('role', Admin::ROLE_EMPLOYEE)
                ->where('created_by', $parentAdminId)
                ->where('is_active', true)
                ->count(),

            'products_to_review' => $admin->products()->where('needs_review', true)->count(),
            'unassigned_orders' => $admin->orders()->where('is_assigned', false)->whereIn('status', ['nouvelle', 'confirmée'])->count(),
        ];

        // Commandes par statut
        $ordersByStatus = $admin->orders()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Évolution des commandes sur les 7 derniers jours
        $ordersChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $ordersChart[] = [
                'date' => $date->format('Y-m-d'),
                'day' => $date->format('D'),
                'count' => $admin->orders()->whereDate('created_at', $date)->count()
            ];
        }

        // Commandes récentes
        $recentOrders = $admin->orders()
            ->with(['items.product'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Produits à faible stock
        $lowStockProducts = $admin->products()
            ->where('stock', '<=', 10)
            ->where('stock', '>', 0)
            ->orderBy('stock', 'asc')
            ->limit(10)
            ->get();

        // Employés les plus actifs (basé sur les commandes assignées)
        $activeEmployees = Admin::where('role', Admin::ROLE_EMPLOYEE)
            ->where('created_by', $parentAdminId)
            ->withCount(['orders' => function($query) {
                $query->whereDate('updated_at', '>=', Carbon::now()->subDays(7));
            }])
            ->orderBy('orders_count', 'desc')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'ordersByStatus',
            'ordersChart',
            'recentOrders',
            'lowStockProducts',
            'activeEmployees'
        ));
    }

    /**
     * API pour les statistiques en temps réel
     */
    public function getStats()
    {
        $admin = Auth::guard('admin')->user();

        $stats = [
            'orders_today' => $admin->orders()->whereDate('created_at', today())->count(),
            'new_orders' => $admin->orders()->where('status', 'nouvelle')->count(),
            'confirmed_orders' => $admin->orders()->where('status', 'confirmée')->count(),
            'products_to_review' => $admin->products()->where('needs_review', true)->count(),
            'low_stock_alerts' => $admin->products()->where('stock', '<=', 10)->where('stock', '>', 0)->count(),
            'out_of_stock_alerts' => $admin->products()->where('stock', '<=', 0)->count(),
            'unassigned_orders' => $admin->orders()->where('is_assigned', false)->whereIn('status', ['nouvelle', 'confirmée'])->count(),
        ];

        return response()->json([
            'stats' => $stats,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Données pour les graphiques du tableau de bord
     */
    public function getChartData(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        $type = $request->get('type', 'orders');
        $period = $request->get('period', '7d');

        switch ($period) {
            case '24h':
                $data = $this->getHourlyData($admin, $type);
                break;
            case '7d':
                $data = $this->getDailyData($admin, $type, 7);
                break;
            case '30d':
                $data = $this->getDailyData($admin, $type, 30);
                break;
            case '12m':
                $data = $this->getMonthlyData($admin, $type);
                break;
            default:
                $data = $this->getDailyData($admin, $type, 7);
        }

        return response()->json($data);
    }

    /**
     * Données horaires (dernières 24h)
     */
    private function getHourlyData($admin, $type)
    {
        $data = [];

        for ($i = 23; $i >= 0; $i--) {
            $hour = Carbon::now()->subHours($i);
            $startHour = $hour->copy()->startOfHour();
            $endHour = $hour->copy()->endOfHour();

            switch ($type) {
                case 'orders':
                    $count = $admin->orders()->whereBetween('created_at', [$startHour, $endHour])->count();
                    break;
                case 'products':
                    $count = $admin->products()->whereBetween('created_at', [$startHour, $endHour])->count();
                    break;
                default:
                    $count = 0;
            }

            $data[] = [
                'label' => $hour->format('H:i'),
                'value' => $count
            ];
        }

        return $data;
    }

    /**
     * Données quotidiennes
     */
    private function getDailyData($admin, $type, $days)
    {
        $data = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);

            switch ($type) {
                case 'orders':
                    $count = $admin->orders()->whereDate('created_at', $date)->count();
                    break;
                case 'products':
                    $count = $admin->products()->whereDate('created_at', $date)->count();
                    break;
                default:
                    $count = 0;
            }

            $data[] = [
                'label' => $date->format('d/m'),
                'value' => $count
            ];
        }

        return $data;
    }

    /**
     * Données mensuelles (derniers 12 mois)
     */
    private function getMonthlyData($admin, $type)
    {
        $data = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);

            switch ($type) {
                case 'orders':
                    $count = $admin->orders()->whereMonth('created_at', $month->month)
                                             ->whereYear('created_at', $month->year)
                                             ->count();
                    break;
                case 'products':
                    $count = $admin->products()->whereMonth('created_at', $month->month)
                                              ->whereYear('created_at', $month->year)
                                              ->count();
                    break;
                default:
                    $count = 0;
            }

            $data[] = [
                'label' => $month->format('M Y'),
                'value' => $count
            ];
        }

        return $data;
    }
}
