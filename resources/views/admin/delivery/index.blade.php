@extends('layouts.admin')

@section('title', 'Gestion des Livraisons')

@section('content')
<div class="container-fluid" x-data="deliveryIndex">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-truck text-primary me-2"></i>
                Gestion des Livraisons
            </h1>
            <p class="text-muted mb-0">Gérez vos transporteurs et expéditions</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.delivery.preparation') }}" class="btn btn-success">
                <i class="fas fa-plus me-1"></i>
                Nouvel Enlèvement
            </a>
            <a href="{{ route('admin.delivery.configuration') }}" class="btn btn-primary">
                <i class="fas fa-cog me-1"></i>
                Configurations
            </a>
        </div>
    </div>

    <!-- Statistiques générales -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Configurations Actives
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $generalStats['active_configurations'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-cog fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Enlèvements en Attente
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $generalStats['pending_pickups'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-box fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Expéditions Actives
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $generalStats['active_shipments'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-truck fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Expéditions
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $generalStats['total_shipments'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shipping-fast fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Grille des transporteurs -->
    <div class="row">
        @foreach($carriersData as $slug => $carrierData)
            <div class="col-lg-6 col-xl-4 mb-4">
                @include('admin.delivery.components.carrier-card', [
                    'slug' => $slug,
                    'carrier' => $carrierData
                ])
            </div>
        @endforeach
    </div>

    <!-- Section activité récente -->
    <div class="row mt-4">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Activité Récente</h6>
                    <a href="{{ route('admin.delivery.pickups') }}" class="btn btn-sm btn-primary">
                        Voir tout
                    </a>
                </div>
                <div class="card-body">
                    @include('admin.delivery.partials.recent-activity')
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Actions Rapides</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('admin.delivery.preparation') }}" 
                           class="list-group-item list-group-item-action d-flex align-items-center">
                            <i class="fas fa-plus text-success me-3"></i>
                            <div>
                                <div class="fw-bold">Créer un Enlèvement</div>
                                <small class="text-muted">Préparer de nouvelles expéditions</small>
                            </div>
                        </a>

                        <a href="{{ route('admin.delivery.pickups') }}" 
                           class="list-group-item list-group-item-action d-flex align-items-center">
                            <i class="fas fa-box text-primary me-3"></i>
                            <div>
                                <div class="fw-bold">Gérer les Enlèvements</div>
                                <small class="text-muted">Valider et suivre les pickups</small>
                            </div>
                        </a>

                        <a href="{{ route('admin.delivery.shipments') }}" 
                           class="list-group-item list-group-item-action d-flex align-items-center">
                            <i class="fas fa-truck text-info me-3"></i>
                            <div>
                                <div class="fw-bold">Suivre les Expéditions</div>
                                <small class="text-muted">Tracking en temps réel</small>
                            </div>
                        </a>

                        <a href="{{ route('admin.delivery.configuration') }}" 
                           class="list-group-item list-group-item-action d-flex align-items-center">
                            <i class="fas fa-cog text-secondary me-3"></i>
                            <div>
                                <div class="fw-bold">Configurer les APIs</div>
                                <small class="text-muted">Gérer les transporteurs</small>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin.delivery.modals.test-connection')
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('deliveryIndex', () => ({
        loading: false,
        stats: @json($generalStats),
        carriers: @json($carriersData),

        init() {
            this.loadRealtimeStats();
            // Actualiser les stats toutes les 30 secondes
            setInterval(() => this.loadRealtimeStats(), 30000);
        },

        async loadRealtimeStats() {
            try {
                const response = await axios.get('{{ route("admin.delivery.api.stats") }}');
                this.stats = response.data.general_stats;
            } catch (error) {
                console.error('Erreur chargement stats:', error);
            }
        },

        async testCarrierConnection(carrierSlug, configId) {
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
                    
                    // Recharger la page pour mettre à jour les statuts
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

        getStatusBadgeClass(status) {
            switch(status) {
                case 'connecté': return 'badge bg-success';
                case 'configuré_inactif': return 'badge bg-warning';
                case 'non_configuré': return 'badge bg-secondary';
                default: return 'badge bg-secondary';
            }
        },

        getStatusLabel(status) {
            switch(status) {
                case 'connecté': return 'Connecté';
                case 'configuré_inactif': return 'Inactif';
                case 'non_configuré': return 'Non configuré';
                default: return 'Inconnu';
            }
        }
    }));
});
</script>
@endpush

@push('styles')
<style>
.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}

.carrier-logo {
    width: 60px;
    height: 60px;
    object-fit: contain;
    background: #f8f9fa;
    border-radius: 8px;
    padding: 8px;
}

.status-indicator {
    position: absolute;
    top: 10px;
    right: 10px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

.status-indicator.connected {
    background-color: #28a745;
    box-shadow: 0 0 0 2px rgba(40, 167, 69, 0.3);
}

.status-indicator.disconnected {
    background-color: #dc3545;
    box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.3);
}

.status-indicator.inactive {
    background-color: #ffc107;
    box-shadow: 0 0 0 2px rgba(255, 193, 7, 0.3);
}
</style>
@endpush