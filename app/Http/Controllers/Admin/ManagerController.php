<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Manager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ManagerController extends Controller
{
    public function index()
    {
        $admin = auth('admin')->user();
        $managers = $admin->managers()->with('employees')->latest()->paginate(10);
        
        return view('admin.managers.index', compact('managers', 'admin'));
    }

    public function create()
    {
        $admin = auth('admin')->user();
        
        // Vérifier si l'admin peut créer plus de managers
        if ($admin->managers()->count() >= $admin->max_managers) {
            return redirect()->route('admin.managers.index')
                ->with('error', 'Vous avez atteint le nombre maximum de managers autorisés (' . $admin->max_managers . ').');
        }
        
        return view('admin.managers.create');
    }

    public function store(Request $request)
    {
        $admin = auth('admin')->user();
        
        // Vérifier les limites
        if ($admin->managers()->count() >= $admin->max_managers) {
            return redirect()->route('admin.managers.index')
                ->with('error', 'Vous avez atteint le nombre maximum de managers autorisés.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:managers,email|unique:admins,email|unique:employees,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $manager = Manager::create([
                'admin_id' => $admin->id,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'is_active' => $request->has('is_active') ? true : false,
            ]);

            return redirect()->route('admin.managers.index')
                ->with('success', 'Manager créé avec succès.');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la création du manager.')
                ->withInput();
        }
    }

    public function show(Manager $manager)
    {
        $this->authorize('view', $manager);
        
        $manager->load(['employees', 'admin']);
        
        return view('admin.managers.show', compact('manager'));
    }

    public function edit(Manager $manager)
    {
        $this->authorize('update', $manager);
        
        return view('admin.managers.edit', compact('manager'));
    }

    public function update(Request $request, Manager $manager)
    {
        $this->authorize('update', $manager);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('managers', 'email')->ignore($manager->id),
                Rule::unique('admins', 'email'),
                Rule::unique('employees', 'email'),
            ],
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'is_active' => $request->has('is_active') ? true : false,
            ];

            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $manager->update($updateData);

            return redirect()->route('admin.managers.index')
                ->with('success', 'Manager mis à jour avec succès.');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la mise à jour du manager.')
                ->withInput();
        }
    }

    public function destroy(Manager $manager)
    {
        $this->authorize('delete', $manager);

        try {
            $manager->delete();
            
            return redirect()->route('admin.managers.index')
                ->with('success', 'Manager supprimé avec succès.');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la suppression du manager.');
        }
    }

    public function toggleActive(Manager $manager)
    {
        $this->authorize('update', $manager);

        try {
            $manager->update(['is_active' => !$manager->is_active]);
            
            $status = $manager->is_active ? 'activé' : 'désactivé';
            
            return redirect()->back()
                ->with('success', "Manager {$status} avec succès.");
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors du changement de statut.');
        }
    }

    public function getManagersForAdmin()
    {
        $admin = auth('admin')->user();
        $managers = $admin->managers()->where('is_active', true)->get(['id', 'name']);
        
        return response()->json($managers);
    }
}