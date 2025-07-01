@extends('layouts.admin')

@section('title', 'Modifier l\'Employé')

@section('content')
<div class="container-fluid animate-fade-in" x-data="editEmployeeForm()">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h2 fw-bold text-dark mb-2">Modifier l'Employé</h1>
            <p class="text-muted">Modifier les informations de {{ $employee->name }}</p>
        </div>
        <div class="col-md-6 text-end">
            <div class="btn-group" role="group">
                <a href="{{ route('admin.employees.show', $employee) }}" 
                   class="btn btn-primary">
                    <i class="fas fa-eye me-2"></i>
                    Voir
                </a>
                <a href="{{ route('admin.employees.index') }}" 
                   class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>
                    Retour
                </a>
            </div>
        </div>
    </div>

    <!-- Main Form Card -->
    <div class="row justify-content-center">
        <div class="col-xl-10">
            <div class="card shadow-lg border-0">
                <!-- Card Header avec avatar -->
                <div class="card-header bg-gradient-success text-white py-4">
                    <div class="d-flex align-items-center">
                        <div class="bg-white bg-opacity-25 rounded-4 p-3 me-3">
                            <span class="h4 mb-0 fw-bold">
                                {{ substr($employee->name, 0, 1) }}
                            </span>
                        </div>
                        <div>
                            <h4 class="card-title mb-1">{{ $employee->name }}</h4>
                            <small class="text-white-50">{{ $employee->email }}</small>
                            @if($employee->manager)
                                <div class="small text-white-50">Supervisé par: {{ $employee->manager->name }}</div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Form Content -->
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('admin.employees.update', $employee) }}" x-ref="form">
                        @csrf
                        @method('PUT')
                        
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
                                           value="{{ old('name', $employee->name) }}" 
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
                                           value="{{ old('email', $employee->email) }}" 
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
                                       value="{{ old('phone', $employee->phone) }}" 
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
                            
                            <div class="row">
                                <div class="col-12">
                                    <label for="manager_id" class="form-label fw-medium">Manager superviseur</label>
                                    <select id="manager_id" 
                                            name="manager_id" 
                                            x-model="selectedManagerId"
                                            @change="checkManagerChange"
                                            class="form-select form-select-lg @error('manager_id') is-invalid @enderror">
                                        <option value="">Aucun manager (employé indépendant)</option>
                                        @foreach($managers as $manager)
                                            <option value="{{ $manager->id }}" {{ old('manager_id', $employee->manager_id) == $manager->id ? 'selected' : '' }}>
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
                                        Modifier l'assignation du manager superviseur de cet employé.
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Alerte changement manager -->
                            <div x-show="managerChangeDetected" 
                                 x-transition
                                 class="alert alert-warning mt-3">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-exclamation-triangle me-2 mt-1"></i>
                                    <div>
                                        <h6 class="alert-heading">Changement détecté</h6>
                                        <p class="mb-0 small" x-text="managerChangeText"></p>
                                    </div>
                                </div>
                            </div>
                            
                            @if($employee->manager)
                                <div class="alert alert-info mt-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <div>
                                            <strong>Manager actuel :</strong>
                                            {{ $employee->manager->name }} ({{ $employee->manager->email }})
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Modifier le mot de passe -->
                        <div class="mb-5">
                            <div class="d-flex align-items-center mb-3 pb-2 border-bottom border-success border-opacity-25">
                                <i class="fas fa-key text-success me-2"></i>
                                <h5 class="text-success mb-0">Modifier le mot de passe</h5>
                            </div>
                            
                            <div class="alert alert-info">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <small>Laissez vide si vous ne souhaitez pas modifier le mot de passe</small>
                                </div>
                            </div>
                            
                            <div class="row g-3">
                                <!-- Nouveau mot de passe -->
                                <div class="col-md-6">
                                    <label for="password" class="form-label fw-medium">Nouveau mot de passe</label>
                                    <div class="input-group">
                                        <input type="password" 
                                               id="password" 
                                               name="password" 
                                               class="form-control form-control-lg @error('password') is-invalid @enderror"
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
                                
                                <!-- Confirmation nouveau mot de passe -->
                                <div class="col-md-6">
                                    <label for="password_confirmation" class="form-label fw-medium">Confirmer le nouveau mot de passe</label>
                                    <div class="input-group">
                                        <input type="password" 
                                               id="password_confirmation" 
                                               name="password_confirmation" 
                                               class="form-control form-control-lg"
                                               x-bind:class="{ 
                                                   'is-invalid': password.length > 0 && passwordConfirmation.length > 0 && !passwordMatch, 
                                                   'is-valid': password.length > 0 && passwordConfirmation.length > 0 && passwordMatch 
                                               }"
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
                        
                        <!-- Paramètres du compte et statistiques -->
                        <div class="mb-5">
                            <div class="d-flex align-items-center mb-3 pb-2 border-bottom border-success border-opacity-25">
                                <i class="fas fa-cog text-success me-2"></i>
                                <h5 class="text-success mb-0">Paramètres du compte</h5>
                            </div>
                            
                            <div class="row g-3">
                                <!-- Switch compte actif -->
                                <div class="col-md-6">
                                    <div class="bg-light rounded p-3">
                                        <div class="form-check">
                                            <input type="checkbox" 
                                                   id="is_active" 
                                                   name="is_active" 
                                                   value="1" 
                                                   {{ old('is_active', $employee->is_active) ? 'checked' : '' }}
                                                   class="form-check-input">
                                            <label class="form-check-label" for="is_active">
                                                <div class="fw-semibold">Compte actif</div>
                                                <small class="text-muted">L'employé peut se connecter</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Statistiques -->
                                <div class="col-md-6">
                                    <div class="bg-light rounded p-3">
                                        <h6 class="fw-semibold mb-3">Statistiques</h6>
                                        <div class="row g-2 small">
                                            <div class="col-6">
                                                <span class="text-muted">Manager assigné:</span>
                                            </div>
                                            <div class="col-6">
                                                <span class="badge {{ $employee->manager ? 'bg-primary' : 'bg-secondary' }}">
                                                    {{ $employee->manager ? $employee->manager->name : 'Aucun' }}
                                                </span>
                                            </div>
                                            <div class="col-6">
                                                <span class="text-muted">Créé le:</span>
                                            </div>
                                            <div class="col-6">
                                                <span>{{ $employee->created_at->format('d/m/Y') }}</span>
                                            </div>
                                            <div class="col-6">
                                                <span class="text-muted">Modifié le:</span>
                                            </div>
                                            <div class="col-6">
                                                <span>{{ $employee->updated_at->format('d/m/Y') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Informations du compte -->
                        <div class="mb-5">
                            <div class="d-flex align-items-center mb-3 pb-2 border-bottom border-success border-opacity-25">
                                <i class="fas fa-history text-success me-2"></i>
                                <h5 class="text-success mb-0">Informations du compte</h5>
                            </div>
                            
                            <div class="row g-3">
                                <!-- Compte créé -->
                                <div class="col-md-6">
                                    <div class="card border-success border-opacity-25 text-center">
                                        <div class="card-body">
                                            <div class="bg-success bg-opacity-10 rounded-3 p-3 mb-3 d-inline-block">
                                                <i class="fas fa-calendar-alt text-success fa-lg"></i>
                                            </div>
                                            <h6 class="fw-semibold text-success">Compte créé</h6>
                                            <p class="mb-1">{{ $employee->created_at->format('d/m/Y à H:i') }}</p>
                                            <small class="text-muted">{{ $employee->created_at->diffForHumans() }}</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Dernière modification -->
                                <div class="col-md-6">
                                    <div class="card border-primary border-opacity-25 text-center">
                                        <div class="card-body">
                                            <div class="bg-primary bg-opacity-10 rounded-3 p-3 mb-3 d-inline-block">
                                                <i class="fas fa-edit text-primary fa-lg"></i>
                                            </div>
                                            <h6 class="fw-semibold text-primary">Dernière modification</h6>
                                            <p class="mb-1">{{ $employee->updated_at->format('d/m/Y à H:i') }}</p>
                                            <small class="text-muted">{{ $employee->updated_at->diffForHumans() }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Boutons d'action -->
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start gap-3">
                            <!-- Bouton supprimer -->
                            <button type="button" 
                                    @click="showDeleteModal = true"
                                    class="btn btn-danger">
                                <i class="fas fa-trash me-2"></i>
                                Supprimer
                            </button>
                            
                            <!-- Actions principales -->
                            <div class="d-flex flex-column flex-sm-row gap-2">
                                <a href="{{ route('admin.employees.index') }}" 
                                   class="btn btn-secondary btn-lg">
                                    <i class="fas fa-times me-2"></i>
                                    Annuler
                                </a>
                                <button type="submit" 
                                        class="btn btn-success btn-lg">
                                    <i class="fas fa-save me-2"></i>
                                    Sauvegarder
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de confirmation de suppression -->
    <div class="modal fade" :class="{ 'show d-block': showDeleteModal }" 
         x-show="showDeleteModal" 
         x-transition
         tabindex="-1" 
         style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <!-- Header -->
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Confirmer la suppression
                    </h5>
                    <button type="button" 
                            @click="showDeleteModal = false" 
                            class="btn-close btn-close-white"></button>
                </div>
                
                <!-- Body -->
                <div class="modal-body">
                    <p class="mb-3">
                        Êtes-vous sûr de vouloir supprimer l'employé <strong>{{ $employee->name }}</strong> ?
                    </p>
                    <div class="alert alert-warning">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-exclamation-triangle me-2 mt-1"></i>
                            <div>
                                <p class="fw-semibold mb-1 small">Attention :</p>
                                <p class="mb-1 small">Cette action est irréversible.</p>
                                @if($employee->manager)
                                    <p class="mb-0 small">
                                        Cet employé est actuellement supervisé par {{ $employee->manager->name }}.
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="modal-footer">
                    <button type="button" 
                            @click="showDeleteModal = false"
                            class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>
                        Annuler
                    </button>
                    <form method="POST" action="{{ route('admin.employees.destroy', $employee) }}" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>
                            Supprimer définitivement
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function editEmployeeForm() {
    return {
        password: '',
        passwordConfirmation: '',
        passwordMatch: true,
        selectedManagerId: '{{ old('manager_id', $employee->manager_id ?? '') }}',
        originalManagerId: '{{ $employee->manager_id ?? '' }}',
        managerChangeDetected: false,
        managerChangeText: '',
        showDeleteModal: false,
        
        init() {
            this.checkPasswordMatch();
        },
        
        checkPasswordMatch() {
            if (this.password.length === 0 && this.passwordConfirmation.length === 0) {
                // Pas de changement de mot de passe
                this.passwordMatch = true;
            } else if (this.password.length > 0 && this.passwordConfirmation.length > 0) {
                // Les deux champs sont remplis
                this.passwordMatch = this.password === this.passwordConfirmation;
            } else if (this.password.length > 0 || this.passwordConfirmation.length > 0) {
                // Un seul champ est rempli
                this.passwordMatch = false;
            } else {
                this.passwordMatch = true;
            }
        },
        
        checkManagerChange() {
            const originalManagerName = '{{ $employee->manager ? $employee->manager->name : "Aucun" }}';
            const select = document.getElementById('manager_id');
            const selectedOption = select.options[select.selectedIndex];
            const newManagerName = selectedOption.value ? selectedOption.text.split(' - ')[0] : 'Aucun';
            
            if (this.originalManagerId !== this.selectedManagerId) {
                this.managerChangeDetected = true;
                this.managerChangeText = `Manager passera de "${originalManagerName}" à "${newManagerName}"`;
            } else {
                this.managerChangeDetected = false;
            }
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

/* Modal custom overlay */
.modal.show {
    backdrop-filter: blur(3px);
}
</style>
@endsection