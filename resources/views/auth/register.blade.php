<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Order Manager</title>
    
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
            padding: 2rem 1rem;
        }

        .register-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 800px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .register-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 2.5rem 2rem;
            text-align: center;
            position: relative;
        }

        .register-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }

        .register-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: inline-block;
            position: relative;
            z-index: 1;
        }

        .register-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }

        .register-subtitle {
            opacity: 0.9;
            font-size: 1.1rem;
            font-weight: 400;
            position: relative;
            z-index: 1;
        }

        .register-body {
            padding: 3rem 2rem;
        }

        .alert {
            border: none;
            border-radius: 12px;
            padding: 1.25rem;
            margin-bottom: 2rem;
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

        .form-floating {
            margin-bottom: 1.5rem;
        }

        .form-floating .form-control {
            border: 2px solid var(--gray-200);
            border-radius: 12px;
            padding: 1rem 0.75rem 0.375rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: var(--gray-50);
            min-height: 58px;
        }

        .form-floating .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.15);
            background-color: white;
        }

        .form-floating .form-control.is-valid {
            border-color: var(--success-color);
            background-color: rgba(16, 185, 129, 0.05);
        }

        .form-floating .form-control.is-invalid {
            border-color: var(--error-color);
            background-color: rgba(239, 68, 68, 0.05);
        }

        .form-floating label {
            color: var(--gray-500);
            font-weight: 500;
            padding-left: 0.75rem;
        }

        .form-floating .form-control:focus ~ label,
        .form-floating .form-control:not(:placeholder-shown) ~ label {
            color: var(--primary-color);
        }

        .password-strength {
            margin-top: 0.5rem;
            padding: 0.75rem;
            border-radius: 8px;
            background-color: var(--gray-50);
            border: 1px solid var(--gray-200);
        }

        .strength-bar {
            height: 4px;
            border-radius: 2px;
            background-color: var(--gray-200);
            margin: 0.5rem 0;
            overflow: hidden;
        }

        .strength-fill {
            height: 100%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .strength-weak { background-color: var(--error-color); width: 25%; }
        .strength-fair { background-color: var(--warning-color); width: 50%; }
        .strength-good { background-color: #3b82f6; width: 75%; }
        .strength-strong { background-color: var(--success-color); width: 100%; }

        .requirements {
            font-size: 0.875rem;
            color: var(--gray-600);
        }

        .requirement {
            display: flex;
            align-items: center;
            margin: 0.25rem 0;
        }

        .requirement i {
            margin-right: 0.5rem;
            width: 1rem;
            color: var(--gray-400);
        }

        .requirement.met i {
            color: var(--success-color);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            border: none;
            border-radius: 12px;
            padding: 1rem 2rem;
            font-weight: 600;
            font-size: 1.1rem;
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

        .login-link {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--gray-200);
        }

        .login-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .login-link a:hover {
            color: var(--primary-dark);
        }

        .invalid-feedback {
            color: var(--error-color);
            font-weight: 500;
            margin-top: 0.375rem;
            font-size: 0.875rem;
        }

        .valid-feedback {
            color: var(--success-color);
            font-weight: 500;
            margin-top: 0.375rem;
            font-size: 0.875rem;
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

        .register-container {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .register-container {
                margin: 1rem;
                border-radius: 16px;
            }
            
            .register-header {
                padding: 2rem 1.5rem;
            }
            
            .register-body {
                padding: 2rem 1.5rem;
            }
            
            .register-icon {
                font-size: 2.5rem;
            }
            
            .register-title {
                font-size: 1.75rem;
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
    <div class="register-container">
        <div class="register-header">
            <div class="register-icon">
                <i class="fas fa-user-plus"></i>
            </div>
            <h1 class="register-title">Créer un compte</h1>
            <p class="register-subtitle">Rejoignez Order Manager et commencez votre essai gratuit</p>
        </div>
        
        <div class="register-body">
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
            
            <form action="{{ route('register.submit') }}" method="POST" id="register-form">
                @csrf
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   placeholder="Nom complet" 
                                   value="{{ old('name') }}" 
                                   required>
                            <label for="name">
                                <i class="fas fa-user me-2"></i>Nom complet
                            </label>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   placeholder="admin@example.com" 
                                   value="{{ old('email') }}" 
                                   required>
                            <label for="email">
                                <i class="fas fa-envelope me-2"></i>Adresse e-mail
                            </label>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   placeholder="••••••••" 
                                   required>
                            <label for="password">
                                <i class="fas fa-lock me-2"></i>Mot de passe
                            </label>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Indicateur de force du mot de passe -->
                        <div class="password-strength" id="password-strength" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted">Force du mot de passe:</small>
                                <small class="strength-text">Faible</small>
                            </div>
                            <div class="strength-bar">
                                <div class="strength-fill" id="strength-fill"></div>
                            </div>
                            <div class="requirements">
                                <div class="requirement" id="req-length">
                                    <i class="fas fa-times"></i>
                                    <span>Au moins 8 caractères</span>
                                </div>
                                <div class="requirement" id="req-uppercase">
                                    <i class="fas fa-times"></i>
                                    <span>Une lettre majuscule</span>
                                </div>
                                <div class="requirement" id="req-lowercase">
                                    <i class="fas fa-times"></i>
                                    <span>Une lettre minuscule</span>
                                </div>
                                <div class="requirement" id="req-number">
                                    <i class="fas fa-times"></i>
                                    <span>Un chiffre</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="password" 
                                   class="form-control @error('password_confirmation') is-invalid @enderror" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   placeholder="••••••••" 
                                   required>
                            <label for="password_confirmation">
                                <i class="fas fa-lock me-2"></i>Confirmer le mot de passe
                            </label>
                            @error('password_confirmation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" 
                                   class="form-control @error('shop_name') is-invalid @enderror" 
                                   id="shop_name" 
                                   name="shop_name" 
                                   placeholder="Nom de la boutique" 
                                   value="{{ old('shop_name') }}" 
                                   required>
                            <label for="shop_name">
                                <i class="fas fa-store me-2"></i>Nom de la boutique
                            </label>
                            @error('shop_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="tel" 
                                   class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" 
                                   name="phone" 
                                   placeholder="+216 XX XXX XXX" 
                                   value="{{ old('phone') }}">
                            <label for="phone">
                                <i class="fas fa-phone me-2"></i>Téléphone (optionnel)
                            </label>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg" id="submit-btn">
                        <i class="fas fa-user-plus me-2"></i>
                        Créer mon compte
                    </button>
                </div>
            </form>
            
            <!-- Lien de connexion -->
            <div class="login-link">
                <a href="{{ route('login') }}">
                    <i class="fas fa-sign-in-alt me-1"></i>
                    Vous avez déjà un compte ? Connectez-vous
                </a>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('register-form');
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('password_confirmation');
            const strengthIndicator = document.getElementById('password-strength');
            const strengthFill = document.getElementById('strength-fill');
            const strengthText = document.querySelector('.strength-text');
            const submitBtn = document.getElementById('submit-btn');
            
            // Gestion de la force du mot de passe
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                
                if (password.length > 0) {
                    strengthIndicator.style.display = 'block';
                    checkPasswordStrength(password);
                } else {
                    strengthIndicator.style.display = 'none';
                }
            });
            
            // Vérification de la confirmation du mot de passe
            confirmPasswordInput.addEventListener('input', function() {
                const password = passwordInput.value;
                const confirm = this.value;
                
                if (confirm.length > 0) {
                    if (password === confirm) {
                        this.classList.remove('is-invalid');
                        this.classList.add('is-valid');
                    } else {
                        this.classList.remove('is-valid');
                        this.classList.add('is-invalid');
                    }
                }
            });
            
            // Validation en temps réel pour tous les champs
            const inputs = form.querySelectorAll('.form-control');
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
            
            // Soumission du formulaire
            form.addEventListener('submit', function(e) {
                let isValid = true;
                
                // Valider tous les champs
                inputs.forEach(input => {
                    if (!validateField(input)) {
                        isValid = false;
                    }
                });
                
                // Vérifier la correspondance des mots de passe
                if (passwordInput.value !== confirmPasswordInput.value) {
                    confirmPasswordInput.classList.add('is-invalid');
                    isValid = false;
                }
                
                if (isValid) {
                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;
                } else {
                    e.preventDefault();
                }
            });
            
            function checkPasswordStrength(password) {
                let score = 0;
                const requirements = {
                    length: password.length >= 8,
                    uppercase: /[A-Z]/.test(password),
                    lowercase: /[a-z]/.test(password),
                    number: /\d/.test(password)
                };
                
                // Mettre à jour les indicateurs visuels
                Object.keys(requirements).forEach(req => {
                    const element = document.getElementById(`req-${req}`);
                    const icon = element.querySelector('i');
                    
                    if (requirements[req]) {
                        element.classList.add('met');
                        icon.className = 'fas fa-check';
                        score++;
                    } else {
                        element.classList.remove('met');
                        icon.className = 'fas fa-times';
                    }
                });
                
                // Mettre à jour la barre de force
                strengthFill.className = 'strength-fill';
                if (score === 1) {
                    strengthFill.classList.add('strength-weak');
                    strengthText.textContent = 'Faible';
                } else if (score === 2) {
                    strengthFill.classList.add('strength-fair');
                    strengthText.textContent = 'Passable';
                } else if (score === 3) {
                    strengthFill.classList.add('strength-good');
                    strengthText.textContent = 'Bon';
                } else if (score === 4) {
                    strengthFill.classList.add('strength-strong');
                    strengthText.textContent = 'Fort';
                }
                
                return score;
            }
            
            function validateField(field) {
                const value = field.value.trim();
                const name = field.name;
                let isValid = true;
                
                // Reset validation state
                field.classList.remove('is-invalid', 'is-valid');
                
                if (field.hasAttribute('required') && value === '') {
                    field.classList.add('is-invalid');
                    return false;
                }
                
                if (value === '') {
                    return true; // Champ optionnel vide
                }
                
                switch (name) {
                    case 'email':
                        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        isValid = emailRegex.test(value);
                        break;
                        
                    case 'password':
                        isValid = value.length >= 8 && 
                                 /[A-Z]/.test(value) && 
                                 /[a-z]/.test(value) && 
                                 /\d/.test(value);
                        break;
                        
                    case 'password_confirmation':
                        isValid = value === passwordInput.value;
                        break;
                        
                    case 'name':
                        isValid = value.length >= 2;
                        break;
                        
                    case 'shop_name':
                        isValid = value.length >= 2;
                        break;
                        
                    case 'phone':
                        if (value) { // Seulement si rempli
                            const phoneRegex = /^[\+]?[0-9\s\-\(\)]{8,}$/;
                            isValid = phoneRegex.test(value);
                        }
                        break;
                }
                
                if (isValid) {
                    field.classList.add('is-valid');
                } else {
                    field.classList.add('is-invalid');
                }
                
                return isValid;
            }
            
            // Focus automatique sur le premier champ
            document.getElementById('name').focus();
            
            // Animation des labels flottants
            inputs.forEach(input => {
                // Vérifier si le champ a une valeur au chargement
                if (input.value) {
                    input.dispatchEvent(new Event('input'));
                }
            });
            
            // Formatage automatique du téléphone
            const phoneInput = document.getElementById('phone');
            phoneInput.addEventListener('input', function() {
                let value = this.value.replace(/\D/g, '');
                if (value.startsWith('216')) {
                    value = '+' + value;
                } else if (value.length > 0 && !value.startsWith('+')) {
                    value = '+216' + value;
                }
                
                // Formatage avec espaces
                if (value.length > 4) {
                    value = value.slice(0, 4) + ' ' + value.slice(4);
                }
                if (value.length > 7) {
                    value = value.slice(0, 7) + ' ' + value.slice(7);
                }
                if (value.length > 11) {
                    value = value.slice(0, 11) + ' ' + value.slice(11);
                }
                
                this.value = value;
            });
        });
    </script>
</body>
</html>