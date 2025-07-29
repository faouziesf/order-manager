@extends('layouts.admin')

@section('title', 'Nouvelle Configuration - ' . $carrier['name'])

@section('content')
<div class="container-fluid" x-data="configurationCreate">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-plus text-primary me-2"></i>
                Nouvelle Configuration
            </h1>
            <p class="text-muted mb-0">Configurer {{ $carrier['name'] }}</p>
        </div>
        <a href="{{ route('admin.delivery.configuration') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Retour
        </a>
    </div>

    <div class="row">
        <!-- Formulaire principal -->
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3 d-flex align-items-center">
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
                      @submit.prevent="submitForm()">
                    @csrf
                    <input type="hidden" name="carrier_slug" value="{{ $carrierSlug }}">
                    
                    <div class="card-body">
                        <!-- Nom de la liaison -->
                        <div class="mb-4">
                            <label for="integration_name" class="form-label">
                                <i class="fas fa-tag me-1"></i>
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
                                Donnez un nom unique à cette configuration pour la différencier des autres
                            </div>
                            @error('integration_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if($carrierSlug === 'jax_delivery')
                            <!-- Configuration JAX Delivery -->
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="username" class="form-label">
                                        <i class="fas fa-user me-1"></i>
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
                                    @error('username')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6 mb-4">
                                    <label for="password" class="form-label">
                                        <i class="fas fa-key me-1"></i>
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
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        @elseif($carrierSlug === 'mes_colis')
                            <!-- Configuration Mes Colis Express -->
                            <div class="mb-4">
                                <label for="username" class="form-label">
                                    <i class="fas fa-key me-1"></i>
                                    Token API <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
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
                                @error('username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                        <!-- Options avancées -->
                        <div class="card bg-light border-0 mb-4">
                            <div class="card-header bg-transparent border-0 py-2">
                                <h6 class="mb-0 text-muted">
                                    <i class="fas fa-cogs me-1"></i>
                                    Options Avancées
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   id="test_after_create"
                                                   x-model="testAfterCreate">
                                            <label class="form-check-label" for="test_after_create">
                                                Tester la connexion après création
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   id="activate_after_test"
                                                   x-model="activateAfterTest">
                                            <label class="form-check-label" for="activate_after_test">
                                                Activer automatiquement si test réussi
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-between">
                        <a href="{{ route('admin.delivery.configuration') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>
                            Annuler
                        </a>
                        <div class="d-flex gap-2">
                            <button type="button" 
                                    class="btn btn-outline-primary"
                                    @click="testConnection()"
                                    :disabled="!canTest()"
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
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle me-1"></i>
                        Aide Configuration
                    </h6>
                </div>
                <div class="card-body">
                    @if($carrierSlug === 'jax_delivery')
                        <h6 class="text-dark">JAX Delivery</h6>
                        <ul class="list-unstyled small text-muted">
                            <li><i class="fas fa-check text-success me-1"></i> Numéro de compte : Fourni lors de l'inscription</li>
                            <li><i class="fas fa-check text-success me-1"></i> Token API : Généré dans votre espace JAX</li>
                            <li><i class="fas fa-check text-success me-1"></i> Support codes gouvernorats 1-24</li>
                            <li><i class="fas fa-check text-success me-1"></i> COD maximum : {{ $carrier['limits']['max_cod_amount'] ?? 'Non défini' }} TND</li>
                        </ul>
                        
                        @if(isset($carrier['website']))
                            <a href="{{ $carrier['website'] }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-external-link-alt me-1"></i>
                                Espace Client JAX
                            </a>
                        @endif
                    @elseif($carrierSlug === 'mes_colis')
                        <h6 class="text-dark">Mes Colis Express</h6>
                        <ul class="list-unstyled small text-muted">
                            <li><i class="fas fa-check text-success me-1"></i> Token unique d'authentification</li>
                            <li><i class="fas fa-check text-success me-1"></i> Support noms gouvernorats complets</li>
                            <li><i class="fas fa-check text-success me-1"></i> COD maximum : {{ $carrier['limits']['max_cod_amount'] ?? 'Non défini' }} TND</li>
                        </ul>
                        
                        @if(isset($carrier['website']))
                            <a href="{{ $carrier['website'] }}" target="_blank" class="btn btn-sm btn-outline-success">
                                <i class="fas fa-external-link-alt me-1"></i>
                                Espace Client Mes Colis
                            </a>
                        @endif
                    @endif
                </div>
            </div>

            <!-- Contact support -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-headset me-1"></i>
                        Besoin d'Aide ?
                    </h6>
                </div>
                <div class="card-body">
                    @if(isset($carrier['support_phone']) || isset($carrier['support_email']))
                        <p class="small text-muted mb-2">Contactez le support {{ $carrier['name'] }} :</p>
                        
                        @if(isset($carrier['support_phone']))
                            <p class="mb-1">
                                <i class="fas fa-phone text-primary me-2"></i>
                                <a href="tel:{{ $carrier['support_phone'] }}">{{ $carrier['support_phone'] }}</a>
                            </p>
                        @endif
                        
                        @if(isset($carrier['support_email']))
                            <p class="mb-1">
                                <i class="fas fa-envelope text-primary me-2"></i>
                                <a href="mailto:{{ $carrier['support_email'] }}">{{ $carrier['support_email'] }}</a>
                            </p>
                        @endif
                    @endif
                    
                    <hr class="my-3">
                    <p class="small text-muted">
                        <i class="fas fa-lightbulb text-warning me-1"></i>
                        Astuce : Testez toujours votre configuration avant de l'utiliser en production.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin.delivery.modals.test-connection')
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('configurationCreate', () => ({
        loading: false,
        showPassword: false,
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
                    text: 'Veuillez remplir tous les champs requis pour tester la connexion',
                });
                return;
            }

            this.loading = true;
            
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
                
                if (response.data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Test réussi !',
                        text: 'La connexion avec {{ $carrier['name'] }} est fonctionnelle',
                        showConfirmButton: true,
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Test échoué',
                        text: response.data.error || 'Impossible de se connecter au transporteur',
                    });
                }
            } catch (error) {
                let errorMessage = 'Erreur lors du test de connexion';
                
                if (error.response?.data?.message) {
                    errorMessage = error.response.data.message;
                } else if (error.response?.data?.error) {
                    errorMessage = error.response.data.error;
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur de test',
                    text: errorMessage,
                });
            } finally {
                this.loading = false;
            }
        },

        async submitForm() {
            if (!this.form.integration_name || !this.form.username) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Champs manquants',
                    text: 'Veuillez remplir tous les champs obligatoires',
                });
                return;
            }
            
            const carrierSlug = '{{ $carrierSlug }}';
            if (carrierSlug === 'jax_delivery' && !this.form.password) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Token API manquant',
                    text: 'Le token API est requis pour JAX Delivery',
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
                    text: errorMessage,
                });
            } finally {
                this.loading = false;
            }
        }
    }));
});
</script>
@endpush

@push('styles')
<style>
.carrier-logo-sm {
    width: 40px;
    height: 40px;
    object-fit: contain;
    background: #f8f9fa;
    border-radius: 6px;
    padding: 6px;
}

.card-header .carrier-logo-sm {
    border: 1px solid #dee2e6;
}
</style>
@endpush