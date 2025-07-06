@extends('layouts.admin')

@section('title', 'Configuration Livraison')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0">Configuration Jax Delivery</h1>
            <p class="text-muted">Gérez vos configurations de livraison Jax Delivery</p>
        </div>
        <div class="col-md-4 text-right">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addConfigModal">
                <i class="fas fa-plus"></i> Nouvelle Configuration
            </button>
        </div>
    </div>

    <!-- Statistiques rapides -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Configurations
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_configs'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-cog fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-success">
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
        <div class="col-md-3">
            <div class="card border-left-info">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Configurations Testées
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['tested_configs'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-vial fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-warning">
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

    <!-- Liste des configurations -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Configurations Jax Delivery</h6>
        </div>
        <div class="card-body">
            @if($configurations->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Nom d'Intégration</th>
                                <th>Environnement</th>
                                <th>Statut</th>
                                <th>Token Expire</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($configurations as $config)
                            <tr>
                                <td>
                                    <strong>{{ $config->integration_name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $config->username ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $config->environment === 'prod' ? 'success' : 'warning' }}">
                                        {{ ucfirst($config->environment) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $config->status_info['badge_class'] }}">
                                        {{ $config->status_info['badge_text'] }}
                                    </span>
                                    @if(!$config->is_active)
                                        <br><small class="text-muted">Inactif</small>
                                    @endif
                                </td>
                                <td>
                                    @if($config->expires_at)
                                        {{ $config->expires_at->format('d/m/Y H:i') }}
                                        <br>
                                        <small class="text-muted">
                                            ({{ $config->expires_at->diffForHumans() }})
                                        </small>
                                    @else
                                        <span class="text-muted">Non défini</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick="testConnection({{ $config->id }})" 
                                                title="Tester la connexion">
                                            <i class="fas fa-vial"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                onclick="editConfig({{ $config->id }})" 
                                                title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-{{ $config->is_active ? 'warning' : 'success' }}" 
                                                onclick="toggleConfig({{ $config->id }})" 
                                                title="{{ $config->is_active ? 'Désactiver' : 'Activer' }}">
                                            <i class="fas fa-{{ $config->is_active ? 'pause' : 'play' }}"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
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
                <div class="text-center py-5">
                    <i class="fas fa-truck fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-600">Aucune configuration</h5>
                    <p class="text-muted">Créez votre première configuration Jax Delivery pour commencer.</p>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addConfigModal">
                        <i class="fas fa-plus"></i> Créer une Configuration
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Ajouter Configuration -->
<div class="modal fade" id="addConfigModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addConfigForm" method="POST" action="{{ route('admin.delivery.configuration.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Nouvelle Configuration Jax Delivery</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Jax Delivery</strong> utilise un token d'API pour l'authentification. 
                        Récupérez votre token depuis votre espace client Jax Delivery.
                    </div>

                    <div class="form-group">
                        <label for="integration_name">Nom d'Intégration *</label>
                        <input type="text" class="form-control" id="integration_name" name="integration_name" 
                               placeholder="Ex: Mon Magasin - Jax" required>
                        <small class="form-text text-muted">
                            Nom pour identifier cette configuration
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="token">Token d'API Jax Delivery *</label>
                        <input type="text" class="form-control" id="token" name="token" 
                               placeholder="Votre token Jax Delivery" required>
                        <small class="form-text text-muted">
                            Récupérez ce token depuis votre espace Jax Delivery
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="environment">Environnement *</label>
                        <select class="form-control" id="environment" name="environment" required>
                            <option value="prod">Production</option>
                            <option value="test">Test</option>
                        </select>
                        <small class="form-text text-muted">
                            Utilisez "Test" pour vos essais, "Production" pour vos vraies commandes
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Modifier Configuration -->
<div class="modal fade" id="editConfigModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editConfigForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <h5 class="modal-title">Modifier Configuration</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_integration_name">Nom d'Intégration *</label>
                        <input type="text" class="form-control" id="edit_integration_name" name="integration_name" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_token">Token d'API (laisser vide pour ne pas changer)</label>
                        <input type="text" class="form-control" id="edit_token" name="token" 
                               placeholder="Nouveau token (optionnel)">
                    </div>

                    <div class="form-group">
                        <label for="edit_environment">Environnement *</label>
                        <select class="form-control" id="edit_environment" name="environment" required>
                            <option value="prod">Production</option>
                            <option value="test">Test</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Mettre à jour
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Test de connexion
function testConnection(configId) {
    const btn = event.target.closest('button');
    const originalHtml = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btn.disabled = true;
    
    fetch(`/admin/delivery/configuration/${configId}/test`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
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
        toastr.error('Erreur lors du test de connexion');
        console.error('Error:', error);
    })
    .finally(() => {
        btn.innerHTML = originalHtml;
        btn.disabled = false;
    });
}

// Modifier configuration
function editConfig(configId) {
    // Charger les données de la configuration
    fetch(`/admin/delivery/configuration/${configId}`)
    .then(response => response.json())
    .then(data => {
        $('#edit_integration_name').val(data.integration_name);
        $('#edit_environment').val(data.environment);
        $('#editConfigForm').attr('action', `/admin/delivery/configuration/${configId}`);
        $('#editConfigModal').modal('show');
    })
    .catch(error => {
        toastr.error('Erreur lors du chargement de la configuration');
    });
}

// Activer/Désactiver configuration
function toggleConfig(configId) {
    fetch(`/admin/delivery/configuration/${configId}/toggle`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
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
        toastr.error('Erreur lors de la modification');
    });
}

// Supprimer configuration
function deleteConfig(configId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette configuration ?')) {
        fetch(`/admin/delivery/configuration/${configId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
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
</script>
@endpush