@extends('layouts.admin')

@section('title', 'Modifier l\'Employé')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Modifier l'Employé</h1>
        <p class="text-muted">Modifier les informations de {{ $employee->name }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.employees.show', $employee) }}" class="btn btn-info">
            <i class="fas fa-eye me-2"></i>Voir
        </a>
        <a href="{{ route('admin.employees.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Retour
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-white d-flex align-items-center justify-content-center me-3" 
                         style="width: 40px; height: 40px;">
                        <span class="text-success font-weight-bold">
                            {{ substr($employee->name, 0, 1) }}
                        </span>
                    </div>
                    <div>
                        <h5 class="mb-0">{{ $employee->name }}</h5>
                        <small class="opacity-75">{{ $employee->email }}</small>
                        @if($employee->manager)
                            <br><small class="opacity-75">Supervisé par: {{ $employee->manager->name }}</small>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                <form method="POST" action="{{ route('admin.employees.update', $employee) }}">
                    @csrf
                    @method('PUT')
                    
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
                                       value="{{ old('name', $employee->name) }}" 
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
                                       value="{{ old('email', $employee->email) }}" 
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
                                   value="{{ old('phone', $employee->phone) }}" 
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
                                    <option value="{{ $manager->id }}" 
                                            {{ old('manager_id', $employee->manager_id) == $manager->id ? 'selected' : '' }}>
                                        {{ $manager->name }} - {{ $manager->email }}
                                    </option>
                                @endforeach
                            </select>
                            @error('manager_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Modifier l'assignation du manager superviseur de cet employé.
                            </small>
                        </div>
                        
                        @if($employee->manager)
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Manager actuel :</strong> {{ $employee->manager->name }} ({{ $employee->manager->email }})
                            </div>
                        @endif
                    </div>
                    
                    <!-- Modifier le mot de passe -->
                    <div class="mb-4">
                        <h6 class="text-success border-bottom pb-2 mb-3">
                            <i class="fas fa-key me-2"></i>Modifier le mot de passe
                        </h6>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Laissez vide si vous ne souhaitez pas modifier le mot de passe
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Nouveau mot de passe</label>
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           id="password" 
                                           name="password">
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
                                <label for="password_confirmation" class="form-label">Confirmer le nouveau mot de passe</label>
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control" 
                                           id="password_confirmation" 
                                           name="password_confirmation">
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
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="is_active" 
                                           name="is_active" 
                                           value="1" 
                                           {{ old('is_active', $employee->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        <strong>Compte actif</strong>
                                        <br>
                                        <small class="text-muted">L'employé peut se connecter</small>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body p-3">
                                        <h6 class="card-title mb-2">Statistiques</h6>
                                        <div class="d-flex justify-content-between">
                                            <small class="text-muted">Manager assigné:</small>
                                            <span class="badge {{ $employee->manager ? 'badge-primary' : 'badge-secondary' }}">
                                                {{ $employee->manager ? $employee->manager->name : 'Aucun' }}
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-between mt-1">
                                            <small class="text-muted">Créé le:</small>
                                            <small>{{ $employee->created_at->format('d/m/Y') }}</small>
                                        </div>
                                        <div class="d-flex justify-content-between mt-1">
                                            <small class="text-muted">Dernière modification:</small>
                                            <small>{{ $employee->updated_at->format('d/m/Y') }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Historique récent -->
                    <div class="mb-4">
                        <h6 class="text-success border-bottom pb-2 mb-3">
                            <i class="fas fa-history me-2"></i>Informations du compte
                        </h6>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card border-success">
                                    <div class="card-body text-center">
                                        <i class="fas fa-calendar-alt fa-2x text-success mb-2"></i>
                                        <h6>Compte créé</h6>
                                        <p class="text-muted mb-0">{{ $employee->created_at->format('d/m/Y à H:i') }}</p>
                                        <small class="text-muted">{{ $employee->created_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-info">
                                    <div class="card-body text-center">
                                        <i class="fas fa-edit fa-2x text-info mb-2"></i>
                                        <h6>Dernière modification</h6>
                                        <p class="text-muted mb-0">{{ $employee->updated_at->format('d/m/Y à H:i') }}</p>
                                        <small class="text-muted">{{ $employee->updated_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Boutons d'action -->
                    <div class="d-flex justify-content-between">
                        <div>
                            <!-- Bouton de suppression -->
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                <i class="fas fa-trash me-2"></i>Supprimer
                            </button>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.employees.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Annuler
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-2"></i>Sauvegarder
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirmer la suppression
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer l'employé <strong>{{ $employee->name }}</strong> ?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Attention :</strong> Cette action est irréversible.
                    @if($employee->manager)
                        <br>Cet employé est actuellement supervisé par {{ $employee->manager->name }}.
                    @endif
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Annuler
                </button>
                <form method="POST" action="{{ route('admin.employees.destroy', $employee) }}" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>Supprimer définitivement
                    </button>
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
    
    if (password && confirmation) {
        if (password !== confirmation) {
            this.classList.add('is-invalid');
            this.classList.remove('is-valid');
        } else {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        }
    } else {
        this.classList.remove('is-invalid', 'is-valid');
    }
});

// Validation du mot de passe principal
document.getElementById('password').addEventListener('input', function() {
    const confirmation = document.getElementById('password_confirmation');
    if (this.value && confirmation.value) {
        if (this.value !== confirmation.value) {
            confirmation.classList.add('is-invalid');
            confirmation.classList.remove('is-valid');
        } else {
            confirmation.classList.remove('is-invalid');
            confirmation.classList.add('is-valid');
        }
    }
});

// Alerte de changement de manager
document.getElementById('manager_id').addEventListener('change', function() {
    const currentManagerText = '{{ $employee->manager ? $employee->manager->name : "Aucun" }}';
    const selectedOption = this.options[this.selectedIndex];
    const newManagerText = selectedOption.value ? selectedOption.text.split(' - ')[0] : 'Aucun';
    
    if (currentManagerText !== newManagerText) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-warning mt-2';
        alertDiv.innerHTML = `
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Changement détecté :</strong> Manager passera de "${currentManagerText}" à "${newManagerText}"
        `;
        
        // Supprimer l'ancienne alerte s'il y en a une
        const existingAlert = this.parentNode.querySelector('.alert-warning');
        if (existingAlert) {
            existingAlert.remove();
        }
        
        // Ajouter la nouvelle alerte
        this.parentNode.appendChild(alertDiv);
    } else {
        // Supprimer l'alerte si le manager revient à la valeur originale
        const existingAlert = this.parentNode.querySelector('.alert-warning');
        if (existingAlert) {
            existingAlert.remove();
        }
    }
});
</script>
@endsection