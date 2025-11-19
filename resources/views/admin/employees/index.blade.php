@extends('layouts.admin')

@section('title', 'Gestion des Employés')

@section('content')
<div class="container-fluid animate-fade-in" x-data="employeesIndex()">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h2 fw-bold text-dark mb-2">Gestion des Employés</h1>
            <p class="text-muted">Gérez vos employés et leurs affectations</p>
        </div>
        <div class="col-md-4 text-end">
            @if(\App\Models\Admin::where('role', \App\Models\Admin::ROLE_EMPLOYEE)->where('admin_id', $admin->id)->count() < $admin->max_employees)
                <a href="{{ route('admin.employees.create') }}"
                   class="btn btn-success btn-lg shadow">
                    <i class="fas fa-plus me-2"></i>
                    Nouvel Employé
                </a>
            @else
                <div class="position-relative d-inline-block">
                    <button disabled 
                            class="btn btn-secondary disabled"
                            data-bs-toggle="tooltip" 
                            title="Vous avez atteint la limite maximale d'employés">
                        <i class="fas fa-lock me-2"></i>
                        Limite atteinte ({{ $admin->max_employees }})
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Alertes de limite -->
    @php
        $employeeCount = \App\Models\Admin::where('role', \App\Models\Admin::ROLE_EMPLOYEE)->where('admin_id', $admin->id)->count();
    @endphp
    @if($employeeCount >= $admin->max_employees * 0.8)
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-warning alert-dismissible fade show" x-data="{ show: true }" x-show="show">
                    <div class="d-flex align-items-start">
                        <div class="me-3">
                            <div class="bg-warning bg-opacity-25 rounded-3 p-2">
                                <i class="fas fa-exclamation-triangle text-warning"></i>
                            </div>
                        </div>
                        <div class="flex-fill">
                            <h6 class="alert-heading">
                                @if($employeeCount >= $admin->max_employees)
                                    Limite d'employés atteinte
                                @else
                                    Approche de la limite d'employés
                                @endif
                            </h6>
                            <p class="mb-0">
                                Vous avez <strong>{{ $employeeCount }}</strong> employé(s) sur un maximum de <strong>{{ $admin->max_employees }}</strong>.
                                @if($employeeCount >= $admin->max_employees)
                                    Contactez le support pour augmenter votre limite.
                                @else
                                    Il vous reste {{ $admin->max_employees - $employeeCount }} place(s).
                                @endif
                            </p>
                        </div>
                        <button type="button" @click="show = false" class="btn-close"></button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <!-- Total Employés -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 card-hover">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted small fw-medium mb-1">Total Employés</p>
                            <h3 class="fw-bold mb-0" x-data="{ count: 0 }" x-init="setTimeout(() => { count = {{ $employees->total() }} }, 300)">
                                <span x-text="count"></span>
                            </h3>
                            <div class="d-flex align-items-center mt-2">
                                <div class="progress flex-fill me-2" style="height: 8px;">
                                    <div class="progress-bar bg-success progress-bar-animated" 
                                         style="width: {{ $admin->max_employees > 0 ? ($employees->total() / $admin->max_employees * 100) : 0 }}%"></div>
                                </div>
                                <small class="text-muted">{{ $admin->max_employees > 0 ? round($employees->total() / $admin->max_employees * 100) : 0 }}%</small>
                            </div>
                        </div>
                        <div class="bg-success bg-opacity-10 rounded-3 p-3">
                            <i class="fas fa-users text-success fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Employés Actifs -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 card-hover">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted small fw-medium mb-1">Employés Actifs</p>
                            @php
                                $activeEmployeeCount = \App\Models\Admin::where('role', \App\Models\Admin::ROLE_EMPLOYEE)->where('admin_id', $admin->id)->where('is_active', true)->count();
                            @endphp
                            <h3 class="fw-bold mb-0">{{ $activeEmployeeCount }}</h3>
                            <small class="text-success">
                                {{ $employees->total() > 0 ? round($activeEmployeeCount / $employees->total() * 100) : 0 }}% du total
                            </small>
                        </div>
                        <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                            <i class="fas fa-check-circle text-primary fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Limite Employés -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 card-hover">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted small fw-medium mb-1">Limite Employés</p>
                            <h3 class="fw-bold mb-0">{{ $admin->max_employees }}</h3>
                            <small class="text-muted">
                                {{ $admin->max_employees - $employees->total() }} place(s) restante(s)
                            </small>
                        </div>
                        <div class="bg-warning bg-opacity-10 rounded-3 p-3">
                            <i class="fas fa-chart-bar text-warning fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sans Manager -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 card-hover">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted small fw-medium mb-1">Sans Manager</p>
                            @php
                                $noManagerCount = \App\Models\Admin::where('role', \App\Models\Admin::ROLE_EMPLOYEE)->where('admin_id', $admin->id)->whereNull('manager_id')->count();
                            @endphp
                            <h3 class="fw-bold mb-0">{{ $noManagerCount }}</h3>
                            @if($noManagerCount > 0)
                                <small class="text-warning">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    À assigner
                                </small>
                            @else
                                <small class="text-success">
                                    <i class="fas fa-check me-1"></i>
                                    Tous assignés
                                </small>
                            @endif
                        </div>
                        <div class="bg-info bg-opacity-10 rounded-3 p-3">
                            <i class="fas fa-user-slash text-info fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card shadow-lg border-0">
        <!-- Card Header -->
        <div class="card-header bg-light border-bottom">
            <div class="row align-items-center g-3">
                <div class="col-md-6">
                    <h5 class="card-title mb-0">Liste des Employés</h5>
                    <small class="text-muted">{{ $employees->total() }} employé(s) au total</small>
                </div>
                
                <div class="col-md-6">
                    <div class="row g-2">
                        <!-- Filter by Manager -->
                        <div class="col-md-6">
                            <select class="form-select form-select-sm"
                                    onchange="filterByManager(this.value)"
                                    x-model="selectedManager">
                                <option value="">Tous les managers</option>
                                <option value="no-manager">Sans manager</option>
                                @foreach(\App\Models\Admin::where('role', \App\Models\Admin::ROLE_MANAGER)->where('admin_id', $admin->id)->get() as $manager)
                                    <option value="{{ $manager->id }}">{{ $manager->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Search Input -->
                        <div class="col-md-6">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" 
                                       class="form-control"
                                       placeholder="Rechercher un employé..." 
                                       id="searchInput"
                                       x-model="searchTerm"
                                       @input="filterEmployees">
                            </div>
                        </div>
                        
                        <!-- Actions groupées -->
                        <div class="col-12" x-show="selectedEmployees.length > 0" x-transition>
                            <div class="dropdown position-relative">
                                <button class="btn btn-primary btn-sm dropdown-toggle" 
                                        type="button"
                                        id="bulkActionsDropdown"
                                        data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                    <i class="fas fa-cog me-2"></i>
                                    Actions (<span x-text="selectedEmployees.length"></span>)
                                </button>
                                
                                <ul class="dropdown-menu" aria-labelledby="bulkActionsDropdown">
                                    <li>
                                        <button @click="bulkActivate(); $refs.bulkDropdown.blur()" 
                                                class="dropdown-item" type="button">
                                            <i class="fas fa-check me-2"></i>
                                            Activer sélectionnés
                                        </button>
                                    </li>
                                    <li>
                                        <button @click="bulkDeactivate(); $refs.bulkDropdown.blur()" 
                                                class="dropdown-item" type="button">
                                            <i class="fas fa-ban me-2"></i>
                                            Désactiver sélectionnés
                                        </button>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <button @click="bulkDelete(); $refs.bulkDropdown.blur()" 
                                                class="dropdown-item text-danger" type="button">
                                            <i class="fas fa-trash me-2"></i>
                                            Supprimer sélectionnés
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Table Content -->
        <div class="card-body p-0">
            @if($employees->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="employeesTable">
                        <thead class="table-light">
                            <tr>
                                <!-- Checkbox pour sélection groupée -->
                                <th style="width: 40px;">
                                    <input type="checkbox" 
                                           @change="toggleAllEmployees($event.target.checked)"
                                           class="form-check-input">
                                </th>
                                <th class="sortable-header cursor-pointer" @click="sortBy('name')">
                                    Employé 
                                    <i class="fas fa-sort ms-1" :class="getSortIcon('name')"></i>
                                </th>
                                <th>Contact</th>
                                <th class="sortable-header cursor-pointer" @click="sortBy('manager')">
                                    Manager
                                    <i class="fas fa-sort ms-1" :class="getSortIcon('manager')"></i>
                                </th>
                                <th class="sortable-header cursor-pointer" @click="sortBy('status')">
                                    Statut
                                    <i class="fas fa-sort ms-1" :class="getSortIcon('status')"></i>
                                </th>
                                <th class="sortable-header cursor-pointer" @click="sortBy('created_at')">
                                    Créé le
                                    <i class="fas fa-sort ms-1" :class="getSortIcon('created_at')"></i>
                                </th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($employees as $employee)
                                <tr class="table-row-hover" 
                                    data-manager-id="{{ $employee->manager_id }}"
                                    data-employee-id="{{ $employee->id }}">
                                    <!-- Checkbox -->
                                    <td>
                                        <input type="checkbox" 
                                               value="{{ $employee->id }}"
                                               @change="toggleEmployee({{ $employee->id }}, $event.target.checked)"
                                               class="form-check-input">
                                    </td>
                                    
                                    <!-- Employé -->
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-success rounded-3 p-2 me-3 text-white fw-bold text-center" style="width: 40px; height: 40px; line-height: 24px;">
                                                {{ substr($employee->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="fw-semibold">{{ $employee->name }}</div>
                                                <small class="text-muted">Employé</small>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <!-- Contact -->
                                    <td>
                                        <div>
                                            <div class="small d-flex align-items-center mb-1">
                                                <i class="fas fa-envelope text-muted me-2"></i>
                                                <a href="mailto:{{ $employee->email }}" class="text-decoration-none">
                                                    {{ $employee->email }}
                                                </a>
                                            </div>
                                            @if($employee->phone)
                                                <div class="small d-flex align-items-center text-muted">
                                                    <i class="fas fa-phone text-muted me-2"></i>
                                                    <a href="tel:{{ $employee->phone }}" class="text-decoration-none">
                                                        {{ $employee->phone }}
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    
                                    <!-- Manager -->
                                    <td>
                                        @if($employee->manager)
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary rounded-circle p-2 me-2 text-white fw-bold text-center" style="width: 32px; height: 32px; line-height: 16px; font-size: 12px;">
                                                    {{ substr($employee->manager->name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <div class="fw-medium small">{{ $employee->manager->name }}</div>
                                                    <small class="text-muted">Manager</small>
                                                </div>
                                            </div>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-user-slash me-1"></i>
                                                Sans manager
                                            </span>
                                        @endif
                                    </td>
                                    
                                    <!-- Statut -->
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="badge {{ $employee->is_active ? 'bg-success' : 'bg-danger' }}">
                                                <i class="fas fa-circle me-1" style="font-size: 8px;"></i>
                                                {{ $employee->is_active ? 'Actif' : 'Inactif' }}
                                            </span>
                                            @if($employee->loginHistory()->latest()->first())
                                                @php $lastLogin = $employee->loginHistory()->latest()->first(); @endphp
                                                <button type="button" 
                                                        class="btn btn-link p-0 border-0 bg-transparent text-muted"
                                                        data-bs-toggle="tooltip" 
                                                        data-bs-placement="top"
                                                        title="Dernière connexion: {{ $lastLogin->login_at->diffForHumans() }}">
                                                    <i class="fas fa-clock small"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                    
                                    <!-- Date -->
                                    <td>
                                        <div class="small">{{ $employee->created_at->format('d/m/Y') }}</div>
                                        <small class="text-muted">{{ $employee->created_at->diffForHumans() }}</small>
                                    </td>
                                    
                                    <!-- Actions -->
                                    <td>
                                        <div class="btn-group btn-group-sm action-buttons">
                                            <!-- Voir -->
                                            <a href="{{ route('admin.employees.show', $employee) }}" 
                                               class="btn btn-outline-primary btn-sm"
                                               data-bs-toggle="tooltip" title="Voir les détails">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            <!-- Modifier -->
                                            <a href="{{ route('admin.employees.edit', $employee) }}" 
                                               class="btn btn-outline-warning btn-sm"
                                               data-bs-toggle="tooltip" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <!-- Toggle Active -->
                                            <button type="button"
                                                    @click="toggleEmployeeStatus({{ $employee->id }}, {{ $employee->is_active ? 'false' : 'true' }})"
                                                    class="btn btn-outline-{{ $employee->is_active ? 'secondary' : 'success' }} btn-sm"
                                                    data-bs-toggle="tooltip" title="{{ $employee->is_active ? 'Désactiver' : 'Activer' }}">
                                                <i class="fas {{ $employee->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                            </button>
                                            
                                            <!-- Supprimer -->
                                            <button type="button"
                                                    @click="deleteEmployee({{ $employee->id }}, '{{ $employee->name }}')"
                                                    class="btn btn-outline-danger btn-sm"
                                                    data-bs-toggle="tooltip" title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                @if($employees->hasPages())
                    <div class="card-footer bg-light">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <small class="text-muted">
                                    Affichage de {{ $employees->firstItem() }} à {{ $employees->lastItem() }} sur {{ $employees->total() }} résultats
                                </small>
                            </div>
                            
                            <div class="col-md-6">
                                <nav class="d-flex justify-content-end">
                                    <ul class="pagination pagination-sm mb-0">
                                        {{-- Previous Page Link --}}
                                        @if ($employees->onFirstPage())
                                            <li class="page-item disabled">
                                                <span class="page-link"><i class="fas fa-chevron-left"></i></span>
                                            </li>
                                        @else
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $employees->previousPageUrl() }}">
                                                    <i class="fas fa-chevron-left"></i>
                                                </a>
                                            </li>
                                        @endif

                                        {{-- Pagination Elements --}}
                                        @foreach ($employees->getUrlRange(max(1, $employees->currentPage() - 2), min($employees->lastPage(), $employees->currentPage() + 2)) as $page => $url)
                                            @if ($page == $employees->currentPage())
                                                <li class="page-item active">
                                                    <span class="page-link">{{ $page }}</span>
                                                </li>
                                            @else
                                                <li class="page-item">
                                                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                                </li>
                                            @endif
                                        @endforeach

                                        {{-- Next Page Link --}}
                                        @if ($employees->hasMorePages())
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $employees->nextPageUrl() }}">
                                                    <i class="fas fa-chevron-right"></i>
                                                </a>
                                            </li>
                                        @else
                                            <li class="page-item disabled">
                                                <span class="page-link"><i class="fas fa-chevron-right"></i></span>
                                            </li>
                                        @endif
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                @endif
            @else
                <!-- Empty State -->
                <div class="text-center py-5">
                    <div class="bg-light rounded-4 p-4 d-inline-block mb-4">
                        <i class="fas fa-users text-muted" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="fw-semibold">Aucun employé trouvé</h5>
                    <p class="text-muted mb-4">
                        @if(request()->has('search') || request()->has('manager'))
                            Aucun employé ne correspond à vos critères de recherche. Essayez de modifier vos filtres.
                        @else
                            Commencez par créer votre premier employé pour développer votre équipe.
                        @endif
                    </p>
                    @if(\App\Models\Admin::where('role', \App\Models\Admin::ROLE_EMPLOYEE)->where('admin_id', $admin->id)->count() < $admin->max_employees)
                        <div class="d-flex flex-column align-items-center gap-3">
                            <a href="{{ route('admin.employees.create') }}"
                               class="btn btn-success btn-lg">
                                <i class="fas fa-plus me-2"></i>
                                Créer un Employé
                            </a>
                            @if(request()->has('search') || request()->has('manager'))
                                <a href="{{ route('admin.employees.index') }}" 
                                   class="btn btn-link">
                                    <i class="fas fa-times me-2"></i>
                                    Effacer les filtres
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Modal de confirmation -->
    <div class="modal fade" :class="{ 'show d-block': showModal }" 
         x-show="showModal" 
         x-transition
         tabindex="-1" 
         style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <!-- Header dynamique -->
                <div class="modal-header"
                     :class="{
                         'bg-danger text-white': modalType === 'delete',
                         'bg-warning text-dark': modalType === 'toggle',
                         'bg-primary text-white': modalType === 'bulk'
                     }">
                    <h5 class="modal-title">
                        <i :class="{
                            'fas fa-exclamation-triangle': modalType === 'delete',
                            'fas fa-question-circle': modalType === 'toggle',
                            'fas fa-cog': modalType === 'bulk'
                        }" class="me-2"></i>
                        <span x-text="modalTitle"></span>
                    </h5>
                    <button type="button" @click="closeModal()" class="btn-close" :class="{ 'btn-close-white': modalType !== 'toggle' }"></button>
                </div>
                
                <!-- Body -->
                <div class="modal-body">
                    <div x-show="modalType === 'delete'">
                        <p x-html="modalMessage"></p>
                        <div class="alert alert-danger">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-exclamation-triangle me-2 mt-1"></i>
                                <div>
                                    <p class="fw-semibold mb-1 small">Attention !</p>
                                    <p class="mb-0 small">Cette action est irréversible et supprimera définitivement l'employé.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div x-show="modalType === 'toggle'">
                        <p x-html="modalMessage"></p>
                    </div>
                    
                    <div x-show="modalType === 'bulk'">
                        <p x-html="modalMessage"></p>
                        <div class="alert alert-info">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-info-circle me-2 mt-1"></i>
                                <div>
                                    <p class="fw-semibold mb-1 small">Information</p>
                                    <p class="mb-0 small">Cette action s'appliquera à tous les employés sélectionnés.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="modal-footer">
                    <button type="button" @click="closeModal()" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>
                        Annuler
                    </button>
                    <button type="button" @click="confirmAction()" 
                            class="btn"
                            :class="{
                                'btn-danger': modalType === 'delete',
                                'btn-warning': modalType === 'toggle',
                                'btn-primary': modalType === 'bulk'
                            }">
                        <i class="me-2" :class="{
                            'fas fa-trash': modalType === 'delete',
                            'fas fa-check': modalType === 'toggle',
                            'fas fa-cog': modalType === 'bulk'
                        }"></i>
                        <span x-text="modalConfirmText"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function employeesIndex() {
    return {
        searchTerm: '',
        selectedManager: '',
        selectedEmployees: [],
        showModal: false,
        modalType: '',
        modalTitle: '',
        modalMessage: '',
        modalConfirmText: '',
        pendingAction: null,
        sortField: '',
        sortDirection: 'asc',
        
        init() {
            this.filterEmployees();
            // Initialiser les tooltips Bootstrap
            this.$nextTick(() => {
                this.initTooltips();
            });
        },
        
        initTooltips() {
            // Attendre que Bootstrap soit chargé
            if (typeof bootstrap === 'undefined') {
                setTimeout(() => this.initTooltips(), 100);
                return;
            }
            
            try {
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    // Éviter de créer des tooltips multiples sur le même élément
                    if (!tooltipTriggerEl._tooltip) {
                        tooltipTriggerEl._tooltip = new bootstrap.Tooltip(tooltipTriggerEl);
                    }
                    return tooltipTriggerEl._tooltip;
                });
            } catch (error) {
                console.warn('Erreur lors de l\'initialisation des tooltips:', error);
            }
        },
        
        // Filtrage des employés
        filterEmployees() {
            const searchTerm = this.searchTerm.toLowerCase();
            const rows = document.querySelectorAll('#employeesTable tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                const managerId = row.dataset.managerId;
                
                let showRow = true;
                
                // Filtrage par recherche
                if (searchTerm.length > 0) {
                    showRow = text.includes(searchTerm);
                }
                
                // Filtrage par manager
                if (this.selectedManager && showRow) {
                    if (this.selectedManager === 'no-manager') {
                        showRow = managerId === '' || managerId === 'null' || !managerId;
                    } else {
                        showRow = managerId === this.selectedManager;
                    }
                }
                
                row.style.display = showRow ? '' : 'none';
            });
        },
        
        // Sélection des employés
        toggleEmployee(employeeId, checked) {
            if (checked) {
                if (!this.selectedEmployees.includes(employeeId)) {
                    this.selectedEmployees.push(employeeId);
                }
            } else {
                this.selectedEmployees = this.selectedEmployees.filter(id => id !== employeeId);
            }
        },
        
        toggleAllEmployees(checked) {
            const visibleRows = document.querySelectorAll('#employeesTable tbody tr:not([style*="display: none"])');
            
            if (checked) {
                this.selectedEmployees = [];
                visibleRows.forEach(row => {
                    const checkbox = row.querySelector('input[type="checkbox"]');
                    if (checkbox) {
                        checkbox.checked = true;
                        const employeeId = parseInt(checkbox.value);
                        if (!this.selectedEmployees.includes(employeeId)) {
                            this.selectedEmployees.push(employeeId);
                        }
                    }
                });
            } else {
                visibleRows.forEach(row => {
                    const checkbox = row.querySelector('input[type="checkbox"]');
                    if (checkbox) {
                        checkbox.checked = false;
                    }
                });
                this.selectedEmployees = [];
            }
        },
        
        // Actions individuelles
        toggleEmployeeStatus(employeeId, newStatus) {
            this.modalType = 'toggle';
            this.modalTitle = newStatus ? 'Activer l\'employé' : 'Désactiver l\'employé';
            this.modalMessage = `Êtes-vous sûr de vouloir ${newStatus ? 'activer' : 'désactiver'} cet employé ?`;
            this.modalConfirmText = newStatus ? 'Activer' : 'Désactiver';
            this.pendingAction = () => this.executeToggleStatus(employeeId);
            this.showModal = true;
        },
        
        deleteEmployee(employeeId, employeeName) {
            this.modalType = 'delete';
            this.modalTitle = 'Supprimer l\'employé';
            this.modalMessage = `Êtes-vous sûr de vouloir supprimer l'employé <strong>${employeeName}</strong> ?`;
            this.modalConfirmText = 'Supprimer';
            this.pendingAction = () => this.executeDelete(employeeId);
            this.showModal = true;
        },
        
        // Actions groupées
        bulkActivate() {
            this.modalType = 'bulk';
            this.modalTitle = 'Activer les employés sélectionnés';
            this.modalMessage = `Activer ${this.selectedEmployees.length} employé(s) sélectionné(s) ?`;
            this.modalConfirmText = 'Activer';
            this.pendingAction = () => this.executeBulkAction('activate');
            this.showModal = true;
        },
        
        bulkDeactivate() {
            this.modalType = 'bulk';
            this.modalTitle = 'Désactiver les employés sélectionnés';
            this.modalMessage = `Désactiver ${this.selectedEmployees.length} employé(s) sélectionné(s) ?`;
            this.modalConfirmText = 'Désactiver';
            this.pendingAction = () => this.executeBulkAction('deactivate');
            this.showModal = true;
        },
        
        bulkDelete() {
            this.modalType = 'bulk';
            this.modalTitle = 'Supprimer les employés sélectionnés';
            this.modalMessage = `Supprimer définitivement ${this.selectedEmployees.length} employé(s) sélectionné(s) ?`;
            this.modalConfirmText = 'Supprimer';
            this.pendingAction = () => this.executeBulkAction('delete');
            this.showModal = true;
        },
        
        // Exécution des actions
        executeToggleStatus(employeeId) {
            this.showToast('Modification du statut en cours...', 'info');
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/employees/${employeeId}/toggle-active`;
            form.style.display = 'none';
            
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            
            const method = document.createElement('input');
            method.type = 'hidden';
            method.name = '_method';
            method.value = 'PATCH';
            
            form.appendChild(csrf);
            form.appendChild(method);
            document.body.appendChild(form);
            
            setTimeout(() => {
                this.showToast('Statut modifié avec succès !', 'success');
                form.submit();
            }, 800);
        },
        
        executeDelete(employeeId) {
            this.showToast('Suppression en cours...', 'warning');
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/employees/${employeeId}`;
            form.style.display = 'none';
            
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            
            const method = document.createElement('input');
            method.type = 'hidden';
            method.name = '_method';
            method.value = 'DELETE';
            
            form.appendChild(csrf);
            form.appendChild(method);
            document.body.appendChild(form);
            
            setTimeout(() => {
                this.showToast('Employé supprimé avec succès !', 'success');
                form.submit();
            }, 800);
        },
        
        executeBulkAction(action) {
            this.showToast(`Traitement de ${this.selectedEmployees.length} employé(s)...`, 'info');
            
            setTimeout(() => {
                const messages = {
                    activate: `${this.selectedEmployees.length} employé(s) activé(s) avec succès !`,
                    deactivate: `${this.selectedEmployees.length} employé(s) désactivé(s) avec succès !`,
                    delete: `${this.selectedEmployees.length} employé(s) supprimé(s) avec succès !`
                };
                
                this.showToast(messages[action] || 'Action terminée avec succès !', 'success');
                
                this.selectedEmployees = [];
                this.toggleAllEmployees(false);
            }, 1500);
        },
        
        // Tri
        sortBy(field) {
            if (this.sortField === field) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortField = field;
                this.sortDirection = 'asc';
            }
            
            const fieldNames = {
                name: 'nom',
                manager: 'manager',
                status: 'statut',
                created_at: 'date de création'
            };
            
            const directionText = this.sortDirection === 'asc' ? 'croissant' : 'décroissant';
            this.showToast(`Tri par ${fieldNames[field] || field} (${directionText})`, 'info');
        },
        
        getSortIcon(field) {
            if (this.sortField !== field) return 'fa-sort';
            return this.sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down';
        },
        
        // Modal
        confirmAction() {
            if (this.pendingAction) {
                this.pendingAction();
                this.closeModal();
            }
        },
        
        closeModal() {
            this.showModal = false;
            this.pendingAction = null;
        },
        
        // Toast notifications
        showToast(message, type = 'info') {
            // Vérifier si window.toast existe, sinon utiliser une méthode de fallback
            if (typeof window.toast !== 'undefined') {
                return window.toast.show(message, type, { duration: 4000 });
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
                const toast = new bootstrap.Toast(toastElement, { delay: 4000 });
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

// Fonction de filtrage par manager (conservée pour compatibilité)
function filterByManager(managerId) {
    Alpine.$data(document.querySelector('[x-data*="employeesIndex"]')).selectedManager = managerId;
    Alpine.$data(document.querySelector('[x-data*="employeesIndex"]')).filterEmployees();
}

// Animation d'apparition progressive
document.addEventListener('DOMContentLoaded', function() {
    // Animer les cartes de stats
    const statCards = document.querySelectorAll('.card-hover');
    statCards.forEach((card, index) => {
        setTimeout(() => {
            card.classList.add('animate-slide-up');
        }, index * 100);
    });
    
    // Animer les lignes du tableau
    const tableRows = document.querySelectorAll('#employeesTable tbody tr');
    tableRows.forEach((row, index) => {
        setTimeout(() => {
            row.classList.add('animate-slide-up');
        }, (index * 50) + 300);
    });
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

/* Amélioration des cartes */
.card-hover {
    transition: all 0.3s ease;
}

.card-hover:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

/* Amélioration des boutons d'action */
.action-buttons {
    opacity: 0.7;
    transition: opacity 0.2s ease;
}

.table-row-hover:hover .action-buttons {
    opacity: 1;
}

/* Style pour les lignes du tableau */
.table-row-hover:hover {
    background-color: rgba(13, 110, 253, 0.05);
}

/* Curseur pointeur pour les en-têtes triables */
.sortable-header {
    cursor: pointer;
    user-select: none;
    transition: background-color 0.2s ease;
}

.sortable-header:hover {
    background-color: rgba(0, 0, 0, 0.05);
}

.cursor-pointer {
    cursor: pointer;
}

/* Amélioration du dropdown */
.dropdown-menu.show {
    display: block;
}

/* Animation pour les barres de progression */
.progress-bar {
    transition: width 1.5s ease-out;
}

/* Style pour les badges */
.badge {
    font-size: 0.75em;
}

/* Amélioration des boutons */
.btn {
    transition: all 0.2s ease-in-out;
}

.btn:hover {
    transform: translateY(-1px);
}

/* Toast container */
.toast-container {
    z-index: 9999;
}

/* Responsive amélioré */
@media (max-width: 768px) {
    .btn-group-sm .btn {
        padding: 0.25rem 0.4rem;
        font-size: 0.7rem;
    }
    
    .action-buttons {
        opacity: 1;
    }
}
</style>
@endsection