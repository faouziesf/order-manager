@extends('layouts.admin')
@section('title', 'Kolixy — Vérification')

@section('css')
@include('admin.kolixy._styles')
<style>
    .package-detail-modal {
        position: fixed; inset: 0; z-index: 9998; background: rgba(0,0,0,0.5);
        display: none; align-items: center; justify-content: center; padding: 1rem;
    }
    .package-detail-modal.show { display: flex; }
    .package-detail-content {
        background: var(--bg-card, white); border-radius: var(--kolixy-radius); max-width: 600px; width: 100%;
        max-height: 85vh; overflow-y: auto; box-shadow: 0 8px 30px rgba(0,0,0,0.2);
    }
    .package-detail-header {
        padding: 1rem 1.25rem; border-bottom: 1px solid var(--kolixy-border);
        display: flex; align-items: center;
    }
    .package-detail-body { padding: 1.25rem; }
    .detail-row { display: flex; justify-content: space-between; padding: 0.4rem 0; border-bottom: 1px solid #f3f4f6; }
    .detail-label { font-size: 0.82rem; color: #6b7280; }
    .detail-value { font-size: 0.85rem; font-weight: 600; color: var(--kolixy-dark); }
</style>
@endsection

@section('content')
<div class="kolixy-page p-3">
    <div id="kolixy-toast" class="kolixy-toast"></div>

    {{-- Header --}}
    <div class="kolixy-card mb-3">
        <div class="kolixy-header">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h4><i class="fas fa-clipboard-check me-2"></i>Vérification</h4>
                    <p>Filtrez et importez des colis Kolixy comme commandes locales</p>
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
            <p class="text-muted">Connectez votre compte Kolixy dans la Configuration.</p>
            <a href="{{ route('admin.kolixy.configuration') }}" class="kolixy-btn kolixy-btn-primary"><i class="fas fa-cog"></i> Configuration</a>
        </div>
    </div>
    @else

    {{-- Filtres statuts --}}
    <div class="kolixy-card mb-3">
        <div class="kolixy-card-body">
            <form method="GET" action="{{ route('admin.kolixy.verification') }}" class="d-flex flex-wrap align-items-center gap-2">
                <span style="font-size:.85rem;font-weight:600;color:var(--kolixy-dark)">Filtrer par statut :</span>
                @foreach($availableStatuses as $key => $label)
                    <label class="d-flex align-items-center gap-1" style="cursor:pointer;font-size:.82rem">
                        <input type="checkbox" name="statuses[]" value="{{ $key }}" {{ in_array($key, $selectedStatuses) ? 'checked' : '' }}>
                        {{ $label }}
                    </label>
                @endforeach
                <button type="submit" class="kolixy-btn kolixy-btn-primary kolixy-btn-sm"><i class="fas fa-filter"></i> Filtrer</button>
            </form>
        </div>
    </div>

    {{-- Summary badges --}}
    @php
        $countByStatus = collect($packages)->groupBy('status')->map->count();
    @endphp
    <div class="d-flex flex-wrap gap-2 mb-3">
        <span class="kolixy-badge kolixy-badge-yellow"><i class="fas fa-clock me-1"></i>En attente retour: {{ $countByStatus->get('AWAITING_RETURN', 0) }}</span>
        <span class="kolixy-badge kolixy-badge-red"><i class="fas fa-times-circle me-1"></i>Refusés: {{ $countByStatus->get('REFUSED', 0) }}</span>
        <span class="kolixy-badge kolixy-badge-gray"><i class="fas fa-question-circle me-1"></i>Indisponibles: {{ $countByStatus->get('UNAVAILABLE', 0) }}</span>
        <span class="kolixy-badge kolixy-badge-purple"><i class="fas fa-box me-1"></i>Total: {{ count($packages) }}</span>
    </div>

    <div class="kolixy-card">
        <div class="kolixy-card-body p-0">
            @if(count($packages) > 0)
            <div class="p-3 border-bottom d-flex gap-2 align-items-center justify-content-between">
                <div class="d-flex gap-2">
                    <button class="kolixy-btn kolixy-btn-outline kolixy-btn-sm" onclick="selectAll(true)"><i class="fas fa-check-square"></i> Tout sélectionner</button>
                    <button class="kolixy-btn kolixy-btn-outline kolixy-btn-sm" onclick="selectAll(false)"><i class="far fa-square"></i> Tout désélectionner</button>
                </div>
                <button class="kolixy-btn kolixy-btn-primary kolixy-btn-sm" onclick="importSelected()">
                    <i class="fas fa-file-import"></i> Importer la sélection
                </button>
            </div>
            @endif

            @if(count($packages) === 0)
            <div class="kolixy-empty py-5">
                <i class="fas fa-check-double"></i>
                <h6>Aucun colis à vérifier</h6>
                <p class="text-muted">Modifiez les filtres pour afficher d'autres statuts.</p>
            </div>
            @else
            <div class="table-responsive">
                <table class="kolixy-table" id="pkg-table">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="chk-all" onchange="selectAll(this.checked)"></th>
                            <th>Code tracking</th>
                            <th>Destinataire</th>
                            <th>Gouvernorat</th>
                            <th>Montant</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($packages as $i => $pkg)
                        @php
                            $trackingCode = $pkg['package_code'] ?? $pkg['tracking_number'] ?? $pkg['barcode'] ?? '';
                        @endphp
                        <tr data-pkg="{{ htmlspecialchars(json_encode($pkg), ENT_QUOTES, 'UTF-8') }}">
                            <td><input type="checkbox" class="pkg-chk" data-tracking="{{ $trackingCode }}"></td>
                            <td>
                                <span class="fw-bold" style="font-family:monospace;color:var(--kolixy-primary);">
                                    {{ $trackingCode ?: '-' }}
                                </span>
                            </td>
                            <td>
                                {{ $pkg['recipient_data']['name'] ?? $pkg['recipient_name'] ?? '-' }}
                                <br>
                                <small class="text-muted">{{ $pkg['recipient_data']['phone'] ?? $pkg['recipient_phone'] ?? '' }}</small>
                            </td>
                            <td>{{ $pkg['recipient_data']['gouvernorat'] ?? $pkg['gouvernorat'] ?? '-' }}</td>
                            <td class="fw-bold">{{ number_format($pkg['cod_amount'] ?? 0, 3) }} TND</td>
                            <td>
                                @php
                                    $st = $pkg['status'] ?? 'UNKNOWN';
                                    $stClass = match($st) {
                                        'AWAITING_RETURN' => 'kolixy-badge-yellow',
                                        'REFUSED' => 'kolixy-badge-red',
                                        'UNAVAILABLE' => 'kolixy-badge-gray',
                                        'DELIVERED' => 'kolixy-badge-green',
                                        default => 'kolixy-badge-blue',
                                    };
                                @endphp
                                <span class="kolixy-badge {{ $stClass }}">{{ str_replace('_', ' ', $st) }}</span>
                            </td>
                            <td>
                                <button class="kolixy-btn kolixy-btn-outline kolixy-btn-sm" onclick="viewDetails('{{ $trackingCode }}')">
                                    <i class="fas fa-eye"></i> Détails
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
    @endif
</div>

{{-- Package detail modal --}}
<div class="package-detail-modal" id="detail-modal" onclick="if(event.target===this) closeDetailModal()">
    <div class="package-detail-content">
        <div class="package-detail-header">
            <h6 class="fw-bold mb-0"><i class="fas fa-box me-2" style="color:var(--kolixy-primary);"></i>Détails du colis</h6>
            <button onclick="closeDetailModal()" style="background:none;border:none;font-size:1.2rem;color:#9ca3af;cursor:pointer;margin-left:auto;">&times;</button>
        </div>
        <div class="package-detail-body" id="detail-body">
            <div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x" style="color:var(--kolixy-primary);"></i></div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const CSRF = '{{ csrf_token() }}';
const IMPORT_URL = '{{ route('admin.kolixy.verification.import') }}';

function showToast(type, msg) {
    const t = document.getElementById('kolixy-toast');
    t.className = 'kolixy-toast ' + type;
    t.textContent = msg;
    t.style.display = 'block';
    setTimeout(() => t.style.display = 'none', 5000);
}

function selectAll(checked) {
    document.querySelectorAll('.pkg-chk').forEach(c => c.checked = checked);
    const chkAll = document.getElementById('chk-all');
    if (chkAll) chkAll.checked = checked;
}

function importSelected() {
    const rows  = document.querySelectorAll('tr[data-pkg]');
    const pkgs  = [];

    rows.forEach(row => {
        const chk = row.querySelector('.pkg-chk');
        if (chk && chk.checked) {
            try {
                pkgs.push(JSON.parse(row.dataset.pkg));
            } catch (e) {}
        }
    });

    if (pkgs.length === 0) {
        showToast('error', 'Sélectionnez au moins un colis à importer.');
        return;
    }

    if (!confirm(`Importer ${pkgs.length} colis comme commandes locales ?`)) return;

    fetch(IMPORT_URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': CSRF,
        },
        body: JSON.stringify({ packages: pkgs }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('success', data.message);
        } else {
            showToast('error', data.message || 'Erreur lors de l\'import.');
        }
    })
    .catch(() => showToast('error', 'Erreur réseau lors de l\'import.'));
}

