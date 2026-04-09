<?php

namespace App\Http\Controllers\Confirmi;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\ConfirmiOrderAssignment;
use App\Models\ConfirmiRequest;
use App\Models\ConfirmiUser;
use App\Models\EmballageTask;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::guard('confirmi')->user();

        if ($user->isCommercial()) {
            return $this->commercialDashboard($user);
        }

        if ($user->isAgent()) {
            return $this->agentDashboard($user);
        }

        // Employees have their own rich dashboard via EmployeeOrderController
        return redirect()->route('confirmi.employee.dashboard');
    }

    private function commercialDashboard(ConfirmiUser $user)
    {
        $activeAdmins = Admin::where('confirmi_status', 'active')->count();
        $pendingRequests = ConfirmiRequest::where('status', 'pending')->count();

        $totalAssignments = ConfirmiOrderAssignment::count();
        $pendingAssignments = ConfirmiOrderAssignment::where('status', 'pending')->count();
        $inProgressAssignments = ConfirmiOrderAssignment::whereIn('status', ['assigned', 'in_progress'])->count();
        $completedToday = ConfirmiOrderAssignment::whereIn('status', ['confirmed', 'delivered'])
            ->whereDate('completed_at', today())->count();

        $totalEmployees = ConfirmiUser::where('role', 'employee')->count();
        $activeEmployees = ConfirmiUser::where('role', 'employee')->where('is_active', true)->count();

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

        // === EMPLOYEE PERFORMANCE STATS ===
        $employeeStats = ConfirmiUser::where('role', 'employee')
            ->where('is_active', true)
            ->withCount([
                'assignedOrders as total_orders',
                'assignedOrders as confirmed_orders' => fn($q) => $q->where('status', 'confirmed'),
                'assignedOrders as cancelled_orders' => fn($q) => $q->where('status', 'cancelled'),
                'assignedOrders as pending_orders' => fn($q) => $q->whereIn('status', ['assigned', 'in_progress']),
                'assignedOrders as today_completed' => fn($q) => $q->whereIn('status', ['confirmed', 'cancelled'])->whereDate('completed_at', today()),
            ])
            ->withSum('assignedOrders as total_attempts', 'attempts')
            ->withAvg('assignedOrders as avg_attempts', 'attempts')
            ->get()
            ->map(function ($emp) {
                $finished = $emp->confirmed_orders + $emp->cancelled_orders;
                $emp->success_rate = $finished > 0 ? round(($emp->confirmed_orders / $finished) * 100, 1) : 0;
                return $emp;
            });

        $stats = compact(
            'activeAdmins', 'pendingRequests', 'totalAssignments',
            'pendingAssignments', 'inProgressAssignments', 'completedToday',
            'totalEmployees', 'activeEmployees'
        );

        return view('confirmi.commercial.dashboard', compact('stats', 'unassignedOrders', 'weeklyStats', 'employeeStats'));
    }

    /**
     * API endpoint for real-time dashboard polling
     */
    public function liveStats()
    {
        $pendingAssignments = ConfirmiOrderAssignment::where('status', 'pending')->count();
        $inProgressAssignments = ConfirmiOrderAssignment::whereIn('status', ['assigned', 'in_progress'])->count();
        $completedToday = ConfirmiOrderAssignment::whereIn('status', ['confirmed', 'delivered'])
            ->whereDate('completed_at', today())->count();
        $pendingRequests = ConfirmiRequest::where('status', 'pending')->count();

        $recentCompleted = ConfirmiOrderAssignment::whereIn('status', ['confirmed', 'cancelled'])
            ->whereDate('completed_at', today())
            ->with(['order:id,customer_name', 'assignee:id,name'])
            ->latest('completed_at')
            ->take(5)
            ->get()
            ->map(fn($a) => [
                'id' => $a->id,
                'order_id' => $a->order->id ?? null,
                'customer' => $a->order->customer_name ?? 'N/A',
                'employee' => $a->assignee->name ?? 'N/A',
                'status' => $a->status,
                'time' => $a->completed_at->format('H:i'),
            ]);

        return response()->json([
            'pendingAssignments' => $pendingAssignments,
            'inProgressAssignments' => $inProgressAssignments,
            'completedToday' => $completedToday,
            'pendingRequests' => $pendingRequests,
            'recentCompleted' => $recentCompleted,
            'timestamp' => now()->format('H:i:s'),
        ]);
    }

    private function employeeDashboard(ConfirmiUser $user)
    {
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

    private function agentDashboard(ConfirmiUser $user)
    {
        $myTasks = EmballageTask::where('assigned_to', $user->id)
            ->whereIn('status', ['pending', 'received', 'packed'])
            ->with(['order', 'admin'])
            ->latest()
            ->get();

        $completedToday = EmballageTask::where('assigned_to', $user->id)
            ->whereIn('status', ['shipped', 'completed'])
            ->whereDate('completed_at', today())
            ->count();

        $totalShipped = EmballageTask::where('assigned_to', $user->id)
            ->whereIn('status', ['shipped', 'completed'])
            ->count();

        $stats = [
            'pending' => $myTasks->where('status', 'pending')->count(),
            'received' => $myTasks->where('status', 'received')->count(),
            'packed' => $myTasks->where('status', 'packed')->count(),
            'completed_today' => $completedToday,
            'total_shipped' => $totalShipped,
        ];

        return view('confirmi.agent.dashboard', compact('stats', 'myTasks'));
    }
}
