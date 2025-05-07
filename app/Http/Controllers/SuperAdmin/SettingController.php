<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key');
        
        return view('super-admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'trial_period' => 'required|integer|min:0',
            'allow_registration' => 'required|boolean',
        ]);

        $this->updateSetting('trial_period', $request->trial_period, 'Période d\'essai en jours pour les nouveaux admins');
        $this->updateSetting('allow_registration', $request->allow_registration, 'Autoriser l\'inscription publique');

        return redirect()->route('super-admin.settings.index')
            ->with('success', 'Paramètres mis à jour avec succès.');
    }

    private function updateSetting($key, $value, $description = null)
    {
        Setting::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'description' => $description
            ]
        );
    }
}