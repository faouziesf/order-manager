@extends('layouts.admin')

@section('title', 'Créer un Employé')

@section('content')
<div class="animate-fade-in" x-data="createEmployeeForm()">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Nouvel Employé</h1>
            <p class="text-gray-600">Créer un nouveau compte employé</p>
        </div>
        <a href="{{ route('admin.employees.index') }}" 
           class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-xl transition-all duration-200 shadow-sm hover:shadow-md hover:-translate-y-0.5">
            <i class="fas fa-arrow-left mr-2"></i>
            Retour
        </a>
    </div>

    <!-- Main Form Card -->
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <!-- Card Header -->
            <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-6 py-4">
                <div class="flex items-center text-white">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center mr-3">
                        <i class="fas fa-user-friends text-xl"></i>
                    </div>
                    <h2 class="text-xl font-semibold">Informations de l'Employé</h2>
                </div>
            </div>

            <!-- Form Content -->
            <div class="p-6">
                <form method="POST" action="{{ route('admin.employees.store') }}" x-ref="form">
                    @csrf
                    
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
                                       value="{{ old('name') }}" 
                                       placeholder="Ex: Fatma Ben Salem"
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
                                       value="{{ old('email') }}" 
                                       placeholder="employee@example.com"
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
                                   value="{{ old('phone') }}" 
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
                        
                        <div class="space-y-2">
                            <label for="manager_id" class="block text-sm font-medium text-gray-700">Manager superviseur</label>
                            <select id="manager_id" 
                                    name="manager_id" 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200 @error('manager_id') border-red-500 ring-2 ring-red-200 @enderror">
                                <option value="">Aucun manager (employé indépendant)</option>
                                @foreach($managers as $manager)
                                    <option value="{{ $manager->id }}" {{ old('manager_id') == $manager->id ? 'selected' : '' }}>
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
                                L'employé peut être assigné à un manager pour une supervision ou travailler de manière indépendante.
                            </p>
                        </div>
                    </div>
                    
                    <!-- Informations de connexion -->
                    <div class="mb-8">
                        <div class="flex items-center mb-4 pb-2 border-b border-green-200">
                            <i class="fas fa-key text-green-600 mr-2"></i>
                            <h3 class="text-lg font-semibold text-green-700">Informations de connexion</h3>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Mot de passe -->
                            <div class="space-y-2">
                                <label for="password" class="block text-sm font-medium text-gray-700">
                                    Mot de passe <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="password" 
                                           id="password" 
                                           name="password" 
                                           placeholder="Minimum 8 caractères"
                                           class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200 @error('password') border-red-500 ring-2 ring-red-200 @enderror"
                                           required
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
                            
                            <!-- Confirmation mot de passe -->
                            <div class="space-y-2">
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                                    Confirmer le mot de passe <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="password" 
                                           id="password_confirmation" 
                                           name="password_confirmation" 
                                           placeholder="Confirmer le mot de passe"
                                           class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200"
                                           :class="{ 'border-red-500 ring-2 ring-red-200': !passwordMatch && passwordConfirmation.length > 0, 'border-green-500 ring-2 ring-green-200': passwordMatch && passwordConfirmation.length > 0 }"
                                           required
                                           x-model="passwordConfirmation"
                                           @input="checkPasswordMatch">
                                    <button type="button" 
                                            @click="togglePassword('password_confirmation')"
                                            class="absolute right-3 top-1/2 transform -translate-y-1/2 p-1 text-gray-400 hover:text-gray-600 transition-colors">
                                        <i class="fas fa-eye" x-ref="passwordConfirmationIcon"></i>
                                    </button>
                                </div>
                                <div x-show="!passwordMatch && passwordConfirmation.length > 0" 
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
                                <div x-show="passwordMatch && passwordConfirmation.length > 0" 
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
                    
                    <!-- Paramètres du compte -->
                    <div class="mb-8">
                        <div class="flex items-center mb-4 pb-2 border-b border-green-200">
                            <i class="fas fa-cog text-green-600 mr-2"></i>
                            <h3 class="text-lg font-semibold text-green-700">Paramètres du compte</h3>
                        </div>
                        
                        <div class="bg-gray-50 rounded-xl p-4">
                            <label class="flex items-start space-x-3 cursor-pointer">
                                <input type="checkbox" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1" 
                                       checked
                                       class="mt-1 w-5 h-5 text-green-600 border-gray-300 rounded focus:ring-green-500 focus:ring-2">
                                <div>
                                    <div class="font-semibold text-gray-900">Compte actif</div>
                                    <p class="text-sm text-gray-600">L'employé pourra se connecter immédiatement</p>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Informations importantes -->
                    <div class="mb-8">
                        <div class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-2xl p-6">
                            <div class="flex items-start space-x-4">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                                        <i class="fas fa-info-circle text-green-600 text-xl"></i>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-lg font-semibold text-green-900 mb-3">Informations importantes</h4>
                                    <ul class="space-y-2 text-green-800">
                                        <li class="flex items-start">
                                            <i class="fas fa-check text-green-600 mr-2 mt-1 text-sm"></i>
                                            L'employé aura accès à un tableau de bord simplifié
                                        </li>
                                        <li class="flex items-start">
                                            <i class="fas fa-check text-green-600 mr-2 mt-1 text-sm"></i>
                                            Il pourra consulter et traiter les commandes assignées
                                        </li>
                                        <li class="flex items-start">
                                            <i class="fas fa-check text-green-600 mr-2 mt-1 text-sm"></i>
                                            Si assigné à un manager, ce dernier pourra superviser son travail
                                        </li>
                                        <li class="flex items-start">
                                            <i class="fas fa-check text-green-600 mr-2 mt-1 text-sm"></i>
                                            Un email de bienvenue sera envoyé avec les informations de connexion
                                        </li>
                                        <li class="flex items-start">
                                            <i class="fas fa-check text-green-600 mr-2 mt-1 text-sm"></i>
                                            L'employé ne peut pas gérer d'autres comptes utilisateurs
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Boutons d'action -->
                    <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-4">
                        <a href="{{ route('admin.employees.index') }}" 
                           class="inline-flex items-center justify-center px-6 py-3 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-xl transition-all duration-200 shadow-sm hover:shadow-md hover:-translate-y-0.5">
                            <i class="fas fa-times mr-2"></i>
                            Annuler
                        </a>
                        <button type="submit" 
                                class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-medium rounded-xl transition-all duration-200 shadow-sm hover:shadow-md hover:-translate-y-0.5"
                                :disabled="!canSubmit"
                                :class="{ 'opacity-50 cursor-not-allowed': !canSubmit }">
                            <i class="fas fa-save mr-2"></i>
                            Créer l'Employé
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function createEmployeeForm() {
    return {
        password: '',
        passwordConfirmation: '',
        passwordMatch: false,
        
        init() {
            this.checkPasswordMatch();
        },
        
        checkPasswordMatch() {
            this.passwordMatch = this.password === this.passwordConfirmation && this.password.length > 0;
        },
        
        get canSubmit() {
            return this.password.length >= 8 && this.passwordMatch;
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
</style>
@endsection