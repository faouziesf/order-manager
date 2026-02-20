<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistiques principales
        $stats = $this->getMainStats();
        
        // Activité récente
        $recentActivity = $this->buildRecentActivity();
        
        // Alertes système
        $alerts = $this->getSystemAlerts();
        
        // Données pour les graphiques
        $chartData = $this->buildChartData();
        
        // Admins récents
        $recentAdmins = Admin::latest()->take(5)->get();
        
        // Performance du système
        $systemPerformance = $this->getSystemPerformance();
        
        return view('super-admin.dashboard', compact(
            'stats',
            'recentActivity',
            'alerts',
            'chartData',
            'recentAdmins',
            'systemPerformance'
        ));
    }

    public function getRealtimeStats()
    {
        return response()->json($this->getMainStats());
    }

    public function getChartData()
    {
        return response()->json($this->buildChartData());
    }

    private function buildChartData()
    {
        return [
            'adminsRegistration' => $this->getAdminsRegistrationChart(),
            'ordersActivity' => $this->getOrdersActivityChart(),
            'systemUsage' => $this->getSystemUsageChart(),
            'revenueChart' => $this->getRevenueChart()
        ];
    }

    public function getRecentActivity()
    {
        return response()->json($this->buildRecentActivity()->values());
    }

    private function buildRecentActivity()
    {
        $activities = collect();
        
        try {
            // Nouveaux admins
            $newAdmins = Admin::where('created_at', '>=', now()->subDays(7))
                ->latest()
                ->take(10)
                ->get()
                ->map(function ($admin) {
                    return [
                        'type' => 'admin_registered',
                        'message' => "Nouvel admin inscrit: {$admin->name} ({$admin->shop_name})",
                        'time' => $admin->created_at,
                        'icon' => 'fas fa-user-plus',
                        'color' => 'success'
                    ];
                });

            // Admins expirés
            $expiredAdmins = Admin::where('expiry_date', '<', now())
                ->where('is_active', true)
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($admin) {
                    return [
                        'type' => 'admin_expired',
                        'message' => "Admin expiré: {$admin->name} ({$admin->shop_name})",
                        'time' => $admin->expiry_date,
                        'icon' => 'fas fa-exclamation-triangle',
                        'color' => 'warning'
                    ];
                });

            // Fusionner et trier par date
            $activities = $activities->merge($newAdmins)
                ->merge($expiredAdmins)
                ->sortByDesc('time')
                ->take(15);
        } catch (\Exception $e) {
            // En cas d'erreur, retourner une collection vide
            $activities = collect();
        }

        return $activities;
    }

    public function getAlerts()
    {
        $alerts = collect();

        try {
            // Admins expirant bientôt
            $expiringAdmins = Admin::where('expiry_date', '<=', now()->addDays(7))
                ->where('expiry_date', '>=', now())
                ->where('is_active', true)
                ->count();

            if ($expiringAdmins > 0) {
                $alerts->push([
                    'type' => 'warning',
                    'message' => "{$expiringAdmins} admin(s) vont expirer dans les 7 prochains jours",
                    'action' => route('super-admin.admins.index', ['filter' => 'expiring'])
                ]);
            }

            // Admins inactifs depuis longtemps
            $inactiveAdmins = Admin::where('last_login_at', '<', now()->subDays(30))
                ->where('is_active', true)
                ->count();

            if ($inactiveAdmins > 0) {
                $alerts->push([
                    'type' => 'info',
                    'message' => "{$inactiveAdmins} admin(s) inactif(s) depuis plus de 30 jours",
                    'action' => route('super-admin.admins.index', ['filter' => 'inactive'])
                ]);
            }

            // Vérifier l'espace disque
            $diskUsage = $this->getDiskUsage();
            if ($diskUsage > 85) {
                $alerts->push([
                    'type' => 'danger',
                    'message' => "Espace disque faible: {$diskUsage}% utilisé",
                    'action' => route('super-admin.system.index')
                ]);
            }
        } catch (\Exception $e) {
            // En cas d'erreur, retourner une collection vide
        }

        return response()->json($alerts);
    }

    private function getMainStats()
    {
        return Cache::remember('super_admin_main_stats', 300, function () {
            try {
                $totalAdmins = Admin::count();
                $activeAdmins = Admin::where('is_active', true)->count();
                $inactiveAdmins = Admin::where('is_active', false)->count();
                $expiredAdmins = Admin::where('expiry_date', '<', now())->where('is_active', true)->count();
                
                // Nouveaux admins ce mois
                $newAdminsThisMonth = Admin::where('created_at', '>=', now()->startOfMonth())->count();
                $newAdminsLastMonth = Admin::where('created_at', '>=', now()->subMonth()->startOfMonth())
                    ->where('created_at', '<', now()->startOfMonth())
                    ->count();
                
                $adminGrowth = $newAdminsLastMonth > 0 
                    ? (($newAdminsThisMonth - $newAdminsLastMonth) / $newAdminsLastMonth) * 100 
                    : 0;

                // Statistiques des managers et employés
                $totalManagers = Admin::where('role', Admin::ROLE_MANAGER)->count();
                $totalEmployees = Admin::where('role', Admin::ROLE_EMPLOYEE)->count();
                
                // Activité des commandes
                $totalOrders = Admin::sum('total_orders') ?? 0;
                
                // Revenus (si applicable)
                $totalRevenue = Admin::sum('total_revenue') ?? 0;
                
                return [
                    'totalAdmins' => $totalAdmins,
                    'activeAdmins' => $activeAdmins,
                    'inactiveAdmins' => $inactiveAdmins,
                    'expiredAdmins' => $expiredAdmins,
                    'newAdminsThisMonth' => $newAdminsThisMonth,
                    'adminGrowth' => round($adminGrowth, 1),
                    'totalManagers' => $totalManagers,
                    'totalEmployees' => $totalEmployees,
                    'totalOrders' => $totalOrders,
                    'ordersThisMonth' => 0, // À implémenter si table orders existe
                    'totalRevenue' => $totalRevenue,
                    'averageOrdersPerAdmin' => $totalAdmins > 0 ? round($totalOrders / $totalAdmins, 1) : 0
                ];
            } catch (\Exception $e) {
                // Retourner des valeurs par défaut en cas d'erreur
                return [
                    'totalAdmins' => 0,
                    'activeAdmins' => 0,
                    'inactiveAdmins' => 0,
                    'expiredAdmins' => 0,
                    'newAdminsThisMonth' => 0,
                    'adminGrowth' => 0,
                    'totalManagers' => 0,
                    'totalEmployees' => 0,
                    'totalOrders' => 0,
                    'ordersThisMonth' => 0,
                    'totalRevenue' => 0,
                    'averageOrdersPerAdmin' => 0
                ];
            }
        });
    }

    private function getSystemAlerts()
    {
        $alerts = collect();

        try {
            // Admins expirant bientôt
            $expiringAdmins = Admin::where('expiry_date', '<=', now()->addDays(7))
                ->where('expiry_date', '>=', now())
                ->where('is_active', true)
                ->count();

            if ($expiringAdmins > 0) {
                $alerts->push([
                    'type' => 'warning',
                    'message' => "{$expiringAdmins} admin(s) vont expirer dans les 7 prochains jours"
                ]);
            }

            // Admins inactifs depuis longtemps
            $inactiveAdmins = Admin::where('last_login_at', '<', now()->subDays(30))
                ->where('is_active', true)
                ->count();

            if ($inactiveAdmins > 0) {
                $alerts->push([
                    'type' => 'info',
                    'message' => "{$inactiveAdmins} admin(s) inactif(s) depuis plus de 30 jours"
                ]);
            }

            // Vérifier l'espace disque
            $diskUsage = $this->getDiskUsage();
            if ($diskUsage > 85) {
                $alerts->push([
                    'type' => 'danger',
                    'message' => "Espace disque faible: {$diskUsage}% utilisé"
                ]);
            }
        } catch (\Exception $e) {
            // En cas d'erreur, ajouter une alerte générique
            $alerts->push([
                'type' => 'warning',
                'message' => 'Impossible de charger certaines alertes système'
            ]);
        }

        return $alerts;
    }

    private function getAdminsRegistrationChart()
    {
        try {
            $data = Admin::select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as count')
                )
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            return [
                'labels' => $data->pluck('date')->map(function ($date) {
                    return Carbon::parse($date)->format('d/m');
                }),
                'datasets' => [
                    [
                        'label' => 'Nouveaux Admins',
                        'data' => $data->pluck('count'),
                        'borderColor' => '#4e73df',
                        'backgroundColor' => 'rgba(78, 115, 223, 0.1)',
                        'fill' => true
                    ]
                ]
            ];
        } catch (\Exception $e) {
            return [
                'labels' => [],
                'datasets' => []
            ];
        }
    }

    private function getOrdersActivityChart()
    {
        // Simuler des données de commandes par jour
        $data = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $orders = rand(50, 200);
            
            $data->push([
                'date' => $date->format('d/m'),
                'orders' => $orders
            ]);
        }

        return [
            'labels' => $data->pluck('date'),
            'datasets' => [
                [
                    'label' => 'Commandes',
                    'data' => $data->pluck('orders'),
                    'borderColor' => '#1cc88a',
                    'backgroundColor' => 'rgba(28, 200, 138, 0.1)',
                    'fill' => true
                ]
            ]
        ];
    }

    private function getSystemUsageChart()
    {
        return [
            'labels' => ['CPU', 'Mémoire', 'Disque', 'Réseau'],
            'datasets' => [
                [
                    'label' => 'Utilisation (%)',
                    'data' => [
                        rand(30, 70), // CPU
                        rand(40, 80), // Mémoire
                        rand(20, 60), // Disque
                        rand(10, 40)  // Réseau
                    ],
                    'backgroundColor' => [
                        '#4e73df',
                        '#1cc88a',
                        '#f6c23e',
                        '#e74a3b'
                    ]
                ]
            ]
        ];
    }

    private function getRevenueChart()
    {
        $months = collect();
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $revenue = rand(5000, 15000);
            
            $months->push([
                'month' => $date->format('M Y'),
                'revenue' => $revenue
            ]);
        }

        return [
            'labels' => $months->pluck('month'),
            'datasets' => [
                [
                    'label' => 'Revenus (€)',
                    'data' => $months->pluck('revenue'),
                    'borderColor' => '#f6c23e',
                    'backgroundColor' => 'rgba(246, 194, 62, 0.1)',
                    'fill' => true
                ]
            ]
        ];
    }

    private function getSystemPerformance()
    {
        return [
            'uptime' => $this->getSystemUptime(),
            'cpu_usage' => rand(20, 70),
            'memory_usage' => rand(40, 80),
            'disk_usage' => $this->getDiskUsage(),
            'response_time' => rand(50, 200) . 'ms'
        ];
    }

    private function getSystemUptime()
    {
        // Simuler un uptime
        $days = rand(10, 100);
        $hours = rand(0, 23);
        $minutes = rand(0, 59);
        
        return "{$days}j {$hours}h {$minutes}m";
    }

    private function getDiskUsage()
    {
        return rand(30, 85);
    }

    private function safeCount($model)
    {
        try {
            return $model::count();
        } catch (\Exception $e) {
            return 0;
        }
    }
}