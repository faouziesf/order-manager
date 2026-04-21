<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#05050a">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Contact — Confirmi.space</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script>
tailwind.config={theme:{extend:{fontFamily:{sans:['Inter','ui-sans-serif'],grotesk:['Space Grotesk','sans-serif']},colors:{brand:{50:'#eef2ff',100:'#e0e7ff',200:'#c7d2fe',300:'#a5b4fc',400:'#818cf8',500:'#6366f1',600:'#4f46e5',700:'#4338ca',800:'#3730a3',900:'#312e81'}}}}}
</script>
<style>
[x-cloak]{display:none!important}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--c-bg:#05050a;--c-surface:#0d0d18;--c-border:rgba(99,102,241,0.15);--c-brand:#6366f1;--c-text:#e2e8f0;}
html{scroll-behavior:smooth}
body{font-family:'Inter',sans-serif;background:var(--c-bg);color:var(--c-text);overflow-x:hidden;-webkit-font-smoothing:antialiased}
::-webkit-scrollbar{width:4px}::-webkit-scrollbar-track{background:var(--c-bg)}::-webkit-scrollbar-thumb{background:#4f46e5;border-radius:99px}
#bgCanvas{position:fixed;inset:0;z-index:0;pointer-events:none;opacity:.35}
.grid-lines{position:fixed;inset:0;z-index:0;pointer-events:none;background-image:linear-gradient(rgba(99,102,241,.025) 1px,transparent 1px),linear-gradient(90deg,rgba(99,102,241,.025) 1px,transparent 1px);background-size:80px 80px}

/* NAV */
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
.ctc-hero{position:relative;z-index:1;min-height:65svh;display:flex;align-items:center;justify-content:center;padding:9rem 2rem 4rem;text-align:center;overflow:hidden}
.hero-badge{display:inline-flex;align-items:center;gap:.5rem;padding:.4rem 1rem;border-radius:99px;border:1px solid rgba(99,102,241,.35);background:rgba(99,102,241,.08);font-size:.7rem;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:#818cf8;margin-bottom:2rem}
.hero-badge-dot{width:5px;height:5px;border-radius:50%;background:#6366f1;animation:pulse-dot 2s ease infinite}
@keyframes pulse-dot{0%,100%{box-shadow:0 0 0 0 rgba(99,102,241,.8)}50%{box-shadow:0 0 0 6px rgba(99,102,241,0)}}
.ctc-h1{font-family:'Space Grotesk',sans-serif;font-size:clamp(2.5rem,7vw,5rem);font-weight:700;line-height:.95;letter-spacing:-.04em;color:#fff;margin-bottom:1.4rem}
.grad{background:linear-gradient(135deg,#818cf8 0%,#6366f1 40%,#a78bfa 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.ctc-sub{font-size:1rem;color:rgba(226,232,240,.5);max-width:500px;margin:0 auto;line-height:1.8}

/* SOCIAL TREE */
.tree-section{position:relative;z-index:1;padding:5rem 2rem 6rem}
.tree-wrap{max-width:640px;margin:0 auto}
.tree-trunk{width:2px;background:linear-gradient(to bottom,#4f46e5,rgba(99,102,241,.1));margin:0 auto;position:relative}
.tree-trunk::after{content:'';position:absolute;inset:0;background:linear-gradient(to bottom,rgba(99,102,241,.4),transparent);filter:blur(4px)}
.tree-dot{width:10px;height:10px;border-radius:50%;background:#6366f1;margin:0 auto;box-shadow:0 0 16px rgba(99,102,241,.8);position:relative;z-index:1}

.social-card{
  display:flex;align-items:center;gap:1.5rem;
  border-radius:20px;padding:1.5rem 1.8rem;
  text-decoration:none;
  transition:transform .3s ease,box-shadow .3s ease;
  position:relative;overflow:hidden;
  cursor:pointer;
}
.social-card:hover{transform:translateY(-5px) scale(1.01)}
.social-card::before{content:'';position:absolute;top:0;left:0;right:0;height:1px;background:linear-gradient(90deg,transparent,rgba(255,255,255,.2),transparent)}
.sc-wa{background:linear-gradient(135deg,rgba(18,140,126,.9),rgba(37,211,102,.8));box-shadow:0 20px 60px rgba(18,140,126,.35)}
.sc-wa:hover{box-shadow:0 30px 80px rgba(37,211,102,.45)}
.sc-fb{background:linear-gradient(135deg,rgba(24,119,242,.9),rgba(66,165,245,.8));box-shadow:0 20px 60px rgba(24,119,242,.35)}
.sc-fb:hover{box-shadow:0 30px 80px rgba(66,165,245,.45)}
.sc-ig{background:linear-gradient(135deg,rgba(131,58,180,.9) 0%,rgba(253,29,29,.8) 50%,rgba(252,176,69,.8) 100%);box-shadow:0 20px 60px rgba(131,58,180,.35)}
.sc-ig:hover{box-shadow:0 30px 80px rgba(253,29,29,.4)}
.sc-icon{width:60px;height:60px;border-radius:50%;background:rgba(255,255,255,.15);backdrop-filter:blur(10px);border:1.5px solid rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-size:1.6rem;color:#fff;flex-shrink:0}
.sc-body{flex:1}
.sc-title{font-family:'Space Grotesk',sans-serif;font-size:1.1rem;font-weight:700;color:#fff;margin-bottom:.25rem}
.sc-desc{font-size:.85rem;color:rgba(255,255,255,.7)}
.sc-arrow{width:40px;height:40px;border-radius:50%;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;font-size:.85rem;color:#fff;flex-shrink:0;transition:transform .2s}
.social-card:hover .sc-arrow{transform:translateX(4px)}

.or-divider{display:flex;align-items:center;gap:1rem;max-width:640px;margin:3rem auto;color:rgba(226,232,240,.25);font-size:.8rem;font-weight:600;letter-spacing:.06em;text-transform:uppercase}
.or-divider::before,.or-divider::after{content:'';flex:1;height:1px;background:rgba(99,102,241,.15)}

/* FORM SECTION */
.form-section{position:relative;z-index:1;padding:0 2rem 7rem}
.form-card{max-width:640px;margin:0 auto;background:#0d0d18;border:1px solid rgba(99,102,241,.2);border-radius:24px;padding:2.5rem;position:relative;overflow:hidden}
.form-card::before{content:'';position:absolute;top:0;left:0;right:0;height:1px;background:linear-gradient(90deg,transparent,rgba(99,102,241,.5),transparent)}
.form-icon{width:52px;height:52px;border-radius:16px;background:linear-gradient(135deg,#6366f1,#4338ca);display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:#fff;margin-bottom:1.2rem;box-shadow:0 0 30px rgba(99,102,241,.4)}
.form-title{font-family:'Space Grotesk',sans-serif;font-size:1.6rem;font-weight:700;color:#fff;margin-bottom:.3rem}
.form-sub{font-size:.85rem;color:rgba(226,232,240,.4);margin-bottom:2rem}
.f-label{display:block;font-size:.78rem;font-weight:700;color:rgba(226,232,240,.5);margin-bottom:.5rem;letter-spacing:.03em}
.f-input{width:100%;background:rgba(5,5,10,.7);border:1.5px solid rgba(99,102,241,.18);border-radius:12px;padding:.85rem 1rem;color:#e2e8f0;font-family:'Inter',sans-serif;font-size:.9rem;outline:none;transition:border-color .15s,box-shadow .15s;resize:none}
.f-input::placeholder{color:rgba(226,232,240,.2)}
.f-input:focus{border-color:#6366f1;box-shadow:0 0 0 4px rgba(99,102,241,.1)}
.f-grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
@media(max-width:540px){.f-grid{grid-template-columns:1fr}}
.f-submit{width:100%;padding:.9rem;border-radius:12px;background:linear-gradient(135deg,#6366f1,#4338ca);font-size:.9rem;font-weight:800;color:#fff;border:none;cursor:pointer;box-shadow:0 0 30px rgba(99,102,241,.4);transition:all .25s;margin-top:.5rem}
.f-submit:hover{box-shadow:0 0 55px rgba(99,102,241,.6);transform:translateY(-2px)}

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

/* Reveal */
.reveal{opacity:0;transform:translateY(40px)}
.reveal-left{opacity:0;transform:translateX(-60px)}
.reveal-right{opacity:0;transform:translateX(60px)}
.reveal-up{opacity:0;transform:translateY(30px)}
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
        <a href="{{ route('confirmi.services') }}">Services</a>
        <a href="{{ route('confirmi.contact') }}" class="active">Contact</a>
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
<section class="ctc-hero">
    <div style="position:absolute;inset:0;background:radial-gradient(ellipse 70% 60% at 50% 0%,rgba(99,102,241,.12),transparent);pointer-events:none"></div>
    <div style="position:relative;z-index:2;max-width:800px;margin:0 auto" id="heroContent">
        <div class="hero-badge" id="heroBadge"><span class="hero-badge-dot"></span>Contactez-nous</div>
        <h1 class="ctc-h1" id="heroH1">On est la.<br>Choisissez votre<br><span class="grad">canal prefere.</span></h1>
        <p class="ctc-sub" id="heroSub">WhatsApp, Facebook, Instagram ou formulaire — reponse garantie dans l'heure en heures ouvrables.</p>
    </div>
</section>

<!-- SOCIAL TREE -->
<section class="tree-section">
    <div class="tree-wrap">
        <!-- trunk top -->
        <div class="tree-trunk" style="height:40px"></div>

        <!-- WA -->
        <a href="https://wa.me/21693357722" target="_blank" rel="noopener noreferrer" class="social-card sc-wa reveal" id="scWa">
            <div class="sc-icon"><i class="fab fa-whatsapp"></i></div>
            <div class="sc-body">
                <div class="sc-title">WhatsApp</div>
                <div class="sc-desc">+216 93 357 722 — Reponse rapide</div>
            </div>
            <div class="sc-arrow"><i class="fas fa-arrow-right"></i></div>
        </a>

        <div class="tree-trunk" style="height:20px"></div>
        <div class="tree-dot"></div>
        <div class="tree-trunk" style="height:20px"></div>

        <!-- FB -->
        <a href="https://www.facebook.com/profile.php?id=61566118672109" target="_blank" rel="noopener noreferrer" class="social-card sc-fb reveal" id="scFb">
            <div class="sc-icon"><i class="fab fa-facebook-f"></i></div>
            <div class="sc-body">
                <div class="sc-title">Facebook</div>
                <div class="sc-desc">Actualites & messages directs</div>
            </div>
            <div class="sc-arrow"><i class="fas fa-arrow-right"></i></div>
        </a>

        <div class="tree-trunk" style="height:20px"></div>
        <div class="tree-dot"></div>
        <div class="tree-trunk" style="height:20px"></div>

        <!-- IG -->
        <a href="https://instagram.com/confirmi_space" target="_blank" rel="noopener noreferrer" class="social-card sc-ig reveal" id="scIg">
            <div class="sc-icon"><i class="fab fa-instagram"></i></div>
            <div class="sc-body">
                <div class="sc-title">Instagram</div>
                <div class="sc-desc">@confirmi_space — DM ouvert</div>
            </div>
            <div class="sc-arrow"><i class="fas fa-arrow-right"></i></div>
        </a>

        <div class="tree-trunk" style="height:30px"></div>
        <div class="tree-dot" style="background:rgba(99,102,241,.3);box-shadow:none;width:6px;height:6px"></div>
    </div>

    <div class="or-divider reveal">ou</div>
</section>

<!-- FORM -->
<section class="form-section">
    @if(session('contact_sent'))
        <div class="max-w-xl mx-auto mb-6 reveal" style="padding:1rem 1.25rem;border-radius:14px;background:rgba(52,211,153,.08);border:1px solid rgba(52,211,153,.2);display:flex;align-items:center;gap:.75rem">
            <i class="fas fa-circle-check" style="color:#34d399"></i>
            <div>
                <div style="font-size:.875rem;font-weight:700;color:#6ee7b7">Message envoye avec succes !</div>
                <div style="font-size:.78rem;color:rgba(110,231,183,.6);margin-top:.15rem">Nous vous repondrons dans les plus brefs delais.</div>
            </div>
        </div>
    @endif
    <div class="form-card reveal" id="formCard">
        <div class="form-icon"><i class="fas fa-paper-plane"></i></div>
        <div class="form-title">Envoyer un message</div>
        <div class="form-sub">Remplissez le formulaire — reponse dans l'heure en heures ouvrables.</div>
        @if($errors->any())
            <div style="margin-bottom:1.2rem;padding:.85rem 1rem;border-radius:12px;background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);font-size:.82rem;color:#fca5a5">
                @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
            </div>
        @endif
        <form action="{{ route('confirmi.contact.send') }}" method="POST" style="display:flex;flex-direction:column;gap:1.1rem">
            @csrf
            <div class="f-grid">
                <div>
                    <label class="f-label">Votre nom</label>
                    <input type="text" name="name" value="{{ old('name') }}" required minlength="2" class="f-input" placeholder="Mohamed Ben Ali">
                </div>
                <div>
                    <label class="f-label">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="f-input" placeholder="votre@email.com">
                </div>
            </div>
            <div>
                <label class="f-label">Sujet</label>
                <input type="text" name="subject" value="{{ old('subject') }}" required class="f-input" placeholder="Demo, integration, partenariat...">
            </div>
            <div>
                <label class="f-label">Message</label>
                <textarea name="message" rows="5" required minlength="10" maxlength="2000" class="f-input" placeholder="Decrivez votre besoin...">{{ old('message') }}</textarea>
            </div>
            <button type="submit" class="f-submit">Envoyer le message <i class="fas fa-paper-plane ml-1"></i></button>
        </form>
    </div>
</section>

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
            <a href="{{ route('confirmi.services') }}#svc-sms">SMS Automation</a>
            <a href="{{ route('confirmi.services') }}#svc-api">API Livraison</a>
            <a href="{{ route('confirmi.services') }}#svc-stock">Stock Atomique</a>
            <a href="{{ route('confirmi.services') }}#svc-queue">Smart Queue</a>
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
// Three.js particles
(function(){
    try{
        const canvas=document.getElementById('bgCanvas');
        const renderer=new THREE.WebGLRenderer({canvas,antialias:false,alpha:true});
        renderer.setPixelRatio(Math.min(window.devicePixelRatio,1.5));
        renderer.setSize(window.innerWidth,window.innerHeight);
        const scene=new THREE.Scene();
        const camera=new THREE.PerspectiveCamera(60,window.innerWidth/window.innerHeight,0.1,1000);
        camera.position.z=6;
        const count=1000;
        const pos=new Float32Array(count*3);
        for(let i=0;i<count;i++){pos[i*3]=(Math.random()-.5)*20;pos[i*3+1]=(Math.random()-.5)*20;pos[i*3+2]=(Math.random()-.5)*18}
        const geo=new THREE.BufferGeometry();
        geo.setAttribute('position',new THREE.BufferAttribute(pos,3));
        const mat=new THREE.PointsMaterial({size:0.04,color:0x6366f1,transparent:true,opacity:.5});
        const pts=new THREE.Points(geo,mat);
        scene.add(pts);
        // Torus ring
        const tg=new THREE.TorusGeometry(3,0.008,12,80);
        const tm=new THREE.MeshBasicMaterial({color:0x4f46e5,transparent:true,opacity:.15,wireframe:true});
        const torus=new THREE.Mesh(tg,tm);
        scene.add(torus);
        window.addEventListener('resize',()=>{camera.aspect=window.innerWidth/window.innerHeight;camera.updateProjectionMatrix();renderer.setSize(window.innerWidth,window.innerHeight)});
        let f=0;
        (function a(){requestAnimationFrame(a);f+=.0015;pts.rotation.y=f*.2;torus.rotation.x=f*.3;torus.rotation.y=f*.5;renderer.render(scene,camera)})();
    }catch(e){console.warn(e)}
})();

document.addEventListener('DOMContentLoaded',function(){
    if(!window.gsap)return;
    gsap.registerPlugin(ScrollTrigger);
    ScrollTrigger.create({start:'top -60',onToggle:s=>document.getElementById('mainNav').classList.toggle('scrolled',s.isActive)});
    // Hero
    const tl=gsap.timeline({delay:.2});
    tl.fromTo('#heroBadge',{y:20,opacity:0},{y:0,opacity:1,duration:.7,ease:'power3.out'})
      .fromTo('#heroH1',{y:50,opacity:0},{y:0,opacity:1,duration:.9,ease:'power3.out'},'-=.4')
      .fromTo('#heroSub',{y:30,opacity:0},{y:0,opacity:1,duration:.7,ease:'power3.out'},'-=.5');
    // Social cards stagger alternating
    gsap.fromTo('#scWa',{x:-80,opacity:0},{x:0,opacity:1,duration:.9,ease:'power3.out',scrollTrigger:{trigger:'#scWa',start:'top 85%'}});
    gsap.fromTo('#scFb',{x:80,opacity:0},{x:0,opacity:1,duration:.9,ease:'power3.out',scrollTrigger:{trigger:'#scFb',start:'top 85%'}});
    gsap.fromTo('#scIg',{x:-80,opacity:0},{x:0,opacity:1,duration:.9,ease:'power3.out',scrollTrigger:{trigger:'#scIg',start:'top 85%'}});
    // Form
    gsap.fromTo('#formCard',{y:60,opacity:0},{y:0,opacity:1,duration:1,ease:'power3.out',scrollTrigger:{trigger:'#formCard',start:'top 80%'}});
    // Generic reveals
    document.querySelectorAll('.reveal').forEach(el=>{
        if(['scWa','scFb','scIg','formCard'].includes(el.id))return;
        gsap.fromTo(el,{y:40,opacity:0},{y:0,opacity:1,duration:.9,ease:'power3.out',scrollTrigger:{trigger:el,start:'top 88%'}});
    });
});
</script>
</body>
</html>