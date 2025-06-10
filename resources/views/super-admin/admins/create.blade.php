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
    .creation-container {
        max-width: 1000px;
        margin: 0 auto;
    }
    
    .step-wizard {
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }
    
    .step-header {
        background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
        color: white;
        padding: 25px 30px;
    }
    
    .step-progress {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }
    
    .step-item {
        display: flex;
        align-items: center;
        flex: 1;
        position: relative;
    }
    
    .step-item:not(:last-child)::after {
        content: '';
        position: absolute;
        top: 50%;
        right: -25px;
        width: 50px;
        height: 2px;
        background: rgba(255, 255, 255, 0.3);
        transform: translateY(-50%);
        z-index: 1;
    }
    
    .step-item.active:not(:last-child)::after {
        background: rgba(255, 255, 255, 0.6);
    }
    
    .step-number {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        font-weight: 600;
        font-size: 16px;
        transition: all 0.3s ease;
        z-index: 2;
        position: relative;
    }
    
    .step-item.active .step-number {
        background: white;
        color: #4f46e5;
        box-shadow: 0 0 15px rgba(255, 255, 255, 0.3);
    }
    
    .step-item.completed .step-number {
        background: #10b981;
        color: white;
    }
    
    .step-text {
        font-size: 14px;
        opacity: 0.8;
    }
    
    .step-item.active .step-text {
        opacity: 1;
        font-weight: 500;
    }
    
    .step-title {
        color: white;
        font-size: 20px;
        font-weight: 600;
        margin: 0;
    }
    
    .step-content {
        padding: 40px;
        min-height: 400px;
    }
    
    /* Correction critique : s'assurer que les étapes s'affichent */
    .form-step {
        display: none !important;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .form-step.active {
        display: block !important;
        opacity: 1;
        animation: fadeInUp 0.4s ease-out;
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
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        padding: 20px 15px 8px;
        font-size: 16px;
        transition: all 0.3s ease;
    }
    
    .form-floating .form-control:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }
    
    .form-floating label {
        color: #6b7280;
        font-weight: 500;
        font-size: 14px;
    }
    
    .subscription-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin: 25px 0;
    }
    
    .subscription-card {
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        padding: 25px 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: white;
        position: relative;
    }
    
    .subscription-card:hover {
        border-color: #4f46e5;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }
    
    .subscription-card.selected {
        border-color: #4f46e5;
        background: #f8faff;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(79, 70, 229, 0.15);
    }
    
    .subscription-card.selected::before {
        content: '✓';
        position: absolute;
        top: -8px;
        right: -8px;
        width: 24px;
        height: 24px;
        background: #10b981;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: 600;
    }
    
    .subscription-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        background: #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        margin: 0 auto 15px;
        color: #6b7280;
    }
    
    .subscription-card.selected .subscription-icon {
        background: #4f46e5;
        color: white;
    }
    
    .subscription-name {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 8px;
        color: #111827;
    }
    
    .subscription-description {
        font-size: 14px;
        color: #6b7280;
        margin-bottom: 15px;
    }
    
    .subscription-features {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .subscription-features li {
        font-size: 13px;
        color: #6b7280;
        margin-bottom: 5px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .subscription-features li i {
        color: #10b981;
        margin-right: 6px;
        font-size: 12px;
    }
    
    .step-actions {
        background: #f9fafb;
        padding: 25px 40px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 1px solid #e5e7eb;
    }
    
    .btn {
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .btn-primary {
        background: #4f46e5;
        border: none;
    }
    
    .btn-primary:hover {
        background: #3730a3;
        transform: translateY(-1px);
    }
    
    .summary-section {
        background: #f9fafb;
        border-radius: 10px;
        padding: 25px;
        margin-bottom: 25px;
    }
    
    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .summary-row:last-child {
        border-bottom: none;
    }
    
    .summary-label {
        font-weight: 500;
        color: #374151;
    }
    
    .summary-value {
        font-weight: 600;
        color: #111827;
    }
    
    .password-strength {
        margin-top: 8px;
    }
    
    .strength-meter {
        height: 4px;
        background: #e5e7eb;
        border-radius: 2px;
        overflow: hidden;
        margin-bottom: 5px;
    }
    
    .strength-fill {
        height: 100%;
        transition: all 0.3s ease;
        border-radius: 2px;
    }
    
    .strength-weak { background: #ef4444; width: 25%; }
    .strength-fair { background: #f59e0b; width: 50%; }
    .strength-good { background: #3b82f6; width: 75%; }
    .strength-strong { background: #10b981; width: 100%; }
    
    .error-text {
        color: #ef4444;
        font-size: 14px;
        margin-top: 5px;
        display: none;
    }
    
    .form-check {
        margin: 15px 0;
    }
    
    .form-check-label {
        font-weight: 500;
        color: #374151;
    }
    
    .limits-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-top: 20px;
    }

</style>
@endsection

@section('content')


    <div class="creation-container">
        <div class="step-wizard">
            <!-- En-tête avec progression -->
            <div class="step-header">
                <div class="step-progress">
                    <div class="step-item active" data-step="1">
                        <div class="step-number">1</div>
                        <div class="step-text">Informations personnelles<br><small>Nom, email, contact</small></div>
                    </div>
                    <div class="step-item" data-step="2">
                        <div class="step-number">2</div>
                        <div class="step-text">Boutique & Accès<br><small>Boutique, mot de passe</small></div>
                    </div>
                    <div class="step-item" data-step="3">
                        <div class="step-number">3</div>
                        <div class="step-text">Abonnement<br><small>Type et limites</small></div>
                    </div>
                    <div class="step-item" data-step="4">
                        <div class="step-number">4</div>
                        <div class="step-text">Confirmation<br><small>Vérification et création</small></div>
                    </div>
                </div>
                <h2 class="step-title" id="stepTitle">Informations personnelles</h2>
            </div>
            
            <!-- Contenu des étapes -->
            <form id="adminForm" action="{{ route('super-admin.admins.store') }}" method="POST">
                @csrf
                
                <div class="step-content">
                    <!-- Étape 1: Informations personnelles -->
                    <div class="form-step show-step" data-step="1">
                        <div class="row g-4">
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
                                    <div class="error-text" id="emailError"></div>
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
                                    <label for="phone">Numéro de téléphone (optionnel)</label>
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Étape 2: Boutique & Accès -->
                    <div class="form-step" data-step="2">
                        <div class="row g-4">
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
                            
                            <div class="col-md-6">
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
                                        <div class="strength-fill" id="strengthFill"></div>
                                    </div>
                                    <small class="text-muted" id="strengthText">Choisissez un mot de passe fort</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="password" 
                                           class="form-control" 
                                           id="password_confirmation" 
                                           name="password_confirmation" 
                                           placeholder="Confirmer le mot de passe"
                                           required>
                                    <label for="password_confirmation">Confirmer le mot de passe *</label>
                                    <div class="error-text" id="passwordMatchError"></div>
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
                                    <label for="expiry_date">Date d'expiration (optionnel)</label>
                                    @error('expiry_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Laissez vide pour un accès illimité</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Étape 3: Abonnement -->
                    <div class="form-step" data-step="3">
                        <h5 class="mb-4">Choisissez le type d'abonnement</h5>
                        
                        <div class="subscription-grid">
                            <div class="subscription-card selected" data-subscription="trial">
                                <div class="subscription-icon">
                                    <i class="fas fa-play"></i>
                                </div>
                                <div class="subscription-name">Essai Gratuit</div>
                                <div class="subscription-description">Parfait pour commencer</div>
                                <ul class="subscription-features">
                                    <li><i class="fas fa-check"></i> 1 Manager maximum</li>
                                    <li><i class="fas fa-check"></i> 2 Employés maximum</li>
                                    <li><i class="fas fa-check"></i> Support par email</li>
                                </ul>
                            </div>
                            
                            <div class="subscription-card" data-subscription="basic">
                                <div class="subscription-icon">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="subscription-name">Basic</div>
                                <div class="subscription-description">Pour petites équipes</div>
                                <ul class="subscription-features">
                                    <li><i class="fas fa-check"></i> 3 Managers maximum</li>
                                    <li><i class="fas fa-check"></i> 10 Employés maximum</li>
                                    <li><i class="fas fa-check"></i> Support prioritaire</li>
                                </ul>
                            </div>
                            
                            <div class="subscription-card" data-subscription="premium">
                                <div class="subscription-icon">
                                    <i class="fas fa-crown"></i>
                                </div>
                                <div class="subscription-name">Premium</div>
                                <div class="subscription-description">Pour équipes moyennes</div>
                                <ul class="subscription-features">
                                    <li><i class="fas fa-check"></i> 10 Managers maximum</li>
                                    <li><i class="fas fa-check"></i> 50 Employés maximum</li>
                                    <li><i class="fas fa-check"></i> Analytics avancées</li>
                                </ul>
                            </div>
                            
                            <div class="subscription-card" data-subscription="enterprise">
                                <div class="subscription-icon">
                                    <i class="fas fa-building"></i>
                                </div>
                                <div class="subscription-name">Enterprise</div>
                                <div class="subscription-description">Pour grandes entreprises</div>
                                <ul class="subscription-features">
                                    <li><i class="fas fa-check"></i> Managers illimités</li>
                                    <li><i class="fas fa-check"></i> Employés illimités</li>
                                    <li><i class="fas fa-check"></i> Support dédié</li>
                                </ul>
                            </div>
                        </div>
                        
                        <input type="hidden" name="subscription_type" id="subscription_type" value="trial">
                        
                        <div class="limits-grid">
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
                    
                    <!-- Étape 4: Confirmation -->
                    <div class="form-step" data-step="4">
                        <h5 class="mb-4">Vérifiez les informations</h5>
                        
                        <div class="summary-section">
                            <div class="summary-row">
                                <span class="summary-label">Nom complet</span>
                                <span class="summary-value" id="summary-name">-</span>
                            </div>
                            <div class="summary-row">
                                <span class="summary-label">Email</span>
                                <span class="summary-value" id="summary-email">-</span>
                            </div>
                            <div class="summary-row">
                                <span class="summary-label">Téléphone</span>
                                <span class="summary-value" id="summary-phone">-</span>
                            </div>
                            <div class="summary-row">
                                <span class="summary-label">Boutique</span>
                                <span class="summary-value" id="summary-shop">-</span>
                            </div>
                            <div class="summary-row">
                                <span class="summary-label">Type d'abonnement</span>
                                <span class="summary-value" id="summary-subscription">-</span>
                            </div>
                            <div class="summary-row">
                                <span class="summary-label">Managers maximum</span>
                                <span class="summary-value" id="summary-managers">-</span>
                            </div>
                            <div class="summary-row">
                                <span class="summary-label">Employés maximum</span>
                                <span class="summary-value" id="summary-employees">-</span>
                            </div>
                            <div class="summary-row">
                                <span class="summary-label">Date d'expiration</span>
                                <span class="summary-value" id="summary-expiry">-</span>
                            </div>
                        </div>
                        
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                            <label class="form-check-label" for="is_active">
                                Activer le compte immédiatement
                            </label>
                        </div>
                        
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="send_welcome_email" name="send_welcome_email" checked>
                            <label class="form-check-label" for="send_welcome_email">
                                Envoyer un email de bienvenue avec les identifiants
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="step-actions">
                    <button type="button" class="btn btn-outline-secondary" id="prevBtn" style="display: none;">
                        <i class="fas fa-arrow-left me-2"></i>Précédent
                    </button>
                    
                    <div class="ms-auto d-flex gap-3">
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
@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Script de création admin chargé');
    
    let currentStep = 1;
    const totalSteps = 4;
    
    const stepTitles = {
        1: 'Informations personnelles',
        2: 'Boutique et Accès', 
        3: 'Type d abonnement',
        4: 'Confirmation'
    };
    
    // Fonction pour afficher les informations de debug
    function updateDebugInfo() {
        const debugStep = document.getElementById('debugStep');
        const debugActive = document.getElementById('debugActive');
        const activeStep = document.querySelector('.form-step.active');
        
        if (debugStep) debugStep.textContent = currentStep;
        if (debugActive) debugActive.textContent = activeStep ? activeStep.getAttribute('data-step') : 'none';
    }
    
    // Initialisation forcée de l'affichage
    function forceShowFirstStep() {
        console.log('Forçage de l\'affichage de la première étape');
        
        // Masquer toutes les étapes
        document.querySelectorAll('.form-step').forEach(step => {
            step.classList.remove('active', 'show-step');
            step.style.display = 'none';
        });
        
        // Afficher la première étape
        const firstStep = document.querySelector('[data-step="1"]');
        if (firstStep) {
            firstStep.classList.add('active', 'show-step');
            firstStep.style.display = 'block';
            console.log('Première étape affichée');
        }
        
        updateDebugInfo();
    }
    
    // Initialisation
    setupEventListeners();
    forceShowFirstStep();
    updateStepDisplay();
    selectSubscription('trial');
    
    function setupEventListeners() {
        console.log('Configuration des événements');
        
        // Navigation
        const nextBtn = document.getElementById('nextBtn');
        const prevBtn = document.getElementById('prevBtn');
        
        if (nextBtn) {
            nextBtn.addEventListener('click', function(e) {
                e.preventDefault();
                nextStep();
            });
        }
        
        if (prevBtn) {
            prevBtn.addEventListener('click', function(e) {
                e.preventDefault();
                prevStep();
            });
        }
        
        // Sélection d'abonnement
        document.querySelectorAll('.subscription-card').forEach(card => {
            card.addEventListener('click', function() {
                selectSubscription(this.dataset.subscription);
            });
        });
        
        // Validation
        const emailField = document.getElementById('email');
        const passwordField = document.getElementById('password');
        const confirmationField = document.getElementById('password_confirmation');
        
        if (emailField) emailField.addEventListener('blur', validateEmail);
        if (passwordField) passwordField.addEventListener('input', checkPasswordStrength);
        if (confirmationField) confirmationField.addEventListener('input', checkPasswordMatch);
        
        // Mise à jour du résumé
        ['name', 'email', 'phone', 'shop_name', 'expiry_date'].forEach(field => {
            const element = document.getElementById(field);
            if (element) {
                element.addEventListener('input', updateSummary);
            }
        });
        
        // Soumission du formulaire
        const form = document.getElementById('adminForm');
        if (form) {
            form.addEventListener('submit', handleSubmit);
        }
    }
    
    function nextStep() {
        console.log('Passage à l étape suivante');
        
        if (validateCurrentStep()) {
            if (currentStep < totalSteps) {
                currentStep++;
                updateStepDisplay();
                updateSummary();
                updateDebugInfo();
            }
        }
    }
    
    function prevStep() {
        console.log('Retour à l étape précédente');
        
        if (currentStep > 1) {
            currentStep--;
            updateStepDisplay();
            updateDebugInfo();
        }
    }
    
    function updateStepDisplay() {
        console.log('Mise à jour de l affichage de l étape:', currentStep);
        
        // Mettre à jour les indicateurs d'étapes
        document.querySelectorAll('.step-item').forEach(item => {
            const stepNum = parseInt(item.dataset.step);
            item.classList.remove('active', 'completed');
            
            if (stepNum === currentStep) {
                item.classList.add('active');
            } else if (stepNum < currentStep) {
                item.classList.add('completed');
            }
        });
        
        // Mettre à jour le titre
        const titleElement = document.getElementById('stepTitle');
        if (titleElement) {
            titleElement.textContent = stepTitles[currentStep];
        }
        
        // Mettre à jour les sections de formulaire - méthode corrigée
        document.querySelectorAll('.form-step').forEach(step => {
            step.classList.remove('active', 'show-step');
            step.style.display = 'none';
        });
        
        const currentSection = document.querySelector(`.form-step[data-step="${currentStep}"]`);
        if (currentSection) {
            currentSection.classList.add('active', 'show-step');
            currentSection.style.display = 'block';
            console.log('Section affichée pour l étape:', currentStep);
        } else {
            console.error('Section non trouvée pour l étape:', currentStep);
        }
        
        // Mettre à jour les boutons
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const submitBtn = document.getElementById('submitBtn');
        
        if (prevBtn) prevBtn.style.display = currentStep > 1 ? 'block' : 'none';
        if (nextBtn) nextBtn.style.display = currentStep < totalSteps ? 'block' : 'none';
        if (submitBtn) submitBtn.style.display = currentStep === totalSteps ? 'block' : 'none';
    }
    
    function validateCurrentStep() {
        let valid = true;
        const currentSection = document.querySelector(`.form-step[data-step="${currentStep}"]`);
        
        if (!currentSection) return false;
        
        // Valider les champs requis
        const requiredFields = currentSection.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                valid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });
        
        // Validations spécifiques par étape
        if (currentStep === 1) {
            valid = valid && validateEmail();
        } else if (currentStep === 2) {
            const passwordStrength = checkPasswordStrength();
            const passwordMatch = checkPasswordMatch();
            valid = valid && passwordMatch && passwordStrength >= 2;
        }
        
        return valid;
    }
    
    function validateEmail() {
        const email = document.getElementById('email');
        const emailError = document.getElementById('emailError');
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (!email || !emailError) return true;
        
        if (email.value && !emailRegex.test(email.value)) {
            emailError.textContent = 'Format d email invalide';
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
        const strengthFill = document.getElementById('strengthFill');
        const strengthText = document.getElementById('strengthText');
        
        if (!password || !strengthFill || !strengthText) return 0;
        
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
        
        strengthFill.className = `strength-fill ${className}`;
        strengthText.textContent = text;
        
        return strength;
    }
    
    function checkPasswordMatch() {
        const password = document.getElementById('password');
        const confirmation = document.getElementById('password_confirmation');
        const error = document.getElementById('passwordMatchError');
        
        if (!password || !confirmation || !error) return true;
        
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
        // Mettre à jour la sélection visuelle
        document.querySelectorAll('.subscription-card').forEach(card => {
            card.classList.remove('selected');
        });
        const selectedCard = document.querySelector(`[data-subscription="${type}"]`);
        if (selectedCard) {
            selectedCard.classList.add('selected');
        }
        
        // Mettre à jour le champ caché
        const subscriptionField = document.getElementById('subscription_type');
        if (subscriptionField) {
            subscriptionField.value = type;
        }
        
        // Mettre à jour les limites
        const limits = {
            trial: { managers: 1, employees: 2 },
            basic: { managers: 3, employees: 10 },
            premium: { managers: 10, employees: 50 },
            enterprise: { managers: 100, employees: 1000 }
        };
        
        const managersField = document.getElementById('max_managers');
        const employeesField = document.getElementById('max_employees');
        
        if (limits[type] && managersField && employeesField) {
            managersField.value = limits[type].managers;
            employeesField.value = limits[type].employees;
        }
        
        updateSummary();
    }
    
    function updateSummary() {
        if (currentStep === 4) {
            const summaryElements = {
                'summary-name': document.getElementById('name')?.value || '-',
                'summary-email': document.getElementById('email')?.value || '-',
                'summary-phone': document.getElementById('phone')?.value || 'Non renseigné',
                'summary-shop': document.getElementById('shop_name')?.value || '-',
                'summary-managers': document.getElementById('max_managers')?.value || '-',
                'summary-employees': document.getElementById('max_employees')?.value || '-'
            };
            
            Object.keys(summaryElements).forEach(id => {
                const element = document.getElementById(id);
                if (element) {
                    element.textContent = summaryElements[id];
                }
            });
            
            const subscription = document.getElementById('subscription_type')?.value || 'trial';
            const subscriptionNames = {
                trial: 'Essai Gratuit',
                basic: 'Basic',
                premium: 'Premium',
                enterprise: 'Enterprise'
            };
            
            const summarySubscription = document.getElementById('summary-subscription');
            if (summarySubscription) {
                summarySubscription.textContent = subscriptionNames[subscription];
            }
            
            const expiryDate = document.getElementById('expiry_date')?.value;
            const summaryExpiry = document.getElementById('summary-expiry');
            if (summaryExpiry) {
                summaryExpiry.textContent = expiryDate ? 
                    new Date(expiryDate).toLocaleDateString('fr-FR') : 'Illimité';
            }
        }
    }
    
    function handleSubmit(e) {
        e.preventDefault();
        
        if (!validateCurrentStep()) {
            return;
        }
        
        const submitBtn = document.getElementById('submitBtn');
        if (submitBtn) {
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Création en cours...';
            submitBtn.disabled = true;
        }
        
        // Soumettre le formulaire normalement
        setTimeout(() => {
            e.target.submit();
        }, 500);
    }
});
</script>
@endsection