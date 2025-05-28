@extends('layouts.admin')

@section('title', 'Gestion des Employés')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Gestion des Employés</h1>
        <p class="text-muted">Gérez vos employés et leurs affectations</p>
    </div>
    @if($admin->employees()->count() < $admin->max_employees)
        <a href="{{ route('admin.employees.create') }}" class="btn btn-success">
            <i class="fas fa-plus me-2"></i>Nouvel Employé
        </a>
    @else
        <button class="btn btn-secondary" disabled>
            <i class="fas fa-lock me-2"></i>Limite atteinte ({{ $admin->max_employees }})
        </button>
    @endif
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card stats-card-success h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="stats-card-label">Total Employés</div>
                        <div class="stats-card-number">{{ $employees->total() }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users stats-card-icon text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card stats-card-primary h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="stats-card-label">Employés Actifs</div>
                        <div class="stats-card-number">{{ $admin->employees()->where('is_active', true)->count() }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle stats-card-icon text-primary"></i>
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
                        <div class="stats-card-label">Limite Employés</div>
                        <div class="stats-card-number">{{ $admin->max_employees }}</div>
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
                        <div class="stats-card-label">Sans Manager</div>
                        <div class="stats-card-number">{{ $admin->employees()->whereNull('manager_id')->count() }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-slash stats-card-icon text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Table Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-success">Liste des Employés</h6>
        <div class="d-flex align-items-center gap-2">
            <select class="form-select form-select-sm" style="width: 200px;" onchange="filterByManager(this.value)">
                <option value="">Tous les managers</option>
                <option value="no-manager">Sans manager</option>
                @foreach($admin->managers as $manager)
                    <option value="{{ $manager->id }}">{{ $manager->name }}</option>
                @endforeach
            </select>
            <div class="input-group input-group-sm" style="width: 250px;">
                <span class="input-group-text">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" class="form-control" placeholder="Rechercher..." id="searchInput">
            </div>
        </div>
    </div>
    
    <div class="card-body">
        @if($employees->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered" id="employeesTable">
                    <thead>
                        <tr>
                            <th>Employé</th>
                            <th>Contact</th>
                            <th>Manager</th>
                            <th>Statut</th>
                            <th>Créé le</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $employee)
                            <tr data-manager-id="{{ $employee->manager_id }}">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar me-3">
                                            <div class="rounded-circle bg-success d-flex align-items-center justify-content-center" 
                                                 style="width: 40px; height: 40px;">
                                                <span class="text-white font-weight-bold">
                                                    {{ substr($employee->name, 0, 1) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="font-weight-bold">{{ $employee->name }}</div>
                                            <small class="text-muted">Employé</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>{{ $employee->email }}</div>
                                    @if($employee->phone)
                                        <small class="text-muted">{{ $employee->phone }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($employee->manager)
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center me-2" 
                                                 style="width: 30px; height: 30px;">
                                                <span class="text-white" style="font-size: 12px;">
                                                    {{ substr($employee->manager->name, 0, 1) }}
                                                </span>
                                            </div>
                                            <div>
                                                <div class="font-weight-bold" style="font-size: 13px;">{{ $employee->manager->name }}</div>
                                                <small class="text-muted">Manager</small>
                                            </div>
                                        </div>
                                    @else
                                        <span class="badge badge-secondary">Sans manager</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $employee->is_active ? 'badge-success' : 'badge-danger' }}">
                                        {{ $employee->is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </td>
                                <td>{{ $employee->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.employees.show', $employee) }}" 
                                           class="btn btn-sm btn-info" title="Voir">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.employees.edit', $employee) }}" 
                                           class="btn btn-sm btn-warning" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.employees.toggle-active', $employee) }}" 
                                              class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
                                                    class="btn btn-sm {{ $employee->is_active ? 'btn-secondary' : 'btn-success' }}" 
                                                    title="{{ $employee->is_active ? 'Désactiver' : 'Activer' }}"
                                                    onclick="return confirm('Êtes-vous sûr ?')">
                                                <i class="fas {{ $employee->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.employees.destroy', $employee) }}" 
                                              class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-danger" 
                                                    title="Supprimer"
                                                    onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet employé ? Cette action est irréversible.')">
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
            @if($employees->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $employees->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Aucun employé</h5>
                <p class="text-muted">Commencez par créer votre premier employé.</p>
                @if($admin->employees()->count() < $admin->max_employees)
                    <a href="{{ route('admin.employees.create') }}" class="btn btn-success">
                        <i class="fas fa-plus me-2"></i>Créer un Employé
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
    const rows = document.querySelectorAll('#employeesTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Filtrage par manager
function filterByManager(managerId) {
    const rows = document.querySelectorAll('#employeesTable tbody tr');
    
    rows.forEach(row => {
        const rowManagerId = row.dataset.managerId;
        
        if (managerId === '') {
            row.style.display = '';
        } else if (managerId === 'no-manager') {
            row.style.display = rowManagerId === '' ? '' : 'none';
        } else {
            row.style.display = rowManagerId === managerId ? '' : 'none';
        }
    });
}
</script>
@endsection