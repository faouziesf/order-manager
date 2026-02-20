@extends('confirmi.layouts.app')
@section('title', 'Historique')
@section('page-title', 'Historique des commandes traitées')

@section('content')
<div class="content-card">
    <div class="card-header-custom">
        <h6><i class="fas fa-history me-2 text-primary"></i>Historique ({{ $assignments->total() }})</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-modern">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Client</th>
                    <th>Destinataire</th>
                    <th>Montant</th>
                    <th>Tentatives</th>
                    <th>Statut</th>
                    <th>Terminée le</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assignments as $a)
                <tr>
                    <td><strong>{{ $a->order->id ?? '-' }}</strong></td>
                    <td><small>{{ $a->admin->shop_name ?? $a->admin->name ?? '-' }}</small></td>
                    <td>{{ $a->order->customer_name ?? 'N/A' }}</td>
                    <td><strong>{{ number_format($a->order->total_price ?? 0, 3) }} DT</strong></td>
                    <td><span class="badge bg-secondary">{{ $a->attempts }}</span></td>
                    <td>
                        <span class="badge-status badge-{{ $a->status }}">
                            {{ match($a->status) { 'confirmed' => 'Confirmée', 'cancelled' => 'Annulée', 'delivered' => 'Livrée', default => $a->status } }}
                        </span>
                    </td>
                    <td><small>{{ $a->completed_at?->format('d/m/Y H:i') ?? '-' }}</small></td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-4 text-muted">Aucun historique.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $assignments->links() }}</div>
</div>
@endsection
