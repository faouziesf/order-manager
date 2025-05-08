@extends('layouts.app')

@section('title', 'Tableau de bord')

@section('page-heading', 'Tableau de bord')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Statistiques des commandes -->
    <div class="bg-white p-6 rounded-lg border border-gray-100 shadow-sm">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h3 class="text-sm font-medium text-gray-500">Total des commandes</h3>
                <p class="text-2xl font-bold text-indigo-600">{{ $totalOrders }}</p>
            </div>
            <div class="p-3 bg-indigo-50 rounded-lg">
                <i class="fas fa-shopping-cart text-indigo-500"></i>
            </div>
        </div>
        <div class="flex items-center text-xs">
            <span class="text-green-500 flex items-center mr-2">
                <i class="fas fa-arrow-up mr-1"></i> 12%
            </span>
            <span class="text-gray-500">depuis le mois dernier</span>
        </div>
    </div>

    <!-- Revenu total -->
    <div class="bg-white p-6 rounded-lg border border-gray-100 shadow-sm">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h3 class="text-sm font-medium text-gray-500">Revenu total</h3>
                <p class="text-2xl font-bold text-green-600">{{ number_format($totalRevenue, 2, ',', ' ') }} €</p>
            </div>
            <div class="p-3 bg-green-50 rounded-lg">
                <i class="fas fa-money-bill-wave text-green-500"></i>
            </div>
        </div>
        <div class="flex items-center text-xs">
            <span class="text-green-500 flex items-center mr-2">
                <i class="fas fa-arrow-up mr-1"></i> 8%
            </span>
            <span class="text-gray-500">depuis le mois dernier</span>
        </div>
    </div>

    <!-- Nouveaux clients -->
    <div class="bg-white p-6 rounded-lg border border-gray-100 shadow-sm">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h3 class="text-sm font-medium text-gray-500">Nouveaux clients</h3>
                <p class="text-2xl font-bold text-blue-600">{{ $newCustomers }}</p>
            </div>
            <div class="p-3 bg-blue-50 rounded-lg">
                <i class="fas fa-users text-blue-500"></i>
            </div>
        </div>
        <div class="flex items-center text-xs">
            <span class="text-green-500 flex items-center mr-2">
                <i class="fas fa-arrow-up mr-1"></i> 5%
            </span>
            <span class="text-gray-500">depuis le mois dernier</span>
        </div>
    </div>

    <!-- Produits populaires -->
    <div class="bg-white p-6 rounded-lg border border-gray-100 shadow-sm">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h3 class="text-sm font-medium text-gray-500">Produits en stock</h3>
                <p class="text-2xl font-bold text-orange-600">{{ $productsInStock }}</p>
            </div>
            <div class="p-3 bg-orange-50 rounded-lg">
                <i class="fas fa-box text-orange-500"></i>
            </div>
        </div>
        <div class="flex items-center text-xs">
            <span class="text-red-500 flex items-center mr-2">
                <i class="fas fa-arrow-down mr-1"></i> 3%
            </span>
            <span class="text-gray-500">depuis le mois dernier</span>
        </div>
    </div>
</div>

<!-- Graphique et commandes récentes -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Graphique des ventes -->
    <div class="lg:col-span-2 bg-white p-6 rounded-lg border border-gray-100 shadow-sm">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-semibold text-gray-800">Statistiques des ventes</h3>
            <div class="flex items-center space-x-2">
                <select class="text-sm border-gray-200 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                    <option>7 derniers jours</option>
                    <option>30 derniers jours</option>
                    <option>Cette année</option>
                </select>
            </div>
        </div>
        
        <div class="h-80">
            <!-- Ici, vous pouvez intégrer un graphique avec Chart.js ou une autre bibliothèque -->
            <div class="w-full h-full flex items-center justify-center text-gray-400">
                <p>Graphique des ventes sera affiché ici</p>
            </div>
        </div>
    </div>

    <!-- Commandes récentes -->
    <div class="bg-white p-6 rounded-lg border border-gray-100 shadow-sm">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-semibold text-gray-800">Commandes récentes</h3>
            <a href="{{ route('admin.orders.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700">Voir tout</a>
        </div>
        
        <div class="space-y-4">
            @forelse($recentOrders as $order)
            <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                <div class="flex items-center">
                    <div class="p-2 bg-indigo-50 rounded-md mr-3">
                        <i class="fas fa-shopping-bag text-indigo-500"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium">{{ $order->customer_name }}</p>
                        <p class="text-xs text-gray-500">{{ $order->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
                <div>
                    <span class="px-2 py-1 text-xs rounded-full 
                        @if($order->status == 'pending') bg-yellow-100 text-yellow-700 
                        @elseif($order->status == 'processing') bg-blue-100 text-blue-700 
                        @elseif($order->status == 'completed') bg-green-100 text-green-700 
                        @else bg-red-100 text-red-700 @endif">
                        {{ ucfirst($order->status) }}
                    </span>
                </div>
            </div>
            @empty
            <div class="text-center text-gray-500 py-6">
                <p>Aucune commande récente</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Produits populaires et dernières activités -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Produits populaires -->
    <div class="bg-white p-6 rounded-lg border border-gray-100 shadow-sm">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-semibold text-gray-800">Produits populaires</h3>
            <a href="{{ route('admin.products.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700">Voir tout</a>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produit</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prix</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ventes</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($popularProducts as $product)
                    <tr>
                        <td class="px-3 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-gray-100 rounded-md flex items-center justify-center mr-3">
                                    @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-8 h-8 rounded-md object-cover">
                                    @else
                                    <i class="fas fa-box text-gray-400"></i>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-700">{{ $product->name }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-700">{{ number_format($product->price, 2, ',', ' ') }} €</td>
                        <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-700">{{ $product->sales_count }}</td>
                        <td class="px-3 py-4 whitespace-nowrap">
                            @if($product->stock > 10)
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700">En stock</span>
                            @elseif($product->stock > 0)
                                <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-700">Faible</span>
                            @else
                                <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-700">Épuisé</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-3 py-4 text-center text-gray-500">
                            Aucun produit disponible
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Dernières activités -->
    <div class="bg-white p-6 rounded-lg border border-gray-100 shadow-sm">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-semibold text-gray-800">Dernières activités</h3>
        </div>
        
        <div class="relative">
            <div class="absolute left-4 h-full w-px bg-gray-200"></div>
            
            <div class="space-y-6 ml-8">
                @forelse($activities as $activity)
                <div class="relative">
                    <div class="absolute -left-10 mt-1 rounded-full bg-indigo-100 p-2">
                        <i class="fas fa-{{ $activity->icon ?? 'circle' }} text-indigo-500 text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-700">{{ $activity->description }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $activity->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @empty
                <div class="text-center text-gray-500 py-6">
                    <p>Aucune activité récente</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    // Ici vous pouvez ajouter le code JavaScript pour les graphiques
    // Par exemple avec Chart.js
</script>
@endsection