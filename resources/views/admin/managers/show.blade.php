@extends('layouts.admin')

@section('title', 'Détails du Manager')

@section('css')
@include('admin.partials._shared-styles')
<style>
    .profile-header {
        background: linear-gradient(135deg, var(--om-primary), var(--om-primary-dark));
        padding: 2rem;
        text-align: center;
        color: white;
    }
    .profile-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: rgba(255,255,255,0.2);
        border: 3px solid rgba(255,255,255,0.4);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 1rem;
    }
    .info-row {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--om-gray-100);
    }
    .info-row:last-child { border-bottom: none; }
    .info-icon {
        width: 36px;
        height: 36px;
        border-radius: var(--om-radius-sm);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 0.85rem;
    }
</style>
@endsection

@section('content')
<div class="container-fluid om-animate">
    <div class="om-page-header">
        <div>
            <h1 class="om-page-title">Détails du Manager</h1>
            <p class="om-page-subtitle">{{ $manager->name }}</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.managers.edit', $manager) }}" class="om-btn om-btn-primary om-btn-sm"><i class="fas fa-pen"></i> Modifier</a>
            <form method="POST" action="{{ route('admin.managers.toggle-active', $manager) }}" class="d-inline">
                @csrf @method('PATCH')
                <button type="submit" class="om-btn om-btn-sm {{ $manager->is_active ? 'om-btn-ghost' : 'om-btn-success' }}"
                        onclick="return confirm('Êtes-vous sûr ?')">
                    <i class="fas {{ $manager->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                    {{ $manager->is_active ? 'Désactiver' : 'Activer' }}
                </button>
            </form>
            <a href="{{ route('admin.managers.index') }}" class="om-btn om-btn-ghost om-btn-sm"><i class="fas fa-arrow-left"></i> Retour</a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left: Profile Card -->
        <div class="col-lg-4">
            <div class="om-card" style="overflow: hidden;">
                <div class="profile-header">
                    <div class="profile-avatar">{{ strtoupper(substr($manager->name, 0, 1)) }}</div>
                    <h5 class="fw-bold mb-1">{{ $manager->name }}</h5>
                    <p class="mb-2 opacity-75">Manager</p>
                    <span class="om-badge {{ $manager->is_active ? 'om-badge-success' : 'om-badge-danger' }}" style="background: rgba(255,255,255,0.2); color: white;">
                        <i class="fas {{ $manager->is_active ? 'fa-check-circle' : 'fa-times-circle' }}" style="font-size: 0.6rem;"></i>
                        {{ $manager->is_active ? 'Actif' : 'Inactif' }}
                    </span>
                </div>
                <div class="om-card-body">
                    <div class="info-row">
                        <div class="info-icon" style="background: var(--om-primary-light); color: var(--om-primary);"><i class="fas fa-envelope"></i></div>
                        <div><small style="color: var(--om-gray-500);">Email</small><br><a href="mailto:{{ $manager->email }}" class="text-decoration-none">{{ $manager->email }}</a></div>
                    </div>
                    @if($manager->phone)
                    <div class="info-row">
                        <div class="info-icon" style="background: var(--om-success-light); color: var(--om-success);"><i class="fas fa-phone"></i></div>
                        <div><small style="color: var(--om-gray-500);">Téléphone</small><br><a href="tel:{{ $manager->phone }}" class="text-decoration-none">{{ $manager->phone }}</a></div>
                    </div>
                    @endif
                    <div class="info-row">
                        <div class="info-icon" style="background: var(--om-info-light); color: var(--om-info);"><i class="fas fa-users"></i></div>
                        <div><small style="color: var(--om-gray-500);">Employés</small><br><strong>{{ $manager->employees()->count() }}</strong> supervisé(s)</div>
                    </div>
                    <div class="info-row">
                        <div class="info-icon" style="background: var(--om-warning-light); color: var(--om-warning);"><i class="fas fa-calendar"></i></div>
                        <div><small style="color: var(--om-gray-500);">Membre depuis</small><br>{{ $manager->created_at->format('d/m/Y') }} <small style="color: var(--om-gray-500);">({{ $manager->created_at->diffForHumans() }})</small></div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="om-card mt-3">
                <div class="om-card-header"><h6 class="fw-bold mb-0"><i class="fas fa-bolt me-2" style="color: var(--om-warning);"></i>Actions rapides</h6></div>
                <div class="om-card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.login-history.show', ['user_type' => 'Manager', 'user_id' => $manager->id]) }}" class="om-btn om-btn-ghost om-btn-sm" style="justify-content: flex-start;">
                            <i class="fas fa-history"></i> Historique des connexions
                        </a>
                        <a href="{{ route('admin.employees.index') }}?manager={{ $manager->id }}" class="om-btn om-btn-ghost om-btn-sm" style="justify-content: flex-start;">
                            <i class="fas fa-users"></i> Voir ses employés
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Stats + Employees -->
        <div class="col-lg-8">
            <!-- Stats -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="om-stat" style="--stat-color: var(--om-primary)">
                        <div class="text-center">
                            <div class="om-stat-value">{{ $manager->employees()->count() }}</div>
                            <div class="om-stat-label">Employés</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="om-stat" style="--stat-color: var(--om-success)">
                        <div class="text-center">
                            <div class="om-stat-value">{{ $manager->employees()->where('is_active', true)->count() }}</div>
                            <div class="om-stat-label">Actifs</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="om-stat" style="--stat-color: var(--om-info)">
                        <div class="text-center">
                            <div class="om-stat-value">{{ $manager->created_at->diffInDays() }}</div>
                            <div class="om-stat-label">Jours</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="om-stat" style="--stat-color: var(--om-warning)">
                        <div class="text-center">
                            <div class="om-stat-value">{{ $manager->loginHistory()->successful()->count() }}</div>
                            <div class="om-stat-label">Connexions</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employees Table -->
            <div class="om-card">
                <div class="om-card-header">
                    <h6 class="fw-bold mb-0"><i class="fas fa-users me-2" style="color: var(--om-primary);"></i>Employés supervisés ({{ $manager->employees()->count() }})</h6>
                    @if($manager->employees()->count() > 0)
                        <a href="{{ route('admin.employees.index') }}?manager={{ $manager->id }}" class="om-btn om-btn-ghost om-btn-sm">Voir tous</a>
                    @endif
                </div>
                <div class="om-card-body" style="padding: 0;">
                    @if($manager->employees()->count() > 0)
                        <div class="table-responsive">
                            <table class="om-table">
                                <thead>
                                    <tr>
                                        <th>Employé</th>
                                        <th>Email</th>
                                        <th class="text-center">Statut</th>
                                        <th class="d-none d-md-table-cell">Créé le</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($manager->employees()->take(10)->get() as $employee)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="om-avatar om-avatar-sm" style="background: var(--om-success);">{{ strtoupper(substr($employee->name, 0, 1)) }}</div>
                                                <span class="fw-semibold">{{ $employee->name }}</span>
                                            </div>
                                        </td>
                                        <td style="color: var(--om-gray-500);">{{ $employee->email }}</td>
                                        <td class="text-center">
                                            <span class="om-badge {{ $employee->is_active ? 'om-badge-success' : 'om-badge-danger' }}">{{ $employee->is_active ? 'Actif' : 'Inactif' }}</span>
                                        </td>
                                        <td class="d-none d-md-table-cell" style="color: var(--om-gray-500); font-size: 0.85rem;">{{ $employee->created_at->format('d/m/Y') }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.employees.show', $employee) }}" class="om-btn om-btn-ghost om-btn-icon om-btn-sm"><i class="fas fa-eye"></i></a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($manager->employees()->count() > 10)
                            <div class="text-center p-3" style="border-top: 1px solid var(--om-gray-200);">
                                <small style="color: var(--om-gray-500);">... et {{ $manager->employees()->count() - 10 }} autre(s)</small>
                            </div>
                        @endif
                    @else
                        <div class="om-empty">
                            <div class="om-empty-icon"><i class="fas fa-users"></i></div>
                            <h5>Aucun employé assigné</h5>
                            <p>Ce manager ne supervise encore aucun employé.</p>
                            <a href="{{ route('admin.employees.create') }}" class="om-btn om-btn-primary om-btn-sm"><i class="fas fa-plus"></i> Créer un employé</a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- System Info -->
            <div class="om-card mt-3">
                <div class="om-card-header"><h6 class="fw-bold mb-0"><i class="fas fa-info-circle me-2" style="color: var(--om-gray-500);"></i>Informations système</h6></div>
                <div class="om-card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <small style="color: var(--om-gray-500);">Créé le</small>
                            <div>{{ $manager->created_at->format('d/m/Y à H:i') }}</div>
                            <small style="color: var(--om-gray-500);">{{ $manager->created_at->diffForHumans() }}</small>
                        </div>
                        <div class="col-md-6">
                            <small style="color: var(--om-gray-500);">Dernière modification</small>
                            <div>{{ $manager->updated_at->format('d/m/Y à H:i') }}</div>
                            <small style="color: var(--om-gray-500);">{{ $manager->updated_at->diffForHumans() }}</small>
                        </div>
                        @if($manager->loginHistory()->latest()->first())
                        <div class="col-md-6">
                            <small style="color: var(--om-gray-500);">Dernière connexion</small>
                            <div>{{ $manager->loginHistory()->latest()->first()->login_at->format('d/m/Y à H:i') }}</div>
                        </div>
                        <div class="col-md-6">
                            <small style="color: var(--om-gray-500);">IP de connexion</small>
                            <div><code style="background: var(--om-gray-100); padding: 0.2rem 0.5rem; border-radius: 4px;">{{ $manager->loginHistory()->latest()->first()->ip_address }}</code></div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
