<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Confirmi</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 50%, #3b82f6 100%);
            display: flex; align-items: center; justify-content: center;
            padding: 1rem;
        }
        .login-container {
            width: 100%; max-width: 420px;
        }
        .login-brand {
            text-align: center; margin-bottom: 2rem; color: white;
        }
        .login-brand .brand-icon {
            width: 56px; height: 56px; margin: 0 auto 0.75rem;
            background: rgba(255,255,255,0.15);
            border-radius: 16px; display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; backdrop-filter: blur(10px);
        }
        .login-brand h1 { font-size: 1.75rem; font-weight: 800; letter-spacing: -0.5px; }
        .login-brand p { font-size: 0.85rem; opacity: 0.8; margin-top: 0.25rem; }

        .login-card {
            background: white; border-radius: 16px; padding: 2rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        }
        .login-card h2 { font-size: 1.2rem; font-weight: 700; color: #0f172a; margin-bottom: 1.5rem; }

        .form-group { margin-bottom: 1.25rem; }
        .form-group label {
            display: block; font-size: 0.8rem; font-weight: 600;
            color: #334155; margin-bottom: 0.4rem;
        }
        .input-wrapper {
            position: relative;
        }
        .input-wrapper i {
            position: absolute; left: 0.85rem; top: 50%; transform: translateY(-50%);
            color: #94a3b8; font-size: 0.9rem;
        }
        .input-wrapper input {
            width: 100%; padding: 0.7rem 0.85rem 0.7rem 2.5rem;
            border: 1.5px solid #e2e8f0; border-radius: 10px;
            font-size: 0.9rem; font-family: 'Inter', sans-serif;
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
        }
        .input-wrapper input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
        }

        .remember-row {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 1.5rem;
        }
        .remember-row label {
            display: flex; align-items: center; gap: 0.4rem;
            font-size: 0.8rem; color: #64748b; cursor: pointer;
        }
        .remember-row input[type="checkbox"] {
            width: 16px; height: 16px; accent-color: #2563eb;
        }

        .btn-login {
            width: 100%; padding: 0.75rem;
            background: linear-gradient(135deg, #1e3a8a, #2563eb);
            color: white; border: none; border-radius: 10px;
            font-size: 0.95rem; font-weight: 700; cursor: pointer;
            transition: opacity 0.2s;
            font-family: 'Inter', sans-serif;
        }
        .btn-login:hover { opacity: 0.9; }

        .alert-error {
            background: #fef2f2; color: #991b1b; border: 1px solid #fecaca;
            padding: 0.65rem 0.85rem; border-radius: 8px; font-size: 0.8rem;
            margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;
        }

        @media (max-width: 480px) {
            .login-card { padding: 1.5rem; }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-brand">
            <div class="brand-icon"><i class="fas fa-headset"></i></div>
            <h1>Confirmi</h1>
            <p>Accès unifié — Commerciaux, Employés, Admins</p>
        </div>

        <div class="login-card">
            <h2>Connexion</h2>

            @if(session('error'))
                <div class="alert-error">
                    <i class="fas fa-exclamation-circle"></i>{{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('confirmi.login.submit') }}">
                @csrf
                <div class="form-group">
                    <label for="email">Adresse email</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="email@confirmi.com" required autofocus>
                    </div>
                    @error('email')<small style="color:#dc2626;font-size:0.75rem;">{{ $message }}</small>@enderror
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Entrez votre mot de passe" required>
                    </div>
                    @error('password')<small style="color:#dc2626;font-size:0.75rem;">{{ $message }}</small>@enderror
                </div>

                <div class="remember-row">
                    <label><input type="checkbox" name="remember"> Se souvenir de moi</label>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt me-1"></i> Se connecter
                </button>
            </form>
        </div>
    </div>
</body>
</html>
