@extends('layouts.admin')

@section('title', 'Configuration Livraison')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Messages Flash -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- En-tête -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-cog text-primary me-2"></i>
                        Configuration Livraison
                    </h1>
                    <p class="text-muted mb-0">Gérez vos transporteurs et adresses d'enlèvement</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.delivery.configuration') }}" class="btn btn-outline-primary">
                        <i class="fas fa-sync-alt me-2"></i>Actualiser
                    </a>
                </div>
            </div>

            <!-- Alertes de configuration -->
            @if($configurations->isEmpty())
                <div class="alert alert-warning">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle me-3 fa-2x"></i>
                        <div>
                            <h5 class="mb-1">Aucun transporteur configuré</h5>
                            <p class="mb-2">Pour commencer à utiliser le système de livraison, vous devez configurer au moins un transporteur.</p>
                            <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#addConfigModal">
                                <i class="fas fa-plus me-2"></i>Ajouter un transporteur
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            @if($pickupAddresses->isEmpty() && $configurations->isNotEmpty())
                <div class="alert alert-info">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-map-marker-alt me-3 fa-2x"></i>
                        <div>
                            <h5 class="mb-1">Aucune adresse d'enlèvement</h5>
                            <p class="mb-2">Ajoutez une adresse d'enlèvement pour que les transporteurs puissent récupérer vos colis.</p>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                                    <i class="fas fa-plus me-2"></i>Ajouter manuellement
                                </button>
                                @if($configurations->where('is_active', true)->isNotEmpty())
                                    <button type="button" class="btn btn-success btn-sm" onclick="importFromFparcel()">
                                        <i class="fas fa-download me-2"></i>Importer depuis Fparcel
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="row">
                <!-- Configurations des transporteurs -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-truck me-2"></i>
                                Transporteurs configurés
                            </h5>
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addConfigModal">
                                <i class="fas fa-plus me-2"></i>Ajouter
                            </button>
                        </div>
                        <div class="card-body">
                            @if($configurations->isNotEmpty())
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Transporteur</th>
                                                <th>Nom d'intégration</th>
                                                <th>Environnement</th>
                                                <th>Statut</th>
                                                <th>Token</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($configurations as $config)
                                                <tr id="config-row-{{ $config->id }}">
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <i class="fas fa-truck text-primary me-2"></i>
                                                            <strong>Fparcel Tunisia</strong>
                                                        </div>
                                                    </td>
                                                    <td>{{ $config->integration_name }}</td>
                                                    <td>
                                                        <span class="badge {{ $config->environment === 'prod' ? 'bg-success' : 'bg-warning' }}">
                                                            {{ strtoupper($config->environment) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge {{ $config->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                            {{ $config->is_active ? 'Actif' : 'Inactif' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if($config->token && $config->expires_at && $config->expires_at->isFuture())
                                                            <span class="badge bg-success">
                                                                <i class="fas fa-check me-1"></i>Valide
                                                            </span>
                                                            <br>
                                                            <small class="text-muted">
                                                                Expire: {{ $config->expires_at->format('d/m/Y H:i') }}
                                                            </small>
                                                        @else
                                                            <span class="badge bg-warning">
                                                                <i class="fas fa-clock me-1"></i>Non testé
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <button type="button" class="btn btn-outline-primary" 
                                                                    onclick="testConnection({{ $config->id }})" 
                                                                    title="Tester la connexion">
                                                                <i class="fas fa-plug"></i>
                                                            </button>
                                                            @if($config->token)
                                                                <button type="button" class="btn btn-outline-success" 
                                                                        onclick="refreshToken({{ $config->id }})" 
                                                                        title="Rafraîchir le token">
                                                                    <i class="fas fa-sync-alt"></i>
                                                                </button>
                                                            @endif
                                                            <button type="button" class="btn btn-outline-{{ $config->is_active ? 'warning' : 'success' }}" 
                                                                    onclick="toggleConfig({{ $config->id }})" 
                                                                    title="{{ $config->is_active ? 'Désactiver' : 'Activer' }}">
                                                                <i class="fas fa-{{ $config->is_active ? 'pause' : 'play' }}"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-outline-danger" 
                                                                    onclick="deleteConfig({{ $config->id }})" 
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
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-truck fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Aucun transporteur configuré</h5>
                                    <p class="text-muted">Ajoutez votre premier transporteur pour commencer</p>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addConfigModal">
                                        <i class="fas fa-plus me-2"></i>Ajouter un transporteur
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Adresses d'enlèvement -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-map-marker-alt me-2"></i>
                                Adresses d'enlèvement
                            </h5>
                            <div class="dropdown">
                                <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-plus me-2"></i>Ajouter
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                                            <i class="fas fa-edit me-2"></i>Saisir manuellement
                                        </button>
                                    </li>
                                    @if($configurations->where('is_active', true)->isNotEmpty())
                                        <li>
                                            <button class="dropdown-item" onclick="importFromFparcel()">
                                                <i class="fas fa-download me-2"></i>Importer depuis Fparcel
                                            </button>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                        <div class="card-body">
                            @if($pickupAddresses->isNotEmpty())
                                @foreach($pickupAddresses as $address)
                                    <div class="card mb-3" id="address-card-{{ $address->id }}">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="mb-1">
                                                    {{ $address->name }}
                                                    @if($address->is_default)
                                                        <span class="badge bg-primary ms-1">Par défaut</span>
                                                    @endif
                                                </h6>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        @if(!$address->is_default)
                                                            <li>
                                                                <button class="dropdown-item" onclick="setDefaultAddress({{ $address->id }})">
                                                                    <i class="fas fa-star me-2"></i>Définir par défaut
                                                                </button>
                                                            </li>
                                                        @endif
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <button class="dropdown-item text-danger" onclick="deleteAddress({{ $address->id }})">
                                                                <i class="fas fa-trash me-2"></i>Supprimer
                                                            </button>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <p class="text-muted mb-1 small">
                                                <strong>{{ $address->contact_name }}</strong>
                                            </p>
                                            <p class="text-muted mb-1 small">{{ $address->address }}</p>
                                            @if($address->city)
                                                <p class="text-muted mb-1 small">{{ $address->city }}</p>
                                            @endif
                                            <p class="text-muted mb-0 small">
                                                <i class="fas fa-phone me-1"></i>{{ $address->phone }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-map-marker-alt fa-2x text-muted mb-3"></i>
                                    <h6 class="text-muted">Aucune adresse configurée</h6>
                                    <div class="d-flex gap-2 justify-content-center">
                                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                                            <i class="fas fa-plus me-2"></i>Ajouter
                                        </button>
                                        @if($configurations->where('is_active', true)->isNotEmpty())
                                            <button type="button" class="btn btn-outline-success btn-sm" onclick="importFromFparcel()">
                                                <i class="fas fa-download me-2"></i>Importer
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Statistiques -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-bar me-2"></i>
                                Statistiques
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6 mb-3">
                                    <h4 class="text-primary mb-1">{{ $stats['total_configs'] }}</h4>
                                    <small class="text-muted">Configurations</small>
                                </div>
                                <div class="col-6 mb-3">
                                    <h4 class="text-success mb-1">{{ $stats['active_configs'] }}</h4>
                                    <small class="text-muted">Actives</small>
                                </div>
                                <div class="col-6">
                                    <h4 class="text-info mb-1">{{ $stats['total_addresses'] }}</h4>
                                    <small class="text-muted">Adresses</small>
                                </div>
                                <div class="col-6">
                                    <h4 class="text-warning mb-1">{{ $stats['expired_tokens'] }}</h4>
                                    <small class="text-muted">Non testés</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ajouter Configuration -->
<div class="modal fade" id="addConfigModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i>Ajouter un transporteur
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.delivery.configuration.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="carrier_slug" class="form-label">Transporteur *</label>
                                <select class="form-select" id="carrier_slug" name="carrier_slug" required>
                                    <option value="">Sélectionner un transporteur</option>
                                    @foreach($availableCarriers as $slug => $info)
                                        <option value="{{ $slug }}">{{ $info['display_name'] }}</option>
                                    @endforeach
                                </select>
                                @error('carrier_slug')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="integration_name" class="form-label">Nom d'intégration *</label>
                                <input type="text" class="form-control @error('integration_name') is-invalid @enderror" 
                                       id="integration_name" name="integration_name" 
                                       value="{{ old('integration_name') }}"
                                       placeholder="Ex: Fparcel - Magasin principal" required>
                                @error('integration_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="username" class="form-label">Nom d'utilisateur *</label>
                                <input type="text" class="form-control @error('username') is-invalid @enderror" 
                                       id="username" name="username" value="{{ old('username') }}" required>
                                @error('username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label">Mot de passe *</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       id="password" name="password" required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="environment" class="form-label">Environnement *</label>
                                <select class="form-select @error('environment') is-invalid @enderror" 
                                        id="environment" name="environment" required>
                                    <option value="test" {{ old('environment') == 'test' ? 'selected' : '' }}>Test</option>
                                    <option value="prod" {{ old('environment') == 'prod' ? 'selected' : '' }}>Production</option>
                                </select>
                                @error('environment')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        La configuration sera créée directement. Vous pourrez tester la connexion après création.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Créer la configuration
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ajouter Adresse -->
<div class="modal fade" id="addAddressModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i>Ajouter une adresse d'enlèvement
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.delivery.pickup-addresses.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="address_name" class="form-label">Nom de l'adresse *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="address_name" name="name" value="{{ old('name') }}"
                               placeholder="Ex: Entrepôt principal" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="contact_name" class="form-label">Nom du contact *</label>
                        <input type="text" class="form-control @error('contact_name') is-invalid @enderror" 
                               id="contact_name" name="contact_name" value="{{ old('contact_name') }}"
                               placeholder="Nom de la personne de contact" required>
                        @error('contact_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Adresse *</label>
                        <textarea class="form-control @error('address') is-invalid @enderror" 
                                  id="address" name="address" rows="3" 
                                  placeholder="Adresse complète" required>{{ old('address') }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="postal_code" class="form-label">Code postal</label>
                                <input type="text" class="form-control @error('postal_code') is-invalid @enderror" 
                                       id="postal_code" name="postal_code" value="{{ old('postal_code') }}">
                                @error('postal_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="city" class="form-label">Ville</label>
                                <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                       id="city" name="city" value="{{ old('city') }}">
                                @error('city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone" class="form-label">Téléphone *</label>
                                <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" name="phone" value="{{ old('phone') }}" required>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email') }}">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_default" name="is_default"
                               {{ old('is_default') ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_default">
                            Définir comme adresse par défaut
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Créer l'adresse
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Actions AJAX pour les configurations
function testConnection(configId) {
    const btn = event.target.closest('button');
    const originalHtml = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btn.disabled = true;
    
    fetch(`/admin/delivery/configuration/${configId}/test`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('success', data.message);
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification('error', data.message);
        }
    })
    .catch(error => {
        showNotification('error', 'Erreur lors du test de connexion');
    })
    .finally(() => {
        btn.innerHTML = originalHtml;
        btn.disabled = false;
    });
}

function refreshToken(configId) {
    const btn = event.target.closest('button');
    const originalHtml = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btn.disabled = true;
    
    fetch(`/admin/delivery/configuration/${configId}/refresh-token`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('success', data.message);
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification('error', data.message);
        }
    })
    .catch(error => {
        showNotification('error', 'Erreur lors du rafraîchissement du token');
    })
    .finally(() => {
        btn.innerHTML = originalHtml;
        btn.disabled = false;
    });
}

function toggleConfig(configId) {
    if (!confirm('Êtes-vous sûr de vouloir changer le statut de cette configuration ?')) {
        return;
    }
    
    fetch(`/admin/delivery/configuration/${configId}/toggle`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('success', data.message);
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification('error', data.message);
        }
    })
    .catch(error => {
        showNotification('error', 'Erreur lors du changement de statut');
    });
}

function deleteConfig(configId) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cette configuration ? Cette action est irréversible.')) {
        return;
    }
    
    fetch(`/admin/delivery/configuration/${configId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('success', data.message);
            document.getElementById(`config-row-${configId}`).remove();
        } else {
            showNotification('error', data.message);
        }
    })
    .catch(error => {
        showNotification('error', 'Erreur lors de la suppression');
    });
}

// Actions pour les adresses
function setDefaultAddress(addressId) {
    fetch(`/admin/delivery/pickup-addresses/${addressId}/set-default`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('success', data.message);
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification('error', data.message);
        }
    })
    .catch(error => {
        showNotification('error', 'Erreur lors de la mise à jour');
    });
}

function deleteAddress(addressId) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cette adresse ?')) {
        return;
    }
    
    fetch(`/admin/delivery/pickup-addresses/${addressId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('success', data.message);
            document.getElementById(`address-card-${addressId}`).remove();
        } else {
            showNotification('error', data.message);
        }
    })
    .catch(error => {
        showNotification('error', 'Erreur lors de la suppression');
    });
}

// Import depuis Fparcel
function importFromFparcel() {
    // Trouver la première configuration active avec token valide
    const activeConfigs = @json($configurations->where('is_active', true)->filter(function($config) {
        return $config->token && $config->expires_at && $config->expires_at->isFuture();
    })->values());
    
    if (activeConfigs.length === 0) {
        showNotification('error', 'Aucune configuration active avec token valide. Veuillez d\'abord tester une connexion.');
        return;
    }
    
    const configId = activeConfigs[0].id;
    
    if (!confirm('Voulez-vous importer les adresses d\'enlèvement depuis votre compte Fparcel ?')) {
        return;
    }
    
    showNotification('info', 'Import en cours depuis Fparcel...');
    
    fetch(`/admin/delivery/configuration/${configId}/import-addresses`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('success', data.message);
            setTimeout(() => location.reload(), 2000);
        } else {
            showNotification('error', data.message);
        }
    })
    .catch(error => {
        showNotification('error', 'Erreur lors de l\'import depuis Fparcel');
    });
}

// Fonction utilitaire pour les notifications
function showNotification(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : type === 'error' ? 'alert-danger' : 'alert-info';
    const icon = type === 'success' ? 'fas fa-check-circle' : type === 'error' ? 'fas fa-exclamation-circle' : 'fas fa-info-circle';

    const notification = document.createElement('div');
    notification.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="${icon} me-2"></i>
            <span>${message}</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    document.body.appendChild(notification);

    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

// Rouvrir le modal avec les erreurs s'il y en a
@if($errors->any() && old())
    $(document).ready(function() {
        @if(old('carrier_slug') || old('integration_name'))
            $('#addConfigModal').modal('show');
        @elseif(old('name') || old('contact_name'))
            $('#addAddressModal').modal('show');
        @endif
    });
@endif
</script>
@endsection