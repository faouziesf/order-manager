@extends('layouts.super-admin')

@section('title', 'Gestion des Administrateurs')

@section('breadcrumb')
    <ol class="breadcrumb breadcrumb-custom">
        <li class="breadcrumb-item"><a href="{{ route('super-admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Administrateurs</li>
    </ol>
@endsection

@section('page-header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">Gestion des Administrateurs</h1>
            <p class="page-subtitle">Gérez tous les administrateurs de votre plateforme</p>
        </div>
        <div class="d-flex gap-2">
            <div class="dropdown">
                <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-download me-2"></i>Exporter
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('super-admin.admins.export.csv') }}">
                        <i class="fas fa-file-csv me-2"></i>CSV
                    </a></li>
                    <li><a class="dropdown-item" href="{{ route('super-admin.admins.export.excel') }}">
                        <i class="fas fa-file-excel me-2"></i>Excel
                    </a></li>
                    <li><a class="dropdown-item" href="{{ route('super-admin.admins.export.pdf') }}">
                        <i class="fas fa-file-pdf me-2"></i>PDF
                    </a></li>
                </ul>
            </div>
            <a href="{{ route('super-admin.admins.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Nouvel Administrateur
            </a>
        </div>
    </div>
@endsection

@section('css')
<style>
    .admin-card {
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    }
    
    .admin-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }
    
    .admin-avatar {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 1.1rem;
    }
    
    .status-indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 8px;
    }
    
    .status-active { background: var(--success-color); }
    .status-inactive { background: var(--danger-color); }
    .status-expired { background: var(--warning-color); }
    
    .filter-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 25px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
    }
    
    .stats-overview {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        border-radius: 15px;
        color: white;
        padding: 25px;
        margin-bottom: 30px;
    }
    
    .search-box {
        position: relative;
    }
    
    .search-box .form-control {
        padding-left: 45px;
        border-radius: 25px;
        border: 2px solid #e2e8f0;
    }
    
    .search-box .search-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--secondary-color);
    }
    
    .batch-actions {
        background: var(--light-color);
        border-radius: 10px;
        padding: 15px 20px;
        margin-bottom: 20px;
        display: none;
        border-left: 4px solid var(--primary-color);
    }
    
    .batch-actions.show {
        display: flex;
        justify-content: space-between;
        align-items: center;
        animation: slideIn 0.3s ease-out;
    }
    
    @keyframes slideIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .admin-meta {
        font-size: 0.875rem;
        color: var(--secondary-color);
    }
    
    .admin-meta .meta-item {
        display: inline-flex;
        align-items: center;
        margin-right: 15px;
        margin-bottom: 5px;
    }
    
    .admin-meta .meta-item i {
        margin-right: 5px;
        width: 14px;
    }
    
    .progress-ring {
        width: 60px;
        height: 60px;
    }
    
    .actions-dropdown .dropdown-menu {
        border: none;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        border-radius: 8px;
    }
    
    .table-responsive {
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    }
    
    .view-toggle {
        background: var(--light-color);
        border-radius: 8px;
        padding: 4px;
    }
    
    .view-toggle .btn {
        border: none;
        padding: 8px 12px;
        border-radius: 6px;
    }
    
    .view-toggle .btn.active {
        background: var(--primary-color);
        color: white;
    }
    
    .card-view .admin-card {
        height: 100%;
    }
    
    .subscription-badge {
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    .subscription-trial { background: #fef3c7; color: #92400e; }
    .subscription-basic { background: #dbeafe; color: #1d4ed8; }
    .subscription-premium { background: #f3e8ff; color: #7c3aed; }
    .subscription-enterprise { background: #ecfdf5; color: #059669; }
</style>
@endsection

@section('content')
    <!-- Stats Overview -->
    <div class="stats-overview">
        <div class="row">
            <div class="col-md-3 text-center">
                <h3 class="mb-1">{{ $stats['total'] }}</h3>
                <small class="opacity-75">Total</small>
            </div>
            <div class="col-md-3 text-center">
                <h3 class="mb-1">{{ $stats['active'] }}</h3>
                <small class="opacity-75">Actifs</small>
            </div>
            <div class="col-md-3 text-center">
                <h3 class="mb-1">{{ $stats['new_this_month'] }}</h3>
                <small class="opacity-75">Nouveaux ce mois</small>
            </div>
            <div class="col-md-3 text-center">
                <h3 class="mb-1">{{ $stats['expiring_soon'] }}</h3>
                <small class="opacity-75">Expirent bientôt</small>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-card">
        <form method="GET" action="{{ route('super-admin.admins.index') }}" id="filterForm">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Rechercher</label>
                    <div class="search-box">
                        <input type="text" 
                               class="form-control" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Nom, email, boutique..."
                               id="searchInput">
                        <i class="fas fa-search search-icon"></i>
                    </div>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Statut</label>
                    <select class="form-select" name="status" id="statusFilter">
                        <option value="">Tous</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actifs</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactifs</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Abonnement</label>
                    <select class="form-select" name="subscription" id="subscriptionFilter">
                        <option value="">Tous</option>
                        <option value="trial" {{ request('subscription') === 'trial' ? 'selected' : '' }}>Essai</option>
                        <option value="basic" {{ request('subscription') === 'basic' ? 'selected' : '' }}>Basic</option>
                        <option value="premium" {{ request('subscription') === 'premium' ? 'selected' : '' }}>Premium</option>
                        <option value="enterprise" {{ request('subscription') === 'enterprise' ? 'selected' : '' }}>Enterprise</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Expiration</label>
                    <select class="form-select" name="expiry_filter" id="expiryFilter">
                        <option value="">Tous</option>
                        <option value="expired" {{ request('expiry_filter') === 'expired' ? 'selected' : '' }}>Expirés</option>
                        <option value="expiring" {{ request('expiry_filter') === 'expiring' ? 'selected' : '' }}>Expirent bientôt</option>
                        <option value="valid" {{ request('expiry_filter') === 'valid' ? 'selected' : '' }}>Valides</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i>
                        </button>
                        <a href="{{ route('super-admin.admins.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- View Toggle & Batch Actions -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="view-toggle">
            <button class="btn active" data-view="table" id="tableView">
                <i class="fas fa-list me-1"></i>Tableau
            </button>
            <button class="btn" data-view="cards" id="cardView">
                <i class="fas fa-th-large me-1"></i>Cartes
            </button>
        </div>
        
        <div class="d-flex align-items-center gap-2">
            <small class="text-muted">{{ $admins->total() }} résultat(s)</small>
            <select class="form-select form-select-sm" style="width: auto;" onchange="changePerPage(this.value)">
                <option value="15" {{ request('per_page') == 15 ? 'selected' : '' }}>15 par page</option>
                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 par page</option>
                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 par page</option>
                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 par page</option>
            </select>
        </div>
    </div>

    <!-- Batch Actions Bar -->
    <div class="batch-actions" id="batchActions">
        <div>
            <span class="fw-medium">
                <span id="selectedCount">0</span> élément(s) sélectionné(s)
            </span>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-success" onclick="batchAction('activate')">
                <i class="fas fa-check me-1"></i>Activer
            </button>
            <button class="btn btn-sm btn-outline-warning" onclick="batchAction('deactivate')">
                <i class="fas fa-times me-1"></i>Désactiver
            </button>
            <button class="btn btn-sm btn-outline-info" onclick="showExtendModal()">
                <i class="fas fa-clock me-1"></i>Prolonger
            </button>
            <button class="btn btn-sm btn-outline-danger" onclick="batchAction('delete')">
                <i class="fas fa-trash me-1"></i>Supprimer
            </button>
            <button class="btn btn-sm btn-outline-secondary" onclick="clearSelection()">
                <i class="fas fa-times me-1"></i>Annuler
            </button>
        </div>
    </div>

    <!-- Table View -->
    <div id="tableViewContainer">
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="50">
                                <input type="checkbox" class="form-check-input" id="selectAll">
                            </th>
                            <th>
                                <a href="#" class="text-decoration-none sort-link" data-sort="name">
                                    Administrateur <i class="fas fa-sort ms-1"></i>
                                </a>
                            </th>
                            <th>Boutique</th>
                            <th>Contact</th>
                            <th>
                                <a href="#" class="text-decoration-none sort-link" data-sort="subscription_type">
                                    Abonnement <i class="fas fa-sort ms-1"></i>
                                </a>
                            </th>
                            <th>
                                <a href="#" class="text-decoration-none sort-link" data-sort="expiry_date">
                                    Expiration <i class="fas fa-sort ms-1"></i>
                                </a>
                            </th>
                            <th>Statistiques</th>
                            <th>
                                <a href="#" class="text-decoration-none sort-link" data-sort="is_active">
                                    Statut <i class="fas fa-sort ms-1"></i>
                                </a>
                            </th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($admins as $admin)
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input admin-checkbox" value="{{ $admin->id }}">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="admin-avatar me-3">
                                            {{ substr($admin->name, 0, 2) }}
                                        </div>
                                        <div>
                                            <div class="fw-medium">{{ $admin->name }}</div>
                                            <small class="text-muted">{{ $admin->identifier }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <div class="fw-medium">{{ $admin->shop_name }}</div>
                                        <small class="text-muted">{{ $admin->email }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="admin-meta">
                                        @if($admin->phone)
                                            <div class="meta-item">
                                                <i class="fas fa-phone"></i>
                                                {{ $admin->phone }}
                                            </div>
                                        @endif
                                        <div class="meta-item">
                                            <i class="fas fa-calendar"></i>
                                            {{ $admin->created_at->format('d/m/Y') }}
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="subscription-badge subscription-{{ $admin->subscription_type ?? 'trial' }}">
                                        {{ ucfirst($admin->subscription_type ?? 'Trial') }}
                                    </span>
                                </td>
                                <td>
                                    @if($admin->expiry_date)
                                        <div class="text-{{ $admin->expiry_date->isPast() ? 'danger' : ($admin->expiry_date->diffInDays() <= 7 ? 'warning' : 'success') }}">
                                            {{ $admin->expiry_date->format('d/m/Y') }}
                                        </div>
                                        <small class="text-muted">
                                            {{ $admin->expiry_date->isPast() ? 'Expiré' : $admin->expiry_date->diffForHumans() }}
                                        </small>
                                    @else
                                        <span class="text-muted">Illimité</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="admin-meta">
                                        <div class="meta-item">
                                            <i class="fas fa-shopping-cart"></i>
                                            {{ $admin->total_orders }} commandes
                                        </div>
                                        <div class="meta-item">
                                            <i class="fas fa-users"></i>
                                            {{ $admin->managers_count ?? 0 }}/{{ $admin->max_managers }} managers
                                        </div>
                                        <div class="meta-item">
                                            <i class="fas fa-user-friends"></i>
                                            {{ $admin->employees_count ?? 0 }}/{{ $admin->max_employees }} employés
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="status-indicator status-{{ $admin->is_active ? 'active' : 'inactive' }}"></span>
                                        <span class="text-{{ $admin->is_active ? 'success' : 'danger' }}">
                                            {{ $admin->is_active ? 'Actif' : 'Inactif' }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="dropdown actions-dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('super-admin.admins.show', $admin) }}">
                                                    <i class="fas fa-eye me-2"></i>Voir détails
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('super-admin.admins.edit', $admin) }}">
                                                    <i class="fas fa-edit me-2"></i>Modifier
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <button class="dropdown-item" onclick="toggleStatus({{ $admin->id }}, {{ $admin->is_active ? 'false' : 'true' }})">
                                                    <i class="fas fa-{{ $admin->is_active ? 'times' : 'check' }} me-2"></i>
                                                    {{ $admin->is_active ? 'Désactiver' : 'Activer' }}
                                                </button>
                                            </li>
                                            <li>
                                                <button class="dropdown-item" onclick="extendSubscription({{ $admin->id }})">
                                                    <i class="fas fa-clock me-2"></i>Prolonger
                                                </button>
                                            </li>
                                            <li>
                                                <button class="dropdown-item" onclick="resetPassword({{ $admin->id }})">
                                                    <i class="fas fa-key me-2"></i>Reset mot de passe
                                                </button>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <button class="dropdown-item text-danger" onclick="deleteAdmin({{ $admin->id }})">
                                                    <i class="fas fa-trash me-2"></i>Supprimer
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Aucun administrateur trouvé</h5>
                                    <p class="text-muted">Essayez de modifier vos filtres ou créez un nouvel administrateur.</p>
                                    <a href="{{ route('super-admin.admins.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Créer un administrateur
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Card View -->
    <div id="cardViewContainer" style="display: none;">
        <div class="row g-4">
            @forelse($admins as $admin)
                <div class="col-xl-4 col-lg-6">
                    <div class="card admin-card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between mb-3">
                                <div class="d-flex align-items-center">
                                    <input type="checkbox" class="form-check-input me-3 admin-checkbox" value="{{ $admin->id }}">
                                    <div class="admin-avatar me-3">
                                        {{ substr($admin->name, 0, 2) }}
                                    </div>
                                    <div>
                                        <h6 class="mb-1">{{ $admin->name }}</h6>
                                        <small class="text-muted">{{ $admin->identifier }}</small>
                                    </div>
                                </div>
                                <div class="dropdown actions-dropdown">
                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="{{ route('super-admin.admins.show', $admin) }}">Voir détails</a></li>
                                        <li><a class="dropdown-item" href="{{ route('super-admin.admins.edit', $admin) }}">Modifier</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><button class="dropdown-item text-danger" onclick="deleteAdmin({{ $admin->id }})">Supprimer</button></li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="fw-medium text-primary">{{ $admin->shop_name }}</div>
                                <small class="text-muted">{{ $admin->email }}</small>
                            </div>
                            
                            <div class="mb-3">
                                <span class="subscription-badge subscription-{{ $admin->subscription_type ?? 'trial' }}">
                                    {{ ucfirst($admin->subscription_type ?? 'Trial') }}
                                </span>
                                <span class="ms-2 d-inline-flex align-items-center">
                                    <span class="status-indicator status-{{ $admin->is_active ? 'active' : 'inactive' }}"></span>
                                    {{ $admin->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </div>
                            
                            <div class="row g-2 mb-3">
                                <div class="col-4 text-center">
                                    <div class="fw-bold text-primary">{{ $admin->total_orders }}</div>
                                    <small class="text-muted">Commandes</small>
                                </div>
                                <div class="col-4 text-center">
                                    <div class="fw-bold text-success">{{ $admin->managers_count ?? 0 }}/{{ $admin->max_managers }}</div>
                                    <small class="text-muted">Managers</small>
                                </div>
                                <div class="col-4 text-center">
                                    <div class="fw-bold text-info">{{ $admin->employees_count ?? 0 }}/{{ $admin->max_employees }}</div>
                                    <small class="text-muted">Employés</small>
                                </div>
                            </div>
                            
                            @if($admin->expiry_date)
                                <div class="text-center">
                                    <small class="text-{{ $admin->expiry_date->isPast() ? 'danger' : ($admin->expiry_date->diffInDays() <= 7 ? 'warning' : 'muted') }}">
                                        <i class="fas fa-calendar me-1"></i>
                                        Expire {{ $admin->expiry_date->diffForHumans() }}
                                    </small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Aucun administrateur trouvé</h5>
                        <p class="text-muted">Essayez de modifier vos filtres ou créez un nouvel administrateur.</p>
                        <a href="{{ route('super-admin.admins.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Créer un administrateur
                        </a>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Pagination -->
    @if($admins->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $admins->appends(request()->query())->links() }}
        </div>
    @endif

    <!-- Modal pour prolonger l'abonnement -->
    <div class="modal fade" id="extendModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Prolonger l'abonnement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="extendForm">
                        <div class="mb-3">
                            <label class="form-label">Nombre de mois</label>
                            <select class="form-select" name="months" required>
                                <option value="1">1 mois</option>
                                <option value="3">3 mois</option>
                                <option value="6">6 mois</option>
                                <option value="12">12 mois</option>
                            </select>
                        </div>
                        <input type="hidden" name="admin_ids" id="extendAdminIds">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" onclick="submitExtend()">Prolonger</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let selectedAdmins = [];
    
    // Initialisation
    setupEventListeners();
    
    function setupEventListeners() {
        // Select all checkbox
        document.getElementById('selectAll')?.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.admin-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = this.checked;
                updateSelection(cb);
            });
        });
        
        // Individual checkboxes
        document.querySelectorAll('.admin-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateSelection(this);
            });
        });
        
        // View toggle
        document.getElementById('tableView')?.addEventListener('click', function() {
            switchView('table');
        });
        
        document.getElementById('cardView')?.addEventListener('click', function() {
            switchView('cards');
        });
        
        // Auto-submit filters on change
        document.querySelectorAll('#statusFilter, #subscriptionFilter, #expiryFilter').forEach(select => {
            select.addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });
        });
        
        // Search with debounce
        let searchTimeout;
        document.getElementById('searchInput')?.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                document.getElementById('filterForm').submit();
            }, 500);
        });
    }
    
    function updateSelection(checkbox) {
        const adminId = parseInt(checkbox.value);
        
        if (checkbox.checked) {
            if (!selectedAdmins.includes(adminId)) {
                selectedAdmins.push(adminId);
            }
        } else {
            selectedAdmins = selectedAdmins.filter(id => id !== adminId);
        }
        
        updateBatchActions();
        updateSelectAllState();
    }
    
    function updateBatchActions() {
        const batchActions = document.getElementById('batchActions');
        const selectedCount = document.getElementById('selectedCount');
        
        selectedCount.textContent = selectedAdmins.length;
        
        if (selectedAdmins.length > 0) {
            batchActions.classList.add('show');
        } else {
            batchActions.classList.remove('show');
        }
    }
    
    function updateSelectAllState() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.admin-checkbox');
        const checkedBoxes = document.querySelectorAll('.admin-checkbox:checked');
        
        if (checkedBoxes.length === 0) {
            selectAll.indeterminate = false;
            selectAll.checked = false;
        } else if (checkedBoxes.length === checkboxes.length) {
            selectAll.indeterminate = false;
            selectAll.checked = true;
        } else {
            selectAll.indeterminate = true;
        }
    }
    
    function switchView(view) {
        const tableView = document.getElementById('tableViewContainer');
        const cardView = document.getElementById('cardViewContainer');
        const tableBtn = document.getElementById('tableView');
        const cardBtn = document.getElementById('cardView');
        
        if (view === 'table') {
            tableView.style.display = 'block';
            cardView.style.display = 'none';
            tableBtn.classList.add('active');
            cardBtn.classList.remove('active');
        } else {
            tableView.style.display = 'none';
            cardView.style.display = 'block';
            tableBtn.classList.remove('active');
            cardBtn.classList.add('active');
        }
        
        // Save preference
        localStorage.setItem('adminsViewMode', view);
    }
    
    // Restore view preference
    const savedView = localStorage.getItem('adminsViewMode');
    if (savedView) {
        switchView(savedView);
    }
    
    // Global functions
    window.clearSelection = function() {
        selectedAdmins = [];
        document.querySelectorAll('.admin-checkbox').forEach(cb => cb.checked = false);
        document.getElementById('selectAll').checked = false;
        updateBatchActions();
    };
    
    window.batchAction = function(action) {
        if (selectedAdmins.length === 0) return;
        
        let confirmMessage = '';
        switch (action) {
            case 'activate':
                confirmMessage = `Activer ${selectedAdmins.length} administrateur(s) ?`;
                break;
            case 'deactivate':
                confirmMessage = `Désactiver ${selectedAdmins.length} administrateur(s) ?`;
                break;
            case 'delete':
                confirmMessage = `Supprimer ${selectedAdmins.length} administrateur(s) ? Cette action est irréversible.`;
                break;
        }
        
        if (confirm(confirmMessage)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('super-admin.admins.bulk-actions') }}';
            
            // CSRF token
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            
            // Action
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = action;
            form.appendChild(actionInput);
            
            // Admin IDs
            const idsInput = document.createElement('input');
            idsInput.type = 'hidden';
            idsInput.name = 'admin_ids';
            idsInput.value = JSON.stringify(selectedAdmins);
            form.appendChild(idsInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    };
    
    window.showExtendModal = function() {
        if (selectedAdmins.length === 0) return;
        
        document.getElementById('extendAdminIds').value = JSON.stringify(selectedAdmins);
        new bootstrap.Modal(document.getElementById('extendModal')).show();
    };
    
    window.submitExtend = function() {
        const form = document.getElementById('extendForm');
        const formData = new FormData(form);
        
        // Create and submit form
        const submitForm = document.createElement('form');
        submitForm.method = 'POST';
        submitForm.action = '{{ route('super-admin.admins.bulk-actions') }}';
        
        // CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        submitForm.appendChild(csrfToken);
        
        // Action
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'extend';
        submitForm.appendChild(actionInput);
        
        // Data
        for (let [key, value] of formData.entries()) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value;
            submitForm.appendChild(input);
        }
        
        document.body.appendChild(submitForm);
        submitForm.submit();
    };
    
    window.toggleStatus = function(adminId, newStatus) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/super-admin/admins/${adminId}/toggle-active`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'PATCH';
        form.appendChild(methodInput);
        
        document.body.appendChild(form);
        form.submit();
    };
    
    window.deleteAdmin = function(adminId) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cet administrateur ? Cette action est irréversible.')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/super-admin/admins/${adminId}`;
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    };
    
    window.changePerPage = function(perPage) {
        const url = new URL(window.location);
        url.searchParams.set('per_page', perPage);
        url.searchParams.delete('page'); // Reset to first page
        window.location.href = url.toString();
    };
});
</script>
@endsection