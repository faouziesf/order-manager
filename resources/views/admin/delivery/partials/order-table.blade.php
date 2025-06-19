@if($orders->count() > 0)
<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th width="50">
                    <input type="checkbox" class="form-check-input" id="selectAllCheckbox" onchange="toggleAllOrders()">
                </th>
                <th>Commande</th>
                <th>Client</th>
                <th>Téléphone</th>
                <th>Adresse</th>
                <th>Montant</th>
                <th>Date</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
            <tr onclick="toggleOrderSelection({{ $order->id }})" style="cursor: pointer;">
                <td onclick="event.stopPropagation();">
                    <input type="checkbox" class="form-check-input order-checkbox" 
                           data-order-id="{{ $order->id }}">
                </td>
                <td>
                    <strong>#{{ $order->id }}</strong>
                    @if($order->order_items_count ?? 0 > 0)
                        <br><small class="text-muted">{{ $order->order_items_count }} article(s)</small>
                    @endif
                </td>
                <td>
                    <strong>{{ $order->customer_name ?? 'N/A' }}</strong>
                </td>
                <td>
                    <span class="font-monospace">{{ $order->customer_phone ?? 'N/A' }}</span>
                </td>
                <td>
                    <small>{{ Str::limit($order->customer_address ?? 'N/A', 50) }}</small>
                </td>
                <td>
                    <strong>{{ number_format($order->total_price ?? 0, 3) }} DT</strong>
                </td>
                <td>
                    <small>{{ $order->created_at->format('d/m/Y H:i') }}</small>
                </td>
                <td>
                    <span class="badge bg-warning">{{ $order->status }}</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@if($orders->hasPages())
<div class="d-flex justify-content-center mt-3">
    {{ $orders->links() }}
</div>
@endif

<div class="mt-3">
    <small class="text-muted">
        {{ $orders->total() }} commande(s) au total • 
        {{ $orders->count() }} commande(s) sur cette page
    </small>
</div>

@else
<div class="text-center py-5">
    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
    <h5 class="text-muted">Aucune commande disponible</h5>
    <p class="text-muted mb-4">
        Toutes les commandes confirmées ont déjà été assignées à des enlèvements<br>
        ou il n'y a pas de commandes avec le statut "confirmée"
    </p>
    
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="alert alert-info">
                <h6 class="alert-heading">
                    <i class="fas fa-info-circle me-2"></i>
                    Information
                </h6>
                <small>
                    Les commandes doivent avoir le statut <strong>"confirmée"</strong> pour apparaître ici.
                    <br>
                    Vérifiez que vos commandes ont bien ce statut dans la gestion des commandes.
                </small>
            </div>
        </div>
    </div>
</div>
@endif