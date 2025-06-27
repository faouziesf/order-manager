@extends('layouts.admin')

@section('title', 'Gestion des Clients')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header moderne -->
    <div class="mb-8">
        <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-2xl p-8 text-white shadow-2xl relative overflow-hidden">
            <div class="absolute inset-0 bg-black opacity-20"></div>
            <div class="absolute -top-10 -right-10 w-40 h-40 bg-white opacity-10 rounded-full"></div>
            <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-white opacity-10 rounded-full"></div>
            
            <div class="relative z-10">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-4xl font-bold mb-2 flex items-center">
                            <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center mr-4">
                                <i class="fas fa-users text-2xl"></i>
                            </div>
                            Gestion des Clients
                        </h1>
                        <p class="text-lg opacity-90">Vue d'ensemble de tous vos clients et leurs commandes</p>
                    </div>
                    <div class="text-right">
                        <div class="text-3xl font-bold">{{ number_format($stats['total_clients']) }}</div>
                        <div class="text-sm opacity-80">Clients total</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100 hover:shadow-xl transition-all duration-300 hover:scale-105">
            <div class="flex items-center">
                <div class="w-14 h-14 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-white text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Clients</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['total_clients']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100 hover:shadow-xl transition-all duration-300 hover:scale-105">
            <div class="flex items-center">
                <div class="w-14 h-14 bg-gradient-to-r from-green-500 to-green-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-shopping-bag text-white text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Commandes</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['total_orders']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100 hover:shadow-xl transition-all duration-300 hover:scale-105">
            <div class="flex items-center">
                <div class="w-14 h-14 bg-gradient-to-r from-amber-500 to-amber-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-copy text-white text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Clients Multiples</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['clients_with_multiple']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100 hover:shadow-xl transition-all duration-300 hover:scale-105">
            <div class="flex items-center">
                <div class="w-14 h-14 bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-coins text-white text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">CA Total</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['total_revenue'], 0) }}</p>
                    <p class="text-xs text-gray-500">TND</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres et recherche -->
    <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100 mb-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end">
            <div class="lg:col-span-5">
                <label class="block text-sm font-medium text-gray-700 mb-2">Recherche</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" id="searchInput" placeholder="Rechercher par téléphone ou nom..." 
                           class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200">
                </div>
            </div>
            <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Min commandes</label>
                <select id="minOrders" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="">Toutes</option>
                    <option value="2">2+ commandes</option>
                    <option value="3">3+ commandes</option>
                    <option value="5">5+ commandes</option>
                    <option value="10">10+ commandes</option>
                </select>
            </div>
            <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Trier par</label>
                <select id="sortField" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="latest_order_date">Plus récent</option>
                    <option value="total_orders">Nb commandes</option>
                    <option value="total_amount">Montant total</option>
                    <option value="customer_name">Nom</option>
                </select>
            </div>
            <div class="lg:col-span-3">
                <div class="flex space-x-2">
                    <button id="refreshBtn" class="flex-1 px-6 py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white rounded-xl hover:from-indigo-700 hover:to-indigo-800 transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl">
                        <i class="fas fa-sync-alt"></i>
                        <span>Actualiser</span>
                    </button>
                    <button class="px-4 py-3 bg-gray-100 text-gray-600 rounded-xl hover:bg-gray-200 transition-all duration-200">
                        <i class="fas fa-filter"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des clients moderne -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900">Liste des Clients</h2>
                <p class="text-sm text-gray-600">Cliquez sur un client pour voir le détail de ses commandes</p>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Commandes</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CA Total</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dernière commande</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statuts</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody id="clientsTableBody" class="bg-white divide-y divide-gray-200">
                    <!-- Contenu chargé via JavaScript -->
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            <div id="paginationContainer" class="flex items-center justify-between">
                <!-- Pagination générée via JavaScript -->
            </div>
        </div>
    </div>
</div>

<!-- Loading overlay -->
<div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-8 text-center shadow-2xl">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mx-auto mb-4"></div>
        <p class="text-gray-600 font-medium">Chargement...</p>
    </div>
</div>

<script>
let currentPage = 1;
let searchTimeout = null;

document.addEventListener('DOMContentLoaded', function() {
    loadClients();
    
    // Event listeners
    document.getElementById('searchInput').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => loadClients(1), 300);
    });
    
    document.getElementById('minOrders').addEventListener('change', () => loadClients(1));
    document.getElementById('sortField').addEventListener('change', () => loadClients(1));
    document.getElementById('refreshBtn').addEventListener('click', () => loadClients(currentPage));
});

