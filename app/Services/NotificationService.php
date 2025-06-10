<?php

// Fichier: app/Services/NotificationService.php

namespace App\Services;

use App\Models\SuperAdminNotification;
use App\Models\Admin;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class NotificationService
{
    /**
     * Types de notifications disponibles
     */
    const TYPES = [
        'admin_registered' => 'Administrateur inscrit',
        'admin_expired' => 'Administrateur expiré',
        'admin_expiring' => 'Administrateur expire bientôt',
        'admin_inactive' => 'Administrateur inactif',
        'high_order_volume' => 'Volume de commandes élevé',
        'system' => 'Système',
        'security' => 'Sécurité',
        'backup' => 'Sauvegarde',
        'maintenance' => 'Maintenance',
        'performance' => 'Performance',
        'disk_space' => 'Espace disque',
        'database' => 'Base de données',
    ];

    /**
     * Priorités des notifications
     */
    const PRIORITIES = [
        'low' => 'Faible',
        'medium' => 'Moyenne',
        'high' => 'Élevée',
        'critical' => 'Critique'
    ];

    /**
     * Créer une nouvelle notification
     */
    public function create(string $type, string $title, string $message, array $options = []): SuperAdminNotification
    {
        $notification = SuperAdminNotification::create([
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'priority' => $options['priority'] ?? 'medium',
            'related_admin_id' => $options['admin_id'] ?? null,
            'data' => $options['data'] ?? []
        ]);

        // Invalider le cache des compteurs
        $this->clearCountersCache();

        // Log de la création
        Log::info('Notification créée', [
            'id' => $notification->id,
            'type' => $type,
            'title' => $title,
            'priority' => $notification->priority
        ]);

        // Déclencher des événements pour les notifications temps réel
        $this->triggerRealtimeNotification($notification);

        return $notification;
    }

    /**
     * Créer une notification liée à un administrateur
     */
    public function createAdminNotification(string $type, string $title, string $message, Admin $admin, array $options = []): SuperAdminNotification
    {
        $options['admin_id'] = $admin->id;
        $options['data'] = array_merge($options['data'] ?? [], [
            'admin_name' => $admin->name,
            'admin_email' => $admin->email,
            'shop_name' => $admin->shop_name
        ]);

        return $this->create($type, $title, $message, $options);
    }

    /**
     * Créer une notification système
     */
    public function createSystemNotification(string $title, string $message, array $options = []): SuperAdminNotification
    {
        return $this->create('system', $title, $message, $options);
    }

    /**
     * Créer une notification de sécurité
     */
    public function createSecurityNotification(string $title, string $message, array $options = []): SuperAdminNotification
    {
        $options['priority'] = $options['priority'] ?? 'high';
        return $this->create('security', $title, $message, $options);
    }

    /**
     * Notifications prédéfinies pour les administrateurs
     */
    public function notifyAdminRegistered(Admin $admin): SuperAdminNotification
    {
        return $this->createAdminNotification(
            'admin_registered',
            'Nouvel administrateur inscrit',
            "L'administrateur {$admin->name} s'est inscrit avec la boutique {$admin->shop_name}",
            $admin,
            ['priority' => 'medium']
        );
    }

    public function notifyAdminExpired(Admin $admin): SuperAdminNotification
    {
        return $this->createAdminNotification(
            'admin_expired',
            'Administrateur expiré',
            "L'administrateur {$admin->name} ({$admin->shop_name}) a expiré",
            $admin,
            ['priority' => 'high']
        );
    }

    public function notifyAdminExpiring(Admin $admin, int $daysLeft): SuperAdminNotification
    {
        return $this->createAdminNotification(
            'admin_expiring',
            'Administrateur expire bientôt',
            "L'administrateur {$admin->name} ({$admin->shop_name}) expire dans {$daysLeft} jour(s)",
            $admin,
            [
                'priority' => $daysLeft <= 3 ? 'high' : 'medium',
                'data' => ['days_left' => $daysLeft]
            ]
        );
    }

    public function notifyHighOrderVolume(Admin $admin, int $orderCount): SuperAdminNotification
    {
        return $this->createAdminNotification(
            'high_order_volume',
            'Volume de commandes élevé',
            "L'administrateur {$admin->name} a traité {$orderCount} commandes aujourd'hui",
            $admin,
            [
                'priority' => 'low',
                'data' => ['order_count' => $orderCount, 'date' => now()->toDateString()]
            ]
        );
    }

    /**
     * Notifications système prédéfinies
     */
    public function notifyBackupCompleted(string $backupSize, string $backupType = 'automatic'): SuperAdminNotification
    {
        return $this->createSystemNotification(
            'Sauvegarde terminée',
            "La sauvegarde {$backupType} a été créée avec succès ({$backupSize})",
            [
                'priority' => 'low',
                'data' => ['backup_size' => $backupSize, 'backup_type' => $backupType]
            ]
        );
    }

    public function notifyBackupFailed(string $reason): SuperAdminNotification
    {
        return $this->create(
            'backup',
            'Échec de la sauvegarde',
            "La sauvegarde automatique a échoué : {$reason}",
            [
                'priority' => 'high',
                'data' => ['error_reason' => $reason]
            ]
        );
    }

    public function notifyDiskSpaceWarning(float $percentage): SuperAdminNotification
    {
        $priority = $percentage >= 90 ? 'critical' : ($percentage >= 80 ? 'high' : 'medium');
        
        return $this->create(
            'disk_space',
            'Espace disque faible',
            "L'espace disque est utilisé à {$percentage}%",
            [
                'priority' => $priority,
                'data' => ['disk_usage' => $percentage]
            ]
        );
    }

    public function notifySecurityAlert(string $alertType, array $details): SuperAdminNotification
    {
        return $this->createSecurityNotification(
            'Alerte de sécurité',
            "Alerte de sécurité détectée : {$alertType}",
            [
                'priority' => 'high',
                'data' => array_merge(['alert_type' => $alertType], $details)
            ]
        );
    }

    public function notifyMaintenanceScheduled(Carbon $scheduledDate, string $duration): SuperAdminNotification
    {
        return $this->create(
            'maintenance',
            'Maintenance programmée',
            "Une maintenance est programmée pour le {$scheduledDate->format('d/m/Y à H:i')} (durée estimée: {$duration})",
            [
                'priority' => 'medium',
                'data' => [
                    'scheduled_date' => $scheduledDate->toISOString(),
                    'duration' => $duration
                ]
            ]
        );
    }

    public function notifyPerformanceIssue(string $issue, array $metrics): SuperAdminNotification
    {
        return $this->create(
            'performance',
            'Problème de performance détecté',
            "Problème de performance : {$issue}",
            [
                'priority' => 'medium',
                'data' => array_merge(['issue' => $issue], $metrics)
            ]
        );
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead(int $notificationId): bool
    {
        $notification = SuperAdminNotification::find($notificationId);
        
        if (!$notification || $notification->read_at) {
            return false;
        }

        $notification->update(['read_at' => now()]);
        $this->clearCountersCache();

        return true;
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function markAllAsRead(): int
    {
        $count = SuperAdminNotification::whereNull('read_at')->update(['read_at' => now()]);
        $this->clearCountersCache();

        return $count;
    }

    /**
     * Supprimer une notification
     */
    public function delete(int $notificationId): bool
    {
        $notification = SuperAdminNotification::find($notificationId);
        
        if (!$notification) {
            return false;
        }

        $notification->delete();
        $this->clearCountersCache();

        return true;
    }

    /**
     * Obtenir les compteurs de notifications (avec cache)
     */
    public function getCounters(): array
    {
        return Cache::remember('notification_counters', 300, function () {
            return [
                'total' => SuperAdminNotification::count(),
                'unread' => SuperAdminNotification::whereNull('read_at')->count(),
                'important' => SuperAdminNotification::where('priority', 'high')->count(),
                'critical' => SuperAdminNotification::where('priority', 'critical')->count(),
                'today' => SuperAdminNotification::whereDate('created_at', today())->count(),
            ];
        });
    }

    /**
     * Obtenir les notifications récentes pour le dropdown
     */
    public function getRecentNotifications(int $limit = 10): array
    {
        return SuperAdminNotification::with('admin')
            ->orderBy('created_at', 'desc')
            ->take($limit)
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
            })
            ->toArray();
    }

    /**
     * Nettoyer les anciennes notifications
     */
    public function cleanup(int $days = 30): int
    {
        $cutoffDate = now()->subDays($days);
        $count = SuperAdminNotification::where('created_at', '<', $cutoffDate)->delete();
        
        $this->clearCountersCache();
        
        Log::info('Nettoyage des notifications', [
            'deleted_count' => $count,
            'cutoff_date' => $cutoffDate->toDateTimeString()
        ]);

        return $count;
    }

    /**
     * Vérifier les administrateurs qui expirent bientôt
     */
    public function checkExpiringAdmins(): array
    {
        $notifications = [];
        
        // Admins qui expirent dans 7 jours
        $expiringAdmins = Admin::where('is_active', true)
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [now(), now()->addDays(7)])
            ->get();

        foreach ($expiringAdmins as $admin) {
            $daysLeft = now()->diffInDays($admin->expiry_date);
            
            // Éviter les doublons en vérifiant si une notification récente existe
            $existingNotification = SuperAdminNotification::where('type', 'admin_expiring')
                ->where('related_admin_id', $admin->id)
                ->where('created_at', '>=', now()->subHours(24))
                ->exists();

            if (!$existingNotification) {
                $notifications[] = $this->notifyAdminExpiring($admin, $daysLeft);
            }
        }

        // Admins expirés
        $expiredAdmins = Admin::where('is_active', true)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<', now())
            ->get();

        foreach ($expiredAdmins as $admin) {
            $existingNotification = SuperAdminNotification::where('type', 'admin_expired')
                ->where('related_admin_id', $admin->id)
                ->where('created_at', '>=', now()->subHours(24))
                ->exists();

            if (!$existingNotification) {
                $notifications[] = $this->notifyAdminExpired($admin);
                
                // Désactiver automatiquement l'admin expiré
                $admin->update(['is_active' => false]);
            }
        }

        return $notifications;
    }

    /**
     * Obtenir les statistiques des notifications
     */
    public function getStatistics(): array
    {
        $now = now();
        $lastWeek = $now->copy()->subWeek();
        $lastMonth = $now->copy()->subMonth();

        return [
            'counters' => $this->getCounters(),
            'by_type' => SuperAdminNotification::selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray(),
            'by_priority' => SuperAdminNotification::selectRaw('priority, COUNT(*) as count')
                ->groupBy('priority')
                ->pluck('count', 'priority')
                ->toArray(),
            'trends' => [
                'this_week' => SuperAdminNotification::where('created_at', '>=', $lastWeek)->count(),
                'last_week' => SuperAdminNotification::whereBetween('created_at', [$lastMonth, $lastWeek])->count(),
                'this_month' => SuperAdminNotification::where('created_at', '>=', $lastMonth)->count(),
            ],
            'read_rate' => SuperAdminNotification::count() > 0 
                ? round((SuperAdminNotification::whereNotNull('read_at')->count() / SuperAdminNotification::count()) * 100, 2)
                : 0,
        ];
    }

    /**
     * Vider le cache des compteurs
     */
    private function clearCountersCache(): void
    {
        Cache::forget('notification_counters');
    }

    /**
     * Déclencher une notification temps réel (pour WebSockets, Pusher, etc.)
     */
    private function triggerRealtimeNotification(SuperAdminNotification $notification): void
    {
        // Ici vous pouvez ajouter l'intégration avec Pusher, WebSockets, etc.
        // Exemple avec Pusher :
        /*
        if (config('broadcasting.default') === 'pusher') {
            broadcast(new NotificationCreated($notification))->toOthers();
        }
        */
        
        // Pour l'instant, nous log juste l'événement
        Log::debug('Notification temps réel déclenchée', [
            'notification_id' => $notification->id,
            'type' => $notification->type
        ]);
    }

    /**
     * Obtenir les types de notifications disponibles
     */
    public static function getAvailableTypes(): array
    {
        return self::TYPES;
    }

    /**
     * Obtenir les priorités disponibles
     */
    public static function getAvailablePriorities(): array
    {
        return self::PRIORITIES;
    }

    /**
     * Créer des notifications de test (pour développement)
     */
    public function createTestNotifications(int $count = 10): array
    {
        if (!app()->environment('local')) {
            return [];
        }

        $notifications = [];
        $types = array_keys(self::TYPES);
        $priorities = array_keys(self::PRIORITIES);

        for ($i = 1; $i <= $count; $i++) {
            $type = $types[array_rand($types)];
            $priority = $priorities[array_rand($priorities)];
            
            $notifications[] = $this->create(
                $type,
                "Notification de test #{$i}",
                "Ceci est une notification de test générée automatiquement pour le type {$type}.",
                [
                    'priority' => $priority,
                    'data' => ['test' => true, 'index' => $i]
                ]
            );
        }

        return $notifications;
    }
}