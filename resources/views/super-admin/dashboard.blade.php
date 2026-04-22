@extends('layouts.super-admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    <!-- Stats Cards -->
    <div class="sa-grid sa-grid-4" style="margin-bottom:24px">
        <div class="sa-stat">
            <div class="sa-stat-icon sa-stat-icon-primary"><i class="fas fa-building"></i></div>
            <div>
                <div class="sa-stat-value">{{ $stats['totalAdmins'] }}</div>
                <div class="sa-stat-label">Total Administrateurs</div>
            </div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-icon sa-stat-icon-success"><i class="fas fa-check-circle"></i></div>
            <div>
                <div class="sa-stat-value">{{ $stats['activeAdmins'] }}</div>
                <div class="sa-stat-label">Admins Actifs</div>
            </div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-icon sa-stat-icon-warning"><i class="fas fa-clock"></i></div>
            <div>
                <div class="sa-stat-value">{{ $stats['expiredAdmins'] }}</div>
                <div class="sa-stat-label">Expirés</div>
            </div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-icon sa-stat-icon-info"><i class="fas fa-user-plus"></i></div>
            <div>
                <div class="sa-stat-value">{{ $stats['newAdminsThisMonth'] }}</div>
                <div class="sa-stat-label">Nouveaux ce mois</div>
            </div>
        </div>
    </div>

    <!-- Commandes & performance -->
    <div class="sa-grid sa-grid-3" style="margin-bottom:24px">
        <div class="sa-stat">
            <div class="sa-stat-icon sa-stat-icon-success"><i class="fas fa-shopping-cart"></i></div>
            <div>
                <div class="sa-stat-value">{{ number_format($stats['totalOrders']) }}</div>
                <div class="sa-stat-label">Total Commandes</div>
            </div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-icon sa-stat-icon-primary"><i class="fas fa-calendar-check"></i></div>
            <div>
                <div class="sa-stat-value">{{ number_format($stats['ordersThisMonth']) }}</div>
                <div class="sa-stat-label">Commandes ce mois</div>
            </div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-icon sa-stat-icon-info"><i class="fas fa-chart-line"></i></div>
            <div>
                <div class="sa-stat-value">{{ number_format($stats['averageOrdersPerAdmin'], 1) }}</div>
                <div class="sa-stat-label">Moyenne commandes / admin</div>
            </div>
        </div>
    </div>

    <!-- Confirmi & Emballage Stats -->
    <div class="sa-grid sa-grid-3" style="margin-bottom:24px">
        @php
            $confirmiActive = \App\Models\Admin::where('confirmi_status','active')->count();
            $emballageActive = \App\Models\Admin::where('emballage_enabled',true)->count();
            $confirmiUsers = \App\Models\ConfirmiUser::count();
        @endphp
        <div class="sa-stat">
            <div class="sa-stat-icon sa-stat-icon-primary"><i class="fas fa-headset"></i></div>
            <div>
                <div class="sa-stat-value">{{ $confirmiActive }}</div>
                <div class="sa-stat-label">Confirmi Actifs</div>
            </div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-icon sa-stat-icon-success"><i class="fas fa-box"></i></div>
            <div>
                <div class="sa-stat-value">{{ $emballageActive }}</div>
                <div class="sa-stat-label">Emballage Actifs</div>
            </div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-icon sa-stat-icon-info"><i class="fas fa-users-cog"></i></div>
            <div>
                <div class="sa-stat-value">{{ $confirmiUsers }}</div>
                <div class="sa-stat-label">Utilisateurs Confirmi</div>
            </div>
        </div>
    </div>

    <div class="sa-grid sa-grid-2" style="margin-bottom:24px">
        <!-- Alerts -->
        <div class="sa-card">
            <div class="sa-card-header">
                <h3 class="sa-card-title"><i class="fas fa-bell" style="color:var(--sa-warning);margin-right:8px"></i>Alertes</h3>
            </div>
            @if($alerts->isEmpty())
                <div class="sa-empty"><i class="fas fa-check-circle"></i><p>Aucune alerte</p></div>
            @else
                @foreach($alerts as $alert)
                    <div class="sa-alert sa-alert-{{ $alert['type'] }}">
                        <i class="fas fa-{{ $alert['type'] === 'warning' ? 'exclamation-triangle' : ($alert['type'] === 'danger' ? 'exclamation-circle' : 'info-circle') }}"></i>
                        {{ $alert['message'] }}
                    </div>
                @endforeach
            @endif
        </div>

        <!-- Quick Actions -->
        <div class="sa-card">
            <div class="sa-card-header">
                <h3 class="sa-card-title"><i class="fas fa-bolt" style="color:var(--sa-primary);margin-right:8px"></i>Actions Rapides</h3>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <a href="{{ route('super-admin.admins.create') }}" class="sa-btn sa-btn-primary" style="justify-content:center"><i class="fas fa-plus"></i> Nouvel Admin</a>
                <a href="{{ route('super-admin.confirmi-users.create') }}" class="sa-btn sa-btn-outline" style="justify-content:center"><i class="fas fa-user-plus"></i> Utilisateur Confirmi</a>
                <a href="{{ route('super-admin.confirmi-requests.index') }}" class="sa-btn sa-btn-outline" style="justify-content:center"><i class="fas fa-inbox"></i> Demandes</a>
                <a href="{{ route('super-admin.confirmi-billing.index') }}" class="sa-btn sa-btn-outline" style="justify-content:center"><i class="fas fa-file-invoice"></i> Facturation</a>
            </div>
        </div>
    </div>

    <!-- Recent Admins Table -->
    <div class="sa-card" style="margin-bottom:24px">
        <div class="sa-card-header">
            <h3 class="sa-card-title"><i class="fas fa-history" style="color:var(--sa-text-secondary);margin-right:8px"></i>Administrateurs Récents</h3>
            <a href="{{ route('super-admin.admins.index') }}" class="sa-btn sa-btn-outline sa-btn-sm">Voir tout</a>
        </div>
        <div class="sa-table-wrap">
            <table class="sa-table">
                <thead>
                    <tr>
                        <th>Admin</th>
                        <th>Boutique</th>
                        <th>Abonnement</th>
                        <th>Statut</th>
                        <th>Confirmi</th>
                        <th>Inscription</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentAdmins as $admin)
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px">
                                    <div class="sa-avatar sa-avatar-primary">{{ strtoupper(substr($admin->name,0,1)) }}</div>
                                    <div>
                                        <strong>{{ $admin->name }}</strong>
                                        <div style="font-size:.7rem;color:var(--sa-text-muted)">{{ $admin->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $admin->shop_name }}</td>
                            <td>
                                <span class="sa-badge sa-badge-{{ $admin->subscription_type === 'premium' ? 'primary' : ($admin->subscription_type === 'enterprise' ? 'success' : ($admin->subscription_type === 'basic' ? 'info' : 'muted')) }}">
                                    {{ ucfirst($admin->subscription_type ?? 'trial') }}
                                </span>
                            </td>
                            <td>
                                <span class="sa-badge sa-badge-{{ $admin->is_active ? 'success' : 'danger' }}">
                                    {{ $admin->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                            <td>
                                @if($admin->confirmi_status === 'active')
                                    <span class="sa-badge sa-badge-success">Actif</span>
                                @elseif($admin->confirmi_status === 'pending')
                                    <span class="sa-badge sa-badge-warning">En attente</span>
                                @else
                                    <span class="sa-badge sa-badge-muted">-</span>
                                @endif
                            </td>
                            <td style="color:var(--sa-text-secondary);font-size:.75rem">{{ $admin->created_at->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="sa-empty"><p>Aucun administrateur</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="sa-card">
        <div class="sa-card-header">
            <h3 class="sa-card-title"><i class="fas fa-stream" style="color:var(--sa-text-secondary);margin-right:8px"></i>Activité Récente</h3>
        </div>
        @if($recentActivity->isEmpty())
            <div class="sa-empty"><i class="fas fa-inbox"></i><p>Aucune activité récente</p></div>
        @else
            @foreach($recentActivity->take(8) as $activity)
                <div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid var(--sa-border)">
                    <div style="width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:.75rem;
                        background:var(--sa-{{ $activity['color'] === 'success' ? 'success-light' : ($activity['color'] === 'warning' ? 'warning-light' : 'danger-light') }});
                        color:var(--sa-{{ $activity['color'] === 'success' ? 'success' : ($activity['color'] === 'warning' ? 'warning' : 'danger') }})">
                        <i class="{{ $activity['icon'] }}"></i>
                    </div>
                    <div style="flex:1;min-width:0">
                        <div style="font-size:.8125rem;font-weight:500">{{ $activity['message'] }}</div>
                        <div style="font-size:.7rem;color:var(--sa-text-muted)">{{ \Carbon\Carbon::parse($activity['time'])->diffForHumans() }}</div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
@endsection
