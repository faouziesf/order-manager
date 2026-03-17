<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#3b82f6">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>Connexion - Order Manager</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            position: relative;
            overflow: hidden;
        }
        body::before {
            content: '';
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.15) 0%, transparent 70%);
            top: -200px;
            right: -200px;
            border-radius: 50%;
            animation: float 20s ease-in-out infinite;
        }
        body::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(147, 51, 234, 0.1) 0%, transparent 70%);
            bottom: -150px;
            left: -150px;
            border-radius: 50%;
            animation: float 15s ease-in-out infinite reverse;
        }
        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -30px) scale(1.1); }
            66% { transform: translate(-30px, 30px) scale(0.9); }
        }
        .login-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 450px;
        }
        .login-container {
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
        .login-header {
            padding: 3rem 2.5rem 2.5rem;
            text-align: center;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            position: relative;
            overflow: hidden;
        }
        .login-header::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                        radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.05) 0%, transparent 50%);
        }
        .logo-container {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 24px;
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
            font-size: 2.5rem;
            color: white;
        }
        .login-title {
            font-size: 2rem;
            font-weight: 900;
            color: white;
            margin-bottom: 0.5rem;
            position: relative;
            letter-spacing: -0.5px;
        }
        .login-subtitle {
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.85);
            font-weight: 500;
            position: relative;
        }
        .login-body {
            padding: 2.5rem 2.5rem 2rem;
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
        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }
        .form-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }
        .form-label i { color: #3b82f6; font-size: 1rem; }
        .form-control {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 16px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f9fafb;
            color: #1f2937;
            font-weight: 500;
        }
        .form-control:focus {
            outline: none;
            border-color: #3b82f6;
            background: white;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }
        .form-control.is-invalid {
            border-color: #ef4444;
            background: #fef2f2;
        }
        .invalid-feedback {
            color: #dc2626;
            font-size: 0.8125rem;
            margin-top: 0.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }
        .btn-login {
            width: 100%;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
            border-radius: 16px;
            font-size: 1.0625rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(59, 130, 246, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            position: relative;
            overflow: hidden;
        }
        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }
        .btn-login:hover::before { left: 100%; }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(59, 130, 246, 0.5);
        }
        .btn-login:active { transform: translateY(0); }
        .btn-login.loading { pointer-events: none; opacity: 0.7; }
        .btn-login .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }
        .btn-login.loading .spinner { display: block; }
        .btn-login.loading .btn-text { display: none; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .divider {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 2rem 0;
        }
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, transparent, #e5e7eb, transparent);
        }
        .divider span {
            font-size: 0.8125rem;
            color: #9ca3af;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .register-box {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border: 2px solid #3b82f6;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            position: relative;
            overflow: hidden;
        }
        .register-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            animation: shimmer 3s ease-in-out infinite;
        }
        @keyframes shimmer {
            0% { left: -100%; }
            100% { left: 100%; }
        }
        .register-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #1e40af;
            font-weight: 800;
            font-size: 0.9375rem;
            margin-bottom: 0.5rem;
        }
        .register-badge i {
            font-size: 1.125rem;
            animation: bounce 2s ease-in-out infinite;
        }
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }
        .register-text {
            color: #1e3a8a;
            font-size: 0.875rem;
            font-weight: 600;
            line-height: 1.6;
        }
        .features-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            margin-top: 1rem;
        }
        .feature-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #1e3a8a;
            font-size: 0.8125rem;
            font-weight: 600;
        }
        .feature-item i { color: #3b82f6; font-size: 1rem; }
        .register-link {
            text-align: center;
            padding: 2rem 0 0.5rem;
            border-top: 1px solid #f3f4f6;
        }
        .register-link p {
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 0.75rem;
        }
        .register-link a {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #3b82f6;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.9375rem;
            transition: all 0.3s ease;
            padding: 0.5rem 1rem;
            border-radius: 12px;
        }
        .register-link a:hover {
            background: #eff6ff;
            color: #2563eb;
            transform: translateX(4px);
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
        @media (max-width: 576px) {
            body { padding: 0.5rem; }
            .login-container { border-radius: 24px; }
            .login-header { padding: 2rem 1.5rem 1.5rem; }
            .logo-container { width: 70px; height: 70px; margin-bottom: 1rem; }
            .logo-container i { font-size: 2rem; }
            .login-title { font-size: 1.5rem; }
            .login-subtitle { font-size: 0.875rem; }
            .login-body { padding: 2rem 1.5rem 1.5rem; }
            .form-control { padding: 0.875rem; font-size: 0.9375rem; }
            .btn-login { padding: 0.875rem 1.5rem; font-size: 1rem; }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-container">
            <div class="login-header">
                <div class="logo-container">
                    <i class="fas fa-shield-halved"></i>
                </div>
                <h1 class="login-title">Bienvenue !</h1>
                <p class="login-subtitle">Connectez-vous à votre espace Order Manager</p>
            </div>
            <div class="login-body">
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
                @if(session('expired_reason'))
                    <div class="alert alert-danger">
                        <i class="fas fa-clock"></i>
                        <span>
                            @switch(session('expired_reason'))
                                @case('expired') Votre période d'essai a expiré. Contactez l'administrateur. @break
                                @case('inactive') Votre compte a été désactivé. Contactez l'administrateur. @break
                                @default Votre compte n'est plus accessible.
                            @endswitch
                        </span>
                    </div>
                @endif
                <form action="{{ route('login.submit') }}" method="POST" id="loginForm">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-envelope"></i>
                            Adresse e-mail
                        </label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                               name="email" placeholder="votreemail@exemple.com"
                               value="{{ old('email') }}" required autofocus>
                        @error('email')
                            <div class="invalid-feedback">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-lock"></i>
                            Mot de passe
                        </label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                               name="password" placeholder="••••••••" required>
                        @error('password')
                            <div class="invalid-feedback">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <input type="hidden" name="remember" value="1">
                    <button type="submit" class="btn-login" id="loginBtn">
                        <div class="spinner"></div>
                        <span class="btn-text">
                            <i class="fas fa-sign-in-alt"></i>
                            Se connecter
                        </span>
                    </button>
                </form>
                <div class="divider"><span>Nouveau sur Order Manager ?</span></div>
                <div class="register-box">
                    <div class="register-badge">
                        <i class="fas fa-gift"></i>
                        Essai gratuit 14 jours
                    </div>
                    <div class="register-text">
                        Accès complet • Support prioritaire • Sans engagement
                    </div>
                    <div class="features-grid">
                        <div class="feature-item"><i class="fas fa-check"></i><span>Multi-utilisateurs</span></div>
                        <div class="feature-item"><i class="fas fa-check"></i><span>Intégrations</span></div>
                        <div class="feature-item"><i class="fas fa-check"></i><span>Tableau de bord</span></div>
                        <div class="feature-item"><i class="fas fa-check"></i><span>Support 24/7</span></div>
                    </div>
                </div>
                <div class="register-link">
                    <p>Vous n'avez pas encore de compte ?</p>
                    <a href="{{ route('register') }}">
                        <i class="fas fa-user-plus"></i>
                        Créer mon compte gratuitement
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
        document.getElementById('loginForm').addEventListener('submit', function() {
            document.getElementById('loginBtn').classList.add('loading');
        });
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('input', function() {
                if (this.classList.contains('is-invalid')) {
                    this.classList.remove('is-invalid');
                    const feedback = this.parentElement.querySelector('.invalid-feedback');
                    if (feedback) feedback.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
