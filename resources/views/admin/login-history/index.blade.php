@extends('layouts.admin')

@section('title', 'Historique des Connexions')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Historique des Connexions</h1>
        <p class="text-muted">Suivi des connexions de tous vos utilisateurs</p>
    </div>
</div>

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
                        <div class="stats-card-label">IPs Uniques</div>
                        <div class="stats-card-number">{{ $stats['unique_ips'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-globe stats-card-icon text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Filtres</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.login-history.index') }}">
            <div class="row">
                <div class="col-md-3">
                    <label for="user_type" class="form-label">Type d'utilisateur</label>
                    <select class="form-select" name="user_type" id="user_type">
                        <option value="">Tous les types</option>
                        <option value="Admin" {{ request('user_type') == 'Admin' ? 'selected' : '' }}>Admin</option>
                        <option value="Manager" {{ request('user_type') == 'Manager' ? 'selected' : '' }}>Manager</option>
                        <option value="Employee" {{ request('user_type') == 'Employee' ? 'selected' : '' }}>Employé</option>
                    </select>
                </div>
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
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter me-2"></i>Filtrer
                    </button>
                    <a href="{{ route('admin.login-history.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Réinitialiser
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- History Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Historique des Connexions</h6>
    </div>
    <div class="card-body">
        @if($loginHistories->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Utilisateur</th>
                            <th>Type</th>
                            <th>Date/Heure</th>
                            <th>Adresse IP</th>
                            <th>Navigateur</th>
                            <th>Appareil</th>
                            <th>Localisation</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($loginHistories as $history)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-{{ $history->is_successful ? 'success' : 'danger' }} d-flex align-items-center justify-content-center me-2" 
                                             style="width: 30px; height: 30px;">
                                            <span class="text-white" style="font-size: 12px;">
                                                {{ $history->user ? substr($history->user->name, 0, 1) : '?' }}
                                            </span>
                                        </div>
                                        <div>
                                            <div class="font-weight-bold" style="font-size: 13px;">
                                                {{ $history->user ? $history->user->name : 'Utilisateur supprimé' }}
                                            </div>
                                            @if($history->user)
                                                <small class="text-muted">{{ $history->user->email }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if(str_contains($history->user_type, 'Admin'))
                                        <span class="badge badge-primary">Admin</span>
                                    @elseif(str_contains($history->user_type, 'Manager'))
                                        <span class="badge badge-info">Manager</span>
                                    @elseif(str_contains($history->user_type, 'Employee'))
                                        <span class="badge badge-success">Employé</span>
                                    @else
                                        <span class="badge badge-secondary">Inconnu</span>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $history->login_at->format('d/m/Y') }}</div>
                                    <small class="text-muted">{{ $history->login_at->format('H:i:s') }}</small>
                                </td>
                                <td>
                                    <code>{{ $history->ip_address }}</code>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($history->browser_name == 'Chrome')
                                            <i class="fab fa-chrome text-warning me-1"></i>
                                        @elseif($history->browser_name == 'Firefox')
                                            <i class="fab fa-firefox text-orange me-1"></i>
                                        @elseif($history->browser_name == 'Safari')
                                            <i class="fab fa-safari text-primary me-1"></i>
                                        @elseif($history->browser_name == 'Edge')
                                            <i class="fab fa-edge text-info me-1"></i>
                                        @else
                                            <i class="fas fa-globe me-1"></i>
                                        @endif
                                        {{ $history->browser_name }}
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($history->device_type == 'Mobile')
                                            <i class="fas fa-mobile-alt me-1"></i>
                                        @elseif($history->device_type == 'Tablette')
                                            <i class="fas fa-tablet-alt me-1"></i>
                                        @else
                                            <i class="fas fa-desktop me-1"></i>
                                        @endif
                                        {{ $history->device_type }}
                                    </div>
                                </td>
                                <td>
                                    @if($history->country || $history->city)
                                        <div>{{ $history->city }}</div>
                                        <small class="text-muted">{{ $history->country }}</small>
                                    @else
                                        <span class="text-muted">Non déterminé</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $history->is_successful ? 'badge-success' : 'badge-danger' }}">
                                        {{ $history->is_successful ? 'Réussie' : 'Échouée' }}
                                    </span>
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
                <h5 class="text-muted">Aucun historique</h5>
                <p class="text-muted">Aucune connexion trouvée avec les critères sélectionnés.</p>
            </div>
        @endif
    </div>
</div>
@endsection