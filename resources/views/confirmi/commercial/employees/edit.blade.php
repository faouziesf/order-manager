@extends('confirmi.layouts.app')
@section('title', 'Modifier employé')
@section('page-title', 'Modifier : ' . $employee->name)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="content-card">
            <div class="card-header-custom">
                <h6><i class="fas fa-user-edit me-2 text-primary"></i>Modifier l'employé</h6>
                <a href="{{ route('confirmi.commercial.employees.index') }}" class="btn btn-sm btn-outline-royal">
                    <i class="fas fa-arrow-left me-1"></i>Retour
                </a>
            </div>
            <div class="p-4">
                @if(session('success'))
                    <div class="alert alert-success mb-3"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger mb-3"><i class="fas fa-exclamation-circle me-2"></i>{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('confirmi.commercial.employees.update', $employee) }}">
                    @csrf @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:.8rem;">Nom complet <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $employee->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:.8rem;">Téléphone</label>
                            <input type="text" name="phone" class="form-control"
                                   value="{{ old('phone', $employee->phone) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size:.8rem;">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $employee->email) }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <hr><p class="text-muted" style="font-size:.78rem;">Laisser vide pour ne pas changer le mot de passe.</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:.8rem;">Nouveau mot de passe</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                   placeholder="Min. 6 caractères">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:.8rem;">Confirmer nouveau mot de passe</label>
                            <input type="password" name="password_confirmation" class="form-control"
                                   placeholder="Répéter le mot de passe">
                        </div>
                        <div class="col-12 mt-2 d-flex gap-2 align-items-center">
                            <button type="submit" class="btn btn-royal">
                                <i class="fas fa-save me-2"></i>Enregistrer
                            </button>
                            <span class="text-muted" style="font-size:.78rem;">
                                Statut actuel :
                                <strong class="{{ $employee->is_active ? 'text-success' : 'text-danger' }}">
                                    {{ $employee->is_active ? 'Actif' : 'Inactif' }}
                                </strong>
                                — changer depuis la liste
                            </span>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
