@extends('layouts.admin')

@section('title', 'Configuration des Transporteurs')

@section('content')
<div class="container-fluid" x-data="deliveryConfiguration">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-cog text-primary me-2"></i>
                Configuration des Transporteurs
            </h1>
            <p class="text-muted mb-0">Gérez vos liaisons avec les transporteurs</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.delivery.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Retour
            </a>
            <div class="dropdown">
                <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-plus me-1"></i>
                    Nouvelle Configuration
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('admin.delivery.configuration.create') }}?carrier=jax_delivery">
                        <i class="fas fa-truck me-2 text-primary"></i>
                        JAX Delivery
                    </a></li>
                    <li><a class="dropdown-item" href="{{ route('admin.delivery.configuration.create') }}?carrier=mes_colis">
                        <i class="fas fa-shipping-fast me-2 text-success"></i>
                        Mes Colis Express
                    </a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Filtres et recherche -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" 
                               class="form-control" 
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
                    <button class="btn btn-outline-primary w-100" @click="testAllConnections()">
                        <i class="fas fa-wifi me-1"></i>
                        Tester Tout
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des configurations -->
    @if($configurations->isEmpty())
        <div class="card shadow">
            <div class="card-body text-center py-5">
                <i class="fas fa-cog fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Aucune configuration trouvée</h5>
                <p class="text-muted mb-4">Créez votre première configuration de transporteur pour commencer</p>
                <a href="{{ route('admin.delivery.configuration.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>
                    Créer une Configuration
                </a>
            </div>
        </div>
    @else
        @foreach($configsByCarrier as $carrierSlug => $carrierConfigs)
            <div class="card shadow mb-4" x-show="isCarrierVisible('{{ $carrierSlug }}')">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-{{ $carrierSlug === 'jax_delivery' ? 'truck' : 'shipping-fast' }} me-2"></i>
                        {{ $carrierSlug === 'jax_delivery' ? 'JAX Delivery' : 'Mes Colis Express' }}
                        <span class="badge bg-secondary ms-2">{{ $carrierConfigs->count() }} configuration(s)</span>
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
                                    <th>Nom de la Liaison</th>
                                    <th>Identifiants</th>
                                    <th>Statut</th>
                                    <th>Dernière Activité</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($carrierConfigs as $config)
                                    <tr x-show="isConfigVisible({{ $config->id }})">
                                        <td>
                                            <div class="d-flex align-items-center">
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
                                                <strong>Compte :</strong> {{ $config->username }}<br>
                                                <small class="text-muted">
                                                    Environnement : {{ ucfirst($config->environment) }}
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $config->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $config->is_active ? 'Actif' : 'Inactif' }}
                                            </span>
                                            @if($config->settings && isset($config->settings['last_test_at']))
                                                <br><small class="text-muted">
                                                    Testé le {{ \Carbon\Carbon::parse($config->settings['last_test_at'])->format('d/m/Y H:i') }}
                                                </small>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $config->updated_at->diffForHumans() }}
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-outline-primary" 
                                                        @click="testConnection({{ $config->id }})"
                                                        title="Tester la connexion">
                                                    <i class="fas fa-wifi"></i>
                                                </button>
                                                
                                                <button class="btn btn-sm btn-outline-{{ $config->is_active ? 'warning' : 'success' }}" 
                                                        @click="toggleConfiguration({{ $config->id }})"
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
</div>

@include('admin.delivery.modals.test-connection')
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('deliveryConfiguration', () => ({
        search: '',
        carrierFilter: '',
        statusFilter: '',
        loading: false,
        configurations: @json($configurations),

        init() {
            // Appliquer les filtres depuis l'URL si présents
            const urlParams = new URLSearchParams(window.location.search);
            this.carrierFilter = urlParams.get('filter') || '';
        },

        filterConfigurations() {
            // La logique de filtrage est côté client pour une meilleure UX
            // En production, vous pourriez vouloir une pagination côté serveur
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

            // Filtre de recherche
            if (this.search) {
                const searchLower = this.search.toLowerCase();
                if (!config.integration_name.toLowerCase().includes(searchLower) &&
                    !config.username.toLowerCase().includes(searchLower)) {
                    return false;
                }
            }

            // Filtre de statut
            if (this.statusFilter) {
                if (this.statusFilter === 'active' && !config.is_active) return false;
                if (this.statusFilter === 'inactive' && config.is_active) return false;
            }

            return true;
        },

        async testConnection(configId) {
            this.loading = true;
            
            try {
                const response = await axios.post(`/admin/delivery/configuration/${configId}/test`);
                
                if (response.data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Connexion réussie !',
                        text: response.data.message,
                        showConfirmButton: false,
                        timer: 2000
                    });
                    
                    // Recharger la page pour mettre à jour
                    setTimeout(() => window.location.reload(), 2000);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Échec de connexion',
                        text: response.data.error || 'Erreur inconnue',
                    });
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Impossible de tester la connexion',
                });
            } finally {
                this.loading = false;
            }
        },

        async toggleConfiguration(configId) {
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
                    
                    // Mettre à jour localement
                    const config = this.configurations.find(c => c.id === configId);
                    if (config) {
                        config.is_active = response.data.is_active;
                    }
                    
                    setTimeout(() => window.location.reload(), 1500);
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Impossible de changer le statut',
                });
            }
        },

        async deleteConfiguration(configId) {
            const result = await Swal.fire({
                title: 'Êtes-vous sûr ?',
                text: 'Cette action est irréversible !',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Oui, supprimer !',
                cancelButtonText: 'Annuler'
            });

            if (result.isConfirmed) {
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
                        text: error.response?.data?.message || 'Impossible de supprimer',
                    });
                }
            }
        },

        async testAllConnections() {
            const activeConfigs = this.configurations.filter(c => c.is_active);
            
            if (activeConfigs.length === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'Aucune configuration active',
                    text: 'Activez au moins une configuration pour tester',
                });
                return;
            }

            this.loading = true;
            let successCount = 0;
            let errorCount = 0;

            for (const config of activeConfigs) {
                try {
                    const response = await axios.post(`/admin/delivery/configuration/${config.id}/test`);
                    if (response.data.success) {
                        successCount++;
                    } else {
                        errorCount++;
                    }
                } catch (error) {
                    errorCount++;
                }
            }

            this.loading = false;

            Swal.fire({
                icon: errorCount === 0 ? 'success' : 'warning',
                title: 'Tests terminés',
                html: `
                    <p><strong>${successCount}</strong> connexion(s) réussie(s)</p>
                    <p><strong>${errorCount}</strong> échec(s)</p>
                `,
            });

            setTimeout(() => window.location.reload(), 2000);
        }
    }));
});
</script>
@endpush