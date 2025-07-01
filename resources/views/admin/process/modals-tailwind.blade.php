{{-- MODAL TENTATIVE D'APPEL --}}
<div class="modal fade" id="callModal" tabindex="-1" aria-labelledby="callModalLabel" aria-hidden="true">
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            
            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white rounded-3xl px-4 pt-5 pb-4 text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-2xl bg-yellow-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-phone-slash text-yellow-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                        <h3 class="text-lg leading-6 font-bold text-gray-900" id="callModalLabel">
                            Tentative d'appel - Ne répond pas
                        </h3>
                        <div class="mt-4 space-y-4">
                            <div>
                                <label for="call-notes" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-sticky-note mr-2 text-gray-400"></i>
                                    Notes sur la tentative <span class="text-red-500">*</span>
                                </label>
                                <textarea id="call-notes" rows="4" required
                                          class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-white focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition-colors resize-none"
                                          placeholder="Décrivez ce qui s'est passé lors de l'appel..."></textarea>
                                <p class="text-xs text-gray-600 mt-2">
                                    Exemple: "Sonnerie mais pas de réponse", "Numéro occupé", "Éteint", "Demande de rappeler plus tard", etc.
                                </p>
                            </div>
                            
                            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                                <div class="flex items-start">
                                    <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-3"></i>
                                    <div class="text-sm">
                                        <span class="font-semibold text-blue-900">Action:</span>
                                        <span class="text-blue-800">Cette action va incrémenter le compteur de tentatives et marquer l'heure de la dernière tentative.</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse gap-3">
                    <button type="button" onclick="submitCallAction()" 
                            class="w-full inline-flex justify-center items-center px-6 py-3 border border-transparent text-base font-semibold rounded-xl text-white bg-gradient-to-r from-yellow-500 to-orange-500 hover:from-yellow-600 hover:to-orange-600 transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl sm:w-auto">
                        <i class="fas fa-save mr-2"></i>Enregistrer la tentative
                    </button>
                    <button type="button" data-bs-dismiss="modal"
                            class="mt-3 w-full inline-flex justify-center items-center px-6 py-3 border border-gray-300 text-base font-semibold rounded-xl text-gray-700 bg-white hover:bg-gray-50 transition-colors shadow-sm sm:mt-0 sm:w-auto">
                        <i class="fas fa-times mr-2"></i>Annuler
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL CONFIRMATION --}}
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            
            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white rounded-3xl px-4 pt-5 pb-4 text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full sm:p-6">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-2xl bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                        <h3 class="text-lg leading-6 font-bold text-gray-900" id="confirmModalLabel">
                            Confirmer la commande
                        </h3>
                        <div class="mt-4 space-y-6">
                            
                            <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                                <div class="flex items-center">
                                    <i class="fas fa-check-circle text-green-500 mr-3"></i>
                                    <span class="font-semibold text-green-900">Validation réussie!</span>
                                    <span class="text-green-800 ml-2">Tous les champs obligatoires sont remplis et le panier contient des produits en stock.</span>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <div class="space-y-4">
                                    <div>
                                        <label for="confirm-price" class="block text-sm font-semibold text-gray-700 mb-2">
                                            <i class="fas fa-money-bill-wave mr-2 text-gray-400"></i>
                                            Prix total de la commande (TND) <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <input type="number" step="0.001" id="confirm-price" required min="0.001"
                                                   class="w-full pl-4 pr-16 py-3 border border-gray-300 rounded-xl bg-white focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                                                   placeholder="0.000">
                                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center">
                                                <span class="text-gray-500 font-semibold">TND</span>
                                            </div>
                                        </div>
                                        <p class="text-xs text-gray-600 mt-1">Prix total final négocié avec le client (incluant tout)</p>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                                            <i class="fas fa-info-circle mr-2 text-gray-400"></i>
                                            Changements à venir
                                        </label>
                                        <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                                            <div class="text-sm space-y-1">
                                                <div class="flex items-center text-green-800">
                                                    <i class="fas fa-arrow-right mr-2 text-green-600"></i>
                                                    <span>Statut: <span class="font-semibold">Confirmée</span></span>
                                                </div>
                                                <div class="flex items-center text-green-800">
                                                    <i class="fas fa-minus mr-2 text-green-600"></i>
                                                    <span>Stock sera décrémenté automatiquement</span>
                                                </div>
                                                <div class="flex items-center text-green-800">
                                                    <i class="fas fa-money-bill mr-2 text-green-600"></i>
                                                    <span>Prix total sera mis à jour</span>
                                                </div>
                                                <div class="flex items-center text-green-800">
                                                    <i class="fas fa-user mr-2 text-green-600"></i>
                                                    <span>Infos client sauvegardées</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="space-y-4">
                                    <div>
                                        <label for="confirm-notes" class="block text-sm font-semibold text-gray-700 mb-2">
                                            <i class="fas fa-comment mr-2 text-gray-400"></i>
                                            Notes de confirmation (optionnel)
                                        </label>
                                        <textarea id="confirm-notes" rows="3"
                                                  class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-white focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors resize-none"
                                                  placeholder="Informations supplémentaires sur la confirmation..."></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Récapitulatif des données -->
                            <div>
                                <h4 class="text-base font-semibold text-gray-900 mb-4 flex items-center">
                                    <i class="fas fa-clipboard-list mr-2 text-gray-600"></i>
                                    Récapitulatif des données
                                </h4>
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                    <div class="bg-gray-50 border border-gray-200 rounded-xl overflow-hidden">
                                        <div class="bg-gray-100 px-4 py-2 border-b border-gray-200">
                                            <h5 class="text-sm font-bold text-gray-700 uppercase tracking-wide">Informations Client</h5>
                                        </div>
                                        <div class="p-4 space-y-2 text-sm">
                                            <div class="flex justify-between">
                                                <span class="font-semibold text-gray-600">Nom:</span>
                                                <span id="confirm-customer-name" class="text-gray-900">-</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="font-semibold text-gray-600">Téléphone:</span>
                                                <span id="confirm-customer-phone" class="text-gray-900">-</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="font-semibold text-gray-600">Lieu:</span>
                                                <span id="confirm-customer-location" class="text-gray-900">-</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="font-semibold text-gray-600">Adresse:</span>
                                                <span id="confirm-customer-address" class="text-gray-900">-</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="bg-gray-50 border border-gray-200 rounded-xl overflow-hidden">
                                        <div class="bg-gray-100 px-4 py-2 border-b border-gray-200">
                                            <h5 class="text-sm font-bold text-gray-700 uppercase tracking-wide">Produits Commandés</h5>
                                        </div>
                                        <div id="confirm-products-list" class="p-4 text-sm text-gray-600 min-h-[100px] flex items-center">
                                            Chargement...
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse gap-3">
                    <button type="button" onclick="submitConfirmAction()" 
                            class="w-full inline-flex justify-center items-center px-6 py-3 border border-transparent text-base font-semibold rounded-xl text-white bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl sm:w-auto">
                        <i class="fas fa-check-circle mr-2"></i>Confirmer définitivement
                    </button>
                    <button type="button" data-bs-dismiss="modal"
                            class="mt-3 w-full inline-flex justify-center items-center px-6 py-3 border border-gray-300 text-base font-semibold rounded-xl text-gray-700 bg-white hover:bg-gray-50 transition-colors shadow-sm sm:mt-0 sm:w-auto">
                        <i class="fas fa-times mr-2"></i>Annuler
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL ANNULATION --}}
<div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            
            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white rounded-3xl px-4 pt-5 pb-4 text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-2xl bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-times-circle text-red-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                        <h3 class="text-lg leading-6 font-bold text-gray-900" id="cancelModalLabel">
                            Annuler la commande
                        </h3>
                        <div class="mt-4 space-y-4">
                            
                            <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                                <div class="flex items-start">
                                    <i class="fas fa-exclamation-triangle text-red-500 mt-0.5 mr-3"></i>
                                    <div class="text-sm">
                                        <span class="font-semibold text-red-900">Attention:</span>
                                        <span class="text-red-800">Cette action changera définitivement le statut de la commande à "Annulée".</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <label for="cancel-notes" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-sticky-note mr-2 text-gray-400"></i>
                                    Raison de l'annulation <span class="text-red-500">*</span>
                                </label>
                                <textarea id="cancel-notes" rows="4" required
                                          class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-white focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors resize-none"
                                          placeholder="Expliquez pourquoi cette commande est annulée..."></textarea>
                                <p class="text-xs text-gray-600 mt-2">
                                    Exemple: "Client a changé d'avis", "Produit non disponible", "Adresse incorrecte", etc.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse gap-3">
                    <button type="button" onclick="submitCancelAction()" 
                            class="w-full inline-flex justify-center items-center px-6 py-3 border border-transparent text-base font-semibold rounded-xl text-white bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl sm:w-auto">
                        <i class="fas fa-times-circle mr-2"></i>Annuler définitivement
                    </button>
                    <button type="button" data-bs-dismiss="modal"
                            class="mt-3 w-full inline-flex justify-center items-center px-6 py-3 border border-gray-300 text-base font-semibold rounded-xl text-gray-700 bg-white hover:bg-gray-50 transition-colors shadow-sm sm:mt-0 sm:w-auto">
                        <i class="fas fa-arrow-left mr-2"></i>Retour
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL PLANIFICATION --}}
<div class="modal fade" id="scheduleModal" tabindex="-1" aria-labelledby="scheduleModalLabel" aria-hidden="true">
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            
            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white rounded-3xl px-4 pt-5 pb-4 text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-2xl bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-calendar-plus text-blue-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                        <h3 class="text-lg leading-6 font-bold text-gray-900" id="scheduleModalLabel">
                            Dater la commande
                        </h3>
                        <div class="mt-4 space-y-4">
                            
                            <div>
                                <label for="schedule-date" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-calendar-alt mr-2 text-gray-400"></i>
                                    Date de rappel <span class="text-red-500">*</span>
                                </label>
                                <input type="date" id="schedule-date" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                <p class="text-xs text-gray-600 mt-2">
                                    La commande apparaîtra dans la file "Datée" à partir de cette date
                                </p>
                            </div>
                            
                            <div>
                                <label for="schedule-notes" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-comment mr-2 text-gray-400"></i>
                                    Notes de planification (optionnel)
                                </label>
                                <textarea id="schedule-notes" rows="3"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none"
                                          placeholder="Raison de la planification et informations pour le rappel..."></textarea>
                            </div>
                            
                            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                                <div class="flex items-start">
                                    <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-3"></i>
                                    <div class="text-sm">
                                        <span class="font-semibold text-blue-900">Action:</span>
                                        <span class="text-blue-800">La commande passera au statut <span class="font-semibold">"Datée"</span> et ses compteurs de tentatives seront remis à zéro.</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse gap-3">
                    <button type="button" onclick="submitScheduleAction()" 
                            class="w-full inline-flex justify-center items-center px-6 py-3 border border-transparent text-base font-semibold rounded-xl text-white bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl sm:w-auto">
                        <i class="fas fa-calendar-check mr-2"></i>Dater la commande
                    </button>
                    <button type="button" data-bs-dismiss="modal"
                            class="mt-3 w-full inline-flex justify-center items-center px-6 py-3 border border-gray-300 text-base font-semibold rounded-xl text-gray-700 bg-white hover:bg-gray-50 transition-colors shadow-sm sm:mt-0 sm:w-auto">
                        <i class="fas fa-times mr-2"></i>Annuler
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL RÉACTIVATION --}}
<div class="modal fade" id="reactivateModal" tabindex="-1" aria-labelledby="reactivateModalLabel" aria-hidden="true">
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            
            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white rounded-3xl px-4 pt-5 pb-4 text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-2xl bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-play-circle text-green-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                        <h3 class="text-lg leading-6 font-bold text-gray-900" id="reactivateModalLabel">
                            Réactiver la commande
                        </h3>
                        <div class="mt-4 space-y-4">
                            
                            <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                                <div class="flex items-start">
                                    <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3"></i>
                                    <div class="text-sm">
                                        <div class="font-semibold text-green-900 mb-1">Stock disponible!</div>
                                        <div class="text-green-800">Tous les produits de cette commande sont maintenant en stock et peuvent être traités normalement.</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <label for="reactivate-notes" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-comment mr-2 text-gray-400"></i>
                                    Notes de réactivation (optionnel)
                                </label>
                                <textarea id="reactivate-notes" rows="3"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-white focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors resize-none"
                                          placeholder="Notes sur la réactivation de cette commande..."></textarea>
                            </div>
                            
                            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                                <div class="flex items-start">
                                    <i class="fas fa-arrow-right text-blue-500 mt-0.5 mr-3"></i>
                                    <div class="text-sm">
                                        <span class="font-semibold text-blue-900">Action:</span>
                                        <span class="text-blue-800">La commande retournera au statut <span class="font-semibold">"Nouvelle"</span> et ne sera plus suspendue. Ses compteurs de tentatives seront remis à zéro.</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse gap-3">
                    <button type="button" onclick="submitReactivateAction()" 
                            class="w-full inline-flex justify-center items-center px-6 py-3 border border-transparent text-base font-semibold rounded-xl text-white bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl sm:w-auto">
                        <i class="fas fa-play-circle mr-2"></i>Réactiver définitivement
                    </button>
                    <button type="button" data-bs-dismiss="modal"
                            class="mt-3 w-full inline-flex justify-center items-center px-6 py-3 border border-gray-300 text-base font-semibold rounded-xl text-gray-700 bg-white hover:bg-gray-50 transition-colors shadow-sm sm:mt-0 sm:w-auto">
                        <i class="fas fa-times mr-2"></i>Annuler
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL HISTORIQUE --}}
<div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            
            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white rounded-3xl px-4 pt-5 pb-4 text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full sm:p-6">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-2xl bg-primary-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-history text-primary-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                        <h3 class="text-lg leading-6 font-bold text-gray-900" id="historyModalLabel">
                            Historique de la commande
                        </h3>
                        <div class="mt-4">
                            <div id="history-content" class="max-h-96 overflow-y-auto">
                                <div class="text-center py-8">
                                    <div class="inline-flex items-center justify-center w-12 h-12 bg-gray-100 rounded-2xl mb-4">
                                        <i class="fas fa-spinner fa-spin text-xl text-gray-400"></i>
                                    </div>
                                    <p class="text-gray-600">Chargement de l'historique...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <button type="button" data-bs-dismiss="modal"
                            class="w-full inline-flex justify-center items-center px-6 py-3 border border-gray-300 text-base font-semibold rounded-xl text-gray-700 bg-white hover:bg-gray-50 transition-colors shadow-sm sm:w-auto">
                        <i class="fas fa-times mr-2"></i>Fermer
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL DOUBLONS --}}
<div class="modal fade" id="duplicatesModal" tabindex="-1" aria-labelledby="duplicatesModalLabel" aria-hidden="true">
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            
            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white rounded-3xl px-4 pt-5 pb-4 text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-6xl sm:w-full sm:p-6">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-2xl bg-yellow-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                        <h3 class="text-lg leading-6 font-bold text-gray-900" id="duplicatesModalLabel">
                            Commandes doublons détectées
                        </h3>
                        <div class="mt-4 space-y-4">
                            
                            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                                <div class="flex items-start">
                                    <i class="fas fa-info-circle text-yellow-500 mt-0.5 mr-3"></i>
                                    <div class="text-sm">
                                        <span class="font-semibold text-yellow-900">Attention:</span>
                                        <span class="text-yellow-800">Ce client a plusieurs commandes dans le système. Vérifiez les détails ci-dessous avant de traiter cette commande.</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="duplicates-content" class="max-h-96 overflow-y-auto">
                                <div class="text-center py-8">
                                    <div class="inline-flex items-center justify-center w-12 h-12 bg-gray-100 rounded-2xl mb-4">
                                        <i class="fas fa-spinner fa-spin text-xl text-gray-400"></i>
                                    </div>
                                    <p class="text-gray-600">Chargement des doublons...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse gap-3">
                    <a href="/admin/duplicates" target="_blank"
                       class="w-full inline-flex justify-center items-center px-6 py-3 border border-transparent text-base font-semibold rounded-xl text-white bg-gradient-to-r from-yellow-500 to-orange-500 hover:from-yellow-600 hover:to-orange-600 transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl sm:w-auto">
                        <i class="fas fa-external-link-alt mr-2"></i>Gérer les doublons
                    </a>
                    <button type="button" data-bs-dismiss="modal"
                            class="mt-3 w-full inline-flex justify-center items-center px-6 py-3 border border-gray-300 text-base font-semibold rounded-xl text-gray-700 bg-white hover:bg-gray-50 transition-colors shadow-sm sm:mt-0 sm:w-auto">
                        <i class="fas fa-times mr-2"></i>Fermer
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- STYLES ET SCRIPTS --}}
<style>
/* Timeline pour l'historique */
.timeline {
    position: relative;
    padding-left: 2rem;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 1rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: linear-gradient(to bottom, #e5e7eb 0%, #d1d5db 100%);
}

.timeline-item {
    position: relative;
    margin-bottom: 1.5rem;
}

.timeline-marker {
    position: absolute;
    left: -1.4rem;
    top: 0.5rem;
    z-index: 2;
}

.timeline-marker-icon {
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.75rem;
    border: 3px solid white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.timeline-content {
    margin-left: 1rem;
}

.timeline-content .bg-white {
    border-left: 3px solid #e5e7eb;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    transition: all 0.2s ease;
    border-radius: 0.75rem;
}

.timeline-content .bg-white:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transform: translateY(-1px);
}

/* Classes de statut pour les badges - Tailwind version */
.status-nouvelle { @apply bg-purple-100 text-purple-800; }
.status-datée { @apply bg-yellow-100 text-yellow-800; }
.status-confirmée { @apply bg-green-100 text-green-800; }
.status-ancienne { @apply bg-red-100 text-red-800; }
.status-annulée { @apply bg-red-100 text-red-800; }
.status-livrée { @apply bg-green-100 text-green-800; }

/* Scroll personnalisé pour les modals */
#history-content::-webkit-scrollbar,
#duplicates-content::-webkit-scrollbar {
    width: 6px;
}

