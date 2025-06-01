@extends('layouts.admin')

@section('title', 'Intégrations WooCommerce')

@section('css')
<style>
    .connection-status {
        display: inline-block;
        width: 15px;
        height: 15px;
        border-radius: 50%;
        margin-right: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .status-active {
        background: linear-gradient(135deg, #10b981, #059669);
        animation: pulse-green 2s infinite;
    }
    
    .status-inactive {
        background: linear-gradient(135deg, #ef4444, #dc2626);
    }
    
    .status-syncing {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        animation: pulse-orange 1.5s infinite;
    }
    
    .status-error {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        animation: pulse-red 1s infinite;
    }
    
    @keyframes pulse-green {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.7; transform: scale(1.1); }
    }
    
    @keyframes pulse-orange {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.6; transform: scale(1.2); }
    }
    
    @keyframes pulse-red {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    
    .card-stats {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
    }
    
    .stat-item {
        text-align: center;
        padding: 16px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        margin-bottom: 16px;
        transition: all 0.3s ease;
    }
    
    .stat-item:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: translateY(-2px);
    }
    
    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 8px;
        background: linear-gradient(45deg, #ffffff, #f0f9ff);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .stat-label {
        font-size: 0.9rem;
        opacity: 0.9;
        font-weight: 500;
    }
    
    .integration-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 16px;
        border: 2px solid #e5e7eb;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .integration-card.active {
        border-color: #10b981;
        box-shadow: 0 4px 20px rgba(16, 185, 129, 0.15);
    }
    
    .integration-card.inactive {
        border-color: #f3f4f6;
        opacity: 0.7;
    }
    
    .integration-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
    }
    
    .integration-header {
        display: flex;
        justify-content: between;
        align-items: center;
        margin-bottom: 16px;
    }
    
    .integration-info h6 {
        margin: 0;
        font-weight: 600;
        color: #1f2937;
    }
    
    .integration-url {
        color: #6b7280;
        font-size: 0.9rem;
        margin-top: 4px;
    }
    
    .integration-actions {
        display: flex;
        gap: 8px;
        align-items: center;
    }
    
    .config-section {
        background: rgba(255, 255, 255, 0.98);
        border-radius: 16px;
        padding: 32px;
        margin-bottom: 24px;
        border: 1px solid rgba(102, 126, 234, 0.1);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        backdrop-filter: blur(20px);
    }
    
    .connection-test-result {
        margin-top: 16px;
        padding: 16px;
        border-radius: 12px;
        border-left: 4px solid;
        display: none;
        animation: slideIn 0.3s ease;
    }
    
    @keyframes slideIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .connection-test-result.success {
        background: linear-gradient(135deg, #ecfdf5, #d1fae5);
        border-left-color: #10b981;
        color: #047857;
    }
    
    .connection-test-result.error {
        background: linear-gradient(135deg, #fef2f2, #fecaca);
        border-left-color: #ef4444;
        color: #dc2626;
    }
    
    .help-box {
        background: linear-gradient(135deg, #dbeafe, #bfdbfe);
        border: 1px solid #93c5fd;
        border-radius: 12px;
        padding: 20px;
        margin: 20px 0;
        color: #1e40af;
    }
    
    .help-box h6 {
        color: #1d4ed8;
        margin-bottom: 16px;
        font-weight: 600;
    }
    
    .help-box ol {
        margin-bottom: 0;
        padding-left: 20px;
    }
    
    .help-box li {
        margin-bottom: 8px;
        line-height: 1.5;
    }
    
    .modern-checkbox {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px;
        background: rgba(102, 126, 234, 0.05);
        border-radius: 12px;
        border: 2px solid transparent;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .modern-checkbox:hover {
        background: rgba(102, 126, 234, 0.1);
        border-color: rgba(102, 126, 234, 0.2);
    }
    
    .modern-checkbox input[type="checkbox"] {
        width: 20px;
        height: 20px;
        accent-color: #667eea;
    }
    
    .integration-info-box {
        background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
        border: 1px solid #0ea5e9;
        border-radius: 12px;
        padding: 20px;
        margin: 20px 0;
        color: #0369a1;
    }
    
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 24px;
    }
    
    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 24px;
    }
    
    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }
    
    input:checked + .slider {
        background-color: #10b981;
    }
    
    input:checked + .slider:before {
        transform: translateX(26px);
    }
    
    /* Styles pour le modal */
    .modal-content {
        border-radius: 16px;
        border: none;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
    }
    
    .modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 16px 16px 0 0;
        border-bottom: none;
        padding: 20px 24px;
    }
    
    .modal-header .btn-close {
        filter: invert(1);
        opacity: 0.8;
    }
    
    .modal-header .btn-close:hover {
        opacity: 1;
    }
    
    .modal-body {
        padding: 24px;
    }
    
    .modal-footer {
        padding: 20px 24px;
        border-top: 1px solid #e5e7eb;
        border-radius: 0 0 16px 16px;
    }
    
    .modal-lg {
        max-width: 800px;
    }
    
    /* Animation pour le modal */
    .modal.fade .modal-dialog {
        transform: translateY(-50px);
        transition: transform 0.3s ease-out;
    }
    
    .modal.show .modal-dialog {
        transform: translateY(0);
    }
