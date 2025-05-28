@extends('layouts.admin')

@section('title', 'Détails de l\'Employé')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Détails de l'Employé</h1>
        <p class="text-muted">Informations complètes de {{ $employee->name }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.employees.edit', $employee) }}" class="btn btn-warning">
            <i class="fas fa-edit me-2"></i>Modifier
        </a>
        <form method="POST" action="{{ route('admin.employees.toggle-active', $employee) }}" class="d-inline">
            @csrf
            @method('PATCH')
            <button type="submit" 
                    class="btn {{ $employee->is_active ? 'btn-secondary' : 'btn-success' }}"
                    onclick="return confirm('Êtes-vous sûr ?')">
                <i class="fas {{ $employee->is_active ? 'fa-ban' : 'fa-check' }} me-2"></i>
                {{ $employee->is_active ? 'Désactiver' : 'Activer' }}
            </button>
        </form>
        <a href="{{ route('admin.employees.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Retour
        </a>
    </div>
</div>

<div class="row">
    <!-- Informations principales -->
    <div class="col-lg-4 mb-4">
        <div class="card shadow">
            <div class="card-header bg-success text-white text-center">
                <div class="rounded-circle bg-white d-flex align-items-center justify-content-center mx-auto mb-3" 
                     style="width: 80px; height: 80px;">
                    <span class="text-success font-weight-bold" style="font-size: 32px;">
                        {{ substr($employee->name, 0, 1) }}
                    </span>
                </div>
                <h5 class="mb-1">{{ $employee->name }}</h5>
                <p class="mb-0 opacity-75">Employé</p>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label text-muted">Email</label>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-envelope text-success me-2"></i>
                        <a href="mailto:{{ $employee->email }}" class="text-decoration-none">{{ $employee->email }}</a>
                    </div>
                </div>
                
                @if($employee->phone)
                <div class="mb-3">
                    <label class="form-label text-muted">Téléphone</label>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-phone text-info me-2"></i>
                        <a href="tel:{{ $employee->phone }}" class="text-decoration-none">{{ $employee->phone }}</a>
                    </div>
                </div>
                @endif
                
                <div class="mb-3">
                    <label class="form-label text-muted">Statut</label>
                    <div>
                        <span class="badge {{ $employee->is_active ? 'badge-success' : 'badge-danger' }} fs-6">
                            <i class="fas {{ $employee->is_active ? 'fa-check-circle' : 'fa-times-circle' }} me-1"></i>
                            {{ $employee->is_active ? 'Actif' : 'Inactif' }}
                        </span>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label text-muted">Manager superviseur</label>
                    <div>
                        @if($employee->manager)
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center me-2" 
                                     style="width: 30px; height: 30px;">
                                    <span class="text-white" style="font-size: 12px;">
                                        {{ substr($employee->manager->name, 0, 1) }}
                                    </span>
                                </div>
                                <div>
                                    <div class="font-weight-bold">{{ $employee->manager->name }}</div>
                                    <small class="text-muted">{{ $employee->manager->email }}</small>
                                </div>
                            </div>
                        @else
                            <span class="badge badge-secondary">
                                <i class="fas fa-user-slash me-1"></i>
                                Employé indépendant
                            </span>
                        @endif
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
                    <a href="{{ route('admin.login-history.show', ['user_type' => 'Employee', 'user_id' => $employee->id]) }}" 
                       class="btn btn-outline-info btn-sm">
                        <i class="fas fa-history me-2"></i>Historique des connexions
                    </a>
                    @if($employee->manager)
                        <a href="{{ route('admin.managers.show', $employee->manager) }}" 
                           class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-user-tie me-2"></i>Voir son manager
                        </a>
                    @endif
                    <button class="btn btn-outline-success btn-sm" onclick="sendWelcomeEmail()">
                        <i class="fas fa-envelope me-2"></i>Renvoyer email de bienvenue
                    </button>
                    @if(!$employee->manager)
                        <button class="btn btn-outline-warning btn-sm" onclick="assignManager()">
                            <i class="fas fa-user-plus me-2"></i>Assigner un manager
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Détails et informations -->
    <div class="col-lg-8">
        <!-- Statistiques -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card stats-card stats-card-success h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-calendar-plus fa-2x text-success mb-2"></i>
                        <h4 class="font-weight-bold">{{ $employee->created_at->diffInDays() }}</h4>
                        <p class="text-muted mb-0">Jours</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card stats-card-info h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-sign-in-alt fa-2x text-info mb-2"></i>
                        <h4 class="font-weight-bold">{{ $employee->loginHistory()->successful()->count() }}</h4>
                        <p class="text-muted mb-0">Connexions</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card stats-card-warning h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-times-circle fa-2x text-warning mb-2"></i>
                        <h4 class="font-weight-bold">{{ $employee->loginHistory()->failed()->count() }}</h4>
                        <p class="text-muted mb-0">Échecs</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card stats-card-primary h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-user-tie fa-2x text-primary mb-2"></i>
                        <h4 class="font-weight-bold">{{ $employee->manager ? '1' : '0' }}</h4>
                        <p class="text-muted mb-0">Manager</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Informations du manager -->
        @if($employee->manager)
        <div class="card shadow mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="fas fa-user-tie me-2"></i>Manager superviseur
                </h6>
                <a href="{{ route('admin.managers.show', $employee->manager) }}" class="btn btn-sm btn-outline-primary">
                    Voir détails
                </a>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2">
                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center mx-auto" 
                             style="width: 60px; height: 60px;">
                            <span class="text-white font-weight-bold" style="font-size: 24px;">
                                {{ substr($employee->manager->name, 0, 1) }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-10">
                        <h5 class="mb-2">{{ $employee->manager->name }}</h5>
                        <div class="mb-2">
                            <i class="fas fa-envelope text-muted me-2"></i>
                            <a href="mailto:{{ $employee->manager->email }}">{{ $employee->manager->email }}</a>
                        </div>
                        @if($employee->manager->phone)
                        <div class="mb-2">
                            <i class="fas fa-phone text-muted me-2"></i>
                            <a href="tel:{{ $employee->manager->phone }}">{{ $employee->manager->phone }}</a>
                        </div>
                        @endif
                        <div>
                            <span class="badge {{ $employee->manager->is_active ? 'badge-success' : 'badge-danger' }}">
                                {{ $employee->manager->is_active ? 'Actif' : 'Inactif' }}
                            </span>
                            <span class="badge badge-info ms-2">
                                {{ $employee->manager->employees()->count() }} employé(s) supervisé(s)
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        
        <!-- Historique récent des connexions -->
        <div class="card shadow mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="fas fa-history me-2"></i>Connexions récentes
                </h6>
                <a href="{{ route('admin.login-history.show', ['user_type' => 'Employee', 'user_id' => $employee->id]) }}" 
                   class="btn btn-sm btn-outline-info">
                    Voir tout
                </a>
            </div>
            <div class="card-body">
                @php
                    $recentLogins = $employee->loginHistory()->latest()->take(5)->get();
                @endphp
                
                @if($recentLogins->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date/Heure</th>
                                    <th>IP</th>
                                    <th>Navigateur</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentLogins as $login)
                                    <tr>
                                        <td>
                                            <div>{{ $login->login_at->format('d/m/Y') }}</div>
                                            <small class="text-muted">{{ $login->login_at->format('H:i:s') }}</small>
                                        </td>
                                        <td><code>{{ $login->ip_address }}</code></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($login->browser_name == 'Chrome')
                                                    <i class="fab fa-chrome text-warning me-1"></i>
                                                @elseif($login->browser_name == 'Firefox')
                                                    <i class="fab fa-firefox text-orange me-1"></i>
                                                @elseif($login->browser_name == 'Safari')
                                                    <i class="fab fa-safari text-primary me-1"></i>
                                                @else
                                                    <i class="fas fa-globe me-1"></i>
                                                @endif
                                                {{ $login->browser_name }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $login->is_successful ? 'badge-success' : 'badge-danger' }}">
                                                {{ $login->is_successful ? 'Réussie' : 'Échouée' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-history fa-3x text-muted mb-3"></i>
                        <h6 class="text-muted">Aucune connexion</h6>
                        <p class="text-muted">Cet employé ne s'est pas encore connecté.</p>
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
                            <div>{{ $employee->created_at->format('d/m/Y à H:i') }}</div>
                            <small class="text-muted">{{ $employee->created_at->diffForHumans() }}</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">Dernière modification</label>
                            <div>{{ $employee->updated_at->format('d/m/Y à H:i') }}</div>
                            <small class="text-muted">{{ $employee->updated_at->diffForHumans() }}</small>
                        </div>
                    </div>
                </div>
                
                @if($employee->loginHistory()->latest()->first())
                    @php $lastLogin = $employee->loginHistory()->latest()->first(); @endphp
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Dernière connexion</label>
                                <div>{{ $lastLogin->login_at->format('d/m/Y à H:i') }}</div>
                                <small class="text-muted">{{ $lastLogin->login_at->diffForHumans() }}</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Dernière IP</label>
                                <div><code>{{ $lastLogin->ip_address }}</code></div>
                                @if($lastLogin->country || $lastLogin->city)
                                    <small class="text-muted">{{ $lastLogin->city }}, {{ $lastLogin->country }}</small>
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Cet employé ne s'est jamais connecté.
                    </div>
                @endif
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">Admin propriétaire</label>
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-info d-flex align-items-center justify-content-center me-2" 
                                     style="width: 25px; height: 25px;">
                                    <span class="text-white" style="font-size: 10px;">
                                        {{ substr($employee->admin->name, 0, 1) }}
                                    </span>
                                </div>
                                <div>
                                    <div class="font-weight-bold" style="font-size: 13px;">{{ $employee->admin->name }}</div>
                                    <small class="text-muted">{{ $employee->admin->shop_name }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">ID unique</label>
                            <div><code>#{{ $employee->id }}</code></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal d'assignation de manager -->
<div class="modal fade" id="assignManagerModal" tabindex="-1" aria-labelledby="assignManagerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="assignManagerModalLabel">
                    <i class="fas fa-user-plus me-2"></i>Assigner un manager
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('admin.employees.update', $employee) }}">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="modal_manager_id" class="form-label">Sélectionner un manager</label>
                        <select class="form-select" name="manager_id" id="modal_manager_id" required>
                            <option value="">Choisir un manager...</option>
                            @foreach($employee->admin->managers()->where('is_active', true)->get() as $manager)
                                <option value="{{ $manager->id }}">
                                    {{ $manager->name }} - {{ $manager->email }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Conserver les autres champs -->
                    <input type="hidden" name="name" value="{{ $employee->name }}">
                    <input type="hidden" name="email" value="{{ $employee->email }}">
                    <input type="hidden" name="phone" value="{{ $employee->phone }}">
                    <input type="hidden" name="is_active" value="{{ $employee->is_active ? '1' : '0' }}">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Annuler
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save me-2"></i>Assigner
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function sendWelcomeEmail() {
    if (confirm('Envoyer un email de bienvenue à {{ $employee->name }} ?')) {
        // Ici vous pouvez ajouter une requête AJAX pour envoyer l'email
        alert('Fonctionnalité à implémenter : envoi d\'email de bienvenue');
    }
}

function assignManager() {
    const modal = new bootstrap.Modal(document.getElementById('assignManagerModal'));
    modal.show();
}
</script>
@endsection