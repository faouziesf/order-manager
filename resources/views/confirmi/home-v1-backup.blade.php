<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta name="theme-color" content="#3b82f6">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<title>Confirmi — La plateforme de confirmation #1 en Tunisie</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="manifest" href="{{ asset('manifest.json') }}">
<style>
:root{--b950:#0a1628;--b900:#0f2249;--b800:#1e3a8a;--b700:#1d4ed8;--b600:#2563eb;--b500:#3b82f6;--b400:#60a5fa;--b50:#eff6ff;--gold:#fbbf24;--text:#0f172a;--muted:#64748b}
*{margin:0;padding:0;box-sizing:border-box}html{scroll-behavior:smooth}
body{font-family:'Inter',sans-serif;color:var(--text);overflow-x:hidden;background:#fafafa}

/* NAV MODERNE */
.nav{position:fixed;top:0;left:0;right:0;z-index:100;height:70px;display:flex;align-items:center;justify-content:space-between;padding:0 5%;background:rgba(10,22,40,.96);backdrop-filter:blur(20px);border-bottom:1px solid rgba(255,255,255,.08);transition:all .3s ease}
.nav-brand{display:flex;align-items:center;gap:12px;text-decoration:none;transition:transform .3s}
.nav-brand:hover{transform:scale(1.05)}
.nav-brand img{height:38px;filter:drop-shadow(0 2px 8px rgba(59,130,246,.3))}
.nav-brand span{color:#fff;font-size:1.3rem;font-weight:900;letter-spacing:-.5px}
.nav-links{display:flex;gap:2rem;align-items:center}
.nav-links a{color:rgba(255,255,255,.75);text-decoration:none;font-size:.9rem;font-weight:600;transition:all .3s;position:relative;padding:.5rem 0}
.nav-links a::after{content:'';position:absolute;bottom:0;left:50%;right:50%;height:2px;background:var(--b400);transition:all .3s}
.nav-links a:hover{color:#fff}
.nav-links a:hover::after{left:0;right:0}
.btn-nav{padding:.625rem 1.5rem;background:linear-gradient(135deg,var(--b600),var(--b700));color:#fff;border:none;border-radius:12px;font-size:.9rem;font-weight:700;cursor:pointer;transition:all .3s;box-shadow:0 4px 12px rgba(59,130,246,.3)}
.btn-nav:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(59,130,246,.4)}
.btn-nav-outline{background:transparent;border:2px solid rgba(255,255,255,.3);box-shadow:none}
.btn-nav-outline:hover{background:rgba(255,255,255,.1);border-color:rgba(255,255,255,.5)}
.mobile-menu-btn{display:none;color:white;background:rgba(255,255,255,.1);border:none;padding:.75rem;border-radius:12px;cursor:pointer;font-size:1.2rem}

/* HERO ULTRA MODERNE */
.hero{min-height:100vh;background:linear-gradient(135deg,#0a1628 0%,#1e3a8a 40%,#2563eb 100%);display:flex;align-items:center;justify-content:center;text-align:center;padding:140px 5% 90px;position:relative;overflow:hidden}
.hero::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse at 30% 50%,rgba(59,130,246,.25) 0%,transparent 50%),radial-gradient(ellipse at 70% 30%,rgba(96,165,250,.15) 0%,transparent 50%);pointer-events:none}
.hero-grid{position:absolute;inset:0;opacity:.04;background-image:linear-gradient(rgba(255,255,255,.8) 1.5px,transparent 1.5px),linear-gradient(90deg,rgba(255,255,255,.8) 1.5px,transparent 1.5px);background-size:60px 60px;animation:gridMove 30s linear infinite}
@keyframes gridMove{0%{transform:translate(0,0)}100%{transform:translate(60px,60px)}}
.hero-inner{position:relative;z-index:1;max-width:900px;margin:0 auto}
.hero-badge{display:inline-flex;align-items:center;gap:10px;background:rgba(251,191,36,.2);border:2px solid rgba(251,191,36,.5);color:var(--gold);padding:8px 20px;border-radius:50px;font-size:.75rem;font-weight:800;letter-spacing:1px;text-transform:uppercase;margin-bottom:1.5rem;box-shadow:0 4px 16px rgba(251,191,36,.2);animation:float 3s ease-in-out infinite}
@keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-10px)}}
.hero-logo-wrap{width:110px;height:110px;margin:0 auto 2rem;background:rgba(255,255,255,.98);border-radius:28px;display:flex;align-items:center;justify-content:center;padding:16px;border:3px solid rgba(255,255,255,.2);box-shadow:0 12px 40px rgba(0,0,0,.3),0 0 0 1px rgba(255,255,255,.1);animation:pulse 3s ease-in-out infinite}
@keyframes pulse{0%,100%{transform:scale(1);box-shadow:0 12px 40px rgba(0,0,0,.3)}50%{transform:scale(1.05);box-shadow:0 16px 50px rgba(59,130,246,.4)}}
.hero-logo-wrap img{width:100%;height:100%;object-fit:contain}
.hero h1{font-size:clamp(2.2rem,6vw,4rem);font-weight:900;color:#fff;line-height:1.15;letter-spacing:-2px;margin-bottom:1.5rem;text-shadow:0 4px 20px rgba(0,0,0,.2)}
.hero h1 .acc{background:linear-gradient(135deg,#60a5fa,#3b82f6);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.hero-sub{font-size:clamp(1rem,2.2vw,1.2rem);color:rgba(255,255,255,.85);max-width:680px;margin:0 auto 3rem;line-height:1.7;font-weight:500}
.hero-cta{display:flex;gap:1.25rem;justify-content:center;flex-wrap:wrap;margin-bottom:3rem}
.btn-hero-primary{padding:1.125rem 2.75rem;background:linear-gradient(135deg,#fff,#f8fafc);color:#1e40af;border:3px solid #3b82f6;border-radius:16px;font-size:1.125rem;font-weight:800;cursor:pointer;transition:all .3s;box-shadow:0 8px 30px rgba(59,130,246,.3);text-decoration:none;display:inline-flex;align-items:center;gap:10px;position:relative;overflow:hidden}
.btn-hero-primary::before{content:'';position:absolute;top:0;left:-100%;width:100%;height:100%;background:linear-gradient(90deg,transparent,rgba(59,130,246,.1),transparent);transition:left .5s}
.btn-hero-primary:hover::before{left:100%}
.btn-hero-primary:hover{transform:translateY(-3px);box-shadow:0 12px 40px rgba(59,130,246,.4);color:#1e40af}
.btn-hero-secondary{padding:1.125rem 2.5rem;background:rgba(255,255,255,.12);color:#fff;border:2px solid rgba(255,255,255,.3);border-radius:16px;font-size:1.05rem;font-weight:700;cursor:pointer;transition:all .3s;text-decoration:none;display:inline-flex;align-items:center;gap:10px;backdrop-filter:blur(10px)}
.btn-hero-secondary:hover{background:rgba(255,255,255,.2);border-color:rgba(255,255,255,.5);transform:translateY(-3px);color:#fff}
.trust-badges{display:flex;justify-content:center;gap:2rem;flex-wrap:wrap;margin-top:3rem}
.trust-badge{display:flex;align-items:center;gap:8px;color:rgba(255,255,255,.7);font-size:.875rem;font-weight:600}
.trust-badge i{color:var(--b400);font-size:1.125rem}

/* STATS MODERNES */
.stats{background:linear-gradient(135deg,#1e3a8a,#1e40af);padding:3rem 5%;border-top:2px solid rgba(255,255,255,.1)}
.stats-grid{max-width:1100px;margin:0 auto;display:grid;grid-template-columns:repeat(4,1fr);gap:2rem}
.stat{text-align:center;padding:1.5rem;background:rgba(255,255,255,.08);backdrop-filter:blur(10px);border-radius:20px;border:2px solid rgba(255,255,255,.1);transition:all .3s}
.stat:hover{transform:translateY(-5px);background:rgba(255,255,255,.12);border-color:rgba(255,255,255,.2)}
.stat-icon{font-size:2rem;margin-bottom:.75rem;color:var(--b400)}
.stat-n{font-size:2.5rem;font-weight:900;color:#fff;display:block;margin-bottom:.25rem}
.stat-l{font-size:.8125rem;color:rgba(255,255,255,.7);font-weight:600}
@media(max-width:768px){.stats-grid{grid-template-columns:1fr 1fr;gap:1rem}.stat{padding:1.25rem}}

/* SECTIONS */
.section{padding:90px 5%}
.wrap{max-width:1200px;margin:0 auto}
.s-lbl{text-align:center;font-size:.75rem;font-weight:800;color:var(--b600);letter-spacing:2px;text-transform:uppercase;margin-bottom:.75rem;display:inline-flex;align-items:center;gap:8px;padding:6px 16px;background:var(--b50);border-radius:50px;margin:0 auto .75rem;display:flex;justify-content:center;width:fit-content;margin-left:auto;margin-right:auto}
.s-title{text-align:center;font-size:clamp(1.75rem,4vw,2.75rem);font-weight:900;color:var(--text);letter-spacing:-1px;margin-bottom:1rem}
.s-sub{text-align:center;max-width:650px;margin:0 auto 4rem;color:var(--muted);font-size:1.0625rem;line-height:1.7;font-weight:500}

/* BENEFITS GRID MODERNE */
.benefits-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:1.75rem}
.bc{background:#fff;border-radius:24px;padding:2.25rem;border:2px solid #f1f5f9;transition:all .3s;position:relative;overflow:hidden}
.bc::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,var(--b500),var(--b400));opacity:0;transition:opacity .3s}
.bc:hover{transform:translateY(-8px);box-shadow:0 20px 60px rgba(59,130,246,.15);border-color:var(--b200)}
.bc:hover::before{opacity:1}
.bi{width:64px;height:64px;border-radius:18px;display:flex;align-items:center;justify-content:center;font-size:1.75rem;margin-bottom:1.25rem;transition:transform .3s}
.bc:hover .bi{transform:scale(1.1) rotate(5deg)}
.bi.bl{background:linear-gradient(135deg,#dbeafe,#bfdbfe);color:var(--b600)}
.bi.gr{background:linear-gradient(135deg,#d1fae5,#a7f3d0);color:#059669}
.bi.am{background:linear-gradient(135deg,#fed7aa,#fcd34d);color:#d97706}
.bi.pu{background:linear-gradient(135deg,#e9d5ff,#d8b4fe);color:#7c3aed}
.bi.ro{background:linear-gradient(135deg,#fecaca,#fca5a5);color:#dc2626}
.bi.te{background:linear-gradient(135deg,#99f6e4,#5eead4);color:#0d9488}
.bc h3{font-size:1.125rem;font-weight:800;color:var(--text);margin-bottom:.625rem;letter-spacing:-.3px}
.bc p{font-size:.9375rem;color:var(--muted);line-height:1.7;font-weight:500}

/* HOW IT WORKS */
.how-bg{background:linear-gradient(135deg,#f8fafc,#f1f5f9)}
.steps{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:2.5rem;max-width:1000px;margin:0 auto}
.step{text-align:center;position:relative}
.step-n{width:70px;height:70px;border-radius:50%;background:linear-gradient(135deg,var(--b600),var(--b700));color:#fff;font-size:1.75rem;font-weight:900;display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;box-shadow:0 8px 24px rgba(37,99,235,.3);position:relative}
.step-n::before{content:'';position:absolute;inset:-4px;border-radius:50%;border:3px solid rgba(59,130,246,.2);animation:ripple 2s ease-out infinite}
@keyframes ripple{0%{transform:scale(1);opacity:1}100%{transform:scale(1.4);opacity:0}}
.step h3{font-size:1.0625rem;font-weight:800;color:var(--text);margin-bottom:.5rem;letter-spacing:-.3px}
.step p{font-size:.875rem;color:var(--muted);line-height:1.65;font-weight:500}

/* TUNISIA SECTION */
.tun{background:linear-gradient(135deg,#0a1628 0%,#1e3a8a 50%,#1e40af 100%);position:relative;overflow:hidden}
.tun::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse at 20% 50%,rgba(59,130,246,.25),transparent 60%),radial-gradient(ellipse at 80% 30%,rgba(96,165,250,.15),transparent 60%)}
.tun-inner{display:grid;grid-template-columns:1.2fr 1fr;gap:5rem;align-items:center;position:relative;z-index:1}
@media(max-width:900px){.tun-inner{grid-template-columns:1fr;gap:3rem}.tun-vis{display:none!important}}
.t-tag{display:inline-flex;align-items:center;gap:10px;background:rgba(251,191,36,.2);border:2px solid rgba(251,191,36,.4);color:var(--gold);padding:8px 18px;border-radius:50px;font-size:.75rem;font-weight:800;letter-spacing:1px;text-transform:uppercase;margin-bottom:1.5rem}
.tun-inner h2{font-size:clamp(1.75rem,3.5vw,2.5rem);font-weight:900;color:#fff;line-height:1.25;margin-bottom:1.25rem;letter-spacing:-1px}
.tun-inner .txt{color:rgba(255,255,255,.8);line-height:1.8;margin-bottom:2rem;font-size:1.0625rem;font-weight:500}
.chk{list-style:none}
.chk li{display:flex;align-items:flex-start;gap:12px;color:rgba(255,255,255,.9);font-size:.9375rem;margin-bottom:.75rem;font-weight:600}
.chk li i{color:var(--b400);margin-top:2px;flex-shrink:0;font-size:1.125rem}
.tun-vis{display:grid;grid-template-columns:1fr 1fr;gap:1.25rem}
.vc{background:rgba(255,255,255,.1);border:2px solid rgba(255,255,255,.15);border-radius:20px;padding:1.75rem;text-align:center;backdrop-filter:blur(12px);transition:all .3s}
.vc:hover{transform:translateY(-5px);background:rgba(255,255,255,.15);border-color:rgba(255,255,255,.25)}
.vc .vi{font-size:2rem;color:var(--b400);margin-bottom:.75rem}
.vc .vn{font-size:2.25rem;font-weight:900;color:#fff;margin-bottom:.25rem}
.vc .vl{font-size:.8125rem;color:rgba(255,255,255,.65);font-weight:600}

/* CTA FINALE */
.cta-bg{background:linear-gradient(135deg,var(--b600),var(--b700));text-align:center;padding:90px 5%;position:relative;overflow:hidden}
.cta-bg::before{content:'';position:absolute;inset:0;background:radial-gradient(circle at 50% 50%,rgba(255,255,255,.1) 0%,transparent 70%)}
.cta-inner{position:relative;z-index:1}
.cta-bg h2{font-size:clamp(1.75rem,4vw,3rem);font-weight:900;color:#fff;letter-spacing:-1px;margin-bottom:1.25rem}
.cta-bg p{color:rgba(255,255,255,.85);max-width:600px;margin:0 auto 2.5rem;font-size:1.125rem;font-weight:500;line-height:1.7}
.promo-box{background:rgba(255,255,255,.12);border:2px solid rgba(255,255,255,.25);border-radius:24px;padding:2rem 2.5rem;margin:0 auto 2.5rem;max-width:700px;backdrop-filter:blur(15px)}
.promo-title{color:#fff;font-weight:800;font-size:1.375rem;margin-bottom:1rem;display:flex;align-items:center;justify-content:center;gap:10px}
.promo-title i{animation:bounce 2s ease-in-out infinite}
@keyframes bounce{0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}
.promo-features{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;margin-top:1.5rem}
.promo-feature{display:flex;align-items:center;gap:8px;color:rgba(255,255,255,.9);font-size:.9375rem;font-weight:600;justify-content:center}
.promo-feature i{color:#bfdbfe}
.btn-cta{padding:1.25rem 3.5rem;background:linear-gradient(135deg,#fff,#f1f5f9);color:#1e40af;border:3px solid #1e40af;border-radius:16px;font-size:1.25rem;font-weight:900;cursor:pointer;transition:all .3s;box-shadow:0 8px 32px rgba(0,0,0,.2);text-decoration:none;display:inline-flex;align-items:center;gap:12px;position:relative;overflow:hidden}
.btn-cta::before{content:'';position:absolute;top:0;left:-100%;width:100%;height:100%;background:linear-gradient(90deg,transparent,rgba(30,64,175,.1),transparent);transition:left .5s}
.btn-cta:hover::before{left:100%}
.btn-cta:hover{transform:translateY(-4px);box-shadow:0 12px 40px rgba(0,0,0,.3);color:#1e40af}

/* FOOTER MODERNE */
footer{background:#0a1628;padding:3rem 5% 2rem}
.ft-inner{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:2rem;border-bottom:2px solid rgba(255,255,255,.08);padding-bottom:2rem;margin-bottom:1.5rem}
.ft-brand{display:flex;align-items:center;gap:12px}
.ft-brand img{height:34px}
.ft-brand span{color:#fff;font-weight:900;font-size:1.125rem;letter-spacing:-.3px}
.ft-links{display:flex;gap:2rem;flex-wrap:wrap}
.ft-links a{color:rgba(255,255,255,.6);font-size:.875rem;text-decoration:none;transition:color .3s;font-weight:600}
.ft-links a:hover{color:rgba(255,255,255,.95)}
.ft-copy{text-align:center;color:rgba(255,255,255,.4);font-size:.8125rem;font-weight:500}

/* MODAL LOGIN MODERNE */
.mOverlay{display:none;position:fixed;inset:0;z-index:9999;background:rgba(5,10,20,.85);backdrop-filter:blur(12px);align-items:center;justify-content:center;padding:1rem;animation:fadeIn .3s ease}
@keyframes fadeIn{from{opacity:0}to{opacity:1}}
.mOverlay.open{display:flex}
.mBox{background:#fff;border-radius:28px;width:100%;max-width:450px;box-shadow:0 32px 80px rgba(0,0,0,.5);overflow:hidden;animation:modalIn .4s cubic-bezier(0.16,1,0.3,1)}
@keyframes modalIn{from{transform:scale(.9) translateY(20px);opacity:0}to{transform:scale(1) translateY(0);opacity:1}}
.mHead{background:linear-gradient(135deg,#1e3a8a,#2563eb);padding:2rem 2rem 1.75rem;text-align:center;position:relative}
.mHead::before{content:'';position:absolute;inset:0;background:radial-gradient(circle at 30% 40%,rgba(255,255,255,.1) 0%,transparent 60%)}
.mHead img{height:48px;margin-bottom:1rem;position:relative}
.mHead h2{color:#fff;font-size:1.5rem;font-weight:900;margin-bottom:.375rem;position:relative;letter-spacing:-.5px}
.mHead p{color:rgba(255,255,255,.8);font-size:.875rem;font-weight:600;position:relative}
.mClose{position:absolute;right:16px;top:16px;background:rgba(255,255,255,.15);border:none;color:#fff;width:36px;height:36px;border-radius:50%;cursor:pointer;font-size:.9375rem;display:flex;align-items:center;justify-content:center;transition:all .3s;backdrop-filter:blur(10px)}
.mClose:hover{background:rgba(255,255,255,.25);transform:rotate(90deg)}
.mBody{padding:2.25rem 2rem 2rem}
.fg{margin-bottom:1.25rem}
.fg label{display:flex;align-items:center;gap:6px;font-size:.8125rem;font-weight:700;color:#1f2937;margin-bottom:.5rem}
.fg label i{color:var(--b600)}
.iw{position:relative}
.iw input{width:100%;padding:.9375rem;border:2px solid #e5e7eb;border-radius:14px;font-size:.9375rem;font-weight:500;transition:all .3s;background:#f9fafb}
.iw input:focus{border-color:var(--b500);box-shadow:0 0 0 4px rgba(59,130,246,.1);background:#fff;outline:none}
.rem-row{display:flex;align-items:center;gap:10px;margin-bottom:1.5rem}
.rem-row input[type="checkbox"]{width:1.125rem;height:1.125rem;border-radius:6px;cursor:pointer;accent-color:var(--b600)}
.rem-row label{font-size:.875rem;color:#64748b;cursor:pointer;font-weight:600}
.btn-mlogin{width:100%;padding:1rem;background:linear-gradient(135deg,var(--b600),var(--b700));color:#fff;border:none;border-radius:14px;font-size:1.0625rem;font-weight:800;cursor:pointer;transition:all .3s;box-shadow:0 4px 16px rgba(37,99,235,.3);display:flex;align-items:center;justify-content:center;gap:10px}
.btn-mlogin:hover{transform:translateY(-2px);box-shadow:0 6px 24px rgba(37,99,235,.4)}
.m-err{background:#fef2f2;color:#991b1b;border:2px solid #fecaca;padding:.75rem 1rem;border-radius:12px;font-size:.875rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:8px;font-weight:600}
.m-divider{text-align:center;margin:2rem 0;position:relative}
.m-divider::before,.m-divider::after{content:'';position:absolute;top:50%;width:45%;height:1px;background:linear-gradient(90deg,transparent,#e5e7eb,transparent)}
.m-divider::before{left:0}.m-divider::after{right:0}
.m-divider span{background:#fff;padding:0 1rem;font-size:.8125rem;color:#9ca3af;font-weight:700;text-transform:uppercase;letter-spacing:.5px}
.m-register-box{background:linear-gradient(135deg,#fef3c7,#fde68a);border:2px solid #f59e0b;border-radius:16px;padding:1.5rem;position:relative;overflow:hidden}
.m-register-box::before{content:'';position:absolute;top:0;left:-100%;width:100%;height:100%;background:linear-gradient(90deg,transparent,rgba(255,255,255,.4),transparent);animation:shimmer 3s ease-in-out infinite}
@keyframes shimmer{0%{left:-100%}100%{left:100%}}
.m-register-badge{display:flex;align-items:center;justify-content:center;gap:8px;color:#92400e;font-weight:800;font-size:.9375rem;margin-bottom:.5rem}
.m-register-badge i{font-size:1.125rem;animation:bounce 2s ease-in-out infinite}
.m-register-text{color:#78350f;font-size:.875rem;font-weight:700;text-align:center;line-height:1.6}
.m-register-link{text-align:center;margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid #e2e8f0}
.m-register-link p{font-size:.875rem;color:#64748b;margin-bottom:.625rem;font-weight:500}
.m-register-link a{display:inline-flex;align-items:center;gap:6px;color:var(--b600);font-size:.9375rem;font-weight:700;text-decoration:none;transition:all .3s;padding:.5rem 1rem;border-radius:10px}
.m-register-link a:hover{background:#eff6ff;color:var(--b700);transform:translateX(4px)}

@media(max-width:768px){
    .nav{height:64px;padding:0 4%}
    .nav-brand span{font-size:1.1rem}
    .nav-links{display:none}
    .mobile-menu-btn{display:block}
    .hero{padding:110px 4% 70px}
    .section{padding:60px 4%}
    .benefits-grid,.steps,.roles-grid{grid-template-columns:1fr}
    .form-row{grid-template-columns:1fr}
    .btn-hero-primary,.btn-hero-secondary{width:100%;justify-content:center}
}
</style>
</head>
<body>

<!-- NAV -->
<nav class="nav">
    <a href="{{ route('confirmi.home') }}" class="nav-brand">
        <img src="{{ asset('img/confirmi.png') }}" alt="Confirmi">
        <span>Confirmi</span>
    </a>
    <div class="nav-links">
        <a href="#benefices">Bénéfices</a>
        <a href="#comment">Comment ça marche</a>
        <a href="#tunisie">À propos</a>
    </div>
    <div style="display:flex;gap:.875rem;align-items:center">
        <a href="{{ route('register') }}" class="btn-nav btn-nav-outline">
            <i class="fas fa-user-plus" style="margin-right:6px"></i>S'inscrire
        </a>
        <button class="btn-nav" onclick="openModal()">
            <i class="fas fa-sign-in-alt" style="margin-right:6px"></i>Connexion
        </button>
    </div>
    <button class="mobile-menu-btn"><i class="fas fa-bars"></i></button>
</nav>

<!-- HERO -->
<section class="hero">
    <div class="hero-grid"></div>
    <div class="hero-inner">
        <div class="hero-badge"><i class="fas fa-star"></i> N°1 en Tunisie pour la confirmation</div>
        <div class="hero-logo-wrap">
            <img src="{{ asset('img/confirmi.png') }}" alt="Confirmi">
        </div>
        <h1>Centralisez. Confirmez.<br><span class="acc">Livrez sans effort.</span></h1>
        <p class="hero-sub">
            Débarrassez-vous d'Excel et des feuilles volantes ! Confirmi est la plateforme tout-en-un pour synchroniser vos boutiques internationales, gérer vos stocks et confirmer vos commandes à la vitesse de l'éclair.
        </p>
        <div class="hero-cta">
            <a href="{{ route('register') }}" class="btn-hero-primary">
                <i class="fas fa-rocket"></i>
                <strong>Commencer mon essai gratuit</strong>
            </a>
            <button class="btn-hero-secondary" onclick="openModal()">
                <i class="fas fa-sign-in-alt"></i> Se connecter
            </button>
            <a href="#benefices" class="btn-hero-secondary">
                <i class="fas fa-arrow-down"></i> Découvrir
            </a>
        </div>
        <div class="trust-badges">
            <div class="trust-badge"><i class="fas fa-shield-check"></i> 100% Sécurisé</div>
            <div class="trust-badge"><i class="fas fa-clock"></i> Essai 14 jours</div>
            <div class="trust-badge"><i class="fas fa-credit-card"></i> Sans CB</div>
        </div>
    </div>
</section>

<!-- STATS -->
<div class="stats">
    <div class="stats-grid">
        <div class="stat">
            <div class="stat-icon"><i class="fas fa-ban"></i></div>
            <span class="stat-n">Zero</span>
            <div class="stat-l">Fichiers Excel / Papiers</div>
        </div>
        <div class="stat">
            <div class="stat-icon"><i class="fas fa-bolt"></i></div>
            <span class="stat-n">Auto</span>
            <div class="stat-l">Import depuis vos boutiques</div>
        </div>
        <div class="stat">
            <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
            <span class="stat-n">100%</span>
            <div class="stat-l">Suivi stocks & commandes</div>
        </div>
        <div class="stat">
            <div class="stat-icon"><i class="fas fa-infinity"></i></div>
            <span class="stat-n">24/7</span>
            <div class="stat-l">Tableau de bord temps réel</div>
        </div>
    </div>
</div>

<!-- BENEFITS -->
<section class="section" id="benefices">
    <div class="wrap">
        <div class="s-lbl"><i class="fas fa-sparkles" style="font-size:.875rem"></i> Pourquoi Confirmi</div>
        <h2 class="s-title">Tout ce dont vous avez besoin pour exploser vos ventes</h2>
        <p class="s-sub">Une suite complète d'outils puissants conçus pour vous faire gagner un temps précieux et éliminer les erreurs humaines.</p>
        <div class="benefits-grid">
            <div class="bc">
                <div class="bi ro"><i class="fas fa-file-excel"></i></div>
                <h3>Adieu Excel et Google Sheets</h3>
                <p>Oubliez les tableurs interminables et les notes papier. Organisez, triez et traitez toutes vos commandes beaucoup plus rapidement sur une interface dédiée et intuitive.</p>
            </div>
            <div class="bc">
                <div class="bi pu"><i class="fas fa-tachometer-alt"></i></div>
                <h3>Dashboard de Suivi Global</h3>
                <p>Un tableau de bord centralisé pour suivre vos commandes et gérer l'état de vos stocks en temps réel. Prenez les meilleures décisions grâce à des statistiques claires.</p>
            </div>
            <div class="bc">
                <div class="bi gr"><i class="fas fa-globe"></i></div>
                <h3>Intégrations Internationales</h3>
                <p>Connectez Confirmi à vos plateformes e-commerce (Shopify, WooCommerce...) pour récupérer vos nouvelles commandes automatiquement et instantanément.</p>
            </div>
            <div class="bc">
                <div class="bi bl"><i class="fas fa-headset"></i></div>
                <h3>Confirmation Téléphonique Rapide</h3>
                <p>Notre interface focalisée permet à vos employés de confirmer les commandes de façon fluide et sans distractions, augmentant drastiquement la productivité.</p>
            </div>
            <div class="bc">
                <div class="bi am"><i class="fas fa-users-cog"></i></div>
                <h3>Travail d'Équipe & Multi-comptes</h3>
                <p>Séparez intelligemment les rôles (Admin, Commercial, Employé). Assignez facilement des listes de commandes aux membres de votre équipe pour un suivi sans faille.</p>
            </div>
            <div class="bc">
                <div class="bi te"><i class="fas fa-shipping-fast"></i></div>
                <h3>Expédition Directe Kolixy</h3>
                <p>Une fois confirmée, la commande est automatiquement transférée vers Kolixy pour l'expédition. Un processus zéro clic !</p>
            </div>
        </div>
    </div>
</section>

<!-- HOW IT WORKS -->
<section class="section how-bg" id="comment">
    <div class="wrap">
        <div class="s-lbl"><i class="fas fa-lightbulb" style="font-size:.875rem"></i> Comment ça marche</div>
        <h2 class="s-title">Simplifiez votre quotidien en 4 étapes</h2>
        <p class="s-sub">Passez d'une gestion manuelle chaotique à un système automatisé et fluide en quelques minutes.</p>
        <div class="steps">
            <div class="step">
                <div class="step-n">1</div>
                <h3>Connectez votre boutique</h3>
                <p>Intégrez Confirmi à votre site e-commerce pour l'import automatique de toutes vos commandes.</p>
            </div>
            <div class="step">
                <div class="step-n">2</div>
                <h3>Organisez votre équipe</h3>
                <p>Créez des accès pour vos employés et commerciaux. Répartissez le travail sans aucun fichier Excel.</p>
            </div>
            <div class="step">
                <div class="step-n">3</div>
                <h3>Confirmation ultra-rapide</h3>
                <p>Vos employés utilisent le poste de traitement pour appeler les clients et mettre à jour les statuts en un clic.</p>
            </div>
            <div class="step">
                <div class="step-n">4</div>
                <h3>Expédition automatisée</h3>
                <p>Dès qu'une commande est confirmée, elle est envoyée directement à Kolixy. Suivez tout depuis le dashboard !</p>
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
                <p class="txt">Nous connaissons les réalités du e-commerce en Tunisie : commandes annulées, clients injoignables, et gestion compliquée sur Excel. Confirmi est la solution locale ultime.</p>
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
    <div class="cta-inner">
        <h2>Prêt à révolutionner votre e-commerce ?</h2>
        <p>Abandonnez définitivement Excel. Automatisez vos imports depuis Shopify ou WooCommerce, suivez votre stock en direct et laissez votre équipe confirmer plus de commandes en un temps record.</p>
        <div class="promo-box">
            <div class="promo-title">
                <i class="fas fa-gift"></i>
                Démarrez avec un essai professionnel complet
            </div>
            <div class="promo-features">
                <div class="promo-feature"><i class="fas fa-check"></i> Intégrations complètes</div>
                <div class="promo-feature"><i class="fas fa-check"></i> Multi-utilisateurs</div>
                <div class="promo-feature"><i class="fas fa-check"></i> Support prioritaire</div>
                <div class="promo-feature"><i class="fas fa-check"></i> Sans engagement</div>
            </div>
        </div>
        <a href="{{ route('register') }}" class="btn-cta">
            <i class="fas fa-rocket"></i>
            <strong>Commencer mon essai gratuit maintenant</strong>
        </a>
    </div>
</section>

<!-- FOOTER -->
<footer>
    <div class="ft-inner">
        <div class="ft-brand">
            <img src="{{ asset('img/confirmi.png') }}" alt="Confirmi">
            <span>Confirmi</span>
        </div>
        <div class="ft-links">
            <a href="#benefices">Bénéfices</a>
            <a href="#comment">Comment ça marche</a>
            <a href="#tunisie">À propos</a>
            <a href="{{ route('register') }}">Inscription</a>
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
                    <label for="email"><i class="fas fa-envelope"></i> Adresse email</label>
                    <div class="iw">
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                               placeholder="votre@email.com" required autofocus>
                    </div>
                </div>
                <div class="fg">
                    <label for="password"><i class="fas fa-lock"></i> Mot de passe</label>
                    <div class="iw">
                        <input type="password" id="password" name="password"
                               placeholder="Votre mot de passe" required>
                    </div>
                </div>
                <div class="rem-row">
                    <input type="checkbox" name="remember" id="remember">
                    <label for="remember">Se souvenir de moi</label>
                </div>
                <button type="submit" class="btn-mlogin">
                    <i class="fas fa-sign-in-alt"></i>Se connecter
                </button>
            </form>
            <div class="m-divider"><span>Nouveau sur Confirmi ?</span></div>
            <div class="m-register-box">
                <div class="m-register-badge">
                    <i class="fas fa-gift"></i>
                    Essai gratuit 14 jours
                </div>
                <div class="m-register-text">
                    Accès complet • Support prioritaire • Sans engagement
                </div>
            </div>
            <div class="m-register-link">
                <p>Pas encore de compte ?</p>
                <a href="{{ route('register') }}">
                    <i class="fas fa-user-plus"></i>
                    Créer mon compte gratuitement
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

    @if(session('error') || $errors->any() || request('login'))
        document.addEventListener('DOMContentLoaded', openModal);
    @endif

    window.addEventListener('scroll', () => {
        const nav = document.querySelector('.nav');
        if (window.scrollY > 50) {
            nav.style.background = 'rgba(10,22,40,.99)';
            nav.style.boxShadow = '0 4px 20px rgba(0,0,0,.3)';
        } else {
            nav.style.background = 'rgba(10,22,40,.96)';
            nav.style.boxShadow = 'none';
        }
    });
</script>
</body>
</html>
