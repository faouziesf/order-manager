@extends('layouts.admin')

@section('title', 'Gestion des Expéditions')

@section('content')
<div class="container-fluid" x-data="deliveryShipments">
    <!-- Header avec breadcrumb -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gradient">
                <i class="fas fa-shipping-fast text-primary me-2"></i>
                Gestion des Expéditions
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.delivery.index') }}">Livraisons</a></li>
                    <li class="breadcrumb-item active">Expéditions</li>
                </ol>
            </nav>
            <p class="text-muted mb-0">Suivez vos expéditions en temps réel</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.delivery.index') }}" class="btn btn-outline-secondary animate-slide-up">
                <i class="fas fa-arrow-left me-1"></i>
                Retour
            </a>
            <button class="btn btn-outline-primary animate-slide-up" @click="refreshAllTracking()" :disabled="refreshing">
                <span x-show="!refreshing">
                    <i class="fas fa-sync me-1"></i>
                    Actualiser Suivi
                </span>
                <span x-show="refreshing">
                    <i class="fas fa-spinner fa-spin me-1"></i>
                    Actualisation...
                </span>
            </button>
        </div>
    </div>

    <!-- Statistiques rapides -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm animate-slide-up" style="animation-delay: 0.1s;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                En Transit
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800" x-text="stats.in_transit">0</div>
                        </div>
                        <div class="ms-3">
                            <div class="icon-circle bg-primary bg-opacity-10">
                                <i class="fas fa-truck-moving fa-2x text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm animate-slide-up" style="animation-delay: 0.2s;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Livrées
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800" x-text="stats.delivered">0</div>
                        </div>
                        <div class="ms-3">
                            <div class="icon-circle bg-success bg-opacity-10">
                                <i class="fas fa-check-circle fa-2x text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm animate-slide-up" style="animation-delay: 0.3s;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                En Retour
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800" x-text="stats.in_return">0</div>
                        </div>
                        <div class="ms-3">
                            <div class="icon-circle bg-warning bg-opacity-10">
                                <i class="fas fa-undo fa-2x text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm animate-slide-up" style="animation-delay: 0.4s;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Anomalies
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800" x-text="stats.anomaly">0</div>
                        </div>
                        <div class="ms-3">
                            <div class="icon-circle bg-danger bg-opacity-10">
                                <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres et recherche -->
    <div class="card border-0 shadow-sm mb-4 animate-slide-up">
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Recherche</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" 
                               class="form-control border-start-0" 
                               placeholder="Numéro suivi, commande..."
                               x-model="searchQuery"
                               @input.debounce.300ms="filterShipments()">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Statut</label>
                    <select class="form-select" x-model="statusFilter" @change="filterShipments()">
                        <option value="">Tous</option>
                        <option value="created">Créées</option>
                        <option value="validated">Validées</option>
                        <option value="picked_up_by_carrier">Récupérées</option>
                        <option value="in_transit">En transit</option>
                        <option value="delivered">Livrées</option>
                        <option value="in_return">En retour</option>
                        <option value="anomaly">Anomalies</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Transporteur</label>
                    <select class="form-select" x-model="carrierFilter" @change="filterShipments()">
                        <option value="">Tous</option>
                        <option value="jax_delivery">JAX Delivery</option>
                        <option value="mes_colis">Mes Colis Express</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Période</label>
                    <select class="form-select" x-model="periodFilter" @change="filterShipments()">
                        <option value="">Toutes</option>
                        <option value="today">Aujourd'hui</option>
                        <option value="yesterday">Hier</option>
                        <option value="week">Cette semaine</option>
                        <option value="month">Ce mois</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary flex-fill" @click="refreshShipments()" :disabled="loading">
                            <span x-show="!loading">
                                <i class="fas fa-sync me-1"></i>
                                Actualiser
                            </span>
                            <span x-show="loading">
                                <i class="fas fa-spinner fa-spin me-1"></i>
                            </span>
                        </button>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-download"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" @click="exportShipments('pdf')">
                                    <i class="fas fa-file-pdf me-2 text-danger"></i>PDF
                                </a></li>
                                <li><a class="dropdown-item" href="#" @click="exportShipments('excel')">
                                    <i class="fas fa-file-excel me-2 text-success"></i>Excel
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des expéditions -->
    <div class="card border-0 shadow-sm animate-slide-up">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list me-1"></i>
                Expéditions
                <span x-show="filteredShipments.length > 0" class="badge bg-primary ms-2" x-text="filteredShipments.length"></span>
            </h6>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-info" @click="trackSelected()" x-show="selectedShipments.length > 0">
                    <i class="fas fa-route me-1"></i>
                    Suivre Sélection
                </button>
                <button class="btn btn-sm btn-outline-success" @click="markSelectedAsDelivered()" x-show="selectedShipments.length > 0">
                    <i class="fas fa-check me-1"></i>
                    Marquer Livrées
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <!-- Chargement -->
            <div x-show="loading" class="text-center py-5">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
                <p class="text-muted">Chargement des expéditions...</p>
            </div>

            <!-- Aucune expédition -->
            <div x-show="!loading && filteredShipments.length === 0" class="text-center py-5">
                <i class="fas fa-shipping-fast fa-3x text-muted mb-3"></i>
                <h6 class="text-muted mb-1">Aucune expédition trouvée</h6>
                <p class="text-muted small mb-4">Créez des enlèvements pour générer des expéditions</p>
                <a href="{{ route('admin.delivery.preparation') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>
                    Créer un Enlèvement
                </a>
            </div>

            <!-- Tableau des expéditions -->
            <div x-show="!loading && filteredShipments.length > 0" class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0" style="width: 50px;">
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           :checked="isAllSelected()"
                                           @change="toggleAllSelection($event.target.checked)">
                                </div>
                            </th>
                            <th class="border-0">Expédition</th>
                            <th class="border-0">Destinataire</th>
                            <th class="border-0">Transporteur</th>
                            <th class="border-0">Suivi</th>
                            <th class="border-0">Montant</th>
                            <th class="border-0">Statut</th>
                            <th class="border-0">Dernière MAJ</th>
                            <th class="border-0">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="shipment in filteredShipments" :key="shipment.id">
                            <tr :class="{ 'table-primary': isSelected(shipment.id) }" class="shipment-row">
                                <td @click.stop>
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               :checked="isSelected(shipment.id)"
                                               @change="toggleSelection(shipment.id)">
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="shipment-indicator me-2" :class="getStatusIndicatorClass(shipment.status)"></div>
                                        <div>
                                            <strong x-text="`#${shipment.id}`" class="text-primary"></strong>
                                            <br><small class="text-muted">Commande #<span x-text="shipment.order_id"></span></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <div class="fw-bold" x-text="shipment.recipient_info.name"></div>
                                        <small class="text-muted">
                                            <i class="fas fa-phone me-1"></i>
                                            <span x-text="shipment.recipient_info.phone"></span>
                                        </small>
                                        <br><small class="text-muted">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            <span x-text="shipment.recipient_info.city"></span>
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="icon-circle me-2" :class="getCarrierBgClass(shipment.carrier_slug)" style="width: 32px; height: 32px;">
                                            <i :class="getCarrierIcon(shipment.carrier_slug)" :class="getCarrierTextClass(shipment.carrier_slug)"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold" x-text="getCarrierName(shipment.carrier_slug)"></div>
                                            <small class="text-muted" x-text="shipment.integration_name"></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div x-show="shipment.pos_barcode">
                                        <code class="small text-primary" x-text="shipment.pos_barcode"></code>
                                        <br><a href="#" @click="trackShipment(shipment.id)" class="small text-decoration-none">
                                            <i class="fas fa-route me-1"></i>Suivre
                                        </a>
                                    </div>
                                    <div x-show="!shipment.pos_barcode">
                                        <small class="text-muted">Non assigné</small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong x-text="`${shipment.cod_amount} TND`" class="text-success"></strong>
                                        <br><small class="text-muted" x-text="`${shipment.weight} kg`"></small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge" :class="getStatusBadgeClass(shipment.status)">
                                        <i :class="getStatusIcon(shipment.status)" class="me-1"></i>
                                        <span x-text="getStatusLabel(shipment.status)"></span>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted" x-text="getTimeSince(shipment.updated_at)"></small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-outline-primary" 
                                                @click="viewShipment(shipment.id)"
                                                title="Voir les détails">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <button x-show="shipment.pos_barcode" 
                                                class="btn btn-sm btn-outline-info" 
                                                @click="trackShipment(shipment.id)"
                                                title="Actualiser suivi">
                                            <i class="fas fa-sync"></i>
                                        </button>
                                        
                                        <button x-show="['in_transit', 'picked_up_by_carrier'].includes(shipment.status)" 
                                                class="btn btn-sm btn-outline-success" 
                                                @click="markAsDelivered(shipment.id)"
                                                title="Marquer livrée">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        
                                        <button class="btn btn-sm btn-outline-secondary" 
                                                @click="contactCustomer(shipment.recipient_info.phone)"
                                                title="Contacter client">
                                            <i class="fas fa-phone"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div x-show="!loading && filteredShipments.length > 0" class="card-footer bg-transparent">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted" x-text="`Affichage de ${filteredShipments.length} expédition(s)`"></small>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item">
                            <a class="page-link" href="#" @click.prevent="previousPage()">Précédent</a>
                        </li>
                        <li class="page-item active">
                            <a class="page-link" href="#" x-text="currentPage"></a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="#" @click.prevent="nextPage()">Suivant</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Modal détails expédition -->
    <div class="modal fade" id="shipmentDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title">
                        <i class="fas fa-shipping-fast me-2"></i>
                        Détails de l'Expédition
                        <span x-show="selectedShipment" x-text="`#${selectedShipment?.id}`"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" x-show="selectedShipment">
                    <!-- Contenu sera similaire à la modal pickup mais adapté pour les expéditions -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="text-primary mb-3">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Informations Expédition
                                    </h6>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <strong>ID:</strong> <span x-text="`#${selectedShipment?.id}`"></span>
                                        </div>
                                        <div class="col-sm-6">
                                            <strong>Commande:</strong> <span x-text="`#${selectedShipment?.order_id}`"></span>
                                        </div>
                                        <div class="col-sm-6 mt-2">
                                            <strong>Suivi:</strong> 
                                            <code x-show="selectedShipment?.pos_barcode" x-text="selectedShipment?.pos_barcode"></code>
                                            <span x-show="!selectedShipment?.pos_barcode" class="text-muted">Non assigné</span>
                                        </div>
                                        <div class="col-sm-6 mt-2">
                                            <strong>Statut:</strong>
                                            <span class="badge ms-1" :class="getStatusBadgeClass(selectedShipment?.status)">
                                                <span x-text="getStatusLabel(selectedShipment?.status)"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="text-success mb-3">
                                        <i class="fas fa-user me-1"></i>
                                        Destinataire
                                    </h6>
                                    <div>
                                        <div class="fw-bold" x-text="selectedShipment?.recipient_info?.name"></div>
                                        <div class="text-muted mt-1">
                                            <i class="fas fa-phone me-1"></i>
                                            <span x-text="selectedShipment?.recipient_info?.phone"></span>
                                        </div>
                                        <div class="text-muted">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            <span x-text="selectedShipment?.recipient_info?.address"></span>
                                        </div>
                                        <div class="text-muted">
                                            <span x-text="selectedShipment?.recipient_info?.city"></span>, 
                                            <span x-text="selectedShipment?.recipient_info?.governorate"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Historique de suivi -->
                    <div class="card border-0" x-show="selectedShipment?.tracking_history && selectedShipment.tracking_history.length > 0">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-route me-1"></i>
                                Historique de Suivi
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                <template x-for="event in selectedShipment?.tracking_history || []" :key="event.id">
                                    <div class="timeline-item">
                                        <div class="timeline-marker" :class="getTimelineMarkerClass(event.status)"></div>
                                        <div class="timeline-content">
                                            <div class="d-flex justify-content-between">
                                                <strong x-text="event.label"></strong>
                                                <small class="text-muted" x-text="formatDateTime(event.timestamp)"></small>
                                            </div>
                                            <p x-show="event.location" class="mb-0 text-muted" x-text="event.location"></p>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer border-0">
                    <div class="d-flex justify-content-between w-100">
                        <div>
                            <!-- Actions selon le statut -->
                            <button x-show="selectedShipment?.pos_barcode" 
                                    class="btn btn-primary"
                                    @click="trackShipment(selectedShipment.id)">
                                <i class="fas fa-sync me-1"></i>
                                Actualiser Suivi
                            </button>
                            
                            <button x-show="['in_transit', 'picked_up_by_carrier'].includes(selectedShipment?.status)" 
                                    class="btn btn-success ms-2"
                                    @click="markAsDelivered(selectedShipment.id); closeShipmentModal()">
                                <i class="fas fa-check me-1"></i>
                                Marquer Livrée
                            </button>
                        </div>
                        
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Fermer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .icon-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .shipment-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .shipment-indicator.created { background: var(--secondary-color); border: 2px solid var(--card-border); }
    .shipment-indicator.validated { background: var(--primary-color); box-shadow: 0 0 0 2px rgba(30, 64, 175, 0.2); }
    .shipment-indicator.picked_up_by_carrier { background: var(--warning-color); box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.2); }
    .shipment-indicator.in_transit { background: var(--info-color); box-shadow: 0 0 0 2px rgba(6, 182, 212, 0.2); }
    .shipment-indicator.delivered { background: var(--success-color); box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2); }
    .shipment-indicator.in_return { background: var(--warning-color); box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.2); }
    .shipment-indicator.anomaly { background: var(--danger-color); box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.2); }

    .shipment-row {
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .shipment-row:hover {
        background: rgba(30, 64, 175, 0.03);
        transform: translateX(2px);
    }

    .shipment-row.table-primary {
        background-color: rgba(30, 64, 175, 0.1) !important;
    }

    .timeline {
        position: relative;
        padding-left: 30px;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e9ecef;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 20px;
    }

    .timeline-marker {
        position: absolute;
        left: -22px;
        top: 5px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: var(--primary-color);
        border: 3px solid #fff;
        box-shadow: 0 0 0 2px var(--primary-color);
    }

    .timeline-marker.delivered {
        background: var(--success-color);
        box-shadow: 0 0 0 2px var(--success-color);
    }

    .timeline-content {
        background: #f8f9fa;
        padding: 10px 15px;
        border-radius: 6px;
        margin-left: 15px;
    }

    .text-xs {
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.05em;
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

    .input-group-text {
        background: var(--light-color);
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(30, 64, 175, 0.25);
    }

    .form-check-input:checked {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }

    .modal-content {
        border-radius: var(--border-radius-lg);
        overflow: hidden;
    }

    .spinner-border {
        width: 3rem;
        height: 3rem;
    }

    .badge {
        font-size: 0.75rem;
        padding: 0.35rem 0.6rem;
    }

    .btn-group .btn {
        padding: 0.25rem 0.5rem;
    }

    code {
        background: rgba(30, 64, 175, 0.1);
        color: var(--primary-color);
        padding: 0.2rem 0.4rem;
        border-radius: 4px;
        font-size: 0.8rem;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('deliveryShipments', () => ({
        loading: false,
        refreshing: false,
        searchQuery: '',
        statusFilter: '',
        carrierFilter: '',
        periodFilter: '',
        currentPage: 1,
        shipments: [],
        filteredShipments: [],
        selectedShipments: [],
        selectedShipment: null,
        stats: {
            in_transit: 0,
            delivered: 0,
            in_return: 0,
            anomaly: 0
        },

        init() {
            this.loadShipments();
            this.loadStats();
            
            // Actualiser toutes les 60 secondes
            setInterval(() => {
                this.loadShipments(false);
                this.loadStats();
            }, 60000);
        },

        async loadShipments(showLoading = true) {
            if (showLoading) this.loading = true;
            
            try {
                // Simuler le chargement des expéditions
                await new Promise(resolve => setTimeout(resolve, 800));
                
                // Données simulées - remplacer par un vrai appel API
                this.shipments = [
                    {
                        id: 1001,
                        order_id: 2847,
                        status: 'in_transit',
                        carrier_slug: 'jax_delivery',
                        integration_name: 'Boutique Principale',
                        pos_barcode: 'JAX240215001',
                        cod_amount: 89.900,
                        weight: 2.5,
                        created_at: new Date(Date.now() - 2 * 24 * 60 * 60 * 1000),
                        updated_at: new Date(Date.now() - 4 * 60 * 60 * 1000),
                        recipient_info: {
                            name: 'Ahmed Ben Ali',
                            phone: '20123456',
                            address: '15 Rue de la République',
                            city: 'Tunis',
                            governorate: 'Tunis'
                        },
                        tracking_history: [
                            {
                                id: 1,
                                status: 'in_transit',
                                label: 'En cours de livraison',
                                location: 'Centre de tri Tunis',
                                timestamp: new Date(Date.now() - 2 * 60 * 60 * 1000)
                            },
                            {
                                id: 2,
                                status: 'picked_up_by_carrier',
                                label: 'Récupéré par le transporteur',
                                location: 'Entrepôt principal',
                                timestamp: new Date(Date.now() - 24 * 60 * 60 * 1000)
                            }
                        ]
                    },
                    {
                        id: 1002,
                        order_id: 2848,
                        status: 'delivered',
                        carrier_slug: 'mes_colis',
                        integration_name: 'Entrepôt Nord',
                        pos_barcode: 'MC240215002',
                        cod_amount: 156.450,
                        weight: 1.8,
                        created_at: new Date(Date.now() - 3 * 24 * 60 * 60 * 1000),
                        updated_at: new Date(Date.now() - 6 * 60 * 60 * 1000),
                        recipient_info: {
                            name: 'Fatma Trabelsi',
                            phone: '25987654',
                            address: '42 Avenue Habib Bourguiba',
                            city: 'Ariana',
                            governorate: 'Ariana'
                        },
                        tracking_history: []
                    },
                    {
                        id: 1003,
                        order_id: 2849,
                        status: 'anomaly',
                        carrier_slug: 'jax_delivery',
                        integration_name: 'Boutique Sud',
                        pos_barcode: 'JAX240215003',
                        cod_amount: 78.200,
                        weight: 3.2,
                        created_at: new Date(Date.now() - 4 * 24 * 60 * 60 * 1000),
                        updated_at: new Date(Date.now() - 8 * 60 * 60 * 1000),
                        recipient_info: {
                            name: 'Mohamed Slim',
                            phone: '28456789',
                            address: '7 Rue des Oliviers',
                            city: 'Sfax',
                            governorate: 'Sfax'
                        },
                        tracking_history: []
                    }
                ];
                
                this.filterShipments();
            } catch (error) {
                console.error('Erreur chargement expéditions:', error);
                this.shipments = [];
                this.filteredShipments = [];
            } finally {
                if (showLoading) this.loading = false;
            }
        },

        async loadStats() {
            // Calculer les stats à partir des données
            this.stats = {
                in_transit: this.shipments.filter(s => s.status === 'in_transit').length,
                delivered: this.shipments.filter(s => s.status === 'delivered').length,
                in_return: this.shipments.filter(s => s.status === 'in_return').length,
                anomaly: this.shipments.filter(s => s.status === 'anomaly').length
            };
        },

        filterShipments() {
            let filtered = [...this.shipments];

            // Filtre de recherche
            if (this.searchQuery) {
                const query = this.searchQuery.toLowerCase();
                filtered = filtered.filter(shipment => 
                    shipment.id.toString().includes(query) ||
                    shipment.order_id.toString().includes(query) ||
                    (shipment.pos_barcode && shipment.pos_barcode.toLowerCase().includes(query)) ||
                    shipment.recipient_info.name.toLowerCase().includes(query) ||
                    shipment.recipient_info.phone.includes(query)
                );
            }

            // Filtre par statut
            if (this.statusFilter) {
                filtered = filtered.filter(shipment => shipment.status === this.statusFilter);
            }

            // Filtre par transporteur
            if (this.carrierFilter) {
                filtered = filtered.filter(shipment => shipment.carrier_slug === this.carrierFilter);
            }

            // Filtre par période
            if (this.periodFilter) {
                const now = new Date();
                const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                
                filtered = filtered.filter(shipment => {
                    const shipmentDate = new Date(shipment.created_at);
                    
                    switch (this.periodFilter) {
                        case 'today':
                            return shipmentDate >= today;
                        case 'yesterday':
                            const yesterday = new Date(today);
                            yesterday.setDate(yesterday.getDate() - 1);
                            return shipmentDate >= yesterday && shipmentDate < today;
                        case 'week':
                            const weekStart = new Date(today);
                            weekStart.setDate(weekStart.getDate() - weekStart.getDay());
                            return shipmentDate >= weekStart;
                        case 'month':
                            const monthStart = new Date(today.getFullYear(), today.getMonth(), 1);
                            return shipmentDate >= monthStart;
                        default:
                            return true;
                    }
                });
            }

            this.filteredShipments = filtered;
        },

        refreshShipments() {
            this.loadShipments();
        },

        async refreshAllTracking() {
            const trackableShipments = this.shipments.filter(s => s.pos_barcode && ['in_transit', 'picked_up_by_carrier'].includes(s.status));
            
            if (trackableShipments.length === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'Aucune expédition à suivre',
                    text: 'Aucune expédition n\'a de numéro de suivi actif'
                });
                return;
            }

            this.refreshing = true;

            try {
                let updated = 0;
                for (const shipment of trackableShipments) {
                    try {
                        await axios.post(`/admin/delivery/shipments/${shipment.id}/track`);
                        updated++;
                    } catch (error) {
                        console.error(`Erreur suivi ${shipment.id}:`, error);
                    }
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Suivi actualisé !',
                    text: `${updated}/${trackableShipments.length} expédition(s) mise(s) à jour`,
                    showConfirmButton: false,
                    timer: 2000
                });

                this.loadShipments(false);
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Impossible d\'actualiser le suivi'
                });
            } finally {
                this.refreshing = false;
            }
        },

        // Méthodes de sélection
        isSelected(shipmentId) {
            return this.selectedShipments.includes(shipmentId);
        },

        toggleSelection(shipmentId) {
            if (this.isSelected(shipmentId)) {
                this.selectedShipments = this.selectedShipments.filter(id => id !== shipmentId);
            } else {
                this.selectedShipments.push(shipmentId);
            }
        },

        isAllSelected() {
            return this.filteredShipments.length > 0 && 
                   this.filteredShipments.every(shipment => this.isSelected(shipment.id));
        },

        toggleAllSelection(checked) {
            if (checked) {
                this.selectedShipments = [...new Set([...this.selectedShipments, ...this.filteredShipments.map(s => s.id)])];
            } else {
                const idsToRemove = this.filteredShipments.map(s => s.id);
                this.selectedShipments = this.selectedShipments.filter(id => !idsToRemove.includes(id));
            }
        },

        // Actions sur les expéditions
        viewShipment(shipmentId) {
            this.selectedShipment = this.shipments.find(s => s.id === shipmentId);
            const modal = new bootstrap.Modal(document.getElementById('shipmentDetailsModal'));
            modal.show();
        },

        closeShipmentModal() {
            const modal = bootstrap.Modal.getInstance(document.getElementById('shipmentDetailsModal'));
            if (modal) modal.hide();
        },

        async trackShipment(shipmentId) {
            try {
                const response = await axios.post(`/admin/delivery/shipments/${shipmentId}/track`);
                
                if (response.data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Suivi mis à jour !',
                        text: response.data.message,
                        showConfirmButton: false,
                        timer: 2000
                    });
                    
                    this.loadShipments(false);
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Impossible de mettre à jour le suivi'
                });
            }
        },

        async markAsDelivered(shipmentId) {
            try {
                const response = await axios.post(`/admin/delivery/shipments/${shipmentId}/mark-delivered`);
                
                if (response.data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Expédition marquée comme livrée !',
                        text: response.data.message,
                        showConfirmButton: false,
                        timer: 2000
                    });
                    
                    this.loadShipments(false);
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Impossible de marquer comme livrée'
                });
            }
        },

        contactCustomer(phone) {
            if (phone) {
                window.open(`tel:${phone}`);
            }
        },

        // Actions groupées
        async trackSelected() {
            if (this.selectedShipments.length === 0) return;

            const trackableSelected = this.selectedShipments.filter(id => {
                const shipment = this.shipments.find(s => s.id === id);
                return shipment && shipment.pos_barcode && ['in_transit', 'picked_up_by_carrier'].includes(shipment.status);
            });

            if (trackableSelected.length === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'Aucune expédition trackable',
                    text: 'Aucune expédition sélectionnée n\'a de numéro de suivi actif'
                });
                return;
            }

            try {
                let updated = 0;
                for (const id of trackableSelected) {
                    try {
                        await axios.post(`/admin/delivery/shipments/${id}/track`);
                        updated++;
                    } catch (error) {
                        console.error(`Erreur suivi ${id}:`, error);
                    }
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Suivi actualisé !',
                    text: `${updated}/${trackableSelected.length} expédition(s) mise(s) à jour`
                });

                this.loadShipments(false);
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Impossible d\'actualiser le suivi'
                });
            }
        },

        async markSelectedAsDelivered() {
            if (this.selectedShipments.length === 0) return;

            const deliverableSelected = this.selectedShipments.filter(id => {
                const shipment = this.shipments.find(s => s.id === id);
                return shipment && ['in_transit', 'picked_up_by_carrier'].includes(shipment.status);
            });

            if (deliverableSelected.length === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'Aucune expédition livrable',
                    text: 'Aucune expédition sélectionnée ne peut être marquée comme livrée'
                });
                return;
            }

            const result = await Swal.fire({
                title: `Marquer ${deliverableSelected.length} expédition(s) comme livrée(s) ?`,
                text: 'Cette action mettra à jour le statut des expéditions',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Marquer livrées',
                cancelButtonText: 'Annuler'
            });

            if (result.isConfirmed) {
                try {
                    await axios.post('/admin/delivery/shipments/bulk-delivered', {
                        shipment_ids: deliverableSelected
                    });

                    Swal.fire({
                        icon: 'success',
                        title: 'Expéditions marquées comme livrées !',
                        showConfirmButton: false,
                        timer: 2000
                    });

                    this.selectedShipments = [];
                    this.loadShipments(false);
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: 'Impossible de marquer les expéditions comme livrées'
                    });
                }
            }
        },

        // Méthodes utilitaires
        getStatusIndicatorClass(status) {
            return status || 'created';
        },

        getStatusBadgeClass(status) {
            const classes = {
                'created': 'bg-secondary',
                'validated': 'bg-primary',
                'picked_up_by_carrier': 'bg-warning',
                'in_transit': 'bg-info',
                'delivered': 'bg-success',
                'cancelled': 'bg-secondary',
                'in_return': 'bg-warning',
                'anomaly': 'bg-danger'
            };
            return classes[status] || 'bg-secondary';
        },

        getStatusIcon(status) {
            const icons = {
                'created': 'fas fa-plus',
                'validated': 'fas fa-check',
                'picked_up_by_carrier': 'fas fa-truck-pickup',
                'in_transit': 'fas fa-truck-moving',
                'delivered': 'fas fa-check-circle',
                'cancelled': 'fas fa-times',
                'in_return': 'fas fa-undo',
                'anomaly': 'fas fa-exclamation-triangle'
            };
            return icons[status] || 'fas fa-question';
        },

        getStatusLabel(status) {
            const labels = {
                'created': 'Créée',
                'validated': 'Validée',
                'picked_up_by_carrier': 'Récupérée',
                'in_transit': 'En Transit',
                'delivered': 'Livrée',
                'cancelled': 'Annulée',
                'in_return': 'En Retour',
                'anomaly': 'Anomalie'
            };
            return labels[status] || 'Inconnu';
        },

        getCarrierIcon(carrierSlug) {
            const icons = {
                'jax_delivery': 'fas fa-truck',
                'mes_colis': 'fas fa-shipping-fast'
            };
            return icons[carrierSlug] || 'fas fa-truck';
        },

        getCarrierName(carrierSlug) {
            const names = {
                'jax_delivery': 'JAX Delivery',
                'mes_colis': 'Mes Colis Express'
            };
            return names[carrierSlug] || 'Inconnu';
        },

        getCarrierBgClass(carrierSlug) {
            const classes = {
                'jax_delivery': 'bg-primary bg-opacity-10',
                'mes_colis': 'bg-success bg-opacity-10'
            };
            return classes[carrierSlug] || 'bg-secondary bg-opacity-10';
        },

        getCarrierTextClass(carrierSlug) {
            const classes = {
                'jax_delivery': 'text-primary',
                'mes_colis': 'text-success'
            };
            return classes[carrierSlug] || 'text-secondary';
        },

        getTimelineMarkerClass(status) {
            if (status === 'delivered') return 'delivered';
            return '';
        },

        getTimeSince(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diffInMinutes = Math.floor((now - date) / (1000 * 60));
            
            if (diffInMinutes < 1) return 'À l\'instant';
            if (diffInMinutes < 60) return `${diffInMinutes}min`;
            if (diffInMinutes < 1440) return `${Math.floor(diffInMinutes / 60)}h`;
            if (diffInMinutes < 10080) return `${Math.floor(diffInMinutes / 1440)}j`;
            
            return date.toLocaleDateString('fr-FR');
        },

        formatDateTime(dateString) {
            return new Date(dateString).toLocaleString('fr-FR');
        },

        // Pagination
        previousPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
            }
        },

        nextPage() {
            this.currentPage++;
        },

        // Export
        exportShipments(format) {
            Swal.fire({
                icon: 'info',
                title: 'Export en cours',
                text: `L'export ${format.toUpperCase()} sera bientôt disponible`
            });
        }
    }));
});
</script>
@endpush