<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=5.0,user-scalable=yes,viewport-fit=cover">
<meta name="theme-color" content="#1e40af">
<meta name="apple-mobile-web-app-capable" content="yes">
<title>Inscription - Confirmi</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root{--royal-900:#1e3a8a;--royal-800:#1e40af;--royal-700:#2563eb;--royal-600:#3b82f6;--royal-500:#60a5fa;--royal-50:#eff6ff;--slate-900:#0f172a;--slate-600:#475569;--slate-500:#64748b;--slate-200:#e2e8f0;--slate-100:#f1f5f9;--slate-50:#f8fafc;--white:#fff;--gold:#fbbf24;--success:#10b981;--danger:#ef4444;--safe-area-bottom:env(safe-area-inset-bottom)}
*{margin:0;padding:0;box-sizing:border-box;-webkit-tap-highlight-color:transparent}
html{font-size:16px;height:100%}
body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--slate-50);color:var(--slate-900);min-height:100vh;display:flex;flex-direction:column;-webkit-font-smoothing:antialiased}
.header{padding:1.5rem 1rem;background:var(--white);border-bottom:1px solid var(--slate-200)}
.header-content{display:flex;align-items:center;gap:1rem;max-width:800px;margin:0 auto}
.back-btn{width:40px;height:40px;border-radius:12px;border:none;background:var(--slate-100);color:var(--slate-700);display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:1.125rem;transition:all .2s}
.back-btn:active{transform:scale(.95)}
.header-title{font-size:1.125rem;font-weight:800;color:var(--slate-900);flex:1}
.main{flex:1;padding:2rem 1rem;max-width:800px;margin:0 auto;width:100%}
.card{background:var(--white);border-radius:24px;padding:2rem 1.5rem;box-shadow:0 4px 24px rgba(30,64,175,.12);margin-bottom:1.5rem;border:1px solid rgba(30,64,175,.06)}
.logo{width:72px;height:72px;margin:0 auto 1rem;background:linear-gradient(135deg,var(--royal-700),var(--royal-800));border-radius:18px;display:flex;align-items:center;justify-content:center;box-shadow:0 8px 24px rgba(30,64,175,.25);border:2px solid rgba(59,130,246,.15)}
.logo i{font-size:2rem;color:var(--white)}
.title{font-size:1.625rem;font-weight:800;color:var(--slate-900);margin-bottom:.5rem;text-align:center}
.subtitle{font-size:.9375rem;color:var(--slate-600);text-align:center;margin-bottom:2rem}
.promo{background:linear-gradient(135deg,#fffbeb,#fef3c7);border:2px solid #f59e0b;border-radius:16px;padding:1.25rem;margin-bottom:2rem;position:relative;overflow:hidden}
.promo::before{content:'';position:absolute;top:0;left:-100%;width:100%;height:100%;background:linear-gradient(90deg,transparent,rgba(255,255,255,.5),transparent);animation:shimmer 3s ease-in-out infinite}
@keyframes shimmer{0%{left:-100%}100%{left:100%}}
.promo-content{display:flex;align-items:center;gap:1rem;position:relative}
.promo-icon{width:50px;height:50px;background:rgba(245,158,11,.2);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.promo-icon i{font-size:1.5rem;color:#d97706;animation:bounce 2s ease-in-out infinite}
@keyframes bounce{0%,100%{transform:translateY(0)}50%{transform:translateY(-5px)}}
.promo-text h3{font-size:1rem;font-weight:800;color:#92400e;margin-bottom:.25rem}
.promo-text p{font-size:.8125rem;color:#78350f;font-weight:600;margin:0}
.grid{display:grid;gap:1.25rem;margin-bottom:1.25rem}
.form-group{display:flex;flex-direction:column}
.label{display:flex;align-items:center;gap:.5rem;font-size:.8125rem;font-weight:700;color:var(--slate-900);margin-bottom:.625rem}
.label i{color:var(--royal-700)}
.input{width:100%;padding:.875rem 1rem;border:2px solid var(--slate-200);border-radius:14px;font-size:.9375rem;font-family:inherit;transition:all .2s;background:var(--white)}
.input:focus{outline:none;border-color:var(--royal-600);box-shadow:0 0 0 4px rgba(59,130,246,.12)}
.strength{margin-top:.875rem;padding:1rem;background:var(--slate-50);border-radius:12px;border:2px solid var(--slate-200);display:none}
.strength-header{display:flex;justify-content:space-between;margin-bottom:.75rem}
.strength-bar{height:4px;border-radius:2px;background:var(--slate-200);margin-bottom:.75rem;overflow:hidden}
.strength-fill{height:100%;transition:all .3s;border-radius:2px}
.weak{background:linear-gradient(90deg,#ef4444,#dc2626);width:25%}
.fair{background:linear-gradient(90deg,#f59e0b,#d97706);width:50%}
.good{background:linear-gradient(90deg,var(--royal-600),var(--royal-700));width:75%}
.strong{background:linear-gradient(90deg,#10b981,#059669);width:100%}
.req{display:flex;align-items:center;gap:.5rem;font-size:.75rem;color:var(--slate-600);margin-bottom:.375rem}
.req.met{color:#059669}
.req i{font-size:.875rem}
.btn{width:100%;padding:1.125rem;background:linear-gradient(135deg,var(--royal-700),var(--royal-900));color:var(--white);border:none;border-radius:14px;font-size:1.0625rem;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.75rem;transition:all .2s;box-shadow:0 4px 16px rgba(30,64,175,.3);margin-top:2rem}
.btn:active{transform:scale(.98)}
.login{text-align:center;padding-top:1.25rem;border-top:1px solid var(--slate-200);margin-top:1.5rem}
.login p{font-size:.875rem;color:var(--slate-600);margin-bottom:.75rem}
.login a{display:inline-flex;align-items:center;gap:.625rem;padding:.875rem 1.5rem;background:var(--royal-50);color:var(--royal-700);border-radius:14px;text-decoration:none;font-weight:700;font-size:.9375rem;transition:all .2s;border:1px solid rgba(30,64,175,.15)}
.login a:active{transform:scale(.98);background:var(--royal-600);color:var(--white)}
.footer{padding:1.5rem 1rem calc(1.5rem + var(--safe-area-bottom));text-align:center}
.footer a{display:inline-flex;align-items:center;gap:.5rem;color:var(--slate-500);text-decoration:none;font-size:.875rem;font-weight:600;padding:.75rem 1.25rem;border-radius:12px;transition:all .2s}
@media(min-width:640px){.main{padding:3rem 2rem}.card{padding:3rem 2.5rem}.grid{grid-template-columns:1fr 1fr}}
</style>
</head>
<body>
<header class="header">
<div class="header-content">
<a href="{{ route('confirmi.home') }}" class="back-btn"><i class="fas fa-arrow-left"></i></a>
<h1 class="header-title">Inscription</h1>
</div>
</header>
<main class="main">
<div class="card">
<div class="logo"><i class="fas fa-rocket"></i></div>
<h2 class="title">Créer votre compte</h2>
<p class="subtitle">Commencez votre essai gratuit maintenant</p>
<div class="promo">
<div class="promo-content">
<div class="promo-icon"><i class="fas fa-gift"></i></div>
<div class="promo-text">
<h3>🎉 Essai professionnel gratuit</h3>
<p>14 jours • Accès complet • Aucune CB requise</p>
</div>
</div>
</div>
<form action="{{ route('register.submit') }}" method="POST" id="form">
@csrf
<div class="grid">
<div class="form-group">
<label class="label"><i class="fas fa-user"></i><span>Nom complet</span></label>
<input type="text" class="input" name="name" placeholder="Votre nom" value="{{ old('name') }}" required>
</div>
<div class="form-group">
<label class="label"><i class="fas fa-envelope"></i><span>Email</span></label>
<input type="email" class="input" name="email" placeholder="email@exemple.com" value="{{ old('email') }}" required>
</div>
<div class="form-group">
<label class="label"><i class="fas fa-lock"></i><span>Mot de passe</span></label>
<input type="password" class="input" name="password" placeholder="••••••••" id="pwd" required>
<div class="strength" id="str">
<div class="strength-header"><small style="font-size:.75rem;color:var(--slate-600);font-weight:600">Force:</small><small id="txt" style="font-size:.8125rem;font-weight:800">Faible</small></div>
<div class="strength-bar"><div class="strength-fill" id="fill"></div></div>
<div class="req" id="r1"><i class="fas fa-times"></i><span>8+ caractères</span></div>
<div class="req" id="r2"><i class="fas fa-times"></i><span>Majuscule</span></div>
<div class="req" id="r3"><i class="fas fa-times"></i><span>Minuscule</span></div>
<div class="req" id="r4"><i class="fas fa-times"></i><span>Chiffre</span></div>
</div>
</div>
<div class="form-group">
<label class="label"><i class="fas fa-check-double"></i><span>Confirmer</span></label>
<input type="password" class="input" name="password_confirmation" placeholder="••••••••" required>
</div>
<div class="form-group">
<label class="label"><i class="fas fa-store"></i><span>Boutique</span></label>
<input type="text" class="input" name="shop_name" placeholder="Ma Boutique" value="{{ old('shop_name') }}" required>
</div>
<div class="form-group">
<label class="label"><i class="fas fa-phone"></i><span>Téléphone <small style="color:var(--slate-500)">(opt)</small></span></label>
<input type="tel" class="input" name="phone" placeholder="+216 XX XXX XXX" value="{{ old('phone') }}">
</div>
</div>
<button type="submit" class="btn"><i class="fas fa-rocket"></i><span>Démarrer l'essai gratuit</span></button>
</form>
<div class="login">
<p>Vous avez déjà un compte ?</p>
<a href="{{ route('login') }}"><i class="fas fa-sign-in-alt"></i><span>Se connecter</span></a>
</div>
</div>
</main>
<footer class="footer"><a href="{{ route('confirmi.home') }}"><i class="fas fa-arrow-left"></i><span>Retour</span></a></footer>
<script>
const p=document.getElementById('pwd'),s=document.getElementById('str'),f=document.getElementById('fill'),t=document.getElementById('txt');
p.addEventListener('input',function(){
const v=this.value;s.style.display=v.length>0?'block':'none';
if(v.length>0){
const r={l:v.length>=8,u:/[A-Z]/.test(v),lo:/[a-z]/.test(v),n:/\d/.test(v)};
['l','u','lo','n'].forEach((k,i)=>{
const e=document.getElementById(`r${i+1}`),ic=e.querySelector('i');
if(r[k]){e.classList.add('met');ic.className='fas fa-check';}else{e.classList.remove('met');ic.className='fas fa-times';}
});
const sc=Object.values(r).filter(Boolean).length;
f.className='strength-fill';
if(sc===1){f.classList.add('weak');t.textContent='Faible';}
else if(sc===2){f.classList.add('fair');t.textContent='Moyen';}
else if(sc===3){f.classList.add('good');t.textContent='Bon';}
else if(sc===4){f.classList.add('strong');t.textContent='Excellent';}
}
});
</script>
</body>
</html>
