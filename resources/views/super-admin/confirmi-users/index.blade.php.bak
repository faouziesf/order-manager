@extends('layouts.super-admin')

@section('title', 'Utilisateurs Confirmi')

@section('breadcrumb')
    <ol class="breadcrumb breadcrumb-custom">
        <li class="breadcrumb-item"><a href="{{ route('super-admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Utilisateurs Confirmi</li>
    </ol>
@endsection

@section('page-header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">Utilisateurs Confirmi</h1>
            <p class="page-subtitle">Gérez les commerciaux et employés de la plateforme Confirmi</p>
        </div>
        <a href="{{ route('super-admin.confirmi-users.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nouvel utilisateur
        </a>
    </div>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Liste des utilisateurs</h5>
        <form method="GET" class="d-flex gap-2">
            <select name="role" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
                <option value="">Tous les rôles</option>
                <option value="commercial" {{ request('role') == 'commercial' ? 'selected' : '' }}>Commerciaux</option>
                <option value="employee" {{ request('role') == 'employee' ? 'selected' : '' }}>Employés</option>
            </select>
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Rechercher..." value="{{ request('search') }}" style="width:200px;">
            <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search"></i></button>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Rôle</th>
                    <th>Statut</th>
                    <th>Dernière connexion</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td><strong>{{ $user->name }}</strong></td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->phone ?? '-' }}</td>
                    <td>
                        @if($user->role === 'commercial')
                            <span class="badge bg-primary">Commercial</span>
                        @else
                            <span class="badge bg-info">Employé</span>
                        @endif
                    </td>
                    <td>
                        @if($user->is_active)
                            <span class="badge bg-success">Actif</span>
                        @else
                            <span class="badge bg-danger">Inactif</span>
                        @endif
                    </td>
                    <td>{{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Jamais' }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('super-admin.confirmi-users.edit', $user) }}" class="btn btn-sm btn-outline-primary" title="Modifier">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('super-admin.confirmi-users.toggle-active', $user) }}" class="d-inline">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-outline-{{ $user->is_active ? 'warning' : 'success' }}" title="{{ $user->is_active ? 'Désactiver' : 'Activer' }}">
                                    <i class="fas fa-{{ $user->is_active ? 'ban' : 'check' }}"></i>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('super-admin.confirmi-users.destroy', $user) }}" class="d-inline" onsubmit="return confirm('Supprimer cet utilisateur ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-4 text-muted">Aucun utilisateur Confirmi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $users->withQueryString()->links() }}</div>
</div>
@endsection
