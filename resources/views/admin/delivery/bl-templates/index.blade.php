@extends('layouts.admin')

@section('title', 'Templates Bill of Lading')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- En-tête -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-file-pdf text-primary me-2"></i>
                        Templates Bill of Lading
                    </h1>
                    <p class="text-muted mb-0">Gérez vos modèles d'étiquettes de livraison</p>
                </div>
                <div>
                    <a href="{{ route('admin.delivery.bl-templates.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Nouveau template
                    </a>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6">
                    <div class="card border-primary">
                        <div class="card-body text-center">
                            <i class="fas fa-file-pdf fa-2x text-primary mb-2"></i>
                            <h4 class="mb-1">{{ $stats['total'] }}</h4>
                            <p class="text-muted mb-0">Total templates</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card border-success">
                        <div class="card-body text-center">
                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                            <h4 class="mb-1">{{ $stats['active'] }}</h4>
                            <p class="text-muted mb-0">Actifs</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card border-warning">
                        <div class="card-body text-center">
                            <i class="fas fa-star fa-2x text-warning mb-2"></i>
                            <h4 class="mb-1">{{ $stats['default'] }}</h4>
                            <p class="text-muted mb-0">Par défaut</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card border-info">
                        <div class="card-body text-center">
                            <i class="fas fa-truck fa-2x text-info mb-2"></i>
                            <h4 class="mb-1">{{ count($stats['by_carrier']) }}</h4>
                            <p class="text-muted mb-0">Transporteurs</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtres et liste -->
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0">
                                <i class="fas fa-list me-2"></i>
                                Liste des templates
                            </h5>
                        </div>
                        <div class="col-auto">
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#filtersModal">
                                    <i class="fas fa-filter me-2"></i>Filtres
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="refreshTemplates()">
                                    <i class="fas fa-sync-alt me-2"></i>Actualiser
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="templatesTable">
                        @include('admin.delivery.bl-templates.table', ['templates' => $templates])
                    </div>

                    @if($templates->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $templates->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal des filtres -->
<div class="modal fade" id="filtersModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-filter me-2"></i>Filtres
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="filtersForm">
                    <div class="mb-3">
                        <label for="filter_carrier" class="form-label">Transporteur</label>
                        <select class="form-select" id="filter_carrier" name="carrier">
                            <option value="">Tous les transporteurs</option>
                            <option value="fparcel">Fparcel</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="filter_status" class="form-label">Statut</label>
                        <select class="form-select" id="filter_status" name="status">
                            <option value="">Tous les statuts</option>
                            <option value="active">Actif</option>
                            <option value="default">Par défaut</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="clearFilters()">Effacer</button>
                <button type="button" class="btn btn-primary" onclick="applyFilters()">Appliquer</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de duplication -->
<div class="modal fade" id="duplicateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-copy me-2"></i>Dupliquer le template
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="duplicateForm">
                <div class="modal-body">
                    <input type="hidden" id="template_to_duplicate" name="template_id">
                    <div class="mb-3">
                        <label for="new_name" class="form-label">Nouveau nom *</label>
                        <input type="text" class="form-control" id="new_name" name="new_name" required>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-copy me-2"></i>Dupliquer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let currentFilters = {};

$(document).ready(function() {
    console.log('Page des templates BL chargée');
    
    // Gestion du formulaire de duplication
    $('#duplicateForm').on('submit', function(e) {
        e.preventDefault();
        duplicateTemplate();
    });
});

// Gestion des filtres
function applyFilters() {
    const form = document.getElementById('filtersForm');
    const formData = new FormData(form);
    
    currentFilters = {};
    for (let [key, value] of formData.entries()) {
        if (value.trim()) {
            currentFilters[key] = value;
        }
    }
    
    loadTemplates();
    bootstrap.Modal.getInstance(document.getElementById('filtersModal')).hide();
}

function clearFilters() {
    document.getElementById('filtersForm').reset();
    currentFilters = {};
    loadTemplates();
}

function loadTemplates() {
    const params = new URLSearchParams(currentFilters);
    
    fetch(`{{ route('admin.delivery.bl-templates.index') }}?${params}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.html) {
            document.getElementById('templatesTable').innerHTML = data.html;
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('error', 'Erreur lors du chargement des templates');
    });
}

function refreshTemplates() {
    loadTemplates();
    showNotification('info', 'Liste des templates actualisée');
}

// Actions sur les templates
function previewTemplate(templateId) {
    window.open(`/admin/delivery/bl-templates/${templateId}/preview`, '_blank');
}

function duplicateTemplateModal(templateId, templateName) {
    document.getElementById('template_to_duplicate').value = templateId;
    document.getElementById('new_name').value = templateName + ' (Copie)';
    
    new bootstrap.Modal(document.getElementById('duplicateModal')).show();
}

function duplicateTemplate() {
    const templateId = document.getElementById('template_to_duplicate').value;
    const newName = document.getElementById('new_name').value;
    
    fetch(`/admin/delivery/bl-templates/${templateId}/duplicate`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            new_name: newName
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('success', data.message);
            bootstrap.Modal.getInstance(document.getElementById('duplicateModal')).hide();
            
            if (data.redirect_url) {
                setTimeout(() => {
                    window.location.href = data.redirect_url;
                }, 1500);
            } else {
                setTimeout(() => location.reload(), 1500);
            }
        } else {
            if (data.errors) {
                displayErrors(data.errors);
            } else {
                showNotification('error', data.message);
            }
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('error', 'Erreur lors de la duplication');
    });
}

function setAsDefault(templateId) {
    if (!confirm('Voulez-vous définir ce template comme template par défaut ?')) {
        return;
    }
    
    fetch(`/admin/delivery/bl-templates/${templateId}/set-default`, {
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

function deleteTemplate(templateId) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer ce template ? Cette action est irréversible.')) {
        return;
    }
    
    fetch(`/admin/delivery/bl-templates/${templateId}`, {
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

function displayErrors(errors) {
    $('.form-control').removeClass('is-invalid');
    $('.invalid-feedback').text('');
    
    Object.keys(errors).forEach(field => {
        const input = document.getElementById(field);
        const feedback = input?.nextElementSibling;
        
        if (input && feedback && feedback.classList.contains('invalid-feedback')) {
            input.classList.add('is-invalid');
            feedback.textContent = errors[field][0];
        }
    });
}

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
</script>
@endsection