<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Order Manager</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-dark: #4338ca;
            --success-color: #10b981;
            --error-color: #ef4444;
            --warning-color: #f59e0b;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 480px;
            padding: 0;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .login-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 2.5rem 2rem 2rem;
            text-align: center;
            position: relative;
        }

        .login-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/><circle cx="90" cy="40" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.5;
        }

        .login-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: inline-block;
            position: relative;
            z-index: 1;
        }

        .login-title {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }

        .login-subtitle {
            opacity: 0.9;
            font-size: 1rem;
            font-weight: 400;
            position: relative;
            z-index: 1;
        }

        .login-body {
            padding: 2.5rem 2rem;
        }

        .alert {
            border: none;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .alert-success {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
            border-left: 4px solid var(--success-color);
        }

        .alert-danger {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--error-color);
            border-left: 4px solid var(--error-color);
        }

        .nav-tabs {
            border: none;
            margin-bottom: 2rem;
            background-color: var(--gray-100);
            border-radius: 12px;
            padding: 0.25rem;
        }

        .nav-tabs .nav-link {
            border: none;
            border-radius: 8px;
            color: var(--gray-600);
            font-weight: 500;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
            margin: 0 0.125rem;
        }

        .nav-tabs .nav-link:hover {
            color: var(--primary-color);
            background-color: rgba(79, 70, 229, 0.1);
        }

        .nav-tabs .nav-link.active {
            background-color: var(--primary-color);
            color: white;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }

        .form-floating {
            margin-bottom: 1.25rem;
        }

        .form-floating .form-control {
            border: 2px solid var(--gray-200);
            border-radius: 12px;
            padding: 1rem 0.75rem 0.375rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: var(--gray-50);
        }

        .form-floating .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.15);
            background-color: white;
        }

        .form-floating label {
            color: var(--gray-500);
            font-weight: 500;
        }

        .form-floating .form-control:focus ~ label,
        .form-floating .form-control:not(:placeholder-shown) ~ label {
            color: var(--primary-color);
        }

        .form-check {
            margin-bottom: 1.5rem;
        }

        .form-check-input {
            border-radius: 6px;
            border: 2px solid var(--gray-300);
            width: 1.125rem;
            height: 1.125rem;
        }

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .form-check-label {
            color: var(--gray-600);
            font-weight: 500;
            margin-left: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            border: none;
            border-radius: 12px;
            padding: 0.875rem 2rem;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(79, 70, 229, 0.3);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .register-link {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--gray-200);
        }

        .register-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .register-link a:hover {
            color: var(--primary-dark);
        }

        .invalid-feedback {
            color: var(--error-color);
            font-weight: 500;
            margin-top: 0.375rem;
        }

        .form-control.is-invalid {
            border-color: var(--error-color);
            background-color: rgba(239, 68, 68, 0.05);
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-container {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Responsive */
        @media (max-width: 576px) {
            .login-container {
                margin: 1rem;
                border-radius: 16px;
            }
            
            .login-header {
                padding: 2rem 1.5rem 1.5rem;
            }
            
            .login-body {
                padding: 2rem 1.5rem;
            }
            
            .login-icon {
                font-size: 2.5rem;
            }
            
            .login-title {
                font-size: 1.5rem;
            }
        }

        /* Loading states */
        .btn-primary.loading {
            position: relative;
            color: transparent;
        }

        .btn-primary.loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid transparent;
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="login-icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h1 class="login-title">Connexion</h1>
            <p class="login-subtitle">Accédez à votre espace Order Manager</p>
        </div>
        
        <div class="login-body">
            <!-- Messages Flash -->
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                </div>
            @endif

            @if(session('expired_reason'))
                <div class="alert alert-danger">
                    <i class="fas fa-clock me-2"></i>
                    @switch(session('expired_reason'))
                        @case('expired')
                            Votre période d'essai a expiré. Contactez l'administrateur.
                            @break
                        @case('inactive')
                            Votre compte a été désactivé. Contactez l'administrateur.
                            @break
                        @case('admin_expired')
                            Le compte administrateur principal a expiré.
                            @break
                        @case('admin_inactive')
                            Le compte administrateur principal a été désactivé.
                            @break
                        @default
                            Votre compte n'est plus accessible. Contactez l'administrateur.
                    @endswitch
                </div>
            @endif
            
            <!-- Onglets de type d'utilisateur -->
            <ul class="nav nav-tabs" id="roleTab" role="tablist">
                <li class="nav-item flex-fill" role="presentation">
                    <button class="nav-link active w-100" id="admin-tab" data-bs-toggle="tab" data-bs-target="#admin" type="button" role="tab">
                        <i class="fas fa-crown me-1"></i> Admin
                    </button>
                </li>
                <li class="nav-item flex-fill" role="presentation">
                    <button class="nav-link w-100" id="manager-tab" data-bs-toggle="tab" data-bs-target="#manager" type="button" role="tab">
                        <i class="fas fa-users-cog me-1"></i> Manager
                    </button>
                </li>
                <li class="nav-item flex-fill" role="presentation">
                    <button class="nav-link w-100" id="employee-tab" data-bs-toggle="tab" data-bs-target="#employee" type="button" role="tab">
                        <i class="fas fa-user me-1"></i> Employé
                    </button>
                </li>
            </ul>
            
            <div class="tab-content" id="roleTabContent">
                <!-- Onglet Admin -->
                <div class="tab-pane fade show active" id="admin" role="tabpanel">
                    <form action="{{ route('login.submit') }}" method="POST" id="admin-form">
                        @csrf
                        <input type="hidden" name="user_type" value="admin">
                        
                        <div class="form-floating">
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="admin-email" name="email" placeholder="admin@example.com" 
                                   value="{{ old('email') }}" required>
                            <label for="admin-email">
                                <i class="fas fa-envelope me-2"></i>Adresse e-mail
                            </label>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-floating">
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="admin-password" name="password" placeholder="••••••••" required>
                            <label for="admin-password">
                                <i class="fas fa-lock me-2"></i>Mot de passe
                            </label>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="admin-remember" name="remember">
                            <label class="form-check-label" for="admin-remember">
                                Se souvenir de moi
                            </label>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Se connecter
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Onglet Manager -->
                <div class="tab-pane fade" id="manager" role="tabpanel">
                    <form action="{{ route('login.submit') }}" method="POST" id="manager-form">
                        @csrf
                        <input type="hidden" name="user_type" value="manager">
                        
                        <div class="form-floating">
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="manager-email" name="email" placeholder="manager@example.com" 
                                   value="{{ old('email') }}" required>
                            <label for="manager-email">
                                <i class="fas fa-envelope me-2"></i>Adresse e-mail
                            </label>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-floating">
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="manager-password" name="password" placeholder="••••••••" required>
                            <label for="manager-password">
                                <i class="fas fa-lock me-2"></i>Mot de passe
                            </label>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="manager-remember" name="remember">
                            <label class="form-check-label" for="manager-remember">
                                Se souvenir de moi
                            </label>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Se connecter
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Onglet Employé -->
                <div class="tab-pane fade" id="employee" role="tabpanel">
                    <form action="{{ route('login.submit') }}" method="POST" id="employee-form">
                        @csrf
                        <input type="hidden" name="user_type" value="employee">
                        
                        <div class="form-floating">
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="employee-email" name="email" placeholder="employee@example.com" 
                                   value="{{ old('email') }}" required>
                            <label for="employee-email">
                                <i class="fas fa-envelope me-2"></i>Adresse e-mail
                            </label>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-floating">
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="employee-password" name="password" placeholder="••••••••" required>
                            <label for="employee-password">
                                <i class="fas fa-lock me-2"></i>Mot de passe
                            </label>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="employee-remember" name="remember">
                            <label class="form-check-label" for="employee-remember">
                                Se souvenir de moi
                            </label>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Se connecter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Lien d'inscription -->
            <div class="register-link">
                <a href="{{ route('register') }}">
                    <i class="fas fa-user-plus me-1"></i>
                    Pas encore de compte ? Inscrivez-vous
                </a>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle avec Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gérer la soumission des formulaires avec animation de chargement
            const forms = document.querySelectorAll('form');
            
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const submitBtn = form.querySelector('button[type="submit"]');
                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;
                });
            });
            
            // Animation des onglets
            const tabs = document.querySelectorAll('[data-bs-toggle="tab"]');
            tabs.forEach(tab => {
                tab.addEventListener('shown.bs.tab', function(e) {
                    const target = document.querySelector(e.target.getAttribute('data-bs-target'));
                    target.style.animation = 'fadeInUp 0.3s ease-out';
                });
            });
            
            // Focus automatique sur le champ email de l'onglet actif
            const activeTab = document.querySelector('.tab-pane.active');
            if (activeTab) {
                const emailInput = activeTab.querySelector('input[type="email"]');
                if (emailInput) {
                    emailInput.focus();
                }
            }
            
            // Validation en temps réel
            const inputs = document.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    validateField(this);
                });
                
                input.addEventListener('input', function() {
                    if (this.classList.contains('is-invalid')) {
                        validateField(this);
                    }
                });
            });
            
            function validateField(field) {
                const value = field.value.trim();
                const type = field.type;
                
                // Reset validation state
                field.classList.remove('is-invalid', 'is-valid');
                
                if (value === '') {
                    return;
                }
                
                let isValid = true;
                
                if (type === 'email') {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    isValid = emailRegex.test(value);
                } else if (type === 'password') {
                    isValid = value.length >= 6;
                }
                
                if (isValid) {
                    field.classList.add('is-valid');
                } else {
                    field.classList.add('is-invalid');
                }
            }
        });
    </script>
</body>
</html>