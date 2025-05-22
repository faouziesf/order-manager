@extends('layouts.admin')

@section('title', 'Traitement des Commandes')

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        /* ===== CSS VARIABLES ===== */
        :root {
            --primary-color: #4e73df;
            --success-color: #1cc88a;
            --danger-color: #e74a3b;
            --warning-color: #f6c23e;
            --info-color: #36b9cc;
            --secondary-color: #858796;
            --light-bg: #f8f9fc;
            --border-color: #e3e6f0;
            --white: #ffffff;
            --shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        }

        /* ===== LAYOUT ===== */
        body {
            background-color: var(--light-bg);
            font-family: 'Nunito', sans-serif;
        }

        .order-header {
            background: var(--white);
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow);
            margin-bottom: 20px;
        }

        .order-info-badges {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .queue-tabs {
            background: var(--white);
            padding: 10px 20px;
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .queue-tabs .nav-link {
            color: var(--secondary-color);
            border: none;
            padding: 8px 20px;
            margin-right: 10px;
            font-weight: 500;
            transition: all 0.3s;
            cursor: pointer;
        }

        .queue-tabs .nav-link.active {
            color: var(--primary-color);
            background-color: var(--light-bg);
            border-bottom: 3px solid var(--primary-color);
        }

        .queue-tabs .nav-link .badge {
            margin-left: 8px;
            font-size: 0.75rem;
        }

        .work-area {
            max-width: 1400px;
            margin: 20px auto;
            padding: 0 15px;
        }

        .order-container {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 20px;
            background: var(--white);
            border-radius: 8px;
            box-shadow: var(--shadow);
            padding: 20px;
        }

        /* ===== FORM STYLES ===== */
        .section-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
            color: var(--primary-color);
        }

        .client-form {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            font-size: 0.9rem;
            color: #5a5c69;
        }

        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #d1d3e2;
            border-radius: 4px;
            font-size: 0.9rem;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
            outline: none;
        }

        .required::after {
            content: " *";
            color: var(--danger-color);
        }

        /* ===== CART SUMMARY ===== */
        .cart-summary {
            margin-top: 20px;
            padding: 15px;
            background: linear-gradient(135deg, var(--light-bg) 0%, #f5f7fa 100%);
            border-radius: 6px;
        }

        .cart-summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .cart-summary-total {
            display: flex;
            justify-content: space-between;
            font-weight: 700;
            font-size: 1.1rem;
            padding-top: 8px;
            border-top: 2px solid var(--border-color);
            margin-top: 8px;
        }

        /* ===== ACTION SECTION ===== */
        .action-section {
            background: var(--white);
            border-radius: 8px;
            padding: 20px;
            border: 1px solid var(--border-color);
            position: sticky;
            top: 80px;
        }

        .action-button {
            width: 100%;
            padding: 12px 15px;
            font-weight: 600;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
            background: var(--success-color);
            color: var(--white);
            font-size: 1rem;
        }

        .action-button:hover:not(:disabled) {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .action-button:disabled {
            background: var(--secondary-color);
            cursor: not-allowed;
        }

        .conditional-section {
            background: var(--light-bg);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 15px;
            display: none;
        }

        /* ===== STATUS BADGES ===== */
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-nouvelle {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-confirmée {
            background: #d4edda;
            color: #155724;
        }

        .status-annulée {
            background: #f8d7da;
            color: #721c24;
        }

        .status-datée {
            background: #fff3cd;
            color: #856404;
        }

        .priority-normale {
            background: #e9ecef;
            color: #495057;
        }

        .priority-urgente {
            background: #fff3cd;
            color: #856404;
        }

        .priority-vip {
            background: #f8d7da;
            color: #721c24;
        }

        /* ===== EMPTY STATES ===== */
        .no-orders,
        .loading-indicator,
        .error-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .no-orders .icon,
        .error-state .icon {
            font-size: 4rem;
            color: #d1d3e2;
            margin-bottom: 20px;
        }

        .error-state .icon {
            color: var(--danger-color);
        }

        /* ===== NOTIFICATIONS ===== */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            max-width: 350px;
            padding: 15px 20px;
            border-radius: 6px;
            background: var(--white);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transform: translateY(-100px);
            opacity: 0;
            transition: all 0.3s ease;
        }

        .notification.show {
            transform: translateY(0);
            opacity: 1;
        }

        .notification.success {
            border-left: 4px solid var(--success-color);
        }

        .notification.error {
            border-left: 4px solid var(--danger-color);
        }

        .notification.warning {
            border-left: 4px solid var(--warning-color);
        }

        .notification.info {
            border-left: 4px solid var(--info-color);
        }

        /* ===== LOADING ===== */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            display: none;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 5px solid var(--light-bg);
            border-top-color: var(--primary-color);
            animation: spin 1s infinite linear;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        /* ===== DEBUG CONSOLE ===== */
        .debug-console {
            position: fixed;
            bottom: 20px;
            left: 20px;
            width: 300px;
            max-height: 200px;
            background: #000;
            color: #0f0;
            font-family: monospace;
            font-size: 12px;
            padding: 10px;
            border-radius: 4px;
            overflow-y: auto;
            z-index: 1001;
            display: none;
        }

        .debug-console.show {
            display: block;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 992px) {
            .order-container {
                grid-template-columns: 1fr;
            }

            .action-section {
                position: relative;
                top: 0;
                margin-top: 20px;
            }
        }

        @media (max-width: 768px) {
            .client-form {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid px-0">
        <!-- Header -->
        <div class="order-header">
            <div>
                <h1 class="h3 mb-0 text-gray-800">Traitement des Commandes</h1>
                <div class="text-muted small" id="currentOrderInfo">
                    <span id="orderIdDisplay">Aucune commande sélectionnée</span>
                    <span id="queueTypeDisplay" class="ms-2"></span>
                </div>
            </div>
            <div class="order-info-badges">
                <span class="status-badge" id="orderStatus" style="display: none;">---</span>
                <span class="status-badge" id="orderPriority" style="display: none;">---</span>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="toggleDebugBtn">
                    <i class="fas fa-bug"></i> Debug
                </button>
            </div>
        </div>

        <!-- Queue Tabs -->
        <div class="queue-tabs">
            <ul class="nav nav-tabs" id="queueTabs">
                <li class="nav-item">
                    <a class="nav-link active" href="#" data-queue="standard">
                        <i class="fas fa-list me-1"></i> File Standard
                        <span class="badge bg-secondary" id="standardCount">0</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" data-queue="dated">
                        <i class="fas fa-calendar-alt me-1"></i> File Datée
                        <span class="badge bg-secondary" id="datedCount">0</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" data-queue="old">
                        <i class="fas fa-history me-1"></i> File Ancienne
                        <span class="badge bg-secondary" id="oldCount">0</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Work Area -->
        <div class="work-area">
            <div id="orderContent">
                <div class="loading-indicator">
                    <div class="spinner"></div>
                    <p class="mt-3">Chargement en cours...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Debug Console -->
    <div class="debug-console" id="debugConsole">
        <div id="debugContent"></div>
    </div>

    <!-- Notifications -->
    <div id="notificationsContainer"></div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js"></script>
    <script>
        // ===== DEBUG SYSTEM =====
        const Debug = {
            enabled: false,
            logs: [],

            log(message, type = 'info') {
                const timestamp = new Date().toLocaleTimeString();
                const logEntry = `[${timestamp}] ${type.toUpperCase()}: ${message}`;

                this.logs.push(logEntry);
                console.log(logEntry);

                if (this.enabled) {
                    this.updateConsole();
                }

                // Keep only last 50 logs
                if (this.logs.length > 50) {
                    this.logs.shift();
                }
            },

            updateConsole() {
                const console = document.getElementById('debugContent');
                if (console) {
                    console.innerHTML = this.logs.slice(-20).join('<br>');
                    console.scrollTop = console.scrollHeight;
                }
            },

            toggle() {
                this.enabled = !this.enabled;
                const debugConsole = document.getElementById('debugConsole');
                const toggleBtn = document.getElementById('toggleDebugBtn');

                if (this.enabled) {
                    debugConsole.classList.add('show');
                    toggleBtn.innerHTML = '<i class="fas fa-bug"></i> Hide Debug';
                    this.updateConsole();
                } else {
                    debugConsole.classList.remove('show');
                    toggleBtn.innerHTML = '<i class="fas fa-bug"></i> Debug';
                }
            }
        };

        // ===== ORDER MANAGER =====
        const OrderManager = {
            config: {
                currentQueue: 'standard',
                currentOrderId: null,
                csrfToken: '{{ csrf_token() }}',
                routes: {
                    counts: '{{ route('admin.process.getCounts') }}',
                    queue: '{{ url('/admin/process') }}',
                    action: '{{ url('/admin/process/action') }}',
                    cities: '{{ route('admin.orders.getCities') }}',
                    test: '{{ url('/admin/process/test') }}'
                },
                retryCount: 0,
                maxRetries: 3
            },

            // ===== INITIALIZATION =====
            async init() {
                Debug.log('🚀 Initializing OrderManager...');

                // Test API connectivity first
                const apiWorking = await this.testAPI();
                if (!apiWorking) {
                    this.showAPIError();
                    return;
                }

                this.setupEventListeners();
                await this.loadQueueCounts();
                await this.loadQueue('standard');

                Debug.log('✅ OrderManager initialized successfully');
            },

            async testAPI() {
                try {
                    Debug.log('🧪 Testing API connectivity...');

                    const response = await fetch(this.config.routes.test, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': this.config.csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    Debug.log(`📡 Test response: ${response.status} ${response.statusText}`);

                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }

                    const data = await response.json();
                    Debug.log(`✅ API test successful: ${data.message}`);
                    return true;

                } catch (error) {
                    Debug.log(`❌ API test failed: ${error.message}`, 'error');
                    return false;
                }
            },

            showAPIError() {
                const container = document.getElementById('orderContent');
                container.innerHTML = `
            <div class="error-state">
                <div class="icon">
                    <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                </div>
                <h4>Erreur de connexion API</h4>
                <p class="text-muted mb-3">
                    Impossible de se connecter au serveur. 
                    Vérifiez que les routes sont correctement configurées.
                </p>
                <div class="mb-3">
                    <small class="text-muted">Routes testées:</small>
                    <ul class="text-start mt-2">
                        <li>Test: <code>${this.config.routes.test}</code></li>
                        <li>Counts: <code>${this.config.routes.counts}</code></li>
                        <li>Queue: <code>${this.config.routes.queue}</code></li>
                    </ul>
                </div>
                <button class="btn btn-outline-primary" onclick="location.reload()">
                    <i class="fas fa-redo"></i> Recharger la page
                </button>
            </div>
        `;
            },

            setupEventListeners() {
                Debug.log('⚙️ Setting up event listeners...');

                // Queue tab navigation
                document.querySelectorAll('.nav-link[data-queue]').forEach(tab => {
                    tab.addEventListener('click', (e) => {
                        e.preventDefault();
                        const queue = e.target.closest('a').dataset.queue;
                        this.switchQueue(queue);
                    });
                });

                // Debug toggle
                document.getElementById('toggleDebugBtn')?.addEventListener('click', () => {
                    Debug.toggle();
                });
            },

            // ===== QUEUE MANAGEMENT =====
            switchQueue(queue) {
                Debug.log(`🔄 Switching to queue: ${queue}`);

                // Update active tab
                document.querySelectorAll('.nav-link[data-queue]').forEach(tab => {
                    tab.classList.remove('active');
                });
                document.querySelector(`[data-queue="${queue}"]`).classList.add('active');

                // Update config
                this.config.currentQueue = queue;
                this.config.retryCount = 0; // Reset retry count

                // Load queue
                this.loadQueue(queue);
            },

            async loadQueueCounts() {
                try {
                    Debug.log('📊 Loading queue counts...');

                    const response = await this.makeRequest(this.config.routes.counts);

                    if (response.error) {
                        throw new Error(response.error);
                    }

                    Debug.log(
                        `✅ Queue counts loaded: standard=${response.standard}, dated=${response.dated}, old=${response.old}`
                        );

                    this.updateQueueCount('standard', response.standard || 0);
                    this.updateQueueCount('dated', response.dated || 0);
                    this.updateQueueCount('old', response.old || 0);

                } catch (error) {
                    Debug.log(`❌ Error loading queue counts: ${error.message}`, 'error');
                    this.showNotification('Erreur lors du chargement des compteurs', 'error');
                }
            },

            async loadQueue(queue) {
                Debug.log(`📥 Loading queue: ${queue}`);

                const container = document.getElementById('orderContent');

                // Show loading state
                container.innerHTML = `
            <div class="loading-indicator">
                <div class="spinner"></div>
                <p class="mt-3">Chargement de la file ${queue}...</p>
            </div>
        `;

                try {
                    const url = `${this.config.routes.queue}/${queue}`;
                    Debug.log(`🌐 Fetching: ${url}`);

                    const response = await this.makeRequest(url);

                    if (response.error) {
                        throw new Error(response.error);
                    }

                    Debug.log(`✅ Queue data received:`, response);

                    if (response.hasOrder && response.order) {
                        this.config.currentOrderId = response.order.id;
                        this.displayOrder(response.order);
                        this.updateOrderHeader(response.order);
                        Debug.log(`📋 Order ${response.order.id} displayed successfully`);
                    } else {
                        this.displayNoOrders(queue);
                        this.updateOrderHeader(null);
                        Debug.log(`📭 No orders in queue ${queue}`);
                    }

                } catch (error) {
                    Debug.log(`❌ Error loading queue ${queue}: ${error.message}`, 'error');
                    this.showQueueError(queue, error.message);
                }
            },

            async makeRequest(url, options = {}) {
                const defaultOptions = {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.config.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                };

                const finalOptions = {
                    ...defaultOptions,
                    ...options
                };

                Debug.log(`📡 Making request to: ${url}`, 'info');
                Debug.log(`📡 Request options:`, finalOptions);

                const response = await fetch(url, finalOptions);

                Debug.log(`📡 Response status: ${response.status} ${response.statusText}`);

                if (!response.ok) {
                    const errorText = await response.text();
                    Debug.log(`📡 Error response: ${errorText}`, 'error');
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();
                Debug.log(`📡 Response data:`, data);

                return data;
            },

            showQueueError(queue, errorMessage) {
                const container = document.getElementById('orderContent');

                const canRetry = this.config.retryCount < this.config.maxRetries;

                container.innerHTML = `
            <div class="error-state">
                <div class="icon">
                    <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                </div>
                <h4>Erreur de chargement</h4>
                <p class="text-muted mb-3">${errorMessage}</p>
                <div class="mb-3">
                    <small class="text-muted">
                        Tentative ${this.config.retryCount + 1}/${this.config.maxRetries + 1}
                    </small>
                </div>
                ${canRetry ? `
                        <button class="btn btn-outline-primary me-2" onclick="OrderManager.retryLoadQueue('${queue}')">
                            <i class="fas fa-redo"></i> Réessayer
                        </button>
                    ` : ''}
                <button class="btn btn-outline-secondary" onclick="location.reload()">
                    <i class="fas fa-refresh"></i> Recharger
                </button>
            </div>
        `;
            },

            async retryLoadQueue(queue) {
                this.config.retryCount++;
                Debug.log(`🔄 Retrying to load queue ${queue} (attempt ${this.config.retryCount})`);

                // Wait a bit before retrying
                await new Promise(resolve => setTimeout(resolve, 1000));

                await this.loadQueue(queue);
            },

            // ===== ORDER DISPLAY =====
            displayOrder(order) {
                Debug.log(`📋 Displaying order: ${order.id}`);

                const container = document.getElementById('orderContent');
                container.innerHTML = this.generateOrderHTML(order);

                // Initialize form components
                setTimeout(() => {
                    this.initializeOrderForm(order);
                }, 100);
            },

            generateOrderHTML(order) {
                return `
            <div class="order-container">
                <!-- Client Section -->
                <div class="client-section">
                    <h4 class="section-title">Informations Client</h4>
                    
                    <form id="processForm" method="POST">
                        <input type="hidden" name="_token" value="${this.config.csrfToken}">
                        <input type="hidden" name="queue" value="${this.config.currentQueue}">
                        
                        <div class="client-form">
                            <div class="form-group">
                                <label for="customer_phone" class="required">Téléphone</label>
                                <input type="tel" class="form-control" id="customer_phone" name="customer_phone" 
                                       value="${order.customer_phone || ''}" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="customer_name">Nom du client</label>
                                <input type="text" class="form-control" id="customer_name" name="customer_name" 
                                       value="${order.customer_name || ''}">
                            </div>
                            
                            <div class="form-group">
                                <label for="customer_phone_2">Téléphone 2</label>
                                <input type="tel" class="form-control" id="customer_phone_2" name="customer_phone_2" 
                                       value="${order.customer_phone_2 || ''}">
                            </div>
                            
                            <div class="form-group">
                                <label for="shipping_cost">Frais de livraison (DT)</label>
                                <input type="number" class="form-control" id="shipping_cost" name="shipping_cost" 
                                       step="0.001" value="${order.shipping_cost || 0}">
                            </div>
                            
                            <div class="form-group full-width">
                                <label for="customer_address">Adresse</label>
                                <textarea class="form-control" id="customer_address" name="customer_address" rows="2">${order.customer_address || ''}</textarea>
                            </div>
                        </div>
                        
                        <!-- Cart Summary -->
                        <div class="cart-summary">
                            <div class="cart-summary-item">
                                <span>Sous-total:</span>
                                <span id="subtotalDisplay">${this.formatPrice(order.total_price)} DT</span>
                            </div>
                            <div class="cart-summary-item">
                                <span>Livraison:</span>
                                <span id="shippingDisplay">${this.formatPrice(order.shipping_cost)} DT</span>
                            </div>
                            <div class="cart-summary-total">
                                <span><strong>Total:</strong></span>
                                <span id="totalDisplay"><strong>${this.formatPrice((order.total_price || 0) + (order.shipping_cost || 0))} DT</strong></span>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Action Section -->
                <div class="action-section">
                    <h4 class="section-title">Actions à effectuer</h4>
                    
                    <div class="form-group">
                        <label for="actionType">Action</label>
                        <select class="form-control" id="actionType" name="action" form="processForm">
                            <option value="">-- Choisir une action --</option>
                            <option value="call">Tentative d'appel</option>
                            <option value="confirm">Confirmer la commande</option>
                            <option value="cancel">Annuler la commande</option>
                            <option value="schedule">Dater la commande</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes" class="required">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" form="processForm" rows="4"
                                  placeholder="Expliquez la raison de cette action..."></textarea>
                    </div>
                    
                    <!-- Conditional sections will be inserted here -->
                    <div id="conditionalSection"></div>
                    
                    <div class="text-center mb-3">
                        <small class="text-muted">
                            <i class="fas fa-phone"></i> 
                            <strong>${order.attempts_count || 0}</strong> tentatives totales
                            | <strong>${order.daily_attempts_count || 0}</strong> aujourd'hui
                        </small>
                    </div>
                    
                    <button type="button" class="action-button" id="submitAction">
                        Enregistrer
                    </button>
                </div>
            </div>
        `;
            },

            initializeOrderForm(order) {
                Debug.log('⚙️ Initializing order form...');

                // Setup event listeners
                this.setupFormEventListeners();

                Debug.log('✅ Order form initialized');
            },

            setupFormEventListeners() {
                // Action type change
                document.getElementById('actionType')?.addEventListener('change', (e) => {
                    this.handleActionChange(e.target.value);
                });

                // Submit button
                document.getElementById('submitAction')?.addEventListener('click', () => {
                    this.submitOrder();
                });

                // Shipping cost change
                document.getElementById('shipping_cost')?.addEventListener('input', () => {
                    this.updateCartSummary();
                });
            },

            handleActionChange(action) {
                const $conditional = document.getElementById('conditionalSection');
                const $submitBtn = document.getElementById('submitAction');

                // Clear conditional section
                $conditional.innerHTML = '';

                // Reset button style
                $submitBtn.style.backgroundColor = '';
                $submitBtn.textContent = 'Enregistrer';

                Debug.log(`🎛️ Action changed to: ${action}`);

                switch (action) {
                    case 'call':
                        $submitBtn.textContent = 'Enregistrer l\'appel';
                        $submitBtn.style.backgroundColor = 'var(--info-color)';
                        break;

                    case 'confirm':
                        $submitBtn.textContent = 'Confirmer la commande';
                        $submitBtn.style.backgroundColor = 'var(--success-color)';
                        this.createConfirmSection($conditional);
                        break;

                    case 'cancel':
                        $submitBtn.textContent = 'Annuler la commande';
                        $submitBtn.style.backgroundColor = 'var(--danger-color)';
                        break;

                    case 'schedule':
                        $submitBtn.textContent = 'Programmer la commande';
                        $submitBtn.style.backgroundColor = 'var(--warning-color)';
                        this.createScheduleSection($conditional);
                        break;
                }
            },

            createConfirmSection(container) {
                container.innerHTML = `
            <div class="conditional-section" style="display: block;">
                <label for="confirmed_price" class="required">Prix confirmé (DT)</label>
                <input type="number" id="confirmed_price" name="confirmed_price" 
                       class="form-control" step="0.001" form="processForm" required>
                <small class="text-muted">Veuillez confirmer le prix total</small>
            </div>
        `;

                // Pre-fill with current total
                const total = this.calculateTotal();
                document.getElementById('confirmed_price').value = this.formatPrice(total);
            },

            createScheduleSection(container) {
                container.innerHTML = `
            <div class="conditional-section" style="display: block;">
                <label for="scheduled_date" class="required">Date de livraison</label>
                <input type="text" id="scheduled_date" name="scheduled_date" 
                       class="form-control flatpickr" form="processForm" required>
                <small class="text-muted">Sélectionnez la date programmée</small>
            </div>
        `;

                // Initialize Flatpickr
                flatpickr('#scheduled_date', {
                    locale: 'fr',
                    dateFormat: 'Y-m-d',
                    minDate: 'today'
                });
            },

            // ===== FORM SUBMISSION =====
            async submitOrder() {
                Debug.log('📤 Submitting order...');

                if (!this.validateForm()) {
                    return;
                }

                const $form = document.getElementById('processForm');
                const $submitBtn = document.getElementById('submitAction');
                const originalText = $submitBtn.textContent;

                try {
                    // Disable button and show loading
                    $submitBtn.disabled = true;
                    $submitBtn.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2"></span>Traitement...';

                    this.showLoading();

                    // Prepare form data
                    const formData = new FormData($form);

                    // Submit
                    const url = `${this.config.routes.action}/${this.config.currentOrderId}`;
                    const response = await this.makeRequest(url, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': this.config.csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (response.error) {
                        throw new Error(response.error);
                    }

                    Debug.log('✅ Order submitted successfully');
                    this.showNotification('Commande traitée avec succès', 'success');

                    // Reload queue after success
                    setTimeout(() => {
                        this.loadQueueCounts();
                        this.loadQueue(this.config.currentQueue);
                    }, 1000);

                } catch (error) {
                    Debug.log(`❌ Error submitting order: ${error.message}`, 'error');
                    this.showNotification(`Erreur: ${error.message}`, 'error');

                    // Re-enable button
                    $submitBtn.disabled = false;
                    $submitBtn.textContent = originalText;
                } finally {
                    this.hideLoading();
                }
            },

            validateForm() {
                const action = document.getElementById('actionType')?.value;
                const notes = document.getElementById('notes')?.value?.trim();

                if (!action) {
                    this.showNotification('Veuillez sélectionner une action', 'error');
                    document.getElementById('actionType')?.focus();
                    return false;
                }

                if (!notes) {
                    this.showNotification('Les notes sont obligatoires', 'error');
                    document.getElementById('notes')?.focus();
                    return false;
                }

                // Validate specific action requirements
                if (action === 'confirm') {
                    const confirmedPrice = document.getElementById('confirmed_price')?.value;
                    if (!confirmedPrice) {
                        this.showNotification('Le prix confirmé est obligatoire', 'error');
                        document.getElementById('confirmed_price')?.focus();
                        return false;
                    }

                    // Check required client fields for confirmation
                    const requiredFields = ['customer_name', 'customer_phone', 'customer_address'];
                    for (const field of requiredFields) {
                        const element = document.getElementById(field);
                        if (!element?.value?.trim()) {
                            this.showNotification('Tous les champs client sont obligatoires pour une confirmation',
                                'error');
                            element?.focus();
                            return false;
                        }
                    }
                } else if (action === 'schedule') {
                    const scheduledDate = document.getElementById('scheduled_date')?.value;
                    if (!scheduledDate) {
                        this.showNotification('La date programmée est obligatoire', 'error');
                        document.getElementById('scheduled_date')?.focus();
                        return false;
                    }
                }

                return true;
            },

            // ===== CART CALCULATIONS =====
            calculateTotal() {
                const shipping = parseFloat(document.getElementById('shipping_cost')?.value) || 0;
                const subtotal = parseFloat(document.getElementById('subtotalDisplay')?.textContent?.replace(' DT',
                    '')) || 0;
                return subtotal + shipping;
            },

            updateCartSummary() {
                const shipping = parseFloat(document.getElementById('shipping_cost')?.value) || 0;
                const subtotal = parseFloat(document.getElementById('subtotalDisplay')?.textContent?.replace(' DT',
                    '')) || 0;
                const total = subtotal + shipping;

                const shippingDisplay = document.getElementById('shippingDisplay');
                const totalDisplay = document.getElementById('totalDisplay');

                if (shippingDisplay) shippingDisplay.textContent = `${this.formatPrice(shipping)} DT`;
                if (totalDisplay) totalDisplay.textContent = `${this.formatPrice(total)} DT`;
            },

            // ===== UI HELPERS =====
            displayNoOrders(queue) {
                const container = document.getElementById('orderContent');
                container.innerHTML = `
            <div class="no-orders">
                <div class="icon">
                    <i class="fas fa-coffee"></i>
                </div>
                <h3>Aucune commande à traiter</h3>
                <p>Il n'y a actuellement aucune commande dans la file ${queue}.</p>
                <p>Prenez une pause café! ☕</p>
                <button class="btn btn-outline-primary" onclick="OrderManager.loadQueue('${queue}')">
                    <i class="fas fa-redo"></i> Actualiser
                </button>
            </div>
        `;
            },

            updateOrderHeader(order) {
                const orderIdDisplay = document.getElementById('orderIdDisplay');
                const queueTypeDisplay = document.getElementById('queueTypeDisplay');
                const orderStatus = document.getElementById('orderStatus');
                const orderPriority = document.getElementById('orderPriority');

                if (order) {
                    orderIdDisplay.textContent = `Commande #${order.id}`;
                    queueTypeDisplay.textContent = `- File ${this.config.currentQueue}`;

                    orderStatus.textContent = order.status || 'nouvelle';
                    orderStatus.className = `status-badge status-${order.status || 'nouvelle'}`;
                    orderStatus.style.display = 'inline-block';

                    orderPriority.textContent = order.priority || 'normale';
                    orderPriority.className = `status-badge priority-${order.priority || 'normale'}`;
                    orderPriority.style.display = 'inline-block';
                } else {
                    orderIdDisplay.textContent = 'Aucune commande sélectionnée';
                    queueTypeDisplay.textContent = '';
                    orderStatus.style.display = 'none';
                    orderPriority.style.display = 'none';
                }
            },

            updateQueueCount(queue, count) {
                const badge = document.getElementById(`${queue}Count`);
                if (!badge) return;

                badge.textContent = count;
                badge.classList.remove('bg-primary', 'bg-warning', 'bg-danger', 'bg-secondary');

                if (count > 0) {
                    if (queue === 'standard') {
                        badge.classList.add('bg-primary');
                    } else if (queue === 'dated') {
                        badge.classList.add('bg-warning');
                    } else if (queue === 'old') {
                        badge.classList.add('bg-danger');
                    }
                } else {
                    badge.classList.add('bg-secondary');
                }
            },

            // ===== NOTIFICATIONS =====
            showNotification(message, type = 'info') {
                const container = document.getElementById('notificationsContainer');

                const notification = document.createElement('div');
                notification.className = `notification ${type}`;
                notification.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                <span>${message}</span>
                <button type="button" class="btn-close btn-close-white ms-2" onclick="this.parentElement.parentElement.remove()"></button>
            </div>
        `;

                container.appendChild(notification);

                // Show with animation
                setTimeout(() => notification.classList.add('show'), 10);

                // Auto hide after 5 seconds
                setTimeout(() => {
                    if (notification.parentElement) {
                        notification.classList.remove('show');
                        setTimeout(() => notification.remove(), 300);
                    }
                }, 5000);
            },

            // ===== LOADING STATES =====
            showLoading() {
                document.getElementById('loadingOverlay').style.display = 'flex';
            },

            hideLoading() {
                document.getElementById('loadingOverlay').style.display = 'none';
            },

            // ===== UTILITIES =====
            formatPrice(price) {
                return parseFloat(price || 0).toFixed(3);
            }
        };

        // ===== INITIALIZATION =====
        document.addEventListener('DOMContentLoaded', () => {
            Debug.log('🎯 DOM loaded, starting OrderManager...');
            OrderManager.init();
        });

        // ===== DEBUGGING =====
        window.OrderManager = OrderManager;
        window.Debug = Debug;

        // ===== GLOBAL ERROR HANDLING =====
        window.addEventListener('error', (event) => {
            Debug.log(`🚨 Global error: ${event.error?.message}`, 'error');
        });

        window.addEventListener('unhandledrejection', (event) => {
            Debug.log(`🚨 Unhandled promise rejection: ${event.reason}`, 'error');
        });
    </script>
@endsection