#history-content::-webkit-scrollbar-track,
#duplicates-content::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 3px;
}

#history-content::-webkit-scrollbar-thumb,
#duplicates-content::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
    border-radius: 3px;
}
</style>

<script>
// Fonctions pour soumettre les actions avec validation - CONSERVÉES IDENTIQUES

function submitCallAction() {
    const notes = $('#call-notes').val().trim();
    
    if (!notes || notes.length < 3) {
        showNotification('Veuillez saisir des notes d\'au moins 3 caractères', 'error');
        $('#call-notes').focus();
        return;
    }
    
    processAction('call', { notes: notes });
}

function submitConfirmAction() {
    const price = $('#confirm-price').val();
    const notes = $('#confirm-notes').val().trim();
    
    // Validation du prix
    if (!price || parseFloat(price) <= 0) {
        showNotification('Veuillez saisir un prix valide supérieur à 0', 'error');
        $('#confirm-price').focus();
        return;
    }
    
    // Note: La validation des champs client a déjà été faite avant l'ouverture du modal
    // donc ici on peut directement procéder
    
    processAction('confirm', { 
        confirmed_price: parseFloat(price),
        notes: notes
    });
}

function submitCancelAction() {
    const notes = $('#cancel-notes').val().trim();
    
    if (!notes || notes.length < 3) {
        showNotification('Veuillez indiquer la raison de l\'annulation (minimum 3 caractères)', 'error');
        $('#cancel-notes').focus();
        return;
    }
    
    processAction('cancel', { notes: notes });
}

