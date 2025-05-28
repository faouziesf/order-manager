@extends('layouts.admin')

@section('title', 'Détails du Manager')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Détails du Manager</h1>
        <p class="text-muted">Informations complètes de {{ $manager->name }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.managers.edit', $manager) }}" class="btn btn-warning">
            <i class="fas fa-edit me-2"></i>Modifier
        </a>
        <form method="POST" action="{{ route('admin.managers.toggle-active', $manager) }}" class="d-inline">
            @csrf
            @method('PATCH')
            <button type="submit" 
                    class="btn {{ $manager->is_active ? 'btn-secondary' : 'btn-success' }}"
                    onclick="return confirm('Êtes-vous sûr ?')">
                <i class="fas {{ $manager->is_active ? 'fa-ban' : 'fa-check' }} me-2"></i>
                {{ $manager->is_active ? 'Désactiver' : 'Activer' }}
            </button>
        </form>
        <a href="{{ route('admin.managers.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Retour
        </a>
    </div>
</div>

<div class="row">
    <!-- Informations principales -->
    <div class="col-lg-4 mb-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white text-center">
                <div class="rounded-circle bg-white d-flex align-items-center justify-content-center mx-auto mb-3" 
                     style="width: 80px; height: 80px;">
                    <span class="text-primary font-weight-bold" style="font-size: 32px;">
                        {{ substr($manager->name, 0, 1) }}
                    </span>
                </div>
                <h5 class="mb-1">{{ $manager->name }}</h5>
                <p class="mb-0 opacity-75">Manager</p>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label text-muted">Email</label>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-envelope text-primary me-2"></i>
                        <a href="mailto:{{ $manager->email }}" class="text-decoration-none">{{ $manager->email }}</a>
                    </div>
                </div>
                
                @if($manager->phone)
                <div class="mb-3">
                    <label class="form-label text-muted">Téléphone</label>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-phone text-success me-2"></i>
                        <a href="tel:{{ $manager->phone }}" class="text-decoration-none">{{ $manager->phone }}</a>
                    </div>
                </div>
                @endif
                
                <div class="mb-3">
                    <label class="form-label text-muted">Statut</label>
                    <div>
                        <span class="badge {{ $manager->is_active ? 'badge-success' : 'badge-danger' }} fs-6">
                            <i class="fas {{ $manager->is_active ? 'fa-check-circle' : 'fa-times-circle' }} me-1"></i>
                            {{ $manager->is_active ? 'Actif' : 'Inactif' }}
                        </span>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label text-muted">Employés supervisés</label>
                    <div>
                        <span class="badge badge-info fs-6">
                            <i class="fas fa-users me-1"></i>
                            {{ $manager->employees()->count() }} employé(s)
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Actions rapides -->
        <div class="card shadow mt-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-bolt me-2"></i>Actions rapides</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.login-history.show', ['user_type' => 'Manager', 'user_id' => $manager->id]) }}" 
                       class="btn btn-outline-info btn-sm">
                        <i class="fas fa-history me-2"></i>Historique des connexions
                    </a>
                    <a href="{{ route('admin.employees.index') }}?manager={{ $manager->id }}" 
                       class="btn btn-outline-success btn-sm">
                        <i class="fas fa-users me-2"></i>Voir ses employés
                    </a>
                    <button class="btn btn-outline-primary btn-sm" onclick="sendWelcomeEmail()">
                        <i class="fas fa-envelope me-2"></i>Renvoyer email de bienvenue
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Détails et statistiques -->
    <div class="col-lg-8">
        <!-- Statistiques -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card stats-card stats-card-primary h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-2x text-primary mb-2"></i>
                        <h4 class="font-weight-bold">{{ $manager->employees()->count() }}</h4>
                        <p class="text-muted mb-0">Employés</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card stats-card-success h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-user-check fa-2x text-success mb-2"></i>
                        <h4 class="font-weight-bold">{{ $manager->employees()->where('is_active', true)->count() }}</h4>
                        <p class="text-muted mb-0">Actifs</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card stats-card-info h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-calendar-alt fa-2x text-info mb-2"></i>
                        <h4 class="font-weight-bold">{{ $manager->created_at->diffInDays() }}</h4>
                        <p class="text-muted mb-0">Jours</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card stats-card-warning h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-sign-in-alt fa-2x text-warning mb-2"></i>
                        <h4 class="font-weight-bold">{{ $manager->loginHistory()->successful()->count() }}</h4>
                        <p class="text-muted mb-0">Connexions</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Employés supervisés -->
        <div class="card shadow mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="fas fa-users me-2"></i>Employés supervisés ({{ $manager->employees()->count() }})
                </h6>
                @if($manager->employees()->count() > 0)
                    <a href="{{ route('admin.employees.index') }}?manager={{ $manager->id }}" class="btn btn-sm btn-outline-primary">
                        Voir tous
                    </a>
                @endif
            </div>
            <div class="card-body">
                @if($manager->employees()->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Employé</th>
                                    <th>Email</th>
                                    <th>Statut</th>
                                    <th>Créé le</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($manager->employees()->take(5)->get() as $employee)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-success d-flex align-items-center justify-content-center me-2" 
                                                     style="width: 30px; height: 30px;">
                                                    <span class="text-white" style="font-size: 12px;">
                                                        {{ substr($employee->name, 0, 1) }}
                                                    </span>
                                                </div>
                                                {{ $employee->name }}
                                            </div>
                                        </td>
                                        <td>{{ $employee->email }}</td>
                                        <td>
                                            <span class="badge {{ $employee->is_active ? 'badge-success' : 'badge-danger' }}">
                                                {{ $employee->is_active ? 'Actif' : 'Inactif' }}
                                            </span>
                                        </td>
                                        <td>{{ $employee->created_at->format('d/m/Y') }}</td>
                                        <td>
                                            <a href="{{ route('admin.employees.show', $employee) }}" 
                                               class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    @if($manager->employees()->count() > 5)
                        <div class="text-center mt-3">
                            <small class="text-muted">... et {{ $manager->employees()->count() - 5 }} autre(s)</small>
                        </div>
                    @endif
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <h6 class="text-muted">Aucun employé assigné</h6>
                        <p class="text-muted">Ce manager ne supervise encore aucun employé.</p>
                        <a href="{{ route('admin.employees.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-2"></i>Créer un employé
                        </a>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Informations système -->
        <div class="card shadow">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informations système</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">Créé le</label>
                            <div>{{ $manager->created_at->format('d/m/Y à H:i') }}</div>
                            <small class="text-muted">{{ $manager->created_at->diffForHumans() }}</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">Dernière modification</label>
                            <div>{{ $manager->updated_at->format('d/m/Y à H:i') }}</div>
                            <small class="text-muted">{{ $manager->updated_at->diffForHumans() }}</small>
                        </div>
                    </div>
                </div>
                
                @if($manager->loginHistory()->latest()->first())
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Dernière connexion</label>
                                <div>{{ $manager->loginHistory()->latest()->first()->login_at->format('d/m/Y à H:i') }}</div>
                                <small class="text-muted">{{ $manager->loginHistory()->latest()->first()->login_at->diffForHumans() }}</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">IP de connexion</label>
                                <div><code>{{ $manager->loginHistory()->latest()->first()->ip_address }}</code></div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function sendWelcomeEmail() {
    if (confirm('Envoyer un email de bienvenue à {{ $manager->name }} ?')) {
        // Ici vous pouvez ajouter une requête AJAX pour envoyer l'email
        alert('Fonctionnalité à implémenter : envoi d\'email de bienvenue');
    }
}
</script>
@endsection