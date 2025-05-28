@extends('layouts.admin')

@section('title', 'Créer un Employé')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Nouvel Employé</h1>
        <p class="text-muted">Créer un nouveau compte employé</p>
    </div>
    <a href="{{ route('admin.employees.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Retour
    </a>
</div>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <div class="d-flex align-items-center">
                    <i class="fas fa-user-friends me-2"></i>
                    <h5 class="mb-0">Informations de l'Employé</h5>
                </div>
            </div>
            
            <div class="card-body">
                <form method="POST" action="{{ route('admin.employees.store') }}">
                    @csrf
                    
                    <!-- Informations personnelles -->
                    <div class="mb-4">
                        <h6 class="text-success border-bottom pb-2 mb-3">
                            <i class="fas fa-user me-2"></i>Informations personnelles
                        </h6>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">
                                    Nom complet <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name') }}" 
                                       placeholder="Ex: Fatma Ben Salem"
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">
                                    Adresse email <span class="text-danger">*</span>
                                </label>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email') }}" 
                                       placeholder="employee@example.com"
                                       required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">Numéro de téléphone</label>
                            <input type="tel" 
                                   class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" 
                                   name="phone" 
                                   value="{{ old('phone') }}" 
                                   placeholder="+216 XX XXX XXX">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Attribution à un manager -->
                    <div class="mb-4">
                        <h6 class="text-success border-bottom pb-2 mb-3">
                            <i class="fas fa-user-tie me-2"></i>Attribution à un manager
                        </h6>
                        
                        <div class="mb-3">
                            <label for="manager_id" class="form-label">Manager superviseur</label>
                            <select class="form-select @error('manager_id') is-invalid @enderror" 
                                    id="manager_id" 
                                    name="manager_id">
                                <option value="">Aucun manager (employé indépendant)</option>
                                @foreach($managers as $manager)
                                    <option value="{{ $manager->id }}" {{ old('manager_id') == $manager->id ? 'selected' : '' }}>
                                        {{ $manager->name }} - {{ $manager->email }}
                                    </option>
                                @endforeach
                            </select>
                            @error('manager_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                L'employé peut être assigné à un manager pour une supervision ou travailler de manière indépendante.
                            </small>
                        </div>
                    </div>
                    
                    <!-- Informations de connexion -->
                    <div class="mb-4">
                        <h6 class="text-success border-bottom pb-2 mb-3">
                            <i class="fas fa-key me-2"></i>Informations de connexion
                        </h6>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">
                                    Mot de passe <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           id="password" 
                                           name="password" 
                                           placeholder="Minimum 8 caractères"
                                           required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                        <i class="fas fa-eye" id="passwordIcon"></i>
                                    </button>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="form-text text-muted">Minimum 8 caractères</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">
                                    Confirmer le mot de passe <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control" 
                                           id="password_confirmation" 
                                           name="password_confirmation" 
                                           placeholder="Confirmer le mot de passe"
                                           required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirmation')">
                                        <i class="fas fa-eye" id="passwordConfirmationIcon"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Paramètres du compte -->
                    <div class="mb-4">
                        <h6 class="text-success border-bottom pb-2 mb-3">
                            <i class="fas fa-cog me-2"></i>Paramètres du compte
                        </h6>
                        
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                            <label class="form-check-label" for="is_active">
                                <strong>Compte actif</strong>
                                <br>
                                <small class="text-muted">L'employé pourra se connecter immédiatement</small>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Informations importantes -->
                    <div class="alert alert-success">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle fa-2x"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="alert-heading">Informations importantes</h6>
                                <ul class="mb-0">
                                    <li>L'employé aura accès à un tableau de bord simplifié</li>
                                    <li>Il pourra consulter et traiter les commandes assignées</li>
                                    <li>Si assigné à un manager, ce dernier pourra superviser son travail</li>
                                    <li>Un email de bienvenue sera envoyé avec les informations de connexion</li>
                                    <li>L'employé ne peut pas gérer d'autres comptes utilisateurs</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Boutons d'action -->
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.employees.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Annuler
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-2"></i>Créer l'Employé
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + 'Icon');
    
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

// Validation en temps réel
document.getElementById('password_confirmation').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmation = this.value;
    
    if (confirmation && password !== confirmation) {
        this.classList.add('is-invalid');
        this.classList.remove('is-valid');
    } else if (confirmation && password === confirmation) {
        this.classList.remove('is-invalid');
        this.classList.add('is-valid');
    }
});
</script>
@endsection