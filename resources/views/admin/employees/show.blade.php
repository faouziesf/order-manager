@extends('layouts.admin')

@section('title', 'Détails de l\'Employé')

@section('content')
<div class="animate-fade-in" x-data="employeeDetailsPage()">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Détails de l'Employé</h1>
            <p class="text-gray-600">Informations complètes de {{ $employee->name }}</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.employees.edit', $employee) }}" 
               class="inline-flex items-center px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-medium rounded-xl transition-all duration-200 shadow-sm hover:shadow-md hover:-translate-y-0.5">
                <i class="fas fa-edit mr-2"></i>
                Modifier
            </a>
            <form method="POST" action="{{ route('admin.employees.toggle-active', $employee) }}" class="inline">
                @csrf
                @method('PATCH')
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 {{ $employee->is_active ? 'bg-gray-500 hover:bg-gray-600' : 'bg-green-500 hover:bg-green-600' }} text-white font-medium rounded-xl transition-all duration-200 shadow-sm hover:shadow-md hover:-translate-y-0.5"
                        onclick="return confirm('Êtes-vous sûr ?')">
                    <i class="fas {{ $employee->is_active ? 'fa-ban' : 'fa-check' }} mr-2"></i>
                    {{ $employee->is_active ? 'Désactiver' : 'Activer' }}
                </button>
            </form>
            <a href="{{ route('admin.employees.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-xl transition-all duration-200 shadow-sm hover:shadow-md hover:-translate-y-0.5">
                <i class="fas fa-arrow-left mr-2"></i>
                Retour
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Sidebar - Informations principales -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Profile Card -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <!-- Header avec avatar -->
                <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-6 py-8 text-center">
                    <div class="w-20 h-20 bg-white/20 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <span class="text-3xl font-bold text-white">
                            {{ substr($employee->name, 0, 1) }}
                        </span>
                    </div>
                    <h2 class="text-xl font-bold text-white mb-1">{{ $employee->name }}</h2>
                    <p class="text-green-100">Employé</p>
                </div>
                
                <!-- Informations de contact -->
                <div class="p-6 space-y-4">
                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Email</label>
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-envelope text-green-600 text-sm"></i>
                            </div>
                            <a href="mailto:{{ $employee->email }}" 
                               class="text-gray-900 hover:text-green-600 transition-colors">
                                {{ $employee->email }}
                            </a>
                        </div>
                    </div>
                    
                    @if($employee->phone)
                    <!-- Téléphone -->
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Téléphone</label>
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-phone text-blue-600 text-sm"></i>
                            </div>
                            <a href="tel:{{ $employee->phone }}" 
                               class="text-gray-900 hover:text-blue-600 transition-colors">
                                {{ $employee->phone }}
                            </a>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Statut -->
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Statut</label>
                        <div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $employee->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                <i class="fas {{ $employee->is_active ? 'fa-check-circle' : 'fa-times-circle' }} mr-1 text-xs"></i>
                                {{ $employee->is_active ? 'Actif' : 'Inactif' }}
                            </span>
                        </div>
                    </div>
                    
                    <!-- Manager -->
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Manager superviseur</label>
                        <div>
                            @if($employee->manager)
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                                        <span class="text-white text-xs font-semibold">
                                            {{ substr($employee->manager->name, 0, 1) }}
                                        </span>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-900 text-sm">{{ $employee->manager->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $employee->manager->email }}</div>
                                    </div>
                                </div>
                            @else
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    <i class="fas fa-user-slash mr-1 text-xs"></i>
                                    Employé indépendant
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Actions rapides -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <h3 class="flex items-center text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-bolt text-yellow-500 mr-2"></i>
                    Actions rapides
                </h3>
                <div class="space-y-3">
                    <a href="{{ route('admin.login-history.show', ['user_type' => 'Employee', 'user_id' => $employee->id]) }}" 
                       class="flex items-center w-full px-4 py-2 text-left bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-xl transition-colors duration-200">
                        <i class="fas fa-history mr-3 w-4"></i>
                        Historique des connexions
                    </a>
                    
                    @if($employee->manager)
                        <a href="{{ route('admin.managers.show', $employee->manager) }}" 
                           class="flex items-center w-full px-4 py-2 text-left bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl transition-colors duration-200">
                            <i class="fas fa-user-tie mr-3 w-4"></i>
                            Voir son manager
                        </a>
                    @endif
                    
                    <button @click="sendWelcomeEmail()" 
                            class="flex items-center w-full px-4 py-2 text-left bg-green-50 hover:bg-green-100 text-green-700 rounded-xl transition-colors duration-200">
                        <i class="fas fa-envelope mr-3 w-4"></i>
                        Renvoyer email de bienvenue
                    </button>
                    
                    @if(!$employee->manager)
                        <button @click="showAssignManagerModal = true" 
                                class="flex items-center w-full px-4 py-2 text-left bg-amber-50 hover:bg-amber-100 text-amber-700 rounded-xl transition-colors duration-200">
                            <i class="fas fa-user-plus mr-3 w-4"></i>
                            Assigner un manager
                        </button>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Main content -->
        <div class="lg:col-span-3 space-y-6">
            <!-- Statistiques -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <!-- Jours depuis création -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 text-center">
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-calendar-plus text-green-600 text-xl"></i>
                    </div>
                    <div class="text-2xl font-bold text-gray-900 mb-1">{{ floor($employee->created_at->diffInDays()) }}</div>
                    <div class="text-gray-500 text-sm">Jours</div>
                </div>
                
                <!-- Connexions réussies -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 text-center">
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-sign-in-alt text-blue-600 text-xl"></i>
                    </div>
                    <div class="text-2xl font-bold text-gray-900 mb-1">{{ $employee->loginHistory()->successful()->count() }}</div>
                    <div class="text-gray-500 text-sm">Connexions</div>
                </div>
                
                <!-- Échecs de connexion -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 text-center">
                    <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-times-circle text-amber-600 text-xl"></i>
                    </div>
                    <div class="text-2xl font-bold text-gray-900 mb-1">{{ $employee->loginHistory()->failed()->count() }}</div>
                    <div class="text-gray-500 text-sm">Échecs</div>
                </div>
                
                <!-- Manager -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 text-center">
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-user-tie text-purple-600 text-xl"></i>
                    </div>
                    <div class="text-2xl font-bold text-gray-900 mb-1">{{ $employee->manager ? '1' : '0' }}</div>
                    <div class="text-gray-500 text-sm">Manager</div>
                </div>
            </div>
            
            <!-- Informations du manager -->
            @if($employee->manager)
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <h3 class="flex items-center text-lg font-semibold text-gray-900">
                            <i class="fas fa-user-tie text-blue-600 mr-2"></i>
                            Manager superviseur
                        </h3>
                        <a href="{{ route('admin.managers.show', $employee->manager) }}" 
                           class="inline-flex items-center px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-lg text-sm transition-colors">
                            Voir détails
                        </a>
                    </div>
                </div>
                <div class="p-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-16 h-16 bg-blue-600 rounded-2xl flex items-center justify-center flex-shrink-0">
                            <span class="text-white font-bold text-xl">
                                {{ substr($employee->manager->name, 0, 1) }}
                            </span>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-xl font-semibold text-gray-900 mb-2">{{ $employee->manager->name }}</h4>
                            <div class="space-y-1">
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-envelope mr-2 w-4"></i>
                                    <a href="mailto:{{ $employee->manager->email }}" class="hover:text-blue-600 transition-colors">
                                        {{ $employee->manager->email }}
                                    </a>
                                </div>
                                @if($employee->manager->phone)
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-phone mr-2 w-4"></i>
                                    <a href="tel:{{ $employee->manager->phone }}" class="hover:text-blue-600 transition-colors">
                                        {{ $employee->manager->phone }}
                                    </a>
                                </div>
                                @endif
                            </div>
                            <div class="flex items-center space-x-3 mt-3">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $employee->manager->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $employee->manager->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $employee->manager->employees()->count() }} employé(s) supervisé(s)
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            
            <!-- Historique récent des connexions -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-gray-50 to-slate-50 px-6 py-4 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <h3 class="flex items-center text-lg font-semibold text-gray-900">
                            <i class="fas fa-history text-gray-600 mr-2"></i>
                            Connexions récentes
                        </h3>
                        <a href="{{ route('admin.login-history.show', ['user_type' => 'Employee', 'user_id' => $employee->id]) }}" 
                           class="inline-flex items-center px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm transition-colors">
                            Voir tout
                        </a>
                    </div>
                </div>
                <div class="p-6">
                    @php
                        $recentLogins = $employee->loginHistory()->latest()->take(5)->get();
                    @endphp
                    
                    @if($recentLogins->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead>
                                    <tr class="border-b border-gray-200">
                                        <th class="text-left py-3 px-2 font-semibold text-gray-700 text-sm">Date/Heure</th>
                                        <th class="text-left py-3 px-2 font-semibold text-gray-700 text-sm">IP</th>
                                        <th class="text-left py-3 px-2 font-semibold text-gray-700 text-sm">Navigateur</th>
                                        <th class="text-left py-3 px-2 font-semibold text-gray-700 text-sm">Statut</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($recentLogins as $login)
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="py-3 px-2">
                                                <div class="text-sm text-gray-900">{{ $login->login_at->format('d/m/Y') }}</div>
                                                <div class="text-xs text-gray-500">{{ $login->login_at->format('H:i:s') }}</div>
                                            </td>
                                            <td class="py-3 px-2">
                                                <code class="bg-gray-100 px-2 py-1 rounded text-xs">{{ $login->ip_address }}</code>
                                            </td>
                                            <td class="py-3 px-2">
                                                <div class="flex items-center">
                                                    @if($login->browser_name == 'Chrome')
                                                        <i class="fab fa-chrome text-yellow-500 mr-1"></i>
                                                    @elseif($login->browser_name == 'Firefox')
                                                        <i class="fab fa-firefox text-orange-500 mr-1"></i>
                                                    @elseif($login->browser_name == 'Safari')
                                                        <i class="fab fa-safari text-blue-500 mr-1"></i>
                                                    @else
                                                        <i class="fas fa-globe mr-1"></i>
                                                    @endif
                                                    <span class="text-sm">{{ $login->browser_name }}</span>
                                                </div>
                                            </td>
                                            <td class="py-3 px-2">
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $login->is_successful ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ $login->is_successful ? 'Réussie' : 'Échouée' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-history text-gray-400 text-2xl"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-500 mb-2">Aucune connexion</h4>
                            <p class="text-gray-400">Cet employé ne s'est pas encore connecté.</p>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Informations système -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-gray-50 to-slate-50 px-6 py-4 border-b border-gray-100">
                    <h3 class="flex items-center text-lg font-semibold text-gray-900">
                        <i class="fas fa-info-circle text-gray-600 mr-2"></i>
                        Informations système
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Dates importantes -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Créé le</label>
                                <div class="text-gray-900">{{ $employee->created_at->format('d/m/Y à H:i') }}</div>
                                <div class="text-xs text-gray-500">{{ $employee->created_at->diffForHumans() }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Dernière modification</label>
                                <div class="text-gray-900">{{ $employee->updated_at->format('d/m/Y à H:i') }}</div>
                                <div class="text-xs text-gray-500">{{ $employee->updated_at->diffForHumans() }}</div>
                            </div>
                        </div>
                        
                        @if($employee->loginHistory()->latest()->first())
                            @php $lastLogin = $employee->loginHistory()->latest()->first(); @endphp
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 mb-1">Dernière connexion</label>
                                    <div class="text-gray-900">{{ $lastLogin->login_at->format('d/m/Y à H:i') }}</div>
                                    <div class="text-xs text-gray-500">{{ $lastLogin->login_at->diffForHumans() }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 mb-1">Dernière IP</label>
                                    <div>
                                        <code class="bg-gray-100 px-2 py-1 rounded text-xs">{{ $lastLogin->ip_address }}</code>
                                    </div>
                                    @if($lastLogin->country || $lastLogin->city)
                                        <div class="text-xs text-gray-500 mt-1">{{ $lastLogin->city }}, {{ $lastLogin->country }}</div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="md:col-span-2">
                                <div class="bg-gradient-to-r from-yellow-50 to-amber-50 border border-yellow-200 rounded-xl p-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-exclamation-triangle text-yellow-600 text-sm"></i>
                                        </div>
                                        <p class="text-yellow-800 text-sm">Cet employé ne s'est jamais connecté.</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        <!-- Informations admin et ID -->
                        <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t border-gray-200">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Admin propriétaire</label>
                                <div class="flex items-center space-x-2">
                                    <div class="w-6 h-6 bg-indigo-600 rounded-lg flex items-center justify-center">
                                        <span class="text-white text-xs font-semibold">
                                            {{ substr($employee->admin->name, 0, 1) }}
                                        </span>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-900 text-sm">{{ $employee->admin->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $employee->admin->shop_name }}</div>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">ID unique</label>
                                <code class="bg-gray-100 px-2 py-1 rounded text-sm">#{{ $employee->id }}</code>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal d'assignation de manager -->
    <div x-show="showAssignManagerModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
         @click.away="showAssignManagerModal = false">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            
            <!-- Header -->
            <div class="bg-gradient-to-r from-amber-500 to-orange-600 px-6 py-4 rounded-t-2xl">
                <div class="flex items-center justify-between text-white">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <h3 class="text-lg font-semibold">Assigner un manager</h3>
                    </div>
                    <button @click="showAssignManagerModal = false" 
                            class="p-1 hover:bg-white/20 rounded-lg transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            <!-- Form -->
            <form method="POST" action="{{ route('admin.employees.update', $employee) }}">
                @csrf
                @method('PUT')
                <div class="p-6">
                    <div class="space-y-2">
                        <label for="modal_manager_id" class="block text-sm font-medium text-gray-700">
                            Sélectionner un manager
                        </label>
                        <select name="manager_id" 
                                id="modal_manager_id" 
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors">
                            <option value="">Choisir un manager...</option>
                            @foreach($employee->admin->managers()->where('is_active', true)->get() as $manager)
                                <option value="{{ $manager->id }}">
                                    {{ $manager->name }} - {{ $manager->email }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Champs cachés pour conserver les autres valeurs -->
                    <input type="hidden" name="name" value="{{ $employee->name }}">
                    <input type="hidden" name="email" value="{{ $employee->email }}">
                    <input type="hidden" name="phone" value="{{ $employee->phone }}">
                    <input type="hidden" name="is_active" value="{{ $employee->is_active ? '1' : '0' }}">
                </div>
                
                <div class="bg-gray-50 px-6 py-4 rounded-b-2xl flex justify-end space-x-3">
                    <button type="button" 
                            @click="showAssignManagerModal = false"
                            class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-xl transition-colors duration-200">
                        <i class="fas fa-times mr-2"></i>
                        Annuler
                    </button>
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white font-medium rounded-xl transition-colors duration-200">
                        <i class="fas fa-save mr-2"></i>
                        Assigner
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function employeeDetailsPage() {
    return {
        showAssignManagerModal: false,
        
        sendWelcomeEmail() {
            if (confirm('Envoyer un email de bienvenue à {{ $employee->name }} ?')) {
                // Simulation - remplacez par une vraie requête AJAX
                this.showNotification('Fonctionnalité à implémenter : envoi d\'email de bienvenue', 'info');
            }
        },
        
        showNotification(message, type = 'info') {
            const colors = {
                success: 'from-green-50 to-emerald-50 border-green-200 text-green-800',
                error: 'from-red-50 to-pink-50 border-red-200 text-red-800',
                info: 'from-blue-50 to-indigo-50 border-blue-200 text-blue-800',
                warning: 'from-amber-50 to-orange-50 border-amber-200 text-amber-800'
            };
            
            const icons = {
                success: 'fa-check-circle',
                error: 'fa-exclamation-circle',
                info: 'fa-info-circle',
                warning: 'fa-exclamation-triangle'
            };
            
            const notification = document.createElement('div');
            notification.className = `fixed top-6 right-6 z-50 bg-gradient-to-r ${colors[type]} border rounded-2xl p-4 shadow-2xl max-w-md transform translate-x-full transition-all duration-300`;
            notification.innerHTML = `
                <div class="flex items-start space-x-3">
                    <div class="w-8 h-8 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
                        <i class="fas ${icons[type]} text-sm"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-sm mb-1">${this.getNotificationTitle(type)}</p>
                        <p class="text-sm opacity-90">${message}</p>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" 
                            class="p-1.5 rounded-lg hover:bg-white/20 transition-colors duration-200 flex-shrink-0">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>
                <div class="absolute bottom-0 left-0 h-1 bg-white/30 rounded-full transition-all duration-5000 animate-progress"></div>
            `;

            document.body.appendChild(notification);

            // Animation d'entrée
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
            }, 100);

            // Auto-remove avec barre de progression
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.remove();
                    }
                }, 300);
            }, 5000);
        },
        
        getNotificationTitle(type) {
            const titles = {
                success: 'Succès',
                error: 'Erreur',
                info: 'Information',
                warning: 'Attention'
            };
            return titles[type] || 'Notification';
        }
    }
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

