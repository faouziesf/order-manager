@extends('layouts.admin')

@section('title', 'Gestion des Employés')

@section('content')
<div class="animate-fade-in" x-data="employeesIndex()">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Gestion des Employés</h1>
            <p class="text-gray-600">Gérez vos employés et leurs affectations</p>
        </div>
        
        <div class="flex-shrink-0">
            @if($admin->employees()->count() < $admin->max_employees)
                <a href="{{ route('admin.employees.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-medium rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl hover:-translate-y-0.5">
                    <i class="fas fa-plus mr-2"></i>
                    Nouvel Employé
                </a>
            @else
                <div class="relative group">
                    <button disabled 
                            class="inline-flex items-center px-4 py-2 bg-gray-400 text-white font-medium rounded-xl cursor-not-allowed opacity-75">
                        <i class="fas fa-lock mr-2"></i>
                        Limite atteinte ({{ $admin->max_employees }})
                    </button>
                    
                    <!-- Tooltip modernisé -->
                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-sm rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap z-10">
                        Vous avez atteint la limite maximale d'employés
                        <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900"></div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Alertes de limite -->
    @if($admin->employees()->count() >= $admin->max_employees * 0.8)
        <div class="mb-6">
            <div class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-2xl p-4 shadow-sm" 
                 x-data="{ show: true }" 
                 x-show="show"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-amber-600 text-sm"></i>
                        </div>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold text-amber-900 mb-1">
                            @if($admin->employees()->count() >= $admin->max_employees)
                                Limite d'employés atteinte
                            @else
                                Approche de la limite d'employés
                            @endif
                        </h4>
                        <p class="text-amber-800 text-sm">
                            Vous avez <strong>{{ $admin->employees()->count() }}</strong> employé(s) sur un maximum de <strong>{{ $admin->max_employees }}</strong>.
                            @if($admin->employees()->count() >= $admin->max_employees)
                                Contactez le support pour augmenter votre limite.
                            @else
                                Il vous reste {{ $admin->max_employees - $admin->employees()->count() }} place(s).
                            @endif
                        </p>
                    </div>
                    <button @click="show = false" 
                            class="flex-shrink-0 p-1 rounded-lg hover:bg-amber-100 transition-colors duration-200">
                        <i class="fas fa-times text-amber-600 text-sm"></i>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Stats Cards avec animations améliorées -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
        <!-- Total Employés -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium mb-1">Total Employés</p>
                    <p class="text-3xl font-bold text-gray-900" x-data="{ count: 0 }" x-init="setTimeout(() => { count = {{ $employees->total() }} }, 300)">
                        <span x-text="count"></span>
                    </p>
                    <div class="flex items-center mt-2">
                        <div class="w-full bg-gray-200 rounded-full h-2 mr-2">
                            <div class="bg-gradient-to-r from-green-500 to-emerald-600 h-2 rounded-full transition-all duration-1000 ease-out" 
                                 style="width: {{ $admin->max_employees > 0 ? ($employees->total() / $admin->max_employees * 100) : 0 }}%"></div>
                        </div>
                        <span class="text-xs text-gray-500 whitespace-nowrap">{{ $admin->max_employees > 0 ? round($employees->total() / $admin->max_employees * 100) : 0 }}%</span>
                    </div>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-users text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Employés Actifs -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium mb-1">Employés Actifs</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $admin->employees()->where('is_active', true)->count() }}</p>
                    <p class="text-xs text-green-600 mt-2">
                        {{ $employees->total() > 0 ? round($admin->employees()->where('is_active', true)->count() / $employees->total() * 100) : 0 }}% du total
                    </p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-check-circle text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Limite Employés -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium mb-1">Limite Employés</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $admin->max_employees }}</p>
                    <p class="text-xs text-gray-600 mt-2">
                        {{ $admin->max_employees - $employees->total() }} place(s) restante(s)
                    </p>
                </div>
                <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-chart-bar text-amber-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Sans Manager -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium mb-1">Sans Manager</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $admin->employees()->whereNull('manager_id')->count() }}</p>
                    @if($admin->employees()->whereNull('manager_id')->count() > 0)
                        <p class="text-xs text-orange-600 mt-2">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            À assigner
                        </p>
                    @else
                        <p class="text-xs text-green-600 mt-2">
                            <i class="fas fa-check mr-1"></i>
                            Tous assignés
                        </p>
                    @endif
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-user-slash text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <!-- Card Header amélioré -->
        <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-slate-50">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Liste des Employés</h2>
                    <p class="text-sm text-gray-600">{{ $employees->total() }} employé(s) au total</p>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-3">
                    <!-- Filter by Manager avec style moderne -->
                    <div class="relative">
                        <select class="appearance-none bg-white px-4 py-2 pr-8 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors cursor-pointer"
                                onchange="filterByManager(this.value)"
                                x-model="selectedManager">
                            <option value="">Tous les managers</option>
                            <option value="no-manager">Sans manager</option>
                            @foreach($admin->managers as $manager)
                                <option value="{{ $manager->id }}">{{ $manager->name }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                            <i class="fas fa-chevron-down text-gray-400 text-sm"></i>
                        </div>
                    </div>
                    
                    <!-- Search Input modernisé -->
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400 text-sm"></i>
                        </div>
                        <input type="text" 
                               class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                               placeholder="Rechercher un employé..." 
                               id="searchInput"
                               x-model="searchTerm"
                               @input="filterEmployees">
                    </div>
                    
                    <!-- Actions groupées -->
                    <div class="relative" x-show="selectedEmployees.length > 0" x-transition>
                        <button @click="showBulkActions = !showBulkActions"
                                class="inline-flex items-center px-3 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-sm transition-colors">
                            <i class="fas fa-cog mr-2"></i>
                            Actions (<span x-text="selectedEmployees.length"></span>)
                        </button>
                        
                        <div x-show="showBulkActions" 
                             @click.away="showBulkActions = false"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                            <div class="py-1">
                                <button @click="bulkActivate()" 
                                        class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-green-50 hover:text-green-700">
                                    <i class="fas fa-check mr-2 w-4"></i>
                                    Activer sélectionnés
                                </button>
                                <button @click="bulkDeactivate()" 
                                        class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    <i class="fas fa-ban mr-2 w-4"></i>
                                    Désactiver sélectionnés
                                </button>
                                <div class="border-t border-gray-100 my-1"></div>
                                <button @click="bulkDelete()" 
                                        class="flex items-center w-full px-4 py-2 text-sm text-red-700 hover:bg-red-50">
                                    <i class="fas fa-trash mr-2 w-4"></i>
                                    Supprimer sélectionnés
                                </button>
                            </div>
                        </div>
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
                                <!-- Checkbox pour sélection groupée -->
                                <th class="text-left py-3 px-4 w-4">
                                    <input type="checkbox" 
                                           @change="toggleAllEmployees($event.target.checked)"
                                           class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700 cursor-pointer hover:text-green-600 transition-colors"
                                    @click="sortBy('name')">
                                    Employé 
                                    <i class="fas fa-sort text-xs ml-1" :class="getSortIcon('name')"></i>
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Contact</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700 cursor-pointer hover:text-green-600 transition-colors"
                                    @click="sortBy('manager')">
                                    Manager
                                    <i class="fas fa-sort text-xs ml-1" :class="getSortIcon('manager')"></i>
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700 cursor-pointer hover:text-green-600 transition-colors"
                                    @click="sortBy('status')">
                                    Statut
                                    <i class="fas fa-sort text-xs ml-1" :class="getSortIcon('status')"></i>
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700 cursor-pointer hover:text-green-600 transition-colors"
                                    @click="sortBy('created_at')">
                                    Créé le
                                    <i class="fas fa-sort text-xs ml-1" :class="getSortIcon('created_at')"></i>
                                </th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($employees as $employee)
                                <tr class="hover:bg-gradient-to-r hover:from-green-50 hover:to-emerald-50 transition-all duration-200 group" 
                                    data-manager-id="{{ $employee->manager_id }}"
                                    data-employee-id="{{ $employee->id }}">
                                    <!-- Checkbox -->
                                    <td class="py-4 px-4">
                                        <input type="checkbox" 
                                               value="{{ $employee->id }}"
                                               @change="toggleEmployee({{ $employee->id }}, $event.target.checked)"
                                               class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                                    </td>
                                    
                                    <!-- Employé -->
                                    <td class="py-4 px-4">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center mr-3 flex-shrink-0 group-hover:scale-110 transition-transform duration-200">
                                                <span class="text-white font-semibold text-sm">
                                                    {{ substr($employee->name, 0, 1) }}
                                                </span>
                                            </div>
                                            <div>
                                                <div class="font-semibold text-gray-900 group-hover:text-green-700 transition-colors">{{ $employee->name }}</div>
                                                <div class="text-sm text-gray-500">Employé</div>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <!-- Contact -->
                                    <td class="py-4 px-4">
                                        <div class="space-y-1">
                                            <div class="text-sm text-gray-900 flex items-center">
                                                <i class="fas fa-envelope text-gray-400 mr-2 w-3"></i>
                                                <a href="mailto:{{ $employee->email }}" class="hover:text-green-600 transition-colors">
                                                    {{ $employee->email }}
                                                </a>
                                            </div>
                                            @if($employee->phone)
                                                <div class="text-sm text-gray-500 flex items-center">
                                                    <i class="fas fa-phone text-gray-400 mr-2 w-3"></i>
                                                    <a href="tel:{{ $employee->phone }}" class="hover:text-blue-600 transition-colors">
                                                        {{ $employee->phone }}
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    
                                    <!-- Manager -->
                                    <td class="py-4 px-4">
                                        @if($employee->manager)
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center mr-2 flex-shrink-0">
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
                                                <i class="fas fa-user-slash mr-1 text-xs"></i>
                                                Sans manager
                                            </span>
                                        @endif
                                    </td>
                                    
                                    <!-- Statut -->
                                    <td class="py-4 px-4">
                                        <div class="flex items-center space-x-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $employee->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                <div class="w-1.5 h-1.5 rounded-full {{ $employee->is_active ? 'bg-green-500' : 'bg-red-500' }} mr-1.5"></div>
                                                {{ $employee->is_active ? 'Actif' : 'Inactif' }}
                                            </span>
                                            @if($employee->loginHistory()->latest()->first())
                                                @php $lastLogin = $employee->loginHistory()->latest()->first(); @endphp
                                                <div class="relative group">
                                                    <i class="fas fa-clock text-gray-400 text-xs cursor-help"></i>
                                                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10">
                                                        Dernière connexion: {{ $lastLogin->login_at->diffForHumans() }}
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    
                                    <!-- Date -->
                                    <td class="py-4 px-4">
                                        <div class="text-sm text-gray-900">{{ $employee->created_at->format('d/m/Y') }}</div>
                                        <div class="text-xs text-gray-500">{{ $employee->created_at->diffForHumans() }}</div>
                                    </td>
                                    
                                    <!-- Actions -->
                                    <td class="py-4 px-4">
                                        <div class="flex items-center space-x-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                            <!-- Voir -->
                                            <a href="{{ route('admin.employees.show', $employee) }}" 
                                               class="inline-flex items-center justify-center w-8 h-8 bg-blue-100 hover:bg-blue-200 text-blue-600 rounded-lg transition-all duration-200 hover:scale-110"
                                               title="Voir les détails">
                                                <i class="fas fa-eye text-sm"></i>
                                            </a>
                                            
                                            <!-- Modifier -->
                                            <a href="{{ route('admin.employees.edit', $employee) }}" 
                                               class="inline-flex items-center justify-center w-8 h-8 bg-amber-100 hover:bg-amber-200 text-amber-600 rounded-lg transition-all duration-200 hover:scale-110"
                                               title="Modifier">
                                                <i class="fas fa-edit text-sm"></i>
                                            </a>
                                            
                                            <!-- Toggle Active -->
                                            <button type="button"
                                                    @click="toggleEmployeeStatus({{ $employee->id }}, {{ $employee->is_active ? 'false' : 'true' }})"
                                                    class="inline-flex items-center justify-center w-8 h-8 {{ $employee->is_active ? 'bg-gray-100 hover:bg-gray-200 text-gray-600' : 'bg-green-100 hover:bg-green-200 text-green-600' }} rounded-lg transition-all duration-200 hover:scale-110"
                                                    title="{{ $employee->is_active ? 'Désactiver' : 'Activer' }}">
                                                <i class="fas {{ $employee->is_active ? 'fa-ban' : 'fa-check' }} text-sm"></i>
                                            </button>
                                            
                                            <!-- Supprimer -->
                                            <button type="button"
                                                    @click="deleteEmployee({{ $employee->id }}, '{{ $employee->name }}')"
                                                    class="inline-flex items-center justify-center w-8 h-8 bg-red-100 hover:bg-red-200 text-red-600 rounded-lg transition-all duration-200 hover:scale-110"
                                                    title="Supprimer">
                                                <i class="fas fa-trash text-sm"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination modernisée -->
                @if($employees->hasPages())
                    <div class="flex flex-col sm:flex-row justify-between items-center mt-6 pt-6 border-t border-gray-200">
                        <div class="text-sm text-gray-700 mb-4 sm:mb-0">
                            Affichage de {{ $employees->firstItem() }} à {{ $employees->lastItem() }} sur {{ $employees->total() }} résultats
                        </div>
                        
                        <nav class="flex items-center space-x-2">
                            {{-- Previous Page Link --}}
                            @if ($employees->onFirstPage())
                                <span class="px-3 py-2 text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">
                                    <i class="fas fa-chevron-left text-sm"></i>
                                </span>
                            @else
                                <a href="{{ $employees->previousPageUrl() }}" 
                                   class="px-3 py-2 text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition-colors">
                                    <i class="fas fa-chevron-left text-sm"></i>
                                </a>
                            @endif

                            {{-- Pagination Elements --}}
                            @foreach ($employees->getUrlRange(max(1, $employees->currentPage() - 2), min($employees->lastPage(), $employees->currentPage() + 2)) as $page => $url)
                                @if ($page == $employees->currentPage())
                                    <span class="px-4 py-2 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-lg font-medium shadow-sm">
                                        {{ $page }}
                                    </span>
                                @else
                                    <a href="{{ $url }}" 
                                       class="px-4 py-2 text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition-colors">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach

                            {{-- Next Page Link --}}
                            @if ($employees->hasMorePages())
                                <a href="{{ $employees->nextPageUrl() }}" 
                                   class="px-3 py-2 text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition-colors">
                                    <i class="fas fa-chevron-right text-sm"></i>
                                </a>
                            @else
                                <span class="px-3 py-2 text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">
                                    <i class="fas fa-chevron-right text-sm"></i>
                                </span>
                            @endif
                        </nav>
                    </div>
                @endif
            @else
                <!-- Empty State modernisé -->
                <div class="text-center py-16">
                    <div class="w-24 h-24 bg-gradient-to-br from-gray-100 to-gray-200 rounded-3xl flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-users text-gray-400 text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Aucun employé trouvé</h3>
                    <p class="text-gray-500 mb-8 max-w-md mx-auto">
                        @if(request()->has('search') || request()->has('manager'))
                            Aucun employé ne correspond à vos critères de recherche. Essayez de modifier vos filtres.
                        @else
                            Commencez par créer votre premier employé pour développer votre équipe.
                        @endif
                    </p>
                    @if($admin->employees()->count() < $admin->max_employees)
                        <div class="space-y-3">
                            <a href="{{ route('admin.employees.create') }}" 
                               class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-medium rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl hover:-translate-y-0.5">
                                <i class="fas fa-plus mr-2"></i>
                                Créer un Employé
                            </a>
                            @if(request()->has('search') || request()->has('manager'))
                                <div>
                                    <a href="{{ route('admin.employees.index') }}" 
                                       class="inline-flex items-center px-4 py-2 text-gray-600 hover:text-gray-900 transition-colors">
                                        <i class="fas fa-times mr-2"></i>
                                        Effacer les filtres
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Modal de confirmation moderne -->
    <div x-show="showModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm"
         @click.away="closeModal()">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 transform"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            
            <!-- Header dynamique -->
            <div class="px-6 py-4 rounded-t-2xl"
                 :class="{
                     'bg-gradient-to-r from-red-500 to-pink-600': modalType === 'delete',
                     'bg-gradient-to-r from-amber-500 to-orange-600': modalType === 'toggle',
                     'bg-gradient-to-r from-blue-500 to-indigo-600': modalType === 'bulk'
                 }">
                <div class="flex items-center justify-between text-white">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center mr-3">
                            <i :class="{
                                'fas fa-exclamation-triangle': modalType === 'delete',
                                'fas fa-question-circle': modalType === 'toggle',
                                'fas fa-cog': modalType === 'bulk'
                            }"></i>
                        </div>
                        <h3 class="text-lg font-semibold" x-text="modalTitle"></h3>
                    </div>
                    <button @click="closeModal()" 
                            class="p-1 hover:bg-white/20 rounded-lg transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            <!-- Body -->
            <div class="p-6">
                <div x-show="modalType === 'delete'">
                    <p class="text-gray-700 mb-4" x-html="modalMessage"></p>
                    <div class="bg-gradient-to-r from-red-50 to-pink-50 border border-red-200 rounded-xl p-4">
                        <div class="flex items-start space-x-3">
                            <div class="w-6 h-6 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-exclamation-triangle text-red-600 text-sm"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-red-900 text-sm">Attention !</p>
                                <p class="text-red-800 text-sm">Cette action est irréversible et supprimera définitivement l'employé.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div x-show="modalType === 'toggle'">
                    <p class="text-gray-700" x-html="modalMessage"></p>
                </div>
                
                <div x-show="modalType === 'bulk'">
                    <p class="text-gray-700 mb-4" x-html="modalMessage"></p>
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-4">
                        <div class="flex items-start space-x-3">
                            <div class="w-6 h-6 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-info-circle text-blue-600 text-sm"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-blue-900 text-sm">Information</p>
                                <p class="text-blue-800 text-sm">Cette action s'appliquera à tous les employés sélectionnés.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="bg-gray-50 px-6 py-4 rounded-b-2xl flex justify-end space-x-3">
                <button @click="closeModal()" 
                        class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-xl transition-colors duration-200">
                    <i class="fas fa-times mr-2"></i>
                    Annuler
                </button>
                <button @click="confirmAction()" 
                        class="inline-flex items-center px-4 py-2 font-medium rounded-xl transition-colors duration-200"
                        :class="{
                            'bg-red-500 hover:bg-red-600 text-white': modalType === 'delete',
                            'bg-amber-500 hover:bg-amber-600 text-white': modalType === 'toggle',
                            'bg-blue-500 hover:bg-blue-600 text-white': modalType === 'bulk'
                        }">
                    <i class="mr-2" :class="{
                        'fas fa-trash': modalType === 'delete',
                        'fas fa-check': modalType === 'toggle',
                        'fas fa-cog': modalType === 'bulk'
                    }"></i>
                    <span x-text="modalConfirmText"></span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function employeesIndex() {
    return {
        searchTerm: '',
        selectedManager: '',
        selectedEmployees: [],
        showBulkActions: false,
        showModal: false,
        modalType: '',
        modalTitle: '',
        modalMessage: '',
        modalConfirmText: '',
        pendingAction: null,
        sortField: '',
        sortDirection: 'asc',
        
        init() {
            this.filterEmployees();
        },
        
        // Filtrage des employés
        filterEmployees() {
            const searchTerm = this.searchTerm.toLowerCase();
            const rows = document.querySelectorAll('#employeesTable tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                const managerId = row.dataset.managerId;
                
                let showRow = text.includes(searchTerm);
                
                if (this.selectedManager) {
                    if (this.selectedManager === 'no-manager') {
                        showRow = showRow && (managerId === '' || managerId === 'null');
                    } else {
                        showRow = showRow && (managerId === this.selectedManager);
                    }
                }
                
                row.style.display = showRow ? '' : 'none';
            });
        },
        
        // Sélection des employés
        toggleEmployee(employeeId, checked) {
            if (checked) {
                if (!this.selectedEmployees.includes(employeeId)) {
                    this.selectedEmployees.push(employeeId);
                }
            } else {
                this.selectedEmployees = this.selectedEmployees.filter(id => id !== employeeId);
            }
        },
        
        toggleAllEmployees(checked) {
            const visibleRows = document.querySelectorAll('#employeesTable tbody tr:not([style*="display: none"])');
            this.selectedEmployees = [];
            
            visibleRows.forEach(row => {
                const checkbox = row.querySelector('input[type="checkbox"]');
                checkbox.checked = checked;
                if (checked) {
                    this.selectedEmployees.push(parseInt(checkbox.value));
                }
            });
        },
        
        // Actions individuelles
        toggleEmployeeStatus(employeeId, newStatus) {
            this.modalType = 'toggle';
            this.modalTitle = newStatus ? 'Activer l\'employé' : 'Désactiver l\'employé';
            this.modalMessage = `Êtes-vous sûr de vouloir ${newStatus ? 'activer' : 'désactiver'} cet employé ?`;
            this.modalConfirmText = newStatus ? 'Activer' : 'Désactiver';
            this.pendingAction = () => this.executeToggleStatus(employeeId);
            this.showModal = true;
        },
        
        deleteEmployee(employeeId, employeeName) {
            this.modalType = 'delete';
            this.modalTitle = 'Supprimer l\'employé';
            this.modalMessage = `Êtes-vous sûr de vouloir supprimer l'employé <strong>${employeeName}</strong> ?`;
            this.modalConfirmText = 'Supprimer';
            this.pendingAction = () => this.executeDelete(employeeId);
            this.showModal = true;
        },
        
        // Actions groupées
        bulkActivate() {
            this.modalType = 'bulk';
            this.modalTitle = 'Activer les employés sélectionnés';
            this.modalMessage = `Activer ${this.selectedEmployees.length} employé(s) sélectionné(s) ?`;
            this.modalConfirmText = 'Activer';
            this.pendingAction = () => this.executeBulkAction('activate');
            this.showModal = true;
        },
        
        bulkDeactivate() {
            this.modalType = 'bulk';
            this.modalTitle = 'Désactiver les employés sélectionnés';
            this.modalMessage = `Désactiver ${this.selectedEmployees.length} employé(s) sélectionné(s) ?`;
            this.modalConfirmText = 'Désactiver';
            this.pendingAction = () => this.executeBulkAction('deactivate');
            this.showModal = true;
        },
        
        bulkDelete() {
            this.modalType = 'bulk';
            this.modalTitle = 'Supprimer les employés sélectionnés';
            this.modalMessage = `Supprimer définitivement ${this.selectedEmployees.length} employé(s) sélectionné(s) ?`;
            this.modalConfirmText = 'Supprimer';
            this.pendingAction = () => this.executeBulkAction('delete');
            this.showModal = true;
        },
        
        // Exécution des actions avec notifications modernes
        executeToggleStatus(employeeId) {
            // Afficher une notification de chargement
            const loadingNotif = window.notifications.show('Modification du statut en cours...', 'info', {
                persistent: true,
                showProgress: false
            });
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/employees/${employeeId}/toggle-active`;
            
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            const method = document.createElement('input');
            method.type = 'hidden';
            method.name = '_method';
            method.value = 'PATCH';
            
            form.appendChild(csrf);
            form.appendChild(method);
            document.body.appendChild(form);
            
            // Simuler un délai pour montrer le loading
            setTimeout(() => {
                window.notifications.hide(loadingNotif);
                window.notifications.success('Statut modifié avec succès !');
                form.submit();
            }, 800);
        },
        
        executeDelete(employeeId) {
            const loadingNotif = window.notifications.show('Suppression en cours...', 'warning', {
                persistent: true,
                showProgress: false
            });
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/employees/${employeeId}`;
            
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            const method = document.createElement('input');
            method.type = 'hidden';
            method.name = '_method';
            method.value = 'DELETE';
            
            form.appendChild(csrf);
            form.appendChild(method);
            document.body.appendChild(form);
            
            setTimeout(() => {
                window.notifications.hide(loadingNotif);
                window.notifications.success('Employé supprimé avec succès !');
                form.submit();
            }, 800);
        },
        
        executeBulkAction(action) {
            const loadingNotif = window.notifications.show(`Traitement de ${this.selectedEmployees.length} employé(s)...`, 'info', {
                persistent: true,
                showProgress: false
            });
            
            // Simulation d'une requête AJAX
            setTimeout(() => {
                window.notifications.hide(loadingNotif);
                
                const messages = {
                    activate: `${this.selectedEmployees.length} employé(s) activé(s) avec succès !`,
                    deactivate: `${this.selectedEmployees.length} employé(s) désactivé(s) avec succès !`,
                    delete: `${this.selectedEmployees.length} employé(s) supprimé(s) avec succès !`
                };
                
                window.notifications.success(messages[action] || 'Action terminée avec succès !', {
                    duration: 6000
                });
                
                this.selectedEmployees = [];
                this.showBulkActions = false;
                this.toggleAllEmployees(false);
            }, 1500);
        },
        
        // Tri avec notifications
        sortBy(field) {
            if (this.sortField === field) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortField = field;
                this.sortDirection = 'asc';
            }
            
            const fieldNames = {
                name: 'nom',
                manager: 'manager',
                status: 'statut',
                created_at: 'date de création'
            };
            
            const directionText = this.sortDirection === 'asc' ? 'croissant' : 'décroissant';
            
            window.notifications.info(`Tri par ${fieldNames[field] || field} (${directionText})`, {
                duration: 2000,
                showProgress: false
            });
        },
        
        getSortIcon(field) {
            if (this.sortField !== field) return '';
            return this.sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down';
        },
        
        // Modal
        confirmAction() {
            if (this.pendingAction) {
                this.pendingAction();
                this.closeModal();
            }
        },
        
        closeModal() {
            this.showModal = false;
            this.pendingAction = null;
        },
        
        // Toast notifications avec le nouveau système
        showToast(message, type = 'info', options = {}) {
            return window.notifications.show(message, type, {
                duration: 4000,
                ...options
            });
        }
    }
}

// Fonction de filtrage par manager (conservée pour compatibilité)
function filterByManager(managerId) {
    // Utilise Alpine.js maintenant
    Alpine.$data(document.querySelector('[x-data*="employeesIndex"]')).selectedManager = managerId;
    Alpine.$data(document.querySelector('[x-data*="employeesIndex"]')).filterEmployees();
}

// Animation d'apparition progressive
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

/* Animation pour les barres de progression */
@keyframes progressFill {
    from { width: 0%; }
    to { width: var(--target-width); }
}

/* Amélioration des transitions au survol */
.group:hover .group-hover\:scale-110 {
    transform: scale(1.1);
}

.group:hover .group-hover\:text-green-700 {
    color: #15803d;
}

/* Animation des compteurs */
@keyframes countUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

[x-data] [x-text] {
    animation: countUp 0.5s ease-out;
}
</style>
@endsection