function viewDetails(tracking) {
    const modal = document.getElementById('detail-modal');
    const body  = document.getElementById('detail-body');
    modal.classList.add('show');
    body.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x" style="color:var(--kolixy-primary);"></i></div>';

    fetch(`{{ url('admin/kolixy/packages') }}/${tracking}/details`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) { body.innerHTML = '<p class="text-danger text-center">Erreur: ' + (data.message || 'Impossible de charger') + '</p>'; return; }
        const p = data.data || {};
        const rd = p.recipient_data || {};
        body.innerHTML = `
            <div class="detail-row"><span class="detail-label">Code tracking</span><span class="detail-value" style="font-family:monospace;">${p.package_code || p.tracking_number || '-'}</span></div>
            <div class="detail-row"><span class="detail-label">Statut</span><span class="detail-value">${(p.status || '-').replace(/_/g, ' ')}</span></div>
            <div class="detail-row"><span class="detail-label">Destinataire</span><span class="detail-value">${rd.name || p.recipient_name || '-'}</span></div>
            <div class="detail-row"><span class="detail-label">Téléphone</span><span class="detail-value">${rd.phone || p.recipient_phone || '-'}</span></div>
            <div class="detail-row"><span class="detail-label">Gouvernorat</span><span class="detail-value">${rd.gouvernorat || p.gouvernorat || '-'}</span></div>
            <div class="detail-row"><span class="detail-label">Délégation</span><span class="detail-value">${rd.delegation || p.delegation || '-'}</span></div>
            <div class="detail-row"><span class="detail-label">Adresse</span><span class="detail-value">${rd.address || p.address || '-'}</span></div>
            <div class="detail-row"><span class="detail-label">Montant COD</span><span class="detail-value">${parseFloat(p.cod_amount || 0).toFixed(3)} TND</span></div>
            <div class="detail-row"><span class="detail-label">Description</span><span class="detail-value">${p.content_description || '-'}</span></div>
            <div class="detail-row"><span class="detail-label">Notes</span><span class="detail-value">${p.notes || '-'}</span></div>
            ${p.status_history && p.status_history.length ? `
                <hr><h6 class="fw-bold mb-2" style="font-size:0.85rem;"><i class="fas fa-history me-1"></i>Historique</h6>
                ${p.status_history.map(h => `<div class="d-flex justify-content-between" style="font-size:0.8rem;padding:0.25rem 0;border-bottom:1px solid #f3f4f6;">
                    <span>${(h.new_status || '').replace(/_/g,' ')}</span>
                    <small class="text-muted">${h.created_at || ''}</small>
                </div>`).join('')}
            ` : ''}
        `;
    })
    .catch(() => {
        body.innerHTML = '<p class="text-danger text-center">Erreur de connexion.</p>';
    });
}

function closeDetailModal() {
    document.getElementById('detail-modal').classList.remove('show');
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDetailModal(); });
</script>
@endsection
