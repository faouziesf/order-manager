@extends('confirmi.layouts.app')
@section('title', 'Demande #' . $confirmiRequest->id)
@section('page-title', 'Demande d\'activation #' . $confirmiRequest->id)

@section('content')
<div class="row g-3">
    <div class="col-lg-8">
        <div class="content-card mb-3">
            <div class="card-header-custom">
                <h6><i class="fas fa-building me-2 text-primary"></i>Informations Admin</h6>
                @if($confirmiRequest->status === 'pending')
                    <span class="badge-status badge-pending">En attente</span>
                @elseif($confirmiRequest->status === 'approved')
                    <span class="badge-status badge-confirmed">Approuvée</span>
                @else
                    <span class="badge-status badge-cancelled">Rejetée</span>
                @endif
            </div>
            <div class="p-3">
                @if($confirmiRequest->admin)
                <div class="row g-3">
                    <div class="col-md-4"><small class="text-muted">Nom</small><br><strong>{{ $confirmiRequest->admin->name }}</strong></div>
                    <div class="col-md-4"><small class="text-muted">Boutique</small><br><strong>{{ $confirmiRequest->admin->shop_name ?? '-' }}</strong></div>
                    <div class="col-md-4"><small class="text-muted">Email</small><br><strong>{{ $confirmiRequest->admin->email }}</strong></div>
                    <div class="col-md-4"><small class="text-muted">Téléphone</small><br>{{ $confirmiRequest->admin->phone ?? '-' }}</div>
                    <div class="col-md-4"><small class="text-muted">Abonnement</small><br>{{ $confirmiRequest->admin->subscription_type ?? '-' }}</div>
                    <div class="col-md-4"><small class="text-muted">Date demande</small><br>{{ $confirmiRequest->created_at->format('d/m/Y H:i') }}</div>
                </div>
                @endif
            </div>
        </div>

        @if($confirmiRequest->admin_message)
        <div class="content-card mb-3">
            <div class="card-header-custom"><h6><i class="fas fa-comment me-2 text-info"></i>Message de l'admin</h6></div>
            <div class="p-3"><p class="mb-0">{{ $confirmiRequest->admin_message }}</p></div>
        </div>
        @endif

        @if($confirmiRequest->response_message)
        <div class="content-card">
            <div class="card-header-custom"><h6><i class="fas fa-reply me-2 text-success"></i>Réponse</h6></div>
            <div class="p-3"><p class="mb-0">{{ $confirmiRequest->response_message }}</p></div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        @if($confirmiRequest->status === 'pending')
        <!-- Approve Form -->
        <div class="content-card mb-3">
            <div class="card-header-custom"><h6><i class="fas fa-check-circle me-2 text-success"></i>Approuver</h6></div>
            <div class="p-3">
                <form method="POST" action="{{ route('confirmi.commercial.requests.approve', $confirmiRequest) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" style="font-size:0.8rem;font-weight:600;">Tarif par commande confirmée (DT)</label>
                        <input type="number" step="0.001" name="rate_confirmed" class="form-control form-control-sm" required min="0" value="{{ old('rate_confirmed', '0.500') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:0.8rem;font-weight:600;">Tarif par commande livrée (DT)</label>
                        <input type="number" step="0.001" name="rate_delivered" class="form-control form-control-sm" required min="0" value="{{ old('rate_delivered', '0.300') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:0.8rem;font-weight:600;">Message (optionnel)</label>
                        <textarea name="response_message" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                    <button type="submit" class="btn btn-success btn-sm w-100"><i class="fas fa-check me-1"></i>Approuver et activer</button>
                </form>
            </div>
        </div>

        <!-- Reject Form -->
        <div class="content-card">
            <div class="card-header-custom"><h6><i class="fas fa-times-circle me-2 text-danger"></i>Rejeter</h6></div>
            <div class="p-3">
                <form method="POST" action="{{ route('confirmi.commercial.requests.reject', $confirmiRequest) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" style="font-size:0.8rem;font-weight:600;">Raison du rejet</label>
                        <textarea name="response_message" class="form-control form-control-sm" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger btn-sm w-100"><i class="fas fa-times me-1"></i>Rejeter</button>
                </form>
            </div>
        </div>
        @else
        <div class="content-card">
            <div class="card-header-custom"><h6><i class="fas fa-info-circle me-2"></i>Résultat</h6></div>
            <div class="p-3">
                <p><small class="text-muted">Tarif confirmé :</small><br><strong>{{ $confirmiRequest->proposed_rate_confirmed }} DT</strong></p>
                <p><small class="text-muted">Tarif livré :</small><br><strong>{{ $confirmiRequest->proposed_rate_delivered }} DT</strong></p>
                <p class="mb-0"><small class="text-muted">Traité le :</small><br>{{ $confirmiRequest->processed_at?->format('d/m/Y H:i') ?? '-' }}</p>
            </div>
        </div>
        @endif
    </div>
</div>

<div class="mt-3">
    <a href="{{ route('confirmi.commercial.requests.index') }}" class="btn btn-outline-royal"><i class="fas fa-arrow-left me-1"></i>Retour</a>
</div>
@endsection
