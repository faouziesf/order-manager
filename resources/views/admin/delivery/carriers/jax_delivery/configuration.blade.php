{{-- resources/views/admin/delivery/carriers/fparcel/configuration.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Configuration Fparcel')

@section('content')
<div class="fparcel-configuration">
    {{-- En-tête --}}
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <img src="{{ asset('images/carriers/fparcel.png') }}" alt="Fparcel" class="carrier-logo-header">
                        Configuration Fparcel
                    </h1>
                    <p class="text-muted">{{ $carrierInfo['description'] ?? 'Service de livraison tunisien complet' }}</p>
                </div>
                <div class="col-sm-6">
                    <div class="float-sm-right">
                        <a href="{{ route('admin.delivery.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Retour
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            
            {{-- Messages flash --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                </div>
            @endif

            <div class="row">
                {{-- Configuration API --}}
                <div class="col-lg-8">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-cog"></i> Configurations API
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#addConfigModal">
                                    <i class="fas fa-plus"></i> Nouvelle Configuration
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            @if($configurations->count() > 0)
                                <div class="configurations-list">
                                    @foreach($configurations as $config)
                                        <div class="configuration-item" data-config-id="{{ $config->id }}">
                                            <div class="config-header">
                                                <div class="config-info">
                                                    <h5>{{ $config->integration_name }}</h5>
                                                    <div class="config-details">
                                                        <span class="badge badge-{{ $config->status_info['badge_class'] }}">
                                                            {{ $config->status_info['badge_text'] }}
                                                        </span>
                                                        <span class="text-muted ml-2">
                                                            <i class="fas fa-user"></i> {{ $config->username }}
                                                        </span>
                                                        <span class="text-muted ml-2">
                                                            <i class="fas fa-server"></i> {{ ucfirst($config->environment) }}
                                                        </span>
                                                        @if($config->expires_at)
                                                            <span class="text-muted ml-2">
                                                                <i class="fas fa-clock"></i> Expire: {{ $config->expires_at->format('d/m/Y H:i') }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="config-actions">
                                                    <button class="btn btn-sm btn-outline-primary" onclick="testConnection({{ $config->id }})">
                                                        <i class="fas fa-plug"></i> Tester
                                                    </button>
                                                    @if($config->expires_at && $config->expires_at->isBefore(now()->addHours(12)))
                                                        <button class="btn btn-sm btn-outline-warning" onclick="refreshToken({{ $config->id }})">
                                                            <i class="fas fa-sync"></i> Rafraîchir
                                                        </button>
                                                    @endif
                                                    <button class="btn btn-sm btn-outline-secondary" onclick="editConfig({{ $config->id }})">
                                                        <i class="fas fa-edit"></i> Modifier
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteConfig({{ $config->id }})">
                                                        <i class="fas fa-trash"></i> Supprimer
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            {{-- Statistiques de la configuration --}}
                                            <div class="config-stats">
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="stat-box">
                                                            <i class="fas fa-box text-primary"></i>
                                                            <span class="stat-number">{{ $config->stats['pickups_count'] }}</span>
                                                            <span class="stat-label">Enlèvements</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="stat-box">
                                                            <i class="fas fa-clock text-warning"></i>
                                                            <span class="stat-number">{{ $config->stats['active_pickups'] }}</span>
                                                            <span class="stat-label">En cours</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="stat-box">
                                                            <i class="fas fa-shipping-fast text-success"></i>
                                                            <span class="stat-number">{{ $config->stats['shipments_count'] }}</span>
                                                            <span class="stat-label">Expéditions</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="stat-box">
                                                            <i class="fas fa-calendar text-info"></i>
                                                            <span class="stat-number">{{ $config->created_at->diffForHumans() }}</span>
                                                            <span class="stat-label">Créée</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="empty-state">
                                    <i class="fas fa-cog fa-3x text-muted mb-3"></i>
                                    <h5>Aucune configuration Fparcel</h5>
                                    <p class="text-muted">Créez votre première configuration pour commencer à utiliser Fparcel.</p>
                                    <button class="btn btn-primary" data-toggle="modal" data-target="#addConfigModal">
                                        <i class="fas fa-plus"></i> Créer une configuration
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Sidebar - Fonctionnalités Fparcel --}}
                <div class="col-lg-4">
                    {{-- Informations transporteur --}}
                    <div class="card card-info">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-info-circle"></i> Informations Fparcel
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="carrier-features">
                                <h6><i class="fas fa-check-circle text-success"></i> Fonctionnalités supportées :</h6>
                                <ul class="feature-list">
                                    <li><i class="fas fa-map-marker-alt"></i> Adresses d'enlèvement personnalisées</li>
                                    <li><i class="fas fa-file-pdf"></i> Templates de bordereaux personnalisés</li>
                                    <li><i class="fas fa-tags"></i> Génération d'étiquettes en masse</li>
                                    <li><i class="fas fa-map-pin"></i> Points de dépôt</li>
                                    <li><i class="fas fa-credit-card"></i> Méthodes de paiement COD</li>
                                    <li><i class="fas fa-tracking"></i> Suivi en temps réel</li>
                                </ul>
                                
                                <h6 class="mt-3"><i class="fas fa-server text-primary"></i> Environnements :</h6>
                                <ul class="environment-list">
                                    <li><strong>Test :</strong> {{ $carrierInfo['api_endpoints']['test'] }}</li>
                                    <li><strong>Production :</strong> {{ $carrierInfo['api_endpoints']['prod'] }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    {{-- Actions rapides --}}
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-rocket"></i> Actions Rapides
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="quick-actions-vertical">
                                <a href="{{ route('admin.delivery.pickup-addresses.index') }}" class="btn btn-outline-primary btn-block">
                                    <i class="fas fa-map-marker-alt"></i> Gérer les Adresses
                                </a>
                                <a href="{{ route('admin.delivery.bl-templates.index') }}" class="btn btn-outline-info btn-block">
                                    <i class="fas fa-file-pdf"></i> Templates BL
                                </a>
                                <a href="{{ route('admin.delivery.preparation') }}" class="btn btn-success btn-block">
                                    <i class="fas fa-plus"></i> Nouvel Enlèvement
                                </a>
                                <a href="{{ route('admin.delivery.pickups.index') }}" class="btn btn-warning btn-block">
                                    <i class="fas fa-truck"></i> Voir les Enlèvements
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sections supplémentaires pour Fparcel --}}
            @if($configurations->count() > 0)
                <div class="row mt-4">
                    {{-- Adresses d'enlèvement --}}
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-map-marker-alt"></i> Adresses d'Enlèvement
                                </h3>
                                <div class="card-tools">
                                    <a href="{{ route('admin.delivery.pickup-addresses.create') }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-plus"></i> Ajouter
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                @if($carrierData['pickup_addresses']->count() > 0)
                                    @foreach($carrierData['pickup_addresses']->take(3) as $address)
                                        <div class="address-item">
                                            <div class="address-header">
                                                <strong>{{ $address->name }}</strong>
                                                @if($address->is_default)
                                                    <span class="badge badge-primary">Par défaut</span>
                                                @endif
                                            </div>
                                            <div class="address-details">
                                                <i class="fas fa-user"></i> {{ $address->contact_name }}<br>
                                                <i class="fas fa-map-marker-alt"></i> {{ Str::limit($address->address, 50) }}<br>
                                                <i class="fas fa-phone"></i> {{ $address->phone }}
                                            </div>
                                        </div>
                                    @endforeach
                                    @if($carrierData['pickup_addresses']->count() > 3)
                                        <div class="text-center mt-2">
                                            <a href="{{ route('admin.delivery.pickup-addresses.index') }}" class="btn btn-sm btn-outline-primary">
                                                Voir tout ({{ $carrierData['pickup_addresses']->count() }})
                                            </a>
                                        </div>
                                    @endif
                                @else
                                    <div class="empty-state-small">
                                        <p class="text-muted">Aucune adresse configurée</p>
                                        <a href="{{ route('admin.delivery.pickup-addresses.create') }}" class="btn btn-sm btn-primary">
                                            Ajouter la première adresse
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Templates BL --}}
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-file-pdf"></i> Templates de Bordereaux
                                </h3>
                                <div class="card-tools">
                                    <a href="{{ route('admin.delivery.bl-templates.create') }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-plus"></i> Créer
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                @if($carrierData['bl_templates']->count() > 0)
                                    @foreach($carrierData['bl_templates']->take(3) as $template)
                                        <div class="template-item">
                                            <div class="template-header">
                                                <strong>{{ $template->template_name }}</strong>
                                                @if($template->is_default)
                                                    <span class="badge badge-primary">Par défaut</span>
                                                @endif
                                                @if($template->is_active)
                                                    <span class="badge badge-success">Actif</span>
                                                @endif
                                            </div>
                                            <div class="template-actions">
                                                <a href="{{ route('admin.delivery.bl-templates.edit', $template) }}" class="btn btn-xs btn-outline-primary">
                                                    <i class="fas fa-edit"></i> Modifier
                                                </a>
                                                <a href="{{ route('admin.delivery.bl-templates.preview', $template) }}" class="btn btn-xs btn-outline-info" target="_blank">
                                                    <i class="fas fa-eye"></i> Aperçu
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                    @if($carrierData['bl_templates']->count() > 3)
                                        <div class="text-center mt-2">
                                            <a href="{{ route('admin.delivery.bl-templates.index') }}" class="btn btn-sm btn-outline-primary">
                                                Voir tout ({{ $carrierData['bl_templates']->count() }})
                                            </a>
                                        </div>
                                    @endif
                                @else
                                    <div class="empty-state-small">
                                        <p class="text-muted">Aucun template configuré</p>
                                        <a href="{{ route('admin.delivery.bl-templates.create') }}" class="btn btn-sm btn-primary">
                                            Créer le premier template
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Modal Ajouter Configuration --}}
<div class="modal fade" id="addConfigModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="addConfigForm" method="POST" action="{{ route('admin.delivery.carrier.config.store', 'fparcel') }}">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title">
                        <i class="fas fa-plus"></i> Nouvelle Configuration Fparcel
                    </h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="integration_name">Nom de l'intégration <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="integration_name" name="integration_name" 
                                       placeholder="Ex: Fparcel Principal" required>
                                <small class="form-text text-muted">Nom pour identifier cette configuration</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="environment">Environnement <span class="text-danger">*</span></label>
                                <select class="form-control" id="environment" name="environment" required>
                                    <option value="test">Test</option>
                                    <option value="prod">Production</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="username">Nom d'utilisateur Fparcel <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password">Mot de passe <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Information :</strong> Vos identifiants seront chiffrés et stockés de manière sécurisée.
                        Vous pourrez tester la connexion après la création.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Créer la Configuration
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Test Connexion --}}
<div class="modal fade" id="testModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Test de Connexion</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="test-result"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.carrier-logo-header {
    width: 40px;
    height: 40px;
    object-fit: contain;
    margin-right: 10px;
}

