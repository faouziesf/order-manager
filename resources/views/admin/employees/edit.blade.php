@extends('layouts.admin')

@section('title', 'Modifier l\'Employé')

@section('content')
<div class="animate-fade-in" x-data="editEmployeeForm()">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Modifier l'Employé</h1>
            <p class="text-gray-600">Modifier les informations de {{ $employee->name }}</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.employees.show', $employee) }}" 
               class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white font-medium rounded-xl transition-all duration-200 shadow-sm hover:shadow-md hover:-translate-y-0.5">
                <i class="fas fa-eye mr-2"></i>
                Voir
            </a>
            <a href="{{ route('admin.employees.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-xl transition-all duration-200 shadow-sm hover:shadow-md hover:-translate-y-0.5">
                <i class="fas fa-arrow-left mr-2"></i>
                Retour
            </a>
        </div>
    </div>

    <!-- Main Form Card -->
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <!-- Card Header avec avatar -->
            <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-6 py-6">
                <div class="flex items-center text-white">
                    <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center mr-4 flex-shrink-0">
                        <span class="text-2xl font-bold">
                            {{ substr($employee->name, 0, 1) }}
                        </span>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold">{{ $employee->name }}</h2>
                        <p class="text-green-100 text-sm">{{ $employee->email }}</p>
                        @if($employee->manager)
                            <p class="text-green-100 text-sm">Supervisé par: {{ $employee->manager->name }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Form Content -->
            <div class="p-6">
                <form method="POST" action="{{ route('admin.employees.update', $employee) }}" x-ref="form">
                    @csrf
                    @method('PUT')
                    
                    <!-- Informations personnelles -->
                    <div class="mb-8">
                        <div class="flex items-center mb-4 pb-2 border-b border-green-200">
                            <i class="fas fa-user text-green-600 mr-2"></i>
                            <h3 class="text-lg font-semibold text-green-700">Informations personnelles</h3>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Nom complet -->
                            <div class="space-y-2">
                                <label for="name" class="block text-sm font-medium text-gray-700">
                                    Nom complet <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name', $employee->name) }}" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200 @error('name') border-red-500 ring-2 ring-red-200 @enderror"
                                       required>
                                @error('name')
                                    <div class="mt-2 bg-gradient-to-r from-red-50 to-pink-50 border border-red-200 rounded-xl p-3 animate-shake">
                                        <div class="flex items-center space-x-2">
                                            <div class="w-5 h-5 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                                                <i class="fas fa-exclamation-circle text-red-600 text-xs"></i>
                                            </div>
                                            <p class="text-red-800 text-sm font-medium">{{ $message }}</p>
                                        </div>
                                    </div>
                                @enderror
                            </div>
                            
                            <!-- Email -->
                            <div class="space-y-2">
                                <label for="email" class="block text-sm font-medium text-gray-700">
                                    Adresse email <span class="text-red-500">*</span>
                                </label>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email', $employee->email) }}" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200 @error('email') border-red-500 ring-2 ring-red-200 @enderror"
                                       required>
                                @error('email')
                                    <div class="mt-2 bg-gradient-to-r from-red-50 to-pink-50 border border-red-200 rounded-xl p-3 animate-shake">
                                        <div class="flex items-center space-x-2">
                                            <div class="w-5 h-5 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                                                <i class="fas fa-exclamation-circle text-red-600 text-xs"></i>
                                            </div>
                                            <p class="text-red-800 text-sm font-medium">{{ $message }}</p>
                                        </div>
                                    </div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Téléphone -->
                        <div class="mt-6 space-y-2">
                            <label for="phone" class="block text-sm font-medium text-gray-700">Numéro de téléphone</label>
                            <input type="tel" 
                                   id="phone" 
                                   name="phone" 
                                   value="{{ old('phone', $employee->phone) }}" 
                                   placeholder="+216 XX XXX XXX"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200 @error('phone') border-red-500 ring-2 ring-red-200 @enderror">
                            @error('phone')
                                <div class="mt-2 bg-gradient-to-r from-red-50 to-pink-50 border border-red-200 rounded-xl p-3 animate-shake">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-5 h-5 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                                            <i class="fas fa-exclamation-circle text-red-600 text-xs"></i>
                                        </div>
                                        <p class="text-red-800 text-sm font-medium">{{ $message }}</p>
                                    </div>
                                </div>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Attribution à un manager -->
                    <div class="mb-8">
                        <div class="flex items-center mb-4 pb-2 border-b border-green-200">
                            <i class="fas fa-user-tie text-green-600 mr-2"></i>
                            <h3 class="text-lg font-semibold text-green-700">Attribution à un manager</h3>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="space-y-2">
                                <label for="manager_id" class="block text-sm font-medium text-gray-700">Manager superviseur</label>
                                <select id="manager_id" 
                                        name="manager_id" 
                                        x-model="selectedManagerId"
                                        @change="checkManagerChange"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200 @error('manager_id') border-red-500 ring-2 ring-red-200 @enderror">
                                    <option value="">Aucun manager (employé indépendant)</option>
                                    @foreach($managers as $manager)
                                        <option value="{{ $manager->id }}" {{ old('manager_id', $employee->manager_id) == $manager->id ? 'selected' : '' }}>
                                            {{ $manager->name }} - {{ $manager->email }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('manager_id')
                                    <div class="mt-2 bg-gradient-to-r from-red-50 to-pink-50 border border-red-200 rounded-xl p-3 animate-shake">
                                        <div class="flex items-center space-x-2">
                                            <div class="w-5 h-5 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                                                <i class="fas fa-exclamation-circle text-red-600 text-xs"></i>
                                            </div>
                                            <p class="text-red-800 text-sm font-medium">{{ $message }}</p>
                                        </div>
                                    </div>
                                @enderror
                                <p class="text-gray-500 text-sm">
                                    Modifier l'assignation du manager superviseur de cet employé.
                                </p>
                            </div>
                            
                            <!-- Alerte changement manager -->
                            <div x-show="managerChangeDetected" 
                                 x-transition
                                 class="bg-gradient-to-r from-yellow-50 to-amber-50 border border-yellow-200 rounded-xl p-4">
                                <div class="flex items-start space-x-3">
                                    <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-exclamation-triangle text-yellow-600 text-sm"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-yellow-900">Changement détecté</h4>
                                        <p class="text-yellow-800 text-sm" x-text="managerChangeText"></p>
                                    </div>
                                </div>
                            </div>
                            
                            @if($employee->manager)
                                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-info-circle text-blue-600 text-sm"></i>
                                        </div>
                                        <div>
                                            <span class="font-semibold text-blue-900">Manager actuel :</span>
                                            <span class="text-blue-800">{{ $employee->manager->name }} ({{ $employee->manager->email }})</span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Modifier le mot de passe -->
                    <div class="mb-8">
                        <div class="flex items-center mb-4 pb-2 border-b border-green-200">
                            <i class="fas fa-key text-green-600 mr-2"></i>
                            <h3 class="text-lg font-semibold text-green-700">Modifier le mot de passe</h3>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-info-circle text-blue-600 text-sm"></i>
                                    </div>
                                    <p class="text-blue-800 text-sm">
                                        Laissez vide si vous ne souhaitez pas modifier le mot de passe
                                    </p>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Nouveau mot de passe -->
                                <div class="space-y-2">
                                    <label for="password" class="block text-sm font-medium text-gray-700">Nouveau mot de passe</label>
                                    <div class="relative">
                                        <input type="password" 
                                               id="password" 
                                               name="password" 
                                               class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200 @error('password') border-red-500 ring-2 ring-red-200 @enderror"
                                               x-model="password"
                                               @input="checkPasswordMatch">
                                        <button type="button" 
                                                @click="togglePassword('password')"
                                                class="absolute right-3 top-1/2 transform -translate-y-1/2 p-1 text-gray-400 hover:text-gray-600 transition-colors">
                                            <i class="fas fa-eye" x-ref="passwordIcon"></i>
                                        </button>
                                    </div>
                                    @error('password')
                                        <div class="mt-2 bg-gradient-to-r from-red-50 to-pink-50 border border-red-200 rounded-xl p-3 animate-shake">
                                            <div class="flex items-center space-x-2">
                                                <div class="w-5 h-5 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <i class="fas fa-exclamation-circle text-red-600 text-xs"></i>
                                                </div>
                                                <p class="text-red-800 text-sm font-medium">{{ $message }}</p>
                                            </div>
                                        </div>
                                    @enderror
                                    <p class="text-gray-500 text-sm">Minimum 8 caractères</p>
                                </div>
                                
                                <!-- Confirmation nouveau mot de passe -->
                                <div class="space-y-2">
                                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmer le nouveau mot de passe</label>
                                    <div class="relative">
                                        <input type="password" 
                                               id="password_confirmation" 
                                               name="password_confirmation" 
                                               class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200"
                                               :class="{ 'border-red-500 ring-2 ring-red-200': !passwordMatch && passwordConfirmation.length > 0 && password.length > 0, 'border-green-500 ring-2 ring-green-200': passwordMatch && passwordConfirmation.length > 0 && password.length > 0 }"
                                               x-model="passwordConfirmation"
                                               @input="checkPasswordMatch">
                                        <button type="button" 
                                                @click="togglePassword('password_confirmation')"
                                                class="absolute right-3 top-1/2 transform -translate-y-1/2 p-1 text-gray-400 hover:text-gray-600 transition-colors">
                                            <i class="fas fa-eye" x-ref="passwordConfirmationIcon"></i>
                                        </button>
                                    </div>
                                    <div x-show="!passwordMatch && passwordConfirmation.length > 0 && password.length > 0" 
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0 scale-95"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         class="mt-2 bg-gradient-to-r from-red-50 to-pink-50 border border-red-200 rounded-xl p-3">
                                        <div class="flex items-center space-x-2">
                                            <div class="w-5 h-5 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                                                <i class="fas fa-exclamation-circle text-red-600 text-xs"></i>
                                            </div>
                                            <p class="text-red-800 text-sm font-medium">Les mots de passe ne correspondent pas</p>
                                        </div>
                                    </div>
                                    <div x-show="passwordMatch && passwordConfirmation.length > 0 && password.length > 0" 
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0 scale-95"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         class="mt-2 bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-xl p-3">
                                        <div class="flex items-center space-x-2">
                                            <div class="w-5 h-5 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                                                <i class="fas fa-check-circle text-green-600 text-xs"></i>
                                            </div>
                                            <p class="text-green-800 text-sm font-medium">Les mots de passe correspondent</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Paramètres du compte et statistiques -->
                    <div class="mb-8">
                        <div class="flex items-center mb-4 pb-2 border-b border-green-200">
                            <i class="fas fa-cog text-green-600 mr-2"></i>
                            <h3 class="text-lg font-semibold text-green-700">Paramètres du compte</h3>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Switch compte actif -->
                            <div class="bg-gray-50 rounded-xl p-4">
                                <label class="flex items-start space-x-3 cursor-pointer">
                                    <input type="checkbox" 
                                           id="is_active" 
                                           name="is_active" 
                                           value="1" 
                                           {{ old('is_active', $employee->is_active) ? 'checked' : '' }}
                                           class="mt-1 w-5 h-5 text-green-600 border-gray-300 rounded focus:ring-green-500 focus:ring-2">
                                    <div>
                                        <div class="font-semibold text-gray-900">Compte actif</div>
                                        <p class="text-sm text-gray-600">L'employé peut se connecter</p>
                                    </div>
                                </label>
                            </div>
                            
                            <!-- Statistiques -->
                            <div class="bg-gray-50 rounded-xl p-4">
                                <h4 class="font-semibold text-gray-900 mb-3">Statistiques</h4>
                                <div class="space-y-2">
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600 text-sm">Manager assigné:</span>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $employee->manager ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ $employee->manager ? $employee->manager->name : 'Aucun' }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600 text-sm">Créé le:</span>
                                        <span class="text-sm text-gray-900">{{ $employee->created_at->format('d/m/Y') }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600 text-sm">Dernière modification:</span>
                                        <span class="text-sm text-gray-900">{{ $employee->updated_at->format('d/m/Y') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Informations du compte -->
                    <div class="mb-8">
                        <div class="flex items-center mb-4 pb-2 border-b border-green-200">
                            <i class="fas fa-history text-green-600 mr-2"></i>
                            <h3 class="text-lg font-semibold text-green-700">Informations du compte</h3>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Compte créé -->
                            <div class="bg-gradient-to-br from-green-50 to-emerald-50 border border-green-200 rounded-xl p-6 text-center">
                                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                                    <i class="fas fa-calendar-alt text-green-600 text-xl"></i>
                                </div>
                                <h4 class="font-semibold text-green-900 mb-2">Compte créé</h4>
                                <p class="text-green-800 text-sm mb-1">{{ $employee->created_at->format('d/m/Y à H:i') }}</p>
                                <p class="text-green-600 text-xs">{{ $employee->created_at->diffForHumans() }}</p>
                            </div>
                            
                            <!-- Dernière modification -->
                            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-6 text-center">
                                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                                    <i class="fas fa-edit text-blue-600 text-xl"></i>
                                </div>
                                <h4 class="font-semibold text-blue-900 mb-2">Dernière modification</h4>
                                <p class="text-blue-800 text-sm mb-1">{{ $employee->updated_at->format('d/m/Y à H:i') }}</p>
                                <p class="text-blue-600 text-xs">{{ $employee->updated_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Boutons d'action -->
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
                        <!-- Bouton supprimer -->
                        <button type="button" 
                                @click="showDeleteModal = true"
                                class="inline-flex items-center px-4 py-2 bg-red-500 hover:bg-red-600 text-white font-medium rounded-xl transition-all duration-200 shadow-sm hover:shadow-md hover:-translate-y-0.5">
                            <i class="fas fa-trash mr-2"></i>
                            Supprimer
                        </button>
                        
                        <!-- Actions principales -->
                        <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
                            <a href="{{ route('admin.employees.index') }}" 
                               class="inline-flex items-center justify-center px-6 py-3 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-xl transition-all duration-200 shadow-sm hover:shadow-md hover:-translate-y-0.5">
                                <i class="fas fa-times mr-2"></i>
                                Annuler
                            </a>
                            <button type="submit" 
                                    class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-medium rounded-xl transition-all duration-200 shadow-sm hover:shadow-md hover:-translate-y-0.5">
                                <i class="fas fa-save mr-2"></i>
                                Sauvegarder
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal de confirmation de suppression -->
    <div x-show="showDeleteModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
         @click.away="showDeleteModal = false">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            
            <!-- Header -->
            <div class="bg-gradient-to-r from-red-500 to-pink-600 px-6 py-4 rounded-t-2xl">
                <div class="flex items-center justify-between text-white">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <h3 class="text-lg font-semibold">Confirmer la suppression</h3>
                    </div>
                    <button @click="showDeleteModal = false" 
                            class="p-1 hover:bg-white/20 rounded-lg transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            <!-- Body -->
            <div class="p-6">
                <p class="text-gray-700 mb-4">
                    Êtes-vous sûr de vouloir supprimer l'employé <strong>{{ $employee->name }}</strong> ?
                </p>
                <div class="bg-gradient-to-r from-yellow-50 to-amber-50 border border-yellow-200 rounded-xl p-4">
                    <div class="flex items-start space-x-3">
                        <div class="w-6 h-6 bg-yellow-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-yellow-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-yellow-900 text-sm">Attention :</p>
                            <p class="text-yellow-800 text-sm">Cette action est irréversible.</p>
                            @if($employee->manager)
                                <p class="text-yellow-800 text-sm">
                                    Cet employé est actuellement supervisé par {{ $employee->manager->name }}.
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="bg-gray-50 px-6 py-4 rounded-b-2xl flex justify-end space-x-3">
                <button @click="showDeleteModal = false" 
                        class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-xl transition-colors duration-200">
                    <i class="fas fa-times mr-2"></i>
                    Annuler
                </button>
                <form method="POST" action="{{ route('admin.employees.destroy', $employee) }}" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-red-500 hover:bg-red-600 text-white font-medium rounded-xl transition-colors duration-200">
                        <i class="fas fa-trash mr-2"></i>
                        Supprimer définitivement
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function editEmployeeForm() {
    return {
        password: '',
        passwordConfirmation: '',
        passwordMatch: true,
        selectedManagerId: '{{ old('manager_id', $employee->manager_id ?? '') }}',
        originalManagerId: '{{ $employee->manager_id ?? '' }}',
        managerChangeDetected: false,
        managerChangeText: '',
        showDeleteModal: false,
        
        init() {
            this.checkPasswordMatch();
        },
        
        checkPasswordMatch() {
            if (this.password.length === 0 && this.passwordConfirmation.length === 0) {
                this.passwordMatch = true;
            } else {
                this.passwordMatch = this.password === this.passwordConfirmation;
            }
        },
        
        checkManagerChange() {
            const originalManagerName = '{{ $employee->manager ? $employee->manager->name : "Aucun" }}';
            const select = document.getElementById('manager_id');
            const selectedOption = select.options[select.selectedIndex];
            const newManagerName = selectedOption.value ? selectedOption.text.split(' - ')[0] : 'Aucun';
            
            if (this.originalManagerId !== this.selectedManagerId) {
                this.managerChangeDetected = true;
                this.managerChangeText = `Manager passera de "${originalManagerName}" à "${newManagerName}"`;
            } else {
                this.managerChangeDetected = false;
            }
        },
        
        togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = this.$refs[fieldId + 'Icon'];
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    }
}

// Animation d'apparition progressive
document.addEventListener('DOMContentLoaded', function() {
    const sections = document.querySelectorAll('.mb-8');
    sections.forEach((section, index) => {
        setTimeout(() => {
            section.classList.add('animate-slide-up');
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

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-2px); }
    20%, 40%, 60%, 80% { transform: translateX(2px); }
}

.animate-slide-up {
    animation: slideUp 0.5s ease-out forwards;
}

.animate-shake {
    animation: shake 0.5s ease-in-out;
}

/* Amélioration des inputs avec erreur */
.border-red-500 {
    animation: shake 0.5s ease-in-out;
}

/* Animation des messages de validation */
[x-transition] {
    transition-property: opacity, transform;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

/* Hover effects pour les boutons */
button:hover, a:hover {
    transform: translateY(-1px);
}

/* Focus visible pour l'accessibilité */
input:focus, select:focus, button:focus {
    outline: 2px solid transparent;
    outline-offset: 2px;
}

/* Animation des checkboxes */
input[type="checkbox"]:checked {
    transform: scale(1.1);
    transition: transform 0.2s ease;
}

/* Animation pour les alertes de changement */
.animate-pulse-soft {
    animation: pulse-soft 2s infinite;
}

@keyframes pulse-soft {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.8; }
}
</style>
@endsection