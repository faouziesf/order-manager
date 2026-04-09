@extends('layouts.admin')
@section('title', 'Kolixy — Imprimer BL')

@section('css')
@include('admin.kolixy._styles')
@endsection

@section('content')
<div class="kolixy-page p-3">
    <div id="kolixy-toast" class="kolixy-toast"></div>

    {{-- Header --}}
    <div class="kolixy-card mb-3">
        <div class="kolixy-header">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h4><i class="fas fa-print me-2"></i>Imprimer Bon de Livraison</h4>
                    <p>Sélectionnez des colis pour imprimer les bons de livraison</p>
                </div>
                <a href="{{ route('admin.kolixy.dashboard') }}" class="kolixy-btn kolixy-btn-outline" style="background:rgba(255,255,255,0.15);color:white;border-color:rgba(255,255,255,0.3);">
                    <i class="fas fa-arrow-left"></i> Dashboard
                </a>
            </div>
        </div>
    </div>

    @if(!$connected)
    <div class="kolixy-card">
        <div class="kolixy-card-body kolixy-empty">
            <i class="fas fa-plug"></i>
            <h6>Compte non connecté</h6>
            <a href="{{ route('admin.kolixy.configuration') }}" class="kolixy-btn kolixy-btn-primary"><i class="fas fa-cog"></i> Configuration</a>
        </div>
    </div>
    @else

    {{-- Action bar --}}
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <div>
            <span class="kolixy-badge kolixy-badge-purple"><i class="fas fa-box me-1"></i>{{ $sentOrders->total() }} colis envoyés</span>
            <span class="kolixy-badge kolixy-badge-blue ms-1" id="selected-count">0 sélectionné(s)</span>
        </div>
        <button class="kolixy-btn kolixy-btn-primary" onclick="printSelectedLabels()" id="btn-download" disabled>
            <i class="fas fa-print"></i> Imprimer BL (<span id="dl-count">0</span>)
        </button>
    </div>

    <div class="kolixy-card">
        <div class="kolixy-card-body p-0">
            @if($sentOrders->isEmpty())
            <div class="kolixy-empty py-5">
                <i class="fas fa-inbox"></i>
                <h6>Aucun colis envoyé</h6>
                <p class="text-muted">Envoyez des commandes vers Kolixy d'abord.</p>
                <a href="{{ route('admin.kolixy.envoyer-commande') }}" class="kolixy-btn kolixy-btn-primary kolixy-btn-sm">
                    <i class="fas fa-paper-plane"></i> Envoyer commandes
                </a>
            </div>
            @else
            <div class="table-responsive">
                <table class="kolixy-table">
                    <thead>
                        <tr>
                            <th style="width:40px;">
                                <input type="checkbox" id="check-all" onchange="toggleAll(this)">
                            </th>
                            <th>#</th>
                            <th>Client</th>
                            <th>N° de suivi</th>
                            <th>Gouvernorat</th>
                            <th>Montant</th>
                            <th>Expédié le</th>
                            <th style="width:80px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sentOrders as $order)
                        <tr>
                            <td>
                                <input type="checkbox" class="order-check" value="{{ $order->id }}" onchange="updateCount()">
                            </td>
                            <td>{{ $order->id }}</td>
                            <td>
                                <span class="fw-semibold">{{ $order->customer_name }}</span>
                                <br><small class="text-muted">{{ $order->customer_phone }}</small>
                            </td>
                            <td>
                                <span style="font-family:monospace;color:var(--kolixy-primary);font-weight:600;cursor:pointer;" onclick="copyTracking('{{ $order->tracking_number }}', this)" title="Cliquer pour copier">
                                    {{ $order->tracking_number }}
                                </span>
                            </td>
                            <td>{{ $order->customer_governorate }}</td>
                            <td class="fw-bold">{{ number_format($order->total_price, 3) }} TND</td>
                            <td><small class="text-muted">{{ $order->shipped_at ? \Carbon\Carbon::parse($order->shipped_at)->format('d/m/Y H:i') : '-' }}</small></td>
                            <td>
                                <a href="{{ route('admin.kolixy.orders.print-bl', $order) }}" target="_blank" class="kolixy-btn kolixy-btn-sm kolixy-btn-primary" style="padding:4px 10px; font-size:12px;">
                                    <i class="fas fa-print"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-3">
                {{ $sentOrders->links() }}
            </div>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
const CSRF = '{{ csrf_token() }}';

function showToast(type, msg) {
    const t = document.getElementById('kolixy-toast');
    t.className = 'kolixy-toast ' + type;
    t.textContent = msg;
    t.style.display = 'block';
    setTimeout(() => t.style.display = 'none', 4000);
}

function toggleAll(master) {
    document.querySelectorAll('.order-check').forEach(c => c.checked = master.checked);
    updateCount();
}

function updateCount() {
    const checked = document.querySelectorAll('.order-check:checked');
    const n = checked.length;
    document.getElementById('selected-count').textContent = n + ' sélectionné(s)';
    document.getElementById('dl-count').textContent = n;
    document.getElementById('btn-download').disabled = n === 0;
}

function printSelectedLabels() {
    const orderIds = Array.from(document.querySelectorAll('.order-check:checked')).map(c => c.value);
    if (orderIds.length === 0) { showToast('error', 'Sélectionnez au moins un colis.'); return; }

    const url = '{{ route("admin.kolixy.print-bl-bulk") }}?ids=' + orderIds.join(',');
    window.open(url, '_blank');
}

function copyTracking(tracking, el) {
    navigator.clipboard.writeText(tracking).then(() => {
        const orig = el.textContent;
        el.textContent = '✓ Copié !';
        setTimeout(() => el.textContent = orig, 1500);
    });
}
</script>
@endsection
