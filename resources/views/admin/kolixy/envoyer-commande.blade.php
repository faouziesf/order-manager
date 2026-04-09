@extends('layouts.admin')
@section('title', 'Kolixy — Envoyer Commande')

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
                    <h4><i class="fas fa-paper-plane me-2"></i>Envoyer Commande</h4>
                    <p>Envoyez vos commandes confirmées vers Kolixy pour expédition</p>
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

    {{-- Tabs --}}
    <div class="kolixy-nav">
        <a href="#" class="active" onclick="switchTab('ready', this); return false;">
            <i class="fas fa-hourglass-half me-1"></i>Prêtes à envoyer
            <span class="kolixy-badge kolixy-badge-yellow ms-1">{{ $readyOrders->count() }}</span>
        </a>
        <a href="#" onclick="switchTab('sent', this); return false;">
            <i class="fas fa-check me-1"></i>Envoyées
            <span class="kolixy-badge kolixy-badge-green ms-1">{{ $sentOrders->total() }}</span>
        </a>
    </div>

    {{-- Tab: Ready --}}
    <div id="tab-ready">
        @if($readyOrders->isEmpty())
        <div class="kolixy-card">
            <div class="kolixy-card-body kolixy-empty py-5">
                <i class="fas fa-inbox"></i>
                <h6>Aucune commande prête</h6>
                <p class="text-muted">Les commandes confirmées apparaîtront ici.</p>
            </div>
        </div>
        @else
        {{-- Bulk action bar --}}
        <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
            <div>
                <span class="kolixy-badge kolixy-badge-purple" id="ready-count">0 sélectionnée(s)</span>
            </div>
            <button class="kolixy-btn kolixy-btn-primary kolixy-btn-sm" onclick="sendSelected()" id="btn-send-bulk" disabled>
                <i class="fas fa-paper-plane"></i> Envoyer sélectionnées (<span id="send-count">0</span>)
            </button>
        </div>

        <div class="kolixy-card">
            <div class="kolixy-card-body p-0">
                <div class="table-responsive">
                    <table class="kolixy-table">
                        <thead>
                            <tr>
                                <th style="width:40px;">
                                    <input type="checkbox" id="check-all-ready" onchange="toggleAllReady(this)">
                                </th>
                                <th>#</th>
                                <th>Client</th>
                                <th>Gouvernorat</th>
                                <th>Montant</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($readyOrders as $order)
                            <tr id="ready-row-{{ $order->id }}">
                                <td>
                                    <input type="checkbox" class="ready-check" value="{{ $order->id }}" onchange="updateReadyCount()">
                                </td>
                                <td>{{ $order->id }}</td>
                                <td>
                                    <span class="fw-semibold">{{ $order->customer_name }}</span>
                                    <br><small class="text-muted">{{ $order->customer_phone }}</small>
                                </td>
                                <td>{{ $order->customer_governorate }}</td>
                                <td class="fw-bold">{{ number_format($order->total_price, 3) }} TND</td>
                                <td><small class="text-muted">{{ $order->created_at->format('d/m/Y') }}</small></td>
                                <td>
                                    <button class="kolixy-btn kolixy-btn-primary kolixy-btn-sm" onclick="sendOrder({{ $order->id }}, this)" id="btn-send-{{ $order->id }}">
                                        <i class="fas fa-paper-plane"></i> Envoyer
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Tab: Sent --}}
    <div id="tab-sent" style="display:none;">
        @if($sentOrders->isEmpty())
        <div class="kolixy-card">
            <div class="kolixy-card-body kolixy-empty py-5">
                <i class="fas fa-paper-plane"></i>
                <h6>Aucune commande envoyée</h6>
            </div>
        </div>
        @else
        <div class="kolixy-card">
            <div class="kolixy-card-body p-0">
                <div class="table-responsive">
                    <table class="kolixy-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Client</th>
                                <th>N° de suivi</th>
                                <th>Statut</th>
                                <th>Expédié le</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sentOrders as $order)
                            <tr>
                                <td>{{ $order->id }}</td>
                                <td>
                                    <span class="fw-semibold">{{ $order->customer_name }}</span>
                                    <br><small class="text-muted">{{ $order->customer_phone }}</small>
                                </td>
                                <td>
                                    <span style="font-family:monospace;color:var(--kolixy-primary);font-weight:600;cursor:pointer;" onclick="copyTracking('{{ $order->tracking_number }}', this)" title="Copier">
                                        {{ $order->tracking_number }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $stClass = match($order->status) {
                                            'livrée' => 'kolixy-badge-green',
                                            'en_retour', 'échec_livraison' => 'kolixy-badge-red',
                                            'en_transit', 'tentative_livraison' => 'kolixy-badge-blue',
                                            'expédiée' => 'kolixy-badge-purple',
                                            default => 'kolixy-badge-gray',
                                        };
                                    @endphp
                                    <span class="kolixy-badge {{ $stClass }}">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
                                </td>
                                <td><small class="text-muted">{{ $order->shipped_at ? \Carbon\Carbon::parse($order->shipped_at)->format('d/m/Y H:i') : '-' }}</small></td>
                                <td>
                                    <button class="kolixy-btn kolixy-btn-outline kolixy-btn-sm" onclick="syncStatus({{ $order->id }}, this)">
                                        <i class="fas fa-sync-alt"></i> Sync
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3">
                    {{ $sentOrders->links() }}
                </div>
            </div>
        </div>
        @endif
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

function switchTab(tab, el) {
    document.getElementById('tab-ready').style.display = tab === 'ready' ? '' : 'none';
    document.getElementById('tab-sent').style.display = tab === 'sent' ? '' : 'none';
    document.querySelectorAll('.kolixy-nav a').forEach(a => a.classList.remove('active'));
    el.classList.add('active');
}

function toggleAllReady(master) {
    document.querySelectorAll('.ready-check').forEach(c => c.checked = master.checked);
    updateReadyCount();
}

function updateReadyCount() {
    const n = document.querySelectorAll('.ready-check:checked').length;
    document.getElementById('ready-count').textContent = n + ' sélectionnée(s)';
    document.getElementById('send-count').textContent = n;
    document.getElementById('btn-send-bulk').disabled = n === 0;
}

function sendOrder(orderId, btn) {
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    fetch(`{{ url('admin/kolixy/orders') }}/${orderId}/send`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('success', 'Commande #' + orderId + ' envoyée ! Tracking: ' + (data.tracking_number || ''));
            const row = document.getElementById('ready-row-' + orderId);
            if (row) {
                row.style.opacity = '0.5';
                row.querySelector('.ready-check')?.remove();
                btn.innerHTML = '<i class="fas fa-check text-success"></i> Envoyé';
                btn.classList.remove('kolixy-btn-primary');
                btn.classList.add('kolixy-btn-outline');
            }
        } else {
            showToast('error', data.message || 'Erreur d\'envoi.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane"></i> Envoyer';
        }
    })
    .catch(() => {
        showToast('error', 'Erreur réseau.');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane"></i> Envoyer';
    });
}