function loadClients(page = 1) {
    showLoading();
    
    const params = new URLSearchParams({
        page: page,
        search: document.getElementById('searchInput').value,
        min_orders: document.getElementById('minOrders').value,
        sort: document.getElementById('sortField').value,
        per_page: 20
    });
    
    fetch(`{{ route('admin.duplicates.clients') }}?${params}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        renderClients(data.clients);
        renderPagination(data.clients);
        currentPage = page;
        hideLoading();
    })
    .catch(error => {
        console.error('Erreur:', error);
        hideLoading();
        showError('Erreur lors du chargement des données');
    });
}

function renderClients(clientsData) {
    const tbody = document.getElementById('clientsTableBody');
    tbody.innerHTML = '';
    
    if (clientsData.data.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                    <div class="flex flex-col items-center">
                        <i class="fas fa-users text-4xl text-gray-300 mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun client trouvé</h3>
                        <p class="text-sm">Essayez de modifier vos filtres de recherche</p>
                    </div>
                </td>
            </tr>
        `;
        return;
    }
    
    clientsData.data.forEach(client => {
        const hasMultiple = client.total_orders > 1;
        const statusBadges = client.statuses ? client.statuses.split(',').map(status => 
            `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${getStatusColor(status.trim())}">${status.trim()}</span>`
        ).join(' ') : '';
        
        const row = document.createElement('tr');
        row.className = 'hover:bg-gray-50 transition-colors duration-200 cursor-pointer';
        row.onclick = () => viewClientDetail(client.customer_phone);
        row.innerHTML = `
            <td class="px-6 py-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0 h-12 w-12">
                        <div class="h-12 w-12 rounded-full bg-gradient-to-r from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-lg">
                            ${(client.customer_name || client.customer_phone).charAt(0).toUpperCase()}
                        </div>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">${client.customer_name || 'Nom non spécifié'}</div>
                        <div class="text-sm text-gray-500 flex items-center">
                            <i class="fas fa-phone w-4 h-4 mr-1"></i>
                            ${client.customer_phone}
                        </div>
                        ${hasMultiple ? '<div class="mt-1"><span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Commandes multiples</span></div>' : ''}
                    </div>
                </div>
            </td>
            <td class="px-6 py-4">
                <div class="flex items-center">
                    <span class="inline-flex items-center justify-center px-3 py-1 rounded-full text-sm font-bold bg-indigo-100 text-indigo-800">
                        ${client.total_orders}
                    </span>
                </div>
            </td>
            <td class="px-6 py-4">
                <div class="text-sm font-bold text-gray-900">${parseFloat(client.total_amount).toFixed(3)} TND</div>
                <div class="text-sm text-gray-500">Moy: ${(parseFloat(client.total_amount) / client.total_orders).toFixed(2)} TND</div>
            </td>
            <td class="px-6 py-4">
                <div class="text-sm text-gray-900">${formatDate(client.latest_order_date)}</div>
                <div class="text-sm text-gray-500">1ère: ${formatDate(client.first_order_date)}</div>
            </td>
            <td class="px-6 py-4">
                <div class="flex flex-wrap gap-1">
                    ${statusBadges}
                </div>
            </td>
            <td class="px-6 py-4">
                <button onclick="event.stopPropagation(); viewClientDetail('${client.customer_phone}')" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 transition-all duration-200 shadow-md hover:shadow-lg">
                    <i class="fas fa-eye w-4 h-4 mr-2"></i>
                    Voir détails
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function renderPagination(clientsData) {
    const container = document.getElementById('paginationContainer');
    
    if (clientsData.last_page <= 1) {
        container.innerHTML = `<div class="text-sm text-gray-700">Total: ${clientsData.total} client(s)</div>`;
        return;
    }
    
    let pagination = `
        <div class="text-sm text-gray-700">
            Affichage ${clientsData.from} à ${clientsData.to} sur ${clientsData.total} résultats
        </div>
        <div class="flex items-center space-x-2">
    `;
    
    if (clientsData.current_page > 1) {
        pagination += `<button onclick="loadClients(${clientsData.current_page - 1})" class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors duration-200">Précédent</button>`;
    }
    
    for (let i = Math.max(1, clientsData.current_page - 2); i <= Math.min(clientsData.last_page, clientsData.current_page + 2); i++) {
        const active = i === clientsData.current_page ? 'bg-indigo-600 text-white' : 'border border-gray-300 hover:bg-gray-50';
        pagination += `<button onclick="loadClients(${i})" class="px-3 py-2 rounded-lg transition-colors duration-200 ${active}">${i}</button>`;
    }
    
    if (clientsData.current_page < clientsData.last_page) {
        pagination += `<button onclick="loadClients(${clientsData.current_page + 1})" class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors duration-200">Suivant</button>`;
    }
    
    pagination += '</div>';
    container.innerHTML = pagination;
}

function getStatusColor(status) {
    const colors = {
        'nouvelle': 'bg-blue-100 text-blue-800',
        'confirmée': 'bg-green-100 text-green-800',
        'annulée': 'bg-red-100 text-red-800',
        'datée': 'bg-yellow-100 text-yellow-800',
        'livrée': 'bg-emerald-100 text-emerald-800',
        'en_route': 'bg-purple-100 text-purple-800'
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('fr-FR');
}

function viewClientDetail(phone) {
    window.location.href = `{{ route('admin.duplicates.detail', ':phone') }}`.replace(':phone', encodeURIComponent(phone));
}

function showLoading() {
    document.getElementById('loadingOverlay').classList.remove('hidden');
    document.getElementById('loadingOverlay').classList.add('flex');
}

function hideLoading() {
    document.getElementById('loadingOverlay').classList.add('hidden');
    document.getElementById('loadingOverlay').classList.remove('flex');
}

function showError(message) {
    // Vous pouvez implémenter un système de notification ici
    alert(message);
}
</script>
@endsection