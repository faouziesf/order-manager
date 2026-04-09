@extends('layouts.super-admin')

@section('title', 'Paramètres du système')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Paramètres du système</h1>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h6 class="card-title">Configuration générale</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('super-admin.settings.update') }}" method="POST">
                @csrf
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="trial_period">Période d'essai (jours) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('trial_period') is-invalid @enderror" id="trial_period" name="trial_period" value="{{ old('trial_period', $settings['trial_period'] ?? 3) }}" min="0" required>
                            <small class="form-text text-muted">Nombre de jours d'essai pour les nouveaux administrateurs (0 = pas de période d'essai)</small>
                            @error('trial_period')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="allow_registration" name="allow_registration" value="1" {{ old('allow_registration', $settings['allow_registration'] ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="allow_registration">
                        Autoriser l'inscription publique
                    </label>
                    <small class="form-text text-muted d-block">Si activé, les nouveaux utilisateurs peuvent s'inscrire et recevoir un compte administrateur actif. Sinon, les comptes créés seront inactifs jusqu'à votre approbation.</small>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-primary">Enregistrer les paramètres</button>
                </div>
            </form>
        </div>
    </div>
@endsection