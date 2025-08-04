@extends('layouts.admin')

@section('title', 'Configuration des Transporteurs')

@section('css')
<style>
    :root {
        --primary: #1d4ed8;
        --primary-dark: #1e3a8a;
        --primary-light: #3b82f6;
        --success: #059669;
        --warning: #d97706;
        --danger: #dc2626;
        --info: #0891b2;
        --light: #f8fafc;
        --dark: #1f2937;
        --border: #e5e7eb;
        --shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        --shadow-lg: 0 4px 6px rgba(0, 0, 0, 0.1);
        --radius: 6px;
        --transition: all 0.15s ease;
    }

    body {
        background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
        font-size: 14px;
    }

    /* ===== CONTAINER PRINCIPAL ===== */
    .config-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 1rem;
    }

    /* ===== HEADER MODERNE ===== */
    .page-header {
        background: white;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .header-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .header-info h1 {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--dark);
        margin: 0 0 0.3rem 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .header-info .subtitle {
        color: #6b7280;
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
    }

    .breadcrumb {
        background: none;
        padding: 0;
        margin: 0;
        font-size: 0.8rem;
    }

    .breadcrumb-item a {
        color: #6b7280;
        text-decoration: none;
    }

    .breadcrumb-item a:hover {
        color: var(--primary);
    }

    .header-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .btn {
        padding: 0.5rem 1rem;
        border-radius: 4px;
        font-weight: 600;
        font-size: 0.8rem;
        text-decoration: none;
        text-align: center;
        transition: var(--transition);
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }

    .btn:hover {
        transform: translateY(-1px);
        text-decoration: none;
    }

    .btn-primary {
        background: var(--primary);
        color: white;
    }

    .btn-primary:hover {
        background: var(--primary-dark);
        color: white;
    }

    .btn-outline {
        background: transparent;
        color: var(--dark);
        border: 1px solid var(--border);
    }

    .btn-outline:hover {
        background: var(--light);
        color: var(--dark);
    }

    .btn-success {
        background: var(--success);
        color: white;
    }

    .btn-success:hover {
        background: #047857;
    }

    .dropdown-menu {
        border: 1px solid var(--border);
        box-shadow: var(--shadow-lg);
        border-radius: var(--radius);
    }

    .dropdown-item {
        padding: 0.5rem 1rem;
        font-size: 0.8rem;
    }

    .dropdown-item:hover {
        background: var(--light);
    }

    /* ===== FILTRES ===== */
    .filters-card {
        background: white;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        padding: 1rem;
        margin-bottom: 1.5rem;
    }

    .filters-grid {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1fr;
        gap: 1rem;
        align-items: center;
    }

    .form-control, .form-select {
        padding: 0.5rem 0.75rem;
        border: 1px solid var(--border);
        border-radius: 4px;
        font-size: 0.8rem;
        transition: var(--transition);
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(29, 78, 216, 0.25);
        outline: none;
    }

    .input-group {
        position: relative;
    }

    .input-group .input-icon {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: #6b7280;
        z-index: 10;
    }

    .input-group .form-control {
        padding-left: 2.5rem;
    }

    /* ===== CARTES TRANSPORTEURS ===== */
    .carrier-section {
        background: white;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        margin-bottom: 1.5rem;
        overflow: hidden;
    }

    .carrier-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        padding: 1rem;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .carrier-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .carrier-icon {
        width: 40px;
        height: 40px;
        background: rgba(255, 255, 255, 0.15);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }

    .carrier-details h3 {
        font-size: 1.1rem;
        font-weight: 700;
        margin: 0 0 0.2rem 0;
    }

    .carrier-details .subtitle {
        font-size: 0.8rem;
        opacity: 0.9;
        margin: 0;
    }

    .btn-add {
        background: rgba(255, 255, 255, 0.15);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.25);
        padding: 0.4rem 0.8rem;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .btn-add:hover {
        background: rgba(255, 255, 255, 0.25);
        color: white;
    }

    /* ===== TABLEAU CONFIGURATIONS ===== */
    .configs-table {
        width: 100%;
        border-collapse: collapse;
    }

    .configs-table th {
        background: #f8fafc;
        padding: 0.75rem;
        font-weight: 600;
        font-size: 0.8rem;
        color: var(--dark);
        text-align: left;
        border-bottom: 1px solid var(--border);
    }

    .configs-table td {
        padding: 1rem 0.75rem;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .configs-table tbody tr {
        transition: var(--transition);
    }

    .configs-table tbody tr:hover {
        background: #f8fafc;
    }

    .config-name {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .status-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .status-indicator.active {
        background: var(--success);
        box-shadow: 0 0 0 2px rgba(5, 150, 105, 0.2);
    }

    .status-indicator.inactive {
        background: #9ca3af;
    }

    .config-details h4 {
        font-size: 0.9rem;
        font-weight: 600;
        margin: 0 0 0.2rem 0;
        color: var(--dark);
    }

    .config-details .meta {
        font-size: 0.75rem;
        color: #6b7280;
        margin: 0;
    }

    .credentials {
        display: flex;
        flex-direction: column;
        gap: 0.3rem;
    }

    .credential-item {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.75rem;
    }

    .credential-item i {
        color: #6b7280;
        width: 12px;
    }

    .credential-value {
        background: rgba(29, 78, 216, 0.1);
        color: var(--primary);
        padding: 0.2rem 0.4rem;
        border-radius: 3px;
        font-family: 'Monaco', 'Consolas', monospace;
        font-size: 0.7rem;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.3rem 0.6rem;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: 600;
    }

    .status-badge.active {
        background: #dcfce7;
        color: #166534;
    }

    .status-badge.inactive {
        background: #f3f4f6;
        color: #6b7280;
    }

    .actions {
        display: flex;
        gap: 0.3rem;
    }

    .btn-action {
        width: 32px;
        height: 32px;
        padding: 0;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        border: 1px solid transparent;
    }

    .btn-toggle.active {
        background: #fef3c7;
        color: #92400e;
        border-color: #fed7aa;
    }

    .btn-toggle.inactive {
        background: #dcfce7;
        color: #166534;
        border-color: #bbf7d0;
    }

    .btn-edit {
        background: #f3f4f6;
        color: #6b7280;
        border-color: #e5e7eb;
    }

    .btn-edit:hover {
        background: #e5e7eb;
        color: #374151;
    }

    .btn-delete {
        background: #fee2e2;
        color: #991b1b;
        border-color: #fecaca;
    }

    .btn-delete:hover {
        background: #fecaca;
        color: #7f1d1d;
    }

    /* ===== ÉTAT VIDE ===== */
    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
    }

    .empty-icon {
        width: 80px;
        height: 80px;
        background: rgba(29, 78, 216, 0.1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        font-size: 2rem;
        color: var(--primary);
    }

    .empty-state h3 {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 0.5rem;
    }

    .empty-state p {
        color: #6b7280;
        margin-bottom: 1.5rem;
        font-size: 0.9rem;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .config-container {
            padding: 0.5rem;
        }

        .header-top {
            flex-direction: column;
            align-items: stretch;
        }

        .header-actions {
            justify-content: stretch;
        }

        .btn {
            flex: 1;
            justify-content: center;
        }

        .filters-grid {
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }

        .carrier-header {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }

        .configs-table {
            font-size: 0.75rem;
        }

        .configs-table th,
        .configs-table td {
            padding: 0.5rem;
        }

        .actions {
            flex-direction: column;
        }
    }

    @media (max-width: 480px) {
        .page-header {
            padding: 1rem;
        }

        .filters-card {
            padding: 0.75rem;
        }

        .carrier-header {
            padding: 0.75rem;
        }

        .configs-table th,
        .configs-table td {
            padding: 0.4rem;
        }

        .btn-action {
            width: 28px;
            height: 28px;
            font-size: 0.7rem;
        }
    }

    /* ===== ANIMATIONS ===== */
    .fade-in {
        animation: fadeIn 0.3s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .loading {
        opacity: 0.6;
        pointer-events: none;
    }
</style>
@endsection

@section('content')
<div class="config-container fade-in" x-data="configurationManager">
    <!-- Header -->
    <div class="page-header">
        <div class="header-top">
            <div class="header-info">
                <h1>
                    <i class="fas fa-cog text-primary"></i>
                    Configuration Transporteurs
                </h1>
                <p class="subtitle">Gérez vos liaisons JAX Delivery et Mes Colis Express</p>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.delivery.index') }}">Livraisons</a></li>
                        <li class="breadcrumb-item active">Configuration</li>
                    </ol>
                </nav>
            </div>
            <div class="header-actions">
                <a href="{{ route('admin.delivery.index') }}" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i>
                    Retour
                </a>
                <div class="dropdown">
                    <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-plus"></i>
                        Nouvelle Config
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.delivery.configuration.create') }}?carrier=jax_delivery">
                                <i class="fas fa-truck me-2 text-primary"></i>
                                JAX Delivery
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.delivery.configuration.create') }}?carrier=mes_colis">
                                <i class="fas fa-shipping-fast me-2 text-success"></i>
                                Mes Colis Express
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="filters-card">
        <div class="filters-grid">
            <div class="input-group">
                <i class="fas fa-search input-icon"></i>
                <input type="text" 
                       class="form-control" 
                       placeholder="Rechercher une configuration..."
                       x-model="search"
                       @input.debounce.300ms="filterConfigs()">
            </div>
            <select class="form-select" x-model="carrierFilter" @change="filterConfigs()">
                <option value="">Tous transporteurs</option>
                <option value="jax_delivery">JAX Delivery</option>
                <option value="mes_colis">Mes Colis Express</option>
            </select>
            <select class="form-select" x-model="statusFilter" @change="filterConfigs()">
                <option value="">Tous statuts</option>
                <option value="active">Actives</option>
                <option value="inactive">Inactives</option>
            </select>
            <div class="text-center">
                <span class="text-muted" style="font-size: 0.8rem;">
                    <span x-text="filteredConfigs.length"></span> configuration(s)
                </span>
            </div>
        </div>
    </div>

    <!-- Configurations par transporteur -->
    @if($configurations->isEmpty())
        <div class="carrier-section">
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-cog"></i>
                </div>
                <h3>Aucune configuration</h3>
                <p>Créez votre première configuration de transporteur pour commencer à expédier vos commandes</p>
                <a href="{{ route('admin.delivery.configuration.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Première configuration
                </a>
            </div>
        </div>
    @else
        @foreach($configsByCarrier as $carrierSlug => $carrierConfigs)
            <div class="carrier-section" 
                 x-show="isCarrierVisible('{{ $carrierSlug }}')"
                 style="display: none;">
                <div class="carrier-header">
                    <div class="carrier-info">
                        <div class="carrier-icon">
                            <i class="fas fa-{{ $carrierSlug === 'jax_delivery' ? 'truck' : 'shipping-fast' }}"></i>
                        </div>
                        <div class="carrier-details">
                            <h3>{{ $carrierSlug === 'jax_delivery' ? 'JAX Delivery' : 'Mes Colis Express' }}</h3>
                            <p class="subtitle">{{ $carrierConfigs->count() }} configuration(s)</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.delivery.configuration.create') }}?carrier={{ $carrierSlug }}" 
                       class="btn-add">
                        <i class="fas fa-plus"></i>
                        Ajouter
                    </a>
                </div>
                
                <table class="configs-table">
                    <thead>
                        <tr>
                            <th>Configuration</th>
                            <th>Identifiants</th>
                            <th>Statut</th>
                            <th>Dernière activité</th>
                            <th style="text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($carrierConfigs as $config)
                            <tr x-show="isConfigVisible({{ $config->id }})"
                                style="display: none;">
                                <td>
                                    <div class="config-name">
                                        <div class="status-indicator {{ $config->is_active ? 'active' : 'inactive' }}"></div>
                                        <div class="config-details">
                                            <h4>{{ $config->integration_name }}</h4>
                                            <p class="meta">Créé le {{ $config->created_at->format('d/m/Y') }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="credentials">
                                        <div class="credential-item">
                                            <i class="fas fa-user"></i>
                                            <span class="credential-value">{{ Str::limit($config->username, 15) }}</span>
                                        </div>
                                        <div class="credential-item">
                                            <i class="fas fa-server"></i>
                                            <span style="font-size: 0.7rem; color: #6b7280;">{{ ucfirst($config->environment) }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="status-badge {{ $config->is_active ? 'active' : 'inactive' }}">
                                        <i class="fas fa-{{ $config->is_active ? 'check' : 'pause' }}"></i>
                                        {{ $config->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <span style="font-size: 0.75rem; color: #6b7280;">
                                        <i class="fas fa-clock"></i>
                                        {{ $config->updated_at->diffForHumans() }}
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <div class="actions">
                                        <button class="btn-action btn-toggle {{ $config->is_active ? 'active' : 'inactive' }}" 
                                                @click="toggleConfig({{ $config->id }})"
                                                :disabled="loading"
                                                title="{{ $config->is_active ? 'Désactiver' : 'Activer' }}">
                                            <i class="fas fa-{{ $config->is_active ? 'pause' : 'play' }}"></i>
                                        </button>
                                        
                                        <a href="{{ route('admin.delivery.configuration.edit', $config) }}" 
                                           class="btn-action btn-edit"
                                           title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <button class="btn-action btn-delete" 
                                                @click="deleteConfig({{ $config->id }})"
                                                :disabled="loading"
                                                title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    @endif
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('configurationManager', () => ({
        search: '',
        carrierFilter: '',
        statusFilter: '',
        loading: false,
        configurations: @json($configurations),
        filteredConfigs: @json($configurations),

        init() {
            const urlParams = new URLSearchParams(window.location.search);
            this.carrierFilter = urlParams.get('filter') || '';
            this.filterConfigs();
        },

        filterConfigs() {
            this.filteredConfigs = this.configurations.filter(config => {
                // Filtre par recherche
                if (this.search) {
                    const searchLower = this.search.toLowerCase();
                    const matchName = config.integration_name.toLowerCase().includes(searchLower);
                    const matchUsername = config.username.toLowerCase().includes(searchLower);
                    if (!matchName && !matchUsername) return false;
                }

                // Filtre par transporteur
                if (this.carrierFilter && config.carrier_slug !== this.carrierFilter) {
                    return false;
                }

                // Filtre par statut
                if (this.statusFilter) {
                    if (this.statusFilter === 'active' && !config.is_active) return false;
                    if (this.statusFilter === 'inactive' && config.is_active) return false;
                }

                return true;
            });
        },

        isCarrierVisible(carrierSlug) {
            return this.filteredConfigs.some(config => config.carrier_slug === carrierSlug);
        },

        isConfigVisible(configId) {
            return this.filteredConfigs.some(config => config.id === configId);
        },

        async toggleConfig(configId) {
            if (this.loading) return;
            
            this.loading = true;
            
            try {
                const response = await fetch(`/admin/delivery/configuration/${configId}/toggle`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (data.success) {
                    this.showToast('success', data.message);
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    this.showToast('error', 'Erreur lors du changement de statut');
                }
            } catch (error) {
                this.showToast('error', 'Erreur de communication');
            } finally {
                this.loading = false;
            }
        },

        async deleteConfig(configId) {
            if (this.loading) return;
            
            if (!confirm('Êtes-vous sûr de vouloir supprimer cette configuration ?')) {
                return;
            }

            this.loading = true;

            try {
                const response = await fetch(`/admin/delivery/configuration/${configId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (data.success) {
                    this.showToast('success', data.message);
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    this.showToast('error', data.error || 'Erreur lors de la suppression');
                }
            } catch (error) {
                this.showToast('error', 'Erreur de communication');
            } finally {
                this.loading = false;
            }
        },

        showToast(type, message) {
            const toast = document.createElement('div');
            toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'}`;
            toast.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 10000;
                min-width: 300px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                animation: slideInRight 0.3s ease;
                padding: 0.75rem 1rem;
                border-radius: 6px;
                border: none;
                background: ${type === 'success' ? '#dcfce7' : '#fee2e2'};
                color: ${type === 'success' ? '#166534' : '#991b1b'};
                font-size: 0.8rem;
                font-weight: 600;
            `;
            
            toast.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                ${message}
            `;
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.style.animation = 'slideOutRight 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    }));
});
</script>

<style>
@keyframes slideInRight {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

@keyframes slideOutRight {
    from { transform: translateX(0); opacity: 1; }
    to { transform: translateX(100%); opacity: 0; }
}

.alert-success {
    background: #dcfce7 !important;
    color: #166534 !important;
    border: 1px solid #bbf7d0 !important;
}

.alert-danger {
    background: #fee2e2 !important;
    color: #991b1b !important;
    border: 1px solid #fecaca !important;
}
</style>
@endsection