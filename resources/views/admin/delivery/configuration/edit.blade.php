@extends('layouts.admin')

@section('title', 'Modifier Configuration Jax Delivery')

@push('styles')
<style>
.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.alert-info {
    background-color: #e3f2fd;
    border-color: #bbdefb;
    color: #1976d2;
}

.alert-warning {
    background-color: #fff3cd;
    border-color: #ffeaa7;
    color: #856404;
}

.card {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    border: none;
}

.card-header {
    background-color: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}

.was-validated .form-control:invalid {
    border-color: #dc3545;
}

.was-validated .form-control:valid {
    border-color: #28a745;
}

.invalid-feedback {
    display: block;
}

.info-item {
    padding: 0.5rem 0;
    border-bottom: 1px solid #e9ecef;
}

.info-item:last-child {
    border-bottom: none;
}

.status-badge {
    font-size: 0.875rem;
    padding: 0.375rem 0.5rem;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="d-flex align-items-center">
                <a href="{{ route('admin.delivery.configuration') }}" class="btn btn-outline-secondary btn-sm mr-3">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
                <div>
                    <h1 class="h3 mb-0">Modifier Configuration Jax Delivery</h1>
                    <p class="text-muted mb-0">{{ $config->integration_name }} (ID: {{ $config->id }})</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-right">
            <span class="badge {{ $config->status_info['badge_class'] }} status-badge">
                {{ $config->status_info['badge_text'] }}
            </span>
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

    <div class="row">
        <!-- Informations actuelles -->
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle me-2"></i>Informations Actuelles
                    </h6>
                </div>
                <div class="card-body">
                    <div class="info-item">
                        <strong>Nom:</strong><br>
                        <span class="text-muted">{{ $config->integration_name }}</span>
                    </div>
                    <div class="info-item">
                        <strong>Environnement:</strong><br>
                        <span class="badge badge-{{ $config->environment === 'prod' ? 'success' : 'warning' }}">
                            {{ ucfirst($config->environment) }}
                        </span>
                    </div>
                    <div class="info-item">
                        <strong>Statut:</strong><br>
                        <span class="badge {{ $config->status_info['badge_class'] }}">
                            {{ $config->status_info['badge_text'] }}
                        </span>
                        @if(!$config->is_active)
                            <br><small class="text-muted">Configuration désactivée</small>
                        @endif
                    </div>
                    <div class="info-item">
                        <strong>Token expire:</strong><br>
                        @if($config->expires_at)
                            <span class="text-muted">{{ $config->expires_at->format('d/m/Y H:i') }}</span>
                            <br><small class="text-muted">({{ $config->expires_at->diffForHumans() }})</small>
                        @else
                            <span class="text-muted">Non défini</span>
                        @endif
                    </div>
                    <div class="info-item">
                        <strong>Créée le:</strong><br>
                        <span class="text-muted">{{ $config->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="info-item">
                        <strong>Dernière MAJ:</strong><br>
                        <span class="text-muted">{{ $config->updated_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>

            <!-- Actions rapides -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt me-2"></i>Actions Rapides
                    </h6>
                </div>
                <div class="card-body">
                    <button type="button" class="btn btn-info btn-block mb-2" onclick="testConnection()">
                        <i class="fas fa-vial"></i> Tester la Connexion
                    </button>
                    <button type="button" class="btn btn-outline-{{ $config->is_active ? 'warning' : 'success' }} btn-block mb-2" 
                            onclick="toggleStatus()">
                        <i class="fas fa-{{ $config->is_active ? 'pause' : 'play' }}"></i> 
                        {{ $config->is_active ? 'Désactiver' : 'Activer' }}
                    </button>
                    @if($config->canBeDeleted())
                        <button type="button" class="btn btn-outline-danger btn-block" onclick="deleteConfig()">
                            <i class="fas fa-trash"></i> Supprimer
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Formulaire d'édition -->
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-edit me-2"></i>Modifier la Configuration
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Attention:</strong> Les modifications de cette configuration affecteront tous les futurs enlèvements.
                        Les enlèvements en cours ne seront pas impactés.
                    </div>

                    <form method="POST" action="{{ route('admin.delivery.configuration.update', $config) }}" id="editConfigForm" novalidate>
                        @csrf
                        @method('PATCH')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="integration_name">Nom d'Intégration <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('integration_name') is-invalid @enderror" 
                                           id="integration_name" 
                                           name="integration_name" 
                                           value="{{ old('integration_name', $config->integration_name) }}"
                                           placeholder="Ex: Mon Magasin - Jax" 
                                           required>
                                    <small class="form-text text-muted">
                                        Nom pour identifier cette configuration dans votre liste
                                    </small>
                                    @error('integration_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="environment">Environnement <span class="text-danger">*</span></label>
                                    <select class="form-control @error('environment') is-invalid @enderror" 
                                            id="environment" 
                                            name="environment" 
                                            required>
                                        <option value="">Sélectionnez un environnement</option>
                                        <option value="prod" {{ old('environment', $config->environment) === 'prod' ? 'selected' : '' }}>Production</option>
                                        <option value="test" {{ old('environment', $config->environment) === 'test' ? 'selected' : '' }}>Test</option>
                                    </select>
                                    <small class="form-text text-muted">
                                        Utilisez "Test" pour vos essais, "Production" pour vos vraies commandes
                                    </small>
                                    @error('environment')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="token">Token d'API Jax Delivery</label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control @error('token') is-invalid @enderror" 
                                       id="token" 
                                       name="token" 
                                       value="{{ old('token') }}"
                                       placeholder="Nouveau token (laisser vide pour ne pas changer)">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" onclick="toggleTokenVisibility()">
                                        <i class="fas fa-eye" id="tokenToggleIcon"></i>
                                    </button>
                                </div>
                            </div>
                            <small class="form-text text-muted">
                                Laissez vide si vous ne voulez pas changer le token actuel. 
                                Si vous fournissez un nouveau token, il remplacera l'ancien.
                            </small>
                            @error('token')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-6">
                                <button type="button" class="btn btn-secondary btn-block" onclick="goBack()">
                                    <i class="fas fa-times me-2"></i>Annuler
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-primary btn-block" id="submitBtn">
                                    <i class="fas fa-save me-2"></i>Mettre à Jour
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Informations sur les enlèvements liés -->
            @if($config->pickups()->exists())
                <div class="card shadow mt-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-warning">
                            <i class="fas fa-warehouse me-2"></i>Enlèvements Liés
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-2">Cette configuration est utilisée par :</p>
                        <ul class="mb-3">
                            <li><strong>{{ $config->pickups()->count() }}</strong> enlèvement(s) au total</li>
                            <li><strong>{{ $config->pickups()->where('status', 'draft')->count() }}</strong> en brouillon</li>
                            <li><strong>{{ $config->pickups()->where('status', 'validated')->count() }}</strong> validé(s)</li>
                            <li><strong>{{ $config->pickups()->where('status', 'picked_up')->count() }}</strong> récupéré(s)</li>
                        </ul>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle"></i>
                            Les modifications n'affecteront que les nouveaux enlèvements. 
                            Les enlèvements existants continueront de fonctionner avec les anciens paramètres.
                        </div>
                    </div>
                </div>
            @endif
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
            success: function(msg) { alert('Succès: ' + msg); },
            error: function(msg) { alert('Erreur: ' + msg); },
            warning: function(msg) { alert('Attention: ' + msg); }
        };
    }

    // Configuration de toastr si disponible
    if (typeof toastr !== 'undefined' && toastr.options) {
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: "toast-top-right",
            timeOut: "5000"
        };
    }

    // Validation en temps réel
    $('#integration_name').on('input', function() {
        validateField(this);
    });

    $('#token').on('input', function() {
        validateField(this);
    });

    $('#environment').on('change', function() {
        validateField(this);
    });

    // Soumission du formulaire
    $('#editConfigForm').on('submit', function(e) {
        e.preventDefault();
        
        // Validation
        if (!validateForm()) {
            toastr.error('Veuillez corriger les erreurs dans le formulaire');
            return;
        }
        
        const form = $(this);
        const submitBtn = $('#submitBtn');
        const originalText = submitBtn.html();
        
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Mise à jour...').prop('disabled', true);
        
        // Soumettre le formulaire
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
                setTimeout(() => {
                    window.location.href = '{{ route("admin.delivery.configuration") }}';
                }, 1500);
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    // Erreurs de validation
                    const errors = xhr.responseJSON.errors;
                    displayValidationErrors(errors);
                    let errorMessage = 'Erreurs de validation:\n';
                    Object.keys(errors).forEach(key => {
                        errorMessage += '• ' + errors[key].join('\n• ') + '\n';
                    });
                    toastr.error(errorMessage);
                } else {
                    const message = xhr.responseJSON?.message || 'Erreur lors de la mise à jour';
                    toastr.error(message);
                }
            },
            complete: function() {
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });

    // Fermer les alerts automatiquement
    $('.alert').each(function() {
        const alert = $(this);
        setTimeout(() => {
            alert.fadeOut();
        }, 5000);
    });
});

