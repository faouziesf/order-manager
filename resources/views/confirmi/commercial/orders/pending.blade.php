@extends('confirmi.layouts.app')
@section('title', 'Commandes en attente')
@section('page-title', 'Commandes en attente d\'assignation')

@section('content')
<div class="content-card">
    <div class="card-header-custom">
        <h6><i class="fas fa-clock me-2 text-warning"></i>En attente ({{ $assignments->total() }})</h6>
        <div class="d-flex gap-2">
            <select id="bulkEmployee" class="form-select form-select-sm" style="width:auto;font-size:0.8rem;">
                <option value="">-- Assigner à --</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                @endforeach
            </select>
            <button onclick="bulkAssign()" class="btn btn-sm btn-royal">
                <i class="fas fa-users-cog me-1"></i>Assigner la sélection
            </button>
        </div>
    </div>

    <form id="bulkForm" method="POST" action="{{ route('confirmi.commercial.orders.bulk-assign') }}">
        @csrf
        <input type="hidden" name="assigned_to" id="bulkAssignedTo">
        <div class="table-responsive">
            <table class="table table-modern">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="checkAll"></th>
                        <th>#</th>
                        <th>Admin</th>
                        <th>Destinataire</th>
                        <th>Téléphone</th>
                        <th>Ville</th>
                        <th>Montant</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignments as $a)
                    <tr>
                        <td><input type="checkbox" name="assignment_ids[]" value="{{ $a->id }}" class="row-check"></td>
                        <td><strong>{{ $a->order->id ?? '-' }}</strong></td>
                        <td>
                            <span class="fw-semibold">{{ $a->admin->shop_name ?? $a->admin->name ?? 'N/A' }}</span>
                        </td>
                        <td>{{ $a->order->customer_name ?? 'N/A' }}</td>
                        <td>
                            <a href="tel:{{ $a->order->customer_phone ?? '' }}" class="text-decoration-none">
                                {{ $a->order->customer_phone ?? 'N/A' }}
                            </a>
                        </td>
                        <td><small>{{ $a->order->customer_city ?? '' }}</small></td>
                        <td><strong>{{ number_format($a->order->total_price ?? 0, 3) }} DT</strong></td>
                        <td><small>{{ $a->created_at->format('d/m H:i') }}</small></td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('confirmi.commercial.orders.show', $a) }}" class="btn btn-sm btn-outline-royal" title="Détails">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">Aucune commande en attente.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </form>
    <div class="p-3">{{ $assignments->links() }}</div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('checkAll').addEventListener('change', function() {
    document.querySelectorAll('.row-check').forEach(c => c.checked = this.checked);
});

function bulkAssign() {
    const emp = document.getElementById('bulkEmployee').value;
    if (!emp) { alert('Sélectionnez un employé.'); return; }
    const checked = document.querySelectorAll('.row-check:checked');
    if (checked.length === 0) { alert('Sélectionnez au moins une commande.'); return; }
    document.getElementById('bulkAssignedTo').value = emp;
    document.getElementById('bulkForm').submit();
}
</script>
@endsection
