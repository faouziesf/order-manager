<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Manager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index()
    {
        $admin = auth('admin')->user();
        $employees = $admin->employees()->with(['manager'])->latest()->paginate(10);
        
        return view('admin.employees.index', compact('employees', 'admin'));
    }

    public function create()
    {
        $admin = auth('admin')->user();
        
        // Vérifier si l'admin peut créer plus d'employés
        if ($admin->employees()->count() >= $admin->max_employees) {
            return redirect()->route('admin.employees.index')
                ->with('error', 'Vous avez atteint le nombre maximum d\'employés autorisés (' . $admin->max_employees . ').');
        }
        
        $managers = $admin->managers()->where('is_active', true)->get();
        
        return view('admin.employees.create', compact('managers'));
    }

    public function store(Request $request)
    {
        $admin = auth('admin')->user();
        
        // Vérifier les limites
        if ($admin->employees()->count() >= $admin->max_employees) {
            return redirect()->route('admin.employees.index')
                ->with('error', 'Vous avez atteint le nombre maximum d\'employés autorisés.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:employees,email|unique:admins,email|unique:managers,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'manager_id' => 'nullable|exists:managers,id',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Vérifier que le manager appartient à l'admin
        if ($request->manager_id) {
            $manager = Manager::where('id', $request->manager_id)
                ->where('admin_id', $admin->id)
                ->first();
            
            if (!$manager) {
                return redirect()->back()
                    ->with('error', 'Manager sélectionné invalide.')
                    ->withInput();
            }
        }

        try {
            $employee = Employee::create([
                'admin_id' => $admin->id,
                'manager_id' => $request->manager_id,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'is_active' => $request->has('is_active') ? true : false,
            ]);

            return redirect()->route('admin.employees.index')
                ->with('success', 'Employé créé avec succès.');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la création de l\'employé.')
                ->withInput();
        }
    }

    public function show(Employee $employee)
    {
        $admin = auth('admin')->user();
        
        // Vérification manuelle d'autorisation
        if ($employee->admin_id !== $admin->id) {
            abort(403, 'Accès non autorisé à cet employé.');
        }
        
        $employee->load(['manager', 'admin']);
        
        return view('admin.employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $admin = auth('admin')->user();
        
        // Vérification manuelle d'autorisation
        if ($employee->admin_id !== $admin->id) {
            abort(403, 'Accès non autorisé à cet employé.');
        }
        
        $managers = $admin->managers()->where('is_active', true)->get();
        
        return view('admin.employees.edit', compact('employee', 'managers'));
    }

    public function update(Request $request, Employee $employee)
    {
        $admin = auth('admin')->user();
        
        // Vérification manuelle d'autorisation
        if ($employee->admin_id !== $admin->id) {
            abort(403, 'Accès non autorisé à cet employé.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('employees', 'email')->ignore($employee->id),
                Rule::unique('admins', 'email'),
                Rule::unique('managers', 'email'),
            ],
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'manager_id' => 'nullable|exists:managers,id',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Vérifier que le manager appartient à l'admin
        if ($request->manager_id) {
            $manager = Manager::where('id', $request->manager_id)
                ->where('admin_id', $admin->id)
                ->first();
            
            if (!$manager) {
                return redirect()->back()
                    ->with('error', 'Manager sélectionné invalide.')
                    ->withInput();
            }
        }

        try {
            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'manager_id' => $request->manager_id,
                'is_active' => $request->has('is_active') ? true : false,
            ];

            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $employee->update($updateData);

            return redirect()->route('admin.employees.index')
                ->with('success', 'Employé mis à jour avec succès.');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la mise à jour de l\'employé.')
                ->withInput();
        }
    }

    public function destroy(Employee $employee)
    {
        $admin = auth('admin')->user();
        
        // Vérification manuelle d'autorisation
        if ($employee->admin_id !== $admin->id) {
            abort(403, 'Accès non autorisé à cet employé.');
        }

        try {
            $employee->delete();
            
            return redirect()->route('admin.employees.index')
                ->with('success', 'Employé supprimé avec succès.');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la suppression de l\'employé.');
        }
    }

    public function toggleActive(Employee $employee)
    {
        $admin = auth('admin')->user();
        
        // Vérification manuelle d'autorisation
        if ($employee->admin_id !== $admin->id) {
            abort(403, 'Accès non autorisé à cet employé.');
        }

        try {
            $employee->update(['is_active' => !$employee->is_active]);
            
            $status = $employee->is_active ? 'activé' : 'désactivé';
            
            return redirect()->back()
                ->with('success', "Employé {$status} avec succès.");
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors du changement de statut.');
        }
    }
}