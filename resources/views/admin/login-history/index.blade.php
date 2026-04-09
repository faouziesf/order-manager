@extends('layouts.admin')

@section('title', 'Historique des Connexions')

@section('css')
@include('admin.partials._shared-styles')
@endsection

@section('content')
<div class="container-fluid om-animate">
    <div class="om-page-header">
        <div>
            <h1 class="om-page-title">Historique des Connexions</h1>
            <p class="om-page-subtitle">Suivi des connexions de tous vos utilisateurs</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="om-stat" style="--stat-color: var(--om-primary)">
                <div class="d-flex align-items-center gap-3">
                    <div class="om-stat-icon"><i class="fas fa-sign-in-alt"></i></div>
                    <div><div class="om-stat-value">{{ $stats['total_logins'] }}</div><div class="om-stat-label">Total</div></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="om-stat" style="--stat-color: var(--om-success)">
                <div class="d-flex align-items-center gap-3">
                    <div class="om-stat-icon"><i class="fas fa-check-circle"></i></div>
                    <div><div class="om-stat-value">{{ $stats['successful_logins'] }}</div><div class="om-stat-label">Réussies</div></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="om-stat" style="--stat-color: var(--om-danger)">
                <div class="d-flex align-items-center gap-3">
                    <div class="om-stat-icon"><i class="fas fa-times-circle"></i></div>
                    <div><div class="om-stat-value">{{ $stats['failed_logins'] }}</div><div class="om-stat-label">Échouées</div></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="om-stat" style="--stat-color: var(--om-info)">
                <div class="d-flex align-items-center gap-3">
                    <div class="om-stat-icon"><i class="fas fa-globe"></i></div>
                    <div><div class="om-stat-value">{{ $stats['unique_ips'] }}</div><div class="om-stat-label">IPs uniques</div></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="om-card mb-4">
        <div class="om-card-header"><h6 class="fw-bold mb-0"><i class="fas fa-filter me-2" style="color: var(--om-primary);"></i>Filtres</h6></div>
        <div class="om-card-body">
            <form method="GET" action="{{ route('admin.login-history.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="om-form-label">Type</label>
                        <select class="form-select" name="user_type" style="border-radius: var(--om-radius-sm);">
                            <option value="">Tous</option>
                            <option value="Admin" {{ request('user_type') == 'Admin' ? 'selected' : '' }}>Admin</option>
                            <option value="Manager" {{ request('user_type') == 'Manager' ? 'selected' : '' }}>Manager</option>
                            <option value="Employee" {{ request('user_type') == 'Employee' ? 'selected' : '' }}>Employé</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="om-form-label">Date début</label>
                        <input type="date" class="om-form-input" name="date_from" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="om-form-label">Date fin</label>
                        <input type="date" class="om-form-input" name="date_to" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="om-form-label">Statut</label>
                        <select class="form-select" name="is_successful" style="border-radius: var(--om-radius-sm);">
                            <option value="">Tous</option>
                            <option value="1" {{ request('is_successful') === '1' ? 'selected' : '' }}>Réussie</option>
                            <option value="0" {{ request('is_successful') === '0' ? 'selected' : '' }}>Échouée</option>
                        </select>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="om-btn om-btn-primary om-btn-sm"><i class="fas fa-filter"></i> Filtrer</button>
                    <a href="{{ route('admin.login-history.index') }}" class="om-btn om-btn-ghost om-btn-sm"><i class="fas fa-times"></i> Réinitialiser</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="om-card">
        <div class="om-card-header"><h6 class="fw-bold mb-0"><i class="fas fa-history me-2" style="color: var(--om-primary);"></i>Historique</h6></div>
        <div class="om-card-body" style="padding: 0;">
            @if($loginHistories->count() > 0)
                <div class="table-responsive">
                    <table class="om-table">
                        <thead>
                            <tr>
                                <th>Utilisateur</th>
                                <th class="d-none d-md-table-cell">Type</th>
                                <th>Date</th>
                                <th class="d-none d-lg-table-cell">IP</th>
                                <th class="d-none d-lg-table-cell">Navigateur</th>
                                <th class="d-none d-md-table-cell">Appareil</th>
                                <th class="text-center">Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($loginHistories as $history)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="om-avatar om-avatar-sm" style="background: var({{ $history->is_successful ? '--om-success' : '--om-danger' }});">
                                            {{ $history->user ? strtoupper(substr($history->user->name, 0, 1)) : '?' }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold" style="font-size: 0.85rem;">{{ $history->user ? $history->user->name : 'Supprimé' }}</div>
                                            @if($history->user)<small style="color: var(--om-gray-500);">{{ $history->user->email }}</small>@endif
                                        </div>
                                    </div>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    <span class="om-badge {{ str_contains($history->user_type, 'Admin') ? 'om-badge-primary' : (str_contains($history->user_type, 'Manager') ? 'om-badge-info' : 'om-badge-success') }}">
                                        {{ str_contains($history->user_type, 'Admin') ? 'Admin' : (str_contains($history->user_type, 'Manager') ? 'Manager' : 'Employé') }}
                                    </span>
                                </td>
                                <td>
                                    <div style="font-size: 0.85rem;">{{ $history->login_at->format('d/m/Y') }}</div>
                                    <small style="color: var(--om-gray-500);">{{ $history->login_at->format('H:i:s') }}</small>
                                </td>
                                <td class="d-none d-lg-table-cell">
                                    <code style="background: var(--om-gray-100); padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">{{ $history->ip_address }}</code>
                                </td>
                                <td class="d-none d-lg-table-cell">
                                    @if($history->browser_name == 'Chrome')<i class="fab fa-chrome text-warning me-1"></i>
                                    @elseif($history->browser_name == 'Firefox')<i class="fab fa-firefox me-1" style="color:#e66000;"></i>
                                    @elseif($history->browser_name == 'Safari')<i class="fab fa-safari text-primary me-1"></i>
                                    @elseif($history->browser_name == 'Edge')<i class="fab fa-edge text-info me-1"></i>
                                    @else<i class="fas fa-globe me-1"></i>@endif
                                    <span style="font-size: 0.85rem;">{{ $history->browser_name }}</span>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    @if($history->device_type == 'Mobile')<i class="fas fa-mobile-alt me-1"></i>
                                    @elseif($history->device_type == 'Tablette')<i class="fas fa-tablet-alt me-1"></i>
                                    @else<i class="fas fa-desktop me-1"></i>@endif
                                    <span style="font-size: 0.85rem;">{{ $history->device_type }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="om-badge {{ $history->is_successful ? 'om-badge-success' : 'om-badge-danger' }}">
                                        <i class="fas {{ $history->is_successful ? 'fa-check' : 'fa-times' }}" style="font-size: 0.6rem;"></i>
                                        {{ $history->is_successful ? 'OK' : 'Échec' }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($loginHistories->hasPages())
                    <div class="d-flex justify-content-center p-3" style="border-top: 1px solid var(--om-gray-200);">
                        {{ $loginHistories->appends(request()->query())->links() }}
                    </div>
                @endif
            @else
                <div class="om-empty">
                    <div class="om-empty-icon"><i class="fas fa-history"></i></div>
                    <h5>Aucun historique</h5>
                    <p>Aucune connexion trouvée avec les critères sélectionnés.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
