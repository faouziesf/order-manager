<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SuperAdminNotification;
use App\Models\Admin;

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
    public function handle(): int
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
            $types = ['admin_registered', 'admin_expired', 'system', 'security', 'backup', 'maintenance'];
            $priorities = ['low', 'medium', 'high'];
            $admins = Admin::take(5)->get();

            for ($i = 1; $i <= $count; $i++) {
                $selectedType = $type ?? $types[array_rand($types)];
                $selectedPriority = $priority ?? $priorities[array_rand($priorities)];
                
                $notificationData = [
                    'type' => $selectedType,
                    'title' => $this->generateTitle($selectedType, $i),
                    'message' => $this->generateMessage($selectedType, $i),
                    'priority' => $selectedPriority,
                    'data' => ['test' => true, 'index' => $i]
                ];

                // Ajouter un admin aléatoire pour certains types
                if (in_array($selectedType, ['admin_registered', 'admin_expired', 'admin_expiring']) && $admins->count() > 0) {
                    $notificationData['related_admin_id'] = $admins->random()->id;
                }

                // Marquer certaines notifications comme lues aléatoirement
                if (rand(0, 2) === 0) {
                    $notificationData['read_at'] = now()->subMinutes(rand(1, 1440));
                }

                SuperAdminNotification::create($notificationData);
            }

            $this->info("✅ {$count} notifications de test créées avec succès.");

            // Afficher les types disponibles
            $this->newLine();
            $this->info('Types disponibles : ' . implode(', ', $types));
            $this->info('Priorités disponibles : ' . implode(', ', $priorities));

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Erreur lors de la création des notifications : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function generateTitle(string $type, int $index): string
    {
        $titles = [
            'admin_registered' => "Nouvel administrateur inscrit #{$index}",
            'admin_expired' => "Administrateur expiré #{$index}",
            'admin_expiring' => "Administrateur expire bientôt #{$index}",
            'system' => "Mise à jour système #{$index}",
            'security' => "Alerte de sécurité #{$index}",
            'backup' => "Sauvegarde automatique #{$index}",
            'maintenance' => "Maintenance programmée #{$index}",
        ];

        return $titles[$type] ?? "Notification de test #{$index}";
    }

    private function generateMessage(string $type, int $index): string
    {
        $messages = [
            'admin_registered' => "Un nouvel administrateur s'est inscrit sur la plateforme avec succès.",
            'admin_expired' => "Un compte administrateur a expiré et nécessite un renouvellement.",
            'admin_expiring' => "Un compte administrateur va expirer dans les prochains jours.",
            'system' => "Une mise à jour système a été installée avec succès.",
            'security' => "Une activité suspecte a été détectée et nécessite votre attention.",
            'backup' => "La sauvegarde automatique des données a été réalisée avec succès.",
            'maintenance' => "Une maintenance système est programmée prochainement.",
        ];

        return $messages[$type] ?? "Ceci est une notification de test générée automatiquement.";
    }
}