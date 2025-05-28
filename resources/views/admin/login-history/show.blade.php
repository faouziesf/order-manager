@extends('layouts.admin')

@section('title', 'Historique des Connexions')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Historique des Connexions</h1>
        @if($user)
            <p class="text-muted">
                Connexions de <strong>{{ $user->name }}</strong> 
                <span class="badge {{ str_contains($userType, 'Manager') ? 'badge-primary' : 'badge-success' }}">
                    {{ str_contains($userType, 'Manager') ? 'Manager' : 'Employé' }}
                </span>
            </p>
        @else
            <p class="text-muted">Utilisateur non trouvé ou supprimé</p>
        @endif
    </div>
    <div class="d-flex gap-2">
        @if($user)
            @if(str_contains($userType, 'Manager'))
                <a href="{{ route('admin.managers.show', $user->id) }}" class="btn btn-info">
                    <i class="fas fa-user-tie me-2"></i>Voir Profil
                </a>
            @else
                <a href="{{ route('admin.employees.show', $user->id) }}" class="btn btn-success">
                    <i class="fas fa-user-friends me-2"></i>Voir Profil
                </a>
            @endif
        @endif
        <a href="{{ route('admin.login-history.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Retour
        </a>
    </div>
</div>

@if($user)
    <!-- Profile Header -->
    <div class="card shadow mb-4">
        <div class="card-header {{ str_contains($userType, 'Manager') ? 'bg-primary' : 'bg-success' }} text-white">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-white d-flex align-items-center justify-content-center me-3" 
                     style="width: 50px; height: 50px;">
                    <span class="{{ str_contains($userType, 'Manager') ? 'text-primary' : 'text-success' }} font-weight-bold" style="font-size: 20px;">
                        {{ substr($user->name, 0, 1) }}
                    </span>
                </div>
                <div>
                    <h5 class="mb-1">{{ $user->name }}</h5>
                    <div class="opacity-75">{{ $user->email }}</div>
                    @if($user->phone)
                        <small class="opacity-75">{{ $user->phone }}</small>
                    @endif
                </div>
                <div class="ms-auto">
                    <span class="badge {{ $user->is_active ? 'badge-light' : 'badge-warning' }}">
                        {{ $user->is_active ? 'Actif' : 'Inactif' }}
                    </span>
                </div>
            </div>
        </div>
    </div>
