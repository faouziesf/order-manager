<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\ConfirmiRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConfirmiRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = ConfirmiRequest::with('admin')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->paginate(20);
        $pendingCount = ConfirmiRequest::where('status', 'pending')->count();

        return view('super-admin.confirmi-requests.index', compact('requests', 'pendingCount'));
    }

    public function approve(Request $request, ConfirmiRequest $confirmiRequest)
    {
        if ($confirmiRequest->status !== 'pending') {
            return back()->with('error', 'Cette demande a déjà été traitée.');
        }

        $request->validate([
            'rate_confirmed' => 'required|numeric|min:0',
            'rate_delivered' => 'required|numeric|min:0',
            'response_message' => 'nullable|string|max:500',
        ]);

        $superAdmin = Auth::guard('super-admin')->user();

        $confirmiRequest->update([
            'status' => 'approved',
            'response_message' => $request->response_message,
            'processed_by' => $superAdmin?->id,
            'processed_by_type' => 'super_admin',
            'processed_at' => now(),
        ]);

        // Activer Confirmi pour l'admin + définir les tarifs
        $confirmiRequest->admin->update([
            'confirmi_status' => 'active',
            'confirmi_rate_confirmed' => $request->rate_confirmed,
            'confirmi_rate_delivered' => $request->rate_delivered,
            'confirmi_activated_at' => now(),
        ]);

        return back()->with('success', "Demande approuvée. Confirmi activé pour {$confirmiRequest->admin->name}.");
    }

    public function reject(Request $request, ConfirmiRequest $confirmiRequest)
    {
        if ($confirmiRequest->status !== 'pending') {
            return back()->with('error', 'Cette demande a déjà été traitée.');
        }

        $request->validate([
            'response_message' => 'nullable|string|max:500',
        ]);

        $superAdmin = Auth::guard('super-admin')->user();

        $confirmiRequest->update([
            'status' => 'rejected',
            'response_message' => $request->response_message,
            'processed_by' => $superAdmin?->id,
            'processed_by_type' => 'super_admin',
            'processed_at' => now(),
        ]);

        $confirmiRequest->admin->update([
            'confirmi_status' => 'disabled',
        ]);

        return back()->with('success', 'Demande rejetée.');
    }
}
