<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test et Diagnostic JAX Delivery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h1 class="h3 mb-0 text-primary">
                            <i class="fas fa-truck me-2"></i>
                            Test et Diagnostic JAX Delivery
                        </h1>
                        <p class="text-muted mb-0">Diagnostiquez et résolvez les problèmes de validation JAX</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tests rapides -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-flask me-2"></i>
                            Tests Rapides
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-primary" onclick="testQuickJax()">
                                <i class="fas fa-plug me-2"></i>
                                Test de Connexion JAX
                            </button>
                            <button class="btn btn-outline-success" onclick="diagnosticJaxComplete()">
                                <i class="fas fa-search me-2"></i>
                                Diagnostic Complet
                            </button>
                            <button class="btn btn-outline-warning" onclick="testJaxCreation()">
                                <i class="fas fa-plus me-2"></i>
                                Test Création Colis
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            Informations
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="admin-info">
                            <p><strong>Admin:</strong> {{ auth('admin')->user()->name }}</p>
                            <p><strong>Email:</strong> {{ auth('admin')->user()->email }}</p>
                            <p><strong>ID:</strong> {{ auth('admin')->user()->id }}</p>
                        </div>
                        <hr>
                        <div id="system-info">
                            <p><small class="text-muted">Laravel {{ app()->version() }}</small></p>
                            <p><small class="text-muted">PHP {{ PHP_VERSION }}</small></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Résultats des tests -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-terminal me-2"></i>
                            Résultats des Tests
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="test-results">
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-clipboard-list fa-3x mb-3"></i>
                                <p>Aucun test exécuté. Cliquez sur un bouton ci-dessus pour commencer.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Zone de logs détaillés -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-file-alt me-2"></i>
                            Logs Détaillés
                        </h5>
                        <button class="btn btn-sm btn-outline-light" onclick="clearLogs()">
                            <i class="fas fa-trash me-1"></i>
                            Effacer
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <pre id="detailed-logs" class="bg-dark text-light p-3 m-0" style="max-height: 400px; overflow-y: auto; font-size: 0.85rem;"></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Configuration CSRF
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        function logMessage(message, type = 'info') {
            const timestamp = new Date().toLocaleTimeString();
            const logElement = document.getElementById('detailed-logs');
            const colorClass = {
                'info': '🔵',
                'success': '✅',
                'error': '❌', 
                'warning': '⚠️'
            }[type] || 'ℹ️';
            
            logElement.textContent += `[${timestamp}] ${colorClass} ${message}\n`;
            logElement.scrollTop = logElement.scrollHeight;
        }

        function clearLogs() {
            document.getElementById('detailed-logs').textContent = '';
        }

        function showResults(data, title) {
            const resultsDiv = document.getElementById('test-results');
            
            let html = `<div class="alert alert-${data.success ? 'success' : 'danger'} border-0">
                <h5><i class="fas fa-${data.success ? 'check-circle' : 'exclamation-circle'} me-2"></i>${title}</h5>`;
            
            if (data.success) {
                html += `<p class="mb-0">${data.message || 'Test réussi'}</p>`;
            } else {
                html += `<p class="mb-0"><strong>Erreur:</strong> ${data.error || data.message}</p>`;
            }
            
            html += '</div>';
            
            if (data.diagnostic || data.config_info || data.summary) {
                html += '<div class="mt-3"><details><summary class="btn btn-outline-secondary btn-sm">Voir les détails</summary>';
                html += `<pre class="mt-2 p-3 bg-light rounded"><code>${JSON.stringify(data, null, 2)}</code></pre>`;
                html += '</details></div>';
            }
            
            resultsDiv.innerHTML = html;
        }

        async function makeRequest(url, method = 'GET', data = null) {
            logMessage(`Envoi requête ${method} vers ${url}`, 'info');
            
            try {
                const options = {
                    method: method,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    }
                };
                
                if (data && method !== 'GET') {
                    options.body = JSON.stringify(data);
                }
                
                const response = await fetch(url, options);
                const result = await response.json();
                
                logMessage(`Réponse reçue (${response.status}): ${response.ok ? 'Succès' : 'Erreur'}`, response.ok ? 'success' : 'error');
                
                return result;
            } catch (error) {
                logMessage(`Erreur réseau: ${error.message}`, 'error');
                throw error;
            }
        }

        async function testQuickJax() {
            logMessage('🧪 Début test de connexion JAX', 'info');
            
            try {
                const result = await makeRequest('/admin/delivery/quick-test-jax');
                showResults(result, 'Test de Connexion JAX');
                
                if (result.success) {
                    logMessage('✅ Test de connexion JAX réussi', 'success');
                    if (result.config_test && result.config_test.success) {
                        logMessage(`✅ API JAX fonctionnelle pour config "${result.config_info.integration_name}"`, 'success');
                    } else {
                        logMessage(`❌ API JAX échouée: ${result.config_test?.message}`, 'error');
                    }
                } else {
                    logMessage(`❌ Test de connexion échoué: ${result.error}`, 'error');
                }
            } catch (error) {
                showResults({success: false, error: error.message}, 'Test de Connexion JAX');
                logMessage(`❌ Erreur test: ${error.message}`, 'error');
            }
        }

        async function diagnosticJaxComplete() {
            logMessage('🔍 Début diagnostic complet JAX', 'info');
            
            try {
                const result = await makeRequest('/admin/delivery/diagnostic-jax-complete');
                showResults(result, 'Diagnostic Complet JAX');
                
                if (result.success) {
                    const summary = result.summary;
                    logMessage(`✅ Diagnostic terminé: ${summary.total_jax_configs} config(s), ${summary.working_configs} fonctionnelle(s)`, 'success');
                    
                    if (summary.working_configs === 0) {
                        logMessage('⚠️ Aucune configuration JAX fonctionnelle trouvée', 'warning');
                    }
                    
                    // Afficher les recommandations
                    if (result.diagnostic.recommendations) {
                        result.diagnostic.recommendations.forEach(rec => {
                            logMessage(`📋 ${rec.type.toUpperCase()}: ${rec.message}`, rec.type === 'error' ? 'error' : 'warning');
                        });
                    }
                } else {
                    logMessage(`❌ Diagnostic échoué: ${result.error}`, 'error');
                }
            } catch (error) {
                showResults({success: false, error: error.message}, 'Diagnostic Complet JAX');
                logMessage(`❌ Erreur diagnostic: ${error.message}`, 'error');
            }
        }

        async function testJaxCreation() {
            if (!confirm('⚠️ Ce test va créer un vrai colis dans votre compte JAX. Continuer ?')) {
                return;
            }
            
            logMessage('🚀 Début test de création de colis JAX', 'warning');
            
            try {
                const result = await makeRequest('/admin/delivery/test-jax-creation-real', 'POST');
                showResults(result, 'Test Création Colis JAX');
                
                if (result.success) {
                    logMessage(`✅ Colis créé avec succès! Numéro de suivi: ${result.tracking_number}`, 'success');
                    logMessage(`📦 Configuration utilisée: ${result.config_used.integration_name}`, 'info');
                } else {
                    logMessage(`❌ Échec création colis: ${result.error}`, 'error');
                    if (result.details) {
                        logMessage(`📋 Détails: ${JSON.stringify(result.details)}`, 'error');
                    }
                }
            } catch (error) {
                showResults({success: false, error: error.message}, 'Test Création Colis JAX');
                logMessage(`❌ Erreur test création: ${error.message}`, 'error');
            }
        }

        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            logMessage('🚀 Interface de test JAX initialisée', 'success');
            logMessage(`👤 Admin connecté: {{ auth('admin')->user()->name }}`, 'info');
        });
    </script>
</body>
</html>