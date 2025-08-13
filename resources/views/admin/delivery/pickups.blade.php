<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚛 Test Pickups - Debug</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; margin: 0; padding: 20px; background: #f8fafc; }
        .container { max-width: 1200px; margin: 0 auto; }
        .card { background: white; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .btn { padding: 8px 16px; border-radius: 6px; border: none; cursor: pointer; font-weight: 500; }
        .btn-primary { background: #3b82f6; color: white; }
        .btn-success { background: #10b981; color: white; }
        .btn-danger { background: #ef4444; color: white; }
        .btn-warning { background: #f59e0b; color: white; }
        .btn:hover { opacity: 0.9; }
        .btn:disabled { opacity: 0.5; cursor: not-allowed; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500; }
        .badge-draft { background: #6b7280; color: white; }
        .badge-validated { background: #10b981; color: white; }
        .badge-problem { background: #ef4444; color: white; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { padding: 12px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        .table th { background: #f9fafb; font-weight: 600; }
        .loading { text-align: center; padding: 40px; color: #6b7280; }
        .error { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 12px; border-radius: 6px; margin: 10px 0; }
        .success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #16a34a; padding: 12px; border-radius: 6px; margin: 10px 0; }
        .debug-logs { background: #1e293b; color: #e2e8f0; padding: 15px; border-radius: 6px; font-family: 'Courier New', monospace; font-size: 13px; max-height: 300px; overflow-y: auto; }
        .log-entry { margin: 5px 0; padding: 5px; border-radius: 3px; }
        .log-info { background: rgba(59, 130, 246, 0.1); }
        .log-success { background: rgba(16, 185, 129, 0.1); }
        .log-error { background: rgba(239, 68, 68, 0.1); }
        .log-warning { background: rgba(245, 158, 11, 0.1); }
        .flex { display: flex; }
        .justify-between { justify-content: space-between; }
        .items-center { align-items: center; }
        .gap-2 { gap: 8px; }
        .mb-4 { margin-bottom: 16px; }
        .mt-4 { margin-top: 16px; }
        .text-sm { font-size: 14px; }
        .text-gray-600 { color: #6b7280; }
    </style>
</head>
<body>
    <div class="container" x-data="pickupsApp()">
        <!-- Header -->
        <div class="card">
            <div class="flex justify-between items-center">
                <h1>🚛 Test Pickups - Debug Interface</h1>
                <div class="flex gap-2">
                    <button @click="loadPickups" :disabled="loading" class="btn btn-primary">
                        <span x-show="!loading">🔄 Recharger</span>
                        <span x-show="loading">⏳ Chargement...</span>
                    </button>
                    <button @click="testSystem" class="btn btn-warning">🧪 Test Système</button>
                    <button @click="fixAllConfigurations" class="btn btn-success">🔧 Réparer Configs</button>
                    <button @click="fixInvalidTokens" class="btn" style="background: #f59e0b; color: white;">🔍 Check Tokens</button>
                    <button @click="clearLogs" class="btn" style="background: #6b7280; color: white;">🗑️ Clear Logs</button>
                </div>
            </div>
            
            <!-- Stats -->
            <div class="mt-4" x-show="stats">
                <div class="flex gap-2 text-sm">
                    <span class="badge badge-draft" x-text="`Draft: ${stats.draft || 0}`"></span>
                    <span class="badge badge-validated" x-text="`Validés: ${stats.validated || 0}`"></span>
                    <span class="badge badge-problem" x-text="`Problèmes: ${stats.problem || 0}`"></span>
                    <span class="badge" style="background: #3b82f6; color: white;" x-text="`Total: ${stats.total || 0}`"></span>
                </div>
            </div>
            
            <!-- Messages -->
            <div x-show="successMessage" class="success" x-text="successMessage"></div>
            <div x-show="errorMessage" class="error" x-text="errorMessage"></div>
        </div>

        <!-- Liste des Pickups -->
        <div class="card">
            <h2>📦 Liste des Pickups</h2>
            
            <div x-show="loading && pickups.length === 0" class="loading">
                ⏳ Chargement des pickups...
            </div>
            
            <div x-show="!loading && pickups.length === 0" class="loading">
                📭 Aucun pickup trouvé
            </div>
            
            <div x-show="pickups.length > 0">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Transporteur</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th>Colis</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="pickup in pickups" :key="`pickup-${pickup.id}-${Date.now()}`">
                            <tr>
                                <td>
                                    <strong x-text="`#${pickup.id}`"></strong>
                                </td>
                                <td>
                                    <span x-text="pickup.carrier_slug"></span>
                                    <br>
                                    <small class="text-gray-600" x-text="pickup.configuration_name || 'Config inconnue'"></small>
                                </td>
                                <td>
                                    <span class="badge" 
                                          :class="{
                                              'badge-draft': pickup.status === 'draft',
                                              'badge-validated': pickup.status === 'validated', 
                                              'badge-problem': pickup.status === 'problem'
                                          }"
                                          x-text="pickup.status">
                                    </span>
                                </td>
                                <td>
                                    <span x-text="pickup.pickup_date || 'Non définie'"></span>
                                    <br>
                                    <small class="text-gray-600" x-text="formatDate(pickup.created_at)"></small>
                                </td>
                                <td>
                                    <strong x-text="pickup.orders_count || 0"></strong> colis
                                    <br>
                                    <small class="text-gray-600">
                                        <span x-text="`${pickup.total_weight || 0}kg`"></span> · 
                                        <span x-text="`${pickup.total_cod_amount || 0} TND`"></span>
                                    </small>
                                </td>
                                <td>
                                    <div class="flex gap-2">
                                        <button @click="validatePickup(pickup.id)" 
                                                x-show="pickup.status === 'draft' && pickup.can_be_validated"
                                                :disabled="validatingPickups.includes(pickup.id)"
                                                class="btn btn-success">
                                            <span x-show="!validatingPickups.includes(pickup.id)">✅ Valider</span>
                                            <span x-show="validatingPickups.includes(pickup.id)">⏳ Validation...</span>
                                        </button>
                                        
                                        <button @click="diagnosticPickup(pickup.id)" 
                                                class="btn btn-warning">
                                            🔍 Diagnostic
                                        </button>
                                        
                                        <button @click="showPickupDetails(pickup.id)" 
                                                class="btn btn-primary">
                                            👁️ Détails
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Logs de Debug -->
        <div class="card">
            <h3>📋 Logs de Debug</h3>
            <div class="debug-logs">
                <div x-show="debugLogs.length === 0" class="text-gray-600">
                    Aucun log pour le moment...
                </div>
                <template x-for="(log, index) in debugLogs.slice(-20).reverse()" :key="`log-${index}-${log.timestamp || Date.now()}`">
                    <div class="log-entry" 
                         :class="{
                             'log-info': log.type === 'info',
                             'log-success': log.type === 'success', 
                             'log-error': log.type === 'error',
                             'log-warning': log.type === 'warning'
                         }">
                        <span x-text="`[${formatLogTime(log.timestamp)}]`"></span>
                        <span x-text="log.message"></span>
                        <div x-show="log.data" x-text="JSON.stringify(log.data, null, 2)" style="margin-top: 5px; font-size: 11px; opacity: 0.8;"></div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <script>
        // Configuration globale d'Axios
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        axios.defaults.headers.common['Accept'] = 'application/json';

        function pickupsApp() {
            return {
                // État de l'application
                pickups: [],
                loading: false,
                validatingPickups: [],
                debugLogs: [],
                successMessage: '',
                errorMessage: '',
                stats: {},

                // Initialisation
                init() {
                    this.addLog('info', '🚀 Application initialisée');
                    this.loadPickups();
                },

                // Gestion des logs
                addLog(type, message, data = null) {
                    this.debugLogs.push({
                        type: type,
                        message: message,
                        data: data,
                        timestamp: new Date().toISOString()
                    });
                    
                    // Garder seulement les 100 derniers logs
                    if (this.debugLogs.length > 100) {
                        this.debugLogs = this.debugLogs.slice(-100);
                    }
                },

                clearLogs() {
                    this.debugLogs = [];
                    this.addLog('info', '🗑️ Logs effacés');
                },

                // Formatage des dates
                formatDate(dateString) {
                    if (!dateString) return 'N/A';
                    try {
                        return new Date(dateString).toLocaleString('fr-FR');
                    } catch (e) {
                        return dateString;
                    }
                },

                formatLogTime(timestamp) {
                    if (!timestamp) return 'N/A';
                    try {
                        return new Date(timestamp).toLocaleTimeString('fr-FR');
                    } catch (e) {
                        return timestamp;
                    }
                },

                // Gestion des messages
                showSuccess(message) {
                    this.successMessage = message;
                    this.errorMessage = '';
                    setTimeout(() => {
                        this.successMessage = '';
                    }, 5000);
                },

                showError(message) {
                    this.errorMessage = message;
                    this.successMessage = '';
                    setTimeout(() => {
                        this.errorMessage = '';
                    }, 8000);
                },

                // Chargement des pickups
                async loadPickups() {
                    this.loading = true;
                    this.addLog('info', '📦 Chargement des pickups...');
                    
                    try {
                        const response = await axios.get('/admin/delivery/pickups/list');
                        
                        if (response.data.success) {
                            this.pickups = response.data.pickups || [];
                            this.stats = response.data.stats || {};
                            
                            this.addLog('success', `✅ ${this.pickups.length} pickup(s) chargé(s)`, {
                                count: this.pickups.length,
                                stats: this.stats
                            });
                            
                            this.showSuccess(`${this.pickups.length} pickup(s) chargé(s) avec succès`);
                        } else {
                            throw new Error(response.data.error || 'Erreur inconnue');
                        }
                    } catch (error) {
                        const errorMsg = error.response?.data?.error || error.message || 'Erreur de chargement';
                        this.addLog('error', `❌ Erreur chargement: ${errorMsg}`, {
                            status: error.response?.status,
                            data: error.response?.data
                        });
                        this.showError(`Erreur de chargement: ${errorMsg}`);
                    } finally {
                        this.loading = false;
                    }
                },

                // Validation d'un pickup
                async validatePickup(pickupId) {
                    this.validatingPickups.push(pickupId);
                    this.addLog('info', `🔄 Validation pickup #${pickupId}...`);
                    
                    try {
                        const response = await axios.post(`/admin/delivery/pickups/${pickupId}/validate`);
                        
                        if (response.data.success) {
                            this.addLog('success', `✅ Pickup #${pickupId} validé avec succès`, {
                                successful_shipments: response.data.data?.successful_shipments,
                                total_shipments: response.data.data?.total_shipments,
                                tracking_numbers: response.data.data?.tracking_numbers
                            });
                            
                            this.showSuccess(`Pickup #${pickupId} validé ! ${response.data.data?.successful_shipments || 0}/${response.data.data?.total_shipments || 0} colis créés`);
                            
                            // Recharger les pickups pour voir les changements
                            this.loadPickups();
                        } else {
                            throw new Error(response.data.error || 'Validation échouée');
                        }
                    } catch (error) {
                        const errorMsg = error.response?.data?.error || error.message || 'Erreur de validation';
                        this.addLog('error', `❌ Erreur validation pickup #${pickupId}: ${errorMsg}`, {
                            status: error.response?.status,
                            details: error.response?.data?.details
                        });
                        this.showError(`Erreur validation pickup #${pickupId}: ${errorMsg}`);
                    } finally {
                        this.validatingPickups = this.validatingPickups.filter(id => id !== pickupId);
                    }
                },

                // Diagnostic d'un pickup
                async diagnosticPickup(pickupId) {
                    this.addLog('info', `🔍 Diagnostic pickup #${pickupId}...`);
                    
                    try {
                        const response = await axios.get(`/admin/delivery/pickups/${pickupId}/diagnostic`);
                        
                        if (response.data.success) {
                            this.addLog('success', `🔍 Diagnostic pickup #${pickupId} terminé`, response.data.diagnostic);
                            
                            // Afficher les recommandations s'il y en a
                            if (response.data.recommendations && response.data.recommendations.length > 0) {
                                response.data.recommendations.forEach(rec => {
                                    this.addLog(rec.type, `💡 ${rec.message}: ${rec.action}`);
                                });
                            }
                        } else {
                            throw new Error(response.data.error || 'Diagnostic échoué');
                        }
                    } catch (error) {
                        const errorMsg = error.response?.data?.error || error.message || 'Erreur diagnostic';
                        this.addLog('error', `❌ Erreur diagnostic pickup #${pickupId}: ${errorMsg}`);
                        this.showError(`Erreur diagnostic: ${errorMsg}`);
                    }
                },

                // Afficher les détails d'un pickup
                async showPickupDetails(pickupId) {
                    this.addLog('info', `👁️ Chargement détails pickup #${pickupId}...`);
                    
                    try {
                        const response = await axios.get(`/admin/delivery/pickups/${pickupId}/details`);
                        
                        if (response.data.success) {
                            this.addLog('success', `👁️ Détails pickup #${pickupId}`, response.data.pickup);
                        } else {
                            throw new Error(response.data.error || 'Chargement détails échoué');
                        }
                    } catch (error) {
                        const errorMsg = error.response?.data?.error || error.message || 'Erreur chargement détails';
                        this.addLog('error', `❌ Erreur détails pickup #${pickupId}: ${errorMsg}`);
                        this.showError(`Erreur détails: ${errorMsg}`);
                    }
                },

                // Test du système
                async testSystem() {
                    this.addLog('info', '🧪 Test du système...');
                    
                    try {
                        const response = await axios.get('/admin/delivery/test-system');
                        this.addLog('success', '🧪 Test système terminé', response.data);
                        this.showSuccess('Test système terminé - voir les logs pour les détails');
                    } catch (error) {
                        const errorMsg = error.response?.data?.error || error.message || 'Erreur test système';
                        this.addLog('error', `❌ Erreur test système: ${errorMsg}`);
                        this.showError(`Erreur test système: ${errorMsg}`);
                    }
                },

                // 🆕 NOUVELLE MÉTHODE : Réparer toutes les configurations
                async fixAllConfigurations() {
                    this.addLog('info', '🔧 Réparation de toutes les configurations...');
                    
                    try {
                        const response = await axios.post('/admin/fix-all-configurations');
                        
                        if (response.data.success) {
                            this.addLog('success', '🔧 Réparation terminée', response.data.results);
                            
                            // Afficher les recommandations
                            if (response.data.results.recommendations) {
                                response.data.results.recommendations.forEach(rec => {
                                    this.addLog(rec.type, `💡 ${rec.message}`, rec);
                                });
                            }
                            
                            this.showSuccess(`Réparation terminée: ${response.data.results.valid_configs}/${response.data.results.total_configs} configurations valides`);
                        } else {
                            throw new Error(response.data.error);
                        }
                    } catch (error) {
                        const errorMsg = error.response?.data?.error || error.message || 'Erreur réparation';
                        this.addLog('error', `❌ Erreur réparation: ${errorMsg}`);
                        this.showError(`Erreur réparation: ${errorMsg}`);
                    }
                },

                // 🆕 NOUVELLE MÉTHODE : Vérifier les tokens invalides
                async fixInvalidTokens() {
                    this.addLog('info', '🔍 Vérification des tokens invalides...');
                    
                    try {
                        const response = await axios.get('/admin/fix-invalid-tokens');
                        
                        if (response.data.success) {
                            this.addLog('success', `🔍 Vérification terminée: ${response.data.invalid_configs_count} config(s) avec problèmes`, response.data.fixes);
                            
                            // Afficher les recommandations pour chaque config
                            response.data.fixes.forEach(fix => {
                                this.addLog('warning', `⚠️ ${fix.integration_name} (${fix.carrier}):`, {
                                    issues: fix.issues,
                                    recommendations: fix.recommendations
                                });
                            });
                            
                            if (response.data.invalid_configs_count === 0) {
                                this.showSuccess('✅ Toutes les configurations sont valides !');
                            } else {
                                this.showError(`⚠️ ${response.data.invalid_configs_count} configuration(s) à corriger - voir les logs`);
                            }
                        } else {
                            throw new Error(response.data.error);
                        }
                    } catch (error) {
                        const errorMsg = error.response?.data?.error || error.message || 'Erreur vérification tokens';
                        this.addLog('error', `❌ Erreur vérification: ${errorMsg}`);
                        this.showError(`Erreur vérification: ${errorMsg}`);
                    }
                }
            }
        }
    </script>
</body>
</html>