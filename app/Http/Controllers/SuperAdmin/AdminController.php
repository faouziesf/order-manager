<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function index()
    {
        $admins = Admin::latest()->paginate(10);
        return view('super-admin.admins.index', compact('admins'));
    }

    public function create()
    {
        return view('super-admin.admins.create');
    }

    public function store(Request $request)
    {
        // Débogage pour voir les données reçues
        \Log::info('Données reçues:', $request->all());
    
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins',
            'password' => 'required|string|min:8',
            'shop_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'max_managers' => 'nullable|integer|min:0',
            'max_employees' => 'nullable|integer|min:0',
            'expiry_date' => 'nullable|date',
            'is_active' => 'boolean',
        ]);
    
        // Générer un identifiant unique au format 4 chiffres/lettre
        $identifier = $this->generateUniqueIdentifier();
    
        try {
            $admin = Admin::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => \Illuminate\Support\Facades\Hash::make($request->password),
                'shop_name' => $request->shop_name,
                'identifier' => $identifier,
                'expiry_date' => $request->expiry_date,
                'phone' => $request->phone,
                'is_active' => $request->has('is_active') ? 1 : 0,
                'max_managers' => $request->max_managers ?? 1,
                'max_employees' => $request->max_employees ?? 2,
            ]);
    
            \Log::info('Admin créé avec succès:', ['id' => $admin->id]);
    
            return redirect()->route('super-admin.admins.index')
                ->with('success', 'Administrateur créé avec succès.');
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la création de l\'admin:', ['message' => $e->getMessage()]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la création de l\'administrateur: ' . $e->getMessage());
        }
    }

    public function show(Admin $admin)
    {
        $totalManagers = $admin->managers()->count();
        $totalEmployees = $admin->employees()->count();
        
        return view('super-admin.admins.show', compact('admin', 'totalManagers', 'totalEmployees'));
    }

    public function edit(Admin $admin)
    {
        return view('super-admin.admins.edit', compact('admin'));
    }

    public function update(Request $request, Admin $admin)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins,email,' . $admin->id,
            'shop_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'max_managers' => 'nullable|integer|min:0',
            'max_employees' => 'nullable|integer|min:0',
            'expiry_date' => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'shop_name' => $request->shop_name,
            'expiry_date' => $request->expiry_date,
            'phone' => $request->phone,
            'is_active' => $request->has('is_active'),
            'max_managers' => $request->max_managers ?? 1,
            'max_employees' => $request->max_employees ?? 2,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $admin->update($data);

        return redirect()->route('super-admin.admins.index')
            ->with('success', 'Administrateur mis à jour avec succès.');
    }

    public function destroy(Admin $admin)
    {
        $admin->delete();

        return redirect()->route('super-admin.admins.index')
            ->with('success', 'Administrateur supprimé avec succès.');
    }

    public function toggleActive(Admin $admin)
    {
        $admin->update([
            'is_active' => !$admin->is_active
        ]);

        $status = $admin->is_active ? 'activé' : 'désactivé';
        
        return redirect()->back()
            ->with('success', "Administrateur {$status} avec succès.");
    }

    private function generateUniqueIdentifier()
    {
        do {
            $numbers = mt_rand(1000, 9999);
            $letter = strtolower(chr(rand(97, 122))); // lettre aléatoire a-z
            $identifier = $numbers . '/' . $letter;
        } while (Admin::where('identifier', $identifier)->exists());

        return $identifier;
    }
}