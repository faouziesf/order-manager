@extends('layouts.admin')


@section('title', 'Gestion des Livraisons')

@section('content')
<div class="container-fluid">
    <h1>🚛 Gestion des Livraisons</h1>
    
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

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-truck"></i> Transporteurs Supportés</h3>
                </div>
                <div class="card-body">
                    @if(isset($supportedCarriers) && count($supportedCarriers) > 0)
                        <div class="row">
                            @foreach($supportedCarriers as $slug => $carrier)
                                <div class="col-md-6 mb-3">
                                    <div class="card border-primary">
                                        <div class="card-header bg-primary text-white">
                                            <h5 class="mb-0">
                                                <i class="fas fa-shipping-fast mr-2"></i>
                                                {{ $carrier['display_name'] ?? 'Transporteur' }}
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            {{-- Description avec fallback --}}
                                            <p class="text-muted">
                                                {{ $carrier['description'] ?? 'Aucune description disponible' }}
                                            </p>
                                            
                                            {{-- Fonctionnalités supportées --}}
                                            <div class="mb-3">
                                                <small class="text-muted">Fonctionnalités :</small><br>
                                                @if(($carrier['supports_pickup_address'] ?? false))
                                                    <span class="badge badge-success mr-1">
                                                        <i class="fas fa-map-marker-alt"></i> Adresses
                                                    </span>
                                                @endif
                                                @if(($carrier['supports_bl_templates'] ?? false))
                                                    <span class="badge badge-info mr-1">
                                                        <i class="fas fa-file-pdf"></i> Templates
                                                    </span>
                                                @endif
                                                @if(($carrier['supports_mass_labels'] ?? false))
                                                    <span class="badge badge-warning mr-1">
                                                        <i class="fas fa-tags"></i> Étiquettes
                                                    </span>
                                                @endif
                                                @if(!($carrier['supports_pickup_address'] ?? false) && 
                                                    !($carrier['supports_bl_templates'] ?? false) && 
                                                    !($carrier['supports_mass_labels'] ?? false))
                                                    <span class="badge badge-secondary">Configuration de base</span>
                                                @endif
                                            </div>
                                            
                                            {{-- Statut de configuration --}}
                                            @php
                                                $config = $userConfigurations->get($slug);
                                                $isConfigured = $config && $config->is_active;
                                            @endphp
                                            
                                            @if($isConfigured)
                                                <div class="alert alert-success py-2">
                                                    <i class="fas fa-check-circle"></i> 
                                                    <strong>Configuré et actif</strong>
                                                </div>
                                            @else
                                                <div class="alert alert-warning py-2">
                                                    <i class="fas fa-exclamation-triangle"></i> 
                                                    <strong>Non configuré</strong>
                                                </div>
                                            @endif
                                            
                                            {{-- Bouton de configuration --}}
                                            <div class="text-center">
                                                <a href="{{ route('admin.delivery.carrier.config', $slug) }}" 
                                                   class="btn {{ $isConfigured ? 'btn-outline-primary' : 'btn-primary' }} btn-block">
                                                    <i class="fas fa-cog"></i> 
                                                    {{ $isConfigured ? 'Gérer' : 'Configurer' }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Aucun transporteur configuré</strong><br>
                            Les transporteurs supportés s'afficheront ici une fois configurés.
                        </div>
                    @endif
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
                    @if(isset($globalStats))
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
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            Statistiques non disponibles
                        </div>
                    @endif
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
                        
                        <a href="{{ route('admin.delivery.pickups.index') }}" 
                           class="btn btn-info btn-lg">
                            <i class="fas fa-truck"></i> Gérer Enlèvements
                        </a>
                        
                        <a href="{{ route('admin.delivery.shipments.index') }}" 
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

    {{-- Section debug (à supprimer en production) --}}
    @if(config('app.debug'))
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-secondary">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-bug"></i> Informations de Debug</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Variables reçues :</strong>
                                <ul class="list-unstyled">
                                    <li>✅ supportedCarriers: {{ isset($supportedCarriers) ? count($supportedCarriers) . ' éléments' : 'NON DÉFINI' }}</li>
                                    <li>✅ userConfigurations: {{ isset($userConfigurations) ? $userConfigurations->count() . ' éléments' : 'NON DÉFINI' }}</li>
                                    <li>✅ globalStats: {{ isset($globalStats) ? 'Défini' : 'NON DÉFINI' }}</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <strong>Routes testées :</strong>
                                <ul class="list-unstyled">
                                    <li>📍 Préparation: {{ route('admin.delivery.preparation') }}</li>
                                    <li>📍 Enlèvements: {{ route('admin.delivery.pickups.index') }}</li>
                                    <li>📍 Expéditions: {{ route('admin.delivery.shipments.index') }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
.d-grid {
    display: grid;
    gap: 0.5rem;
}

.card {
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

.badge {
    font-size: 0.75em;
}
</style>
@endpush