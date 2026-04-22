<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ConfirmiUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ConfirmiUserController extends Controller
{
    public function index(Request $request)
    {
        $query = ConfirmiUser::query();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        $users = $query->latest()->paginate(20);
        return view('super-admin.confirmi-users.index', compact('users'));
    }

    public function create()
    {
        return view('super-admin.confirmi-users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:confirmi_users,email',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:commercial,employee,agent',
        ]);

        ConfirmiUser::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'role' => $request->role,
            'is_active' => true,
            'created_by' => Auth::guard('super-admin')->id(),
        ]);

        return redirect()->route('super-admin.confirmi-users.index')
            ->with('success', "Utilisateur Confirmi créé avec succès.");
    }

    public function edit(ConfirmiUser $confirmiUser)
    {
        return view('super-admin.confirmi-users.edit', compact('confirmiUser'));
    }

    public function update(Request $request, ConfirmiUser $confirmiUser)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:confirmi_users,email,' . $confirmiUser->id,
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:commercial,employee,agent',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $data = $request->only('name', 'email', 'phone', 'role');
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $confirmiUser->update($data);

        return redirect()->route('super-admin.confirmi-users.index')
            ->with('success', "Utilisateur Confirmi mis à jour.");
    }

    public function toggleActive(ConfirmiUser $confirmiUser)
    {
        $confirmiUser->update(['is_active' => !$confirmiUser->is_active]);
        $status = $confirmiUser->is_active ? 'activé' : 'désactivé';
        return back()->with('success', "Utilisateur {$status}.");
    }

    public function destroy(ConfirmiUser $confirmiUser)
    {
        $confirmiUser->delete();
        return redirect()->route('super-admin.confirmi-users.index')
            ->with('success', 'Utilisateur supprimé.');
    }

    public function loginAs(ConfirmiUser $confirmiUser, Request $request)
    {
        if (!$confirmiUser->is_active) {
            return back()->with('error', "Impossible d'ouvrir une session sur un compte Confirmi inactif.");
        }

        $superAdmin = Auth::guard('super-admin')->user();

        Auth::guard('super-admin')->logout();
        Auth::guard('admin')->logout();
        Auth::guard('confirmi')->logout();

        $request->session()->put('impersonator_super_admin_id', $superAdmin->id);
        $request->session()->put('impersonator_super_admin_name', $superAdmin->name ?? 'Super Admin');
        $request->session()->regenerate();

        Auth::guard('confirmi')->login($confirmiUser);

        return redirect()->route('confirmi.dashboard')
            ->with('success', "Session ouverte sur le compte Confirmi {$confirmiUser->name}.");
    }

    public function stopImpersonation(Request $request)
    {
        $superAdminId = $request->session()->pull('impersonator_super_admin_id');
        $superAdminName = $request->session()->pull('impersonator_super_admin_name', 'Super Admin');

        Auth::guard('confirmi')->logout();

        if (!$superAdminId) {
            return redirect()->route('super-admin.login')->with('error', 'Session d\'impersonation introuvable.');
        }

        Auth::guard('super-admin')->loginUsingId($superAdminId);
        $request->session()->regenerate();

        return redirect()->route('super-admin.dashboard')
            ->with('success', "Retour au compte {$superAdminName}.");
    }
}
