@extends('layouts.admin')

@section('title', 'Créer un Employé')

@section('content')
<div class="container-fluid animate-fade-in" x-data="createEmployeeForm()">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h2 fw-bold text-dark mb-2">Nouvel Employé</h1>
            <p class="text-muted">Créer un nouveau compte employé</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.employees.index') }}" 
               class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>
                Retour
            </a>
        </div>
    </div>

    <!-- Main Form Card -->
    <div class="row justify-content-center">
        <div class="col-xl-10">
            <div class="card shadow-lg border-0">
                <!-- Card Header -->
                <div class="card-header bg-gradient-success text-white py-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-white bg-opacity-25 rounded-3 p-2 me-3">
                            <i class="fas fa-user-friends fa-lg"></i>
                        </div>
                        <h4 class="card-title mb-0">Informations de l'Employé</h4>
                    </div>
                </div>

                <!-- Form Content -->
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('admin.employees.store') }}" x-ref="form">
                        @csrf
                        
                        <!-- Informations personnelles -->
                        <div class="mb-5">
                            <div class="d-flex align-items-center mb-3 pb-2 border-bottom border-success border-opacity-25">
                                <i class="fas fa-user text-success me-2"></i>
                                <h5 class="text-success mb-0">Informations personnelles</h5>
                            </div>
                            
                            <div class="row g-3">
                                <!-- Nom complet -->
                                <div class="col-md-6">
                                    <label for="name" class="form-label fw-medium">
                                        Nom complet <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name') }}" 
                                           placeholder="Ex: Fatma Ben Salem"
                                           class="form-control form-control-lg @error('name') is-invalid @enderror"
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-exclamation-circle me-2"></i>
                                                {{ $message }}
                                            </div>
                                        </div>
                                    @enderror
                                </div>
                                
                                <!-- Email -->
                                <div class="col-md-6">
                                    <label for="email" class="form-label fw-medium">
                                        Adresse email <span class="text-danger">*</span>
                                    </label>
                                    <input type="email" 
                                           id="email" 
                                           name="email" 
                                           value="{{ old('email') }}" 
                                           placeholder="employee@example.com"
                                           class="form-control form-control-lg @error('email') is-invalid @enderror"
                                           required>
                                    @error('email')
                                        <div class="invalid-feedback">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-exclamation-circle me-2"></i>
                                                {{ $message }}
                                            </div>
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- Téléphone -->
                            <div class="mt-3">
                                <label for="phone" class="form-label fw-medium">Numéro de téléphone</label>
                                <input type="tel" 
                                       id="phone" 
                                       name="phone" 
                                       value="{{ old('phone') }}" 
                                       placeholder="+216 XX XXX XXX"
                                       class="form-control form-control-lg @error('phone') is-invalid @enderror">
                                @error('phone')
                                    <div class="invalid-feedback">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-exclamation-circle me-2"></i>
                                            {{ $message }}
                                        </div>
                                    </div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Attribution à un manager -->
                        <div class="mb-5">
                            <div class="d-flex align-items-center mb-3 pb-2 border-bottom border-success border-opacity-25">
                                <i class="fas fa-user-tie text-success me-2"></i>
                                <h5 class="text-success mb-0">Attribution à un manager</h5>
                            </div>
                            
                            <div class="col-12">
                                <label for="manager_id" class="form-label fw-medium">Manager superviseur</label>
                                <select id="manager_id" 
                                        name="manager_id" 
                                        class="form-select form-select-lg @error('manager_id') is-invalid @enderror">
                                    <option value="">Aucun manager (employé indépendant)</option>
                                    @foreach($managers as $manager)
                                        <option value="{{ $manager->id }}" {{ old('manager_id') == $manager->id ? 'selected' : '' }}>
                                            {{ $manager->name }} - {{ $manager->email }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('manager_id')
                                    <div class="invalid-feedback">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-exclamation-circle me-2"></i>
                                            {{ $message }}
                                        </div>
                                    </div>
                                @enderror
                                <div class="form-text">
                                    L'employé peut être assigné à un manager pour une supervision ou travailler de manière indépendante.
                                </div>
                            </div>
                        </div>
                        
                        <!-- Informations de connexion -->
                        <div class="mb-5">
                            <div class="d-flex align-items-center mb-3 pb-2 border-bottom border-success border-opacity-25">
                                <i class="fas fa-key text-success me-2"></i>
                                <h5 class="text-success mb-0">Informations de connexion</h5>
                            </div>
                            
                            <div class="row g-3">
                                <!-- Mot de passe -->
                                <div class="col-md-6">
                                    <label for="password" class="form-label fw-medium">
                                        Mot de passe <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="password" 
                                               id="password" 
                                               name="password" 
                                               placeholder="Minimum 8 caractères"
                                               class="form-control form-control-lg @error('password') is-invalid @enderror"
                                               required
                                               x-model="password"
                                               @input="checkPasswordMatch">
                                        <button type="button" 
                                                @click="togglePassword('password')"
                                                class="btn btn-outline-secondary"
                                                tabindex="-1">
                                            <i class="fas fa-eye" x-ref="passwordIcon"></i>
                                        </button>
                                    </div>
                                    @error('password')
                                        <div class="invalid-feedback">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-exclamation-circle me-2"></i>
                                                {{ $message }}
                                            </div>
                                        </div>
                                    @enderror
                                    <div class="form-text">Minimum 8 caractères</div>
                                </div>
                                
                                <!-- Confirmation mot de passe -->
                                <div class="col-md-6">
                                    <label for="password_confirmation" class="form-label fw-medium">
                                        Confirmer le mot de passe <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="password" 
                                               id="password_confirmation" 
                                               name="password_confirmation" 
                                               placeholder="Confirmer le mot de passe"
                                               class="form-control form-control-lg"
                                               x-bind:class="{ 
                                                   'is-invalid': password.length > 0 && passwordConfirmation.length > 0 && !passwordMatch, 
                                                   'is-valid': password.length > 0 && passwordConfirmation.length > 0 && passwordMatch 
                                               }"
                                               required
                                               x-model="passwordConfirmation"
                                               @input="checkPasswordMatch">
                                        <button type="button" 
                                                @click="togglePassword('password_confirmation')"
                                                class="btn btn-outline-secondary"
                                                tabindex="-1">
                                            <i class="fas fa-eye" x-ref="passwordConfirmationIcon"></i>
                                        </button>
                                    </div>
                                    <div x-show="password.length > 0 && passwordConfirmation.length > 0 && !passwordMatch" 
                                         x-transition
                                         class="invalid-feedback d-block">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-exclamation-circle me-2"></i>
                                            Les mots de passe ne correspondent pas
                                        </div>
                                    </div>
                                    <div x-show="password.length > 0 && passwordConfirmation.length > 0 && passwordMatch" 
                                         x-transition
                                         class="valid-feedback d-block">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-check-circle me-2"></i>
                                            Les mots de passe correspondent
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Paramètres du compte -->
                        <div class="mb-5">
                            <div class="d-flex align-items-center mb-3 pb-2 border-bottom border-success border-opacity-25">
                                <i class="fas fa-cog text-success me-2"></i>
                                <h5 class="text-success mb-0">Paramètres du compte</h5>
                            </div>
                            
                            <div class="bg-light rounded p-3">
                                <div class="form-check">
                                    <input type="checkbox" 
                                           id="is_active" 
                                           name="is_active" 
                                           value="1" 
                                           checked
                                           class="form-check-input">
                                    <label class="form-check-label" for="is_active">
                                        <div class="fw-semibold">Compte actif</div>
                                        <small class="text-muted">L'employé pourra se connecter immédiatement</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Informations importantes -->
                        <div class="mb-4">
                            <div class="alert alert-success">
                                <div class="d-flex">
                                    <div class="me-3">
                                        <div class="bg-success bg-opacity-25 rounded-3 p-2">
                                            <i class="fas fa-info-circle text-success"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="alert-heading">Informations importantes</h6>
                                        <ul class="mb-0 small">
                                            <li class="mb-1">L'employé aura accès à un tableau de bord simplifié</li>
                                            <li class="mb-1">Il pourra consulter et traiter les commandes assignées</li>
                                            <li class="mb-1">Si assigné à un manager, ce dernier pourra superviser son travail</li>
                                            <li class="mb-1">Un email de bienvenue sera envoyé avec les informations de connexion</li>
                                            <li class="mb-1">L'employé ne peut pas gérer d'autres comptes utilisateurs</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Boutons d'action -->
                        <div class="d-flex flex-column flex-sm-row justify-content-end gap-2">
                            <a href="{{ route('admin.employees.index') }}" 
                               class="btn btn-secondary btn-lg">
                                <i class="fas fa-times me-2"></i>
                                Annuler
                            </a>
                            <button type="submit" 
                                    class="btn btn-success btn-lg"
                                    x-bind:disabled="!canSubmit"
                                    x-bind:class="{ 'opacity-50': !canSubmit }">
                                <i class="fas fa-save me-2"></i>
                                Créer l'Employé
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function createEmployeeForm() {
    return {
        password: '',
        passwordConfirmation: '',
        passwordMatch: false,
        
        init() {
            this.checkPasswordMatch();
        },
        
        checkPasswordMatch() {
            if (this.password.length === 0 && this.passwordConfirmation.length === 0) {
                this.passwordMatch = true;
            } else if (this.password.length > 0 && this.passwordConfirmation.length > 0) {
                this.passwordMatch = this.password === this.passwordConfirmation;
            } else {
                this.passwordMatch = false;
            }
        },
        
        get canSubmit() {
            return this.password.length >= 8 && this.passwordConfirmation.length >= 8 && this.passwordMatch;
        },
        
        togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = this.$refs[fieldId + 'Icon'];
            
            if (!field || !icon) {
                console.warn('Élément non trouvé pour toggle password:', fieldId);
                return;
            }
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    }
}

// Animation d'apparition progressive
document.addEventListener('DOMContentLoaded', function() {
    const sections = document.querySelectorAll('.mb-5');
    sections.forEach((section, index) => {
        setTimeout(() => {
            section.classList.add('animate-slide-up');
        }, index * 100);
    });
});
</script>

<style>
/* Animations personnalisées */
@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.animate-fade-in {
    animation: fadeIn 0.5s ease-out;
}

.animate-slide-up {
    animation: slideUp 0.5s ease-out forwards;
}

/* Gradient de succès personnalisé */
.bg-gradient-success {
    background: linear-gradient(135deg, #198754 0%, #20c997 100%);
}

/* Amélioration des transitions */
.btn {
    transition: all 0.2s ease-in-out;
}

.btn:hover {
    transform: translateY(-1px);
}

.form-control:focus, .form-select:focus {
    box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
    border-color: #198754;
}

/* Amélioration des cartes */
.card {
    border-radius: 1rem;
    transition: transform 0.2s ease-in-out;
    border: 0;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175);
}
</style>
@endsection