@extends('layouts.admin')

@section('title', 'Facturation Confirmi')

@section('css')
@include('admin.partials._shared-styles')
<style>
    /* ===== Page Header ===== */
    .cfm-page-header {
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 12px; margin-bottom: 28px;
    }
    .cfm-page-title {
        font-size: 1.5rem; font-weight: 800; color: var(--text); margin: 0;
        display: flex; align-items: center; gap: 10px;
    }
    .cfm-page-title i { color: var(--warning); font-size: 1.2rem; }
    .cfm-page-subtitle { color: var(--text-secondary); margin: 4px 0 0; font-size: 14px; }

    /* ===== Totals Row ===== */
    .cfm-totals {
        display: grid; grid-template-columns: repeat(3, 1fr);
        gap: 16px; margin-bottom: 24px;
    }
    .cfm-total {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg, 16px);
        padding: 28px 24px;
        text-align: center;
        position: relative;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .cfm-total:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }
    .cfm-total::after {
        content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
    }
    .cfm-total.total-all::after { background: linear-gradient(90deg, var(--primary), var(--info)); }
    .cfm-total.total-paid::after { background: linear-gradient(90deg, var(--success), #6ee7b7); }
    .cfm-total.total-unpaid::after { background: linear-gradient(90deg, var(--danger), #fca5a5); }
    .cfm-total-label {
        font-size: 12px; font-weight: 600; color: var(--text-muted);
        text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;
    }
    .cfm-total-value { font-size: 1.6rem; font-weight: 800; }
    .cfm-total-value.val-text { color: var(--text); }
    .cfm-total-value.val-success { color: var(--success); }
    .cfm-total-value.val-danger { color: var(--danger); }
    @media (max-width: 768px) { .cfm-totals { grid-template-columns: 1fr; } }

    /* ===== Filter Card ===== */
    .cfm-filter-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius, 12px);
        margin-bottom: 24px;
        overflow: hidden;
    }
    .cfm-filter-body { padding: 20px 24px; }

    /* ===== Table Card ===== */
    .cfm-table-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg, 16px);
        overflow: hidden;
    }
    .cfm-table-footer {
        padding: 16px 24px;
        border-top: 1px solid var(--border);
    }
</style>
@endsection

@section('content')
<div class="cfm-page-header">
    <div>
        <h1 class="cfm-page-title">
            <i class="fas fa-file-invoice-dollar"></i>Facturation Confirmi
        </h1>
        <p class="cfm-page-subtitle">Historique de vos frais de confirmation</p>
    </div>
    <a href="{{ route('admin.confirmi.index') }}" class="om-btn om-btn-ghost om-btn-sm">
        <i class="fas fa-arrow-left"></i> Retour
    </a>
</div>

{{-- Totals --}}
<div class="cfm-totals">
    <div class="cfm-total total-all">
        <div class="cfm-total-label">Total cumule</div>
        <div class="cfm-total-value val-text">{{ number_format($totals['all'], 3) }} DT</div>
    </div>
    <div class="cfm-total total-paid">
        <div class="cfm-total-label">Paye</div>
        <div class="cfm-total-value val-success">{{ number_format($totals['paid'], 3) }} DT</div>
    </div>
    <div class="cfm-total total-unpaid">
        <div class="cfm-total-label">Impaye</div>
        <div class="cfm-total-value val-danger">{{ number_format($totals['unpaid'], 3) }} DT</div>
    </div>
</div>

{{-- Filters --}}
<div class="cfm-filter-card">
    <form method="GET" action="{{ route('admin.confirmi.billing') }}">
        <div class="cfm-filter-body">
            <div class="row align-items-end" style="gap:0;">
                <div class="col-md-3 mb-3">
                    <div class="om-form-group">
                        <label class="om-form-label">Type</label>
                        <select name="type" class="om-form-input">
                            <option value="">Tous</option>
                            <option value="confirmed" {{ request('type') == 'confirmed' ? 'selected' : '' }}>Confirmations</option>
                            <option value="delivered" {{ request('type') == 'delivered' ? 'selected' : '' }}>Livraisons</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="om-form-group">
                        <label class="om-form-label">Statut paiement</label>
                        <select name="paid" class="om-form-input">
                            <option value="">Tous</option>
                            <option value="0" {{ request('paid') === '0' ? 'selected' : '' }}>Impaye</option>
                            <option value="1" {{ request('paid') === '1' ? 'selected' : '' }}>Paye</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="om-form-group">
                        <label class="om-form-label">Mois</label>
                        <select name="month" class="om-form-input">
                            <option value="">Tous les mois</option>
                            @php $months = ['','Janvier','Fevrier','Mars','Avril','Mai','Juin','Juillet','Aout','Septembre','Octobre','Novembre','Decembre']; @endphp
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>{{ $months[$m] }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
                <div class="col-md-3 mb-3 d-flex gap-2">
                    <button type="submit" class="om-btn om-btn-primary om-btn-sm" style="flex:1;">
                        <i class="fas fa-filter"></i> Filtrer
                    </button>
                    <a href="{{ route('admin.confirmi.billing') }}" class="om-btn om-btn-ghost om-btn-sm">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- Table --}}
<div class="cfm-table-card">
    <div class="table-responsive">
        <table class="om-table">
            <thead>
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
                    <td style="color:var(--text-muted); font-size:13px;">{{ $bill->id }}</td>
                    <td>
                        <strong style="color:var(--primary);">#{{ $bill->order_id }}</strong>
                        @if($bill->order)
                            <br><small style="color:var(--text-muted);">{{ $bill->order->customer_name }}</small>
                        @endif
                    </td>
                    <td>
                        @if($bill->billing_type === 'confirmed')
                            <span class="om-badge-success">Confirmation</span>
                        @else
                            <span class="om-badge-primary">Livraison</span>
                        @endif
                    </td>
                    <td><strong>{{ number_format($bill->amount, 3) }} DT</strong></td>
                    <td>
                        @if($bill->is_paid)
                            <span class="om-badge-success">Paye</span>
                        @else
                            <span class="om-badge-warning">Impaye</span>
                        @endif
                    </td>
                    <td style="color:var(--text-muted); font-size:13px;">{{ $bill->billed_at->format('d/m/Y H:i') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="om-empty">
                            <div class="om-empty-icon"><i class="fas fa-file-invoice"></i></div>
                            <h3>Aucune facturation</h3>
                            <p>Aucune facturation pour les criteres selectionnes.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($bills, 'links'))
    <div class="cfm-table-footer">
        {{ $bills->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
