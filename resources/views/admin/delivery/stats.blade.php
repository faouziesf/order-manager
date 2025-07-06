@extends('layouts.admin')

@section('title', 'Statistiques Livraison')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="h3 mb-0">Statistiques de Livraison</h1>
            <p class="text-muted">Aperçu de vos performances Jax Delivery</p>
        </div>
    </div>

    <!-- Statistiques principales -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Enlèvements
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_pickups'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-warehouse fa-2x text-gray-300"></i>
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
                                En Brouillon
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['draft_pickups'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-edit fa-2x text-gray-300"></i>
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
                                Validés
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['validated_pickups'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                                Total Expéditions
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_shipments'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shipping-fast fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques secondaires -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Expéditions Actives
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active_shipments'] }}</div>
                            <div class="text-xs text-gray-500">En transit ou en cours</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-truck fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Livrées ce Mois
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['delivered_this_month'] }}</div>
                            <div class="text-xs text-gray-500">{{ date('F Y') }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-double fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-12 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Taux de Livraison
                            </div>
                            @php
                                $deliveryRate = $stats['total_shipments'] > 0 
                                    ? round(($stats['delivered_this_month'] / $stats['total_shipments']) * 100, 1)
                                    : 0;
                            @endphp
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $deliveryRate }}%</div>
                            <div class="text-xs text-gray-500">Ce mois</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-percentage fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques et détails -->
    <div class="row">
        <!-- Activité récente -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-clock"></i> Activité Récente
                    </h6>
                </div>
                <div class="card-body">
                    @php
                        $recentPickups = \App\Models\Pickup::where('admin_id', auth('admin')->id())
                            ->with('deliveryConfiguration')
                            ->orderBy('created_at', 'desc')
                            ->limit(5)
                            ->get();
                    @endphp
                    
                    @if($recentPickups->count() > 0)
                        <div class="list-group">
                            @foreach($recentPickups as $pickup)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">Enlèvement #{{ $pickup->id }}</h6>
                                    <p class="mb-1 text-muted">
                                        {{ $pickup->shipment_count }} expédition(s) • 
                                        {{ $pickup->deliveryConfiguration->integration_name ?? 'N/A' }}
                                    </p>
                                    <small class="text-muted">{{ $pickup->created_at->diffForHumans() }}</small>
                                </div>
                                <span class="badge {{ $pickup->status_badge_class }}">
                                    {{ $pickup->status_label }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-clock fa-2x text-gray-300 mb-3"></i>
                            <p class="text-muted">Aucune activité récente</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Répartition par statut -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie"></i> Répartition des Expéditions
                    </h6>
                </div>
                <div class="card-body">
                    @php
                        $shipmentStats = [
                            'created' => \App\Models\Shipment::where('admin_id', auth('admin')->id())->where('status', 'created')->count(),
                            'validated' => \App\Models\Shipment::where('admin_id', auth('admin')->id())->where('status', 'validated')->count(),
                            'in_transit' => \App\Models\Shipment::where('admin_id', auth('admin')->id())->where('status', 'in_transit')->count(),
                            'delivered' => \App\Models\Shipment::where('admin_id', auth('admin')->id())->where('status', 'delivered')->count(),
                            'problem' => \App\Models\Shipment::where('admin_id', auth('admin')->id())->whereIn('status', ['cancelled', 'anomaly', 'in_return'])->count(),
                        ];
                        $total = array_sum($shipmentStats);
                    @endphp

                    @if($total > 0)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Créées</span>
                                <span class="badge badge-secondary">{{ $shipmentStats['created'] }}</span>
                            </div>
                            <div class="progress mb-3" style="height: 10px;">
                                <div class="progress-bar bg-secondary" 
                                     style="width: {{ round(($shipmentStats['created'] / $total) * 100, 1) }}%"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Validées</span>
                                <span class="badge badge-primary">{{ $shipmentStats['validated'] }}</span>
                            </div>
                            <div class="progress mb-3" style="height: 10px;">
                                <div class="progress-bar bg-primary" 
                                     style="width: {{ round(($shipmentStats['validated'] / $total) * 100, 1) }}%"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>En Transit</span>
                                <span class="badge badge-warning">{{ $shipmentStats['in_transit'] }}</span>
                            </div>
                            <div class="progress mb-3" style="height: 10px;">
                                <div class="progress-bar bg-warning" 
                                     style="width: {{ round(($shipmentStats['in_transit'] / $total) * 100, 1) }}%"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Livrées</span>
                                <span class="badge badge-success">{{ $shipmentStats['delivered'] }}</span>
                            </div>
                            <div class="progress mb-3" style="height: 10px;">
                                <div class="progress-bar bg-success" 
                                     style="width: {{ round(($shipmentStats['delivered'] / $total) * 100, 1) }}%"></div>
                            </div>
                        </div>

                        @if($shipmentStats['problem'] > 0)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Problèmes</span>
                                <span class="badge badge-danger">{{ $shipmentStats['problem'] }}</span>
                            </div>
                            <div class="progress mb-3" style="height: 10px;">
                                <div class="progress-bar bg-danger" 
                                     style="width: {{ round(($shipmentStats['problem'] / $total) * 100, 1) }}%"></div>
                            </div>
                        </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-chart-pie fa-2x text-gray-300 mb-3"></i>
                            <p class="text-muted">Aucune expédition à analyser</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt"></i> Actions Rapides
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <a href="{{ route('admin.delivery.preparation') }}" class="btn btn-primary btn-block">
                                <i class="fas fa-plus fa-2x mb-2"></i>
                                <br>Nouvel Enlèvement
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('admin.delivery.pickups') }}" class="btn btn-info btn-block">
                                <i class="fas fa-warehouse fa-2x mb-2"></i>
                                <br>Voir les Enlèvements
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('admin.delivery.shipments') }}" class="btn btn-success btn-block">
                                <i class="fas fa-shipping-fast fa-2x mb-2"></i>
                                <br>Voir les Expéditions
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('admin.delivery.configuration') }}" class="btn btn-secondary btn-block">
                                <i class="fas fa-cog fa-2x mb-2"></i>
                                <br>Configuration
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection