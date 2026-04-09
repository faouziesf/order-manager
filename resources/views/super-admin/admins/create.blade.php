@extends('layouts.super-admin')

@section('title', 'Nouvel Administrateur')
@section('page-title', 'Nouvel Administrateur')

@section('content')
    <div style="max-width:720px">
        <div class="sa-card">
            <div class="sa-card-header">
                <h3 class="sa-card-title"><i class="fas fa-plus-circle" style="color:var(--sa-primary);margin-right:8px"></i>Créer un Administrateur</h3>
                <a href="{{ route('super-admin.admins.index') }}" class="sa-btn sa-btn-outline sa-btn-sm"><i class="fas fa-arrow-left"></i> Retour</a>
            </div>

            @if($errors->any())
                <div class="sa-alert sa-alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
                </div>
            @endif

            <form method="POST" action="{{ route('super-admin.admins.store') }}">
                @csrf

                <div class="sa-grid sa-grid-2">
                    <div class="sa-form-group">
                        <label class="sa-form-label">Nom complet *</label>
                        <input type="text" name="name" class="sa-input" value="{{ old('name') }}" required>
                    </div>
                    <div class="sa-form-group">
                        <label class="sa-form-label">Email *</label>
                        <input type="email" name="email" class="sa-input" value="{{ old('email') }}" required>
                    </div>
                </div>

                <div class="sa-grid sa-grid-2">
                    <div class="sa-form-group">
                        <label class="sa-form-label">Mot de passe *</label>
                        <input type="password" name="password" class="sa-input" required minlength="8">
                    </div>
                    <div class="sa-form-group">
                        <label class="sa-form-label">Téléphone</label>
                        <input type="text" name="phone" class="sa-input" value="{{ old('phone') }}">
                    </div>
                </div>

                <div class="sa-form-group">
                    <label class="sa-form-label">Nom de la boutique *</label>
                    <input type="text" name="shop_name" class="sa-input" value="{{ old('shop_name') }}" required>
                </div>

                <div class="sa-grid sa-grid-3">
                    <div class="sa-form-group">
                        <label class="sa-form-label">Type d'abonnement</label>
                        <select name="subscription_type" class="sa-input sa-select">
                            <option value="trial" {{ old('subscription_type') === 'trial' ? 'selected' : '' }}>Trial</option>
                            <option value="basic" {{ old('subscription_type') === 'basic' ? 'selected' : '' }}>Basic</option>
                            <option value="premium" {{ old('subscription_type') === 'premium' ? 'selected' : '' }}>Premium</option>
                            <option value="enterprise" {{ old('subscription_type') === 'enterprise' ? 'selected' : '' }}>Enterprise</option>
                        </select>
                    </div>
                    <div class="sa-form-group">
                        <label class="sa-form-label">Date d'expiration</label>
                        <input type="date" name="expiry_date" class="sa-input" value="{{ old('expiry_date') }}">
                    </div>
                    <div class="sa-form-group">
                        <label class="sa-form-label">&nbsp;</label>
                        <label style="display:flex;align-items:center;gap:8px;padding:9px 0;font-size:.8125rem;font-weight:500;cursor:pointer">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}> Compte actif
                        </label>
                    </div>
                </div>

                <div class="sa-grid sa-grid-2">
                    <div class="sa-form-group">
                        <label class="sa-form-label">Max Managers</label>
                        <input type="number" name="max_managers" class="sa-input" value="{{ old('max_managers', 1) }}" min="0" max="100">
                    </div>
                    <div class="sa-form-group">
                        <label class="sa-form-label">Max Employés</label>
                        <input type="number" name="max_employees" class="sa-input" value="{{ old('max_employees', 2) }}" min="0" max="1000">
                    </div>
                </div>

                <div style="display:flex;align-items:center;gap:16px;margin-top:8px">
                    <label style="display:flex;align-items:center;gap:8px;font-size:.8125rem;font-weight:500;cursor:pointer">
                        <input type="checkbox" name="send_welcome_email" value="1"> Envoyer un email de bienvenue
                    </label>
                </div>

                <div style="display:flex;gap:12px;margin-top:24px;padding-top:20px;border-top:1px solid var(--sa-border)">
                    <button type="submit" class="sa-btn sa-btn-primary"><i class="fas fa-save"></i> Créer l'administrateur</button>
                    <a href="{{ route('super-admin.admins.index') }}" class="sa-btn sa-btn-outline">Annuler</a>
                </div>
            </form>
        </div>
    </div>
@endsection
