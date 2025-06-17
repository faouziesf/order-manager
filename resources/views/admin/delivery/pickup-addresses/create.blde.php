@extends('layouts.admin')

@section('title', 'Nouvelle Adresse d\'Enlèvement')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- En-tête -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.delivery.pickup-addresses.index') }}">Adresses d'enlèvement</a>
                            </li>
                            <li class="breadcrumb-item active">Nouvelle adresse</li>
                        </ol>
                    </nav>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-plus text-primary me-2"></i>
                        Nouvelle Adresse d'Enlèvement
                    </h1>
                    <p class="text-muted mb-0">Ajoutez une nouvelle adresse d'enlèvement pour vos transporteurs</p>
                </div>
                <div>
                    <a href="{{ route('admin.delivery.pickup-addresses.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Retour
                    </a>
                </div>
            </div>

            <!-- Formulaire -->
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-map-marker-alt me-2"></i>
                                Informations de l'adresse
                            </h5>
                        </div>
                        <div class="card-body">
                            <form id="addressForm" method="POST" action="{{ route('admin.delivery.pickup-addresses.store') }}">
                                @csrf
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">
                                                Nom de l'adresse *
                                                <i class="fas fa-info-circle text-muted ms-1" 
                                                   data-bs-toggle="tooltip" 
                                                   title="Nom unique pour identifier cette adresse (ex: Entrepôt Principal, Magasin Centre-Ville)"></i>
                                            </label>
                                            <input type="text" 
                                                   class="form-control @error('name') is-invalid @enderror" 
                                                   id="name" 
                                                   name="name" 
                                                   value="{{ old('name') }}" 
                                                   required
                                                   placeholder="Ex: Entrepôt Principal">
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="contact_name" class="form-label">
                                                Nom du contact *
                                                <i class="fas fa-info-circle text-muted ms-1" 
                                                   data-bs-toggle="tooltip" 
                                                   title="Personne responsable de cette adresse d'enlèvement"></i>
                                            </label>
                                            <input type="text" 
                                                   class="form-control @error('contact_name') is-invalid @enderror" 
                                                   id="contact_name" 
                                                   name="contact_name" 
                                                   value="{{ old('contact_name') }}" 
                                                   required
                                                   placeholder="Ex: Ahmed Ben Ali">
                                            @error('contact_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="address" class="form-label">
                                        Adresse complète *
                                        <i class="fas fa-info-circle text-muted ms-1" 
                                           data-bs-toggle="tooltip" 
                                           title="Adresse détaillée avec numéro, rue, et indications si nécessaire"></i>
                                    </label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" 
                                              id="address" 
                                              name="address" 
                                              rows="3" 
                                              required
                                              placeholder="Ex: 123 Avenue Habib Bourguiba, Bloc A, Étage 2">{{ old('address') }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="city" class="form-label">Ville</label>
                                            <input type="text" 
                                                   class="form-control @error('city') is-invalid @enderror" 
                                                   id="city" 
                                                   name="city" 
                                                   value="{{ old('city') }}"
                                                   placeholder="Ex: Tunis">
                                            @error('city')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="postal_code" class="form-label">Code postal</label>
                                            <input type="text" 
                                                   class="form-control @error('postal_code') is-invalid @enderror" 
                                                   id="postal_code" 
                                                   name="postal_code" 
                                                   value="{{ old('postal_code') }}"
                                                   placeholder="Ex: 1001">
                                            @error('postal_code')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="phone" class="form-label">
                                                Téléphone *
                                                <i class="fas fa-info-circle text-muted ms-1" 
                                                   data-bs-toggle="tooltip" 
                                                   title="Numéro de téléphone pour les coordinations d'enlèvement"></i>
                                            </label>
                                            <input type="tel" 
                                                   class="form-control @error('phone') is-invalid @enderror" 
                                                   id="phone" 
                                                   name="phone" 
                                                   value="{{ old('phone') }}" 
                                                   required
                                                   placeholder="Ex: +216 71 123 456">
                                            @error('phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email (optionnel)</label>
                                            <input type="email" 
                                                   class="form-control @error('email') is-invalid @enderror" 
                                                   id="email" 
                                                   name="email" 
                                                   value="{{ old('email') }}"
                                                   placeholder="Ex: contact@entreprise.tn">
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="is_default" 
                                               name="is_default" 
                                               value="1" 
                                               {{ old('is_default') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_default">
                                            <strong>Définir comme adresse par défaut</strong>
                                            <div class="small text-muted">
                                                Cette adresse sera utilisée automatiquement lors de la création d'enlèvements
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <!-- Aperçu de l'adresse -->
                                <div class="alert alert-info">
                                    <h6 class="alert-heading">
                                        <i class="fas fa-eye me-2"></i>Aperçu de l'adresse
                                    </h6>
                                    <div id="addressPreview" class="mb-0">
                                        <div class="text-muted">L'aperçu s'affichera ici au fur et à mesure que vous remplissez le formulaire</div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.delivery.pickup-addresses.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times me-2"></i>Annuler
                                    </a>
                                    <button type="submit" class="btn btn-primary" id="submitBtn">
                                        <i class="fas fa-save me-2"></i>Enregistrer l'adresse
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    console.log('Formulaire de création d\'adresse chargé');
    
    // Initialiser les tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
    
    // Écouter les changements pour l'aperçu
    $('#name, #contact_name, #address, #city, #postal_code, #phone, #email').on('input', updatePreview);
    
    // Mise à jour initiale
    updatePreview();
    
    // Soumission du formulaire
    $('#addressForm').on('submit', function(e) {
        e.preventDefault();
        submitForm();
    });
});

function updatePreview() {
    const name = $('#name').val();
    const contactName = $('#contact_name').val();
    const address = $('#address').val();
    const city = $('#city').val();
    const postalCode = $('#postal_code').val();
    const phone = $('#phone').val();
    const email = $('#email').val();
    
    let preview = '';
    
    if (name) {
        preview += `<strong>${name}</strong><br>`;
    }
    
    if (contactName) {
        preview += `Contact: ${contactName}<br>`;
    }
    
    if (address) {
        preview += `${address}<br>`;
    }
    
    if (city || postalCode) {
        let cityLine = '';
        if (city) cityLine += city;
        if (postalCode) cityLine += ` ${postalCode}`;
        if (cityLine) preview += `${cityLine}<br>`;
    }
    
    if (phone) {
        preview += `<i class="fas fa-phone me-1"></i>${phone}<br>`;
    }
    
    if (email) {
        preview += `<i class="fas fa-envelope me-1"></i>${email}`;
    }
    
    if (!preview) {
        preview = '<div class="text-muted">L\'aperçu s\'affichera ici au fur et à mesure que vous remplissez le formulaire</div>';
    }
    
    $('#addressPreview').html(preview);
}

function submitForm() {
    const submitBtn = $('#submitBtn');
    const originalHtml = submitBtn.html();
    
    // Désactiver le bouton et afficher le loader
    submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Enregistrement...').prop('disabled', true);
    
    // Supprimer les erreurs précédentes
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').text('');
    
    // Récupérer les données du formulaire
    const formData = new FormData(document.getElementById('addressForm'));
    
    fetch('{{ route("admin.delivery.pickup-addresses.store") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('success', data.message);
            setTimeout(() => {
                window.location.href = '{{ route("admin.delivery.pickup-addresses.index") }}';
            }, 1500);
        } else {
            if (data.errors) {
                displayErrors(data.errors);
            } else {
                showNotification('error', data.message || 'Erreur lors de l\'enregistrement');
            }
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('error', 'Erreur lors de l\'enregistrement de l\'adresse');
    })
    .finally(() => {
        // Restaurer le bouton
        submitBtn.html(originalHtml).prop('disabled', false);
    });
}

function displayErrors(errors) {
    Object.keys(errors).forEach(field => {
        const input = document.getElementById(field);
        if (input) {
            input.classList.add('is-invalid');
            const feedback = input.nextElementSibling;
            if (feedback && feedback.classList.contains('invalid-feedback')) {
                feedback.textContent = errors[field][0];
            }
        }
    });
    
    // Scroll vers la première erreur
    const firstError = document.querySelector('.is-invalid');
    if (firstError) {
        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        firstError.focus();
    }
}

function showNotification(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const icon = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';

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
</script>
@endsection