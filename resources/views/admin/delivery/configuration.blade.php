@extends('layouts.admin')

@section('title', 'Configuration Livraison')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
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
                    <button type="button" class="btn btn-outline-primary" onclick="refreshAllConfigs()">
                        <i class="fas fa-sync-alt me-2"></i>Actualiser
                    </button>
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
                            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                                <i class="fas fa-plus me-2"></i>Ajouter une adresse
                            </button>
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
                                                            <strong>{{ $config->carrier_display_name }}</strong>
                                                        </div>
                                                    </td>
                                                    <td>{{ $config->integration_name }}</td>
                                                    <td>
                                                        <span class="badge {{ $config->environment === 'prod' ? 'bg-success' : 'bg-warning' }}">
                                                            {{ strtoupper($config->environment) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge {{ $config->status_badge_class }}">
                                                            {{ $config->status_label }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if($config->hasValidToken())
                                                            <span class="badge bg-success">
                                                                <i class="fas fa-check me-1"></i>Valide
                                                            </span>
                                                            <br>
                                                            <small class="text-muted">
                                                                Expire: {{ $config->expires_at->format('d/m/Y H:i') }}
                                                            </small>
                                                        @else
                                                            <span class="badge bg-danger">
                                                                <i class="fas fa-times me-1"></i>Expiré
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
                                                            <button type="button" class="btn btn-outline-success" 
                                                                    onclick="refreshToken({{ $config->id }})" 
                                                                    title="Rafraîchir le token">
                                                                <i class="fas fa-sync-alt"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-outline-secondary" 
                                                                    onclick="editConfig({{ $config->id }})" 
                                                                    title="Modifier">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
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
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                                <i class="fas fa-plus me-2"></i>Ajouter
                            </button>
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
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('admin.delivery.pickup-addresses.edit', $address) }}">
                                                                <i class="fas fa-edit me-2"></i>Modifier
                                                            </a>
                                                        </li>
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
                                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                                        <i class="fas fa-plus me-2"></i>Ajouter
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Liens rapides -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-external-link-alt me-2"></i>
                                Liens rapides
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('admin.delivery.preparation') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-boxes me-2"></i>Préparation d'enlèvement
                                </a>
                                <a href="{{ route('admin.delivery.pickups') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-warehouse me-2"></i>Gestion des enlèvements
                                </a>
                                <a href="{{ route('admin.delivery.shipments') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-shipping-fast me-2"></i>Suivi des expéditions
                                </a>
                                <a href="{{ route('admin.delivery.bl-templates.index') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-file-pdf me-2"></i>Templates BL
                                </a>
                                <a href="{{ route('admin.delivery.stats') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-chart-bar me-2"></i>Statistiques
                                </a>
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
            <form id="addConfigForm" onsubmit="submitConfigForm(event)">
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
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="integration_name" class="form-label">Nom d'intégration *</label>
                                <input type="text" class="form-control" id="integration_name" name="integration_name" 
                                       placeholder="Ex: Fparcel - Magasin principal" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="username" class="form-label">Nom d'utilisateur *</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label">Mot de passe *</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="environment" class="form-label">Environnement *</label>
                                <select class="form-select" id="environment" name="environment" required>
                                    <option value="test">Test</option>
                                    <option value="prod">Production</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        La connexion sera testée automatiquement lors de la création.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Créer et tester
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
            <form id="addAddressForm" onsubmit="submitAddressForm(event)">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="address_name" class="form-label">Nom de l'adresse *</label>
                        <input type="text" class="form-control" id="address_name" name="name" 
                               placeholder="Ex: Entrepôt principal" required>
                    </div>
                    <div class="mb-3">
                        <label for="contact_name" class="form-label">Nom du contact *</label>
                        <input type="text" class="form-control" id="contact_name" name="contact_name" 
                               placeholder="Nom de la personne de contact" required>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Adresse *</label>
                        <textarea class="form-control" id="address" name="address" rows="3" 
                                  placeholder="Adresse complète" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="postal_code" class="form-label">Code postal</label>
                                <input type="text" class="form-control" id="postal_code" name="postal_code">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="city" class="form-label">Ville</label>
                                <input type="text" class="form-control" id="city" name="city">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone" class="form-label">Téléphone *</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email">
                            </div>
                        </div>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_default" name="is_default">
                        <label class="form-check-label" for="is_default">
                            Définir comme adresse par défaut
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Créer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Initialisation
    console.log('Page de configuration chargée');
});

// Configuration des transporteurs
function submitConfigForm(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Test en cours...';
    submitBtn.disabled = true;
    
    fetch('{{ route("admin.delivery.configuration.store") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('success', data.message);
            bootstrap.Modal.getInstance(document.getElementById('addConfigModal')).hide();
            setTimeout(() => location.reload(), 1500);
        } else {
            if (data.errors) {
                Object.keys(data.errors).forEach(key => {
                    const input = form.querySelector(`[name="${key}"]`);
                    if (input) {
                        input.classList.add('is-invalid');
                        let feedback = input.parentNode.querySelector('.invalid-feedback');
                        if (!feedback) {
                            feedback = document.createElement('div');
                            feedback.className = 'invalid-feedback';
                            input.parentNode.appendChild(feedback);
                        }
                        feedback.textContent = data.errors[key][0];
                    }
                });
            } else {
                showNotification('error', data.message || 'Erreur lors de la création');
            }
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('error', 'Erreur lors de la création de la configuration');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

// Adresses d'enlèvement
function submitAddressForm(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Création...';
    submitBtn.disabled = true;
    
    fetch('{{ route("admin.delivery.pickup-addresses.store") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('success', data.message);
            bootstrap.Modal.getInstance(document.getElementById('addAddressModal')).hide();
            setTimeout(() => location.reload(), 1500);
        } else {
            if (data.errors) {
                Object.keys(data.errors).forEach(key => {
                    const input = form.querySelector(`[name="${key}"]`);
                    if (input) {
                        input.classList.add('is-invalid');
                        let feedback = input.parentNode.querySelector('.invalid-feedback');
                        if (!feedback) {
                            feedback = document.createElement('div');
                            feedback.className = 'invalid-feedback';
                            input.parentNode.appendChild(feedback);
                        }
                        feedback.textContent = data.errors[key][0];
                    }
                });
            } else {
                showNotification('error', data.message || 'Erreur lors de la création');
            }
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('error', 'Erreur lors de la création de l\'adresse');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

// Actions sur les configurations
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

// Actions sur les adresses
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

function refreshAllConfigs() {
    showNotification('info', 'Actualisation en cours...');
    setTimeout(() => location.reload(), 500);
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

// Nettoyage des erreurs de validation lors de la saisie
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('is-invalid')) {
        e.target.classList.remove('is-invalid');
        const feedback = e.target.parentNode.querySelector('.invalid-feedback');
        if (feedback) {
            feedback.remove();
        }
    }
});
</script>
@endsection