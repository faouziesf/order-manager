@extends('layouts.super-admin')

@section('title', 'Demandes Confirmi')

@section('breadcrumb')
    <ol class="breadcrumb breadcrumb-custom">
        <li class="breadcrumb-item"><a href="{{ route('super-admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Demandes Confirmi</li>
    </ol>
@endsection

@section('page-header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">Demandes d'activation Confirmi</h1>
            <p class="page-subtitle">Approuvez ou rejetez les demandes d'accès Confirmi des administrateurs</p>
        </div>
        @if($pendingCount > 0)
            <span class="badge bg-warning text-dark fs-6">{{ $pendingCount }} en attente</span>
        @endif
    </div>
@endsection

@section('content')
<div class="mb-3 d-flex gap-2">
    <a href="{{ route('super-admin.confirmi-requests.index') }}" class="btn btn-sm {{ !request('status') ? 'btn-primary' : 'btn-outline-primary' }}">Toutes</a>
    <a href="{{ route('super-admin.confirmi-requests.index', ['status' => 'pending']) }}" class="btn btn-sm {{ request('status') == 'pending' ? 'btn-warning' : 'btn-outline-warning' }}">En attente</a>
    <a href="{{ route('super-admin.confirmi-requests.index', ['status' => 'approved']) }}" class="btn btn-sm {{ request('status') == 'approved' ? 'btn-success' : 'btn-outline-success' }}">Approuvées</a>
    <a href="{{ route('super-admin.confirmi-requests.index', ['status' => 'rejected']) }}" class="btn btn-sm {{ request('status') == 'rejected' ? 'btn-danger' : 'btn-outline-danger' }}">Rejetées</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Admin</th>
                    <th>Boutique</th>
                    <th>Message</th>
                    <th>Statut</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $req)
                <tr>
                    <td>{{ $req->id }}</td>
                    <td>
                        <strong>{{ $req->admin->name ?? 'N/A' }}</strong>
                        <br><small class="text-muted">{{ $req->admin->email ?? '' }}</small>
                    </td>
                    <td>{{ $req->admin->shop_name ?? '-' }}</td>
                    <td><small>{{ $req->admin_message ? Str::limit($req->admin_message, 60) : '-' }}</small></td>
                    <td>
                        @if($req->status === 'pending')
                            <span class="badge bg-warning text-dark">En attente</span>
                        @elseif($req->status === 'approved')
                            <span class="badge bg-success">Approuvée</span>
                        @else
                            <span class="badge bg-danger">Rejetée</span>
                        @endif
                    </td>
                    <td><small>{{ $req->created_at->format('d/m/Y H:i') }}</small></td>
                    <td>
                        @if($req->status === 'pending')
                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#approveModal{{ $req->id }}">
                                <i class="fas fa-check"></i> Approuver
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $req->id }}">
                                <i class="fas fa-times"></i> Rejeter
                            </button>

                            {{-- Approve Modal --}}
                            <div class="modal fade" id="approveModal{{ $req->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('super-admin.confirmi-requests.approve', $req) }}">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Approuver – {{ $req->admin->name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Tarif par commande confirmée (DT) <span class="text-danger">*</span></label>
                                                    <input type="number" name="rate_confirmed" class="form-control" step="0.001" min="0" value="{{ $req->admin->confirmi_rate_confirmed ?? 0.500 }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Tarif par commande livrée (DT) <span class="text-danger">*</span></label>
                                                    <input type="number" name="rate_delivered" class="form-control" step="0.001" min="0" value="{{ $req->admin->confirmi_rate_delivered ?? 0.500 }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Message (optionnel)</label>
                                                    <textarea name="response_message" class="form-control" rows="2" placeholder="Bienvenue sur Confirmi..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                                                <button type="submit" class="btn btn-success"><i class="fas fa-check me-1"></i>Approuver</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            {{-- Reject Modal --}}
                            <div class="modal fade" id="rejectModal{{ $req->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('super-admin.confirmi-requests.reject', $req) }}">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Rejeter – {{ $req->admin->name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Raison du rejet (optionnel)</label>
                                                    <textarea name="response_message" class="form-control" rows="2" placeholder="Ex: Dossier incomplet..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                                                <button type="submit" class="btn btn-danger"><i class="fas fa-times me-1"></i>Rejeter</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @else
                            <small class="text-muted">
                                Traitée le {{ $req->processed_at?->format('d/m/Y') ?? '-' }}
                                @if($req->response_message)
                                    <br><em>{{ Str::limit($req->response_message, 40) }}</em>
                                @endif
                            </small>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-4 text-muted">Aucune demande Confirmi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $requests->withQueryString()->links() }}</div>
</div>
@endsection
