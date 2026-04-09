@extends('confirmi.layouts.app')
@section('title', 'Gestion des employés')
@section('page-title', 'Employés Confirmi')

@section('content')
<style>
    .emp-avatar { width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700; }
    .emp-avatar.av-blue   { background:var(--accent-bg); color:var(--accent); }
    .emp-avatar.av-amber  { background:var(--warning-bg); color:#b45309; }
    [data-theme="dark"] .emp-avatar.av-amber { color:#fbbf24; }
</style>
<div class="content-card">
    <div class="card-header-custom">
        <h6><i class="fas fa-users me-2" style="color:var(--accent);"></i>Employés ({{ $employees->count() }})</h6>
        <a href="{{ route('confirmi.commercial.employees.create') }}" class="btn btn-sm btn-royal">
            <i class="fas fa-plus me-1"></i>Nouvel employé
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-modern">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>En cours</th>
                    <th>Confirmées</th>
                    <th>Total</th>
                    <th>Statut</th>
                    <th>Dernière connexion</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $emp)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="emp-avatar av-blue">
                                {{ strtoupper(substr($emp->name, 0, 2)) }}
                            </div>
                            <strong>{{ $emp->name }}</strong>
                        </div>
                    </td>
                    <td><small>{{ $emp->email }}</small></td>
                    <td>{{ $emp->phone ?? '—' }}</td>
                    <td><span class="badge-status badge-pending">{{ $emp->pending_orders ?? 0 }}</span></td>
                    <td><span class="badge-status badge-confirmed">{{ $emp->confirmed_orders ?? 0 }}</span></td>
                    <td><strong>{{ $emp->total_orders ?? 0 }}</strong></td>
                    <td>
                        <form method="POST" action="{{ route('confirmi.commercial.employees.toggle', $emp) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="badge border-0 {{ $emp->is_active ? 'bg-success' : 'bg-danger' }}" style="cursor:pointer;padding:.35em .65em;">
                                {{ $emp->is_active ? 'Actif' : 'Inactif' }}
                            </button>
                        </form>
                    </td>
                    <td><small class="text-muted">{{ $emp->last_login_at ? $emp->last_login_at->diffForHumans() : 'Jamais' }}</small></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('confirmi.commercial.employees.edit', $emp) }}" class="btn btn-sm btn-outline-royal">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('confirmi.commercial.employees.destroy', $emp) }}"
                                  onsubmit="return confirm('Supprimer cet employé ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center py-4 text-muted">Aucun employé. <a href="{{ route('confirmi.commercial.employees.create') }}">Créer le premier</a></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- AGENTS SECTION --}}
<div class="content-card mt-4">
    <div class="card-header-custom">
        <h6><i class="fas fa-box-open me-2" style="color:var(--warning);"></i>Agents Emballage ({{ $agents->count() }})</h6>
        <a href="{{ route('confirmi.commercial.employees.create') }}?role=agent" class="btn btn-sm btn-warning text-dark">
            <i class="fas fa-plus me-1"></i>Nouvel agent
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-modern">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>En cours</th>
                    <th>Expédiés</th>
                    <th>Total</th>
                    <th>Statut</th>
                    <th>Dernière connexion</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($agents as $ag)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="emp-avatar av-amber">
                                {{ strtoupper(substr($ag->name, 0, 2)) }}
                            </div>
                            <strong>{{ $ag->name }}</strong>
                        </div>
                    </td>
                    <td><small>{{ $ag->email }}</small></td>
                    <td>{{ $ag->phone ?? '—' }}</td>
                    <td><span class="badge-status badge-pending">{{ $ag->pending_tasks ?? 0 }}</span></td>
                    <td><span class="badge-status badge-confirmed">{{ $ag->shipped_tasks ?? 0 }}</span></td>
                    <td><strong>{{ $ag->total_tasks ?? 0 }}</strong></td>
                    <td>
                        <form method="POST" action="{{ route('confirmi.commercial.employees.toggle', $ag) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="badge border-0 {{ $ag->is_active ? 'bg-success' : 'bg-danger' }}" style="cursor:pointer;padding:.35em .65em;">
                                {{ $ag->is_active ? 'Actif' : 'Inactif' }}
                            </button>
                        </form>
                    </td>
                    <td><small class="text-muted">{{ $ag->last_login_at ? $ag->last_login_at->diffForHumans() : 'Jamais' }}</small></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('confirmi.commercial.employees.edit', $ag) }}" class="btn btn-sm btn-outline-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('confirmi.commercial.employees.destroy', $ag) }}"
                                  onsubmit="return confirm('Supprimer cet agent ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center py-4 text-muted">Aucun agent emballage.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
