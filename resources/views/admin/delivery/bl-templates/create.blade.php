@extends('layouts.admin')

@section('title', 'Nouveau Template BL')

@section('css')
<style>
.template-designer {
    min-height: 600px;
    border: 2px dashed #dee2e6;
    background: #f8f9fa;
    position: relative;
    overflow: auto;
}

.field-item {
    position: absolute;
    padding: 8px;
    border: 1px solid #007bff;
    background: rgba(0, 123, 255, 0.1);
    cursor: move;
    user-select: none;
    min-width: 100px;
    min-height: 30px;
}

.field-item:hover {
    border-color: #0056b3;
    background: rgba(0, 123, 255, 0.2);
}

.field-item.selected {
    border-color: #dc3545;
    background: rgba(220, 53, 69, 0.1);
}

.field-item .field-label {
    font-weight: bold;
    font-size: 0.8rem;
    color: #495057;
}

.field-item .field-content {
    font-size: 0.9rem;
    margin-top: 4px;
}

.field-properties {
    max-height: 400px;
    overflow-y: auto;
}

.available-fields {
    max-height: 300px;
    overflow-y: auto;
}

.field-drag-item {
    padding: 8px 12px;
    margin: 4px 0;
    background: #e9ecef;
    border: 1px solid #ced4da;
    border-radius: 4px;
    cursor: grab;
    user-select: none;
}

.field-drag-item:hover {
    background: #dee2e6;
}

.field-drag-item:active {
    cursor: grabbing;
}

