{{--
    Composant de formulaire de configuration pour les transporteurs
    
    Props:
    - $carrier: Array avec les informations du transporteur
    - $carrierSlug: String slug du transporteur (jax_delivery, mes_colis)
    - $config: Model DeliveryConfiguration (optionnel, pour édition)
    - $mode: String 'create' ou 'edit' (défaut: 'create')
    - $showTestButton: Boolean pour afficher le bouton test (défaut: true)
--}}

@php
    $mode = $mode ?? 'create';
    $showTestButton = $showTestButton ?? true;
    $isEdit = $mode === 'edit' && isset($config);
@endphp

<div class="configuration-form" x-data="configurationForm">
    <!-- Header du formulaire -->
    <div class="card-header py-3 d-flex align-items-center">
        <div class="me-3">
            @if(isset($carrier['logo']))
                <img src="{{ asset($carrier['logo']) }}" 
                     alt="{{ $carrier['name'] }}" 
                     class="carrier-logo">
            @else
                <div class="carrier-logo d-flex align-items-center justify-content-center">
                    <i class="fas fa-truck fa-lg text-muted"></i>
                </div>
            @endif
        </div>
        <div>
            <h6 class="m-0 font-weight-bold text-primary">{{ $carrier['name'] }}</h6>
            <small class="text-muted">
                {{ $carrier['description'] ?? 'Configuration des paramètres de connexion' }}
            </small>
        </div>
    </div>

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
                   value="{{ $isEdit ? $config->integration_name : old('integration_name') }}"
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

        <!-- Champs spécifiques selon le transporteur -->
        @if($carrierSlug === 'jax_delivery')
            @include('admin.delivery.components.forms.jax-fields', [
                'config' => $isEdit ? $config : null
            ])
        @elseif($carrierSlug === 'mes_colis')
            @include('admin.delivery.components.forms.mes-colis-fields', [
                'config' => $isEdit ? $config : null
            ])
        @endif

        <!-- Environnement -->
        <div class="mb-4">
            <label class="form-label">
                <i class="fas fa-server me-1"></i>
                Environnement
            </label>
            <div class="form-check">
                <input class="form-check-input" 
                       type="radio" 
                       name="environment" 
                       id="env_prod" 
                       value="prod" 
                       {{ ($isEdit ? $config->environment : 'prod') === 'prod' ? 'checked' : '' }}
                       x-model="form.environment">
                <label class="form-check-label" for="env_prod">
                    <strong>Production</strong>
                    <small class="text-muted d-block">Environnement de production (recommandé)</small>
                </label>
            </div>
        </div>

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
                                   id="test_after_save"
                                   x-model="options.testAfterSave">
                            <label class="form-check-label" for="test_after_save">
                                Tester la connexion après sauvegarde
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="activate_after_test"
                                   x-model="options.activateAfterTest">
                            <label class="form-check-label" for="activate_after_test">
                                Activer automatiquement si test réussi
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Paramètres supplémentaires -->
                <div class="mt-3">
                    <label class="form-label">
                        <i class="fas fa-sliders-h me-1"></i>
                        Paramètres Supplémentaires (JSON)
                    </label>
                    <textarea class="form-control" 
                              name="settings" 
                              rows="3"
                              x-model="form.settings"
                              placeholder='{"timeout": 30, "retry_count": 3}'>{{ $isEdit && $config->settings ? json_encode($config->settings, JSON_PRETTY_PRINT) : '' }}</textarea>
                    <small class="form-text text-muted">
                        Configuration JSON optionnelle pour des paramètres avancés
                    </small>
                </div>
            </div>
        </div>

        <!-- État de test -->
        <div x-show="testResult" class="alert" :class="testResult?.success ? 'alert-success' : 'alert-danger'">
            <div class="d-flex align-items-center">
                <i :class="testResult?.success ? 'fas fa-check-circle text-success' : 'fas fa-exclamation-circle text-danger'" class="me-2"></i>
                <div>
                    <strong x-text="testResult?.success ? 'Test réussi !' : 'Test échoué'"></strong>
                    <p class="mb-0" x-text="testResult?.message"></p>
                    <div x-show="testResult?.details" class="mt-2">
                        <small>
                            <strong>Détails:</strong>
                            <span x-text="JSON.stringify(testResult?.details, null, 2)"></span>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer avec actions -->
    <div class="card-footer d-flex justify-content-between">
        <div>
            <a href="{{ route('admin.delivery.configuration') }}" class="btn btn-secondary">
                <i class="fas fa-times me-1"></i>
                Annuler
            </a>
        </div>
        
        <div class="d-flex gap-2">
            @if($showTestButton)
                <button type="button" 
                        class="btn btn-outline-primary"
                        @click="testConnection()"
                        :disabled="!canTest() || loading"
                        x-show="!loading">
                    <i class="fas fa-wifi me-1"></i>
                    Tester la Connexion
                </button>
            @endif
            
            <button type="submit" 
                    class="btn btn-success"
                    :disabled="loading || !isFormValid()">
                <span x-show="loading">
                    <i class="fas fa-spinner fa-spin me-1"></i>
                    {{ $isEdit ? 'Mise à jour...' : 'Création...' }}
                </span>
                <span x-show="!loading">
                    <i class="fas fa-save me-1"></i>
                    {{ $isEdit ? 'Mettre à Jour' : 'Créer' }} la Configuration
                </span>
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('configurationForm', () => ({
        loading: false,
        showPassword: false,
        testResult: null,
        
        form: {
            integration_name: '{{ $isEdit ? $config->integration_name : '' }}',
            username: '{{ $isEdit ? $config->username : '' }}',
            password: '{{ $isEdit ? '' : '' }}', // Ne pas pré-remplir le mot de passe en édition
            environment: '{{ $isEdit ? $config->environment : 'prod' }}',
            settings: '{{ $isEdit && $config->settings ? json_encode($config->settings, JSON_PRETTY_PRINT) : '' }}'
        },
        
        options: {
            testAfterSave: true,
            activateAfterTest: true
        },

        isFormValid() {
            const carrierSlug = '{{ $carrierSlug }}';
            
            if (!this.form.integration_name || !this.form.username) {
                return false;
            }
            
            // Pour JAX Delivery, le password est requis
            if (carrierSlug === 'jax_delivery' && !this.form.password && {{ $isEdit ? 'false' : 'true' }}) {
                return false;
            }
            
            return true;
        },

        canTest() {
            const carrierSlug = '{{ $carrierSlug }}';
            
            if (!this.form.username) return false;
            
            if (carrierSlug === 'jax_delivery') {
                return this.form.password || {{ $isEdit ? 'true' : 'false' }};
            }
            
            return true;
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
            this.testResult = null;
            
            try {
                // Préparer les données de test
                const testData = new FormData();
                testData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                testData.append('carrier_slug', '{{ $carrierSlug }}');
                testData.append('integration_name', this.form.integration_name || 'Test');
                testData.append('username', this.form.username);
                if (this.form.password) {
                    testData.append('password', this.form.password);
                }
                testData.append('environment', this.form.environment);
                testData.append('test_only', 'true');

                const url = {{ $isEdit ? "'/admin/delivery/configuration/' + {$config->id} + '/test'" : "'/admin/delivery/configuration/test'" }};
                const response = await axios.post(url, testData);
                
                this.testResult = {
                    success: true,
                    message: response.data.message,
                    details: response.data.details
                };
                
                Swal.fire({
                    icon: 'success',
                    title: 'Test réussi !',
                    text: 'La connexion avec {{ $carrier['name'] }} est fonctionnelle',
                    showConfirmButton: true,
                });
                
            } catch (error) {
                const errorMessage = error.response?.data?.error || 
                                   error.response?.data?.message || 
                                   'Impossible de se connecter au transporteur';
                
                this.testResult = {
                    success: false,
                    message: errorMessage,
                    details: error.response?.data
                };
                
                Swal.fire({
                    icon: 'error',
                    title: 'Test échoué',
                    text: errorMessage,
                });
            } finally {
                this.loading = false;
            }
        },

        async submitForm() {
            if (!this.isFormValid()) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Formulaire incomplet',
                    text: 'Veuillez remplir tous les champs obligatoires',
                });
                return false;
            }
            
            // Valider le JSON des settings si fourni
            if (this.form.settings) {
                try {
                    JSON.parse(this.form.settings);
                } catch (e) {
                    Swal.fire({
                        icon: 'error',
                        title: 'JSON invalide',
                        text: 'Le format des paramètres supplémentaires n\'est pas valide',
                    });
                    return false;
                }
            }
            
            return true;
        }
    }));
});
</script>
@endpush

@push('styles')
<style>
.configuration-form .carrier-logo {
    width: 40px;
    height: 40px;
    object-fit: contain;
    background: #f8f9fa;
    border-radius: 6px;
    padding: 6px;
    border: 1px solid #dee2e6;
}

.configuration-form .form-check-label small {
    font-size: 0.875em;
    margin-top: 2px;
}

.configuration-form pre {
    font-size: 0.75rem;
    max-height: 200px;
    overflow-y: auto;
}
</style>
@endpush