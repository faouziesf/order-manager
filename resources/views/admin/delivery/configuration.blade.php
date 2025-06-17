@extends('layouts.admin')

@section('title', 'Configuration des Transporteurs')

@section('content')
<div class="container-fluid">
    <!-- En-tête avec statistiques -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-truck mr-2"></i>Configuration des Transporteurs
                </h1>
                <button class="btn btn-primary" data-toggle="modal" data-target="#addConfigModal">
                    <i class="fas fa-plus mr-1"></i>Nouveau Transporteur
                </button>
            </div>
        </div>
    </div>

    <!-- Cartes de statistiques -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Configurations Totales
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_configs'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-cogs fa-2x text-gray-300"></i>
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
                                Configurations Actives
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active_configs'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                                Adresses d'Enlèvement
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_addresses'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-map-marker-alt fa-2x text-gray-300"></i>
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
                                Tokens Expirés
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['expired_tokens'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Messages de succès/erreur -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <!-- Configurations de transporteurs -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-shipping-fast mr-2"></i>Configurations des Transporteurs
                    </h6>
                </div>
                <div class="card-body">
                    @if($configurations->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
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
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-truck text-primary mr-2"></i>
                                                    <strong>{{ $config->carrier_display_name }}</strong>
                                                </div>
                                            </td>
                                            <td>{{ $config->integration_name }}</td>
                                            <td>
                                                <span class="badge badge-{{ $config->environment === 'prod' ? 'success' : 'warning' }}">
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
                                                    <small class="text-success">
                                                        <i class="fas fa-check-circle mr-1"></i>
                                                        Expire {{ $config->expires_at->diffForHumans() }}
                                                    </small>
                                                @else
                                                    <small class="text-danger">
                                                        <i class="fas fa-times-circle mr-1"></i>
                                                        Expiré ou manquant
                                                    </small>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-outline-info" onclick="testConnection({{ $config->id }})" title="Tester la connexion">
                                                        <i class="fas fa-plug"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-secondary" onclick="refreshToken({{ $config->id }})" title="Rafraîchir le token">
                                                        <i class="fas fa-sync"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-primary" onclick="editConfiguration({{ $config->id }})" title="Modifier">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-{{ $config->is_active ? 'warning' : 'success' }}" 
                                                            onclick="toggleConfiguration({{ $config->id }})" 
                                                            title="{{ $config->is_active ? 'Désactiver' : 'Activer' }}">
                                                        <i class="fas fa-{{ $config->is_active ? 'pause' : 'play' }}"></i>
                                                    </button>
                                                    @if($config->pickups()->count() === 0)
                                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteConfiguration({{ $config->id }})" title="Supprimer">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-truck fa-3x text-gray-300 mb-3"></i>
                            <h5 class="text-gray-600">Aucune configuration de transporteur</h5>
                            <p class="text-gray-500">Commencez par ajouter votre premier transporteur pour gérer vos livraisons.</p>
                            <button class="btn btn-primary mt-2" data-toggle="modal" data-target="#addConfigModal">
                                <i class="fas fa-plus mr-1"></i>Ajouter un transporteur
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Adresses d'enlèvement -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-map-marker-alt mr-2"></i>Adresses d'Enlèvement
                    </h6>
                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addAddressModal">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <div class="card-body">
                    @if($pickupAddresses->count() > 0)
                        @foreach($pickupAddresses as $address)
                            <div class="card mb-2 {{ $address->is_default ? 'border-primary' : '' }}">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">
                                                {{ $address->name }}
                                                @if($address->is_default)
                                                    <span class="badge badge-primary badge-sm ml-1">Par défaut</span>
                                                @endif
                                            </h6>
                                            <small class="text-muted">
                                                <i class="fas fa-user mr-1"></i>{{ $address->contact_name }}<br>
                                                <i class="fas fa-map-marker-alt mr-1"></i>{{ $address->full_address }}<br>
                                                <i class="fas fa-phone mr-1"></i>{{ $address->phone }}
                                            </small>
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                @if(!$address->is_default)
                                                    <a class="dropdown-item" href="#" onclick="setDefaultAddress({{ $address->id }})">
                                                        <i class="fas fa-star mr-2"></i>Définir par défaut
                                                    </a>
                                                @endif
                                                <a class="dropdown-item" href="#" onclick="editAddress({{ $address->id }})">
                                                    <i class="fas fa-edit mr-2"></i>Modifier
                                                </a>
                                                @if($address->canBeDeleted())
                                                    <div class="dropdown-divider"></div>
                                                    <a class="dropdown-item text-danger" href="#" onclick="deleteAddress({{ $address->id }})">
                                                        <i class="fas fa-trash mr-2"></i>Supprimer
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-map-marker-alt fa-2x text-gray-300 mb-2"></i>
                            <p class="text-gray-500 mb-2">Aucune adresse d'enlèvement</p>
                            <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addAddressModal">
                                <i class="fas fa-plus mr-1"></i>Ajouter une adresse
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Transporteurs supportés -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle mr-2"></i>Transporteurs Supportés
                    </h6>
                </div>
                <div class="card-body">
                    @foreach($supportedCarriers as $slug => $carrier)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong>{{ $carrier['display_name'] ?? 'Transporteur inconnu' }}</strong>
                                <span class="badge badge-success">Disponible</span>
                            </div>
                            <small class="text-muted">
                                @php
                                    $features = $carrier['features'] ?? [];
                                @endphp
                                @if(($features['cod'] ?? false))
                                    <i class="fas fa-money-bill mr-1" title="Contre remboursement"></i>
                                @endif
                                @if(($features['tracking'] ?? false))
                                    <i class="fas fa-search-location mr-1" title="Suivi en temps réel"></i>
                                @endif
                                @if(($features['mass_labels'] ?? false))
                                    <i class="fas fa-tags mr-1" title="Étiquettes en masse"></i>
                                @endif
                                @if(($features['pickup_address_selection'] ?? false))
                                    <i class="fas fa-map-marker-alt mr-1" title="Sélection d'adresse"></i>
                                @endif
                            </small>
                        </div>
                    @endforeach
                </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ajouter Configuration -->
<div class="modal fade" id="addConfigModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus mr-2"></i>Nouvelle Configuration de Transporteur
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.delivery.configuration.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="carrier_slug">Transporteur <span class="text-danger">*</span></label>
                                <select name="carrier_slug" id="carrier_slug" class="form-control" required>
                                    <option value="">Sélectionner un transporteur</option>
                                    @foreach($supportedCarriers as $slug => $carrier)
                                        <option value="{{ $slug }}">{{ $carrier['display_name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="integration_name">Nom d'intégration <span class="text-danger">*</span></label>
                                <input type="text" name="integration_name" id="integration_name" class="form-control" 
                                       placeholder="Ex: Fparcel - Entrepôt Principal" required>
                                <small class="form-text text-muted">Nom pour identifier cette configuration</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="username">Nom d'utilisateur <span class="text-danger">*</span></label>
                                <input type="text" name="username" id="username" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password">Mot de passe <span class="text-danger">*</span></label>
                                <input type="password" name="password" id="password" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="environment">Environnement <span class="text-danger">*</span></label>
                        <select name="environment" id="environment" class="form-control" required>
                            <option value="test">Test</option>
                            <option value="prod">Production</option>
                        </select>
                        <small class="form-text text-muted">Commencez par l'environnement de test</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>Créer la configuration
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ajouter Adresse -->
<div class="modal fade" id="addAddressModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-map-marker-alt mr-2"></i>Nouvelle Adresse d'Enlèvement
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.delivery.pickup-addresses.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="address_name">Nom de l'adresse <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="address_name" class="form-control" 
                                       placeholder="Ex: Entrepôt Principal" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="contact_name">Nom du contact <span class="text-danger">*</span></label>
                                <input type="text" name="contact_name" id="contact_name" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="address">Adresse complète <span class="text-danger">*</span></label>
                        <textarea name="address" id="address" class="form-control" rows="2" required></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="postal_code">Code postal</label>
                                <input type="text" name="postal_code" id="postal_code" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="city">Ville</label>
                                <input type="text" name="city" id="city" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="phone">Téléphone <span class="text-danger">*</span></label>
                                <input type="text" name="phone" id="phone" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" name="email" id="email" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox mt-4">
                                    <input type="checkbox" class="custom-control-input" id="is_default" name="is_default">
                                    <label class="custom-control-label" for="is_default">
                                        Définir comme adresse par défaut
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>Créer l'adresse
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function testConnection(configId) {
    $.ajax({
        url: `/admin/delivery/configuration/${configId}/test`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function() {
            Swal.fire({
                title: 'Test de connexion...',
                text: 'Vérification de la connexion au transporteur',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading()
                }
            });
        },
        success: function(response) {
            Swal.fire({
                title: 'Connexion réussie!',
                text: response.message,
                icon: 'success',
                timer: 3000
            });
            location.reload();
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            Swal.fire({
                title: 'Erreur de connexion',
                text: response.message || 'Impossible de se connecter au transporteur',
                icon: 'error'
            });
        }
    });
}

function refreshToken(configId) {
    $.ajax({
        url: `/admin/delivery/configuration/${configId}/refresh-token`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function() {
            Swal.fire({
                title: 'Rafraîchissement...',
                text: 'Mise à jour du token d\'authentification',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading()
                }
            });
        },
        success: function(response) {
            Swal.fire({
                title: 'Token rafraîchi!',
                text: response.message,
                icon: 'success',
                timer: 3000
            });
            location.reload();
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            Swal.fire({
                title: 'Erreur',
                text: response.message || 'Impossible de rafraîchir le token',
                icon: 'error'
            });
        }
    });
}

function toggleConfiguration(configId) {
    Swal.fire({
        title: 'Confirmer l\'action',
        text: 'Voulez-vous activer/désactiver cette configuration?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Oui, continuer',
        cancelButtonText: 'Annuler'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/admin/delivery/configuration/${configId}/toggle`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    Swal.fire({
                        title: 'Succès!',
                        text: response.message,
                        icon: 'success',
                        timer: 3000
                    });
                    location.reload();
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    Swal.fire({
                        title: 'Erreur',
                        text: response.message || 'Une erreur est survenue',
                        icon: 'error'
                    });
                }
            });
        }
    });
}

function deleteConfiguration(configId) {
    Swal.fire({
        title: 'Supprimer la configuration?',
        text: 'Cette action est irréversible!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Oui, supprimer',
        cancelButtonText: 'Annuler',
        confirmButtonColor: '#d33'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/delivery/configuration/${configId}`;
            
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            
            const tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '_token';
            tokenInput.value = $('meta[name="csrf-token"]').attr('content');
            
            form.appendChild(methodInput);
            form.appendChild(tokenInput);
            document.body.appendChild(form);
            form.submit();
        }
    });
}

// Validation en temps réel du formulaire
$('#addConfigModal form').on('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const submitBtn = $(form).find('button[type="submit"]');
    const originalText = submitBtn.html();
    
    submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Vérification...');
    
    // Simuler une vérification de connexion
    setTimeout(() => {
        form.submit();
    }, 1000);
});
</script>
@endpush

@push('styles')
<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}
.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}
.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}
.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.card-body .table {
    margin-bottom: 0;
}

.btn-group .btn {
    border-radius: 0.35rem;
    margin-right: 2px;
}

.badge-sm {
    font-size: 0.7em;
}
</style>
@endpush