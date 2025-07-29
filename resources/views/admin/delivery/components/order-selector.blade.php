{{--
    Composant de sélection de commandes pour les enlèvements
    
    Props:
    - $orders: Collection des commandes disponibles
    - $selectedOrders: Array des IDs des commandes sélectionnées (Alpine.js binding)
    - $multiple: boolean pour permettre la sélection multiple (défaut: true)
    - $showFilters: boolean pour afficher les filtres (défaut: true)
--}}

@php
    $multiple = $multiple ?? true;
    $showFilters = $showFilters ?? true;
    $compact = $compact ?? false;
@endphp

<div class="order-selector" x-data="orderSelector">
    @if($showFilters)
        <!-- Barre de filtres -->
        <div class="card border-0 bg-light mb-3">
            <div class="card-body py-2">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" 
                                   class="form-control" 
                                   x-model="localSearch"
                                   @input.debounce.300ms="filterOrders()"
                                   placeholder="Rechercher commande, client...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select form-select-sm" 
                                x-model="governorateFilter"
                                @change="filterOrders()">
                            <option value="">Tous gouvernorats</option>
                            @for($i = 1; $i <= 24; $i++)
                                <option value="{{ $i }}">Gouvernorat {{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select form-select-sm" 
                                x-model="stockFilter"
                                @change="filterOrders()">
                            <option value="">Tous stocks</option>
                            <option value="available">Stock OK</option>
                            <option value="issues">Problèmes stock</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-sm btn-outline-secondary w-100" 
                                @click="resetFilters()">
                            <i class="fas fa-times me-1"></i>
                            Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Header avec actions -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h6 class="mb-0">
                <i class="fas fa-list me-1"></i>
                Commandes disponibles
                <span x-show="filteredOrders.length > 0" 
                      class="badge bg-primary ms-1" 
                      x-text="filteredOrders.length"></span>
            </h6>
            <small class="text-muted" x-show="selectedCount > 0">
                <span x-text="selectedCount"></span> commande(s) sélectionnée(s)
            </small>
        </div>
        
        @if($multiple)
            <div class="btn-group btn-group-sm" role="group">
                <button class="btn btn-outline-primary" 
                        @click="selectAll()"
                        x-show="filteredOrders.length > 0">
                    <i class="fas fa-check-square me-1"></i>
                    Tout sélectionner
                </button>
                <button class="btn btn-outline-secondary" 
                        @click="clearSelection()"
                        x-show="selectedCount > 0">
                    <i class="fas fa-square me-1"></i>
                    Désélectionner
                </button>
            </div>
        @endif
    </div>

    <!-- Liste des commandes -->
    <div class="border rounded">
        <!-- Header du tableau -->
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        @if($multiple)
                            <th width="40">
                                <input type="checkbox" 
                                       class="form-check-input"
                                       :checked="isAllSelected()"
                                       @change="toggleAll($event.target.checked)">
                            </th>
                        @endif
                        <th>Commande</th>
                        <th>Client</th>
                        @if(!$compact)
                            <th>Téléphone</th>
                            <th>Adresse</th>
                        @endif
                        <th>Montant</th>
                        <th>Stock</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Message si aucune commande -->
                    <tr x-show="filteredOrders.length === 0">
                        <td :colspan="$multiple ? {{ $compact ? 6 : 8 }} : {{ $compact ? 5 : 7 }}" class="text-center py-4">
                            <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">Aucune commande disponible</p>
                        </td>
                    </tr>

                    <!-- Liste des commandes -->
                    <template x-for="order in filteredOrders" :key="order.id">
                        <tr :class="{ 
                                'table-warning': !order.can_be_shipped,
                                'table-active': isSelected(order.id)
                            }"
                            @click="toggleOrder(order.id, $event)"
                            style="cursor: pointer;">
                            
                            @if($multiple)
                                <td @click.stop>
                                    <input type="checkbox" 
                                           class="form-check-input"
                                           :checked="isSelected(order.id)"
                                           :disabled="!order.can_be_shipped"
                                           @change="toggleOrder(order.id, $event)">
                                </td>
                            @endif
                            
                            <td>
                                <div>
                                    <strong x-text="`#${order.id}`"></strong>
                                    <br>
                                    <small class="text-muted" x-text="order.status"></small>
                                </div>
                            </td>
                            
                            <td>
                                <div x-text="order.customer_name"></div>
                                @if($compact)
                                    <small class="text-muted" x-text="order.customer_phone"></small>
                                @endif
                            </td>
                            
                            @if(!$compact)
                                <td>
                                    <span x-text="order.customer_phone"></span>
                                </td>
                                
                                <td>
                                    <div>
                                        <div x-text="order.customer_city"></div>
                                        <small class="text-muted" x-text="order.region_name"></small>
                                    </div>
                                </td>
                            @endif
                            
                            <td>
                                <strong x-text="`${order.total_price} TND`"></strong>
                            </td>
                            
                            <td>
                                <span x-show="order.can_be_shipped" class="badge bg-success">
                                    <i class="fas fa-check me-1"></i>OK
                                </span>
                                <span x-show="!order.can_be_shipped" 
                                      class="badge bg-warning"
                                      :title="getStockIssuesSummary(order)">
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

        <!-- Footer avec pagination simple si nécessaire -->
        <div x-show="filteredOrders.length > 50" class="border-top p-2 text-center">
            <small class="text-muted">
                Affichage des 50 premiers résultats - 
                <a href="#" @click.prevent="showAll = !showAll">
                    <span x-show="!showAll">Voir tout</span>
                    <span x-show="showAll">Réduire</span>
                </a>
            </small>
        </div>
    </div>

    <!-- Résumé de la sélection -->
    <div x-show="selectedCount > 0" class="mt-3">
        <div class="alert alert-info py-2">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <i class="fas fa-info-circle me-1"></i>
                    <strong x-text="selectedCount"></strong> commande(s) sélectionnée(s) pour un montant total de 
                    <strong x-text="`${getTotalAmount()} TND`"></strong>
                </div>
                <div class="col-md-4 text-end">
                    <button class="btn btn-sm btn-outline-secondary" @click="clearSelection()">
                        <i class="fas fa-times me-1"></i>
                        Désélectionner tout
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Composant Alpine.js pour le sélecteur de commandes
document.addEventListener('alpine:init', () => {
    Alpine.data('orderSelector', () => ({
        // Données
        allOrders: @json($orders ?? []),
        filteredOrders: [],
        selectedOrderIds: @json($selectedOrders ?? []),
        
        // Filtres locaux
        localSearch: '',
        governorateFilter: '',
        stockFilter: '',
        showAll: false,

        // Computed
        get selectedCount() {
            return this.selectedOrderIds.length;
        },

        init() {
            this.filteredOrders = this.allOrders;
            this.filterOrders();
        },

        // Méthodes de filtrage
        filterOrders() {
            let filtered = this.allOrders;

            // Filtre par recherche
            if (this.localSearch) {
                const search = this.localSearch.toLowerCase();
                filtered = filtered.filter(order => 
                    order.id.toString().includes(search) ||
                    order.customer_name.toLowerCase().includes(search) ||
                    order.customer_phone.includes(search)
                );
            }

            // Filtre par gouvernorat
            if (this.governorateFilter) {
                filtered = filtered.filter(order => 
                    order.customer_governorate == this.governorateFilter
                );
            }

            // Filtre par stock
            if (this.stockFilter === 'available') {
                filtered = filtered.filter(order => order.can_be_shipped);
            } else if (this.stockFilter === 'issues') {
                filtered = filtered.filter(order => !order.can_be_shipped);
            }

            // Limiter l'affichage si nécessaire
            if (!this.showAll && filtered.length > 50) {
                filtered = filtered.slice(0, 50);
            }

            this.filteredOrders = filtered;
        },

        resetFilters() {
            this.localSearch = '';
            this.governorateFilter = '';
            this.stockFilter = '';
            this.showAll = false;
            this.filterOrders();
        },

        // Méthodes de sélection
        isSelected(orderId) {
            return this.selectedOrderIds.includes(orderId);
        },

        isAllSelected() {
            const selectableOrders = this.filteredOrders.filter(order => order.can_be_shipped);
            return selectableOrders.length > 0 && 
                   selectableOrders.every(order => this.isSelected(order.id));
        },

        toggleOrder(orderId, event) {
            const order = this.allOrders.find(o => o.id === orderId);
            if (!order || !order.can_be_shipped) return;

            // Si c'est un clic sur le checkbox, ne pas faire de double toggle
            if (event && event.target.type === 'checkbox') {
                if (event.target.checked) {
                    if (!this.isSelected(orderId)) {
                        this.selectedOrderIds.push(orderId);
                    }
                } else {
                    this.selectedOrderIds = this.selectedOrderIds.filter(id => id !== orderId);
                }
                return;
            }

            // Toggle normal
            if (this.isSelected(orderId)) {
                this.selectedOrderIds = this.selectedOrderIds.filter(id => id !== orderId);
            } else {
                @if($multiple)
                    this.selectedOrderIds.push(orderId);
                @else
                    this.selectedOrderIds = [orderId];
                @endif
            }

            // Émettre un événement pour notifier le parent
            this.$dispatch('selection-changed', {
                selected: this.selectedOrderIds,
                orders: this.getSelectedOrders()
            });
        },

        toggleAll(checked) {
            const selectableOrders = this.filteredOrders.filter(order => order.can_be_shipped);
            
            if (checked) {
                // Ajouter tous les IDs qui ne sont pas déjà sélectionnés
                const newIds = selectableOrders
                    .map(order => order.id)
                    .filter(id => !this.isSelected(id));
                this.selectedOrderIds.push(...newIds);
            } else {
                // Retirer tous les IDs des commandes filtrées
                const idsToRemove = selectableOrders.map(order => order.id);
                this.selectedOrderIds = this.selectedOrderIds.filter(id => !idsToRemove.includes(id));
            }

            this.$dispatch('selection-changed', {
                selected: this.selectedOrderIds,
                orders: this.getSelectedOrders()
            });
        },

        selectAll() {
            this.toggleAll(true);
        },

        clearSelection() {
            this.selectedOrderIds = [];
            this.$dispatch('selection-changed', {
                selected: [],
                orders: []
            });
        },

        // Méthodes utilitaires
        getSelectedOrders() {
            return this.allOrders.filter(order => this.isSelected(order.id));
        },

        getTotalAmount() {
            return this.getSelectedOrders()
                .reduce((total, order) => total + parseFloat(order.total_price), 0)
                .toFixed(3);
        },

        getStockIssuesSummary(order) {
            if (!order.stock_issues || order.stock_issues.length === 0) {
                return 'Problème de stock non spécifié';
            }
            return order.stock_issues.map(issue => issue.message).join(', ');
        },

        formatDate(dateString) {
            return new Date(dateString).toLocaleDateString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
        }
    }));
});
</script>
@endpush

@push('styles')
<style>
.order-selector .table tbody tr {
    transition: background-color 0.2s ease;
}

.order-selector .table tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05) !important;
}

.order-selector .table tbody tr.table-warning:hover {
    background-color: rgba(255, 193, 7, 0.1) !important;
}

.order-selector .table tbody tr.table-active {
    background-color: rgba(0, 123, 255, 0.1) !important;
}

.order-selector .table tbody tr.table-warning {
    cursor: not-allowed;
}

.order-selector .table tbody tr.table-warning td {
    opacity: 0.7;
}
</style>
@endpush