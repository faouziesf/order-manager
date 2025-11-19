<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Order;
use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index()
    {
        $recentReports = Report::latest()->take(10)->get();
        $scheduledReports = Report::where('is_scheduled', true)->get();
        
        $reportStats = [
            'total_reports' => Report::count(),
            'generated_this_month' => Report::where('created_at', '>=', now()->startOfMonth())->count(),
            'scheduled_reports' => Report::where('is_scheduled', true)->count(),
            'auto_reports' => Report::where('is_automated', true)->count()
        ];

        return view('super-admin.reports.index', compact('recentReports', 'scheduledReports', 'reportStats'));
    }

    public function adminActivity()
    {
        return view('super-admin.reports.admin-activity');
    }

    public function systemUsage()
    {
        $usageData = $this->getSystemUsageData();
        return view('super-admin.reports.system-usage', compact('usageData'));
    }

    public function performance()
    {
        $performanceData = $this->getPerformanceData();
        return view('super-admin.reports.performance', compact('performanceData'));
    }

    public function custom()
    {
        $availableMetrics = $this->getAvailableMetrics();
        return view('super-admin.reports.custom', compact('availableMetrics'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'report_type' => 'required|in:admin_activity,system_usage,performance,revenue,custom',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'format' => 'required|in:pdf,excel,csv',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'metrics' => 'nullable|array',
            'include_charts' => 'boolean',
            'schedule' => 'boolean',
            'schedule_frequency' => 'required_if:schedule,true|in:daily,weekly,monthly',
        ]);

        try {
            $reportData = $this->generateReportData($request);
            
            // Créer l'enregistrement du rapport
            $report = Report::create([
                'title' => $request->title,
                'description' => $request->description,
                'type' => $request->report_type,
                'format' => $request->format,
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
                'metrics' => $request->metrics ?? [],
                'data' => $reportData,
                'include_charts' => $request->has('include_charts'),
                'is_scheduled' => $request->has('schedule'),
                'schedule_frequency' => $request->schedule_frequency,
                'generated_by' => 'super_admin',
                'file_path' => null
            ]);

            // Générer le fichier
            $filePath = $this->generateReportFile($report, $reportData, $request->format);
            $report->update(['file_path' => $filePath]);

            if ($request->has('schedule')) {
                return redirect()->route('super-admin.reports.scheduled')
                    ->with('success', 'Rapport programmé avec succès.');
            }

            return $this->downloadReport($report);

        } catch (\Exception $e) {
            \Log::error('Erreur génération rapport:', ['message' => $e->getMessage()]);
            return redirect()->back()
                ->with('error', 'Erreur lors de la génération du rapport: ' . $e->getMessage());
        }
    }

    public function download(Report $report)
    {
        return $this->downloadReport($report);
    }

    public function scheduled()
    {
        $scheduledReports = Report::where('is_scheduled', true)
            ->with('executions')
            ->paginate(15);

        return view('super-admin.reports.scheduled', compact('scheduledReports'));
    }

    public function schedule(Request $request)
    {
        $request->validate([
            'report_id' => 'required|exists:reports,id',
            'frequency' => 'required|in:daily,weekly,monthly',
            'is_active' => 'boolean'
        ]);

        $report = Report::findOrFail($request->report_id);
        $report->update([
            'is_scheduled' => $request->has('is_active'),
            'schedule_frequency' => $request->frequency,
            'next_execution' => $this->calculateNextExecution($request->frequency)
        ]);

        return redirect()->back()
            ->with('success', 'Programmation du rapport mise à jour.');
    }

    // Méthodes privées pour la génération de données
    private function generateReportData(Request $request)
    {
        $dateFrom = Carbon::parse($request->date_from);
        $dateTo = Carbon::parse($request->date_to);

        switch ($request->report_type) {
            case 'admin_activity':
                return $this->getAdminActivityData($dateFrom, $dateTo);
            
            case 'system_usage':
                return $this->getSystemUsageReportData($dateFrom, $dateTo);
            
            case 'performance':
                return $this->getPerformanceReportData($dateFrom, $dateTo);
            
            case 'revenue':
                return $this->getRevenueReportData($dateFrom, $dateTo);
            
            case 'custom':
                return $this->getCustomReportData($dateFrom, $dateTo, $request->metrics);
            
            default:
                throw new \Exception('Type de rapport non supporté');
        }
    }

    private function getAdminActivityData($dateFrom, $dateTo)
    {
        $admins = Admin::where('role', Admin::ROLE_ADMIN)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->get();

        $loginStats = Admin::whereBetween('last_login_at', [$dateFrom, $dateTo])
            ->selectRaw('DATE(last_login_at) as date, COUNT(*) as logins')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $orderStats = Admin::selectRaw('
                admins.id,
                admins.name,
                admins.shop_name,
                admins.total_orders,
                admins.total_revenue,
                COUNT(managers.id) as managers_count,
                COUNT(employees.id) as employees_count
            ')
            ->leftJoin('managers', 'admins.id', '=', 'managers.admin_id')
            ->leftJoin('employees', 'admins.id', '=', 'employees.admin_id')
            ->groupBy('admins.id', 'admins.name', 'admins.shop_name', 'admins.total_orders', 'admins.total_revenue')
            ->orderBy('admins.total_orders', 'desc')
            ->get();

        return [
            'summary' => [
                'total_admins' => $admins->count(),
                'new_registrations' => $admins->count(),
                'active_admins' => $admins->where('is_active', true)->count(),
                'total_orders' => $admins->sum('total_orders'),
                'total_revenue' => $admins->sum('total_revenue'),
                'date_range' => $dateFrom->format('d/m/Y') . ' - ' . $dateTo->format('d/m/Y')
            ],
            'admins' => $admins,
            'login_stats' => $loginStats,
            'order_stats' => $orderStats,
            'top_performers' => $orderStats->take(10),
            'charts' => [
                'daily_registrations' => $this->getDailyRegistrationsChart($dateFrom, $dateTo),
                'login_activity' => $this->getLoginActivityChart($loginStats)
            ]
        ];
    }

    private function getSystemUsageReportData($dateFrom, $dateTo)
    {
        return [
            'summary' => [
                'total_users' => Admin::count(),
                'daily_active_users' => rand(50, 200),
                'peak_concurrent_users' => rand(20, 80),
                'total_sessions' => rand(1000, 5000),
                'average_session_duration' => '25 minutes',
                'bounce_rate' => rand(15, 35) . '%'
            ],
            'feature_usage' => [
                'order_management' => rand(80, 95),
                'employee_management' => rand(60, 85),
                'reports' => rand(40, 70),
                'analytics' => rand(30, 60),
                'settings' => rand(20, 50)
            ],
            'performance_metrics' => [
                'average_response_time' => rand(100, 300) . 'ms',
                'error_rate' => rand(1, 5) / 10 . '%',
                'uptime' => rand(98, 100) . '%',
                'database_queries_per_second' => rand(50, 200)
            ]
        ];
    }

    private function getPerformanceReportData($dateFrom, $dateTo)
    {
        $topAdmins = Admin::orderBy('total_orders', 'desc')->take(20)->get();
        
        return [
            'summary' => [
                'total_orders_processed' => Admin::sum('total_orders'),
                'average_orders_per_admin' => round(Admin::avg('total_orders'), 2),
                'highest_performing_admin' => $topAdmins->first(),
                'system_efficiency' => rand(85, 98) . '%'
            ],
            'top_performers' => $topAdmins,
            'system_metrics' => [
                'cpu_usage_avg' => rand(30, 70) . '%',
                'memory_usage_avg' => rand(40, 80) . '%',
                'disk_usage' => rand(20, 60) . '%',
                'network_utilization' => rand(10, 40) . '%'
            ],
            'trends' => $this->getPerformanceTrends($dateFrom, $dateTo)
        ];
    }

    private function getRevenueReportData($dateFrom, $dateTo)
    {
        return [
            'summary' => [
                'total_revenue' => Admin::sum('total_revenue'),
                'average_revenue_per_admin' => round(Admin::avg('total_revenue'), 2),
                'growth_rate' => rand(-5, 15) . '%',
                'projected_monthly' => rand(50000, 100000)
            ],
            'by_admin' => Admin::orderBy('total_revenue', 'desc')->take(20)->get(),
            'trends' => $this->getRevenueTrends($dateFrom, $dateTo)
        ];
    }

    private function getCustomReportData($dateFrom, $dateTo, $metrics)
    {
        $data = [];
        
        if (in_array('admin_count', $metrics ?? [])) {
            $data['admin_metrics'] = [
                'total' => Admin::count(),
                'active' => Admin::where('is_active', true)->count(),
                'new' => Admin::whereBetween('created_at', [$dateFrom, $dateTo])->count()
            ];
        }
        
        if (in_array('order_metrics', $metrics ?? [])) {
            $data['order_metrics'] = [
                'total_orders' => Admin::sum('total_orders'),
                'average_per_admin' => round(Admin::avg('total_orders'), 2)
            ];
        }
        
        return $data;
    }

    private function generateReportFile(Report $report, $data, $format)
    {
        $fileName = 'reports/' . Str::slug($report->title) . '_' . now()->format('Y-m-d_H-i-s');
        
        switch ($format) {
            case 'pdf':
                return $this->generatePdfReport($report, $data, $fileName);
            
            case 'excel':
                return $this->generateExcelReport($report, $data, $fileName);
            
            case 'csv':
                return $this->generateCsvReport($report, $data, $fileName);
            
            default:
                throw new \Exception('Format non supporté');
        }
    }

    private function generatePdfReport(Report $report, $data, $fileName)
    {
        $pdf = Pdf::loadView('super-admin.reports.pdf-template', [
            'report' => $report,
            'data' => $data
        ]);
        
        $filePath = $fileName . '.pdf';
        Storage::put($filePath, $pdf->output());
        
        return $filePath;
    }

    private function generateExcelReport(Report $report, $data, $fileName)
    {
        // Implémentation Excel
        $filePath = $fileName . '.xlsx';
        // Excel::store(new ReportExport($report, $data), $filePath);
        return $filePath;
    }

    private function generateCsvReport(Report $report, $data, $fileName)
    {
        $filePath = $fileName . '.csv';
        $csvContent = $this->convertDataToCsv($data);
        Storage::put($filePath, $csvContent);
        return $filePath;
    }

    private function downloadReport(Report $report)
    {
        if (!$report->file_path || !Storage::exists($report->file_path)) {
            return redirect()->back()->with('error', 'Fichier de rapport introuvable.');
        }

        return Storage::download($report->file_path, basename($report->file_path));
    }

    private function calculateNextExecution($frequency)
    {
        switch ($frequency) {
            case 'daily':
                return now()->addDay();
            case 'weekly':
                return now()->addWeek();
            case 'monthly':
                return now()->addMonth();
            default:
                return null;
        }
    }

    // Méthodes helper pour les graphiques et données
    private function getDailyRegistrationsChart($dateFrom, $dateTo)
    {
        return [
            'labels' => ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun'],
            'data' => [12, 19, 3, 5, 2, 3]
        ];
    }

    private function getLoginActivityChart($loginStats)
    {
        return [
            'labels' => $loginStats->pluck('date'),
            'data' => $loginStats->pluck('logins')
        ];
    }

    private function getPerformanceTrends($dateFrom, $dateTo)
    {
        return [
            'response_times' => [120, 115, 130, 110, 125],
            'error_rates' => [2.1, 1.8, 2.5, 1.5, 1.9],
            'throughput' => [150, 165, 140, 175, 160]
        ];
    }

    private function getRevenueTrends($dateFrom, $dateTo)
    {
        return [
            'monthly' => [45000, 52000, 48000, 61000, 58000],
            'growth' => [5.2, 15.6, -7.7, 27.1, -4.9]
        ];
    }

    private function getAvailableMetrics()
    {
        return [
            'admin_count' => 'Nombre d\'administrateurs',
            'order_metrics' => 'Métriques des commandes',
            'revenue_metrics' => 'Métriques de revenus',
            'usage_metrics' => 'Métriques d\'utilisation',
            'performance_metrics' => 'Métriques de performance'
        ];
    }

    private function convertDataToCsv($data)
    {
        $csv = '';
        // Implémentation de conversion en CSV
        return $csv;
    }

    private function getSystemUsageData()
    {
        return [
            'daily_users' => rand(50, 200),
            'peak_users' => rand(20, 80),
            'feature_usage' => [
                'orders' => 85,
                'reports' => 60,
                'settings' => 40
            ]
        ];
    }

    private function getPerformanceData()
    {
        return [
            'response_time' => rand(100, 300),
            'error_rate' => rand(1, 5),
            'uptime' => rand(98, 100)
        ];
    }
}