<?php

namespace App\Http\Controllers\Confirmi;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\ConfirmiBilling;
use App\Models\ConfirmiOrderAssignment;
use App\Models\ConfirmiUser;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderManagementController extends Controller
{
    /**
     * Liste de toutes les commandes Confirmi (Commercial)
     */
    public function index(Request $request)
    {
        $query = ConfirmiOrderAssignment::with(['order', 'admin', 'assignee', 'assigner']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('admin_id')) {
            $query->where('admin_id', $request->admin_id);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        $assignments = $query->latest()->paginate(20);

        $admins = Admin::where('confirmi_status', 'active')->get();
        $employees = ConfirmiUser::where('role', 'employee')->where('is_active', true)->get();

        return view('confirmi.commercial.orders.index', compact('assignments', 'admins', 'employees'));
    }

    /**
     * Commandes en attente d'assignation
     */
    public function pending()
    {
        $assignments = ConfirmiOrderAssignment::where('status', 'pending')
            ->with(['order', 'admin'])
            ->latest()
            ->paginate(20);

        $employees = ConfirmiUser::where('role', 'employee')->where('is_active', true)->get();

        return view('confirmi.commercial.orders.pending', compact('assignments', 'employees'));
    }

    /**
     * Assigner une commande à un employé Confirmi
     */
    public function assign(Request $request, ConfirmiOrderAssignment $assignment)
    {
        $request->validate([
            'assigned_to' => 'required|exists:confirmi_users,id',
        ]);

        $employee = ConfirmiUser::findOrFail($request->assigned_to);
        if (!$employee->isEmployee() || !$employee->is_active) {
            return back()->with('error', 'Employé invalide ou inactif.');
        }

        $commercial = Auth::guard('confirmi')->user();

        $assignment->update([
            'assigned_to' => $employee->id,
            'assigned_by' => $commercial->id,
            'status' => 'assigned',
            'assigned_at' => now(),
        ]);

        return back()->with('success', "Commande assignée à {$employee->name}.");
    }

    /**
     * Assigner en masse
     */
    public function bulkAssign(Request $request)
    {
        $request->validate([
            'assignment_ids' => 'required|array',
            'assignment_ids.*' => 'exists:confirmi_order_assignments,id',
            'assigned_to' => 'required|exists:confirmi_users,id',
        ]);

        $employee = ConfirmiUser::findOrFail($request->assigned_to);
        $commercial = Auth::guard('confirmi')->user();

        $count = ConfirmiOrderAssignment::whereIn('id', $request->assignment_ids)
            ->where('status', 'pending')
            ->update([
                'assigned_to' => $employee->id,
                'assigned_by' => $commercial->id,
                'status' => 'assigned',
                'assigned_at' => now(),
            ]);

        return back()->with('success', "{$count} commande(s) assignée(s) à {$employee->name}.");
    }

    /**
     * Détails d'une commande Confirmi
     */
    public function show(ConfirmiOrderAssignment $assignment)
    {
        $assignment->load(['order.items.product', 'admin', 'assignee', 'assigner']);
        return view('confirmi.commercial.orders.show', compact('assignment'));
    }

    /**
     * Marquer une commande Confirmi comme livrée (déclenche facturation livraison)
     */
    public function markDelivered(ConfirmiOrderAssignment $assignment)
    {
        if ($assignment->status !== 'confirmed') {
            return back()->with('error', 'Seules les commandes confirmées peuvent être marquées comme livrées.');
        }

        $assignment->update([
            'status' => 'delivered',
            'completed_at' => $assignment->completed_at ?? now(),
        ]);

        if ($assignment->order) {
            $assignment->order->update(['status' => 'livrée']);
        }

        // Facturer la livraison
        $admin = $assignment->admin;
        if ($admin && $admin->confirmi_rate_delivered > 0) {
            // Éviter la double facturation
            $alreadyBilled = ConfirmiBilling::where('order_id', $assignment->order_id)
                ->where('billing_type', 'delivered')
                ->exists();

            if (!$alreadyBilled) {
                ConfirmiBilling::create([
                    'admin_id' => $admin->id,
                    'order_id' => $assignment->order_id,
                    'billing_type' => 'delivered',
                    'amount' => $admin->confirmi_rate_delivered,
                    'billed_at' => now(),
                ]);
            }
        }

        return back()->with('success', 'Commande marquée comme livrée. Facturation enregistrée.');
    }

    /**
     * Liste des admins avec Confirmi actif
     */
    public function adminsList()
    {
        $admins = Admin::where('confirmi_status', 'active')
            ->withCount([
                'orders as total_confirmi_orders' => function ($q) {
                    $q->whereHas('confirmiAssignment');
                },
                'orders as pending_confirmi_orders' => function ($q) {
                    $q->whereHas('confirmiAssignment', fn($qa) => $qa->whereIn('status', ['pending', 'assigned', 'in_progress']));
                },
            ])
            ->get();

        return view('confirmi.commercial.admins', compact('admins'));
    }
}