</style>
@endsection

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="fab fa-wordpress me-3"></i>Intégrations WooCommerce
    </h1>
    <div class="d-flex gap-2">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addIntegrationModal">
            <i class="fas fa-plus me-2"></i>Nouvelle intégration
        </button>
        <button class="btn btn-outline-primary" onclick="refreshStats()">
            <i class="fas fa-sync-alt me-2"></i>Actualiser
        </button>
        @if($integrations->where('is_active', true)->count() > 0)
            <a href="{{ route('admin.woocommerce.sync') }}" class="btn btn-primary">
                <i class="fas fa-download me-2"></i>Synchroniser toutes
            </a>
        @endif
    </div>
</div>

<!-- Statistiques globales -->
<div class="card-stats">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h4 class="mb-3">
                <i class="fas fa-chart-bar me-2"></i>Aperçu des intégrations
            </h4>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="stat-item">
                        <div class="stat-number" id="total-integrations">{{ $syncStats['total_integrations'] }}</div>
                        <div class="stat-label">Intégrations totales</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-item">
                        <div class="stat-number" id="active-integrations">{{ $syncStats['active_integrations'] }}</div>
                        <div class="stat-label">Intégrations actives</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-item">
                        <div class="stat-number" id="total-orders">{{ $syncStats['total_orders'] }}</div>
                        <div class="stat-label">Commandes importées</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 text-end">
            <div class="d-flex flex-column gap-2">
                <span class="badge bg-light text-dark fs-6 p-2">
                    <i class="fas fa-clock me-2"></i>Sync auto: toutes les 3 min
                </span>
                <span class="badge bg-light text-dark fs-6 p-2">
                    <i class="fas fa-store me-2"></i>Multi-boutiques supportées
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Liste des intégrations existantes -->
@if($integrations->count() > 0)
<div class="row mb-4">
    <div class="col-12">
        <h4 class="mb-3">
            <i class="fas fa-list me-2"></i>Intégrations configurées
        </h4>
        
        @foreach($integrations as $integration)
        <div class="integration-card {{ $integration->is_active ? 'active' : 'inactive' }}">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="integration-info">
                        <div class="d-flex align-items-center mb-2">
                            <span class="connection-status {{ $integration->is_active ? ($integration->sync_status === 'syncing' ? 'status-syncing' : ($integration->sync_status === 'error' ? 'status-error' : 'status-active')) : 'status-inactive' }}"></span>
                            <h6 class="mb-0">{{ parse_url($integration->store_url, PHP_URL_HOST) }}</h6>
                        </div>
                        <div class="integration-url">{{ $integration->store_url }}</div>
                        @if($integration->last_sync_at)
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>Dernière sync: {{ $integration->last_sync_at->diffForHumans() }}
                            </small>
                        @endif
                        @if($integration->sync_error)
                            <div class="text-danger mt-1">
                                <small><i class="fas fa-exclamation-triangle me-1"></i>{{ $integration->sync_error }}</small>
                            </div>
                        @endif
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="d-flex justify-content-end align-items-center gap-3">
                        <!-- Toggle Switch -->
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted">{{ $integration->is_active ? 'Actif' : 'Inactif' }}</span>
                            <label class="toggle-switch">
                                <input type="checkbox" {{ $integration->is_active ? 'checked' : '' }} 
                                       onchange="toggleIntegration({{ $integration->id }}, this)">
                                <span class="slider"></span>
                            </label>
                        </div>
                        
                        <!-- Actions -->
                        <div class="integration-actions">
                            <button class="btn btn-sm btn-outline-danger" 
                                    onclick="deleteIntegration({{ $integration->id }}, '{{ parse_url($integration->store_url, PHP_URL_HOST) }}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

