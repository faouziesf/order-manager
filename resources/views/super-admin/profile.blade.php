@extends('layouts.super-admin')
@section('title', 'Mon Profil')
@section('page-title', 'Mon Profil')

@section('css')
<style>
.profile-card {
    background: var(--sa-card);
    border: 1px solid var(--sa-border);
    border-radius: var(--sa-radius);
    padding: 2rem;
    box-shadow: var(--sa-shadow);
    margin-bottom: 1.5rem;
}
.profile-avatar-wrap {
    display: flex; align-items: center; gap: 1.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--sa-border);
    margin-bottom: 1.5rem;
}
.profile-avatar {
    width: 72px; height: 72px; border-radius: 50%;
    background: linear-gradient(135deg, var(--sa-primary), var(--sa-primary-light));
    color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.75rem; font-weight: 800; flex-shrink: 0;
}
.profile-meta-name { font-size: 1.2rem; font-weight: 700; color: var(--sa-text); }
.profile-meta-badge {
    display: inline-flex; align-items: center; gap: 0.4rem;
    margin-top: 0.35rem; font-size: 0.75rem; font-weight: 600;
    padding: 0.25rem 0.7rem; border-radius: 6px;
    background: var(--sa-primary-50); color: var(--sa-primary-dark);
}
.section-title {
    font-size: 0.95rem; font-weight: 700; color: var(--sa-text);
    margin-bottom: 1rem; padding-bottom: 0.5rem;
    border-bottom: 1px solid var(--sa-border);
    display: flex; align-items: center; gap: 0.5rem;
}
.section-title i { color: var(--sa-primary); }
.form-group { margin-bottom: 1.1rem; }
.form-label-custom {
    display: block; font-size: 0.8rem; font-weight: 600;
    color: var(--sa-text-secondary); margin-bottom: 0.4rem;
    text-transform: uppercase; letter-spacing: 0.04em;
}
.form-control-custom {
    width: 100%; padding: 0.6rem 0.9rem;
    background: var(--sa-input-bg); color: var(--sa-text);
    border: 1px solid var(--sa-input-border); border-radius: var(--sa-radius-sm);
    font-size: 0.875rem; font-family: inherit;
    transition: border-color 0.15s, box-shadow 0.15s;
}
.form-control-custom:focus {
    outline: none; border-color: var(--sa-primary);
    box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
}
.form-control-custom::placeholder { color: var(--sa-text-muted); }
.btn-save {
    background: var(--sa-primary); color: #fff;
    border: none; border-radius: var(--sa-radius-sm);
    padding: 0.6rem 1.5rem; font-size: 0.875rem; font-weight: 600;
    cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem;
    transition: background 0.15s, box-shadow 0.15s; font-family: inherit;
}
.btn-save:hover { background: var(--sa-primary-dark); box-shadow: var(--sa-shadow-lg); }
.divider { height: 1px; background: var(--sa-border); margin: 1.75rem 0; }
</style>
@endsection

@section('content')
<div style="max-width: 700px; margin: 0 auto;">

    @if(session('success'))
        <div class="sa-alert sa-alert-success"><i class="fas fa-check-circle"></i>{{ session('success') }}</div>
    @endif

    <div class="profile-card">
        <div class="profile-avatar-wrap">
            <div class="profile-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
            <div>
                <div class="profile-meta-name">{{ $user->name }}</div>
                <span class="profile-meta-badge"><i class="fas fa-shield-halved"></i> Super Admin</span>
                <div style="margin-top:0.5rem;font-size:0.78rem;color:var(--sa-text-muted);">
                    <i class="fas fa-envelope me-1"></i>{{ $user->email }}
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('super-admin.profile.update') }}">
            @csrf
            @method('PUT')

            <div class="section-title"><i class="fas fa-id-card"></i> Informations personnelles</div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label-custom">Nom complet</label>
                        <input type="text" name="name" class="form-control-custom" value="{{ old('name', $user->name) }}" required>
                        @error('name')<div style="color:var(--sa-danger);font-size:.78rem;margin-top:.25rem;">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label-custom">Email</label>
                        <input type="email" name="email" class="form-control-custom" value="{{ old('email', $user->email) }}" required>
                        @error('email')<div style="color:var(--sa-danger);font-size:.78rem;margin-top:.25rem;">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label-custom">Téléphone</label>
                        <input type="text" name="phone" class="form-control-custom" value="{{ old('phone', $user->phone) }}" placeholder="+213...">
                        @error('phone')<div style="color:var(--sa-danger);font-size:.78rem;margin-top:.25rem;">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="divider"></div>
            <div class="section-title"><i class="fas fa-lock"></i> Changer le mot de passe</div>
            <p style="font-size:0.8rem;color:var(--sa-text-muted);margin-bottom:1rem;">Laissez vide pour conserver le mot de passe actuel.</p>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label-custom">Mot de passe actuel</label>
                        <input type="password" name="current_password" class="form-control-custom" placeholder="••••••••" autocomplete="current-password">
                        @error('current_password')<div style="color:var(--sa-danger);font-size:.78rem;margin-top:.25rem;">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label-custom">Nouveau mot de passe</label>
                        <input type="password" name="password" class="form-control-custom" placeholder="••••••••" autocomplete="new-password">
                        @error('password')<div style="color:var(--sa-danger);font-size:.78rem;margin-top:.25rem;">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label-custom">Confirmer le mot de passe</label>
                        <input type="password" name="password_confirmation" class="form-control-custom" placeholder="••••••••" autocomplete="new-password">
                    </div>
                </div>
            </div>

            <div style="display:flex;justify-content:flex-end;margin-top:0.5rem;">
                <button type="submit" class="btn-save">
                    <i class="fas fa-save"></i> Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
