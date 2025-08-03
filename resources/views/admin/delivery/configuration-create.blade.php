@extends('layouts.admin')

@section('title', 'Nouvelle Configuration - ' . $carrier['name'])

@section('content')
<div class="container-fluid" x-data="configurationCreate">
    <!-- Header avec breadcrumb -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gradient">
                <i class="fas fa-plus text-primary me-2"></i>
                Nouvelle Configuration
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.delivery.index') }}">Livraisons</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.delivery.configuration') }}">Configuration</a></li>
                    <li class="breadcrumb-item active">Nouvelle</li>
                </ol>
            </nav>
            <p class="text-muted mb-0">Configurer {{ $carrier['name'] }}</p>
        </div>
        <a href="{{ route('admin.delivery.configuration') }}" class="btn btn-outline-secondary animate-slide-up">
            <i class="fas fa-arrow-left me-1"></i>
            Retour
        </a>
    </div>

    <div class="row">
        <!-- Formulaire principal -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm animate-slide-up">
                <div class="card-header d-flex align-items-center">
                    <div class="me-3">
                        @if(isset($carrier['logo']))
                            <img src="{{ asset($carrier['logo']) }}" 
                                 alt="{{ $carrier['name'] }}" 
                                 class="carrier-logo-sm">
                        @else
                            <div class="carrier-logo-sm d-flex align-items-center justify-content-center">
                                <i class="fas fa-truck fa-lg text-muted"></i>
                            </div>
                        @endif
                    </div>
                    <div>
                        <h6 class="m-0 font-weight-bold text-primary">{{ $carrier['name'] }}</h6>
                        <small class="text-muted">{{ $carrier['description'] ?? 'Configuration des paramètres de connexion' }}</small>
                    </div>
                </div>
                
                <form action="{{ route('admin.delivery.configuration.store') }}" 
                      method="POST" 
                      @submit.prevent="submitForm()"
                      class="needs-validation"
                      novalidate>
                    @csrf
                    <input type="hidden" name="carrier_slug" value="{{ $carrierSlug }}">
                    
                    <div class="card-body">
                        <!-- Nom de la liaison -->
                        <div class="mb-4">
                            <label for="integration_name" class="form-label">
                                <i class="fas fa-tag me-1 text-primary"></i>
                                Nom de la Liaison <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('integration_name') is-invalid @enderror" 
                                   id="integration_name" 
                                   name="integration_name" 
                                   value="{{ old('integration_name') }}"
                                   x-model="form.integration_name"
                                   placeholder="Ex: Boutique Principale, Entrepôt Tunis..."
                                   required>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Donnez un nom unique à cette configuration pour la différencier des autres
                            </div>
                            @error('integration_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if($carrierSlug === 'jax_delivery')
                            <!-- Configuration JAX Delivery -->
                            <div class="card bg-light bg-opacity-50 border-0 mb-4">
                                <div class="card-header bg-transparent border-0">
                                    <h6 class="mb-0 text-primary">
                                        <i class="fas fa-truck me-1"></i>
                                        Configuration JAX Delivery
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="username" class="form-label">
                                                <i class="fas fa-user me-1 text-primary"></i>
                                                Numéro de Compte <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" 
                                                   class="form-control @error('username') is-invalid @enderror" 
                                                   id="username" 
                                                   name="username" 
                                                   value="{{ old('username') }}"
                                                   x-model="form.username"
                                                   placeholder="Votre numéro de compte JAX"
                                                   required>
                                            <div class="form-text">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Numéro fourni lors de l'inscription JAX
                                            </div>
                                            @error('username')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="password" class="form-label">
                                                <i class="fas fa-key me-1 text-primary"></i>
                                                Token API <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <input :type="showPassword ? 'text' : 'password'" 
                                                       class="form-control @error('password') is-invalid @enderror" 
                                                       id="password" 
                                                       name="password" 
                                                       value="{{ old('password') }}"
                                                       x-model="form.password"
                                                       placeholder="Votre token API JAX"
                                                       required>
                                                <button class="btn btn-outline-secondary" 
                                                        type="button" 
                                                        @click="showPassword = !showPassword">
                                                    <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                                </button>
                                            </div>
                                            <div class="form-text">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Token généré dans votre espace client JAX
                                            </div>
                                            @error('password')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Informations spécifiques JAX -->
                                    <div class="alert alert-info border-0">
                                        <div class="d-flex align-items-start">
                                            <i class="fas fa-info-circle fa-lg me-3 mt-1"></i>
                                            <div>
                                                <h6 class="alert-heading">Spécifications JAX Delivery</h6>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <ul class="list-unstyled mb-0">
                                                            <li><i class="fas fa-check text-success me-2"></i>24 gouvernorats tunisiens</li>
                                                            <li><i class="fas fa-check text-success me-2"></i>Codes numériques (1-24)</li>
                                                            <li><i class="fas fa-check text-success me-2"></i>Bearer Token Auth</li>
                                                        </ul>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <ul class="list-unstyled mb-0">
                                                            <li><i class="fas fa-check text-success me-2"></i>Poids max: 30 kg</li>
                                                            <li><i class="fas fa-check text-success me-2"></i>COD max: 5000 TND</li>
                                                            <li><i class="fas fa-check text-success me-2"></i>Suivi temps réel</li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @elseif($carrierSlug === 'mes_colis')
                            <!-- Configuration Mes Colis Express -->
                            <div class="card bg-light bg-opacity-50 border-0 mb-4">
                                <div class="card-header bg-transparent border-0">
                                    <h6 class="mb-0 text-success">
                                        <i class="fas fa-shipping-fast me-1"></i>
                                        Configuration Mes Colis Express
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="username" class="form-label">
                                            <i class="fas fa-key me-1 text-success"></i>
                                            Token d'Accès <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">
                                                <i class="fas fa-shield-alt text-success"></i>
                                            </span>
                                            <input :type="showPassword ? 'text' : 'password'" 
                                                   class="form-control @error('username') is-invalid @enderror" 
                                                   id="username" 
                                                   name="username" 
                                                   value="{{ old('username') }}"
                                                   x-model="form.username"
                                                   placeholder="Votre token d'accès Mes Colis"
                                                   required>
                                            <button class="btn btn-outline-secondary" 
                                                    type="button" 
                                                    @click="showPassword = !showPassword">
                                                <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                            </button>
                                        </div>
                                        <div class="form-text">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Token unique fourni par Mes Colis Express
                                        </div>
                                        @error('username')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Informations spécifiques Mes Colis -->
                                    <div class="alert alert-success border-0">
                                        <div class="d-flex align-items-start">
                                            <i class="fas fa-info-circle fa-lg me-3 mt-1"></i>
                                            <div>
                                                <h6 class="alert-heading">Spécifications Mes Colis Express</h6>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <ul class="list-unstyled mb-0">
                                                            <li><i class="fas fa-check text-success me-2"></i>24 gouvernorats tunisiens</li>
                                                            <li><i class="fas fa-check text-success me-2"></i>Noms complets</li>
                                                            <li><i class="fas fa-check text-success me-2"></i>Header Token Auth</li>
                                                        </ul>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <ul class="list-unstyled mb-0">
                                                            <li><i class="fas fa-check text-success me-2"></i>Poids max: 25 kg</li>
                                                            <li><i class="fas fa-check text-success me-2"></i>COD max: 3000 TND</li>
                                                            <li><i class="fas fa-check text-success me-2"></i>Suivi temps réel</li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Options avancées -->
                        <div class="card bg-light bg-opacity-50 border-0 mb-4">
                            <div class="card-header bg-transparent border-0">
                                <h6 class="mb-0 text-muted">
                                    <i class="fas fa-cogs me-1"></i>
                                    Options Avancées
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   id="test_after_create"
                                                   x-model="testAfterCreate">
                                            <label class="form-check-label" for="test_after_create">
                                                <strong>Tester après création</strong>
                                                <br><small class="text-muted">Teste automatiquement la connexion</small>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   id="activate_after_test"
                                                   x-model="activateAfterTest">
                                            <label class="form-check-label" for="activate_after_test">
                                                <strong>Activer si test réussi</strong>
                                                <br><small class="text-muted">Active automatiquement la config</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer bg-transparent border-0 d-flex justify-content-between">
                        <a href="{{ route('admin.delivery.configuration') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>
                            Annuler
                        </a>
                        <div class="d-flex gap-2">
                            <button type="button" 
                                    class="btn btn-outline-primary"
                                    @click="testConnection()"
                                    :disabled="!canTest() || loading"
                                    x-show="!loading">
                                <i class="fas fa-wifi me-1"></i>
                                Tester la Connexion
                            </button>
                            <button type="submit" 
                                    class="btn btn-success"
                                    :disabled="loading">
                                <span x-show="loading">
                                    <i class="fas fa-spinner fa-spin me-1"></i>
                                    Création...
                                </span>
                                <span x-show="!loading">
                                    <i class="fas fa-save me-1"></i>
                                    Créer la Configuration
                                </span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Aide et informations -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4 animate-slide-up" style="animation-delay: 0.2s;">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle me-1"></i>
                        Aide Configuration
                    </h6>
                </div>
                <div class="card-body">
                    @if($carrierSlug === 'jax_delivery')
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-circle bg-primary bg-opacity-10 me-3">
                                <i class="fas fa-truck text-primary"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">JAX Delivery</h6>
                                <small class="text-muted">Transporteur national</small>
                            </div>
                        </div>
                        
                        <ul class="list-unstyled small">
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                Numéro de compte fourni lors de l'inscription
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                Token API généré dans votre espace JAX
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                Support codes gouvernorats 1-24
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                COD maximum: {{ $carrier['limits']['max_cod_amount'] ?? '5000' }} TND
                            </li>
                        </ul>
                        
                        @if(isset($carrier['website']))
                            <a href="{{ $carrier['website'] }}" target="_blank" class="btn btn-sm btn-outline-primary w-100">
                                <i class="fas fa-external-link-alt me-1"></i>
                                Espace Client JAX
                            </a>
                        @endif
                    @elseif($carrierSlug === 'mes_colis')
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-circle bg-success bg-opacity-10 me-3">
                                <i class="fas fa-shipping-fast text-success"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Mes Colis Express</h6>
                                <small class="text-muted">Livraisons express</small>
                            </div>
                        </div>
                        
                        <ul class="list-unstyled small">
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                Token unique d'authentification
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                Support noms gouvernorats complets
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                COD maximum: {{ $carrier['limits']['max_cod_amount'] ?? '3000' }} TND
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                Configuration simplifiée
                            </li>
                        </ul>
                        
                        @if(isset($carrier['website']))
                            <a href="{{ $carrier['website'] }}" target="_blank" class="btn btn-sm btn-outline-success w-100">
                                <i class="fas fa-external-link-alt me-1"></i>
                                Espace Client Mes Colis
                            </a>
                        @endif
                    @endif
                </div>
            </div>

            <!-- Contact support -->
            <div class="card border-0 shadow-sm animate-slide-up" style="animation-delay: 0.3s;">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-headset me-1"></i>
                        Besoin d'Aide ?
                    </h6>
                </div>
                <div class="card-body">
                    @if(isset($carrier['support_phone']) || isset($carrier['support_email']))
                        <p class="small text-muted mb-3">Contactez le support {{ $carrier['name'] }} :</p>
                        
                        @if(isset($carrier['support_phone']))
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-phone text-primary me-2"></i>
                                <a href="tel:{{ $carrier['support_phone'] }}" class="text-decoration-none">
                                    {{ $carrier['support_phone'] }}
                                </a>
                            </div>
                        @endif
                        
                        @if(isset($carrier['support_email']))
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-envelope text-primary me-2"></i>
                                <a href="mailto:{{ $carrier['support_email'] }}" class="text-decoration-none">
                                    {{ $carrier['support_email'] }}
                                </a>
                            </div>
                        @endif
                    @endif
                    
                    <div class="alert alert-warning border-0 bg-warning bg-opacity-10">
                        <i class="fas fa-lightbulb text-warning me-2"></i>
                        <strong>Astuce :</strong> Testez toujours votre configuration avant de l'utiliser en production.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de test -->
    <div class="modal fade" id="testModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title">
                        <i class="fas fa-wifi me-2"></i>
                        Test de Connexion
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center py-4" x-show="testInProgress">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Test en cours...</span>
                        </div>
                        <h5 class="text-primary">Test en cours...</h5>
                        <p class="text-muted">Connexion à {{ $carrier['name'] }}</p>
                    </div>
                    
                    <div x-show="testResult && !testInProgress">
                        <div x-show="testResult?.success" class="alert alert-success border-0">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle fa-2x me-3"></i>
                                <div>
                                    <h6 class="alert-heading mb-1">Test Réussi !</h6>
                                    <p class="mb-0" x-text="testResult?.message"></p>
                                </div>
                            </div>
                        </div>
                        
                        <div x-show="!testResult?.success" class="alert alert-danger border-0">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                                <div>
                                    <h6 class="alert-heading mb-1">Test Échoué</h6>
                                    <p class="mb-0" x-text="testResult?.error"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Fermer
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .carrier-logo-sm {
        width: 40px;
        height: 40px;
        object-fit: contain;
        background: var(--light-color);
        border-radius: var(--border-radius);
        padding: 6px;
        border: 1px solid var(--card-border);
    }

    .form-check-label strong {
        color: var(--text-color);
    }

    .form-switch .form-check-input:checked {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }

    .icon-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .breadcrumb-item + .breadcrumb-item::before {
        content: "›";
        color: var(--text-muted);
    }

    .breadcrumb-item a {
        color: var(--text-muted);
        text-decoration: none;
        transition: var(--transition);
    }

    .breadcrumb-item a:hover {
        color: var(--primary-color);
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(30, 64, 175, 0.25);
    }

    .input-group .form-control:focus {
        z-index: 3;
    }

    .modal-content {
        border-radius: var(--border-radius-lg);
        overflow: hidden;
    }

    .spinner-border {
        width: 3rem;
        height: 3rem;
    }

    .alert {
        border-radius: var(--border-radius);
    }

    .card-header {
        background: rgba(30, 64, 175, 0.03);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('configurationCreate', () => ({
        loading: false,
        showPassword: false,
        testInProgress: false,
        testResult: null,
        testAfterCreate: true,
        activateAfterTest: true,
        form: {
            integration_name: '',
            username: '',
            password: ''
        },
        
        canTest() {
            const carrierSlug = '{{ $carrierSlug }}';
            
            if (carrierSlug === 'jax_delivery') {
                return this.form.username && this.form.password;
            } else if (carrierSlug === 'mes_colis') {
                return this.form.username;
            }
            
            return false;
        },

        async testConnection() {
            if (!this.canTest()) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Champs manquants',
                    text: 'Veuillez remplir tous les champs requis pour tester la connexion'
                });
                return;
            }

            this.testInProgress = true;
            this.testResult = null;
            
            // Ouvrir la modal
            const modal = new bootstrap.Modal(document.getElementById('testModal'));
            modal.show();
            
            try {
                // Créer un FormData temporaire pour le test
                const testData = new FormData();
                testData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                testData.append('carrier_slug', '{{ $carrierSlug }}');
                testData.append('integration_name', this.form.integration_name || 'Test');
                testData.append('username', this.form.username);
                testData.append('password', this.form.password);
                testData.append('test_only', 'true');

                const response = await axios.post('{{ route("admin.delivery.configuration.store") }}', testData);
                
                this.testResult = {
                    success: true,
                    message: response.data.message || 'Connexion réussie'
                };
                
            } catch (error) {
                const errorMessage = error.response?.data?.error || 
                                   error.response?.data?.message || 
                                   'Impossible de se connecter au transporteur';
                
                this.testResult = {
                    success: false,
                    error: errorMessage
                };
            } finally {
                this.testInProgress = false;
            }
        },

        async submitForm() {
            if (!this.form.integration_name || !this.form.username) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Champs manquants',
                    text: 'Veuillez remplir tous les champs obligatoires'
                });
                return;
            }
            
            const carrierSlug = '{{ $carrierSlug }}';
            if (carrierSlug === 'jax_delivery' && !this.form.password) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Token API manquant',
                    text: 'Le token API est requis pour JAX Delivery'
                });
                return;
            }

            this.loading = true;
            
            try {
                const formData = new FormData();
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                formData.append('carrier_slug', carrierSlug);
                formData.append('integration_name', this.form.integration_name);
                formData.append('username', this.form.username);
                formData.append('password', this.form.password);

                const response = await axios.post('{{ route("admin.delivery.configuration.store") }}', formData);
                
                Swal.fire({
                    icon: 'success',
                    title: 'Configuration créée !',
                    text: 'La configuration a été créée avec succès',
                    showConfirmButton: false,
                    timer: 2000
                });
                
                // Rediriger vers la liste des configurations
                setTimeout(() => {
                    window.location.href = '{{ route("admin.delivery.configuration") }}';
                }, 2000);
                
            } catch (error) {
                let errorMessage = 'Erreur lors de la création';
                
                if (error.response?.status === 422) {
                    // Erreurs de validation
                    const errors = error.response.data.errors;
                    errorMessage = Object.values(errors).flat().join('\n');
                } else if (error.response?.data?.message) {
                    errorMessage = error.response.data.message;
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur de création',
                    text: errorMessage
                });
            } finally {
                this.loading = false;
            }
        }
    }));
});
</script>
@endpush