@if($integrations->count() === 0)
<!-- Message si aucune intégration -->
<div class="row">
    <div class="col-12">
        <div class="config-section text-center">
            <div class="mb-4">
                <i class="fab fa-wordpress display-4 text-muted mb-3"></i>
                <h4 class="text-muted">Aucune intégration WooCommerce configurée</h4>
                <p class="text-muted">Commencez par connecter votre première boutique WooCommerce</p>
            </div>
            <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#addIntegrationModal">
                <i class="fas fa-plus me-2"></i>Ajouter votre première intégration
            </button>
        </div>
    </div>
</div>
@endif

<!-- Modal pour ajouter une nouvelle intégration -->
<div class="modal fade" id="addIntegrationModal" tabindex="-1" aria-labelledby="addIntegrationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addIntegrationModalLabel">
                    <i class="fab fa-wordpress me-2"></i>Nouvelle intégration WooCommerce
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form action="{{ route('admin.woocommerce.store') }}" method="POST" id="woocommerce-form">
                @csrf
                <div class="modal-body">
                    <div class="connection-test-result" id="connection-result"></div>
                    
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="store_url">URL de la boutique WooCommerce</label>
                                <input type="url" class="form-control @error('store_url') is-invalid @enderror" 
                                       id="store_url" name="store_url" 
                                       value="{{ old('store_url') }}" 
                                       required placeholder="https://votreboutique.com">
                                @error('store_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">L'URL complète de votre boutique WooCommerce</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="consumer_key">Clé API (Consumer Key)</label>
                                <input type="text" class="form-control @error('consumer_key') is-invalid @enderror" 
                                       id="consumer_key" name="consumer_key" 
                                       value="{{ old('consumer_key') }}" 
                                       required placeholder="ck_xxxxxxxxxxxxxxxxx">
                                @error('consumer_key')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="consumer_secret">Secret API (Consumer Secret)</label>
                                <input type="password" class="form-control @error('consumer_secret') is-invalid @enderror" 
                                       id="consumer_secret" name="consumer_secret" 
                                       value="{{ old('consumer_secret') }}" 
                                       required placeholder="cs_xxxxxxxxxxxxxxxxx">
                                @error('consumer_secret')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="modern-checkbox mb-4">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                        <label class="form-check-label fw-bold" for="is_active">
                            Activer immédiatement cette intégration
                            <small class="d-block text-muted">Synchronisation automatique toutes les 3 minutes</small>
                        </label>
                    </div>
                    
                    <div class="help-box">
                        <h6><i class="fas fa-info-circle me-2"></i>Comment obtenir vos clés API WooCommerce</h6>
                        <ol>
                            <li>Connectez-vous à l'administration WordPress de votre site</li>
                            <li>Allez dans <strong>WooCommerce → Paramètres → Avancé → REST API</strong></li>
                            <li>Cliquez sur <strong>"Ajouter une clé"</strong></li>
                            <li>Entrez une description (ex: "Order Manager Integration")</li>
                            <li>Sélectionnez <strong>"Lecture/Écriture"</strong> pour les droits</li>
                            <li>Cliquez sur <strong>"Générer une clé API"</strong></li>
                            <li>Copiez la "Clé client" et le "Secret client" dans les champs ci-dessus</li>
                        </ol>
                    </div>
                    
                    <div class="integration-info-box">
                        <h6><i class="fas fa-magic me-2"></i>Fonctionnement de l'intégration automatique</h6>
                        <ul>
                            <li><strong>Multi-boutiques :</strong> Vous pouvez connecter plusieurs boutiques WooCommerce</li>
                            <li><strong>Import intelligent :</strong> Seules les commandes terminées/annulées gardent leur statut</li>
                            <li><strong>Statut "nouvelle" :</strong> Toutes les autres commandes deviennent "nouvelle" pour traitement</li>
                            <li><strong>Localisation automatique :</strong> Les régions et villes sont créées automatiquement</li>
                            <li><strong>Synchronisation bidirectionnelle :</strong> Les modifications sont synchronisées dans les deux sens</li>
                        </ul>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary" onclick="testConnection()">
                        <i class="fas fa-plug me-2"></i>Tester la connexion
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Annuler
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Ajouter cette intégration
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Configuration CSRF
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Test de connexion en temps réel
    function testConnection() {
        const storeUrl = document.getElementById('store_url').value;
        const consumerKey = document.getElementById('consumer_key').value;
        const consumerSecret = document.getElementById('consumer_secret').value;
        const resultDiv = document.getElementById('connection-result');
        const testBtn = document.querySelector('button[onclick="testConnection()"]');
        
        if (!storeUrl || !consumerKey || !consumerSecret) {
            showConnectionResult('error', 'Veuillez remplir tous les champs requis avant de tester la connexion.');
            return;
        }
        
        // Animation du bouton
        testBtn.disabled = true;
        testBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Test en cours...';
        
        fetch('{{ route("admin.woocommerce.test-connection") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                store_url: storeUrl,
                consumer_key: consumerKey,
                consumer_secret: consumerSecret
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let message = data.message;
                if (data.store_info) {
                    message += `<br><strong>Boutique:</strong> ${data.store_info.name}`;
                    if (data.store_info.version) {
                        message += `<br><strong>Version WooCommerce:</strong> ${data.store_info.version}`;
                    }
                }
                showConnectionResult('success', message);
            } else {
                showConnectionResult('error', data.message);
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showConnectionResult('error', 'Erreur de connexion: ' + error.message);
        })
        .finally(() => {
            testBtn.disabled = false;
            testBtn.innerHTML = '<i class="fas fa-plug me-2"></i>Tester la connexion';
        });
    }
    
    function showConnectionResult(type, message) {
        const resultDiv = document.getElementById('connection-result');
        resultDiv.className = `connection-test-result ${type}`;
        resultDiv.innerHTML = `
            <div class="d-flex align-items-start">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-3 mt-1"></i>
                <div>${message}</div>
            </div>
        `;
        resultDiv.style.display = 'block';
        
        // Cacher après 10 secondes si succès
        if (type === 'success') {
            setTimeout(() => {
                resultDiv.style.display = 'none';
            }, 10000);
        }
    }
    
    // Toggle d'activation/désactivation
    function toggleIntegration(id, checkbox) {
        const isActive = checkbox.checked;
        
        fetch(`{{ route('admin.woocommerce.index') }}/toggle/${id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                is_active: isActive
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Mettre à jour visuellement la carte d'intégration
                const card = checkbox.closest('.integration-card');
                if (isActive) {
                    card.classList.remove('inactive');
                    card.classList.add('active');
                } else {
                    card.classList.remove('active');
                    card.classList.add('inactive');
                }
                
                // Actualiser les statistiques
                refreshStats();
                
                // Afficher un message de succès
                showNotification('success', data.message);
            } else {
                // Revenir à l'état précédent en cas d'erreur
                checkbox.checked = !isActive;
                showNotification('error', 'Erreur lors de la modification');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            checkbox.checked = !isActive;
            showNotification('error', 'Erreur de connexion');
        });
    }
    
    // Suppression d'intégration
    function deleteIntegration(id, storeName) {
        if (confirm(`Êtes-vous sûr de vouloir supprimer l'intégration avec ${storeName} ?`)) {
            window.location.href = `{{ route('admin.woocommerce.index') }}/delete/${id}`;
        }
    }
    
    // Actualisation des statistiques
    function refreshStats() {
        fetch('{{ route("admin.woocommerce.stats") }}')
        .then(response => response.json())
        .then(data => {
            document.getElementById('total-integrations').textContent = data.total_integrations;
            document.getElementById('active-integrations').textContent = data.active_integrations;
            document.getElementById('total-orders').textContent = data.total_orders;
        })
        .catch(error => {
            console.error('Erreur lors de l\'actualisation:', error);
        });
    }
    
    // Fonction pour afficher les notifications
    function showNotification(type, message) {
        // Créer une notification temporaire
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Supprimer automatiquement après 3 secondes
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 3000);
    }
    
    // Auto-refresh des stats toutes les 30 secondes
    setInterval(refreshStats, 30000);
    
    // Gestion du formulaire
    document.addEventListener('DOMContentLoaded', function() {
        // Validation en temps réel des URLs
        const urlInput = document.getElementById('store_url');
        urlInput.addEventListener('blur', function() {
            if (this.value && !this.value.startsWith('http')) {
                this.value = 'https://' + this.value;
            }
        });
        
        // Réinitialiser le formulaire après soumission réussie
        const form = document.getElementById('woocommerce-form');
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Enregistrement...';
        });
        
        // Rouvrir le modal en cas d'erreur de validation
        @if($errors->any())
            var addIntegrationModal = new bootstrap.Modal(document.getElementById('addIntegrationModal'));
            addIntegrationModal.show();
        @endif
        
        // Réinitialiser le formulaire quand le modal se ferme
        document.getElementById('addIntegrationModal').addEventListener('hidden.bs.modal', function () {
            const form = document.getElementById('woocommerce-form');
            form.reset();
            document.getElementById('connection-result').style.display = 'none';
            
            // Remettre le bouton submit dans son état normal
            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Ajouter cette intégration';
        });
    });
</script>
@endsection