<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Confirmi — La plateforme de confirmation #1 en Tunisie</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root{--b950:#0a1628;--b900:#0f2249;--b800:#1e3a8a;--b700:#1d4ed8;--b600:#2563eb;--b500:#3b82f6;--b400:#60a5fa;--b50:#eff6ff;--gold:#f59e0b;--text:#0f172a;--muted:#64748b}
*{margin:0;padding:0;box-sizing:border-box}html{scroll-behavior:smooth}
body{font-family:'Inter',sans-serif;color:var(--text);overflow-x:hidden}
/* NAV */
.nav{position:fixed;top:0;left:0;right:0;z-index:100;height:66px;display:flex;align-items:center;justify-content:space-between;padding:0 5%;background:rgba(10,22,40,.93);backdrop-filter:blur(14px);border-bottom:1px solid rgba(255,255,255,.07)}
.nav-brand{display:flex;align-items:center;gap:10px;text-decoration:none}
.nav-brand img{height:34px}.nav-brand span{color:#fff;font-size:1.2rem;font-weight:800;letter-spacing:-.5px}
.nav-links{display:flex;gap:1.75rem}
.nav-links a{color:rgba(255,255,255,.7);text-decoration:none;font-size:.85rem;font-weight:500;transition:color .2s}
.nav-links a:hover{color:#fff}
.btn-nav{padding:.48rem 1.2rem;background:var(--b600);color:#fff;border:none;border-radius:8px;font:.875rem/1 'Inter',sans-serif;font-weight:600;cursor:pointer;transition:background .2s}
.btn-nav:hover{background:var(--b700)}
@media(max-width:768px){.nav-links{display:none}.hero-cta{flex-direction:column;align-items:center}.hero-cta .btn-white,.hero-cta .btn-outline{width:100%;max-width:280px}}
/* HERO */
.hero{min-height:100vh;background:linear-gradient(140deg,var(--b950) 0%,var(--b900) 35%,var(--b800) 70%,#1d4ed8 100%);display:flex;align-items:center;justify-content:center;text-align:center;padding:130px 5% 90px;position:relative;overflow:hidden}
.hero::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse at 25% 60%,rgba(37,99,235,.35) 0%,transparent 55%),radial-gradient(ellipse at 80% 20%,rgba(96,165,250,.2) 0%,transparent 50%);pointer-events:none}
.hero-grid{position:absolute;inset:0;opacity:.035;background-image:linear-gradient(rgba(255,255,255,.6) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.6) 1px,transparent 1px);background-size:48px 48px}
.hero-inner{position:relative;z-index:1;max-width:820px;margin:0 auto}
.hero-badge{display:inline-flex;align-items:center;gap:8px;background:rgba(245,158,11,.15);border:1px solid rgba(245,158,11,.45);color:var(--gold);padding:6px 16px;border-radius:50px;font-size:.7rem;font-weight:700;letter-spacing:.6px;text-transform:uppercase;margin-bottom:1.5rem}
.hero-logo-wrap{width:94px;height:94px;margin:0 auto 1.5rem;background:rgba(255, 255, 255, 1);border-radius:24px;display:flex;align-items:center;justify-content:center;padding:12px;backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.15);box-shadow:0 8px 32px rgba(0,0,0,.3)}
.hero-logo-wrap img{width:100%;height:100%;object-fit:contain}
.hero h1{font-size:clamp(2rem,5.5vw,3.6rem);font-weight:900;color:#fff;line-height:1.13;letter-spacing:-1.5px;margin-bottom:1.25rem}
.hero h1 .acc{color:var(--b400)}
.hero-sub{font-size:clamp(.9rem,2vw,1.08rem);color:rgba(255,255,255,.72);max-width:600px;margin:0 auto 2.5rem;line-height:1.78}
.hero-cta{display:flex;gap:1rem;justify-content:center;flex-wrap:wrap}
.btn-white{padding:.875rem 2.25rem;background:#fff;color:var(--b800);border:none;border-radius:10px;font:700 1rem/1 'Inter',sans-serif;cursor:pointer;transition:transform .2s,box-shadow .2s;box-shadow:0 4px 20px rgba(0,0,0,.3)}
.btn-white:hover{transform:translateY(-2px);box-shadow:0 8px 30px rgba(0,0,0,.4)}
.btn-outline{padding:.875rem 2.25rem;background:rgba(255,255,255,.1);color:#fff;border:1px solid rgba(255,255,255,.28);border-radius:10px;font:600 1rem/1 'Inter',sans-serif;cursor:pointer;transition:background .2s;text-decoration:none;display:inline-flex;align-items:center;gap:8px}
.btn-outline:hover{background:rgba(255,255,255,.18);color:#fff}
.hero-bounce{position:absolute;bottom:26px;left:50%;transform:translateX(-50%);color:rgba(255,255,255,.4);font-size:.7rem;display:flex;flex-direction:column;align-items:center;gap:5px;animation:boc 2s infinite}
@keyframes boc{0%,100%{transform:translateX(-50%) translateY(0)}50%{transform:translateX(-50%) translateY(7px)}}
@keyframes pulse{0%,100%{transform:scale(1);opacity:1}50%{transform:scale(1.05);opacity:.9}}
@keyframes bounce{0%,100%{transform:translateY(0)}25%{transform:translateY(-5px)}75%{transform:translateY(-3px)}}
@keyframes rotate{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}
@keyframes shimmer{0%{transform:translateX(-100%)}100%{transform:translateX(100%)}}
/* STATS */
.stats{background:var(--b800);padding:2.25rem 5%}
.stats-grid{max-width:1100px;margin:0 auto;display:grid;grid-template-columns:repeat(4,1fr)}
.stat{text-align:center;padding:.875rem;border-right:1px solid rgba(255,255,255,.1)}
.stat:last-child{border-right:none}
.stat-n{font-size:2rem;font-weight:900;color:#fff;display:block}
.stat-l{font-size:.76rem;color:rgba(255,255,255,.6);margin-top:.2rem}
@media(max-width:600px){.stats-grid{grid-template-columns:repeat(2,1fr)}.stat:nth-child(2){border-right:none}}
/* LAYOUT HELPERS */
.section{padding:78px 5%}
.wrap{max-width:1100px;margin:0 auto}
.s-lbl{text-align:center;font-size:.7rem;font-weight:700;color:var(--b600);letter-spacing:1.5px;text-transform:uppercase;margin-bottom:.55rem}
.s-title{text-align:center;font-size:clamp(1.4rem,3.5vw,2.2rem);font-weight:800;color:var(--text);letter-spacing:-.75px;margin-bottom:.7rem}
.s-sub{text-align:center;max-width:560px;margin:0 auto 3rem;color:var(--muted);font-size:.95rem;line-height:1.72}
/* BENEFITS */
.benefits-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(295px,1fr));gap:1.4rem}
.bc{background:#fff;border-radius:16px;padding:1.75rem;border:1.5px solid #e2e8f0;transition:transform .2s,box-shadow .2s,border-color .2s}
.bc:hover{transform:translateY(-4px);box-shadow:0 12px 40px rgba(37,99,235,.1);border-color:var(--b500)}
.bi{width:50px;height:50px;border-radius:13px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;margin-bottom:.9rem}
.bi.bl{background:var(--b50);color:var(--b600)}.bi.gr{background:#f0fdf4;color:#16a34a}
.bi.am{background:#fffbeb;color:#d97706}.bi.pu{background:#f5f3ff;color:#7c3aed}
.bi.ro{background:#fff1f2;color:#e11d48}.bi.te{background:#f0fdfa;color:#0d9488}
.bc h3{font-size:.95rem;font-weight:700;color:var(--text);margin-bottom:.42rem}
.bc p{font-size:.845rem;color:var(--muted);line-height:1.65}
/* HOW */
.how-bg{background:var(--b50)}
.steps{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:2rem;max-width:900px;margin:0 auto}
.step{text-align:center}
.step-n{width:54px;height:54px;border-radius:50%;background:var(--b600);color:#fff;font-size:1.2rem;font-weight:900;display:flex;align-items:center;justify-content:center;margin:0 auto .9rem;box-shadow:0 4px 16px rgba(37,99,235,.35)}
.step h3{font-size:.93rem;font-weight:700;color:var(--text);margin-bottom:.38rem}
.step p{font-size:.82rem;color:var(--muted);line-height:1.62}
/* TUNISIA */
.tun{background:linear-gradient(140deg,var(--b950),var(--b800));position:relative;overflow:hidden}
.tun::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse at 80% 50%,rgba(59,130,246,.2),transparent 60%);pointer-events:none}
.tun-inner{display:grid;grid-template-columns:1fr 1fr;gap:4rem;align-items:center;position:relative;z-index:1}
@media(max-width:768px){.tun-inner{grid-template-columns:1fr}.tun-vis{display:none!important}}
.t-tag{display:inline-flex;align-items:center;gap:8px;background:rgba(245,158,11,.15);border:1px solid rgba(245,158,11,.38);color:var(--gold);padding:5px 14px;border-radius:50px;font-size:.7rem;font-weight:700;letter-spacing:.8px;text-transform:uppercase;margin-bottom:1.2rem}
.tun-inner h2{font-size:clamp(1.35rem,3vw,2rem);font-weight:800;color:#fff;line-height:1.3;margin-bottom:.9rem}
.tun-inner .txt{color:rgba(255,255,255,.7);line-height:1.77;margin-bottom:1.5rem;font-size:.92rem}
.chk{list-style:none}
.chk li{display:flex;align-items:flex-start;gap:10px;color:rgba(255,255,255,.85);font-size:.875rem;margin-bottom:.52rem}
.chk li i{color:var(--b400);margin-top:2px;flex-shrink:0}
.tun-vis{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
.vc{background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.1);border-radius:14px;padding:1.25rem;text-align:center;backdrop-filter:blur(8px)}
.vc .vn{font-size:1.7rem;font-weight:900;color:#fff}.vc .vl{font-size:.7rem;color:rgba(255,255,255,.55);margin-top:3px}
.vc .vi{font-size:1.35rem;color:var(--b400);margin-bottom:.35rem}
/* ROLES */
.roles-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(235px,1fr));gap:1.4rem}
.rc{border-radius:16px;padding:1.9rem;text-align:center;border:2px solid transparent;transition:transform .2s}
.rc:hover{transform:translateY(-4px)}
.rc.sa{background:linear-gradient(135deg,#0a1628,#1e3a8a);border-color:#3b82f6}
.rc.co{background:linear-gradient(135deg,#065f46,#059669);border-color:#34d399}
.rc.em{background:linear-gradient(135deg,#4c1d95,#7c3aed);border-color:#a78bfa}
.rc.ad{background:linear-gradient(135deg,#78350f,#d97706);border-color:#fbbf24}
.ri{width:58px;height:58px;border-radius:50%;background:rgba(255,255,255,.12);display:flex;align-items:center;justify-content:center;font-size:1.35rem;color:#fff;margin:0 auto .9rem}
.rc h3{color:#fff;font-size:.98rem;font-weight:700;margin-bottom:.38rem}
.rc p{color:rgba(255,255,255,.68);font-size:.81rem;line-height:1.6}
.r-badge{display:inline-block;margin-top:.7rem;padding:3px 10px;border-radius:50px;background:rgba(255,255,255,.15);color:rgba(255,255,255,.9);font-size:.68rem;font-weight:600}
/* CTA */
.cta-bg{background:var(--b600);text-align:center;padding:76px 5%}
.cta-bg h2{font-size:clamp(1.5rem,3.5vw,2.2rem);font-weight:900;color:#fff;letter-spacing:-.75px;margin-bottom:.9rem}
.cta-bg p{color:rgba(255,255,255,.8);max-width:480px;margin:0 auto 2rem;font-size:.95rem}
.btn-cta{padding:.875rem 2.25rem;background:#fff;color:var(--b700);border:none;border-radius:10px;font:700 1rem/1 'Inter',sans-serif;cursor:pointer;transition:transform .2s,box-shadow .2s;box-shadow:0 4px 16px rgba(0,0,0,.2)}
.btn-cta:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,0,0,.25)}
/* FOOTER */
footer{background:var(--b950);padding:2.5rem 5% 1.5rem}
.ft-inner{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;border-bottom:1px solid rgba(255,255,255,.08);padding-bottom:1.5rem;margin-bottom:1.25rem}
.ft-brand{display:flex;align-items:center;gap:10px}
.ft-brand img{height:30px}.ft-brand span{color:#fff;font-weight:800;font-size:1rem}
.ft-links{display:flex;gap:1.5rem}
.ft-links a{color:rgba(255,255,255,.45);font-size:.77rem;text-decoration:none;transition:color .2s}
.ft-links a:hover{color:rgba(255,255,255,.9)}
.ft-copy{text-align:center;color:rgba(255,255,255,.3);font-size:.71rem}
/* MODAL */
.mOverlay{display:none;position:fixed;inset:0;z-index:9999;background:rgba(5,10,20,.78);backdrop-filter:blur(7px);align-items:center;justify-content:center;padding:1rem}
.mOverlay.open{display:flex}
.mBox{background:#fff;border-radius:20px;width:100%;max-width:420px;box-shadow:0 32px 80px rgba(0,0,0,.4);overflow:hidden;animation:mIn .25s ease}
@keyframes mIn{from{transform:scale(.94) translateY(10px);opacity:0}to{transform:scale(1) translateY(0);opacity:1}}
.mHead{background:linear-gradient(135deg,var(--b950),var(--b800));padding:1.75rem 2rem 1.5rem;text-align:center;position:relative}
.mHead img{height:42px;margin-bottom:.65rem}
.mHead h2{color:#fff;font-size:1.15rem;font-weight:800;margin-bottom:.2rem}
.mHead p{color:rgba(255,255,255,.62);font-size:.77rem}
.mClose{position:absolute;right:12px;top:12px;background:rgba(255,255,255,.15);border:none;color:#fff;width:28px;height:28px;border-radius:50%;cursor:pointer;font-size:.8rem;display:flex;align-items:center;justify-content:center}
.mBody{padding:1.75rem 2rem}
.fg{margin-bottom:1rem}
.fg label{display:block;font-size:.76rem;font-weight:600;color:#334155;margin-bottom:.3rem}
.iw{position:relative}
.iw i{position:absolute;left:.85rem;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:.82rem}
.iw input{width:100%;padding:.68rem .85rem .68rem 2.35rem;border:1.5px solid #e2e8f0;border-radius:10px;font:.875rem/1 'Inter',sans-serif;transition:border-color .2s,box-shadow .2s;outline:none}
.iw input:focus{border-color:var(--b500);box-shadow:0 0 0 3px rgba(37,99,235,.1)}
.rem-row{display:flex;align-items:center;gap:8px;margin-bottom:1.2rem}
.rem-row label{font-size:.76rem;color:#64748b;cursor:pointer;display:flex;align-items:center;gap:6px}
.btn-mlogin{width:100%;padding:.78rem;background:linear-gradient(135deg,var(--b800),var(--b600));color:#fff;border:none;border-radius:10px;font:700 .95rem/1 'Inter',sans-serif;cursor:pointer;transition:opacity .2s}
.btn-mlogin:hover{opacity:.9}
.m-err{background:#fef2f2;color:#991b1b;border:1px solid #fecaca;padding:.58rem .85rem;border-radius:8px;font-size:.76rem;margin-bottom:.9rem;display:flex;align-items:center;gap:6px}
</style>
</head>
<body>

<!-- NAV -->
<nav class="nav">
    <a href="{{ route('confirmi.home') }}" class="nav-brand">
        <img src="{{ asset('img/confirmi.png') }}" alt="Confirmi">
    </a>
    <div class="nav-links">
        <a href="#benefices">Bénéfices</a>
        <a href="#comment">Comment ça marche</a>
        <a href="#tunisie">À propos</a>
    </div>
    <div style="display:flex;gap:0.75rem;align-items:center;">
        <a href="{{ route('register') }}" class="btn-nav" style="background:transparent;border:1px solid rgba(255,255,255,.3);">
            <i class="fas fa-user-plus" style="margin-right:6px;"></i>S'inscrire
        </a>
        <button class="btn-nav" onclick="openModal()">
            <i class="fas fa-sign-in-alt" style="margin-right:6px;"></i>Se connecter
        </button>
    </div>
</nav>

<!-- HERO -->
<section class="hero">
    <div class="hero-grid"></div>
    <div class="hero-inner">
        <div class="hero-badge"><i class="fas fa-star"></i> Première plateforme de confirmation en Tunisie</div>
        <div class="hero-badge" style="background:linear-gradient(135deg,rgba(59,130,246,.2),rgba(37,99,235,.25));border:3px solid #ffffffff;color:#ffffff;margin-top:1rem;padding:12px 24px;font-size:.85rem;font-weight:800;letter-spacing:1px;box-shadow:0 8px 32px rgba(255, 255, 255, 1);animation:pulse 2s infinite;position:relative;overflow:hidden;">
            <div style="position:absolute;top:0;left:0;width:100%;height:100%;background:linear-gradient(90deg,transparent,rgba(255,255,255,.2),transparent);animation:shimmer 3s infinite;"></div>
            <i class="fas fa-rocket" style="margin-right:8px;position:relative;z-index:1;"></i> 
            <span style="position:relative;z-index:1;">ESSAI GRATUIT 14 JOURS</span>
            <span style="margin-left:8px;position:relative;z-index:1;">•</span>
            <span style="margin-left:8px;position:relative;z-index:1;">Accès complet</span>
            <span style="margin-left:8px;position:relative;z-index:1;">•</span>
            <span style="margin-left:8px;position:relative;z-index:1;">Sans engagement</span>
        </div>
        <div class="hero-logo-wrap">
            <img src="{{ asset('img/confirmi.png') }}" alt="Confirmi">
        </div>
        <h1>Centralisez. Confirmez.<br><span class="acc">Livrez sans effort.</span></h1>
        <p class="hero-sub">
            Débarrassez-vous d'Excel et des feuilles volantes ! Confirmi est la plateforme tout-en-un pour synchroniser vos boutiques internationales, gérer vos stocks et confirmer vos commandes à la vitesse de l'éclair.
        </p>
        <div class="hero-cta">
            <a href="{{ route('register') }}" class="btn-white" style="text-decoration:none;position:relative;overflow:hidden;background:linear-gradient(135deg,#fff,#f8fafc);border:2px solid #3b82f6;box-shadow:0 8px 32px rgba(59,130,246,.2);">
                <div style="position:absolute;top:0;left:0;width:100%;height:100%;background:linear-gradient(90deg,transparent,rgba(59,130,246,.05),transparent);animation:shimmer 3s infinite;"></div>
                <i class="fas fa-play-circle" style="margin-right:10px;color:#3b82f6;position:relative;z-index:1;"></i>
                <span style="color:#1e40af;font-weight:800;font-size:1.05rem;position:relative;z-index:1;">Commencer mon essai gratuit</span>
            </a>
            <button class="btn-outline" onclick="openModal()">
                <i class="fas fa-sign-in-alt"></i> Se connecter
            </button>
            <a href="#benefices" class="btn-outline">
                <i class="fas fa-play-circle"></i> Découvrir les avantages
            </a>
        </div>
    </div>
    <div class="hero-bounce"><i class="fas fa-chevron-down"></i></div>
</section>

<!-- STATS -->
<div class="stats">
    <div class="stats-grid">
        <div class="stat"><span class="stat-n">Zero</span><div class="stat-l">Fichiers Excel / Papiers</div></div>
        <div class="stat"><span class="stat-n">Auto</span><div class="stat-l">Import depuis vos boutiques</div></div>
        <div class="stat"><span class="stat-n">100%</span><div class="stat-l">Suivi des stocks & commandes</div></div>
        <div class="stat"><span class="stat-n">24/7</span><div class="stat-l">Tableau de bord temps réel</div></div>
    </div>
</div>

<!-- BENEFITS -->
<section class="section" id="benefices">
    <div class="wrap">
        <div class="s-lbl">Pourquoi choisir Confirmi ?</div>
        <h2 class="s-title">Tout ce dont vous avez besoin pour exploser vos ventes</h2>
        <p class="s-sub">Une suite complète d'outils puissants conçus pour vous faire gagner un temps précieux et éliminer les erreurs humaines.</p>
        <div class="benefits-grid">
                        <div class="bc">
                <div class="bi ro"><i class="fas fa-file-excel"></i></div>
                <h3>Adieu Excel et Google Sheets</h3>
                <p>Oubliez les tableurs interminables et les notes papier. Organisez, triez et traitez toutes vos commandes beaucoup plus rapidement sur une interface de traitement dédiée et intuitive.</p>
            </div>
            <div class="bc">
                <div class="bi pu"><i class="fas fa-tachometer-alt"></i></div>
                <h3>Dashboard de Suivi Global</h3>
                <p>Un tableau de bord centralisé pour suivre vos commandes et gérer l'état de vos stocks en temps réel. Prenez les meilleures décisions grâce à des statistiques claires et précises.</p>
            </div>
            <div class="bc">
                <div class="bi gr"><i class="fas fa-globe"></i></div>
                <h3>Intégrations Internationales</h3>
                <p>Connectez Confirmi à vos plateformes e-commerce préférées (Shopify, WooCommerce, etc.) pour récupérer vos nouvelles commandes de manière automatique et instantanée.</p>
            </div>
            <div class="bc">
                <div class="bi bl"><i class="fas fa-headset"></i></div>
                <h3>Confirmation Téléphonique Rapide</h3>
                <p>Notre interface focalisée ("Poste de traitement") permet à vos employés de procéder à la confirmation des commandes de façon fluide et sans distractions, augmentant drastiquement la productivité.</p>
            </div>
            <div class="bc">
                <div class="bi am"><i class="fas fa-users-cog"></i></div>
                <h3>Travail d'Équipe & Multi-comptes</h3>
                <p>Séparez intelligemment les rôles (Admin, Commercial, Employé). Assignez facilement des listes de commandes aux membres de votre équipe pour un suivi sans faille.</p>
            </div>
            <div class="bc">
                <div class="bi te"><i class="fas fa-shipping-fast"></i></div>
                <h3>Expédition Directe Masafa Express</h3>
                <p>Une fois qu'une commande est confirmée par téléphone, elle est automatiquement transférée vers Masafa Express pour l'expédition. Un processus zéro clic !</p>
            </div>
        </div>
    </div>
</section>

<!-- HOW IT WORKS -->
<section class="section how-bg" id="comment">
    <div class="wrap">
        <div class="s-lbl">Comment ça marche</div>
        <h2 class="s-title">Simplifiez votre quotidien en 4 étapes</h2>
        <p class="s-sub">Passez d'une gestion manuelle chaotique à un système automatisé et fluide en quelques minutes.</p>
        <div class="steps">
            <div class="step">
                <div class="step-n">1</div>
                <h3>Connectez votre boutique</h3>
                <p>Intégrez Confirmi à votre site e-commerce (Shopify, WooCommerce...) pour l'import automatique de toutes vos commandes.</p>
            </div>
            <div class="step">
                <div class="step-n">2</div>
                <h3>Organisez votre équipe</h3>
                <p>Créez des accès pour vos employés et commerciaux. Répartissez le travail de confirmation sans aucun fichier Excel.</p>
            </div>
            <div class="step">
                <div class="step-n">3</div>
                <h3>Confirmation ultra-rapide</h3>
                <p>Vos employés utilisent le "Poste de traitement" pour appeler les clients et mettre à jour les statuts en un seul clic.</p>
            </div>
            <div class="step">
                <div class="step-n">4</div>
                <h3>Expédition automatisée</h3>
                <p>Dès qu'une commande est confirmée, elle est envoyée directement à Masafa Express. Suivez tout depuis le dashboard !</p>
            </div>
        </div>
    </div>
</section>

<!-- TUNISIA FIRST -->
<section class="section tun" id="tunisie">
    <div class="wrap">
        <div class="tun-inner">
            <div>
                <div class="t-tag"><i class="fas fa-flag"></i> Fierté Tunisienne</div>
                <h2>Conçu par des Tunisiens, pour le marché Tunisien</h2>
                <p class="txt">Nous connaissons les réalités du e-commerce en Tunisie : commandes annulées à la dernière minute, clients injoignables, et gestion des stocks compliquée sur Excel. Confirmi est la solution locale ultime pour ces défis.</p>
                <ul class="chk">
                    <li><i class="fas fa-check-circle"></i> Fini la paperasse et les Google Sheets qui se perdent</li>
                    <li><i class="fas fa-check-circle"></i> Intégration parfaite avec les transporteurs tunisiens</li>
                    <li><i class="fas fa-check-circle"></i> Support client local, réactif et à votre écoute</li>
                    <li><i class="fas fa-check-circle"></i> Gestion centralisée multi-boutiques et multi-utilisateurs</li>
                    <li><i class="fas fa-check-circle"></i> Hébergement sécurisé et confidentialité de vos données</li>
                </ul>
            </div>
            <div class="tun-vis">
                <div class="vc"><div class="vi"><i class="fas fa-rocket"></i></div><div class="vn">x3</div><div class="vl">Plus rapide qu'Excel</div></div>
                <div class="vc"><div class="vi"><i class="fas fa-chart-line"></i></div><div class="vn">95%</div><div class="vl">Taux de livraison</div></div>
                <div class="vc"><div class="vi"><i class="fas fa-boxes"></i></div><div class="vn">Live</div><div class="vl">Suivi des stocks</div></div>
                <div class="vc"><div class="vi"><i class="fas fa-globe"></i></div><div class="vn">API</div><div class="vl">Intégrations globales</div></div>
            </div>
        </div>
    </div>
</section>



<!-- CTA FINAL -->
<section class="cta-bg">
    <div class="wrap">
        <h2>Prêt à révolutionner votre e-commerce ?</h2>
        <p style="font-size: 1.1rem; margin-bottom: 1.5rem;">Abandonnez définitivement Excel. Automatisez vos imports depuis Shopify ou WooCommerce, suivez votre stock en direct et laissez votre équipe confirmer plus de commandes en un temps record.</p>
        <div style="background:linear-gradient(135deg,rgba(59,130,246,.1),rgba(37,99,235,.15));border:2px solid #3b82f6;border-radius:16px;padding:2rem 2.5rem;margin-bottom:2.5rem;text-align:center;position:relative;">
            <div style="color:#1e40af;font-weight:800;font-size:1.3rem;margin-bottom:1rem;">
                <i class="fas fa-rocket" style="margin-right:10px;"></i>Démarrez avec un essai professionnel
            </div>
            <div style="color:#1e293b;font-size:1.1rem;line-height:1.7;margin-bottom:1.5rem;">
                Testez <strong>toutes les fonctionnalités</strong> pendant 14 jours • 
                <strong>Accès complet</strong> • 
                <strong>Support dédié</strong> • 
                <strong>Annulation flexible</strong>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:1rem;margin-top:1.5rem;">
                <div style="display:flex;align-items:center;gap:8px;color:#475569;font-size:.9rem;">
                    <i class="fas fa-check-circle" style="color:#3b82f6;"></i>
                    <span>Intégrations complètes</span>
                </div>
                <div style="display:flex;align-items:center;gap:8px;color:#475569;font-size:.9rem;">
                    <i class="fas fa-check-circle" style="color:#3b82f6;"></i>
                    <span>Multi-utilisateurs</span>
                </div>
                <div style="display:flex;align-items:center;gap:8px;color:#475569;font-size:.9rem;">
                    <i class="fas fa-check-circle" style="color:#3b82f6;"></i>
                    <span>Support prioritaire</span>
                </div>
                <div style="display:flex;align-items:center;gap:8px;color:#475569;font-size:.9rem;">
                    <i class="fas fa-check-circle" style="color:#3b82f6;"></i>
                    <span>Sans engagement</span>
                </div>
            </div>
        </div>
        <a href="{{ route('register') }}" class="btn-cta" style="text-decoration:none;background:linear-gradient(135deg,#3b82f6,#2563eb);font-size:1.15rem;padding:1.1rem 3rem;box-shadow:0 8px 32px rgba(59,130,246,.3);border:2px solid #1e40af;">
            <i class="fas fa-play-circle" style="margin-right:12px;"></i><strong>Commencer mon essai gratuit</strong>
        </a>
    </div>
</section>

<!-- FOOTER -->
<footer>
    <div class="ft-inner">
        <div class="ft-brand">
            <img src="{{ asset('img/confirmi.png') }}" alt="Confirmi">
        </div>
        <div class="ft-links">
            <a href="#benefices">Bénéfices</a>
            <a href="#comment">Comment ça marche</a>
            <a href="#tunisie">À propos</a>
        </div>
    </div>
    <p class="ft-copy">© {{ date('Y') }} Confirmi — La plateforme de confirmation #1 en Tunisie. Tous droits réservés.</p>
</footer>

<!-- LOGIN MODAL -->
<div class="mOverlay" id="loginModal" onclick="closeOnOverlay(event)">
    <div class="mBox">
        <div class="mHead">
            <button class="mClose" onclick="closeModal()"><i class="fas fa-times"></i></button>
            <img src="{{ asset('img/confirmi.png') }}" alt="Confirmi">
            <h2>Connexion</h2>
            <p>Accès unifié — Commerciaux, Employés, Admins</p>
        </div>
        <div class="mBody">
            @if(session('error'))
                <div class="m-err"><i class="fas fa-exclamation-circle"></i>{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="m-err"><i class="fas fa-exclamation-circle"></i>{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('confirmi.login.submit') }}">
                @csrf
                <div class="fg">
                    <label for="email">Adresse email</label>
                    <div class="iw">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                               placeholder="votre@email.com" required autofocus>
                    </div>
                </div>
                <div class="fg">
                    <label for="password">Mot de passe</label>
                    <div class="iw">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password"
                               placeholder="Votre mot de passe" required>
                    </div>
                </div>
                <div class="rem-row">
                    <label><input type="checkbox" name="remember" style="accent-color:var(--b600)"> Se souvenir de moi</label>
                </div>
                <button type="submit" class="btn-mlogin">
                    <i class="fas fa-sign-in-alt" style="margin-right:8px;"></i>Se connecter
                </button>
            </form>
            <div style="text-align:center;margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid #e2e8f0;">
                <div style="background:linear-gradient(135deg,#eff6ff,#dbeafe);border:2px solid #3b82f6;border-radius:12px;padding:1.2rem;margin-bottom:1rem;position:relative;">
                    <div style="color:#1e40af;font-weight:800;font-size:.9rem;margin-bottom:.4rem;">
                        <i class="fas fa-rocket" style="margin-right:8px;"></i>Essai professionnel 14 jours
                    </div>
                    <div style="color:#3730a3;font-size:.8rem;font-weight:500;">Accès complet • Support prioritaire • Sans engagement</div>
                </div>
                <p style="font-size:.78rem;color:#64748b;margin-bottom:.6rem;">Pas encore de compte ?</p>
                <a href="{{ route('register') }}" style="display:inline-flex;align-items:center;gap:6px;color:var(--b600);font-size:.8rem;font-weight:600;text-decoration:none;transition:color .2s;" onmouseover="this.style.color='var(--b700)'" onmouseout="this.style.color='var(--b600)'">
                    <i class="fas fa-user-plus"></i>
                    Créer mon compte d'essai
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    function openModal() {
        document.getElementById('loginModal').classList.add('open');
        document.body.style.overflow = 'hidden';
        setTimeout(() => document.getElementById('email').focus(), 300);
    }
    function closeModal() {
        document.getElementById('loginModal').classList.remove('open');
        document.body.style.overflow = '';
    }
    function closeOnOverlay(e) {
        if (e.target === document.getElementById('loginModal')) closeModal();
    }
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

    // Auto-open modal if redirected from /confirmi/login or if there are errors
    @if(session('error') || $errors->any() || request('login'))
        document.addEventListener('DOMContentLoaded', openModal);
    @endif

    // Navbar scroll effect
    window.addEventListener('scroll', () => {
        const nav = document.querySelector('.nav');
        nav.style.background = window.scrollY > 40
            ? 'rgba(10,22,40,.98)'
            : 'rgba(10,22,40,.93)';
    });
</script>
</body>
</html>
