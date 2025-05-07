@extends('adminlte::auth.register')

@section('title', 'Inscription')

@section('auth_header', 'Créer un compte')

@section('css')
<style>
    .register-page {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
    }
    
    .register-card-body {
        border-radius: 10px;
    }
    
    .input-group-text {
        background-color: #f8f9fc;
    }
    
    .btn-primary {
        background-color: #4e73df;
        border-color: #4e73df;
    }
    
    .btn-primary:hover {
        background-color: #2e59d9;
        border-color: #2e59d9;
    }
    
    .register-card-body .card-header {
        border-bottom: 1px solid #e3e6f0;
        margin-bottom: 20px;
        padding-bottom: 15px;
    }
</style>
@stop

@section('auth_body')
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('register.submit') }}" method="post">
        @csrf
        
        <div class="row">
            <div class="col-md-6">
                <div class="input-group mb-3">
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                           placeholder="Nom complet" value="{{ old('name') }}" required autofocus>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-user"></span>
                        </div>
                    </div>
                    @error('name')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="input-group mb-3">
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                           placeholder="Email" value="{{ old('email') }}" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-envelope"></span>
                        </div>
                    </div>
                    @error('email')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="input-group mb-3">
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" 
                           placeholder="Mot de passe" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                    @error('password')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="input-group mb-3">
                    <input type="password" name="password_confirmation" class="form-control" 
                           placeholder="Confirmer mot de passe" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="input-group mb-3">
                    <input type="text" name="shop_name" class="form-control @error('shop_name') is-invalid @enderror" 
                           placeholder="Nom de la boutique" value="{{ old('shop_name') }}" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-store"></span>
                        </div>
                    </div>
                    @error('shop_name')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="input-group mb-3">
                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                           placeholder="Téléphone (optionnel)" value="{{ old('phone') }}">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-phone"></span>
                        </div>
                    </div>
                    @error('phone')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <button type="submit" class="btn btn-primary btn-block">S'inscrire</button>
            </div>
        </div>
    </form>
    
    <p class="mt-3 mb-1 text-center">
        <a href="{{ route('login') }}">J'ai déjà un compte</a>
    </p>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Afficher l'animation de chargement lors de la soumission du formulaire
        $('form').on('submit', function() {
            // Désactiver le bouton de soumission pour éviter les soumissions multiples
            $(this).find('button[type="submit"]').prop('disabled', true);
            
            // Ajouter une classe pour afficher un indicateur de chargement
            $(this).find('button[type="submit"]').html('<i class="fas fa-spinner fa-spin"></i> Inscription en cours...');
        });
        
        // Vérification de la conformité du mot de passe
        $('input[name="password"], input[name="password_confirmation"]').on('keyup', function() {
            var password = $('input[name="password"]').val();
            var confirmPassword = $('input[name="password_confirmation"]').val();
            
            if (password != '' && confirmPassword != '') {
                if (password != confirmPassword) {
                    $('input[name="password_confirmation"]').addClass('is-invalid');
                    $('input[name="password_confirmation"]').removeClass('is-valid');
                } else {
                    $('input[name="password_confirmation"]').removeClass('is-invalid');
                    $('input[name="password_confirmation"]').addClass('is-valid');
                }
            }
        });
    });
</script>
@stop