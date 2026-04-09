@extends('layouts.super-admin')

@section('title', 'Facturation Confirmi')
@section('page-title', 'Facturation Confirmi')

@section('content')
    <!-- Totals -->
    <div class="sa-grid sa-grid-4" style="margin-bottom:24px">
        <div class="sa-stat">
            <div class="sa-stat-icon sa-stat-icon-primary"><i class="fas fa-file-invoice-dollar"></i></div>
            <div><div class="sa-stat-value">{{ number_format($totals['all'], 2) }} DH</div><div class="sa-stat-label">Total Global</div></div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-icon sa-stat-icon-success"><i class="fas fa-check-circle"></i></div>
            <div><div class="sa-stat-value">{{ number_format($totals['paid'], 2) }} DH</div><div class="sa-stat-label">Total Payé</div></div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-icon sa-stat-icon-danger"><i class="fas fa-exclamation-circle"></i></div>
            <div><div class="sa-stat-value">{{ number_format($totals['unpaid'], 2) }} DH</div><div class="sa-stat-label">Total Impayé</div></div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-icon sa-stat-icon-info"><i class="fas fa-calendar"></i></div>
            <div><div class="sa-stat-value">{{ number_format($totals['this_month'], 2) }} DH</div><div class="sa-stat-label">Ce mois</div></div>
        </div>
    </div>

    <!-- Filters -->
    <div class="sa-card" style="margin-bottom:24px">
        <form method="GET" action="{{ route('super-admin.confirmi-billing.index') }}" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
            <select name="admin_id" class="sa-input sa-select" style="max-width:220px">
                <option value="">Tous les admins</option>
                @foreach($admins as $admin)
                    <option value="{{ $admin->id }}" {{ request('admin_id') == $admin->id ? 'selected' : '' }}>{{ $admin->name }}</option>
                @endforeach
            </select>
            <select name="type" class="sa-input sa-select" style="max-width:160px">
                <option value="">Tous les types</option>
                <option value="confirmed" {{ request('type') === 'confirmed' ? 'selected' : '' }}>Confirmé</option>
                <option value="delivered" {{ request('type') === 'delivered' ? 'selected' : '' }}>Livré</option>
            </select>
            <select name="paid" class="sa-input sa-select" style="max-width:140px">
                <option value="">Paiement</option>
                <option value="1" {{ request('paid') === '1' ? 'selected' : '' }}>Payé</option>
                <option value="0" {{ request('paid') === '0' ? 'selected' : '' }}>Impayé</option>
            </select>
            <button type="submit" class="sa-btn sa-btn-primary sa-btn-sm"><i class="fas fa-filter"></i> Filtrer</button>
            @if(request()->hasAny(['admin_id','type','paid']))
                <a href="{{ route('super-admin.confirmi-billing.index') }}" class="sa-btn sa-btn-outline sa-btn-sm"><i class="fas fa-times"></i></a>
            @endif
        </form>
    </div>

    <!-- Billing Table -->
    <form id="billForm" method="POST" action="{{ route('super-admin.confirmi-billing.mark-paid') }}">
        @csrf
        <div class="sa-card">
            <div class="sa-card-header">
                <h3 class="sa-card-title">{{ $bills->total() }} facture(s)</h3>
                <div style="display:flex;gap:8px">
                    <button type="submit" id="markPaidBtn" class="sa-btn sa-btn-success sa-btn-sm" style="display:none" onclick="return confirm('Marquer comme payées ?')"><i class="fas fa-check"></i> Marquer payées</button>
                </div>
            </div>
            <div class="sa-table-wrap">
                <table class="sa-table">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="checkAll" onchange="toggleAllBills(this)"></th>
                            <th>Admin</th>
                            <th>Type</th>
                            <th>Montant</th>
                            <th>Commande</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bills as $bill)
                            <tr>
                                <td>
                                    @if(!$bill->is_paid)
                                        <input type="checkbox" name="billing_ids[]" value="{{ $bill->id }}" class="bill-check" onchange="updateBillBtns()">
                                    @endif
                                </td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:8px">
                                        <div class="sa-avatar sa-avatar-primary" style="width:28px;height:28px;font-size:.65rem">{{ strtoupper(substr($bill->admin->name ?? '?', 0, 1)) }}</div>
                                        <span style="font-size:.8125rem;font-weight:500">{{ $bill->admin->name ?? '-' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="sa-badge sa-badge-{{ $bill->billing_type === 'confirmed' ? 'primary' : 'success' }}">
                                        {{ $bill->billing_type === 'confirmed' ? 'Confirmé' : 'Livré' }}
                                    </span>
                                </td>
                                <td style="font-weight:700;font-size:.875rem">{{ number_format($bill->amount, 2) }} DH</td>
                                <td style="font-size:.8rem;color:var(--sa-text-secondary)">#{{ $bill->order_id ?? '-' }}</td>
                                <td>
                                    <span class="sa-badge sa-badge-{{ $bill->is_paid ? 'success' : 'danger' }}">
                                        {{ $bill->is_paid ? 'Payé' : 'Impayé' }}
                                    </span>
                                </td>
                                <td style="font-size:.75rem;color:var(--sa-text-muted)">{{ $bill->billed_at ? $bill->billed_at->format('d/m/Y') : $bill->created_at->format('d/m/Y') }}</td>
                                <td>
                                    @if(!$bill->is_paid && $bill->admin)
                                        <form action="{{ route('super-admin.confirmi-billing.mark-paid-admin', $bill->admin) }}" method="POST" style="display:inline" onsubmit="return confirm('Marquer toutes les factures de {{ $bill->admin->name }} comme payées ?')">
                                            @csrf
                                            <button type="submit" class="sa-btn sa-btn-outline sa-btn-sm" title="Tout payer pour cet admin"><i class="fas fa-check-double"></i></button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8"><div class="sa-empty"><i class="fas fa-file-invoice-dollar"></i><p>Aucune facture</p></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($bills->hasPages())
                <div class="sa-pagination">{{ $bills->links() }}</div>
            @endif
        </div>
    </form>
@endsection

@section('scripts')
<script>
function toggleAllBills(el){document.querySelectorAll('.bill-check').forEach(c=>c.checked=el.checked);updateBillBtns()}
function updateBillBtns(){const c=document.querySelectorAll('.bill-check:checked').length;document.getElementById('markPaidBtn').style.display=c>0?'':'none'}
</script>
@endsection