function submitScheduleAction() {
    const date = $('#schedule-date').val();
    const notes = $('#schedule-notes').val().trim();
    
    if (!date) {
        showNotification('Veuillez sélectionner une date', 'error');
        $('#schedule-date').focus();
        return;
    }
    
    // Vérifier que la date n'est pas dans le passé
    const selectedDate = new Date(date);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    if (selectedDate < today) {
        showNotification('La date ne peut pas être dans le passé', 'error');
        $('#schedule-date').focus();
        return;
    }
    
    processAction('schedule', { 
        scheduled_date: date,
        notes: notes
    });
}

function submitReactivateAction() {
    const notes = $('#reactivate-notes').val().trim();
    
    processAction('reactivate', { 
        notes: notes
    });
}

// Fonctions utilitaires pour les modals
function getSelectedGovernorate() {
    const governorateSelect = $('#customer_governorate');
    const selectedOption = governorateSelect.find('option:selected');
    return selectedOption.text() !== 'Sélectionner un gouvernorat' ? selectedOption.text() : '';
}

function getSelectedCity() {
    const citySelect = $('#customer_city');
    const selectedOption = citySelect.find('option:selected');
    return selectedOption.text() !== 'Sélectionner une ville' ? selectedOption.text() : '';
}

function updateConfirmModalData() {
    // Mettre à jour les informations client dans le modal de confirmation
    $('#confirm-customer-name').text($('#customer_name').val() || 'Non renseigné');
    $('#confirm-customer-phone').text($('#customer_phone').val() || 'Non renseigné');
    
    const governorate = getSelectedGovernorate();
    const city = getSelectedCity();
    let location = '';
    if (governorate && city) {
        location = `${city}, ${governorate}`;
    } else if (governorate) {
        location = governorate;
    } else {
        location = 'Non renseigné';
    }
    $('#confirm-customer-location').text(location);
    $('#confirm-customer-address').text($('#customer_address').val() || 'Non renseignée');
    
    // Mettre à jour la liste des produits
    const productsList = $('#confirm-products-list');
    productsList.empty();
    
    if (cartItems && cartItems.length > 0) {
        let totalProducts = 0;
        let totalPrice = 0;
        
        cartItems.forEach(item => {
            if (item.product) {
                totalProducts += item.quantity;
                totalPrice += parseFloat(item.total_price) || 0;
                
                productsList.append(`
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-gray-900">${item.product.name} (×${item.quantity})</span>
                        <span class="font-bold text-gray-900">${parseFloat(item.total_price || 0).toFixed(3)} TND</span>
                    </div>
                `);
            }
        });
        
        productsList.append(`
            <div class="border-t border-gray-200 pt-2 mt-2">
                <div class="flex justify-between items-center font-bold text-gray-900">
                    <span>${totalProducts} article${totalProducts > 1 ? 's' : ''}</span>
                    <span>${totalPrice.toFixed(3)} TND</span>
                </div>
            </div>
        `);
    } else {
        productsList.html('<div class="text-gray-500 text-sm text-center py-4">Aucun produit</div>');
    }
}

