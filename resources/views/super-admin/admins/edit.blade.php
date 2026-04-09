@extends('layouts.super-admin')

@section('title', 'Modifier - ' . $admin->name)
@section('page-title', 'Modifier l\'administrateur')

@section('content')
    <div style="max-width:720px">
        <div class="sa-card">
            <div class="sa-card-header">
                <h3 class="sa-card-title"><i class="fas fa-edit" style="color:var(--sa-primary);margin-right:8px"></i>{{ $admin->name }}</h3>
                <a href="{{ route('super-admin.admins.index') }}" class="sa-btn sa-btn-outline sa-btn-sm"><i class="fas fa-arrow-left"></i> Retour</a>
            </div>

            @if($errors->any())
                <div class="sa-alert sa-alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
                </div>
            @endif

            <form method="POST" action="{{ route('super-admin.admins.update', $admin) }}">
                @csrf @method('PUT')

                <div class="sa-grid sa-grid-2">
                    <div class="sa-form-group">
                        <label class="sa-form-label">Nom complet *</label>
                        <input type="text" name="name" class="sa-input" value="{{ old('name', $admin->name) }}" required>
                    </div>
                    <div class="sa-form-group">
                        <label class="sa-form-label">Email *</label>
                        <input type="email" name="email" class="sa-input" value="{{ old('email', $admin->email) }}" required>
                    </div>
                </div>

                <div class="sa-grid sa-grid-2">
                    <div class="sa-form-group">
                        <label class="sa-form-label">Nouveau mot de passe</label>
                        <input type="password" name="password" class="sa-input" placeholder="Laisser vide pour garder l'actuel" minlength="8">
                    </div>
                    <div class="sa-form-group">
                        <label class="sa-form-label">Téléphone</label>
                        <input type="text" name="phone" class="sa-input" value="{{ old('phone', $admin->phone) }}">
                    </div>
                </div>

                <div class="sa-form-group">
                    <label class="sa-form-label">Nom de la boutique *</label>
                    <input type="text" name="shop_name" class="sa-input" value="{{ old('shop_name', $admin->shop_name) }}" required>
                </div>

                <div class="sa-grid sa-grid-3">
                    <div class="sa-form-group">
                        <label class="sa-form-label">Type d'abonnement</label>
                        <select name="subscription_type" class="sa-input sa-select">
                            @foreach(['trial','basic','premium','enterprise'] as $type)
                                <option value="{{ $type }}" {{ old('subscription_type', $admin->subscription_type) === $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sa-form-group">
                        <label class="sa-form-label">Date d'expiration</label>
                        <input type="date" name="expiry_date" class="sa-input" value="{{ old('expiry_date', $admin->expiry_date?->format('Y-m-d')) }}">
                    </div>
                    <div class="sa-form-group">
                        <label class="sa-form-label">&nbsp;</label>
                        <label style="display:flex;align-items:center;gap:8px;padding:9px 0;font-size:.8125rem;font-weight:500;cursor:pointer">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $admin->is_active) ? 'checked' : '' }}> Compte actif
                        </label>
                    </div>
                </div>

                <div class="sa-grid sa-grid-2">
                    <div class="sa-form-group">
                        <label class="sa-form-label">Max Managers</label>
                        <input type="number" name="max_managers" class="sa-input" value="{{ old('max_managers', $admin->max_managers) }}" min="0" max="100">
                    </div>
                    <div class="sa-form-group">
                        <label class="sa-form-label">Max Employés</label>
                        <input type="number" name="max_employees" class="sa-input" value="{{ old('max_employees', $admin->max_employees) }}" min="0" max="1000">
                    </div>
                </div>

                <!-- Confirmi Section -->
                <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--sa-border)">
                    <h4 style="font-size:.875rem;font-weight:700;margin-bottom:12px"><i class="fas fa-headset" style="color:var(--sa-primary);margin-right:6px"></i>Confirmi</h4>
                    <div class="sa-grid sa-grid-3">
                        <div>
                            <span class="sa-form-label">Statut:</span>
                            @if($admin->confirmi_status === 'active')
                                <span class="sa-badge sa-badge-success">Actif</span>
                            @elseif($admin->confirmi_status === 'pending')
                                <span class="sa-badge sa-badge-warning">En attente</span>
                            @else
                                <span class="sa-badge sa-badge-muted">Inactif</span>
                            @endif
                        </div>
                        <div>
                            <span class="sa-form-label">Tarif confirmé:</span>
                            <span style="font-size:.875rem;font-weight:600">{{ $admin->confirmi_rate_confirmed ?? '-' }} DH</span>
                        </div>
                        <div>
                            <span class="sa-form-label">Tarif livré:</span>
                            <span style="font-size:.875rem;font-weight:600">{{ $admin->confirmi_rate_delivered ?? '-' }} DH</span>
                        </div>
                    </div>
                </div>

                <div style="display:flex;gap:12px;margin-top:24px;padding-top:20px;border-top:1px solid var(--sa-border)">
                    <button type="submit" class="sa-btn sa-btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
                    <a href="{{ route('super-admin.admins.index') }}" class="sa-btn sa-btn-outline">Annuler</a>
                </div>
            </form>
        </div>

        <!-- Danger Zone -->
        <div class="sa-card" style="margin-top:24px;border-color:var(--sa-danger)">
            <h4 style="font-size:.875rem;font-weight:700;color:var(--sa-danger);margin-bottom:16px"><i class="fas fa-exclamation-triangle" style="margin-right:6px"></i>Zone Dangereuse</h4>
            <div style="display:flex;gap:12px;flex-wrap:wrap">
                <form action="{{ route('super-admin.admins.reset-password', $admin) }}" method="POST" onsubmit="return confirm('Réinitialiser le mot de passe ?')">
                    @csrf
                    <button type="submit" class="sa-btn sa-btn-outline sa-btn-sm" style="border-color:var(--sa-warning);color:var(--sa-warning)"><i class="fas fa-key"></i> Réinitialiser MDP</button>
                </form>
                <form action="{{ route('super-admin.admins.destroy', $admin) }}" method="POST" onsubmit="return confirm('Supprimer définitivement cet administrateur ?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="sa-btn sa-btn-danger sa-btn-sm"><i class="fas fa-trash"></i> Supprimer</button>
                </form>
            </div>
        </div>
    </div>
@endsection
