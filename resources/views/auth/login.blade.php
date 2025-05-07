@extends('adminlte::auth.login')

@section('title', 'Connexion')

@section('auth_header', 'Connexion à Order Manager')

@section('css')
<style>
    .login-page {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
    }
    
    .login-card-body {
        border-radius: 10px;
    }
    
    .login-logo img {
        max-height: 100px;
        margin-bottom: 20px;
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
    
    .tabs-auth {
        margin-bottom: 20px;
    }
    
    .tabs-auth .nav-item {
        width: 33.33%;
        text-align: center;
    }
    
    .tabs-auth .nav-link {
        border-radius: 0.25rem;
        padding: 10px;
        color: #6c757d;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
    }
    
    .tabs-auth .nav-link.active {
        color: #fff;
        background-color: #4e73df;
        border-color: #4e73df;
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

    <ul class="nav nav-tabs tabs-auth" id="roleTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="admin-tab" data-toggle="tab" href="#admin" role="tab">Admin</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="manager-tab" data-toggle="tab" href="#manager" role="tab">Manager</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="employee-tab" data-toggle="tab" href="#employee" role="tab">Employé</a>
        </li>
    </ul>
    
    <div class="tab-content" id="roleTabContent">
        <div class="tab-pane fade show active" id="admin" role="tabpanel">
            <form action="{{ route('login.submit') }}" method="post">
                @csrf
                <input type="hidden" name="user_type" value="admin">
                
                <div class="input-group mb-3">
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                           placeholder="Email" value="{{ old('email') }}" required autofocus>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-envelope"></span>
                        </div>
                    </div>
                    @error('email')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                
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
                
                <div class="row">
                    <div class="col-8">
                        <div class="icheck-primary">
                            <input type="checkbox" id="admin-remember" name="remember">
                            <label for="admin-remember">
                                Se souvenir de moi
                            </label>
                        </div>
                    </div>
                    <div class="col-4">
                        <button type="submit" class="btn btn-primary btn-block">Connexion</button>
                    </div>
                </div>
            </form>
            
            <p class="mt-3 mb-1 text-center">
                <a href="{{ route('register') }}">Créer un compte</a>
            </p>
        </div>
        
        <div class="tab-pane fade" id="manager" role="tabpanel">
            <form action="{{ route('login.submit') }}" method="post">
                @csrf
                <input type="hidden" name="user_type" value="manager">
                
                <div class="input-group mb-3">
                    <input type="email" name="email" class="form-control" 
                           placeholder="Email" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-envelope"></span>
                        </div>
                    </div>
                </div>
                
                <div class="input-group mb-3">
                    <input type="password" name="password" class="form-control" 
                           placeholder="Mot de passe" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-8">
                        <div class="icheck-primary">
                            <input type="checkbox" id="manager-remember" name="remember">
                            <label for="manager-remember">
                                Se souvenir de moi
                            </label>
                        </div>
                    </div>
                    <div class="col-4">
                        <button type="submit" class="btn btn-primary btn-block">Connexion</button>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="tab-pane fade" id="employee" role="tabpanel">
            <form action="{{ route('login.submit') }}" method="post">
                @csrf
                <input type="hidden" name="user_type" value="employee">
                
                <div class="input-group mb-3">
                    <input type="email" name="email" class="form-control" 
                           placeholder="Email" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-envelope"></span>
                        </div>
                    </div>
                </div>
                
                <div class="input-group mb-3">
                    <input type="password" name="password" class="form-control" 
                           placeholder="Mot de passe" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-8">
                        <div class="icheck-primary">
                            <input type="checkbox" id="employee-remember" name="remember">
                            <label for="employee-remember">
                                Se souvenir de moi
                            </label>
                        </div>
                    </div>
                    <div class="col-4">
                        <button type="submit" class="btn btn-primary btn-block">Connexion</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Afficher l'animation de chargement lors de la soumission du formulaire
        $('form').on('submit', function() {
            // Désactiver le bouton de soumission pour éviter les soumissions multiples
            $(this).find('button[type="submit"]').prop('disabled', true);
            
            // Ajouter une classe pour afficher un indicateur de chargement
            $(this).find('button[type="submit"]').html('<i class="fas fa-spinner fa-spin"></i> Connexion...');
        });
        
        // Transition en douceur entre les onglets
        $('.nav-tabs a').on('click', function (e) {
            e.preventDefault();
            $(this).tab('show');
        });
        
        // Récupérer l'onglet actif depuis l'URL ou le localStorage
        var activeTab = localStorage.getItem('activeLoginTab');
        if (location.hash) {
            $('a[href="' + location.hash + '"]').tab('show');
        } else if (activeTab) {
            $('a[href="' + activeTab + '"]').tab('show');
        }
        
        // Stocker l'onglet actif dans localStorage
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            localStorage.setItem('activeLoginTab', $(e.target).attr('href'));
        });
    });
</script>
@stop