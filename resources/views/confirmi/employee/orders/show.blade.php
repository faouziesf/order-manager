@extends('confirmi.layouts.app')
@section('title', 'Traiter commande')
@section('page-title', 'Traiter commande #' . ($assignment->order->id ?? 'N/A'))

@section('content')
<div class="row g-3">
    <div class="col-lg-8">
        <div class="content-card mb-3">
            <div class="card-header-custom">
                <h6><i class="fas fa-shopping-bag me-2 text-primary"></i>Informations commande</h6>
                <span class="badge-status badge-{{ $assignment->status }}">
                    {{ match($assignment->status) {
                        'assigned' => 'Assignée', 'in_progress' => 'En cours',
                        'confirmed' => 'Confirmée', 'cancelled' => 'Annulée', default => $assignment->status
                    } }}
                </span>
            </div>
            <div class="p-3">
                @if($assignment->order)
                @php $order = $assignment->order; @endphp
                <div class="row g-3">
                    <div class="col-md-6">
                        <small class="text-muted d-block">Destinataire</small>
                        <strong class="fs-5">{{ $order->customer_name }}</strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Téléphone</small>
                        <div class="d-flex gap-2 align-items-center">
                            <a href="tel:{{ $order->customer_phone }}" class="btn btn-sm btn-royal">
                                <i class="fas fa-phone me-1"></i>{{ $order->customer_phone }}
                            </a>
                            @if($order->customer_phone_2)
                            <a href="tel:{{ $order->customer_phone_2 }}" class="btn btn-sm btn-outline-royal">
                                <i class="fas fa-phone me-1"></i>{{ $order->customer_phone_2 }}
                            </a>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Adresse</small>
                        <span>{{ $order->customer_address }}</span>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Gouvernorat</small>
                        <span>{{ $order->customer_governorate }}</span>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Ville</small>
                        <span>{{ $order->customer_city }}</span>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Montant total</small>
                        <strong class="fs-4 text-primary">{{ number_format($order->total_price, 3) }} DT</strong>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Statut commande</small>
                        <span>{{ $order->status }}</span>
                    </div>
                    @if($order->notes)
                    <div class="col-12">
                        <small class="text-muted d-block">Notes</small>
                        <span>{{ $order->notes }}</span>
                    </div>
                    @endif
                </div>

                @if($order->items && $order->items->count() > 0)
                <hr>
                <h6 class="fw-bold mb-2"><i class="fas fa-box me-1"></i>Articles</h6>
                <div class="table-responsive">
                    <table class="table table-modern table-sm">
                        <thead><tr><th>Produit</th><th>Qté</th><th>Prix</th><th>Total</th></tr></thead>
                        <tbody>
                            @foreach($order->items as $item)
                            <tr>
                                <td>{{ $item->product->name ?? $item->product_name ?? 'N/A' }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format($item->unit_price ?? 0, 3) }} DT</td>
                                <td><strong>{{ number_format(($item->unit_price ?? 0) * $item->quantity, 3) }} DT</strong></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
                @endif
            </div>
        </div>

        <div class="content-card">
            <div class="card-header-custom"><h6><i class="fas fa-building me-2 text-info"></i>Client</h6></div>
            <div class="p-3">
                <div class="row g-2">
                    <div class="col-md-6"><small class="text-muted">Admin</small><br><strong>{{ $assignment->admin->name ?? '-' }}</strong></div>
                    <div class="col-md-6"><small class="text-muted">Boutique</small><br><strong>{{ $assignment->admin->shop_name ?? '-' }}</strong></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Status info -->
        <div class="content-card mb-3">
            <div class="card-header-custom"><h6><i class="fas fa-info-circle me-2"></i>Suivi</h6></div>
            <div class="p-3">
                <p><small class="text-muted">Tentatives :</small> <strong>{{ $assignment->attempts }}</strong></p>
                @if($assignment->first_attempt_at)
                    <p><small class="text-muted">Première tentative :</small><br>{{ $assignment->first_attempt_at->format('d/m/Y H:i') }}</p>
                @endif
                @if($assignment->last_attempt_at)
                    <p><small class="text-muted">Dernière tentative :</small><br>{{ $assignment->last_attempt_at->format('d/m/Y H:i') }}</p>
                @endif
                @if($assignment->notes)
                    <p class="mb-0"><small class="text-muted">Notes :</small><br>{{ $assignment->notes }}</p>
                @endif
            </div>
        </div>

        <!-- Action Panel -->
        @if($assignment->canBeManaged())
        <div class="content-card mb-3">
            <div class="card-header-custom"><h6><i class="fas fa-headset me-2 text-primary"></i>Enregistrer résultat</h6></div>
            <div class="p-3">
                @if($assignment->status === 'assigned')
                    <form method="POST" action="{{ route('confirmi.employee.orders.start', $assignment) }}" class="mb-3">
                        @csrf
                        <button type="submit" class="btn btn-royal w-100"><i class="fas fa-play me-1"></i>Démarrer le traitement</button>
                    </form>
                    <hr>
                @endif

                <form method="POST" action="{{ route('confirmi.employee.orders.attempt', $assignment) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" style="font-size:0.8rem;font-weight:600;">Résultat de l'appel</label>
                        <div class="d-grid gap-2">
                            <label class="d-flex align-items-center gap-2 p-2 border rounded-2 cursor-pointer" style="cursor:pointer;">
                                <input type="radio" name="result" value="confirmed" required>
                                <span><i class="fas fa-check-circle text-success me-1"></i>Confirmée</span>
                            </label>
                            <label class="d-flex align-items-center gap-2 p-2 border rounded-2" style="cursor:pointer;">
                                <input type="radio" name="result" value="no_answer">
                                <span><i class="fas fa-phone-slash text-warning me-1"></i>Pas de réponse</span>
                            </label>
                            <label class="d-flex align-items-center gap-2 p-2 border rounded-2" style="cursor:pointer;">
                                <input type="radio" name="result" value="callback">
                                <span><i class="fas fa-phone-alt text-info me-1"></i>Rappeler plus tard</span>
                            </label>
                            <label class="d-flex align-items-center gap-2 p-2 border rounded-2" style="cursor:pointer;">
                                <input type="radio" name="result" value="cancelled">
                                <span><i class="fas fa-times-circle text-danger me-1"></i>Annulée</span>
                            </label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:0.8rem;font-weight:600;">Notes (optionnel)</label>
                        <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="Notes sur l'appel..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-royal w-100"><i class="fas fa-save me-1"></i>Enregistrer</button>
                </form>
            </div>
        </div>
        @else
        <div class="content-card">
            <div class="card-header-custom"><h6><i class="fas fa-flag-checkered me-2 text-success"></i>Terminée</h6></div>
            <div class="p-3 text-center">
                @if($assignment->status === 'confirmed')
                    <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                    <p class="fw-bold text-success mb-0">Commande confirmée</p>
                @elseif($assignment->status === 'cancelled')
                    <i class="fas fa-times-circle fa-2x text-danger mb-2"></i>
                    <p class="fw-bold text-danger mb-0">Commande annulée</p>
                @endif
                @if($assignment->completed_at)
                    <small class="text-muted">{{ $assignment->completed_at->format('d/m/Y H:i') }}</small>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

<div class="mt-3">
    <a href="{{ route('confirmi.employee.orders.index') }}" class="btn btn-outline-royal"><i class="fas fa-arrow-left me-1"></i>Retour</a>
</div>
@endsection
