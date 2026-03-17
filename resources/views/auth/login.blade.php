<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=5.0,user-scalable=yes,viewport-fit=cover">
<meta name="theme-color" content="#1e40af">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<title>Connexion - Confirmi</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root{
--royal-900:#1e3a8a;--royal-800:#1e40af;--royal-700:#2563eb;--royal-600:#3b82f6;--royal-500:#60a5fa;--royal-50:#eff6ff;
--slate-900:#0f172a;--slate-800:#1e293b;--slate-600:#475569;--slate-500:#64748b;--slate-200:#e2e8f0;--slate-100:#f1f5f9;--slate-50:#f8fafc;
--white:#ffffff;--gold:#fbbf24;--success:#10b981;--danger:#ef4444;
--safe-area-top:env(safe-area-inset-top);--safe-area-bottom:env(safe-area-inset-bottom);
--shadow-elegant:0 4px 24px rgba(30,64,175,.12);
--shadow-hover:0 8px 32px rgba(30,64,175,.18);
}
*{margin:0;padding:0;box-sizing:border-box;-webkit-tap-highlight-color:transparent}
html{font-size:16px;height:100%;-webkit-text-size-adjust:100%}
body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--slate-50);color:var(--slate-900);min-height:100vh;display:flex;flex-direction:column;-webkit-font-smoothing:antialiased;padding-top:var(--safe-area-top)}

/* Header */
.header{padding:1.5rem 1rem;background:var(--white);border-bottom:1px solid var(--slate-200)}
.header-content{display:flex;align-items:center;gap:1rem;max-width:500px;margin:0 auto}
.back-btn{width:40px;height:40px;border-radius:12px;border:none;background:var(--slate-100);color:var(--slate-700);display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:1.125rem;transition:all .2s}
.back-btn:active{transform:scale(.95);background:var(--slate-200)}
.header-title{font-size:1.125rem;font-weight:800;color:var(--slate-900);flex:1}

/* Main Content */
.main-content{flex:1;padding:2rem 1rem;max-width:500px;margin:0 auto;width:100%}
.login-card{background:var(--white);border-radius:24px;padding:2rem 1.5rem;box-shadow:0 4px 16px rgba(15,118,110,.08);margin-bottom:1.5rem}
.logo-section{text-align:center;margin-bottom:2rem}
.logo-icon{width:80px;height:80px;margin:0 auto 1rem;background:linear-gradient(135deg,var(--royal-700),var(--royal-800));border-radius:20px;display:flex;align-items:center;justify-content:center;box-shadow:0 8px 24px rgba(30,64,175,.25);border:2px solid rgba(59,130,246,.15)}
.logo-icon i{font-size:2.5rem;color:var(--white)}
.page-title{font-size:1.75rem;font-weight:800;color:var(--slate-900);margin-bottom:.5rem;letter-spacing:-.02em}
.page-subtitle{font-size:.9375rem;color:var(--slate-600)}

