@extends('layouts.admin')

@section('title', 'Intégration WooCommerce')

@section('css')
<style>
    .connection-status {
        display: inline-block;
        width: 15px;
        height: 15px;
        border-radius: 50%;
        margin-right: 5px;
    }
    
    .status-active {
        background-color: #28a745;
    }
    
    .status-inactive {
        background-color: #dc3545;
    }
    
    .status-syncing {
        background-color: #ffc107;
        animation: pulse 1.5s infinite;
    }
    
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }
    
    .card-header-tabs {
        margin-bottom: -0.75rem;
    }
    
    .sync-info {
        font-size: 0.9rem;
        margin-top: 10px;
        color: #6c757d;
    }
</style>
@endsection

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Intégration WooCommerce</h1>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Configuration WooCommerce</h6>
                <div>
                    @if($settings->id)
                        <span class="mr-3">
                            <span class="connection-status {{ $settings->is_active ? 'status-active' : 'status-inactive' }}"></span>
                            {{ $settings->is_active ? 'Actif' : 'Inactif' }}
                        </span>
                        
                        @if($settings->is_active)
                            <a href="{{ route('admin.woocommerce.sync') }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-sync-alt mr-1"></i> Synchroniser maintenant
                            </a>
                        @endif
                    @endif
                </div>
            </div>
            <div class="card-body">
                @if($settings->sync_status === 'syncing')
                    <div class="alert alert-warning">
                        <i class="fas fa-sync-alt fa-spin mr-2"></i> Synchronisation en cours...
                    </div>
                @elseif($settings->sync_status === 'error')
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle mr-2"></i> Erreur de synchronisation: {{ $settings->sync_error }}
                    </div>
                @endif
                
                <form action="{{ route('admin.woocommerce.store') }}" method="POST">
                    @csrf
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="store_url">URL de la boutique WooCommerce</label>
                                <input type="url" class="form-control @error('store_url') is-invalid @enderror" id="store_url" name="store_url" value="{{ old('store_url', $settings->store_url) }}" required placeholder="https://votreboutique.com">
                                @error('store_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">L'URL complète de votre boutique WooCommerce (ex: https://votreboutique.com)</small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" {{ old('is_active', $settings->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    <strong>Activer l'intégration</strong> (la synchronisation automatique sera active)
                                </label>
                            </div>
                            
                            @if($settings->last_sync_at)
                                <div class="sync-info">
                                    <i class="fas fa-info-circle mr-1"></i> Dernière synchronisation: {{ $settings->last_sync_at->format('d/m/Y H:i') }}
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="consumer_key">Clé API (Consumer Key)</label>
                                <input type="text" class="form-control @error('consumer_key') is-invalid @enderror" id="consumer_key" name="consumer_key" value="{{ old('consumer_key', $settings->consumer_key) }}" required>
                                @error('consumer_key')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="consumer_secret">Secret API (Consumer Secret)</label>
                                <input type="text" class="form-control @error('consumer_secret') is-invalid @enderror" id="consumer_secret" name="consumer_secret" value="{{ old('consumer_secret', $settings->consumer_secret) }}" required>
                                @error('consumer_secret')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle mr-1"></i> Comment obtenir vos clés API WooCommerce</h6>
                        <ol class="mb-0">
                            <li>Connectez-vous à l'administration WordPress</li>
                            <li>Allez dans WooCommerce > Paramètres > Avancé > REST API</li>
                            <li>Cliquez sur "Ajouter une clé"</li>
                            <li>Entrez une description (ex: "Order Manager")</li>
                            <li>Sélectionnez "Lecture/Écriture" pour les droits</li>
                            <li>Cliquez sur "Générer une clé API"</li>
                            <li>Copiez la "Clé client" et le "Secret client" dans les champs ci-dessus</li>
                        </ol>
                    </div>
                    
                    <h5 class="mb-3 mt-4">Paramètres d'importation</h5>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="default_status">Statut par défaut des commandes importées</label>
                                <select class="form-control" id="default_status" name="default_status" required>
                                    <option value="nouvelle" {{ old('default_status', $settings->default_status) == 'nouvelle' ? 'selected' : '' }}>Nouvelle</option>
                                    <option value="confirmée" {{ old('default_status', $settings->default_status) == 'confirmée' ? 'selected' : '' }}>Confirmée</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="default_priority">Priorité par défaut</label>
                                <select class="form-control" id="default_priority" name="default_priority" required>
                                    <option value="normale" {{ old('default_priority', $settings->default_priority) == 'normale' ? 'selected' : '' }}>Normale</option>
                                    <option value="urgente" {{ old('default_priority', $settings->default_priority) == 'urgente' ? 'selected' : '' }}>Urgente</option>
                                    <option value="vip" {{ old('default_priority', $settings->default_priority) == 'vip' ? 'selected' : '' }}>VIP</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="default_governorate_id">Gouvernorat par défaut</label>
                                <select class="form-control" id="default_governorate_id" name="default_governorate_id">
                                    <option value="">-- Sélectionner --</option>
                                    @foreach($regions as $region)
                                        <option value="{{ $region->id }}" {{ old('default_governorate_id', $settings->default_governorate_id) == $region->id ? 'selected' : '' }}>{{ $region->name }}</option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Utilisé si le gouvernorat ne peut pas être déterminé depuis WooCommerce</small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="default_city_id">Ville par défaut</label>
                                <select class="form-control" id="default_city_id" name="default_city_id">
                                    <option value="">-- Sélectionner d'abord un gouvernorat --</option>
                                    @if($settings->defaultCity)
                                        <option value="{{ $settings->defaultCity->id }}" selected>{{ $settings->defaultCity->name }}</option>
                                    @endif
                                </select>
                                <small class="form-text text-muted">Utilisé si la ville ne peut pas être déterminée depuis WooCommerce</small>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Enregistrer les paramètres
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const governorateSelect = document.getElementById('default_governorate_id');
        const citySelect = document.getElementById('default_city_id');
        
        governorateSelect.addEventListener('change', function() {
            const regionId = this.value;
            
            // Réinitialiser le select des villes
            citySelect.innerHTML = '<option value="">-- Sélectionner une ville --</option>';
            
            if (regionId) {
                // Charger les villes via AJAX
                fetch(`/admin/get-cities?region_id=${regionId}`)
                    .then(response => response.json())
                    .then(cities => {
                        cities.forEach(city => {
                            const option = document.createElement('option');
                            option.value = city.id;
                            option.textContent = city.name;
                            citySelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Erreur lors du chargement des villes:', error));
            }
        });
    });
</script>
@endsection