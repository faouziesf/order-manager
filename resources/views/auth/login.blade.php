<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Order Manager</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body {
            height: 100vh;
            display: flex;
            align-items: center;
            background-color: #f8f9fc;
            font-family: 'Nunito', sans-serif;
        }
        
        .login-container {
            max-width: 400px;
            width: 100%;
            margin: 0 auto;
        }
        
        .card {
            border: none;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        .card-header {
            background-color: #4e73df;
            color: white;
            text-align: center;
            padding: 1.5rem;
            border-bottom: none;
        }
        
        .login-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .card-body {
            padding: 2rem;
        }
        
        .form-floating {
            margin-bottom: 1rem;
        }
        
        .btn-primary {
            background-color: #4e73df;
            border-color: #4e73df;
        }
        
        .btn-primary:hover {
            background-color: #2e59d9;
            border-color: #2e59d9;
        }
        
        .nav-tabs .nav-link {
            color: #4e73df;
        }
        
        .nav-tabs .nav-link.active {
            color: #4e73df;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="card">
                <div class="card-header">
                    <div class="login-icon">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <h4 class="mb-0">Connexion</h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @enderror
                    
                    <ul class="nav nav-tabs mb-3" id="roleTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="admin-tab" data-bs-toggle="tab" data-bs-target="#admin" type="button" role="tab" aria-controls="admin" aria-selected="true">Admin</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="manager-tab" data-bs-toggle="tab" data-bs-target="#manager" type="button" role="tab" aria-controls="manager" aria-selected="false">Manager</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="employee-tab" data-bs-toggle="tab" data-bs-target="#employee" type="button" role="tab" aria-controls="employee" aria-selected="false">Employé</button>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="roleTabContent">
                        <div class="tab-pane fade show active" id="admin" role="tabpanel" aria-labelledby="admin-tab">
                            <form action="{{ route('login.submit') }}" method="POST">
                                @csrf
                                <input type="hidden" name="user_type" value="admin">
                                
                                <div class="form-floating">
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="admin-email" name="email" placeholder="Adresse e-mail" value="{{ old('email') }}" required>
                                    <label for="admin-email">Adresse e-mail</label>
                                    @error('email')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                
                                <div class="form-floating">
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="admin-password" name="password" placeholder="Mot de passe" required>
                                    <label for="admin-password">Mot de passe</label>
                                    @error('password')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="admin-remember" name="remember">
                                    <label class="form-check-label" for="admin-remember">
                                        Se souvenir de moi
                                    </label>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        Connexion
                                    </button>
                                </div>
                                
                                <div class="text-center mt-3">
                                    <a href="{{ route('register') }}" class="text-decoration-none">Pas encore de compte ? Inscrivez-vous</a>
                                </div>
                            </form>
                        </div>
                        
                        <div class="tab-pane fade" id="manager" role="tabpanel" aria-labelledby="manager-tab">
                            <form action="{{ route('login.submit') }}" method="POST">
                                @csrf
                                <input type="hidden" name="user_type" value="manager">
                                
                                <div class="form-floating">
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="manager-email" name="email" placeholder="Adresse e-mail" value="{{ old('email') }}" required>
                                    <label for="manager-email">Adresse e-mail</label>
                                    @error('email')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                
                                <div class="form-floating">
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="manager-password" name="password" placeholder="Mot de passe" required>
                                    <label for="manager-password">Mot de passe</label>
                                    @error('password')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="manager-remember" name="remember">
                                    <label class="form-check-label" for="manager-remember">
                                        Se souvenir de moi
                                    </label>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        Connexion
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <div class="tab-pane fade" id="employee" role="tabpanel" aria-labelledby="employee-tab">
                            <form action="{{ route('login.submit') }}" method="POST">
                                @csrf
                                <input type="hidden" name="user_type" value="employee">
                                
                                <div class="form-floating">
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="employee-email" name="email" placeholder="Adresse e-mail" value="{{ old('email') }}" required>
                                    <label for="employee-email">Adresse e-mail</label>
                                    @error('email')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                
                                <div class="form-floating">
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="employee-password" name="password" placeholder="Mot de passe" required>
                                    <label for="employee-password">Mot de passe</label>
                                    @error('password')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="employee-remember" name="remember">
                                    <label class="form-check-label" for="employee-remember">
                                        Se souvenir de moi
                                    </label>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        Connexion
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>