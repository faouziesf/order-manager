@extends('layouts.admin')

@section('title', 'Nouvelle Configuration - ' . $carrier['name'])

@section('css')
<style>
    :root {
        --royal-blue: #1e3a8a;
        --royal-blue-light: #3b82f6;
        --royal-blue-lighter: #60a5fa;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --info: #06b6d4;
        --light: #f8fafc;
        --dark: #1f2937;
        --border: #e5e7eb;
        --shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        --shadow-lg: 0 8px 25px rgba(0, 0, 0, 0.1);
        --radius: 8px;
        --transition: all 0.2s ease;
    }

    body {
        background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
        font-family: 'Inter', system-ui, sans-serif;
    }

    /* ===== CONTAINER PRINCIPAL ===== */
    .config-container {
        background: white;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        margin: 1rem;
        min-height: calc(100vh - 100px);
        overflow: hidden;
    }

    /* ===== HEADER ===== */
    .config-header {
        background: linear-gradient(135deg, var(--royal-blue) 0%, var(--royal-blue-light) 100%);
        padding: 1.5rem;
        color: white;
        position: relative;
    }

    .config-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 150px;
        height: 150px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }

    .header-content {
        position: relative;
        z-index: 2;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .header-info h1 {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .header-info p {
        opacity: 0.9;
        margin: 0;
        font-size: 0.9rem;
    }

    .btn-back {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.3);
        padding: 0.5rem 1rem;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.875rem;
        transition: var(--transition);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-back:hover {
        background: rgba(255, 255, 255, 0.3);
        color: white;
        text-decoration: none;
        transform: translateY(-1px);
    }

    /* ===== FORMULAIRE ===== */
    .form-section {
        padding: 2rem;
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 300px;
        gap: 2rem;
    }

    .form-main {
        background: white;
        border-radius: var(--radius);
        border: 1px solid var(--border);
        overflow: hidden;
    }

    .form-header {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        padding: 1.25rem;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .carrier-logo {
        width: 48px;
        height: 48px;
        background: #f3f4f6;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        object-fit: contain;
        padding: 8px;
    }

    .carrier-info h3 {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 0.25rem;
    }

    .carrier-info p {
        color: #6b7280;
        font-size: 0.875rem;
        margin: 0;
    }

    .form-body {
        padding: 2rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-label .required {
        color: var(--danger);
    }

    .form-control {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid var(--border);
        border-radius: 6px;
        font-size: 0.875rem;
        transition: var(--transition);
        background: white;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--royal-blue);
        box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.1);
    }

    .form-control.error {
        border-color: var(--danger);
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }

    .form-help {
        margin-top: 0.5rem;
        font-size: 0.8rem;
        color: #6b7280;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-error {
        margin-top: 0.5rem;
        font-size: 0.8rem;
        color: var(--danger);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .input-group {
        display: flex;
    }

    .input-group .form-control {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
        border-right: none;
    }

    .input-addon {
        background: #f3f4f6;
        border: 1px solid var(--border);
        border-left: none;
        border-top-right-radius: 6px;
        border-bottom-right-radius: 6px;
        padding: 0.75rem;
        display: flex;
        align-items: center;
        cursor: pointer;
        transition: var(--transition);
    }

    .input-addon:hover {
        background: #e5e7eb;
    }

    .form-actions {
        padding: 1.5rem 2rem;
        background: #f9fafb;
        border-top: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
    }

    .btn {
        padding: 0.75rem 1.5rem;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.875rem;
        text-decoration: none;
        text-align: center;
        transition: var(--transition);
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        position: relative;
        overflow: hidden;
    }

    .btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        transition: left 0.5s;
    }

    .btn:hover::before {
        left: 100%;
    }

    .btn:hover {
        transform: translateY(-1px);
        text-decoration: none;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--royal-blue), var(--royal-blue-light));
        color: white;
    }

    .btn-secondary {
        background: #6b7280;
        color: white;
    }

    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }

    .btn:disabled::before {
        display: none;
    }

    .btn.loading {
        pointer-events: none;
    }

    .btn.loading i {
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    /* ===== SIDEBAR AIDE ===== */
    .help-sidebar {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .help-card {
        background: white;
        border-radius: var(--radius);
        border: 1px solid var(--border);
        overflow: hidden;
    }

    .help-header {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        padding: 1rem;
        border-bottom: 1px solid var(--border);
        font-weight: 600;
        color: var(--dark);
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .help-body {
        padding: 1rem;
    }

    .help-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .help-list li {
        padding: 0.5rem 0;
        font-size: 0.8rem;
        color: #6b7280;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        border-bottom: 1px solid #f3f4f6;
    }

    .help-list li:last-child {
        border-bottom: none;
    }

    .spec-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem;
        margin-top: 1rem;
    }

    .spec-item {
        background: #f9fafb;
        padding: 0.75rem;
        border-radius: 4px;
        font-size: 0.8rem;
        text-align: center;
    }

    .spec-value {
        font-weight: 700;
        color: var(--royal-blue);
        display: block;
        margin-bottom: 0.25rem;
    }

    .spec-label {
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-size: 0.7rem;
    }

    /* ===== NOTIFICATIONS ===== */
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        min-width: 300px;
        max-width: 400px;
        padding: 1rem;
        border-radius: 6px;
        color: white;
        font-weight: 600;
        font-size: 0.875rem;
        box-shadow: var(--shadow-lg);
        animation: slideInRight 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .notification.success { background: var(--success); }
    .notification.error { background: var(--danger); }
    .notification.warning { background: var(--warning); }

    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    @keyframes slideOutRight {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .config-container {
            margin: 0.5rem;
        }

        .config-header {
            padding: 1rem;
        }

        .header-content {
            flex-direction: column;
            text-align: center;
        }

        .form-section {
            padding: 1rem;
        }

        .form-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .form-body {
            padding: 1.5rem;
        }

        .form-actions {
            padding: 1rem;
            flex-direction: column;
        }

        .form-actions .btn {
            width: 100%;
        }

        .spec-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 480px) {
        .config-header {
            padding: 0.75rem;
        }

        .header-info h1 {
            font-size: 1.25rem;
        }

        .form-body {
            padding: 1rem;
        }

        .carrier-logo {
            width: 40px;
            height: 40px;
        }
    }

    /* ===== ANIMATIONS ===== */
    .fade-in {
        animation: fadeIn 0.4s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection

@section('content')
<div class="config-container fade-in">
    <!-- Header -->
    <div class="config-header">
        <div class="header-content">
            <div class="header-info">
                <h1>
                    <i class="fas fa-plus"></i>
                    Nouvelle Configuration
                </h1>
                <p>Configuration de {{ $carrier['name'] }} pour vos expéditions</p>
            </div>
            <a href="{{ route('admin.delivery.configuration') }}" class="btn-back">
                <i class="fas fa-arrow-left"></i>
                Retour
            </a>
        </div>
    </div>

    <!-- Formulaire -->
    <div class="form-section">
        <div class="form-grid">
            <!-- Formulaire principal -->
            <div class="form-main">
                <div class="form-header">
                    @if(isset($carrier['logo']))
                        <img src="{{ asset($carrier['logo']) }}" 
                             alt="{{ $carrier['name'] }}" 
                             class="carrier-logo">
                    @else
                        <div class="carrier-logo">
                            <i class="fas fa-truck" style="color: #6b7280; font-size: 1.25rem;"></i>
                        </div>
                    @endif
                    <div class="carrier-info">
                        <h3>{{ $carrier['name'] }}</h3>
                        <p>{{ $carrier['description'] ?? 'Configuration des paramètres de connexion' }}</p>
                    </div>
                </div>

                <form id="configForm" action="{{ route('admin.delivery.configuration.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="carrier_slug" value="{{ $carrierSlug }}">
                    
                    <div class="form-body">
                        <!-- Nom de la liaison -->
                        <div class="form-group">
                            <label for="integration_name" class="form-label">
                                <i class="fas fa-tag"></i>
                                Nom de la Liaison <span class="required">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="integration_name" 
                                   name="integration_name" 
                                   value="{{ old('integration_name') }}"
                                   placeholder="Ex: Boutique Principale, Entrepôt Tunis..."
                                   required>
                            <div class="form-help">
                                <i class="fas fa-info-circle"></i>
                                Nom unique pour identifier cette configuration
                            </div>
                            @error('integration_name')
                                <div class="form-error">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        @if($carrierSlug === 'jax_delivery')
                            <!-- Configuration JAX Delivery -->
                            <div class="form-group">
                                <label for="username" class="form-label">
                                    <i class="fas fa-user"></i>
                                    Numéro de Compte <span class="required">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="username" 
                                       name="username" 
                                       value="{{ old('username') }}"
                                       placeholder="Votre numéro de compte JAX"
                                       required>
                                <div class="form-help">
                                    <i class="fas fa-info-circle"></i>
                                    Numéro fourni lors de votre inscription JAX Delivery
                                </div>
                                @error('username')
                                    <div class="form-error">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="password" class="form-label">
                                    <i class="fas fa-key"></i>
                                    Token API <span class="required">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control" 
                                           id="password" 
                                           name="password" 
                                           value="{{ old('password') }}"
                                           placeholder="Votre token API JAX"
                                           required>
                                    <div class="input-addon" onclick="togglePassword()">
                                        <i class="fas fa-eye" id="passwordToggle"></i>
                                    </div>
                                </div>
                                <div class="form-help">
                                    <i class="fas fa-info-circle"></i>
                                    Token JWT généré dans votre espace client JAX
                                </div>
                                @error('password')
                                    <div class="form-error">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                        @elseif($carrierSlug === 'mes_colis')
                            <!-- Configuration Mes Colis Express -->
                            <div class="form-group">
                                <label for="username" class="form-label">
                                    <i class="fas fa-key"></i>
                                    Token d'Accès <span class="required">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control" 
                                           id="username" 
                                           name="username" 
                                           value="{{ old('username') }}"
                                           placeholder="Votre token d'accès Mes Colis"
                                           required>
                                    <div class="input-addon" onclick="togglePassword()">
                                        <i class="fas fa-eye" id="passwordToggle"></i>
                                    </div>
                                </div>
                                <div class="form-help">
                                    <i class="fas fa-info-circle"></i>
                                    Token unique fourni par Mes Colis Express (x-access-token)
                                </div>
                                @error('username')
                                    <div class="form-error">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        @endif
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('admin.delivery.configuration') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                            Annuler
                        </a>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-save"></i>
                            Créer la Configuration
                        </button>
                    </div>
                </form>
            </div>

            <!-- Sidebar d'aide -->
            <div class="help-sidebar">
                <div class="help-card">
                    <div class="help-header">
                        <i class="fas fa-info-circle"></i>
                        Informations {{ $carrier['name'] }}
                    </div>
                    <div class="help-body">
                        @if($carrierSlug === 'jax_delivery')
                            <ul class="help-list">
                                <li>
                                    <i class="fas fa-check text-success"></i>
                                    Couverture nationale (24 gouvernorats)
                                </li>
                                <li>
                                    <i class="fas fa-check text-success"></i>
                                    Authentification Bearer Token
                                </li>
                                <li>
                                    <i class="fas fa-check text-success"></i>
                                    Suivi temps réel disponible
                                </li>
                                <li>
                                    <i class="fas fa-check text-success"></i>
                                    Support des pickups groupés
                                </li>
                            </ul>
                            
                            <div class="spec-grid">
                                <div class="spec-item">
                                    <span class="spec-value">30 kg</span>
                                    <div class="spec-label">Poids Max</div>
                                </div>
                                <div class="spec-item">
                                    <span class="spec-value">5000 TND</span>
                                    <div class="spec-label">COD Max</div>
                                </div>
                            </div>
                        @elseif($carrierSlug === 'mes_colis')
                            <ul class="help-list">
                                <li>
                                    <i class="fas fa-check text-success"></i>
                                    Livraisons express en Tunisie
                                </li>
                                <li>
                                    <i class="fas fa-check text-success"></i>
                                    Token d'authentification simple
                                </li>
                                <li>
                                    <i class="fas fa-check text-success"></i>
                                    Interface API moderne
                                </li>
                                <li>
                                    <i class="fas fa-check text-success"></i>
                                    Support gouvernorats complets
                                </li>
                            </ul>
                            
                            <div class="spec-grid">
                                <div class="spec-item">
                                    <span class="spec-value">25 kg</span>
                                    <div class="spec-label">Poids Max</div>
                                </div>
                                <div class="spec-item">
                                    <span class="spec-value">3000 TND</span>
                                    <div class="spec-label">COD Max</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                @if(isset($carrier['support_phone']) || isset($carrier['support_email']))
                <div class="help-card">
                    <div class="help-header">
                        <i class="fas fa-headset"></i>
                        Support & Contact
                    </div>
                    <div class="help-body">
                        @if(isset($carrier['support_phone']))
                            <div style="margin-bottom: 0.75rem;">
                                <i class="fas fa-phone" style="color: var(--royal-blue); margin-right: 0.5rem;"></i>
                                <a href="tel:{{ $carrier['support_phone'] }}" style="color: var(--dark); text-decoration: none;">
                                    {{ $carrier['support_phone'] }}
                                </a>
                            </div>
                        @endif
                        
                        @if(isset($carrier['support_email']))
                            <div style="margin-bottom: 0.75rem;">
                                <i class="fas fa-envelope" style="color: var(--royal-blue); margin-right: 0.5rem;"></i>
                                <a href="mailto:{{ $carrier['support_email'] }}" style="color: var(--dark); text-decoration: none;">
                                    {{ $carrier['support_email'] }}
                                </a>
                            </div>
                        @endif
                        
                        @if(isset($carrier['website']))
                            <div>
                                <i class="fas fa-globe" style="color: var(--royal-blue); margin-right: 0.5rem;"></i>
                                <a href="{{ $carrier['website'] }}" target="_blank" style="color: var(--dark); text-decoration: none;">
                                    Site web officiel
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// ===== CONFIGURATION =====
const CONFIG = {
    submitUrl: '{{ route("admin.delivery.configuration.store") }}',
    redirectUrl: '{{ route("admin.delivery.configuration") }}',
    csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
};

// ===== VARIABLES GLOBALES =====
let isSubmitting = false;

// ===== INITIALISATION =====
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Configuration creation page initialized');
    
    if (!CONFIG.csrfToken) {
        console.error('❌ CSRF token not found');
        showNotification('error', 'Erreur de sécurité. Veuillez recharger la page.');
        return;
    }
    
    setupForm();
    setupValidation();
});

// ===== GESTION DU FORMULAIRE =====
function setupForm() {
    const form = document.getElementById('configForm');
    if (!form) return;
    
    form.addEventListener('submit', handleSubmit);
    
    // Validation temps réel
    const inputs = form.querySelectorAll('input[required]');
    inputs.forEach(input => {
        input.addEventListener('blur', validateField);
        input.addEventListener('input', clearFieldError);
    });
}

function setupValidation() {
    // Validation du nom de liaison
    const integrationName = document.getElementById('integration_name');
    if (integrationName) {
        integrationName.addEventListener('input', function() {
            if (this.value.length < 3) {
                setFieldError(this, 'Le nom doit contenir au moins 3 caractères');
            } else {
                clearFieldError(this);
            }
        });
    }
    
    // Validation spécifique selon le transporteur
    const carrierSlug = '{{ $carrierSlug }}';
    if (carrierSlug === 'jax_delivery') {
        setupJaxValidation();
    } else if (carrierSlug === 'mes_colis') {
        setupMesColisValidation();
    }
}

function setupJaxValidation() {
    const username = document.getElementById('username');
    const password = document.getElementById('password');
    
    if (username) {
        username.addEventListener('input', function() {
            if (this.value.length < 2) {
                setFieldError(this, 'Numéro de compte requis');
            } else {
                clearFieldError(this);
            }
        });
    }
    
    if (password) {
        password.addEventListener('input', function() {
            if (this.value.length < 10) {
                setFieldError(this, 'Token API trop court');
            } else if (!isValidJwtFormat(this.value)) {
                setFieldError(this, 'Format de token invalide');
            } else {
                clearFieldError(this);
            }
        });
    }
}

function setupMesColisValidation() {
    const username = document.getElementById('username');
    
    if (username) {
        username.addEventListener('input', function() {
            if (this.value.length < 10) {
                setFieldError(this, 'Token d\'accès trop court');
            } else {
                clearFieldError(this);
            }
        });
    }
}

function isValidJwtFormat(token) {
    const parts = token.split('.');
    return parts.length === 3 && parts.every(part => part.length > 10);
}

// ===== GESTION DES ERREURS DE CHAMPS =====
function validateField(event) {
    const field = event.target;
    
    if (!field.value.trim() && field.hasAttribute('required')) {
        setFieldError(field, 'Ce champ est requis');
        return false;
    }
    
    clearFieldError(field);
    return true;
}

function setFieldError(field, message) {
    field.classList.add('error');
    
    // Supprimer l'ancien message d'erreur
    const existingError = field.parentNode.querySelector('.form-error');
    if (existingError) {
        existingError.remove();
    }
    
    // Ajouter le nouveau message d'erreur
    const errorDiv = document.createElement('div');
    errorDiv.className = 'form-error';
    errorDiv.innerHTML = `<i class="fas fa-exclamation-triangle"></i> ${message}`;
    field.parentNode.appendChild(errorDiv);
}

function clearFieldError(field) {
    if (typeof field === 'object' && field.target) {
        field = field.target;
    }
    
    field.classList.remove('error');
    const errorDiv = field.parentNode.querySelector('.form-error');
    if (errorDiv) {
        errorDiv.remove();
    }
}

// ===== SOUMISSION DU FORMULAIRE =====
async function handleSubmit(event) {
    event.preventDefault();
    
    if (isSubmitting) return;
    
    const form = event.target;
    const formData = new FormData(form);
    
    // Validation finale
    if (!validateForm(form)) {
        showNotification('error', 'Veuillez corriger les erreurs dans le formulaire');
        return;
    }
    
    isSubmitting = true;
    updateSubmitButton(true);
    
    try {
        const response = await fetch(CONFIG.submitUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CONFIG.csrfToken,
                'Accept': 'application/json'
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (response.ok && data.success) {
            showNotification('success', data.message || 'Configuration créée avec succès !');
            
            // Redirection après 2 secondes
            setTimeout(() => {
                window.location.href = CONFIG.redirectUrl;
            }, 2000);
        } else {
            handleFormErrors(data);
        }
        
    } catch (error) {
        console.error('❌ Submit error:', error);
        showNotification('error', 'Erreur de connexion. Veuillez réessayer.');
    } finally {
        isSubmitting = false;
        updateSubmitButton(false);
    }
}

function validateForm(form) {
    const requiredFields = form.querySelectorAll('input[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!validateField({ target: field })) {
            isValid = false;
        }
    });
    
    return isValid;
}

