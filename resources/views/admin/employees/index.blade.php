@extends('layouts.admin')

@section('title', 'Gestion des Employés')

@section('content')
<div class="animate-fade-in">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Gestion des Employés</h1>
            <p class="text-gray-600">Gérez vos employés et leurs affectations</p>
        </div>
        
        <div class="flex-shrink-0">
            @if($admin->employees()->count() < $admin->max_employees)
                <a href="{{ route('admin.employees.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-xl transition-colors duration-200 shadow-sm hover:shadow-md">
                    <i class="fas fa-plus mr-2"></i>
                    Nouvel Employé
                </a>
            @else
                <button disabled 
                        class="inline-flex items-center px-4 py-2 bg-gray-400 text-white font-medium rounded-xl cursor-not-allowed">
                    <i class="fas fa-lock mr-2"></i>
                    Limite atteinte ({{ $admin->max_employees }})
                </button>
            @endif
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
        <!-- Total Employés -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium mb-1">Total Employés</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $employees->total() }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Employés Actifs -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium mb-1">Employés Actifs</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $admin->employees()->where('is_active', true)->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-primary-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-check-circle text-primary-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Limite Employés -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium mb-1">Limite Employés</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $admin->max_employees }}</p>
                </div>
                <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-limit text-amber-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Sans Manager -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium mb-1">Sans Manager</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $admin->employees()->whereNull('manager_id')->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-user-slash text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <!-- Card Header -->
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <h2 class="text-lg font-semibold text-gray-900">Liste des Employés</h2>
                
                <div class="flex flex-col sm:flex-row gap-3">
                    <!-- Filter by Manager -->
                    <select class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors"
                            onchange="filterByManager(this.value)">
                        <option value="">Tous les managers</option>
                        <option value="no-manager">Sans manager</option>
                        @foreach($admin->managers as $manager)
                            <option value="{{ $manager->id }}">{{ $manager->name }}</option>
                        @endforeach
                    </select>
                    
                    <!-- Search Input -->
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" 
                               class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors"
                               placeholder="Rechercher..." 
                               id="searchInput">
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Table Content -->
        <div class="p-6">
            @if($employees->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full" id="employeesTable">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Employé</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Contact</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Manager</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Statut</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Créé le</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($employees as $employee)
                                <tr class="hover:bg-gray-50 transition-colors duration-150" data-manager-id="{{ $employee->manager_id }}">
                                    <!-- Employé -->
                                    <td class="py-4 px-4">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                                                <span class="text-white font-semibold text-sm">
                                                    {{ substr($employee->name, 0, 1) }}
                                                </span>
                                            </div>
                                            <div>
                                                <div class="font-semibold text-gray-900">{{ $employee->name }}</div>
                                                <div class="text-sm text-gray-500">Employé</div>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <!-- Contact -->
                                    <td class="py-4 px-4">
                                        <div class="text-sm text-gray-900">{{ $employee->email }}</div>
                                        @if($employee->phone)
                                            <div class="text-sm text-gray-500">{{ $employee->phone }}</div>
                                        @endif
                                    </td>
                                    
                                    <!-- Manager -->
                                    <td class="py-4 px-4">
                                        @if($employee->manager)
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 bg-primary-600 rounded-full flex items-center justify-center mr-2 flex-shrink-0">
                                                    <span class="text-white font-semibold text-xs">
                                                        {{ substr($employee->manager->name, 0, 1) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <div class="font-medium text-gray-900 text-sm">{{ $employee->manager->name }}</div>
                                                    <div class="text-xs text-gray-500">Manager</div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                Sans manager
                                            </span>
                                        @endif
                                    </td>
                                    
                                    <!-- Statut -->
                                    <td class="py-4 px-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $employee->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $employee->is_active ? 'Actif' : 'Inactif' }}
                                        </span>
                                    </td>
                                    
                                    <!-- Date -->
                                    <td class="py-4 px-4 text-sm text-gray-900">
                                        {{ $employee->created_at->format('d/m/Y') }}
                                    </td>
                                    
                                    <!-- Actions -->
                                    <td class="py-4 px-4">
                                        <div class="flex items-center space-x-2">
                                            <!-- Voir -->
                                            <a href="{{ route('admin.employees.show', $employee) }}" 
                                               class="inline-flex items-center justify-center w-8 h-8 bg-blue-100 hover:bg-blue-200 text-blue-600 rounded-lg transition-colors duration-200"
                                               title="Voir">
                                                <i class="fas fa-eye text-sm"></i>
                                            </a>
                                            
                                            <!-- Modifier -->
                                            <a href="{{ route('admin.employees.edit', $employee) }}" 
                                               class="inline-flex items-center justify-center w-8 h-8 bg-amber-100 hover:bg-amber-200 text-amber-600 rounded-lg transition-colors duration-200"
                                               title="Modifier">
                                                <i class="fas fa-edit text-sm"></i>
                                            </a>
                                            
                                            <!-- Toggle Active -->
                                            <form method="POST" action="{{ route('admin.employees.toggle-active', $employee) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" 
                                                        class="inline-flex items-center justify-center w-8 h-8 {{ $employee->is_active ? 'bg-gray-100 hover:bg-gray-200 text-gray-600' : 'bg-green-100 hover:bg-green-200 text-green-600' }} rounded-lg transition-colors duration-200"
                                                        title="{{ $employee->is_active ? 'Désactiver' : 'Activer' }}"
                                                        onclick="return confirm('Êtes-vous sûr ?')">
                                                    <i class="fas {{ $employee->is_active ? 'fa-ban' : 'fa-check' }} text-sm"></i>
                                                </button>
                                            </form>
                                            
                                            <!-- Supprimer -->
                                            <form method="POST" action="{{ route('admin.employees.destroy', $employee) }}" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="inline-flex items-center justify-center w-8 h-8 bg-red-100 hover:bg-red-200 text-red-600 rounded-lg transition-colors duration-200"
                                                        title="Supprimer"
                                                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet employé ? Cette action est irréversible.')">
                                                    <i class="fas fa-trash text-sm"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                @if($employees->hasPages())
                    <div class="flex justify-center mt-6">
                        <nav class="flex items-center space-x-2">
                            {{-- Previous Page Link --}}
                            @if ($employees->onFirstPage())
                                <span class="px-3 py-2 text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">
                                    <i class="fas fa-chevron-left"></i>
                                </span>
                            @else
                                <a href="{{ $employees->previousPageUrl() }}" 
                                   class="px-3 py-2 text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            @endif

                            {{-- Pagination Elements --}}
                            @foreach ($employees->getUrlRange(1, $employees->lastPage()) as $page => $url)
                                @if ($page == $employees->currentPage())
                                    <span class="px-4 py-2 bg-primary-600 text-white rounded-lg font-medium">
                                        {{ $page }}
                                    </span>
                                @else
                                    <a href="{{ $url }}" 
                                       class="px-4 py-2 text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach

                            {{-- Next Page Link --}}
                            @if ($employees->hasMorePages())
                                <a href="{{ $employees->nextPageUrl() }}" 
                                   class="px-3 py-2 text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            @else
                                <span class="px-3 py-2 text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">
                                    <i class="fas fa-chevron-right"></i>
                                </span>
                            @endif
                        </nav>
                    </div>
                @endif
            @else
                <!-- Empty State -->
                <div class="text-center py-12">
                    <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-users text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Aucun employé</h3>
                    <p class="text-gray-500 mb-6">Commencez par créer votre premier employé.</p>
                    @if($admin->employees()->count() < $admin->max_employees)
                        <a href="{{ route('admin.employees.create') }}" 
                           class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-xl transition-colors duration-200">
                            <i class="fas fa-plus mr-2"></i>
                            Créer un Employé
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Recherche en temps réel
document.getElementById('searchInput').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#employeesTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Filtrage par manager
function filterByManager(managerId) {
    const rows = document.querySelectorAll('#employeesTable tbody tr');
    
    rows.forEach(row => {
        const rowManagerId = row.dataset.managerId;
        
        if (managerId === '') {
            row.style.display = '';
        } else if (managerId === 'no-manager') {
            row.style.display = rowManagerId === '' ? '' : 'none';
        } else {
            row.style.display = rowManagerId === managerId ? '' : 'none';
        }
    });
}

// Animation d'apparition des éléments
document.addEventListener('DOMContentLoaded', function() {
    // Animer les cartes de stats
    const statCards = document.querySelectorAll('.grid > div');
    statCards.forEach((card, index) => {
        setTimeout(() => {
            card.classList.add('animate-slide-up');
        }, index * 100);
    });
    
    // Animer les lignes du tableau
    const tableRows = document.querySelectorAll('#employeesTable tbody tr');
    tableRows.forEach((row, index) => {
        setTimeout(() => {
            row.classList.add('animate-slide-up');
        }, (index * 50) + 300);
    });
});

// Améliorer l'expérience utilisateur avec des transitions
document.querySelectorAll('button[onclick*="confirm"]').forEach(button => {
    button.addEventListener('click', function(e) {
        const originalText = this.innerHTML;
        if (this.onclick && this.onclick.toString().includes('confirm')) {
            // Ajouter un état de chargement après confirmation
            const originalOnclick = this.onclick;
            this.onclick = function(event) {
                if (originalOnclick.call(this, event)) {
                    this.innerHTML = '<i class="fas fa-spinner animate-spin text-sm"></i>';
                    this.disabled = true;
                    setTimeout(() => {
                        // Restaurer l'état original si pas de redirect
                        this.innerHTML = originalText;
                        this.disabled = false;
                    }, 3000);
                    return true;
                }
                return false;
            };
        }
    });
});
</script>

<style>
@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-slide-up {
    animation: slideUp 0.5s ease-out forwards;
}
</style>
@endsection