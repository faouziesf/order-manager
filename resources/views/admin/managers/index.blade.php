@extends('layouts.admin')

@section('title', 'Gestion des Managers')

@section('css')
@include('admin.partials._shared-styles')
@endsection

@section('content')
@php
    $managerCount = \App\Models\Admin::where('role', \App\Models\Admin::ROLE_MANAGER)->where('created_by', $admin->id)->count();
    $activeCount = \App\Models\Admin::where('role', \App\Models\Admin::ROLE_MANAGER)->where('created_by', $admin->id)->where('is_active', true)->count();
@endphp

<div class="container-fluid om-animate">
    <!-- Page Header -->
    <div class="om-page-header">
        <div>
            <h1 class="om-page-title">Gestion des Managers</h1>
            <p class="om-page-subtitle">Gérez vos managers et leurs accès</p>
        </div>
        <div>
            @if($managerCount < $admin->max_managers)
                <a href="{{ route('admin.managers.create') }}" class="om-btn om-btn-primary">
                    <i class="fas fa-plus"></i> Nouveau Manager
                </a>
            @else
                <button class="om-btn om-btn-ghost" disabled title="Limite maximale atteinte">
                    <i class="fas fa-lock"></i> Limite atteinte ({{ $admin->max_managers }})
                </button>
            @endif
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="om-stat" style="--stat-color: var(--om-primary)">
                <div class="d-flex align-items-center gap-3">
                    <div class="om-stat-icon"><i class="fas fa-user-tie"></i></div>
                    <div>
                        <div class="om-stat-value">{{ $managers->total() }}</div>
                        <div class="om-stat-label">Total</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="om-stat" style="--stat-color: var(--om-success)">
                <div class="d-flex align-items-center gap-3">
                    <div class="om-stat-icon"><i class="fas fa-check-circle"></i></div>
                    <div>
                        <div class="om-stat-value">{{ $activeCount }}</div>
                        <div class="om-stat-label">Actifs</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="om-stat" style="--stat-color: var(--om-warning)">
                <div class="d-flex align-items-center gap-3">
                    <div class="om-stat-icon"><i class="fas fa-crown"></i></div>
                    <div>
                        <div class="om-stat-value">{{ $admin->max_managers }}</div>
                        <div class="om-stat-label">Limite</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="om-stat" style="--stat-color: var(--om-info)">
                <div class="d-flex align-items-center gap-3">
                    <div class="om-stat-icon"><i class="fas fa-users"></i></div>
                    <div>
                        <div class="om-stat-value">{{ \App\Models\Admin::where('role', \App\Models\Admin::ROLE_EMPLOYEE)->where('created_by', $admin->id)->count() }}</div>
                        <div class="om-stat-label">Employés</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Managers Table Card -->
    <div class="om-card">
        <div class="om-card-header">
            <h6 class="fw-bold mb-0"><i class="fas fa-list me-2 text-primary"></i>Liste des Managers</h6>
            <div class="d-flex align-items-center gap-2">
                <div class="position-relative">
                    <i class="fas fa-search position-absolute top-50 translate-middle-y" style="left: 12px; color: var(--om-gray-500); font-size: 0.8rem;"></i>
                    <input type="text" class="om-form-input" id="searchInput" placeholder="Rechercher..."
                           style="padding-left: 2.25rem; width: 220px; font-size: 0.85rem; height: 38px;">
                </div>
            </div>
        </div>

        <div class="om-card-body" style="padding: 0;">
            @if($managers->count() > 0)
                <div class="table-responsive">
                    <table class="om-table" id="managersTable">
                        <thead>
                            <tr>
                                <th>Manager</th>
                                <th>Contact</th>
                                <th class="text-center">Statut</th>
                                <th class="d-none d-md-table-cell">Employés</th>
                                <th class="d-none d-md-table-cell">Créé le</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($managers as $manager)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="om-avatar" style="background: var(--om-primary);">
                                                {{ strtoupper(substr($manager->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold" style="color: var(--om-gray-900);">{{ $manager->name }}</div>
                                                <small style="color: var(--om-gray-500);">Manager</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="color: var(--om-gray-900);">{{ $manager->email }}</div>
                                        @if($manager->phone)
                                            <small style="color: var(--om-gray-500);">{{ $manager->phone }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="om-badge {{ $manager->is_active ? 'om-badge-success' : 'om-badge-danger' }}">
                                            <i class="fas {{ $manager->is_active ? 'fa-check-circle' : 'fa-times-circle' }}" style="font-size: 0.6rem;"></i>
                                            {{ $manager->is_active ? 'Actif' : 'Inactif' }}
                                        </span>
                                    </td>
                                    <td class="d-none d-md-table-cell">
                                        <span class="om-badge om-badge-info">
                                            <i class="fas fa-users" style="font-size: 0.6rem;"></i>
                                            {{ $manager->employees()->count() }}
                                        </span>
                                    </td>
                                    <td class="d-none d-md-table-cell">
                                        <span style="color: var(--om-gray-500); font-size: 0.85rem;">{{ $manager->created_at->format('d/m/Y') }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center justify-content-end gap-1">
                                            <a href="{{ route('admin.managers.show', $manager) }}" class="om-btn om-btn-ghost om-btn-icon om-btn-sm" title="Voir">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.managers.edit', $manager) }}" class="om-btn om-btn-ghost om-btn-icon om-btn-sm" title="Modifier">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                            <form method="POST" action="{{ route('admin.managers.toggle-active', $manager) }}" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="om-btn om-btn-icon om-btn-sm {{ $manager->is_active ? 'om-btn-ghost' : 'om-btn-success' }}"
                                                        title="{{ $manager->is_active ? 'Désactiver' : 'Activer' }}"
                                                        onclick="return confirm('Êtes-vous sûr ?')">
                                                    <i class="fas {{ $manager->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.managers.destroy', $manager) }}" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="om-btn om-btn-danger om-btn-icon om-btn-sm"
                                                        title="Supprimer"
                                                        onclick="return confirm('Supprimer ce manager ? Cette action est irréversible.')">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($managers->hasPages())
                    <div class="d-flex justify-content-center p-3 border-top">
                        {{ $managers->links() }}
                    </div>
                @endif
            @else
                <div class="om-empty">
                    <div class="om-empty-icon"><i class="fas fa-user-tie"></i></div>
                    <h5>Aucun manager</h5>
                    <p>Commencez par créer votre premier manager pour déléguer la gestion.</p>
                    @if($managerCount < $admin->max_managers)
                        <a href="{{ route('admin.managers.create') }}" class="om-btn om-btn-primary">
                            <i class="fas fa-plus"></i> Créer un Manager
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('searchInput')?.addEventListener('input', function() {
    const val = this.value.toLowerCase();
    document.querySelectorAll('#managersTable tbody tr').forEach(function(row) {
        row.style.display = row.textContent.toLowerCase().includes(val) ? '' : 'none';
    });
});
</script>
@endsection
