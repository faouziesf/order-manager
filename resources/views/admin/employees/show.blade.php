@extends('layouts.admin')

@section('title', 'Détails de l\'Employé')

@section('css')
@include('admin.partials._shared-styles')
<style>
    .emp-profile-card {
        background: var(--bg-card); border: 1px solid var(--border);
        border-radius: var(--radius, 14px); overflow: hidden;
    }
    .emp-profile-header {
        padding: 1.75rem 1.5rem; text-align: center;
        background: var(--bg-muted); border-bottom: 1px solid var(--border);
    }
    .emp-profile-avatar {
        width: 72px; height: 72px;
        border-radius: var(--radius, 14px);
        background: var(--primary-50, #eef2ff); color: var(--primary);
        display: inline-flex; align-items: center; justify-content: center;
        font-weight: 800; font-size: 1.75rem; margin-bottom: 0.75rem;
    }
    .emp-profile-header h4 { color: var(--text); font-weight: 800; margin: 0 0 0.2rem; }
    .emp-profile-header p { color: var(--text-secondary); margin: 0; font-size: 0.85rem; }
    .emp-mini-stat {
        background: var(--bg-card); border: 1px solid var(--border);
        border-radius: var(--radius, 14px); padding: 1.15rem;
        text-align: center; height: 100%; transition: transform 0.2s, box-shadow 0.2s;
    }
    .emp-mini-stat:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
    .emp-mini-stat .stat-icon {
        width: 44px; height: 44px; border-radius: var(--radius-sm, 10px);
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 1rem; margin-bottom: 0.6rem;
    }
    .emp-mini-stat h4 { color: var(--text); font-weight: 800; margin: 0 0 0.15rem; }
    .emp-mini-stat small { color: var(--text-secondary); font-size: 0.78rem; }
    .emp-section-card {
        background: var(--bg-card); border: 1px solid var(--border);
        border-radius: var(--radius, 14px); overflow: hidden; margin-bottom: 1rem;
    }
    .emp-section-head {
        padding: 1rem 1.25rem; background: var(--bg-muted); border-bottom: 1px solid var(--border);
        display: flex; align-items: center; justify-content: space-between;
    }
    .emp-section-head h6 { margin: 0; font-weight: 700; color: var(--text); font-size: 0.9rem; display: flex; align-items: center; gap: 0.5rem; }
    .emp-section-head h6 i { color: var(--primary); }
    .emp-page-header h1 { color: var(--text); font-weight: 800; letter-spacing: -0.02em; }
    .emp-page-header p { color: var(--text-secondary); }
    .sticky-sidebar { position: sticky; top: 80px; }
    @media (max-width: 991.98px) { .sticky-sidebar { position: static; } }
</style>
@endsection

@section('content')
<div class="container-fluid animate-fade-in" x-data="employeeDetailsPage()">
    <!-- Header Section -->
    <div class="row mb-4 emp-page-header">
        <div class="col-md-8">
            <h1 class="h2 mb-2">Détails de l'Employé</h1>
            <p class="mb-0">Informations complètes de {{ $employee->name }}</p>
        </div>
        <div class="col-md-4 text-end">
            <div class="btn-group" role="group">
                <a href="{{ route('admin.employees.edit', $employee) }}" 
                   class="btn btn-warning">
                    <i class="fas fa-edit me-2"></i>
                    Modifier
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
                <a href="{{ route('admin.employees.index') }}" 
                   class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>
                    Retour
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Sidebar - Informations principales -->
        <div class="col-lg-4">
            <div class="sticky-sidebar">
                <!-- Profile Card -->
                <div class="emp-profile-card mb-4">
                    <!-- Header avec avatar -->
                    <div class="emp-profile-header">
                        <div class="emp-profile-avatar">
                            {{ substr($employee->name, 0, 1) }}
                        </div>
                        <h4>{{ $employee->name }}</h4>
                        <p>Employé</p>
                    </div>
                    
                    <!-- Informations de contact -->
                    <div class="card-body">
                        <!-- Email -->
                        <div class="mb-3">
                            <label class="form-label small fw-medium text-muted mb-1">Email</label>
                            <div class="d-flex align-items-center">
                                <div class="bg-success bg-opacity-10 rounded-3 p-2 me-3">
                                    <i class="fas fa-envelope text-success"></i>
                                </div>
                                <a href="mailto:{{ $employee->email }}" 
                                   class="text-decoration-none">
                                    {{ $employee->email }}
                                </a>
                            </div>
                        </div>
                        
                        @if($employee->phone)
                        <!-- Téléphone -->
                        <div class="mb-3">
                            <label class="form-label small fw-medium text-muted mb-1">Téléphone</label>
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 rounded-3 p-2 me-3">
                                    <i class="fas fa-phone text-primary"></i>
                                </div>
                                <a href="tel:{{ $employee->phone }}" 
                                   class="text-decoration-none">
                                    {{ $employee->phone }}
                                </a>
                            </div>
                        </div>
                        @endif
                        
                        <!-- Statut -->
                        <div class="mb-3">
                            <label class="form-label small fw-medium text-muted mb-1">Statut</label>
                            <div>
                                <span class="badge {{ $employee->is_active ? 'bg-success' : 'bg-danger' }} fs-6">
                                    <i class="fas {{ $employee->is_active ? 'fa-check-circle' : 'fa-times-circle' }} me-1"></i>
                                    {{ $employee->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </div>
                        </div>
                        
                        <!-- Manager -->
                        <div class="mb-0">
                            <label class="form-label small fw-medium text-muted mb-1">Manager superviseur</label>
                            <div>
                                @if($employee->manager)
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary rounded-circle p-2 me-3 text-white fw-bold text-center" style="width: 32px; height: 32px; line-height: 16px; font-size: 14px;">
                                            {{ substr($employee->manager->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold small">{{ $employee->manager->name }}</div>
                                            <small class="text-muted">{{ $employee->manager->email }}</small>
                                        </div>
                                    </div>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-user-slash me-1"></i>
                                        Employé indépendant
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Actions rapides -->
                <div class="emp-section-card">
                    <div class="emp-section-head">
                        <h6><i class="fas fa-bolt"></i> Actions rapides</h6>
                    </div>
                    <div class="card-body p-2">
                        <div class="d-grid gap-2">
                            <a href="{{ route('admin.login-history.show', ['user_type' => 'Employee', 'user_id' => $employee->id]) }}" 
                               class="btn btn-outline-primary btn-sm text-start">
                                <i class="fas fa-history me-2"></i>
                                Historique des connexions
                            </a>
                            
                            @if($employee->manager)
                                <a href="{{ route('admin.managers.show', $employee->manager) }}" 
                                   class="btn btn-outline-info btn-sm text-start">
                                    <i class="fas fa-user-tie me-2"></i>
                                    Voir son manager
                                </a>
                            @endif
                            
                            <button @click="sendWelcomeEmail()" 
                                    class="btn btn-outline-success btn-sm text-start">
                                <i class="fas fa-envelope me-2"></i>
                                Renvoyer email de bienvenue
                            </button>
                            
                            @if(!$employee->manager)
                                <button @click="showAssignManagerModal = true" 
                                        class="btn btn-outline-warning btn-sm text-start">
                                    <i class="fas fa-user-plus me-2"></i>
                                    Assigner un manager
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main content -->
        <div class="col-lg-8">
            <!-- Statistiques -->
            <div class="row g-3 mb-4">
                <!-- Jours depuis création -->
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 shadow-sm text-center h-100">
                        <div class="card-body">
                            <div class="bg-success bg-opacity-10 rounded-3 p-3 d-inline-block mb-3">
                                <i class="fas fa-calendar-plus text-success fa-lg"></i>
                            </div>
                            <h4 class="fw-bold mb-1">{{ floor($employee->created_at->diffInDays()) }}</h4>
                            <small class="text-muted">Jours</small>
                        </div>
                    </div>
                </div>
                
                <!-- Connexions réussies -->
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 shadow-sm text-center h-100">
                        <div class="card-body">
                            <div class="bg-primary bg-opacity-10 rounded-3 p-3 d-inline-block mb-3">
                                <i class="fas fa-sign-in-alt text-primary fa-lg"></i>
                            </div>
                            <h4 class="fw-bold mb-1">{{ $employee->loginHistory()->successful()->count() }}</h4>
                            <small class="text-muted">Connexions</small>
                        </div>
                    </div>
                </div>
                
                <!-- Échecs de connexion -->
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 shadow-sm text-center h-100">
                        <div class="card-body">
                            <div class="bg-warning bg-opacity-10 rounded-3 p-3 d-inline-block mb-3">
                                <i class="fas fa-times-circle text-warning fa-lg"></i>
                            </div>
                            <h4 class="fw-bold mb-1">{{ $employee->loginHistory()->failed()->count() }}</h4>
                            <small class="text-muted">Échecs</small>
                        </div>
                    </div>
                </div>
                
                <!-- Manager -->
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 shadow-sm text-center h-100">
                        <div class="card-body">
                            <div class="bg-info bg-opacity-10 rounded-3 p-3 d-inline-block mb-3">
                                <i class="fas fa-user-tie text-info fa-lg"></i>
                            </div>
                            <h4 class="fw-bold mb-1">{{ $employee->manager ? '1' : '0' }}</h4>
                            <small class="text-muted">Manager</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Informations du manager -->
            @if($employee->manager)
            <div class="card shadow border-0 mb-4">
                <div class="card-header bg-primary bg-opacity-10">
                    <div class="d-flex align-items-center justify-content-between">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-user-tie text-primary me-2"></i>
                            Manager superviseur
                        </h6>
                        <a href="{{ route('admin.managers.show', $employee->manager) }}" 
                           class="btn btn-outline-primary btn-sm">
                            Voir détails
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary rounded-4 p-3 me-4 text-white fw-bold text-center" style="width: 64px; height: 64px; line-height: 40px; font-size: 20px;">
                            {{ substr($employee->manager->name, 0, 1) }}
                        </div>
                        <div class="flex-fill">
                            <h5 class="fw-semibold mb-2">{{ $employee->manager->name }}</h5>
                            <div class="row g-2 small">
                                <div class="col-12">
                                    <div class="d-flex align-items-center text-muted">
                                        <i class="fas fa-envelope me-2"></i>
                                        <a href="mailto:{{ $employee->manager->email }}" class="text-decoration-none">
                                            {{ $employee->manager->email }}
                                        </a>
                                    </div>
                                </div>
                                @if($employee->manager->phone)
                                <div class="col-12">
                                    <div class="d-flex align-items-center text-muted">
                                        <i class="fas fa-phone me-2"></i>
                                        <a href="tel:{{ $employee->manager->phone }}" class="text-decoration-none">
                                            {{ $employee->manager->phone }}
                                        </a>
                                    </div>
                                </div>
                                @endif
                            </div>
                            <div class="d-flex gap-2 mt-3">
                                <span class="badge {{ $employee->manager->is_active ? 'bg-success' : 'bg-danger' }}">
                                    {{ $employee->manager->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                                <span class="badge bg-primary">
                                    {{ \App\Models\Admin::where('role', \App\Models\Admin::ROLE_EMPLOYEE)->where('manager_id', $employee->manager->id)->count() }} employé(s) supervisé(s)
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            
            <!-- Historique récent des connexions -->
            <div class="card shadow border-0 mb-4">
                <div class="card-header bg-light">
                    <div class="d-flex align-items-center justify-content-between">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-history text-muted me-2"></i>
                            Connexions récentes
                        </h6>
                        <a href="{{ route('admin.login-history.show', ['user_type' => 'Employee', 'user_id' => $employee->id]) }}" 
                           class="btn btn-outline-secondary btn-sm">
                            Voir tout
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @php
                        $recentLogins = $employee->loginHistory()->latest()->take(5)->get();
                    @endphp
                    
                    @if($recentLogins->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="small">Date/Heure</th>
                                        <th class="small">IP</th>
                                        <th class="small">Navigateur</th>
                                        <th class="small">Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentLogins as $login)
                                        <tr>
                                            <td>
                                                <div class="small">{{ $login->login_at->format('d/m/Y') }}</div>
                                                <small class="text-muted">{{ $login->login_at->format('H:i:s') }}</small>
                                            </td>
                                            <td>
                                                <code class="small">{{ $login->ip_address }}</code>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center small">
                                                    @if($login->browser_name == 'Chrome')
                                                        <i class="fab fa-chrome text-warning me-1"></i>
                                                    @elseif($login->browser_name == 'Firefox')
                                                        <i class="fab fa-firefox text-danger me-1"></i>
                                                    @elseif($login->browser_name == 'Safari')
                                                        <i class="fab fa-safari text-primary me-1"></i>
                                                    @else
                                                        <i class="fas fa-globe me-1"></i>
                                                    @endif
                                                    {{ $login->browser_name }}
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge {{ $login->is_successful ? 'bg-success' : 'bg-danger' }}">
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
                            <div class="bg-light rounded-4 p-4 d-inline-block mb-3">
                                <i class="fas fa-history text-muted" style="font-size: 2rem;"></i>
                            </div>
                            <h6 class="text-muted">Aucune connexion</h6>
                            <p class="text-muted small mb-0">Cet employé ne s'est pas encore connecté.</p>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Informations système -->
            <div class="card shadow border-0">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-info-circle text-muted me-2"></i>
                        Informations système
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <!-- Dates importantes -->
                        <div class="col-md-6">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label small fw-medium text-muted mb-1">Créé le</label>
                                    <div>{{ $employee->created_at->format('d/m/Y à H:i') }}</div>
                                    <small class="text-muted">{{ $employee->created_at->diffForHumans() }}</small>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-medium text-muted mb-1">Dernière modification</label>
                                    <div>{{ $employee->updated_at->format('d/m/Y à H:i') }}</div>
                                    <small class="text-muted">{{ $employee->updated_at->diffForHumans() }}</small>
                                </div>
                            </div>
                        </div>
                        
                        @if($employee->loginHistory()->latest()->first())
                            @php $lastLogin = $employee->loginHistory()->latest()->first(); @endphp
                            <div class="col-md-6">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label small fw-medium text-muted mb-1">Dernière connexion</label>
                                        <div>{{ $lastLogin->login_at->format('d/m/Y à H:i') }}</div>
                                        <small class="text-muted">{{ $lastLogin->login_at->diffForHumans() }}</small>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-medium text-muted mb-1">Dernière IP</label>
                                        <div>
                                            <code class="small">{{ $lastLogin->ip_address }}</code>
                                        </div>
                                        @if($lastLogin->country || $lastLogin->city)
                                            <small class="text-muted">{{ $lastLogin->city }}, {{ $lastLogin->country }}</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="col-12">
                                <div class="alert alert-warning">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <small>Cet employé ne s'est jamais connecté.</small>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        <!-- Informations admin et ID -->
                        <div class="col-12">
                            <hr>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-medium text-muted mb-1">Admin propriétaire</label>
                                    <div class="d-flex align-items-center">
                                        @if($employee->creator)
                                            <div class="bg-secondary rounded-3 p-2 me-2 text-white fw-bold text-center" style="width: 24px; height: 24px; line-height: 8px; font-size: 12px;">
                                                {{ substr($employee->creator->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="fw-semibold small">{{ $employee->creator->name }}</div>
                                                <small class="text-muted">{{ $employee->creator->email }}</small>
                                            </div>
                                        @else
                                            <span class="badge bg-secondary">Aucun admin</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-medium text-muted mb-1">ID unique</label>
                                    <div>
                                        <code class="small">#{{ $employee->id }}</code>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal d'assignation de manager -->
    <div class="modal fade" :class="{ 'show d-block': showAssignManagerModal }" 
         x-show="showAssignManagerModal" 
         x-transition
         tabindex="-1" 
         style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <!-- Header -->
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">
                        <i class="fas fa-user-plus me-2"></i>
                        Assigner un manager
                    </h5>
                    <button type="button" 
                            @click="showAssignManagerModal = false" 
                            class="btn-close"></button>
                </div>
                
                <!-- Form -->
                <form method="POST" action="{{ route('admin.employees.update', $employee) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="modal_manager_id" class="form-label fw-medium">
                                Sélectionner un manager
                            </label>
                            <select name="manager_id"
                                    id="modal_manager_id"
                                    required
                                    class="form-select form-select-lg">
                                <option value="">Choisir un manager...</option>
                                @foreach(\App\Models\Admin::where('role', \App\Models\Admin::ROLE_MANAGER)->where('created_by', $employee->created_by)->where('is_active', true)->get() as $manager)
                                    <option value="{{ $manager->id }}">
                                        {{ $manager->name }} - {{ $manager->email }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Champs cachés pour conserver les autres valeurs -->
                        <input type="hidden" name="name" value="{{ $employee->name }}">
                        <input type="hidden" name="email" value="{{ $employee->email }}">
                        <input type="hidden" name="phone" value="{{ $employee->phone }}">
                        <input type="hidden" name="is_active" value="{{ $employee->is_active ? '1' : '0' }}">
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" 
                                @click="showAssignManagerModal = false"
                                class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>
                            Annuler
                        </button>
                        <button type="submit" 
                                class="btn btn-warning">
                            <i class="fas fa-save me-2"></i>
                            Assigner
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function employeeDetailsPage() {
    return {
        showAssignManagerModal: false,
        
        sendWelcomeEmail() {
            if (confirm('Envoyer un email de bienvenue à {{ $employee->name }} ?')) {
                this.showNotification('Fonctionnalité à implémenter : envoi d\'email de bienvenue', 'info');
            }
        },
        
        showNotification(message, type = 'info') {
            // Vérifier si window.toast existe, sinon utiliser une méthode de fallback
            if (typeof window.toast !== 'undefined') {
                return window.toast.show(message, type, { duration: 5000 });
            } else {
                // Fallback: créer un toast Bootstrap simple
                this.createBootstrapToast(message, type);
            }
        },
        
        createBootstrapToast(message, type) {
            const toastContainer = document.getElementById('toast-container') || this.createToastContainer();
            
            const colors = {
                success: 'text-bg-success',
                error: 'text-bg-danger',
                info: 'text-bg-info',
                warning: 'text-bg-warning'
            };
            
            const icons = {
                success: 'fa-check-circle',
                error: 'fa-exclamation-circle',
                info: 'fa-info-circle',
                warning: 'fa-exclamation-triangle'
            };
            
            const toastId = 'toast-' + Date.now();
            const toastHtml = `
                <div id="${toastId}" class="toast ${colors[type]} border-0" role="alert">
                    <div class="d-flex">
                        <div class="toast-body d-flex align-items-center">
                            <i class="fas ${icons[type]} me-2"></i>
                            ${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            `;
            
            toastContainer.insertAdjacentHTML('beforeend', toastHtml);
            const toastElement = document.getElementById(toastId);
            
            if (typeof bootstrap !== 'undefined') {
                const toast = new bootstrap.Toast(toastElement, { 
                    autohide: true,
                    delay: 5000 
                });
                toast.show();
                
                toastElement.addEventListener('hidden.bs.toast', () => {
                    toastElement.remove();
                });
            }
        },
        
        createToastContainer() {
            const container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
            return container;
        }
    }
}

// Animation d'apparition progressive
document.addEventListener('DOMContentLoaded', function() {
    // Animer les cartes de stats
    const statCards = document.querySelectorAll('.card');
    statCards.forEach((card, index) => {
        setTimeout(() => {
            card.classList.add('animate-slide-up');
        }, index * 100);
    });
    
    // Initialiser les tooltips Bootstrap avec vérification
    setTimeout(() => {
        if (typeof bootstrap !== 'undefined') {
            try {
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    if (!tooltipTriggerEl._tooltip) {
                        tooltipTriggerEl._tooltip = new bootstrap.Tooltip(tooltipTriggerEl);
                    }
                    return tooltipTriggerEl._tooltip;
                });
            } catch (error) {
                console.warn('Erreur lors de l\'initialisation des tooltips:', error);
            }
        }
    }, 500);
});
</script>

<style>
/* Animations personnalisées */
@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.animate-fade-in {
    animation: fadeIn 0.5s ease-out;
}

.animate-slide-up {
    animation: slideUp 0.5s ease-out forwards;
}

/* Gradient de succès personnalisé */
.bg-gradient-success { background: var(--bg-muted); }

/* Modern animation */
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.animate-fade-in { animation: fadeIn 0.3s ease-out; }

@media (max-width: 576px) {
    .btn-group { flex-direction: column; width: 100%; gap: 0.25rem; }
    .btn-group .btn { border-radius: 0.375rem !important; }
}
</style>
@endsection