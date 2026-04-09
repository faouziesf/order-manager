@extends('layouts.admin')

@section('title', 'Créer un Manager')

@section('css')
@include('admin.partials._shared-styles')
@endsection

@section('content')
<div class="container-fluid om-animate">
    <div class="om-page-header">
        <div>
            <h1 class="om-page-title">Nouveau Manager</h1>
            <p class="om-page-subtitle">Créer un nouveau compte manager</p>
        </div>
        <a href="{{ route('admin.managers.index') }}" class="om-btn om-btn-ghost">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="om-card">
                <div class="om-card-header" style="background: linear-gradient(135deg, var(--om-primary), var(--om-primary-dark)); border: none;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="om-avatar" style="background: rgba(255,255,255,0.2); border: 2px solid rgba(255,255,255,0.3);">
                            <i class="fas fa-user-tie" style="color: white;"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 text-white fw-bold">Informations du Manager</h5>
                            <small class="text-white-50">Remplissez les champs ci-dessous</small>
                        </div>
                    </div>
                </div>

                <div class="om-card-body">
                    <form method="POST" action="{{ route('admin.managers.store') }}">
                        @csrf

                        <div class="om-form-section">
                            <div class="om-form-section-title">
                                <i class="fas fa-user"></i> Informations personnelles
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="om-form-group">
                                        <label class="om-form-label">Nom complet <span class="text-danger">*</span></label>
                                        <input type="text" class="om-form-input @error('name') is-invalid @enderror"
                                               name="name" value="{{ old('name') }}" required placeholder="Ex: Ahmed Ben Ali">
                                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="om-form-group">
                                        <label class="om-form-label">Adresse email <span class="text-danger">*</span></label>
                                        <input type="email" class="om-form-input @error('email') is-invalid @enderror"
                                               name="email" value="{{ old('email') }}" required placeholder="manager@example.com">
                                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="om-form-group">
                                        <label class="om-form-label">Numéro de téléphone</label>
                                        <input type="tel" class="om-form-input @error('phone') is-invalid @enderror"
                                               name="phone" value="{{ old('phone') }}" placeholder="+216 XX XXX XXX">
                                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="om-form-section">
                            <div class="om-form-section-title">
                                <i class="fas fa-key"></i> Informations de connexion
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="om-form-group">
                                        <label class="om-form-label">Mot de passe <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                                   id="password" name="password" required style="border-radius: var(--om-radius-sm) 0 0 var(--om-radius-sm);">
                                            <button class="btn btn-outline-secondary" type="button" onclick="togglePwd('password')">
                                                <i class="fas fa-eye" id="passwordIcon"></i>
                                            </button>
                                        </div>
                                        <small style="color: var(--om-gray-500); font-size: 0.8rem;">Minimum 8 caractères</small>
                                        @error('password')<div class="text-danger" style="font-size: 0.8rem;">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="om-form-group">
                                        <label class="om-form-label">Confirmer <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="password" class="form-control"
                                                   id="password_confirmation" name="password_confirmation" required style="border-radius: var(--om-radius-sm) 0 0 var(--om-radius-sm);">
                                            <button class="btn btn-outline-secondary" type="button" onclick="togglePwd('password_confirmation')">
                                                <i class="fas fa-eye" id="password_confirmationIcon"></i>
                                            </button>
                                        </div>
                                        <small id="pwdMatch" style="font-size: 0.8rem; display: none;"></small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="om-form-section">
                            <div class="om-form-section-title">
                                <i class="fas fa-cog"></i> Paramètres du compte
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                                <label class="form-check-label" for="is_active">
                                    <strong>Compte actif</strong><br>
                                    <small style="color: var(--om-gray-500);">Le manager pourra se connecter immédiatement</small>
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-3" style="border-top: 1px solid var(--om-gray-200);">
                            <a href="{{ route('admin.managers.index') }}" class="om-btn om-btn-ghost">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                            <button type="submit" class="om-btn om-btn-primary">
                                <i class="fas fa-save"></i> Créer le Manager
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
function togglePwd(id) {
    const f = document.getElementById(id);
    const i = document.getElementById(id + 'Icon');
    if (f.type === 'password') { f.type = 'text'; i.classList.replace('fa-eye', 'fa-eye-slash'); }
    else { f.type = 'password'; i.classList.replace('fa-eye-slash', 'fa-eye'); }
}
document.getElementById('password_confirmation')?.addEventListener('input', function() {
    const pwd = document.getElementById('password').value;
    const hint = document.getElementById('pwdMatch');
    if (this.value) {
        hint.style.display = 'block';
        if (pwd === this.value) { hint.textContent = '✓ Mots de passe identiques'; hint.style.color = 'var(--om-success)'; }
        else { hint.textContent = '✗ Les mots de passe ne correspondent pas'; hint.style.color = 'var(--om-danger)'; }
    } else { hint.style.display = 'none'; }
});
</script>
@endsection
