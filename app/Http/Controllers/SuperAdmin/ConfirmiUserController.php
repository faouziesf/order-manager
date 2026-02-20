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
            'role' => 'required|in:commercial,employee',
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
            'role' => 'required|in:commercial,employee',
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
}
