@extends('confirmi.layouts.app')
@section('title', 'Mes commandes')
@section('page-title', 'Mes commandes à traiter')

@section('content')
<div class="content-card">
    <div class="card-header-custom">
        <h6><i class="fas fa-phone-volume me-2 text-primary"></i>Commandes assignées ({{ $assignments->total() }})</h6>
        <div class="d-flex gap-2">
            <a href="{{ route('confirmi.employee.orders.index') }}" class="btn btn-sm {{ !request('status') ? 'btn-royal' : 'btn-outline-royal' }}">À traiter</a>
            <a href="{{ route('confirmi.employee.orders.index', ['status' => 'confirmed']) }}" class="btn btn-sm {{ request('status') == 'confirmed' ? 'btn-royal' : 'btn-outline-royal' }}">Confirmées</a>
            <a href="{{ route('confirmi.employee.orders.index', ['status' => 'cancelled']) }}" class="btn btn-sm {{ request('status') == 'cancelled' ? 'btn-royal' : 'btn-outline-royal' }}">Annulées</a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-modern">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Client</th>
                    <th>Destinataire</th>
                    <th>Téléphone</th>
                    <th>Montant</th>
                    <th>Tentatives</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assignments as $a)
                <tr>
                    <td><strong>{{ $a->order->id ?? '-' }}</strong></td>
                    <td><small class="text-muted">{{ $a->admin->shop_name ?? $a->admin->name ?? '-' }}</small></td>
                    <td>{{ $a->order->customer_name ?? 'N/A' }}</td>
                    <td>
                        <a href="tel:{{ $a->order->customer_phone ?? '' }}" class="fw-semibold text-decoration-none">
                            {{ $a->order->customer_phone ?? 'N/A' }}
                        </a>
                    </td>
                    <td><strong>{{ number_format($a->order->total_price ?? 0, 3) }} DT</strong></td>
                    <td><span class="badge bg-secondary">{{ $a->attempts }}</span></td>
                    <td>
                        <span class="badge-status badge-{{ $a->status }}">
                            {{ match($a->status) {
                                'assigned' => 'Assignée', 'in_progress' => 'En cours',
                                'confirmed' => 'Confirmée', 'cancelled' => 'Annulée', default => $a->status
                            } }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('confirmi.employee.orders.show', $a) }}" class="btn btn-sm btn-royal">
                            <i class="fas fa-headset"></i> Traiter
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-4 text-muted">Aucune commande.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $assignments->withQueryString()->links() }}</div>
</div>
@endsection
