<?php

namespace App\Http\Controllers\Confirmi;

use App\Http\Controllers\Controller;
use App\Models\ConfirmiBilling;
use App\Models\ConfirmiOrderAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeOrderController extends Controller
{
    /**
     * Mes commandes assignées
     */
    public function index(Request $request)
    {
        $user = Auth::guard('confirmi')->user();

        $query = ConfirmiOrderAssignment::where('assigned_to', $user->id)
            ->with(['order', 'admin']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->whereIn('status', ['assigned', 'in_progress']);
        }

        $assignments = $query->latest('assigned_at')->paginate(20);

        return view('confirmi.employee.orders.index', compact('assignments'));
    }

    /**
     * Détails d'une commande assignée
     */
    public function show(ConfirmiOrderAssignment $assignment)
    {
        $user = Auth::guard('confirmi')->user();

        if ($assignment->assigned_to !== $user->id) {
            abort(403);
        }

        $assignment->load(['order.items.product', 'admin']);

        return view('confirmi.employee.orders.show', compact('assignment'));
    }

    /**
     * Démarrer le traitement d'une commande
     */
    public function startProcessing(ConfirmiOrderAssignment $assignment)
    {
        $user = Auth::guard('confirmi')->user();

        if ($assignment->assigned_to !== $user->id || $assignment->status !== 'assigned') {
            return back()->with('error', 'Action non autorisée.');
        }

        $assignment->update([
            'status' => 'in_progress',
            'first_attempt_at' => $assignment->first_attempt_at ?? now(),
        ]);

        return back()->with('success', 'Traitement démarré.');
    }

    /**
     * Enregistrer une tentative de confirmation
     */
    public function recordAttempt(Request $request, ConfirmiOrderAssignment $assignment)
    {
        $user = Auth::guard('confirmi')->user();

        if ($assignment->assigned_to !== $user->id || !$assignment->canBeManaged()) {
            return back()->with('error', 'Action non autorisée.');
        }

        $request->validate([
            'result' => 'required|in:confirmed,no_answer,callback,cancelled',
            'notes' => 'nullable|string|max:500',
        ]);

        $assignment->increment('attempts');
        $assignment->update([
            'last_attempt_at' => now(),
            'notes' => $request->notes,
        ]);

        if ($assignment->status === 'assigned') {
            $assignment->update(['status' => 'in_progress', 'first_attempt_at' => now()]);
        }

        $result = $request->result;

        if ($result === 'confirmed') {
            $assignment->update([
                'status' => 'confirmed',
                'completed_at' => now(),
            ]);

            // Mettre à jour le statut de la commande
            if ($assignment->order) {
                $assignment->order->update(['status' => 'confirmée']);
            }

            // Facturer la confirmation
            $admin = $assignment->admin;
            if ($admin && $admin->confirmi_rate_confirmed > 0) {
                ConfirmiBilling::create([
                    'admin_id' => $admin->id,
                    'order_id' => $assignment->order_id,
                    'billing_type' => 'confirmed',
                    'amount' => $admin->confirmi_rate_confirmed,
                    'billed_at' => now(),
                ]);
            }

            return back()->with('success', 'Commande confirmée avec succès.');
        }

        if ($result === 'cancelled') {
            $assignment->update([
                'status' => 'cancelled',
                'completed_at' => now(),
            ]);

            if ($assignment->order) {
                $assignment->order->update(['status' => 'annulée']);
            }

            return back()->with('success', 'Commande annulée.');
        }

        // no_answer ou callback → reste in_progress
        return back()->with('success', 'Tentative enregistrée. (' . $assignment->attempts . ' tentative(s))');
    }

    /**
     * Rediriger vers la prochaine commande à traiter (file de traitement)
     */
    public function processQueue()
    {
        $user = Auth::guard('confirmi')->user();

        $next = ConfirmiOrderAssignment::where('assigned_to', $user->id)
            ->whereIn('status', ['in_progress', 'assigned'])
            ->with('order')
            ->orderByRaw("FIELD(status, 'in_progress', 'assigned')")
            ->orderBy('assigned_at')
            ->first();

        if (!$next) {
            return redirect()->route('confirmi.employee.orders.index')
                ->with('success', 'Aucune commande en attente. File vide !');
        }

        return redirect()->route('confirmi.employee.orders.process', $next);
    }

    /**
     * Interface de traitement focalisée (mode poste de travail)
     */
    public function process(ConfirmiOrderAssignment $assignment)
    {
        $user = Auth::guard('confirmi')->user();

        if ($assignment->assigned_to !== $user->id) {
            abort(403);
        }

        $assignment->load(['order.items.product', 'admin']);

        $remaining = ConfirmiOrderAssignment::where('assigned_to', $user->id)
            ->whereIn('status', ['assigned', 'in_progress'])
            ->count();

        $nextAssignment = ConfirmiOrderAssignment::where('assigned_to', $user->id)
            ->whereIn('status', ['in_progress', 'assigned'])
            ->where('id', '!=', $assignment->id)
            ->orderByRaw("FIELD(status, 'in_progress', 'assigned')")
            ->orderBy('assigned_at')
            ->first();

        return view('confirmi.employee.orders.process', compact('assignment', 'remaining', 'nextAssignment'));
    }

    /**
     * Historique des commandes traitées
     */
    public function history(Request $request)
    {
        $user = Auth::guard('confirmi')->user();

        $assignments = ConfirmiOrderAssignment::where('assigned_to', $user->id)
            ->whereIn('status', ['confirmed', 'delivered', 'cancelled'])
            ->with(['order', 'admin'])
            ->latest('completed_at')
            ->paginate(20);

        return view('confirmi.employee.orders.history', compact('assignments'));
    }
}
