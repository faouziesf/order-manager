@extends('layouts.super-admin')

@section('title', 'Nouvel Utilisateur Confirmi')
@section('page-title', 'Nouvel Utilisateur Confirmi')

@section('content')
    <div style="max-width:560px">
        <div class="sa-card">
            <div class="sa-card-header">
                <h3 class="sa-card-title"><i class="fas fa-user-plus" style="color:var(--sa-primary);margin-right:8px"></i>Créer un Utilisateur</h3>
                <a href="{{ route('super-admin.confirmi-users.index') }}" class="sa-btn sa-btn-outline sa-btn-sm"><i class="fas fa-arrow-left"></i> Retour</a>
            </div>

            @if($errors->any())
                <div class="sa-alert sa-alert-danger"><i class="fas fa-exclamation-circle"></i><div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div></div>
            @endif

            <form method="POST" action="{{ route('super-admin.confirmi-users.store') }}">
                @csrf
                <div class="sa-form-group">
                    <label class="sa-form-label">Nom complet *</label>
                    <input type="text" name="name" class="sa-input" value="{{ old('name') }}" required>
                </div>
                <div class="sa-form-group">
                    <label class="sa-form-label">Email *</label>
                    <input type="email" name="email" class="sa-input" value="{{ old('email') }}" required>
                </div>
                <div class="sa-grid sa-grid-2">
                    <div class="sa-form-group">
                        <label class="sa-form-label">Mot de passe *</label>
                        <input type="password" name="password" class="sa-input" required minlength="6">
                    </div>
                    <div class="sa-form-group">
                        <label class="sa-form-label">Confirmer *</label>
                        <input type="password" name="password_confirmation" class="sa-input" required>
                    </div>
                </div>
                <div class="sa-grid sa-grid-2">
                    <div class="sa-form-group">
                        <label class="sa-form-label">Téléphone</label>
                        <input type="text" name="phone" class="sa-input" value="{{ old('phone') }}">
                    </div>
                    <div class="sa-form-group">
                        <label class="sa-form-label">Rôle *</label>
                        <select name="role" class="sa-input sa-select" required>
                            <option value="commercial" {{ old('role') === 'commercial' ? 'selected' : '' }}>Commercial</option>
                            <option value="employee" {{ old('role') === 'employee' ? 'selected' : '' }}>Employé</option>
                            <option value="agent" {{ old('role') === 'agent' ? 'selected' : '' }}>Agent</option>
                        </select>
                    </div>
                </div>
                <div style="display:flex;gap:12px;margin-top:24px;padding-top:20px;border-top:1px solid var(--sa-border)">
                    <button type="submit" class="sa-btn sa-btn-primary"><i class="fas fa-save"></i> Créer</button>
                    <a href="{{ route('super-admin.confirmi-users.index') }}" class="sa-btn sa-btn-outline">Annuler</a>
                </div>
            </form>
        </div>
    </div>
@endsection
