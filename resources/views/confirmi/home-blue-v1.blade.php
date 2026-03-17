<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=5.0,user-scalable=yes,viewport-fit=cover">
<meta name="theme-color" content="#1e40af">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="mobile-web-app-capable" content="yes">
<title>Confirmi — Plateforme de Confirmation #1</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="manifest" href="{{ asset('manifest.json') }}">
<style>
:root{
--royal-900:#1e3a8a;--royal-800:#1e40af;--royal-700:#2563eb;--royal-600:#3b82f6;--royal-500:#60a5fa;--royal-50:#eff6ff;
--gold:#fbbf24;--amber:#f59e0b;
--slate-900:#0f172a;--slate-800:#1e293b;--slate-600:#475569;--slate-500:#64748b;--slate-200:#e2e8f0;--slate-100:#f1f5f9;--slate-50:#f8fafc;
--white:#ffffff;--success:#10b981;--danger:#ef4444;
--safe-area-top:env(safe-area-inset-top);--safe-area-bottom:env(safe-area-inset-bottom);
--shadow-elegant:0 4px 24px rgba(30,64,175,.12);
--shadow-hover:0 8px 32px rgba(30,64,175,.18);
}
*{margin:0;padding:0;box-sizing:border-box;-webkit-tap-highlight-color:transparent}
html{scroll-behavior:smooth;font-size:16px;height:100%}
body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--slate-50);color:var(--slate-900);overflow-x:hidden;line-height:1.6;-webkit-font-smoothing:antialiased;min-height:100vh};-moz-osx-font-smoothing:grayscale;min-height:100vh;padding-bottom:var(--bottom-nav-height)}

/* TOP NAV - Minimaliste Mobile-First */
.top-nav{position:fixed;top:0;left:0;right:0;height:var(--nav-height);background:var(--white);border-bottom:1px solid rgba(15,118,110,.08);z-index:1000;display:flex;align-items:center;justify-content:space-between;padding:0 1rem;backdrop-filter:blur(20px);box-shadow:0 1px 3px rgba(0,0,0,.04)}
.logo-area{display:flex;align-items:center;gap:.75rem}
.logo-area img{height:32px;width:auto}
.logo-text{font-size:1.125rem;font-weight:800;color:var(--royal-700);letter-spacing:-.02em}
.nav-actions{display:flex;gap:.5rem}
.btn-icon{width:40px;height:40px;border-radius:12px;border:none;background:var(--slate-100);color:var(--slate-700);display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all .2s;font-size:1.125rem}
.btn-icon:active{transform:scale(.95);background:var(--slate-200)}
.btn-primary-sm{padding:.625rem 1.125rem;background:linear-gradient(135deg,var(--royal-700),var(--royal-800));color:var(--white);border:none;border-radius:12px;font-size:.875rem;font-weight:700;cursor:pointer;transition:all .2s;white-space:nowrap;box-shadow:0 2px 8px rgba(30,64,175,.25)}
.btn-primary-sm:active{transform:scale(.98);box-shadow:0 1px 4px rgba(30,64,175,.3)}

/* HERO Section - Card-Based Mobile-First */
.hero-section{padding:calc(var(--nav-height) + 2rem) 1rem 3rem;background:linear-gradient(180deg,var(--royal-50) 0%,var(--white) 100%)}
.hero-card{background:var(--white);border-radius:24px;padding:2rem 1.5rem;box-shadow:var(--shadow-elegant);text-align:center;margin-bottom:1.5rem;border:1px solid rgba(30,64,175,.06)}
.hero-badge{display:inline-flex;align-items:center;gap:.5rem;background:linear-gradient(135deg,var(--royal-600),var(--royal-700));color:var(--white);padding:.5rem 1rem;border-radius:100px;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;margin-bottom:1.5rem;box-shadow:0 4px 12px rgba(59,130,246,.3)}
.hero-icon{width:80px;height:80px;margin:0 auto 1.5rem;background:linear-gradient(135deg,var(--royal-700),var(--royal-800));border-radius:20px;display:flex;align-items:center;justify-content:center;box-shadow:0 8px 24px rgba(30,64,175,.25)}
.hero-icon i{font-size:2.5rem;color:var(--white)}
.hero-title{font-size:clamp(1.75rem,5vw,2.5rem);font-weight:800;color:var(--slate-900);line-height:1.2;margin-bottom:1rem;letter-spacing:-.02em}
.hero-subtitle{font-size:1rem;color:var(--slate-600);line-height:1.6;margin-bottom:2rem}
.hero-cta{display:flex;flex-direction:column;gap:.75rem}
.btn-cta-lg{padding:1rem;background:linear-gradient(135deg,var(--royal-700),var(--royal-900));color:var(--white);border:none;border-radius:16px;font-size:1rem;font-weight:700;cursor:pointer;transition:all .2s;box-shadow:0 4px 16px rgba(30,64,175,.3);display:flex;align-items:center;justify-content:center;gap:.625rem}
.btn-cta-lg:active{transform:scale(.98);box-shadow:0 2px 8px rgba(30,64,175,.35)}
.btn-cta-outline{background:var(--white);color:var(--royal-700);border:2px solid var(--royal-700);box-shadow:0 2px 8px rgba(30,64,175,.15)}
.btn-cta-outline:active{background:var(--royal-50)}

