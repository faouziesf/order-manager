{{-- ============================================================
     Cinematic Hero Section v2 — Confirmi.space
     Usage: <x-cinematic.hero />
     GSAP script self-contained at bottom of this component.
     ============================================================ --}}

<section
    id="hero"
    class="relative flex flex-col items-center justify-center overflow-hidden"
    style="height: 100vh; background-color: #030712; perspective: 1400px;"
>

    {{-- Subtle dot grid --}}
    <div class="absolute inset-0 pointer-events-none" style="
        background-image: radial-gradient(rgba(255,255,255,0.06) 1px, transparent 1px);
        background-size: 28px 28px;
        mask-image: radial-gradient(ellipse 80% 70% at 50% 50%, black, transparent);
    "></div>

    {{-- Radial indigo aurora —  animated --}}
    <div class="absolute inset-0 pointer-events-none" style="
        background:
            radial-gradient(ellipse 75% 50% at 50% 0%,   rgba(99,102,241,0.18), transparent 65%),
            radial-gradient(ellipse 40% 30% at 80% 20%,  rgba(139,92,246,0.10), transparent 60%),
            radial-gradient(ellipse 40% 30% at 20% 70%,  rgba(14,165,233,0.06), transparent 60%);
        animation: heroGlow 8s ease-in-out infinite alternate;
    "></div>

    {{-- ── Hero content ── --}}
    <div class="relative z-10 flex flex-col items-center text-center px-6 w-full max-w-5xl mx-auto">

        {{-- Eyebrow --}}
        <div id="hero-eyebrow" class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full mb-8" style="
            background: rgba(99,102,241,0.1);
            border: 1px solid rgba(99,102,241,0.22);
            backdrop-filter: blur(8px);
        ">
            <span class="w-1.5 h-1.5 rounded-full" style="background:#818cf8; box-shadow: 0 0 6px rgba(129,140,248,0.8); animation: pulse 2s infinite;"></span>
            <span style="font-size: 11px; font-weight: 600; letter-spacing: 0.1em; color: #a5b4fc; text-transform: uppercase;">Gestion des commandes · Nouvelle génération</span>
        </div>

        {{-- Main title --}}
        <h1 id="hero-title" style="
            font-size: clamp(3.2rem, 8.5vw, 6rem);
            font-weight: 900;
            letter-spacing: -0.03em;
            line-height: 1;
            margin-bottom: 1.5rem;
            color: #fff;
        ">
            La plateforme qui<br>
            <span style="
                background: linear-gradient(135deg, #818cf8 0%, #a78bfa 40%, #c084fc 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            ">accélère vos ventes</span>
        </h1>

        {{-- Subtitle --}}
        <p id="hero-subtitle" style="
            font-size: 1.125rem;
            font-weight: 300;
            line-height: 1.75;
            color: rgba(255,255,255,0.42);
            max-width: 34rem;
            margin: 0 auto 2.5rem;
        ">
            Importez, confirmez, suivez et livrez — depuis une seule interface.<br>
            Conçu pour les équipes e-commerce ambitieuses.
        </p>

        {{-- CTA --}}
        <div id="hero-cta" class="flex flex-wrap items-center justify-center gap-3">
            <a
                href="{{ route('confirmi.login') }}"
                style="
                    display: inline-flex; align-items: center; gap: 8px;
                    padding: .75rem 1.75rem; border-radius: .75rem;
                    font-size: .875rem; font-weight: 600; color: #fff;
                    background: linear-gradient(135deg, #4f46e5, #7c3aed);
                    box-shadow: 0 0 0 1px rgba(99,102,241,0.5), 0 8px 32px rgba(99,102,241,0.25);
                    text-decoration: none; transition: all .2s;
                "
                onmouseover="this.style.boxShadow='0 0 0 1px rgba(99,102,241,0.7), 0 12px 40px rgba(99,102,241,0.4)'; this.style.transform='translateY(-1px)';"
                onmouseout="this.style.boxShadow='0 0 0 1px rgba(99,102,241,0.5), 0 8px 32px rgba(99,102,241,0.25)'; this.style.transform='translateY(0)';"
            >
                Commencer gratuitement
                <i class="fas fa-arrow-right" style="font-size: 11px;"></i>
            </a>
            <a
                href="#features"
                style="
                    display: inline-flex; align-items: center; gap: 8px;
                    padding: .75rem 1.5rem; border-radius: .75rem;
                    font-size: .875rem; font-weight: 500;
                    border: 1px solid rgba(255,255,255,0.1);
                    background: rgba(255,255,255,0.04);
                    color: rgba(255,255,255,0.55);
                    text-decoration: none; transition: all .2s;
                "
                onmouseover="this.style.background='rgba(255,255,255,0.08)'; this.style.color='rgba(255,255,255,0.85)'; this.style.borderColor='rgba(255,255,255,0.18)';"
                onmouseout="this.style.background='rgba(255,255,255,0.04)'; this.style.color='rgba(255,255,255,0.55)'; this.style.borderColor='rgba(255,255,255,0.1)';"
            >
                Voir les fonctionnalités
                <i class="fas fa-chevron-down" style="font-size: 10px;"></i>
            </a>
        </div>

        {{-- Social proof --}}
        <div style="display: flex; align-items: center; gap: 12px; margin-top: 2rem;">
            <div style="display: flex;">
                @foreach(['#6366f1','#8b5cf6','#06b6d4','#10b981'] as $c)
                <div style="
                    width: 28px; height: 28px; border-radius: 50%; margin-left: -8px;
                    background: {{ $c }}; border: 2px solid #030712;
                    display: flex; align-items: center; justify-content: center;
                    font-size: 9px; font-weight: 700; color: #fff;
                ">{{ substr('AYMO', $loop->index, 1) }}</div>
                @endforeach
            </div>
            <span style="font-size: 12px; color: rgba(255,255,255,0.32); font-weight: 400;">
                +120 équipes utilisent Confirmi aujourd'hui
            </span>
        </div>
    </div>

    {{-- ── Dashboard Preview 3D ── --}}
    <div
        id="dashboard-preview"
        style="
            position: relative;
            width: 100%; max-width: 900px;
            padding: 0 1.5rem;
            margin: 3rem auto 0;
            transform: rotateX(22deg) scale(0.92);
            transform-origin: top center;
            transform-style: preserve-3d;
            will-change: transform;
        "
    >
        {{-- Glow under dashboard --}}
        <div style="
            position: absolute; bottom: -40px; left: 10%; right: 10%;
            height: 80px; pointer-events: none;
            background: radial-gradient(ellipse, rgba(99,102,241,0.3), transparent 70%);
            filter: blur(20px);
        "></div>

        {{-- Browser window --}}
        <div style="
            border-radius: 14px; overflow: hidden;
            border: 1px solid rgba(255,255,255,0.09);
            background: #0d1117;
            box-shadow:
                0 80px 160px -20px rgba(0,0,0,0.9),
                0 0 0 1px rgba(255,255,255,0.04),
                inset 0 1px 0 rgba(255,255,255,0.07);
        ">

            {{-- Browser chrome --}}
            <div style="
                display: flex; align-items: center; gap: 12px;
                padding: 10px 18px;
                background: rgba(255,255,255,0.025);
                border-bottom: 1px solid rgba(255,255,255,0.055);
            ">
                <div style="display: flex; gap: 6px; flex-shrink: 0;">
                    <div style="width: 12px; height: 12px; border-radius: 50%; background: rgba(239,68,68,0.5);"></div>
                    <div style="width: 12px; height: 12px; border-radius: 50%; background: rgba(234,179,8,0.5);"></div>
                    <div style="width: 12px; height: 12px; border-radius: 50%; background: rgba(34,197,94,0.5);"></div>
                </div>
                <div style="flex: 1; display: flex; justify-content: center;">
                    <div style="
                        display: flex; align-items: center; gap: 6px;
                        padding: 5px 14px; border-radius: 6px; width: 200px;
                        background: rgba(255,255,255,0.04);
                        font-size: 10px; color: rgba(255,255,255,0.18);
                    ">
                        <i class="fas fa-lock" style="font-size: 7px;"></i>
                        app.confirmi.space/dashboard
                    </div>
                </div>
                <div style="display: flex; gap: 8px; flex-shrink: 0;">
                    <div style="width: 50px; height: 14px; border-radius: 4px; background: rgba(255,255,255,0.04);"></div>
                    <div style="width: 30px; height: 14px; border-radius: 4px; background: rgba(255,255,255,0.04);"></div>
                </div>
            </div>

            {{-- App UI --}}
            <div style="display: flex; height: 260px;">

                {{-- Sidebar --}}
                <div style="
                    width: 52px; flex-shrink: 0;
                    background: rgba(0,0,0,0.25);
                    border-right: 1px solid rgba(255,255,255,0.05);
                    display: flex; flex-direction: column; align-items: center;
                    padding: 14px 0; gap: 10px;
                ">
                    <div style="
                        width: 32px; height: 32px; border-radius: 10px; flex-shrink: 0;
                        background: rgba(99,102,241,0.25);
                        border: 1px solid rgba(99,102,241,0.4);
                        display: flex; align-items: center; justify-content: center;
                    ">
                        <i class="fas fa-cubes" style="font-size: 9px; color: #818cf8;"></i>
                    </div>
                    @foreach(['rgba(255,255,255,0.1)','rgba(255,255,255,0.05)','rgba(255,255,255,0.05)','rgba(255,255,255,0.05)','rgba(255,255,255,0.05)'] as $bg)
                    <div style="width: 26px; height: 20px; border-radius: 6px; background: {{ $bg }}; flex-shrink: 0;"></div>
                    @endforeach
                </div>

                {{-- Main content area --}}
                <div style="flex: 1; padding: 14px; display: flex; flex-direction: column; gap: 10px; overflow: hidden; min-width: 0;">

                    {{-- Top bar with greeting + search --}}
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-shrink: 0;">
                        <div>
                            <div style="height: 7px; width: 120px; border-radius: 4px; background: rgba(255,255,255,0.12);"></div>
                            <div style="height: 5px; width: 70px; border-radius: 3px; background: rgba(255,255,255,0.05); margin-top: 4px;"></div>
                        </div>
                        <div style="display: flex; gap: 6px;">
                            <div style="height: 20px; width: 80px; border-radius: 6px; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06);"></div>
                            <div style="height: 20px; width: 20px; border-radius: 6px; background: rgba(99,102,241,0.3); border: 1px solid rgba(99,102,241,0.4);"></div>
                        </div>
                    </div>

                    {{-- KPI cards --}}
                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 7px; flex-shrink: 0;">
                        @foreach([
                            ['1 248',     'Commandes',  'rgba(99,102,241,0.18)',  '#818cf8'],
                            ['84 320 DH', 'CA Mensuel', 'rgba(139,92,246,0.18)', '#a78bfa'],
                            ['3 402',     'Clients',    'rgba(14,165,233,0.18)', '#38bdf8'],
                            ['94.2%',     'Livraison',  'rgba(34,197,94,0.18)',  '#4ade80'],
                        ] as [$val, $label, $bg, $color])
                        <div style="border-radius: 10px; padding: 9px 10px; background: {{ $bg }}; border: 1px solid {{ $bg }};">
                            <div style="font-size: 6px; text-transform: uppercase; letter-spacing: 0.08em; color: rgba(255,255,255,0.35); margin-bottom: 3px;">{{ $label }}</div>
                            <div style="font-size: 11px; font-weight: 700; color: {{ $color }};">{{ $val }}</div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Chart area --}}
                    <div style="
                        flex: 1; border-radius: 10px; overflow: hidden;
                        background: rgba(255,255,255,0.02);
                        border: 1px solid rgba(255,255,255,0.05);
                        display: flex; align-items: flex-end;
                        padding: 8px 10px 8px;
                        gap: 4px;
                        min-height: 0;
                    ">
                        @foreach([28,45,35,62,40,78,55,70,42,84,60,76,48,90,65] as $h)
                        <div style="
                            flex: 1; border-radius: 3px; min-width: 0;
                            height: {{ $h }}%;
                            background: linear-gradient(to top, rgba(99,102,241,0.6), rgba(139,92,246,0.15));
                        "></div>
                        @endforeach
                    </div>

                    {{-- Table rows --}}
                    <div style="display: flex; flex-direction: column; gap: 5px; flex-shrink: 0;">
                        @foreach([
                            ['rgba(34,197,94,0.7)',  '#ORD-1248', 'Livrée',   72],
                            ['rgba(234,179,8,0.7)',  '#ORD-1247', 'En cours', 55],
                            ['rgba(99,102,241,0.7)', '#ORD-1246', 'Assignée', 40],
                        ] as [$dot, $ref, $status, $w])
                        <div style="
                            height: 18px; border-radius: 6px;
                            display: flex; align-items: center; padding: 0 8px; gap: 7px;
                            background: rgba(255,255,255,0.025);
                            border: 1px solid rgba(255,255,255,0.04);
                        ">
                            <div style="width: 6px; height: 6px; border-radius: 50%; background: {{ $dot }}; flex-shrink: 0;"></div>
                            <div style="height: 5px; width: {{ $w }}%; border-radius: 3px; background: rgba(255,255,255,0.1); flex-shrink: 0;"></div>
                            <div style="margin-left: auto; height: 5px; width: 28px; border-radius: 3px; background: rgba(255,255,255,0.05); flex-shrink: 0;"></div>
                        </div>
                        @endforeach
                    </div>

                </div>

                {{-- Right panel --}}
                <div style="
                    width: 110px; flex-shrink: 0;
                    background: rgba(0,0,0,0.18);
                    border-left: 1px solid rgba(255,255,255,0.05);
                    padding: 12px 10px;
                ">
                    <div style="font-size: 6px; text-transform: uppercase; letter-spacing: 0.1em; color: rgba(255,255,255,0.2); margin-bottom: 8px;">Activité</div>
                    @foreach([80, 60, 90, 45, 70] as $w)
                    <div style="
                        border-radius: 6px; padding: 6px 7px; margin-bottom: 5px;
                        background: rgba(255,255,255,0.03);
                        border: 1px solid rgba(255,255,255,0.05);
                    ">
                        <div style="height: 5px; border-radius: 3px; background: rgba(255,255,255,0.1); width: 100%; margin-bottom: 3px;"></div>
                        <div style="height: 5px; border-radius: 3px; background: rgba(255,255,255,0.05); width: {{ $w }}%;"></div>
                    </div>
                    @endforeach
                </div>

            </div>{{-- /App UI --}}

        </div>{{-- /browser window --}}

        {{-- Bottom fade --}}
        <div style="
            position: absolute; bottom: 0; left: 0; right: 0; height: 80px;
            pointer-events: none;
            background: linear-gradient(to bottom, transparent, #030712);
        "></div>

    </div>{{-- /#dashboard-preview --}}

    {{-- Scroll cue --}}
    <div style="
        position: absolute; bottom: 28px; left: 50%; transform: translateX(-50%);
        display: flex; flex-direction: column; align-items: center; gap: 6px;
        pointer-events: none;
        animation: scrollCue 2s ease-in-out infinite;
    ">
        <span style="font-size: 9px; color: rgba(255,255,255,0.16); letter-spacing: 0.2em; font-weight: 600; text-transform: uppercase;">Scroll</span>
        <div style="width: 1px; height: 28px; background: linear-gradient(to bottom, rgba(255,255,255,0.22), transparent);"></div>
    </div>

</section>

<style>
@keyframes heroGlow {
    0%   { opacity: 0.7; }
    100% { opacity: 1; }
}
@keyframes scrollCue {
    0%, 100% { opacity: 0.4; transform: translateX(-50%) translateY(0); }
    50%       { opacity: 0.9; transform: translateX(-50%) translateY(5px); }
}
</style>

{{-- ── GSAP Hero Animation (self-contained) ─────────────────────────────── --}}
<script>
(function () {
    function initCinematicHero() {
        if (typeof gsap === 'undefined' || !document.getElementById('hero')) return;

        gsap.registerPlugin(ScrollTrigger);

        // Ensure all elements start fully visible — prevents black screen on load/refresh
        gsap.set('#hero-eyebrow, #hero-title, #hero-subtitle, #hero-cta', { opacity: 1, y: 0, scale: 1, clearProps: 'transform' });

        const tl = gsap.timeline({
            scrollTrigger: {
                trigger: '#hero',
                start: 'top top',
                end: '+=110%',
                scrub: 1.8,
                pin: true,
                anticipatePin: 1,
            }
        });

        // Text layer fades out
        tl.to('#hero-eyebrow',  { opacity: 0, y: -24, duration: 1 }, 0)
          .to('#hero-title',    { opacity: 0, y: -48, scale: 0.88, duration: 1 }, 0.05)
          .to('#hero-subtitle', { opacity: 0, y: -20, duration: 0.9 }, 0.08)
          .to('#hero-cta',      { opacity: 0, y: -16, duration: 0.8 }, 0.1)
          // Dashboard straightens and fills the screen
          .to('#dashboard-preview', {
              rotateX: 0,
              scale: 1.12,
              y: -90,
              ease: 'power2.out',
              duration: 1,
          }, 0);

        // Bento cards stagger in on scroll
        gsap.utils.toArray('.bento-card').forEach(function (card, i) {
            gsap.from(card, {
                scrollTrigger: {
                    trigger: card,
                    start: 'top 88%',
                    toggleActions: 'play none none reverse',
                },
                opacity: 0,
                y: 32,
                duration: 0.75,
                delay: i * 0.06,
                ease: 'power2.out',
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCinematicHero);
    } else {
        initCinematicHero();
    }
})();
</script>
