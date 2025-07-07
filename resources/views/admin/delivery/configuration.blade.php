@extends('layouts.admin')

@section('title', 'Configuration Livraison')

@push('styles')
<style>
.modal-header {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
    border-bottom: none;
}

.modal-header .close {
    color: white;
    opacity: 0.8;
}

.modal-header .close:hover {
    opacity: 1;
}

.modal-title {
    font-weight: 600;
}

.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.btn-group .btn {
    border-radius: 4px;
    margin-right: 2px;
}

.table th {
    background-color: #f8f9fa;
    border-top: none;
    font-weight: 600;
    color: #495057;
}

.badge {
    font-size: 0.75em;
    padding: 0.375rem 0.5rem;
}

.alert-info {
    background-color: #e3f2fd;
    border-color: #bbdefb;
    color: #1976d2;
}

.card {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    border: none;
}

.card-header {
    background-color: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
}

.text-gray-800 {
    color: #5a5c69 !important;
}

.text-gray-300 {
    color: #dddfeb !important;
}

.text-gray-600 {
    color: #858796 !important;
}

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

/* Loading states */
.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}

/* Responsive improvements */
@media (max-width: 768px) {
    .btn-group {
        display: flex;
        flex-direction: column;
    }
    
    .btn-group .btn {
        margin-bottom: 2px;
        margin-right: 0;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .table-responsive {
        font-size: 0.875rem;
    }
}

/* Animation pour les alerts */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.alert {
    animation: fadeIn 0.3s ease-out;
}
</style>
@endpush

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

    <!-- Messages Flash -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

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
                                    <small class="text-muted">ID: {{ $config->id }}</small>
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
<div class="modal fade" id="addConfigModal" tabindex="-1" role="dialog" aria-labelledby="addConfigModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="addConfigForm" method="POST" action="{{ route('admin.delivery.configuration.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addConfigModalLabel">
                        <i class="fas fa-plus me-2"></i>Nouvelle Configuration Jax Delivery
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Jax Delivery</strong> utilise un token d'API pour l'authentification. 
                        Récupérez votre token depuis votre espace client Jax Delivery.
                    </div>

                    <div class="form-group">
                        <label for="integration_name">Nom d'Intégration <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="integration_name" name="integration_name" 
                               placeholder="Ex: Mon Magasin - Jax" required>
                        <small class="form-text text-muted">
                            Nom pour identifier cette configuration
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="token">Token d'API Jax Delivery <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="token" name="token" 
                               placeholder="Votre token Jax Delivery" required>
                        <small class="form-text text-muted">
                            Récupérez ce token depuis votre espace Jax Delivery
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="environment">Environnement <span class="text-danger">*</span></label>
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
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Annuler
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Modifier Configuration -->
<div class="modal fade" id="editConfigModal" tabindex="-1" role="dialog" aria-labelledby="editConfigModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="editConfigForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <h5 class="modal-title" id="editConfigModalLabel">
                        <i class="fas fa-edit me-2"></i>Modifier Configuration
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_integration_name">Nom d'Intégration <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_integration_name" name="integration_name" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_token">Token d'API (laisser vide pour ne pas changer)</label>
                        <input type="text" class="form-control" id="edit_token" name="token" 
                               placeholder="Nouveau token (optionnel)">
                        <small class="form-text text-muted">
                            Laissez vide si vous ne voulez pas changer le token actuel
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="edit_environment">Environnement <span class="text-danger">*</span></label>
                        <select class="form-control" id="edit_environment" name="environment" required>
                            <option value="prod">Production</option>
                            <option value="test">Test</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Annuler
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Mettre à jour
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Vérifier que toastr est disponible
    if (typeof toastr === 'undefined') {
        console.warn('Toastr n\'est pas chargé, utilisation d\'alert de fallback');
        window.toastr = {
            success: function(msg) { 
                console.log('Success: ' + msg);
                alert('Succès: ' + msg); 
            },
            error: function(msg) { 
                console.error('Error: ' + msg);
                alert('Erreur: ' + msg); 
            },
            warning: function(msg) { 
                console.warn('Warning: ' + msg);
                alert('Attention: ' + msg); 
            }
        };
    }

    // Configuration de toastr si disponible
    if (typeof toastr !== 'undefined' && toastr.options) {
        toastr.options = {
            closeButton: true,
            debug: false,
            newestOnTop: true,
            progressBar: true,
            positionClass: "toast-top-right",
            preventDuplicates: false,
            onclick: null,
            showDuration: "300",
            hideDuration: "1000",
            timeOut: "5000",
            extendedTimeOut: "1000",
            showEasing: "swing",
            hideEasing: "linear",
            showMethod: "fadeIn",
            hideMethod: "fadeOut"
        };
    }
});

// Test de connexion
function testConnection(configId) {
    const btn = event.target.closest('button');
    const originalHtml = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btn.disabled = true;
    
    fetch(`{{ route('admin.delivery.configuration.test', ':id') }}`.replace(':id', configId), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erreur réseau: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            toastr.success(data.message);
            setTimeout(() => location.reload(), 1500);
        } else {
            toastr.error(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        toastr.error('Erreur lors du test de connexion: ' + error.message);
    })
    .finally(() => {
        btn.innerHTML = originalHtml;
        btn.disabled = false;
    });
}

// Modifier configuration
function editConfig(configId) {
    // Afficher un indicateur de chargement
    const originalButton = event.target.closest('button');
    const originalHtml = originalButton.innerHTML;
    originalButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    originalButton.disabled = true;

    // Charger les données de la configuration
    fetch(`{{ route('admin.delivery.configuration.get', ':id') }}`.replace(':id', configId), {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erreur réseau: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Remplir le formulaire
            $('#edit_integration_name').val(data.data.integration_name);
            $('#edit_environment').val(data.data.environment);
            $('#edit_token').val(''); // Ne pas pré-remplir le token pour la sécurité
            
            // Définir l'action du formulaire
            $('#editConfigForm').attr('action', `{{ route('admin.delivery.configuration.update', ':id') }}`.replace(':id', configId));
            
            // Afficher le modal
            $('#editConfigModal').modal('show');
        } else {
            toastr.error(data.message || 'Erreur lors du chargement');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        toastr.error('Erreur lors du chargement de la configuration: ' + error.message);
    })
    .finally(() => {
        originalButton.innerHTML = originalHtml;
        originalButton.disabled = false;
    });
}

// Activer/Désactiver configuration
function toggleConfig(configId) {
    const btn = event.target.closest('button');
    const originalHtml = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btn.disabled = true;
    
    fetch(`{{ route('admin.delivery.configuration.toggle', ':id') }}`.replace(':id', configId), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erreur réseau: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            toastr.success(data.message);
            setTimeout(() => location.reload(), 1500);
        } else {
            toastr.error(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        toastr.error('Erreur lors de la modification: ' + error.message);
    })
    .finally(() => {
        btn.innerHTML = originalHtml;
        btn.disabled = false;
    });
}

// Supprimer configuration
function deleteConfig(configId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette configuration ?\n\nCette action est irréversible et supprimera également tous les enlèvements associés.')) {
        const btn = event.target.closest('button');
        const originalHtml = btn.innerHTML;
        
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        btn.disabled = true;
        
        fetch(`{{ route('admin.delivery.configuration.delete', ':id') }}`.replace(':id', configId), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur réseau: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                toastr.success(data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                toastr.error(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            toastr.error('Erreur lors de la suppression: ' + error.message);
        })
        .finally(() => {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        });
    }
}

// Gestion des formulaires modaux
$('#addConfigForm').on('submit', function(e) {
    e.preventDefault();
    
    const form = $(this);
    const submitBtn = form.find('button[type="submit"]');
    const originalText = submitBtn.html();
    
    submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Enregistrement...');
    submitBtn.prop('disabled', true);
    
    $.ajax({
        url: form.attr('action'),
        method: 'POST',
        data: form.serialize(),
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        success: function(response) {
            toastr.success('Configuration créée avec succès');
            $('#addConfigModal').modal('hide');
            setTimeout(() => location.reload(), 1500);
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                // Erreurs de validation
                const errors = xhr.responseJSON.errors;
                let errorMessage = '';
                Object.keys(errors).forEach(key => {
                    errorMessage += errors[key].join('\n') + '\n';
                });
                toastr.error('Erreurs de validation:\n' + errorMessage);
            } else {
                const message = xhr.responseJSON?.message || 'Erreur lors de la création de la configuration';
                toastr.error(message);
            }
        },
        complete: function() {
            submitBtn.html(originalText);
            submitBtn.prop('disabled', false);
        }
    });
});

$('#editConfigForm').on('submit', function(e) {
    e.preventDefault();
    
    const form = $(this);
    const submitBtn = form.find('button[type="submit"]');
    const originalText = submitBtn.html();
    
    submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Mise à jour...');
    submitBtn.prop('disabled', true);
    
    $.ajax({
        url: form.attr('action'),
        method: 'PATCH',
        data: form.serialize(),
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        success: function(response) {
            toastr.success('Configuration mise à jour avec succès');
            $('#editConfigModal').modal('hide');
            setTimeout(() => location.reload(), 1500);
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                // Erreurs de validation
                const errors = xhr.responseJSON.errors;
                let errorMessage = '';
                Object.keys(errors).forEach(key => {
                    errorMessage += errors[key].join('\n') + '\n';
                });
                toastr.error('Erreurs de validation:\n' + errorMessage);
            } else {
                const message = xhr.responseJSON?.message || 'Erreur lors de la mise à jour';
                toastr.error(message);
            }
        },
        complete: function() {
            submitBtn.html(originalText);
            submitBtn.prop('disabled', false);
        }
    });
});

// Réinitialiser les formulaires quand les modals se ferment
$('#addConfigModal').on('hidden.bs.modal', function() {
    $('#addConfigForm')[0].reset();
});

$('#editConfigModal').on('hidden.bs.modal', function() {
    $('#editConfigForm')[0].reset();
});

// Fermer les alerts automatiquement après 5 secondes
$('.alert').each(function() {
    const alert = $(this);
    setTimeout(() => {
        alert.fadeOut();
    }, 5000);
});
</script>
@endpush