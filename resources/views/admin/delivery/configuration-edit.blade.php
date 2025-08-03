@extends('layouts.admin')

@section('title', 'Modifier la Configuration - ' . $config->integration_name)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-edit text-primary me-2"></i>
                Modifier la Configuration
            </h1>
            <p class="text-muted mb-0">{{ $config->integration_name }} - {{ $carrier['name'] }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.delivery.configuration') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Retour
            </a>
            @if($config->is_active)
                <span class="badge bg-success px-3 py-2 d-flex align-items-center">
                    <i class="fas fa-check-circle me-1"></i>
                    Active
                </span>
            @else
                <span class="badge bg-warning px-3 py-2 d-flex align-items-center">
                    <i class="fas fa-pause-circle me-1"></i>
                    Inactive
                </span>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3 d-flex align-items-center">
                    <div class="me-3">
                        @if(isset($carrier['logo']))
                            <img src="{{ asset($carrier['logo']) }}" 
                                 alt="{{ $carrier['name'] }}" 
                                 class="carrier-logo-sm">
                        @else
                            <div class="carrier-logo-sm d-flex align-items-center justify-content-center">
                                <i class="fas fa-truck fa-lg text-muted"></i>
                            </div>
                        @endif
                    </div>
                    <div>
                        <h6 class="m-0 font-weight-bold text-primary">{{ $carrier['name'] }}</h6>
                        <small class="text-muted">ID de Configuration : {{ $config->id }}</small>
                    </div>
                </div>
                
                <form id="editConfigForm" action="{{ route('admin.delivery.configuration.update', $config) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <div class="mb-4">
                            <label for="integration_name" class="form-label">
                                <i class="fas fa-tag me-1"></i>
                                Nom de la Liaison <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('integration_name') is-invalid @enderror" 
                                   id="integration_name" 
                                   name="integration_name" 
                                   value="{{ old('integration_name', $config->integration_name) }}"
                                   required>
                            <div class="form-text">
                                Nom unique pour identifier cette configuration.
                            </div>
                            @error('integration_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if($config->carrier_slug === 'jax_delivery')
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="username" class="form-label">
                                        <i class="fas fa-user me-1"></i>
                                        Numéro de Compte <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('username') is-invalid @enderror" 
                                           id="username" 
                                           name="username" 
                                           value="{{ old('username', $config->username) }}"
                                           required>
                                    @error('username')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6 mb-4">
                                    <label for="password" class="form-label">
                                        <i class="fas fa-key me-1"></i>
                                        Token API
                                    </label>
                                    <div class="input-group">
                                        <input type="password" 
                                               class="form-control @error('password') is-invalid @enderror" 
                                               id="password" 
                                               name="password" 
                                               value="{{ old('password') }}"
                                               placeholder="Laisser vide pour conserver l'actuel">
                                        <button class="btn btn-outline-secondary" 
                                                type="button" 
                                                onclick="togglePasswordVisibility()">
                                            <i class="fas fa-eye" id="passwordToggleIcon"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">
                                        Remplir uniquement pour changer le token.
                                    </div>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        @elseif($config->carrier_slug === 'mes_colis')
                            <div class="mb-4">
                                <label for="username" class="form-label">
                                    <i class="fas fa-key me-1"></i>
                                    Token API <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="text" 
                                           class="form-control @error('username') is-invalid @enderror" 
                                           id="username" 
                                           name="username" 
                                           value="{{ old('username', $config->username) }}"
                                           required>
                                    <button class="btn btn-outline-secondary" 
                                            type="button" 
                                            onclick="togglePasswordVisibility()">
                                        <i class="fas fa-eye" id="passwordToggleIcon"></i>
                                    </button>
                                </div>
                                @error('username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                        <div class="card bg-light border-0 mb-4">
                            <div class="card-header bg-transparent border-0 py-2">
                                <h6 class="mb-0 text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Informations sur la Configuration
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <strong>Statut actuel :</strong>
                                            @if($config->is_active)
                                                <span class="badge bg-success ms-2">
                                                    <i class="fas fa-check me-1"></i>
                                                    Actif
                                                </span>
                                            @else
                                                <span class="badge bg-warning ms-2">
                                                    <i class="fas fa-pause me-1"></i>
                                                    Inactif
                                                </span>
                                            @endif
                                        </div>
                                        <div class="mb-3">
                                            <strong>Environnement :</strong>
                                            <span class="badge bg-{{ $config->environment === 'prod' ? 'primary' : 'secondary' }} ms-2">
                                                {{ ucfirst($config->environment) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <strong>Créée le :</strong>
                                            <span class="ms-2">{{ $config->created_at->format('d/m/Y à H:i') }}</span>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Dernière modification :</strong>
                                            <span class="ms-2">{{ $config->updated_at->format('d/m/Y à H:i') }}</span>
                                        </div>
                                    </div>
                                </div>

                                @if($config->settings && isset($config->settings['last_test_at']))
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="alert {{ ($config->settings['last_test_success'] ?? false) ? 'alert-success' : 'alert-warning' }} py-2 mb-0">
                                                <small>
                                                    <i class="fas fa-{{ ($config->settings['last_test_success'] ?? false) ? 'check-circle' : 'exclamation-triangle' }} me-1"></i>
                                                    <strong>Dernier test :</strong> 
                                                    {{ \Carbon\Carbon::parse($config->settings['last_test_at'])->format('d/m/Y à H:i') }}
                                                    - <strong>{{ ($config->settings['last_test_success'] ?? false) ? 'Réussi' : 'Échoué' }}</strong>
                                                    @if(isset($config->settings['last_test_error']))
                                                        <br><span class="text-danger">{{ $config->settings['last_test_error'] }}</span>
                                                    @endif
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <div>
                            <button type="button" 
                                    class="btn btn-outline-danger"
                                    onclick="deleteConfiguration()"
                                    id="deleteButton">
                                <i class="fas fa-trash me-1"></i>
                                Supprimer
                            </button>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="button" 
                                    class="btn btn-outline-primary"
                                    onclick="testConnection()"
                                    id="testButton">
                                <i class="fas fa-wifi me-1"></i>
                                Tester la Connexion
                            </button>
                            
                            <button type="button" 
                                    class="btn btn-{{ $config->is_active ? 'warning' : 'success' }}"
                                    onclick="toggleStatus()"
                                    id="toggleButton">
                                <i class="fas fa-{{ $config->is_active ? 'pause' : 'play' }} me-1"></i>
                                {{ $config->is_active ? 'Désactiver' : 'Activer' }}
                            </button>
                            
                            <button type="submit" 
                                    class="btn btn-primary"
                                    id="submitButton">
                                <i class="fas fa-save me-1"></i>
                                Sauvegarder
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar me-1"></i>
                        Statistiques d'Utilisation
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="h4 mb-0 text-primary">{{ $config->pickups()->count() }}</div>
                            <small class="text-muted">Enlèvements</small>
                        </div>
                        <div class="col-6">
                            <div class="h4 mb-0 text-success">{{ $config->shipments()->count() }}</div>
                            <small class="text-muted">Expéditions</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-rocket me-1"></i>
                        Actions Rapides
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($config->is_active)
                            <a href="{{ route('admin.delivery.preparation') }}?config_id={{ $config->id }}" 
                               class="btn btn-success">
                                <i class="fas fa-plus me-1"></i>
                                Créer un Enlèvement
                            </a>
                        @endif
                        
                        <a href="{{ route('admin.delivery.pickups') }}?config_id={{ $config->id }}" 
                           class="btn btn-outline-primary">
                            <i class="fas fa-truck-pickup me-1"></i>
                            Voir les Enlèvements
                        </a>
                        
                        <a href="{{ route('admin.delivery.shipments') }}?config_id={{ $config->id }}" 
                           class="btn btn-outline-info">
                            <i class="fas fa-shipping-fast me-1"></i>
                            Voir les Expéditions
                        </a>
                    </div>
                </div>
            </div>

            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-dark">
                        <i class="fas fa-info-circle me-1"></i>
                        Informations du Transporteur
                    </h6>
                </div>
                <div class="card-body">
                    @if(isset($carrier['website']))
                        <p class="mb-2">
                            <i class="fas fa-globe text-primary fa-fw me-2"></i>
                            <a href="{{ $carrier['website'] }}" target="_blank">Site Web</a>
                        </p>
                    @endif
                    
                    @if(isset($carrier['support_phone']))
                        <p class="mb-2">
                            <i class="fas fa-phone text-success fa-fw me-2"></i>
                            <a href="tel:{{ $carrier['support_phone'] }}">{{ $carrier['support_phone'] }}</a>
                        </p>
                    @endif
                    
                    @if(isset($carrier['support_email']))
                        <p class="mb-0">
                            <i class="fas fa-envelope text-info fa-fw me-2"></i>
                            <a href="mailto:{{ $carrier['support_email'] }}">{{ $carrier['support_email'] }}</a>
                        </p>
                    @endif

                    <hr>
                    <small class="text-muted">
                        <strong>Limites :</strong><br>
                        • Poids max : {{ $carrier['limits']['max_weight'] ?? 'N/A' }} kg<br>
                        • COD max : {{ number_format($carrier['limits']['max_cod_amount'] ?? 0, 3, '.', ' ') }} TND
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Test de Connexion -->
<div class="modal fade" id="testConnectionModal" tabindex="-1" aria-labelledby="testConnectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="testConnectionModalLabel">
                    <i class="fas fa-wifi me-2"></i>
                    Test de Connexion
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="testResult"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const configId = {{ $config->id }};
const isCurrentlyActive = {{ $config->is_active ? 'true' : 'false' }};

function togglePasswordVisibility() {
    const passwordInput = document.getElementById('password') || document.getElementById('username');
    const toggleIcon = document.getElementById('passwordToggleIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.className = 'fas fa-eye-slash';
    } else {
        passwordInput.type = 'password';
        toggleIcon.className = 'fas fa-eye';
    }
}

async function testConnection() {
    const modal = new bootstrap.Modal(document.getElementById('testConnectionModal'));
    const resultDiv = document.getElementById('testResult');
    const testButton = document.getElementById('testButton');
    
    testButton.disabled = true;
    testButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Test en cours...';
    
    resultDiv.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary mb-3" role="status">
                <span class="visually-hidden">Test en cours...</span>
            </div>
            <h5 class="text-primary">Test en cours...</h5>
            <p class="text-muted">Connexion au transporteur</p>
        </div>
    `;
    
    modal.show();
    
    try {
        const response = await fetch(`/admin/delivery/configuration/${configId}/test`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            resultDiv.innerHTML = `
                <div class="alert alert-success">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle fa-2x me-3"></i>
                        <div>
                            <h6 class="alert-heading mb-1">Test réussi !</h6>
                            <p class="mb-0">${data.message}</p>
                        </div>
                    </div>
                </div>
            `;
            
            setTimeout(() => {
                modal.hide();
                window.location.reload();
            }, 2000);
        } else {
            resultDiv.innerHTML = `
                <div class="alert alert-danger">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                        <div>
                            <h6 class="alert-heading mb-1">Test échoué</h6>
                            <p class="mb-0">${data.error || 'Impossible de se connecter au transporteur.'}</p>
                        </div>
                    </div>
                </div>
            `;
        }
    } catch (error) {
        resultDiv.innerHTML = `
            <div class="alert alert-danger">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                    <div>
                        <h6 class="alert-heading mb-1">Erreur de test</h6>
                        <p class="mb-0">Une erreur inattendue est survenue: ${error.message}</p>
                    </div>
                </div>
            </div>
        `;
    } finally {
        testButton.disabled = false;
        testButton.innerHTML = '<i class="fas fa-wifi me-1"></i>Tester la Connexion';
    }
}

async function toggleStatus() {
    const action = isCurrentlyActive ? "désactiver" : "activer";
    const toggleButton = document.getElementById('toggleButton');
    
    const result = await Swal.fire({
        title: `${action.charAt(0).toUpperCase() + action.slice(1)} la configuration ?`,
        text: `Êtes-vous sûr(e) de vouloir ${action} cette liaison ?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Confirmer',
        cancelButtonText: 'Annuler'
    });

    if (!result.isConfirmed) return;
    
    toggleButton.disabled = true;
    toggleButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Mise à jour...';

    try {
        const response = await fetch(`/admin/delivery/configuration/${configId}/toggle`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Statut mis à jour !',
                text: data.message,
                showConfirmButton: false,
                timer: 1500
            });
            
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            throw new Error(data.message || 'Impossible de changer le statut.');
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Erreur',
            text: error.message,
        });
        
        toggleButton.disabled = false;
        const originalText = isCurrentlyActive 
            ? '<i class="fas fa-pause me-1"></i>Désactiver'
            : '<i class="fas fa-play me-1"></i>Activer';
        toggleButton.innerHTML = originalText;
    }
}

