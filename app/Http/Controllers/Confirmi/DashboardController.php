<?php

namespace App\Http\Controllers\Confirmi;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\ConfirmiOrderAssignment;
use App\Models\ConfirmiRequest;
use App\Models\ConfirmiUser;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::guard('confirmi')->user();

        if ($user->isCommercial()) {
            return $this->commercialDashboard($user);
        }

        return $this->employeeDashboard($user);
    }

    private function commercialDashboard(ConfirmiUser $user)
    {
        // Admins avec Confirmi actif
        $activeAdmins = Admin::where('confirmi_status', 'active')->count();
        $pendingRequests = ConfirmiRequest::where('status', 'pending')->count();

        // Commandes Confirmi
        $totalAssignments = ConfirmiOrderAssignment::count();
        $pendingAssignments = ConfirmiOrderAssignment::where('status', 'pending')->count();
        $inProgressAssignments = ConfirmiOrderAssignment::whereIn('status', ['assigned', 'in_progress'])->count();
        $completedToday = ConfirmiOrderAssignment::whereIn('status', ['confirmed', 'delivered'])
            ->whereDate('completed_at', today())->count();

        // Employés Confirmi
        $totalEmployees = ConfirmiUser::where('role', 'employee')->count();
        $activeEmployees = ConfirmiUser::where('role', 'employee')->where('is_active', true)->count();

        // Commandes récentes non assignées
        $unassignedOrders = ConfirmiOrderAssignment::where('status', 'pending')
            ->with(['order', 'admin'])
            ->latest()
            ->take(10)
            ->get();

        // Performance des 7 derniers jours
        $weeklyStats = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $weeklyStats[] = [
                'date' => $date->format('D d'),
                'completed' => ConfirmiOrderAssignment::whereIn('status', ['confirmed', 'delivered'])
                    ->whereDate('completed_at', $date)->count(),
                'new' => ConfirmiOrderAssignment::whereDate('created_at', $date)->count(),
            ];
        }

        $stats = compact(
            'activeAdmins', 'pendingRequests', 'totalAssignments',
            'pendingAssignments', 'inProgressAssignments', 'completedToday',
            'totalEmployees', 'activeEmployees'
        );

        return view('confirmi.commercial.dashboard', compact('stats', 'unassignedOrders', 'weeklyStats'));
    }

    private function employeeDashboard(ConfirmiUser $user)
    {
        // Mes commandes assignées
        $myAssignments = ConfirmiOrderAssignment::where('assigned_to', $user->id)
            ->whereIn('status', ['assigned', 'in_progress'])
            ->with(['order', 'admin'])
            ->latest('assigned_at')
            ->get();

        $completedToday = ConfirmiOrderAssignment::where('assigned_to', $user->id)
            ->whereIn('status', ['confirmed', 'delivered', 'cancelled'])
            ->whereDate('completed_at', today())
            ->count();

        $totalCompleted = ConfirmiOrderAssignment::where('assigned_to', $user->id)
            ->whereIn('status', ['confirmed', 'delivered'])
            ->count();

        $totalAttempts = ConfirmiOrderAssignment::where('assigned_to', $user->id)
            ->sum('attempts');

        $stats = [
            'pending' => $myAssignments->where('status', 'assigned')->count(),
            'in_progress' => $myAssignments->where('status', 'in_progress')->count(),
            'completed_today' => $completedToday,
            'total_completed' => $totalCompleted,
            'total_attempts' => $totalAttempts,
        ];

        return view('confirmi.employee.dashboard', compact('stats', 'myAssignments'));
    }
}
