@extends('layouts.super-admin')

@section('title', 'Modifier un Administrateur')

@section('breadcrumb')
    <ol class="breadcrumb breadcrumb-custom">
        <li class="breadcrumb-item"><a href="{{ route('super-admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('super-admin.admins.index') }}">Administrateurs</a></li>
        <li class="breadcrumb-item active">Modifier</li>
    </ol>
@endsection

@section('page-header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">Modifier un Administrateur</h1>
            <p class="page-subtitle">Modifiez les informations de {{ $admin->name }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('super-admin.admins.show', $admin) }}" class="btn btn-outline-info">
                <i class="fas fa-eye me-2"></i>Voir le profil
            </a>
            <a href="{{ route('super-admin.admins.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Retour à la liste
            </a>
        </div>
    </div>
@endsection

@section('css')
<style>
    .edit-container {
        max-width: 1000px;
        margin: 0 auto;
    }
    
    .admin-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }
    
    .admin-header {
        background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
        color: white;
        padding: 30px;
    }
    
    .admin-avatar {
        width: 80px;
        height: 80px;
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        font-weight: 600;
        margin-bottom: 15px;
    }
    
    .admin-info h3 {
        margin: 0 0 5px 0;
        font-size: 24px;
        font-weight: 600;
    }
    
    .admin-meta {
        display: flex;
        align-items: center;
        gap: 20px;
        margin-top: 15px;
        font-size: 14px;
        opacity: 0.9;
    }
    
    .meta-item {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    
    .form-section {
        padding: 30px;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .form-section:last-child {
        border-bottom: none;
    }
    
    .section-title {
        font-size: 18px;
        font-weight: 600;
        color: #111827;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .section-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6b7280;
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
    
    .subscription-selection {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin: 20px 0;
    }
    
    .subscription-option {
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: white;
        position: relative;
    }
    
    .subscription-option.selected {
        border-color: #4f46e5;
        background: #f8faff;
    }
    
    .subscription-option.selected::before {
        content: '✓';
        position: absolute;
        top: -8px;
        right: -8px;
        width: 20px;
        height: 20px;
        background: #10b981;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
    }
    
    .subscription-name {
        font-weight: 600;
        margin-bottom: 5px;
        color: #111827;
    }
    
    .subscription-desc {
        font-size: 13px;
        color: #6b7280;
    }
    
    .status-toggle {
        background: #f9fafb;
        border-radius: 10px;
        padding: 20px;
        margin: 20px 0;
    }
    
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 24px;
    }
    
    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    
    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 24px;
    }
    
    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }
    
    input:checked + .toggle-slider {
        background-color: #4f46e5;
    }
    
    input:checked + .toggle-slider:before {
        transform: translateX(26px);
    }
    
    .password-section {
        background: #fef3c7;
        border: 1px solid #f59e0b;
        border-radius: 10px;
        padding: 20px;
        margin: 20px 0;
    }
    
    .password-warning {
        color: #92400e;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 15px;
    }
    
    .limits-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    
    .btn-section {
        background: #f9fafb;
        padding: 25px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
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
    
    .stats-mini {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
        margin: 20px 0;
    }
    
    .stat-item {
        text-align: center;
        padding: 15px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 10px;
    }
    
    .stat-number {
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 5px;
    }
    
    .stat-label {
        font-size: 12px;
        opacity: 0.8;
    }
    
    .alert {
        border: none;
        border-radius: 10px;
        padding: 15px 20px;
    }
</style>
@endsection

@section('content')
    <div class="edit-container">
        <div class="admin-card">
            <!-- En-tête avec informations admin -->
            <div class="admin-header">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <div class="admin-avatar">
                            {{ substr($admin->name, 0, 2) }}
                        </div>
                    </div>
                    <div class="col">
                        <div class="admin-info">
                            <h3>{{ $admin->name }}</h3>
                            <div class="admin-meta">
                                <div class="meta-item">
                                    <i class="fas fa-store"></i>
                                    {{ $admin->shop_name }}
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-envelope"></i>
                                    {{ $admin->email }}
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-calendar"></i>
                                    Créé le {{ $admin->created_at->format('d/m/Y') }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="stats-mini">
                            <div class="stat-item">
                                <div class="stat-number">{{ $admin->total_orders }}</div>
                                <div class="stat-label">Commandes</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number">{{ $admin->managers_count ?? 0 }}</div>
                                <div class="stat-label">Managers</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number">{{ $admin->employees_count ?? 0 }}</div>
                                <div class="stat-label">Employés</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Formulaire d'édition -->
            <form action="{{ route('super-admin.admins.update', $admin) }}" method="POST" id="editForm">
                @csrf
                @method('PUT')
                
                <!-- Section Informations personnelles -->
                <div class="form-section">
                    <div class="section-title">
                        <div class="section-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        Informations personnelles
                    </div>
                    
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name', $admin->name) }}" 
                                       required>
                                <label for="name">Nom complet *</label>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email', $admin->email) }}" 
                                       required>
                                <label for="email">Adresse email *</label>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="tel" 
                                       class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" 
                                       name="phone" 
                                       value="{{ old('phone', $admin->phone) }}">
                                <label for="phone">Numéro de téléphone</label>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" 
                                       class="form-control @error('shop_name') is-invalid @enderror" 
                                       id="shop_name" 
                                       name="shop_name" 
                                       value="{{ old('shop_name', $admin->shop_name) }}" 
                                       required>
                                <label for="shop_name">Nom de la boutique *</label>
                                @error('shop_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Section Sécurité -->
                <div class="form-section">
                    <div class="section-title">
                        <div class="section-icon">
                            <i class="fas fa-lock"></i>
                        </div>
                        Sécurité et accès
                    </div>
                    
                    <div class="password-section">
                        <div class="password-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            Laissez vide pour conserver le mot de passe actuel
                        </div>
                        
                        <div class="form-floating">
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Nouveau mot de passe">
                            <label for="password">Nouveau mot de passe</label>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="form-floating">
                        <input type="date" 
                               class="form-control @error('expiry_date') is-invalid @enderror" 
                               id="expiry_date" 
                               name="expiry_date" 
                               value="{{ old('expiry_date', $admin->expiry_date ? $admin->expiry_date->format('Y-m-d') : '') }}">
                        <label for="expiry_date">Date d'expiration</label>
                        @error('expiry_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Laissez vide pour un accès illimité</small>
                    </div>
                </div>
                
                <!-- Section Abonnement -->
                <div class="form-section">
                    <div class="section-title">
                        <div class="section-icon">
                            <i class="fas fa-crown"></i>
                        </div>
                        Type d'abonnement
                    </div>
                    
                    <div class="subscription-selection">
                        <div class="subscription-option {{ ($admin->subscription_type ?? 'trial') === 'trial' ? 'selected' : '' }}" 
                             data-subscription="trial">
                            <div class="subscription-name">Essai</div>
                            <div class="subscription-desc">Accès limité</div>
                        </div>
                        
                        <div class="subscription-option {{ ($admin->subscription_type ?? 'trial') === 'basic' ? 'selected' : '' }}" 
                             data-subscription="basic">
                            <div class="subscription-name">Basic</div>
                            <div class="subscription-desc">Petites équipes</div>
                        </div>
                        
                        <div class="subscription-option {{ ($admin->subscription_type ?? 'trial') === 'premium' ? 'selected' : '' }}" 
                             data-subscription="premium">
                            <div class="subscription-name">Premium</div>
                            <div class="subscription-desc">Équipes moyennes</div>
                        </div>
                        
                        <div class="subscription-option {{ ($admin->subscription_type ?? 'trial') === 'enterprise' ? 'selected' : '' }}" 
                             data-subscription="enterprise">
                            <div class="subscription-name">Enterprise</div>
                            <div class="subscription-desc">Grandes entreprises</div>
                        </div>
                    </div>
                    
                    <input type="hidden" name="subscription_type" id="subscription_type" value="{{ $admin->subscription_type ?? 'trial' }}">
                    
                    <div class="limits-grid">
                        <div class="form-floating">
                            <input type="number" 
                                   class="form-control @error('max_managers') is-invalid @enderror" 
                                   id="max_managers" 
                                   name="max_managers" 
                                   value="{{ old('max_managers', $admin->max_managers) }}" 
                                   min="0" 
                                   max="100">
                            <label for="max_managers">Nombre maximum de managers</label>
                            @error('max_managers')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-floating">
                            <input type="number" 
                                   class="form-control @error('max_employees') is-invalid @enderror" 
                                   id="max_employees" 
                                   name="max_employees" 
                                   value="{{ old('max_employees', $admin->max_employees) }}" 
                                   min="0" 
                                   max="1000">
                            <label for="max_employees">Nombre maximum d'employés</label>
                            @error('max_employees')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Section Statut -->
                <div class="form-section">
                    <div class="section-title">
                        <div class="section-icon">
                            <i class="fas fa-toggle-on"></i>
                        </div>
                        Statut du compte
                    </div>
                    
                    <div class="status-toggle">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Compte actif</h6>
                                <small class="text-muted">
                                    {{ $admin->is_active ? 'L\'administrateur peut se connecter et utiliser la plateforme' : 'L\'administrateur ne peut pas accéder à la plateforme' }}
                                </small>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" 
                                       name="is_active" 
                                       value="1"
                                       {{ old('is_active', $admin->is_active) ? 'checked' : '' }}>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Boutons d'action -->
                <div class="btn-section">
                    <div>
                        <button type="button" class="btn btn-outline-warning" onclick="resetForm()">
                            <i class="fas fa-undo me-2"></i>Réinitialiser
                        </button>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <a href="{{ route('super-admin.admins.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>Annuler
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Mettre à jour
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
    setupSubscriptionSelection();
    setupFormValidation();
    
    function setupSubscriptionSelection() {
        document.querySelectorAll('.subscription-option').forEach(option => {
            option.addEventListener('click', function() {
                // Retirer la sélection de toutes les options
                document.querySelectorAll('.subscription-option').forEach(opt => {
                    opt.classList.remove('selected');
                });
                
                // Ajouter la sélection à l'option cliquée
                this.classList.add('selected');
                
                // Mettre à jour le champ caché
                const subscriptionType = this.dataset.subscription;
                const subscriptionField = document.getElementById('subscription_type');
                if (subscriptionField) {
                    subscriptionField.value = subscriptionType;
                }
                
                // Mettre à jour les limites par défaut
                updateDefaultLimits(subscriptionType);
            });
        });
    }
    
    function updateDefaultLimits(type) {
        const limits = {
            trial: { managers: 1, employees: 2 },
            basic: { managers: 3, employees: 10 },
            premium: { managers: 10, employees: 50 },
            enterprise: { managers: 100, employees: 1000 }
        };
        
        if (limits[type]) {
            const managersField = document.getElementById('max_managers');
            const employeesField = document.getElementById('max_employees');
            
            if (managersField && employeesField) {
                managersField.value = limits[type].managers;
                employeesField.value = limits[type].employees;
            }
        }
    }
    
    function setupFormValidation() {
        const form = document.getElementById('editForm');
        
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Validation des champs requis
            const requiredFields = form.querySelectorAll('[required]');
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            // Validation de l'email
            const email = document.getElementById('email');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (email && email.value && !emailRegex.test(email.value)) {
                email.classList.add('is-invalid');
                isValid = false;
            } else if (email) {
                email.classList.remove('is-invalid');
            }
            
            if (!isValid) {
                e.preventDefault();
                showAlert('Veuillez corriger les erreurs dans le formulaire', 'error');
            } else {
                // Montrer l'état de chargement
                const submitBtn = e.target.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Mise à jour...';
                    submitBtn.disabled = true;
                }
            }
        });
        
        // Validation en temps réel
        const emailField = document.getElementById('email');
        if (emailField) {
            emailField.addEventListener('blur', function() {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (this.value && !emailRegex.test(this.value)) {
                    this.classList.add('is-invalid');
                } else {
                    this.classList.remove('is-invalid');
                }
            });
        }
    }
    
    function resetForm() {
        if (confirm('Êtes-vous sûr de vouloir réinitialiser le formulaire ? Toutes les modifications seront perdues.')) {
            location.reload();
        }
    }
    
    function showAlert(message, type) {
        const alertClass = type === 'error' ? 'alert-danger' : 'alert-success';
        const alert = document.createElement('div');
        alert.className = `alert ${alertClass} alert-dismissible fade show`;
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        // Insérer l'alerte au début du formulaire
        const form = document.getElementById('editForm');
        form.insertBefore(alert, form.firstChild);
        
        // Faire défiler vers le haut
        alert.scrollIntoView({ behavior: 'smooth' });
        
        // Supprimer l'alerte après 5 secondes
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    }
    
    // Rendre resetForm disponible globalement
    window.resetForm = resetForm;
});
</script>
@endsection