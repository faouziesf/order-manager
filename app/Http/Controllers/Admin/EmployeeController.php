<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index()
    {
        $admin = auth('admin')->user();

        // Récupérer les employés créés par cet admin
        $employees = Admin::where('role', Admin::ROLE_EMPLOYEE)
            ->where('created_by', $admin->id)
            ->latest()
            ->paginate(10);

        return view('admin.employees.index', compact('employees', 'admin'));
    }

    public function create()
    {
        $admin = auth('admin')->user();

        // Vérifier si l'admin peut créer plus d'employés
        $employeeCount = Admin::where('role', Admin::ROLE_EMPLOYEE)->where('created_by', $admin->id)->count();
        if ($employeeCount >= $admin->max_employees) {
            return redirect()->route('admin.employees.index')
                ->with('error', 'Vous avez atteint le nombre maximum d\'employés autorisés (' . $admin->max_employees . ').');
        }

        // Récupérer les managers créés par cet admin
        $managers = Admin::where('role', Admin::ROLE_MANAGER)
            ->where('created_by', $admin->id)
            ->where('is_active', true)
            ->get();

        return view('admin.employees.create', compact('managers'));
    }

    public function store(Request $request)
    {
        $admin = auth('admin')->user();

        // Vérifier les limites
        $employeeCount = Admin::where('role', Admin::ROLE_EMPLOYEE)->where('created_by', $admin->id)->count();
        if ($employeeCount >= $admin->max_employees) {
            return redirect()->route('admin.employees.index')
                ->with('error', 'Vous avez atteint le nombre maximum d\'employés autorisés.');
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
            $employee = Admin::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'role' => Admin::ROLE_EMPLOYEE,
                'created_by' => $admin->id, // IMPORTANT: Lier l'employé à l'admin qui le crée
                'shop_name' => 'employee_' . time(),
                'identifier' => $this->generateUniqueIdentifier('EMP'),
                'is_active' => $request->has('is_active') ? true : false,
            ]);

            return redirect()->route('admin.employees.index')
                ->with('success', 'Employé créé avec succès.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la création de l\'employé: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(Admin $employee)
    {
        // Vérifier que c'est bien un employé appartenant à cet admin
        if ($employee->role !== Admin::ROLE_EMPLOYEE || $employee->created_by !== auth('admin')->id()) {
            abort(404);
        }

        return view('admin.employees.show', compact('employee'));
    }

    public function edit(Admin $employee)
    {
        // Vérifier que c'est bien un employé appartenant à cet admin
        if ($employee->role !== Admin::ROLE_EMPLOYEE || $employee->created_by !== auth('admin')->id()) {
            abort(404);
        }

        $managers = Admin::where('role', Admin::ROLE_MANAGER)
            ->where('created_by', auth('admin')->id())
            ->where('is_active', true)
            ->get();

        return view('admin.employees.edit', compact('employee', 'managers'));
    }

    public function update(Request $request, Admin $employee)
    {
        // Vérifier que c'est bien un employé appartenant à cet admin
        if ($employee->role !== Admin::ROLE_EMPLOYEE || $employee->created_by !== auth('admin')->id()) {
            abort(404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('admins', 'email')->ignore($employee->id),
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

            $employee->update($updateData);

            return redirect()->route('admin.employees.index')
                ->with('success', 'Employé mis à jour avec succès.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la mise à jour de l\'employé.')
                ->withInput();
        }
    }

    public function destroy(Admin $employee)
    {
        // Vérifier que c'est bien un employé appartenant à cet admin
        if ($employee->role !== Admin::ROLE_EMPLOYEE || $employee->created_by !== auth('admin')->id()) {
            abort(404);
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

    public function toggleActive(Admin $employee)
    {
        // Vérifier que c'est bien un employé appartenant à cet admin
        if ($employee->role !== Admin::ROLE_EMPLOYEE || $employee->created_by !== auth('admin')->id()) {
            abort(404);
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

    private function generateUniqueIdentifier(string $prefix): string
    {
        do {
            $identifier = $prefix . strtoupper(substr(uniqid(), -5));
        } while (Admin::where('identifier', $identifier)->exists());

        return $identifier;
    }
}
