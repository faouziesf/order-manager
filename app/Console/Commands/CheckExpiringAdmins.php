<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NotificationService;
use App\Models\Admin;

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
        $expiringAdmins = Admin::where('is_active', true)
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [now(), now()->addDays(7)])
            ->get();

        $expiredAdmins = Admin::where('is_active', true)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<', now())
            ->get();

        if ($expiringAdmins->count() > 0 || $expiredAdmins->count() > 0) {
            $this->table(
                ['Admin', 'Email', 'Boutique', 'Date d\'expiration', 'Jours restants', 'Statut'],
                $expiringAdmins->map(function ($admin) {
                    return [
                        $admin->name,
                        $admin->email,
                        $admin->shop_name,
                        $admin->expiry_date->format('d/m/Y'),
                        now()->diffInDays($admin->expiry_date),
                        '⚠️ Expire bientôt'
                    ];
                })->concat(
                    $expiredAdmins->map(function ($admin) {
                        return [
                            $admin->name,
                            $admin->email,
                            $admin->shop_name,
                            $admin->expiry_date->format('d/m/Y'),
                            '❌ ' . now()->diffInDays($admin->expiry_date) . ' jour(s) de retard',
                            '🚫 Expiré'
                        ];
                    })
                )
            );
        }

        $this->newLine();
        $this->info('📊 Résumé :');
        $this->info('- Administrateurs qui expirent bientôt : ' . $expiringAdmins->count());
        $this->info('- Administrateurs expirés : ' . $expiredAdmins->count());
        
        if ($expiringAdmins->count() > 0) {
            $this->warn('⚠️  Des administrateurs arrivent à expiration !');
        }
        
        if ($expiredAdmins->count() > 0) {
            $this->error('🚫 Des administrateurs ont expiré et nécessitent une action !');
        }
        
        if ($expiringAdmins->count() === 0 && $expiredAdmins->count() === 0) {
            $this->info('✅ Tous les administrateurs sont à jour.');
        }
    }
}