@keyframes progress {
    from { width: 100%; }
    to { width: 0%; }
}

@keyframes pulse-soft {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.8; transform: scale(1.02); }
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-5px); }
}

.animate-slide-up {
    animation: slideUp 0.5s ease-out forwards;
}

.animate-progress {
    animation: progress 5s linear;
}

.animate-pulse-soft {
    animation: pulse-soft 2s infinite;
}

.animate-float {
    animation: float 3s ease-in-out infinite;
}

/* Amélioration des cartes statistiques */
.stat-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.stat-card:hover {
    transform: translateY(-4px) scale(1.02);
}

/* Tooltips améliorés */
.tooltip-modern {
    position: relative;
}

.tooltip-modern::before {
    content: attr(data-tooltip);
    position: absolute;
    bottom: 125%;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0, 0, 0, 0.9);
    color: white;
    padding: 8px 12px;
    border-radius: 8px;
    font-size: 0.8rem;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    transition: all 0.3s ease;
    z-index: 1000;
}

.tooltip-modern::after {
    content: '';
    position: absolute;
    bottom: 116%;
    left: 50%;
    transform: translateX(-50%);
    width: 0;
    height: 0;
    border-left: 5px solid transparent;
    border-right: 5px solid transparent;
    border-top: 5px solid rgba(0, 0, 0, 0.9);
    opacity: 0;
    transition: all 0.3s ease;
}

