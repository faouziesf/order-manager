@extends('confirmi.layouts.app')
@section('title', 'Nouvel employé')
@section('page-title', 'Créer un employé')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="content-card">
            <div class="card-header-custom">
                <h6><i class="fas fa-user-plus me-2 text-primary"></i>Nouvel employé Confirmi</h6>
                <a href="{{ route('confirmi.commercial.employees.index') }}" class="btn btn-sm btn-outline-royal">
                    <i class="fas fa-arrow-left me-1"></i>Retour
                </a>
            </div>
            <div class="p-4">
                @if($errors->any())
                    <div class="alert alert-danger mb-3">
                        <i class="fas fa-exclamation-circle me-2"></i>{{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('confirmi.commercial.employees.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:.8rem;">Nom complet <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" placeholder="Prénom Nom" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:.8rem;">Téléphone</label>
                            <input type="text" name="phone" class="form-control"
                                   value="{{ old('phone') }}" placeholder="+216 XX XXX XXX">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size:.8rem;">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" placeholder="employe@exemple.com" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:.8rem;">Mot de passe <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                   placeholder="Min. 6 caractères" required>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:.8rem;">Confirmer le mot de passe <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control"
                                   placeholder="Répéter le mot de passe" required>
                        </div>
                        <div class="col-12 mt-2">
                            <button type="submit" class="btn btn-royal">
                                <i class="fas fa-save me-2"></i>Créer l'employé
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
