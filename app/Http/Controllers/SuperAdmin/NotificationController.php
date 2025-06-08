<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SuperAdminNotification;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all');
        
        $query = SuperAdminNotification::query()
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
                $query->where('type', 'admin');
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