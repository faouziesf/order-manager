@if($orders->isNotEmpty())
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th width="50">
                        <input type="checkbox" class="form-check-input" id="selectAll" onchange="toggleAllOrders()">
                    </th>
                    <th>Commande</th>
                    <th>Client</th>
                    <th>Téléphone</th>
                    <th>Adresse de livraison</th>
                    <th>Montant</th>
                    <th>Date création</th>
                    <th>Priorité</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                    <tr onclick="toggleOrderSelection({{ $order->id }})" style="cursor: pointer;">
                        <td onclick="event.stopPropagation();">
                            <input type="checkbox" 
                                   class="form-check-input order-checkbox" 
                                   data-order-id="{{ $order->id }}"
                                   onchange="updateSelection()">
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-shopping-cart text-primary me-2"></i>
                                <div>
                                    <strong>#{{ $order->id }}</strong>
                                    @if($order->order_number)
                                        <br><small class="text-muted">{{ $order->order_number }}</small>
                                    @endif
                                    <br><small class="text-info">{{ $order->items_count }} article(s)</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div>
                                <strong>{{ $order->customer_name ?: 'Client non renseigné' }}</strong>
                                @if($order->employee)
                                    <br><small class="text-muted">
                                        <i class="fas fa-user me-1"></i>{{ $order->employee->name }}
                                    </small>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($order->customer_phone)
                                <span class="font-monospace">{{ $order->customer_phone }}</span>
                                @if($order->customer_phone_2)
                                    <br><small class="font-monospace text-muted">{{ $order->customer_phone_2 }}</small>
                                @endif
                            @else
                                <span class="text-muted">Non renseigné</span>
                            @endif
                        </td>
                        <td>
                            @if($order->customer_address)
                                <div title="{{ $order->customer_address }}">
                                    {{ Str::limit($order->customer_address, 40) }}
                                </div>
                                @if($order->city)
                                    <small class="text-muted">
                                        <i class="fas fa-map-marker-alt me-1"></i>{{ $order->city->name ?? $order->customer_city }}
                                    </small>
                                @endif
                            @else
                                <span class="text-muted">Adresse non renseignée</span>
                            @endif
                        </td>
                        <td>
                            <div>
                                <strong class="text-primary">{{ number_format($order->total_price, 3) }} DT</strong>
                                @if($order->confirmed_price && $order->confirmed_price != $order->total_price)
                                    <br><small class="text-success">Confirmé: {{ number_format($order->confirmed_price, 3) }} DT</small>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="text-muted">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                            <br><small class="text-muted">{{ $order->created_at->diffForHumans() }}</small>
                        </td>
                        <td>
                            @php
                                $priorityConfig = [
                                    'normale' => ['badge' => 'bg-secondary', 'icon' => 'fas fa-circle', 'label' => 'Normale'],
                                    'urgente' => ['badge' => 'bg-warning', 'icon' => 'fas fa-exclamation-circle', 'label' => 'Urgente'],
                                    'vip' => ['badge' => 'bg-danger', 'icon' => 'fas fa-star', 'label' => 'VIP']
                                ];
                                $config = $priorityConfig[$order->priority] ?? $priorityConfig['normale'];
                            @endphp
                            <span class="badge {{ $config['badge'] }}">
                                <i class="{{ $config['icon'] }} me-1"></i>{{ $config['label'] }}
                            </span>
                        </td>
                        <td onclick="event.stopPropagation();">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.orders.show', $order) }}" 
                                   class="btn btn-outline-info" 
                                   target="_blank"
                                   title="Voir la commande">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                <button type="button" class="btn btn-outline-primary" 
                                        onclick="viewOrderDetails({{ $order->id }})"
                                        title="Détails rapides">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                                
                                @if($order->customer_phone)
                                    <a href="tel:{{ $order->customer_phone }}" 
                                       class="btn btn-outline-success"
                                       title="Appeler le client">
                                        <i class="fas fa-phone"></i>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Actions groupées -->
    <div class="d-flex justify-content-between align-items-center mt-3">
        <div>
            <span id="selectedCount">0</span> commande(s) sélectionnée(s)
        </div>
        <div class="btn-group">
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="selectAllVisible()">
                <i class="fas fa-check-square me-1"></i>Tout sélectionner
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="deselectAll()">
                <i class="fas fa-square me-1"></i>Tout désélectionner
            </button>
            <button type="button" class="btn btn-primary" id="createPickupBtn" onclick="showCreatePickupModal()" disabled>
                <i class="fas fa-plus me-2"></i>Créer l'enlèvement
            </button>
        </div>
    </div>

    @if($orders->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $orders->links() }}
        </div>
    @endif
@else
    <div class="text-center py-5">
        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">Aucune commande disponible</h5>
        <p class="text-muted">
            Aucune commande confirmée n'est disponible pour la préparation d'enlèvement.
            <br>Les commandes apparaîtront ici une fois qu'elles seront confirmées.
        </p>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-eye me-2"></i>Voir toutes les commandes
        </a>
    </div>
