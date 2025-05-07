<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalAdmins = Admin::count();
        $activeAdmins = Admin::where('is_active', true)->count();
        $inactiveAdmins = Admin::where('is_active', false)->count();
        
        $recentAdmins = Admin::latest()->take(5)->get();
        $totalOrders = Admin::sum('total_orders');
        $totalActiveHours = Admin::sum('total_active_hours');
        
        return view('super-admin.dashboard', compact(
            'totalAdmins', 
            'activeAdmins', 
            'inactiveAdmins', 
            'recentAdmins',
            'totalOrders',
            'totalActiveHours'
        ));
    }
}