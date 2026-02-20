<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\ConfirmiBilling;
use Illuminate\Http\Request;

class ConfirmiBillingController extends Controller
{
    public function index(Request $request)
    {
        $query = ConfirmiBilling::with(['admin', 'order'])->latest('billed_at');

        if ($request->filled('admin_id')) {
            $query->where('admin_id', $request->admin_id);
        }
        if ($request->filled('type')) {
            $query->where('billing_type', $request->type);
        }
        if ($request->filled('paid')) {
            $query->where('is_paid', $request->paid === '1');
        }

        $bills = $query->paginate(30);

        $admins = Admin::where('confirmi_status', 'active')->orderBy('name')->get();

        $totals = [
            'all' => ConfirmiBilling::sum('amount'),
            'paid' => ConfirmiBilling::where('is_paid', true)->sum('amount'),
            'unpaid' => ConfirmiBilling::where('is_paid', false)->sum('amount'),
            'this_month' => ConfirmiBilling::whereMonth('billed_at', now()->month)
                ->whereYear('billed_at', now()->year)->sum('amount'),
        ];

        return view('super-admin.confirmi-billing.index', compact('bills', 'admins', 'totals'));
    }

    public function markPaid(Request $request)
    {
        $request->validate([
            'billing_ids' => 'required|array',
            'billing_ids.*' => 'exists:confirmi_billing,id',
        ]);

        ConfirmiBilling::whereIn('id', $request->billing_ids)->update(['is_paid' => true]);

        return back()->with('success', count($request->billing_ids) . ' facture(s) marquée(s) comme payée(s).');
    }

    public function markPaidForAdmin(Admin $admin)
    {
        $count = ConfirmiBilling::where('admin_id', $admin->id)
            ->where('is_paid', false)
            ->update(['is_paid' => true]);

        return back()->with('success', "{$count} facture(s) de {$admin->name} marquée(s) comme payées.");
    }
}
