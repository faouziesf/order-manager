{{-- Activité récente du système de livraison --}}
<div class="recent-activity-container" x-data="recentActivity">
    <!-- Loader -->
    <div x-show="loading" class="text-center py-3">
        <div class="spinner-border spinner-border-sm text-primary" role="status">
            <span class="visually-hidden">Chargement...</span>
        </div>
        <small class="text-muted ms-2">Chargement de l'activité...</small>
    </div>

    <!-- Liste des activités -->
    <div x-show="!loading && activities.length > 0" class="activity-list">
        <template x-for="activity in activities" :key="activity.id">
            <div class="activity-item d-flex align-items-start mb-3 p-2 rounded hover-bg">
                <!-- Icône de l'activité -->
                <div class="activity-icon me-3">
                    <div :class="getActivityIconClass(activity.type)" class="activity-icon-circle">
                        <i :class="getActivityIcon(activity.type)"></i>
                    </div>
                </div>

                <!-- Contenu de l'activité -->
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="activity-content">
                            <h6 class="mb-1" x-text="activity.title"></h6>
                            <p class="text-muted mb-1 small" x-text="activity.description"></p>
                            
                            <!-- Détails selon le type -->
                            <div x-show="activity.details" class="activity-details">
                                <template x-if="activity.type === 'pickup_created'">
                                    <div class="d-flex gap-3 small text-muted">
                                        <span>
                                            <i class="fas fa-truck me-1"></i>
                                            <span x-text="activity.details.carrier_name"></span>
                                        </span>
                                        <span>
                                            <i class="fas fa-box me-1"></i>
                                            <span x-text="activity.details.orders_count"></span> commandes
                                        </span>
                                        <span>
                                            <i class="fas fa-money-bill me-1"></i>
                                            <span x-text="activity.details.total_amount"></span> TND
                                        </span>
                                    </div>
                                </template>

                                <template x-if="activity.type === 'pickup_validated'">
                                    <div class="d-flex gap-3 small text-muted">
                                        <span>
                                            <i class="fas fa-check me-1"></i>
                                            Pickup #<span x-text="activity.details.pickup_id"></span>
                                        </span>
                                        <span>
                                            <i class="fas fa-truck me-1"></i>
                                            <span x-text="activity.details.carrier_name"></span>
                                        </span>
                                    </div>
                                </template>

                                <template x-if="activity.type === 'shipment_delivered'">
                                    <div class="d-flex gap-3 small text-muted">
                                        <span>
                                            <i class="fas fa-user me-1"></i>
                                            <span x-text="activity.details.customer_name"></span>
                                        </span>
                                        <span>
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            <span x-text="activity.details.city"></span>
                                        </span>
                                    </div>
                                </template>

                                <template x-if="activity.type === 'configuration_added'">
                                    <div class="small text-muted">
                                        <i class="fas fa-cog me-1"></i>
                                        <span x-text="activity.details.integration_name"></span>
                                        (<span x-text="activity.details.carrier_name"></span>)
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Timestamp et actions -->
                        <div class="activity-meta text-end">
                            <small class="text-muted" x-text="formatRelativeTime(activity.created_at)"></small>
                            
                            <!-- Actions rapides -->
                            <div class="activity-actions mt-1" x-show="activity.actions">
                                <template x-if="activity.type === 'pickup_created' && activity.details.pickup_id">
                                    <a :href="`/admin/delivery/pickups?id=${activity.details.pickup_id}`" 
                                       class="btn btn-xs btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </template>

                                <template x-if="activity.type === 'shipment_delivered' && activity.details.shipment_id">
                                    <a :href="`/admin/delivery/shipments?id=${activity.details.shipment_id}`" 
                                       class="btn btn-xs btn-outline-success">
                                        <i class="fas fa-check"></i>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Message si aucune activité -->
    <div x-show="!loading && activities.length === 0" class="text-center py-4">
        <i class="fas fa-clock fa-2x text-muted mb-2"></i>
        <h6 class="text-muted mb-1">Aucune activité récente</h6>
        <p class="text-muted small mb-0">Les dernières actions apparaîtront ici</p>
    </div>

    <!-- Lien pour voir plus -->
    <div x-show="!loading && activities.length > 0" class="text-center mt-3">
        <a href="{{ route('admin.delivery.pickups') }}" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-list me-1"></i>
            Voir toute l'activité
        </a>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('recentActivity', () => ({
        loading: true,
        activities: [],

        init() {
            this.loadRecentActivity();
            
            // Actualiser toutes les 2 minutes
            setInterval(() => this.loadRecentActivity(), 120000);
        },

        async loadRecentActivity() {
            this.loading = true;
            
            try {
                // Simuler des données d'activité récente
                // En production, cela devrait être un appel API réel
                await new Promise(resolve => setTimeout(resolve, 500));
                
                this.activities = await this.fetchRecentActivities();
            } catch (error) {
                console.error('Erreur chargement activités:', error);
                this.activities = [];
            } finally {
                this.loading = false;
            }
        },

        async fetchRecentActivities() {
            // TODO: Remplacer par un vrai appel API
            // const response = await axios.get('/admin/delivery/api/recent-activity');
            // return response.data.activities;
            
            // Données simulées pour la démonstration
            const now = new Date();
            return [
                {
                    id: 1,
                    type: 'pickup_created',
                    title: 'Nouvel enlèvement créé',
                    description: 'Pickup #15 avec 8 commandes',
                    created_at: new Date(now.getTime() - 15 * 60 * 1000), // Il y a 15 min
                    details: {
                        pickup_id: 15,
                        carrier_name: 'JAX Delivery',
                        orders_count: 8,
                        total_amount: '456.750'
                    },
                    actions: true
                },
                {
                    id: 2,
                    type: 'shipment_delivered',
                    title: 'Livraison confirmée',
                    description: 'Commande #2847 livrée avec succès',
                    created_at: new Date(now.getTime() - 45 * 60 * 1000), // Il y a 45 min
                    details: {
                        shipment_id: 234,
                        customer_name: 'Ahmed Ben Ali',
                        city: 'Tunis'
                    },
                    actions: true
                },
                {
                    id: 3,
                    type: 'pickup_validated',
                    title: 'Enlèvement validé',
                    description: 'Pickup #14 envoyé au transporteur',
                    created_at: new Date(now.getTime() - 2 * 60 * 60 * 1000), // Il y a 2h
                    details: {
                        pickup_id: 14,
                        carrier_name: 'Mes Colis Express'
                    },
                    actions: true
                },
                {
                    id: 4,
                    type: 'configuration_added',
                    title: 'Nouvelle configuration',
                    description: 'Configuration transporteur ajoutée',
                    created_at: new Date(now.getTime() - 4 * 60 * 60 * 1000), // Il y a 4h
                    details: {
                        integration_name: 'Entrepôt Principal',
                        carrier_name: 'JAX Delivery'
                    },
                    actions: false
                },
                {
                    id: 5,
                    type: 'shipment_in_transit',
                    title: 'Expédition en transit',
                    description: '3 colis récupérés par le transporteur',
                    created_at: new Date(now.getTime() - 6 * 60 * 60 * 1000), // Il y a 6h
                    details: {
                        count: 3
                    },
                    actions: false
                }
            ].slice(0, 5); // Limiter à 5 éléments
        },

        getActivityIconClass(type) {
            const classes = {
                'pickup_created': 'bg-primary',
                'pickup_validated': 'bg-success',
                'shipment_delivered': 'bg-success',
                'shipment_in_transit': 'bg-info',
                'configuration_added': 'bg-warning',
                'error': 'bg-danger'
            };
            return `activity-icon-circle ${classes[type] || 'bg-secondary'}`;
        },

        getActivityIcon(type) {
            const icons = {
                'pickup_created': 'fas fa-plus',
                'pickup_validated': 'fas fa-check',
                'shipment_delivered': 'fas fa-check-circle',
                'shipment_in_transit': 'fas fa-truck',
                'configuration_added': 'fas fa-cog',
                'error': 'fas fa-exclamation-triangle'
            };
            return icons[type] || 'fas fa-info-circle';
        },

        formatRelativeTime(date) {
            const now = new Date();
            const diffInMinutes = Math.floor((now - new Date(date)) / (1000 * 60));
            
            if (diffInMinutes < 1) return 'À l\'instant';
            if (diffInMinutes < 60) return `${diffInMinutes}min`;
            if (diffInMinutes < 1440) return `${Math.floor(diffInMinutes / 60)}h`;
            if (diffInMinutes < 10080) return `${Math.floor(diffInMinutes / 1440)}j`;
            
            return new Date(date).toLocaleDateString('fr-FR', {
                day: '2-digit',
                month: '2-digit'
            });
        }
    }));
});
</script>
@endpush

@push('styles')
<style>
.recent-activity-container {
    max-height: 400px;
    overflow-y: auto;
}

.activity-item {
    transition: background-color 0.2s ease;
}

.activity-item:hover,
.hover-bg:hover {
    background-color: rgba(0, 123, 255, 0.05) !important;
}

.activity-icon-circle {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 14px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.activity-content h6 {
    font-size: 0.9rem;
    font-weight: 600;
}

.activity-details {
    margin-top: 4px;
}

.activity-actions .btn-xs {
    padding: 0.15rem 0.3rem;
    font-size: 0.7rem;
    line-height: 1;
}

.activity-meta {
    min-width: 80px;
}

/* Animation pour les nouvelles activités */
@keyframes slideInRight {
    from {
        transform: translateX(20px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.activity-item {
    animation: slideInRight 0.3s ease;
}

/* Scrollbar personnalisée */
.recent-activity-container::-webkit-scrollbar {
    width: 4px;
}

.recent-activity-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.recent-activity-container::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

.recent-activity-container::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Responsive */
@media (max-width: 768px) {
    .activity-details {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    
    .activity-details > div {
        flex-direction: column;
        gap: 0;
    }
    
    .activity-meta {
        min-width: 60px;
    }
}
</style>
@endpush