/* Stats Cards - Swipeable on Mobile */
.stats-section{padding:0 1rem 2rem;overflow-x:auto;scroll-snap-type:x mandatory;-webkit-overflow-scrolling:touch;scrollbar-width:none}
.stats-section::-webkit-scrollbar{display:none}
.stats-grid{display:flex;gap:1rem;padding:.5rem 0}
.stat-card{min-width:160px;background:var(--white);border-radius:16px;padding:1.25rem;box-shadow:var(--shadow-elegant);scroll-snap-align:start;border:1px solid rgba(30,64,175,.08);text-align:center;flex-shrink:0;transition:all .2s}
.stat-card:active{transform:scale(.98);box-shadow:var(--shadow-hover)}
.stat-icon{width:48px;height:48px;margin:0 auto .75rem;background:linear-gradient(135deg,#dbeafe,#bfdbfe);border-radius:12px;display:flex;align-items:center;justify-content:center;color:var(--royal-700);font-size:1.5rem}
.stat-value{font-size:1.75rem;font-weight:800;color:var(--royal-700);margin-bottom:.25rem}
.stat-label{font-size:.75rem;color:var(--slate-600);font-weight:600;line-height:1.3}

/* Features Grid */
.features-section{padding:3rem 1rem}
.section-header{text-align:center;margin-bottom:2.5rem}
.section-badge{display:inline-flex;align-items:center;gap:.5rem;background:var(--royal-50);color:var(--royal-700);padding:.5rem 1rem;border-radius:100px;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;margin-bottom:.75rem;border:1px solid rgba(30,64,175,.15)}
.section-title{font-size:clamp(1.5rem,4vw,2rem);font-weight:800;color:var(--slate-900);margin-bottom:.75rem;letter-spacing:-.02em}
.section-subtitle{font-size:.9375rem;color:var(--slate-600);max-width:500px;margin:0 auto}
.features-grid{display:grid;gap:1rem}
.feature-card{background:var(--white);border-radius:20px;padding:1.5rem;box-shadow:var(--shadow-elegant);border:1px solid rgba(30,64,175,.08);transition:all .2s}
.feature-card:active{transform:scale(.98);box-shadow:var(--shadow-hover)}
.feature-icon{width:56px;height:56px;background:linear-gradient(135deg,var(--royal-600),var(--royal-700));border-radius:14px;display:flex;align-items:center;justify-content:center;color:var(--white);font-size:1.5rem;margin-bottom:1rem;box-shadow:0 4px 12px rgba(59,130,246,.25)}
.feature-title{font-size:1.0625rem;font-weight:700;color:var(--slate-900);margin-bottom:.5rem;letter-spacing:-.01em}
.feature-desc{font-size:.875rem;color:var(--slate-600);line-height:1.6}

/* How It Works - Timeline Style */
.steps-section{padding:3rem 1rem;background:linear-gradient(180deg,var(--white) 0%,var(--slate-50) 100%)}
.steps-container{max-width:500px;margin:0 auto}
.step-item{position:relative;padding-left:3.5rem;padding-bottom:2rem}
.step-item:last-child{padding-bottom:0}
.step-item::before{content:'';position:absolute;left:1.25rem;top:3.5rem;bottom:0;width:2px;background:linear-gradient(180deg,var(--royal-500),transparent)}
.step-number{position:absolute;left:0;top:0;width:2.5rem;height:2.5rem;background:linear-gradient(135deg,var(--royal-700),var(--royal-900));color:var(--white);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.125rem;font-weight:800;box-shadow:0 4px 12px rgba(30,64,175,.3);z-index:1}
.step-content{background:var(--white);border-radius:16px;padding:1.25rem;box-shadow:var(--shadow-elegant);border:1px solid rgba(30,64,175,.08)}
.step-title{font-size:1rem;font-weight:700;color:var(--slate-900);margin-bottom:.5rem}
.step-desc{font-size:.875rem;color:var(--slate-600);line-height:1.5}

/* CTA Section */
.cta-section{padding:3rem 1rem;background:linear-gradient(135deg,var(--royal-800),var(--royal-900));color:var(--white);text-align:center;position:relative;overflow:hidden}
.cta-section::before{content:'';position:absolute;inset:0;background:radial-gradient(circle at 30% 50%,rgba(96,165,250,.15) 0%,transparent 60%)}
.cta-content{max-width:500px;margin:0 auto}
.cta-title{font-size:clamp(1.5rem,4vw,2rem);font-weight:800;margin-bottom:1rem;letter-spacing:-.02em}
.cta-text{font-size:.9375rem;opacity:.9;margin-bottom:2rem;line-height:1.6}
.cta-features{background:rgba(255,255,255,.1);border-radius:16px;padding:1.25rem;margin-bottom:2rem;backdrop-filter:blur(10px)}
.cta-feature-list{display:grid;gap:.75rem;text-align:left}
.cta-feature-item{display:flex;align-items:center;gap:.75rem;font-size:.875rem;font-weight:600}
.cta-feature-item i{color:#bfdbfe;font-size:1.125rem}
.btn-cta-white{background:var(--white);color:var(--royal-800);padding:1rem;border:none;border-radius:16px;font-size:1rem;font-weight:700;cursor:pointer;box-shadow:0 4px 16px rgba(0,0,0,.2);display:flex;align-items:center;justify-content:center;gap:.625rem;transition:all .2s}
.btn-cta-white:active{transform:scale(.98)}

/* Bottom Navigation - PWA Style */
.bottom-nav{position:fixed;bottom:0;left:0;right:0;height:calc(var(--bottom-nav-height) + var(--safe-area-bottom));background:var(--white);border-top:1px solid rgba(30,64,175,.08);z-index:1000;display:flex;align-items:center;justify-content:space-around;padding:0 .5rem calc(.5rem + var(--safe-area-bottom));box-shadow:0 -2px 16px rgba(30,64,175,.06);backdrop-filter:blur(20px)}
.nav-item{flex:1;display:flex;flex-direction:column;align-items:center;gap:.25rem;padding:.625rem;border-radius:12px;cursor:pointer;transition:all .2s;text-decoration:none}
.nav-item:active{background:var(--slate-100);transform:scale(.95)}
.nav-item.active{background:var(--royal-50)}
.nav-icon{font-size:1.25rem;color:var(--slate-600);transition:color .2s}
.nav-item.active .nav-icon{color:var(--royal-700)}
.nav-label{font-size:.6875rem;font-weight:600;color:var(--slate-600);transition:color .2s}
.nav-item.active .nav-label{color:var(--royal-700)}

/* Modal - Full Screen Mobile */
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:9999;backdrop-filter:blur(8px);animation:fadeIn .3s}
.modal-overlay.active{display:flex;align-items:flex-end}
@keyframes fadeIn{from{opacity:0}to{opacity:1}}
.modal-content{background:var(--white);border-radius:24px 24px 0 0;width:100%;max-height:90vh;overflow-y:auto;padding:2rem 1.5rem calc(2rem + var(--safe-area-bottom));animation:slideUp .3s cubic-bezier(.4,0,.2,1)}
@keyframes slideUp{from{transform:translateY(100%)}to{transform:translateY(0)}}
.modal-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem}
.modal-title{font-size:1.25rem;font-weight:800;color:var(--slate-900)}
.modal-close{width:40px;height:40px;border-radius:12px;border:none;background:var(--slate-100);color:var(--slate-700);display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:1.125rem}
.form-group{margin-bottom:1.25rem}
.form-label{display:flex;align-items:center;gap:.5rem;font-size:.875rem;font-weight:700;color:var(--slate-900);margin-bottom:.5rem}
.form-label i{color:var(--royal-700)}
.form-input{width:100%;padding:.875rem 1rem;border:2px solid var(--slate-200);border-radius:14px;font-size:1rem;font-family:inherit;transition:all .2s;background:var(--white)}
.form-input:focus{outline:none;border-color:var(--royal-600);box-shadow:0 0 0 4px rgba(59,130,246,.12)}
.btn-submit{width:100%;padding:1rem;background:linear-gradient(135deg,var(--royal-700),var(--royal-900));color:var(--white);border:none;border-radius:14px;font-size:1rem;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.625rem;transition:all .2s;box-shadow:0 4px 16px rgba(30,64,175,.3)}
.btn-submit:active{transform:scale(.98)}
.divider{display:flex;align-items:center;gap:1rem;margin:1.5rem 0}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:var(--slate-200)}
.divider-text{font-size:.75rem;color:var(--slate-500);font-weight:600;text-transform:uppercase}