@endif

<!-- Modal Détails de commande -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-shopping-cart me-2"></i>
                    <span id="orderDetailsTitle">Détails de la commande</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="orderDetailsContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary" id="addToSelectionBtn" onclick="addOrderToSelection()">
                    <i class="fas fa-plus me-2"></i>Ajouter à la sélection
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let selectedOrders = new Set();
let currentOrderDetails = null;

function toggleOrderSelection(orderId) {
    const checkbox = document.querySelector(`input[data-order-id="${orderId}"]`);
    if (checkbox) {
        checkbox.checked = !checkbox.checked;
        updateSelection();
    }
}

function toggleAllOrders() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.order-checkbox');
    
    checkboxes.forEach(cb => {
        cb.checked = selectAll.checked;
    });
    
    updateSelection();
}

function selectAllVisible() {
    document.querySelectorAll('.order-checkbox').forEach(cb => {
        cb.checked = true;
    });
    updateSelection();
}

function deselectAll() {
    document.querySelectorAll('.order-checkbox').forEach(cb => {
        cb.checked = false;
    });
    document.getElementById('selectAll').checked = false;
    updateSelection();
}

function updateSelection() {
    selectedOrders.clear();
    
    document.querySelectorAll('.order-checkbox:checked').forEach(cb => {
        selectedOrders.add(parseInt(cb.getAttribute('data-order-id')));
    });
    
    // Mettre à jour l'affichage
    document.getElementById('selectedCount').textContent = selectedOrders.size;
    document.getElementById('createPickupBtn').disabled = selectedOrders.size === 0;
    
    // Mettre à jour le checkbox "Tout sélectionner"
    const allCheckboxes = document.querySelectorAll('.order-checkbox');
    const checkedCheckboxes = document.querySelectorAll('.order-checkbox:checked');
    const selectAllCheckbox = document.getElementById('selectAll');
    
    if (checkedCheckboxes.length === 0) {
        selectAllCheckbox.indeterminate = false;
        selectAllCheckbox.checked = false;
    } else if (checkedCheckboxes.length === allCheckboxes.length) {
        selectAllCheckbox.indeterminate = false;
        selectAllCheckbox.checked = true;
    } else {
        selectAllCheckbox.indeterminate = true;
    }
}

function viewOrderDetails(orderId) {
    currentOrderDetails = orderId;
    
    document.getElementById('orderDetailsTitle').textContent = `Commande #${orderId}`;
    document.getElementById('orderDetailsContent').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Chargement...</span>
            </div>
        </div>
    `;
    
    new bootstrap.Modal(document.getElementById('orderDetailsModal')).show();
    
    // Charger les détails via AJAX
    fetch(`/admin/orders/${orderId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        // Extraire juste le contenu principal sans le layout
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const content = doc.querySelector('.card-body') || doc.querySelector('main') || doc.body;
        
        document.getElementById('orderDetailsContent').innerHTML = content.innerHTML;
        
        // Mettre à jour le bouton d'ajout
        const addBtn = document.getElementById('addToSelectionBtn');
        const isSelected = selectedOrders.has(orderId);
        addBtn.innerHTML = isSelected 
            ? '<i class="fas fa-minus me-2"></i>Retirer de la sélection'
            : '<i class="fas fa-plus me-2"></i>Ajouter à la sélection';
    })
    .catch(error => {
        console.error('Erreur:', error);
        document.getElementById('orderDetailsContent').innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Erreur lors du chargement des détails de la commande
            </div>
        `;
    });
}

function addOrderToSelection() {
    if (!currentOrderDetails) return;
    
    const checkbox = document.querySelector(`input[data-order-id="${currentOrderDetails}"]`);
    if (checkbox) {
        checkbox.checked = !checkbox.checked;
        updateSelection();
        
        // Mettre à jour le bouton
        const addBtn = document.getElementById('addToSelectionBtn');
        const isSelected = selectedOrders.has(currentOrderDetails);
        addBtn.innerHTML = isSelected 
            ? '<i class="fas fa-minus me-2"></i>Retirer de la sélection'
            : '<i class="fas fa-plus me-2"></i>Ajouter à la sélection';
    }
}

function showCreatePickupModal() {
    if (selectedOrders.size === 0) {
        alert('Veuillez sélectionner au moins une commande');
        return;
    }
    
    // Déclencher l'événement pour ouvrir le modal de création d'enlèvement
    window.parent.postMessage({
        type: 'showCreatePickupModal',
        orderIds: Array.from(selectedOrders)
    }, '*');
}

// Écouter les messages du parent
window.addEventListener('message', function(event) {
    if (event.data.type === 'pickupCreated') {
        // Recharger la liste des commandes après création d'un enlèvement
        location.reload();
    }
});

// Initialisation
updateSelection();
</script>