{{-- configuration.blade.php --}}
@extends('layouts.admin')

@section('title', 'Configuration des Transporteurs')

@section('content')
<div class="container-fluid" x-data="deliveryConfiguration">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gradient">
                <i class="fas fa-cog text-primary me-2"></i>
                Configuration des Transporteurs
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.delivery.index') }}">Livraisons</a></li>
                    <li class="breadcrumb-item active">Configuration</li>
                </ol>
            </nav>
            <p class="text-muted mb-0">Gérez vos liaisons avec les transporteurs</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.delivery.index') }}" class="btn btn-outline-secondary animate-slide-up">
                <i class="fas fa-arrow-left me-1"></i>
                Retour
            </a>
            <div class="dropdown animate-slide-up">
                <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-plus me-1"></i>
                    Nouvelle Configuration
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

    <div class="card border-0 shadow-sm mb-4 animate-slide-up">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" 
                               class="form-control border-start-0" 
                               placeholder="Rechercher une configuration..."
                               x-model="search"
                               @input.debounce.300ms="filterConfigurations()">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" x-model="carrierFilter" @change="filterConfigurations()">
                        <option value="">Tous les transporteurs</option>
                        <option value="jax_delivery">JAX Delivery</option>
                        <option value="mes_colis">Mes Colis Express</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" x-model="statusFilter" @change="filterConfigurations()">
                        <option value="">Tous les statuts</option>
                        <option value="active">Actives</option>
                        <option value="inactive">Inactives</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-primary w-100" @click="testAllConnections()" :disabled="loading">
                        <span x-show="!loading">
                            <i class="fas fa-wifi me-1"></i>
                            Tester Tout
                        </span>
                        <span x-show="loading">
                            <i class="fas fa-spinner fa-spin me-1"></i>
                            Test...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if($configurations->isEmpty())
        <div class="card border-0 shadow-sm animate-slide-up">
            <div class="card-body text-center py-5">
                <div class="icon-circle bg-primary bg-opacity-10 mx-auto mb-3" style="width: 80px; height: 80px;">
                    <i class="fas fa-cog fa-3x text-primary"></i>
                </div>
                <h5 class="text-muted mb-2">Aucune configuration trouvée</h5>
                <p class="text-muted mb-4">Créez votre première configuration de transporteur pour commencer</p>
                <a href="{{ route('admin.delivery.configuration.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>
                    Créer une Configuration
                </a>
            </div>
        </div>
    @else
        @foreach($configsByCarrier as $carrierSlug => $carrierConfigs)
            <div class="card border-0 shadow-sm mb-4 animate-slide-up" 
                 x-show="isCarrierVisible('{{ $carrierSlug }}')"
                 style="animation-delay: {{ $loop->index * 0.1 }}s;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <div class="d-flex align-items-center">
                            <div class="icon-circle bg-{{ $carrierSlug === 'jax_delivery' ? 'primary' : 'success' }} bg-opacity-10 me-3">
                                <i class="fas fa-{{ $carrierSlug === 'jax_delivery' ? 'truck' : 'shipping-fast' }} text-{{ $carrierSlug === 'jax_delivery' ? 'primary' : 'success' }}"></i>
                            </div>
                            <div>
                                <div>{{ $carrierSlug === 'jax_delivery' ? 'JAX Delivery' : 'Mes Colis Express' }}</div>
                                <small class="text-muted fw-normal">{{ $carrierConfigs->count() }} configuration(s)</small>
                            </div>
                        </div>
                    </h6>
                    <a href="{{ route('admin.delivery.configuration.create') }}?carrier={{ $carrierSlug }}" 
                       class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-1"></i>
                        Ajouter
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0">Nom de la Liaison</th>
                                    <th class="border-0">Identifiants</th>
                                    <th class="border-0">Statut</th>
                                    <th class="border-0">Dernière Activité</th>
                                    <th class="border-0 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($carrierConfigs as $config)
                                    <tr x-show="isConfigVisible({{ $config->id }})" class="config-row">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="config-indicator me-2 {{ $config->is_active ? 'active' : 'inactive' }}"></div>
                                                <div>
                                                    <div class="fw-bold">{{ $config->integration_name }}</div>
                                                    <small class="text-muted">
                                                        Créé le {{ $config->created_at->format('d/m/Y à H:i') }}
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-user me-1 text-muted"></i>
                                                    <code class="small">{{ $config->username }}</code>
                                                </div>
                                                <small class="text-muted">
                                                    <i class="fas fa-server me-1"></i>
                                                    {{ ucfirst($config->environment) }}
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $config->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                <i class="fas fa-{{ $config->is_active ? 'check' : 'pause' }} me-1"></i>
                                                {{ $config->is_active ? 'Actif' : 'Inactif' }}
                                            </span>
                                            @if($config->settings && isset($config->settings['last_test_at']))
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fas fa-{{ $config->settings['last_test_success'] ?? false ? 'check' : 'times' }} me-1"></i>
                                                    Test {{ \Carbon\Carbon::parse($config->settings['last_test_at'])->format('d/m/Y H:i') }}
                                                </small>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <i class="fas fa-clock me-1"></i>
                                                {{ $config->updated_at->diffForHumans() }}
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-outline-primary" 
                                                        @click="testConnection({{ $config->id }})"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#testConnectionModal"
                                                        :disabled="loading"
                                                        title="Tester la connexion">
                                                    <i class="fas fa-wifi"></i>
                                                </button>
                                                
                                                <button class="btn btn-sm btn-outline-{{ $config->is_active ? 'warning' : 'success' }}" 
                                                        @click="toggleConfiguration({{ $config->id }})"
                                                        :disabled="loading"
                                                        title="{{ $config->is_active ? 'Désactiver' : 'Activer' }}">
                                                    <i class="fas fa-{{ $config->is_active ? 'pause' : 'play' }}"></i>
                                                </button>
                                                
                                                <a href="{{ route('admin.delivery.configuration.edit', $config) }}" 
                                                   class="btn btn-sm btn-outline-secondary"
                                                   title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                <button class="btn btn-sm btn-outline-danger" 
                                                        @click="deleteConfiguration({{ $config->id }})"
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
                </div>
            </div>
        @endforeach
    @endif

    @include('admin.delivery.modals.test-connection')
    