function sendSelected() {
    const ids = Array.from(document.querySelectorAll('.ready-check:checked')).map(c => parseInt(c.value));
    if (ids.length === 0) { showToast('error', 'Sélectionnez des commandes.'); return; }

    const btn = document.getElementById('btn-send-bulk');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi en cours...';

    fetch('{{ route("admin.kolixy.orders.send-bulk") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify({ order_ids: ids })
    })
    .then(r => r.json())
    .then(data => {
        showToast(data.success ? 'success' : 'error', data.message || 'Terminé.');
        if (data.results) {
            data.results.details.forEach(d => {
                if (d.ok) {
                    const row = document.getElementById('ready-row-' + d.order_id);
                    if (row) { row.style.opacity = '0.4'; }
                }
            });
        }
        setTimeout(() => location.reload(), 2000);
    })
    .catch(() => showToast('error', 'Erreur réseau.'))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane"></i> Envoyer sélectionnées (<span id="send-count">0</span>)';
        updateReadyCount();
    });
}

function syncStatus(orderId, btn) {
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    fetch(`{{ url('admin/kolixy/orders') }}/${orderId}/sync-status`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('success', 'Statut synchronisé : ' + (data.order_status || ''));
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast('error', data.message || 'Erreur sync.');
        }
    })
    .catch(() => showToast('error', 'Erreur réseau.'))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-sync-alt"></i> Sync';
    });
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
