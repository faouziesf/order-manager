@extends('layouts.super-admin')

@section('title', 'Créer un Administrateur')

@section('breadcrumb')
    <ol class="breadcrumb breadcrumb-custom">
        <li class="breadcrumb-item"><a href="{{ route('super-admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('super-admin.admins.index') }}">Administrateurs</a></li>
        <li class="breadcrumb-item active">Nouveau</li>
    </ol>
@endsection

@section('page-header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">Créer un Administrateur</h1>
            <p class="page-subtitle">Ajoutez un nouvel administrateur à votre plateforme</p>
        </div>
        <div>
            <a href="{{ route('super-admin.admins.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Retour à la liste
            </a>
        </div>
    </div>
@endsection

@section('css')
<style>
    .form-wizard {
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }
    
    .wizard-steps {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        color: white;
        padding: 30px;
    }
    
    .step-item {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
        opacity: 0.6;
        transition: all 0.3s ease;
    }
    
    .step-item.active {
        opacity: 1;
    }
    
    .step-item.completed {
        opacity: 0.8;
    }
    
    .step-number {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .step-item.active .step-number {
        background: white;
        color: var(--primary-color);
    }
    
    .step-item.completed .step-number {
        background: var(--success-color);
        color: white;
    }
    
    .step-content {
        flex: 1;
    }
    
    .step-title {
        font-weight: 600;
        margin-bottom: 5px;
    }
    
    .step-description {
        font-size: 0.875rem;
        opacity: 0.8;
    }
    
    .wizard-content {
        padding: 40px;
    }
    
    .form-section {
        display: none;
    }
    
    .form-section.active {
        display: block;
        animation: fadeInUp 0.5s ease-out;
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .form-floating {
        margin-bottom: 20px;
    }
    
    .form-floating .form-control {
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        padding: 20px 15px 10px;
    }
    
    .form-floating .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.25);
    }
    
    .form-floating label {
        color: var(--secondary-color);
        font-weight: 500;
    }
    
    .input-group-addon {
        background: var(--light-color);
        border: 2px solid #e2e8f0;
        border-left: none;
        border-radius: 0 10px 10px 0;
        padding: 0 15px;
        display: flex;
        align-items: center;
    }
    
    .subscription-option {
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: white;
    }
    
    .subscription-option:hover {
        border-color: var(--primary-color);
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    
    .subscription-option.selected {
        border-color: var(--primary-color);
        background: var(--primary-color);
        color: white;
    }
    
    .subscription-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        background: var(--light-color);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin: 0 auto 15px;
        transition: all 0.3s ease;
    }
    
    .subscription-option.selected .subscription-icon {
        background: rgba(255, 255, 255, 0.2);
        color: white;
    }
    
    .feature-list {
        list-style: none;
        padding: 0;
        margin: 15px 0 0;
    }
    
    .feature-list li {
        padding: 5px 0;
        font-size: 0.875rem;
    }
    
    .feature-list li i {
        margin-right: 8px;
        color: var(--success-color);
    }
    
    .subscription-option.selected .feature-list li i {
        color: rgba(255, 255, 255, 0.8);
    }
    
    .wizard-actions {
        background: var(--light-color);
        padding: 20px 40px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .form-summary {
        background: var(--light-color);
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .summary-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .summary-item:last-child {
        border-bottom: none;
    }
    
    .summary-label {
        font-weight: 500;
        color: var(--secondary-color);
    }
    
    .summary-value {
        font-weight: 600;
        color: var(--dark-color);
    }
    
    .password-strength {
        margin-top: 10px;
    }
    
    .strength-meter {
        height: 4px;
        background: #e2e8f0;
        border-radius: 2px;
        overflow: hidden;
        margin-bottom: 5px;
    }
    
    .strength-bar {
        height: 100%;
        transition: all 0.3s ease;
        border-radius: 2px;
    }
    
    .strength-weak { background: var(--danger-color); width: 25%; }
    .strength-fair { background: var(--warning-color); width: 50%; }
    .strength-good { background: var(--info-color); width: 75%; }
    .strength-strong { background: var(--success-color); width: 100%; }
    
    .error-message {
        color: var(--danger-color);
        font-size: 0.875rem;
        margin-top: 5px;
        display: none;
    }
    
    .success-animation {
        text-align: center;
        padding: 40px;
    }
    
    .success-icon {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: var(--success-color);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        margin: 0 auto 20px;
        animation: bounceIn 0.6s ease-out;
    }
    
    @keyframes bounceIn {
        0% { transform: scale(0.3); opacity: 0; }
        50% { transform: scale(1.05); }
        70% { transform: scale(0.9); }
        100% { transform: scale(1); opacity: 1; }
    }
</style>
@endsection

@section('content')
    <div class="form-wizard">
        <div class="row g-0">
            <!-- Wizard Steps -->
            <div class="col-md-4">
                <div class="wizard-steps">
                    <div class="step-item active" data-step="1">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <div class="step-title">Informations personnelles</div>
                            <div class="step-description">Nom, email, contact</div>
                        </div>
                    </div>
                    
                    <div class="step-item" data-step="2">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <div class="step-title">Boutique & Accès</div>
                            <div class="step-description">Boutique, mot de passe</div>
                        </div>
                    </div>
                    
                    <div class="step-item" data-step="3">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <div class="step-title">Abonnement</div>
                            <div class="step-description">Type et limites</div>
                        </div>
                    </div>
                    
                    <div class="step-item" data-step="4">
                        <div class="step-number">4</div>
                        <div class="step-content">
                            <div class="step-title">Confirmation</div>
                            <div class="step-description">Vérification et création</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Wizard Content -->
            <div class="col-md-8">
                <form id="adminForm" action="{{ route('super-admin.admins.store') }}" method="POST">
                    @csrf
                    
                    <div class="wizard-content">
                        <!-- Step 1: Personal Information -->
                        <div class="form-section active" data-step="1">
                            <h4 class="mb-4">Informations personnelles</h4>
                            
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input type="text" 
                                               class="form-control @error('name') is-invalid @enderror" 
                                               id="name" 
                                               name="name" 
                                               value="{{ old('name') }}" 
                                               placeholder="Nom complet"
                                               required>
                                        <label for="name">Nom complet *</label>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input type="email" 
                                               class="form-control @error('email') is-invalid @enderror" 
                                               id="email" 
                                               name="email" 
                                               value="{{ old('email') }}" 
                                               placeholder="Adresse email"
                                               required>
                                        <label for="email">Adresse email *</label>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="error-message" id="emailError"></div>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input type="tel" 
                                               class="form-control @error('phone') is-invalid @enderror" 
                                               id="phone" 
                                               name="phone" 
                                               value="{{ old('phone') }}" 
                                               placeholder="Numéro de téléphone">
                                        <label for="phone">Numéro de téléphone</label>
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Step 2: Shop & Access -->
                        <div class="form-section" data-step="2">
                            <h4 class="mb-4">Boutique & Accès</h4>
                            
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input type="text" 
                                               class="form-control @error('shop_name') is-invalid @enderror" 
                                               id="shop_name" 
                                               name="shop_name" 
                                               value="{{ old('shop_name') }}" 
                                               placeholder="Nom de la boutique"
                                               required>
                                        <label for="shop_name">Nom de la boutique *</label>
                                        @error('shop_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input type="password" 
                                               class="form-control @error('password') is-invalid @enderror" 
                                               id="password" 
                                               name="password" 
                                               placeholder="Mot de passe"
                                               required>
                                        <label for="password">Mot de passe *</label>
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="password-strength">
                                        <div class="strength-meter">
                                            <div class="strength-bar" id="strengthBar"></div>
                                        </div>
                                        <small class="text-muted" id="strengthText">Tapez un mot de passe</small>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input type="password" 
                                               class="form-control" 
                                               id="password_confirmation" 
                                               name="password_confirmation" 
                                               placeholder="Confirmer le mot de passe"
                                               required>
                                        <label for="password_confirmation">Confirmer le mot de passe *</label>
                                        <div class="error-message" id="passwordMatchError"></div>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input type="date" 
                                               class="form-control @error('expiry_date') is-invalid @enderror" 
                                               id="expiry_date" 
                                               name="expiry_date" 
                                               value="{{ old('expiry_date') }}" 
                                               min="{{ date('Y-m-d') }}">
                                        <label for="expiry_date">Date d'expiration</label>
                                        @error('expiry_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Laissez vide pour un accès illimité</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Step 3: Subscription -->
                        <div class="form-section" data-step="3">
                            <h4 class="mb-4">Type d'abonnement</h4>
                            
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="subscription-option" data-subscription="trial">
                                        <div class="subscription-icon">
                                            <i class="fas fa-play"></i>
                                        </div>
                                        <h6>Essai</h6>
                                        <p class="text-muted mb-0">Accès limité pour tester</p>
                                        <ul class="feature-list">
                                            <li><i class="fas fa-check"></i> 1 Manager max</li>
                                            <li><i class="fas fa-check"></i> 2 Employés max</li>
                                            <li><i class="fas fa-check"></i> Support de base</li>
                                        </ul>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="subscription-option" data-subscription="basic">
                                        <div class="subscription-icon">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <h6>Basic</h6>
                                        <p class="text-muted mb-0">Pour petites équipes</p>
                                        <ul class="feature-list">
                                            <li><i class="fas fa-check"></i> 3 Managers max</li>
                                            <li><i class="fas fa-check"></i> 10 Employés max</li>
                                            <li><i class="fas fa-check"></i> Support prioritaire</li>
                                        </ul>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="subscription-option" data-subscription="premium">
                                        <div class="subscription-icon">
                                            <i class="fas fa-crown"></i>
                                        </div>
                                        <h6>Premium</h6>
                                        <p class="text-muted mb-0">Pour équipes moyennes</p>
                                        <ul class="feature-list">
                                            <li><i class="fas fa-check"></i> 10 Managers max</li>
                                            <li><i class="fas fa-check"></i> 50 Employés max</li>
                                            <li><i class="fas fa-check"></i> Analytics avancées</li>
                                            <li><i class="fas fa-check"></i> Support 24/7</li>
                                        </ul>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="subscription-option" data-subscription="enterprise">
                                        <div class="subscription-icon">
                                            <i class="fas fa-building"></i>
                                        </div>
                                        <h6>Enterprise</h6>
                                        <p class="text-muted mb-0">Pour grandes entreprises</p>
                                        <ul class="feature-list">
                                            <li><i class="fas fa-check"></i> Managers illimités</li>
                                            <li><i class="fas fa-check"></i> Employés illimités</li>
                                            <li><i class="fas fa-check"></i> Toutes les fonctionnalités</li>
                                            <li><i class="fas fa-check"></i> Support dédié</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <input type="hidden" name="subscription_type" id="subscription_type" value="trial">
                            
                            <div class="row g-3 mt-4">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="number" 
                                               class="form-control" 
                                               id="max_managers" 
                                               name="max_managers" 
                                               value="1" 
                                               min="1" 
                                               max="100">
                                        <label for="max_managers">Nombre max de managers</label>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="number" 
                                               class="form-control" 
                                               id="max_employees" 
                                               name="max_employees" 
                                               value="2" 
                                               min="1" 
                                               max="1000">
                                        <label for="max_employees">Nombre max d'employés</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Step 4: Confirmation -->
                        <div class="form-section" data-step="4">
                            <h4 class="mb-4">Confirmation</h4>
                            
                            <div class="form-summary">
                                <h6 class="mb-3">Résumé des informations</h6>
                                
                                <div class="summary-item">
                                    <span class="summary-label">Nom complet</span>
                                    <span class="summary-value" id="summary-name">-</span>
                                </div>
                                
                                <div class="summary-item">
                                    <span class="summary-label">Email</span>
                                    <span class="summary-value" id="summary-email">-</span>
                                </div>
                                
                                <div class="summary-item">
                                    <span class="summary-label">Téléphone</span>
                                    <span class="summary-value" id="summary-phone">-</span>
                                </div>
                                
                                <div class="summary-item">
                                    <span class="summary-label">Boutique</span>
                                    <span class="summary-value" id="summary-shop">-</span>
                                </div>
                                
                                <div class="summary-item">
                                    <span class="summary-label">Type d'abonnement</span>
                                    <span class="summary-value" id="summary-subscription">-</span>
                                </div>
                                
                                <div class="summary-item">
                                    <span class="summary-label">Managers max</span>
                                    <span class="summary-value" id="summary-managers">-</span>
                                </div>
                                
                                <div class="summary-item">
                                    <span class="summary-label">Employés max</span>
                                    <span class="summary-value" id="summary-employees">-</span>
                                </div>
                                
                                <div class="summary-item">
                                    <span class="summary-label">Date d'expiration</span>
                                    <span class="summary-value" id="summary-expiry">-</span>
                                </div>
                            </div>
                            
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                                <label class="form-check-label" for="is_active">
                                    <strong>Activer le compte immédiatement</strong>
                                    <br><small class="text-muted">L'administrateur pourra se connecter dès la création</small>
                                </label>
                            </div>
                            
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" id="send_welcome_email" name="send_welcome_email" checked>
                                <label class="form-check-label" for="send_welcome_email">
                                    <strong>Envoyer un email de bienvenue</strong>
                                    <br><small class="text-muted">L'administrateur recevra ses identifiants par email</small>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Wizard Actions -->
                    <div class="wizard-actions">
                        <button type="button" class="btn btn-outline-secondary" id="prevBtn" style="display: none;">
                            <i class="fas fa-arrow-left me-2"></i>Précédent
                        </button>
                        
                        <div class="ms-auto d-flex gap-2">
                            <button type="button" class="btn btn-primary" id="nextBtn">
                                Suivant <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                            <button type="submit" class="btn btn-success" id="submitBtn" style="display: none;">
                                <i class="fas fa-check me-2"></i>Créer l'administrateur
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="success-animation">
                        <div class="success-icon">
                            <i class="fas fa-check"></i>
                        </div>
                        <h4 class="text-success mb-3">Administrateur créé avec succès !</h4>
                        <p class="text-muted mb-4">L'administrateur a été créé et peut maintenant accéder à la plateforme.</p>
                        <div class="d-flex gap-2 justify-content-center">
                            <a href="{{ route('super-admin.admins.index') }}" class="btn btn-outline-primary">
                                <i class="fas fa-list me-2"></i>Voir la liste
                            </a>
                            <a href="{{ route('super-admin.admins.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Créer un autre
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentStep = 1;
    const totalSteps = 4;
    
    // Elements
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');
    const form = document.getElementById('adminForm');
    
    // Initialize
    setupEventListeners();
    updateWizard();
    
    function setupEventListeners() {
        // Navigation buttons
        nextBtn.addEventListener('click', nextStep);
        prevBtn.addEventListener('click', prevStep);
        
        // Subscription selection
        document.querySelectorAll('.subscription-option').forEach(option => {
            option.addEventListener('click', function() {
                selectSubscription(this.dataset.subscription);
            });
        });
        
        // Form validation
        document.getElementById('email').addEventListener('blur', validateEmail);
        document.getElementById('password').addEventListener('input', checkPasswordStrength);
        document.getElementById('password_confirmation').addEventListener('input', checkPasswordMatch);
        
        // Form inputs for summary
        ['name', 'email', 'phone', 'shop_name'].forEach(field => {
            document.getElementById(field).addEventListener('input', updateSummary);
        });
        
        document.getElementById('expiry_date').addEventListener('change', updateSummary);
        
        // Form submission
        form.addEventListener('submit', handleSubmit);
    }
    
    function nextStep() {
        if (validateCurrentStep()) {
            if (currentStep < totalSteps) {
                currentStep++;
                updateWizard();
                updateSummary();
            }
        }
    }
    
    function prevStep() {
        if (currentStep > 1) {
            currentStep--;
            updateWizard();
        }
    }
    
    function updateWizard() {
        // Update steps
        document.querySelectorAll('.step-item').forEach(step => {
            const stepNumber = parseInt(step.dataset.step);
            step.classList.remove('active', 'completed');
            
            if (stepNumber === currentStep) {
                step.classList.add('active');
            } else if (stepNumber < currentStep) {
                step.classList.add('completed');
            }
        });
        
        // Update form sections
        document.querySelectorAll('.form-section').forEach(section => {
            section.classList.remove('active');
        });
        document.querySelector(`[data-step="${currentStep}"]`).classList.add('active');
        
        // Update buttons
        prevBtn.style.display = currentStep > 1 ? 'block' : 'none';
        nextBtn.style.display = currentStep < totalSteps ? 'block' : 'none';
        submitBtn.style.display = currentStep === totalSteps ? 'block' : 'none';
    }
    
    function validateCurrentStep() {
        let valid = true;
        const currentSection = document.querySelector(`[data-step="${currentStep}"]`);
        const requiredFields = currentSection.querySelectorAll('[required]');
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                valid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });
        
        // Step-specific validation
        if (currentStep === 1) {
            valid = valid && validateEmail();
        } else if (currentStep === 2) {
            valid = valid && checkPasswordMatch() && checkPasswordStrength();
        }
        
        return valid;
    }
    
    function validateEmail() {
        const email = document.getElementById('email');
        const emailError = document.getElementById('emailError');
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (email.value && !emailRegex.test(email.value)) {
            emailError.textContent = 'Format d\'email invalide';
            emailError.style.display = 'block';
            email.classList.add('is-invalid');
            return false;
        } else {
            emailError.style.display = 'none';
            email.classList.remove('is-invalid');
            return true;
        }
    }
    
    function checkPasswordStrength() {
        const password = document.getElementById('password');
        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');
        
        const value = password.value;
        let strength = 0;
        let text = '';
        let className = '';
        
        if (value.length >= 8) strength++;
        if (/[a-z]/.test(value)) strength++;
        if (/[A-Z]/.test(value)) strength++;
        if (/[0-9]/.test(value)) strength++;
        if (/[^A-Za-z0-9]/.test(value)) strength++;
        
        switch (strength) {
            case 0:
            case 1:
                text = 'Très faible';
                className = 'strength-weak';
                break;
            case 2:
                text = 'Faible';
                className = 'strength-fair';
                break;
            case 3:
                text = 'Moyen';
                className = 'strength-good';
                break;
            case 4:
            case 5:
                text = 'Fort';
                className = 'strength-strong';
                break;
        }
        
        strengthBar.className = `strength-bar ${className}`;
        strengthText.textContent = text;
        
        return strength >= 3;
    }
    
    function checkPasswordMatch() {
        const password = document.getElementById('password');
        const confirmation = document.getElementById('password_confirmation');
        const error = document.getElementById('passwordMatchError');
        
        if (confirmation.value && password.value !== confirmation.value) {
            error.textContent = 'Les mots de passe ne correspondent pas';
            error.style.display = 'block';
            confirmation.classList.add('is-invalid');
            return false;
        } else {
            error.style.display = 'none';
            confirmation.classList.remove('is-invalid');
            return true;
        }
    }
    
    function selectSubscription(type) {
        // Update visual selection
        document.querySelectorAll('.subscription-option').forEach(option => {
            option.classList.remove('selected');
        });
        document.querySelector(`[data-subscription="${type}"]`).classList.add('selected');
        
        // Update hidden input
        document.getElementById('subscription_type').value = type;
        
        // Update limits based on subscription
        const limits = {
            trial: { managers: 1, employees: 2 },
            basic: { managers: 3, employees: 10 },
            premium: { managers: 10, employees: 50 },
            enterprise: { managers: 100, employees: 1000 }
        };
        
        document.getElementById('max_managers').value = limits[type].managers;
        document.getElementById('max_employees').value = limits[type].employees;
        
        updateSummary();
    }
    
    function updateSummary() {
        if (currentStep === 4) {
            document.getElementById('summary-name').textContent = document.getElementById('name').value || '-';
            document.getElementById('summary-email').textContent = document.getElementById('email').value || '-';
            document.getElementById('summary-phone').textContent = document.getElementById('phone').value || 'Non renseigné';
            document.getElementById('summary-shop').textContent = document.getElementById('shop_name').value || '-';
            
            const subscription = document.getElementById('subscription_type').value;
            document.getElementById('summary-subscription').textContent = subscription.charAt(0).toUpperCase() + subscription.slice(1);
            
            document.getElementById('summary-managers').textContent = document.getElementById('max_managers').value;
            document.getElementById('summary-employees').textContent = document.getElementById('max_employees').value;
            
            const expiryDate = document.getElementById('expiry_date').value;
            document.getElementById('summary-expiry').textContent = expiryDate ? new Date(expiryDate).toLocaleDateString('fr-FR') : 'Illimité';
        }
    }
    
    function handleSubmit(e) {
        e.preventDefault();
        
        if (!validateCurrentStep()) {
            return;
        }
        
        // Show loading state
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Création en cours...';
        submitBtn.disabled = true;
        
        // Submit form
        const formData = new FormData(form);
        
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('[name="_token"]').value
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                new bootstrap.Modal(document.getElementById('successModal')).show();
            } else {
                throw new Error(data.message || 'Erreur lors de la création');
            }
        })
        .catch(error => {
            alert('Erreur: ' + error.message);
            submitBtn.innerHTML = '<i class="fas fa-check me-2"></i>Créer l\'administrateur';
            submitBtn.disabled = false;
        });
    }
    
    // Initialize subscription selection
    selectSubscription('trial');
});
</script>
@endsection