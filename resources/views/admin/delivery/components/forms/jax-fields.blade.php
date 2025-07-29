{{--
    Champs de formulaire spécifiques à JAX Delivery
    
    Props:
    - $config: Model DeliveryConfiguration (optionnel, pour édition)
--}}

@php
    $isEdit = isset($config) && $config;
@endphp

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
               value="{{ $isEdit ? $config->username : old('username') }}"
               x-model="form.username"
               placeholder="Votre numéro de compte JAX"
               required>
        <div class="form-text">
            <i class="fas fa-info-circle me-1"></i>
            Numéro de compte fourni par JAX Delivery lors de l'inscription
        </div>
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
                   value="{{ $isEdit ? '' : old('password') }}"
                   x-model="form.password"
                   placeholder="{{ $isEdit ? 'Laisser vide pour conserver l\'actuel' : 'Votre token API JAX' }}"
                   {{ $isEdit ? '' : 'required' }}>
            <button class="btn btn-outline-secondary" 
                    type="button" 
                    @click="showPassword = !showPassword">
                <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
            </button>
        </div>
        <div class="form-text">
            <i class="fas fa-info-circle me-1"></i>
            Token d'authentification généré dans votre espace client JAX
            @if($isEdit)
                <br><small class="text-muted">Laissez vide pour conserver le token actuel</small>
            @endif
        </div>
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<!-- Informations spécifiques JAX -->
<div class="card bg-info bg-opacity-10 border-info mb-4">
    <div class="card-body">
        <h6 class="card-title text-info">
            <i class="fas fa-info-circle me-1"></i>
            Spécifications JAX Delivery
        </h6>
        <div class="row">
            <div class="col-md-6">
                <ul class="list-unstyled mb-0">
                    <li><i class="fas fa-check text-success me-2"></i>Support des 24 gouvernorats tunisiens</li>
                    <li><i class="fas fa-check text-success me-2"></i>Codes gouvernorats numériques (1-24)</li>
                    <li><i class="fas fa-check text-success me-2"></i>Authentification Bearer Token</li>
                </ul>
            </div>
            <div class="col-md-6">
                <ul class="list-unstyled mb-0">
                    <li><i class="fas fa-check text-success me-2"></i>Poids maximum : 30 kg</li>
                    <li><i class="fas fa-check text-success me-2"></i>COD maximum : 5000 TND</li>
                    <li><i class="fas fa-check text-success me-2"></i>Suivi en temps réel</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Validation temps réel -->
<div x-show="form.username || form.password" class="mb-3">
    <div class="alert alert-light border">
        <div class="d-flex align-items-center">
            <div class="me-3">
                <i :class="getValidationIcon()" class="fa-lg"></i>
            </div>
            <div>
                <strong x-text="getValidationMessage()"></strong>
                <br>
                <small class="text-muted" x-text="getValidationDetails()"></small>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Extension pour les méthodes spécifiques à JAX
document.addEventListener('alpine:init', () => {
    Alpine.store('jaxValidation', {
        validateAccount(accountNumber) {
            // Validation basique du numéro de compte JAX
            if (!accountNumber) return { valid: false, message: 'Numéro de compte requis' };
            if (accountNumber.length < 3) return { valid: false, message: 'Numéro de compte trop court' };
            if (!/^[a-zA-Z0-9]+$/.test(accountNumber)) return { valid: false, message: 'Format invalide (lettres et chiffres uniquement)' };
            return { valid: true, message: 'Format valide' };
        },
        
        validateToken(token) {
            // Validation basique du token JAX
            if (!token) return { valid: false, message: 'Token API requis' };
            if (token.length < 10) return { valid: false, message: 'Token trop court' };
            return { valid: true, message: 'Format valide' };
        }
    });
});

// Ajouter les méthodes au composant principal
function extendConfigFormForJax() {
    return {
        getValidationIcon() {
            const accountValid = Alpine.store('jaxValidation').validateAccount(this.form.username).valid;
            const tokenValid = this.form.password ? Alpine.store('jaxValidation').validateToken(this.form.password).valid : {{ $isEdit ? 'true' : 'false' }};
            
            if (accountValid && tokenValid) return 'fas fa-check-circle text-success';
            if (!this.form.username && !this.form.password) return 'fas fa-info-circle text-info';
            return 'fas fa-exclamation-circle text-warning';
        },
        
        getValidationMessage() {
            const accountValid = Alpine.store('jaxValidation').validateAccount(this.form.username).valid;
            const tokenValid = this.form.password ? Alpine.store('jaxValidation').validateToken(this.form.password).valid : {{ $isEdit ? 'true' : 'false' }};
            
            if (accountValid && tokenValid) return 'Configuration JAX Delivery valide';
            if (!this.form.username && !this.form.password) return 'Saisissez vos identifiants JAX Delivery';
            return 'Vérifiez vos identifiants';
        },
        
        getValidationDetails() {
            const accountValidation = Alpine.store('jaxValidation').validateAccount(this.form.username);
            const tokenValidation = this.form.password ? Alpine.store('jaxValidation').validateToken(this.form.password) : { valid: {{ $isEdit ? 'true' : 'false' }}, message: '{{ $isEdit ? 'Token conservé' : 'Token requis' }}' };
            
            return `Compte: ${accountValidation.message} • Token: ${tokenValidation.message}`;
        }
    };
}
</script>
@endpush