@endif

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card stats-card-primary h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="stats-card-label">Total Connexions</div>
                        <div class="stats-card-number">{{ $stats['total_logins'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-sign-in-alt stats-card-icon text-primary"></i>
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
                        <div class="stats-card-label">Connexions Réussies</div>
                        <div class="stats-card-number">{{ $stats['successful_logins'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle stats-card-icon text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card stats-card-danger h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="stats-card-label">Connexions Échouées</div>
                        <div class="stats-card-number">{{ $stats['failed_logins'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-times-circle stats-card-icon text-danger"></i>
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
                        <div class="stats-card-label">Dernière Connexion</div>
                        <div class="stats-card-number" style="font-size: 14px;">
                            @if($stats['last_login'])
                                {{ $stats['last_login']->login_at->format('d/m/Y') }}
                                <br><small>{{ $stats['last_login']->login_at->format('H:i') }}</small>
                            @else
                                Jamais
                            @endif
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock stats-card-icon text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold {{ str_contains($userType, 'Manager') ? 'text-primary' : 'text-success' }}">
            Filtres et Options
        </h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.login-history.show', ['user_type' => $userType, 'user_id' => $user ? $user->id : 0]) }}">
            <div class="row">
                <div class="col-md-3">
                    <label for="date_from" class="form-label">Date de début</label>
                    <input type="date" class="form-control" name="date_from" id="date_from" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <label for="date_to" class="form-label">Date de fin</label>
                    <input type="date" class="form-control" name="date_to" id="date_to" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3">
                    <label for="is_successful" class="form-label">Statut</label>
                    <select class="form-select" name="is_successful" id="is_successful">
                        <option value="">Tous</option>
                        <option value="1" {{ request('is_successful') === '1' ? 'selected' : '' }}>Réussie</option>
                        <option value="0" {{ request('is_successful') === '0' ? 'selected' : '' }}>Échouée</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn {{ str_contains($userType, 'Manager') ? 'btn-primary' : 'btn-success' }} me-2">
                        <i class="fas fa-filter me-2"></i>Filtrer
                    </button>
                    <a href="{{ route('admin.login-history.show', ['user_type' => $userType, 'user_id' => $user ? $user->id : 0]) }}" 
                       class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- History Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold {{ str_contains($userType, 'Manager') ? 'text-primary' : 'text-success' }}">
            Historique Détaillé des Connexions
        </h6>
        <div class="text-muted small">
            {{ $loginHistories->total() }} connexion(s) au total
        </div>
    </div>
    <div class="card-body">
        @if($loginHistories->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Date/Heure</th>
                            <th>Adresse IP</th>
                            <th>Navigateur</th>
                            <th>Appareil</th>
                            <th>Localisation</th>
                            <th>Durée Session</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($loginHistories as $history)
                            <tr class="{{ !$history->is_successful ? 'table-danger' : '' }}">
                                <td>
                                    <div class="font-weight-bold">{{ $history->login_at->format('d/m/Y') }}</div>
                                    <small class="text-muted">{{ $history->login_at->format('H:i:s') }}</small>
                                    <br>
                                    <small class="text-muted">{{ $history->login_at->diffForHumans() }}</small>
                                </td>
                                <td>
                                    <code>{{ $history->ip_address }}</code>
                                    @if($history->ip_address !== '127.0.0.1')
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-globe me-1"></i>Externe
                                        </small>
                                    @else
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-home me-1"></i>Local
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($history->browser_name == 'Chrome')
                                            <i class="fab fa-chrome text-warning me-2"></i>
                                        @elseif($history->browser_name == 'Firefox')
                                            <i class="fab fa-firefox text-orange me-2"></i>
                                        @elseif($history->browser_name == 'Safari')
                                            <i class="fab fa-safari text-primary me-2"></i>
                                        @elseif($history->browser_name == 'Edge')
                                            <i class="fab fa-edge text-info me-2"></i>
                                        @else
                                            <i class="fas fa-globe me-2"></i>
                                        @endif
                                        <div>
                                            <div>{{ $history->browser_name }}</div>
                                            <small class="text-muted">{{ Str::limit($history->user_agent, 30) }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($history->device_type == 'Mobile')
                                            <i class="fas fa-mobile-alt text-success me-2"></i>
                                        @elseif($history->device_type == 'Tablette')
                                            <i class="fas fa-tablet-alt text-info me-2"></i>
                                        @else
                                            <i class="fas fa-desktop text-primary me-2"></i>
                                        @endif
                                        {{ $history->device_type }}
                                    </div>
                                </td>
                                <td>
                                    @if($history->country || $history->city)
                                        <div>
                                            <i class="fas fa-map-marker-alt text-danger me-1"></i>
                                            {{ $history->city }}
                                        </div>
                                        <small class="text-muted">{{ $history->country }}</small>
                                    @else
                                        <span class="text-muted">
                                            <i class="fas fa-question-circle me-1"></i>
                                            Non déterminé
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($history->logout_at)
                                        @php
                                            $duration = $history->login_at->diffInMinutes($history->logout_at);
                                        @endphp
                                        <div class="text-success">
                                            <i class="fas fa-clock me-1"></i>
                                            @if($duration < 60)
                                                {{ $duration }} min
                                            @else
                                                {{ intval($duration / 60) }}h {{ $duration % 60 }}min
                                            @endif
                                        </div>
                                        <small class="text-muted">
                                            Déconnexion: {{ $history->logout_at->format('H:i') }}
                                        </small>
                                    @else
                                        @if($history->is_successful)
                                            <span class="text-warning">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                Session active
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    @if($history->is_successful)
                                        <span class="badge badge-success">
                                            <i class="fas fa-check me-1"></i>Réussie
                                        </span>
                                    @else
                                        <span class="badge badge-danger">
                                            <i class="fas fa-times me-1"></i>Échouée
                                        </span>
                                        <br>
                                        <small class="text-danger">Tentative d'intrusion</small>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($loginHistories->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $loginHistories->appends(request()->query())->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <i class="fas fa-history fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Aucune connexion trouvée</h5>
                @if($user)
                    <p class="text-muted">
                        {{ $user->name }} ne s'est pas encore connecté{{ request()->anyFilled(['date_from', 'date_to', 'is_successful']) ? ' avec ces critères' : '' }}.
                    </p>
                @else
                    <p class="text-muted">Utilisateur non trouvé.</p>
                @endif
            </div>
        @endif
    </div>
</div>

<!-- Summary Card -->
@if($loginHistories->count() > 0)
<div class="row">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header {{ str_contains($userType, 'Manager') ? 'bg-primary' : 'bg-success' }} text-white">
                <h6 class="mb-0">
                    <i class="fas fa-chart-pie me-2"></i>Résumé des Connexions
                </h6>
            </div>
            <div class="card-body">
                @php
                    $successRate = $stats['total_logins'] > 0 ? round(($stats['successful_logins'] / $stats['total_logins']) * 100, 1) : 0;
                @endphp
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Taux de réussite</span>
                        <strong>{{ $successRate }}%</strong>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-success" style="width: {{ $successRate }}%"></div>
                    </div>
                </div>
                
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <h4 class="text-success">{{ $stats['successful_logins'] }}</h4>
                            <small class="text-muted">Réussies</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h4 class="text-danger">{{ $stats['failed_logins'] }}</h4>
                        <small class="text-muted">Échouées</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header {{ str_contains($userType, 'Manager') ? 'bg-primary' : 'bg-success' }} text-white">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>Informations Utiles
                </h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="fas fa-calendar text-info me-2"></i>
                        <strong>Première connexion:</strong>
                        @if($stats['total_logins'] > 0)
                            {{ $loginHistories->last()->login_at->format('d/m/Y à H:i') }}
                        @else
                            Jamais
                        @endif
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-clock text-warning me-2"></i>
                        <strong>Dernière connexion:</strong>
                        @if($stats['last_login'])
                            {{ $stats['last_login']->login_at->format('d/m/Y à H:i') }}
                        @else
                            Jamais
                        @endif
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-user-clock text-success me-2"></i>
                        <strong>Compte créé:</strong>
                        @if($user)
                            {{ $user->created_at->format('d/m/Y') }}
                        @endif
                    </li>
                    <li>
                        <i class="fas fa-shield-alt text-danger me-2"></i>
                        <strong>Sécurité:</strong>
                        @if($stats['failed_logins'] == 0)
                            <span class="text-success">Aucune tentative suspecte</span>
                        @else
                            <span class="text-warning">{{ $stats['failed_logins'] }} tentative(s) échouée(s)</span>
                        @endif
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
// Auto-submit form when dates change
document.getElementById('date_from').addEventListener('change', function() {
    if (this.value && document.getElementById('date_to').value) {
        this.form.submit();
    }
});

document.getElementById('date_to').addEventListener('change', function() {
    if (this.value && document.getElementById('date_from').value) {
        this.form.submit();
    }
});

// Tooltip for long user agents
document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endsection