@extends('confirmi.layouts.app')

@section('title', 'Dashboard Agent')
@section('page-title', 'Dashboard Emballage')

@section('content')
<style>
    .agent-stat { background: var(--bg-card); border-radius: 16px; padding: 20px; border: 1px solid var(--border); text-align: center; }
    .agent-stat-value { font-size: 28px; font-weight: 800; color: var(--text); }
    .agent-stat-label { font-size: 12px; color: var(--text-secondary); margin-top: 4px; }
    .agent-stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; font-size: 20px; }
    .agent-stat-icon.s-warning { background: var(--warning-bg); color: var(--warning); }
    .agent-stat-icon.s-blue    { background: var(--accent-bg); color: var(--accent-light); }
    .agent-stat-icon.s-success { background: var(--success-bg); color: var(--success); }
    .agent-stat-icon.s-cyan    { background: rgba(6,182,212,0.1); color: #06b6d4; }
    .agent-stat-icon.s-purple  { background: rgba(139,92,246,0.1); color: #8b5cf6; }
    [data-theme="dark"] .agent-stat-icon.s-cyan   { background: rgba(6,182,212,0.12); color: #22d3ee; }
    [data-theme="dark"] .agent-stat-icon.s-purple { background: rgba(139,92,246,0.12); color: #a78bfa; }
    .agent-hero { background: linear-gradient(135deg, #f59e0b 0%, #d97706 50%, #b45309 100%); border-radius: 16px; padding: 24px; color: white; margin-bottom: 24px; }
    .agent-hero-cta { background: white; color: #b45309; padding: 12px 24px; border-radius: 12px; font-weight: 700; text-decoration: none; font-size: 14px; transition: all 0.2s; }
    .agent-hero-cta:hover { opacity: 0.9; color: #b45309; }
    [data-theme="dark"] .agent-hero-cta { background: rgba(255,255,255,0.15); color: white; }
    .task-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px; padding: 16px; margin-bottom: 12px; }
    .task-admin { font-size: 11px; padding: 3px 8px; border-radius: 6px; background: var(--royal-50); color: var(--royal); font-weight: 600; }
</style>

{{-- Hero Banner --}}
<div class="agent-hero">
    <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
        <div style="width:56px; height:56px; background:rgba(255,255,255,0.2); border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:24px;">
            <i class="fas fa-box-open"></i>
        </div>
        <div style="flex:1;">
            <h3 style="font-weight:800; margin:0;">Interface d'Emballage</h3>
            <p style="opacity:0.9; margin:4px 0 0; font-size:14px;">Réception, emballage et expédition des commandes confirmées</p>
        </div>
        <a href="{{ route('confirmi.agent.emballage.interface') }}" class="agent-hero-cta">
            <i class="fas fa-play"></i> Démarrer
        </a>
    </div>
</div>

{{-- Stats --}}
<div class="row" style="gap:0;">
    <div class="col-6 col-md-4 mb-3">
        <div class="agent-stat">
            <div class="agent-stat-icon s-warning">
                <i class="fas fa-clock"></i>
            </div>
            <div class="agent-stat-value">{{ $stats['pending'] }}</div>
            <div class="agent-stat-label">En attente réception</div>
        </div>
    </div>
    <div class="col-6 col-md-4 mb-3">
        <div class="agent-stat">
            <div class="agent-stat-icon s-blue">
                <i class="fas fa-box"></i>
            </div>
            <div class="agent-stat-value">{{ $stats['received'] }}</div>
            <div class="agent-stat-label">Reçus à emballer</div>
        </div>
    </div>
    <div class="col-6 col-md-4 mb-3">
        <div class="agent-stat">
            <div class="agent-stat-icon s-success">
                <i class="fas fa-tape"></i>
            </div>
            <div class="agent-stat-value">{{ $stats['packed'] }}</div>
            <div class="agent-stat-label">Emballés à expédier</div>
        </div>
    </div>
    <div class="col-6 col-md-4 mb-3">
        <div class="agent-stat">
            <div class="agent-stat-icon s-cyan">
                <i class="fas fa-check-double"></i>
            </div>
            <div class="agent-stat-value">{{ $stats['completed_today'] }}</div>
            <div class="agent-stat-label">Expédiés aujourd'hui</div>
        </div>
    </div>
    <div class="col-6 col-md-4 mb-3">
        <div class="agent-stat">
            <div class="agent-stat-icon s-purple">
                <i class="fas fa-truck"></i>
            </div>
            <div class="agent-stat-value">{{ $stats['total_shipped'] }}</div>
            <div class="agent-stat-label">Total expédiés</div>
        </div>
    </div>
</div>

{{-- Active Tasks --}}
@if($myTasks->count() > 0)
<div style="margin-top:16px;">
    <h5 style="font-weight:700; color:var(--text); margin-bottom:12px;"><i class="fas fa-tasks" style="color:var(--warning); margin-right:8px;"></i>Tâches en cours</h5>
    @foreach($myTasks->take(10) as $task)
    <div class="task-card">
        <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:8px;">
            <div>
                <strong style="font-size:15px;">{{ $task->order->customer_name ?? 'Client' }}</strong>
                <span class="task-admin">{{ $task->admin->store_name ?? $task->admin->name ?? '' }}</span>
            </div>
            <span class="badge bg-{{ $task->status === 'pending' ? 'warning' : ($task->status === 'received' ? 'info' : 'success') }}">
                {{ $task->status === 'pending' ? 'En attente' : ($task->status === 'received' ? 'Reçu' : 'Emballé') }}
            </span>
        </div>
        <div style="color:var(--text-secondary); font-size:13px;">
            #{{ $task->order_id }} · {{ $task->order->region ?? '' }} · {{ number_format($task->order->total_price, 3) }} DT
        </div>
    </div>
    @endforeach
</div>
@else
<div style="text-align:center; padding:40px 20px; color:var(--text-secondary);">
    <i class="fas fa-inbox" style="font-size:48px; opacity:0.3; margin-bottom:12px;"></i>
    <p>Aucune tâche en cours. Commencez depuis l'interface d'emballage.</p>
</div>
@endif
@endsection
