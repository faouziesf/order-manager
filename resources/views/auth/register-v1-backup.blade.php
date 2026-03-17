<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#8b5cf6">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>Inscription - Order Manager</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4c1d95 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            position: relative;
            overflow-x: hidden;
        }
        body::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(139, 92, 246, 0.2) 0%, transparent 70%);
            top: -150px;
            right: -150px;
            border-radius: 50%;
            animation: float 20s ease-in-out infinite;
        }
        body::after {
            content: '';
            position: absolute;
            width: 350px;
            height: 350px;
            background: radial-gradient(circle, rgba(217, 70, 239, 0.15) 0%, transparent 70%);
            bottom: -100px;
            left: -100px;
            border-radius: 50%;
            animation: float 15s ease-in-out infinite reverse;
        }
        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -30px) scale(1.1); }
            66% { transform: translate(-30px, 30px) scale(0.9); }
        }
        .register-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 850px;
        }
        .register-container {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 32px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(255, 255, 255, 0.1);
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .register-header {
            padding: 2.5rem 2.5rem 2rem;
            text-align: center;
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            position: relative;
            overflow: hidden;
        }
        .register-header::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 30% 40%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                        radial-gradient(circle at 70% 70%, rgba(255, 255, 255, 0.05) 0%, transparent 50%);
        }
        .logo-container {
            width: 70px;
            height: 70px;
            margin: 0 auto 1.25rem;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            border: 2px solid rgba(255, 255, 255, 0.2);
            animation: pulse 3s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.4); }
            50% { transform: scale(1.05); box-shadow: 0 0 0 15px rgba(255, 255, 255, 0); }
        }
        .logo-container i {
            font-size: 2rem;
            color: white;
        }
        .register-title {
            font-size: 1.75rem;
            font-weight: 900;
            color: white;
            margin-bottom: 0.5rem;
            position: relative;
            letter-spacing: -0.5px;
        }
        .register-subtitle {
            font-size: 0.9375rem;
            color: rgba(255, 255, 255, 0.85);
            font-weight: 500;
            position: relative;
        }
        .register-body {
            padding: 2.5rem;
        }
        .alert {
            padding: 1rem 1.25rem;
            border-radius: 16px;
            margin-bottom: 1.5rem;
            font-weight: 500;
            font-size: 0.9375rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: slideDown 0.3s ease-out;
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .alert-success {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            color: #065f46;
            border: 2px solid #10b981;
        }
        .alert-danger {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            color: #991b1b;
            border: 2px solid #ef4444;
        }
        .alert i { font-size: 1.25rem; }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
            margin-bottom: 1.25rem;
        }
        .form-group {
            margin-bottom: 1.25rem;
        }
        .form-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8125rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }
        .form-label i { color: #8b5cf6; font-size: 0.9375rem; }
        .form-control {
            width: 100%;
            padding: 0.9375rem;
            border: 2px solid #e5e7eb;
            border-radius: 14px;
            font-size: 0.9375rem;
            transition: all 0.3s ease;
            background: #f9fafb;
            color: #1f2937;
            font-weight: 500;
        }
        .form-control:focus {
            outline: none;
            border-color: #8b5cf6;
            background: white;
            box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
        }
        .form-control.is-invalid {
            border-color: #ef4444;
            background: #fef2f2;
        }
        .form-control.is-valid {
            border-color: #10b981;
            background: #ecfdf5;
        }
        .invalid-feedback {
            color: #dc2626;
            font-size: 0.75rem;
            margin-top: 0.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }
        .password-strength {
            margin-top: 0.75rem;
            padding: 1rem;
            background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
            border-radius: 12px;
            border: 2px solid #e5e7eb;
        }
        .strength-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
        }
        .strength-header small {
            font-size: 0.75rem;
            color: #6b7280;
            font-weight: 600;
        }
        .strength-text {
            font-size: 0.8125rem;
            font-weight: 700;
        }
        .strength-bar {
            height: 5px;
            border-radius: 3px;
            background: #e5e7eb;
            overflow: hidden;
            margin-bottom: 0.75rem;
        }
        .strength-fill {
            height: 100%;
            transition: all 0.3s ease;
            border-radius: 3px;
        }
        .strength-weak { background: linear-gradient(90deg, #ef4444, #dc2626); width: 25%; }
        .strength-fair { background: linear-gradient(90deg, #f59e0b, #d97706); width: 50%; }
        .strength-good { background: linear-gradient(90deg, #3b82f6, #2563eb); width: 75%; }
        .strength-strong { background: linear-gradient(90deg, #10b981, #059669); width: 100%; }
        .requirements {
            display: grid;
            gap: 0.375rem;
        }
        .requirement {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.75rem;
            color: #6b7280;
            font-weight: 500;
        }
        .requirement i {
            font-size: 0.875rem;
            color: #9ca3af;
        }
        .requirement.met {
            color: #059669;
        }
        .requirement.met i {
            color: #10b981;
        }
        .btn-register {
            width: 100%;
            padding: 1.125rem 2rem;
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            border: none;
            border-radius: 16px;
            font-size: 1.0625rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(139, 92, 246, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            position: relative;
            overflow: hidden;
            margin-top: 2rem;
        }
        .btn-register::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }
        .btn-register:hover::before { left: 100%; }
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(139, 92, 246, 0.5);
        }
        .btn-register:active { transform: translateY(0); }
        .btn-register.loading { pointer-events: none; opacity: 0.7; }
        .btn-register .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }
        .btn-register.loading .spinner { display: block; }
        .btn-register.loading .btn-text { display: none; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .login-link {
            text-align: center;
            padding: 2rem 0 0.5rem;
            border-top: 1px solid #f3f4f6;
        }
        .login-link p {
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 0.75rem;
        }
        .login-link a {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #8b5cf6;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.9375rem;
            transition: all 0.3s ease;
            padding: 0.5rem 1rem;
            border-radius: 12px;
        }
        .login-link a:hover {
            background: #f5f3ff;
            color: #7c3aed;
            transform: translateX(-4px);
        }
        .back-link {
            text-align: center;
            margin-top: 1.5rem;
        }
        .back-link a {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .back-link a:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(-4px);
        }
        .promo-banner {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 2px solid #f59e0b;
            border-radius: 16px;
            padding: 1.25rem;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }
        .promo-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.5), transparent);
            animation: shimmer 3s ease-in-out infinite;
        }
        @keyframes shimmer {
            0% { left: -100%; }
            100% { left: 100%; }
        }
        .promo-content {
            display: flex;
            align-items: center;
            gap: 1rem;
            position: relative;
        }
        .promo-icon {
            width: 50px;
            height: 50px;
            background: rgba(245, 158, 11, 0.2);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .promo-icon i {
            font-size: 1.5rem;
            color: #d97706;
            animation: bounce 2s ease-in-out infinite;
        }
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }
        .promo-text h3 {
            font-size: 1rem;
            font-weight: 800;
            color: #92400e;
            margin-bottom: 0.25rem;
        }
        .promo-text p {
            font-size: 0.8125rem;
            color: #78350f;
            font-weight: 600;
            margin: 0;
        }
        @media (max-width: 768px) {
            body { padding: 1rem 0.5rem; }
            .register-container { border-radius: 24px; }
            .register-header { padding: 2rem 1.5rem 1.5rem; }
            .logo-container { width: 60px; height: 60px; margin-bottom: 1rem; }
            .logo-container i { font-size: 1.75rem; }
            .register-title { font-size: 1.5rem; }
            .register-subtitle { font-size: 0.875rem; }
            .register-body { padding: 2rem 1.5rem; }
            .form-row { grid-template-columns: 1fr; gap: 0; }
            .form-control { padding: 0.875rem; font-size: 0.9375rem; }
            .btn-register { padding: 1rem 1.5rem; font-size: 1rem; }
            .promo-content { flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body>
    <div class="register-wrapper">
        <div class="register-container">
            <div class="register-header">
                <div class="logo-container">
                    <i class="fas fa-rocket"></i>
                </div>
                <h1 class="register-title">Créer votre compte</h1>
                <p class="register-subtitle">Commencez votre essai gratuit de 14 jours dès maintenant</p>
            </div>
            <div class="register-body">
                @if(session('success'))
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif
                <div class="promo-banner">
                    <div class="promo-content">
                        <div class="promo-icon">
                            <i class="fas fa-gift"></i>
                        </div>
                        <div class="promo-text">
                            <h3>🎉 Essai professionnel gratuit</h3>
                            <p>Accès complet pendant 14 jours • Aucune carte requise • Support prioritaire</p>
                        </div>
                    </div>
                </div>
                <form action="{{ route('register.submit') }}" method="POST" id="registerForm">
                    @csrf
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-user"></i>
                                Nom complet
                            </label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   name="name" placeholder="Votre nom" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-envelope"></i>
                                Adresse e-mail
                            </label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   name="email" placeholder="email@exemple.com" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-lock"></i>
                                Mot de passe
                            </label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                   name="password" placeholder="••••••••" id="password" required>
                            @error('password')
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                            <div class="password-strength" id="passwordStrength" style="display: none;">
                                <div class="strength-header">
                                    <small>Force du mot de passe:</small>
                                    <small class="strength-text" id="strengthText">Faible</small>
                                </div>
                                <div class="strength-bar">
                                    <div class="strength-fill" id="strengthFill"></div>
                                </div>
                                <div class="requirements">
                                    <div class="requirement" id="reqLength">
                                        <i class="fas fa-times"></i>
                                        <span>Au moins 8 caractères</span>
                                    </div>
                                    <div class="requirement" id="reqUpper">
                                        <i class="fas fa-times"></i>
                                        <span>Une lettre majuscule</span>
                                    </div>
                                    <div class="requirement" id="reqLower">
                                        <i class="fas fa-times"></i>
                                        <span>Une lettre minuscule</span>
                                    </div>
                                    <div class="requirement" id="reqNumber">
                                        <i class="fas fa-times"></i>
                                        <span>Un chiffre</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-check-double"></i>
                                Confirmer le mot de passe
                            </label>
                            <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror"
                                   name="password_confirmation" placeholder="••••••••" id="passwordConfirm" required>
                            @error('password_confirmation')
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-store"></i>
                                Nom de la boutique
                            </label>
                            <input type="text" class="form-control @error('shop_name') is-invalid @enderror"
                                   name="shop_name" placeholder="Ma Boutique" value="{{ old('shop_name') }}" required>
                            @error('shop_name')
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-phone"></i>
                                Téléphone <small style="color: #9ca3af; font-weight: 500;">(optionnel)</small>
                            </label>
                            <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                   name="phone" placeholder="+216 XX XXX XXX" value="{{ old('phone') }}">
                            @error('phone')
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                    <button type="submit" class="btn-register" id="registerBtn">
                        <div class="spinner"></div>
                        <span class="btn-text">
                            <i class="fas fa-rocket"></i>
                            Démarrer mon essai gratuit
                        </span>
                    </button>
                </form>
                <div class="login-link">
                    <p>Vous avez déjà un compte ?</p>
                    <a href="{{ route('login') }}">
                        <i class="fas fa-sign-in-alt"></i>
                        Se connecter
                    </a>
                </div>
            </div>
        </div>
        <div class="back-link">
            <a href="{{ route('confirmi.home') }}">
                <i class="fas fa-arrow-left"></i>
                Retour à l'accueil
            </a>
        </div>
    </div>
    <script>
        const passwordInput = document.getElementById('password');
        const confirmInput = document.getElementById('passwordConfirm');
        const strengthBox = document.getElementById('passwordStrength');
        const strengthFill = document.getElementById('strengthFill');
        const strengthText = document.getElementById('strengthText');
        
        passwordInput.addEventListener('input', function() {
            const pwd = this.value;
            if (pwd.length > 0) {
                strengthBox.style.display = 'block';
                checkStrength(pwd);
            } else {
                strengthBox.style.display = 'none';
            }
        });
        
        function checkStrength(pwd) {
            const reqs = {
                length: pwd.length >= 8,
                upper: /[A-Z]/.test(pwd),
                lower: /[a-z]/.test(pwd),
                number: /\d/.test(pwd)
            };
            
            ['length', 'upper', 'lower', 'number'].forEach(req => {
                const el = document.getElementById(`req${req.charAt(0).toUpperCase() + req.slice(1)}`);
                const icon = el.querySelector('i');
                if (reqs[req]) {
                    el.classList.add('met');
                    icon.className = 'fas fa-check';
                } else {
                    el.classList.remove('met');
                    icon.className = 'fas fa-times';
                }
            });
            
            const score = Object.values(reqs).filter(Boolean).length;
            strengthFill.className = 'strength-fill';
            if (score === 1) {
                strengthFill.classList.add('strength-weak');
                strengthText.textContent = 'Faible';
            } else if (score === 2) {
                strengthFill.classList.add('strength-fair');
                strengthText.textContent = 'Moyen';
            } else if (score === 3) {
                strengthFill.classList.add('strength-good');
                strengthText.textContent = 'Bon';
            } else if (score === 4) {
                strengthFill.classList.add('strength-strong');
                strengthText.textContent = 'Excellent';
            }
        }
        
        confirmInput.addEventListener('input', function() {
            if (this.value.length > 0) {
                if (passwordInput.value === this.value) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else {
                    this.classList.remove('is-valid');
                    this.classList.add('is-invalid');
                }
            }
        });
        
        document.getElementById('registerForm').addEventListener('submit', function() {
            document.getElementById('registerBtn').classList.add('loading');
        });
        
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('input', function() {
                if (this.classList.contains('is-invalid') && this.value) {
                    this.classList.remove('is-invalid');
                }
            });
        });
    </script>
</body>
</html>