function handleFormErrors(data) {
    if (data.errors) {
        // Erreurs de validation Laravel
        Object.keys(data.errors).forEach(fieldName => {
            const field = document.querySelector(`[name="${fieldName}"]`);
            if (field) {
                setFieldError(field, data.errors[fieldName][0]);
            }
        });
        showNotification('error', 'Veuillez corriger les erreurs dans le formulaire');
    } else {
        const message = data.error || data.message || 'Erreur lors de la création de la configuration';
        showNotification('error', message);
    }
}

function updateSubmitButton(loading) {
    const btn = document.getElementById('submitBtn');
    if (!btn) return;
    
    if (loading) {
        btn.disabled = true;
        btn.classList.add('loading');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Création en cours...';
    } else {
        btn.disabled = false;
        btn.classList.remove('loading');
        btn.innerHTML = '<i class="fas fa-save"></i> Créer la Configuration';
    }
}

// ===== GESTION DU MOT DE PASSE =====
function togglePassword() {
    const passwordInput = document.querySelector('input[type="password"], input[type="text"][id="password"], input[type="text"][id="username"]');
    const toggleIcon = document.getElementById('passwordToggle');
    
    if (!passwordInput || !toggleIcon) return;
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.className = 'fas fa-eye-slash';
    } else {
        passwordInput.type = 'password';
        toggleIcon.className = 'fas fa-eye';
    }
}

// ===== NOTIFICATIONS =====
function showNotification(type, message) {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    
    const icon = type === 'success' ? 'check-circle' : 
                 type === 'error' ? 'exclamation-circle' : 'info-circle';
    
    notification.innerHTML = `
        <i class="fas fa-${icon}"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 4000);
}

console.log('✅ Configuration creation scripts loaded');
</script>
@endsection