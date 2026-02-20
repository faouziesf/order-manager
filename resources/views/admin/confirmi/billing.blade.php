@extends('layouts.admin')

@section('title', 'Facturation Confirmi')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-file-invoice-dollar me-2 text-warning"></i>Facturation Confirmi</h4>
            <p class="text-muted mb-0">Historique de vos frais de confirmation</p>
        </div>
        <a href="{{ route('admin.confirmi.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Retour
        </a>
    </div>

    {{-- Totaux --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <small class="text-muted d-block">Total cumulé</small>
                    <div class="fs-4 fw-bold text-dark">{{ number_format($totals['all'], 3) }} DT</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <small class="text-muted d-block">Payé</small>
                    <div class="fs-4 fw-bold text-success">{{ number_format($totals['paid'], 3) }} DT</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <small class="text-muted d-block">Impayé</small>
                    <div class="fs-4 fw-bold text-danger">{{ number_format($totals['unpaid'], 3) }} DT</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtres --}}
    <form method="GET" action="{{ route('admin.confirmi.billing') }}" class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Type</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        <option value="confirmed" {{ request('type') == 'confirmed' ? 'selected' : '' }}>Confirmations</option>
                        <option value="delivered" {{ request('type') == 'delivered' ? 'selected' : '' }}>Livraisons</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Statut paiement</label>
                    <select name="paid" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        <option value="0" {{ request('paid') === '0' ? 'selected' : '' }}>Impayé</option>
                        <option value="1" {{ request('paid') === '1' ? 'selected' : '' }}>Payé</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Mois</label>
                    <select name="month" class="form-select form-select-sm">
                        <option value="">Tous les mois</option>
                        @php $months = ['','Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre']; @endphp
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                {{ $months[$m] }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill">
                        <i class="fas fa-filter me-1"></i>Filtrer
                    </button>
                    <a href="{{ route('admin.confirmi.billing') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </div>
        </div>
    </form>

    {{-- Table --}}
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
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
                        <td><small class="text-muted">{{ $bill->id }}</small></td>
                        <td>
                            <strong>#{{ $bill->order_id }}</strong>
                            @if($bill->order)
                                <br><small class="text-muted">{{ $bill->order->customer_name }}</small>
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
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="fas fa-file-invoice fa-2x mb-2 d-block opacity-25"></i>
                            Aucune facturation pour les critères sélectionnés.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $bills->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
