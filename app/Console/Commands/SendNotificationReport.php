<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SuperAdminNotification;

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
    public function handle(): int
    {
        $email = $this->option('email');
        $format = $this->option('format');

        try {
            $stats = $this->getStatistics();
            $recentNotifications = $this->getRecentNotifications(20);

            $report = $this->generateReport($stats, $recentNotifications, $format);

            if ($email) {
                // Pour l'instant, on simule l'envoi d'email
                $this->info("Envoi du rapport à {$email}...");
                // Ici vous pourriez intégrer avec votre service d'email
                // Mail::to($email)->send(new NotificationReport($report));
                $this->info('Rapport envoyé avec succès (simulation).');
                
                // Sauvegarder le rapport dans les logs
                \Log::info("Rapport de notifications envoyé à {$email}", [
                    'stats' => $stats,
                    'notifications_count' => count($recentNotifications)
                ]);
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
        $report .= "- Total : {$stats['total']}\n";
        $report .= "- Non lues : {$stats['unread']}\n";
        $report .= "- Importantes : {$stats['important']}\n";
        $report .= "- Aujourd'hui : {$stats['today']}\n";
        $report .= "- Taux de lecture : {$stats['read_rate']}%\n\n";

        $report .= "NOTIFICATIONS RÉCENTES :\n";
        foreach (array_slice($notifications, 0, 10) as $notification) {
            $status = $notification['read_at'] ? '[LUE]' : '[NON LUE]';
            $report .= "- {$status} {$notification['title']} ({$notification['priority']})\n";
        }

        $report .= "\nRÉPARTITION PAR TYPE :\n";
        foreach ($stats['by_type'] as $type => $count) {
            $report .= "- {$type}: {$count}\n";
        }

        $report .= "\nRÉPARTITION PAR PRIORITÉ :\n";
        foreach ($stats['by_priority'] as $priority => $count) {
            $report .= "- {$priority}: {$count}\n";
        }

        return $report;
    }

    private function generateHtmlReport(array $stats, array $notifications, string $date): string
    {
        $html = "<h1>Rapport quotidien des notifications - {$date}</h1>";
        
        $html .= "<h2>Résumé</h2>";
        $html .= "<ul>";
        $html .= "<li>Total : {$stats['total']}</li>";
        $html .= "<li>Non lues : {$stats['unread']}</li>";
        $html .= "<li>Importantes : {$stats['important']}</li>";
        $html .= "<li>Aujourd'hui : {$stats['today']}</li>";
        $html .= "<li>Taux de lecture : {$stats['read_rate']}%</li>";
        $html .= "</ul>";

        $html .= "<h2>Notifications récentes</h2>";
        $html .= "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        $html .= "<tr><th>Statut</th><th>Titre</th><th>Priorité</th><th>Date</th></tr>";
        
        foreach (array_slice($notifications, 0, 10) as $notification) {
            $status = $notification['read_at'] ? '✅ Lue' : '📬 Non lue';
            $html .= "<tr>";
            $html .= "<td>{$status}</td>";
            $html .= "<td>{$notification['title']}</td>";
            $html .= "<td>{$notification['priority']}</td>";
            $html .= "<td>{$notification['created_at']}</td>";
            $html .= "</tr>";
        }
        
        $html .= "</table>";

        $html .= "<h2>Répartition par type</h2>";
        $html .= "<ul>";
        foreach ($stats['by_type'] as $type => $count) {
            $html .= "<li>{$type}: {$count}</li>";
        }
        $html .= "</ul>";

        return $html;
    }

    private function getStatistics(): array
    {
        $total = SuperAdminNotification::count();
        
        return [
            'total' => $total,
            'unread' => SuperAdminNotification::whereNull('read_at')->count(),
            'important' => SuperAdminNotification::where('priority', 'high')->count(),
            'today' => SuperAdminNotification::whereDate('created_at', today())->count(),
            'read_rate' => $total > 0 
                ? round((SuperAdminNotification::whereNotNull('read_at')->count() / $total) * 100, 2)
                : 0,
            'by_type' => SuperAdminNotification::selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray(),
            'by_priority' => SuperAdminNotification::selectRaw('priority, COUNT(*) as count')
                ->groupBy('priority')
                ->pluck('count', 'priority')
                ->toArray()
        ];
    }

    private function getRecentNotifications(int $limit): array
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
                    'created_at' => $notification->created_at->format('d/m/Y H:i'),
                    'admin' => $notification->admin ? $notification->admin->name : null
                ];
            })
            ->toArray();
    }
}