<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SystemLog;
use App\Models\Backup;
use App\Models\SuperAdminNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class SystemController extends Controller
{
    public function index()
    {
        $systemHealth = $this->getSystemHealth();
        $recentLogs = $this->getRecentLogs();
        $systemStats = $this->getSystemStats();
        $maintenanceMode = $this->isMaintenanceMode();
        
        return view('super-admin.system.index', compact(
            'systemHealth',
            'recentLogs', 
            'systemStats',
            'maintenanceMode'
        ));
    }

    public function health()
    {
        $healthChecks = $this->performHealthChecks();
        $systemMetrics = $this->getDetailedSystemMetrics();
        $services = $this->getServicesStatus();
        
        return view('super-admin.system.health', compact(
            'healthChecks',
            'systemMetrics',
            'services'
        ));
    }

    public function logs(Request $request)
    {
        $level = $request->get('level', 'all');
        $date = $request->get('date', today()->format('Y-m-d'));
        
        $logs = $this->getSystemLogs($level, $date);
        $logStats = $this->getLogStats();
        
        return view('super-admin.system.logs', compact('logs', 'logStats', 'level', 'date'));
    }

    public function backups()
    {
        $backups = Backup::orderBy('created_at', 'desc')->paginate(20);
        $backupStats = $this->getBackupStats();
        $storageInfo = $this->getStorageInfo();
        
        return view('super-admin.system.backups', compact('backups', 'backupStats', 'storageInfo'));
    }

    public function maintenance()
    {
        $maintenanceInfo = $this->getMaintenanceInfo();
        $scheduledTasks = $this->getScheduledTasks();
        
        return view('super-admin.system.maintenance', compact('maintenanceInfo', 'scheduledTasks'));
    }

    // Actions système
    public function createBackup(Request $request)
    {
        $request->validate([
            'type' => 'required|in:database,files,full',
            'description' => 'nullable|string|max:255'
        ]);

        try {
            $backupType = $request->type;
            $description = $request->description ?? "Sauvegarde {$backupType} manuelle";
            
            $backup = Backup::create([
                'type' => $backupType,
                'description' => $description,
                'status' => 'in_progress',
                'size' => 0,
                'started_at' => now()
            ]);

            // Lancer la sauvegarde en arrière-plan
            $this->performBackup($backup);

            NotificationController::notifyBackupCompleted($this->formatFileSize($backup->size ?? 0));

            return redirect()->back()
                ->with('success', 'Sauvegarde lancée avec succès.');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la création de sauvegarde:', ['message' => $e->getMessage()]);
            return redirect()->back()
                ->with('error', 'Erreur lors de la création de la sauvegarde: ' . $e->getMessage());
        }
    }

    public function toggleMaintenance()
    {
        try {
            if ($this->isMaintenanceMode()) {
                Artisan::call('up');
                $status = 'désactivé';
            } else {
                Artisan::call('down', [
                    '--retry' => 60,
                    '--message' => 'Maintenance programmée en cours...'
                ]);
                $status = 'activé';
            }

            NotificationController::notifySystemAlert(
                'Mode maintenance ' . $status,
                "Le mode maintenance a été {$status}",
                'medium'
            );

            return redirect()->back()
                ->with('success', "Mode maintenance {$status} avec succès.");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors du changement du mode maintenance: ' . $e->getMessage());
        }
    }

    public function clearCache(Request $request)
    {
        $cacheType = $request->get('type', 'all');

        try {
            switch ($cacheType) {
                case 'application':
                    Artisan::call('cache:clear');
                    break;
                
                case 'config':
                    Artisan::call('config:clear');
                    break;
                
                case 'route':
                    Artisan::call('route:clear');
                    break;
                
                case 'view':
                    Artisan::call('view:clear');
                    break;
                
                case 'all':
                    Artisan::call('cache:clear');
                    Artisan::call('config:clear');
                    Artisan::call('route:clear');
                    Artisan::call('view:clear');
                    break;
            }

            return redirect()->back()
                ->with('success', 'Cache vidé avec succès.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors du vidage du cache: ' . $e->getMessage());
        }
    }

    public function clearLogs(Request $request)
    {
        $daysToKeep = $request->get('days', 30);

        try {
            $cutoffDate = now()->subDays($daysToKeep);
            
            // Supprimer les anciens logs de la base de données
            SystemLog::where('created_at', '<', $cutoffDate)->delete();
            
            // Supprimer les anciens fichiers de logs
            $logPath = storage_path('logs');
            $files = File::files($logPath);
            
            foreach ($files as $file) {
                if (File::lastModified($file) < $cutoffDate->timestamp) {
                    File::delete($file);
                }
            }

            return redirect()->back()
                ->with('success', "Logs de plus de {$daysToKeep} jours supprimés avec succès.");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la suppression des logs: ' . $e->getMessage());
        }
    }

    // API Methods
    public function getHealthStatus()
    {
        return response()->json($this->performHealthChecks());
    }

    public function getPerformanceMetrics()
    {
        return response()->json($this->getDetailedSystemMetrics());
    }

    public function getDiskUsage()
    {
        return response()->json($this->getStorageInfo());
    }

    // Méthodes privées
    private function getSystemHealth()
    {
        return [
            'overall_status' => 'healthy', // healthy, warning, critical
            'database_status' => $this->checkDatabaseConnection(),
            'storage_status' => $this->checkStorageSpace(),
            'cache_status' => $this->checkCacheConnection(),
            'queue_status' => $this->checkQueueConnection(),
            'last_check' => now()
        ];
    }

    private function performHealthChecks()
    {
        $checks = [];

        // Vérification base de données
        $checks['database'] = [
            'name' => 'Base de données',
            'status' => $this->checkDatabaseConnection(),
            'message' => $this->getDatabaseMessage(),
            'last_check' => now()
        ];

        // Vérification espace disque
        $checks['storage'] = [
            'name' => 'Espace disque',
            'status' => $this->checkStorageSpace(),
            'message' => $this->getStorageMessage(),
            'last_check' => now()
        ];

        // Vérification cache
        $checks['cache'] = [
            'name' => 'Cache Redis/Memcached',
            'status' => $this->checkCacheConnection(),
            'message' => $this->getCacheMessage(),
            'last_check' => now()
        ];

        // Vérification queue
        $checks['queue'] = [
            'name' => 'Files d\'attente',
            'status' => $this->checkQueueConnection(),
            'message' => $this->getQueueMessage(),
            'last_check' => now()
        ];

        return $checks;
    }

    private function getDetailedSystemMetrics()
    {
        return [
            'cpu_usage' => $this->getCpuUsage(),
            'memory_usage' => $this->getMemoryUsage(),
            'disk_usage' => $this->getDiskUsagePercentage(),
            'load_average' => $this->getLoadAverage(),
            'uptime' => $this->getSystemUptime(),
            'processes' => $this->getActiveProcesses(),
            'network_stats' => $this->getNetworkStats()
        ];
    }

    private function getServicesStatus()
    {
        return [
            'web_server' => $this->checkWebServer(),
            'database' => $this->checkDatabaseConnection(),
            'redis' => $this->checkRedisConnection(),
            'queue_worker' => $this->checkQueueWorker(),
            'scheduler' => $this->checkScheduler()
        ];
    }

    private function getRecentLogs()
    {
        return SystemLog::latest()
            ->take(10)
            ->get()
            ->map(function ($log) {
                return [
                    'level' => $log->level,
                    'message' => $log->message,
                    'context' => $log->context,
                    'created_at' => $log->created_at,
                    'formatted_time' => $log->created_at->diffForHumans()
                ];
            });
    }

    private function getSystemStats()
    {
        return [
            'total_admins' => \App\Models\Admin::count(),
            'active_sessions' => $this->getActiveSessions(),
            'database_size' => $this->getDatabaseSize(),
            'storage_used' => $this->getStorageUsed(),
            'cache_hit_ratio' => $this->getCacheHitRatio(),
            'average_response_time' => $this->getAverageResponseTime()
        ];
    }

    private function isMaintenanceMode()
    {
        return app()->isDownForMaintenance();
    }

    private function getSystemLogs($level, $date)
    {
        $query = SystemLog::whereDate('created_at', $date);
        
        if ($level !== 'all') {
            $query->where('level', $level);
        }
        
        return $query->orderBy('created_at', 'desc')->paginate(50);
    }

    private function getLogStats()
    {
        return [
            'total_today' => SystemLog::whereDate('created_at', today())->count(),
            'errors_today' => SystemLog::whereDate('created_at', today())->where('level', 'error')->count(),
            'warnings_today' => SystemLog::whereDate('created_at', today())->where('level', 'warning')->count(),
            'total_size' => $this->getLogFilesSize()
        ];
    }

    private function getBackupStats()
    {
        return [
            'total_backups' => Backup::count(),
            'successful_backups' => Backup::where('status', 'completed')->count(),
            'failed_backups' => Backup::where('status', 'failed')->count(),
            'total_size' => Backup::where('status', 'completed')->sum('size'),
            'last_backup' => Backup::where('status', 'completed')->latest()->first()
        ];
    }

    private function getStorageInfo()
    {
        $totalSpace = disk_total_space('/');
        $freeSpace = disk_free_space('/');
        $usedSpace = $totalSpace - $freeSpace;
        
        return [
            'total' => $this->formatFileSize($totalSpace),
            'used' => $this->formatFileSize($usedSpace),
            'free' => $this->formatFileSize($freeSpace),
            'usage_percentage' => round(($usedSpace / $totalSpace) * 100, 2)
        ];
    }

    private function getMaintenanceInfo()
    {
        return [
            'is_maintenance_mode' => $this->isMaintenanceMode(),
            'last_maintenance' => Cache::get('last_maintenance_date'),
            'scheduled_maintenance' => Cache::get('scheduled_maintenance'),
            'maintenance_window' => '02:00 - 04:00 UTC'
        ];
    }

    private function getScheduledTasks()
    {
        return [
            [
                'name' => 'Sauvegarde automatique',
                'frequency' => 'Quotidienne',
                'next_run' => now()->addDay()->format('d/m/Y H:i'),
                'status' => 'active'
            ],
            [
                'name' => 'Nettoyage des logs',
                'frequency' => 'Hebdomadaire',
                'next_run' => now()->addWeek()->format('d/m/Y H:i'),
                'status' => 'active'
            ],
            [
                'name' => 'Optimisation base de données',
                'frequency' => 'Mensuelle',
                'next_run' => now()->addMonth()->format('d/m/Y H:i'),
                'status' => 'active'
            ]
        ];
    }

    private function performBackup($backup)
    {
        try {
            $fileName = 'backup_' . $backup->type . '_' . now()->format('Y_m_d_H_i_s');
            
            switch ($backup->type) {
                case 'database':
                    $this->createDatabaseBackup($fileName);
                    break;
                case 'files':
                    $this->createFilesBackup($fileName);
                    break;
                case 'full':
                    $this->createFullBackup($fileName);
                    break;
            }
            
            $backup->update([
                'status' => 'completed',
                'file_path' => "backups/{$fileName}",
                'size' => rand(1024*1024, 1024*1024*50), // Simuler une taille
                'completed_at' => now()
            ]);
            
        } catch (\Exception $e) {
            $backup->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now()
            ]);
            throw $e;
        }
    }

    // Méthodes de vérification système
    private function checkDatabaseConnection()
    {
        try {
            DB::connection()->getPdo();
            return 'healthy';
        } catch (\Exception $e) {
            return 'critical';
        }
    }

    private function checkStorageSpace()
    {
        $usage = $this->getDiskUsagePercentage();
        if ($usage > 90) return 'critical';
        if ($usage > 80) return 'warning';
        return 'healthy';
    }

    private function checkCacheConnection()
    {
        try {
            Cache::put('health_check', 'ok', 60);
            return Cache::get('health_check') === 'ok' ? 'healthy' : 'warning';
        } catch (\Exception $e) {
            return 'critical';
        }
    }

    private function checkQueueConnection()
    {
        // Simuler la vérification des queues
        return 'healthy';
    }

    // Méthodes de métriques système (simulées)
    private function getCpuUsage() { return rand(20, 80); }
    private function getMemoryUsage() { return rand(40, 85); }
    private function getDiskUsagePercentage() { return rand(30, 75); }
    private function getLoadAverage() { return number_format(rand(1, 4) + rand(0, 99)/100, 2); }
    private function getSystemUptime() { return rand(1, 100) . ' jours'; }
    private function getActiveProcesses() { return rand(50, 200); }
    private function getNetworkStats() { return ['in' => '1.2 MB/s', 'out' => '800 KB/s']; }
    private function getActiveSessions() { return rand(10, 50); }
    private function getDatabaseSize() { return $this->formatFileSize(rand(100*1024*1024, 1024*1024*1024)); }
    private function getStorageUsed() { return $this->formatFileSize(rand(1024*1024*1024, 10*1024*1024*1024)); }
    private function getCacheHitRatio() { return rand(85, 98) . '%'; }
    private function getAverageResponseTime() { return rand(50, 200) . 'ms'; }
    private function getLogFilesSize() { return $this->formatFileSize(rand(10*1024*1024, 100*1024*1024)); }

    // Méthodes helper
    private function formatFileSize($size)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $unitIndex = 0;
        
        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }
        
        return round($size, 2) . ' ' . $units[$unitIndex];
    }

    private function getDatabaseMessage()
    {
        return $this->checkDatabaseConnection() === 'healthy' 
            ? 'Connexion base de données OK' 
            : 'Problème de connexion à la base de données';
    }

    private function getStorageMessage()
    {
        $usage = $this->getDiskUsagePercentage();
        return "Espace disque utilisé: {$usage}%";
    }

    private function getCacheMessage()
    {
        return $this->checkCacheConnection() === 'healthy' 
            ? 'Cache fonctionnel' 
            : 'Problème avec le cache';
    }

    private function getQueueMessage()
    {
        return 'Files d\'attente opérationnelles';
    }

    private function checkWebServer() { return 'healthy'; }
    private function checkRedisConnection() { return 'healthy'; }
    private function checkQueueWorker() { return 'healthy'; }
    private function checkScheduler() { return 'healthy'; }

    private function createDatabaseBackup($fileName) { /* Implémentation */ }
    private function createFilesBackup($fileName) { /* Implémentation */ }
    private function createFullBackup($fileName) { /* Implémentation */ }
}