.configuration-item {
    border: 1px solid #e3e6f0;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    background: #f8f9fc;
}

.config-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
}

.config-info h5 {
    margin: 0;
    color: #2c3e50;
}

.config-details {
    margin-top: 8px;
}

.config-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.config-stats {
    border-top: 1px solid #dee2e6;
    padding-top: 15px;
}

.stat-box {
    text-align: center;
    padding: 10px;
}

.stat-box i {
    font-size: 24px;
    display: block;
    margin-bottom: 5px;
}

.stat-number {
    display: block;
    font-size: 18px;
    font-weight: bold;
    color: #2c3e50;
}

.stat-label {
    display: block;
    font-size: 12px;
    color: #6c757d;
    text-transform: uppercase;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
}

.empty-state-small {
    text-align: center;
    padding: 20px;
}

.feature-list {
    list-style: none;
    padding: 0;
}

.feature-list li {
    padding: 5px 0;
}

.feature-list i {
    width: 20px;
    color: #28a745;
}

.environment-list {
    list-style: none;
    padding: 0;
}

.environment-list li {
    padding: 3px 0;
    font-size: 14px;
}

.quick-actions-vertical .btn {
    margin-bottom: 10px;
}

.address-item, .template-item {
    border-bottom: 1px solid #eee;
    padding: 10px 0;
}

