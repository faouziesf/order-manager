<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#05050a">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Services — Confirmi.space</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script defer src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script defer src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script>
tailwind.config={theme:{extend:{fontFamily:{sans:['Inter','ui-sans-serif'],grotesk:['Space Grotesk','sans-serif']},colors:{brand:{50:'#eef2ff',100:'#e0e7ff',200:'#c7d2fe',300:'#a5b4fc',400:'#818cf8',500:'#6366f1',600:'#4f46e5',700:'#4338ca',800:'#3730a3',900:'#312e81'}}}}}
</script>
<style>
[x-cloak]{display:none!important}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--c-bg:#05050a;--c-surface:#0d0d18;--c-border:rgba(99,102,241,0.15);--c-brand:#6366f1;--c-text:#e2e8f0;--c-muted:#64748b;}
html{scroll-behavior:smooth}
body{font-family:'Inter',sans-serif;background:var(--c-bg);color:var(--c-text);overflow-x:hidden;-webkit-font-smoothing:antialiased}
::-webkit-scrollbar{width:4px}::-webkit-scrollbar-track{background:var(--c-bg)}::-webkit-scrollbar-thumb{background:#4f46e5;border-radius:99px}
#bgCanvas{position:fixed;inset:0;z-index:0;pointer-events:none;opacity:.4}
.grid-lines{position:fixed;inset:0;z-index:0;pointer-events:none;background-image:linear-gradient(rgba(99,102,241,.025) 1px,transparent 1px),linear-gradient(90deg,rgba(99,102,241,.025) 1px,transparent 1px);background-size:80px 80px}
.cin-nav{position:fixed;top:0;left:0;right:0;z-index:100;padding:1.1rem 2rem;display:flex;align-items:center;justify-content:space-between;transition:background .4s,backdrop-filter .4s,border-color .4s}
.cin-nav.scrolled{background:rgba(5,5,10,0.88);backdrop-filter:blur(24px);border-bottom:1px solid rgba(99,102,241,.15)}
.nav-logo{display:flex;align-items:center;gap:.6rem;text-decoration:none}
.nav-logo-icon{width:34px;height:34px;border-radius:10px;background:linear-gradient(135deg,#6366f1,#4f46e5);display:flex;align-items:center;justify-content:center;font-size:.75rem;color:#fff;box-shadow:0 0 20px rgba(99,102,241,.5)}
.nav-logo-text{font-family:'Space Grotesk',sans-serif;font-size:1.05rem;font-weight:700;color:#fff;letter-spacing:-.02em}
.nav-logo-text span{color:#818cf8}
.nav-links{display:flex;align-items:center;gap:2.2rem}
.nav-links a{font-size:.82rem;font-weight:600;color:rgba(226,232,240,.5);text-decoration:none;letter-spacing:.02em;transition:color .2s}
.nav-links a:hover,.nav-links a.active{color:#fff}
.btn-ghost{padding:.5rem 1.1rem;border-radius:10px;border:1px solid rgba(99,102,241,.3);font-size:.8rem;font-weight:700;color:#a5b4fc;background:transparent;text-decoration:none;transition:all .2s;cursor:pointer}
.btn-ghost:hover{border-color:#6366f1;background:rgba(99,102,241,.1);color:#c7d2fe}
.btn-primary{padding:.5rem 1.25rem;border-radius:10px;background:linear-gradient(135deg,#6366f1,#4338ca);font-size:.8rem;font-weight:800;color:#fff;text-decoration:none;box-shadow:0 0 24px rgba(99,102,241,.4);transition:all .2s}
.btn-primary:hover{box-shadow:0 0 40px rgba(99,102,241,.6);transform:translateY(-1px)}
.wa-float{position:fixed;bottom:1.8rem;right:1.8rem;z-index:200;width:54px;height:54px;border-radius:50%;background:linear-gradient(135deg,#25d366,#128c7e);box-shadow:0 0 30px rgba(37,211,102,.4);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.4rem;text-decoration:none;transition:transform .2s,box-shadow .2s}
.wa-float:hover{transform:scale(1.1) translateY(-2px);box-shadow:0 0 50px rgba(37,211,102,.6)}

/* HERO */
.svc-hero{position:relative;z-index:1;min-height:75svh;display:flex;align-items:center;justify-content:center;padding:9rem 2rem 5rem;text-align:center;overflow:hidden}
.hero-badge{display:inline-flex;align-items:center;gap:.5rem;padding:.4rem 1rem;border-radius:99px;border:1px solid rgba(99,102,241,.35);background:rgba(99,102,241,.08);font-size:.7rem;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:#818cf8;margin-bottom:2rem}
.hero-badge-dot{width:5px;height:5px;border-radius:50%;background:#6366f1;animation:pulse-dot 2s ease infinite}
@keyframes pulse-dot{0%,100%{box-shadow:0 0 0 0 rgba(99,102,241,.8)}50%{box-shadow:0 0 0 6px rgba(99,102,241,0)}}
.svc-h1{font-family:'Space Grotesk',sans-serif;font-size:clamp(2.5rem,7vw,5.5rem);font-weight:700;line-height:.95;letter-spacing:-.04em;color:#fff;margin-bottom:1.5rem}
.grad{background:linear-gradient(135deg,#818cf8 0%,#6366f1 40%,#a78bfa 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.svc-sub{font-size:1rem;font-weight:400;color:rgba(226,232,240,.5);max-width:520px;margin:0 auto 2.5rem;line-height:1.8}
.stat-pills{display:flex;align-items:center;justify-content:center;gap:1rem;flex-wrap:wrap}
.stat-pill{display:inline-flex;align-items:center;gap:.5rem;padding:.45rem 1rem;border-radius:12px;background:rgba(13,13,24,.9);border:1px solid rgba(99,102,241,.18);font-size:.75rem;font-weight:700;color:rgba(226,232,240,.7)}
.stat-pill-dot{width:6px;height:6px;border-radius:50%}

/* SVC SECTIONS */
.svc-section{position:relative;z-index:1;padding:7rem 2rem}
.section-label{display:inline-flex;align-items:center;gap:.5rem;font-size:.68rem;font-weight:800;letter-spacing:.18em;text-transform:uppercase;color:#6366f1;margin-bottom:1.2rem}
.section-label::before{content:'';width:24px;height:1.5px;background:#6366f1;display:block}
.section-h2{font-family:'Space Grotesk',sans-serif;font-size:clamp(1.9rem,4.5vw,3.4rem);font-weight:700;line-height:1.05;letter-spacing:-.04em;color:#fff;margin-bottom:1.2rem}
.section-sub{font-size:.975rem;color:rgba(226,232,240,.5);line-height:1.75;max-width:480px}
.feat-row{display:grid;grid-template-columns:1fr 1fr;gap:5rem;align-items:center;max-width:1100px;margin:0 auto}
.feat-row.rev{direction:rtl}.feat-row.rev>*{direction:ltr}
@media(max-width:768px){.feat-row,.feat-row.rev{grid-template-columns:1fr;gap:3rem}}
.feat-icon-big{width:60px;height:60px;border-radius:18px;display:flex;align-items:center;justify-content:center;font-size:1.4rem;margin-bottom:1.4rem;position:relative}
.feat-list{margin-top:1.5rem;display:flex;flex-direction:column;gap:.85rem}
.feat-list-item{display:flex;align-items:flex-start;gap:.75rem;font-size:.875rem;color:rgba(226,232,240,.6);line-height:1.6}
.feat-list-item i{font-size:.78rem;margin-top:.3rem;flex-shrink:0}
.feat-list-item strong{color:#e2e8f0}

/* Code panel */
.code-panel{background:#040410;border:1px solid rgba(99,102,241,.2);border-radius:18px;overflow:hidden;font-family:'SF Mono','Fira Code',monospace;font-size:.75rem;line-height:1.9}
.code-panel-header{display:flex;align-items:center;gap:.5rem;padding:.65rem 1rem;border-bottom:1px solid rgba(99,102,241,.15);background:rgba(99,102,241,.05)}
.code-dot{width:10px;height:10px;border-radius:50%}
.code-body{padding:1.2rem 1.4rem;overflow-x:auto}
.ck{color:#818cf8;font-weight:700}.cv{color:#34d399}.cs{color:#fbbf24}.cc{color:rgba(226,232,240,.3);font-style:italic}

/* Queue visual */
.queue-visual{background:#040410;border:1px solid rgba(99,102,241,.18);border-radius:18px;padding:1.8rem;overflow:hidden}
.q-header{font-size:.65rem;font-weight:800;letter-spacing:.16em;text-transform:uppercase;color:rgba(226,232,240,.25);margin-bottom:1.5rem}
.q-row{display:flex;align-items:center;justify-content:space-between;border-radius:14px;padding:1rem 1.2rem;margin-bottom:.8rem;border:1px solid transparent;transition:border-color .3s}
.q-row:last-child{margin-bottom:0}
.q-badge{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:.6rem;font-weight:900;color:#fff;flex-shrink:0}
.q-name{font-size:.875rem;font-weight:700;color:#e2e8f0;flex:1;margin-left:.8rem}
.q-count{font-family:'Space Grotesk',sans-serif;font-size:1rem;font-weight:700}

/* CTA Band */
.cta-band{position:relative;z-index:1;padding:7rem 2rem;text-align:center;overflow:hidden}
.cta-band::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 80% 80% at 50% 50%,rgba(99,102,241,.12),transparent);pointer-events:none}
.cta-title{font-family:'Space Grotesk',sans-serif;font-size:clamp(2rem,5vw,3.8rem);font-weight:700;color:#fff;letter-spacing:-.04em;line-height:1.05;margin-bottom:1.2rem}
.hero-cta-primary{display:inline-flex;align-items:center;gap:.6rem;padding:.95rem 2.2rem;border-radius:14px;background:linear-gradient(135deg,#6366f1,#4338ca);font-size:.9rem;font-weight:800;color:#fff;text-decoration:none;box-shadow:0 0 40px rgba(99,102,241,.45);transition:all .25s}
.hero-cta-primary:hover{box-shadow:0 0 70px rgba(99,102,241,.65);transform:translateY(-2px)}
.hero-cta-secondary{display:inline-flex;align-items:center;gap:.6rem;padding:.95rem 2rem;border-radius:14px;border:1px solid rgba(99,102,241,.3);background:rgba(99,102,241,.06);font-size:.9rem;font-weight:700;color:#a5b4fc;text-decoration:none;transition:all .2s}
.hero-cta-secondary:hover{border-color:#6366f1;background:rgba(99,102,241,.12)}

/* FOOTER */
.cin-footer{position:relative;z-index:1;border-top:1px solid rgba(99,102,241,.1);background:rgba(5,5,10,.98);padding:4rem 2rem 2.5rem}
.footer-grid{display:grid;grid-template-columns:1.8fr 1fr 1fr 1fr;gap:3rem;max-width:1100px;margin:0 auto;padding-bottom:3rem;border-bottom:1px solid rgba(99,102,241,.08)}
@media(max-width:768px){.footer-grid{grid-template-columns:1fr 1fr;gap:2rem}}
.footer-col-label{font-size:.65rem;font-weight:800;letter-spacing:.16em;text-transform:uppercase;color:rgba(226,232,240,.25);margin-bottom:1rem}
.footer-col a{display:block;font-size:.82rem;font-weight:500;color:rgba(226,232,240,.5);text-decoration:none;margin-bottom:.6rem;transition:color .2s}
.footer-col a:hover{color:#818cf8}
.footer-bottom{max-width:1100px;margin:0 auto;padding-top:2rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem}
.footer-copy{font-size:.75rem;color:rgba(226,232,240,.25)}
.footer-social{display:flex;gap:.8rem}
.footer-social a{width:34px;height:34px;border-radius:10px;border:1px solid rgba(99,102,241,.2);background:rgba(99,102,241,.05);display:flex;align-items:center;justify-content:center;font-size:.8rem;color:rgba(226,232,240,.4);text-decoration:none;transition:all .2s}
.footer-social a:hover{border-color:#6366f1;color:#818cf8}

/* MODAL */
@keyframes modal-spring{0%{transform:translateY(40px) scale(.88);opacity:0}55%{transform:translateY(-6px) scale(1.01);opacity:1}75%{transform:translateY(3px) scale(.99)}100%{transform:translateY(0) scale(1)}}
@keyframes fade-in-overlay{from{opacity:0}to{opacity:1}}
.modal-spring{animation:modal-spring 500ms cubic-bezier(.2,.9,.2,1) both}
.fade-in-overlay{animation:fade-in-overlay 280ms ease both}
.modal-input{width:100%;background:rgba(5,5,10,.8);border:1.5px solid rgba(99,102,241,.2);border-radius:12px;padding:.8rem 1rem;color:#e2e8f0;font-family:'Inter',sans-serif;font-size:.9rem;outline:none;transition:border-color .15s,box-shadow .15s}
.modal-input::placeholder{color:rgba(226,232,240,.25)}
.modal-input:focus{border-color:#6366f1;box-shadow:0 0 0 4px rgba(99,102,241,.12)}

/* Divider */
.svc-divider{max-width:1100px;margin:0 auto;height:1px;background:linear-gradient(90deg,transparent,rgba(99,102,241,.2),transparent)}

/* Reveal */
.reveal{opacity:0;transform:translateY(40px)}
.reveal-left{opacity:0;transform:translateX(-60px)}
.reveal-right{opacity:0;transform:translateX(60px)}
.reveal-scale{opacity:0;transform:scale(.85)}

@media(max-width:768px){
    .cin-nav{padding:.85rem 1rem}
    .nav-logo-text{font-size:.95rem}
    .btn-ghost,.btn-primary{padding:.45rem .8rem;font-size:.72rem}
    .svc-hero{padding:7.5rem 1rem 3rem;min-height:62svh}
    .hero-badge{font-size:.62rem;letter-spacing:.1em;margin-bottom:1.2rem}
    .svc-sub{font-size:.9rem;line-height:1.65}
    .stat-pills{gap:.55rem}
    .stat-pill{font-size:.67rem;padding:.36rem .75rem}
    .svc-section{padding:4.5rem 1rem}
    .code-body{font-size:.69rem;padding:1rem .9rem}
    .queue-visual{padding:1.1rem}
    .q-row{padding:.75rem .85rem}
    .q-name{font-size:.78rem}
    .cta-band{padding:4.8rem 1rem}
    .cin-footer{padding:3rem 1rem 2rem}
    .footer-grid{grid-template-columns:1fr;gap:1.4rem;padding-bottom:1.7rem}
    .footer-bottom{padding-top:1.2rem}
    .wa-float{width:48px;height:48px;right:1rem;bottom:1rem;font-size:1.2rem}
}

@media(max-width:480px){
    .cin-nav{gap:.5rem}
    .nav-logo-icon{width:30px;height:30px}
    .btn-ghost,.btn-primary{padding:.42rem .68rem;font-size:.68rem}
    .svc-h1{font-size:clamp(1.9rem,9vw,2.5rem)}
    .hero-badge{font-size:.56rem;padding:.32rem .65rem}
    .svc-sub{font-size:.85rem}
    .stat-pill{font-size:.63rem;padding:.32rem .62rem}
    .section-h2{font-size:clamp(1.45rem,8vw,1.95rem)}
    .section-sub{font-size:.84rem;line-height:1.6}
    .feat-icon-big{width:52px;height:52px;font-size:1.15rem;border-radius:14px}
    .feat-list-item{font-size:.8rem}
    .code-panel{border-radius:14px}
    .queue-visual{border-radius:14px}
    .q-badge{width:32px;height:32px}
    .q-count{font-size:.9rem}
    .hero-cta-primary,.hero-cta-secondary{width:100%;justify-content:center;padding:.78rem .9rem;font-size:.8rem}
    .footer-copy{font-size:.68rem}
}

@media(max-width:380px){
    .btn-ghost{display:none}
    .btn-primary{padding:.4rem .62rem;font-size:.66rem}
    .svc-hero{padding-top:6.8rem}
    .wa-float{width:44px;height:44px}
}
</style>
</head>
<body x-data="{ loginOpen: {{ request()->boolean('login') || $errors->hasAny(['email','password']) ? 'true' : 'false' }} }">
@include('partials._no-cache')

<div class="grid-lines"></div>
<canvas id="bgCanvas"></canvas>

<!-- NAV -->
<nav class="cin-nav" id="mainNav">
    <a href="{{ route('confirmi.home') }}" class="nav-logo">
        <div class="nav-logo-icon"><i class="fas fa-bolt"></i></div>
        <span class="nav-logo-text">Confirmi<span>.space</span></span>
    </a>
    <div class="nav-links hidden md:flex">
        <a href="{{ route('confirmi.home') }}">Accueil</a>
        <a href="{{ route('confirmi.services') }}" class="active">Services</a>
        <a href="{{ route('confirmi.contact') }}">Contact</a>
        <a href="{{ route('confirmi.home') }}#pricing">Tarifs</a>
    </div>
    <div style="display:flex;align-items:center;gap:.7rem">
        <a href="{{ route('confirmi.login') }}" @click.prevent="loginOpen = true" class="btn-ghost">Connexion</a>
        <a href="{{ route('confirmi.register') }}" class="btn-primary">S'inscrire</a>
    </div>
</nav>

<a href="https://wa.me/21693357722" target="_blank" rel="noopener noreferrer" class="wa-float" aria-label="WhatsApp">
    <i class="fab fa-whatsapp"></i>
</a>

<!-- HERO -->
<section class="svc-hero" id="hero">
    <div style="position:absolute;inset:0;background:radial-gradient(ellipse 70% 60% at 50% 0%,rgba(99,102,241,.15),transparent);pointer-events:none"></div>
    <div style="position:relative;z-index:2;max-width:860px;margin:0 auto" id="heroContent">
        <div class="hero-badge" id="heroBadge"><span class="hero-badge-dot"></span>4 services — 1 pipeline unifie</div>
        <h1 class="svc-h1" id="heroH1">L'intelligence au coeur<br>de votre <span class="grad">e-commerce.</span></h1>
        <p class="svc-sub" id="heroSub">Confirmi orchestre chaque etape de votre pipeline avec une precision atomique. SMS, API livraison, stock, files intelligentes.</p>
        <div class="stat-pills" id="heroStats">
            <div class="stat-pill"><span class="stat-pill-dot" style="background:#6366f1"></span>94% confirmation</div>
            <div class="stat-pill"><span class="stat-pill-dot" style="background:#34d399"></span>Decrementation atomique</div>
            <div class="stat-pill"><span class="stat-pill-dot" style="background:#38bdf8"></span>Kolixy + MasafaExpress</div>
            <div class="stat-pill"><span class="stat-pill-dot" style="background:#c084fc"></span>4 files intelligentes</div>
        </div>
    </div>
</section>

<!-- SVC 1: SMS -->
<section class="svc-section" id="svc-sms">
    <div class="feat-row">
        <div>
            <p class="section-label reveal">01 — SMS Automation</p>
            <div class="feat-icon-big reveal" style="background:linear-gradient(135deg,rgba(99,102,241,.2),rgba(67,56,202,.1));border:1px solid rgba(99,102,241,.25);color:#818cf8"><i class="fas fa-comment-dots"></i></div>
            <h2 class="section-h2 reveal">Confirmation<br><span class="grad">haute-cadence.</span></h2>
            <p class="section-sub reveal">Des boucles intelligentes contactent vos clients via SMS. Chaque tentative est tracee, relancee si necessaire, et le statut mis a jour en temps reel.</p>
            <div class="feat-list reveal">
                <div class="feat-list-item"><i class="fas fa-check-circle" style="color:#6366f1"></i><span><strong>Relance automatique</strong> — tentatives multiples configurees</span></div>
                <div class="feat-list-item"><i class="fas fa-check-circle" style="color:#6366f1"></i><span><strong>Webhook temps reel</strong> — statut mis a jour instantanement</span></div>
                <div class="feat-list-item"><i class="fas fa-check-circle" style="color:#6366f1"></i><span><strong>Queue intelligente</strong> — Standard, Dated, Old, Restock</span></div>
                <div class="feat-list-item"><i class="fas fa-check-circle" style="color:#6366f1"></i><span><strong>Historique complet</strong> — tracking de chaque interaction</span></div>
            </div>
        </div>
        <div class="code-panel reveal-right">
            <div class="code-panel-header">
                <span class="code-dot" style="background:#ff5f57"></span>
                <span class="code-dot" style="background:#ffbd2e"></span>
                <span class="code-dot" style="background:#28c840"></span>
                <span style="margin-left:.6rem;font-size:.65rem;color:rgba(226,232,240,.3)">sms.pipeline.config</span>
            </div>
            <div class="code-body">
<span class="cc">// Configuration SMS Automation</span>
<span class="ck">sms_automation</span>: {
  <span class="ck">attempts</span>: <span class="cv">3</span>,
  <span class="ck">interval_hours</span>: <span class="cv">4</span>,
  <span class="ck">on_confirm</span>: <span class="cs">"update_status: shipped"</span>,
  <span class="ck">on_cancel</span>: <span class="cs">"restock_increment: queue"</span>,
  <span class="ck">fallback</span>: <span class="cs">"move_to_old_queue"</span>,
  <span class="ck">webhook</span>: {
    <span class="ck">latency</span>: <span class="cs">"&lt; 120ms"</span>,
    <span class="ck">retry</span>: <span class="cv">true</span>
  }
}
            </div>
        </div>
    </div>
</section>
<div class="svc-divider" style="padding:0 2rem"><div class="svc-divider"></div></div>

<!-- SVC 2: API -->
<section class="svc-section" id="svc-api">
    <div class="feat-row rev">
        <div>
            <p class="section-label reveal">02 — API Livraison</p>
            <div class="feat-icon-big reveal" style="background:rgba(56,189,248,.08);border:1px solid rgba(56,189,248,.2);color:#38bdf8"><i class="fas fa-plug"></i></div>
            <h2 class="section-h2 reveal">Vos partenaires<br><span class="grad">integres.</span></h2>
            <p class="section-sub reveal">Confirmi se connecte nativement a Kolixy et MasafaExpress. Les commandes confirmees sont automatiquement routees vers le bon transporteur.</p>
            <div class="feat-list reveal">
                <div class="feat-list-item"><i class="fas fa-check-circle" style="color:#38bdf8"></i><span><strong>Routing automatique</strong> — selection intelligente du transporteur</span></div>
                <div class="feat-list-item"><i class="fas fa-check-circle" style="color:#38bdf8"></i><span><strong>Statut temps reel</strong> — suivi de livraison dans le pipeline</span></div>
                <div class="feat-list-item"><i class="fas fa-check-circle" style="color:#38bdf8"></i><span><strong>Retours integres</strong> — flux retour reintegre en stock</span></div>
            </div>
        </div>
        <div class="code-panel reveal-left">
            <div class="code-panel-header">
                <span class="code-dot" style="background:#ff5f57"></span>
                <span class="code-dot" style="background:#ffbd2e"></span>
                <span class="code-dot" style="background:#28c840"></span>
                <span style="margin-left:.6rem;font-size:.65rem;color:rgba(226,232,240,.3)">delivery.routes.config</span>
            </div>
            <div class="code-body">
<span class="cc">// Transporteurs actifs</span>
<span class="ck">delivery_providers</span>: [
  {
    <span class="ck">name</span>: <span class="cs">"Kolixy"</span>,
    <span class="ck">status</span>: <span class="cv">active</span>,
    <span class="ck">routing</span>: <span class="cs">"auto"</span>
  },
  {
    <span class="ck">name</span>: <span class="cs">"MasafaExpress"</span>,
    <span class="ck">status</span>: <span class="cv">active</span>,
    <span class="ck">routing</span>: <span class="cs">"auto-select"</span>
  }
]

<span class="cc">// Webhook recu</span>
<span class="cv">CMD-4821</span> → <span class="cs">expedie via Kolixy</span> ✓
            </div>
        </div>
    </div>
</section>
<div style="padding:0 2rem"><div class="svc-divider"></div></div>

<!-- SVC 3: STOCK -->
<section class="svc-section" id="svc-stock">
    <div class="feat-row">
        <div>
            <p class="section-label reveal">03 — Stock Management</p>
            <div class="feat-icon-big reveal" style="background:rgba(52,211,153,.08);border:1px solid rgba(52,211,153,.2);color:#34d399"><i class="fas fa-cubes"></i></div>
            <h2 class="section-h2 reveal">Decrementation<br><span class="grad">atomique.</span></h2>
            <p class="section-sub reveal">A chaque confirmation, Confirmi decremente le stock de facon transactionnelle. Zero survente. Alertes automatiques en cas de rupture.</p>
            <div class="feat-list reveal">
                <div class="feat-list-item"><i class="fas fa-check-circle" style="color:#34d399"></i><span><strong>Transaction isolee</strong> — BEGIN/COMMIT, zero doublon</span></div>
                <div class="feat-list-item"><i class="fas fa-check-circle" style="color:#34d399"></i><span><strong>Alerte de suspension</strong> — commande bloquee, restock attendu</span></div>
                <div class="feat-list-item"><i class="fas fa-check-circle" style="color:#34d399"></i><span><strong>Restock auto</strong> — file reactivee des la reconstitution</span></div>
                <div class="feat-list-item"><i class="fas fa-check-circle" style="color:#34d399"></i><span><strong>Multi-variantes</strong> — references et tailles illimitees</span></div>
            </div>
        </div>
        <div class="code-panel reveal-right">
            <div class="code-panel-header">
                <span class="code-dot" style="background:#ff5f57"></span>
                <span class="code-dot" style="background:#ffbd2e"></span>
                <span class="code-dot" style="background:#28c840"></span>
                <span style="margin-left:.6rem;font-size:.65rem;color:rgba(226,232,240,.3)">stock.atomic.sql</span>
            </div>
            <div class="code-body">
<span class="cc">-- Decrementation transactionnelle</span>
<span class="ck">BEGIN</span> TRANSACTION;

<span class="ck">SELECT</span> qty <span class="ck">FROM</span> products
  <span class="ck">WHERE</span> id = <span class="cv">$product_id</span>
  <span class="ck">FOR UPDATE</span>;

<span class="ck">IF</span> qty &gt; <span class="cv">0</span> <span class="ck">THEN</span>
  <span class="ck">UPDATE</span> SET qty = qty - <span class="cv">1</span>;
  <span class="ck">UPDATE</span> order SET status = <span class="cs">'confirmed'</span>;
<span class="ck">ELSE</span>
  <span class="ck">UPDATE</span> order SET status = <span class="cs">'suspended'</span>;
<span class="ck">END IF</span>;

<span class="ck">COMMIT</span>;
            </div>
        </div>
    </div>
</section>
<div style="padding:0 2rem"><div class="svc-divider"></div></div>

<!-- SVC 4: QUEUE -->
<section class="svc-section" id="svc-queue">
    <div class="feat-row rev">
        <div>
            <p class="section-label reveal">04 — Smart Queue</p>
            <div class="feat-icon-big reveal" style="background:rgba(192,132,252,.08);border:1px solid rgba(192,132,252,.2);color:#c084fc"><i class="fas fa-layer-group"></i></div>
            <h2 class="section-h2 reveal">Zero commande<br><span class="grad">perdue.</span></h2>
            <p class="section-sub reveal">Quatre files de priorite intelligentes assurent qu'aucune commande ne reste bloquee. La queue Restock reactive automatiquement les commandes suspendues.</p>
            <div class="feat-list reveal">
                <div class="feat-list-item"><i class="fas fa-check-circle" style="color:#c084fc"></i><span><strong>File Standard</strong> — nouvelles commandes prioritaires</span></div>
                <div class="feat-list-item"><i class="fas fa-check-circle" style="color:#c084fc"></i><span><strong>File Dated</strong> — livraison avec date limite</span></div>
                <div class="feat-list-item"><i class="fas fa-check-circle" style="color:#c084fc"></i><span><strong>File Old</strong> — ultime tentative avant annulation</span></div>
                <div class="feat-list-item"><i class="fas fa-check-circle" style="color:#c084fc"></i><span><strong>File Restock</strong> — reactivation automatique</span></div>
            </div>
        </div>
        <div class="queue-visual reveal-left">
            <div class="q-header">Files actives — pipeline Confirmi</div>
            <div class="q-row" style="background:rgba(99,102,241,.08);border-color:rgba(99,102,241,.2)">
                <div class="q-badge" style="background:#4f46e5">STD</div>
                <div class="q-name">Standard</div>
                <div class="q-count" style="color:#818cf8">874 colis</div>
            </div>
            <div class="q-row" style="background:rgba(251,191,36,.06);border-color:rgba(251,191,36,.2)">
                <div class="q-badge" style="background:#d97706">DAT</div>
                <div class="q-name">Dated</div>
                <div class="q-count" style="color:#fbbf24">212 colis</div>
            </div>
            <div class="q-row" style="background:rgba(239,68,68,.06);border-color:rgba(239,68,68,.2)">
                <div class="q-badge" style="background:#dc2626">OLD</div>
                <div class="q-name">Old</div>
                <div class="q-count" style="color:#f87171">67 colis</div>
            </div>
            <div class="q-row" style="background:rgba(52,211,153,.06);border-color:rgba(52,211,153,.2)">
                <div class="q-badge" style="background:#059669">RST</div>
                <div class="q-name">Restock</div>
                <div class="q-count" style="color:#34d399">43 colis</div>
            </div>
        </div>
    </div>
</section>

<!-- CTA BAND -->
<div class="cta-band" style="border-top:1px solid rgba(99,102,241,.1);border-bottom:1px solid rgba(99,102,241,.1)">
    <div class="cta-title reveal">Pret a automatiser<br>votre pipeline ?</div>
    <p style="font-size:.975rem;color:rgba(226,232,240,.45);max-width:480px;margin:0 auto 2.5rem;line-height:1.7" class="reveal">Demarrez en quelques minutes. Aucune carte bancaire requise.</p>
    <div style="display:flex;align-items:center;justify-content:center;gap:1rem;flex-wrap:wrap" class="reveal">
        <a href="{{ route('confirmi.register') }}" class="hero-cta-primary">Demarrer gratuitement <i class="fas fa-arrow-right text-xs"></i></a>
        <a href="https://wa.me/21693357722" target="_blank" rel="noopener noreferrer" class="hero-cta-secondary"><i class="fab fa-whatsapp" style="color:#25d366"></i> Demo WhatsApp</a>
    </div>
</div>

<!-- FOOTER -->
<footer class="cin-footer">
    <div class="footer-grid">
        <div>
            <div class="nav-logo" style="margin-bottom:1rem">
                <div class="nav-logo-icon"><i class="fas fa-bolt"></i></div>
                <span class="nav-logo-text">Confirmi<span>.space</span></span>
            </div>
            <p style="font-size:.82rem;color:rgba(226,232,240,.35);line-height:1.7;max-width:280px">Pipeline intelligence pour les equipes e-commerce tunisiennes.</p>
            <a href="https://wa.me/21693357722" target="_blank" rel="noopener noreferrer" style="display:inline-flex;align-items:center;gap:.5rem;margin-top:1rem;font-size:.8rem;font-weight:700;color:#34d399;text-decoration:none"><i class="fab fa-whatsapp"></i> +216 93 357 722</a>
        </div>
        <div class="footer-col">
            <div class="footer-col-label">Navigation</div>
            <a href="{{ route('confirmi.home') }}">Accueil</a>
            <a href="{{ route('confirmi.services') }}">Services</a>
            <a href="{{ route('confirmi.contact') }}">Contact</a>
            <a href="{{ route('confirmi.home') }}#pricing">Tarifs</a>
        </div>
        <div class="footer-col">
            <div class="footer-col-label">Services</div>
            <a href="#svc-sms">SMS Automation</a>
            <a href="#svc-api">API Livraison</a>
            <a href="#svc-stock">Stock Atomique</a>
            <a href="#svc-queue">Smart Queue</a>
        </div>
        <div class="footer-col">
            <div class="footer-col-label">Reseaux</div>
            <a href="https://www.facebook.com/profile.php?id=61566118672109" target="_blank" rel="noopener noreferrer"><i class="fab fa-facebook-f mr-1.5"></i> Facebook</a>
            <a href="https://instagram.com/confirmi_space" target="_blank" rel="noopener noreferrer"><i class="fab fa-instagram mr-1.5"></i> Instagram</a>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="footer-copy">&copy; {{ date('Y') }} Confirmi.space — Built in Tunisia</div>
        <div class="footer-social">
            <a href="https://www.facebook.com/profile.php?id=61566118672109" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="https://instagram.com/confirmi_space" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
            <a href="https://wa.me/21693357722" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
        </div>
    </div>
</footer>

<!-- AUTH MODAL -->
<div x-show="loginOpen" x-cloak @click="loginOpen = false" class="fade-in-overlay fixed inset-0 z-[200] bg-black/70 backdrop-blur-xl"></div>
<div x-show="loginOpen" x-cloak class="fixed inset-0 z-[201] flex items-center justify-center p-4" @keydown.escape.window="loginOpen = false">
    <div @click.stop class="modal-spring w-full max-w-md rounded-3xl p-8" style="background:#0d0d18;border:1px solid rgba(99,102,241,.25);box-shadow:0 0 100px rgba(99,102,241,.2)">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:2rem">
            <div>
                <div class="nav-logo-icon" style="margin-bottom:.75rem"><i class="fas fa-bolt"></i></div>
                <div style="font-family:'Space Grotesk',sans-serif;font-size:1.5rem;font-weight:700;color:#fff">Connexion</div>
                <div style="font-size:.82rem;color:rgba(226,232,240,.4);margin-top:.25rem">Acces a votre espace Confirmi</div>
            </div>
            <button @click="loginOpen = false" style="width:36px;height:36px;border-radius:10px;border:1px solid rgba(99,102,241,.2);background:transparent;color:rgba(226,232,240,.5);cursor:pointer;display:flex;align-items:center;justify-content:center">
                <i class="fas fa-times" style="font-size:.8rem"></i>
            </button>
        </div>
        @if(session('error'))<div style="margin-bottom:1rem;padding:.75rem 1rem;border-radius:12px;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);font-size:.82rem;color:#fca5a5">{{ session('error') }}</div>@endif
        @if(session('error') && (str_contains(strtolower(session('error')), 'desactive') || str_contains(strtolower(session('error')), 'inactif')))
            <div style="margin-bottom:1rem;padding:.75rem 1rem;border-radius:12px;background:rgba(52,211,153,.08);border:1px solid rgba(52,211,153,.25);font-size:.8rem;color:#a7f3d0">
                Besoin de reactivation ? Contactez le support: <a href="https://wa.me/21693357722" target="_blank" rel="noopener noreferrer" style="color:#34d399;font-weight:700;text-decoration:none">+216 93 357 722 (WhatsApp)</a>
            </div>
        @endif
        @if($errors->any())<div style="margin-bottom:1rem;padding:.75rem 1rem;border-radius:12px;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);font-size:.82rem;color:#fca5a5">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
        <form action="{{ route('confirmi.login.submit') }}" method="POST" style="display:flex;flex-direction:column;gap:1rem">
            @csrf
            <div><label style="display:block;font-size:.78rem;font-weight:700;color:rgba(226,232,240,.6);margin-bottom:.5rem">Email</label><input type="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="modal-input" placeholder="votre@email.com"></div>
            <div><label style="display:block;font-size:.78rem;font-weight:700;color:rgba(226,232,240,.6);margin-bottom:.5rem">Mot de passe</label><input type="password" name="password" required autocomplete="current-password" class="modal-input" placeholder="••••••••"></div>
            <button type="submit" class="btn-primary" style="width:100%;padding:.85rem;border:none;cursor:pointer;font-size:.9rem;margin-top:.5rem;border-radius:12px;box-shadow:0 0 30px rgba(99,102,241,.4)">Se connecter</button>
        </form>
        <p style="text-align:center;font-size:.82rem;color:rgba(226,232,240,.35);margin-top:1.5rem">Pas de compte ? <a href="{{ route('confirmi.register') }}" @click="loginOpen = false" style="color:#818cf8;font-weight:700;text-decoration:none">S'inscrire</a></p>
    </div>
</div>

<script>
// Three.js background
function initServicesThree(){
    if(!window.THREE || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    try{
        const canvas=document.getElementById('bgCanvas');
        const mobile=window.innerWidth < 900;
        const renderer=new THREE.WebGLRenderer({canvas,antialias:false,alpha:true,powerPreference:'high-performance'});
        renderer.setPixelRatio(Math.min(window.devicePixelRatio,mobile?1:1.25));
        renderer.setSize(window.innerWidth,window.innerHeight);
        const scene=new THREE.Scene();
        const camera=new THREE.PerspectiveCamera(60,window.innerWidth/window.innerHeight,0.1,1000);
        camera.position.z=5;
        const count=mobile?650:1200;
        const pos=new Float32Array(count*3);
        for(let i=0;i<count;i++){pos[i*3]=(Math.random()-.5)*20;pos[i*3+1]=(Math.random()-.5)*20;pos[i*3+2]=(Math.random()-.5)*20}
        const geo=new THREE.BufferGeometry();
        geo.setAttribute('position',new THREE.BufferAttribute(pos,3));
        const mat=new THREE.PointsMaterial({size:mobile?0.028:0.035,color:0x6366f1,transparent:true,opacity:.6});
        const particles=new THREE.Points(geo,mat);
        scene.add(particles);
        let paused=false;
        document.addEventListener('visibilitychange',()=>{paused=document.hidden});
        window.addEventListener('resize',()=>{camera.aspect=window.innerWidth/window.innerHeight;camera.updateProjectionMatrix();renderer.setPixelRatio(Math.min(window.devicePixelRatio,window.innerWidth<900?1:1.25));renderer.setSize(window.innerWidth,window.innerHeight)});
        let f=0;
        (function a(){requestAnimationFrame(a);if(paused)return;f+=.002;particles.rotation.y=f*.25;particles.rotation.x=f*.1;renderer.render(scene,camera)})();
    }catch(e){console.warn('Three.js',e)}
}

document.addEventListener('DOMContentLoaded',function(){
    const idle=window.requestIdleCallback||function(cb){return setTimeout(cb,60)};
    idle(initServicesThree);
    if(!window.gsap)return;
    const reduce=window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if(reduce){
        document.querySelectorAll('.reveal,.reveal-left,.reveal-right,.reveal-scale').forEach(el=>{el.style.opacity='1';el.style.transform='none'});
        return;
    }
    gsap.registerPlugin(ScrollTrigger);
    ScrollTrigger.create({start:'top -60',onToggle:s=>document.getElementById('mainNav').classList.toggle('scrolled',s.isActive)});
    const tl=gsap.timeline({delay:.2});
    tl.fromTo('#heroBadge',{y:20,opacity:0},{y:0,opacity:1,duration:.7,ease:'power3.out'})
      .fromTo('#heroH1',{y:50,opacity:0},{y:0,opacity:1,duration:.9,ease:'power3.out'},'-=.4')
      .fromTo('#heroSub',{y:30,opacity:0},{y:0,opacity:1,duration:.7,ease:'power3.out'},'-=.5')
      .fromTo('#heroStats',{y:25,opacity:0},{y:0,opacity:1,duration:.6,ease:'power3.out'},'-=.4');
    document.querySelectorAll('.reveal').forEach(el=>{gsap.fromTo(el,{y:50,opacity:0},{y:0,opacity:1,duration:.9,ease:'power3.out',scrollTrigger:{trigger:el,start:'top 85%'}})});
    document.querySelectorAll('.reveal-left').forEach(el=>{gsap.fromTo(el,{x:-70,opacity:0},{x:0,opacity:1,duration:1,ease:'power3.out',scrollTrigger:{trigger:el,start:'top 85%'}})});
    document.querySelectorAll('.reveal-right').forEach(el=>{gsap.fromTo(el,{x:70,opacity:0},{x:0,opacity:1,duration:1,ease:'power3.out',scrollTrigger:{trigger:el,start:'top 85%'}})});
    // Animate queue rows
    gsap.fromTo('.q-row',{x:40,opacity:0},{x:0,opacity:1,duration:.7,stagger:.15,ease:'power3.out',scrollTrigger:{trigger:'.queue-visual',start:'top 80%'}});
});
</script>
</body>
</html>