/* Tablet & Desktop Adjustments */
@media(min-width:640px){
.hero-card{padding:3rem 2rem}
.stats-section{padding:0 2rem 3rem}
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem}
.stat-card{min-width:auto}
.features-section,.steps-section{padding:4rem 2rem}
.features-grid{grid-template-columns:repeat(2,1fr);gap:1.5rem}
.cta-section{padding:4rem 2rem}
.bottom-nav{justify-content:center;gap:2rem;padding:0 2rem calc(1rem + var(--safe-area-bottom))}
.nav-item{flex:0 0 auto;min-width:80px}
}
@media(min-width:1024px){
.hero-section{padding:calc(var(--nav-height) + 4rem) 2rem 4rem}
.features-grid{grid-template-columns:repeat(3,1fr)}
.modal-overlay.active{align-items:center;justify-content:center}
.modal-content{border-radius:24px;max-width:500px;max-height:80vh;padding:2.5rem 2rem}
}
</style>
</head>
<body>

<!-- Top Navigation -->
<nav class="top-nav">
<div class="logo-area">
<img src="{{ asset('img/confirmi.png') }}" alt="Confirmi">
<span class="logo-text">Confirmi</span>
</div>
<div class="nav-actions">
<button class="btn-primary-sm" onclick="openModal()">
<i class="fas fa-sign-in-alt"></i> Connexion
</button>
</div>
</nav>

