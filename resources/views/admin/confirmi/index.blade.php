@extends('layouts.admin')

@section('title', 'Confirmation par Confirmi')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Confirmation par Confirmi</h4>
            <p class="text-muted mb-0">Service de confirmation de commandes par notre équipe dédiée</p>
        </div>
    </div>

    @if($status === 'disabled')
        {{-- PAS ENCORE ACTIVÉ --}}
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <div style="width:80px;height:80px;margin:0 auto;background:#eff6ff;border-radius:20px;display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-headset fa-2x" style="color:#2563eb;"></i>
                            </div>
                        </div>
                        <h5 class="fw-bold">Activez la confirmation par Confirmi</h5>
                        <p class="text-muted mb-4" style="max-width:500px;margin:0 auto;">
                            Notre équipe de confirmation appelle vos clients pour confirmer les commandes avant l'expédition.
                            Réduisez les retours et augmentez votre taux de livraison.
                        </p>
                        <div class="row g-3 justify-content-center mb-4">
                            <div class="col-md-4">
                                <div class="p-3 rounded-3" style="background:#f0fdf4;">
                                    <i class="fas fa-phone-volume text-success mb-2 d-block"></i>
                                    <small class="fw-bold">Confirmation téléphonique</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 rounded-3" style="background:#eff6ff;">
                                    <i class="fas fa-chart-line text-primary mb-2 d-block"></i>
                                    <small class="fw-bold">Taux de livraison amélioré</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 rounded-3" style="background:#fef3c7;">
                                    <i class="fas fa-undo text-warning mb-2 d-block"></i>
                                    <small class="fw-bold">Moins de retours</small>
                                </div>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('admin.confirmi.request') }}">
                            @csrf
                            <div class="mb-3" style="max-width:400px;margin:0 auto;">
                                <textarea name="message" class="form-control" rows="3" placeholder="Message optionnel (ex: volume estimé, besoins spécifiques...)"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="fas fa-paper-plane me-2"></i>Demander l'activation
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    @elseif($status === 'pending')
        {{-- DEMANDE EN COURS --}}
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <div class="mb-3">
                            <div class="spinner-border text-primary" role="status" style="width:3rem;height:3rem;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        <h5 class="fw-bold">Demande en cours de traitement</h5>
                        <p class="text-muted mb-3">
                            Votre demande d'activation Confirmi est en cours de traitement par notre équipe commerciale.
                            Vous serez notifié dès que votre compte sera activé.
                        </p>
                        @if($pendingRequest)
                            <div class="text-muted small">
                                <i class="fas fa-clock me-1"></i>Envoyée le {{ $pendingRequest->created_at->format('d/m/Y à H:i') }}
                            </div>
                            @if($pendingRequest->admin_message)
                                <div class="mt-3 p-3 bg-light rounded">
                                    <small class="text-muted">Votre message :</small><br>
                                    {{ $pendingRequest->admin_message }}
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>

    @elseif($status === 'active')
        {{-- ACTIF - DASHBOARD --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="fs-3 fw-bold text-primary">{{ $stats['total'] }}</div>
                        <small class="text-muted">Total commandes</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="fs-3 fw-bold text-warning">{{ $stats['pending'] + $stats['in_progress'] }}</div>
                        <small class="text-muted">En cours</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="fs-3 fw-bold text-success">{{ $stats['confirmed'] }}</div>
                        <small class="text-muted">Confirmées</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="fs-3 fw-bold text-danger">{{ $stats['cancelled'] }}</div>
                        <small class="text-muted">Annulées</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- BILLING SUMMARY --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="fas fa-file-invoice-dollar me-2 text-warning"></i>Facturation ce mois</h6>
                <a href="{{ route('admin.confirmi.billing') }}" class="btn btn-sm btn-outline-warning">
                    <i class="fas fa-history me-1"></i>Historique
                </a>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3 col-6">
                        <small class="text-muted d-block">Montant ce mois</small>
                        <div class="fw-bold fs-5 text-warning">{{ number_format($billing['month_total'], 3) }} DT</div>
                    </div>
                    <div class="col-md-3 col-6">
                        <small class="text-muted d-block">Impayé</small>
                        <div class="fw-bold fs-5 text-danger">{{ number_format($billing['unpaid'], 3) }} DT</div>
                    </div>
                    <div class="col-md-3 col-6">
                        <small class="text-muted d-block">Confirmées facturées</small>
                        <div class="fw-bold">{{ $billing['month_confirmed'] }} commandes</div>
                    </div>
                    <div class="col-md-3 col-6">
                        <small class="text-muted d-block">Livrées facturées</small>
                        <div class="fw-bold">{{ $billing['month_delivered'] }} commandes</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="fas fa-info-circle me-2 text-primary"></i>Votre abonnement Confirmi</h6>
                <span class="badge bg-success">Actif</span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <small class="text-muted">Tarif par commande confirmée</small>
                        <div class="fw-bold fs-5">{{ number_format($admin->confirmi_rate_confirmed, 3) }} DT</div>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">Tarif par commande livrée</small>
                        <div class="fw-bold fs-5">{{ number_format($admin->confirmi_rate_delivered, 3) }} DT</div>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">Activé depuis</small>
                        <div class="fw-bold">{{ $admin->confirmi_activated_at ? $admin->confirmi_activated_at->format('d/m/Y') : '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0">Vos commandes Confirmi</h5>
            <a href="{{ route('admin.confirmi.orders') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-list me-1"></i>Voir toutes
            </a>
        </div>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            Les commandes gérées par Confirmi sont en <strong>lecture seule</strong>.
            Une fois qu'une tentative de confirmation est enregistrée, vous ne pouvez plus modifier la commande.
        </div>
    @endif

    @if($status === 'active')
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold"><i class="fas fa-plug me-2 text-info"></i>Configuration Masafa Express</h6>
            @if($masafaConfig && $masafaConfig->is_active)
                <span class="badge bg-success">Configuré</span>
            @else
                <span class="badge bg-secondary">Non configuré</span>
            @endif
        </div>
        <div class="card-body">
            <p class="text-muted small mb-3">
                Connectez votre compte Masafa Express pour envoyer automatiquement les commandes confirmées en livraison.
            </p>
            <form method="POST" action="{{ route('admin.confirmi.masafa-config') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Token API Masafa Express <span class="text-danger">*</span></label>
                        <input type="password" name="api_token" class="form-control form-control-sm"
                               placeholder="Bearer token..." value="{{ $masafaConfig ? '••••••••' : '' }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">ID Client Masafa</label>
                        <input type="text" name="masafa_client_id" class="form-control form-control-sm"
                               value="{{ $masafaConfig->masafa_client_id ?? '' }}" placeholder="ex: pickup_1">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="auto_send" id="autoSend" value="1"
                                   {{ ($masafaConfig && $masafaConfig->auto_send) ? 'checked' : '' }}>
                            <label class="form-check-label small" for="autoSend">Auto-envoi</label>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-info btn-sm mt-3 text-white">
                    <i class="fas fa-save me-1"></i>Sauvegarder
                </button>
            </form>
        </div>
    </div>
    @endif

    @if($latestRequest && $latestRequest->status === 'rejected')
        <div class="alert alert-warning mt-3">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Dernière demande rejetée</strong> ({{ $latestRequest->processed_at?->format('d/m/Y') }})
            @if($latestRequest->response_message)
                <br><small>{{ $latestRequest->response_message }}</small>
            @endif
            <br><small>Vous pouvez soumettre une nouvelle demande.</small>
        </div>
    @endif
</div>
@endsection
