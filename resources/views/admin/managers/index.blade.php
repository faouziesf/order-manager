@extends('layouts.admin')

@section('title', 'Gestion des Managers')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Gestion des Managers</h1>
        <p class="text-muted">Gérez vos managers et leurs accès</p>
    </div>
    @php
        $managerCount = \App\Models\Admin::where('role', \App\Models\Admin::ROLE_MANAGER)->count();
    @endphp
    @if($managerCount < $admin->max_managers)
        <a href="{{ route('admin.managers.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nouveau Manager
        </a>
    @else
        <button class="btn btn-secondary" disabled>
            <i class="fas fa-lock me-2"></i>Limite atteinte ({{ $admin->max_managers }})
        </button>
    @endif
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card stats-card-primary h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="stats-card-label">Total Managers</div>
                        <div class="stats-card-number">{{ $managers->total() }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-tie stats-card-icon text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card stats-card-success h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="stats-card-label">Managers Actifs</div>
                        <div class="stats-card-number">{{ \App\Models\Admin::where('role', \App\Models\Admin::ROLE_MANAGER)->where('is_active', true)->count() }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle stats-card-icon text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card stats-card-warning h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="stats-card-label">Limite Managers</div>
                        <div class="stats-card-number">{{ $admin->max_managers }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-limit stats-card-icon text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card stats-card-info h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="stats-card-label">Employés Total</div>
                        <div class="stats-card-number">{{ \App\Models\Admin::where('role', \App\Models\Admin::ROLE_EMPLOYEE)->count() }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users stats-card-icon text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Table Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Liste des Managers</h6>
        <div class="d-flex align-items-center">
            <div class="input-group input-group-sm me-3" style="width: 250px;">
                <span class="input-group-text">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" class="form-control" placeholder="Rechercher..." id="searchInput">
            </div>
        </div>
    </div>

    <div class="card-body">
        @if($managers->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered" id="managersTable">
                    <thead>
                        <tr>
                            <th>Manager</th>
                            <th>Contact</th>
                            <th>Statut</th>
                            <th>Créé le</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($managers as $manager)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar me-3">
                                            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center"
                                                 style="width: 40px; height: 40px;">
                                                <span class="text-white font-weight-bold">
                                                    {{ substr($manager->name, 0, 1) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="font-weight-bold">{{ $manager->name }}</div>
                                            <small class="text-muted">Manager</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>{{ $manager->email }}</div>
                                    @if($manager->phone)
                                        <small class="text-muted">{{ $manager->phone }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $manager->is_active ? 'badge-success' : 'badge-danger' }}">
                                        {{ $manager->is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </td>
                                <td>{{ $manager->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.managers.show', $manager) }}"
                                           class="btn btn-sm btn-info" title="Voir">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.managers.edit', $manager) }}"
                                           class="btn btn-sm btn-warning" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.managers.toggle-active', $manager) }}"
                                              class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="btn btn-sm {{ $manager->is_active ? 'btn-secondary' : 'btn-success' }}"
                                                    title="{{ $manager->is_active ? 'Désactiver' : 'Activer' }}"
                                                    onclick="return confirm('Êtes-vous sûr ?')">
                                                <i class="fas {{ $manager->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.managers.destroy', $manager) }}"
                                              class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="btn btn-sm btn-danger"
                                                    title="Supprimer"
                                                    onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce manager ? Cette action est irréversible.')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($managers->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $managers->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <i class="fas fa-user-tie fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Aucun manager</h5>
                <p class="text-muted">Commencez par créer votre premier manager.</p>
                @if($managerCount < $admin->max_managers)
                    <a href="{{ route('admin.managers.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Créer un Manager
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
// Recherche en temps réel
document.getElementById('searchInput').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#managersTable tbody tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});
</script>
@endsection
