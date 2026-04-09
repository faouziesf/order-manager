@extends('confirmi.layouts.app')
@section('title', 'Clients Confirmi')
@section('page-title', 'Clients Confirmi actifs')

@section('css')
<style>
.admins-header {
    background: linear-gradient(135deg, var(--accent, #1e40af), #2563eb 60%, #6366f1);
    border-radius: var(--radius-lg);
    padding: 1.5rem 1.75rem;
    color: white;
    position: relative;
    overflow: hidden;
    margin-bottom: 1.25rem;
    box-shadow: 0 8px 20px -5px rgba(30,64,175,.25);
}
.admins-header::before {
    content: '';
    position: absolute;
    top: -50%; right: -10%;
    width: 250px; height: 250px;
    background: rgba(255,255,255,.05);
    border-radius: 50%;
}
.admins-header h2 { font-weight: 800; font-size: 1.3rem; margin: 0; position: relative; z-index: 1; }
.admins-header p { opacity: .85; font-size: .85rem; margin: .3rem 0 0; position: relative; z-index: 1; }

.admin-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow);
    transition: all .2s;
    overflow: hidden;
}
.admin-card:hover {
    box-shadow: var(--shadow-md);
    transform: translateY(-2px);
}
.admin-card-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: .75rem;
}
.admin-avatar {
    width: 40px; height: 40px;
    border-radius: 12px;
    background: linear-gradient(135deg, var(--accent), #6366f1);
    color: white;
    display: flex; align-items: center; justify-content: center;
    font-size: .85rem; font-weight: 700;
    flex-shrink: 0;
}
.admin-card-body {
    padding: 1rem 1.25rem;
}
.admin-stat-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: .5rem;
    margin-bottom: .75rem;
}
.admin-stat {
    padding: .5rem .75rem;
    background: var(--bg-card-alt);
    border-radius: var(--radius, 10px);
    border: 1px solid var(--border);
}
.admin-stat-label {
    font-size: .68rem;
    color: var(--text-secondary);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .3px;
}
.admin-stat-value {
    font-size: 1.1rem;
    font-weight: 800;
    color: var(--text);
    margin-top: 2px;
}
.admin-card-footer {
    padding: .75rem 1.25rem;
    border-top: 1px solid var(--border);
    background: var(--bg-card-alt);
}
.admin-section-title {
    font-size: .72rem;
    font-weight: 700;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: .5px;
    margin-bottom: .5rem;
}
.auto-assign-form {
    display: flex;
    gap: .5rem;
    align-items: center;
}
.auto-assign-form .form-select {
    font-size: .78rem;
    padding: .4rem .65rem;
    border-radius: 8px;
    flex: 1;
}
.emballage-section {
    margin-top: .75rem;
    padding-top: .75rem;
    border-top: 1px solid var(--border);
}
.badge-orders {
    font-size: .7rem;
    font-weight: 700;
    padding: .2rem .55rem;
    border-radius: 6px;
}

@media (max-width: 768px) {
    .admins-header { padding: 1.25rem; }
    .admin-stat-row { grid-template-columns: 1fr; }
}
</style>
@endsection

@section('content')
{{-- Header --}}
<div class="admins-header">
    <h2><i class="fas fa-building me-2"></i>Clients Confirmi</h2>
    <p>{{ $admins->count() }} client(s) avec le service Confirmi actif</p>
</div>

@if($admins->count() === 0)
<div class="content-card">
    <div class="text-center py-5">
        <i class="fas fa-building fa-3x d-block mb-3" style="color:var(--text-secondary); opacity:.3;"></i>
        <p style="color:var(--text-secondary);" class="mb-0">Aucun client actif.</p>
    </div>
</div>
@else
<div class="row g-3">
    @foreach($admins as $admin)
    <div class="col-12 col-lg-6 col-xl-4">
        <div class="admin-card">
            {{-- Header --}}
            <div class="admin-card-header">
                <div class="admin-avatar">{{ strtoupper(substr($admin->name, 0, 1)) }}</div>
                <div style="flex:1; min-width:0;">
                    <div style="font-weight:700; font-size:.9rem; color:var(--text);">{{ $admin->name }}</div>
                    <div style="font-size:.78rem; color:var(--text-secondary);">{{ $admin->shop_name ?? 'Sans boutique' }}</div>
                </div>
                @if($admin->defaultConfirmiEmployee)
                    <span class="badge bg-success" style="font-size:.65rem;">Auto</span>
                @endif
            </div>

            {{-- Body --}}
            <div class="admin-card-body">
                <div class="admin-stat-row">
                    <div class="admin-stat">
                        <div class="admin-stat-label">Tarif confirmé</div>
                        <div class="admin-stat-value" style="color:var(--success);">{{ number_format($admin->confirmi_rate_confirmed, 3) }} <small style="font-size:.65rem; font-weight:500;">DT</small></div>
                    </div>
                    <div class="admin-stat">
                        <div class="admin-stat-label">Tarif livré</div>
                        <div class="admin-stat-value" style="color:var(--accent);">{{ number_format($admin->confirmi_rate_delivered, 3) }} <small style="font-size:.65rem; font-weight:500;">DT</small></div>
                    </div>
                </div>
                <div class="admin-stat-row">
                    <div class="admin-stat">
                        <div class="admin-stat-label">Total commandes</div>
                        <div class="admin-stat-value">{{ $admin->total_confirmi_orders ?? 0 }}</div>
                    </div>
                    <div class="admin-stat">
                        <div class="admin-stat-label">En cours</div>
                        <div class="admin-stat-value" style="color:var(--warning);">{{ $admin->pending_confirmi_orders ?? 0 }}</div>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="admin-card-footer">
                <div class="admin-section-title"><i class="fas fa-user-check me-1"></i>Auto-assignation</div>
                <form method="POST" action="{{ route('confirmi.commercial.admins.auto-assign', $admin) }}" class="auto-assign-form">
                    @csrf
                    <select name="employee_id" class="form-select">
                        <option value="">-- Manuel --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ $admin->confirmi_default_employee_id == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-sm btn-royal" title="Sauvegarder">
                        <i class="fas fa-save"></i>
                    </button>
                </form>

                <div class="emballage-section">
                    <div class="admin-section-title"><i class="fas fa-box me-1"></i>Emballage</div>
                    <div class="d-flex align-items-center gap-2">
                        <form method="POST" action="{{ route('confirmi.commercial.admins.toggle-emballage', $admin) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm {{ $admin->emballage_enabled ? 'btn-success' : 'btn-outline-secondary' }}" style="font-size:.75rem; border-radius:8px;">
                                <i class="fas fa-{{ $admin->emballage_enabled ? 'check-circle' : 'times-circle' }} me-1"></i>{{ $admin->emballage_enabled ? 'Actif' : 'Inactif' }}
                            </button>
                        </form>
                        @if($admin->emballage_enabled)
                        <form method="POST" action="{{ route('confirmi.commercial.admins.default-agent', $admin) }}" class="d-flex gap-1 align-items-center" style="flex:1;">
                            @csrf
                            <select name="agent_id" class="form-select" style="font-size:.75rem; padding:.35rem .6rem;">
                                <option value="">-- Agent --</option>
                                @foreach($agents as $ag)
                                    <option value="{{ $ag->id }}" {{ $admin->confirmi_default_agent_id == $ag->id ? 'selected' : '' }}>{{ $ag->name }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-sm btn-warning" title="Sauvegarder agent" style="border-radius:8px;"><i class="fas fa-save"></i></button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif
@endsection
