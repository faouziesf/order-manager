@if($orders->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th width="50">
                        <input type="checkbox" id="selectAllTableCheckbox">
                    </th>
                    <th>N° Commande</th>
                    <th>Client</th>
                    <th>Téléphone</th>
                    <th>Adresse de livraison</th>
                    <th>Gouvernorat</th>
                    <th>Montant</th>
                    <th>Date de création</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                    <tr>
                        <td>
                            <input type="checkbox" class="order-checkbox" value="{{ $order->id }}">
                        </td>
                        <td>
                            <strong>#{{ $order->id }}</strong>
                            @if($order->external_id)
                                <br>
                                <small class="text-muted">Ext: {{ $order->external_id }}</small>
                            @endif
                        </td>
                        <td>
                            <div>
                                <strong>{{ $order->customer_name ?: 'N/A' }}</strong>
                                @if($order->customer_email)
                                    <br>
                                    <small class="text-muted">
                                        <i class="fas fa-envelope"></i> {{ $order->customer_email }}
                                    </small>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div>
                                @if($order->customer_phone)
                                    <span class="text-primary">
                                        <i class="fas fa-phone"></i> {{ $order->customer_phone }}
                                    </span>
                                @else
                                    <span class="text-muted">Non renseigné</span>
                                @endif
                                @if($order->customer_phone_2)
                                    <br>
                                    <small class="text-muted">
                                        <i class="fas fa-phone"></i> {{ $order->customer_phone_2 }}
                                    </small>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="text-truncate" style="max-width: 200px;" title="{{ $order->customer_address }}">
                                {{ $order->customer_address ?: 'Non renseignée' }}
                            </div>
                            @if($order->customer_city)
                                <br>
                                <small class="text-muted">{{ $order->customer_city }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-info">
                                {{ $order->customer_governorate ?: 'N/A' }}
                            </span>
                        </td>
                        <td>
                            <div>
                                <strong class="text-success">
                                    {{ number_format($order->total_price, 3) }} TND
                                </strong>
                                @if($order->shipping_cost > 0)
                                    <br>
                                    <small class="text-muted">
                                        + {{ number_format($order->shipping_cost, 3) }} TND (livraison)
                                    </small>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div>
                                {{ $order->created_at->format('d/m/Y') }}
                                <br>
                                <small class="text-muted">{{ $order->created_at->format('H:i') }}</small>
                            </div>
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

    <!-- Résumé -->
    <div class="row mt-3">
        <div class="col-md-6">
            <div class="alert alert-info">
                <strong><i class="fas fa-info-circle"></i> Résumé</strong>
                <br>
                <strong>{{ $orders->total() }}</strong> commande(s) disponible(s)
                @if($orders->count() > 0)
                    <br>
                    Valeur totale: <strong>{{ number_format($orders->sum('total_price'), 3) }} TND</strong>
                @endif
            </div>
        </div>
        <div class="col-md-6">
            <div class="alert alert-warning">
                <strong><i class="fas fa-exclamation-triangle"></i> Important</strong>
                <br>
                Seules les commandes <strong>confirmées</strong> et <strong>non assignées</strong> 
                à un enlèvement sont affichées.
            </div>
        </div>
    </div>

@else
    <div class="text-center py-5">
        <i class="fas fa-inbox fa-3x text-gray-300 mb-3"></i>
        <h5 class="text-gray-600">Aucune commande disponible</h5>
        <p class="text-muted">
            @if(request()->hasAny(['date_from', 'date_to', 'min_amount', 'max_amount', 'search']))
                Aucune commande ne correspond à vos critères de recherche.
                <br>
                <button type="button" class="btn btn-outline-secondary btn-sm mt-2" onclick="clearFilters()">
                    <i class="fas fa-times"></i> Effacer les filtres
                </button>
            @else
                Il n'y a actuellement aucune commande confirmée disponible pour un enlèvement.
                <br>
                <small class="text-muted">
                    Les commandes doivent être au statut "confirmée" et ne pas être déjà assignées à un enlèvement.
                </small>
            @endif
        </p>
    </div>
@endif

<script>
// Attacher les événements pour cette table
$(document).ready(function() {
    // Gestion du checkbox "select all" dans la table
    $('#selectAllTableCheckbox').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.order-checkbox').prop('checked', isChecked);
        
        // Mettre à jour la liste des commandes sélectionnées
        if (typeof updateSelectedOrders === 'function') {
            updateSelectedOrders();
        }
    });
    
    // Gestion des checkboxes individuels
    $('.order-checkbox').on('change', function() {
        // Vérifier si tous les checkboxes sont cochés
        const totalCheckboxes = $('.order-checkbox').length;
        const checkedCheckboxes = $('.order-checkbox:checked').length;
        
        $('#selectAllTableCheckbox').prop('checked', totalCheckboxes === checkedCheckboxes);
        
        // Mettre à jour la liste des commandes sélectionnées
        if (typeof updateSelectedOrders === 'function') {
            updateSelectedOrders();
        }
    });
});

function clearFilters() {
    $('#date_from').val('');
    $('#date_to').val('');
    $('#min_amount').val('');
    $('#max_amount').val('');
    $('#search').val('');
    
    if (typeof loadOrders === 'function') {
        loadOrders();
    }
}
</script>