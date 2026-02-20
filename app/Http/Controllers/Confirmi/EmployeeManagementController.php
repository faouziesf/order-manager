<?php

namespace App\Http\Controllers\Confirmi;

use App\Http\Controllers\Controller;
use App\Models\ConfirmiUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class EmployeeManagementController extends Controller
{
    public function index()
    {
        $employees = ConfirmiUser::employees()
            ->withCount(['assignedOrders as total_orders',
                'assignedOrders as confirmed_orders' => fn($q) => $q->where('status', 'confirmed'),
                'assignedOrders as pending_orders'   => fn($q) => $q->whereIn('status', ['assigned', 'in_progress']),
            ])
            ->latest()
            ->get();

        return view('confirmi.commercial.employees.index', compact('employees'));
    }

    public function create()
    {
        return view('confirmi.commercial.employees.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:confirmi_users,email',
            'phone'    => 'nullable|string|max:20',
            'password' => 'required|string|min:6|confirmed',
        ]);

        ConfirmiUser::create([
            'name'       => $request->name,
            'email'      => $request->email,
            'phone'      => $request->phone,
            'password'   => Hash::make($request->password),
            'role'       => ConfirmiUser::ROLE_EMPLOYEE,
            'is_active'  => true,
            'created_by' => null,
        ]);

        return redirect()->route('confirmi.commercial.employees.index')
            ->with('success', 'Employé créé avec succès.');
    }

    public function edit(ConfirmiUser $employee)
    {
        if (!$employee->isEmployee()) {
            abort(403);
        }

        return view('confirmi.commercial.employees.edit', compact('employee'));
    }

    public function update(Request $request, ConfirmiUser $employee)
    {
        if (!$employee->isEmployee()) {
            abort(403);
        }

        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:confirmi_users,email,' . $employee->id,
            'phone'    => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $data = [
            'name'  => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $employee->update($data);

        return redirect()->route('confirmi.commercial.employees.index')
            ->with('success', 'Employé mis à jour.');
    }

    public function toggleActive(ConfirmiUser $employee)
    {
        if (!$employee->isEmployee()) {
            abort(403);
        }

        $employee->update(['is_active' => !$employee->is_active]);

        $msg = $employee->is_active ? 'Employé activé.' : 'Employé désactivé.';
        return back()->with('success', $msg);
    }

    public function destroy(ConfirmiUser $employee)
    {
        if (!$employee->isEmployee()) {
            abort(403);
        }

        $employee->delete();

        return redirect()->route('confirmi.commercial.employees.index')
            ->with('success', 'Employé supprimé.');
    }
}
