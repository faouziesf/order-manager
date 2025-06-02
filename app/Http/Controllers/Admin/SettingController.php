<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SettingController extends Controller
{
    /**
     * Afficher la page des paramètres
     */
    public function index()
    {
        // Récupérer tous les paramètres avec des valeurs par défaut
        $settings = [
            // Paramètres de la file standard
            'standard_max_daily_attempts' => AdminSetting::get('standard_max_daily_attempts', 3),
            'standard_delay_hours' => AdminSetting::get('standard_delay_hours', 2.5),
            'standard_max_total_attempts' => AdminSetting::get('standard_max_total_attempts', 9),
            
            // Paramètres de la file datée
            'dated_max_daily_attempts' => AdminSetting::get('dated_max_daily_attempts', 2),
            'dated_delay_hours' => AdminSetting::get('dated_delay_hours', 3.5),
            'dated_max_total_attempts' => AdminSetting::get('dated_max_total_attempts', 5),
            
            // Paramètres de la file ancienne
            'old_max_daily_attempts' => AdminSetting::get('old_max_daily_attempts', 2),
            'old_delay_hours' => AdminSetting::get('old_delay_hours', 6),
            'old_max_total_attempts' => AdminSetting::get('old_max_total_attempts', 0),
            
            // NOUVEAUX: Paramètres de la file de retour en stock
            'restock_max_daily_attempts' => AdminSetting::get('restock_max_daily_attempts', 3),
            'restock_delay_hours' => AdminSetting::get('restock_delay_hours', 4),
            'restock_max_total_attempts' => AdminSetting::get('restock_max_total_attempts', 10),
            
            // NOUVEAUX: Paramètres de l'interface d'examen
            'examination_auto_refresh_interval' => AdminSetting::get('examination_auto_refresh_interval', 120),
            'examination_max_orders_per_page' => AdminSetting::get('examination_max_orders_per_page', 50),
            
            // NOUVEAUX: Paramètres des commandes suspendues
            'suspended_auto_check_interval' => AdminSetting::get('suspended_auto_check_interval', 300),
            'suspended_max_orders_per_page' => AdminSetting::get('suspended_max_orders_per_page', 30),
            
            // NOUVEAUX: Paramètres de suspension automatique
            'auto_suspend_on_stock_issue' => AdminSetting::get('auto_suspend_on_stock_issue', 1),
            'auto_suspend_threshold_days' => AdminSetting::get('auto_suspend_threshold_days', 7),
            'restock_notification_enabled' => AdminSetting::get('restock_notification_enabled', 1),
            
            // NOUVEAUX: Paramètres de performance
            'stock_check_cache_duration' => AdminSetting::get('stock_check_cache_duration', 300),
            'bulk_action_max_orders' => AdminSetting::get('bulk_action_max_orders', 100),
        ];
        
        return view('admin.settings.index', compact('settings'));
    }
    
    /**
     * Enregistrer les paramètres
     */
    public function store(Request $request)
    {
        try {
            // Validation des paramètres
            $validated = $request->validate([
                // File standard
                'standard_max_daily_attempts' => 'required|integer|min:1|max:10',
                'standard_delay_hours' => 'required|numeric|min:0.5|max:24',
                'standard_max_total_attempts' => 'required|integer|min:1|max:50',
                
                // File datée
                'dated_max_daily_attempts' => 'required|integer|min:1|max:10',
                'dated_delay_hours' => 'required|numeric|min:0.5|max:24',
                'dated_max_total_attempts' => 'required|integer|min:1|max:20',
                
                // File ancienne
                'old_max_daily_attempts' => 'required|integer|min:1|max:10',
                'old_delay_hours' => 'required|numeric|min:1|max:48',
                'old_max_total_attempts' => 'required|integer|min:0|max:30',
                
                // NOUVEAUX: File retour en stock
                'restock_max_daily_attempts' => 'required|integer|min:1|max:10',
                'restock_delay_hours' => 'required|numeric|min:0.5|max:24',
                'restock_max_total_attempts' => 'required|integer|min:1|max:20',
                
                // NOUVEAUX: Interface d'examen
                'examination_auto_refresh_interval' => 'required|integer|min:30|max:600',
                'examination_max_orders_per_page' => 'required|integer|min:10|max:100',
                
                // NOUVEAUX: Commandes suspendues
                'suspended_auto_check_interval' => 'required|integer|min:60|max:1800',
                'suspended_max_orders_per_page' => 'required|integer|min:10|max:100',
                
                // NOUVEAUX: Suspension automatique
                'auto_suspend_on_stock_issue' => 'required|boolean',
                'auto_suspend_threshold_days' => 'required|integer|min:1|max:30',
                'restock_notification_enabled' => 'required|boolean',
                
                // NOUVEAUX: Performance
                'stock_check_cache_duration' => 'required|integer|min:60|max:3600',
                'bulk_action_max_orders' => 'required|integer|min:10|max:500',
            ], [
                // Messages d'erreur personnalisés
                'standard_max_daily_attempts.required' => 'Le nombre maximum de tentatives quotidiennes pour la file standard est requis.',
                'standard_max_daily_attempts.min' => 'Le nombre minimum de tentatives quotidiennes doit être de 1.',
                'standard_max_daily_attempts.max' => 'Le nombre maximum de tentatives quotidiennes ne peut dépasser 10.',
                
                'standard_delay_hours.required' => 'Le délai entre les tentatives pour la file standard est requis.',
                'standard_delay_hours.min' => 'Le délai minimum entre les tentatives doit être de 0.5 heure.',
                'standard_delay_hours.max' => 'Le délai maximum entre les tentatives ne peut dépasser 24 heures.',
                
                'restock_max_daily_attempts.required' => 'Le nombre maximum de tentatives quotidiennes pour le retour en stock est requis.',
                'restock_delay_hours.required' => 'Le délai entre les tentatives pour le retour en stock est requis.',
                
                'examination_auto_refresh_interval.min' => 'L\'intervalle de rafraîchissement doit être d\'au moins 30 secondes.',
                'examination_auto_refresh_interval.max' => 'L\'intervalle de rafraîchissement ne peut dépasser 10 minutes.',
                
                'bulk_action_max_orders.min' => 'Le nombre minimum d\'ordres par action groupée doit être de 10.',
                'bulk_action_max_orders.max' => 'Le nombre maximum d\'ordres par action groupée ne peut dépasser 500.',
            ]);
            
            // Sauvegarder tous les paramètres
            foreach ($validated as $key => $value) {
                AdminSetting::set($key, $value);
            }
            
            Log::info('Paramètres admin mis à jour', [
                'admin_id' => auth('admin')->id(),
                'settings_updated' => array_keys($validated)
            ]);
            
            return redirect()->back()->with('success', 'Paramètres sauvegardés avec succès !');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Veuillez corriger les erreurs dans le formulaire.');
            
        } catch (\Exception $e) {
            Log::error('Erreur lors de la sauvegarde des paramètres: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Une erreur est survenue lors de la sauvegarde des paramètres.');
        }
    }
    
    /**
     * Réinitialiser les paramètres aux valeurs par défaut
     */
    public function reset()
    {
        try {
            // Valeurs par défaut
            $defaultSettings = [
                // File standard
                'standard_max_daily_attempts' => 3,
                'standard_delay_hours' => 2.5,
                'standard_max_total_attempts' => 9,
                
                // File datée
                'dated_max_daily_attempts' => 2,
                'dated_delay_hours' => 3.5,
                'dated_max_total_attempts' => 5,
                
                // File ancienne
                'old_max_daily_attempts' => 2,
                'old_delay_hours' => 6,
                'old_max_total_attempts' => 0,
                
                // File retour en stock
                'restock_max_daily_attempts' => 3,
                'restock_delay_hours' => 4,
                'restock_max_total_attempts' => 10,
                
                // Interface d'examen
                'examination_auto_refresh_interval' => 120,
                'examination_max_orders_per_page' => 50,
                
                // Commandes suspendues
                'suspended_auto_check_interval' => 300,
                'suspended_max_orders_per_page' => 30,
                
                // Suspension automatique
                'auto_suspend_on_stock_issue' => 1,
                'auto_suspend_threshold_days' => 7,
                'restock_notification_enabled' => 1,
                
                // Performance
                'stock_check_cache_duration' => 300,
                'bulk_action_max_orders' => 100,
            ];
            
            foreach ($defaultSettings as $key => $value) {
                AdminSetting::set($key, $value);
            }
            
            Log::info('Paramètres admin réinitialisés', [
                'admin_id' => auth('admin')->id()
            ]);
            
            return redirect()->back()->with('success', 'Paramètres réinitialisés aux valeurs par défaut !');
            
        } catch (\Exception $e) {
            Log::error('Erreur lors de la réinitialisation des paramètres: ' . $e->getMessage());
            
            return redirect()->back()->with('error', 'Une erreur est survenue lors de la réinitialisation.');
        }
    }
    
    /**
     * Exporter les paramètres au format JSON
     */
    public function export()
    {
        try {
            $settings = AdminSetting::all()->pluck('value', 'key')->toArray();
            
            $filename = 'admin_settings_' . now()->format('Y-m-d_H-i-s') . '.json';
            
            return response()->json($settings)
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
            
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'export des paramètres: ' . $e->getMessage());
            
            return redirect()->back()->with('error', 'Erreur lors de l\'export des paramètres.');
        }
    }
    
    /**
     * Importer des paramètres depuis un fichier JSON
     */
    public function import(Request $request)
    {
        try {
            $request->validate([
                'settings_file' => 'required|file|mimes:json|max:1024' // Max 1MB
            ]);
            
            $file = $request->file('settings_file');
            $content = file_get_contents($file->getRealPath());
            $settings = json_decode($content, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return redirect()->back()->with('error', 'Fichier JSON invalide.');
            }
            
            $importedCount = 0;
            
            foreach ($settings as $key => $value) {
                // Vérifier que la clé existe dans les paramètres valides
                if (AdminSetting::where('key', $key)->exists()) {
                    AdminSetting::set($key, $value);
                    $importedCount++;
                }
            }
            
            Log::info('Paramètres admin importés', [
                'admin_id' => auth('admin')->id(),
                'imported_count' => $importedCount
            ]);
            
            return redirect()->back()->with('success', "Paramètres importés avec succès ! ({$importedCount} paramètres mis à jour)");
            
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'import des paramètres: ' . $e->getMessage());
            
            return redirect()->back()->with('error', 'Erreur lors de l\'import des paramètres.');
        }
    }
    
    /**
     * API pour obtenir un paramètre spécifique
     */
    public function getSetting($key)
    {
        try {
            $value = AdminSetting::get($key);
            
            if ($value === null) {
                return response()->json(['error' => 'Paramètre non trouvé'], 404);
            }
            
            return response()->json([
                'key' => $key,
                'value' => $value
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération du paramètre: ' . $e->getMessage());
            
            return response()->json(['error' => 'Erreur serveur'], 500);
        }
    }
    
    /**
     * API pour mettre à jour un paramètre spécifique
     */
    public function setSetting(Request $request, $key)
    {
        try {
            $request->validate([
                'value' => 'required'
            ]);
            
            AdminSetting::set($key, $request->value);
            
            Log::info('Paramètre admin mis à jour via API', [
                'admin_id' => auth('admin')->id(),
                'key' => $key,
                'value' => $request->value
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Paramètre mis à jour avec succès'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour du paramètre: ' . $e->getMessage());
            
            return response()->json(['error' => 'Erreur serveur'], 500);
        }
    }
    
    /**
     * Obtenir les statistiques d'utilisation des paramètres
     */
    public function getUsageStats()
    {
        try {
            // Statistiques basiques
            $stats = [
                'total_settings' => AdminSetting::count(),
                'last_updated' => AdminSetting::latest('updated_at')->first()?->updated_at,
                'settings_by_category' => [
                    'queue_standard' => AdminSetting::where('key', 'like', 'standard_%')->count(),
                    'queue_dated' => AdminSetting::where('key', 'like', 'dated_%')->count(),
                    'queue_old' => AdminSetting::where('key', 'like', 'old_%')->count(),
                    'queue_restock' => AdminSetting::where('key', 'like', 'restock_%')->count(),
                    'examination' => AdminSetting::where('key', 'like', 'examination_%')->count(),
                    'suspended' => AdminSetting::where('key', 'like', 'suspended_%')->count(),
                    'performance' => AdminSetting::where('key', 'like', '%cache%')
                        ->orWhere('key', 'like', '%bulk%')->count(),
                ]
            ];
            
            return response()->json($stats);
            
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des statistiques: ' . $e->getMessage());
            
            return response()->json(['error' => 'Erreur serveur'], 500);
        }
    }
}