function validateField(field) {
    const $field = $(field);
    const value = $field.val().trim();
    
    $field.removeClass('is-valid is-invalid');
    
    if (field.hasAttribute('required') && !value) {
        $field.addClass('is-invalid');
        return false;
    }
    
    // Validation spécifique
    if (field.id === 'integration_name' && value.length < 3) {
        $field.addClass('is-invalid');
        return false;
    }
    
    if (field.id === 'token' && value.length > 0 && value.length < 10) {
        $field.addClass('is-invalid');
        return false;
    }
    
    $field.addClass('is-valid');
    return true;
}

function validateForm() {
    let isValid = true;
    
    $('#editConfigForm input[required], #editConfigForm select[required]').each(function() {
        if (!validateField(this)) {
            isValid = false;
        }
    });
    
    return isValid;
}

function displayValidationErrors(errors) {
    // Nettoyer les erreurs précédentes
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').remove();
    
    // Afficher les nouvelles erreurs
    Object.keys(errors).forEach(field => {
        const $field = $(`#${field}`);
        $field.addClass('is-invalid');
        
        const errorMessages = errors[field];
        errorMessages.forEach(message => {
            $field.after(`<div class="invalid-feedback">${message}</div>`);
        });
    });
}

function toggleTokenVisibility() {
    const tokenInput = document.getElementById('token');
    const toggleIcon = document.getElementById('tokenToggleIcon');
    
    if (tokenInput.type === 'password') {
        tokenInput.type = 'text';
        toggleIcon.className = 'fas fa-eye-slash';
    } else {
        tokenInput.type = 'password';
        toggleIcon.className = 'fas fa-eye';
    }
}

