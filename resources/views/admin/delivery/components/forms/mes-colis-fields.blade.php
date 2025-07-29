{{--
    Champs de formulaire spécifiques à Mes Colis Express
    
    Props:
    - $config: Model DeliveryConfiguration (optionnel, pour édition)
--}}

@php
    $isEdit = isset($config) && $config;
@endphp

<!-- Configuration Mes Colis Express -->
<div class="mb-4">
    <label for="username" class="form-label">
        <i class="fas fa-key me-1"></i>
        Token d'Accès <span class="text-danger">*</span>
    </label>
    <div class="input-group">
        <span class="input-group-text">
            <i class="fas fa-shield-alt text-success"></i>
        </span>
        <input :type="showPassword ? 'text' : 'password'" 
               class="form-control @error('username') is-invalid @enderror" 
               id="username" 
               name="username" 
               value="{{ $isEdit ? $config->username : old('username') }}"
               x-model="form.username"
               placeholder="{{ $isEdit ? 'Laisser vide pour conserver l\'actuel' : 'Votre token d\'accès Mes Colis' }}"
               {{ $isEdit ? '' : 'required' }}>
        <button class="btn btn-outline-secondary" 
                type="button" 
                @click="showPassword = !showPassword">
            <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
        </button>
    </div>
    <div class="form-text">
        <i class="fas fa-info-circle me-1"></i>
        Token unique d'authentification fourni par Mes Colis Express
        @if($isEdit)
            <br><small class="text-muted">Laissez vide pour conserver le token actuel</small>
        @endif
    </div>
    @error('username')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<!-- Informations spécifiques Mes Colis -->
<div class="card bg-success bg-opacity-10 border-success mb-4">
    <div class="card-body">
        <h6 class="card-title text-success">
            <i class="fas fa-info-circle me-1"></i>
            Spécifications Mes Colis Express
        </h6>
        <div class="row">
            <div class="col-md-6">
                <ul class="list-unstyled mb-0">
                    <li><i class="fas fa-check text-success me-2"></i>Support des 24 gouvernorats tunisiens</li>
                    <li><i class="fas fa-check text-success me-2"></i>Noms gouvernorats complets</li>
                    <li><i class="fas fa-check text-success me-2"></i>Authentification par header token</li>
                </ul>
            </div>
            <div class="col-md-6">
                <ul class="list-unstyled mb-0">
                    <li><i class="fas fa-check text-success me-2"></i>Poids maximum : 25 kg</li>
                    <li><i class="fas fa-check text-success me-2"></i>COD maximum : 3000 TND</li>
                    <li><i class="fas fa-check text-success me-2"></i>Suivi en temps réel</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Configuration simplifiée -->
<div class="alert alert-info">
    <div class="d-flex align-items-start">
        <i class="fas fa-lightbulb fa-lg text-warning me-3 mt-1"></i>
        <div>
            <strong>Configuration Simplifiée</strong>
            <p class="mb-0">
                Mes Colis Express utilise une approche simplifiée avec un seul token d'authentification. 
                Pas besoin de paramètres supplémentaires - le token suffit pour toutes les opérations.
            </p>
        </div>
    </div>
</div>

<!-- Test de format du token -->
<div x-show="form.username" class="mb-3">
    <div class="alert alert-light border">
        <div class="d-flex align-items-center">
            <div class="me-3">
                <i :class="getTokenValidationIcon()" class="fa-lg"></i>
            </div>
            <div>
                <strong x-text="getTokenValidationMessage()"></strong>
                <br>
                <small class="text-muted" x-text="getTokenValidationDetails()"></small>
            </div>
        </div>
    </div>
</div>

<!-- Aide à la configuration -->
<div class="card border-0 bg-light">
    <div class="card-body">
        <h6 class="card-title">
            <i class="fas fa-question-circle me-1"></i>
            Comment obtenir votre token ?
        </h6>
        <ol class="mb-0">
            <li>Connectez-vous à votre espace client Mes Colis Express</li>
            <li>Naviguez vers la section "API & Intégrations"</li>
            <li>Générez un nouveau token d'accès pour votre application</li>
            <li>Copiez le token et collez-le dans le champ ci-dessus</li>
        </ol>
        
        <div class="mt-3">
            <a href="https://api.mescolis.tn/docs" 
               target="_blank" 
               class="btn btn-sm btn-outline-success">
                <i class="fas fa-external-link-alt me-1"></i>
                Documentation API
            </a>
            <a href="https://mescolis.tn/contact" 
               target="_blank" 
               class="btn btn-sm btn-outline-secondary ms-2">
                <i class="fas fa-headset me-1"></i>
                Support Client
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Extension pour les méthodes spécifiques à Mes Colis
document.addEventListener('alpine:init', () => {
    Alpine.store('mesColisValidation', {
        validateToken(token) {
            // Validation basique du token Mes Colis
            if (!token) return { valid: false, message: 'Token requis', level: 'error' };
            if (token.length < 20) return { valid: false, message: 'Token trop court (minimum 20 caractères)', level: 'warning' };
            if (token.length > 200) return { valid: false, message: 'Token trop long (maximum 200 caractères)', level: 'warning' };
            
            // Vérification du format (généralement alphanumerique avec quelques caractères spéciaux)
            if (!/^[a-zA-Z0-9\-_\.]+$/.test(token)) {
                return { valid: false, message: 'Format invalide (lettres, chiffres, tirets et points uniquement)', level: 'warning' };
            }
            
            return { valid: true, message: 'Format valide', level: 'success' };
        }
    });
});

// Ajouter les méthodes au composant principal
function extendConfigFormForMesColis() {
    return {
        getTokenValidationIcon() {
            const validation = Alpine.store('mesColisValidation').validateToken(this.form.username);
            
            switch(validation.level) {
                case 'success': return 'fas fa-check-circle text-success';
                case 'warning': return 'fas fa-exclamation-triangle text-warning';
                case 'error': return 'fas fa-times-circle text-danger';
                default: return 'fas fa-info-circle text-info';
            }
        },
        
        getTokenValidationMessage() {
            if (!this.form.username) return 'Saisissez votre token Mes Colis Express';
            
            const validation = Alpine.store('mesColisValidation').validateToken(this.form.username);
            return validation.valid ? 'Token Mes Colis Express valide' : 'Problème avec le token';
        },
        
        getTokenValidationDetails() {
            if (!this.form.username) return 'Le token est l\'unique identifiant requis pour Mes Colis Express';
            
            const validation = Alpine.store('mesColisValidation').validateToken(this.form.username);
            return validation.message;
        }
    };
}
</script>
@endpush