<!-- Hero Section -->
<section class="hero-section">
<div class="hero-card">
<div class="hero-badge">
<i class="fas fa-star"></i>
<span>#1 en Tunisie</span>
</div>
<div class="hero-icon">
<i class="fas fa-rocket"></i>
</div>
<h1 class="hero-title">Gérez vos commandes sans effort</h1>
<p class="hero-subtitle">Automatisez votre confirmation, gérez vos stocks en temps réel et expédiez plus rapidement que jamais.</p>
<div class="hero-cta">
<a href="{{ route('register') }}" class="btn-cta-lg">
<i class="fas fa-play-circle"></i>
<span>Commencer l'essai gratuit</span>
</a>
<button class="btn-cta-lg btn-cta-outline" onclick="openModal()">
<i class="fas fa-sign-in-alt"></i>
<span>Se connecter</span>
</button>
</div>
</div>
</section>

<!-- Stats Cards -->
<section class="stats-section">
<div class="stats-grid">
<div class="stat-card">
<div class="stat-icon"><i class="fas fa-ban"></i></div>
<div class="stat-value">0</div>
<div class="stat-label">Fichiers Excel</div>
</div>
<div class="stat-card">
<div class="stat-icon"><i class="fas fa-bolt"></i></div>
<div class="stat-value">Auto</div>
<div class="stat-label">Import commandes</div>
</div>
<div class="stat-card">
<div class="stat-icon"><i class="fas fa-chart-line"></i></div>
<div class="stat-value">100%</div>
<div class="stat-label">Suivi stocks</div>
</div>
<div class="stat-card">
<div class="stat-icon"><i class="fas fa-infinity"></i></div>
<div class="stat-value">24/7</div>
<div class="stat-label">Temps réel</div>
</div>
</div>
</section>

