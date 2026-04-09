@extends('layouts.admin')

@section('title', 'Commandes Confirmi')

@section('css')
@include('admin.partials._shared-styles')
<style>
    /* ===== Page Header ===== */
    .cfm-page-header {
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 12px; margin-bottom: 28px;
    }
    .cfm-page-title {
        font-size: 1.5rem; font-weight: 800; color: var(--text); margin: 0;
        display: flex; align-items: center; gap: 10px;
    }
    .cfm-page-title i { color: var(--primary); font-size: 1.2rem; }
    .cfm-page-subtitle { color: var(--text-secondary); margin: 4px 0 0; font-size: 14px; }

    /* ===== Filter Pills ===== */
    .cfm-filters {
        display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 24px;
    }
    .cfm-pill {
        padding: 8px 18px; border-radius: 24px;
        font-size: 13px; font-weight: 600;
        text-decoration: none;
        border: 1px solid var(--border);
        background: var(--bg-card);
        color: var(--text-secondary);
        transition: all 0.2s ease;
    }
    .cfm-pill:hover { border-color: var(--primary); color: var(--primary); background: var(--primary-50); }
    .cfm-pill.active {
        background: var(--primary); color: #fff; border-color: var(--primary);
        box-shadow: 0 2px 8px rgba(99,102,241,0.25);
    }
    .cfm-pill.active-dark {
        background: var(--text); color: var(--bg-card); border-color: var(--text);
    }

    /* ===== Table Card ===== */
    .cfm-table-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg, 16px);
        overflow: hidden;
    }
    .cfm-table-footer {
        padding: 16px 24px;
        border-top: 1px solid var(--border);
    }
</style>
@endsection

@section('content')
<div class="cfm-page-header">
    <div>
        <h1 class="cfm-page-title">
            <i class="fas fa-tasks"></i>Commandes Confirmi
        </h1>
        <p class="cfm-page-subtitle">Vue en lecture seule de vos commandes en cours de confirmation</p>
    </div>
    <a href="{{ route('admin.confirmi.index') }}" class="om-btn om-btn-ghost om-btn-sm">
        <i class="fas fa-arrow-left"></i> Retour
    </a>
</div>

<div class="cfm-filters">
    <a href="{{ route('admin.confirmi.orders') }}" class="cfm-pill {{ !request('status') ? 'active' : '' }}">Toutes</a>
    <a href="{{ route('admin.confirmi.orders', ['status' => 'pending']) }}" class="cfm-pill {{ request('status') == 'pending' ? 'active' : '' }}">En attente</a>
    <a href="{{ route('admin.confirmi.orders', ['status' => 'assigned']) }}" class="cfm-pill {{ request('status') == 'assigned' ? 'active' : '' }}">Assignees</a>
    <a href="{{ route('admin.confirmi.orders', ['status' => 'in_progress']) }}" class="cfm-pill {{ request('status') == 'in_progress' ? 'active' : '' }}">En cours</a>
    <a href="{{ route('admin.confirmi.orders', ['status' => 'confirmed']) }}" class="cfm-pill {{ request('status') == 'confirmed' ? 'active' : '' }}">Confirmees</a>
    <a href="{{ route('admin.confirmi.orders', ['status' => 'cancelled']) }}" class="cfm-pill {{ request('status') == 'cancelled' ? 'active' : '' }}">Annulees</a>
    <a href="{{ route('admin.confirmi.orders', ['status' => 'delivered']) }}" class="cfm-pill {{ request('status') == 'delivered' ? 'active-dark' : '' }}">Livrees</a>
</div>

<div class="cfm-table-card">
    <div class="table-responsive">
        <table class="om-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Destinataire</th>
                    <th>Telephone</th>
                    <th>Montant</th>
                    <th>Employe</th>
                    <th>Tentatives</th>
                    <th>Statut</th>
                    <th>Derniere tentative</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assignments as $a)
                <tr>
                    <td><strong style="color:var(--primary);">#{{ $a->order->id ?? '-' }}</strong></td>
                    <td style="font-weight:600;">{{ $a->order->customer_name ?? 'N/A' }}</td>
                    <td style="font-family:monospace; font-size:13px;">{{ $a->order->customer_phone ?? 'N/A' }}</td>
                    <td><strong>{{ number_format($a->order->total_price ?? 0, 3) }} DT</strong></td>
                    <td>
                        @if($a->assignee)
                            <span style="display:inline-flex; align-items:center; gap:6px;">
                                <span class="om-avatar om-avatar-sm">{{ substr($a->assignee->name, 0, 1) }}</span>
                                {{ $a->assignee->name }}
                            </span>
                        @else
                            <span style="color:var(--text-muted);">-</span>
                        @endif
                    </td>
                    <td><span class="om-badge-info">{{ $a->attempts }}</span></td>
                    <td>
                        @switch($a->status)
                            @case('pending')<span class="om-badge-warning">En attente</span>@break
                            @case('assigned')<span class="om-badge-info">Assignee</span>@break
                            @case('in_progress')<span class="om-badge-primary">En cours</span>@break
                            @case('confirmed')<span class="om-badge-success">Confirmee</span>@break
                            @case('cancelled')<span class="om-badge-danger">Annulee</span>@break
                            @case('delivered')<span class="om-badge-primary">Livree</span>@break
                            @default <span class="om-badge-info">{{ $a->status }}</span>
                        @endswitch
                    </td>
                    <td style="color:var(--text-muted); font-size:13px;">{{ $a->last_attempt_at?->format('d/m H:i') ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="om-empty">
                            <div class="om-empty-icon"><i class="fas fa-inbox"></i></div>
                            <h3>Aucune commande Confirmi</h3>
                            <p>Les commandes apparaitront ici une fois soumises.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($assignments, 'links'))
    <div class="cfm-table-footer">
        {{ $assignments->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
