<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminSetting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Affiche la page des paramètres
     */
    public function index()
    {
        // Regrouper les paramètres par type
        $standardSettings = [
            'max_daily_attempts' => AdminSetting::get('standard_max_daily_attempts', 3),
            'delay_hours' => AdminSetting::get('standard_delay_hours', 2.5),
            'max_total_attempts' => AdminSetting::get('standard_max_total_attempts', 9),
        ];
        
        $datedSettings = [
            'max_daily_attempts' => AdminSetting::get('dated_max_daily_attempts', 2),
            'delay_hours' => AdminSetting::get('dated_delay_hours', 3.5),
            'max_total_attempts' => AdminSetting::get('dated_max_total_attempts', 5),
        ];
        
        $oldSettings = [
            'max_daily_attempts' => AdminSetting::get('old_max_daily_attempts', 2),
            'delay_hours' => AdminSetting::get('old_delay_hours', 6),
            'max_total_attempts' => AdminSetting::get('old_max_total_attempts', 0),
        ];
        
        return view('admin.settings.index', compact('standardSettings', 'datedSettings', 'oldSettings'));
    }

    /**
     * Enregistre les paramètres
     */
    public function store(Request $request)
    {
        $request->validate([
            'standard_max_daily_attempts' => 'required|integer|min:1|max:10',
            'standard_delay_hours' => 'required|numeric|min:0.5|max:12',
            'standard_max_total_attempts' => 'required|integer|min:1|max:30',
            
            'dated_max_daily_attempts' => 'required|integer|min:1|max:10',
            'dated_delay_hours' => 'required|numeric|min:0.5|max:12',
            'dated_max_total_attempts' => 'required|integer|min:1|max:30',
            
            'old_max_daily_attempts' => 'required|integer|min:1|max:10',
            'old_delay_hours' => 'required|numeric|min:0.5|max:12',
            'old_max_total_attempts' => 'required|integer|min:0|max:30',
        ]);

        // Enregistrer les paramètres
        AdminSetting::set('standard_max_daily_attempts', $request->standard_max_daily_attempts);
        AdminSetting::set('standard_delay_hours', $request->standard_delay_hours);
        AdminSetting::set('standard_max_total_attempts', $request->standard_max_total_attempts);
        
        AdminSetting::set('dated_max_daily_attempts', $request->dated_max_daily_attempts);
        AdminSetting::set('dated_delay_hours', $request->dated_delay_hours);
        AdminSetting::set('dated_max_total_attempts', $request->dated_max_total_attempts);
        
        AdminSetting::set('old_max_daily_attempts', $request->old_max_daily_attempts);
        AdminSetting::set('old_delay_hours', $request->old_delay_hours);
        AdminSetting::set('old_max_total_attempts', $request->old_max_total_attempts);
        
        return redirect()->route('admin.settings.index')
            ->with('success', 'Paramètres enregistrés avec succès.');
    }
}