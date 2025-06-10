<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SuperAdminNotification;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all');
        
        $query = SuperAdminNotification::with('admin')
            ->orderBy('created_at', 'desc');
        
        switch ($filter) {
            case 'unread':
                $query->whereNull('read_at');
                break;
            case 'important':
                $query->where('priority', 'high');
                break;
            case 'system':
                $query->where('type', 'system');
                break;
            case 'admin':
                $query->whereIn('type', ['admin_registered', 'admin_expired', 'admin_expiring']);
                break;
        }
        
        $notifications = $query->paginate(20);
        
        // Statistiques
        $stats = [
            'total' => SuperAdminNotification::count(),
            'unread' => SuperAdminNotification::whereNull('read_at')->count(),
            'important' => SuperAdminNotification::where('priority', 'high')->count(),
            'today' => SuperAdminNotification::whereDate('created_at', today())->count()
        ];
        
        return view('super-admin.notifications.index', compact('notifications', 'stats', 'filter'));
    }

    public function markAsRead(SuperAdminNotification $notification)
    {
        $notification->update(['read_at' => now()]);
        
        return response()->json(['status' => 'success']);
    }

    public function markAllAsRead()
    {
        SuperAdminNotification::whereNull('read_at')
            ->update(['read_at' => now()]);
        
        return redirect()->back()->with('success', 'Toutes les notifications ont été marquées comme lues.');
    }

    public function destroy(SuperAdminNotification $notification)
    {
        $notification->delete();
        
        return redirect()->back()->with('success', 'Notification supprimée avec succès.');
    }

    public function getUnreadCount()
    {
        $count = SuperAdminNotification::whereNull('read_at')->count();
        
        return response()->json(['count' => $count]);
    }

    /**
     * API pour récupérer les notifications récentes pour le dropdown
     */
    public function getRecentNotifications()
    {
        $notifications = SuperAdminNotification::with('admin')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'priority' => $notification->priority,
                    'read_at' => $notification->read_at,
                    'time_ago' => $notification->time_ago,
                    'created_at' => $notification->created_at,
                    'admin' => $notification->admin ? [
                        'id' => $notification->admin->id,
                        'name' => $notification->admin->name
                    ] : null
                ];
            });

        $unreadCount = SuperAdminNotification::whereNull('read_at')->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
            'total_count' => SuperAdminNotification::count()
        ]);
    }

    /**
     * API pour récupérer les statistiques de notifications
     */
    public function getNotificationStats()
    {
        $stats = [
            'total' => SuperAdminNotification::count(),
            'unread' => SuperAdminNotification::whereNull('read_at')->count(),
            'important' => SuperAdminNotification::where('priority', 'high')->count(),
            'today' => SuperAdminNotification::whereDate('created_at', today())->count(),
            'this_week' => SuperAdminNotification::whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count(),
            'by_type' => SuperAdminNotification::selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray(),
            'by_priority' => SuperAdminNotification::selectRaw('priority, COUNT(*) as count')
                ->groupBy('priority')
                ->pluck('count', 'priority')
                ->toArray()
        ];

        return response()->json($stats);
    }

    /**
     * Marquer plusieurs notifications comme lues
     */
    public function markMultipleAsRead(Request $request)
    {
        $request->validate([
            'notification_ids' => 'required|array',
            'notification_ids.*' => 'exists:super_admin_notifications,id'
        ]);

        $updated = SuperAdminNotification::whereIn('id', $request->notification_ids)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'status' => 'success',
            'updated_count' => $updated,
            'message' => "{$updated} notification(s) marquée(s) comme lue(s)"
        ]);
    }

    /**
     * Supprimer plusieurs notifications
     */
    public function deleteMultiple(Request $request)
    {
        $request->validate([
            'notification_ids' => 'required|array',
            'notification_ids.*' => 'exists:super_admin_notifications,id'
        ]);

        $deleted = SuperAdminNotification::whereIn('id', $request->notification_ids)->delete();

        return response()->json([
            'status' => 'success',
            'deleted_count' => $deleted,
            'message' => "{$deleted} notification(s) supprimée(s)"
        ]);
    }

    /**
     * Créer une notification de test (pour développement)
     */
    public function createTestNotification(Request $request)
    {
        if (!app()->environment('local')) {
            return response()->json(['error' => 'Disponible uniquement en développement'], 403);
        }

        $types = ['admin_registered', 'admin_expired', 'system', 'security', 'backup'];
        $priorities = ['low', 'medium', 'high'];

        $notification = SuperAdminNotification::create([
            'type' => $request->get('type', $types[array_rand($types)]),
            'title' => $request->get('title', 'Notification de test'),
            'message' => $request->get('message', 'Ceci est une notification de test générée automatiquement.'),
            'priority' => $request->get('priority', $priorities[array_rand($priorities)]),
            'data' => $request->get('data', [])
        ]);

        return response()->json([
            'status' => 'success',
            'notification' => $notification,
            'message' => 'Notification de test créée'
        ]);
    }

    /**
     * Nettoyer les anciennes notifications
     */
    public function cleanupOldNotifications(Request $request)
    {
        $days = $request->get('days', 30);
        
        $cutoffDate = now()->subDays($days);
        $deleted = SuperAdminNotification::where('created_at', '<', $cutoffDate)->delete();

        return response()->json([
            'status' => 'success',
            'deleted_count' => $deleted,
            'cutoff_date' => $cutoffDate->format('Y-m-d H:i:s'),
            'message' => "Nettoyage terminé : {$deleted} notifications supprimées"
        ]);
    }

    /**
     * Exporter les notifications
     */
    public function exportNotifications(Request $request)
    {
        $format = $request->get('format', 'csv');
        $query = SuperAdminNotification::with('admin')->orderBy('created_at', 'desc');

        // Appliquer les filtres
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $notifications = $query->get();

        switch ($format) {
            case 'json':
                return $this->exportToJson($notifications);
            default:
                return $this->exportToCsv($notifications);
        }
    }

    /**
     * Exporter vers CSV
     */
    private function exportToCsv($notifications)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="notifications_' . date('Y-m-d') . '.csv"'
        ];

        $callback = function() use ($notifications) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'ID', 'Type', 'Titre', 'Message', 'Priorité', 'Statut', 
                'Admin concerné', 'Date de création', 'Date de lecture'
            ]);
            
            // Data
            foreach ($notifications as $notification) {
                fputcsv($file, [
                    $notification->id,
                    $notification->type,
                    $notification->title,
                    $notification->message,
                    $notification->priority,
                    $notification->read_at ? 'Lu' : 'Non lu',
                    $notification->admin ? $notification->admin->name : '',
                    $notification->created_at->format('d/m/Y H:i'),
                    $notification->read_at ? $notification->read_at->format('d/m/Y H:i') : ''
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Exporter vers JSON
     */
    private function exportToJson($notifications)
    {
        $data = $notifications->map(function ($notification) {
            return [
                'id' => $notification->id,
                'type' => $notification->type,
                'title' => $notification->title,
                'message' => $notification->message,
                'priority' => $notification->priority,
                'read_at' => $notification->read_at,
                'admin' => $notification->admin ? [
                    'id' => $notification->admin->id,
                    'name' => $notification->admin->name,
                    'email' => $notification->admin->email
                ] : null,
                'created_at' => $notification->created_at,
                'data' => $notification->data
            ];
        });

        $filename = 'notifications_' . date('Y-m-d') . '.json';
        
        return response()->json([
            'export_date' => now()->toISOString(),
            'total_notifications' => $notifications->count(),
            'notifications' => $data
        ])->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    // Méthodes pour créer des notifications
    public static function createAdminNotification($type, $title, $message, $adminId = null, $priority = 'medium')
    {
        return SuperAdminNotification::create([
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'priority' => $priority,
            'related_admin_id' => $adminId,
            'data' => []
        ]);
    }

    public static function createSystemNotification($title, $message, $priority = 'medium', $data = [])
    {
        return SuperAdminNotification::create([
            'type' => 'system',
            'title' => $title,
            'message' => $message,
            'priority' => $priority,
            'data' => $data
        ]);
    }

    // Notifications automatiques
    public static function notifyAdminRegistered(Admin $admin)
    {
        self::createAdminNotification(
            'admin_registered',
            'Nouvel administrateur inscrit',
            "L'administrateur {$admin->name} s'est inscrit avec la boutique {$admin->shop_name}",
            $admin->id,
            'medium'
        );
    }

    public static function notifyAdminExpired(Admin $admin)
    {
        self::createAdminNotification(
            'admin_expired',
            'Administrateur expiré',
            "L'administrateur {$admin->name} ({$admin->shop_name}) a expiré",
            $admin->id,
            'high'
        );
    }

    public static function notifyAdminExpiring(Admin $admin, $daysLeft)
    {
        self::createAdminNotification(
            'admin_expiring',
            'Administrateur expire bientôt',
            "L'administrateur {$admin->name} ({$admin->shop_name}) expire dans {$daysLeft} jour(s)",
            $admin->id,
            'medium'
        );
    }

    public static function notifyHighOrderVolume(Admin $admin, $orderCount)
    {
        self::createAdminNotification(
            'high_order_volume',
            'Volume de commandes élevé',
            "L'administrateur {$admin->name} a traité {$orderCount} commandes aujourd'hui",
            $admin->id,
            'low'
        );
    }

    public static function notifySystemAlert($title, $message, $priority = 'high')
    {
        self::createSystemNotification($title, $message, $priority);
    }

    public static function notifyDiskSpaceWarning($percentage)
    {
        self::createSystemNotification(
            'Espace disque faible',
            "L'espace disque est utilisé à {$percentage}%",
            'high',
            ['disk_usage' => $percentage]
        );
    }

    public static function notifyBackupCompleted($backupSize)
    {
        self::createSystemNotification(
            'Sauvegarde terminée',
            "La sauvegarde automatique a été créée avec succès ({$backupSize})",
            'low',
            ['backup_size' => $backupSize]
        );
    }

    public static function notifySecurityAlert($type, $details)
    {
        self::createSystemNotification(
            'Alerte de sécurité',
            "Alerte de sécurité détectée: {$type}",
            'high',
            ['security_type' => $type, 'details' => $details]
        );
    }
}