<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=5.0,user-scalable=yes">
<meta name="theme-color" content="#1e40af">
<title>Plateforme de Gestion des Commandes</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Plus Jakarta Sans',sans-serif;background:linear-gradient(135deg,#f8fafc 0%,#eff6ff 100%);color:#0f172a;line-height:1.6;min-height:100vh}

/* Header Simple */
.header{background:#fff;padding:1.25rem 1.5rem;box-shadow:0 1px 3px rgba(0,0,0,.05);position:sticky;top:0;z-index:100}
.header-content{max-width:1200px;margin:0 auto;display:flex;align-items:center;justify-content:space-between}
.logo img{height:40px}
.header-link{display:inline-flex;align-items:center;gap:.5rem;padding:.625rem 1.25rem;background:#eff6ff;color:#1e40af;border-radius:10px;text-decoration:none;font-weight:700;font-size:.875rem;transition:all .2s}
.header-link:hover{background:#1e40af;color:#fff;transform:translateY(-1px)}

/* Hero Section avec Image */
.hero{max-width:1200px;margin:0 auto;padding:4rem 1.5rem;display:grid;gap:3rem;align-items:center}
.hero-content h1{font-size:clamp(2rem,5vw,3rem);font-weight:800;color:#0f172a;line-height:1.2;margin-bottom:1rem}
.hero-content h1 span{background:linear-gradient(135deg,#1e40af,#3b82f6);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.hero-content p{font-size:1.125rem;color:#475569;margin-bottom:2rem;line-height:1.7}
.hero-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:1.5rem;margin-top:2.5rem}
.stat{text-align:center;padding:1.5rem 1rem;background:#fff;border-radius:16px;box-shadow:0 4px 12px rgba(30,64,175,.08);border:1px solid rgba(30,64,175,.06)}
.stat-icon{font-size:2rem;margin-bottom:.75rem;background:linear-gradient(135deg,#1e40af,#3b82f6);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.stat-value{font-size:1.75rem;font-weight:800;color:#1e40af;margin-bottom:.25rem}
.stat-label{font-size:.8125rem;color:#64748b;font-weight:600}

/* Hero Image avec SVG */
.hero-image{position:relative;text-align:center}
.hero-visual{width:100%;max-width:500px;height:auto;filter:drop-shadow(0 20px 40px rgba(30,64,175,.15))}

/* Login Card Intégré */
.login-card{background:#fff;padding:2.5rem;border-radius:24px;box-shadow:0 8px 32px rgba(30,64,175,.12);border:1px solid rgba(30,64,175,.08);max-width:420px}
.login-card h2{font-size:1.5rem;font-weight:800;color:#0f172a;margin-bottom:.5rem;text-align:center}
.login-card p{text-align:center;color:#64748b;font-size:.9375rem;margin-bottom:2rem}
.form-group{margin-bottom:1.25rem}
.form-label{display:flex;align-items:center;gap:.5rem;font-size:.875rem;font-weight:700;color:#0f172a;margin-bottom:.625rem}
.form-label i{color:#1e40af;font-size:1rem}
.form-input{width:100%;padding:.875rem 1rem;border:2px solid #e2e8f0;border-radius:12px;font-size:1rem;font-family:inherit;transition:all .2s}
.form-input:focus{outline:none;border-color:#1e40af;box-shadow:0 0 0 4px rgba(30,64,175,.1)}
.btn-login{width:100%;padding:1rem;background:linear-gradient(135deg,#1e40af,#1e3a8a);color:#fff;border:none;border-radius:12px;font-size:1rem;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.625rem;transition:all .2s;box-shadow:0 4px 16px rgba(30,64,175,.25)}
.btn-login:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(30,64,175,.35)}
.btn-login:active{transform:translateY(0)}
.register-link{text-align:center;margin-top:1.5rem;padding-top:1.5rem;border-top:1px solid #f1f5f9}
.register-link p{font-size:.875rem;color:#64748b;margin-bottom:.75rem}
.register-link a{display:inline-flex;align-items:center;gap:.5rem;color:#1e40af;text-decoration:none;font-weight:700;padding:.625rem 1rem;border-radius:10px;transition:all .2s}
.register-link a:hover{background:#eff6ff}

/* Features avec Images */
.features{max-width:1200px;margin:4rem auto;padding:0 1.5rem}
.section-title{text-align:center;font-size:2rem;font-weight:800;color:#0f172a;margin-bottom:3rem}
.features-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:2rem}
.feature{background:#fff;padding:2rem;border-radius:20px;text-align:center;box-shadow:0 4px 16px rgba(30,64,175,.08);border:1px solid rgba(30,64,175,.06);transition:all .3s}
.feature:hover{transform:translateY(-5px);box-shadow:0 8px 24px rgba(30,64,175,.15)}
.feature-icon{width:80px;height:80px;margin:0 auto 1.5rem;background:linear-gradient(135deg,#eff6ff,#dbeafe);border-radius:18px;display:flex;align-items:center;justify-content:center}
.feature-icon i{font-size:2rem;color:#1e40af}
.feature h3{font-size:1.125rem;font-weight:700;color:#0f172a;margin-bottom:.75rem}
.feature p{font-size:.9375rem;color:#64748b;line-height:1.6}

/* Footer Simple */
.footer{background:#fff;padding:2rem 1.5rem;margin-top:4rem;border-top:1px solid #e2e8f0}
.footer-content{max-width:1200px;margin:0 auto;text-align:center;color:#64748b;font-size:.875rem}

/* Responsive */
@media(min-width:768px){
.hero{grid-template-columns:1fr 1fr;gap:4rem;padding:5rem 1.5rem}
.login-card{margin-left:auto}
}
@media(max-width:767px){
.hero-image{order:-1}
.hero-visual{max-width:100%}
.stat{padding:1rem .75rem}
.stat-value{font-size:1.5rem}
}
</style>
</head>
<body>

<!-- Header -->
<header class="header">
<div class="header-content">
<div class="logo">
<img src="{{ asset('img/confirmi.png') }}" alt="Logo">
</div>
<a href="{{ route('register') }}" class="header-link">
<i class="fas fa-rocket"></i>
<span>Essai gratuit</span>
</a>
</div>
</header>

<!-- Hero Section -->
<section class="hero">
<div class="hero-content">
<h1>Gérez vos commandes <span>sans effort</span></h1>
<p>Automatisez la confirmation, suivez vos stocks en temps réel et expédiez plus rapidement avec notre plateforme intelligente.</p>

<!-- Stats -->
<div class="hero-stats">
<div class="stat">
<div class="stat-icon"><i class="fas fa-bolt"></i></div>
<div class="stat-value">Auto</div>
<div class="stat-label">Import</div>
</div>
<div class="stat">
<div class="stat-icon"><i class="fas fa-chart-line"></i></div>
<div class="stat-value">100%</div>
<div class="stat-label">Suivi</div>
</div>
<div class="stat">
<div class="stat-icon"><i class="fas fa-clock"></i></div>
<div class="stat-value">24/7</div>
<div class="stat-label">Temps réel</div>
</div>
</div>
</div>

<!-- Hero Image + Login Card -->
<div class="hero-image">
<!-- SVG Illustration -->
<svg class="hero-visual" viewBox="0 0 500 400" fill="none" xmlns="http://www.w3.org/2000/svg">
<rect width="500" height="400" fill="url(#grad1)"/>
<defs>
<linearGradient id="grad1" x1="0%" y1="0%" x2="100%" y2="100%">
<stop offset="0%" style="stop-color:#eff6ff;stop-opacity:1"/>
<stop offset="100%" style="stop-color:#dbeafe;stop-opacity:1"/>
</linearGradient>
</defs>
<!-- Dashboard -->
<rect x="50" y="80" width="400" height="280" rx="20" fill="white" stroke="#1e40af" stroke-width="2"/>
<rect x="70" y="100" width="120" height="40" rx="8" fill="#1e40af"/>
<rect x="210" y="100" width="80" height="40" rx="8" fill="#3b82f6"/>
<rect x="310" y="100" width="120" height="40" rx="8" fill="#60a5fa"/>
<!-- Charts -->
<circle cx="130" cy="220" r="50" fill="#eff6ff" stroke="#1e40af" stroke-width="4"/>
<rect x="220" y="180" width="200" height="100" rx="10" fill="#eff6ff"/>
<path d="M 240 240 L 270 210 L 300 225 L 330 200 L 360 215 L 390 190" stroke="#1e40af" stroke-width="3" fill="none"/>
<!-- Icons -->
<circle cx="100" cy="310" r="15" fill="#1e40af"/>
<rect x="140" y="295" width="100" height="10" rx="5" fill="#e2e8f0"/>
<rect x="140" y="315" width="70" height="10" rx="5" fill="#e2e8f0"/>
</svg>

<!-- Login Card Intégré -->
<div class="login-card">
<h2>Connexion</h2>
<p>Accédez à votre espace</p>

@if(session('error'))
<div style="background:#fef2f2;color:#991b1b;border:2px solid #fecaca;padding:.875rem 1rem;border-radius:12px;font-size:.875rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:.625rem">
<i class="fas fa-exclamation-circle"></i>
<span>{{ session('error') }}</span>
</div>
@endif

@if(session('csrf_error'))
<div style="background:#fef3c7;color:#78350f;border:2px solid #fbbf24;padding:.875rem 1rem;border-radius:12px;font-size:.875rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:.625rem">
<i class="fas fa-exclamation-triangle"></i>
<span>Votre session a expiré. La page va se recharger automatiquement...</span>
</div>
<script>setTimeout(function(){window.location.reload();},2000);</script>
@endif

<form method="POST" action="{{ route('confirmi.login.submit') }}" id="loginForm">
@csrf
<div class="form-group">
<label class="form-label">
<i class="fas fa-envelope"></i>
<span>Email</span>
</label>
<input type="email" name="email" class="form-input" placeholder="votre@email.com" value="{{ old('email') }}" required autofocus>
</div>

<div class="form-group">
<label class="form-label">
<i class="fas fa-lock"></i>
<span>Mot de passe</span>
</label>
<input type="password" name="password" class="form-input" placeholder="••••••••" required>
</div>

<button type="submit" class="btn-login">
<i class="fas fa-sign-in-alt"></i>
<span>Se connecter</span>
</button>
</form>

<div class="register-link">
<p>Nouveau sur la plateforme ?</p>
<a href="{{ route('register') }}">
<i class="fas fa-user-plus"></i>
<span>Créer un compte gratuit</span>
</a>
</div>
</div>
</div>
</section>

<!-- Features -->
<section class="features">
<h2 class="section-title">Fonctionnalités principales</h2>
<div class="features-grid">
<div class="feature">
<div class="feature-icon">
<i class="fas fa-file-excel"></i>
</div>
<h3>Adieu Excel</h3>
<p>Organisez et traitez vos commandes rapidement sur une interface intuitive moderne.</p>
</div>

<div class="feature">
<div class="feature-icon">
<i class="fas fa-tachometer-alt"></i>
</div>
<h3>Dashboard Global</h3>
<p>Suivez vos stocks et commandes en temps réel avec des statistiques claires.</p>
</div>

<div class="feature">
<div class="feature-icon">
<i class="fas fa-globe"></i>
</div>
<h3>Intégrations</h3>
<p>Connectez Shopify, WooCommerce et récupérez vos commandes automatiquement.</p>
</div>

<div class="feature">
<div class="feature-icon">
<i class="fas fa-headset"></i>
</div>
<h3>Confirmation Rapide</h3>
<p>Interface focalisée pour augmenter la productivité de votre équipe.</p>
</div>

<div class="feature">
<div class="feature-icon">
<i class="fas fa-users-cog"></i>
</div>
<h3>Multi-utilisateurs</h3>
<p>Séparez les rôles et assignez facilement les tâches à votre équipe.</p>
</div>

<div class="feature">
<div class="feature-icon">
<i class="fas fa-shipping-fast"></i>
</div>
<h3>Expédition Auto</h3>
<p>Transfert automatique vers Masafa Express après confirmation.</p>
</div>
</div>
</section>

<!-- Footer -->
<footer class="footer">
<div class="footer-content">
<p>&copy; 2026 Plateforme de Gestion des Commandes. Tous droits réservés.</p>
</div>
</footer>

</body>
</html>
