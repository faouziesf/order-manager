@extends('layouts.super-admin')

@section('title', 'Facturation Confirmi')

@section('breadcrumb')
    <ol class="breadcrumb breadcrumb-custom">
        <li class="breadcrumb-item"><a href="{{ route('super-admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Facturation Confirmi</li>
    </ol>
@endsection

@section('page-header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">Facturation Confirmi</h1>
            <p class="page-subtitle">Suivi des frais de confirmation pour tous les admins</p>
        </div>
    </div>
@endsection

@section('content')

{{-- Totaux --}}
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card">
            <div class="card-body text-center">
                <small class="text-muted d-block">Total cumulé</small>
                <div class="fs-4 fw-bold text-dark">{{ number_format($totals['all'], 3) }} DT</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card">
            <div class="card-body text-center">
                <small class="text-muted d-block">Ce mois</small>
                <div class="fs-4 fw-bold text-primary">{{ number_format($totals['this_month'], 3) }} DT</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card">
            <div class="card-body text-center">
                <small class="text-muted d-block">Payé</small>
                <div class="fs-4 fw-bold text-success">{{ number_format($totals['paid'], 3) }} DT</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card">
            <div class="card-body text-center">
                <small class="text-muted d-block">Impayé</small>
                <div class="fs-4 fw-bold text-danger">{{ number_format($totals['unpaid'], 3) }} DT</div>
            </div>
        </div>
    </div>
</div>

{{-- Mark all paid for admin shortcuts --}}
@if($admins->count())
<div class="card mb-4">
    <div class="card-header"><h6 class="mb-0 fw-bold"><i class="fas fa-money-check me-2"></i>Tout marquer payé par admin</h6></div>
    <div class="card-body">
        <div class="d-flex flex-wrap gap-2">
            @foreach($admins as $adm)
                @php $adUnpaid = \App\Models\ConfirmiBilling::where('admin_id', $adm->id)->where('is_paid', false)->sum('amount'); @endphp
                @if($adUnpaid > 0)
                <form method="POST" action="{{ route('super-admin.confirmi-billing.mark-paid-admin', $adm) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-success"
                        onclick="return confirm('Marquer toutes les factures impayées de {{ $adm->name }} ({{ number_format($adUnpaid,3) }} DT) comme payées ?')">
                        {{ $adm->name }} — {{ number_format($adUnpaid, 3) }} DT
                    </button>
                </form>
                @endif
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- Filters --}}
<form method="GET" action="{{ route('super-admin.confirmi-billing.index') }}" class="card mb-4">
    <div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold">Admin</label>
                <select name="admin_id" class="form-select form-select-sm">
                    <option value="">Tous les admins</option>
                    @foreach($admins as $adm)
                        <option value="{{ $adm->id }}" {{ request('admin_id') == $adm->id ? 'selected' : '' }}>
                            {{ $adm->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Type</label>
                <select name="type" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    <option value="confirmed" {{ request('type') == 'confirmed' ? 'selected' : '' }}>Confirmation</option>
                    <option value="delivered" {{ request('type') == 'delivered' ? 'selected' : '' }}>Livraison</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Paiement</label>
                <select name="paid" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    <option value="0" {{ request('paid') === '0' ? 'selected' : '' }}>Impayé</option>
                    <option value="1" {{ request('paid') === '1' ? 'selected' : '' }}>Payé</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-fill"><i class="fas fa-filter me-1"></i>Filtrer</button>
                <a href="{{ route('super-admin.confirmi-billing.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-times"></i></a>
            </div>
        </div>
    </div>
</form>

{{-- Table with bulk mark-paid --}}
<form method="POST" action="{{ route('super-admin.confirmi-billing.mark-paid') }}" id="billsForm">
    @csrf
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <input type="checkbox" id="selectAll" class="form-check-input mt-0">
                <label for="selectAll" class="small fw-bold mb-0">Tout sélectionner</label>
            </div>
            <button type="submit" class="btn btn-sm btn-success" id="markPaidBtn" disabled>
                <i class="fas fa-check me-1"></i>Marquer payé (<span id="selectedCount">0</span>)
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:40px;"></th>
                        <th>#</th>
                        <th>Admin</th>
                        <th>Commande</th>
                        <th>Type</th>
                        <th>Montant</th>
                        <th>Statut</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bills as $bill)
                    <tr>
                        <td>
                            @if(!$bill->is_paid)
                                <input type="checkbox" name="billing_ids[]" value="{{ $bill->id }}"
                                       class="form-check-input bill-check mt-0">
                            @endif
                        </td>
                        <td><small class="text-muted">{{ $bill->id }}</small></td>
                        <td>
                            <strong>{{ $bill->admin->name ?? 'N/A' }}</strong>
                            <br><small class="text-muted">{{ $bill->admin->shop_name ?? '' }}</small>
                        </td>
                        <td>
                            <strong>#{{ $bill->order_id }}</strong>
                            @if($bill->order)
                                <br><small class="text-muted">{{ Str::limit($bill->order->customer_name, 20) }}</small>
                            @endif
                        </td>
                        <td>
                            @if($bill->billing_type === 'confirmed')
                                <span class="badge bg-success">Confirmation</span>
                            @else
                                <span class="badge bg-primary">Livraison</span>
                            @endif
                        </td>
                        <td><strong>{{ number_format($bill->amount, 3) }} DT</strong></td>
                        <td>
                            @if($bill->is_paid)
                                <span class="badge bg-success">Payé</span>
                            @else
                                <span class="badge bg-warning text-dark">Impayé</span>
                            @endif
                        </td>
                        <td><small>{{ $bill->billed_at->format('d/m/Y H:i') }}</small></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">Aucune facturation.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $bills->withQueryString()->links() }}</div>
    </div>
</form>

@endsection

@section('scripts')
<script>
    const checks = document.querySelectorAll('.bill-check');
    const selectAll = document.getElementById('selectAll');
    const btn = document.getElementById('markPaidBtn');
    const counter = document.getElementById('selectedCount');

    function updateBtn() {
        const checked = document.querySelectorAll('.bill-check:checked').length;
        counter.textContent = checked;
        btn.disabled = checked === 0;
    }

    selectAll?.addEventListener('change', () => {
        checks.forEach(c => c.checked = selectAll.checked);
        updateBtn();
    });
    checks.forEach(c => c.addEventListener('change', updateBtn));
</script>
@endsection
