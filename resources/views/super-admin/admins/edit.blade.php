@extends('layouts.super-admin')

@section('title', 'Modifier un Administrateur')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Modifier un Administrateur</h1>
        <a href="{{ route('super-admin.admins.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left fa-sm"></i> Retour à la liste
        </a>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h6 class="card-title">Informations de l'Administrateur</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('super-admin.admins.update', $admin) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Nom complet <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $admin->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email">Adresse e-mail <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $admin->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="shop_name">Nom de la boutique <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('shop_name') is-invalid @enderror" id="shop_name" name="shop_name" value="{{ old('shop_name', $admin->shop_name) }}" required>
                            @error('shop_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="phone">Téléphone</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $admin->phone) }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="expiry_date">Date d'expiration</label>
                            <input type="date" class="form-control @error('expiry_date') is-invalid @enderror" id="expiry_date" name="expiry_date" value="{{ old('expiry_date', $admin->expiry_date ? $admin->expiry_date->format('Y-m-d') : '') }}">
                            @error('expiry_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="max_managers">Nombre maximum de managers</label>
                            <input type="number" class="form-control @error('max_managers') is-invalid @enderror" id="max_managers" name="max_managers" value="{{ old('max_managers', $admin->max_managers) }}" min="0">
                            @error('max_managers')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="max_employees">Nombre maximum d'employés</label>
                            <input type="number" class="form-control @error('max_employees') is-invalid @enderror" id="max_employees" name="max_employees" value="{{ old('max_employees', $admin->max_employees) }}" min="0">
                            @error('max_employees')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" {{ old('is_active', $admin->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">
                        Compte actif
                    </label>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="reset" class="btn btn-secondary me-md-2">Réinitialiser</button>
                    <button type="submit" class="btn btn-primary">Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>
@endsection