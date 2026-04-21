<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#0a0a0f">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Confirmi.space — From Order to Delivery, Effortlessly.</title>
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
tailwind.config={theme:{extend:{fontFamily:{sans:['Inter','ui-sans-serif'],grotesk:['Space Grotesk','sans-serif']},colors:{brand:{50:'#eef2ff',100:'#e0e7ff',200:'#c7d2fe',300:'#a5b4fc',400:'#818cf8',500:'#6366f1',600:'#4f46e5',700:'#4338ca',800:'#3730a3',900:'#312e81'}},boxShadow:{glow:'0 0 60px rgba(99,102,241,0.4)',glow2:'0 0 120px rgba(99,102,241,0.2)','glow-sm':'0 0 30px rgba(99,102,241,0.3)'}}}}
</script>
<style>
[x-cloak]{display:none!important}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --c-bg:#05050a;
  --c-surface:#0d0d18;
  --c-border:rgba(99,102,241,0.15);
  --c-brand:#6366f1;
  --c-brand2:#4f46e5;
  --c-text:#e2e8f0;
  --c-muted:#64748b;
  --c-glow:rgba(99,102,241,0.5);
}
html{scroll-behavior:smooth}
body{font-family:'Inter',sans-serif;background:var(--c-bg);color:var(--c-text);overflow-x:hidden;-webkit-font-smoothing:antialiased}

/* scrollbar */
::-webkit-scrollbar{width:4px}
::-webkit-scrollbar-track{background:var(--c-bg)}
::-webkit-scrollbar-thumb{background:var(--c-brand2);border-radius:99px}

/* canvas bg */
#bgCanvas{position:fixed;inset:0;z-index:0;pointer-events:none;opacity:.55}

/* smooth scroll wrapper */
#smooth-wrapper{overflow:hidden}
#smooth-content{will-change:transform}

