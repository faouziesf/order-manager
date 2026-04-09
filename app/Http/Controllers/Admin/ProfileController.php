<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show()
    {
        $user = auth('admin')->user();
        return view('admin.profile', compact('user'));
    }

    public function update(Request $request)
    {
        $user = auth('admin')->user();

        $rules = [
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('admins')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
        ];

        if ($request->filled('password')) {
            $rules['password']              = ['min:8', 'confirmed'];
            $rules['current_password']      = ['required'];
        }

        $validated = $request->validate($rules);

        if ($request->filled('password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Le mot de passe actuel est incorrect.'])->withInput();
            }
            $user->password = Hash::make($validated['password']);
        }

        $user->name  = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'] ?? $user->phone;
        if ($user->isAdmin()) {
            $user->shop_name = $request->input('shop_name', $user->shop_name);
        }
        $user->save();

        return back()->with('success', 'Profil mis à jour avec succès.');
    }
}
