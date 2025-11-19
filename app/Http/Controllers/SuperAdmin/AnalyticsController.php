<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AnalyticsController extends Controller
{
    public function index()
    {
        $overviewStats = $this->getOverviewStats();
        $trendsData = $this->getTrendsData();
        
        return view('super-admin.analytics.index', compact('overviewStats', 'trendsData'));
    }

    public function performance()
    {
        $performanceMetrics = $this->getPerformanceMetrics();
        $topPerformingAdmins = $this->getTopPerformingAdmins();
        $systemMetrics = $this->getSystemMetrics();
        
        return view('super-admin.analytics.performance', compact(
            'performanceMetrics',
            'topPerformingAdmins',
            'systemMetrics'
        ));
    }

    public function usage()
    {
        $usageStats = $this->getUsageStats();
        $featureUsage = $this->getFeatureUsage();
        $peakHours = $this->getPeakUsageHours();
        
        return view('super-admin.analytics.usage', compact(
            'usageStats',
            'featureUsage',
            'peakHours'
        ));
    }

    public function trends()
    {
        $growthTrends = $this->getGrowthTrends();
        $retentionData = $this->getRetentionData();
        $seasonalData = $this->getSeasonalData();
        
        return view('super-admin.analytics.trends', compact(
            'growthTrends',
            'retentionData',
            'seasonalData'
        ));
    }

    public function geographic()
    {
        $geographicData = $this->getGeographicData();
        $countryStats = $this->getCountryStats();
        $cityStats = $this->getCityStats();
        
        return view('super-admin.analytics.geographic', compact(
            'geographicData',
            'countryStats',
            'cityStats'
        ));
    }

    // API Methods
    public function getPerformanceData(Request $request)
    {
        $period = $request->get('period', '30');
        $startDate = now()->subDays($period);
        
        $data = [
            'admins_performance' => $this->getAdminsPerformanceData($startDate),
            'orders_performance' => $this->getOrdersPerformanceData($startDate),
            'revenue_performance' => $this->getRevenuePerformanceData($startDate),
            'system_performance' => $this->getSystemPerformanceData($startDate)
        ];

        return response()->json($data);
    }

    public function getUsageData(Request $request)
    {
        $period = $request->get('period', '7');
        
        return response()->json([
            'daily_active_admins' => $this->getDailyActiveAdmins($period),
            'feature_usage' => $this->getFeatureUsageData($period),
            'session_duration' => $this->getSessionDurationData($period),
            'page_views' => $this->getPageViewsData($period)
        ]);
    }

    public function getTrendsData(Request $request)
    {
        $period = $request->get('period', '12');
        
        return response()->json([
            'user_growth' => $this->getUserGrowthTrend($period),
            'revenue_trend' => $this->getRevenueTrend($period),
            'retention_trend' => $this->getRetentionTrend($period),
            'churn_rate' => $this->getChurnRateTrend($period)
        ]);
    }

    public function getGeographicData(Request $request)
    {
        return response()->json([
            'countries' => $this->getCountryDistribution(),
            'cities' => $this->getCityDistribution(),
            'regions' => $this->getRegionDistribution()
        ]);
    }

    // Private helper methods
    private function getOverviewStats()
    {
        return Cache::remember('analytics_overview_stats', 3600, function () {
            $totalAdmins = Admin::count();
            $activeAdmins = Admin::where('is_active', true)->count();
            $totalRevenue = Admin::sum('total_revenue') ?? 0;
            $averageRevenue = $totalAdmins > 0 ? $totalRevenue / $totalAdmins : 0;
            
            return [
                'total_admins' => $totalAdmins,
                'active_admins' => $activeAdmins,
                'total_revenue' => $totalRevenue,
                'average_revenue_per_admin' => round($averageRevenue, 2),
                'growth_rate' => $this->calculateGrowthRate(),
                'retention_rate' => $this->calculateRetentionRate(),
                'churn_rate' => $this->calculateChurnRate()
            ];
        });
    }

    private function getPerformanceMetrics()
    {
        return [
            'top_admins_by_orders' => Admin::orderBy('total_orders', 'desc')->take(10)->get(),
            'top_admins_by_revenue' => Admin::orderBy('total_revenue', 'desc')->take(10)->get(),
            'most_active_admins' => Admin::orderBy('last_login_at', 'desc')->take(10)->get(),
            'average_session_time' => $this->getAverageSessionTime(),
            'orders_per_day' => $this->getOrdersPerDay(),
            'revenue_per_day' => $this->getRevenuePerDay()
        ];
    }

    private function getTopPerformingAdmins()
    {
        return Admin::select([
                'id', 'name', 'shop_name', 'total_orders', 
                'total_revenue', 'total_active_hours', 'last_login_at'
            ])
            ->where('is_active', true)
            ->orderBy('total_orders', 'desc')
            ->take(20)
            ->get()
            ->map(function ($admin) {
                return [
                    'id' => $admin->id,
                    'name' => $admin->name,
                    'shop_name' => $admin->shop_name,
                    'total_orders' => $admin->total_orders,
                    'total_revenue' => $admin->total_revenue,
                    'active_hours' => $admin->total_active_hours,
                    'last_login' => $admin->last_login_at?->diffForHumans(),
                    'performance_score' => $this->calculatePerformanceScore($admin)
                ];
            });
    }

    private function getSystemMetrics()
    {
        return [
            'response_times' => $this->getResponseTimes(),
            'error_rates' => $this->getErrorRates(),
            'database_performance' => $this->getDatabasePerformance(),
            'server_load' => $this->getServerLoad()
        ];
    }

    private function getUsageStats()
    {
        return [
            'daily_active_users' => $this->getDailyActiveUsers(),
            'weekly_active_users' => $this->getWeeklyActiveUsers(),
            'monthly_active_users' => $this->getMonthlyActiveUsers(),
            'session_duration' => $this->getAverageSessionDuration(),
            'pages_per_session' => $this->getPagesPerSession(),
            'bounce_rate' => $this->getBounceRate()
        ];
    }

    private function getFeatureUsage()
    {
        return [
            'order_management' => rand(70, 95),
            'employee_management' => rand(60, 85),
            'reports' => rand(40, 70),
            'analytics' => rand(30, 60),
            'settings' => rand(20, 50)
        ];
    }

    private function getPeakUsageHours()
    {
        $hours = [];
        for ($i = 0; $i < 24; $i++) {
            $hours[] = [
                'hour' => str_pad($i, 2, '0', STR_PAD_LEFT) . ':00',
                'users' => rand(5, 50)
            ];
        }
        return $hours;
    }

    private function getGrowthTrends()
    {
        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = [
                'month' => $date->format('M Y'),
                'new_admins' => rand(10, 50),
                'total_admins' => rand(100, 500),
                'growth_rate' => rand(-5, 15)
            ];
        }
        return $months;
    }

    private function getRetentionData()
    {
        return [
            '1_month' => 85,
            '3_months' => 70,
            '6_months' => 60,
            '12_months' => 45
        ];
    }

    private function getSeasonalData()
    {
        return [
            'Q1' => ['orders' => 1200, 'revenue' => 45000],
            'Q2' => ['orders' => 1500, 'revenue' => 52000],
            'Q3' => ['orders' => 1800, 'revenue' => 61000],
            'Q4' => ['orders' => 2200, 'revenue' => 78000]
        ];
    }

    private function getCountryStats()
    {
        return [
            ['country' => 'France', 'admins' => 45, 'percentage' => 35],
            ['country' => 'Belgique', 'admins' => 25, 'percentage' => 20],
            ['country' => 'Suisse', 'admins' => 20, 'percentage' => 15],
            ['country' => 'Canada', 'admins' => 15, 'percentage' => 12],
            ['country' => 'Autres', 'admins' => 23, 'percentage' => 18]
        ];
    }

    private function getCityStats()
    {
        return [
            ['city' => 'Paris', 'admins' => 25],
            ['city' => 'Lyon', 'admins' => 12],
            ['city' => 'Marseille', 'admins' => 8],
            ['city' => 'Bruxelles', 'admins' => 15],
            ['city' => 'Genève', 'admins' => 10]
        ];
    }

    // Helper calculation methods
    private function calculateGrowthRate()
    {
        $thisMonth = Admin::where('created_at', '>=', now()->startOfMonth())->count();
        $lastMonth = Admin::where('created_at', '>=', now()->subMonth()->startOfMonth())
            ->where('created_at', '<', now()->startOfMonth())
            ->count();
        
        return $lastMonth > 0 ? round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1) : 0;
    }

    private function calculateRetentionRate()
    {
        // Simuler un taux de rétention
        return rand(75, 90);
    }

    private function calculateChurnRate()
    {
        // Calculer le taux de désabonnement
        $totalAdmins = Admin::count();
        $inactiveAdmins = Admin::where('is_active', false)->count();
        
        return $totalAdmins > 0 ? round(($inactiveAdmins / $totalAdmins) * 100, 1) : 0;
    }

    private function calculatePerformanceScore($admin)
    {
        $score = 0;
        $score += min($admin->total_orders / 10, 50); // Max 50 points pour les commandes
        $score += min($admin->total_revenue / 1000, 30); // Max 30 points pour le revenus
        $score += min($admin->total_active_hours / 100, 20); // Max 20 points pour les heures
        
        return round($score, 1);
    }

    // Méthodes simulées (à remplacer par de vraies données)
    private function getAverageSessionTime() { return '45 minutes'; }
    private function getOrdersPerDay() { return rand(50, 200); }
    private function getRevenuePerDay() { return rand(2000, 8000); }
    private function getResponseTimes() { return ['avg' => '120ms', 'p95' => '200ms']; }
    private function getErrorRates() { return 0.5; }
    private function getDatabasePerformance() { return ['queries_per_second' => 150]; }
    private function getServerLoad() { return ['cpu' => 45, 'memory' => 67]; }
    private function getDailyActiveUsers() { return rand(50, 150); }
    private function getWeeklyActiveUsers() { return rand(200, 400); }
    private function getMonthlyActiveUsers() { return rand(500, 800); }
    private function getAverageSessionDuration() { return '32 minutes'; }
    private function getPagesPerSession() { return 8.5; }
    private function getBounceRate() { return 25; }
}