async function deleteConfiguration() {
    const deleteButton = document.getElementById('deleteButton');
    
    const result = await Swal.fire({
        title: 'Supprimer la configuration ?',
        text: 'Cette action est irréversible et supprimera la liaison définitivement.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Oui, supprimer',
        cancelButtonText: 'Annuler'
    });

    if (!result.isConfirmed) return;
    
    deleteButton.disabled = true;
    deleteButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Suppression...';

    try {
        const response = await fetch(`/admin/delivery/configuration/${configId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            await Swal.fire({
                icon: 'success',
                title: 'Supprimée !',
                text: 'La configuration a été supprimée avec succès.',
                showConfirmButton: false,
                timer: 2000
            });
            
            window.location.href = '{{ route("admin.delivery.configuration") }}';
        } else {
            throw new Error(data.error || 'Impossible de supprimer cette configuration.');
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Erreur',
            text: error.message,
        });
        
        deleteButton.disabled = false;
        deleteButton.innerHTML = '<i class="fas fa-trash me-1"></i>Supprimer';
    }
}

// Soumission du formulaire
document.getElementById('editConfigForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitButton = document.getElementById('submitButton');
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Sauvegarde...';
    
    try {
        const formData = new FormData(this);
        
        const response = await fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Configuration mise à jour !',
                text: data.message || 'Les modifications ont été sauvegardées.',
                showConfirmButton: false,
                timer: 2000
            });
            
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            throw new Error(data.message || 'Erreur lors de la sauvegarde');
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Erreur de sauvegarde',
            text: error.message,
        });
        
        submitButton.disabled = false;
        submitButton.innerHTML = '<i class="fas fa-save me-1"></i>Sauvegarder';
    }
});
</script>
@endpush

@push('styles')
<style>
.carrier-logo-sm {
    width: 40px;
    height: 40px;
    object-fit: contain;
    background: #f8f9fa;
    border-radius: 6px;
    padding: 6px;
    border: 1px solid #dee2e6;
}

.btn:disabled {
    opacity: 0.65;
    cursor: not-allowed;
}
</style>
@endpush