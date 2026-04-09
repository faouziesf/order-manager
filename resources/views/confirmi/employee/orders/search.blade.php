@extends('confirmi.layouts.app')
@section('title', 'Recherche commandes')
@section('page-title', 'Rechercher des commandes')

@section('css')
<style>
.search-banner {
    background: linear-gradient(135deg, var(--accent, #2563eb), #7c3aed);
    border-radius: var(--radius-lg); padding: 1.25rem 1.5rem;
    color: white; position: relative; overflow: hidden; margin-bottom: 1.25rem;
}
.search-banner::before {
    content:''; position:absolute; top:-40%; right:-8%;
    width:250px; height:250px; background:rgba(255,255,255,.06);
    border-radius:50%;
}
.search-banner h2 { font-weight:800; font-size:1.2rem; margin:0; position:relative; z-index:1; }
.search-banner p { opacity:.8; font-size:.82rem; margin:.25rem 0 0; position:relative; z-index:1; }

.order-preview {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg, 12px);
    padding: 1rem 1.15rem;
    margin-bottom: .65rem;
    transition: all .15s;
}
.order-preview:hover { border-color: var(--accent); box-shadow: var(--shadow); }

.op-head {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: .5rem; flex-wrap: wrap; gap: .5rem;
}
.op-id { font-weight: 800; font-size: .95rem; color: var(--text); }
.op-admin {
    font-size: .72rem; font-weight: 600; padding: .2rem .6rem;
    border-radius: 6px; background: rgba(37,99,235,0.08); color: var(--accent);
}

.op-grid {
    display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: .4rem .75rem; font-size: .82rem;
}
.op-grid .lbl { font-size: .68rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600; letter-spacing: .3px; }
.op-grid .val { font-weight: 600; color: var(--text); }

.op-items {
    display: flex; flex-wrap: wrap; gap: .35rem; margin-top: .5rem;
}
.op-item-chip {
    font-size: .72rem; font-weight: 600; padding: .2rem .55rem;
    border-radius: 6px; background: var(--bg-hover); color: var(--text-secondary);
    border: 1px solid var(--border);
}
</style>
@endsection

@section('content')
<div class="search-banner">
    <h2><i class="fas fa-search me-2"></i>Commandes non assignées</h2>
    <p>Consultez les commandes en attente d'assignation (lecture seule)</p>
</div>

{{-- Filter Bar --}}
<div class="content-card mb-3">
    <div class="p-3">
        <form method="GET" action="{{ route('confirmi.employee.orders.search') }}" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label" style="font-size:.75rem;font-weight:600;">Recherche</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Nom, téléphone, n° commande, boutique..." value="{{ request('search') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label" style="font-size:.75rem;font-weight:600;">Client (Admin)</label>
                <select name="admin_id" class="form-select form-select-sm">
                    <option value="">Tous les clients</option>
                    @foreach($admins as $admin)
                        <option value="{{ $admin->id }}" {{ request('admin_id') == $admin->id ? 'selected' : '' }}>
                            {{ $admin->shop_name ?? $admin->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-royal flex-grow-1"><i class="fas fa-search me-1"></i>Rechercher</button>
                <a href="{{ route('confirmi.employee.orders.search') }}" class="btn btn-sm btn-outline-royal"><i class="fas fa-undo"></i></a>
            </div>
        </form>
    </div>
</div>

{{-- Results --}}
<div class="d-flex align-items-center justify-content-between mb-2">
    <small style="color:var(--text-secondary);font-weight:600;">{{ $assignments->total() }} commande(s) non assignée(s)</small>
</div>

@forelse($assignments as $a)
    @php $order = $a->order; @endphp
    <div class="order-preview">
        <div class="op-head">
            <div class="d-flex align-items-center gap-2">
                <span class="op-id">#{{ $order->id ?? 'N/A' }}</span>
                <span class="badge-status badge-pending">Non assignée</span>
            </div>
            <span class="op-admin"><i class="fas fa-store me-1"></i>{{ $a->admin->shop_name ?? $a->admin->name ?? 'N/A' }}</span>
        </div>
        <div class="op-grid">
            <div>
                <div class="lbl">Destinataire</div>
                <div class="val">{{ $order->customer_name ?? '-' }}</div>
            </div>
            <div>
                <div class="lbl">Téléphone</div>
                <div class="val">{{ $order->customer_phone ?? '-' }}</div>
            </div>
            <div>
                <div class="lbl">Gouvernorat</div>
                <div class="val">{{ $order->customer_governorate ?? '-' }}</div>
            </div>
            <div>
                <div class="lbl">Ville</div>
                <div class="val">{{ $order->customer_city ?? '-' }}</div>
            </div>
            <div>
                <div class="lbl">Montant</div>
                <div class="val" style="color:var(--accent);">{{ number_format($order->total_price ?? 0, 3) }} DT</div>
            </div>
            <div>
                <div class="lbl">Date</div>
                <div class="val">{{ $a->created_at->format('d/m/Y H:i') }}</div>
            </div>
        </div>
        @if($order && $order->items && $order->items->count() > 0)
        <div class="op-items">
            @foreach($order->items as $item)
                <span class="op-item-chip">
                    <i class="fas fa-box me-1" style="font-size:.6rem;"></i>{{ $item->product->name ?? 'Produit' }} × {{ $item->quantity }}
                </span>
            @endforeach
        </div>
        @endif
        @if($order->notes)
        <div style="margin-top:.4rem;font-size:.78rem;color:var(--text-secondary);">
            <i class="fas fa-sticky-note me-1" style="color:var(--warning);"></i>{{ Str::limit($order->notes, 100) }}
        </div>
        @endif
    </div>
@empty
    <div class="content-card">
        <div class="p-4 text-center" style="color:var(--text-secondary);">
            <i class="fas fa-inbox d-block mb-2" style="font-size:2rem;opacity:.4;"></i>
            Aucune commande non assignée trouvée.
        </div>
    </div>
@endforelse

@if($assignments->hasPages())
<div class="mt-3">{{ $assignments->links() }}</div>
@endif
@endsection
