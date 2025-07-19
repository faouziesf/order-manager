@extends('layouts.admin')

@section('title', 'Modifier le produit')

@section('css')
<style>
    :root {
        --royal-blue: #1e40af;
        --royal-blue-dark: #1e3a8a;
        --royal-blue-light: #3b82f6;
        --royal-blue-lighter: #dbeafe;
        --royal-blue-bg: #eff6ff;
        --success: #059669;
        --warning: #d97706;
        --danger: #dc2626;
        --gray-50: #f9fafb;
        --gray-100: #f3f4f6;
        --gray-200: #e5e7eb;
        --gray-500: #6b7280;
        --gray-700: #374151;
        --gray-900: #111827;
    }

    body {
        background: linear-gradient(135deg, var(--royal-blue-bg) 0%, var(--gray-50) 100%);
        min-height: 100vh;
    }
    
    .main-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(30, 64, 175, 0.1);
        border: 1px solid var(--royal-blue-lighter);
        overflow: hidden;
    }
    
    .card-header {
        background: linear-gradient(135deg, var(--warning) 0%, #ea580c 100%);
        color: white;
        padding: 2rem;
        text-align: center;
        border: none;
    }
    
    .card-header h2 {
        margin: 0;
        font-size: 1.75rem;
        font-weight: 700;
    }
    
    .card-header p {
        margin: 0.5rem 0 0 0;
        opacity: 0.9;
        font-size: 1rem;
    }
    
    .card-body {
        padding: 2.5rem;
    }
    
    .product-info {
        background: linear-gradient(135deg, var(--gray-50) 0%, var(--royal-blue-bg) 100%);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        border-left: 4px solid var(--warning);
    }
    
    .form-section {
        background: var(--gray-50);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        border-left: 4px solid var(--royal-blue);
    }
    
    .section-title {
        color: var(--royal-blue-dark);
        font-size: 1.125rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .form-label {
        font-weight: 600;
        color: var(--gray-700);
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
    }
    
    .form-control, .form-select {
        border: 2px solid var(--gray-200);
        border-radius: 8px;
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
        background: white;
        font-size: 0.875rem;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: var(--royal-blue);
        box-shadow: 0 0 0 0.2rem rgba(30, 64, 175, 0.25);
        background: white;
    }
    
    .input-group-text {
        background: var(--royal-blue);
        color: white;
        border: 2px solid var(--royal-blue);
        border-radius: 8px 0 0 8px;
        font-weight: 500;
    }
    
    .image-section {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        align-items: start;
    }
    
    .current-image {
        text-align: center;
    }
    
    .current-image img {
        max-width: 100%;
        max-height: 200px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        border: 2px solid var(--royal-blue-lighter);
    }
    
    .image-upload-area {
        border: 3px dashed var(--royal-blue-lighter);
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
        background: var(--royal-blue-bg);
        transition: all 0.3s ease;
        cursor: pointer;
        min-height: 150px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
    
    .image-upload-area:hover, .image-upload-area.dragover {
        border-color: var(--royal-blue);
        background: var(--royal-blue-lighter);
        transform: scale(1.02);
    }
    
    .image-upload-area i {
        font-size: 2rem;
        color: var(--royal-blue);
        margin-bottom: 0.5rem;
    }
    
    .upload-text {
        color: var(--royal-blue-dark);
        font-weight: 600;
        margin-bottom: 0.25rem;
        font-size: 0.875rem;
    }
    
    .upload-hint {
        color: var(--gray-500);
        font-size: 0.75rem;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, var(--royal-blue) 0%, var(--royal-blue-dark) 100%);
        border: none;
        color: white;
        padding: 0.75rem 2rem;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
        font-size: 0.875rem;
    }
    
    .btn-primary:hover {
        background: linear-gradient(135deg, var(--royal-blue-dark) 0%, var(--royal-blue) 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(30, 64, 175, 0.3);
        color: white;
    }
    
    .btn-secondary {
        background: var(--gray-100);
        border: 2px solid var(--gray-200);
        color: var(--gray-700);
        padding: 0.75rem 2rem;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
        font-size: 0.875rem;
    }
    
    .btn-secondary:hover {
        background: var(--gray-200);
        color: var(--gray-900);
        transform: translateY(-2px);
    }
    
    .btn-danger {
        background: var(--danger);
        border: none;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-size: 0.875rem;
        transition: all 0.3s ease;
    }
    
    .btn-danger:hover {
        background: #b91c1c;
        color: white;
    }
    
    .required {
        color: var(--danger);
        font-weight: 600;
    }
    
    .form-check-input:checked {
        background-color: var(--royal-blue);
        border-color: var(--royal-blue);
    }
    
    .form-check-input:focus {
        border-color: var(--royal-blue);
        box-shadow: 0 0 0 0.25rem rgba(30, 64, 175, 0.25);
    }
    
    .character-count {
        font-size: 0.75rem;
        color: var(--gray-500);
        text-align: right;
        margin-top: 0.25rem;
    }
    
    .file-selected {
        background: linear-gradient(135deg, var(--success) 0%, #047857 100%);
        color: white;
        padding: 1rem;
        border-radius: 8px;
        text-align: center;
        margin-top: 1rem;
    }
    
    .breadcrumb {
        background: white;
        border-radius: 8px;
        padding: 1rem;
        box-shadow: 0 2px 8px rgba(30, 64, 175, 0.1);
        border: 1px solid var(--royal-blue-lighter);
    }
    
    .breadcrumb-item a {
        color: var(--royal-blue);
        text-decoration: none;
        font-weight: 500;
    }
    
    .breadcrumb-item a:hover {
        color: var(--royal-blue-dark);
    }
    
    .breadcrumb-item.active {
        color: var(--gray-700);
        font-weight: 600;
    }
    
    .invalid-feedback {
        display: block;
        color: var(--danger);
        font-size: 0.875rem;
        margin-top: 0.25rem;
        font-weight: 500;
    }
    
    .form-control.is-invalid, .form-select.is-invalid {
        border-color: var(--danger);
    }
    
    .alert {
        border-radius: 8px;
        border: none;
        padding: 1rem 1.5rem;
    }
    
    .alert-warning {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #92400e;
        border-left: 4px solid var(--warning);
    }
    
    .stock-indicator {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-left: 0.5rem;
    }
    
    .stock-low {
        background: #fee2e2;
        color: #991b1b;
    }
    
    .stock-medium {
        background: #fef3c7;
        color: #92400e;
    }
    
    .stock-high {
        background: #dcfce7;
        color: #166534;
    }
    
    @media (max-width: 768px) {
        .card-body {
            padding: 1.5rem;
        }
        
        .form-section {
            padding: 1rem;
        }
        
        .image-section {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .btn-primary, .btn-secondary {
            width: 100%;
            margin-bottom: 0.5rem;
        }
    }
</style>
@endsection

@section('content')
<div class="container">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.dashboard') }}">
                    <i class="fas fa-home me-1"></i>Accueil
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('admin.products.index') }}">Produits</a>
            </li>
            <li class="breadcrumb-item active">Modifier: {{ $product->name }}</li>
        </ol>
    </nav>

    <!-- Formulaire principal -->
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="main-card">
                <!-- En-tête -->
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-center">
                        <i class="fas fa-edit fa-2x me-3"></i>
                        <div>
                            <h2>Modifier le Produit</h2>
                            <p>{{ $product->name }}</p>
                        </div>
                    </div>
                </div>

                <!-- Corps -->
                <div class="card-body">
                    <!-- Informations du produit -->
                    <div class="product-info">
                        <div class="row">
                            <div class="col-md-4">
                                <small class="text-muted">Référence:</small>
                                <div class="fw-bold">{{ $product->formatted_reference ?? 'Non définie' }}</div>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">Créé le:</small>
                                <div class="fw-bold">{{ $product->created_at->format('d/m/Y à H:i') }}</div>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">Modifié le:</small>
                                <div class="fw-bold">{{ $product->updated_at->format('d/m/Y à H:i') }}</div>
                            </div>
                        </div>
                        
                        @if($product->needs_review)
                            <div class="alert alert-warning mt-3 mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Ce produit nécessite un examen car il a été créé automatiquement.
                            </div>
                        @endif
                        
                        @if($product->isUsedInOrders())
                            <div class="alert alert-info mt-3 mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                Ce produit est utilisé dans {{ $product->getOrdersCount() }} commande(s). 
                                Il ne peut pas être supprimé.
                            </div>
                        @endif
                    </div>

                    <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data" id="productForm">
                        @csrf
                        @method('PUT')
                        
                        <!-- Section Informations principales -->
                        <div class="form-section">
                            <h5 class="section-title">
                                <i class="fas fa-info-circle"></i>
                                Informations principales
                            </h5>
                            
                            <div class="row">
                                <!-- Référence -->
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="reference" class="form-label">
                                            Référence
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-hashtag"></i>
                                            </span>
                                            <input type="number" 
                                                   class="form-control @error('reference') is-invalid @enderror" 
                                                   id="reference" 
                                                   name="reference" 
                                                   value="{{ old('reference', $product->reference) }}" 
                                                   placeholder="Non définie"
                                                   min="1">
                                        </div>
                                        @error('reference')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Nom du produit -->
                                <div class="col-md-9">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">
                                            Nom du produit <span class="required">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-tag"></i>
                                            </span>
                                            <input type="text" 
                                                   class="form-control @error('name') is-invalid @enderror" 
                                                   id="name" 
                                                   name="name" 
                                                   value="{{ old('name', $product->name) }}" 
                                                   placeholder="Nom du produit..."
                                                   maxlength="255"
                                                   required>
                                        </div>
                                        <div class="character-count">
                                            <span id="nameCount">0</span>/255
                                        </div>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Prix -->
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="price" class="form-label">
                                            Prix (DT) <span class="required">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">DT</span>
                                            <input type="number" 
                                                   class="form-control @error('price') is-invalid @enderror" 
                                                   id="price" 
                                                   name="price" 
                                                   value="{{ old('price', $product->price) }}" 
                                                   placeholder="0.000"
                                                   step="0.001"
                                                   min="0"
                                                   required>
                                        </div>
                                        @error('price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Stock -->
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="stock" class="form-label">
                                            Stock <span class="required">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-boxes"></i>
                                            </span>
                                            <input type="number" 
                                                   class="form-control @error('stock') is-invalid @enderror" 
                                                   id="stock" 
                                                   name="stock" 
                                                   value="{{ old('stock', $product->stock) }}" 
                                                   placeholder="0"
                                                   min="0"
                                                   required>
                                        </div>
                                        @if($product->stock <= 0)
                                            <span class="stock-indicator stock-low">
                                                <i class="fas fa-times me-1"></i>Rupture
                                            </span>
                                        @elseif($product->stock <= 10)
                                            <span class="stock-indicator stock-medium">
                                                <i class="fas fa-exclamation-triangle me-1"></i>Stock faible
                                            </span>
                                        @else
                                            <span class="stock-indicator stock-high">
                                                <i class="fas fa-check me-1"></i>Stock normal
                                            </span>
                                        @endif
                                        @error('stock')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Statut -->
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Statut</label>
                                        <div class="d-flex align-items-center mt-2">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       id="is_active" 
                                                       name="is_active" 
                                                       {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-bold" for="is_active">
                                                    Produit actif
                                                </label>
                                            </div>
                                        </div>
                                        <small class="text-muted">Le produit sera visible et disponible</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="mb-0">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" 
                                          name="description" 
                                          rows="3" 
                                          placeholder="Description du produit..."
                                          maxlength="2000">{{ old('description', $product->description) }}</textarea>
                                <div class="character-count">
                                    <span id="descriptionCount">0</span>/2000
                                </div>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Section Image -->
                        <div class="form-section">
                            <h5 class="section-title">
                                <i class="fas fa-image"></i>
                                Image du produit
                            </h5>
                            
                            <div class="image-section">
                                <!-- Image actuelle -->
                                @if($product->image)
                                <div class="current-image">
                                    <h6 class="text-muted mb-2">Image actuelle</h6>
                                    <img src="{{ Storage::url($product->image) }}" 
                                         alt="{{ $product->name }}" 
                                         id="currentImage">
                                </div>
                                @endif
                                
                                <!-- Upload nouvelle image -->
                                <div>
                                    <h6 class="text-muted mb-2">
                                        {{ $product->image ? 'Remplacer l\'image' : 'Ajouter une image' }}
                                    </h6>
                                    <div class="image-upload-area" id="imageUpload">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <div class="upload-text">Cliquez pour changer</div>
                                        <div class="upload-hint">JPG, PNG, GIF (max. 5MB)</div>
                                    </div>
                                </div>
                            </div>
                            
                            <input type="file" 
                                   id="image" 
                                   name="image" 
                                   accept="image/*" 
                                   class="d-none @error('image') is-invalid @enderror">
                            
                            <div id="fileSelected" class="file-selected" style="display: none;">
                                <i class="fas fa-check me-2"></i>
                                <span id="fileName"></span>
                                <button type="button" class="btn btn-danger btn-sm ms-2" id="removeFile">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            
                            @error('image')
                                <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Boutons d'action -->
                        <div class="d-flex justify-content-between flex-wrap gap-2">
                            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Retour
                            </a>
                            
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-save me-2"></i>Sauvegarder
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Compteurs de caractères
    $('#name').on('input', function() {
        $('#nameCount').text($(this).val().length);
    });
    
    $('#description').on('input', function() {
        $('#descriptionCount').text($(this).val().length);
    });
    
    // Initialiser les compteurs
    $('#nameCount').text($('#name').val().length);
    $('#descriptionCount').text($('#description').val().length);
    
    // Gestion de l'upload d'image
    const imageUpload = $('#imageUpload');
    const imageInput = $('#image');
    const fileSelected = $('#fileSelected');
    const fileName = $('#fileName');
    const removeFileBtn = $('#removeFile');
    
    // Click sur la zone d'upload
    imageUpload.on('click', function() {
        imageInput.click();
    });
    
    // Drag & Drop
    imageUpload.on('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).addClass('dragover');
    });
    
    imageUpload.on('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('dragover');
    });
    
    imageUpload.on('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('dragover');
        
        const files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            handleImageFile(files[0]);
        }
    });
    
    // Changement de fichier
    imageInput.on('change', function() {
        const file = this.files[0];
        if (file) {
            handleImageFile(file);
        }
    });
    
    // Supprimer le fichier
    removeFileBtn.on('click', function() {
        resetImageUpload();
    });
    
    function handleImageFile(file) {
        // Vérification du type
        if (!file.type.startsWith('image/')) {
            alert('Veuillez sélectionner une image valide.');
            return;
        }
        
        // Vérification de la taille (5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('L\'image ne doit pas dépasser 5MB.');
            return;
        }
        
        // Afficher le nom du fichier
        fileName.text(file.name);
        fileSelected.show();
        
        // Simuler la sélection du fichier
        const dt = new DataTransfer();
        dt.items.add(file);
        imageInput[0].files = dt.files;
    }
    
    function resetImageUpload() {
        imageInput.val('');
        fileSelected.hide();
        fileName.text('');
    }
    
    // Validation du formulaire
    $('#productForm').on('submit', function(e) {
        let isValid = true;
        
        // Vérifier les champs requis
        const requiredFields = ['name', 'price', 'stock'];
        requiredFields.forEach(function(field) {
            const input = $('#' + field);
            if (!input.val().trim()) {
                input.addClass('is-invalid');
                isValid = false;
            } else {
                input.removeClass('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('Veuillez remplir tous les champs obligatoires.');
            return;
        }
        
        // Animation de soumission
        $('#submitBtn').html('<i class="fas fa-spinner fa-spin me-2"></i>Sauvegarde...');
        $('#submitBtn').prop('disabled', true);
    });
    
    // Détecter les changements
    let formChanged = false;
    $('#productForm input, #productForm textarea').on('change input', function() {
        formChanged = true;
    });
    
    // Avertir avant de quitter
    $(window).on('beforeunload', function(e) {
        if (formChanged) {
            return 'Vous avez des modifications non sauvegardées.';
        }
    });
    
    // Ne pas avertir lors de la soumission
    $('#productForm').on('submit', function() {
        formChanged = false;
    });
    
    // Raccourcis clavier
    $(document).on('keydown', function(e) {
        if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
            $('#productForm').submit();
        }
    });
});
</script>
@endsection