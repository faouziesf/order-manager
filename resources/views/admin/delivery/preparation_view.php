@extends('layouts.admin')

@section('title', 'Préparation des Enlèvements')

@section('content')
<div class="container-fluid" x-data="deliveryPreparation">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-box-open text-primary me-2"></i>
                Préparation des Enlèvements
            </h1>
            <p class="text-muted mb-0">Sélectionnez les commandes à expédier</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.delivery.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Retour
            </a>
            <a href="{{ route('admin.delivery.pickups') }}" class="btn btn-outline-primary">
                <i class="fas fa-truck me-1"></i>
                Voir les Enlèvements
            </a>
        </div>
    </div>

    <!-- Alertes -->
    @if($activeConfigurations->isEmpty())
        <div class="alert alert-warning d-flex align-items-center" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <div>
                <strong>Aucune configuration active !</strong>
                Vous devez d'abord configurer au moins un transporteur.
                <a href="{{ route('admin.delivery.configuration') }}" class="alert-link">Configurer maintenant</a>
            </div>
        </div>
    @endif

    <div class="row">
        <!-- Panel de sélection -->
        <div class="col-lg-8">
            <!-- Filtres et configuration -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-filter me-1"></i>
                        Configuration de l'Enlèvement
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Choix du transporteur -->
                        <div class="col-md-6 mb-3">
                            <label for="delivery_configuration_id" class="form-label">
                                <i class="fas fa-truck me-1"></i>
                                Transporteur <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" 
                                    id="delivery_configuration_id"
                                    x-model="selectedConfig"
                                    @change="loadOrders()"
                                    required>
                                <option value="">Choisir un transporteur...</option>
                                @foreach($activeConfigurations as $config)
                                    <option value="{{ $config->id }}">
                                        {{ $config->integration_name }} 
                                        ({{ $config->carrier_name }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Date d'enlèvement -->
                        <div class="col-md-6 mb-3">
                            <label for="pickup_date" class="form-label">
                                <i class="fas fa-calendar me-1"></i>
                                Date d'Enlèvement
                            </label>
                            <input type="date" 
                                   class="form-control" 
                                   id="pickup_date"
                                   x-model="pickupDate"
                                   :min="new Date().toISOString().split('T')[0]">
                        </div>
                    </div>

                    <!-- Filtres de recherche -->
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="search" class="form-label">Recherche</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" 
                                       class="form-control" 
                                       id="search"
                                       x-model="search"
                                       @input.debounce.300ms="loadOrders()"
                                       placeholder="Nom, téléphone, ID...">
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="governorate" class="form-label">Gouvernorat</label>
                            <select class="form-select" 
                                    id="governorate"
                                    x-model="governorate"
                                    @change="loadOrders()">
                                <option value="">Tous les gouvernorats</option>
                                @for($i = 1; $i <= 24; $i++)
                                    <option value="{{ $i }}">Gouvernorat {{ $i }}</option>
                                @endfor
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="per_page" class="form-label">Affichage</label>
                            <select class="form-select" 
                                    id="per_page"
                                    x-model="perPage"
                                    @change="loadOrders()">
                                <option value="25">25 par page</option>
                                <option value="50">50 par page</option>
                                <option value="100">100 par page</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Liste des commandes -->
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list me-1"></i>
                        Commandes Disponibles
                        <span x-show="orders.length > 0" class="badge bg-primary ms-2" x-text="orders.length"></span>
                    </h6>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-primary" 
                                @click="selectAll()"
                                x-show="orders.length > 0">
                            <i class="fas fa-check-square me-1"></i>
                            Tout Sélectionner
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" 
                                @click="clearSelection()"
                                x-show="selectedOrders.length > 0">
                            <i class="fas fa-times me-1"></i>
                            Désélectionner
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <!-- Chargement -->
                    <div x-show="loading" class="text-center py-4">
                        <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                        <p class="text-muted mt-2">Chargement des commandes...</p>
                    </div>

                    <!-- Message si aucune configuration -->
                    <div x-show="!selectedConfig && !loading" class="text-center py-4">
                        <i class="fas fa-truck fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Sélectionnez un transporteur</h5>
                        <p class="text-muted">Choisissez d'abord une configuration de transporteur pour afficher les commandes disponibles.</p>
                    </div>

                    <!-- Message si aucune commande -->
                    <div x-show="selectedConfig && !loading && orders.length === 0" class="text-center py-4">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Aucune commande disponible</h5>
                        <p class="text-muted">Toutes les commandes confirmées ont déjà été expédiées ou ne répondent pas aux critères de filtrage.</p>
                    </div>

                    <!-- Table des commandes -->
                    <div x-show="orders.length > 0" class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">
                                        <input type="checkbox" 
                                               class="form-check-input"
                                               @change="toggleAll($event.target.checked)">
                                    </th>
                                    <th>Commande</th>
                                    <th>Client</th>
                                    <th>Adresse</th>
                                    <th>Montant</th>
                                    <th>Stock</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="order in orders" :key="order.id">
                                    <tr :class="{ 'table-warning': !order.can_be_shipped }"
                                        @click="toggleOrder(order.id, $event)">
                                        <td>
                                            <input type="checkbox" 
                                                   class="form-check-input"
                                                   :value="order.id"
                                                   :checked="selectedOrders.includes(order.id)"
                                                   :disabled="!order.can_be_shipped"
                                                   @change="toggleOrder(order.id, $event)"
                                                   @click.stop>
                                        </td>
                                        <td>
                                            <div>
                                                <strong x-text="`#${order.id}`"></strong>
                                                <br>
                                                <small class="text-muted" x-text="order.status"></small>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <div x-text="order.customer_name"></div>
                                                <small class="text-muted" x-text="order.customer_phone"></small>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <div x-text="order.customer_city"></div>
                                                <small class="text-muted" x-text="order.region_name"></small>
                                            </div>
                                        </td>
                                        <td>
                                            <strong x-text="`${order.total_price} TND`"></strong>
                                        </td>
                                        <td>
                                            <span x-show="order.can_be_shipped" class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>OK
                                            </span>
                                            <span x-show="!order.can_be_shipped" class="badge bg-warning">
                                                <i class="fas fa-exclamation-triangle me-1"></i>Problème
                                            </span>
                                        </td>
                                        <td>
                                            <small x-text="formatDate(order.created_at)"></small>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div x-show="pagination && pagination.last_page > 1" class="d-flex justify-content-between align-items-center p-3 border-top">
                        <div>
                            <small class="text-muted">
                                Affichage de <span x-text="((pagination.current_page - 1) * pagination.per_page) + 1"></span> 
                                à <span x-text="Math.min(pagination.current_page * pagination.per_page, pagination.total)"></span> 
                                sur <span x-text="pagination.total"></span> commandes
                            </small>
                        </div>
                        <nav>
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item" :class="{ 'disabled': pagination.current_page <= 1 }">
                                    <button class="page-link" @click="changePage(pagination.current_page - 1)">Précédent</button>
                                </li>
                                
                                <template x-for="page in getPaginationPages()" :key="page">
                                    <li class="page-item" :class="{ 'active': page === pagination.current_page }">
                                        <button class="page-link" @click="changePage(page)" x-text="page"></button>
                                    </li>
                                </template>
                                
                                <li class="page-item" :class="{ 'disabled': pagination.current_page >= pagination.last_page }">
                                    <button class="page-link" @click="changePage(pagination.current_page + 1)">Suivant</button>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel de résumé et création -->
        <div class="col-lg-4">
            <div class="card shadow sticky-top" style="top: 20px;">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-shopping-cart me-1"></i>
                        Résumé de l'Enlèvement
                    </h6>
                </div>
                <div class="card-body">
                    <!-- Info transporteur sélectionné -->
                    <div x-show="selectedConfig" class="mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-truck text-primary me-2"></i>
                            <strong>Transporteur sélectionné</strong>
                        </div>
                        <div class="bg-light p-2 rounded" x-show="getSelectedConfigInfo()">
                            <small x-text="getSelectedConfigInfo()?.integration_name"></small><br>
                            <small class="text-muted" x-text="`(${getSelectedConfigInfo()?.carrier_name})`"></small>
                        </div>
                    </div>

                    <!-- Statistiques sélection -->
                    <div class="row text-center mb-3">
                        <div class="col-6">
                            <div class="h4 mb-0 text-primary" x-text="selectedOrders.length"></div>
                            <small class="text-muted">Commandes</small>
                        </div>
                        <div class="col-6">
                            <div class="h4 mb-0 text-success" x-text="getTotalAmount()"></div>
                            <small class="text-muted">TND Total</small>
                        </div>
                    </div>

                    <!-- Date d'enlèvement -->
                    <div class="mb-3" x-show="selectedOrders.length > 0">
                        <label class="form-label small">Date d'enlèvement :</label>
                        <div class="bg-light p-2 rounded text-center">
                            <i class="fas fa-calendar text-primary me-1"></i>
                            <span x-text="formatPickupDate()"></span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="d-grid gap-2">
                        <button class="btn btn-success"
                                @click="createPickup()"
                                :disabled="!canCreatePickup()"
                                x-show="selectedOrders.length > 0">
                            <i class="fas fa-plus me-1"></i>
                            Créer l'Enlèvement
                            <span class="badge bg-light text-dark ms-1" x-text="selectedOrders.length"></span>
                        </button>

                        <div x-show="selectedOrders.length === 0" class="text-center text-muted py-3">
                            <i class="fas fa-hand-pointer fa-2x mb-2"></i>
                            <p class="small mb-0">Sélectionnez des commandes pour créer un enlèvement</p>
                        </div>
                    </div>

                    <!-- Messages d'aide -->
                    <div x-show="selectedOrders.length > 0" class="mt-3">
                        <div class="alert alert-info py-2">
                            <small>
                                <i class="fas fa-info-circle me-1"></i>
                                Les commandes seront groupées dans un même enlèvement et envoyées au transporteur.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('deliveryPreparation', () => ({
        loading: false,
        orders: [],
        selectedOrders: [],
        selectedConfig: '',
        pickupDate: new Date(Date.now() + 86400000).toISOString().split('T')[0], // Demain
        search: '',
        governorate: '',
        perPage: 25,
        currentPage: 1,
        pagination: null,
        activeConfigurations: @json($activeConfigurations),

        init() {
            // Auto-sélectionner la première config si une seule disponible
            if (this.activeConfigurations.length === 1) {
                this.selectedConfig = this.activeConfigurations[0].id;
                this.loadOrders();
            }
        },

        async loadOrders() {
            if (!this.selectedConfig) {
                this.orders = [];
                return;
            }

            this.loading = true;
            
            try {
                const params = new URLSearchParams({
                    page: this.currentPage,
                    per_page: this.perPage,
                    search: this.search,
                    governorate: this.governorate,
                    configuration_id: this.selectedConfig
                });

                const response = await axios.get(`{{ route('admin.delivery.preparation.orders') }}?${params}`);
                
                if (response.data.success) {
                    this.orders = response.data.orders;
                    this.pagination = response.data.pagination;
                    // Nettoyer la sélection des commandes qui ne sont plus dans la liste
                    this.selectedOrders = this.selectedOrders.filter(id => 
                        this.orders.some(order => order.id === id)
                    );
                }
            } catch (error) {
                console.error('Erreur chargement commandes:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Impossible de charger les commandes',
                });
            } finally {
                this.loading = false;
            }
        },

        toggleOrder(orderId, event) {
            // Vérifier si la commande peut être expédiée
            const order = this.orders.find(o => o.id === orderId);
            if (!order || !order.can_be_shipped) return;

            if (event && event.target.type === 'checkbox') {
                // Géré par le checkbox
                if (event.target.checked) {
                    if (!this.selectedOrders.includes(orderId)) {
                        this.selectedOrders.push(orderId);
                    }
                } else {
                    this.selectedOrders = this.selectedOrders.filter(id => id !== orderId);
                }
            } else {
                // Clic sur la ligne
                if (this.selectedOrders.includes(orderId)) {
                    this.selectedOrders = this.selectedOrders.filter(id => id !== orderId);
                } else {
                    this.selectedOrders.push(orderId);
                }
            }
        },

        toggleAll(checked) {
            if (checked) {
                this.selectAll();
            } else {
                this.clearSelection();
            }
        },

        selectAll() {
            this.selectedOrders = this.orders
                .filter(order => order.can_be_shipped)
                .map(order => order.id);
        },

        clearSelection() {
            this.selectedOrders = [];
        },

        changePage(page) {
            if (page >= 1 && page <= this.pagination.last_page) {
                this.currentPage = page;
                this.loadOrders();
            }
        },

        getPaginationPages() {
            if (!this.pagination) return [];
            
            const current = this.pagination.current_page;
            const last = this.pagination.last_page;
            const pages = [];
            
            // Logique simple de pagination
            for (let i = Math.max(1, current - 2); i <= Math.min(last, current + 2); i++) {
                pages.push(i);
            }
            
            return pages;
        },

        getSelectedConfigInfo() {
            return this.activeConfigurations.find(config => config.id == this.selectedConfig);
        },

        getTotalAmount() {
            return this.selectedOrders.reduce((total, orderId) => {
                const order = this.orders.find(o => o.id === orderId);
                return total + (order ? parseFloat(order.total_price) : 0);
            }, 0).toFixed(3);
        },

        formatDate(dateString) {
            return new Date(dateString).toLocaleDateString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
        },

        formatPickupDate() {
            return new Date(this.pickupDate).toLocaleDateString('fr-FR', {
                weekday: 'long',
                day: '2-digit',
                month: 'long',
                year: 'numeric'
            });
        },

        canCreatePickup() {
            return this.selectedConfig && this.selectedOrders.length > 0;
        },

        async createPickup() {
            if (!this.canCreatePickup()) return;

            const result = await Swal.fire({
                title: 'Créer l\'enlèvement ?',
                html: `
                    <p>Vous allez créer un enlèvement avec :</p>
                    <ul class="text-start">
                        <li><strong>${this.selectedOrders.length}</strong> commande(s)</li>
                        <li>Total : <strong>${this.getTotalAmount()} TND</strong></li>
                        <li>Date : <strong>${this.formatPickupDate()}</strong></li>
                        <li>Transporteur : <strong>${this.getSelectedConfigInfo()?.integration_name}</strong></li>
                    </ul>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Créer l\'Enlèvement',
                cancelButtonText: 'Annuler'
            });

            if (!result.isConfirmed) return;

            this.loading = true;

            try {
                const response = await axios.post('{{ route("admin.delivery.preparation.store") }}', {
                    delivery_configuration_id: this.selectedConfig,
                    order_ids: this.selectedOrders,
                    pickup_date: this.pickupDate
                });

                if (response.data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Enlèvement créé !',
                        text: `Pickup #${response.data.pickup_id} créé avec ${response.data.orders_count} commande(s)`,
                        showConfirmButton: false,
                        timer: 3000
                    });

                    // Rediriger vers la gestion des pickups
                    setTimeout(() => {
                        window.location.href = '{{ route("admin.delivery.pickups") }}';
                    }, 3000);
                } else {
                    throw new Error(response.data.message || 'Erreur inconnue');
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: error.response?.data?.error || error.message || 'Impossible de créer l\'enlèvement',
                });
            } finally {
                this.loading = false;
            }
        }
    }));
});
</script>
@endpush

@push('styles')
<style>
.table tbody tr {
    cursor: pointer;
}

.table tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

.table tbody tr.table-warning {
    cursor: not-allowed;
}

.table tbody tr.table-warning:hover {
    background-color: rgba(255, 193, 7, 0.1);
}

.sticky-top {
    z-index: 1020;
}

.page-link {
    cursor: pointer;
}

.pagination .page-item.disabled .page-link {
    cursor: not-allowed;
}
</style>
@endpush