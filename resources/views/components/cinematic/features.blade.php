{{-- ============================================================
     Cinematic Bento Features v2 — Confirmi.space
     4 cards: Import · Suivi · Statistiques · API
     Usage: <x-cinematic.features />
     ============================================================ --}}

<section
    id="features"
    style="position: relative; padding: 8rem 1.5rem; overflow: hidden; background-color: #030712;"
>
    {{-- Ambient top glow --}}
    <div style="
        position: absolute; inset: 0; pointer-events: none;
        background:
            radial-gradient(ellipse 70% 35% at 50% 0%, rgba(99,102,241,0.09), transparent 65%),
            radial-gradient(ellipse 30% 20% at 10% 50%, rgba(14,165,233,0.05), transparent 60%),
            radial-gradient(ellipse 30% 20% at 90% 60%, rgba(139,92,246,0.06), transparent 60%);
    "></div>

    <div style="position: relative; z-index: 10; max-width: 1200px; margin: 0 auto;">

        {{-- ── Section Header ── --}}
        <div style="text-align: center; margin-bottom: 4rem;">
            <div style="
                display: inline-flex; align-items: center; gap: 8px;
                padding: 6px 16px; border-radius: 100px;
                background: rgba(99,102,241,0.1);
                border: 1px solid rgba(99,102,241,0.2);
                margin-bottom: 1.5rem;
            ">
                <span style="width: 6px; height: 6px; border-radius: 50%; background: #818cf8;"></span>
                <span style="font-size: 11px; font-weight: 600; letter-spacing: 0.1em; color: #a5b4fc; text-transform: uppercase;">Fonctionnalités</span>
            </div>
            <h2 style="
                font-size: clamp(2rem, 5vw, 3.25rem);
                font-weight: 900;
                letter-spacing: -0.03em;
                color: #fff;
                margin-bottom: 1rem;
                line-height: 1.1;
            ">
                Tout ce dont vous avez besoin,<br>
                <span style="background: linear-gradient(135deg, #818cf8, #c084fc); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">rien de plus.</span>
            </h2>
            <p style="font-size: 1.0625rem; color: rgba(255,255,255,0.4); font-weight: 300; max-width: 36rem; margin: 0 auto; line-height: 1.75;">
                De l'import à la livraison — une plateforme unifiée pour les équipes e-commerce ambitieuses.
            </p>
        </div>

        {{-- ── Bento Grid ──
             Row 1: Import (2 cols) · Suivi (1 col)
             Row 2: Statistiques (1 col) · API (2 cols) --}}
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; grid-auto-rows: 310px;">

            {{-- ── Card 1 · IMPORT (col-span-2, large) ── --}}
            <div
                class="bento-card"
                style="
                    grid-column: span 2;
                    border-radius: 20px;
                    padding: 2rem;
                    position: relative;
                    overflow: hidden;
                    background: rgba(255,255,255,0.025);
                    border: 1px solid rgba(255,255,255,0.07);
                    backdrop-filter: blur(20px);
                    -webkit-backdrop-filter: blur(20px);
                    transition: border-color .3s;
                    display: flex; flex-direction: column;
                "
                onmouseover="this.style.borderColor='rgba(99,102,241,0.35)'"
                onmouseout="this.style.borderColor='rgba(255,255,255,0.07)'"
            >
                {{-- Hover glow --}}
                <div class="bento-hover-glow" style="
                    position: absolute; inset: 0; pointer-events: none; opacity: 0;
                    background: radial-gradient(ellipse 60% 70% at 20% 55%, rgba(99,102,241,0.1), transparent);
                    transition: opacity .4s;
                "></div>

                {{-- Content --}}
                <div style="position: relative; z-index: 1; display: flex; flex-direction: column; height: 100%;">

                    {{-- Icon + label --}}
                    <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1.25rem; flex-shrink: 0;">
                        <div style="
                            width: 46px; height: 46px; border-radius: 14px; flex-shrink: 0;
                            background: rgba(99,102,241,0.16); border: 1px solid rgba(99,102,241,0.3);
                            display: flex; align-items: center; justify-content: center;
                        ">
                            <i class="fas fa-cloud-upload-alt" style="font-size: 18px; color: #818cf8;"></i>
                        </div>
                        <span style="
                            font-size: 11px; font-weight: 600; letter-spacing: 0.08em;
                            color: rgba(99,102,241,0.8); background: rgba(99,102,241,0.1);
                            border: 1px solid rgba(99,102,241,0.2);
                            padding: 3px 10px; border-radius: 100px; text-transform: uppercase;
                        ">Import</span>
                    </div>

                    <h3 style="font-size: 1.25rem; font-weight: 700; color: #fff; margin-bottom: .5rem; letter-spacing: -.02em;">Import automatique</h3>
                    <p style="font-size: .875rem; color: rgba(255,255,255,0.42); line-height: 1.65; max-width: 28rem; margin-bottom: 1.25rem;">
                        Glissez un fichier Excel ou CSV, ou connectez Shopify et WooCommerce. Vos commandes arrivent en quelques secondes, zéro saisie manuelle.
                    </p>

                    {{-- Visual: drag zone + platform badges --}}
                    <div style="margin-top: auto; display: flex; gap: 12px; align-items: flex-end;">

                        {{-- Drag zone mock --}}
                        <div style="
                            flex: 1; border-radius: 14px; padding: 16px;
                            border: 1.5px dashed rgba(99,102,241,0.28);
                            background: rgba(99,102,241,0.04);
                            display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px;
                            min-height: 80px;
                        ">
                            <i class="fas fa-file-upload" style="font-size: 20px; color: rgba(99,102,241,0.55);"></i>
                            <div style="font-size: 11px; color: rgba(255,255,255,0.3); font-weight: 500;">Déposer ici · CSV, XLSX</div>
                            <div style="
                                padding: 4px 14px; border-radius: 8px; font-size: 11px; font-weight: 600;
                                background: rgba(99,102,241,0.2); border: 1px solid rgba(99,102,241,0.35);
                                color: rgba(255,255,255,0.7);
                            ">Parcourir</div>
                        </div>

                        {{-- Platform badges --}}
                        <div style="display: flex; flex-direction: column; gap: 7px;">
                            @foreach([
                                ['fab fa-shopify',      'Shopify',     '#95BF47', 'rgba(149,191,71,0.1)',  'rgba(149,191,71,0.25)'],
                                ['fab fa-wordpress',    'WooCommerce', '#7F54B3', 'rgba(127,84,179,0.1)',  'rgba(127,84,179,0.25)'],
                                ['fas fa-file-excel',   'Excel / CSV', '#217346', 'rgba(33,115,70,0.1)',   'rgba(33,115,70,0.25)'],
                            ] as [$icon, $name, $color, $bg, $border])
                            <div style="
                                display: inline-flex; align-items: center; gap: 7px;
                                padding: 6px 12px; border-radius: 10px;
                                background: {{ $bg }}; border: 1px solid {{ $border }};
                                font-size: 12px; font-weight: 600; color: rgba(255,255,255,0.75);
                                white-space: nowrap;
                            ">
                                <i class="{{ $icon }}" style="font-size: 13px; color: {{ $color }};"></i>
                                {{ $name }}
                            </div>
                            @endforeach
                        </div>
                    </div>

                </div>
            </div>

            {{-- ── Card 2 · SUIVI (col-span-1, small) ── --}}
            <div
                class="bento-card"
                style="
                    grid-column: span 1;
                    border-radius: 20px; padding: 2rem;
                    position: relative; overflow: hidden;
                    background: rgba(255,255,255,0.025);
                    border: 1px solid rgba(255,255,255,0.07);
                    backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
                    transition: border-color .3s;
                    display: flex; flex-direction: column;
                "
                onmouseover="this.style.borderColor='rgba(14,165,233,0.38)'"
                onmouseout="this.style.borderColor='rgba(255,255,255,0.07)'"
            >
                <div class="bento-hover-glow" style="
                    position: absolute; inset: 0; pointer-events: none; opacity: 0;
                    background: radial-gradient(ellipse 90% 90% at 80% 10%, rgba(14,165,233,0.1), transparent);
                    transition: opacity .4s;
                "></div>

                <div style="position: relative; z-index: 1; display: flex; flex-direction: column; height: 100%;">

                    <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1.25rem; flex-shrink: 0;">
                        <div style="
                            width: 46px; height: 46px; border-radius: 14px;
                            background: rgba(14,165,233,0.16); border: 1px solid rgba(14,165,233,0.3);
                            display: flex; align-items: center; justify-content: center;
                        ">
                            <i class="fas fa-route" style="font-size: 18px; color: #38bdf8;"></i>
                        </div>
                        <span style="
                            font-size: 11px; font-weight: 600; letter-spacing: 0.08em;
                            color: rgba(14,165,233,0.8); background: rgba(14,165,233,0.1);
                            border: 1px solid rgba(14,165,233,0.22);
                            padding: 3px 10px; border-radius: 100px; text-transform: uppercase;
                        ">Temps réel</span>
                    </div>

                    <h3 style="font-size: 1.2rem; font-weight: 700; color: #fff; margin-bottom: .5rem; letter-spacing: -.02em;">Suivi des commandes</h3>
                    <p style="font-size: .8125rem; color: rgba(255,255,255,0.4); line-height: 1.65; margin-bottom: 1rem;">
                        Chaque étape visible en un coup d'œil — de la confirmation à la livraison.
                    </p>

                    {{-- Visual: vertical timeline --}}
                    <div style="margin-top: auto; display: flex; flex-direction: column; gap: 0;">
                        @foreach([
                            ['Confirmée',   '#38bdf8', true,  'rgba(14,165,233,0.18)', '2 min'],
                            ['En prépa.',   '#a78bfa', true,  'rgba(139,92,246,0.18)', '18 min'],
                            ['Expédiée',    '#fbbf24', true,  'rgba(234,179,8,0.18)',  '2h'],
                            ['Livrée',      '#4ade80', false, 'rgba(34,197,94,0.18)',  'Aujourd\'hui'],
                        ] as [$label, $color, $hasLine, $bg, $time])
                        <div style="display: flex; align-items: center; gap: 10px; padding: 5px 0;">
                            <div style="display: flex; flex-direction: column; align-items: center; flex-shrink: 0; width: 16px;">
                                <div style="
                                    width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0;
                                    background: {{ $color }};
                                    box-shadow: 0 0 8px {{ $color }}80;
                                "></div>
                                @if($hasLine)
                                <div style="width: 1px; height: 16px; background: rgba(255,255,255,0.06); margin-top: 2px;"></div>
                                @endif
                            </div>
                            <div style="flex: 1; display: flex; align-items: center; justify-content: space-between;">
                                <span style="
                                    font-size: 12px; font-weight: 600; padding: 3px 10px; border-radius: 8px;
                                    background: {{ $bg }}; color: rgba(255,255,255,0.78);
                                ">{{ $label }}</span>
                                <span style="font-size: 10px; color: rgba(255,255,255,0.22); font-weight: 500;">{{ $time }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>

                </div>
            </div>

            {{-- ── Card 3 · STATISTIQUES (col-span-1, small) ── --}}
            <div
                class="bento-card"
                style="
                    grid-column: span 1;
                    border-radius: 20px; padding: 2rem;
                    position: relative; overflow: hidden;
                    background: rgba(255,255,255,0.025);
                    border: 1px solid rgba(255,255,255,0.07);
                    backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
                    transition: border-color .3s;
                    display: flex; flex-direction: column;
                "
                onmouseover="this.style.borderColor='rgba(139,92,246,0.38)'"
                onmouseout="this.style.borderColor='rgba(255,255,255,0.07)'"
            >
                <div class="bento-hover-glow" style="
                    position: absolute; inset: 0; pointer-events: none; opacity: 0;
                    background: radial-gradient(ellipse 90% 90% at 20% 80%, rgba(139,92,246,0.12), transparent);
                    transition: opacity .4s;
                "></div>

                <div style="position: relative; z-index: 1; display: flex; flex-direction: column; height: 100%;">

                    <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1.25rem; flex-shrink: 0;">
                        <div style="
                            width: 46px; height: 46px; border-radius: 14px;
                            background: rgba(139,92,246,0.16); border: 1px solid rgba(139,92,246,0.3);
                            display: flex; align-items: center; justify-content: center;
                        ">
                            <i class="fas fa-chart-bar" style="font-size: 18px; color: #a78bfa;"></i>
                        </div>
                        <span style="
                            font-size: 11px; font-weight: 600; letter-spacing: 0.08em;
                            color: rgba(139,92,246,0.9); background: rgba(139,92,246,0.1);
                            border: 1px solid rgba(139,92,246,0.22);
                            padding: 3px 10px; border-radius: 100px; text-transform: uppercase;
                        ">Analytics</span>
                    </div>

                    <h3 style="font-size: 1.2rem; font-weight: 700; color: #fff; margin-bottom: .5rem; letter-spacing: -.02em;">Statistiques</h3>
                    <p style="font-size: .8125rem; color: rgba(255,255,255,0.4); line-height: 1.65; margin-bottom: 1rem;">
                        KPIs, CA mensuel et performances équipe — toujours à jour.
                    </p>

                    {{-- Visual: mini sparkline + 2 KPIs --}}
                    <div style="margin-top: auto; display: flex; flex-direction: column; gap: 10px;">

                        {{-- Sparkline bars --}}
                        <div style="
                            display: flex; align-items: flex-end; gap: 3px;
                            height: 44px; border-radius: 10px; padding: 6px 8px;
                            background: rgba(255,255,255,0.025); border: 1px solid rgba(255,255,255,0.05);
                        ">
                            @foreach([30,48,38,65,44,80,56,72,46,88,62,78] as $h)
                            <div style="
                                flex: 1; border-radius: 2px; min-width: 0;
                                height: {{ $h }}%;
                                background: linear-gradient(to top, rgba(139,92,246,0.65), rgba(196,130,252,0.15));
                            "></div>
                            @endforeach
                        </div>

                        {{-- KPI row --}}
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                            @foreach([
                                ['94.2%', 'Taux livraison', '#4ade80',  'rgba(34,197,94,0.12)'],
                                ['↑ 12%', 'CA ce mois',     '#a78bfa',  'rgba(139,92,246,0.12)'],
                            ] as [$val, $label, $color, $bg])
                            <div style="
                                border-radius: 12px; padding: 10px;
                                background: {{ $bg }}; border: 1px solid {{ $bg }};
                                text-align: center;
                            ">
                                <div style="font-size: 15px; font-weight: 800; color: {{ $color }}; letter-spacing: -.02em;">{{ $val }}</div>
                                <div style="font-size: 10px; color: rgba(255,255,255,0.32); margin-top: 2px; font-weight: 500;">{{ $label }}</div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Card 4 · API (col-span-2, large) ── --}}
            <div
                class="bento-card"
                style="
                    grid-column: span 2;
                    border-radius: 20px; padding: 2rem;
                    position: relative; overflow: hidden;
                    background: rgba(255,255,255,0.025);
                    border: 1px solid rgba(255,255,255,0.07);
                    backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
                    transition: border-color .3s;
                    display: flex; flex-direction: column;
                "
                onmouseover="this.style.borderColor='rgba(34,197,94,0.35)'"
                onmouseout="this.style.borderColor='rgba(255,255,255,0.07)'"
            >
                <div class="bento-hover-glow" style="
                    position: absolute; inset: 0; pointer-events: none; opacity: 0;
                    background: radial-gradient(ellipse 60% 70% at 80% 50%, rgba(34,197,94,0.07), transparent);
                    transition: opacity .4s;
                "></div>

                <div style="position: relative; z-index: 1; display: flex; flex-direction: column; height: 100%;">

                    <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1.25rem; flex-shrink: 0;">
                        <div style="
                            width: 46px; height: 46px; border-radius: 14px;
                            background: rgba(34,197,94,0.15); border: 1px solid rgba(34,197,94,0.28);
                            display: flex; align-items: center; justify-content: center;
                        ">
                            <i class="fas fa-terminal" style="font-size: 16px; color: #4ade80;"></i>
                        </div>
                        <span style="
                            font-size: 11px; font-weight: 600; letter-spacing: 0.08em;
                            color: rgba(34,197,94,0.9); background: rgba(34,197,94,0.1);
                            border: 1px solid rgba(34,197,94,0.22);
                            padding: 3px 10px; border-radius: 100px; text-transform: uppercase;
                        ">REST · Webhooks</span>
                    </div>

                    <h3 style="font-size: 1.25rem; font-weight: 700; color: #fff; margin-bottom: .5rem; letter-spacing: -.02em;">API & Intégrations</h3>
                    <p style="font-size: .875rem; color: rgba(255,255,255,0.42); line-height: 1.65; max-width: 26rem; margin-bottom: 1.25rem;">
                        Une API RESTful documentée pour automatiser chaque étape — récupération, mise à jour, webhooks temps réel.
                    </p>

                    {{-- Visual: code snippet + endpoint list --}}
                    <div style="margin-top: auto; display: flex; gap: 12px; align-items: flex-start;">

                        {{-- Code block --}}
                        <div style="
                            flex: 1; border-radius: 14px; padding: 14px 16px;
                            background: rgba(0,0,0,0.35); border: 1px solid rgba(255,255,255,0.07);
                            font-family: 'Menlo', 'Consolas', monospace; font-size: 11.5px;
                            line-height: 1.7; overflow: hidden;
                        ">
                            <div style="color: rgba(255,255,255,0.2); margin-bottom: 6px; font-size: 10px; font-family: sans-serif; letter-spacing: .05em;">GET /api/v1/orders</div>
                            <div><span style="color: #7dd3fc;">{</span></div>
                            <div style="padding-left: 12px;">
                                <span style="color: #fca5a5;">"status"</span><span style="color: rgba(255,255,255,0.3);">: </span><span style="color: #86efac;">"success"</span><span style="color: rgba(255,255,255,0.3);">,</span>
                            </div>
                            <div style="padding-left: 12px;">
                                <span style="color: #fca5a5;">"total"</span><span style="color: rgba(255,255,255,0.3);">: </span><span style="color: #fcd34d;">1248</span><span style="color: rgba(255,255,255,0.3);">,</span>
                            </div>
                            <div style="padding-left: 12px;">
                                <span style="color: #fca5a5;">"data"</span><span style="color: rgba(255,255,255,0.3);">: </span><span style="color: #7dd3fc;">[...]</span>
                            </div>
                            <div><span style="color: #7dd3fc;">}</span></div>
                        </div>

                        {{-- Endpoints + badges --}}
                        <div style="display: flex; flex-direction: column; gap: 7px; flex-shrink: 0;">
                            @foreach([
                                ['GET',    '/orders',    '#38bdf8', 'rgba(14,165,233,0.12)'],
                                ['POST',   '/orders',    '#4ade80', 'rgba(34,197,94,0.12)'],
                                ['PATCH',  '/orders/:id','#fbbf24', 'rgba(234,179,8,0.12)'],
                                ['DELETE', '/orders/:id','#f87171', 'rgba(239,68,68,0.12)'],
                            ] as [$method, $path, $color, $bg])
                            <div style="
                                display: flex; align-items: center; gap: 8px;
                                padding: 5px 10px; border-radius: 9px;
                                background: {{ $bg }}; border: 1px solid {{ $bg }};
                                font-family: 'Menlo', 'Consolas', monospace; font-size: 11px;
                                white-space: nowrap;
                            ">
                                <span style="font-weight: 700; color: {{ $color }}; min-width: 42px;">{{ $method }}</span>
                                <span style="color: rgba(255,255,255,0.45);">{{ $path }}</span>
                            </div>
                            @endforeach

                            {{-- Webhook badge --}}
                            <div style="
                                display: flex; align-items: center; gap: 6px;
                                padding: 5px 10px; border-radius: 9px;
                                background: rgba(139,92,246,0.12); border: 1px solid rgba(139,92,246,0.22);
                                font-size: 11px; white-space: nowrap;
                            ">
                                <i class="fas fa-bolt" style="font-size: 9px; color: #a78bfa;"></i>
                                <span style="color: rgba(255,255,255,0.6); font-weight: 600;">Webhooks disponibles</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>{{-- /bento grid --}}

        {{-- ── CTA bottom ── --}}
        <div style="text-align: center; margin-top: 4rem;">
            <a
                href="{{ route('confirmi.login') }}"
                style="
                    display: inline-flex; align-items: center; gap: 10px;
                    padding: .875rem 2rem; border-radius: .875rem;
                    font-size: .9375rem; font-weight: 700; color: #fff; text-decoration: none;
                    background: linear-gradient(135deg, #4f46e5, #7c3aed);
                    box-shadow: 0 0 0 1px rgba(99,102,241,0.45), 0 8px 40px rgba(99,102,241,0.28);
                    transition: all .2s;
                "
                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 0 0 1px rgba(99,102,241,0.65), 0 14px 50px rgba(99,102,241,0.42)';"
                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 0 0 1px rgba(99,102,241,0.45), 0 8px 40px rgba(99,102,241,0.28)';"
            >
                <i class="fas fa-rocket" style="font-size: 14px;"></i>
                Commencer gratuitement
                <i class="fas fa-arrow-right" style="font-size: 12px; opacity: .7;"></i>
            </a>
            <p style="margin-top: .875rem; font-size: .8125rem; color: rgba(255,255,255,0.22); font-weight: 400;">
                Aucune carte bancaire requise · Essai gratuit 14 jours
            </p>
        </div>

    </div>
</section>

{{-- Hover glow activation --}}
<script>
document.querySelectorAll('.bento-card').forEach(function(card) {
    var glow = card.querySelector('.bento-hover-glow');
    if (!glow) return;
    card.addEventListener('mouseenter', function() { glow.style.opacity = '1'; });
    card.addEventListener('mouseleave', function() { glow.style.opacity = '0'; });
});
</script>
