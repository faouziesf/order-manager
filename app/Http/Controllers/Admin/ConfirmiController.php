<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConfirmiBilling;
use App\Models\ConfirmiOrderAssignment;
use App\Models\ConfirmiRequest;
use App\Models\MasafaConfiguration;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConfirmiController extends Controller
{
    /**
     * Page principale Confirmi pour l'admin
     */
    public function index()
    {
        $admin = Auth::guard('admin')->user();

        // Seulement les admins (pas managers/employees)
        if ($admin->role !== 'admin') {
            return redirect()->route('admin.dashboard')->with('error', 'Accès réservé aux administrateurs.');
        }

        $status = $admin->confirmi_status;
        $pendingRequest = ConfirmiRequest::where('admin_id', $admin->id)
            ->where('status', 'pending')
            ->first();

        $latestRequest = ConfirmiRequest::where('admin_id', $admin->id)
            ->latest()
            ->first();

        $stats = null;
        if ($status === 'active') {
            $stats = [
                'total' => ConfirmiOrderAssignment::where('admin_id', $admin->id)->count(),
                'pending' => ConfirmiOrderAssignment::where('admin_id', $admin->id)->where('status', 'pending')->count(),
                'in_progress' => ConfirmiOrderAssignment::where('admin_id', $admin->id)->whereIn('status', ['assigned', 'in_progress'])->count(),
                'confirmed' => ConfirmiOrderAssignment::where('admin_id', $admin->id)->where('status', 'confirmed')->count(),
                'cancelled' => ConfirmiOrderAssignment::where('admin_id', $admin->id)->where('status', 'cancelled')->count(),
            ];
        }

        $masafaConfig = MasafaConfiguration::where('admin_id', $admin->id)->first();

        $billing = null;
        if ($status === 'active') {
            $billing = [
                'month_total' => ConfirmiBilling::where('admin_id', $admin->id)
                    ->whereMonth('billed_at', now()->month)
                    ->whereYear('billed_at', now()->year)
                    ->sum('amount'),
                'all_time_total' => ConfirmiBilling::where('admin_id', $admin->id)->sum('amount'),
                'unpaid' => ConfirmiBilling::where('admin_id', $admin->id)->where('is_paid', false)->sum('amount'),
                'month_confirmed' => ConfirmiBilling::where('admin_id', $admin->id)
                    ->where('billing_type', 'confirmed')
                    ->whereMonth('billed_at', now()->month)->count(),
                'month_delivered' => ConfirmiBilling::where('admin_id', $admin->id)
                    ->where('billing_type', 'delivered')
                    ->whereMonth('billed_at', now()->month)->count(),
            ];
        }

        return view('admin.confirmi.index', compact('admin', 'status', 'pendingRequest', 'latestRequest', 'stats', 'masafaConfig', 'billing'));
    }

    /**
     * Soumettre une demande d'activation Confirmi
     */
    public function requestActivation(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        if ($admin->role !== 'admin') {
            return back()->with('error', 'Accès réservé aux administrateurs.');
        }

        if ($admin->confirmi_status === 'active') {
            return back()->with('error', 'Confirmi est déjà activé pour votre compte.');
        }

        // Vérifier qu'il n'y a pas déjà une demande en cours
        $pending = ConfirmiRequest::where('admin_id', $admin->id)
            ->where('status', 'pending')
            ->exists();

        if ($pending) {
            return back()->with('error', 'Vous avez déjà une demande en cours de traitement.');
        }

        $request->validate([
            'message' => 'nullable|string|max:500',
        ]);

        ConfirmiRequest::create([
            'admin_id' => $admin->id,
            'status' => 'pending',
            'admin_message' => $request->message,
        ]);

        $admin->update(['confirmi_status' => 'pending']);

        return back()->with('success', 'Votre demande d\'activation Confirmi a été envoyée. Elle sera traitée dans les plus brefs délais.');
    }

    /**
     * Historique de facturation Confirmi de l'admin
     */
    public function billing(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        if ($admin->confirmi_status !== 'active') {
            return redirect()->route('admin.confirmi.index')->with('error', 'Confirmi n\'est pas activé.');
        }

        $query = ConfirmiBilling::where('admin_id', $admin->id)->with('order')->latest('billed_at');

        if ($request->filled('type')) {
            $query->where('billing_type', $request->type);
        }
        if ($request->filled('paid')) {
            $query->where('is_paid', $request->paid === '1');
        }
        if ($request->filled('month')) {
            $query->whereMonth('billed_at', $request->month)->whereYear('billed_at', now()->year);
        }

        $bills = $query->paginate(30);

        $totals = [
            'all' => ConfirmiBilling::where('admin_id', $admin->id)->sum('amount'),
            'paid' => ConfirmiBilling::where('admin_id', $admin->id)->where('is_paid', true)->sum('amount'),
            'unpaid' => ConfirmiBilling::where('admin_id', $admin->id)->where('is_paid', false)->sum('amount'),
        ];

        return view('admin.confirmi.billing', compact('bills', 'totals'));
    }

    /**
     * Sauvegarder la configuration Masafa Express
     */
    public function saveMasafaConfig(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        if ($admin->confirmi_status !== 'active') {
            return back()->with('error', 'Confirmi doit être actif pour configurer Masafa Express.');
        }

        $request->validate([
            'api_token' => 'required|string|min:10',
            'masafa_client_id' => 'nullable|string',
            'auto_send' => 'boolean',
        ]);

        MasafaConfiguration::updateOrCreate(
            ['admin_id' => $admin->id],
            [
                'api_token' => $request->api_token,
                'masafa_client_id' => $request->masafa_client_id,
                'auto_send' => $request->boolean('auto_send'),
                'is_active' => true,
            ]
        );

        return back()->with('success', 'Configuration Masafa Express sauvegardée.');
    }

    /**
     * Voir les commandes gérées par Confirmi (vue lecture seule pour l'admin)
     */
    public function orders(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        if ($admin->confirmi_status !== 'active') {
            return redirect()->route('admin.confirmi.index')->with('error', 'Confirmi n\'est pas activé.');
        }

        $query = ConfirmiOrderAssignment::where('admin_id', $admin->id)
            ->with(['order', 'assignee']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $assignments = $query->latest()->paginate(20);

        return view('admin.confirmi.orders', compact('assignments', 'admin'));
    }
}
