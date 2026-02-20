@extends('confirmi.layouts.app')
@section('title', 'Détails commande')
@section('page-title', 'Commande #' . ($assignment->order->id ?? 'N/A'))

@section('content')
<div class="row g-3">
    <div class="col-lg-8">
        <div class="content-card mb-3">
            <div class="card-header-custom">
                <h6><i class="fas fa-shopping-bag me-2 text-primary"></i>Détails de la commande</h6>
                <span class="badge-status badge-{{ $assignment->status }}">
                    {{ match($assignment->status) {
                        'pending' => 'En attente', 'assigned' => 'Assignée', 'in_progress' => 'En cours',
                        'confirmed' => 'Confirmée', 'cancelled' => 'Annulée', 'delivered' => 'Livrée', default => $assignment->status
                    } }}
                </span>
            </div>
            <div class="p-3">
                @if($assignment->order)
                    @php $order = $assignment->order; @endphp
                    <div class="row g-3">
                        <div class="col-md-6">
                            <p class="mb-1"><small class="text-muted">Destinataire</small></p>
                            <p class="fw-bold mb-0">{{ $order->customer_name }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><small class="text-muted">Téléphone</small></p>
                            <p class="fw-bold mb-0">
                                <a href="tel:{{ $order->customer_phone }}">{{ $order->customer_phone }}</a>
                                @if($order->customer_phone_2)
                                    / <a href="tel:{{ $order->customer_phone_2 }}">{{ $order->customer_phone_2 }}</a>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><small class="text-muted">Adresse</small></p>
                            <p class="mb-0">{{ $order->customer_address }}</p>
                        </div>
                        <div class="col-md-3">
                            <p class="mb-1"><small class="text-muted">Gouvernorat</small></p>
                            <p class="mb-0">{{ $order->customer_governorate }}</p>
                        </div>
                        <div class="col-md-3">
                            <p class="mb-1"><small class="text-muted">Ville</small></p>
                            <p class="mb-0">{{ $order->customer_city }}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1"><small class="text-muted">Montant total</small></p>
                            <p class="fw-bold fs-5 text-primary mb-0">{{ number_format($order->total_price, 3) }} DT</p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1"><small class="text-muted">Statut commande</small></p>
                            <p class="mb-0">{{ $order->status }}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1"><small class="text-muted">Notes</small></p>
                            <p class="mb-0">{{ $order->notes ?? '-' }}</p>
                        </div>
                    </div>

                    @if($order->items && $order->items->count() > 0)
                    <hr>
                    <h6 class="fw-bold mb-3"><i class="fas fa-box me-1"></i>Articles</h6>
                    <div class="table-responsive">
                        <table class="table table-modern table-sm">
                            <thead>
                                <tr><th>Produit</th><th>Qté</th><th>Prix</th><th>Total</th></tr>
                            </thead>
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

        <!-- Admin info -->
        <div class="content-card">
            <div class="card-header-custom">
                <h6><i class="fas fa-building me-2 text-info"></i>Client (Admin)</h6>
            </div>
            <div class="p-3">
                @if($assignment->admin)
                <div class="row g-2">
                    <div class="col-md-4"><small class="text-muted">Nom</small><br><strong>{{ $assignment->admin->name }}</strong></div>
                    <div class="col-md-4"><small class="text-muted">Boutique</small><br><strong>{{ $assignment->admin->shop_name ?? '-' }}</strong></div>
                    <div class="col-md-4"><small class="text-muted">Tarif confirmé / livré</small><br><strong>{{ $assignment->admin->confirmi_rate_confirmed }} / {{ $assignment->admin->confirmi_rate_delivered }} DT</strong></div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Assignment info -->
        <div class="content-card mb-3">
            <div class="card-header-custom"><h6><i class="fas fa-user-check me-2"></i>Assignation</h6></div>
            <div class="p-3">
                <p><small class="text-muted">Assigné à :</small><br><strong>{{ $assignment->assignee->name ?? 'Non assigné' }}</strong></p>
                <p><small class="text-muted">Assigné par :</small><br>{{ $assignment->assigner->name ?? '-' }}</p>
                <p><small class="text-muted">Tentatives :</small><br><strong>{{ $assignment->attempts }}</strong></p>
                @if($assignment->assigned_at)
                    <p class="mb-0"><small class="text-muted">Date assignation :</small><br>{{ $assignment->assigned_at->format('d/m/Y H:i') }}</p>
                @endif
            </div>
        </div>

        <!-- Assign to employee -->
        @if($assignment->status === 'pending' || $assignment->status === 'assigned')
        <div class="content-card mb-3">
            <div class="card-header-custom"><h6><i class="fas fa-user-plus me-2 text-primary"></i>{{ $assignment->status === 'pending' ? 'Assigner' : 'Réassigner' }}</h6></div>
            <div class="p-3">
                <form method="POST" action="{{ route('confirmi.commercial.orders.assign', $assignment) }}">
                    @csrf
                    <div class="mb-3">
                        <select name="assigned_to" class="form-select form-select-sm" required>
                            <option value="">Choisir un employé</option>
                            @foreach(\App\Models\ConfirmiUser::where('role','employee')->where('is_active',true)->get() as $emp)
                                <option value="{{ $emp->id }}" {{ $assignment->assigned_to == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-royal btn-sm w-100"><i class="fas fa-check me-1"></i>Assigner</button>
                </form>
            </div>
        </div>
        @endif

        @if($assignment->status === 'confirmed')
        <div class="content-card mb-3">
            <div class="card-header-custom"><h6><i class="fas fa-truck me-2 text-success"></i>Marquer comme livrée</h6></div>
            <div class="p-3">
                <p class="text-muted mb-3" style="font-size:0.85rem;">
                    La commande a été confirmée. Une fois livrée par le livreur, marquez-la ici pour déclencher la facturation de livraison.
                </p>
                <form method="POST" action="{{ route('confirmi.commercial.orders.mark-delivered', $assignment) }}" onsubmit="return confirm('Confirmer la livraison de cette commande ?')">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm w-100">
                        <i class="fas fa-check-double me-1"></i>Confirmer la livraison
                    </button>
                </form>
            </div>
        </div>
        @endif

        @if($assignment->notes)
        <div class="content-card">
            <div class="card-header-custom"><h6><i class="fas fa-sticky-note me-2 text-warning"></i>Notes</h6></div>
            <div class="p-3"><p class="mb-0" style="font-size:0.85rem;">{{ $assignment->notes }}</p></div>
        </div>
        @endif
    </div>
</div>

<div class="mt-3">
    <a href="{{ url()->previous() }}" class="btn btn-outline-royal"><i class="fas fa-arrow-left me-1"></i>Retour</a>
</div>
@endsection