<!-- Features -->
<section class="features-section" id="features">
<div class="section-header">
<div class="section-badge">
<i class="fas fa-sparkles"></i>
<span>Fonctionnalités</span>
</div>
<h2 class="section-title">Tout ce dont vous avez besoin</h2>
<p class="section-subtitle">Une plateforme complète pour gérer votre e-commerce efficacement</p>
</div>
<div class="features-grid">
<div class="feature-card">
<div class="feature-icon"><i class="fas fa-file-excel"></i></div>
<h3 class="feature-title">Adieu Excel</h3>
<p class="feature-desc">Organisez et traitez vos commandes rapidement sur une interface intuitive.</p>
</div>
<div class="feature-card">
<div class="feature-icon"><i class="fas fa-tachometer-alt"></i></div>
<h3 class="feature-title">Dashboard Global</h3>
<p class="feature-desc">Suivez vos stocks et commandes en temps réel avec des statistiques claires.</p>
</div>
<div class="feature-card">
<div class="feature-icon"><i class="fas fa-globe"></i></div>
<h3 class="feature-title">Intégrations</h3>
<p class="feature-desc">Connectez Shopify, WooCommerce et récupérez vos commandes automatiquement.</p>
</div>
<div class="feature-card">
<div class="feature-icon"><i class="fas fa-headset"></i></div>
<h3 class="feature-title">Confirmation Rapide</h3>
<p class="feature-desc">Interface focalisée pour augmenter la productivité de votre équipe.</p>
</div>
<div class="feature-card">
<div class="feature-icon"><i class="fas fa-users-cog"></i></div>
<h3 class="feature-title">Multi-utilisateurs</h3>
<p class="feature-desc">Séparez les rôles et assignez facilement les tâches à votre équipe.</p>
</div>
<div class="feature-card">
<div class="feature-icon"><i class="fas fa-shipping-fast"></i></div>
<h3 class="feature-title">Expédition Auto</h3>
<p class="feature-desc">Transfert automatique vers Masafa Express après confirmation.</p>
</div>
</div>
</section>

<!-- How It Works -->
<section class="steps-section" id="how">
<div class="section-header">
<div class="section-badge">
<i class="fas fa-lightbulb"></i>
<span>Comment ça marche</span>
</div>
<h2 class="section-title">Simple et rapide</h2>
<p class="section-subtitle">Démarrez en 4 étapes faciles</p>
</div>
<div class="steps-container">
<div class="step-item">
<div class="step-number">1</div>
<div class="step-content">
<h3 class="step-title">Connectez votre boutique</h3>
<p class="step-desc">Intégrez votre site e-commerce pour l'import automatique des commandes.</p>
</div>
</div>
<div class="step-item">
<div class="step-number">2</div>
<div class="step-content">
<h3 class="step-title">Organisez l'équipe</h3>
<p class="step-desc">Créez des accès et répartissez le travail sans fichiers Excel.</p>
</div>
</div>
<div class="step-item">
<div class="step-number">3</div>
<div class="step-content">
<h3 class="step-title">Confirmez rapidement</h3>
<p class="step-desc">Utilisez le poste de traitement pour confirmer en un clic.</p>
</div>
</div>
<div class="step-item">
<div class="step-number">4</div>
<div class="step-content">
<h3 class="step-title">Expédiez automatiquement</h3>
<p class="step-desc">Envoi direct à Masafa Express après confirmation.</p>
</div>
</div>
</div>
</section>

<!-- CTA -->
<section class="cta-section">
<div class="cta-content">
<h2 class="cta-title">Prêt à démarrer ?</h2>
<p class="cta-text">Rejoignez les e-commerçants qui ont abandonné Excel pour Confirmi.</p>
<div class="cta-features">
<div class="cta-feature-list">
<div class="cta-feature-item">
<i class="fas fa-check-circle"></i>
<span>Essai gratuit 14 jours</span>
</div>
<div class="cta-feature-item">
<i class="fas fa-check-circle"></i>
<span>Accès complet sans CB</span>
</div>
<div class="cta-feature-item">
<i class="fas fa-check-circle"></i>
<span>Support prioritaire</span>
</div>
<div class="cta-feature-item">
<i class="fas fa-check-circle"></i>
<span>Sans engagement</span>
</div>
</div>
</div>
<a href="{{ route('register') }}" class="btn-cta-white">
<i class="fas fa-rocket"></i>
<span>Commencer maintenant</span>
</a>
</div>
</section>

