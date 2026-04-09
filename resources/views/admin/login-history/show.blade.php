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
            @if($user)
                <p class="om-page-subtitle">
                    Connexions de <strong>{{ $user->name }}</strong>
                    <span class="om-badge {{ str_contains($userType, 'Manager') ? 'om-badge-primary' : 'om-badge-success' }}">
                        {{ str_contains($userType, 'Manager') ? 'Manager' : 'Employé' }}
                    </span>
                </p>
            @else
                <p class="om-page-subtitle">Utilisateur non trouvé ou supprimé</p>
            @endif
        </div>
        <div class="d-flex gap-2 flex-wrap">
            @if($user)
                @if(str_contains($userType, 'Manager'))
                    <a href="{{ route('admin.managers.show', $user->id) }}" class="om-btn om-btn-primary om-btn-sm"><i class="fas fa-user-tie"></i> Profil</a>
                @else
                    <a href="{{ route('admin.employees.show', $user->id) }}" class="om-btn om-btn-success om-btn-sm"><i class="fas fa-user"></i> Profil</a>
                @endif
            @endif
            <a href="{{ route('admin.login-history.index') }}" class="om-btn om-btn-ghost om-btn-sm"><i class="fas fa-arrow-left"></i> Retour</a>
        </div>
    </div>

    @if($user)
    <!-- Profile Card -->
    <div class="om-card mb-4" style="overflow: hidden;">
        <div style="background: linear-gradient(135deg, {{ str_contains($userType, 'Manager') ? 'var(--om-primary), var(--om-primary-dark)' : 'var(--om-success), #059669' }}); padding: 1.5rem;">
            <div class="d-flex align-items-center gap-3">
                <div class="om-avatar om-avatar-lg" style="background: rgba(255,255,255,0.2); border: 2px solid rgba(255,255,255,0.3);">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div class="flex-grow-1">
                    <h5 class="text-white fw-bold mb-1">{{ $user->name }}</h5>
                    <div class="text-white-50">{{ $user->email }}</div>
                    @if($user->phone)<small class="text-white-50">{{ $user->phone }}</small>@endif
                </div>
                <span class="om-badge {{ $user->is_active ? 'om-badge-success' : 'om-badge-danger' }}" style="background: rgba(255,255,255,0.2); color: white;">
                    {{ $user->is_active ? 'Actif' : 'Inactif' }}
                </span>
            </div>
        </div>
    </div>
    @endif

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

    <!-- Table -->
    <div class="om-card">
        <div class="om-card-header"><h6 class="fw-bold mb-0"><i class="fas fa-history me-2" style="color: var(--om-primary);"></i>Détails des connexions</h6></div>
        <div class="om-card-body" style="padding: 0;">
            @if($loginHistories->count() > 0)
                <div class="table-responsive">
                    <table class="om-table">
                        <thead>
                            <tr>
                                <th>Date/Heure</th>
                                <th class="d-none d-md-table-cell">IP</th>
                                <th class="d-none d-lg-table-cell">Navigateur</th>
                                <th class="d-none d-md-table-cell">Appareil</th>
                                <th class="d-none d-lg-table-cell">Localisation</th>
                                <th class="text-center">Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($loginHistories as $history)
                            <tr>
                                <td>
                                    <div style="font-size: 0.85rem;">{{ $history->login_at->format('d/m/Y') }}</div>
                                    <small style="color: var(--om-gray-500);">{{ $history->login_at->format('H:i:s') }} - {{ $history->login_at->diffForHumans() }}</small>
                                </td>
                                <td class="d-none d-md-table-cell">
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
                                <td class="d-none d-lg-table-cell">
                                    @if($history->country || $history->city)
                                        <div style="font-size: 0.85rem;">{{ $history->city }}</div>
                                        <small style="color: var(--om-gray-500);">{{ $history->country }}</small>
                                    @else
                                        <span style="color: var(--om-gray-500); font-size: 0.85rem;">Non déterminé</span>
                                    @endif
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
                    <p>Aucune connexion trouvée pour cet utilisateur.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
