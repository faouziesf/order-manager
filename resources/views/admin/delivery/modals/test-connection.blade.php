<!-- Modal Test de Connexion -->
<div class="modal fade" id="testConnectionModal" tabindex="-1" aria-labelledby="testConnectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="testConnectionModalLabel">
                    <i class="fas fa-wifi me-2"></i>
                    Test de Connexion Transporteur
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <!-- Informations de la configuration testée -->
                <div class="card bg-light border-0 mb-4" x-show="testConfig">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary mb-2">Configuration Testée</h6>
                                <div>
                                    <strong x-text="testConfig?.integration_name"></strong>
                                    <br>
                                    <small class="text-muted" x-text="testConfig?.carrier_name"></small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary mb-2">Informations</h6>
                                <div>
                                    <small>
                                        <strong>Compte:</strong> <span x-text="testConfig?.username"></span><br>
                                        <strong>Environnement:</strong> <span x-text="testConfig?.environment"></span>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- État du test -->
                <div class="d-flex align-items-center justify-content-center py-4" x-show="testInProgress">
                    <div class="text-center">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Test en cours...</span>
                        </div>
                        <h5 class="text-primary">Test en cours...</h5>
                        <p class="text-muted mb-0">Connexion au transporteur</p>
                        <div class="progress mt-3" style="width: 300px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" 
                                 :style="`width: ${testProgress}%`"
                                 :aria-valuenow="testProgress" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100"></div>
                        </div>
                        <small class="text-muted mt-2 d-block" x-text="testMessage"></small>
                    </div>
                </div>

                <!-- Résultat du test -->
                <div x-show="testCompleted && !testInProgress">
                    <!-- Succès -->
                    <div x-show="testResult?.success" class="alert alert-success">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle fa-2x me-3"></i>
                            <div class="flex-grow-1">
                                <h6 class="alert-heading mb-1">
                                    <i class="fas fa-check me-1"></i>
                                    Connexion Réussie !
                                </h6>
                                <p class="mb-0" x-text="testResult?.message"></p>
                            </div>
                        </div>
                        
                        <!-- Détails du test réussi -->
                        <div x-show="testResult?.details" class="mt-3">
                            <hr>
                            <h6 class="text-success">Détails de la Connexion</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <small>
                                        <strong>URL API:</strong> <span x-text="testResult?.details?.api_url"></span><br>
                                        <strong>Temps de test:</strong> <span x-text="formatTestTime(testResult?.details?.test_time)"></span>
                                    </small>
                                </div>
                                <div class="col-md-6" x-show="testResult?.details?.account_info">
                                    <small>
                                        <strong>Statut compte:</strong> <span x-text="testResult?.details?.account_info?.account_status"></span><br>
                                        <strong>Solde:</strong> <span x-text="testResult?.details?.account_info?.balance"></span> TND
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Échec -->
                    <div x-show="!testResult?.success" class="alert alert-danger">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                            <div class="flex-grow-1">
                                <h6 class="alert-heading mb-1">
                                    <i class="fas fa-times me-1"></i>
                                    Échec de Connexion
                                </h6>
                                <p class="mb-0" x-text="testResult?.error || 'Erreur inconnue'"></p>
                            </div>
                        </div>
                        
                        <!-- Détails de l'erreur -->
                        <div x-show="testResult?.details" class="mt-3">
                            <hr>
                            <h6 class="text-danger">Informations de Débogage</h6>
                            <div class="bg-light p-2 rounded">
                                <small class="font-monospace">
                                    <strong>Code d'erreur:</strong> <span x-text="testResult?.details?.error_code || 'N/A'"></span><br>
                                    <strong>URL tentée:</strong> <span x-text="testResult?.details?.url || 'N/A'"></span><br>
                                    <strong>Statut HTTP:</strong> <span x-text="testResult?.details?.status_code || 'N/A'"></span>
                                </small>
                            </div>
                        </div>

                        <!-- Suggestions de résolution -->
                        <div class="mt-3">
                            <h6 class="text-danger">Solutions Possibles</h6>
                            <ul class="mb-0">
                                <li>Vérifiez vos identifiants de connexion</li>
                                <li>Assurez-vous que votre compte transporteur est actif</li>
                                <li>Contactez le support du transporteur si le problème persiste</li>
                                <li>Vérifiez votre connexion internet</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Historique des tests récents -->
                <div x-show="testHistory && testHistory.length > 0" class="mt-4">
                    <h6 class="text-muted">
                        <i class="fas fa-history me-1"></i>
                        Tests Récents
                    </h6>
                    <div class="list-group list-group-flush">
                        <template x-for="test in testHistory.slice(0, 3)" :key="test.id">
                            <div class="list-group-item border-0 px-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <i :class="test.success ? 'fas fa-check-circle text-success' : 'fas fa-times-circle text-danger'" class="me-2"></i>
                                        <div>
                                            <span x-text="test.success ? 'Succès' : 'Échec'"></span>
                                            <span x-show="!test.success" class="text-muted">- <span x-text="test.error"></span></span>
                                        </div>
                                    </div>
                                    <small class="text-muted" x-text="formatTestTime(test.timestamp)"></small>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <div class="d-flex justify-content-between w-100">
                    <div>
                        <!-- Bouton de re-test -->
                        <button x-show="testCompleted && !testInProgress" 
                                class="btn btn-outline-primary"
                                @click="retestConnection()">
                            <i class="fas fa-redo me-1"></i>
                            Retester
                        </button>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <!-- Bouton d'activation si test réussi -->
                        <button x-show="testResult?.success && !testConfig?.is_active" 
                                class="btn btn-success"
                                @click="activateConfiguration()">
                            <i class="fas fa-power-off me-1"></i>
                            Activer la Configuration
                        </button>
                        
                        <!-- Bouton de fermeture -->
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <span x-show="testInProgress">Annuler</span>
                            <span x-show="!testInProgress">Fermer</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Fonctions utilitaires pour la modal de test (à intégrer dans le composant Alpine principal)
