@extends('layouts.super-admin')

@section('title', 'Emballage')
@section('page-title', 'Gestion Emballage')

@section('content')
    @php
        $totalTasks = \App\Models\EmballageTask::count();
        $pendingTasks = \App\Models\EmballageTask::where('status','pending')->count();
        $completedTasks = \App\Models\EmballageTask::where('status','completed')->count();
        $adminsWithEmballage = \App\Models\Admin::where('emballage_enabled', true)->with('kolixyConfiguration')->get();
        $agents = \App\Models\ConfirmiUser::where('role','agent')->get();
        $recentTasks = \App\Models\EmballageTask::with(['admin','agent','order'])->latest()->take(20)->get();
    @endphp

    <!-- Stats -->
    <div class="sa-grid sa-grid-4" style="margin-bottom:24px">
        <div class="sa-stat">
            <div class="sa-stat-icon sa-stat-icon-primary"><i class="fas fa-box"></i></div>
            <div><div class="sa-stat-value">{{ $totalTasks }}</div><div class="sa-stat-label">Total Tâches</div></div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-icon sa-stat-icon-warning"><i class="fas fa-clock"></i></div>
            <div><div class="sa-stat-value">{{ $pendingTasks }}</div><div class="sa-stat-label">En attente</div></div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-icon sa-stat-icon-success"><i class="fas fa-check-circle"></i></div>
            <div><div class="sa-stat-value">{{ $completedTasks }}</div><div class="sa-stat-label">Terminées</div></div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-icon sa-stat-icon-info"><i class="fas fa-user-shield"></i></div>
            <div><div class="sa-stat-value">{{ $agents->count() }}</div><div class="sa-stat-label">Agents</div></div>
        </div>
    </div>

    <div class="sa-grid sa-grid-2" style="margin-bottom:24px">
        <!-- Admins with emballage -->
        <div class="sa-card">
            <div class="sa-card-header">
                <h3 class="sa-card-title"><i class="fas fa-building" style="color:var(--sa-primary);margin-right:8px"></i>Admins Emballage Actif</h3>
            </div>
            @if($adminsWithEmballage->isEmpty())
                <div class="sa-empty"><i class="fas fa-box-open"></i><p>Aucun admin avec emballage activé</p></div>
            @else
                <div class="sa-table-wrap">
                    <table class="sa-table">
                        <thead>
                            <tr><th>Admin</th><th>Config Kolixy</th><th>Agent par défaut</th></tr>
                        </thead>
                        <tbody>
                            @foreach($adminsWithEmballage as $admin)
                                <tr>
                                    <td>
                                        <div style="display:flex;align-items:center;gap:8px">
                                            <div class="sa-avatar sa-avatar-primary" style="width:28px;height:28px;font-size:.65rem">{{ strtoupper(substr($admin->name,0,1)) }}</div>
                                            <div>
                                                <strong style="font-size:.8rem">{{ $admin->name }}</strong>
                                                <div style="font-size:.65rem;color:var(--sa-text-muted)">{{ $admin->shop_name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($admin->kolixyConfiguration)
                                            <span class="sa-badge sa-badge-success">Configuré</span>
                                        @else
                                            <span class="sa-badge sa-badge-muted">Non configuré</span>
                                        @endif
                                    </td>
                                    <td style="font-size:.8rem;color:var(--sa-text-secondary)">
                                        @if($admin->confirmi_default_agent_id)
                                            {{ \App\Models\ConfirmiUser::find($admin->confirmi_default_agent_id)?->name ?? '-' }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Agents -->
        <div class="sa-card">
            <div class="sa-card-header">
                <h3 class="sa-card-title"><i class="fas fa-user-shield" style="color:var(--sa-warning);margin-right:8px"></i>Agents Emballage</h3>
                <a href="{{ route('super-admin.confirmi-users.create') }}" class="sa-btn sa-btn-outline sa-btn-sm"><i class="fas fa-plus"></i></a>
            </div>
            @if($agents->isEmpty())
                <div class="sa-empty"><i class="fas fa-user-shield"></i><p>Aucun agent</p></div>
            @else
                @foreach($agents as $agent)
                    <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--sa-border)">
                        <div class="sa-avatar" style="width:32px;height:32px;font-size:.7rem;background:linear-gradient(135deg,var(--sa-warning),#fbbf24)">{{ strtoupper(substr($agent->name,0,1)) }}</div>
                        <div style="flex:1;min-width:0">
                            <strong style="font-size:.8rem">{{ $agent->name }}</strong>
                            <div style="font-size:.65rem;color:var(--sa-text-muted)">{{ $agent->email }}</div>
                        </div>
                        <span class="sa-badge sa-badge-{{ $agent->is_active ? 'success' : 'danger' }}" style="font-size:.6rem">{{ $agent->is_active ? 'Actif' : 'Inactif' }}</span>
                        @php $agentTaskCount = $agent->emballageTasks()->count() @endphp
                        <span style="font-size:.75rem;color:var(--sa-text-secondary)">{{ $agentTaskCount }} tâche(s)</span>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    <!-- Recent Tasks -->
    <div class="sa-card">
        <div class="sa-card-header">
            <h3 class="sa-card-title"><i class="fas fa-history" style="color:var(--sa-text-secondary);margin-right:8px"></i>Tâches Récentes</h3>
        </div>
        <div class="sa-table-wrap">
            <table class="sa-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Admin</th>
                        <th>Agent</th>
                        <th>Commande</th>
                        <th>Statut</th>
                        <th>Tracking</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentTasks as $task)
                        <tr>
                            <td style="font-size:.8rem;font-weight:600">#{{ $task->id }}</td>
                            <td style="font-size:.8rem">{{ $task->admin->name ?? '-' }}</td>
                            <td style="font-size:.8rem">{{ $task->agent->name ?? '-' }}</td>
                            <td style="font-size:.8rem;color:var(--sa-text-secondary)">#{{ $task->order_id ?? '-' }}</td>
                            <td>
                                @php
                                    $statusColors = ['pending'=>'warning','received'=>'info','packed'=>'primary','shipped'=>'info','completed'=>'success'];
                                    $statusLabels = ['pending'=>'En attente','received'=>'Reçu','packed'=>'Emballé','shipped'=>'Expédié','completed'=>'Terminé'];
                                @endphp
                                <span class="sa-badge sa-badge-{{ $statusColors[$task->status] ?? 'muted' }}">{{ $statusLabels[$task->status] ?? $task->status }}</span>
                            </td>
                            <td style="font-size:.75rem;color:var(--sa-text-secondary)">{{ $task->tracking_number ?? '-' }}</td>
                            <td style="font-size:.75rem;color:var(--sa-text-muted)">{{ $task->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="sa-empty"><i class="fas fa-box-open"></i><p>Aucune tâche d'emballage</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
