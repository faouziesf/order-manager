@extends('layouts.admin')

@section('title', 'Nouvelle Configuration Jax Delivery')

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
                    <h1 class="h3 mb-0">Nouvelle Configuration Jax Delivery</h1>
                    <p class="text-muted mb-0">Configurez votre connexion avec l'API Jax Delivery</p>
                </div>
            </div>
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

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-plus me-2"></i>Nouvelle Configuration Jax Delivery
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Jax Delivery</strong> utilise un token d'API pour l'authentification. 
                        Récupérez votre token depuis votre espace client Jax Delivery.
                    </div>

                    <form method="POST" action="{{ route('admin.delivery.configuration.store') }}" id="createConfigForm" novalidate>
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="integration_name">Nom d'Intégration <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('integration_name') is-invalid @enderror" 
                                           id="integration_name" 
                                           name="integration_name" 
                                           value="{{ old('integration_name') }}"
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
                                        <option value="prod" {{ old('environment') === 'prod' ? 'selected' : '' }}>Production</option>
                                        <option value="test" {{ old('environment') === 'test' ? 'selected' : '' }}>Test</option>
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
                            <label for="token">Token d'API Jax Delivery <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control @error('token') is-invalid @enderror" 
                                       id="token" 
                                       name="token" 
                                       value="{{ old('token') }}"
                                       placeholder="Votre token Jax Delivery" 
                                       required>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" onclick="toggleTokenVisibility()">
                                        <i class="fas fa-eye" id="tokenToggleIcon"></i>
                                    </button>
                                </div>
                            </div>
                            <small class="form-text text-muted">
                                Récupérez ce token depuis votre espace Jax Delivery. Il sera stocké de manière sécurisée.
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
                                    <i class="fas fa-save me-2"></i>Enregistrer la Configuration
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Informations supplémentaires -->
            <div class="card shadow mt-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-question-circle me-2"></i>Comment obtenir votre token Jax Delivery ?
                    </h6>
                </div>
                <div class="card-body">
                    <ol class="mb-0">
                        <li class="mb-2">Connectez-vous à votre espace client Jax Delivery</li>
                        <li class="mb-2">Accédez à la section "API" ou "Intégrations"</li>
                        <li class="mb-2">Générez un nouveau token d'API si nécessaire</li>
                        <li class="mb-2">Copiez le token et collez-le dans le champ ci-dessus</li>
                        <li class="mb-0">Testez la connexion après avoir enregistré</li>
                    </ol>
                </div>
            </div>
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
                alert('Succès: ' + msg); 
            },
            error: function(msg) { 
                alert('Erreur: ' + msg); 
            },
            warning: function(msg) { 
                alert('Attention: ' + msg); 
            }
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
    $('#createConfigForm').on('submit', function(e) {
        e.preventDefault();
        
        // Validation
        if (!validateForm()) {
            toastr.error('Veuillez corriger les erreurs dans le formulaire');
            return;
        }
        
        const form = $(this);
        const submitBtn = $('#submitBtn');
        const originalText = submitBtn.html();
        
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Enregistrement...').prop('disabled', true);
        
        // Soumettre le formulaire
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
                    const message = xhr.responseJSON?.message || 'Erreur lors de la création de la configuration';
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
    
    if (field.id === 'token' && value.length < 10) {
        $field.addClass('is-invalid');
        return false;
    }
    
    $field.addClass('is-valid');
    return true;
}

function validateForm() {
    let isValid = true;
    
    $('#createConfigForm input[required], #createConfigForm select[required]').each(function() {
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

function goBack() {
    if (confirm('Êtes-vous sûr de vouloir quitter ? Les modifications non sauvegardées seront perdues.')) {
        window.location.href = '{{ route("admin.delivery.configuration") }}';
    }
}
</script>
@endpush