.template-ruler {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 20px;
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.template-ruler-v {
    position: absolute;
    top: 20px;
    left: 0;
    width: 20px;
    height: calc(100% - 20px);
    background: #f8f9fa;
    border-right: 1px solid #dee2e6;
}

.design-area {
    margin-left: 20px;
    margin-top: 20px;
    width: calc(100% - 20px);
    height: calc(100% - 20px);
    background: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
</style>
@endsection

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
                                <a href="{{ route('admin.delivery.bl-templates.index') }}">Templates BL</a>
                            </li>
                            <li class="breadcrumb-item active">Nouveau template</li>
                        </ol>
                    </nav>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-plus text-primary me-2"></i>
                        Nouveau Template BL
                    </h1>
                    <p class="text-muted mb-0">Créez un nouveau modèle d'étiquette de livraison</p>
                </div>
                <div>
                    <a href="{{ route('admin.delivery.bl-templates.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Retour
                    </a>
                </div>
            </div>

            <form id="templateForm" enctype="multipart/form-data">
                <div class="row">
                    <!-- Paramètres du template -->
                    <div class="col-lg-3">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-cog me-2"></i>
                                    Paramètres
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="template_name" class="form-label">Nom du template *</label>
                                    <input type="text" class="form-control" id="template_name" name="template_name" required>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="mb-3">
                                    <label for="carrier_slug" class="form-label">Transporteur</label>
                                    <select class="form-select" id="carrier_slug" name="carrier_slug">
                                        <option value="">Universel (tous transporteurs)</option>
                                        @foreach($carriers as $slug => $name)
                                            <option value="{{ $slug }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_default" name="is_default">
                                        <label class="form-check-label" for="is_default">
                                            Template par défaut
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="logo" class="form-label">Logo (optionnel)</label>
                                    <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                                    <div class="form-text">Formats: JPG, PNG, GIF, SVG (max 2MB)</div>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Champs disponibles -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-list me-2"></i>
                                    Champs disponibles
                                </h6>
                            </div>
                            <div class="card-body p-2">
                                <div class="available-fields">
                                    @foreach($availableFields as $group => $fields)
                                        <div class="mb-3">
                                            <h6 class="text-muted mb-2">{{ $group }}</h6>
                                            @foreach($fields as $key => $field)
                                                <div class="field-drag-item" 
                                                     draggable="true"
                                                     data-field-key="{{ $key }}"
                                                     data-field-label="{{ $field['label'] }}"
                                                     data-field-type="{{ $field['type'] }}">
                                                    <i class="fas fa-{{ $field['icon'] ?? 'text' }} me-2"></i>
                                                    {{ $field['label'] }}
                                                </div>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Propriétés du champ sélectionné -->
                        <div class="card" id="fieldPropertiesCard" style="display: none;">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-edit me-2"></i>
                                    Propriétés
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="field-properties" id="fieldProperties">
                                    <!-- Les propriétés seront générées dynamiquement -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Concepteur de template -->
                    <div class="col-lg-9">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-paint-brush me-2"></i>
                                    Concepteur de template
                                </h5>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-secondary" onclick="clearDesign()">
                                        <i class="fas fa-trash me-1"></i>Effacer
                                    </button>
                                    <button type="button" class="btn btn-outline-primary" onclick="previewTemplate()">
                                        <i class="fas fa-eye me-1"></i>Aperçu
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="template-designer" id="templateDesigner" ondrop="dropField(event)" ondragover="allowDrop(event)">
                                    <div class="template-ruler"></div>
                                    <div class="template-ruler-v"></div>
                                    <div class="design-area" id="designArea">
                                        <!-- Les champs seront ajoutés ici -->
                                        <div class="text-center text-muted p-5">
                                            <i class="fas fa-mouse-pointer fa-2x mb-3"></i>
                                            <h5>Glissez-déposez les champs ici</h5>
                                            <p>Faites glisser les champs depuis la liste de gauche pour créer votre template</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Boutons d'action -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.delivery.bl-templates.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Annuler
                            </a>
                            <button type="button" class="btn btn-outline-primary" onclick="previewTemplate()">
                                <i class="fas fa-eye me-2"></i>Aperçu
                            </button>
                            <button type="submit" class="btn btn-primary" id="saveBtn">
                                <i class="fas fa-save me-2"></i>Enregistrer
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Input caché pour la configuration -->
                <input type="hidden" id="layout_config" name="layout_config">
            </form>
        </div>
    </div>
</div>

<!-- Modal d'aperçu -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-eye me-2"></i>
                    Aperçu du template
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="previewContent" class="border p-3" style="background: white; min-height: 400px;">
                    <!-- L'aperçu sera généré ici -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let selectedField = null;
let fieldCounter = 0;
let templateConfig = {
    fields: {},
    logo: {
        enabled: false,
        position: 'top-left',
        width: 100,
        height: 50
    },
    page: {
        width: 210, // mm
        height: 297, // mm
        margin: 10
    }
};

// Données d'exemple pour l'aperçu
const sampleData = {
    customer: {
        name: 'Ahmed Ben Ali',
        phone: '+216 98 123 456',
        address: '123 Avenue Habib Bourguiba, Tunis 1001',
        city: 'Tunis',
        email: 'ahmed@example.com'
    },
    sender: {
        name: 'Mon Entreprise',
        address: '456 Rue de la Liberté, Ariana 2080',
        phone: '+216 71 123 456'
    },
    shipment: {
        tracking_number: 'TN' + new Date().toISOString().slice(0,10).replace(/-/g,'') + '001',
        order_number: 'CMD-' + new Date().toISOString().slice(0,10).replace(/-/g,'') + '-001',
        return_barcode: 'RET_' + new Date().toISOString().slice(0,10).replace(/-/g,'') + '_001'
    },
    financial: {
        total_amount: '89.500',
        cod_amount: '89.500'
    },
    dates: {
        shipping_date: new Date().toLocaleDateString('fr-FR'),
        pickup_date: new Date().toLocaleDateString('fr-FR')
    }
};

$(document).ready(function() {
    console.log('Concepteur de template BL chargé');
    
    initializeDragAndDrop();
    
    // Soumission du formulaire
    $('#templateForm').on('submit', function(e) {
        e.preventDefault();
        saveTemplate();
    });
    
    // Prévisualisation des images uploadées
    $('#logo').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                templateConfig.logo.enabled = true;
                templateConfig.logo.path = e.target.result; // Base64 pour l'aperçu
                updateLogoPreview();
            };
            reader.readAsDataURL(file);
        }
    });
});

function initializeDragAndDrop() {
    // Initialiser le drag depuis les champs disponibles
    const dragItems = document.querySelectorAll('.field-drag-item');
    dragItems.forEach(item => {
        item.addEventListener('dragstart', function(e) {
            e.dataTransfer.setData('text/plain', JSON.stringify({
                key: this.dataset.fieldKey,
                label: this.dataset.fieldLabel,
                type: this.dataset.fieldType
            }));
        });
    });
}

function allowDrop(e) {
    e.preventDefault();
}

function dropField(e) {
    e.preventDefault();
    
    try {
        const fieldData = JSON.parse(e.dataTransfer.getData('text/plain'));
        const designArea = document.getElementById('designArea');
        const rect = designArea.getBoundingClientRect();
        
        // Calculer la position relative
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        
        addFieldToDesign(fieldData, x, y);
    } catch (error) {
        console.error('Erreur lors du drop:', error);
    }
}

