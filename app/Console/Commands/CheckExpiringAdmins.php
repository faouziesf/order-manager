<?php

// Fichier: app/Console/Commands/CheckExpiringAdmins.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NotificationService;

class CheckExpiringAdmins extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'notifications:check-expiring-admins {--dry-run : Afficher ce qui serait fait sans créer les notifications}';

    /**
     * The console command description.
     */
    protected $description = 'Vérifier les administrateurs qui expirent bientôt et créer des notifications';

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $notificationService): int
    {
        $this->info('Vérification des administrateurs qui expirent...');

        if ($this->option('dry-run')) {
            $this->warn('Mode DRY-RUN activé - Aucune notification ne sera créée');
        }

        try {
            if ($this->option('dry-run')) {
                // Simulation sans créer de notifications
                $this->simulateExpiringCheck();
            } else {
                $notifications = $notificationService->checkExpiringAdmins();
                
                if (empty($notifications)) {
                    $this->info('Aucun administrateur proche de l\'expiration trouvé.');
                } else {
                    $this->info('Notifications créées : ' . count($notifications));
                    
                    foreach ($notifications as $notification) {
                        $this->line("- {$notification->title} (Priorité: {$notification->priority})");
                    }
                }
            }

            $this->info('Vérification terminée avec succès.');
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('Erreur lors de la vérification : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function simulateExpiringCheck(): void
    {
        $expiringAdmins = \App\Models\Admin::where('is_active', true)
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [now(), now()->addDays(7)])
            ->get();

        $expiredAdmins = \App\Models\Admin::where('is_active', true)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<', now())
            ->get();

        $this->table(
            ['Admin', 'Email', 'Boutique', 'Date d\'expiration', 'Jours restants', 'Statut'],
            $expiringAdmins->map(function ($admin) {
                return [
                    $admin->name,
                    $admin->email,
                    $admin->shop_name,
                    $admin->expiry_date->format('d/m/Y'),
                    now()->diffInDays($admin->expiry_date),
                    'Expire bientôt'
                ];
            })->concat(
                $expiredAdmins->map(function ($admin) {
                    return [
                        $admin->name,
                        $admin->email,
                        $admin->shop_name,
                        $admin->expiry_date->format('d/m/Y'),
                        now()->diffInDays($admin->expiry_date) * -1 . ' (expiré)',
                        'Expiré'
                    ];
                })
            )
        );

        $this->info('Administrateurs qui expirent bientôt : ' . $expiringAdmins->count());
        $this->info('Administrateurs expirés : ' . $expiredAdmins->count());
    }
}

// =====================================

// Fichier: app/Console/Commands/CleanupNotifications.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NotificationService;

class CleanupNotifications extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'notifications:cleanup 
                            {--days=30 : Nombre de jours à conserver}
                            {--dry-run : Afficher ce qui serait supprimé sans supprimer}
                            {--force : Forcer la suppression sans confirmation}';

    /**
     * The console command description.
     */
    protected $description = 'Nettoyer les anciennes notifications';

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $notificationService): int
    {
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info("Nettoyage des notifications plus anciennes que {$days} jours...");

        if ($dryRun) {
            $this->warn('Mode DRY-RUN activé - Aucune suppression ne sera effectuée');
        }

        try {
            $cutoffDate = now()->subDays($days);
            $query = \App\Models\SuperAdminNotification::where('created_at', '<', $cutoffDate);
            $count = $query->count();

            if ($count === 0) {
                $this->info('Aucune notification ancienne à supprimer.');
                return Command::SUCCESS;
            }

            $this->info("Notifications à supprimer : {$count}");

            if ($dryRun) {
                // Afficher quelques exemples
                $examples = $query->take(5)->get();
                $this->table(
                    ['ID', 'Type', 'Titre', 'Date de création'],
                    $examples->map(function ($notification) {
                        return [
                            $notification->id,
                            $notification->type,
                            \Str::limit($notification->title, 50),
                            $notification->created_at->format('d/m/Y H:i')
                        ];
                    })
                );

                if ($count > 5) {
                    $this->info("... et " . ($count - 5) . " autres notifications");
                }

                return Command::SUCCESS;
            }

            // Confirmation si pas --force
            if (!$force) {
                if (!$this->confirm("Êtes-vous sûr de vouloir supprimer {$count} notifications ?")) {
                    $this->info('Opération annulée.');
                    return Command::SUCCESS;
                }
            }

            // Effectuer le nettoyage
            $deletedCount = $notificationService->cleanup($days);

            $this->info("Nettoyage terminé : {$deletedCount} notifications supprimées.");
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Erreur lors du nettoyage : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

// =====================================

// Fichier: app/Console/Commands/NotificationStats.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NotificationService;

class NotificationStats extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'notifications:stats {--format=table : Format de sortie (table, json)}';

    /**
     * The console command description.
     */
    protected $description = 'Afficher les statistiques des notifications';

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $notificationService): int
    {
        $format = $this->option('format');

        try {
            $stats = $notificationService->getStatistics();

            if ($format === 'json') {
                $this->line(json_encode($stats, JSON_PRETTY_PRINT));
                return Command::SUCCESS;
            }

            // Affichage en tableau
            $this->info('=== STATISTIQUES DES NOTIFICATIONS ===');
            $this->newLine();

            // Compteurs généraux
            $this->info('📊 Compteurs généraux :');
            $this->table(
                ['Métrique', 'Valeur'],
                [
                    ['Total', $stats['counters']['total']],
                    ['Non lues', $stats['counters']['unread']],
                    ['Importantes', $stats['counters']['important']],
                    ['Critiques', $stats['counters']['critical'] ?? 0],
                    ['Aujourd\'hui', $stats['counters']['today']],
                ]
            );

            $this->newLine();

            // Répartition par type
            if (!empty($stats['by_type'])) {
                $this->info('📋 Répartition par type :');
                $typeData = [];
                foreach ($stats['by_type'] as $type => $count) {
                    $typeData[] = [$type, $count, round(($count / $stats['counters']['total']) * 100, 1) . '%'];
                }
                $this->table(['Type', 'Nombre', 'Pourcentage'], $typeData);
                $this->newLine();
            }

            // Répartition par priorité
            if (!empty($stats['by_priority'])) {
                $this->info('⚡ Répartition par priorité :');
                $priorityData = [];
                foreach ($stats['by_priority'] as $priority => $count) {
                    $priorityData[] = [$priority, $count, round(($count / $stats['counters']['total']) * 100, 1) . '%'];
                }
                $this->table(['Priorité', 'Nombre', 'Pourcentage'], $priorityData);
                $this->newLine();
            }

            // Tendances
            $this->info('📈 Tendances :');
            $this->table(
                ['Période', 'Nombre'],
                [
                    ['Cette semaine', $stats['trends']['this_week']],
                    ['Semaine dernière', $stats['trends']['last_week']],
                    ['Ce mois', $stats['trends']['this_month']],
                ]
            );

            $this->newLine();

            // Taux de lecture
            $this->info("📖 Taux de lecture : {$stats['read_rate']}%");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Erreur lors de la récupération des statistiques : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

// =====================================

// Fichier: app/Console/Commands/CreateTestNotifications.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NotificationService;

class CreateTestNotifications extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'notifications:test 
                            {--count=10 : Nombre de notifications à créer}
                            {--type= : Type spécifique de notification}
                            {--priority= : Priorité spécifique}';

    /**
     * The console command description.
     */
    protected $description = 'Créer des notifications de test (développement uniquement)';

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $notificationService): int
    {
        if (!app()->environment('local')) {
            $this->error('Cette commande n\'est disponible qu\'en environnement de développement.');
            return Command::FAILURE;
        }

        $count = (int) $this->option('count');
        $type = $this->option('type');
        $priority = $this->option('priority');

        $this->info("Création de {$count} notifications de test...");

        try {
            if ($type || $priority) {
                // Création ciblée
                for ($i = 1; $i <= $count; $i++) {
                    $notificationService->create(
                        $type ?? 'system',
                        "Notification de test #{$i}",
                        "Ceci est une notification de test générée automatiquement.",
                        [
                            'priority' => $priority ?? 'medium',
                            'data' => ['test' => true, 'index' => $i]
                        ]
                    );
                }
            } else {
                // Création aléatoire
                $notifications = $notificationService->createTestNotifications($count);
            }

            $this->info("✅ {$count} notifications de test créées avec succès.");

            // Afficher les types disponibles
            $this->newLine();
            $this->info('Types disponibles : ' . implode(', ', array_keys(NotificationService::getAvailableTypes())));
            $this->info('Priorités disponibles : ' . implode(', ', array_keys(NotificationService::getAvailablePriorities())));

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Erreur lors de la création des notifications : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

// =====================================

// Fichier: app/Console/Commands/SendNotificationReport.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Mail;

class SendNotificationReport extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'notifications:report 
                            {--email= : Adresse email pour envoyer le rapport}
                            {--format=text : Format du rapport (text, html)}';

    /**
     * The console command description.
     */
    protected $description = 'Générer et envoyer un rapport quotidien des notifications';

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $notificationService): int
    {
        $email = $this->option('email');
        $format = $this->option('format');

        try {
            $stats = $notificationService->getStatistics();
            $recentNotifications = $notificationService->getRecentNotifications(20);

            $report = $this->generateReport($stats, $recentNotifications, $format);

            if ($email) {
                // Envoyer par email (vous devrez créer la classe Mail correspondante)
                $this->info("Envoi du rapport à {$email}...");
                // Mail::to($email)->send(new NotificationReport($report));
                $this->info('Rapport envoyé avec succès.');
            } else {
                // Afficher dans la console
                $this->line($report);
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Erreur lors de la génération du rapport : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function generateReport(array $stats, array $notifications, string $format): string
    {
        $date = now()->format('d/m/Y');
        
        if ($format === 'html') {
            return $this->generateHtmlReport($stats, $notifications, $date);
        }
        
        return $this->generateTextReport($stats, $notifications, $date);
    }

    private function generateTextReport(array $stats, array $notifications, string $date): string
    {
        $report = "=== RAPPORT QUOTIDIEN DES NOTIFICATIONS - {$date} ===\n\n";
        
        $report .= "RÉSUMÉ :\n";
        $report .= "- Total : {$stats['counters']['total']}\n";
        $report .= "- Non lues : {$stats['counters']['unread']}\n";
        $report .= "- Importantes : {$stats['counters']['important']}\n";
        $report .= "- Aujourd'hui : {$stats['counters']['today']}\n";
        $report .= "- Taux de lecture : {$stats['read_rate']}%\n\n";

        $report .= "NOTIFICATIONS RÉCENTES :\n";
        foreach (array_slice($notifications, 0, 10) as $notification) {
            $status = $notification['read_at'] ? '[LUE]' : '[NON LUE]';
            $report .= "- {$status} {$notification['title']} ({$notification['priority']})\n";
        }

        return $report;
    }

    private function generateHtmlReport(array $stats, array $notifications, string $date): string
    {
        $html = "<h1>Rapport quotidien des notifications - {$date}</h1>";
        
        $html .= "<h2>Résumé</h2>";
        $html .= "<ul>";
        $html .= "<li>Total : {$stats['counters']['total']}</li>";
        $html .= "<li>Non lues : {$stats['counters']['unread']}</li>";
        $html .= "<li>Importantes : {$stats['counters']['important']}</li>";
        $html .= "<li>Aujourd'hui : {$stats['counters']['today']}</li>";
        $html .= "<li>Taux de lecture : {$stats['read_rate']}%</li>";
        $html .= "</ul>";

        $html .= "<h2>Notifications récentes</h2>";
        $html .= "<table border='1'>";
        $html .= "<tr><th>Statut</th><th>Titre</th><th>Priorité</th><th>Date</th></tr>";
        
        foreach (array_slice($notifications, 0, 10) as $notification) {
            $status = $notification['read_at'] ? 'Lue' : 'Non lue';
            $html .= "<tr>";
            $html .= "<td>{$status}</td>";
            $html .= "<td>{$notification['title']}</td>";
            $html .= "<td>{$notification['priority']}</td>";
            $html .= "<td>{$notification['created_at']}</td>";
            $html .= "</tr>";
        }
        
        $html .= "</table>";

        return $html;
    }
}