<!-- Bottom Navigation -->
<nav class="bottom-nav">
<a href="#" class="nav-item active">
<i class="fas fa-home nav-icon"></i>
<span class="nav-label">Accueil</span>
</a>
<a href="#features" class="nav-item">
<i class="fas fa-star nav-icon"></i>
<span class="nav-label">Features</span>
</a>
<a href="#how" class="nav-item">
<i class="fas fa-question-circle nav-icon"></i>
<span class="nav-label">Comment</span>
</a>
<a href="{{ route('register') }}" class="nav-item">
<i class="fas fa-user-plus nav-icon"></i>
<span class="nav-label">Inscription</span>
</a>
</nav>

<!-- Login Modal -->
<div class="modal-overlay" id="loginModal" onclick="closeOnOverlay(event)">
<div class="modal-content" onclick="event.stopPropagation()">
<div class="modal-header">
<h2 class="modal-title">Connexion</h2>
<button class="modal-close" onclick="closeModal()">
<i class="fas fa-times"></i>
</button>
</div>
@if(session('error'))
<div style="background:#fef2f2;color:#991b1b;border:2px solid #fecaca;padding:.875rem 1rem;border-radius:12px;font-size:.875rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:.625rem;font-weight:600">
<i class="fas fa-exclamation-circle"></i>
<span>{{ session('error') }}</span>
</div>
@endif
<form method="POST" action="{{ route('confirmi.login.submit') }}">
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
<div style="display:flex;align-items:center;gap:.625rem;margin-bottom:1.5rem">
<input type="checkbox" name="remember" id="remember" style="width:1.125rem;height:1.125rem;border-radius:6px;cursor:pointer;accent-color:var(--royal-700)">
<label for="remember" style="font-size:.875rem;color:var(--slate-600);cursor:pointer">Se souvenir de moi</label>
</div>
<button type="submit" class="btn-submit">
<i class="fas fa-sign-in-alt"></i>
<span>Se connecter</span>
</button>
</form>
<div class="divider">
<span class="divider-text">ou</span>
</div>
<a href="{{ route('register') }}" style="display:flex;align-items:center;justify-content:center;gap:.625rem;padding:1rem;background:var(--royal-50);color:var(--royal-700);border-radius:14px;font-weight:700;text-decoration:none;transition:all .2s">
<i class="fas fa-user-plus"></i>
<span>Créer un compte</span>
</a>
</div>
</div>

<script>
function openModal(){
const modal=document.getElementById('loginModal');
modal.classList.add('active');
document.body.style.overflow='hidden';
}
function closeModal(){
const modal=document.getElementById('loginModal');
modal.classList.remove('active');
document.body.style.overflow='';
}
function closeOnOverlay(e){
if(e.target.id==='loginModal')closeModal();
}
document.addEventListener('keydown',e=>{if(e.key==='Escape')closeModal()});
@if(session('error') || $errors->any() || request('login'))
document.addEventListener('DOMContentLoaded',openModal);
@endif

// Smooth scroll for nav items
document.querySelectorAll('a[href^="#"]').forEach(anchor=>{
anchor.addEventListener('click',function(e){
e.preventDefault();
const target=document.querySelector(this.getAttribute('href'));
if(target){
target.scrollIntoView({behavior:'smooth',block:'start'});
}
});
});

// Active nav item on scroll
const navItems=document.querySelectorAll('.bottom-nav .nav-item');
const sections=document.querySelectorAll('section[id]');
window.addEventListener('scroll',()=>{
let current='';
sections.forEach(section=>{
const sectionTop=section.offsetTop-100;
if(scrollY>=sectionTop){
current=section.getAttribute('id');
}
});
navItems.forEach(item=>{
item.classList.remove('active');
if(item.getAttribute('href')==='#'+current){
item.classList.add('active');
}
});
});
</script>
</body>
</html>
