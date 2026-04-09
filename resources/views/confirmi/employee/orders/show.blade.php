@extends('confirmi.layouts.app')
@section('title', 'Commande #' . ($assignment->order->id ?? 'N/A'))
@section('page-title', 'Commande #' . ($assignment->order->id ?? 'N/A'))

@section('css')
<style>
.show-grid {
    display: grid;
    grid-template-columns: 1fr 360px;
    gap: 1rem;
    align-items: start;
}
.s-card {
    background: var(--bg-card);
    border-radius: var(--radius-lg, 14px);
    border: 1px solid var(--border);
    box-shadow: var(--shadow);
    overflow: hidden;
}
.s-card-head {
    padding: 0.75rem 1.15rem;
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
}
.s-card-head h6 {
    font-weight: 700; font-size: 0.85rem; margin: 0;
    color: var(--text); display: flex; align-items: center; gap: 0.4rem;
}
.s-card-body { padding: 1rem 1.15rem; }

.order-banner {
    background: linear-gradient(135deg, var(--accent, #2563eb), #7c3aed);
    border-radius: var(--radius-lg, 14px);
    padding: 1.15rem 1.35rem; color: white;
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1rem;
}
.order-banner-id { font-size: 1.2rem; font-weight: 800; display: flex; align-items: center; gap: 0.6rem; }
.order-banner-meta { display: flex; gap: 0.75rem; flex-wrap: wrap; margin-top: 0.35rem; font-size: 0.8rem; opacity: 0.85; }
.order-banner-meta .m { display: flex; align-items: center; gap: 4px; }
.order-banner-status {
    padding: 0.3rem 0.85rem; border-radius: 10px; font-weight: 700;
    font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.3px;
}
.ots-assigned   { background: rgba(255,255,255,0.2); color: #fff; }
.ots-in_progress { background: rgba(139,92,246,0.3); color: #e9d5ff; }
.ots-confirmed  { background: rgba(16,185,129,0.3); color: #a7f3d0; }
.ots-cancelled  { background: rgba(239,68,68,0.3); color: #fca5a5; }

.info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
.info-grid .full { grid-column: 1 / -1; }
.info-lbl { font-size: 0.68rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 0.15rem; }
.info-val { font-size: 0.88rem; font-weight: 600; color: var(--text); }

.phone-btn {
    display: inline-flex; align-items: center; gap: 0.35rem;
    padding: 0.4rem 0.9rem; border-radius: 8px;
    font-weight: 600; font-size: 0.82rem; text-decoration: none; transition: all 0.15s;
}
.phone-btn-primary { background: var(--success); color: white; }
.phone-btn-primary:hover { background: #059669; color: white; }
.phone-btn-alt { background: var(--bg-hover); color: var(--text); border: 1px solid var(--border); }
.phone-btn-alt:hover { border-color: var(--accent); color: var(--accent); }

.item-row {
    display: flex; align-items: center; gap: 0.65rem;
    padding: 0.55rem 0.75rem; background: var(--bg-card-alt, var(--bg-card));
    border-radius: 8px; border: 1px solid var(--border); margin-bottom: 0.4rem;
}
.item-ico {
    width: 32px; height: 32px; border-radius: 8px;
    background: rgba(37,99,235,0.1); color: var(--accent);
    display: flex; align-items: center; justify-content: center;
    font-size: 0.8rem; flex-shrink: 0;
}
[data-theme="dark"] .item-ico { background: rgba(37,99,235,0.15); }
.item-name { font-weight: 600; font-size: 0.82rem; color: var(--text); }
.item-detail { font-size: 0.72rem; color: var(--text-secondary); }
.item-price { font-weight: 700; font-size: 0.82rem; color: var(--text); white-space: nowrap; }
.total-amount { font-size: 1.3rem; font-weight: 800; color: var(--accent); }

.action-panel {
    position: sticky; top: calc(var(--header-h, 60px) + 1rem);
}
.result-opt {
    display: flex; align-items: center; gap: 0.55rem;
    padding: 0.6rem 0.75rem; background: var(--bg-card-alt, var(--bg-card));
    border: 2px solid var(--border); border-radius: 10px;
    cursor: pointer; transition: all 0.15s; color: var(--text); margin-bottom: 0.4rem;
}
.result-opt:hover { border-color: rgba(37,99,235,0.4); }
.result-opt input[type="radio"] { display: none; }
.result-opt:has(input:checked) { border-color: var(--accent); background: rgba(37,99,235,0.06); }
[data-theme="dark"] .result-opt:has(input:checked) { background: rgba(37,99,235,0.12); }
.result-ico {
    width: 34px; height: 34px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.88rem; flex-shrink: 0;
}
.result-ico.r-green  { background: var(--success-bg); color: #059669; }
.result-ico.r-amber  { background: var(--warning-bg); color: #d97706; }
.result-ico.r-blue   { background: var(--accent-bg); color: var(--accent-light); }
.result-ico.r-red    { background: var(--danger-bg); color: #dc2626; }
[data-theme="dark"] .result-ico.r-green  { color: #34d399; }
[data-theme="dark"] .result-ico.r-amber  { color: #fbbf24; }
[data-theme="dark"] .result-ico.r-blue   { color: #93c5fd; }
[data-theme="dark"] .result-ico.r-red    { color: #fca5a5; }
.result-text { font-weight: 600; font-size: 0.82rem; }

.s-btn {
    display: flex; align-items: center; justify-content: center;
    gap: 0.4rem; padding: 0.65rem 1rem; border: none; border-radius: 10px;
    font-weight: 700; font-size: 0.85rem; cursor: pointer;
    transition: all 0.15s; width: 100%;
}
.s-btn:hover { transform: translateY(-1px); box-shadow: var(--shadow-md); }
.s-btn-primary { background: var(--accent); color: white; }
.s-btn-start { background: var(--info, #06b6d4); color: white; }
.back-btn {
    display: inline-flex; align-items: center; gap: 0.35rem;
    padding: 0.4rem 0.85rem; background: var(--bg-card);
    border: 1.5px solid var(--border); border-radius: 8px;
    color: var(--text-secondary); text-decoration: none;
    font-weight: 600; font-size: 0.82rem; transition: all 0.15s;
}
.back-btn:hover { border-color: var(--accent); color: var(--accent); }

@media (max-width: 992px) {
    .show-grid { grid-template-columns: 1fr; }
    .action-panel { position: static; }
}
@media (max-width: 576px) {
    .info-grid { grid-template-columns: 1fr; }
    .order-banner { padding: 1rem; }
    .order-banner-id { font-size: 1rem; }
}
</style>
@endsection

@section('content')
@php $order = $assignment->order; @endphp

{{-- ═══ Order Banner ═══ --}}
<div class="order-banner">
    <div>
        <div class="order-banner-id">
            <i class="fas fa-shopping-basket"></i>
            Commande #{{ $order->id ?? 'N/A' }}
        </div>
        <div class="order-banner-meta">
            <span class="m"><i class="fas fa-store"></i> {{ $assignment->admin->shop_name ?? $assignment->admin->name ?? '-' }}</span>
            <span class="m"><i class="fas fa-calendar"></i> {{ $order->created_at?->format('d/m/Y') ?? '-' }}</span>
            <span class="m"><i class="fas fa-redo"></i> {{ $assignment->attempts }} tentative(s)</span>
            <span class="m"><i class="fas fa-clock"></i> {{ $assignment->last_attempt_at?->diffForHumans() ?? 'Jamais appelé' }}</span>
        </div>
    </div>
    <div class="order-banner-status ots-{{ $assignment->status }}">
        {{ match($assignment->status) {
            'assigned' => 'Assignée', 'in_progress' => 'En cours',
            'confirmed' => 'Confirmée', 'cancelled' => 'Annulée', default => ucfirst($assignment->status)
        } }}
    </div>
</div>

<div class="show-grid">
    {{-- LEFT — Order Info --}}
    <div>
        {{-- Customer --}}
        <div class="s-card mb-3">
            <div class="s-card-head">
                <h6><i class="fas fa-user" style="color:var(--accent);"></i> Informations client</h6>
            </div>
            <div class="s-card-body">
                @if($order)
                <div class="info-grid">
                    <div>
                        <div class="info-lbl">Destinataire</div>
                        <div class="info-val" style="font-size:1rem;">{{ $order->customer_name }}</div>
                    </div>
                    <div>
                        <div class="info-lbl">Téléphone</div>
                        <div class="d-flex gap-2 flex-wrap align-items-center">
                            <a href="tel:{{ $order->customer_phone }}" class="phone-btn phone-btn-primary">
                                <i class="fas fa-phone"></i> {{ $order->customer_phone }}
                            </a>
                            @if($order->customer_phone_2)
                            <a href="tel:{{ $order->customer_phone_2 }}" class="phone-btn phone-btn-alt">
                                <i class="fas fa-phone"></i> {{ $order->customer_phone_2 }}
                            </a>
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="info-lbl">Gouvernorat</div>
                        <div class="info-val">{{ $order->customer_governorate ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="info-lbl">Ville</div>
                        <div class="info-val">{{ $order->customer_city ?? '-' }}</div>
                    </div>
                    <div class="full">
                        <div class="info-lbl">Adresse</div>
                        <div class="info-val" style="font-weight:500;">{{ $order->customer_address ?? '-' }}</div>
                    </div>
                    @if($order->notes)
                    <div class="full">
                        <div class="info-lbl">Notes</div>
                        <div class="info-val" style="font-weight:500;">{{ $order->notes }}</div>
                    </div>
                    @endif
                </div>
                @endif
            </div>
        </div>

        {{-- Items --}}
        @if($order && $order->items && $order->items->count() > 0)
        <div class="s-card mb-3">
            <div class="s-card-head">
                <h6><i class="fas fa-shopping-cart" style="color:var(--success);"></i> Articles</h6>
                <span style="font-size:0.75rem;font-weight:700;color:var(--text-secondary);">{{ $order->items->count() }} article(s)</span>
            </div>
            <div class="s-card-body">
                @foreach($order->items as $item)
                <div class="item-row">
                    <div class="item-ico"><i class="fas fa-box"></i></div>
                    <div class="flex-grow-1">
                        <div class="item-name">{{ $item->product->name ?? $item->product_name ?? 'N/A' }}</div>
                        <div class="item-detail">Qté: {{ $item->quantity }} × {{ number_format($item->unit_price ?? 0, 3) }} DT</div>
                    </div>
                    <div class="item-price">{{ number_format(($item->unit_price ?? 0) * $item->quantity, 3) }} DT</div>
                </div>
                @endforeach
                <div class="d-flex justify-content-between align-items-center mt-3 pt-3" style="border-top:2px solid var(--border);">
                    <span class="fw-bold" style="color:var(--text-secondary);font-size:0.82rem;">TOTAL</span>
                    <span class="total-amount">{{ number_format($order->total_price, 3) }} DT</span>
                </div>
            </div>
        </div>
        @elseif($order)
        <div class="s-card mb-3">
            <div class="s-card-head">
                <h6><i class="fas fa-box" style="color:var(--success);"></i> Montant</h6>
            </div>
            <div class="s-card-body">
                <span class="total-amount">{{ number_format($order->total_price ?? 0, 3) }} DT</span>
            </div>
        </div>
        @endif

        {{-- Admin --}}
        <div class="s-card mb-3">
            <div class="s-card-head">
                <h6><i class="fas fa-building" style="color:var(--info);"></i> Client (Admin)</h6>
            </div>
            <div class="s-card-body">
                <div class="info-grid">
                    <div>
                        <div class="info-lbl">Admin</div>
                        <div class="info-val">{{ $assignment->admin->name ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="info-lbl">Boutique</div>
                        <div class="info-val">{{ $assignment->admin->shop_name ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- RIGHT — Action Panel + Tracking --}}
    <div class="action-panel">
        {{-- Tracking --}}
        <div class="s-card mb-3">
            <div class="s-card-head">
                <h6><i class="fas fa-info-circle" style="color:var(--warning);"></i> Suivi</h6>
            </div>
            <div class="s-card-body" style="padding:0.75rem 1rem;">
                <div style="display:flex;justify-content:space-between;padding:0.35rem 0;border-bottom:1px solid var(--border);">
                    <span style="font-size:0.75rem;color:var(--text-secondary);">Tentatives</span>
                    <span style="font-weight:700;font-size:0.85rem;color:var(--text);">{{ $assignment->attempts }}</span>
                </div>
                @if($assignment->first_attempt_at)
                <div style="display:flex;justify-content:space-between;padding:0.35rem 0;border-bottom:1px solid var(--border);">
                    <span style="font-size:0.75rem;color:var(--text-secondary);">Premier appel</span>
                    <span style="font-weight:500;font-size:0.78rem;color:var(--text);">{{ $assignment->first_attempt_at->format('d/m/Y H:i') }}</span>
                </div>
                @endif
                @if($assignment->last_attempt_at)
                <div style="display:flex;justify-content:space-between;padding:0.35rem 0;border-bottom:1px solid var(--border);">
                    <span style="font-size:0.75rem;color:var(--text-secondary);">Dernier appel</span>
                    <span style="font-weight:500;font-size:0.78rem;color:var(--text);">{{ $assignment->last_attempt_at->format('d/m/Y H:i') }}</span>
                </div>
                @endif
                @if($assignment->notes)
                <div style="padding:0.35rem 0;margin-top:0.15rem;">
                    <span style="font-size:0.68rem;color:var(--text-muted);text-transform:uppercase;font-weight:600;">Notes</span>
                    <div style="font-size:0.8rem;color:var(--text);margin-top:0.1rem;">{{ $assignment->notes }}</div>
                </div>
                @endif
            </div>
        </div>

        {{-- Action Form --}}
        @if($assignment->canBeManaged())
        <div class="s-card">
            <div class="s-card-head" style="background:var(--accent);border-bottom:none;">
                <h6 style="color:white;"><i class="fas fa-headset"></i> Enregistrer résultat</h6>
            </div>
            <div class="s-card-body">
                @if($assignment->status === 'assigned')
                    <form method="POST" action="{{ route('confirmi.employee.orders.start', $assignment) }}" class="mb-3">
                        @csrf
                        <button type="submit" class="s-btn s-btn-start">
                            <i class="fas fa-play"></i> Démarrer le traitement
                        </button>
                    </form>
                    <div style="height:1px;background:var(--border);margin-bottom:0.85rem;"></div>
                @endif

                <form method="POST" action="{{ route('confirmi.employee.orders.attempt', $assignment) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="result-opt">
                            <input type="radio" name="result" value="confirmed" required>
                            <div class="result-ico r-green"><i class="fas fa-check-circle"></i></div>
                            <span class="result-text">Confirmée</span>
                        </label>
                        <label class="result-opt">
                            <input type="radio" name="result" value="no_answer">
                            <div class="result-ico r-amber"><i class="fas fa-phone-slash"></i></div>
                            <span class="result-text">Pas de réponse</span>
                        </label>
                        <label class="result-opt">
                            <input type="radio" name="result" value="callback">
                            <div class="result-ico r-blue"><i class="fas fa-phone-alt"></i></div>
                            <span class="result-text">Rappeler plus tard</span>
                        </label>
                        <label class="result-opt">
                            <input type="radio" name="result" value="cancelled">
                            <div class="result-ico r-red"><i class="fas fa-times-circle"></i></div>
                            <span class="result-text">Annulée</span>
                        </label>
                    </div>
                    <div class="mb-3">
                        <textarea name="notes" class="form-control" rows="2" placeholder="Notes sur l'appel (optionnel)..." style="font-size:0.82rem;"></textarea>
                    </div>
                    <button type="submit" class="s-btn s-btn-primary">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                </form>
            </div>
        </div>
        @else
        <div class="s-card">
            <div class="s-card-body text-center py-4">
                @if($assignment->status === 'confirmed')
                    <i class="fas fa-check-circle d-block mb-2" style="font-size:2rem;color:var(--success);"></i>
                    <p class="fw-bold mb-1" style="color:var(--success);">Commande confirmée</p>
                @elseif($assignment->status === 'cancelled')
                    <i class="fas fa-times-circle d-block mb-2" style="font-size:2rem;color:var(--danger);"></i>
                    <p class="fw-bold mb-1" style="color:var(--danger);">Commande annulée</p>
                @endif
                @if($assignment->completed_at)
                    <small style="color:var(--text-secondary);">{{ $assignment->completed_at->format('d/m/Y H:i') }}</small>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

<div class="mt-3">
    <a href="{{ route('confirmi.employee.orders.index') }}" class="back-btn"><i class="fas fa-arrow-left"></i> Retour</a>
</div>
@endsection
