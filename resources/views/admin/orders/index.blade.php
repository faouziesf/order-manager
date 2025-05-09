@extends('layouts.admin')

@section('title', 'Gestion des Commandes')

@section('css')
<style>
    .search-form .form-control {
        border-radius: 0.25rem;
    }
    .orders-table th {
        font-size: 0.85rem;
        white-space: nowrap;
    }
    .status-badge {
        font-size: 0.85rem;
        padding: 0.25rem 0.5rem;
    }
    .status-nouvelle { background-color: #3498db; color: white; }
    .status-confirmée { background-color: #2ecc71; color: white; }
    .status-annulée { background-color: #e74c3c; color: white; }
    .status-datée { background-color: #f39c12; color: white; }
    .status-en_route { background-color: #9b59b6; color: white; }
    .status-livrée { background-color: #27ae60; color: white; }
    
    .priority-normale { background-color: #95a5a6; color: white; }
    .priority-urgente { background-color: #e67e22; color: white; }
    .priority-vip { background-color: #c0392b; color: white; }
    
    .search-container {
        position: relative;
    }
    
    .search-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #ddd;
        border-radius: 0.25rem;
        z-index: 1000;
        max-height: 300px;
        overflow-y: auto;
        display: none;
    }
    
    .search-results.show {
        display: block;
    }
    
    .search-result-item {
        padding: 0.5rem 1rem;
        border-bottom: 1px solid #eee;
        cursor: pointer;
    }
    
    .search-result-item:hover {
        background-color: #f8f9fa;
    }
    
    .stats-card {
        transition: all 0.3s;
    }
    
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Gestion des Commandes</h1>
        <a href="{{ route('admin.orders.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Nouvelle Commande
        </a>
    </div>
    
    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Commandes</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalOrders }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Nouvelles Commandes</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $newOrders }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-boxes fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Commandes Confirmées</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $confirmedOrders }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Commandes Datées</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $scheduledOrders }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recherche et filtres -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Rechercher des commandes</h6>
        </div>
        <div class="card-body">
            <form id="searchForm" action="{{ route('admin.orders.index') }}" method="GET" class="search-form">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="search-container">
                            <input type="text" class="form-control" id="searchInput" name="search" placeholder="Rechercher par nom, téléphone, adresse..." value="{{ request('search') }}">
                            <div class="search-results" id="searchResults"></div>
                        </div>
                    </div>
                    
                    <div class="col-md-2 mb-3">
                        <select class="form-control" name="status">
                            <option value="">Tous les statuts</option>
                            <option value="nouvelle" {{ request('status') == 'nouvelle' ? 'selected' : '' }}>Nouvelle</option>
                            <option value="confirmée" {{ request('status') == 'confirmée' ? 'selected' : '' }}>Confirmée</option>
                            <option value="annulée" {{ request('status') == 'annulée' ? 'selected' : '' }}>Annulée</option>
                            <option value="datée" {{ request('status') == 'datée' ? 'selected' : '' }}>Datée</option>
                            <option value="en_route" {{ request('status') == 'en_route' ? 'selected' : '' }}>En route</option>
                            <option value="livrée" {{ request('status') == 'livrée' ? 'selected' : '' }}>Livrée</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2 mb-3">
                        <select class="form-control" name="assigned">
                            <option value="">Assignation</option>
                            <option value="yes" {{ request('assigned') == 'yes' ? 'selected' : '' }}>Assignées</option>
                            <option value="no" {{ request('assigned') == 'no' ? 'selected' : '' }}>Non assignées</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2 mb-3">
                        <input type="date" class="form-control" name="date_from" placeholder="Date de début" value="{{ request('date_from') }}">
                    </div>
                    
                    <div class="col-md-2 mb-3">
                        <input type="date" class="form-control" name="date_to" placeholder="Date de fin" value="{{ request('date_to') }}">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12 text-right">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Rechercher
                        </button>
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Réinitialiser
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Liste des commandes -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Liste des Commandes</h6>
            <div>
                <button type="button" class="btn btn-sm btn-outline-primary" id="refreshButton">
                    <i class="fas fa-sync-alt"></i> Actualiser
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered orders-table" id="ordersTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Client</th>
                            <th>Téléphone</th>
                            <th>Adresse</th>
                            <th>Prix Total</th>
                            <th>Statut</th>
                            <th>Priorité</th>
                            <th>Tentatives</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr>
                            <td>{{ $order->id }}</td>
                            <td>{{ $order->customer_name ?? 'Non spécifié' }}</td>
                            <td>
                                <div>{{ $order->customer_phone }}</div>
                                @if($order->customer_phone_2)
                                <div class="text-muted small">{{ $order->customer_phone_2 }}</div>
                                @endif
                            </td>
                            <td>
                                @if($order->customer_governorate && $order->customer_city)
                                <div>{{ $order->region->name ?? '' }} - {{ $order->city->name ?? '' }}</div>
                                @endif
                                <div class="small">{{ $order->customer_address }}</div>
                            </td>
                            <td>{{ number_format($order->total_price, 3) }} DT</td>
                            <td>
                                <span class="badge status-{{ $order->status }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge priority-{{ $order->priority }}">
                                    {{ ucfirst($order->priority) }}
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-info history-btn" data-order-id="{{ $order->id }}">
                                    {{ $order->attempts_count }} <i class="fas fa-history"></i>
                                </button>
                            </td>
                            <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <a href="{{ route('admin.orders.edit', $order) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#historyModal" data-order-id="{{ $order->id }}">
                                        <i class="fas fa-history"></i>
                                    </button>
                                    <form action="{{ route('admin.orders.destroy', $order) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette commande?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center">Aucune commande trouvée</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Modal Historique -->
<div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="historyModalLabel">Historique des tentatives</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="historyModalBody">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Chargement...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Stocker les URL de base pour éviter les problèmes de paramètres manquants
        const baseUrls = {
            edit: "{{ url('admin/orders') }}/",
            history: "{{ url('admin/orders') }}/"
        };
        
        // Gestion de la recherche en temps réel
        let searchTimeout;
        
        $('#searchInput').on('input', function() {
            clearTimeout(searchTimeout);
            const query = $(this).val();
            
            if (query.length < 2) {
                $('#searchResults').removeClass('show').empty();
                return;
            }
            
            searchTimeout = setTimeout(function() {
                $.ajax({
                    url: "{{ route('admin.orders.index') }}",
                    data: { search: query, ajax: 1 },
                    success: function(data) {
                        if (data.orders && data.orders.length > 0) {
                            let resultsHtml = '';
                            
                            data.orders.forEach(function(order) {
                                resultsHtml += `
                                    <div class="search-result-item" data-id="${order.id}">
                                        <div><strong>#${order.id}</strong> - ${order.customer_name || 'Sans nom'}</div>
                                        <div class="small">${order.customer_phone} - ${order.status}</div>
                                    </div>
                                `;
                            });
                            
                            $('#searchResults').html(resultsHtml).addClass('show');
                        } else {
                            $('#searchResults').html('<div class="p-3">Aucun résultat trouvé</div>').addClass('show');
                        }
                    }
                });
            }, 300);
        });
        
        // Sélection d'un résultat de recherche - CORRIGÉ
        $(document).on('click', '.search-result-item', function() {
            const orderId = $(this).data('id');
            window.location.href = baseUrls.edit + orderId + "/edit";
        });
        
        // Cacher les résultats au clic ailleurs
        $(document).on('click', function(event) {
            if (!$(event.target).closest('.search-container').length) {
                $('#searchResults').removeClass('show');
            }
        });
        
        // Gestion du modal d'historique - CORRIGÉ
        $('#historyModal').on('show.bs.modal', function (event) {
            const button = $(event.relatedTarget);
            const orderId = button.data('order-id');
            const modal = $(this);
            
            // Mettre à jour le titre
            modal.find('.modal-title').text('Historique de la commande #' + orderId);
            
            // Charger l'historique avec URL construite manuellement
            $.ajax({
                url: baseUrls.history + orderId + "/history",
                success: function(data) {
                    modal.find('.modal-body').html(data);
                },
                error: function() {
                    modal.find('.modal-body').html('<div class="alert alert-danger">Erreur lors du chargement de l\'historique</div>');
                }
            });
        });
        
        // Bouton d'actualisation
        $('#refreshButton').click(function() {
            location.reload();
        });
    });
</script>
@endsection