@if($pickups->isNotEmpty())
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Transporteur</th>
                    <th>Adresse d'enlèvement</th>
                    <th>Expéditions</th>
                    <th>Statut</th>
                    <th>Date de création</th>
                    <th>Dernière MAJ</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pickups as $pickup)
                    <tr data-pickup-id="{{ $pickup->id }}" style="cursor: pointer;" onclick="viewPickup({{ $pickup->id }})">
                        <td>
                            <strong>#{{ $pickup->id }}</strong>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-truck text-primary me-2"></i>
                                <div>
                                    <strong>{{ $pickup->carrier_display_name }}</strong>
                                    <br><small class="text-muted">{{ $pickup->deliveryConfiguration->integration_name }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($pickup->pickupAddress)
                                <div>
                                    <strong>{{ $pickup->pickupAddress->name }}</strong>
                                    <br><small class="text-muted">{{ $pickup->pickupAddress->contact_name }}</small>
                                </div>
                            @else
                                <span class="text-muted">Adresse par défaut</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-boxes me-2"></i>
                                <div>
                                    <strong>{{ $pickup->shipment_count }}</strong> expédition(s)
                                    @if($pickup->validated_shipments_count > 0)
                                        <br><small class="text-success">{{ $pickup->validated_shipments_count }} validée(s)</small>
                                    @endif
                                    @if($pickup->delivered_shipments_count > 0)
                                        <br><small class="text-info">{{ $pickup->delivered_shipments_count }} livrée(s)</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge {{ $pickup->status_badge_class }}">
                                {{ $pickup->status_label }}
                            </span>
                            @if($pickup->status === 'validated' && $pickup->days_in_current_status > 1)
                                <br><small class="text-warning">
                                    <i class="fas fa-clock me-1"></i>{{ $pickup->days_in_current_status }} jour(s)
                                </small>
                            @endif
                            @if($pickup->status === 'problem')
                                <br><small class="text-danger">
                                    <i class="fas fa-exclamation-triangle me-1"></i>Attention requise
                                </small>
                            @endif
                        </td>
                        <td>
                            <span class="text-muted">{{ $pickup->created_at->format('d/m/Y H:i') }}</span>
                        </td>
                        <td>
                            <span class="text-muted">{{ $pickup->updated_at->format('d/m/Y H:i') }}</span>
                        </td>
                        <td onclick="event.stopPropagation();">
                            <div class="btn-group btn-group-sm">
                                @if($pickup->status === 'draft')
                                    @can('validate', $pickup)
                                        <button type="button" class="btn btn-outline-success" 
                                                onclick="validatePickup({{ $pickup->id }})" 
                                                title="Valider l'enlèvement">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    @endcan
                                @endif

                                @if($pickup->status === 'validated' || $pickup->status === 'picked_up')
                                    @can('generateLabels', $pickup)
                                        <button type="button" class="btn btn-outline-primary" 
                                                onclick="generateLabels({{ $pickup->id }})" 
                                                title="Générer les étiquettes">
                                            <i class="fas fa-tags"></i>
                                        </button>
                                    @endcan

                                    @can('generateManifest', $pickup)
                                        <button type="button" class="btn btn-outline-info" 
                                                onclick="generateManifest({{ $pickup->id }})" 
                                                title="Générer le manifeste">
                                            <i class="fas fa-file-pdf"></i>
                                        </button>
                                    @endcan

                                    @can('refreshStatus', $pickup)
                                        <button type="button" class="btn btn-outline-warning" 
                                                onclick="refreshPickupStatus({{ $pickup->id }})" 
                                                title="Rafraîchir le statut">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    @endcan
                                @endif

                                <!-- Bouton de visualisation -->
                                <button type="button" class="btn btn-outline-secondary" 
                                        onclick="viewPickup({{ $pickup->id }})" 
                                        title="Voir les détails">
                                    <i class="fas fa-eye"></i>
                                </button>

                                @if($pickup->status === 'draft')
                                    @can('delete', $pickup)
                                        <button type="button" class="btn btn-outline-danger" 
                                                onclick="deletePickup({{ $pickup->id }})" 
                                                title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($pickups->hasPages())
        <div class="d-flex justify-content-center mt-3">
            {{ $pickups->links() }}
        </div>
    @endif
@else
    <div class="text-center py-5">
        <i class="fas fa-warehouse fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">Aucun enlèvement trouvé</h5>
        <p class="text-muted">Aucun enlèvement ne correspond aux critères de recherche</p>
        <button type="button" class="btn btn-outline-secondary" onclick="clearFilters()">
            <i class="fas fa-times me-2"></i>Effacer les filtres
        </button>
    </div>
@endif