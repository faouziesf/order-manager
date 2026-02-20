<?php

namespace App\Http\Controllers\Confirmi;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\ConfirmiRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequestController extends Controller
{
    public function index()
    {
        $requests = ConfirmiRequest::with('admin')
            ->latest()
            ->paginate(20);

        return view('confirmi.commercial.requests.index', compact('requests'));
    }

    public function show(ConfirmiRequest $confirmiRequest)
    {
        $confirmiRequest->load('admin');
        return view('confirmi.commercial.requests.show', compact('confirmiRequest'));
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

        $user = Auth::guard('confirmi')->user();

        $confirmiRequest->update([
            'status' => 'approved',
            'proposed_rate_confirmed' => $request->rate_confirmed,
            'proposed_rate_delivered' => $request->rate_delivered,
            'response_message' => $request->response_message,
            'processed_by' => $user->id,
            'processed_by_type' => 'confirmi_user',
            'processed_at' => now(),
        ]);

        // Activer Confirmi pour l'admin
        $confirmiRequest->admin->update([
            'confirmi_status' => 'active',
            'confirmi_rate_confirmed' => $request->rate_confirmed,
            'confirmi_rate_delivered' => $request->rate_delivered,
            'confirmi_approved_by' => $user->id,
            'confirmi_activated_at' => now(),
        ]);

        return redirect()->route('confirmi.commercial.requests.index')
            ->with('success', "Demande approuvée. Confirmi activé pour {$confirmiRequest->admin->name}.");
    }

    public function reject(Request $request, ConfirmiRequest $confirmiRequest)
    {
        if ($confirmiRequest->status !== 'pending') {
            return back()->with('error', 'Cette demande a déjà été traitée.');
        }

        $request->validate([
            'response_message' => 'required|string|max:500',
        ]);

        $user = Auth::guard('confirmi')->user();

        $confirmiRequest->update([
            'status' => 'rejected',
            'response_message' => $request->response_message,
            'processed_by' => $user->id,
            'processed_by_type' => 'confirmi_user',
            'processed_at' => now(),
        ]);

        $confirmiRequest->admin->update(['confirmi_status' => 'disabled']);

        return redirect()->route('confirmi.commercial.requests.index')
            ->with('success', 'Demande rejetée.');
    }
}
