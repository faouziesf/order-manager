@if($shipments->isNotEmpty())
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Commande</th>
                    <th>Client</th>
                    <th>Code suivi</th>
                    <th>Transporteur</th>
                    <th>Montant</th>
                    <th>Statut</th>
                    <th>Date création</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($shipments as $shipment)
                    <tr style="cursor: pointer;" onclick="viewShipment({{ $shipment->id }})">
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-shopping-cart text-primary me-2"></i>
                                <div>
                                    <strong>#{{ $shipment->order_id }}</strong>
                                    @if($shipment->order_number)
                                        <br><small class="text-muted">{{ $shipment->order_number }}</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <div>
                                <strong>{{ $shipment->customer_name ?: 'N/A' }}</strong>
                                @if($shipment->customer_phone)
                                    <br><small class="font-monospace text-muted">{{ $shipment->customer_phone }}</small>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($shipment->pos_barcode)
                                <div>
                                    <code class="bg-light px-2 py-1 rounded">{{ $shipment->pos_barcode }}</code>
                                    @if($shipment->tracking_url)
                                        <br><a href="{{ $shipment->tracking_url }}" target="_blank" class="small text-primary">
                                            <i class="fas fa-external-link-alt me-1"></i>Suivre
                                        </a>
                                    @endif
                                </div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($shipment->pickup && $shipment->pickup->deliveryConfiguration)
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-truck text-info me-2"></i>
                                    <div>
                                        <span class="badge bg-info">{{ ucfirst($shipment->pickup->carrier_slug) }}</span>
                                        <br><small class="text-muted">{{ $shipment->pickup->deliveryConfiguration->integration_name }}</small>
                                    </div>
                                </div>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            <strong class="text-primary">{{ number_format($shipment->value ?? 0, 3) }} DT</strong>
                            @if($shipment->cod_amount && $shipment->cod_amount != $shipment->value)
                                <br><small class="text-muted">COD: {{ number_format($shipment->cod_amount, 3) }} DT</small>
                            @endif
                        </td>
                        <td>
                            @php
                                $statusConfig = [
                                    'created' => ['badge' => 'bg-primary', 'icon' => 'fas fa-plus-circle', 'label' => 'Créée'],
                                    'validated' => ['badge' => 'bg-info', 'icon' => 'fas fa-check-circle', 'label' => 'Validée'],
                                    'picked_up_by_carrier' => ['badge' => 'bg-warning', 'icon' => 'fas fa-truck', 'label' => 'Récupérée'],
                                    'in_transit' => ['badge' => 'bg-info', 'icon' => 'fas fa-road', 'label' => 'En transit'],
                                    'delivered' => ['badge' => 'bg-success', 'icon' => 'fas fa-check-circle', 'label' => 'Livrée'],
                                    'in_return' => ['badge' => 'bg-warning', 'icon' => 'fas fa-undo', 'label' => 'En retour'],
                                    'anomaly' => ['badge' => 'bg-danger', 'icon' => 'fas fa-exclamation-triangle', 'label' => 'Anomalie'],
                                    'cancelled' => ['badge' => 'bg-secondary', 'icon' => 'fas fa-times-circle', 'label' => 'Annulée']
                                ];
                                $config = $statusConfig[$shipment->status] ?? ['badge' => 'bg-secondary', 'icon' => 'fas fa-question', 'label' => ucfirst($shipment->status)];
                            @endphp
                            <span class="badge {{ $config['badge'] }}">
                                <i class="{{ $config['icon'] }} me-1"></i>{{ $config['label'] }}
                            </span>
                            
                            @if($shipment->delivered_at)
                                <br><small class="text-success">{{ $shipment->delivered_at->format('d/m/Y H:i') }}</small>
                            @elseif($shipment->carrier_last_status_update)
                                <br><small class="text-muted">MAJ: {{ $shipment->carrier_last_status_update->format('d/m H:i') }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="text-muted">{{ $shipment->created_at->format('d/m/Y H:i') }}</span>
                            @if($shipment->pickup)
                                <br><small class="text-muted">
                                    <a href="{{ route('admin.delivery.pickups.show', $shipment->pickup) }}" class="text-decoration-none">
                                        Enlèvement #{{ $shipment->pickup_id }}
                                    </a>
                                </small>
                            @endif
                        </td>
                        <td onclick="event.stopPropagation();">
                            <div class="btn-group btn-group-sm">
                                <!-- Suivi -->
                                <button type="button" class="btn btn-outline-primary" 
                                        onclick="trackShipment({{ $shipment->id }})" 
                                        title="Actualiser le suivi">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                                
                                <!-- Voir détails -->
                                <button type="button" class="btn btn-outline-info" 
                                        onclick="viewShipment({{ $shipment->id }})" 
                                        title="Voir les détails">
                                    <i class="fas fa-eye"></i>
                                </button>
                                
                                <!-- Marquer comme livré (si pas encore livré) -->
                                @if($shipment->status !== 'delivered' && $shipment->status !== 'cancelled')
                                    <button type="button" class="btn btn-outline-success" 
                                            onclick="markAsDelivered({{ $shipment->id }})" 
                                            title="Marquer comme livré">
                                        <i class="fas fa-check"></i>
                                    </button>
                                @endif
                                
                                <!-- Lien vers la commande -->
                                @if($shipment->order)
                                    <a href="{{ route('admin.orders.show', $shipment->order) }}" 
                                       class="btn btn-outline-secondary" 
                                       target="_blank"
                                       title="Voir la commande">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="text-center py-5">
        <i class="fas fa-shipping-fast fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">Aucune expédition trouvée</h5>
        <p class="text-muted">Aucune expédition ne correspond aux critères de recherche</p>
        <button type="button" class="btn btn-outline-secondary" onclick="clearFilters()">
            <i class="fas fa-times me-2"></i>Effacer les filtres
        </button>
    </div>
@endif