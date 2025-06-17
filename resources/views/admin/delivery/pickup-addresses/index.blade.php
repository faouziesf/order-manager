@extends('layouts.admin')

@section('title', 'Adresses d\'enlèvement')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- En-tête -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-map-marker-alt text-primary me-2"></i>
                        Adresses d'enlèvement
                    </h1>
                    <p class="text-muted mb-0">Gérez vos adresses d'enlèvement pour les transporteurs</p>
                </div>
                <div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createAddressModal">
                        <i class="fas fa-plus me-2"></i>Nouvelle adresse
                    </button>
                </div>
            </div>

            <!-- Statistiques rapides -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card border-primary">
                        <div class="card-body text-center">
                            <i class="fas fa-map-marker-alt fa-2x text-primary mb-2"></i>
                            <h4 class="mb-1">{{ $addresses->total() }}</h4>
                            <p class="text-muted mb-0">Total adresses</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-success">
                        <div class="card-body text-center">
                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                            <h4 class="mb-1">{{ $addresses->where('is_active', true)->count() }}</h4>
                            <p class="text-muted mb-0">Actives</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-warning">
                        <div class="card-body text-center">
                            <i class="fas fa-star fa-2x text-warning mb-2"></i>
                            <h4 class="mb-1">{{ $addresses->where('is_default', true)->count() }}</h4>
                            <p class="text-muted mb-0">Par défaut</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-secondary">
                        <div class="card-body text-center">
                            <i class="fas fa-truck fa-2x text-secondary mb-2"></i>
                            <h4 class="mb-1">{{ $addresses->sum(function($addr) { return $addr->pickups()->count(); }) }}</h4>
                            <p class="text-muted mb-0">Enlèvements</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Liste des adresses -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        Liste des adresses
                    </h5>
                </div>
                <div class="card-body">
                    @if($addresses->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nom</th>
                                        <th>Contact</th>
                                        <th>Adresse</th>
                                        <th>Téléphone</th>
                                        <th>Email</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($addresses as $address)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <strong>{{ $address->name }}</strong>
                                                    @if($address->is_default)
                                                        <span class="badge bg-warning ms-2">
                                                            <i class="fas fa-star me-1"></i>Défaut
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>{{ $address->contact_name }}</td>
                                            <td>
                                                <div>
                                                    {{ $address->address }}
                                                    @if($address->city || $address->postal_code)
                                                        <br>
                                                        <small class="text-muted">
                                                            {{ $address->city }}
                                                            @if($address->postal_code)
                                                                {{ $address->postal_code }}
                                                            @endif
                                                        </small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <span class="font-monospace">{{ $address->phone }}</span>
                                            </td>
                                            <td>
                                                @if($address->email)
                                                    <a href="mailto:{{ $address->email }}">{{ $address->email }}</a>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($address->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    @if(!$address->is_default && $address->is_active)
                                                        <button type="button" class="btn btn-outline-warning" 
                                                                onclick="setAsDefault({{ $address->id }})"
                                                                title="Définir par défaut">
                                                            <i class="fas fa-star"></i>
                                                        </button>
                                                    @endif
                                                    
                                                    <button type="button" class="btn btn-outline-primary"
                                                            onclick="editAddress({{ $address->id }})"
                                                            title="Modifier">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    
                                                    <button type="button" class="btn btn-outline-{{ $address->is_active ? 'secondary' : 'success' }}"
                                                            onclick="toggleStatus({{ $address->id }})"
                                                            title="{{ $address->is_active ? 'Désactiver' : 'Activer' }}">
                                                        <i class="fas fa-{{ $address->is_active ? 'eye-slash' : 'eye' }}"></i>
                                                    </button>
                                                    
                                                    @if($address->canBeDeleted())
                                                        <button type="button" class="btn btn-outline-danger"
                                                                onclick="deleteAddress({{ $address->id }})"
                                                                title="Supprimer">
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

                        @if($addresses->hasPages())
                            <div class="d-flex justify-content-center mt-4">
                                {{ $addresses->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-map-marker-alt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Aucune adresse d'enlèvement</h5>
                            <p class="text-muted">Créez votre première adresse d'enlèvement pour commencer</p>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createAddressModal">
                                <i class="fas fa-plus me-2"></i>Créer une adresse
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Création/Édition -->
<div class="modal fade" id="createAddressModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i>
                    <span id="modalTitle">Nouvelle adresse d'enlèvement</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addressForm">
                <div class="modal-body">
                    <input type="hidden" id="addressId" name="address_id">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Nom de l'adresse *</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="contact_name" class="form-label">Nom du contact *</label>
                        <input type="text" class="form-control" id="contact_name" name="contact_name" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Adresse complète *</label>
                        <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="city" class="form-label">Ville</label>
                                <input type="text" class="form-control" id="city" name="city">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="postal_code" class="form-label">Code postal</label>
                                <input type="text" class="form-control" id="postal_code" name="postal_code">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label">Téléphone *</label>
                        <input type="tel" class="form-control" id="phone" name="phone" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email">
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_default" name="is_default">
                            <label class="form-check-label" for="is_default">
                                Définir comme adresse par défaut
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save me-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let isEditing = false;
let editingId = null;

$(document).ready(function() {
    console.log('Page des adresses d\'enlèvement chargée');
    
    // Réinitialiser le formulaire à la fermeture du modal
    $('#createAddressModal').on('hidden.bs.modal', function() {
        resetForm();
    });
    
    // Soumission du formulaire
    $('#addressForm').on('submit', function(e) {
        e.preventDefault();
        saveAddress();
    });
});

function resetForm() {
    isEditing = false;
    editingId = null;
    document.getElementById('addressForm').reset();
    document.getElementById('addressId').value = '';
    document.getElementById('modalTitle').textContent = 'Nouvelle adresse d\'enlèvement';
    document.getElementById('submitBtn').innerHTML = '<i class="fas fa-save me-2"></i>Enregistrer';
    
    // Supprimer les classes d'erreur
    $('.form-control').removeClass('is-invalid');
    $('.invalid-feedback').text('');
}

function editAddress(addressId) {
    // Vous devriez faire un appel AJAX pour récupérer les données de l'adresse
    // Pour l'instant, simulation
    isEditing = true;
    editingId = addressId;
    
    document.getElementById('modalTitle').textContent = 'Modifier l\'adresse d\'enlèvement';
    document.getElementById('submitBtn').innerHTML = '<i class="fas fa-save me-2"></i>Mettre à jour';
    document.getElementById('addressId').value = addressId;
    
    // Ici vous devriez récupérer et remplir les données
    // fetch(`/admin/delivery/pickup-addresses/${addressId}/edit`)...
    
    new bootstrap.Modal(document.getElementById('createAddressModal')).show();
}

function saveAddress() {
    const formData = new FormData(document.getElementById('addressForm'));
    const url = isEditing 
        ? `/admin/delivery/pickup-addresses/${editingId}` 
        : '{{ route("admin.delivery.pickup-addresses.store") }}';
    const method = isEditing ? 'PUT' : 'POST';
    
    // Ajouter le token CSRF
    if (isEditing) {
        formData.append('_method', 'PUT');
    }
    
    const submitBtn = document.getElementById('submitBtn');
    const originalHtml = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Enregistrement...';
    submitBtn.disabled = true;
    
    fetch(url, {
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
            bootstrap.Modal.getInstance(document.getElementById('createAddressModal')).hide();
            setTimeout(() => location.reload(), 1500);
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
        showNotification('error', 'Erreur lors de l\'enregistrement');
    })
    .finally(() => {
        submitBtn.innerHTML = originalHtml;
        submitBtn.disabled = false;
    });
}

function displayErrors(errors) {
    // Supprimer les erreurs précédentes
    $('.form-control').removeClass('is-invalid');
    $('.invalid-feedback').text('');
    
    // Afficher les nouvelles erreurs
    Object.keys(errors).forEach(field => {
        const input = document.getElementById(field);
        const feedback = input.nextElementSibling;
        
        if (input && feedback && feedback.classList.contains('invalid-feedback')) {
            input.classList.add('is-invalid');
            feedback.textContent = errors[field][0];
        }
    });
}

function setAsDefault(addressId) {
    if (!confirm('Voulez-vous définir cette adresse comme adresse par défaut ?')) {
        return;
    }
    
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
        console.error('Erreur:', error);
        showNotification('error', 'Erreur lors de la mise à jour');
    });
}

function toggleStatus(addressId) {
    fetch(`/admin/delivery/pickup-addresses/${addressId}/toggle`, {
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
        console.error('Erreur:', error);
        showNotification('error', 'Erreur lors de la mise à jour');
    });
}

function deleteAddress(addressId) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cette adresse ? Cette action est irréversible.')) {
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
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification('error', data.message);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('error', 'Erreur lors de la suppression');
    });
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