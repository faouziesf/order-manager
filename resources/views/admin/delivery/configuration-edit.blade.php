@extends('layouts.admin')

@section('title', 'Modifier Configuration - ' . $config->integration_name)

@section('content')
<div class="container-fluid" x-data="configurationEdit">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-edit text-primary me-2"></i>
                Modifier Configuration
            </h1>
            <p class="text-muted mb-0">{{ $config->integration_name }} - {{ $carrier['name'] }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.delivery.configuration') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Retour
            </a>
            @if($config->is_active)
                <span class="badge bg-success px-3 py-2">
                    <i class="fas fa-check me-1"></i>
                    Configuration Active
                </span>
            @else
                <span class="badge bg-warning px-3 py-2">
                    <i class="fas fa-pause me-1"></i>
                    Configuration Inactive
                </span>
            @endif
        </div>
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
                        <small class="text-muted">Configuration ID: {{ $config->id }}</small>
                    </div>
                </div>
                
                <form action="{{ route('admin.delivery.configuration.update', $config) }}" 
                      method="POST" 
                      @submit.prevent="submitForm()">
                    @csrf
                    @method('PATCH')
                    
                    <div class="card-body">
                        <!-- Alertes -->
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

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
                                   value="{{ old('integration_name', $config->integration_name) }}"
                                   x-model="form.integration_name"
                                   required>
                            <div class="form-text">
                                Nom unique pour identifier cette configuration
                            </div>
                            @error('integration_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if($config->carrier_slug === 'jax_delivery')
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
                                           value="{{ old('username', $config->username) }}"
                                           x-model="form.username"
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
                                               placeholder="Laisser vide pour conserver le token actuel">
                                        <button class="btn btn-outline-secondary" 
                                                type="button" 
                                                @click="showPassword = !showPassword">
                                            <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">
                                        Laisser vide pour conserver le token actuel
                                    </div>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        @elseif($config->carrier_slug === 'mes_colis')
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
                                           value="{{ old('username', $config->username) }}"
                                           x-model="form.username"
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

                        <!-- Informations de statut -->
                        <div class="card bg-light border-0 mb-4">
                            <div class="card-header bg-transparent border-0 py-2">
                                <h6 class="mb-0 text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Informations sur la Configuration
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <strong>Statut actuel:</strong>
                                            @if($config->is_active)
                                                <span class="badge bg-success ms-2">
                                                    <i class="fas fa-check me-1"></i>
                                                    Actif
                                                </span>
                                            @else
                                                <span class="badge bg-warning ms-2">
                                                    <i class="fas fa-pause me-1"></i>
                                                    Inactif
                                                </span>
                                            @endif
                                        </div>
                                        <div class="mb-3">
                                            <strong>Environnement:</strong>
                                            <span class="badge bg-{{ $config->environment === 'prod' ? 'danger' : 'warning' }} ms-2">
                                                {{ ucfirst($config->environment) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <strong>Créé le:</strong>
                                            <span class="ms-2">{{ $config->created_at->format('d/m/Y à H:i') }}</span>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Dernière modification:</strong>
                                            <span class="ms-2">{{ $config->updated_at->format('d/m/Y à H:i') }}</span>
                                        </div>
                                    </div>
                                </div>

                                @if($config->settings && isset($config->settings['last_test_at']))
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="alert {{ $config->settings['last_test_success'] ?? false ? 'alert-success' : 'alert-warning' }} py-2">
                                                <small>
                                                    <i class="fas fa-{{ $config->settings['last_test_success'] ?? false ? 'check' : 'exclamation-triangle' }} me-1"></i>
                                                    <strong>Dernier test:</strong> 
                                                    {{ \Carbon\Carbon::parse($config->settings['last_test_at'])->format('d/m/Y à H:i') }}
                                                    - {{ $config->settings['last_test_success'] ?? false ? 'Réussi' : 'Échoué' }}
                                                    @if(isset($config->settings['last_test_error']))
                                                        <br>{{ $config->settings['last_test_error'] }}
                                                    @endif
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-between">
                        <div class="d-flex gap-2">
                            <button type="button" 
                                    class="btn btn-outline-danger"
                                    @click="deleteConfiguration()"
                                    x-show="!loading">
                                <i class="fas fa-trash me-1"></i>
                                Supprimer
                            </button>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="button" 
                                    class="btn btn-outline-primary"
                                    @click="testConnection()"
                                    :disabled="!canTest()"
                                    x-show="!loading">
                                <i class="fas fa-wifi me-1"></i>
                                Tester la Connexion
                            </button>
                            
                            <button type="button" 
                                    class="btn btn-{{ $config->is_active ? 'warning' : 'success' }}"
                                    @click="toggleStatus()"
                                    x-show="!loading">
                                <i class="fas fa-{{ $config->is_active ? 'pause' : 'play' }} me-1"></i>
                                {{ $config->is_active ? 'Désactiver' : 'Activer' }}
                            </button>
                            
                            <button type="submit" 
                                    class="btn btn-primary"
                                    :disabled="loading">
                                <span x-show="loading">
                                    <i class="fas fa-spinner fa-spin me-1"></i>
                                    Sauvegarde...
                                </span>
                                <span x-show="!loading">
                                    <i class="fas fa-save me-1"></i>
                                    Sauvegarder
                                </span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar avec statistiques et actions -->
        <div class="col-lg-4">
            <!-- Statistiques d'utilisation -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar me-1"></i>
                        Statistiques d'Utilisation
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="h4 mb-0 text-primary" x-text="stats.pickups"></div>
                            <small class="text-muted">Enlèvements</small>
                        </div>
                        <div class="col-6">
                            <div class="h4 mb-0 text-success" x-text="stats.shipments"></div>
                            <small class="text-muted">Expéditions</small>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <div class="h5 mb-0 text-info" x-text="stats.total_amount"></div>
                        <small class="text-muted">Total COD (TND)</small>
                    </div>
                </div>
            </div>

            <!-- Actions rapides -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-rocket me-1"></i>
                        Actions Rapides
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($config->is_active)
                            <a href="{{ route('admin.delivery.preparation') }}?carrier={{ $config->carrier_slug }}" 
                               class="btn btn-success">
                                <i class="fas fa-plus me-1"></i>
                                Créer un Enlèvement
                            </a>
                        @endif
                        
                        <a href="{{ route('admin.delivery.pickups') }}?carrier={{ $config->carrier_slug }}" 
                           class="btn btn-outline-primary">
                            <i class="fas fa-truck me-1"></i>
                            Voir les Enlèvements
                        </a>
                        
                        <a href="{{ route('admin.delivery.shipments') }}?carrier={{ $config->carrier_slug }}" 
                           class="btn btn-outline-info">
                            <i class="fas fa-shipping-fast me-1"></i>
                            Voir les Expéditions
                        </a>
                        
                        <button class="btn btn-outline-secondary" @click="duplicateConfiguration()">
                            <i class="fas fa-copy me-1"></i>
                            Dupliquer Configuration
                        </button>
                    </div>
                </div>
            </div>

            <!-- Informations transporteur -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-info-circle me-1"></i>
                        Informations Transporteur
                    </h6>
                </div>
                <div class="card-body">
                    @if(isset($carrier['website']))
                        <p class="mb-2">
                            <i class="fas fa-globe text-primary me-2"></i>
                            <a href="{{ $carrier['website'] }}" target="_blank">Site Web</a>
                        </p>
                    @endif
                    
                    @if(isset($carrier['support_phone']))
                        <p class="mb-2">
                            <i class="fas fa-phone text-success me-2"></i>
                            <a href="tel:{{ $carrier['support_phone'] }}">{{ $carrier['support_phone'] }}</a>
                        </p>
                    @endif
                    
                    @if(isset($carrier['support_email']))
                        <p class="mb-2">
                            <i class="fas fa-envelope text-info me-2"></i>
                            <a href="mailto:{{ $carrier['support_email'] }}">{{ $carrier['support_email'] }}</a>
                        </p>
                    @endif

                    <hr>
                    <small class="text-muted">
                        <strong>Limites:</strong><br>
                        • Poids max: {{ $carrier['limits']['max_weight'] ?? 'N/A' }} kg<br>
                        • COD max: {{ $carrier['limits']['max_cod_amount'] ?? 'N/A' }} TND
                    </small>
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
    Alpine.data('configurationEdit', () => ({
        loading: false,
        showPassword: false,
        form: {
            integration_name: '{{ old('integration_name', $config->integration_name) }}',
            username: '{{ old('username', $config->username) }}',
            password: '{{ old('password') }}'
        },
        stats: {
            pickups: 0,
            shipments: 0,
            total_amount: '0.000'
        },
        
        init() {
            this.loadStats();
        },

        async loadStats() {
            // TODO: Charger les vraies statistiques via API
            // Simulation pour la démonstration
            this.stats = {
                pickups: Math.floor(Math.random() * 20),
                shipments: Math.floor(Math.random() * 50),
                total_amount: (Math.random() * 5000).toFixed(3)
            };
        },
        
        canTest() {
            const carrierSlug = '{{ $config->carrier_slug }}';
            
            if (carrierSlug === 'jax_delivery') {
                return this.form.username && (this.form.password || '{{ $config->password ? "true" : "false" }}' === 'true');
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
                const response = await axios.post('{{ route("admin.delivery.configuration.test", $config) }}');
                
                if (response.data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Test réussi !',
                        text: response.data.message,
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
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur de test',
                    text: error.response?.data?.message || 'Erreur lors du test de connexion',
                });
            } finally {
                this.loading = false;
            }
        },

        async toggleStatus() {
            const action = {{ $config->is_active ? 'false' : 'true' }} ? 'activer' : 'désactiver';
            
            const result = await Swal.fire({
                title: `${action.charAt(0).toUpperCase() + action.slice(1)} la configuration ?`,
                text: `Cette action va ${action} la configuration`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: action.charAt(0).toUpperCase() + action.slice(1),
                cancelButtonText: 'Annuler'
            });

            if (!result.isConfirmed) return;

            try {
                const response = await axios.post('{{ route("admin.delivery.configuration.toggle", $config) }}');
                
                if (response.data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Statut mis à jour !',
                        text: response.data.message,
                        showConfirmButton: false,
                        timer: 2000
                    });
                    
                    setTimeout(() => window.location.reload(), 2000);
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Impossible de changer le statut',
                });
            }
        },

        async deleteConfiguration() {
            const result = await Swal.fire({
                title: 'Supprimer la configuration ?',
                text: 'Cette action est irréversible !',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Oui, supprimer !',
                cancelButtonText: 'Annuler'
            });

            if (!result.isConfirmed) return;

            try {
                await axios.delete('{{ route("admin.delivery.configuration.delete", $config) }}');
                
                Swal.fire({
                    icon: 'success',
                    title: 'Supprimé !',
                    text: 'Configuration supprimée avec succès',
                    showConfirmButton: false,
                    timer: 2000
                });
                
                setTimeout(() => {
                    window.location.href = '{{ route("admin.delivery.configuration") }}';
                }, 2000);
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: error.response?.data?.message || 'Impossible de supprimer',
                });
            }
        },

        async duplicateConfiguration() {
            const { value: newName } = await Swal.fire({
                title: 'Dupliquer la configuration',
                input: 'text',
                inputLabel: 'Nom de la nouvelle configuration',
                inputValue: this.form.integration_name + ' - Copie',
                showCancelButton: true,
                inputValidator: (value) => {
                    if (!value) {
                        return 'Vous devez saisir un nom !'
                    }
                }
            });

            if (newName) {
                window.location.href = `{{ route('admin.delivery.configuration.create') }}?carrier={{ $config->carrier_slug }}&duplicate={{ $config->id }}&name=${encodeURIComponent(newName)}`;
            }
        },

        async submitForm() {
            this.loading = true;
            
            try {
                const formData = new FormData();
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                formData.append('_method', 'PATCH');
                formData.append('integration_name', this.form.integration_name);
                formData.append('username', this.form.username);
                if (this.form.password) {
                    formData.append('password', this.form.password);
                }

                const response = await axios.post('{{ route("admin.delivery.configuration.update", $config) }}', formData);
                
                Swal.fire({
                    icon: 'success',
                    title: 'Configuration mise à jour !',
                    text: 'Les modifications ont été sauvegardées',
                    showConfirmButton: false,
                    timer: 2000
                });
                
                setTimeout(() => window.location.reload(), 2000);
                
            } catch (error) {
                let errorMessage = 'Erreur lors de la sauvegarde';
                
                if (error.response?.status === 422) {
                    const errors = error.response.data.errors;
                    errorMessage = Object.values(errors).flat().join('\n');
                } else if (error.response?.data?.message) {
                    errorMessage = error.response.data.message;
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur de sauvegarde',
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
    border: 1px solid #dee2e6;
}
</style>
@endpush