function addFieldToDesign(fieldData, x, y) {
    fieldCounter++;
    const fieldId = `field_${fieldCounter}`;
    
    // Créer l'élément visuel
    const fieldElement = document.createElement('div');
    fieldElement.className = 'field-item';
    fieldElement.id = fieldId;
    fieldElement.style.left = x + 'px';
    fieldElement.style.top = y + 'px';
    fieldElement.style.width = '150px';
    fieldElement.style.height = '40px';
    
    fieldElement.innerHTML = `
        <div class="field-label">${fieldData.label}</div>
        <div class="field-content">${getSampleValue(fieldData.key)}</div>
        <button type="button" class="btn btn-sm btn-outline-danger position-absolute" 
                style="top: -5px; right: -5px; width: 20px; height: 20px; padding: 0; font-size: 10px;"
                onclick="removeField('${fieldId}')">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    // Ajouter les événements
    fieldElement.addEventListener('click', () => selectField(fieldId));
    makeFieldDraggable(fieldElement);
    
    // Ajouter au design
    document.getElementById('designArea').appendChild(fieldElement);
    
    // Sauvegarder dans la configuration
    templateConfig.fields[fieldId] = {
        key: fieldData.key,
        label: fieldData.label,
        type: fieldData.type,
        x: x,
        y: y,
        width: 150,
        height: 40,
        fontSize: 12,
        fontWeight: 'normal',
        color: '#000000',
        align: 'left'
    };
    
    // Sélectionner automatiquement
    selectField(fieldId);
}

function makeFieldDraggable(element) {
    let isDragging = false;
    let startX, startY, initialX, initialY;
    
    element.addEventListener('mousedown', function(e) {
        if (e.target.tagName === 'BUTTON' || e.target.tagName === 'I') return;
        
        isDragging = true;
        startX = e.clientX;
        startY = e.clientY;
        initialX = parseInt(element.style.left);
        initialY = parseInt(element.style.top);
        
        document.addEventListener('mousemove', drag);
        document.addEventListener('mouseup', stopDrag);
        e.preventDefault();
    });
    
    function drag(e) {
        if (!isDragging) return;
        
        const newX = initialX + (e.clientX - startX);
        const newY = initialY + (e.clientY - startY);
        
        element.style.left = Math.max(0, newX) + 'px';
        element.style.top = Math.max(0, newY) + 'px';
        
        // Mettre à jour la configuration
        if (templateConfig.fields[element.id]) {
            templateConfig.fields[element.id].x = newX;
            templateConfig.fields[element.id].y = newY;
        }
    }
    
    function stopDrag() {
        isDragging = false;
        document.removeEventListener('mousemove', drag);
        document.removeEventListener('mouseup', stopDrag);
    }
}

function selectField(fieldId) {
    // Désélectionner tous les champs
    document.querySelectorAll('.field-item').forEach(item => {
        item.classList.remove('selected');
    });
    
    // Sélectionner le champ
    const field = document.getElementById(fieldId);
    if (field) {
        field.classList.add('selected');
        selectedField = fieldId;
        showFieldProperties(fieldId);
    }
}

function showFieldProperties(fieldId) {
    const fieldConfig = templateConfig.fields[fieldId];
    if (!fieldConfig) return;
    
    const propertiesHtml = `
        <div class="mb-3">
            <label class="form-label">Largeur (px)</label>
            <input type="number" class="form-control form-control-sm" 
                   value="${fieldConfig.width}" 
                   onchange="updateFieldProperty('${fieldId}', 'width', this.value)">
        </div>
        <div class="mb-3">
            <label class="form-label">Hauteur (px)</label>
            <input type="number" class="form-control form-control-sm" 
                   value="${fieldConfig.height}" 
                   onchange="updateFieldProperty('${fieldId}', 'height', this.value)">
        </div>
        <div class="mb-3">
            <label class="form-label">Taille police</label>
            <input type="number" class="form-control form-control-sm" 
                   value="${fieldConfig.fontSize}" 
                   onchange="updateFieldProperty('${fieldId}', 'fontSize', this.value)">
        </div>
        <div class="mb-3">
            <label class="form-label">Poids police</label>
            <select class="form-select form-select-sm" 
                    onchange="updateFieldProperty('${fieldId}', 'fontWeight', this.value)">
                <option value="normal" ${fieldConfig.fontWeight === 'normal' ? 'selected' : ''}>Normal</option>
                <option value="bold" ${fieldConfig.fontWeight === 'bold' ? 'selected' : ''}>Gras</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Alignement</label>
            <select class="form-select form-select-sm" 
                    onchange="updateFieldProperty('${fieldId}', 'align', this.value)">
                <option value="left" ${fieldConfig.align === 'left' ? 'selected' : ''}>Gauche</option>
                <option value="center" ${fieldConfig.align === 'center' ? 'selected' : ''}>Centre</option>
                <option value="right" ${fieldConfig.align === 'right' ? 'selected' : ''}>Droite</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Couleur</label>
            <input type="color" class="form-control form-control-sm" 
                   value="${fieldConfig.color}" 
                   onchange="updateFieldProperty('${fieldId}', 'color', this.value)">
        </div>
    `;
    
    document.getElementById('fieldProperties').innerHTML = propertiesHtml;
    document.getElementById('fieldPropertiesCard').style.display = 'block';
}

function updateFieldProperty(fieldId, property, value) {
    if (!templateConfig.fields[fieldId]) return;
    
    templateConfig.fields[fieldId][property] = value;
    
    // Mettre à jour l'élément visuel
    const element = document.getElementById(fieldId);
    if (element) {
        switch (property) {
            case 'width':
                element.style.width = value + 'px';
                break;
            case 'height':
                element.style.height = value + 'px';
                break;
            case 'fontSize':
                element.style.fontSize = value + 'px';
                break;
            case 'fontWeight':
                element.style.fontWeight = value;
                break;
            case 'color':
                element.style.color = value;
                break;
            case 'align':
                element.style.textAlign = value;
                break;
        }
    }
}

function removeField(fieldId) {
    const element = document.getElementById(fieldId);
    if (element) {
        element.remove();
        delete templateConfig.fields[fieldId];
        
        if (selectedField === fieldId) {
            selectedField = null;
            document.getElementById('fieldPropertiesCard').style.display = 'none';
        }
    }
}

function clearDesign() {
    if (!confirm('Êtes-vous sûr de vouloir effacer tout le design ?')) return;
    
    document.querySelectorAll('.field-item').forEach(item => item.remove());
    templateConfig.fields = {};
    selectedField = null;
    document.getElementById('fieldPropertiesCard').style.display = 'none';
}

function getSampleValue(fieldKey) {
    const mapping = {
        'customer_name': sampleData.customer.name,
        'customer_phone': sampleData.customer.phone,
        'customer_address': sampleData.customer.address,
        'sender_name': sampleData.sender.name,
        'sender_address': sampleData.sender.address,
        'tracking_number': sampleData.shipment.tracking_number,
        'order_number': sampleData.shipment.order_number,
        'return_barcode': sampleData.shipment.return_barcode,
        'total_amount': sampleData.financial.total_amount + ' DT',
        'shipping_date': sampleData.dates.shipping_date
    };
    
    return mapping[fieldKey] || 'Valeur d\'exemple';
}

function previewTemplate() {
    generatePreview();
    new bootstrap.Modal(document.getElementById('previewModal')).show();
}

function generatePreview() {
    const previewContent = document.getElementById('previewContent');
    let html = '<div style="position: relative; width: 100%; height: 400px; background: white;">';
    
    // Ajouter le logo si activé
    if (templateConfig.logo.enabled && templateConfig.logo.path) {
        html += `<img src="${templateConfig.logo.path}" 
                     style="position: absolute; top: 10px; left: 10px; width: ${templateConfig.logo.width}px; height: ${templateConfig.logo.height}px;">`;
    }
    
    // Ajouter tous les champs
    Object.keys(templateConfig.fields).forEach(fieldId => {
        const field = templateConfig.fields[fieldId];
        const value = getSampleValue(field.key);
        
        html += `<div style="position: absolute; 
                               left: ${field.x}px; 
                               top: ${field.y}px; 
                               width: ${field.width}px; 
                               height: ${field.height}px;
                               font-size: ${field.fontSize}px;
                               font-weight: ${field.fontWeight};
                               color: ${field.color};
                               text-align: ${field.align};
                               border: 1px dashed #ccc;
                               padding: 4px;">
                    <div style="font-size: 10px; color: #666; margin-bottom: 2px;">${field.label}</div>
                    <div>${value}</div>
                 </div>`;
    });
    
    html += '</div>';
    previewContent.innerHTML = html;
}

function updateLogoPreview() {
    // Mettre à jour l'aperçu du logo dans le designer si nécessaire
}

function saveTemplate() {
    const formData = new FormData(document.getElementById('templateForm'));
    
    // Ajouter la configuration JSON
    formData.set('layout_config', JSON.stringify(templateConfig));
    
    const saveBtn = document.getElementById('saveBtn');
    const originalHtml = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Enregistrement...';
    saveBtn.disabled = true;
    
    fetch('{{ route("admin.delivery.bl-templates.store") }}', {
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
                window.location.href = '{{ route("admin.delivery.bl-templates.index") }}';
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
        showNotification('error', 'Erreur lors de l\'enregistrement');
    })
    .finally(() => {
        saveBtn.innerHTML = originalHtml;
        saveBtn.disabled = false;
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