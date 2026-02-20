<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ManagerController extends Controller
{
    public function index()
    {
        $admin = auth('admin')->user();

        // Récupérer les managers créés par cet admin
        $managers = Admin::where('role', Admin::ROLE_MANAGER)
            ->where('created_by', $admin->id)
            ->latest()
            ->paginate(10);

        return view('admin.managers.index', compact('managers', 'admin'));
    }

    public function create()
    {
        $admin = auth('admin')->user();

        // Vérifier si l'admin peut créer plus de managers
        $managerCount = Admin::where('role', Admin::ROLE_MANAGER)->where('created_by', $admin->id)->count();
        if ($managerCount >= $admin->max_managers) {
            return redirect()->route('admin.managers.index')
                ->with('error', 'Vous avez atteint le nombre maximum de managers autorisés (' . $admin->max_managers . ').');
        }

        return view('admin.managers.create');
    }

    public function store(Request $request)
    {
        $admin = auth('admin')->user();

        // Vérifier les limites
        $managerCount = Admin::where('role', Admin::ROLE_MANAGER)->where('created_by', $admin->id)->count();
        if ($managerCount >= $admin->max_managers) {
            return redirect()->route('admin.managers.index')
                ->with('error', 'Vous avez atteint le nombre maximum de managers autorisés.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins,email',
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
            $manager = Admin::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'role' => Admin::ROLE_MANAGER,
                'created_by' => $admin->id, // IMPORTANT: Lier le manager à l'admin qui le crée
                'shop_name' => 'manager_' . time(),
                'identifier' => $this->generateUniqueIdentifier('MGR'),
                'is_active' => $request->has('is_active') ? true : false,
            ]);

            return redirect()->route('admin.managers.index')
                ->with('success', 'Manager créé avec succès.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la création du manager: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(Admin $manager)
    {
        // Vérifier que c'est bien un manager appartenant à cet admin
        if ($manager->role !== Admin::ROLE_MANAGER || $manager->created_by !== auth('admin')->id()) {
            abort(404);
        }

        return view('admin.managers.show', compact('manager'));
    }

    public function edit(Admin $manager)
    {
        // Vérifier que c'est bien un manager appartenant à cet admin
        if ($manager->role !== Admin::ROLE_MANAGER || $manager->created_by !== auth('admin')->id()) {
            abort(404);
        }

        return view('admin.managers.edit', compact('manager'));
    }

    public function update(Request $request, Admin $manager)
    {
        // Vérifier que c'est bien un manager appartenant à cet admin
        if ($manager->role !== Admin::ROLE_MANAGER || $manager->created_by !== auth('admin')->id()) {
            abort(404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('admins', 'email')->ignore($manager->id),
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

    public function destroy(Admin $manager)
    {
        // Vérifier que c'est bien un manager appartenant à cet admin
        if ($manager->role !== Admin::ROLE_MANAGER || $manager->created_by !== auth('admin')->id()) {
            abort(404);
        }

        try {
            $manager->delete();

            return redirect()->route('admin.managers.index')
                ->with('success', 'Manager supprimé avec succès.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la suppression du manager.');
        }
    }

    public function toggleActive(Admin $manager)
    {
        // Vérifier que c'est bien un manager appartenant à cet admin
        if ($manager->role !== Admin::ROLE_MANAGER || $manager->created_by !== auth('admin')->id()) {
            abort(404);
        }

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

        $managers = Admin::where('role', Admin::ROLE_MANAGER)
            ->where('created_by', $admin->id)
            ->where('is_active', true)
            ->select('id', 'name', 'email')
            ->get();

        return response()->json($managers);
    }

    private function generateUniqueIdentifier(string $prefix): string
    {
        do {
            $identifier = $prefix . strtoupper(substr(uniqid(), -5));
        } while (Admin::where('identifier', $identifier)->exists());

        return $identifier;
    }
}
