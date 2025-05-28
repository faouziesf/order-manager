@extends('layouts.admin')

@section('title', 'Créer un produit')

@section('css')
<style>
    .form-container {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: calc(100vh - 160px);
        padding: 1rem 0;
    }
    
    .form-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        border: none;
    }
    
    .form-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1.5rem;
        text-align: center;
    }
    
    .form-body {
        padding: 2rem;
    }
    
    .form-label {
        font-weight: 600;
        color: #4a5568;
        margin-bottom: 0.5rem;
    }
    
    .form-control {
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
        background: #f8fafc;
    }
    
    .form-control:focus {
        background: white;
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    
    .form-select {
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.75rem 1rem;
        background: #f8fafc;
        transition: all 0.3s ease;
    }
    
    .form-select:focus {
        background: white;
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    
    .image-upload {
        border: 3px dashed #cbd5e0;
        border-radius: 15px;
        padding: 2rem;
        text-align: center;
        background: #f8fafc;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .image-upload:hover {
        border-color: #667eea;
        background: #eef2ff;
    }
    
    .image-upload.dragover {
        border-color: #667eea;
        background: #eef2ff;
        transform: scale(1.02);
    }
    
    .btn-gradient {
        background: linear-gradient(45deg, #667eea, #764ba2);
        border: none;
        color: white;
        padding: 0.75rem 2rem;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-gradient:hover {
        background: linear-gradient(45deg, #764ba2, #667eea);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        color: white;
    }
    
    .btn-outline-gradient {
        border: 2px solid #667eea;
        color: #667eea;
        background: transparent;
        padding: 0.75rem 2rem;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-outline-gradient:hover {
        background: #667eea;
        color: white;
        transform: translateY(-2px);
    }
    
    .required {
        color: #e53e3e;
    }
    
    .input-group-text {
        background: #667eea;
        color: white;
        border: none;
        border-radius: 10px 0 0 10px;
    }
    
    .invalid-feedback {
        display: block;
        color: #e53e3e;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }
    
    .character-count {
        font-size: 0.8rem;
        color: #718096;
        text-align: right;
        margin-top: 0.25rem;
    }
    
    @media (max-width: 768px) {
        .form-container {
            padding: 1rem 0;
        }
        
        .form-body {
            padding: 1.5rem;
        }
        
        .form-header {
            padding: 1.5rem;
        }
    }
</style>
@endsection

@section('content')
<div class="form-container">
    <div class="container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.dashboard') }}" class="text-decoration-none">
                        <i class="fas fa-home me-1"></i>Accueil
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.products.index') }}" class="text-decoration-none">Produits</a>
                </li>
                <li class="breadcrumb-item active">Créer un produit</li>
            </ol>
        </nav>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card form-card">
                    <!-- Header -->
                    <div class="form-header">
                        <div class="d-flex align-items-center justify-content-center">
                            <i class="fas fa-plus-circle fa-2x me-3"></i>
                            <div>
                                <h2 class="mb-0">Créer un Nouveau Produit</h2>
                                <p class="mb-0 opacity-75">Ajoutez un nouveau produit à votre catalogue</p>
                            </div>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="form-body">
                        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" id="productForm">
                            @csrf
                            
                            <div class="row">
                                <!-- Informations de base -->
                                <div class="col-md-9">
                                    <h5 class="mb-3">
                                        <i class="fas fa-info-circle me-2 text-primary"></i>Informations de base
                                    </h5>
                                    
                                    <!-- Nom du produit -->
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
                                                   placeholder="Entrez le nom du produit..."
                                                   maxlength="255"
                                                   required>
                                        </div>
                                        <div class="character-count">
                                            <span id="nameCount">0</span>/255 caractères
                                        </div>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Prix et Stock en une ligne -->
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="price" class="form-label">
                                                    Prix unitaire (DT) <span class="required">*</span>
                                                </label>
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-dollar-sign"></i>
                                                    </span>
                                                    <input type="number" 
                                                           class="form-control @error('price') is-invalid @enderror" 
                                                           id="price" 
                                                           name="price" 
                                                           value="{{ old('price') }}" 
                                                           placeholder="0.000"
                                                           step="0.001"
                                                           min="0"
                                                           required>
                                                    <span class="input-group-text">DT</span>
                                                </div>
                                                @error('price')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="stock" class="form-label">
                                                    Quantité en stock <span class="required">*</span>
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

                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Statut</label>
                                                <div class="form-check form-switch mt-2">
                                                    <input class="form-check-input" 
                                                           type="checkbox" 
                                                           id="is_active" 
                                                           name="is_active" 
                                                           {{ old('is_active', true) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="is_active">
                                                        <strong>Produit actif</strong>
                                                    </label>
                                                </div>
                                                <small class="text-muted">Le produit sera visible et disponible à la vente</small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Description -->
                                    <div class="mb-3">
                                        <label for="description" class="form-label">
                                            Description
                                        </label>
                                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                                  id="description" 
                                                  name="description" 
                                                  rows="3" 
                                                  placeholder="Décrivez votre produit..."
                                                  maxlength="1000">{{ old('description') }}</textarea>
                                        <div class="character-count">
                                            <span id="descriptionCount">0</span>/1000 caractères
                                        </div>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Image -->
                                <div class="col-md-3">
                                    <h5 class="mb-3">
                                        <i class="fas fa-image me-2 text-primary"></i>Image du produit
                                    </h5>
                                    
                                    <div class="image-upload" id="imageUpload">
                                        <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                        <h6>Sélectionner une image</h6>
                                        <p class="text-muted">Cliquez pour parcourir</p>
                                        <small class="text-muted">JPG, PNG, GIF (max. 2MB)</small>
                                    </div>
                                    
                                    <input type="file" 
                                           id="image" 
                                           name="image" 
                                           accept="image/*" 
                                           class="d-none @error('image') is-invalid @enderror">
                                    
                                    <div id="selectedFileName" class="mt-2 text-center" style="display: none;">
                                        <small class="text-success">
                                            <i class="fas fa-check me-1"></i>
                                            <span id="fileName"></span>
                                        </small>
                                        <br>
                                        <button type="button" class="btn btn-sm btn-outline-danger mt-1" id="removeFile">
                                            <i class="fas fa-times me-1"></i>Supprimer
                                        </button>
                                    </div>
                                    
                                    @error('image')
                                        <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Boutons d'action -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="d-flex justify-content-between">
                                        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-gradient">
                                            <i class="fas fa-arrow-left me-2"></i>Retour à la liste
                                        </a>
                                        
                                        <button type="submit" class="btn btn-gradient" id="submitBtn">
                                            <i class="fas fa-save me-2"></i>Créer le produit
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
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
    const selectedFileName = $('#selectedFileName');
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
        
        // Vérification de la taille (2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('L\'image ne doit pas dépasser 2MB.');
            return;
        }
        
        // Afficher le nom du fichier
        fileName.text(file.name);
        selectedFileName.show();
        
        // Simuler la sélection du fichier
        const dt = new DataTransfer();
        dt.items.add(file);
        imageInput[0].files = dt.files;
    }
    
    function resetImageUpload() {
        imageInput.val('');
        selectedFileName.hide();
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
        $('#submitBtn').html('<i class="fas fa-spinner fa-spin me-2"></i>Création en cours...');
        $('#submitBtn').prop('disabled', true);
    });
    
    // Raccourcis clavier
    $(document).on('keydown', function(e) {
        // Ctrl + S pour sauvegarder
        if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
            $('#productForm').submit();
        }
    });
});
</script>
@endsection