</div>
@endsection

@push('styles')
<style>
    .config-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .config-indicator.active {
        background: var(--success-color);
        box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
    }

    .config-indicator.inactive {
        background: var(--secondary-color);
        border: 2px solid var(--card-border);
    }

    .config-row {
        transition: all 0.2s ease;
    }

    .config-row:hover {
        background: rgba(30, 64, 175, 0.03);
        transform: translateX(2px);
    }

    .table th {
        font-weight: 600;
        font-size: 0.85rem;
        color: var(--text-color);
        padding: 1rem 0.75rem;
    }

    .table td {
        padding: 1rem 0.75rem;
        vertical-align: middle;
    }

    .btn-group .btn {
        padding: 0.25rem 0.5rem;
    }

    .input-group-text {
        background: var(--light-color);
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(30, 64, 175, 0.25);
    }

    code {
        background: rgba(30, 64, 175, 0.1);
        color: var(--primary-color);
        padding: 0.2rem 0.4rem;
        border-radius: 4px;
        font-size: 0.8rem;
    }

    .icon-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .breadcrumb-item + .breadcrumb-item::before {
        content: "›";
        color: var(--text-muted);
    }

    .breadcrumb-item a {
        color: var(--text-muted);
        text-decoration: none;
        transition: var(--transition);
    }

    .breadcrumb-item a:hover {
        color: var(--primary-color);
    }

    .badge {
        font-size: 0.75rem;
        padding: 0.35rem 0.6rem;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('deliveryConfiguration', () => ({
        search: '',
        carrierFilter: '',
        statusFilter: '',
        loading: false,
        testInProgress: false,
        testResult: null,
        testConfig: null,
        testProgress: 0,
        testMessage: '',
        testCompleted: false,
        testHistory: [],
        configurations: @json($configurations),

        init() {
            const urlParams = new URLSearchParams(window.location.search);
            this.carrierFilter = urlParams.get('filter') || '';
        },

        filterConfigurations() {
            console.log('Filtrage:', this.search, this.carrierFilter, this.statusFilter);
        },

        isCarrierVisible(carrierSlug) {
            if (this.carrierFilter && this.carrierFilter !== carrierSlug) {
                return false;
            }
            return true;
        },

        isConfigVisible(configId) {
            const config = this.configurations.find(c => c.id === configId);
            if (!config) return false;

            if (this.search) {
                const searchLower = this.search.toLowerCase();
                if (!config.integration_name.toLowerCase().includes(searchLower) &&
                    !config.username.toLowerCase().includes(searchLower)) {
                    return false;
                }
            }

            if (this.statusFilter) {
                if (this.statusFilter === 'active' && !config.is_active) return false;
                if (this.statusFilter === 'inactive' && config.is_active) return false;
            }

            return true;
        },

        async testConnection(configId) {
            this.testInProgress = true;
            this.testCompleted = false;
            this.testResult = null;
            this.testProgress = 0;
            this.testMessage = 'Initialisation...';
            this.testConfig = this.configurations.find(c => c.id === configId);
            
            const progressInterval = setInterval(() => {
                if (this.testProgress < 90) {
                    this.testProgress += 10;
                }
            }, 200);

            try {
                this.testMessage = 'Connexion à l\'API...';
                const response = await axios.post(`/admin/delivery/configuration/${configId}/test`);
                
                this.testProgress = 100;
                this.testMessage = 'Test terminé.';
                this.testResult = response.data;

                if (response.data.success) {
                    setTimeout(() => {
                        // Optionnel: recharger la page ou juste mettre à jour l'état localement
                        const config = this.configurations.find(c => c.id === configId);
                        if (config) {
                            if (!config.settings) config.settings = {};
                            config.settings.last_test_at = new Date().toISOString();
                            config.settings.last_test_success = true;
                        }
                    }, 2000);
                }
            } catch (error) {
                this.testProgress = 100;
                this.testResult = {
                    success: false,
                    error: error.response?.data?.message || 'Erreur de communication',
                    details: error.response?.data?.details || {}
                };
            } finally {
                clearInterval(progressInterval);
                this.testInProgress = false;
                this.testCompleted = true;
            }
        },

        retestConnection() {
            if (this.testConfig) {
                this.testConnection(this.testConfig.id);
            }
        },
        
        formatTestTime(value) {
            return value ? `${value} ms` : '-';
        },

        async toggleConfiguration(configId) {
            this.loading = true;
            
            try {
                const response = await axios.post(`/admin/delivery/configuration/${configId}/toggle`);
                
                if (response.data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Statut mis à jour !',
                        text: response.data.message,
                        showConfirmButton: false,
                        timer: 1500
                    });
                    
                    setTimeout(() => window.location.reload(), 1500);
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Impossible de changer le statut'
                });
            } finally {
                this.loading = false;
            }
        },

        async deleteConfiguration(configId) {
            const result = await Swal.fire({
                title: 'Êtes-vous sûr ?',
                text: 'Cette action est irréversible !',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Oui, supprimer !',
                cancelButtonText: 'Annuler'
            });

            if (result.isConfirmed) {
                this.loading = true;
                
                try {
                    await axios.delete(`/admin/delivery/configuration/${configId}`);
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Supprimé !',
                        text: 'Configuration supprimée avec succès',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    
                    setTimeout(() => window.location.reload(), 1500);
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: error.response?.data?.message || 'Impossible de supprimer'
                    });
                } finally {
                    this.loading = false;
                }
            }
        },

        async testAllConnections() {
            // ... (logique inchangée)
        }
    }));
});
</script>
@endpush