window.testConnectionHelpers = {
    async simulateTest(configId) {
        // Simulation d'un test de connexion avec étapes
        const steps = [
            { progress: 20, message: 'Vérification des paramètres...' },
            { progress: 40, message: 'Connexion au serveur...' },
            { progress: 60, message: 'Authentification...' },
            { progress: 80, message: 'Test de l\'API...' },
            { progress: 100, message: 'Finalisation...' }
        ];

        for (const step of steps) {
            this.testProgress = step.progress;
            this.testMessage = step.message;
            await new Promise(resolve => setTimeout(resolve, 800));
        }
    },

    formatTestTime(timeString) {
        if (!timeString) return '';
        
        try {
            const date = new Date(timeString);
            return date.toLocaleString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        } catch (e) {
            return timeString;
        }
    },

    getTestResultIcon(success) {
        return success ? 'fas fa-check-circle text-success' : 'fas fa-times-circle text-danger';
    }
};

// Méthodes à ajouter au composant Alpine principal
function extendWithTestConnection() {
    return {
        testInProgress: false,
        testCompleted: false,
        testResult: null,
        testConfig: null,
        testProgress: 0,
        testMessage: '',
        testHistory: [],

        async openTestModal(configId) {
            // Récupérer les infos de la configuration
            this.testConfig = this.configurations?.find(c => c.id === configId) || 
                             this.carriers?.[Object.keys(this.carriers)[0]]?.configurations?.find(c => c.id === configId);
            
            this.resetTestState();
            
            const modal = new bootstrap.Modal(document.getElementById('testConnectionModal'));
            modal.show();
        },

        resetTestState() {
            this.testInProgress = false;
            this.testCompleted = false;
            this.testResult = null;
            this.testProgress = 0;
            this.testMessage = '';
        },

        async performTest(configId) {
            this.testInProgress = true;
            this.testCompleted = false;
            this.testProgress = 0;
            
            try {
                // Simulation du progrès
                await window.testConnectionHelpers.simulateTest.call(this, configId);
                
                // Appel réel à l'API
                const response = await axios.post(`/admin/delivery/configuration/${configId}/test`);
                
                this.testResult = response.data;
                this.testCompleted = true;
                
                // Ajouter à l'historique
                this.addToTestHistory(response.data);
                
            } catch (error) {
                this.testResult = {
                    success: false,
                    error: error.response?.data?.error || error.message || 'Erreur de connexion',
                    details: error.response?.data?.details || {}
                };
                this.testCompleted = true;
                
                this.addToTestHistory(this.testResult);
            } finally {
                this.testInProgress = false;
            }
        },

        addToTestHistory(result) {
            this.testHistory.unshift({
                id: Date.now(),
                success: result.success,
                error: result.error,
                timestamp: new Date().toISOString()
            });
            
            // Garder seulement les 10 derniers tests
            this.testHistory = this.testHistory.slice(0, 10);
        },

        async retestConnection() {
            if (this.testConfig) {
                await this.performTest(this.testConfig.id);
            }
        },

        async activateConfiguration() {
            if (!this.testConfig) return;
            
            try {
                const response = await axios.post(`/admin/delivery/configuration/${this.testConfig.id}/toggle`);
                
                if (response.data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Configuration activée !',
                        text: 'La configuration est maintenant active',
                        showConfirmButton: false,
                        timer: 2000
                    });
                    
                    // Fermer la modal et recharger
                    bootstrap.Modal.getInstance(document.getElementById('testConnectionModal')).hide();
                    setTimeout(() => window.location.reload(), 2000);
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Impossible d\'activer la configuration',
                });
            }
        },

        formatTestTime: window.testConnectionHelpers.formatTestTime
    };
}
</script>
@endpush

@push('styles')
<style>
.progress {
    height: 8px;
}

.modal-body .spinner-border {
    width: 3rem;
    height: 3rem;
}

.timeline-marker {
    position: absolute;
    left: -8px;
    top: 8px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background-color: #007bff;
}

.list-group-item:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

@keyframes pulse-success {
    0% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(40, 167, 69, 0); }
    100% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0); }
}

@keyframes pulse-danger {
    0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
    100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
}

.alert-success .fa-check-circle {
    animation: pulse-success 2s infinite;
}

.alert-danger .fa-exclamation-triangle {
    animation: pulse-danger 2s infinite;
}
</style>
@endpush