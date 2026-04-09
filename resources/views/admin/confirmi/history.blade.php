@extends('layouts.admin')

@section('title', 'Historique Confirmi')

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
        display: inline-flex; align-items: center; gap: 6px;
    }
    .cfm-pill:hover { border-color: var(--primary); color: var(--primary); background: var(--primary-50); }
    .cfm-pill.active {
        background: var(--primary); color: #fff; border-color: var(--primary);
        box-shadow: 0 2px 8px rgba(99,102,241,0.25);
    }
    .cfm-pill-count {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 22px; height: 22px; border-radius: 11px;
        font-size: 11px; font-weight: 700; padding: 0 6px;
    }
    .cfm-pill.active .cfm-pill-count { background: rgba(255,255,255,0.25); color: #fff; }

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
            <i class="fas fa-history"></i>Historique Confirmi
        </h1>
        <p class="cfm-page-subtitle">Commandes terminées par le service Confirmi</p>
    </div>
    <a href="{{ route('admin.confirmi.index') }}" class="om-btn om-btn-ghost om-btn-sm">
        <i class="fas fa-arrow-left"></i> Retour
    </a>
</div>

<div class="cfm-filters">
    <a href="{{ route('admin.confirmi.history') }}" class="cfm-pill {{ !request('status') ? 'active' : '' }}">
        Toutes <span class="cfm-pill-count" style="background:var(--bg-muted); color:var(--text);">{{ array_sum($historyCounts) }}</span>
    </a>
    <a href="{{ route('admin.confirmi.history', ['status' => 'confirmed']) }}" class="cfm-pill {{ request('status') == 'confirmed' ? 'active' : '' }}">
        Confirmées <span class="cfm-pill-count" style="background:var(--success-light); color:var(--success);">{{ $historyCounts['confirmed'] }}</span>
    </a>
    <a href="{{ route('admin.confirmi.history', ['status' => 'cancelled']) }}" class="cfm-pill {{ request('status') == 'cancelled' ? 'active' : '' }}">
        Annulées <span class="cfm-pill-count" style="background:var(--danger-light); color:var(--danger);">{{ $historyCounts['cancelled'] }}</span>
    </a>
    <a href="{{ route('admin.confirmi.history', ['status' => 'delivered']) }}" class="cfm-pill {{ request('status') == 'delivered' ? 'active' : '' }}">
        Livrées <span class="cfm-pill-count" style="background:var(--primary-50); color:var(--primary);">{{ $historyCounts['delivered'] }}</span>
    </a>
</div>

<div class="cfm-table-card">
    <div class="table-responsive">
        <table class="om-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Destinataire</th>
                    <th>Téléphone</th>
                    <th>Employé</th>
                    <th>Tentatives</th>
                    <th>Statut</th>
                    <th>Terminée le</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assignments as $a)
                <tr>
                    <td><strong style="color:var(--primary);">#{{ $a->order->id ?? '-' }}</strong></td>
                    <td style="font-weight:600;">{{ $a->order->customer_name ?? 'N/A' }}</td>
                    <td style="font-family:monospace; font-size:13px;">{{ $a->order->customer_phone ?? 'N/A' }}</td>
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
                            @case('confirmed')<span class="om-badge-success">Confirmée</span>@break
                            @case('cancelled')<span class="om-badge-danger">Annulée</span>@break
                            @case('delivered')<span class="om-badge-primary">Livrée</span>@break
                            @default <span class="om-badge-info">{{ $a->status }}</span>
                        @endswitch
                    </td>
                    <td style="color:var(--text-muted); font-size:13px;">{{ $a->completed_at?->format('d/m/Y H:i') ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="om-empty">
                            <div class="om-empty-icon"><i class="fas fa-inbox"></i></div>
                            <h3>Aucun historique</h3>
                            <p>Les commandes terminées apparaîtront ici.</p>
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
