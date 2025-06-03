<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminSettingsService;
use App\Models\AdminSetting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class SettingController extends Controller
{
    private AdminSettingsService $settingsService;

    public function __construct(AdminSettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
        $this->middleware('auth:admin');
    }

    /**
     * Afficher la page des paramètres de l'admin connecté
     */
    public function index()
    {
        $adminId = auth('admin')->id();
        
        // Récupérer tous les paramètres avec des valeurs par défaut
        $settings = [
            // Paramètres de la file standard
            'standard_max_daily_attempts' => AdminSetting::getForAdmin($adminId, 'standard_max_daily_attempts', 3),
            'standard_delay_hours' => AdminSetting::getForAdmin($adminId, 'standard_delay_hours', 2.5),
            'standard_max_total_attempts' => AdminSetting::getForAdmin($adminId, 'standard_max_total_attempts', 9),
            
            // Paramètres de la file datée
            'dated_max_daily_attempts' => AdminSetting::getForAdmin($adminId, 'dated_max_daily_attempts', 2),
            'dated_delay_hours' => AdminSetting::getForAdmin($adminId, 'dated_delay_hours', 3.5),
            'dated_max_total_attempts' => AdminSetting::getForAdmin($adminId, 'dated_max_total_attempts', 5),
            
            // Paramètres de la file ancienne
            'old_max_daily_attempts' => AdminSetting::getForAdmin($adminId, 'old_max_daily_attempts', 2),
            'old_delay_hours' => AdminSetting::getForAdmin($adminId, 'old_delay_hours', 6),
            'old_max_total_attempts' => AdminSetting::getForAdmin($adminId, 'old_max_total_attempts', 0),
            
            // Paramètres de la file de retour en stock
            'restock_max_daily_attempts' => AdminSetting::getForAdmin($adminId, 'restock_max_daily_attempts', 3),
            'restock_delay_hours' => AdminSetting::getForAdmin($adminId, 'restock_delay_hours', 4),
            'restock_max_total_attempts' => AdminSetting::getForAdmin($adminId, 'restock_max_total_attempts', 10),
            
            // Paramètres de l'interface d'examen
            'examination_auto_refresh_interval' => AdminSetting::getForAdmin($adminId, 'examination_auto_refresh_interval', 120),
            'examination_max_orders_per_page' => AdminSetting::getForAdmin($adminId, 'examination_max_orders_per_page', 50),
            
            // Paramètres des commandes suspendues
            'suspended_auto_check_interval' => AdminSetting::getForAdmin($adminId, 'suspended_auto_check_interval', 300),
            'suspended_max_orders_per_page' => AdminSetting::getForAdmin($adminId, 'suspended_max_orders_per_page', 30),
            
            // Paramètres de suspension automatique
            'auto_suspend_on_stock_issue' => AdminSetting::getForAdmin($adminId, 'auto_suspend_on_stock_issue', 1),
            'auto_suspend_threshold_days' => AdminSetting::getForAdmin($adminId, 'auto_suspend_threshold_days', 7),
            'restock_notification_enabled' => AdminSetting::getForAdmin($adminId, 'restock_notification_enabled', 1),
            
            // Paramètres de performance
            'stock_check_cache_duration' => AdminSetting::getForAdmin($adminId, 'stock_check_cache_duration', 300),
            'bulk_action_max_orders' => AdminSetting::getForAdmin($adminId, 'bulk_action_max_orders', 100),
        ];
        
        return view('admin.settings.index', compact('settings'));
    }
    
    /**
     * Enregistrer les paramètres de l'admin connecté
     */
    public function store(Request $request)
    {
        try {
            $adminId = auth('admin')->id();
            
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
                
                // File retour en stock
                'restock_max_daily_attempts' => 'required|integer|min:1|max:10',
                'restock_delay_hours' => 'required|numeric|min:0.5|max:24',
                'restock_max_total_attempts' => 'required|integer|min:1|max:20',
                
                // Interface d'examen
                'examination_auto_refresh_interval' => 'required|integer|min:30|max:600',
                'examination_max_orders_per_page' => 'required|integer|min:10|max:100',
                
                // Commandes suspendues
                'suspended_auto_check_interval' => 'required|integer|min:60|max:1800',
                'suspended_max_orders_per_page' => 'required|integer|min:10|max:100',
                
                // Suspension automatique
                'auto_suspend_on_stock_issue' => 'required|boolean',
                'auto_suspend_threshold_days' => 'required|integer|min:1|max:30',
                'restock_notification_enabled' => 'required|boolean',
                
                // Performance
                'stock_check_cache_duration' => 'required|integer|min:60|max:3600',
                'bulk_action_max_orders' => 'required|integer|min:10|max:500',
            ], [
                // Messages d'erreur personnalisés (mêmes que l'original)
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
            
            // Sauvegarder tous les paramètres pour cet admin
            foreach ($validated as $key => $value) {
                AdminSetting::setForAdmin($adminId, $key, $value);
            }
            
            Log::info('Paramètres admin mis à jour', [
                'admin_id' => $adminId,
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
     * Réinitialiser les paramètres aux valeurs par défaut pour l'admin connecté
     */
    public function reset()
    {
        try {
            $adminId = auth('admin')->id();
            $this->settingsService->initializeDefaultSettings($adminId);
            
            Log::info('Paramètres admin réinitialisés', [
                'admin_id' => $adminId
            ]);
            
            return redirect()->back()->with('success', 'Paramètres réinitialisés aux valeurs par défaut !');
            
        } catch (\Exception $e) {
            Log::error('Erreur lors de la réinitialisation des paramètres: ' . $e->getMessage());
            
            return redirect()->back()->with('error', 'Une erreur est survenue lors de la réinitialisation.');
        }
    }
    
    /**
     * Initialiser les paramètres par défaut pour l'admin connecté
     */
    public function initializeDefaults(): JsonResponse
    {
        try {
            $adminId = auth('admin')->id();
            $this->settingsService->initializeDefaultSettings($adminId);

            return response()->json([
                'success' => true,
                'message' => 'Paramètres par défaut initialisés avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'initialisation des paramètres',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API pour obtenir un paramètre spécifique de l'admin connecté
     */
    public function getSetting($key)
    {
        try {
            $adminId = auth('admin')->id();
            $value = AdminSetting::getForAdmin($adminId, $key);
            
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
     * API pour mettre à jour un paramètre spécifique de l'admin connecté
     */
    public function setSetting(Request $request, $key)
    {
        try {
            $request->validate([
                'value' => 'required'
            ]);
            
            $adminId = auth('admin')->id();
            AdminSetting::setForAdmin($adminId, $key, $request->value);
            
            Log::info('Paramètre admin mis à jour via API', [
                'admin_id' => $adminId,
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
}