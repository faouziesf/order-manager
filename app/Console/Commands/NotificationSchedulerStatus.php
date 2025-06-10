<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SuperAdminNotification;

class NotificationSchedulerStatus extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'notifications:scheduler-status';

    /**
     * The console command description.
     */
    protected $description = 'Afficher le statut du planificateur de notifications';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('=== STATUT DU PLANIFICATEUR DE NOTIFICATIONS ===');
        $this->newLine();

        // Vérifier si le cron est configuré
        $this->checkCronConfiguration();

        // Afficher les tâches programmées
        $this->showScheduledTasks();

        // Vérifier les logs récents
        $this->checkRecentLogs();

        return Command::SUCCESS;
    }

    private function checkCronConfiguration(): void
    {
        $this->info('🔧 Configuration du cron :');
        
        $cronCommand = '* * * * * cd ' . base_path() . ' && php artisan schedule:run >> /dev/null 2>&1';
        
        $this->line('Commande cron requise :');
        $this->line($cronCommand);
        $this->newLine();

        // Vérifier si le fichier de log du scheduler existe
        $logFile = storage_path('logs/laravel.log');
        if (file_exists($logFile)) {
            $this->info('✅ Fichier de log trouvé');
            
            // Chercher des entrées récentes du scheduler
            $recent = shell_exec("tail -100 {$logFile} | grep -i 'schedule' | tail -5");
            if ($recent) {
                $this->info('Entrées récentes du scheduler :');
                $this->line($recent);
            }
        } else {
            $this->warn('⚠️  Fichier de log non trouvé');
        }
        
        $this->newLine();
    }

    private function showScheduledTasks(): void
    {
        $this->info('📅 Tâches programmées pour les notifications :');
        
        $tasks = [
            'notifications:check-expiring-admins' => 'Tous les jours à 8h00',
            'notifications:cleanup' => 'Tous les dimanches à 2h00',
            'notifications:report' => 'Tous les jours à 9h00',
            'Vérification santé système' => 'Toutes les heures',
            'Nettoyage cache' => 'Tous les jours à minuit',
            'Statistiques hebdomadaires' => 'Tous les lundis à 8h00',
            'Archivage mensuel' => 'Tous les mois',
            'Vérification performances' => 'Toutes les 6 heures'
        ];

        $this->table(
            ['Tâche', 'Fréquence'],
            collect($tasks)->map(function ($frequency, $task) {
                return [$task, $frequency];
            })->toArray()
        );

        $this->newLine();
    }

    private function checkRecentLogs(): void
    {
        $this->info('📊 Vérification des logs récents :');

        try {
            // Vérifier les logs de notifications des dernières 24h
            $recentNotifications = SuperAdminNotification::where('created_at', '>=', now()->subDay())->count();
            
            $this->table(
                ['Métrique', 'Valeur'],
                [
                    ['Notifications créées (24h)', $recentNotifications],
                    ['Dernière vérification santé', $this->getLastHealthCheck()],
                    ['Dernier nettoyage', $this->getLastCleanup()],
                    ['Statut général', $recentNotifications > 0 ? '✅ Actif' : '⚠️  Inactif']
                ]
            );

        } catch (\Exception $e) {
            $this->error('Erreur lors de la vérification des logs : ' . $e->getMessage());
        }

        $this->newLine();
        $this->info('💡 Conseils :');
        $this->line('- Vérifiez que le cron job est configuré sur votre serveur');
        $this->line('- Testez manuellement : php artisan schedule:run');
        $this->line('- Consultez les logs : tail -f storage/logs/laravel.log');
    }

    private function getLastHealthCheck(): string
    {
        try {
            $lastCheck = SuperAdminNotification::where('title', 'Test de santé système')
                ->latest()
                ->first();
            
            return $lastCheck ? $lastCheck->created_at->diffForHumans() : 'Jamais';
        } catch (\Exception $e) {
            return 'Erreur';
        }
    }

    private function getLastCleanup(): string
    {
        try {
            $logFile = storage_path('logs/laravel.log');
            if (file_exists($logFile)) {
                $cleanupLog = shell_exec("grep -i 'nettoyage.*notifications' {$logFile} | tail -1");
                return $cleanupLog ? 'Récent' : 'Aucun log trouvé';
            }
            return 'Log non disponible';
        } catch (\Exception $e) {
            return 'Erreur';
        }
    }
}