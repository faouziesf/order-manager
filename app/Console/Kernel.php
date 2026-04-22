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
        // ========================================
        // SYNCHRONISATION WOOCOMMERCE
        // ========================================
        
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
        
        // ========================================
        // 🆕 GESTION DES LIVRAISONS MULTI-TRANSPORTEURS
        // ========================================
        
        // Suivi automatique des statuts toutes les heures (principal)
        $schedule->command('delivery:track-statuses')
            ->hourly()
            ->withoutOverlapping(30) // Éviter les doublons, timeout 30min
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/delivery-track.log'))
            ->onSuccess(function () {
                \Log::info('🚚 Suivi automatique des livraisons terminé avec succès');
            })
            ->onFailure(function () {
                \Log::error('❌ Échec du suivi automatique des livraisons');
            });
        
        // Suivi plus fréquent en heures de bureau (9h-18h) - toutes les 30 minutes
        $schedule->command('delivery:track-statuses --limit=100')
            ->cron('*/30 9-18 * * 1-6') // Lun-Sam 9h-18h toutes les 30min
            ->withoutOverlapping(15)
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/delivery-track-business.log'))
            ->name('delivery-track-business-hours')
            ->description('Suivi fréquent des livraisons en heures de bureau');
        
        // Suivi spécifique JAX en heures de pointe
        $schedule->command('delivery:track-statuses --carrier=jax_delivery --limit=200')
            ->cron('0 10,14,16 * * 1-6') // 10h, 14h, 16h en semaine
            ->withoutOverlapping()
            ->runInBackground()
            ->name('delivery-track-jax')
            ->description('Suivi spécialisé JAX Delivery');
        
        // Suivi spécifique Mes Colis
        $schedule->command('delivery:track-statuses --carrier=mes_colis --limit=150')
            ->cron('30 10,14,16 * * 1-6') // 10h30, 14h30, 16h30 en semaine
            ->withoutOverlapping()
            ->runInBackground()
            ->name('delivery-track-mes-colis')
            ->description('Suivi spécialisé Mes Colis Express');
        
        // Nettoyage des pickups vides anciens (tous les jours à 3h)
        $schedule->call(function () {
            $deleted = \App\Models\Pickup::cleanupEmpty(7);
            \Log::info("🧹 Nettoyage des pickups vides : {$deleted} pickup(s) supprimé(s)");
        })
            ->dailyAt('03:00')
            ->name('cleanup-empty-pickups')
            ->description('Nettoyage des pickups vides de plus de 7 jours');
        
        // Nettoyage des anciens logs de livraison (hebdomadaire)
        $schedule->call(function () {
            $logFiles = [
                'delivery-track.log',
                'delivery-track-business.log',
                'delivery-track-*.log'
            ];
            
            $deleted = 0;
            foreach ($logFiles as $pattern) {
                $files = glob(storage_path('logs/' . $pattern));
                foreach ($files as $file) {
                    if (filemtime($file) < now()->subDays(14)->timestamp) {
                        unlink($file);
                        $deleted++;
                    }
                }
            }
            
            \Log::info("🧹 Nettoyage des logs de livraison : {$deleted} fichier(s) supprimé(s)");
        })
            ->weekly()
            ->sundays()
            ->at('04:00')
            ->name('cleanup-delivery-logs')
            ->description('Nettoyage des anciens logs de livraison');
        
        // Génération de rapports de livraison quotidiens (optionnel)
        $schedule->call(function () {
            if (config('app.env') === 'production') {
                // Générer des statistiques quotidiennes de livraison
                $stats = [
                    'date' => today()->toDateString(),
                    'shipments_tracked' => \App\Models\Shipment::whereDate('carrier_last_status_update', today())->count(),
                    'shipments_delivered' => \App\Models\Shipment::whereDate('delivered_at', today())->count(),
                    'active_shipments' => \App\Models\Shipment::whereIn('status', [
                        'validated', 'picked_up_by_carrier', 'in_transit'
                    ])->count(),
                ];
                
                \Log::info('📊 Statistiques quotidiennes de livraison', $stats);
                
                // Sauvegarder dans un fichier de stats si nécessaire
                $statsFile = storage_path('app/delivery-stats/' . today()->format('Y-m') . '.json');
                if (!file_exists(dirname($statsFile))) {
                    mkdir(dirname($statsFile), 0755, true);
                }
                
                $existingStats = [];
                if (file_exists($statsFile)) {
                    $existingStats = json_decode(file_get_contents($statsFile), true) ?: [];
                }
                
                $existingStats[today()->toDateString()] = $stats;
                file_put_contents($statsFile, json_encode($existingStats, JSON_PRETTY_PRINT));
            }
        })
            ->dailyAt('23:30')
            ->name('delivery-daily-stats')
            ->description('Génération des statistiques quotidiennes de livraison');
        
        // ========================================
        // MAINTENANCE GÉNÉRALE DU SYSTÈME
        // ========================================
        
        // Réinitialisation des tentatives journalières à minuit
        $schedule->call(function () {
            \App\Models\Order::query()->update(['daily_attempts_count' => 0]);
            \Log::info('Daily attempts count reset completed');
        })->dailyAt('00:00')->name('reset-daily-attempts');
        
        // Nettoyage des anciens logs système (hebdomadaire)
        $schedule->call(function () {
            $logPath = storage_path('logs');
            $files = glob($logPath . '/laravel-*.log');
            
            $deleted = 0;
            foreach ($files as $file) {
                if (filemtime($file) < strtotime('-30 days')) {
                    unlink($file);
                    $deleted++;
                }
            }
            
            \Log::info("Old log files cleaned up: {$deleted} files deleted");
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

        // ========================================
        // 🆕 SURVEILLANCE ET MAINTENANCE AVANCÉE DU SYSTÈME DE LIVRAISON
        // ========================================
        
        // Vérification de la santé des configurations de transporteurs (toutes les 6h)
        $schedule->call(function () {
            $this->checkCarrierConfigsHealth();
        })
            ->everySixHours()
            ->name('carrier-configs-health-check')
            ->description('Vérification de la santé des configurations transporteurs');
            
        // Alertes pour les expéditions en retard (tous les jours à 10h)
        $schedule->call(function () {
            $this->checkOverdueShipments();
        })
            ->dailyAt('10:00')
            ->name('check-overdue-shipments')
            ->description('Vérification des expéditions en retard');

        // ========================================
        // SYNCHRONISATION INTÉGRATIONS PLANIFIÉES
        // ========================================
        // Synchronise les intégrations (WooCommerce, Shopify, PrestaShop, Wix, Google Sheets)
        // selon le jour et l'heure configurés pour chacune
        $schedule->command('sync:integrations-scheduled')
            ->hourly()
            ->withoutOverlapping(15)
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/integrations-scheduled.log'))
            ->onSuccess(function () {
                \Log::info('Scheduled integrations sync completed');
            })
            ->onFailure(function () {
                \Log::error('Scheduled integrations sync failed');
            });
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
        // Commandes de notifications existantes
        \App\Console\Commands\CheckExpiringAdmins::class,
        \App\Console\Commands\CleanupNotifications::class,
        \App\Console\Commands\NotificationStats::class,
        \App\Console\Commands\CreateTestNotifications::class,
        \App\Console\Commands\SendNotificationReport::class,
        \App\Console\Commands\NotificationSchedulerStatus::class,
        
        // 🆕 Nouvelle commande de suivi des livraisons
        \App\Console\Commands\TrackDeliveryStatuses::class,
    ];

    // ========================================
    // MÉTHODES EXISTANTES POUR LES NOTIFICATIONS
    // ========================================

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

    // ========================================
    // 🆕 NOUVELLES MÉTHODES POUR LE SYSTÈME DE LIVRAISON
    // ========================================

    /**
     * 🆕 Vérifier la santé des configurations transporteurs
     */
    private function checkCarrierConfigsHealth(): void
    {
        try {
            $configs = \App\Models\DeliveryConfiguration::where('is_active', true)->get();
            $issues = [];
            
            foreach ($configs as $config) {
                try {
                    // Test de connexion basique
                    $testResult = $config->testConnection();
                    
                    if (!$testResult['success']) {
                        $issues[] = [
                            'config_id' => $config->id,
                            'carrier' => $config->carrier_slug,
                            'integration_name' => $config->integration_name,
                            'error' => $testResult['message'],
                        ];
                    }
                    
                } catch (\Exception $e) {
                    $issues[] = [
                        'config_id' => $config->id,
                        'carrier' => $config->carrier_slug,
                        'integration_name' => $config->integration_name,
                        'error' => $e->getMessage(),
                    ];
                }
            }
            
            if (!empty($issues)) {
                \Log::warning('🚨 Problèmes détectés avec les configurations transporteurs', [
                    'issues_count' => count($issues),
                    'issues' => $issues,
                ]);
                
                // Créer une notification si système de notifications disponible
                if (class_exists('\App\Models\SuperAdminNotification')) {
                    \App\Models\SuperAdminNotification::create([
                        'type' => 'delivery',
                        'title' => 'Problèmes configurations transporteurs',
                        'message' => count($issues) . ' configuration(s) transporteur(s) rencontrent des problèmes de connexion.',
                        'priority' => 'high',
                        'data' => ['issues' => $issues],
                    ]);
                }
            } else {
                \Log::info('✅ Toutes les configurations transporteurs sont opérationnelles', [
                    'configs_checked' => $configs->count(),
                ]);
            }
            
        } catch (\Exception $e) {
            \Log::error('❌ Erreur lors de la vérification des configurations transporteurs', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 🆕 Vérifier les expéditions en retard
     */
    private function checkOverdueShipments(): void
    {
        try {
            // Expéditions actives créées il y a plus de 3 jours
            $overdueShipments = \App\Models\Shipment::whereIn('status', [
                'validated', 'picked_up_by_carrier', 'in_transit'
            ])
            ->where('created_at', '<', now()->subDays(3))
            ->with(['order', 'pickup.deliveryConfiguration'])
            ->get();
            
            if ($overdueShipments->count() > 0) {
                $groupedByCarrier = $overdueShipments->groupBy('carrier_slug');
                
                $summary = [];
                foreach ($groupedByCarrier as $carrier => $shipments) {
                    $summary[$carrier] = [
                        'count' => $shipments->count(),
                        'oldest' => $shipments->min('created_at'),
                        'shipment_ids' => $shipments->pluck('id')->toArray(),
                    ];
                }
                
                \Log::warning('📦 Expéditions en retard détectées', [
                    'total_overdue' => $overdueShipments->count(),
                    'by_carrier' => $summary,
                ]);
                
                // Créer une notification si système disponible
                if (class_exists('\App\Models\SuperAdminNotification')) {
                    \App\Models\SuperAdminNotification::create([
                        'type' => 'delivery',
                        'title' => 'Expéditions en retard',
                        'message' => $overdueShipments->count() . ' expédition(s) sont en cours depuis plus de 3 jours.',
                        'priority' => 'medium',
                        'data' => [
                            'total_overdue' => $overdueShipments->count(),
                            'by_carrier' => $summary,
                        ],
                    ]);
                }
            } else {
                \Log::info('✅ Aucune expédition en retard détectée');
            }
            
        } catch (\Exception $e) {
            \Log::error('❌ Erreur lors de la vérification des expéditions en retard', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}