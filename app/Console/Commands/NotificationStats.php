<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SuperAdminNotification;

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
    public function handle(): int
    {
        $format = $this->option('format');

        try {
            $stats = $this->getStatistics();

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

    private function getStatistics(): array
    {
        $now = now();
        $lastWeek = $now->copy()->subWeek();
        $lastMonth = $now->copy()->subMonth();

        $counters = [
            'total' => SuperAdminNotification::count(),
            'unread' => SuperAdminNotification::whereNull('read_at')->count(),
            'important' => SuperAdminNotification::where('priority', 'high')->count(),
            'today' => SuperAdminNotification::whereDate('created_at', today())->count(),
        ];

        return [
            'counters' => $counters,
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
            'read_rate' => $counters['total'] > 0 
                ? round((SuperAdminNotification::whereNotNull('read_at')->count() / $counters['total']) * 100, 2)
                : 0,
        ];
    }
}