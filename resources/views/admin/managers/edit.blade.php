@extends('layouts.admin')

@section('title', 'Modifier le Manager')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Modifier le Manager</h1>
        <p class="text-muted">Modifier les informations de {{ $manager->name }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.managers.show', $manager) }}" class="btn btn-info">
            <i class="fas fa-eye me-2"></i>Voir
        </a>
        <a href="{{ route('admin.managers.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Retour
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-white d-flex align-items-center justify-content-center me-3" 
                         style="width: 40px; height: 40px;">
                        <span class="text-primary font-weight-bold">
                            {{ substr($manager->name, 0, 1) }}
                        </span>
                    </div>
                    <div>
                        <h5 class="mb-0">{{ $manager->name }}</h5>
                        <small class="opacity-75">{{ $manager->email }}</small>
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                <form method="POST" action="{{ route('admin.managers.update', $manager) }}">
                    @csrf
                    @method('PUT')
                    
                    <!-- Informations personnelles -->
                    <div class="mb-4">
                        <h6 class="text-primary border-bottom pb-2 mb-3">
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
                                       value="{{ old('name', $manager->name) }}" 
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
                                       value="{{ old('email', $manager->email) }}" 
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
                                   value="{{ old('phone', $manager->phone) }}" 
                                   placeholder="+216 XX XXX XXX">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Modifier le mot de passe -->
                    <div class="mb-4">
                        <h6 class="text-primary border-bottom pb-2 mb-3">
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
                        <h6 class="text-primary border-bottom pb-2 mb-3">
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
                                           {{ old('is_active', $manager->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        <strong>Compte actif</strong>
                                        <br>
                                        <small class="text-muted">Le manager peut se connecter</small>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body p-3">
                                        <h6 class="card-title mb-2">Statistiques</h6>
                                        <div class="d-flex justify-content-between">
                                            <small class="text-muted">Employés gérés:</small>
                                            <span class="badge badge-info">{{ $manager->employees()->count() }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mt-1">
                                            <small class="text-muted">Créé le:</small>
                                            <small>{{ $manager->created_at->format('d/m/Y') }}</small>
                                        </div>
                                        <div class="d-flex justify-content-between mt-1">
                                            <small class="text-muted">Dernière modification:</small>
                                            <small>{{ $manager->updated_at->format('d/m/Y') }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Employés assignés -->
                    @if($manager->employees()->count() > 0)
                        <div class="mb-4">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-users me-2"></i>Employés assignés ({{ $manager->employees()->count() }})
                            </h6>
                            
                            <div class="row">
                                @foreach($manager->employees as $employee)
                                    <div class="col-md-6 mb-2">
                                        <div class="d-flex align-items-center p-2 border rounded">
                                            <div class="rounded-circle bg-success d-flex align-items-center justify-content-center me-2" 
                                                 style="width: 30px; height: 30px;">
                                                <span class="text-white" style="font-size: 12px;">
                                                    {{ substr($employee->name, 0, 1) }}
                                                </span>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="font-weight-bold" style="font-size: 13px;">{{ $employee->name }}</div>
                                                <small class="text-muted">{{ $employee->email }}</small>
                                            </div>
                                            <span class="badge {{ $employee->is_active ? 'badge-success' : 'badge-secondary' }}">
                                                {{ $employee->is_active ? 'Actif' : 'Inactif' }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    <!-- Boutons d'action -->
                    <div class="d-flex justify-content-between">
                        <div>
                            <!-- Bouton de suppression -->
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                <i class="fas fa-trash me-2"></i>Supprimer
                            </button>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.managers.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
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
                <p>Êtes-vous sûr de vouloir supprimer le manager <strong>{{ $manager->name }}</strong> ?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Attention :</strong> Cette action est irréversible.
                    @if($manager->employees()->count() > 0)
                        <br>Ce manager supervise {{ $manager->employees()->count() }} employé(s). Ils seront automatiquement désassignés.
                    @endif
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Annuler
                </button>
                <form method="POST" action="{{ route('admin.managers.destroy', $manager) }}" class="d-inline">
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
</script>
@endsection