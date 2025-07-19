@extends('layouts.admin')

@section('title', 'Créer un produit')

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
        background: linear-gradient(135deg, var(--royal-blue) 0%, var(--royal-blue-dark) 100%);
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
    
    .image-upload-area {
        border: 3px dashed var(--royal-blue-lighter);
        border-radius: 12px;
        padding: 2rem;
        text-align: center;
        background: var(--royal-blue-bg);
        transition: all 0.3s ease;
        cursor: pointer;
        min-height: 200px;
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
        font-size: 2.5rem;
        color: var(--royal-blue);
        margin-bottom: 1rem;
    }
    
    .upload-text {
        color: var(--royal-blue-dark);
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    
    .upload-hint {
        color: var(--gray-500);
        font-size: 0.875rem;
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
    
    @media (max-width: 768px) {
        .card-body {
            padding: 1.5rem;
        }
        
        .form-section {
            padding: 1rem;
        }
        
        .image-upload-area {
            padding: 1.5rem;
            min-height: 150px;
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
            <li class="breadcrumb-item active">Nouveau Produit</li>
        </ol>
    </nav>

    <!-- Formulaire principal -->
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="main-card">
                <!-- En-tête -->
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-center">
                        <i class="fas fa-plus-circle fa-2x me-3"></i>
                        <div>
                            <h2>Nouveau Produit</h2>
                            <p>Ajoutez un produit à votre catalogue</p>
                        </div>
                    </div>
                </div>

                <!-- Corps -->
                <div class="card-body">
                    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" id="productForm">
                        @csrf
                        
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
                                                   value="{{ old('reference') }}" 
                                                   placeholder="Auto-généré"
                                                   min="1">
                                        </div>
                                        <small class="text-muted">Laissez vide pour génération automatique</small>
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
                                                   value="{{ old('name') }}" 
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
                                                   value="{{ old('price') }}" 
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
                                                   value="{{ old('stock', 0) }}" 
                                                   placeholder="0"
                                                   min="0"
                                                   required>
                                        </div>
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
                                                       {{ old('is_active', true) ? 'checked' : '' }}>
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
                                          maxlength="2000">{{ old('description') }}</textarea>
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
                            
                            <div class="image-upload-area" id="imageUpload">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <div class="upload-text">Cliquez ou glissez votre image ici</div>
                                <div class="upload-hint">JPG, PNG, GIF (max. 5MB)</div>
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
                                <i class="fas fa-save me-2"></i>Créer le produit
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
        imageUpload.hide();
        
        // Simuler la sélection du fichier
        const dt = new DataTransfer();
        dt.items.add(file);
        imageInput[0].files = dt.files;
    }
    
    function resetImageUpload() {
        imageInput.val('');
        fileSelected.hide();
        imageUpload.show();
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
        $('#submitBtn').html('<i class="fas fa-spinner fa-spin me-2"></i>Création...');
        $('#submitBtn').prop('disabled', true);
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