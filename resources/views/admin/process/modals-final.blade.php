{{-- MODALES TAILWIND FINALES AVEC GESTION JQUERY CORRIGÉE --}}

{{-- MODAL TENTATIVE D'APPEL --}}
<div id="call-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm hidden">
    <div class="bg-white rounded-3xl shadow-2xl border border-gray-200 max-w-md w-full max-h-[90vh] overflow-y-auto transform transition-all duration-300">
        <!-- Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-yellow-500 rounded-2xl flex items-center justify-center text-white">
                    <i class="fas fa-phone-slash"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900">Tentative d'appel - Ne répond pas</h3>
            </div>
            <button onclick="closeModal('call-modal')" 
                    class="w-10 h-10 bg-gray-100 hover:bg-gray-200 rounded-xl flex items-center justify-center text-gray-500 hover:text-gray-700 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <!-- Body -->
        <div class="p-6 space-y-6">
            <div class="space-y-3">
                <label class="flex items-center gap-2 text-sm font-semibold text-gray-700">
                    <i class="fas fa-sticky-note text-gray-500"></i>
                    Notes sur la tentative <span class="text-red-500">*</span>
                </label>
                <textarea id="call-notes" 
                          rows="4" 
                          placeholder="Décrivez ce qui s'est passé lors de l'appel..."
                          class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl bg-gray-50 transition-all duration-200 focus:border-yellow-500 focus:bg-white focus:outline-none resize-none"></textarea>
                <p class="text-sm text-gray-600">
                    Exemple: "Sonnerie mais pas de réponse", "Numéro occupé", "Éteint", "Demande de rappeler plus tard", etc.
                </p>
            </div>
            
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                <div class="flex items-start gap-3">
                    <div class="w-6 h-6 bg-blue-500 rounded-lg flex items-center justify-center text-white flex-shrink-0 mt-0.5">
                        <i class="fas fa-info-circle text-xs"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold text-blue-900 mb-1">Action</h4>
                        <p class="text-blue-800 text-sm">Cette action va incrémenter le compteur de tentatives et marquer l'heure de la dernière tentative.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="flex items-center justify-end gap-3 p-6 border-t border-gray-200">
            <button onclick="closeModal('call-modal')" 
                    class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-semibold transition-colors">
                <i class="fas fa-times mr-2"></i>Annuler
            </button>
            <button id="submit-call-action"
                    class="px-6 py-3 bg-yellow-500 hover:bg-yellow-600 text-white rounded-xl font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="fas fa-save mr-2"></i>Enregistrer la tentative
            </button>
        </div>
    </div>
</div>

{{-- MODAL CONFIRMATION --}}
<div id="confirm-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm hidden">
    <div class="bg-white rounded-3xl shadow-2xl border border-gray-200 max-w-4xl w-full max-h-[90vh] overflow-y-auto transform transition-all duration-300">
        <!-- Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-green-500 rounded-2xl flex items-center justify-center text-white">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900">Confirmer la commande</h3>
            </div>
            <button onclick="closeModal('confirm-modal')" 
                    class="w-10 h-10 bg-gray-100 hover:bg-gray-200 rounded-xl flex items-center justify-center text-gray-500 hover:text-gray-700 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <!-- Body -->
        <div class="p-6 space-y-6">
            
            <!-- Alert validation -->
            <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-green-500 rounded-xl flex items-center justify-center text-white">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold text-green-900">Validation réussie!</h4>
                        <p class="text-green-800 text-sm">Tous les champs obligatoires sont remplis et le panier contient des produits en stock.</p>
                    </div>
                </div>
            </div>
            
            <!-- Formulaire prix et infos -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div class="space-y-3">
                        <label class="flex items-center gap-2 text-sm font-semibold text-gray-700">
                            <i class="fas fa-money-bill-wave text-gray-500"></i>
                            Prix total de la commande (TND) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" 
                                   step="0.001" 
                                   id="confirm-price" 
                                   placeholder="0.000" 
                                   min="0.001"
                                   class="w-full px-4 py-3 pr-16 border-2 border-gray-200 rounded-xl bg-gray-50 transition-all duration-200 focus:border-green-500 focus:bg-white focus:outline-none">
                            <span class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-500 font-medium">TND</span>
                        </div>
                        <p class="text-sm text-gray-600">Prix total final négocié avec le client (incluant tout)</p>
                    </div>
                    
                    <div class="space-y-3">
                        <label class="flex items-center gap-2 text-sm font-semibold text-gray-700">
                            <i class="fas fa-comment text-gray-500"></i>
                            Notes de confirmation (optionnel)
                        </label>
                        <textarea id="confirm-notes" 
                                  rows="3" 
                                  placeholder="Informations supplémentaires sur la confirmation..."
                                  class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl bg-gray-50 transition-all duration-200 focus:border-green-500 focus:bg-white focus:outline-none resize-none"></textarea>
                    </div>
                </div>
                
                <div class="space-y-3">
                    <label class="flex items-center gap-2 text-sm font-semibold text-gray-700">
                        <i class="fas fa-info-circle text-gray-500"></i>
                        Changements à venir
                    </label>
                    <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                        <div class="space-y-2 text-sm text-green-800">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-arrow-right text-green-600"></i>
                                <span>Statut: <strong>Confirmée</strong></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="fas fa-minus text-green-600"></i>
                                <span>Stock sera décrémenté automatiquement</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="fas fa-money-bill text-green-600"></i>
                                <span>Prix total sera mis à jour</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="fas fa-user text-green-600"></i>
                                <span>Infos client sauvegardées</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Récapitulatif des données -->
            <div class="space-y-4">
                <h4 class="flex items-center gap-2 text-lg font-semibold text-gray-900">
                    <i class="fas fa-clipboard-list text-gray-600"></i>
                    Récapitulatif des données
                </h4>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Informations client -->
                    <div class="bg-gray-50 rounded-2xl overflow-hidden">
                        <div class="bg-gray-100 px-4 py-3 border-b border-gray-200">
                            <h5 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Informations Client</h5>
                        </div>
                        <div class="p-4 space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Nom:</span>
                                <span class="font-semibold text-gray-900" id="confirm-customer-name">-</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Téléphone:</span>
                                <span class="font-semibold text-gray-900" id="confirm-customer-phone">-</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Lieu:</span>
                                <span class="font-semibold text-gray-900" id="confirm-customer-location">-</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Adresse:</span>
                                <div class="font-semibold text-gray-900 mt-1" id="confirm-customer-address">-</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Produits commandés -->
                    <div class="bg-gray-50 rounded-2xl overflow-hidden">
                        <div class="bg-gray-100 px-4 py-3 border-b border-gray-200">
                            <h5 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Produits Commandés</h5>
                        </div>
                        <div class="p-4" id="confirm-products-list">
                            <div class="text-sm text-gray-500">Chargement...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="flex items-center justify-end gap-3 p-6 border-t border-gray-200">
            <button onclick="closeModal('confirm-modal')" 
                    class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-semibold transition-colors">
                <i class="fas fa-times mr-2"></i>Annuler
            </button>
            <button id="submit-confirm-action"
                    class="px-6 py-3 bg-green-500 hover:bg-green-600 text-white rounded-xl font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="fas fa-check-circle mr-2"></i>Confirmer définitivement
            </button>
        </div>
    </div>
</div>

{{-- MODAL ANNULATION --}}
<div id="cancel-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm hidden">
    <div class="bg-white rounded-3xl shadow-2xl border border-gray-200 max-w-md w-full max-h-[90vh] overflow-y-auto transform transition-all duration-300">
        <!-- Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-red-500 rounded-2xl flex items-center justify-center text-white">
                    <i class="fas fa-times-circle"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900">Annuler la commande</h3>
            </div>
            <button onclick="closeModal('cancel-modal')" 
                    class="w-10 h-10 bg-gray-100 hover:bg-gray-200 rounded-xl flex items-center justify-center text-gray-500 hover:text-gray-700 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <!-- Body -->
        <div class="p-6 space-y-6">
            
            <!-- Alert danger -->
            <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                <div class="flex items-start gap-3">
                    <div class="w-6 h-6 bg-red-500 rounded-lg flex items-center justify-center text-white flex-shrink-0 mt-0.5">
                        <i class="fas fa-exclamation-triangle text-xs"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold text-red-900 mb-1">Attention</h4>
                        <p class="text-red-800 text-sm">Cette action changera définitivement le statut de la commande à "Annulée".</p>
                    </div>
                </div>
            </div>
            
            <div class="space-y-3">
                <label class="flex items-center gap-2 text-sm font-semibold text-gray-700">
                    <i class="fas fa-sticky-note text-gray-500"></i>
                    Raison de l'annulation <span class="text-red-500">*</span>
                </label>
                <textarea id="cancel-notes" 
                          rows="4" 
                          placeholder="Expliquez pourquoi cette commande est annulée..."
                          class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl bg-gray-50 transition-all duration-200 focus:border-red-500 focus:bg-white focus:outline-none resize-none"></textarea>
                <p class="text-sm text-gray-600">
                    Exemple: "Client a changé d'avis", "Produit non disponible", "Adresse incorrecte", etc.
                </p>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="flex items-center justify-end gap-3 p-6 border-t border-gray-200">
            <button onclick="closeModal('cancel-modal')" 
                    class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-semibold transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Retour
            </button>
            <button id="submit-cancel-action"
                    class="px-6 py-3 bg-red-500 hover:bg-red-600 text-white rounded-xl font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="fas fa-times-circle mr-2"></i>Annuler définitivement
            </button>
        </div>
    </div>
</div>

{{-- MODAL PLANIFICATION --}}
<div id="schedule-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm hidden">
    <div class="bg-white rounded-3xl shadow-2xl border border-gray-200 max-w-md w-full max-h-[90vh] overflow-y-auto transform transition-all duration-300">
        <!-- Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-500 rounded-2xl flex items-center justify-center text-white">
                    <i class="fas fa-calendar-plus"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900">Dater la commande</h3>
            </div>
            <button onclick="closeModal('schedule-modal')" 
                    class="w-10 h-10 bg-gray-100 hover:bg-gray-200 rounded-xl flex items-center justify-center text-gray-500 hover:text-gray-700 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <!-- Body -->
        <div class="p-6 space-y-6">
            
            <div class="space-y-3">
                <label class="flex items-center gap-2 text-sm font-semibold text-gray-700">
                    <i class="fas fa-calendar-alt text-gray-500"></i>
                    Date de rappel <span class="text-red-500">*</span>
                </label>
                <input type="date" 
                       id="schedule-date" 
                       class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl bg-gray-50 transition-all duration-200 focus:border-blue-500 focus:bg-white focus:outline-none">
                <p class="text-sm text-gray-600">
                    La commande apparaîtra dans la file "Datée" à partir de cette date
                </p>
            </div>
            
            <div class="space-y-3">
                <label class="flex items-center gap-2 text-sm font-semibold text-gray-700">
                    <i class="fas fa-comment text-gray-500"></i>
                    Notes de planification (optionnel)
                </label>
                <textarea id="schedule-notes" 
                          rows="3" 
                          placeholder="Raison de la planification et informations pour le rappel..."
                          class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl bg-gray-50 transition-all duration-200 focus:border-blue-500 focus:bg-white focus:outline-none resize-none"></textarea>
            </div>
            
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                <div class="flex items-start gap-3">
                    <div class="w-6 h-6 bg-blue-500 rounded-lg flex items-center justify-center text-white flex-shrink-0 mt-0.5">
                        <i class="fas fa-info-circle text-xs"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold text-blue-900 mb-1">Action</h4>
                        <p class="text-blue-800 text-sm">La commande passera au statut <strong>"Datée"</strong> et ses compteurs de tentatives seront remis à zéro.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="flex items-center justify-end gap-3 p-6 border-t border-gray-200">
            <button onclick="closeModal('schedule-modal')" 
                    class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-semibold transition-colors">
                <i class="fas fa-times mr-2"></i>Annuler
            </button>
            <button id="submit-schedule-action"
                    class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-xl font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="fas fa-calendar-check mr-2"></i>Dater la commande
            </button>
        </div>
    </div>
</div>

{{-- MODAL RÉACTIVATION --}}
<div id="reactivate-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm hidden">
    <div class="bg-white rounded-3xl shadow-2xl border border-gray-200 max-w-md w-full max-h-[90vh] overflow-y-auto transform transition-all duration-300">
        <!-- Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-green-500 rounded-2xl flex items-center justify-center text-white">
                    <i class="fas fa-play-circle"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900">Réactiver la commande</h3>
            </div>
            <button onclick="closeModal('reactivate-modal')" 
                    class="w-10 h-10 bg-gray-100 hover:bg-gray-200 rounded-xl flex items-center justify-center text-gray-500 hover:text-gray-700 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <!-- Body -->
        <div class="p-6 space-y-6">
            
            <!-- Alert success -->
            <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 bg-green-500 rounded-xl flex items-center justify-center text-white flex-shrink-0">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold text-green-900 mb-1">Stock disponible!</h4>
                        <p class="text-green-800 text-sm">Tous les produits de cette commande sont maintenant en stock et peuvent être traités normalement.</p>
                    </div>
                </div>
            </div>
            
            <div class="space-y-3">
                <label class="flex items-center gap-2 text-sm font-semibold text-gray-700">
                    <i class="fas fa-comment text-gray-500"></i>
                    Notes de réactivation (optionnel)
                </label>
                <textarea id="reactivate-notes" 
                          rows="3" 
                          placeholder="Notes sur la réactivation de cette commande..."
                          class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl bg-gray-50 transition-all duration-200 focus:border-green-500 focus:bg-white focus:outline-none resize-none"></textarea>
            </div>
            
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                <div class="flex items-start gap-3">
                    <div class="w-6 h-6 bg-blue-500 rounded-lg flex items-center justify-center text-white flex-shrink-0 mt-0.5">
                        <i class="fas fa-arrow-right text-xs"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold text-blue-900 mb-1">Action</h4>
                        <p class="text-blue-800 text-sm">La commande retournera au statut <strong>"Nouvelle"</strong> et ne sera plus suspendue. Ses compteurs de tentatives seront remis à zéro.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="flex items-center justify-end gap-3 p-6 border-t border-gray-200">
            <button onclick="closeModal('reactivate-modal')" 
                    class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-semibold transition-colors">
                <i class="fas fa-times mr-2"></i>Annuler
            </button>
            <button id="submit-reactivate-action"
                    class="px-6 py-3 bg-green-500 hover:bg-green-600 text-white rounded-xl font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                <i class="fas fa-play-circle mr-2"></i>Réactiver définitivement
            </button>
        </div>
    </div>
</div>

{{-- MODAL HISTORIQUE --}}
<div id="history-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm hidden">
    <div class="bg-white rounded-3xl shadow-2xl border border-gray-200 max-w-4xl w-full max-h-[90vh] overflow-y-auto transform transition-all duration-300">
        <!-- Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-purple-500 rounded-2xl flex items-center justify-center text-white">
                    <i class="fas fa-history"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900">Historique de la commande</h3>
            </div>
            <button onclick="closeModal('history-modal')" 
                    class="w-10 h-10 bg-gray-100 hover:bg-gray-200 rounded-xl flex items-center justify-center text-gray-500 hover:text-gray-700 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <!-- Body -->
        <div class="p-6">
            <div id="history-content" class="space-y-4">
                <div class="text-center py-8">
                    <div class="w-12 h-12 bg-purple-500 rounded-2xl flex items-center justify-center text-white text-xl mx-auto mb-4 animate-spin">
                        <i class="fas fa-spinner"></i>
                    </div>
                    <p class="text-gray-600">Chargement de l'historique...</p>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="flex items-center justify-end gap-3 p-6 border-t border-gray-200">
            <button onclick="closeModal('history-modal')" 
                    class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-semibold transition-colors">
                <i class="fas fa-times mr-2"></i>Fermer
            </button>
        </div>
    </div>
</div>

{{-- MODAL DOUBLONS --}}
<div id="duplicates-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm hidden">
    <div class="bg-white rounded-3xl shadow-2xl border border-gray-200 max-w-6xl w-full max-h-[90vh] overflow-y-auto transform transition-all duration-300">
        <!-- Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-yellow-500 rounded-2xl flex items-center justify-center text-white">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900">Commandes doublons détectées</h3>
            </div>
            <button onclick="closeModal('duplicates-modal')" 
                    class="w-10 h-10 bg-gray-100 hover:bg-gray-200 rounded-xl flex items-center justify-center text-gray-500 hover:text-gray-700 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <!-- Body -->
        <div class="p-6 space-y-6">
            
            <!-- Alert warning -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-yellow-500 rounded-xl flex items-center justify-center text-white">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold text-yellow-900">Attention</h4>
                        <p class="text-yellow-800 text-sm">Ce client a plusieurs commandes dans le système. Vérifiez les détails ci-dessous avant de traiter cette commande.</p>
                    </div>
                </div>
            </div>
            
            <div id="duplicates-content">
                <div class="text-center py-8">
                    <div class="w-12 h-12 bg-yellow-500 rounded-2xl flex items-center justify-center text-white text-xl mx-auto mb-4 animate-spin">
                        <i class="fas fa-spinner"></i>
                    </div>
                    <p class="text-gray-600">Chargement des doublons...</p>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="flex items-center justify-end gap-3 p-6 border-t border-gray-200">
            <button onclick="closeModal('duplicates-modal')" 
                    class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-semibold transition-colors">
                <i class="fas fa-times mr-2"></i>Fermer
            </button>
            <a href="/admin/duplicates" 
               target="_blank"
               class="px-6 py-3 bg-yellow-500 hover:bg-yellow-600 text-white rounded-xl font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                <i class="fas fa-external-link-alt mr-2"></i>Gérer les doublons
            </a>
        </div>
    </div>
</div>

<style>
/* Timeline pour l'historique */
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: linear-gradient(to bottom, #e5e7eb 0%, #d1d5db 100%);
}

.timeline-item {
    position: relative;
    margin-bottom: 24px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 8px;
    z-index: 2;
}

.timeline-marker-icon {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 12px;
    border: 3px solid white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.timeline-content {
    margin-left: 15px;
}

.timeline-content .timeline-card {
    @apply bg-white rounded-xl border-l-4 border-gray-300 shadow-sm hover:shadow-md transition-all duration-200 hover:-translate-y-0.5 p-4;
}

/* Classes de statut pour les badges dans les modales */
.status-nouvelle { @apply bg-gradient-to-r from-purple-100 to-purple-200 text-purple-800 px-3 py-1 rounded-full text-xs font-semibold; }
.status-datée { @apply bg-gradient-to-r from-yellow-100 to-yellow-200 text-yellow-800 px-3 py-1 rounded-full text-xs font-semibold; }
.status-confirmée { @apply bg-gradient-to-r from-green-100 to-green-200 text-green-800 px-3 py-1 rounded-full text-xs font-semibold; }
.status-ancienne { @apply bg-gradient-to-r from-red-100 to-red-200 text-red-800 px-3 py-1 rounded-full text-xs font-semibold; }
.status-annulée { @apply bg-gradient-to-r from-red-100 to-red-200 text-red-800 px-3 py-1 rounded-full text-xs font-semibold; }
.status-livrée { @apply bg-gradient-to-r from-green-100 to-green-200 text-green-800 px-3 py-1 rounded-full text-xs font-semibold; }
</style>

<script>
// GESTIONNAIRE DE MODALES TAILWIND FINAL - ATTENDRE JQUERY
document.addEventListener('DOMContentLoaded', function() {
    console.log('🎭 Initialisation des modales...');
    
    function waitForjQueryAndInitModals() {
        if (typeof jQuery !== 'undefined' && typeof $ !== 'undefined') {
            console.log('✅ jQuery détecté pour modales, initialisation...');
            initializeModals();
        } else {
            console.log('⏳ Attente de jQuery pour modales...');
            setTimeout(waitForjQueryAndInitModals, 100);
        }
    }
    
    waitForjQueryAndInitModals();
});

function initializeModals() {
    $(document).ready(function() {
        console.log('🎭 Modales Tailwind initialisées');
        
        // Configuration de la date minimale
        const today = new Date().toISOString().split('T')[0];
        $('#schedule-date').attr('min', today);
        
        // Event listeners pour les boutons de soumission
        setupModalEventListeners();
        
        // Validation en temps réel
        setupRealTimeValidation();
        
        // Exposer les fonctions globalement
        exposeGlobalFunctions();
    });
}

function setupModalEventListeners() {
    // Bouton Call Action
    $('#submit-call-action').off('click').on('click', function() {
        submitCallAction();
    });
    
    // Bouton Confirm Action
    $('#submit-confirm-action').off('click').on('click', function() {
        submitConfirmAction();
    });
    
    // Bouton Cancel Action
    $('#submit-cancel-action').off('click').on('click', function() {
        submitCancelAction();
    });
    
    // Bouton Schedule Action
    $('#submit-schedule-action').off('click').on('click', function() {
        submitScheduleAction();
    });
    
    // Bouton Reactivate Action
    $('#submit-reactivate-action').off('click').on('click', function() {
        submitReactivateAction();
    });
}

function setupRealTimeValidation() {
    // Validation notes d'appel
    $('#call-notes').off('input').on('input', function() {
        const notes = $(this).val().trim();
        const isValid = notes.length >= 3;
        $('#submit-call-action').prop('disabled', !isValid);
        
        if (isValid) {
            $(this).removeClass('border-red-500').addClass('border-gray-200');
        } else {
            $(this).removeClass('border-gray-200').addClass('border-red-500');
        }
    });
    
    // Validation prix de confirmation
    $('#confirm-price').off('input').on('input', function() {
        const price = parseFloat($(this).val()) || 0;
        const isValid = price > 0;
        $('#submit-confirm-action').prop('disabled', !isValid);
        
        if (isValid) {
            $(this).removeClass('border-red-500').addClass('border-gray-200');
        } else {
            $(this).removeClass('border-gray-200').addClass('border-red-500');
        }
    });
    
    // Validation notes d'annulation
    $('#cancel-notes').off('input').on('input', function() {
        const notes = $(this).val().trim();
        const isValid = notes.length >= 3;
        $('#submit-cancel-action').prop('disabled', !isValid);
        
        if (isValid) {
            $(this).removeClass('border-red-500').addClass('border-gray-200');
        } else {
            $(this).removeClass('border-gray-200').addClass('border-red-500');
        }
    });
    
    // Validation date de planification
    $('#schedule-date').off('change').on('change', function() {
        const selectedDate = new Date($(this).val());
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const isValid = selectedDate >= today;
        $('#submit-schedule-action').prop('disabled', !isValid);
        
        if (isValid) {
            $(this).removeClass('border-red-500').addClass('border-gray-200');
        } else {
            $(this).removeClass('border-gray-200').addClass('border-red-500');
        }
    });
}

// Fonctions d'ouverture et fermeture des modales
function showModal(action) {
    console.log(`🎭 Ouverture du modal: ${action}`);
    
    // Réinitialiser les champs
    resetModalFields();
    
    switch (action) {
        case 'call':
            openModal('call-modal');
            break;
        case 'confirm':
            updateConfirmModalData();
            openModal('confirm-modal');
            break;
        case 'cancel':
            openModal('cancel-modal');
            break;
        case 'schedule':
            openModal('schedule-modal');
            break;
        case 'reactivate':
            openModal('reactivate-modal');
            break;
    }
}

function openModal(modalId) {
    $(`#${modalId}`).removeClass('hidden').addClass('flex');
    $('body').addClass('overflow-hidden');
    
    // Animation d'entrée
    setTimeout(() => {
        $(`#${modalId} > div`).removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100');
    }, 10);
}

function closeModal(modalId) {
    console.log(`🎭 Fermeture du modal: ${modalId}`);
    
    // Animation de sortie
    $(`#${modalId} > div`).removeClass('scale-100 opacity-100').addClass('scale-95 opacity-0');
    
    setTimeout(() => {
        $(`#${modalId}`).removeClass('flex').addClass('hidden');
        $('body').removeClass('overflow-hidden');
    }, 200);
}

function closeAllModals() {
    console.log('🎭 Fermeture de tous les modales');
    
    $('.fixed.inset-0.z-50').each(function() {
        const modalId = $(this).attr('id');
        if (modalId && !$(this).hasClass('hidden')) {
            closeModal(modalId);
        }
    });
}

function resetModalFields() {
    // Vider tous les champs de saisie
    $('#call-notes, #confirm-price, #confirm-notes, #cancel-notes, #schedule-date, #schedule-notes, #reactivate-notes').val('');
    
    // Réinitialiser la validation visuelle
    $('.border-red-500').removeClass('border-red-500').addClass('border-gray-200');
    
    // Désactiver les boutons de soumission
    $('#submit-call-action, #submit-confirm-action, #submit-cancel-action, #submit-schedule-action').prop('disabled', true);
}

function updateConfirmModalData() {
    console.log('🎭 Mise à jour des données du modal de confirmation');
    
    // Mettre à jour les informations client
    $('#confirm-customer-name').text($('#customer_name').val() || 'Non renseigné');
    $('#confirm-customer-phone').text($('#customer_phone').val() || 'Non renseigné');
    $('#confirm-customer-address').text($('#customer_address').val() || 'Non renseignée');
    
    // Gouvernorat et ville
    const governorateSelect = $('#customer_governorate');
    const citySelect = $('#customer_city');
    
    let location = 'Non renseigné';
    if (governorateSelect.length && citySelect.length) {
        const governorate = governorateSelect.find('option:selected').text();
        const city = citySelect.find('option:selected').text();
        
        if (governorate && governorate !== 'Sélectionner un gouvernorat' && 
            city && city !== 'Sélectionner une ville') {
            location = `${city}, ${governorate}`;
        } else if (governorate && governorate !== 'Sélectionner un gouvernorat') {
            location = governorate;
        }
    }
    $('#confirm-customer-location').text(location);

    // Mettre à jour la liste des produits
    updateConfirmProductsList();

    // Calculer le prix automatiquement
    if (typeof cartItems !== 'undefined' && cartItems?.length > 0) {
        const total = cartItems.reduce((sum, item) => sum + (parseFloat(item.total_price) || 0), 0);
        $('#confirm-price').val(total.toFixed(3));
        
        // Déclencher la validation
        $('#confirm-price').trigger('input');
    }
}

function updateConfirmProductsList() {
    const productsList = $('#confirm-products-list');
    productsList.empty();
    
    if (typeof cartItems !== 'undefined' && cartItems?.length > 0) {
        let totalProducts = 0;
        let totalPrice = 0;
        
        cartItems.forEach(item => {
            if (item.product) {
                totalProducts += item.quantity;
                totalPrice += parseFloat(item.total_price) || 0;
                
                const productDiv = $(`
                    <div class="flex justify-between items-center py-1 text-sm">
                        <span class="text-gray-700">${item.product.name} (×${item.quantity})</span>
                        <span class="font-semibold text-gray-900">${parseFloat(item.total_price || 0).toFixed(3)} TND</span>
                    </div>
                `);
                productsList.append(productDiv);
            }
        });
        
        // Ajouter le total
        productsList.append(`
            <hr class="my-2 border-gray-300">
            <div class="flex justify-between items-center font-bold text-gray-900">
                <span>${totalProducts} article${totalProducts > 1 ? 's' : ''}</span>
                <span>${totalPrice.toFixed(3)} TND</span>
            </div>
        `);
    } else {
        productsList.html('<div class="text-sm text-gray-500">Aucun produit</div>');
    }
}

// Fonctions de soumission des actions
function submitCallAction() {
    const notes = $('#call-notes').val().trim();
    
    if (!notes || notes.length < 3) {
        if (typeof showNotification === 'function') {
            showNotification('Veuillez saisir des notes d\'au moins 3 caractères', 'error');
        }
        $('#call-notes').focus();
        return;
    }
    
    if (typeof processAction === 'function') {
        processAction('call', { notes: notes });
        closeModal('call-modal');
    }
}

function submitConfirmAction() {
    const price = $('#confirm-price').val();
    const notes = $('#confirm-notes').val().trim();
    
    if (!price || parseFloat(price) <= 0) {
        if (typeof showNotification === 'function') {
            showNotification('Veuillez saisir un prix valide supérieur à 0', 'error');
        }
        $('#confirm-price').focus();
        return;
    }
    
    if (typeof processAction === 'function') {
        processAction('confirm', { 
            confirmed_price: parseFloat(price),
            notes: notes
        });
        closeModal('confirm-modal');
    }
}

function submitCancelAction() {
    const notes = $('#cancel-notes').val().trim();
    
    if (!notes || notes.length < 3) {
        if (typeof showNotification === 'function') {
            showNotification('Veuillez indiquer la raison de l\'annulation (minimum 3 caractères)', 'error');
        }
        $('#cancel-notes').focus();
        return;
    }
    
    if (typeof processAction === 'function') {
        processAction('cancel', { notes: notes });
        closeModal('cancel-modal');
    }
}

function submitScheduleAction() {
    const date = $('#schedule-date').val();
    const notes = $('#schedule-notes').val().trim();
    
    if (!date) {
        if (typeof showNotification === 'function') {
            showNotification('Veuillez sélectionner une date', 'error');
        }
        $('#schedule-date').focus();
        return;
    }
    
    const selectedDate = new Date(date);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    if (selectedDate < today) {
        if (typeof showNotification === 'function') {
            showNotification('La date ne peut pas être dans le passé', 'error');
        }
        $('#schedule-date').focus();
        return;
    }
    
    if (typeof processAction === 'function') {
        processAction('schedule', { 
            scheduled_date: date,
            notes: notes
        });
        closeModal('schedule-modal');
    }
}

function submitReactivateAction() {
    const notes = $('#reactivate-notes').val().trim();
    
    if (typeof processAction === 'function') {
        processAction('reactivate', { 
            notes: notes
        });
        closeModal('reactivate-modal');
    }
}

function exposeGlobalFunctions() {
    // Fonctions globales pour la compatibilité
    window.showModal = showModal;
    window.closeModal = closeModal;
    window.closeAllModals = closeAllModals;
    window.showHistoryModal = function(content) {
        $('#history-content').html(content);
        openModal('history-modal');
    };
    window.showDuplicatesModalWithContent = function(content) {
        $('#duplicates-content').html(content);
        openModal('duplicates-modal');
    };
}

// Fermer les modales avec Escape - Attendre DOM
$(document).ready(function() {
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAllModals();
        }
    });
});

console.log('✅ Gestionnaire de modales Tailwind finalisé');
</script>