.address-item:last-child, .template-item:last-child {
    border-bottom: none;
}

.address-header, .template-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 5px;
}

.address-details {
    font-size: 13px;
    color: #6c757d;
    line-height: 1.4;
}

.template-actions {
    margin-top: 5px;
}

.template-actions .btn {
    margin-right: 5px;
}

@media (max-width: 768px) {
    .config-header {
        flex-direction: column;
        align-items: stretch;
    }
    
    .config-actions {
        margin-top: 15px;
        justify-content: center;
    }
    
    .config-actions .btn {
        flex: 1;
        min-width: 0;
    }
}
</style>
@endpush

@push('scripts')
<script>
function testConnection(configId) {
    $('#testModal').modal('show');
    $('#test-result').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Test en cours...</div>');
    
    fetch(`{{ url('admin/delivery/fparcel/test-connection') }}/${configId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            $('#test-result').html(`
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> ${data.message}
                    ${data.token_expires_at ? `<br><small class="text-muted">Token valide jusqu'au: ${data.token_expires_at}</small>` : ''}
                </div>
            `);
            // Recharger la page après 2 secondes
            setTimeout(() => location.reload(), 2000);
        } else {
            $('#test-result').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-times-circle"></i> ${data.message}
                </div>
            `);
        }
    })
    .catch(error => {
        $('#test-result').html(`
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> Erreur de communication
            </div>
        `);
    });
}

function refreshToken(configId) {
    // Logique de rafraîchissement du token
    fetch(`{{ url('admin/delivery/fparcel/refresh-token') }}/${configId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            toastr.success(data.message);
            location.reload();
        } else {
            toastr.error(data.message);
        }
    })
    .catch(error => {
        toastr.error('Erreur lors du rafraîchissement');
    });
}

function editConfig(configId) {
    // TODO: Implémenter la modification
    toastr.info('Fonctionnalité en cours de développement');
}

function deleteConfig(configId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette configuration ?')) {
        fetch(`{{ url('admin/delivery/fparcel/configuration') }}/${configId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                toastr.success(data.message);
                location.reload();
            } else {
                toastr.error(data.message);
            }
        })
        .catch(error => {
            toastr.error('Erreur lors de la suppression');
        });
    }
}

// Validation du formulaire
document.getElementById('addConfigForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Création...';
    submitBtn.disabled = true;
    
    fetch(this.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: formData
    })
    .then(response => {
        if (response.ok) {
            location.reload();
        } else {
            return response.json().then(data => {
                throw new Error(data.message || 'Erreur lors de la création');
            });
        }
    })
    .catch(error => {
        toastr.error(error.message);
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});
</script>
@endpush