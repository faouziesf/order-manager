@extends('confirmi.layouts.app')
@section('title', 'Gestion des employés')
@section('page-title', 'Employés Confirmi')

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="content-card">
    <div class="card-header-custom">
        <h6><i class="fas fa-users me-2 text-primary"></i>Employés ({{ $employees->count() }})</h6>
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
                            <div style="width:34px;height:34px;border-radius:50%;background:var(--royal-blue-100);color:var(--royal-blue-800);display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700;">
                                {{ strtoupper(substr($emp->name, 0, 2)) }}
                            </div>
                            <strong>{{ $emp->name }}</strong>
                        </div>
                    </td>
                    <td><small>{{ $emp->email }}</small></td>
                    <td>{{ $emp->phone ?? '—' }}</td>
                    <td><span class="badge bg-warning text-dark">{{ $emp->pending_orders ?? 0 }}</span></td>
                    <td><span class="badge bg-success">{{ $emp->confirmed_orders ?? 0 }}</span></td>
                    <td><span class="badge bg-secondary">{{ $emp->total_orders ?? 0 }}</span></td>
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
@endsection
