@extends('layouts.admin')

@section('title', 'Gestion des Livraisons')

@section('content')
<div class="container-fluid">
    <h1>🚛 Gestion des Livraisons Multi-Transporteurs</h1>
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('error') }}
        </div>
    @endif

    {{-- Grille des transporteurs supportés --}}
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-truck"></i> Transporteurs Supportés</h3>
                </div>
                <div class="card-body">
                    <div class="carriers-grid">
                        @foreach($supportedCarriers as $slug => $carrier)
                            @php
                                $config = $userConfigurations->get($slug);
                                $statusClass = $config && $config->is_active ? 'connected' : 'not-configured';
                                $statusLabel = $config && $config->is_active ? 'Connecté' : 'Non configuré';
                            @endphp
                            
                            <div class="carrier-card {{ $statusClass }}" 
                                 onclick="window.location.href='{{ route('admin.delivery.configuration') }}'">
                                
                                {{-- Logo du transporteur --}}
                                <div class="carrier-logo">
                                    @if(file_exists(public_path("images/carriers/{$carrier['logo']}")))
                                        <img src="{{ asset("images/carriers/{$carrier['logo']}") }}" 
                                             alt="{{ $carrier['display_name'] }}"
                                             class="img-fluid">
                                    @else
                                        <div class="logo-placeholder">
                                            <i class="fas fa-truck fa-3x text-primary"></i>
                                        </div>
                                    @endif
                                </div>
                                
                                {{-- Nom et description --}}
                                <div class="carrier-info">
                                    <h5>{{ $carrier['display_name'] }}</h5>
                                    <p class="text-muted small">{{ $carrier['description'] }}</p>
                                </div>
                                
                                {{-- Statut de connexion --}}
                                <div class="carrier-status">
                                    <span class="status-badge status-{{ $statusClass }}">
                                        <i class="fas fa-{{ $statusClass === 'connected' ? 'check-circle' : 'exclamation-triangle' }}"></i>
                                        {{ $statusLabel }}
                                    </span>
                                </div>
                                
                                {{-- Fonctionnalités supportées --}}
                                <div class="carrier-capabilities mb-3">
                                    @if($carrier['supports_pickup_address'])
                                        <span class="badge badge-success mr-1">
                                            <i class="fas fa-map-marker-alt"></i> Adresses
                                        </span>
                                    @endif
                                    @if($carrier['supports_bl_templates'])
                                        <span class="badge badge-info mr-1">
                                            <i class="fas fa-file-pdf"></i> Templates
                                        </span>
                                    @endif
                                    @if($carrier['supports_mass_labels'])
                                        <span class="badge badge-warning mr-1">
                                            <i class="fas fa-tags"></i> Étiquettes
                                        </span>
                                    @endif
                                    @if(!($carrier['supports_pickup_address']) && 
                                        !($carrier['supports_bl_templates']) && 
                                        !($carrier['supports_mass_labels']))
                                        <span class="badge badge-secondary">Configuration simplifiée</span>
                                    @endif
                                </div>
                                
                                {{-- Statistiques si configuré --}}
                                @if($config)
                                    <div class="carrier-stats">
                                        @php
                                            $pickupsCount = $config->pickups()->count();
                                            $shipmentsCount = \App\Models\Shipment::whereHas('pickup', function($q) use ($config) {
                                                $q->where('delivery_configuration_id', $config->id);
                                            })->count();
                                        @endphp
                                        
                                        <small class="text-muted">
                                            {{ $pickupsCount }} enlèvement(s) • {{ $shipmentsCount }} expédition(s)
                                        </small>
                                    </div>
                                @endif
                                
                                {{-- Bouton de configuration --}}
                                <div class="text-center">
                                    <span class="btn {{ $config ? 'btn-outline-primary' : 'btn-primary' }} btn-sm">
                                        <i class="fas fa-cog"></i> 
                                        {{ $config ? 'Gérer' : 'Configurer' }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                        
                        {{-- Placeholder pour futurs transporteurs --}}
                        <div class="carrier-card future-carrier">
                            <div class="carrier-logo">
                                <div class="logo-placeholder">
                                    <i class="fas fa-plus fa-2x text-muted"></i>
                                </div>
                            </div>
                            <div class="carrier-info">
                                <h5 class="text-muted">Futurs Transporteurs</h5>
                                <p class="text-muted small">Fparcel, Aramex, DHL...</p>
                            </div>
                            <div class="text-center">
                                <span class="badge badge-light">Prochainement</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            {{-- Statistiques --}}
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-chart-bar"></i> Statistiques</h3>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="bg-primary text-white rounded p-2">
                                <h4 class="mb-0">{{ $globalStats['total_configs'] ?? 0 }}</h4>
                                <small>Configurations</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="bg-success text-white rounded p-2">
                                <h4 class="mb-0">{{ $globalStats['active_configs'] ?? 0 }}</h4>
                                <small>Actives</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-warning text-white rounded p-2">
                                <h4 class="mb-0">{{ $globalStats['draft_pickups'] ?? 0 }}</h4>
                                <small>Brouillons</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-info text-white rounded p-2">
                                <h4 class="mb-0">{{ $globalStats['active_shipments'] ?? 0 }}</h4>
                                <small>Expéditions</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Actions rapides --}}
            <div class="card mt-3">
                <div class="card-header">
                    <h3><i class="fas fa-rocket"></i> Actions Rapides</h3>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.delivery.preparation') }}" 
                           class="btn btn-success btn-lg">
                            <i class="fas fa-plus"></i> Nouvel Enlèvement
                            @if(($globalStats['draft_pickups'] ?? 0) > 0)
                                <span class="badge badge-light ml-2">
                                    {{ $globalStats['draft_pickups'] }}
                                </span>
                            @endif
                        </a>
                        
                        <a href="{{ route('admin.delivery.pickups') }}" 
                           class="btn btn-info btn-lg">
                            <i class="fas fa-truck"></i> Gérer Enlèvements
                        </a>
                        
                        <a href="{{ route('admin.delivery.shipments') }}" 
                           class="btn btn-primary btn-lg">
                            <i class="fas fa-box"></i> Suivi Expéditions
                            @if(($globalStats['active_shipments'] ?? 0) > 0)
                                <span class="badge badge-light ml-2">
                                    {{ $globalStats['active_shipments'] }}
                                </span>
                            @endif
                        </a>
                        
                        <a href="{{ route('admin.delivery.stats') }}" 
                           class="btn btn-secondary btn-lg">
                            <i class="fas fa-chart-line"></i> Statistiques
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.carriers-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.carrier-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    cursor: pointer;
    border: 2px solid transparent;
    text-align: center;
}

