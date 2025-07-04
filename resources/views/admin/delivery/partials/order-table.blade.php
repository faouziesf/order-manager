@if($orders->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th width="50">
                        <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll()">
                    </th>
                    <th>N° Commande</th>
                    <th>Client</th>
                    <th>Adresse</th>
                    <th>Montant</th>
                    <th>Date</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                <tr>
                    <td>
                        <input type="checkbox" class="order-checkbox" value="{{ $order->id }}" 
                               onchange="toggleOrder({{ $order->id }})">
                    </td>
                    <td>
                        <strong>#{{ $order->id }}</strong>
                    </td>
                    <td>
                        <div>
                            <strong>{{ $order->customer_name }}</strong>
                            <br>
                            <small class="text-muted">
                                <i class="fas fa-phone"></i> {{ $order->customer_phone }}
                                @if($order->customer_phone_2)
                                    / {{ $order->customer_phone_2 }}
                                @endif
                            </small>
                            @if($order->customer_email)
                                <br>
                                <small class="text-muted">
                                    <i class="fas fa-envelope"></i> {{ $order->customer_email }}
                                </small>
                            @endif
                        </div>
                    </td>
                    <td>
                        <div class="text-truncate" style="max-width: 200px;" title="{{ $order->customer_address }}">
                            {{ $order->customer_address }}
                        </div>
                        @if($order->customer_governorate)
                            <small class="text-muted">{{ $order->customer_governorate }}</small>
                        @endif
                    </td>
                    <td>
                        <strong class="text-success">{{ number_format($order->total_price, 3) }} TND</strong>
                        @if($order->items_count ?? 0 > 0)
                            <br>
                            <small class="text-muted">{{ $order->items_count }} article(s)</small>
                        @endif
                    </td>
                    <td>
                        <div>
                            {{ $order->created_at->format('d/m/Y') }}
                            <br>
                            <small class="text-muted">{{ $order->created_at->format('H:i') }}</small>
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-success">{{ ucfirst($order->status) }}</span>
                        @if($order->is_suspended)
                            <br>
                            <span class="badge badge-warning badge-sm">Suspendue</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($orders->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
                <small class="text-muted">
                    Affichage de {{ $orders->firstItem() }} à {{ $orders->lastItem() }} 
                    sur {{ $orders->total() }} commandes
                </small>
            </div>
            <div>
                {{ $orders->appends(request()->query())->links() }}
            </div>
        </div>
    @endif
@else
    <div class="text-center py-5">
        <i class="fas fa-inbox fa-3x text-gray-300 mb-3"></i>
        <h5 class="text-gray-600">Aucune commande trouvée</h5>
        <p class="text-muted">
            @if(request()->hasAny(['search', 'date_from', 'date_to', 'min_amount', 'max_amount']))
                Aucune commande ne correspond à vos critères de recherche.
                <br>Essayez de modifier les filtres.
            @else
                Toutes vos commandes confirmées ont déjà été expédiées.
            @endif
        </p>
    </div>
@endif