// Initialiser les dates minimales et événements
$(document).ready(function() {
    // Date minimum pour la planification = aujourd'hui
    const today = new Date().toISOString().split('T')[0];
    $('#schedule-date').attr('min', today);
    
    // Vider les champs des modales à leur ouverture
    $('.modal').on('show.bs.modal', function() {
        $(this).find('textarea, input[type="text"], input[type="number"], input[type="date"]').val('');
    });
    
    // Calculer le prix automatiquement et mettre à jour les données pour la confirmation
    $('#confirmModal').on('show.bs.modal', function() {
        if (cartItems && cartItems.length > 0) {
            const total = cartItems.reduce((sum, item) => sum + (parseFloat(item.total_price) || 0), 0);
            $('#confirm-price').val(total.toFixed(3));
        }
        
        // Mettre à jour toutes les données du récapitulatif
        updateConfirmModalData();
    });
    
    // Validation en temps réel pour le prix de confirmation
    $('#confirm-price').on('input', function() {
        const value = parseFloat($(this).val());
        const submitBtn = $(this).closest('.modal').find('button[onclick="submitConfirmAction()"]');
        
        if (value > 0) {
            submitBtn.prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
            $(this).removeClass('border-red-500 ring-red-200').addClass('border-gray-300');
        } else {
            submitBtn.prop('disabled', true).addClass('opacity-50 cursor-not-allowed');
            $(this).removeClass('border-gray-300').addClass('border-red-500 ring-red-200');
        }
    });
    
    // Validation en temps réel pour les notes obligatoires
    $('#call-notes, #cancel-notes').on('input', function() {
        const value = $(this).val().trim();
        const modal = $(this).closest('.modal');
        const submitBtn = modal.find('button[onclick*="submit"]').not('[data-bs-dismiss]');
        
        if (value.length >= 3) {
            submitBtn.prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
            $(this).removeClass('border-red-500 ring-red-200').addClass('border-gray-300');
        } else {
            submitBtn.prop('disabled', true).addClass('opacity-50 cursor-not-allowed');
            $(this).removeClass('border-gray-300').addClass('border-red-500 ring-red-200');
        }
    });
    
    // Validation pour la date de planification
    $('#schedule-date').on('change', function() {
        const selectedDate = new Date($(this).val());
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const submitBtn = $(this).closest('.modal').find('button[onclick="submitScheduleAction()"]');
        
        if (selectedDate >= today) {
            submitBtn.prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
            $(this).removeClass('border-red-500 ring-red-200').addClass('border-gray-300');
        } else {
            submitBtn.prop('disabled', true).addClass('opacity-50 cursor-not-allowed');
            $(this).removeClass('border-gray-300').addClass('border-red-500 ring-red-200');
        }
    });
});
</script>