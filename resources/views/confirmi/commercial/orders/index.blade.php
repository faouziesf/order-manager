@extends('confirmi.layouts.app')
@section('title', 'Toutes les commandes')
@section('page-title', 'Gestion des commandes Confirmi')

@section('content')
<div class="content-card mb-3">
    <div class="card-header-custom">
        <h6><i class="fas fa-filter me-2"></i>Filtres</h6>
    </div>
    <div class="p-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label" style="font-size:0.78rem;font-weight:600;">Statut</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                    <option value="assigned" {{ request('status') == 'assigned' ? 'selected' : '' }}>Assignée</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>En cours</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmée</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Annulée</option>
                    <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Livrée</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" style="font-size:0.78rem;font-weight:600;">Client (Admin)</label>
                <select name="admin_id" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    @foreach($admins as $admin)
                        <option value="{{ $admin->id }}" {{ request('admin_id') == $admin->id ? 'selected' : '' }}>{{ $admin->shop_name ?? $admin->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" style="font-size:0.78rem;font-weight:600;">Employé</label>
                <select name="assigned_to" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ request('assigned_to') == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-royal"><i class="fas fa-search me-1"></i>Filtrer</button>
                <a href="{{ route('confirmi.commercial.orders.index') }}" class="btn btn-sm btn-outline-royal">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- BULK ACTIONS BAR --}}
<div class="content-card mb-3" id="bulkBar" style="display:none;">
    <div class="p-3 d-flex align-items-center gap-3 flex-wrap" style="background:rgba(37,99,235,0.05);">
        <strong id="selectedCount" style="font-size:0.85rem;">0 sélectionnée(s)</strong>
        <small class="text-muted">(uniquement les commandes sans tentative)</small>
        <div class="ms-auto d-flex gap-2">
            <select id="reassignEmployee" class="form-select form-select-sm" style="width:auto;font-size:0.8rem;">
                <option value="">-- Réassigner à --</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                @endforeach
            </select>
            <button onclick="bulkReassign()" class="btn btn-sm btn-royal"><i class="fas fa-exchange-alt me-1"></i>Réassigner</button>
            <button onclick="bulkReject()" class="btn btn-sm btn-danger"><i class="fas fa-ban me-1"></i>Rejeter</button>
        </div>
    </div>
</div>

<div class="content-card">
    <div class="card-header-custom">
        <h6>Commandes ({{ $assignments->total() }})</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-modern">
            <thead>
                <tr>
                    <th><input type="checkbox" id="checkAll"></th>
                    <th>#</th>
                    <th>Admin</th>
                    <th>Destinataire</th>
                    <th>Téléphone</th>
                    <th>Assigné à</th>
                    <th>Tentatives</th>
                    <th>Statut</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assignments as $a)
                <tr>
                    <td>
                        @if($a->attempts === 0 && in_array($a->status, ['pending', 'assigned', 'in_progress']))
                            <input type="checkbox" class="row-check" value="{{ $a->id }}">
                        @endif
                    </td>
                    <td><strong>{{ $a->order->id ?? '-' }}</strong></td>
                    <td>{{ $a->admin->shop_name ?? $a->admin->name ?? 'N/A' }}</td>
                    <td>{{ $a->order->customer_name ?? 'N/A' }}</td>
                    <td>{{ $a->order->customer_phone ?? 'N/A' }}</td>
                    <td>{{ $a->assignee->name ?? '-' }}</td>
                    <td><span class="badge bg-secondary">{{ $a->attempts }}</span></td>
                    <td>
                        <span class="badge-status badge-{{ $a->status }}">
                            {{ match($a->status) {
                                'pending' => 'En attente',
                                'assigned' => 'Assignée',
                                'in_progress' => 'En cours',
                                'confirmed' => 'Confirmée',
                                'cancelled' => 'Annulée',
                                'delivered' => 'Livrée',
                                default => $a->status
                            } }}
                        </span>
                    </td>
                    <td><small>{{ $a->created_at->format('d/m H:i') }}</small></td>
                    <td>
                        <a href="{{ route('confirmi.commercial.orders.show', $a) }}" class="btn btn-sm btn-outline-royal"><i class="fas fa-eye"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="10" class="text-center py-4 text-muted">Aucune commande trouvée.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $assignments->withQueryString()->links() }}</div>
</div>

{{-- Hidden forms for bulk actions --}}
<form id="bulkRejectForm" method="POST" action="{{ route('confirmi.commercial.orders.bulk-reject') }}" style="display:none;">
    @csrf
    <div id="rejectIds"></div>
</form>
<form id="bulkReassignForm" method="POST" action="{{ route('confirmi.commercial.orders.bulk-reassign') }}" style="display:none;">
    @csrf
    <input type="hidden" name="assigned_to" id="reassignTo">
    <div id="reassignIds"></div>
</form>
@endsection

@section('scripts')
<script>
document.getElementById('checkAll').addEventListener('change', function() {
    document.querySelectorAll('.row-check').forEach(c => c.checked = this.checked);
    updateBulkBar();
});
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('row-check')) updateBulkBar();
});

function getCheckedIds() {
    return Array.from(document.querySelectorAll('.row-check:checked')).map(c => c.value);
}

function updateBulkBar() {
    const ids = getCheckedIds();
    document.getElementById('bulkBar').style.display = ids.length > 0 ? 'block' : 'none';
    document.getElementById('selectedCount').textContent = ids.length + ' sélectionnée(s)';
}

function bulkReject() {
    const ids = getCheckedIds();
    if (ids.length === 0) return;
    if (!confirm('Rejeter ' + ids.length + ' commande(s) sans tentative ?')) return;
    const container = document.getElementById('rejectIds');
    container.innerHTML = '';
    ids.forEach(id => {
        container.innerHTML += '<input type="hidden" name="assignment_ids[]" value="' + id + '">';
    });
    document.getElementById('bulkRejectForm').submit();
}

function bulkReassign() {
    const ids = getCheckedIds();
    const emp = document.getElementById('reassignEmployee').value;
    if (ids.length === 0) { alert('Sélectionnez au moins une commande.'); return; }
    if (!emp) { alert('Sélectionnez un employé.'); return; }
    document.getElementById('reassignTo').value = emp;
    const container = document.getElementById('reassignIds');
    container.innerHTML = '';
    ids.forEach(id => {
        container.innerHTML += '<input type="hidden" name="assignment_ids[]" value="' + id + '">';
    });
    document.getElementById('bulkReassignForm').submit();
}
</script>
@endsection
