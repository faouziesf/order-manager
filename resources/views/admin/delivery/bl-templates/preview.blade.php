@extends('layouts.admin')

@section('title', 'Aperçu Template BL')

@section('css')
<style>
.preview-container {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.preview-paper {
    background: white;
    min-height: 600px;
    padding: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    border-radius: 4px;
    position: relative;
    margin: 0 auto;
    max-width: 210mm; /* Format A4 */
}

.preview-field {
    position: absolute;
    border: 1px dashed #ccc;
    padding: 4px;
    background: rgba(0, 123, 255, 0.05);
    transition: all 0.3s ease;
}

.preview-field:hover {
    border-color: #007bff;
    background: rgba(0, 123, 255, 0.1);
    z-index: 10;
}

.preview-field-label {
    font-size: 8px;
    color: #666;
    font-weight: bold;
    margin-bottom: 2px;
    text-transform: uppercase;
}

.preview-field-content {
    font-size: 12px;
    color: #333;
}

.sample-data-panel {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    max-height: 600px;
    overflow-y: auto;
}

.sample-data-section {
    border-bottom: 1px solid #e9ecef;
    padding: 15px;
}

.sample-data-section:last-child {
    border-bottom: none;
}

.sample-data-title {
    font-weight: 600;
    color: #495057;
    margin-bottom: 10px;
    font-size: 14px;
}

.sample-data-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 5px 0;
    border-bottom: 1px solid #f8f9fa;
}

.sample-data-item:last-child {
    border-bottom: none;
}

.sample-data-label {
    font-size: 12px;
    color: #6c757d;
    font-weight: 500;
}

.sample-data-value {
    font-size: 12px;
    color: #212529;
    font-family: 'Courier New', monospace;
    background: #f8f9fa;
    padding: 2px 6px;
    border-radius: 3px;
}

.preview-controls {
    background: white;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    margin-bottom: 20px;
}

.zoom-controls {
    display: flex;
    align-items: center;
    gap: 10px;
}

.zoom-level {
    font-weight: 600;
    min-width: 60px;
    text-align: center;
}