.tooltip-modern:hover::before,
.tooltip-modern:hover::after {
    opacity: 1;
}

/* Amélioration des tables */
.table-hover tr:hover {
    background: linear-gradient(90deg, rgba(59, 130, 246, 0.05) 0%, rgba(147, 51, 234, 0.05) 100%);
}

/* Amélioration des liens */
a:hover {
    transition: all 0.2s ease;
}

/* Amélioration des boutons d'action */
.action-button {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.action-button:hover {
    transform: translateY(-1px) scale(1.05);
}

/* Animation des statuts */
.status-indicator {
    position: relative;
    overflow: hidden;
}

.status-indicator::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    animation: shimmer 2s infinite;
}

@keyframes shimmer {
    0% { left: -100%; }
    100% { left: 100%; }
}

/* Responsive amélioré */
@media (max-width: 768px) {
    .tooltip-modern::before {
        font-size: 0.7rem;
        padding: 6px 8px;
    }
}

/* Focus amélioré pour l'accessibilité */
*:focus {
    outline: 2px solid rgba(59, 130, 246, 0.5);
    outline-offset: 2px;
    border-radius: 4px;
}

/* Animation pour les éléments qui apparaissent */
.fade-in {
    opacity: 0;
    animation: fadeIn 0.6s ease-out forwards;
}

@keyframes fadeIn {
    to { opacity: 1; }
}

/* Animation pour les icônes */
.icon-spin:hover {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
@endsection