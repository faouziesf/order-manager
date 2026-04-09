@extends('layouts.super-admin')

@section('title', 'Modifier - ' . $confirmiUser->name)
@section('page-title', 'Modifier l\'utilisateur')

@section('content')
    <div style="max-width:560px">
        <div class="sa-card">
            <div class="sa-card-header">
                <h3 class="sa-card-title"><i class="fas fa-edit" style="color:var(--sa-primary);margin-right:8px"></i>{{ $confirmiUser->name }}</h3>
                <a href="{{ route('super-admin.confirmi-users.index') }}" class="sa-btn sa-btn-outline sa-btn-sm"><i class="fas fa-arrow-left"></i> Retour</a>
            </div>

            @if($errors->any())
                <div class="sa-alert sa-alert-danger"><i class="fas fa-exclamation-circle"></i><div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div></div>
            @endif

            <form method="POST" action="{{ route('super-admin.confirmi-users.update', $confirmiUser) }}">
                @csrf @method('PUT')
                <div class="sa-form-group">
                    <label class="sa-form-label">Nom complet *</label>
                    <input type="text" name="name" class="sa-input" value="{{ old('name', $confirmiUser->name) }}" required>
                </div>
                <div class="sa-form-group">
                    <label class="sa-form-label">Email *</label>
                    <input type="email" name="email" class="sa-input" value="{{ old('email', $confirmiUser->email) }}" required>
                </div>
                <div class="sa-grid sa-grid-2">
                    <div class="sa-form-group">
                        <label class="sa-form-label">Nouveau mot de passe</label>
                        <input type="password" name="password" class="sa-input" placeholder="Laisser vide" minlength="6">
                    </div>
                    <div class="sa-form-group">
                        <label class="sa-form-label">Confirmer</label>
                        <input type="password" name="password_confirmation" class="sa-input">
                    </div>
                </div>
                <div class="sa-grid sa-grid-2">
                    <div class="sa-form-group">
                        <label class="sa-form-label">Téléphone</label>
                        <input type="text" name="phone" class="sa-input" value="{{ old('phone', $confirmiUser->phone) }}">
                    </div>
                    <div class="sa-form-group">
                        <label class="sa-form-label">Rôle *</label>
                        <select name="role" class="sa-input sa-select" required>
                            @foreach(['commercial','employee','agent'] as $role)
                                <option value="{{ $role }}" {{ old('role', $confirmiUser->role) === $role ? 'selected' : '' }}>{{ ucfirst($role) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div style="display:flex;gap:12px;margin-top:24px;padding-top:20px;border-top:1px solid var(--sa-border)">
                    <button type="submit" class="sa-btn sa-btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
                    <a href="{{ route('super-admin.confirmi-users.index') }}" class="sa-btn sa-btn-outline">Annuler</a>
                </div>
            </form>
        </div>

        <!-- Danger -->
        <div class="sa-card" style="margin-top:24px;border-color:var(--sa-danger)">
            <div style="display:flex;justify-content:space-between;align-items:center">
                <span style="font-size:.8125rem;font-weight:600;color:var(--sa-danger)"><i class="fas fa-exclamation-triangle" style="margin-right:6px"></i>Supprimer cet utilisateur</span>
                <form action="{{ route('super-admin.confirmi-users.destroy', $confirmiUser) }}" method="POST" onsubmit="return confirm('Supprimer cet utilisateur ?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="sa-btn sa-btn-danger sa-btn-sm"><i class="fas fa-trash"></i> Supprimer</button>
                </form>
            </div>
        </div>
    </div>
@endsection
