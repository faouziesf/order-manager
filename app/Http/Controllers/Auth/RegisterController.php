<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins',
            'password' => 'required|string|min:8|confirmed',
            'shop_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        // Générer un identifiant unique au format 4 chiffres/lettre
        do {
            $numbers = mt_rand(1000, 9999);
            $letter = strtolower(chr(rand(97, 122))); // lettre aléatoire a-z
            $identifier = $numbers . '/' . $letter;
        } while (Admin::where('identifier', $identifier)->exists());

        // Récupérer la période d'essai dans les paramètres
        $trialPeriod = Setting::where('key', 'trial_period')->first();
        $trialDays = $trialPeriod ? $trialPeriod->value : 3; // Par défaut 3 jours
        
        // Récupérer le paramètre pour autoriser l'inscription
        $allowRegistration = Setting::where('key', 'allow_registration')->first();
        $isActive = $allowRegistration && $allowRegistration->value ? true : false;
        
        try {
            // Créer l'administrateur
            Admin::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'shop_name' => $request->shop_name,
                'identifier' => $identifier,
                'expiry_date' => now()->addDays($trialDays),
                'phone' => $request->phone,
                'is_active' => $isActive,
                'max_managers' => 1, // Valeur par défaut
                'max_employees' => 2, // Valeur par défaut
            ]);

            return redirect()->route('login')->with('success', 'Votre compte a été créé avec succès. ' . 
                ($isActive ? 'Vous pouvez maintenant vous connecter.' : 'Il sera activé après vérification.'));
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la création du compte: ' . $e->getMessage());
        }
    }
}