/* Alerts */
.alert{padding:1rem 1.125rem;border-radius:14px;margin-bottom:1.5rem;font-size:.875rem;font-weight:600;display:flex;align-items:center;gap:.75rem;animation:slideDown .3s}
@keyframes slideDown{from{opacity:0;transform:translateY(-10px)}to{opacity:1;transform:translateY(0)}}
.alert-success{background:#ecfdf5;color:#065f46;border:2px solid #10b981}
.alert-danger{background:#fef2f2;color:#991b1b;border:2px solid #fecaca}
.alert i{font-size:1.125rem}

/* Form */
.form-group{margin-bottom:1.25rem}
.form-label{display:flex;align-items:center;gap:.5rem;font-size:.875rem;font-weight:700;color:var(--slate-900);margin-bottom:.625rem}
.form-label i{color:var(--royal-700);font-size:1rem}
.form-input{width:100%;padding:.9375rem 1rem;border:2px solid var(--slate-200);border-radius:14px;font-size:1rem;font-family:inherit;transition:all .2s;background:var(--white);color:var(--slate-900)}
.form-input:focus{outline:none;border-color:var(--royal-600);box-shadow:0 0 0 4px rgba(59,130,246,.12)}
.form-input.is-invalid{border-color:var(--danger);background:#fef2f2}
.invalid-feedback{color:var(--danger);font-size:.8125rem;margin-top:.5rem;font-weight:600;display:flex;align-items:center;gap:.375rem}
.form-checkbox{display:flex;align-items:center;gap:.625rem;margin-bottom:1.5rem}
.form-checkbox input{width:1.125rem;height:1.125rem;border-radius:6px;cursor:pointer;accent-color:var(--royal-700)}
.form-checkbox label{font-size:.875rem;color:var(--slate-600);cursor:pointer;user-select:none}

/* Buttons */
.btn-submit{width:100%;padding:1rem;background:linear-gradient(135deg,var(--royal-700),var(--royal-900));color:var(--white);border:none;border-radius:14px;font-size:1.0625rem;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.75rem;transition:all .2s;box-shadow:0 4px 16px rgba(30,64,175,.3);position:relative;overflow:hidden}
.btn-submit::before{content:'';position:absolute;top:0;left:-100%;width:100%;height:100%;background:linear-gradient(90deg,transparent,rgba(255,255,255,.2),transparent);transition:left .5s}
.btn-submit:active{transform:scale(.98);box-shadow:0 2px 8px rgba(30,64,175,.35)}
.btn-submit:hover::before{left:100%}
.btn-submit.loading{pointer-events:none;opacity:.7}
.btn-submit .spinner{display:none;width:20px;height:20px;border:2px solid rgba(255,255,255,.3);border-top-color:var(--white);border-radius:50%;animation:spin .6s linear infinite}
.btn-submit.loading .spinner{display:block}
.btn-submit.loading .btn-text{display:none}
@keyframes spin{to{transform:rotate(360deg)}}

/* Divider */
.divider{display:flex;align-items:center;gap:1rem;margin:1.75rem 0}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:var(--slate-200)}
.divider-text{font-size:.75rem;color:var(--slate-500);font-weight:600;text-transform:uppercase;letter-spacing:.5px}

/* Promo Box */
.promo-box{background:linear-gradient(135deg,#fffbeb,#fef3c7);border:2px solid #f59e0b;border-radius:16px;padding:1.25rem;margin-bottom:1.5rem;position:relative;overflow:hidden}
.promo-box::before{content:'';position:absolute;top:0;left:-100%;width:100%;height:100%;background:linear-gradient(90deg,transparent,rgba(255,255,255,.5),transparent);animation:shimmer 3s ease-in-out infinite}
@keyframes shimmer{0%{left:-100%}100%{left:100%}}
.promo-badge{display:flex;align-items:center;justify-content:center;gap:.625rem;color:#92400e;font-weight:800;font-size:.9375rem;margin-bottom:.5rem;position:relative}
.promo-badge i{font-size:1.125rem;animation:bounce 2s ease-in-out infinite}
@keyframes bounce{0%,100%{transform:translateY(0)}50%{transform:translateY(-5px)}}
.promo-text{color:#78350f;font-size:.875rem;font-weight:700;text-align:center;line-height:1.5;position:relative}
.promo-features{display:grid;grid-template-columns:1fr 1fr;gap:.625rem;margin-top:.875rem;position:relative}
.promo-feature{display:flex;align-items:center;gap:.5rem;color:#78350f;font-size:.75rem;font-weight:700}
.promo-feature i{color:#f59e0b;font-size:.875rem}

/* Register Link */
.register-section{text-align:center;padding-top:1rem}
.register-text{font-size:.875rem;color:var(--slate-600);margin-bottom:.75rem}
.register-link{display:inline-flex;align-items:center;gap:.625rem;padding:.875rem 1.5rem;background:var(--royal-50);color:var(--royal-700);border-radius:14px;text-decoration:none;font-weight:700;font-size:.9375rem;transition:all .2s;border:1px solid rgba(30,64,175,.15)}
.register-link:active{transform:scale(.98);background:var(--royal-600);color:var(--white)}

/* Footer */
.footer{padding:1.5rem 1rem calc(1.5rem + var(--safe-area-bottom));text-align:center}
.footer-link{display:inline-flex;align-items:center;gap:.5rem;color:var(--slate-500);text-decoration:none;font-size:.875rem;font-weight:600;padding:.75rem 1.25rem;border-radius:12px;transition:all .2s}
.footer-link:active{background:var(--slate-100);transform:scale(.98)}

/* Tablet & Desktop */
@media(min-width:640px){
.main-content{padding:3rem 2rem}
.login-card{padding:3rem 2.5rem}
.promo-features{grid-template-columns:repeat(4,1fr)}
}
</style>
</head>
<body>

<!-- Header -->
<header class="header">
<div class="header-content">
<a href="{{ route('confirmi.home') }}" class="back-btn">
<i class="fas fa-arrow-left"></i>
</a>
<h1 class="header-title">Connexion</h1>
</div>
</header>

<!-- Main -->
<main class="main-content">
<div class="login-card">
<div class="logo-section">
<div class="logo-icon">
<i class="fas fa-shield-halved"></i>
</div>
<h2 class="page-title">Bienvenue !</h2>
<p class="page-subtitle">Connectez-vous à votre espace</p>
</div>

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
@case('expired') Votre période d'essai a expiré. @break
@case('inactive') Votre compte a été désactivé. @break
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
<span>Adresse e-mail</span>
</label>
<input type="email" class="form-input @error('email') is-invalid @enderror" name="email" placeholder="votreemail@exemple.com" value="{{ old('email') }}" required autofocus>
@error('email')
<div class="invalid-feedback">
<i class="fas fa-exclamation-circle"></i>
<span>{{ $message }}</span>
</div>
@enderror
</div>

<div class="form-group">
<label class="form-label">
<i class="fas fa-lock"></i>
<span>Mot de passe</span>
</label>
<input type="password" class="form-input @error('password') is-invalid @enderror" name="password" placeholder="••••••••" required>
@error('password')
<div class="invalid-feedback">
<i class="fas fa-exclamation-circle"></i>
<span>{{ $message }}</span>
</div>
@enderror
</div>

<input type="hidden" name="remember" value="1">

<button type="submit" class="btn-submit" id="submitBtn">
<div class="spinner"></div>
<span class="btn-text">
<i class="fas fa-sign-in-alt"></i>
<span>Se connecter</span>
</span>
</button>
</form>

<div class="divider">
<span class="divider-text">Nouveau ici ?</span>
</div>

<div class="promo-box">
<div class="promo-badge">
<i class="fas fa-gift"></i>
<span>Essai gratuit 14 jours</span>
</div>
<div class="promo-text">
Accès complet • Support prioritaire • Sans engagement
</div>
<div class="promo-features">
<div class="promo-feature">
<i class="fas fa-check"></i>
<span>Multi-users</span>
</div>
<div class="promo-feature">
<i class="fas fa-check"></i>
<span>Intégrations</span>
</div>
<div class="promo-feature">
<i class="fas fa-check"></i>
<span>Dashboard</span>
</div>
<div class="promo-feature">
<i class="fas fa-check"></i>
<span>Support 24/7</span>
</div>
</div>
</div>

<div class="register-section">
<p class="register-text">Vous n'avez pas encore de compte ?</p>
<a href="{{ route('register') }}" class="register-link">
<i class="fas fa-user-plus"></i>
<span>Créer mon compte gratuitement</span>
</a>
</div>
</div>
</main>

<!-- Footer -->
<footer class="footer">
<a href="{{ route('confirmi.home') }}" class="footer-link">
<i class="fas fa-arrow-left"></i>
<span>Retour à l'accueil</span>
</a>
</footer>

<script>
document.getElementById('loginForm').addEventListener('submit',function(){
document.getElementById('submitBtn').classList.add('loading');
});

document.querySelectorAll('.form-input').forEach(input=>{
input.addEventListener('input',function(){
if(this.classList.contains('is-invalid')){
this.classList.remove('is-invalid');
const feedback=this.parentElement.querySelector('.invalid-feedback');
if(feedback)feedback.style.display='none';
}
});
});
</script>
</body>
</html>
