@extends('layouts.admin')

@section('title', 'Commandes Confirmi')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Commandes gérées par Confirmi</h4>
            <p class="text-muted mb-0">Vue en lecture seule de vos commandes en cours de confirmation</p>
        </div>
        <a href="{{ route('admin.confirmi.index') }}" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Retour
        </a>
    </div>

    <div class="mb-3 d-flex gap-2">
        <a href="{{ route('admin.confirmi.orders') }}" class="btn btn-sm {{ !request('status') ? 'btn-primary' : 'btn-outline-primary' }}">Toutes</a>
        <a href="{{ route('admin.confirmi.orders', ['status' => 'pending']) }}" class="btn btn-sm {{ request('status') == 'pending' ? 'btn-primary' : 'btn-outline-primary' }}">En attente</a>
        <a href="{{ route('admin.confirmi.orders', ['status' => 'assigned']) }}" class="btn btn-sm {{ request('status') == 'assigned' ? 'btn-primary' : 'btn-outline-primary' }}">Assignées</a>
        <a href="{{ route('admin.confirmi.orders', ['status' => 'in_progress']) }}" class="btn btn-sm {{ request('status') == 'in_progress' ? 'btn-primary' : 'btn-outline-primary' }}">En cours</a>
        <a href="{{ route('admin.confirmi.orders', ['status' => 'confirmed']) }}" class="btn btn-sm {{ request('status') == 'confirmed' ? 'btn-primary' : 'btn-outline-primary' }}">Confirmées</a>
        <a href="{{ route('admin.confirmi.orders', ['status' => 'cancelled']) }}" class="btn btn-sm {{ request('status') == 'cancelled' ? 'btn-primary' : 'btn-outline-primary' }}">Annulées</a>
        <a href="{{ route('admin.confirmi.orders', ['status' => 'delivered']) }}" class="btn btn-sm {{ request('status') == 'delivered' ? 'btn-dark' : 'btn-outline-dark' }}">Livrées</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Destinataire</th>
                        <th>Téléphone</th>
                        <th>Montant</th>
                        <th>Employé</th>
                        <th>Tentatives</th>
                        <th>Statut</th>
                        <th>Dernière tentative</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignments as $a)
                    <tr>
                        <td><strong>{{ $a->order->id ?? '-' }}</strong></td>
                        <td>{{ $a->order->customer_name ?? 'N/A' }}</td>
                        <td>{{ $a->order->customer_phone ?? 'N/A' }}</td>
                        <td><strong>{{ number_format($a->order->total_price ?? 0, 3) }} DT</strong></td>
                        <td>{{ $a->assignee->name ?? '-' }}</td>
                        <td><span class="badge bg-secondary">{{ $a->attempts }}</span></td>
                        <td>
                            @switch($a->status)
                                @case('pending')<span class="badge bg-warning text-dark">En attente</span>@break
                                @case('assigned')<span class="badge bg-info">Assignée</span>@break
                                @case('in_progress')<span class="badge bg-primary">En cours</span>@break
                                @case('confirmed')<span class="badge bg-success">Confirmée</span>@break
                                @case('cancelled')<span class="badge bg-danger">Annulée</span>@break
                                @case('delivered')<span class="badge bg-dark">Livrée</span>@break
                                @default <span class="badge bg-secondary">{{ $a->status }}</span>
                            @endswitch
                        </td>
                        <td>
                            <small class="text-muted">{{ $a->last_attempt_at?->format('d/m H:i') ?? '-' }}</small>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-4 text-muted">Aucune commande Confirmi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $assignments->withQueryString()->links() }}</div>
    </div>
</div>
@endsection
