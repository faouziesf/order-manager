<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SuperAdminNotification;

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
    public function handle(): int
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
            $query = SuperAdminNotification::where('created_at', '<', $cutoffDate);
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
            $deletedCount = $query->delete();

            $this->info("Nettoyage terminé : {$deletedCount} notifications supprimées.");
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Erreur lors du nettoyage : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}