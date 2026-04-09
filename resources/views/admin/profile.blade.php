@extends('layouts.admin')
@section('title', 'Mon Profil')
@section('page-title', 'Mon Profil')

@section('css')
<style>
.profile-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 2rem;
    box-shadow: var(--shadow-sm);
    margin-bottom: 1.5rem;
}
.profile-avatar-wrap {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--border);
    margin-bottom: 1.5rem;
}
.profile-avatar {
    width: 72px; height: 72px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.75rem; font-weight: 800; flex-shrink: 0;
}
.profile-meta-name { font-size: 1.2rem; font-weight: 700; color: var(--text); }
.profile-meta-role {
    display: inline-flex; align-items: center; gap: 0.4rem;
    margin-top: 0.35rem;
    font-size: 0.75rem; font-weight: 600;
    padding: 0.25rem 0.7rem; border-radius: 6px;
}
.role-admin    { background: var(--primary-50); color: var(--primary); }
.role-manager  { background: var(--warning-light); color: var(--warning); }
.role-employee { background: var(--info-light); color: var(--info); }
.section-title {
    font-size: 0.95rem; font-weight: 700; color: var(--text);
    margin-bottom: 1rem; padding-bottom: 0.5rem;
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; gap: 0.5rem;
}
.section-title i { color: var(--primary); }
.form-group { margin-bottom: 1.1rem; }
.form-label-custom {
    display: block; font-size: 0.8rem; font-weight: 600;
    color: var(--text-secondary); margin-bottom: 0.4rem;
    text-transform: uppercase; letter-spacing: 0.04em;
}
.form-control-custom {
    width: 100%; padding: 0.6rem 0.9rem;
    background: var(--bg-card); color: var(--text);
    border: 1px solid var(--border); border-radius: var(--radius-sm);
    font-size: 0.875rem; font-family: inherit;
    transition: border-color 0.15s, box-shadow 0.15s;
}
.form-control-custom:focus {
    outline: none; border-color: var(--primary);
    box-shadow: 0 0 0 3px var(--primary-50);
}
.form-control-custom::placeholder { color: var(--text-muted); }
.btn-save {
    background: var(--primary); color: #fff;
    border: none; border-radius: var(--radius-sm);
    padding: 0.6rem 1.5rem; font-size: 0.875rem; font-weight: 600;
    cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem;
    transition: background 0.15s, box-shadow 0.15s;
    font-family: inherit;
}
.btn-save:hover { background: var(--primary-dark); box-shadow: var(--shadow-md); }
.divider { height: 1px; background: var(--border); margin: 1.75rem 0; }
</style>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8 col-xl-7">

        {{-- Avatar / header card --}}
        <div class="profile-card">
            <div class="profile-avatar-wrap">
                <div class="profile-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                <div>
                    <div class="profile-meta-name">{{ $user->name }}</div>
                    <span class="profile-meta-role {{ 'role-' . $user->role }}">
                        <i class="fas fa-{{ $user->role === 'admin' ? 'crown' : ($user->role === 'manager' ? 'user-tie' : 'user') }}"></i>
                        {{ ucfirst($user->role) }}
                    </span>
                    <div style="margin-top:0.5rem;font-size:0.78rem;color:var(--text-muted);">
                        <i class="fas fa-envelope me-1"></i>{{ $user->email }}
                    </div>
                </div>
            </div>

            {{-- Info form --}}
            <form method="POST" action="{{ route('admin.profile.update') }}">
                @csrf
                @method('PUT')

                <div class="section-title"><i class="fas fa-id-card"></i> Informations personnelles</div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label-custom">Nom complet</label>
                            <input type="text" name="name" class="form-control-custom @error('name') is-invalid @enderror"
                                   value="{{ old('name', $user->name) }}" required>
                            @error('name')<div class="text-danger" style="font-size:.78rem;margin-top:.25rem;">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label-custom">Email</label>
                            <input type="email" name="email" class="form-control-custom @error('email') is-invalid @enderror"
                                   value="{{ old('email', $user->email) }}" required>
                            @error('email')<div class="text-danger" style="font-size:.78rem;margin-top:.25rem;">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label-custom">Téléphone</label>
                            <input type="text" name="phone" class="form-control-custom @error('phone') is-invalid @enderror"
                                   value="{{ old('phone', $user->phone) }}" placeholder="+213...">
                            @error('phone')<div class="text-danger" style="font-size:.78rem;margin-top:.25rem;">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    @if($user->isAdmin())
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label-custom">Nom de la boutique</label>
                            <input type="text" name="shop_name" class="form-control-custom"
                                   value="{{ old('shop_name', $user->shop_name) }}" placeholder="Ma boutique">
                        </div>
                    </div>
                    @endif
                </div>

                <div class="divider"></div>
                <div class="section-title"><i class="fas fa-lock"></i> Changer le mot de passe</div>
                <p style="font-size:0.8rem;color:var(--text-muted);margin-bottom:1rem;">Laissez vide pour conserver le mot de passe actuel.</p>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label-custom">Mot de passe actuel</label>
                            <input type="password" name="current_password" class="form-control-custom @error('current_password') is-invalid @enderror"
                                   placeholder="••••••••" autocomplete="current-password">
                            @error('current_password')<div class="text-danger" style="font-size:.78rem;margin-top:.25rem;">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label-custom">Nouveau mot de passe</label>
                            <input type="password" name="password" class="form-control-custom @error('password') is-invalid @enderror"
                                   placeholder="••••••••" autocomplete="new-password">
                            @error('password')<div class="text-danger" style="font-size:.78rem;margin-top:.25rem;">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label-custom">Confirmer le mot de passe</label>
                            <input type="password" name="password_confirmation" class="form-control-custom"
                                   placeholder="••••••••" autocomplete="new-password">
                        </div>
                    </div>
                </div>

                <div style="margin-top:0.5rem;">
                    <button type="submit" class="btn-save">
                        <i class="fas fa-save"></i> Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection
