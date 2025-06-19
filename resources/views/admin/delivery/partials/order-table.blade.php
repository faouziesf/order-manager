@if($orders->isNotEmpty())
    <div class="table-responsive">
        <table class="table table-sm table-hover">
            <thead>
                <tr>
                    <th width="50">
                        <input type="checkbox" class="form-check-input" id="selectAllOrders" onchange="toggleAllOrders()">
                    </th>
                    <th>Commande</th>
                    <th>Client</th>
                    <th>Téléphone</th>
                    <th>Adresse</th>
                    <th>Montant</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                    <tr onclick="toggleOrderSelection({{ $order->id }})">
                        <td>
                            <input type="checkbox" class="form-check-input order-checkbox" 
                                   data-order-id="{{ $order->id }}"
                                   onchange="updateOrderSelection()">
                        </td>
                        <td>
                            <strong>#{{ $order->id }}</strong>
                        </td>
                        <td>
                            <strong>{{ $order->customer_name ?: 'N/A' }}</strong>
                        </td>
                        <td>
                            <span class="font-monospace">{{ $order->customer_phone ?: 'N/A' }}</span>
                        </td>
                        <td>
                            <small>{{ Str::limit($order->customer_address ?: 'N/A', 40) }}</small>
                        </td>
                        <td>
                            <strong>{{ number_format($order->total_price, 3) }} DT</strong>
                        </td>
                        <td>
                            <small>{{ $order->created_at->format('d/m/Y H:i') }}</small>
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

    <div class="d-flex justify-content-between align-items-center mt-3">
        <span class="text-muted">
            {{ $orders->total() }} commande(s) disponible(s)
        </span>
        <div>
            <span id="selectedOrdersCount" class="badge bg-primary">0 sélectionnée(s)</span>
        </div>
    </div>
@else
    <div class="text-center py-5">
        <i class="fas fa-inbox fa-2x text-muted mb-3"></i>
        <h6 class="text-muted">Aucune commande disponible</h6>
        <p class="text-muted">Toutes les commandes confirmées ont déjà été assignées à un enlèvement.</p>
    </div>
@endif

<script>
let selectedOrders = new Set();

function toggleOrderSelection(orderId) {
    if (selectedOrders.has(orderId)) {
        selectedOrders.delete(orderId);
    } else {
        selectedOrders.add(orderId);
    }
    updateOrdersUI();
}

function toggleAllOrders() {
    const selectAllCheckbox = document.getElementById('selectAllOrders');
    const checkboxes = document.querySelectorAll('.order-checkbox');
    
    if (selectAllCheckbox.checked) {
        checkboxes.forEach(cb => {
            const orderId = parseInt(cb.getAttribute('data-order-id'));
            selectedOrders.add(orderId);
        });
    } else {
        selectedOrders.clear();
    }
    updateOrdersUI();
}

function updateOrderSelection() {
    const checkbox = event.target;
    const orderId = parseInt(checkbox.getAttribute('data-order-id'));
    
    if (checkbox.checked) {
        selectedOrders.add(orderId);
    } else {
        selectedOrders.delete(orderId);
    }
    updateOrdersUI();
}

function updateOrdersUI() {
    // Mettre à jour les checkboxes
    document.querySelectorAll('.order-checkbox').forEach(cb => {
        const orderId = parseInt(cb.getAttribute('data-order-id'));
        cb.checked = selectedOrders.has(orderId);
    });
    
    // Mettre à jour le compteur
    const countElement = document.getElementById('selectedOrdersCount');
    if (countElement) {
        countElement.textContent = `${selectedOrders.size} sélectionnée(s)`;
    }
    
    // Mettre à jour la checkbox "Tout sélectionner"
    const selectAllCheckbox = document.getElementById('selectAllOrders');
    if (selectAllCheckbox) {
        const totalCheckboxes = document.querySelectorAll('.order-checkbox').length;
        selectAllCheckbox.checked = selectedOrders.size === totalCheckboxes && totalCheckboxes > 0;
        selectAllCheckbox.indeterminate = selectedOrders.size > 0 && selectedOrders.size < totalCheckboxes;
    }
    
    // Émettre un événement pour les formulaires parents
    if (typeof window.updateSelectedOrders === 'function') {
        window.updateSelectedOrders(Array.from(selectedOrders));
    }
}

// Fonction pour récupérer les commandes sélectionnées
function getSelectedOrders() {
    return Array.from(selectedOrders);
}

// Fonction pour vider la sélection
function clearOrderSelection() {
    selectedOrders.clear();
    updateOrdersUI();
}
</script>