/* nav */
.cin-nav{
  position:fixed;top:0;left:0;right:0;z-index:100;
  padding:1.1rem 2rem;
  display:flex;align-items:center;justify-content:space-between;
  transition:background .4s,backdrop-filter .4s,border-color .4s;
}
.cin-nav.scrolled{
  background:rgba(5,5,10,0.85);
  backdrop-filter:blur(24px);
  border-bottom:1px solid var(--c-border);
}
.nav-logo{display:flex;align-items:center;gap:.6rem;text-decoration:none}
.nav-logo-icon{
  width:34px;height:34px;border-radius:10px;
  background:linear-gradient(135deg,#6366f1,#4f46e5);
  display:flex;align-items:center;justify-content:center;
  font-size:.75rem;color:#fff;
  box-shadow:0 0 20px rgba(99,102,241,.5);
}
.nav-logo-text{font-family:'Space Grotesk',sans-serif;font-size:1.05rem;font-weight:700;color:#fff;letter-spacing:-.02em}
.nav-logo-text span{color:#818cf8}
.nav-links{display:flex;align-items:center;gap:2.2rem}
.nav-links a{font-size:.82rem;font-weight:600;color:rgba(226,232,240,.6);text-decoration:none;letter-spacing:.02em;transition:color .2s}
.nav-links a:hover,.nav-links a.active{color:#fff}
.nav-btns{display:flex;align-items:center;gap:.7rem}
.btn-ghost{
  padding:.5rem 1.1rem;border-radius:10px;
  border:1px solid rgba(99,102,241,.3);
  font-size:.8rem;font-weight:700;color:#a5b4fc;
  background:transparent;text-decoration:none;
  transition:border-color .2s,color .2s,background .2s;cursor:pointer;
}
.btn-ghost:hover{border-color:#6366f1;background:rgba(99,102,241,.1);color:#c7d2fe}
.btn-primary{
  padding:.5rem 1.25rem;border-radius:10px;
  background:linear-gradient(135deg,#6366f1,#4338ca);
  font-size:.8rem;font-weight:800;color:#fff;
  text-decoration:none;
  box-shadow:0 0 24px rgba(99,102,241,.4);
  transition:box-shadow .2s,transform .2s;
}
.btn-primary:hover{box-shadow:0 0 40px rgba(99,102,241,.6);transform:translateY(-1px)}

/* HERO */
.hero-section{
  position:relative;z-index:1;
  min-height:100svh;
  display:flex;align-items:center;justify-content:center;
  padding:7rem 2rem 4rem;
  overflow:hidden;
}
.hero-noise{
  position:absolute;inset:0;z-index:0;pointer-events:none;
  background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='.03'/%3E%3C/svg%3E");
  opacity:.4;
}
.hero-glow-orb{
  position:absolute;border-radius:50%;pointer-events:none;
  background:radial-gradient(circle,var(--c-glow),transparent 70%);
  filter:blur(0px);
}
.hero-content{position:relative;z-index:2;text-align:center;max-width:900px;margin:0 auto}
.hero-badge{
  display:inline-flex;align-items:center;gap:.5rem;
  padding:.4rem 1rem;border-radius:99px;
  border:1px solid rgba(99,102,241,.35);
  background:rgba(99,102,241,.08);
  font-size:.7rem;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:#818cf8;
  margin-bottom:2rem;
}
.hero-badge-dot{width:5px;height:5px;border-radius:50%;background:#6366f1;animation:pulse-dot 2s ease infinite}
@keyframes pulse-dot{0%,100%{box-shadow:0 0 0 0 rgba(99,102,241,.8)}50%{box-shadow:0 0 0 6px rgba(99,102,241,0)}}
.hero-h1{
  font-family:'Space Grotesk',sans-serif;
  font-size:clamp(2.8rem,8vw,6.5rem);
  font-weight:700;
  line-height:.95;
  letter-spacing:-.04em;
  color:#fff;
  margin-bottom:1.8rem;
}
.hero-h1 .grad{
  background:linear-gradient(135deg,#818cf8 0%,#6366f1 40%,#a78bfa 100%);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
}
.hero-sub{
  font-size:1.05rem;font-weight:400;color:rgba(226,232,240,.55);
  max-width:560px;margin:0 auto 2.8rem;line-height:1.8;
}
.hero-cta-row{display:flex;align-items:center;justify-content:center;gap:1rem;flex-wrap:wrap;margin-bottom:3.5rem}
.hero-cta-primary{
  display:inline-flex;align-items:center;gap:.6rem;
  padding:.95rem 2.2rem;border-radius:14px;
  background:linear-gradient(135deg,#6366f1,#4338ca);
  font-size:.9rem;font-weight:800;color:#fff;text-decoration:none;
  box-shadow:0 0 40px rgba(99,102,241,.45),inset 0 1px 0 rgba(255,255,255,.1);
  transition:box-shadow .25s,transform .25s;
  position:relative;overflow:hidden;
}
.hero-cta-primary::after{
  content:'';position:absolute;inset:0;
  background:linear-gradient(135deg,rgba(255,255,255,.08),transparent);
}
.hero-cta-primary:hover{box-shadow:0 0 70px rgba(99,102,241,.65);transform:translateY(-2px)}
.hero-cta-secondary{
  display:inline-flex;align-items:center;gap:.6rem;
  padding:.95rem 2rem;border-radius:14px;
  border:1px solid rgba(99,102,241,.3);
  background:rgba(99,102,241,.06);
  font-size:.9rem;font-weight:700;color:#a5b4fc;text-decoration:none;
  transition:border-color .2s,background .2s;
}
.hero-cta-secondary:hover{border-color:#6366f1;background:rgba(99,102,241,.12)}
.hero-stats{
  display:flex;align-items:center;justify-content:center;gap:3rem;flex-wrap:wrap;
  padding-top:2rem;border-top:1px solid rgba(99,102,241,.12);
}
.hero-stat-val{font-family:'Space Grotesk',sans-serif;font-size:2rem;font-weight:700;color:#fff;letter-spacing:-.04em}
.hero-stat-val span{color:#818cf8;font-size:1.2rem}
.hero-stat-lbl{font-size:.72rem;font-weight:600;color:rgba(226,232,240,.4);letter-spacing:.08em;text-transform:uppercase;margin-top:.2rem}

/* SCROLL INDICATOR */
.scroll-indicator{
  position:absolute;bottom:2.5rem;left:50%;transform:translateX(-50%);
  display:flex;flex-direction:column;align-items:center;gap:.5rem;
  color:rgba(226,232,240,.3);font-size:.65rem;letter-spacing:.1em;text-transform:uppercase;
}
.scroll-mouse{
  width:22px;height:36px;border-radius:99px;
  border:1.5px solid rgba(226,232,240,.2);
  display:flex;justify-content:center;padding-top:6px;
}
.scroll-wheel{width:3px;height:6px;background:rgba(226,232,240,.4);border-radius:99px;animation:scroll-whl 1.5s ease infinite}
@keyframes scroll-whl{0%{opacity:1;transform:translateY(0)}100%{opacity:0;transform:translateY(10px)}}

/* SECTION BASE */
.cin-section{position:relative;z-index:1;padding:8rem 2rem}
.section-label{
  display:inline-flex;align-items:center;gap:.5rem;
  font-size:.68rem;font-weight:800;letter-spacing:.18em;text-transform:uppercase;
  color:#6366f1;margin-bottom:1.2rem;
}
.section-label::before{content:'';width:24px;height:1.5px;background:#6366f1;display:block}
.section-h2{
  font-family:'Space Grotesk',sans-serif;
  font-size:clamp(2rem,5vw,3.8rem);
  font-weight:700;line-height:1.05;letter-spacing:-.04em;color:#fff;
  margin-bottom:1.2rem;
}
.section-h2 .grad{
  background:linear-gradient(90deg,#818cf8,#a78bfa);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
}
.section-sub{font-size:.975rem;color:rgba(226,232,240,.5);line-height:1.75;max-width:540px}

/* TIMELINE / PIPELINE */
.pipeline-wrap{position:relative;max-width:900px;margin:5rem auto 0}
.pipeline-line{
  position:absolute;left:50%;top:0;bottom:0;width:1px;
  background:linear-gradient(to bottom,transparent,rgba(99,102,241,.3) 10%,rgba(99,102,241,.3) 90%,transparent);
  transform:translateX(-50%);
}
.pipeline-node{
  display:grid;grid-template-columns:1fr 60px 1fr;
  align-items:center;gap:1.5rem;
  margin-bottom:3rem;
}
.pipeline-card{
  background:rgba(13,13,24,.9);
  border:1px solid rgba(99,102,241,.18);
  border-radius:20px;padding:1.8rem;
  backdrop-filter:blur(20px);
  transition:border-color .3s,box-shadow .3s;
  position:relative;overflow:hidden;
}
.pipeline-card::before{
  content:'';position:absolute;top:0;left:0;right:0;height:1px;
  background:linear-gradient(90deg,transparent,rgba(99,102,241,.5),transparent);
}
.pipeline-card:hover{border-color:rgba(99,102,241,.4);box-shadow:0 0 40px rgba(99,102,241,.12)}
.pipeline-dot{
  width:44px;height:44px;border-radius:50%;
  background:linear-gradient(135deg,#6366f1,#4338ca);
  display:flex;align-items:center;justify-content:center;
  color:#fff;font-size:.85rem;font-weight:900;
  box-shadow:0 0 30px rgba(99,102,241,.5);
  margin:0 auto;position:relative;z-index:1;
}
.pipeline-dot::after{
  content:'';position:absolute;inset:-6px;border-radius:50%;
  border:1px solid rgba(99,102,241,.3);animation:ping-ring 2s ease infinite;
}
@keyframes ping-ring{0%{transform:scale(1);opacity:.6}100%{transform:scale(1.5);opacity:0}}
.pcard-icon{
  width:44px;height:44px;border-radius:12px;
  background:rgba(99,102,241,.1);border:1px solid rgba(99,102,241,.2);
  display:flex;align-items:center;justify-content:center;
  font-size:1.1rem;color:#818cf8;margin-bottom:1rem;
}
.pcard-title{font-family:'Space Grotesk',sans-serif;font-size:1.1rem;font-weight:700;color:#fff;margin-bottom:.5rem;letter-spacing:-.02em}
.pcard-desc{font-size:.825rem;color:rgba(226,232,240,.5);line-height:1.7}
.pcard-kpi{
  display:inline-block;margin-top:.8rem;
  font-size:.7rem;font-weight:800;letter-spacing:.06em;
  padding:.3rem .7rem;border-radius:8px;
  background:rgba(99,102,241,.1);border:1px solid rgba(99,102,241,.2);color:#818cf8;
}

/* BENTO GRID */
.bento-grid{
  display:grid;
  grid-template-columns:repeat(12,1fr);
  grid-template-rows:auto;
  gap:1.25rem;
  max-width:1100px;margin:4rem auto 0;
}
.bento-cell{
  background:rgba(13,13,24,.9);
  border:1px solid rgba(99,102,241,.15);
  border-radius:24px;padding:2rem;
  backdrop-filter:blur(20px);
  position:relative;overflow:hidden;
  transition:border-color .3s,transform .3s;
}
.bento-cell:hover{border-color:rgba(99,102,241,.35);transform:translateY(-4px)}
.bento-cell::before{
  content:'';position:absolute;top:0;left:0;right:0;height:1px;
  background:linear-gradient(90deg,transparent,rgba(99,102,241,.4),transparent);
}
.bc-1{grid-column:span 7}
.bc-2{grid-column:span 5}
.bc-3{grid-column:span 4}
.bc-4{grid-column:span 4}
.bc-5{grid-column:span 4}
.bc-6{grid-column:span 12}
@media(max-width:768px){
  .bento-grid{grid-template-columns:1fr;gap:.9rem}
  .bc-1,.bc-2,.bc-3,.bc-4,.bc-5,.bc-6{grid-column:span 1}
  .pipeline-node{grid-template-columns:1fr;gap:1rem}
  .pipeline-line{display:none}
}
.bento-num{
  font-family:'Space Grotesk',sans-serif;
  font-size:3.5rem;font-weight:700;color:#fff;
  letter-spacing:-.06em;line-height:1;
  margin-bottom:.4rem;
}
.bento-num .unit{font-size:1.6rem;color:#6366f1}
.bento-label{font-size:.78rem;font-weight:600;color:rgba(226,232,240,.4);letter-spacing:.06em;text-transform:uppercase}

/* mini bar chart */
.mini-bars{display:flex;align-items:flex-end;gap:4px;height:48px;margin-top:1rem}
.mini-bar{flex:1;border-radius:4px 4px 0 0;background:rgba(99,102,241,.25);transition:height .5s ease}
.mini-bar.lit{background:linear-gradient(to top,#4f46e5,#818cf8)}

/* orbit ecosystem */
.ecosystem-wrap{position:relative;width:360px;height:360px;margin:0 auto}
.eco-center{
  position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);
  width:80px;height:80px;border-radius:50%;
  background:linear-gradient(135deg,#4f46e5,#312e81);
  border:2px solid rgba(99,102,241,.4);
  box-shadow:0 0 60px rgba(99,102,241,.5);
  display:flex;align-items:center;justify-content:center;
  font-size:.6rem;font-weight:800;color:#c7d2fe;letter-spacing:.06em;text-align:center;line-height:1.3;
  z-index:2;
}
.eco-ring{
  position:absolute;top:50%;left:50%;border-radius:50%;
  border:1px dashed rgba(99,102,241,.2);
  transform:translate(-50%,-50%);
}
.eco-ring-1{width:190px;height:190px;animation:spin-slow 18s linear infinite}
.eco-ring-2{width:310px;height:310px;animation:spin-slow 28s linear infinite reverse}
@keyframes spin-slow{from{transform:translate(-50%,-50%) rotate(0deg)}to{transform:translate(-50%,-50%) rotate(360deg)}}
.eco-node{
  position:absolute;top:50%;left:50%;
  width:40px;height:40px;border-radius:50%;
  background:var(--c-surface);border:1px solid rgba(99,102,241,.3);
  display:flex;align-items:center;justify-content:center;
  font-size:.65rem;color:#818cf8;font-weight:700;
  transform-origin:0 0;
  box-shadow:0 0 16px rgba(99,102,241,.2);
}

/* CODE PANEL */
.code-panel{
  background:#070712;
  border:1px solid rgba(99,102,241,.2);
  border-radius:16px;overflow:hidden;
  font-family:'SF Mono','Fira Code',monospace;
  font-size:.75rem;line-height:1.9;
}
.code-panel-header{
  display:flex;align-items:center;gap:.5rem;
  padding:.65rem 1rem;
  border-bottom:1px solid rgba(99,102,241,.15);
  background:rgba(99,102,241,.05);
}
.code-dot{width:10px;height:10px;border-radius:50%}
.code-body{padding:1.2rem 1.4rem;overflow-x:auto}
.ck{color:#818cf8;font-weight:700}
.cv{color:#34d399}
.cs{color:#fbbf24}
.cc{color:rgba(226,232,240,.3);font-style:italic}

/* FEATURE ROW */
.feat-row{display:grid;grid-template-columns:1fr 1fr;gap:5rem;align-items:center;max-width:1100px;margin:0 auto}
.feat-row.rev{direction:rtl}
.feat-row.rev>*{direction:ltr}
@media(max-width:768px){.feat-row,.feat-row.rev{grid-template-columns:1fr;gap:3rem}}
.feat-icon-big{
  width:64px;height:64px;border-radius:20px;
  background:linear-gradient(135deg,rgba(99,102,241,.2),rgba(67,56,202,.1));
  border:1px solid rgba(99,102,241,.25);
  display:flex;align-items:center;justify-content:center;
  font-size:1.5rem;color:#818cf8;margin-bottom:1.5rem;
  position:relative;
}
.feat-icon-big::after{
  content:'';position:absolute;inset:-1px;border-radius:20px;
  background:linear-gradient(135deg,rgba(99,102,241,.15),transparent);
  pointer-events:none;
}
.feat-list{margin-top:1.5rem;display:flex;flex-direction:column;gap:.9rem}
.feat-list-item{display:flex;align-items:flex-start;gap:.75rem;font-size:.875rem;color:rgba(226,232,240,.65);line-height:1.6}
.feat-list-item i{color:#6366f1;font-size:.8rem;margin-top:.25rem;flex-shrink:0}
.feat-list-item strong{color:#e2e8f0}

/* ORDER LIFECYCLE */
.lifecycle-grid{display:grid;grid-template-columns:1.1fr .9fr;gap:3rem;max-width:1100px;margin:4rem auto 0;align-items:start}
@media(max-width:900px){.lifecycle-grid{grid-template-columns:1fr}}
.life-track{position:relative;padding-left:2rem}
.life-track::before{content:'';position:absolute;left:7px;top:.3rem;bottom:.3rem;width:2px;background:linear-gradient(to bottom,rgba(99,102,241,.08),rgba(99,102,241,.35),rgba(99,102,241,.08))}
.life-step{position:relative;padding:1rem 1.1rem 1rem 1.3rem;border:1px solid rgba(99,102,241,.12);background:rgba(13,13,24,.75);border-radius:16px;margin-bottom:.9rem;transition:all .25s}
.life-step::before{content:'';position:absolute;left:-1.95rem;top:1.3rem;width:14px;height:14px;border-radius:50%;border:2px solid rgba(99,102,241,.3);background:#090914;box-shadow:0 0 0 4px rgba(99,102,241,.05)}
.life-step.active{border-color:rgba(99,102,241,.45);transform:translateX(6px);box-shadow:0 0 40px rgba(99,102,241,.16)}
.life-step.active::before{background:linear-gradient(135deg,#6366f1,#4338ca);border-color:#818cf8;box-shadow:0 0 0 4px rgba(99,102,241,.18),0 0 20px rgba(99,102,241,.5)}
.life-step-title{font-family:'Space Grotesk',sans-serif;font-size:1rem;font-weight:700;color:#fff;margin-bottom:.25rem}
.life-step-desc{font-size:.8rem;line-height:1.6;color:rgba(226,232,240,.48)}
.life-step-meta{display:inline-flex;align-items:center;gap:.45rem;margin-top:.5rem;padding:.28rem .6rem;border-radius:8px;font-size:.68rem;font-weight:700;letter-spacing:.05em;color:#a5b4fc;background:rgba(99,102,241,.1);border:1px solid rgba(99,102,241,.22)}

.life-sticky{position:sticky;top:100px}
.order-stage{border-radius:24px;border:1px solid rgba(99,102,241,.2);background:linear-gradient(145deg,rgba(13,13,24,.98),rgba(16,13,32,.95));padding:1.3rem;box-shadow:0 0 70px rgba(99,102,241,.14)}
.order-top{display:flex;align-items:center;justify-content:space-between;gap:1rem;margin-bottom:1rem}
.order-id{font-family:'Space Grotesk',sans-serif;font-size:1.05rem;font-weight:700;color:#fff;letter-spacing:.01em}
.order-chip{padding:.33rem .7rem;border-radius:999px;font-size:.66rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase;border:1px solid rgba(99,102,241,.3);background:rgba(99,102,241,.14);color:#c7d2fe}
.order-3d-zone{height:170px;display:flex;align-items:center;justify-content:center;border:1px solid rgba(99,102,241,.16);border-radius:16px;background:radial-gradient(circle at 50% 25%,rgba(99,102,241,.16),rgba(6,6,12,.7) 65%);margin-bottom:1rem;overflow:hidden;position:relative}
.pack-cube{position:relative;width:74px;height:74px;transform-style:preserve-3d;animation:cube-spin 7s linear infinite}
.pack-face{position:absolute;inset:0;border:1px solid rgba(129,140,248,.35);background:linear-gradient(135deg,rgba(79,70,229,.55),rgba(67,56,202,.2));backdrop-filter:blur(4px)}
.pack-face.f1{transform:translateZ(37px)}
.pack-face.f2{transform:rotateY(180deg) translateZ(37px)}
.pack-face.f3{transform:rotateY(90deg) translateZ(37px)}
.pack-face.f4{transform:rotateY(-90deg) translateZ(37px)}
.pack-face.f5{transform:rotateX(90deg) translateZ(37px)}
.pack-face.f6{transform:rotateX(-90deg) translateZ(37px)}
@keyframes cube-spin{0%{transform:rotateX(-16deg) rotateY(0deg)}100%{transform:rotateX(-16deg) rotateY(360deg)}}
.scanner-line{position:absolute;left:10%;right:10%;height:2px;background:linear-gradient(90deg,transparent,#a5b4fc,transparent);box-shadow:0 0 18px rgba(129,140,248,.8);animation:scanner-sweep 2.4s ease-in-out infinite}
@keyframes scanner-sweep{0%,100%{top:22%}50%{top:78%}}
.order-state{padding:1rem;border-radius:14px;border:1px solid rgba(99,102,241,.2);background:rgba(7,7,18,.9)}
.state-label{font-size:.67rem;font-weight:700;letter-spacing:.08em;color:rgba(226,232,240,.38);text-transform:uppercase;margin-bottom:.4rem}
.state-value{font-family:'Space Grotesk',sans-serif;font-size:1.18rem;font-weight:700;color:#fff;letter-spacing:-.02em;margin-bottom:.3rem}
.state-sub{font-size:.78rem;line-height:1.6;color:rgba(226,232,240,.48)}
.order-progress{margin-top:.8rem;height:6px;border-radius:999px;background:rgba(99,102,241,.13);overflow:hidden}
.order-progress-fill{height:100%;width:18%;border-radius:999px;background:linear-gradient(90deg,#6366f1,#818cf8);box-shadow:0 0 16px rgba(99,102,241,.5);transition:width .45s ease}

/* PRICING */
.pricing-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:1.5rem;max-width:1160px;margin:4rem auto 0}
@media(max-width:768px){.pricing-grid{grid-template-columns:1fr}}
.price-card{
  background:rgba(13,13,24,.95);
  border:1px solid rgba(99,102,241,.15);
  border-radius:24px;padding:2.2rem;
  position:relative;overflow:hidden;
  transition:transform .3s,border-color .3s,box-shadow .3s;
}
.price-card:hover{transform:translateY(-6px);border-color:rgba(99,102,241,.35);box-shadow:0 0 60px rgba(99,102,241,.15)}
.price-card.featured{
  border-color:rgba(99,102,241,.5);
  box-shadow:0 0 80px rgba(99,102,241,.2);
}
.price-card.featured::before{
  content:'';position:absolute;top:0;left:0;right:0;height:2px;
  background:linear-gradient(90deg,#4338ca,#818cf8,#4338ca);
}
.price-badge{
  display:inline-block;margin-bottom:.8rem;
  font-size:.65rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase;
  padding:.3rem .8rem;border-radius:8px;
  background:rgba(99,102,241,.15);color:#818cf8;
}
.price-name{font-family:'Space Grotesk',sans-serif;font-size:1.2rem;font-weight:700;color:#fff;margin-bottom:.4rem}
.price-amount{
  font-family:'Space Grotesk',sans-serif;font-size:3rem;font-weight:700;color:#fff;
  letter-spacing:-.05em;line-height:1;margin:1.2rem 0;
}
.price-amount .cur{font-size:1.4rem;color:#6366f1}
.price-amount .per{font-size:.85rem;font-weight:500;color:rgba(226,232,240,.4)}
.price-desc{font-size:.82rem;color:rgba(226,232,240,.45);line-height:1.6;margin-bottom:1.5rem;padding-bottom:1.5rem;border-bottom:1px solid rgba(99,102,241,.1)}
.price-feature{display:flex;align-items:flex-start;gap:.6rem;font-size:.8rem;color:rgba(226,232,240,.6);line-height:1.5;margin-bottom:.7rem}
.price-feature i{color:#6366f1;font-size:.7rem;margin-top:.3rem;flex-shrink:0}
.price-btn{
  display:block;width:100%;margin-top:1.8rem;
  padding:.85rem;border-radius:14px;text-align:center;text-decoration:none;
  font-size:.85rem;font-weight:800;
  transition:all .25s;
}
.price-btn-ghost{
  border:1px solid rgba(99,102,241,.3);color:#818cf8;
  background:transparent;
}
.price-btn-ghost:hover{border-color:#6366f1;background:rgba(99,102,241,.1);color:#c7d2fe}
.price-btn-full{
  background:linear-gradient(135deg,#6366f1,#4338ca);color:#fff;
  box-shadow:0 0 30px rgba(99,102,241,.35);
}
.price-btn-full:hover{box-shadow:0 0 55px rgba(99,102,241,.55);transform:translateY(-1px)}

/* CTA BAND */
.cta-band{
  position:relative;z-index:1;
  padding:7rem 2rem;text-align:center;overflow:hidden;
}
.cta-band::before{
  content:'';position:absolute;inset:0;
  background:radial-gradient(ellipse 80% 80% at 50% 50%,rgba(99,102,241,.12),transparent);
  pointer-events:none;
}
.cta-band-title{
  font-family:'Space Grotesk',sans-serif;
  font-size:clamp(2rem,5vw,4rem);
  font-weight:700;color:#fff;letter-spacing:-.04em;
  line-height:1.05;margin-bottom:1.2rem;
}
.cta-band-sub{font-size:1rem;color:rgba(226,232,240,.5);max-width:500px;margin:0 auto 2.5rem}

/* FOOTER */
.cin-footer{
  position:relative;z-index:1;
  border-top:1px solid rgba(99,102,241,.1);
  background:rgba(5,5,10,.98);
  padding:4rem 2rem 2.5rem;
}
.footer-grid{display:grid;grid-template-columns:1.8fr 1fr 1fr 1fr;gap:3rem;max-width:1100px;margin:0 auto;padding-bottom:3rem;border-bottom:1px solid rgba(99,102,241,.08)}
@media(max-width:768px){.footer-grid{grid-template-columns:1fr 1fr;gap:2rem}}
.footer-col-label{font-size:.65rem;font-weight:800;letter-spacing:.16em;text-transform:uppercase;color:rgba(226,232,240,.25);margin-bottom:1rem}
.footer-col a{display:block;font-size:.82rem;font-weight:500;color:rgba(226,232,240,.5);text-decoration:none;margin-bottom:.6rem;transition:color .2s}
.footer-col a:hover{color:#818cf8}
.footer-bottom{max-width:1100px;margin:.0 auto;padding-top:2rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem}
.footer-copy{font-size:.75rem;color:rgba(226,232,240,.25)}
.footer-social{display:flex;gap:.8rem}
.footer-social a{
  width:34px;height:34px;border-radius:10px;
  border:1px solid rgba(99,102,241,.2);
  background:rgba(99,102,241,.05);
  display:flex;align-items:center;justify-content:center;
  font-size:.8rem;color:rgba(226,232,240,.4);
  text-decoration:none;transition:border-color .2s,color .2s;
}
.footer-social a:hover{border-color:#6366f1;color:#818cf8}

/* WA FLOAT */
.wa-float{
  position:fixed;bottom:1.8rem;right:1.8rem;z-index:200;
  width:54px;height:54px;border-radius:50%;
  background:linear-gradient(135deg,#25d366,#128c7e);
  box-shadow:0 0 30px rgba(37,211,102,.4);
  display:flex;align-items:center;justify-content:center;
  color:#fff;font-size:1.4rem;text-decoration:none;
  transition:transform .2s,box-shadow .2s;
}
.wa-float:hover{transform:scale(1.1) translateY(-2px);box-shadow:0 0 50px rgba(37,211,102,.6)}

/* MODAL */
@keyframes modal-spring{0%{transform:translateY(40px) scale(.88);opacity:0}55%{transform:translateY(-6px) scale(1.01);opacity:1}75%{transform:translateY(3px) scale(.99)}100%{transform:translateY(0) scale(1)}}
@keyframes fade-in-overlay{from{opacity:0}to{opacity:1}}
.modal-spring{animation:modal-spring 500ms cubic-bezier(.2,.9,.2,1) both}
.fade-in-overlay{animation:fade-in-overlay 280ms ease both}
.modal-input{
  width:100%;background:rgba(5,5,10,.8);
  border:1.5px solid rgba(99,102,241,.2);border-radius:12px;
  padding:.8rem 1rem;color:#e2e8f0;
  font-family:'Inter',sans-serif;font-size:.9rem;outline:none;
  transition:border-color .15s,box-shadow .15s;
}
.modal-input::placeholder{color:rgba(226,232,240,.25)}
.modal-input:focus{border-color:#6366f1;box-shadow:0 0 0 4px rgba(99,102,241,.12)}

/* GRID BG LINES */
.grid-lines{
  position:fixed;inset:0;z-index:0;pointer-events:none;
  background-image:
    linear-gradient(rgba(99,102,241,.03) 1px, transparent 1px),
    linear-gradient(90deg, rgba(99,102,241,.03) 1px, transparent 1px);
  background-size:80px 80px;
}

/* REVEAL ANIM */
.reveal{opacity:0;transform:translateY(40px)}
.reveal-left{opacity:0;transform:translateX(-60px)}
.reveal-right{opacity:0;transform:translateX(60px)}
.reveal-scale{opacity:0;transform:scale(.85)}
</style>
</head>
<body x-data="{ loginOpen: {{ request()->boolean('login') || $errors->hasAny(['email','password']) ? 'true' : 'false' }} }">
@include('partials._no-cache')

<!-- BACKGROUND ELEMENTS -->
<div class="grid-lines"></div>
<canvas id="bgCanvas"></canvas>

<!-- NAV -->
<nav class="cin-nav" id="mainNav">
    <a href="{{ route('confirmi.home') }}" class="nav-logo">
        <div class="nav-logo-icon"><i class="fas fa-bolt"></i></div>
        <span class="nav-logo-text">Confirmi<span>.space</span></span>
    </a>
    <div class="nav-links hidden md:flex">
        <a href="{{ route('confirmi.home') }}" class="active">Accueil</a>
        <a href="{{ route('confirmi.services') }}">Services</a>
        <a href="{{ route('confirmi.contact') }}">Contact</a>
        <a href="#pricing">Tarifs</a>
    </div>
    <div class="nav-btns">
        <a href="{{ route('confirmi.login') }}" @click.prevent="loginOpen = true" class="btn-ghost">Connexion</a>
        <a href="{{ route('confirmi.register') }}" class="btn-primary">S'inscrire</a>
    </div>
</nav>

<!-- WA FLOAT -->
<a href="https://wa.me/21693357722" target="_blank" rel="noopener noreferrer" class="wa-float" aria-label="WhatsApp">
    <i class="fab fa-whatsapp"></i>
</a>

<!-- ═══════════════════════════════════════════
     HERO
════════════════════════════════════════════ -->
<section class="hero-section" id="hero">
    <div class="hero-noise"></div>
    <!-- glow orbs -->
    <div class="hero-glow-orb" id="orb1" style="width:600px;height:600px;top:-200px;left:-200px;opacity:.25"></div>
    <div class="hero-glow-orb" id="orb2" style="width:500px;height:500px;bottom:-180px;right:-180px;opacity:.2"></div>
    <div class="hero-content" id="heroContent">
        <div class="hero-badge" id="heroBadge">
            <span class="hero-badge-dot"></span>
            Pipeline e-commerce nouvelle generation
        </div>
        <h1 class="hero-h1" id="heroH1">
            De la commande<br>a la livraison.<br><span class="grad">Sans effort.</span>
        </h1>
        <p class="hero-sub" id="heroSub">
            Confirmi orchestre chaque etape de votre e-commerce tunisien — confirmation SMS, routage livreur, stock atomique et gestion des retours.
        </p>
        <div class="hero-cta-row" id="heroCtas">
            <a href="{{ route('confirmi.register') }}" class="hero-cta-primary">
                Commencer gratuitement <i class="fas fa-arrow-right text-xs"></i>
            </a>
            <a href="https://wa.me/21693357722" target="_blank" rel="noopener noreferrer" class="hero-cta-secondary">
                <i class="fab fa-whatsapp" style="color:#25d366"></i> Demo WhatsApp
            </a>
        </div>
        <div class="hero-stats" id="heroStats">
            <div class="text-center">
                <div class="hero-stat-val" id="statConf">0<span>%</span></div>
                <div class="hero-stat-lbl">Taux confirmation</div>
            </div>
            <div style="width:1px;height:40px;background:rgba(99,102,241,.2)"></div>
            <div class="text-center">
                <div class="hero-stat-val" id="statCmd">0<span>+</span></div>
                <div class="hero-stat-lbl">Commandes / jour</div>
            </div>
            <div style="width:1px;height:40px;background:rgba(99,102,241,.2)"></div>
            <div class="text-center">
                <div class="hero-stat-val" id="statMin">0<span>min</span></div>
                <div class="hero-stat-lbl">Temps de setup</div>
            </div>
        </div>
    </div>
    <div class="scroll-indicator">
        <div class="scroll-mouse"><div class="scroll-wheel"></div></div>
        <span>Defiler</span>
    </div>
</section>

<!-- ═══════════════════════════════════════════
     PIPELINE / SERVICES
════════════════════════════════════════════ -->
<section class="cin-section" id="pipeline">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-2">
            <p class="section-label reveal" style="justify-content:center;margin-left:0">Services</p>
            <h2 class="section-h2 reveal" style="text-align:center">Un pipeline <span class="grad">tout-en-un</span></h2>
            <p class="section-sub reveal" style="text-align:center;margin:0 auto">Quatres moteurs. Un seul tableau de bord. Zero couture visible.</p>
        </div>
        <div class="pipeline-wrap">
            <div class="pipeline-line"></div>

            <!-- Node 1 -->
            <div class="pipeline-node reveal">
                <div class="pipeline-card">
                    <div class="pcard-icon"><i class="fas fa-comment-dots"></i></div>
                    <div class="pcard-title">SMS Automation</div>
                    <div class="pcard-desc">Confirmations automatiques multi-tentatives. Chaque reponse client met a jour le statut en temps reel via webhook.</div>
                    <span class="pcard-kpi">94% taux de confirmation</span>
                </div>
                <div class="pipeline-dot">01</div>
                <div></div>
            </div>

            <!-- Node 2 -->
            <div class="pipeline-node reveal">
                <div></div>
                <div class="pipeline-dot">02</div>
                <div class="pipeline-card">
                    <div class="pcard-icon" style="color:#38bdf8;background:rgba(56,189,248,.08);border-color:rgba(56,189,248,.2)"><i class="fas fa-plug"></i></div>
                    <div class="pcard-title">API Livraison</div>
                    <div class="pcard-desc">Connecte nativement a Kolixy et MasafaExpress. Routage automatique vers le meilleur transporteur.</div>
                    <span class="pcard-kpi" style="background:rgba(56,189,248,.08);border-color:rgba(56,189,248,.2);color:#38bdf8">Kolixy + MasafaExpress</span>
                </div>
            </div>

            <!-- Node 3 -->
            <div class="pipeline-node reveal">
                <div class="pipeline-card">
                    <div class="pcard-icon" style="color:#34d399;background:rgba(52,211,153,.08);border-color:rgba(52,211,153,.2)"><i class="fas fa-cubes"></i></div>
                    <div class="pcard-title">Stock Atomique</div>
                    <div class="pcard-desc">Decrementation transactionnelle ISO. Zero survente. Alertes automatiques et reactivation de file restock.</div>
                    <span class="pcard-kpi" style="background:rgba(52,211,153,.08);border-color:rgba(52,211,153,.2);color:#34d399">Decrementation atomique</span>
                </div>
                <div class="pipeline-dot">03</div>
                <div></div>
            </div>

            <!-- Node 4 -->
            <div class="pipeline-node reveal">
                <div></div>
                <div class="pipeline-dot">04</div>
                <div class="pipeline-card">
                    <div class="pcard-icon" style="color:#c084fc;background:rgba(192,132,252,.08);border-color:rgba(192,132,252,.2)"><i class="fas fa-layer-group"></i></div>
                    <div class="pcard-title">Smart Queue</div>
                    <div class="pcard-desc">Quatre files intelligentes (Standard, Dated, Old, Restock). Aucune commande ne reste bloquee.</div>
                    <span class="pcard-kpi" style="background:rgba(192,132,252,.08);border-color:rgba(192,132,252,.2);color:#c084fc">Zero commande perdue</span>
                </div>
            </div>
        </div>
    </div>
</section>

    <!-- ═══════════════════════════════════════════
       COMMANDE LIFECYCLE
    ════════════════════════════════════════════ -->
    <section class="cin-section" id="lifecycle" style="padding-top:3rem">
      <div class="max-w-7xl mx-auto">
        <div class="text-center mb-2">
          <p class="section-label reveal" style="justify-content:center;margin-left:0">Cycle de vie commande</p>
          <h2 class="section-h2 reveal" style="text-align:center">Une commande vivante,<br><span class="grad">du site a la livraison.</span></h2>
          <p class="section-sub reveal" style="text-align:center;margin:0 auto;max-width:700px">La meme commande reste visible pendant tout le workflow: creation/import, confirmation, emballage, expedition et livraison. L'etat evolue en direct selon l'action effectuee.</p>
        </div>

        <div class="lifecycle-grid" id="lifeGrid">
          <div class="life-track reveal-left">
            <div class="life-step active" data-life-step="0">
              <div class="life-step-title">1. Commande sur site</div>
              <div class="life-step-desc">La commande est creee sur Shopify / WooCommerce, ou importee depuis votre site web custom.</div>
              <div class="life-step-meta"><i class="fas fa-globe"></i> Source externe</div>
            </div>
            <div class="life-step" data-life-step="1">
              <div class="life-step-title">2. Synchronisation vers Confirmi</div>
              <div class="life-step-desc">Confirmi recupere automatiquement la commande, cree l'ID interne et lance le workflow intelligent.</div>
              <div class="life-step-meta"><i class="fas fa-arrows-rotate"></i> Sync automatique</div>
            </div>
            <div class="life-step" data-life-step="2">
              <div class="life-step-title">3. Confirmation client</div>
              <div class="life-step-desc">Le moteur SMS appelle le client. Chaque reponse met a jour la meme commande en temps reel.</div>
              <div class="life-step-meta"><i class="fas fa-comment-dots"></i> Webhook instantane</div>
            </div>
            <div class="life-step" data-life-step="3">
              <div class="life-step-title">4. Emballage</div>
              <div class="life-step-desc">Validation stock atomique puis passage a l'equipe emballage avec checklist qualite et etiquette transport.</div>
              <div class="life-step-meta"><i class="fas fa-box"></i> Zero survente</div>
            </div>
            <div class="life-step" data-life-step="4">
              <div class="life-step-title">5. Livraison et preuve</div>
              <div class="life-step-desc">Routage Kolixy/Masafa, suivi livreur, livraison finale, preuve capturee, dashboard mis a jour.</div>
              <div class="life-step-meta"><i class="fas fa-truck-fast"></i> Tracking end-to-end</div>
            </div>
          </div>

          <div class="life-sticky reveal-right">
            <div class="order-stage" id="orderStage">
              <div class="order-top">
                <div class="order-id">CMD-2026-009834</div>
                <div class="order-chip" id="orderChip">Nouvelle commande</div>
              </div>
              <div class="order-3d-zone">
                <div class="pack-cube">
                  <div class="pack-face f1"></div><div class="pack-face f2"></div>
                  <div class="pack-face f3"></div><div class="pack-face f4"></div>
                  <div class="pack-face f5"></div><div class="pack-face f6"></div>
                </div>
                <div class="scanner-line"></div>
              </div>
              <div class="order-state">
                <div class="state-label">Etat en cours</div>
                <div class="state-value" id="stateTitle">Creation / importation</div>
                <div class="state-sub" id="stateSub">Commande captee depuis votre boutique et injectee automatiquement dans Confirmi.</div>
                <div class="order-progress"><div class="order-progress-fill" id="orderProgress"></div></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

<!-- ═══════════════════════════════════════════
     METRICS BENTO
════════════════════════════════════════════ -->
<section class="cin-section" style="padding-top:2rem">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-2 reveal">
            <p class="section-label" style="justify-content:center;margin-left:0">Impact</p>
            <h2 class="section-h2" style="text-align:center">Des chiffres qui <span class="grad">parlent</span></h2>
        </div>
        <div class="bento-grid reveal-scale" id="bentoGrid">
            <div class="bento-cell bc-1" style="background:linear-gradient(135deg,rgba(13,13,24,.9),rgba(20,14,40,.9))">
                <div class="bento-num"><span id="bNum1">0</span><span class="unit">%</span></div>
                <div class="bento-label">Taux de confirmation SMS</div>
                <div class="mini-bars" id="miniBars">
                    <div class="mini-bar" style="height:40%"></div>
                    <div class="mini-bar lit" style="height:60%"></div>
                    <div class="mini-bar" style="height:45%"></div>
                    <div class="mini-bar lit" style="height:75%"></div>
                    <div class="mini-bar" style="height:55%"></div>
                    <div class="mini-bar lit" style="height:85%"></div>
                    <div class="mini-bar" style="height:70%"></div>
                    <div class="mini-bar lit" style="height:94%"></div>
                </div>
            </div>
            <div class="bento-cell bc-2">
                <div class="bento-num"><span id="bNum2">0</span><span class="unit">ms</span></div>
                <div class="bento-label">Temps de reaction webhook</div>
                <p style="font-size:.78rem;color:rgba(226,232,240,.35);margin-top:.8rem;line-height:1.6">Mise a jour du statut commande en temps reel a chaque reponse client.</p>
            </div>
            <div class="bento-cell bc-3" style="background:linear-gradient(135deg,rgba(13,13,24,.9),rgba(14,20,38,.9))">
                <div class="bento-num"><span id="bNum3">0</span><span class="unit">K+</span></div>
                <div class="bento-label">Colis traites / mois</div>
            </div>
            <div class="bento-cell bc-4">
                <div class="pcard-icon" style="margin-bottom:.8rem"><i class="fas fa-shield-alt"></i></div>
                <div style="font-size:.95rem;font-weight:700;color:#fff;margin-bottom:.4rem">Zero survente</div>
                <div style="font-size:.78rem;color:rgba(226,232,240,.4);line-height:1.6">Transactions SQL atomiques — stock protege meme sous forte charge.</div>
            </div>
            <div class="bento-cell bc-5" style="background:linear-gradient(135deg,rgba(13,13,24,.9),rgba(18,14,35,.95))">
                <div class="pcard-icon" style="color:#c084fc;background:rgba(192,132,252,.08);border-color:rgba(192,132,252,.2);margin-bottom:.8rem"><i class="fas fa-layer-group"></i></div>
                <div style="font-size:.95rem;font-weight:700;color:#fff;margin-bottom:.4rem">4 files intelligentes</div>
                <div style="font-size:.78rem;color:rgba(226,232,240,.4);line-height:1.6">Standard · Dated · Old · Restock</div>
            </div>
            <div class="bento-cell bc-6" style="padding:1.5rem 2rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1.5rem;background:linear-gradient(135deg,rgba(99,102,241,.12),rgba(67,56,202,.06))">
                <div>
                    <div style="font-size:.7rem;font-weight:800;letter-spacing:.14em;text-transform:uppercase;color:#6366f1;margin-bottom:.4rem">Transporteurs integres</div>
                    <div style="font-family:'Space Grotesk',sans-serif;font-size:1.6rem;font-weight:700;color:#fff">Kolixy &amp; MasafaExpress</div>
                </div>
                <div style="display:flex;gap:1rem;flex-wrap:wrap">
                    <span style="padding:.4rem 1.1rem;border-radius:10px;background:rgba(99,102,241,.1);border:1px solid rgba(99,102,241,.2);font-size:.75rem;font-weight:700;color:#818cf8">API native</span>
                    <span style="padding:.4rem 1.1rem;border-radius:10px;background:rgba(52,211,153,.08);border:1px solid rgba(52,211,153,.2);font-size:.75rem;font-weight:700;color:#34d399">Routing auto</span>
                    <span style="padding:.4rem 1.1rem;border-radius:10px;background:rgba(56,189,248,.08);border:1px solid rgba(56,189,248,.2);font-size:.75rem;font-weight:700;color:#38bdf8">Webhook livraison</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════
     ECOSYSTEM ORBIT
════════════════════════════════════════════ -->
<section class="cin-section">
    <div class="max-w-7xl mx-auto">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:5rem;align-items:center" id="ecoGrid">
            <div>
                <p class="section-label reveal">Ecosysteme</p>
                <h2 class="section-h2 reveal">Tout ce que vous<br>utilisez <span class="grad">deja.</span></h2>
                <p class="section-sub reveal">Connectez vos boutiques, transporteurs et outils marketing en quelques clics. Confirmi s'intègre dans votre stack sans friction.</p>
                <div class="feat-list reveal" style="margin-top:1.5rem">
                    <div class="feat-list-item"><i class="fas fa-check-circle"></i><span><strong>Kolixy & MasafaExpress</strong> — routage automatique</span></div>
                    <div class="feat-list-item"><i class="fas fa-check-circle"></i><span><strong>Shopify & WooCommerce</strong> — import commandes</span></div>
                    <div class="feat-list-item"><i class="fas fa-check-circle"></i><span><strong>SMS Tunisie</strong> — confirmation haute-cadence</span></div>
                    <div class="feat-list-item"><i class="fas fa-check-circle"></i><span><strong>Dashboard temps reel</strong> — suivi multi-livreurs</span></div>
                </div>
            </div>
            <div class="ecosystem-wrap reveal-scale" id="ecoWrap" style="display:flex;align-items:center;justify-content:center">
                <div class="eco-ring eco-ring-1" id="ecoRing1"></div>
                <div class="eco-ring eco-ring-2" id="ecoRing2"></div>
                <div class="eco-center">Confirmi<br>HUB</div>
                <!-- inner ring nodes -->
                <div class="eco-node" id="ecoN1" style="width:44px;height:44px;font-size:.55rem;left:calc(50% + 80px);top:calc(50% - 22px)">Kolixy</div>
                <div class="eco-node" id="ecoN2" style="width:44px;height:44px;font-size:.55rem;left:calc(50% - 124px);top:calc(50% - 22px)">Masafa</div>
                <div class="eco-node" id="ecoN3" style="width:40px;height:40px;font-size:.5rem;left:calc(50% - 20px);top:calc(50% - 115px)">SMS</div>
                <div class="eco-node" id="ecoN4" style="width:40px;height:40px;font-size:.5rem;left:calc(50% - 20px);top:calc(50% + 75px)">Stock</div>
                <!-- outer ring nodes -->
                <div class="eco-node" id="ecoN5" style="width:44px;height:44px;font-size:.52rem;left:calc(50% + 130px);top:calc(50% - 80px)">Shopify</div>
                <div class="eco-node" id="ecoN6" style="width:48px;height:48px;font-size:.52rem;left:calc(50% - 178px);top:calc(50% + 40px)">Woo<br>Comm.</div>
                <div class="eco-node" id="ecoN7" style="width:44px;height:44px;font-size:.5rem;left:calc(50% + 60px);top:calc(50% + 110px)">Webhook</div>
                <div class="eco-node" id="ecoN8" style="width:44px;height:44px;font-size:.5rem;left:calc(50% - 30px);top:calc(50% - 155px)">API</div>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════
     FEATURE: CODE PANELS
════════════════════════════════════════════ -->
<section class="cin-section" style="padding-top:2rem">
    <div class="max-w-7xl mx-auto">
        <div class="feat-row">
            <div>
                <p class="section-label reveal">Code & Technique</p>
                <div class="feat-icon-big reveal"><i class="fas fa-code-branch"></i></div>
                <h2 class="section-h2 reveal">Logique <span class="grad">atomique</span></h2>
                <p class="section-sub reveal">Chaque confirmation decremente le stock dans une transaction SQL isolee. Zero risque de survente, meme sous forte charge.</p>
                <div class="feat-list reveal">
                    <div class="feat-list-item"><i class="fas fa-bolt"></i><span><strong>BEGIN / COMMIT</strong> — isolation complete</span></div>
                    <div class="feat-list-item"><i class="fas fa-bolt"></i><span><strong>SELECT ... FOR UPDATE</strong> — verrouillage pessimiste</span></div>
                    <div class="feat-list-item"><i class="fas fa-bolt"></i><span><strong>Restock automatique</strong> — la file est reactiveee</span></div>
                </div>
            </div>
            <div class="code-panel reveal-right">
                <div class="code-panel-header">
                    <span class="code-dot" style="background:#ff5f57"></span>
                    <span class="code-dot" style="background:#ffbd2e"></span>
                    <span class="code-dot" style="background:#28c840"></span>
                    <span style="margin-left:.6rem;font-size:.65rem;color:rgba(226,232,240,.3)">stock.transaction.sql</span>
                </div>
                <div class="code-body">
<span class="cc">-- Decrementation atomique</span>
<span class="ck">BEGIN</span> TRANSACTION;

<span class="ck">SELECT</span> qty <span class="ck">FROM</span> products
  <span class="ck">WHERE</span> id = <span class="cv">$product_id</span>
  <span class="ck">FOR UPDATE</span>;

<span class="ck">IF</span> qty &gt; <span class="cv">0</span> <span class="ck">THEN</span>
  <span class="ck">UPDATE</span> products
    <span class="ck">SET</span> qty = qty - <span class="cv">1</span>;
  <span class="ck">UPDATE</span> orders
    <span class="ck">SET</span> status = <span class="cs">'confirmed'</span>;
<span class="ck">ELSE</span>
  <span class="ck">UPDATE</span> orders
    <span class="ck">SET</span> status = <span class="cs">'suspended'</span>;
<span class="ck">END IF</span>;

<span class="ck">COMMIT</span>;
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════
     PRICING
════════════════════════════════════════════ -->
<section class="cin-section" id="pricing">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-2">
            <p class="section-label reveal" style="justify-content:center;margin-left:0">Tarifs</p>
            <h2 class="section-h2 reveal" style="text-align:center">Simple. <span class="grad">Transparent.</span></h2>
            <p class="section-sub reveal" style="text-align:center;margin:0 auto">Choisissez le plan qui correspond a votre volume.</p>
        </div>
        <div class="pricing-grid">
            <div class="price-card reveal-left">
            <div class="price-badge">Essai</div>
                <div class="price-name">Free</div>
                <div class="price-amount"><span class="cur">TND</span> 0 <span class="per">/ mois</span></div>
            <div class="price-desc">14 jours d'essai pour tester la plateforme dans des conditions reelles.</div>
            <div class="price-feature"><i class="fas fa-check-circle"></i><span>14 jours d'essai complet</span></div>
            <div class="price-feature"><i class="fas fa-check-circle"></i><span>1 compte manager</span></div>
            <div class="price-feature"><i class="fas fa-check-circle"></i><span>2 comptes employee</span></div>
            <div class="price-feature"><i class="fas fa-check-circle"></i><span>Cycle commande complet</span></div>
                <a href="{{ route('confirmi.register') }}" class="price-btn price-btn-ghost">Commencer</a>
            </div>

          <div class="price-card reveal">
            <div class="price-badge" style="background:rgba(251,191,36,.15);color:#fde68a">Reduction</div>
            <div class="price-name">Starter</div>
            <div class="price-amount"><span class="cur">TND</span> 19 <span class="per">/ mois</span></div>
            <div class="price-desc">Le plan Free + support client disponible 24h/24. Prix reduit de 25 DT a 19 DT.</div>
            <div class="price-feature"><i class="fas fa-check-circle"></i><span>1 compte manager</span></div>
            <div class="price-feature"><i class="fas fa-check-circle"></i><span>2 comptes employee</span></div>
            <div class="price-feature"><i class="fas fa-check-circle"></i><span>Support client h24</span></div>
            <div class="price-feature"><i class="fas fa-check-circle"></i><span>179 DT / an</span></div>
            <a href="{{ route('confirmi.register') }}" class="price-btn price-btn-ghost">Passer Starter</a>
          </div>

          <div class="price-card featured reveal" style="transform:scale(1.02)">
            <div class="price-badge" style="background:rgba(99,102,241,.25);color:#c7d2fe">Le plus choisi</div>
                <div class="price-name">Pro</div>
            <div class="price-amount"><span class="cur">TND</span> 39 <span class="per">/ mois</span></div>
            <div class="price-desc">Pour les operations a gros volume avec equipes elargies.</div>
            <div class="price-feature"><i class="fas fa-check-circle"></i><span>10 comptes manager</span></div>
            <div class="price-feature"><i class="fas fa-check-circle"></i><span>100 comptes employee</span></div>
            <div class="price-feature"><i class="fas fa-check-circle"></i><span>Cycle de vie commande live</span></div>
            <div class="price-feature"><i class="fas fa-check-circle"></i><span>349 DT / an</span></div>
            <div class="price-feature"><i class="fas fa-check-circle"></i><span>Support prioritaire</span></div>
                <a href="{{ route('confirmi.register') }}" class="price-btn price-btn-full">Demarrer Pro</a>
            </div>

            <div class="price-card reveal-right">
                <div class="price-badge">Sur-mesure</div>
            <div class="price-name">Business</div>
            <div class="price-amount" style="font-size:2rem"><span class="cur" style="font-size:1.1rem">On discute</span></div>
            <div class="price-desc">Pour besoins specifiques et operations accompagnees.</div>
            <div class="price-feature"><i class="fas fa-check-circle"></i><span>Tarif adapte a votre volume</span></div>
            <div class="price-feature"><i class="fas fa-check-circle"></i><span>Plan equipe Confirmi: confirmation + emballage</span></div>
            <div class="price-feature"><i class="fas fa-check-circle"></i><span>Integrations et SLA personnalises</span></div>
            <div class="price-feature"><i class="fas fa-check-circle"></i><span>Accompagnement operationnel dedie</span></div>
                <a href="https://wa.me/21693357722" target="_blank" rel="noopener noreferrer" class="price-btn price-btn-ghost">Nous contacter</a>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════
     CTA BAND
════════════════════════════════════════════ -->
<div class="cta-band">
    <div style="position:absolute;inset:0;border-top:1px solid rgba(99,102,241,.1);border-bottom:1px solid rgba(99,102,241,.1);pointer-events:none"></div>
    <div class="cta-band-title reveal">Pret a transformer<br>votre pipeline ?</div>
    <p class="cta-band-sub reveal">Demarrez en quelques minutes. Aucune carte bancaire requise.</p>
    <div style="display:flex;align-items:center;justify-content:center;gap:1rem;flex-wrap:wrap" class="reveal">
        <a href="{{ route('confirmi.register') }}" class="hero-cta-primary">Demarrer gratuitement <i class="fas fa-arrow-right text-xs"></i></a>
        <a href="https://wa.me/21693357722" target="_blank" rel="noopener noreferrer" class="hero-cta-secondary"><i class="fab fa-whatsapp" style="color:#25d366"></i> Demo via WhatsApp</a>
    </div>
</div>

<!-- ═══════════════════════════════════════════
     FOOTER
════════════════════════════════════════════ -->
<footer class="cin-footer">
    <div class="footer-grid">
        <div>
            <div class="nav-logo" style="margin-bottom:1rem">
                <div class="nav-logo-icon"><i class="fas fa-bolt"></i></div>
                <span class="nav-logo-text">Confirmi<span>.space</span></span>
            </div>
            <p style="font-size:.82rem;color:rgba(226,232,240,.35);line-height:1.7;max-width:280px">Pipeline intelligence pour les equipes e-commerce tunisiennes. De la commande a la livraison.</p>
            <a href="https://wa.me/21693357722" target="_blank" rel="noopener noreferrer" style="display:inline-flex;align-items:center;gap:.5rem;margin-top:1rem;font-size:.8rem;font-weight:700;color:#34d399;text-decoration:none">
                <i class="fab fa-whatsapp"></i> +216 93 357 722
            </a>
        </div>
        <div class="footer-col">
            <div class="footer-col-label">Navigation</div>
            <a href="{{ route('confirmi.home') }}">Accueil</a>
            <a href="{{ route('confirmi.services') }}">Services</a>
            <a href="{{ route('confirmi.contact') }}">Contact</a>
            <a href="#pricing">Tarifs</a>
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

<!-- ═══════════════════════════════════════════
     AUTH MODAL
════════════════════════════════════════════ -->
<div x-show="loginOpen" x-cloak @click="loginOpen = false" class="fade-in-overlay fixed inset-0 z-[200] bg-black/70 backdrop-blur-xl"></div>
<div x-show="loginOpen" x-cloak class="fixed inset-0 z-[201] flex items-center justify-center p-4" @keydown.escape.window="loginOpen = false">
    <div @click.stop class="modal-spring w-full max-w-md rounded-3xl p-8" style="background:#0d0d18;border:1px solid rgba(99,102,241,.25);box-shadow:0 0 100px rgba(99,102,241,.2)">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:2rem">
            <div>
                <div class="nav-logo-icon" style="margin-bottom:.75rem"><i class="fas fa-bolt"></i></div>
                <div style="font-family:'Space Grotesk',sans-serif;font-size:1.5rem;font-weight:700;color:#fff">Connexion</div>
                <div style="font-size:.82rem;color:rgba(226,232,240,.4);margin-top:.25rem">Acces a votre espace Confirmi</div>
            </div>
            <button @click="loginOpen = false" style="width:36px;height:36px;border-radius:10px;border:1px solid rgba(99,102,241,.2);background:transparent;color:rgba(226,232,240,.5);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:.8rem" class="transition hover:border-indigo-500 hover:text-white">
                <i class="fas fa-times"></i>
            </button>
        </div>
        @if(session('error'))<div style="margin-bottom:1rem;padding:.75rem 1rem;border-radius:12px;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);font-size:.82rem;color:#fca5a5">{{ session('error') }}</div>@endif
        @if($errors->any())<div style="margin-bottom:1rem;padding:.75rem 1rem;border-radius:12px;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);font-size:.82rem;color:#fca5a5">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
        <form action="{{ route('confirmi.login.submit') }}" method="POST" style="display:flex;flex-direction:column;gap:1rem">
            @csrf
            <div>
                <label style="display:block;font-size:.78rem;font-weight:700;color:rgba(226,232,240,.6);margin-bottom:.5rem">Adresse email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="modal-input" placeholder="votre@email.com">
            </div>
            <div>
                <label style="display:block;font-size:.78rem;font-weight:700;color:rgba(226,232,240,.6);margin-bottom:.5rem">Mot de passe</label>
                <input type="password" name="password" required autocomplete="current-password" class="modal-input" placeholder="••••••••">
            </div>
            <button type="submit" class="btn-primary" style="width:100%;padding:.85rem;border:none;cursor:pointer;font-size:.9rem;margin-top:.5rem;border-radius:12px">Se connecter</button>
        </form>
        <p style="text-align:center;font-size:.82rem;color:rgba(226,232,240,.35);margin-top:1.5rem">Pas encore de compte ? <a href="{{ route('confirmi.register') }}" @click="loginOpen = false" style="color:#818cf8;font-weight:700;text-decoration:none">S'inscrire</a></p>
    </div>
</div>

<!-- ═══════════════════════════════════════════
     SCRIPTS
════════════════════════════════════════════ -->
<script>
// ── THREE.JS PARTICLE FIELD ──────────────────────────────────────────
function initCinematicThree() {
  if (!window.THREE) return;
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (reduceMotion) return;
  try {
    const canvas = document.getElementById('bgCanvas');
    const renderer = new THREE.WebGLRenderer({ canvas, antialias: false, alpha: true, powerPreference: 'high-performance' });
    const isMobile = window.innerWidth < 900;
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, isMobile ? 1 : 1.25));
    renderer.setSize(window.innerWidth, window.innerHeight);

    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(60, window.innerWidth / window.innerHeight, 0.1, 1000);
    camera.position.z = 5;

    const count = isMobile ? 1100 : 1900;
    const positions = new Float32Array(count * 3);
    const colors = new Float32Array(count * 3);
    for (let i = 0; i < count; i++) {
      positions[i * 3] = (Math.random() - 0.5) * 20;
      positions[i * 3 + 1] = (Math.random() - 0.5) * 20;
      positions[i * 3 + 2] = (Math.random() - 0.5) * 20;
      const t = Math.random();
      colors[i * 3] = 0.39 + t * 0.2;
      colors[i * 3 + 1] = 0.40 + t * 0.1;
      colors[i * 3 + 2] = 0.95 + t * 0.05;
    }
    const geo = new THREE.BufferGeometry();
    geo.setAttribute('position', new THREE.BufferAttribute(positions, 3));
    geo.setAttribute('color', new THREE.BufferAttribute(colors, 3));
    const mat = new THREE.PointsMaterial({ size: isMobile ? 0.034 : 0.04, vertexColors: true, transparent: true, opacity: 0.68 });
    const particles = new THREE.Points(geo, mat);
    scene.add(particles);

    const addRing = (r, tube, x, y, z, col) => {
      const g = new THREE.TorusGeometry(r, tube, 12, 52);
      const m = new THREE.MeshBasicMaterial({ color: col, transparent: true, opacity: .12, wireframe: true });
      const mesh = new THREE.Mesh(g, m);
      mesh.position.set(x, y, z);
      scene.add(mesh);
      return mesh;
    };
    const ring1 = addRing(2.2, 0.008, 0, 0, 0, 0x6366f1);
    const ring2 = addRing(3.5, 0.006, 0.5, 0.3, -1, 0x818cf8);
    const ring3 = addRing(1.4, 0.01, -1, -0.5, 0.5, 0x4338ca);

    let mouseX = 0;
    let mouseY = 0;
    let paused = false;

    window.addEventListener('mousemove', (e) => {
      mouseX = (e.clientX / window.innerWidth - 0.5) * 2;
      mouseY = -(e.clientY / window.innerHeight - 0.5) * 2;
    }, { passive: true });

    document.addEventListener('visibilitychange', () => {
      paused = document.hidden;
    });

    window.addEventListener('resize', () => {
      camera.aspect = window.innerWidth / window.innerHeight;
      camera.updateProjectionMatrix();
      renderer.setPixelRatio(Math.min(window.devicePixelRatio, window.innerWidth < 900 ? 1 : 1.25));
      renderer.setSize(window.innerWidth, window.innerHeight);
    });

    let frame = 0;
    function animate() {
      requestAnimationFrame(animate);
      if (paused) return;
      frame += 0.003;
      particles.rotation.y = frame * 0.3;
      particles.rotation.x = frame * 0.1;
      ring1.rotation.x = frame * 0.5;
      ring1.rotation.y = frame * 0.3;
      ring2.rotation.x = -frame * 0.3;
      ring2.rotation.z = frame * 0.4;
      ring3.rotation.y = frame * 0.6;
      camera.position.x += (mouseX * 0.5 - camera.position.x) * 0.03;
      camera.position.y += (mouseY * 0.3 - camera.position.y) * 0.03;
      renderer.render(scene, camera);
    }
    animate();
  } catch (e) {
    console.warn('Three.js unavailable', e);
  }
}

// ── GSAP ──────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
  const idle = window.requestIdleCallback || function(cb){ return setTimeout(cb, 60); };
  idle(initCinematicThree);

    if (!window.gsap) return;
    gsap.registerPlugin(ScrollTrigger);

    // Scrolled nav
    ScrollTrigger.create({
        start: 'top -60',
        onToggle: s => document.getElementById('mainNav').classList.toggle('scrolled', s.isActive)
    });

    // HERO entrance
    const tl = gsap.timeline({ delay: .2 });
    tl.fromTo('#heroBadge', { y: 20, opacity: 0 }, { y: 0, opacity: 1, duration: .7, ease: 'power3.out' })
      .fromTo('#heroH1',    { y: 50, opacity: 0 }, { y: 0, opacity: 1, duration: .9, ease: 'power3.out' }, '-=.4')
      .fromTo('#heroSub',   { y: 30, opacity: 0 }, { y: 0, opacity: 1, duration: .7, ease: 'power3.out' }, '-=.5')
      .fromTo('#heroCtas',  { y: 25, opacity: 0 }, { y: 0, opacity: 1, duration: .6, ease: 'power3.out' }, '-=.4')
      .fromTo('#heroStats', { y: 20, opacity: 0 }, { y: 0, opacity: 1, duration: .6, ease: 'power3.out' }, '-=.3');

    // Hero orb parallax
    gsap.to('#orb1', { yPercent: -30, ease: 'none', scrollTrigger: { trigger: '#hero', start: 'top top', end: 'bottom top', scrub: true } });
    gsap.to('#orb2', { yPercent: 20,  ease: 'none', scrollTrigger: { trigger: '#hero', start: 'top top', end: 'bottom top', scrub: true } });

    // Counters
    function animCounter(el, target, suffix) {
        gsap.fromTo({ val: 0 }, { val: target }, {
            duration: 2, ease: 'power2.out', delay: .4,
            onUpdate: function() { el.textContent = Math.round(this.targets()[0].val) }
        });
    }
    animCounter(document.getElementById('statConf'), 94, '%');
    animCounter(document.getElementById('statCmd'),  500, '+');
    animCounter(document.getElementById('statMin'),  5, 'min');
    animCounter(document.getElementById('bNum1'), 94, '%');
    animCounter(document.getElementById('bNum2'), 120, 'ms');
    animCounter(document.getElementById('bNum3'), 25, 'K+');

    // Generic reveal on scroll
    document.querySelectorAll('.reveal').forEach(el => {
        gsap.fromTo(el, { y: 50, opacity: 0 }, { y: 0, opacity: 1, duration: .9, ease: 'power3.out',
            scrollTrigger: { trigger: el, start: 'top 85%' } });
    });
    document.querySelectorAll('.reveal-left').forEach(el => {
        gsap.fromTo(el, { x: -70, opacity: 0 }, { x: 0, opacity: 1, duration: 1, ease: 'power3.out',
            scrollTrigger: { trigger: el, start: 'top 85%' } });
    });
    document.querySelectorAll('.reveal-right').forEach(el => {
        gsap.fromTo(el, { x: 70, opacity: 0 }, { x: 0, opacity: 1, duration: 1, ease: 'power3.out',
            scrollTrigger: { trigger: el, start: 'top 85%' } });
    });
    document.querySelectorAll('.reveal-scale').forEach(el => {
        gsap.fromTo(el, { scale: .8, opacity: 0 }, { scale: 1, opacity: 1, duration: 1, ease: 'back.out(1.5)',
            scrollTrigger: { trigger: el, start: 'top 80%' } });
    });

    // Pipeline nodes stagger
    gsap.fromTo('.pipeline-node', { x: 0, opacity: 0, y: 60 }, {
        y: 0, opacity: 1, duration: .9, stagger: .25, ease: 'power3.out',
        scrollTrigger: { trigger: '.pipeline-wrap', start: 'top 80%' }
    });

    // Bento cells
    gsap.fromTo('.bento-cell', { y: 40, opacity: 0 }, {
        y: 0, opacity: 1, duration: .75, stagger: .1, ease: 'power3.out',
        scrollTrigger: { trigger: '#bentoGrid', start: 'top 80%' }
    });

    // Eco orbit parallax
    gsap.to('#ecoRing1', { rotation: 360, duration: 18, repeat: -1, ease: 'none' });
    gsap.to('#ecoRing2', { rotation: -360, duration: 28, repeat: -1, ease: 'none' });

    // Mini bars animate in
    ScrollTrigger.create({
        trigger: '#miniBars',
        start: 'top 85%',
        onEnter: () => {
            document.querySelectorAll('.mini-bar').forEach((b, i) => {
                const h = b.style.height;
                b.style.height = '0%';
                setTimeout(() => { b.style.height = h; }, i * 80);
            });
        }
    });

    // Lifecycle synchronized state
    const lifecycleStates = [
      { chip: 'Nouvelle commande', title: 'Creation / importation', sub: 'Commande captee depuis votre boutique et injectee automatiquement dans Confirmi.', progress: 18 },
      { chip: 'Sync Confirmi', title: 'Synchronisation active', sub: 'L\'ID interne est cree, les verifications produit/client sont lancees.', progress: 36 },
      { chip: 'Confirmation', title: 'Validation client', sub: 'Le moteur SMS confirme la commande et met a jour le statut en direct.', progress: 58 },
      { chip: 'Emballage', title: 'Preparation colis', sub: 'Checklist emballage executee avec stock atomique et etiquette transport.', progress: 78 },
      { chip: 'Livraison', title: 'Livree avec preuve', sub: 'Le transporteur confirme la livraison, preuve capturee, dashboard finalise.', progress: 100 }
    ];

    const lifeSteps = document.querySelectorAll('.life-step');
    const orderChip = document.getElementById('orderChip');
    const stateTitle = document.getElementById('stateTitle');
    const stateSub = document.getElementById('stateSub');
    const orderProgress = document.getElementById('orderProgress');

    function setLifeState(index) {
      const s = lifecycleStates[index] || lifecycleStates[0];
      lifeSteps.forEach((el, i) => el.classList.toggle('active', i <= index));
      if (orderChip) orderChip.textContent = s.chip;
      if (stateTitle) stateTitle.textContent = s.title;
      if (stateSub) stateSub.textContent = s.sub;
      if (orderProgress) orderProgress.style.width = s.progress + '%';
    }

    lifeSteps.forEach((step, index) => {
      ScrollTrigger.create({
        trigger: step,
        start: 'top 58%',
        end: 'bottom 52%',
        onEnter: () => setLifeState(index),
        onEnterBack: () => setLifeState(index)
      });
    });
});
</script>
</body>
</html>