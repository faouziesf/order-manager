@if($addresses->isNotEmpty())
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Contact</th>
                    <th>Adresse</th>
                    <th>Téléphone</th>
                    <th>Email</th>
                    <th>Statut</th>
                    <th>Utilisation</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($addresses as $address)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                <div>
                                    <strong>{{ $address->name }}</strong>
                                    @if($address->is_default)
                                        <span class="badge bg-warning ms-2">
                                            <i class="fas fa-star me-1"></i>Défaut
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <div>
                                <strong>{{ $address->contact_name }}</strong>
                            </div>
                        </td>
                        <td>
                            <div>
                                {{ $address->address }}
                                @if($address->city || $address->postal_code)
                                    <br>
                                    <small class="text-muted">
                                        {{ $address->city }}
                                        @if($address->postal_code)
                                            {{ $address->postal_code }}
                                        @endif
                                    </small>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="font-monospace">{{ $address->phone }}</span>
                            <a href="tel:{{ $address->phone }}" class="btn btn-sm btn-outline-success ms-1" title="Appeler">
                                <i class="fas fa-phone"></i>
                            </a>
                        </td>
                        <td>
                            @if($address->email)
                                <a href="mailto:{{ $address->email }}" class="text-decoration-none">
                                    {{ $address->email }}
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($address->is_active)
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle me-1"></i>Active
                                </span>
                            @else
                                <span class="badge bg-secondary">
                                    <i class="fas fa-pause-circle me-1"></i>Inactive
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="text-center">
                                @php
                                    $pickupsCount = $address->pickups()->count();
                                    $lastPickup = $address->pickups()->latest()->first();
                                @endphp
                                <div class="fw-bold text-primary">{{ $pickupsCount }}</div>
                                <small class="text-muted">enlèvement(s)</small>
                                @if($lastPickup)
                                    <br><small class="text-muted">
                                        Dernier: {{ $lastPickup->created_at->diffForHumans() }}
                                    </small>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                @if(!$address->is_default && $address->is_active)
                                    <button type="button" class="btn btn-outline-warning" 
                                            onclick="setAsDefault({{ $address->id }})"
                                            title="Définir par défaut">
                                        <i class="fas fa-star"></i>
                                    </button>
                                @endif
                                
                                <a href="{{ route('admin.delivery.pickup-addresses.edit', $address) }}" 
                                   class="btn btn-outline-primary"
                                   title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                <button type="button" class="btn btn-outline-{{ $address->is_active ? 'secondary' : 'success' }}"
                                        onclick="toggleStatus({{ $address->id }})"
                                        title="{{ $address->is_active ? 'Désactiver' : 'Activer' }}">
                                    <i class="fas fa-{{ $address->is_active ? 'eye-slash' : 'eye' }}"></i>
                                </button>
                                
                                @if($address->canBeDeleted())
                                    <button type="button" class="btn btn-outline-danger"
                                            onclick="deleteAddress({{ $address->id }})"
                                            title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @else
                                    <button type="button" class="btn btn-outline-danger" 
                                            disabled
                                            title="Impossible de supprimer - adresse utilisée">
                                        <i class="fas fa-lock"></i>
                                    </button>
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
        <i class="fas fa-map-marker-alt fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">Aucune adresse d'enlèvement</h5>
        <p class="text-muted">Créez votre première adresse d'enlèvement pour commencer</p>
        <a href="{{ route('admin.delivery.pickup-addresses.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Créer une adresse
        </a>
    </div>
@endif