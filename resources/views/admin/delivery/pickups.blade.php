<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Validation Pickup</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .console-log {
            background: #1e1e1e;
            color: #00ff00;
            font-family: 'Courier New', monospace;
            padding: 15px;
            border-radius: 5px;
            height: 400px;
            overflow-y: auto;
            margin: 20px 0;
            font-size: 13px;
            line-height: 1.4;
        }
        .log-success { color: #00ff00; }
        .log-error { color: #ff4444; }
        .log-warning { color: #ffaa00; }
        .log-info { color: #44aaff; }
        .log-debug { color: #888888; }
        .step-card {
            border-left: 4px solid #007bff;
            margin-bottom: 15px;
        }
        .step-success { border-left-color: #28a745; }
        .step-error { border-left-color: #dc3545; }
        .step-warning { border-left-color: #ffc107; }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">🔧 Debug Validation Pickup</h1>
                
                <!-- Contrôles -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label">Pickup ID à tester</label>
                                <input type="number" id="pickupId" class="form-control" value="3" min="1">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Transporteur à tester</label>
                                <select id="carrierSlug" class="form-control">
                                    <option value="jax_delivery">JAX Delivery</option>
                                    <option value="mes_colis">Mes Colis Express</option>
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button onclick="runFullDiagnostic()" class="btn btn-primary me-2">🔍 Diagnostic Complet</button>
                                <button onclick="testValidation()" class="btn btn-success me-2">✅ Test Validation</button>
                                <button onclick="clearConsole()" class="btn btn-secondary">🧹 Clear</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Console de debug -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">📋 Console de Debug</h5>
                    </div>
                    <div class="card-body">
                        <div id="console" class="console-log"></div>
                    </div>
                </div>

                <!-- Étapes de diagnostic -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <h5>🧪 Tests Individuels</h5>
                        <button onclick="testAuth()" class="btn btn-outline-primary btn-sm mb-2">Test Auth</button>
                        <button onclick="testPickupData()" class="btn btn-outline-info btn-sm mb-2">Test Pickup Data</button>
                        <button onclick="testCarrierConfig()" class="btn btn-outline-warning btn-sm mb-2">Test Config Transporteur</button>
                        <button onclick="testCarrierFactory()" class="btn btn-outline-success btn-sm mb-2">Test Factory</button>
                        <button onclick="testCarrierService()" class="btn btn-outline-danger btn-sm mb-2">Test Service API</button>
                    </div>
                    <div class="col-md-6">
                        <h5>📊 Informations Système</h5>
                        <div id="systemInfo" class="small text-muted">
                            <p>🕐 <span id="currentTime"></span></p>
                            <p>🌐 URL: <span id="currentUrl"></span></p>
                            <p>👤 Admin: <span id="adminInfo">Chargement...</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        // Configuration globale
        const API_BASE = '/admin/delivery';
        let consoleElement;
        let logCount = 0;

        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            consoleElement = document.getElementById('console');
            updateSystemInfo();
            log('🚀 Debug Validation Pickup initialisé', 'success');
            log('📍 URL de base API: ' + API_BASE, 'info');
        });

        // Fonction de log avec couleurs
        function log(message, type = 'info') {
            const timestamp = new Date().toLocaleTimeString();
            const logClass = `log-${type}`;
            logCount++;
            
            const logLine = `<div class="${logClass}">[${timestamp}] #${logCount.toString().padStart(3, '0')} ${message}</div>`;
            consoleElement.innerHTML += logLine;
            consoleElement.scrollTop = consoleElement.scrollHeight;
            
            // Log aussi dans la console navigateur
            const consoleMethod = type === 'error' ? 'error' : type === 'warning' ? 'warn' : 'log';
            console[consoleMethod](`[DEBUG PICKUP] ${message}`);
        }

        function clearConsole() {
            consoleElement.innerHTML = '';
            logCount = 0;
            log('🧹 Console nettoyée', 'info');
        }

        function updateSystemInfo() {
            document.getElementById('currentTime').textContent = new Date().toLocaleString();
            document.getElementById('currentUrl').textContent = window.location.href;
            
            // Récupérer info admin
            axios.get('/admin/debug-auth')
                .then(response => {
                    const admin = response.data;
                    document.getElementById('adminInfo').textContent = 
                        `${admin.admin_name} (ID: ${admin.admin_id})`;
                })
                .catch(error => {
                    document.getElementById('adminInfo').textContent = 'Erreur auth';
                });
        }

        // ========================================
        // TESTS INDIVIDUELS
        // ========================================

        async function testAuth() {
            log('🔐 Test authentification...', 'info');
            try {
                const response = await axios.get('/admin/debug-auth');
                if (response.data.is_authenticated) {
                    log(`✅ Authentifié: ${response.data.admin_name} (ID: ${response.data.admin_id})`, 'success');
                } else {
                    log('❌ Non authentifié', 'error');
                }
            } catch (error) {
                log(`❌ Erreur auth: ${error.message}`, 'error');
            }
        }

        async function testPickupData() {
            const pickupId = document.getElementById('pickupId').value;
            log(`📦 Test données pickup #${pickupId}...`, 'info');
            
            try {
                const response = await axios.get(`${API_BASE}/pickups/${pickupId}/details`);
                const pickup = response.data.pickup;
                
                log(`✅ Pickup trouvé: ${pickup.carrier_slug} - Status: ${pickup.status}`, 'success');
                log(`📊 Shipments: ${pickup.shipments?.length || 0}`, 'info');
                log(`⚙️ Config: ${pickup.deliveryConfiguration?.integration_name || 'Aucune'}`, 'info');
                log(`🔴 Can be validated: ${pickup.can_be_validated}`, pickup.can_be_validated ? 'success' : 'warning');
                
                if (pickup.shipments && pickup.shipments.length > 0) {
                    log(`📋 Premier shipment: Order #${pickup.shipments[0].order_id}`, 'debug');
                    log(`📋 Recipient info: ${JSON.stringify(pickup.shipments[0].recipient_info || {})}`, 'debug');
                }
                
            } catch (error) {
                log(`❌ Erreur données pickup: ${error.response?.status} - ${error.message}`, 'error');
                if (error.response?.data) {
                    log(`📝 Détails: ${JSON.stringify(error.response.data)}`, 'debug');
                }
            }
        }

        async function testCarrierConfig() {
            const pickupId = document.getElementById('pickupId').value;
            log(`⚙️ Test configuration transporteur pour pickup #${pickupId}...`, 'info');
            
            try {
                // D'abord récupérer les données du pickup
                const pickupResponse = await axios.get(`${API_BASE}/pickups/${pickupId}/details`);
                const pickup = pickupResponse.data.pickup;
                const config = pickup.deliveryConfiguration;
                
                if (!config) {
                    log('❌ Aucune configuration trouvée', 'error');
                    return;
                }
                
                log(`✅ Config trouvée: ${config.integration_name}`, 'success');
                log(`🔑 Username: ${config.username ? '✓' : '✗'}`, config.username ? 'success' : 'warning');
                log(`🔑 Password/Token: ${config.password ? '✓' : '✗'}`, config.password ? 'success' : 'warning');
                log(`🌍 Environnement: ${config.environment || 'Non défini'}`, 'info');
                log(`🟢 Active: ${config.is_active}`, config.is_active ? 'success' : 'error');
                
                // Test de connexion si config valide
                if (config.is_active && config.password) {
                    log('🧪 Test de connexion avec le transporteur...', 'info');
                    try {
                        const testResponse = await axios.post(`${API_BASE}/configuration/${config.id}/test`);
                        if (testResponse.data.success) {
                            log('✅ Connexion transporteur OK', 'success');
                        } else {
                            log(`❌ Échec connexion: ${testResponse.data.message}`, 'error');
                        }
                    } catch (testError) {
                        log(`❌ Erreur test connexion: ${testError.message}`, 'error');
                    }
                }
                
            } catch (error) {
                log(`❌ Erreur config transporteur: ${error.message}`, 'error');
            }
        }

        async function testCarrierFactory() {
            const carrierSlug = document.getElementById('carrierSlug').value;
            log(`🏭 Test factory pour ${carrierSlug}...`, 'info');
            
            try {
                const response = await axios.get('/admin/test-carrier-factory');
                if (response.data.success) {
                    log(`✅ Factory OK: ${response.data.service_class}`, 'success');
                    log(`🔗 Test connexion: ${response.data.test_connection.success ? '✅' : '❌'}`, 
                        response.data.test_connection.success ? 'success' : 'warning');
                } else {
                    log(`❌ Erreur factory: ${response.data.error}`, 'error');
                }
            } catch (error) {
                log(`❌ Factory non disponible: ${error.message}`, 'error');
                log('💡 Créez la route de test dans routes/admin.php', 'info');
            }
        }

        async function testCarrierService() {
            log('🌐 Test service API transporteur...', 'info');
            log('⚠️ Cette fonction nécessite une route de test spécifique', 'warning');
            // TODO: Implémenter un test direct du service API
        }

        // ========================================
        // DIAGNOSTIC COMPLET
        // ========================================

        async function runFullDiagnostic() {
            log('🔍 === DÉBUT DIAGNOSTIC COMPLET ===', 'info');
            clearConsole();
            
            const steps = [
                { name: 'Authentification', func: testAuth },
                { name: 'Données Pickup', func: testPickupData },
                { name: 'Configuration Transporteur', func: testCarrierConfig },
                { name: 'Factory Transporteur', func: testCarrierFactory }
            ];
            
            for (let i = 0; i < steps.length; i++) {
                const step = steps[i];
                log(`\n📋 ÉTAPE ${i + 1}/4: ${step.name}`, 'info');
                log('─'.repeat(50), 'debug');
                
                try {
                    await step.func();
                } catch (error) {
                    log(`❌ Erreur étape ${step.name}: ${error.message}`, 'error');
                }
                
                // Petite pause entre les étapes
                await new Promise(resolve => setTimeout(resolve, 500));
            }
            
            log('\n🏁 === FIN DIAGNOSTIC COMPLET ===', 'info');
        }

        // ========================================
        // TEST DE VALIDATION RÉELLE
        // ========================================

        async function testValidation() {
            const pickupId = document.getElementById('pickupId').value;
            log(`✅ === TEST VALIDATION PICKUP #${pickupId} ===`, 'info');
            
            try {
                log('📡 Envoi requête de validation...', 'info');
                const startTime = Date.now();
                
                const response = await axios.post(`${API_BASE}/pickups/${pickupId}/validate`);
                const duration = Date.now() - startTime;
                
                log(`✅ Validation réussie en ${duration}ms`, 'success');
                log(`📊 Résultat: ${JSON.stringify(response.data, null, 2)}`, 'success');
                
            } catch (error) {
                const duration = Date.now() - startTime;
                log(`❌ Validation échouée après ${duration}ms`, 'error');
                log(`📱 Status: ${error.response?.status || 'Network Error'}`, 'error');
                log(`💬 Message: ${error.message}`, 'error');
                
                if (error.response?.data) {
                    log(`📝 Détails serveur:`, 'error');
                    log(JSON.stringify(error.response.data, null, 2), 'debug');
                }
                
                // Suggestions de debug
                log('\n🔧 SUGGESTIONS DE DEBUG:', 'warning');
                log('1. Vérifiez les logs Laravel: tail -f storage/logs/laravel.log', 'info');
                log('2. Vérifiez que SimpleCarrierFactory existe', 'info');
                log('3. Vérifiez les imports dans Pickup.php', 'info');
                log('4. Vérifiez la configuration du transporteur', 'info');
            }
        }

        // Auto-refresh du temps
        setInterval(updateSystemInfo, 30000);
    </script>
</body>
</html>