.ruler {
    background: linear-gradient(to right, transparent 0%, transparent 95%, #ccc 95%, #ccc 100%);
    background-size: 10px 100%;
    height: 20px;
    border-bottom: 1px solid #ccc;
}

.ruler-vertical {
    background: linear-gradient(to bottom, transparent 0%, transparent 95%, #ccc 95%, #ccc 100%);
    background-size: 100% 10px;
    width: 20px;
    border-right: 1px solid #ccc;
}

@media print {
    .no-print {
        display: none !important;
    }
    
    .preview-container {
        background: white;
        padding: 0;
        box-shadow: none;
    }
    
    .preview-paper {
        box-shadow: none;
        max-width: none;
        width: 100%;
    }
    
    .preview-field {
        border: none !important;
        background: none !important;
    }
    
    .preview-field-label {
        display: none;
    }
}
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- En-tête -->
            <div class="d-flex justify-content-between align-items-center mb-4 no-print">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.delivery.bl-templates.index') }}">Templates BL</a>
                            </li>
                            <li class="breadcrumb-item active">Aperçu "{{ $blTemplate->template_name }}"</li>
                        </ol>
                    </nav>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-eye text-primary me-2"></i>
                        Aperçu Template BL
                    </h1>
                    <p class="text-muted mb-0">Prévisualisation du template "{{ $blTemplate->template_name }}"</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.delivery.bl-templates.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Retour
                    </a>
                    <a href="{{ route('admin.delivery.bl-templates.edit', $blTemplate) }}" class="btn btn-outline-primary">
                        <i class="fas fa-edit me-2"></i>Modifier
                    </a>
                    <button type="button" class="btn btn-success" onclick="window.print()">
                        <i class="fas fa-print me-2"></i>Imprimer
                    </button>
                </div>
            </div>

            <div class="row">
                <!-- Contrôles et données d'exemple -->
                <div class="col-lg-3 no-print">
                    <!-- Contrôles de zoom -->
                    <div class="preview-controls">
                        <h6 class="mb-3">
                            <i class="fas fa-cog me-2"></i>Contrôles
                        </h6>
                        <div class="zoom-controls">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="changeZoom(-0.1)">
                                <i class="fas fa-minus"></i>
                            </button>
                            <span class="zoom-level" id="zoomLevel">100%</span>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="changeZoom(0.1)">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <hr>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-info btn-sm" onclick="toggleFieldLabels()">
                                <i class="fas fa-tag me-2"></i>
                                <span id="fieldLabelsToggleText">Masquer les labels</span>
                            </button>
                            <button type="button" class="btn btn-outline-warning btn-sm" onclick="refreshPreview()">
                                <i class="fas fa-sync-alt me-2"></i>Actualiser
                            </button>
                        </div>
                    </div>

                    <!-- Données d'exemple -->
                    <div class="sample-data-panel">
                        <div class="sample-data-section">
                            <div class="sample-data-title">
                                <i class="fas fa-user me-2"></i>Client
                            </div>
                            <div class="sample-data-item">
                                <span class="sample-data-label">Nom:</span>
                                <span class="sample-data-value">{{ $sampleData['customer']['name'] }}</span>
                            </div>
                            <div class="sample-data-item">
                                <span class="sample-data-label">Téléphone:</span>
                                <span class="sample-data-value">{{ $sampleData['customer']['phone'] }}</span>
                            </div>
                            <div class="sample-data-item">
                                <span class="sample-data-label">Adresse:</span>
                                <span class="sample-data-value">{{ Str::limit($sampleData['customer']['address'], 20) }}</span>
                            </div>
                            <div class="sample-data-item">
                                <span class="sample-data-label">Ville:</span>
                                <span class="sample-data-value">{{ $sampleData['customer']['city'] }}</span>
                            </div>
                            <div class="sample-data-item">
                                <span class="sample-data-label">Email:</span>
                                <span class="sample-data-value">{{ $sampleData['customer']['email'] }}</span>
                            </div>
                        </div>

                        <div class="sample-data-section">
                            <div class="sample-data-title">
                                <i class="fas fa-building me-2"></i>Expéditeur
                            </div>
                            <div class="sample-data-item">
                                <span class="sample-data-label">Nom:</span>
                                <span class="sample-data-value">{{ $sampleData['sender']['name'] }}</span>
                            </div>
                            <div class="sample-data-item">
                                <span class="sample-data-label">Adresse:</span>
                                <span class="sample-data-value">{{ Str::limit($sampleData['sender']['address'], 20) }}</span>
                            </div>
                            <div class="sample-data-item">
                                <span class="sample-data-label">Téléphone:</span>
                                <span class="sample-data-value">{{ $sampleData['sender']['phone'] }}</span>
                            </div>
                            <div class="sample-data-item">
                                <span class="sample-data-label">Email:</span>
                                <span class="sample-data-value">{{ $sampleData['sender']['email'] }}</span>
                            </div>
                        </div>

                        <div class="sample-data-section">
                            <div class="sample-data-title">
                                <i class="fas fa-shipping-fast me-2"></i>Expédition
                            </div>
                            <div class="sample-data-item">
                                <span class="sample-data-label">Code suivi:</span>
                                <span class="sample-data-value">{{ $sampleData['shipment']['tracking_number'] }}</span>
                            </div>
                            <div class="sample-data-item">
                                <span class="sample-data-label">N° Commande:</span>
                                <span class="sample-data-value">{{ $sampleData['shipment']['order_number'] }}</span>
                            </div>
                            <div class="sample-data-item">
                                <span class="sample-data-label">Code retour:</span>
                                <span class="sample-data-value">{{ $sampleData['shipment']['return_barcode'] }}</span>
                            </div>
                            <div class="sample-data-item">
                                <span class="sample-data-label">Poids:</span>
                                <span class="sample-data-value">{{ $sampleData['shipment']['weight'] }} kg</span>
                            </div>
                            <div class="sample-data-item">
                                <span class="sample-data-label">Nb colis:</span>
                                <span class="sample-data-value">{{ $sampleData['shipment']['pieces_count'] }}</span>
                            </div>
                        </div>

                        <div class="sample-data-section">
                            <div class="sample-data-title">
                                <i class="fas fa-euro-sign me-2"></i>Financier
                            </div>
                            <div class="sample-data-item">
                                <span class="sample-data-label">Montant total:</span>
                                <span class="sample-data-value">{{ $sampleData['financial']['total_amount'] }} DT</span>
                            </div>
                            <div class="sample-data-item">
                                <span class="sample-data-label">Montant COD:</span>
                                <span class="sample-data-value">{{ $sampleData['financial']['cod_amount'] }} DT</span>
                            </div>
                            <div class="sample-data-item">
                                <span class="sample-data-label">Frais port:</span>
                                <span class="sample-data-value">{{ $sampleData['financial']['shipping_cost'] }} DT</span>
                            </div>
                        </div>

                        <div class="sample-data-section">
                            <div class="sample-data-title">
                                <i class="fas fa-calendar me-2"></i>Dates
                            </div>
                            <div class="sample-data-item">
                                <span class="sample-data-label">Expédition:</span>
                                <span class="sample-data-value">{{ $sampleData['dates']['shipping_date'] }}</span>
                            </div>
                            <div class="sample-data-item">
                                <span class="sample-data-label">Enlèvement:</span>
                                <span class="sample-data-value">{{ $sampleData['dates']['pickup_date'] }}</span>
                            </div>
                            <div class="sample-data-item">
                                <span class="sample-data-label">Livraison est.:</span>
                                <span class="sample-data-value">{{ $sampleData['dates']['estimated_delivery'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Aperçu du template -->
                <div class="col-lg-9">
                    <div class="preview-container">
                        <!-- Règles -->
                        <div class="d-flex no-print">
                            <div style="width: 20px; height: 20px;"></div>
                            <div class="ruler flex-grow-1"></div>
                        </div>
                        
                        <div class="d-flex">
                            <div class="ruler-vertical no-print"></div>
                            <div class="preview-paper" id="previewPaper">
                                <!-- Logo -->
                                @if(!empty($blTemplate->layout_config['logo']['enabled']) && !empty($blTemplate->layout_config['logo']['path']))
                                    <img src="{{ Storage::url($blTemplate->layout_config['logo']['path']) }}" 
                                         style="position: absolute; top: 10px; left: 10px; 
                                                width: {{ $blTemplate->layout_config['logo']['width'] ?? 100 }}px; 
                                                height: {{ $blTemplate->layout_config['logo']['height'] ?? 50 }}px;"
                                         alt="Logo">
                                @endif

                                <!-- Champs du template -->
                                @if(!empty($blTemplate->layout_config['fields']))
                                    @foreach($blTemplate->layout_config['fields'] as $fieldId => $field)
                                        <div class="preview-field" 
                                             style="left: {{ $field['x'] }}px; 
                                                    top: {{ $field['y'] }}px; 
                                                    width: {{ $field['width'] }}px; 
                                                    height: {{ $field['height'] }}px;
                                                    font-size: {{ $field['fontSize'] }}px;
                                                    font-weight: {{ $field['fontWeight'] }};
                                                    color: {{ $field['color'] }};
                                                    text-align: {{ $field['align'] }};">
                                            
                                            <div class="preview-field-label">{{ $field['label'] }}</div>
                                            <div class="preview-field-content">
                                                @php
                                                    $value = '';
                                                    switch($field['key']) {
                                                        case 'customer_name': $value = $sampleData['customer']['name']; break;
                                                        case 'customer_phone': $value = $sampleData['customer']['phone']; break;
                                                        case 'customer_address': $value = $sampleData['customer']['address']; break;
                                                        case 'customer_city': $value = $sampleData['customer']['city']; break;
                                                        case 'customer_email': $value = $sampleData['customer']['email']; break;
                                                        case 'sender_name': $value = $sampleData['sender']['name']; break;
                                                        case 'sender_address': $value = $sampleData['sender']['address']; break;
                                                        case 'sender_phone': $value = $sampleData['sender']['phone']; break;
                                                        case 'sender_email': $value = $sampleData['sender']['email']; break;
                                                        case 'tracking_number': $value = $sampleData['shipment']['tracking_number']; break;
                                                        case 'order_number': $value = $sampleData['shipment']['order_number']; break;
                                                        case 'return_barcode': $value = $sampleData['shipment']['return_barcode']; break;
                                                        case 'weight': $value = $sampleData['shipment']['weight'] . ' kg'; break;
                                                        case 'pieces_count': $value = $sampleData['shipment']['pieces_count']; break;
                                                        case 'content_description': $value = $sampleData['shipment']['content_description']; break;
                                                        case 'total_amount': $value = $sampleData['financial']['total_amount'] . ' DT'; break;
                                                        case 'cod_amount': $value = $sampleData['financial']['cod_amount'] . ' DT'; break;
                                                        case 'shipping_cost': $value = $sampleData['financial']['shipping_cost'] . ' DT'; break;
                                                        case 'shipping_date': $value = $sampleData['dates']['shipping_date']; break;
                                                        case 'pickup_date': $value = $sampleData['dates']['pickup_date']; break;
                                                        case 'estimated_delivery': $value = $sampleData['dates']['estimated_delivery']; break;
                                                        default: $value = 'Valeur d\'exemple';
                                                    }
                                                @endphp
                                                {{ $value }}
                                            </div>
                                        </div>
                                    @endforeach
                                @endif

                                <!-- Message si aucun champ -->
                                @if(empty($blTemplate->layout_config['fields']))
                                    <div class="text-center text-muted p-5">
                                        <i class="fas fa-file-alt fa-3x mb-3"></i>
                                        <h5>Template vide</h5>
                                        <p>Ce template ne contient aucun champ.<br>
                                           <a href="{{ route('admin.delivery.bl-templates.edit', $blTemplate) }}">Cliquez ici pour l'éditer</a>
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Informations du template -->
                    <div class="card mt-4 no-print">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                Informations du template
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <dl class="row">
                                        <dt class="col-sm-4">Nom:</dt>
                                        <dd class="col-sm-8">{{ $blTemplate->template_name }}</dd>
                                        
                                        <dt class="col-sm-4">Transporteur:</dt>
                                        <dd class="col-sm-8">
                                            @if($blTemplate->carrier_slug)
                                                <span class="badge bg-info">{{ ucfirst($blTemplate->carrier_slug) }}</span>
                                            @else
                                                <span class="text-muted">Universel</span>
                                            @endif
                                        </dd>
                                        
                                        <dt class="col-sm-4">Statut:</dt>
                                        <dd class="col-sm-8">
                                            <span class="badge {{ $blTemplate->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $blTemplate->is_active ? 'Actif' : 'Inactif' }}
                                            </span>
                                            @if($blTemplate->is_default)
                                                <span class="badge bg-warning ms-1">Par défaut</span>
                                            @endif
                                        </dd>
                                    </dl>
                                </div>
                                <div class="col-md-6">
                                    <dl class="row">
                                        <dt class="col-sm-4">Créé le:</dt>
                                        <dd class="col-sm-8">{{ $blTemplate->created_at->format('d/m/Y H:i') }}</dd>
                                        
                                        <dt class="col-sm-4">Modifié le:</dt>
                                        <dd class="col-sm-8">{{ $blTemplate->updated_at->format('d/m/Y H:i') }}</dd>
                                        
                                        <dt class="col-sm-4">Champs:</dt>
                                        <dd class="col-sm-8">
                                            {{ count($blTemplate->layout_config['fields'] ?? []) }} champ(s)
                                        </dd>
                                    </dl>
                                </div>
                            </div>
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
let currentZoom = 1;
let showFieldLabels = true;

$(document).ready(function() {
    console.log('Prévisualisation du template BL chargée');
    updateZoomDisplay();
});

function changeZoom(delta) {
    currentZoom = Math.max(0.5, Math.min(2, currentZoom + delta));
    updateZoomDisplay();
    applyZoom();
}

function updateZoomDisplay() {
    document.getElementById('zoomLevel').textContent = Math.round(currentZoom * 100) + '%';
}

function applyZoom() {
    const previewPaper = document.getElementById('previewPaper');
    previewPaper.style.transform = `scale(${currentZoom})`;
    previewPaper.style.transformOrigin = 'top left';
    
    // Ajuster la hauteur du conteneur
    const container = previewPaper.parentElement;
    const originalHeight = previewPaper.offsetHeight;
    container.style.height = (originalHeight * currentZoom) + 'px';
}

function toggleFieldLabels() {
    showFieldLabels = !showFieldLabels;
    const labels = document.querySelectorAll('.preview-field-label');
    const toggleText = document.getElementById('fieldLabelsToggleText');
    
    labels.forEach(label => {
        label.style.display = showFieldLabels ? 'block' : 'none';
    });
    
    toggleText.textContent = showFieldLabels ? 'Masquer les labels' : 'Afficher les labels';
}

function refreshPreview() {
    // Recharger la page pour actualiser l'aperçu
    location.reload();
}

// Gestion du zoom avec la molette
document.getElementById('previewPaper').addEventListener('wheel', function(e) {
    if (e.ctrlKey) {
        e.preventDefault();
        const delta = e.deltaY > 0 ? -0.1 : 0.1;
        changeZoom(delta);
    }
});

// Raccourcis clavier
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey) {
        switch(e.key) {
            case '+':
            case '=':
                e.preventDefault();
                changeZoom(0.1);
                break;
            case '-':
                e.preventDefault();
                changeZoom(-0.1);
                break;
            case '0':
                e.preventDefault();
                currentZoom = 1;
                updateZoomDisplay();
                applyZoom();
                break;
            case 'p':
                e.preventDefault();
                window.print();
                break;
        }
    }
    
    if (e.key === 'Escape') {
        window.history.back();
    }
});

// Afficher les raccourcis dans une info-bulle
$('[data-bs-toggle="tooltip"]').tooltip();
</script>
@endsection