function testConnection() {
    const btn = event.target;
    const originalHtml = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Test...';
    btn.disabled = true;
    
    fetch('{{ route("admin.delivery.configuration.test", $config) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            toastr.success(data.message);
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

function toggleStatus() {
    const btn = event.target;
    const originalHtml = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btn.disabled = true;
    
    fetch('{{ route("admin.delivery.configuration.toggle", $config) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            toastr.success(data.message);
            setTimeout(() => location.reload(), 1500);
        } else {
            toastr.error(data.message);
        }
    })
    .catch(error => {
        toastr.error('Erreur lors de la modification');
        console.error('Error:', error);
    })
    .finally(() => {
        btn.innerHTML = originalHtml;
        btn.disabled = false;
    });
}

function deleteConfig() {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette configuration ?\n\nCette action est irréversible et supprimera également tous les enlèvements associés.')) {
        fetch('{{ route("admin.delivery.configuration.delete", $config) }}', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                toastr.success(data.message);
                setTimeout(() => {
                    window.location.href = '{{ route("admin.delivery.configuration") }}';
                }, 1500);
            } else {
                toastr.error(data.message);
            }
        })
        .catch(error => {
            toastr.error('Erreur lors de la suppression');
            console.error('Error:', error);
        });
    }
}

function goBack() {
    if (confirm('Êtes-vous sûr de vouloir quitter ? Les modifications non sauvegardées seront perdues.')) {
        window.location.href = '{{ route("admin.delivery.configuration") }}';
    }
}
</script>
@endpush