.carrier-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    border-color: #007bff;
}

.carrier-card.connected {
    border-color: #28a745;
    background: linear-gradient(135deg, #ffffff 0%, #f8fff9 100%);
}

.carrier-card.not-configured {
    border-color: #ffc107;
}

.carrier-card.future-carrier {
    border: 2px dashed #dee2e6;
    background: #f8f9fa;
    cursor: default;
}

.carrier-card.future-carrier:hover {
    transform: none;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border-color: #dee2e6;
}

.carrier-logo {
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 15px;
}

.carrier-logo img {
    max-width: 100px;
    max-height: 60px;
    object-fit: contain;
}

.logo-placeholder {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: #f1f3f4;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}

.carrier-info h5 {
    margin-bottom: 5px;
    color: #2c3e50;
}

.carrier-status {
    margin: 15px 0;
}

.status-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: bold;
}

.status-connected { 
    background: #d4edda; 
    color: #155724; 
}

.status-not-configured { 
    background: #fff3cd; 
    color: #856404; 
}

.carrier-capabilities {
    margin: 10px 0;
}

.carrier-stats {
    background: #f8f9fa;
    border-radius: 6px;
    padding: 8px;
    margin: 10px 0;
}

.d-grid {
    display: grid;
    gap: 0.5rem;
}

@media (max-width: 768px) {
    .carriers-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection