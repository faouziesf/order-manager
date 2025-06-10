<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Synchronisation WooCommerce toutes les 3 minutes
        $schedule->command('woocommerce:sync')
            ->everyThreeMinutes()
            ->withoutOverlapping(10) // Timeout après 10 minutes si bloqué
            ->runInBackground()
            ->onSuccess(function () {
                \Log::info('WooCommerce sync completed successfully via scheduler');
            })
            ->onFailure(function () {
                \Log::error('WooCommerce sync failed via scheduler');
            });
        
        // Réinitialisation des tentatives journalières à minuit
        $schedule->call(function () {
            \App\Models\Order::query()->update(['daily_attempts_count' => 0]);
            \Log::info('Daily attempts count reset completed');
        })->dailyAt('00:00')->name('reset-daily-attempts');
        
        // Nettoyage des anciens logs WooCommerce (optionnel, hebdomadaire)
        $schedule->call(function () {
            $logPath = storage_path('logs');
            $files = glob($logPath . '/laravel-*.log');
            
            foreach ($files as $file) {
                if (filemtime($file) < strtotime('-30 days')) {
                    unlink($file);
                }
            }
            
            \Log::info('Old log files cleaned up');
        })->weekly()->sundays()->at('02:00')->name('cleanup-logs');

        // ========================================
        // TÂCHES POUR LES NOTIFICATIONS
        // ========================================
        
        // Vérifier les administrateurs qui expirent - tous les jours à 8h00
        $schedule->command('notifications:check-expiring-admins')
            ->dailyAt('08:00')
            ->name('check-expiring-admins')
            ->description('Vérification des administrateurs qui expirent')
            ->emailOutputOnFailure(env('ADMIN_EMAIL', 'admin@example.com'));

        // Nettoyer les anciennes notifications - tous les dimanches à 2h00
        $schedule->command('notifications:cleanup --days=30 --force')
            ->weeklyOn(0, '02:00') // Dimanche à 2h00
            ->name('cleanup-notifications')
            ->description('Nettoyage des anciennes notifications')
            ->emailOutputOnFailure(env('ADMIN_EMAIL', 'admin@example.com'));

        // Rapport quotidien des notifications - tous les jours à 9h00
        $schedule->command('notifications:report --email=' . env('ADMIN_EMAIL', 'admin@example.com'))
            ->dailyAt('09:00')
            ->name('notification-report')
            ->description('Rapport quotidien des notifications')
            ->skip(function () {
                // Passer le week-end si souhaité
                return now()->isWeekend();
            });

        // Vérification de la santé du système de notifications - toutes les heures
        $schedule->call(function () {
            $this->checkNotificationSystemHealth();
        })
            ->hourly()
            ->name('notification-health-check')
            ->description('Vérification de la santé du système de notifications');

        // Nettoyage du cache des notifications - tous les jours à minuit
        $schedule->call(function () {
            \Illuminate\Support\Facades\Cache::forget('notification_counters');
            \Illuminate\Support\Facades\Cache::tags(['notifications'])->flush();
        })
            ->daily()
            ->name('notification-cache-cleanup')
            ->description('Nettoyage du cache des notifications');

        // Statistiques hebdomadaires - tous les lundis à 8h00
        $schedule->command('notifications:stats --format=json')
            ->weeklyOn(1, '08:00')
            ->name('notification-weekly-stats')
            ->description('Génération des statistiques hebdomadaires')
            ->appendOutputTo(storage_path('logs/notification-stats.log'));

        // Archivage des notifications importantes - tous les mois
        $schedule->call(function () {
            $this->archiveImportantNotifications();
        })
            ->monthly()
            ->name('notification-archiving')
            ->description('Archivage des notifications importantes');

        // Vérification des performances du système - toutes les 6 heures
        $schedule->call(function () {
            $this->checkSystemPerformance();
        })
            ->everySixHours()
            ->name('notification-performance-check')
            ->description('Vérification des performances système');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    protected $commands = [
        \App\Console\Commands\CheckExpiringAdmins::class,
        \App\Console\Commands\CleanupNotifications::class,
        \App\Console\Commands\NotificationStats::class,
        \App\Console\Commands\CreateTestNotifications::class,
        \App\Console\Commands\SendNotificationReport::class,
        \App\Console\Commands\NotificationSchedulerStatus::class,
    ];

    /**
     * Vérifier la santé du système de notifications
     */
    private function checkNotificationSystemHealth(): void
    {
        try {
            // Vérifier que les notifications peuvent être créées
            $testNotification = \App\Models\SuperAdminNotification::create([
                'type' => 'system',
                'title' => 'Test de santé système',
                'message' => 'Test automatique du système de notifications',
                'priority' => 'low',
                'data' => ['health_check' => true]
            ]);

            // Marquer immédiatement comme lue pour éviter l'accumulation
            $testNotification->update(['read_at' => now()]);

            // Vérifier les compteurs
            $counters = [
                'total' => \App\Models\SuperAdminNotification::count(),
                'unread' => \App\Models\SuperAdminNotification::whereNull('read_at')->count(),
                'important' => \App\Models\SuperAdminNotification::where('priority', 'high')->count(),
                'today' => \App\Models\SuperAdminNotification::whereDate('created_at', today())->count(),
            ];
            
            // Alerter si trop de notifications non lues
            if ($counters['unread'] > 100) {
                \App\Models\SuperAdminNotification::create([
                    'type' => 'system',
                    'title' => 'Trop de notifications non lues',
                    'message' => "Il y a {$counters['unread']} notifications non lues. Une révision est recommandée.",
                    'priority' => 'medium',
                    'data' => $counters
                ]);
            }

            \Log::info('Vérification de santé des notifications réussie', $counters);

        } catch (\Exception $e) {
            \Log::error('Échec de la vérification de santé des notifications', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Tentative de notification d'urgence
            try {
                \App\Models\SuperAdminNotification::create([
                    'type' => 'system',
                    'title' => 'Système de notifications défaillant',
                    'message' => 'Le système de notifications a rencontré une erreur : ' . $e->getMessage(),
                    'priority' => 'high',
                    'data' => ['error' => $e->getMessage()]
                ]);
            } catch (\Exception $secondaryE) {
                \Log::critical('Impossible de créer une notification d\'urgence', [
                    'original_error' => $e->getMessage(),
                    'secondary_error' => $secondaryE->getMessage()
                ]);
            }
        }
    }

    /**
     * Archiver les notifications importantes
     */
    private function archiveImportantNotifications(): void
    {
        try {
            $cutoffDate = now()->subMonths(6);
            
            // Notifications critiques et importantes anciennes
            $notifications = \App\Models\SuperAdminNotification::whereIn('priority', ['high'])
                ->where('created_at', '<', $cutoffDate)
                ->get();

            if ($notifications->count() > 0) {
                // Créer un fichier d'archive
                $archiveData = $notifications->map(function ($notification) {
                    return [
                        'id' => $notification->id,
                        'type' => $notification->type,
                        'title' => $notification->title,
                        'message' => $notification->message,
                        'priority' => $notification->priority,
                        'created_at' => $notification->created_at,
                        'read_at' => $notification->read_at,
                        'data' => $notification->data,
                        'admin' => $notification->admin ? [
                            'id' => $notification->admin->id,
                            'name' => $notification->admin->name,
                            'email' => $notification->admin->email
                        ] : null
                    ];
                });

                $archiveFile = storage_path('app/archives/notifications_' . now()->format('Y-m') . '.json');
                
                // Créer le dossier si nécessaire
                if (!file_exists(dirname($archiveFile))) {
                    mkdir(dirname($archiveFile), 0755, true);
                }

                file_put_contents($archiveFile, json_encode([
                    'archived_at' => now()->toISOString(),
                    'count' => $notifications->count(),
                    'notifications' => $archiveData
                ], JSON_PRETTY_PRINT));

                \Log::info('Notifications archivées', [
                    'count' => $notifications->count(),
                    'archive_file' => $archiveFile
                ]);

                // Créer une notification de confirmation
                \App\Models\SuperAdminNotification::create([
                    'type' => 'system',
                    'title' => 'Archivage terminé',
                    'message' => "{$notifications->count()} notifications importantes ont été archivées dans {$archiveFile}",
                    'priority' => 'low',
                    'data' => ['archive_file' => $archiveFile, 'count' => $notifications->count()]
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'archivage des notifications', [
                'error' => $e->getMessage()
            ]);

            \App\Models\SuperAdminNotification::create([
                'type' => 'system',
                'title' => 'Erreur d\'archivage',
                'message' => 'L\'archivage automatique des notifications a échoué : ' . $e->getMessage(),
                'priority' => 'medium',
                'data' => ['error' => $e->getMessage()]
            ]);
        }
    }

    /**
     * Vérifier les performances du système
     */
    private function checkSystemPerformance(): void
    {
        try {
            $startTime = microtime(true);

            // Mesurer le temps de récupération des compteurs
            $counters = [
                'total' => \App\Models\SuperAdminNotification::count(),
                'unread' => \App\Models\SuperAdminNotification::whereNull('read_at')->count(),
            ];
            $counterTime = microtime(true) - $startTime;

            // Mesurer le temps de récupération des notifications récentes
            $startRecent = microtime(true);
            $recent = \App\Models\SuperAdminNotification::orderBy('created_at', 'desc')->take(10)->get();
            $recentTime = microtime(true) - $startRecent;

            $metrics = [
                'counter_time' => round($counterTime * 1000, 2), // en millisecondes
                'recent_time' => round($recentTime * 1000, 2),
                'total_notifications' => $counters['total'],
                'memory_usage' => memory_get_usage(true),
                'memory_peak' => memory_get_peak_usage(true)
            ];

            \Log::info('Métriques de performance des notifications', $metrics);

            // Alerter si les performances sont dégradées
            if ($counterTime > 1.0) { // Plus d'une seconde
                \App\Models\SuperAdminNotification::create([
                    'type' => 'system',
                    'title' => 'Performance dégradée',
                    'message' => 'Le système de notifications répond lentement (temps de réponse: ' . round($counterTime, 2) . 's)',
                    'priority' => 'medium',
                    'data' => $metrics
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('Erreur lors de la vérification des performances', [
                'error' => $e->getMessage()
            ]);
        }
    }
}