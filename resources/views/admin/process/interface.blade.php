@extends('layouts.admin')

@section('title', 'Traitement des Commandes')

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --success-color: #1cc88a;
            --danger-color: #e74a3b;
            --warning-color: #f6c23e;
            --info-color: #36b9cc;
            --secondary-color: #858796;
            --light-bg: #f8f9fc;
            --border-color: #e3e6f0;
        }

        body {
            background-color: var(--light-bg);
            font-family: 'Nunito', sans-serif;
        }

        /* Header amélioré */
        .order-header {
            background: white;
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .queue-tabs {
            background: white;
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
        }

        .queue-tabs .nav-link.active {
            color: var(--primary-color);
            background-color: #f8f9fc;
            border-bottom: 3px solid var(--primary-color);
        }

        .queue-tabs .nav-link .badge {
            margin-left: 8px;
            font-size: 0.75rem;
        }

        /* Zone de travail principales */
        .work-area {
            max-width: 1400px;
            margin: 20px auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            min-height: calc(100vh - 160px);
        }

        .order-container {
            display: grid;
            grid-template-columns: 1fr 500px;
            gap: 20px;
            padding: 20px;
        }

        /* Informations client */
        .client-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .client-info .form-group {
            margin-bottom: 10px;
        }

        .client-info .full-width {
            grid-column: 1 / -1;
        }

        .client-info label {
            font-weight: 600;
            font-size: 0.9rem;
            color: #5a5c69;
            margin-bottom: 4px;
        }

        .client-info input,
        .client-info select,
        .client-info textarea {
            width: 100%;
            border: 1px solid #d1d3e2;
            border-radius: 4px;
            padding: 8px 12px;
            font-size: 0.9rem;
        }

        .client-info input:focus,
        .client-info select:focus,
        .client-info textarea:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.1);
        }

        .required::after {
            content: " *";
            color: var(--danger-color);
        }

        /* Résumé du panier */
        .cart-summary {
            grid-column: 1 / -1;
            margin-top: 10px;
            padding: 15px;
            background: linear-gradient(135deg, #f8f9fc 0%, #f5f7fa 100%);
            border-radius: 6px;
        }

        .cart-summary table {
            width: 100%;
            border-collapse: collapse;
        }

        .cart-summary td {
            padding: 6px 0;
            font-size: 0.9rem;
        }

        .cart-summary .total-row {
            font-weight: 700;
            font-size: 1.1rem;
            border-top: 2px solid var(--border-color);
            padding-top: 10px;
        }

        /* Zone d'action */
        .action-zone {
            background: #f8f9fc;
            padding: 20px;
            border-radius: 8px;
        }

        .action-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .action-header h3 {
            font-size: 1.1rem;
            font-weight: 700;
            color: #5a5c69;
            margin: 0;
        }

        .cart-toggle {
            background: none;
            border: 1px solid #d1d3e2;
            color: #5a5c69;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .cart-toggle:hover {
            background: #e3e6f0;
        }

        /* Section panier */
        .cart-section {
            background: white;
            border: 1px solid #e3e6f0;
            border-radius: 6px;
            margin-bottom: 20px;
            overflow: hidden;
        }

        .cart-header {
            background: #f8f9fc;
            padding: 12px 15px;
            font-weight: 600;
            font-size: 0.9rem;
            color: #5a5c69;
            border-bottom: 1px solid #e3e6f0;
        }

        .product-line {
            padding: 12px 15px;
            border-bottom: 1px solid #f0f0f0;
            display: grid;
            grid-template-columns: 2fr 100px 80px 40px;
            gap: 10px;
            align-items: center;
        }

        .product-line:last-child {
            border-bottom: none;
        }

        .product-select,
        .product-quantity {
            width: 100%;
            border: 1px solid #d1d3e2;
            border-radius: 4px;
            padding: 6px 10px;
        }

        .line-total {
            text-align: right;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .add-product-btn {
            padding: 8px 15px;
            margin: 15px;
            border: 1px dashed #d1d3e2;
            background: white;
            color: var(--primary-color);
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .add-product-btn:hover {
            background: #f8f9fc;
            border-color: var(--primary-color);
        }

        /* Actions */
        .action-selector {
            margin-bottom: 20px;
        }

        .action-selector label {
            font-weight: 600;
            font-size: 0.9rem;
            color: #5a5c69;
            margin-bottom: 6px;
            display: block;
        }

        .action-selector select {
            width: 100%;
            border: 1px solid #d1d3e2;
            border-radius: 4px;
            padding: 8px 12px;
            font-size: 0.9rem;
        }

        .notes-section {
            margin-bottom: 20px;
        }

        .notes-section textarea {
            width: 100%;
            height: 100px;
            border: 1px solid #d1d3e2;
            border-radius: 4px;
            padding: 8px 12px;
            resize: vertical;
            font-size: 0.9rem;
        }

        /* Section conditionnelle */
        .conditional-section {
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fc;
            border: 1px solid #e3e6f0;
            border-radius: 6px;
        }

        .conditional-section label {
            font-weight: 600;
            font-size: 0.9rem;
            color: #5a5c69;
            margin-bottom: 6px;
            display: block;
        }

        .conditional-section input {
            width: 100%;
            border: 1px solid #d1d3e2;
            border-radius: 4px;
            padding: 8px 12px;
            font-size: 0.9rem;
        }

        /* Historique */
        .history-section {
            margin-top: 20px;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.5s ease-out;
        }

        .history-section.show {
            max-height: 500px;
            overflow-y: auto;
        }

        .history-timeline {
            padding: 15px;
            background: #f8f9fc;
            border: 1px solid #e3e6f0;
            border-radius: 6px;
        }

        .history-item {
            padding: 10px;
            border-left: 3px solid #e3e6f0;
            margin-left: 10px;
            padding-left: 15px;
            margin-bottom: 10px;
        }

        .history-item:last-child {
            margin-bottom: 0;
        }

        .history-item.tentative {
            border-left-color: var(--info-color);
        }

        .history-item.confirmation {
            border-left-color: var(--success-color);
        }

        .history-item.annulation {
            border-left-color: var(--danger-color);
        }

        .history-item.datation {
            border-left-color: var(--warning-color);
        }

        .history-date {
            font-size: 0.8rem;
            color: #6c757d;
        }

        .history-action {
            font-weight: 600;
            color: #495057;
        }

        .history-notes {
            font-size: 0.9rem;
            color: #6c757d;
            margin-top: 5px;
        }

        /* Boutons d'action */
        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn-submit {
            flex: 1;
            padding: 10px 20px;
            background: var(--success-color);
            color: white;
            border: none;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            background: #17a673;
        }

        .btn-submit:disabled {
            background: #d1d3e2;
            cursor: not-allowed;
        }

        /* Messages */
        .message {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 20px;
            border-radius: 6px;
            color: white;
            z-index: 1000;
            opacity: 0;
            transition: all 0.3s ease;
            transform: translateY(-20px);
        }

        .message.show {
            opacity: 1;
            transform: translateY(0);
        }

        .message.success {
            background: var(--success-color);
        }

        .message.error {
            background: var(--danger-color);
        }

        /* États vides */
        .no-orders {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .no-orders .icon {
            font-size: 4rem;
            color: #d1d3e2;
            margin-bottom: 20px;
        }

        .no-orders h3 {
            font-size: 1.5rem;
            color: #5a5c69;
            margin-bottom: 10px;
        }

        /* Raccourcis */
        .shortcuts-modal {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            padding: 20px;
            z-index: 2000;
            max-width: 400px;
            display: none;
        }

        .shortcuts-modal h3 {
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 1.25rem;
        }

        .shortcuts-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .shortcuts-list li {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .shortcut-key {
            background: #f8f9fc;
            border: 1px solid #e3e6f0;
            padding: 2px 8px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 0.9rem;
        }

        /* Statuts visuels */
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

        .priority-urgente {
            background: #fff3cd;
            color: #856404;
        }

        .priority-vip {
            background: #f8d7da;
            color: #721c24;
        }

        /* Animations et transitions */
        .fade-in {
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Media queries pour la responsivité */
        @media (max-width: 1200px) {
            .order-container {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .client-info {
                grid-template-columns: 1fr;
            }

            .product-line {
                grid-template-columns: 1fr;
                gap: 8px;
            }

            .queue-tabs {
                padding: 5px 10px;
            }

            .queue-tabs .nav-link {
                padding: 6px 12px;
                margin-right: 5px;
            }
        }

        /* Animation pour les badges de compteur */
        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.2);
            }

            100% {
                transform: scale(1);
            }
        }

        .pulse {
            animation: pulse 0.8s infinite;
            color: white;
            background-color: var(--danger-color);
        }

        /* Fix pour Select2 */
        .select2-container--default .select2-selection--single {
            height: 38px;
            border: 1px solid #d1d3e2 !important;
            border-radius: 4px;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }
    </style>
@endsection

@section('content')
    <!-- Header -->
    <div class="order-header">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Traitement des Commandes</h1>
            <div class="text-muted small" id="orderIdDisplay">Commande #---</div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-outline-primary" id="toggleHistory">
                <i class="fas fa-history"></i> Historique
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary" id="showShortcuts">
                <i class="fas fa-question-circle"></i>
            </button>
            <span class="badge status-badge" id="orderStatus">---</span>
            <span class="badge status-badge" id="orderPriority">---</span>
        </div>
    </div>

    <!-- Onglets des files -->
    <div class="queue-tabs">
        <ul class="nav nav-tabs" id="queueTabs">
            <li class="nav-item">
                <a class="nav-link active" id="standard-tab" data-bs-toggle="tab" href="#standard">
                    <i class="fas fa-list me-1"></i> File Standard
                    <span class="badge bg-secondary" id="standardCount">0</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="dated-tab" data-bs-toggle="tab" href="#dated">
                    <i class="fas fa-calendar-alt me-1"></i> File Datée
                    <span class="badge bg-secondary" id="datedCount">0</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="old-tab" data-bs-toggle="tab" href="#old">
                    <i class="fas fa-history me-1"></i> File Ancienne
                    <span class="badge bg-secondary" id="oldCount">0</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Zone de travail principale -->
    <div class="work-area">
        <div class="tab-content" id="queueTabsContent">
            <div class="tab-pane fade show active" id="standard" role="tabpanel">
                <div id="standardQueue">
                    <div class="loading-indicator text-center p-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                        <p class="mt-3">Chargement de la file standard...</p>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="dated" role="tabpanel">
                <div id="datedQueue">
                    <div class="loading-indicator text-center p-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                        <p class="mt-3">Chargement de la file datée...</p>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="old" role="tabpanel">
                <div id="oldQueue">
                    <div class="loading-indicator text-center p-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                        <p class="mt-3">Chargement de la file ancienne...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal des raccourcis -->
    <div class="shortcuts-modal" id="shortcutsModal">
        <h3>Raccourcis clavier</h3>
        <ul class="shortcuts-list">
            <li>
                <span>Soumettre le formulaire</span>
                <span class="shortcut-key">Ctrl + S</span>
            </li>
            <li>
                <span>Afficher/masquer l'historique</span>
                <span class="shortcut-key">Ctrl + H</span>
            </li>
            <li>
                <span>Afficher/masquer le panier</span>
                <span class="shortcut-key">Ctrl + P</span>
            </li>
            <li>
                <span>Ajouter un produit</span>
                <span class="shortcut-key">Ctrl + A</span>
            </li>
            <li>
                <span>Fermer les modals</span>
                <span class="shortcut-key">Échap</span>
            </li>
        </ul>
        <button type="button" class="btn btn-sm btn-secondary mt-3" onclick="toggleShortcuts()">Fermer</button>
    </div>

    <!-- Modal de chargement -->
    <div id="loadingOverlay"
        style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.8); z-index: 9999; display: none; justify-content: center; align-items: center;">
        <div class="loading-spinner" style="width: 50px; height: 50px;"></div>
    </div>

    <!-- Zone de notification -->
    <div id="notificationArea"></div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js"></script>
    <script>
        // Configuration globale
        const config = {
            queues: ['standard', 'dated', 'old'],
            currentQueue: 'standard',
            currentOrderId: null,
            productCounter: 0,
            cartVisible: false,
            historyVisible: false
        };

        // Initialisation au chargement de la page
        $(document).ready(function() {
            initializeApp();
            setupEventListeners();
            loadQueueCounts();
            loadQueue('standard');
        });

        // Initialisation de l'application
        function initializeApp() {
            // Initialiser les onglets Bootstrap
            $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
                const queue = e.target.id.replace('-tab', '');
                config.currentQueue = queue;
                loadQueue(queue);
            });
        }

        // Configuration des écouteurs d'événements
        function setupEventListeners() {
            // Raccourcis clavier
            document.addEventListener('keydown', handleKeyboardShortcuts);

            // Boutons principaux
            document.getElementById('toggleHistory').addEventListener('click', toggleHistory);
            document.getElementById('showShortcuts').addEventListener('click', toggleShortcuts);

            // Fermer les modals sur Échap
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    const modal = document.getElementById('shortcutsModal');
                    if (modal.style.display === 'block') {
                        toggleShortcuts();
                    }
                }
            });
        }

        // Gestion des raccourcis clavier
        function handleKeyboardShortcuts(e) {
            if (e.ctrlKey) {
                switch (e.key.toLowerCase()) {
                    case 's':
                        e.preventDefault();
                        if (config.currentOrderId) {
                            const form = document.getElementById('orderForm');
                            if (form) {
                                form.requestSubmit();
                            }
                        }
                        break;
                    case 'h':
                        e.preventDefault();
                        toggleHistory();
                        break;
                    case 'p':
                        e.preventDefault();
                        toggleCart();
                        break;
                    case 'a':
                        e.preventDefault();
                        if (config.cartVisible) {
                            const addBtn = document.getElementById('addProductBtn');
                            if (addBtn) {
                                addBtn.click();
                            }
                        }
                        break;
                }
            }
        }

        // Charger les compteurs des files
        function loadQueueCounts() {
            $.ajax({
                url: "{{ route('admin.process.counts', [], false) }}",
                method: 'GET',
                success: function(data) {
                    updateQueueCount('standard', data.standard || 0);
                    updateQueueCount('dated', data.dated || 0);
                    updateQueueCount('old', data.old || 0);
                },
                error: function() {
                    console.error('Erreur lors du chargement des compteurs');
                }
            });
        }

        // Charger une file d'attente
        function loadQueue(queue) {
            const container = document.getElementById(`${queue}Queue`);
            container.innerHTML = `
            <div class="loading-indicator text-center p-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
                <p class="mt-3">Chargement de la file ${queue}...</p>
            </div>
        `;

            $.ajax({
                url: "{{ url('/admin/process/') }}/" + queue,
                method: 'GET',
                success: function(data) {
                    if (data.hasOrder) {
                        config.currentOrderId = data.order.id;
                        displayOrder(data.order);
                    } else {
                        displayNoOrders(queue);
                    }
                },
                error: function(xhr) {
                    let errorMsg = 'Erreur lors du chargement de la file';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }

                    container.innerHTML = `
                    <div class="alert alert-danger m-4">
                        ${errorMsg}. Veuillez réessayer.
                    </div>
                `;
                }
            });
        }

        // Afficher une commande
        function displayOrder(order) {
            const container = document.getElementById(`${config.currentQueue}Queue`);

            // Récupérer le formulaire via AJAX
            $.ajax({
                url: "{{ url('/admin/process/') }}/" + config.currentQueue + "/" + order.id + "/form",
                method: 'GET',
                success: function(html) {
                    container.innerHTML = html;

                    // Initialiser les fonctionnalités sur le formulaire nouvellement chargé
                    initializeOrderFormFunctionality(order);

                    // Mettre à jour le header
                    updateHeader(order);
                },
                error: function(xhr) {
                    let errorMsg = 'Erreur lors du chargement du formulaire';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }

                    container.innerHTML = `
                    <div class="alert alert-danger m-4">
                        ${errorMsg}. Veuillez réessayer.
                    </div>
                `;
                }
            });
        }

        // Initialiser les fonctionnalités du formulaire de commande
        function initializeOrderFormFunctionality(order) {
            // Nettoyer les Select2 existants
            $('.select2-container').remove();

            // Initialiser Select2
            $('.select2').select2({
                theme: 'default',
                width: '100%'
            });

            // Initialiser Flatpickr
            flatpickr('.flatpickr', {
                locale: 'fr',
                dateFormat: 'Y-m-d',
                minDate: 'today'
            });

            // Toggle panier
            $('#toggleCart').off('click').on('click', toggleCart);

            // Ajouter produit
            $('#addProductBtn').off('click').on('click', addProductLine);

            // Changer action
            $('#actionType').off('change').on('change', function() {
                handleActionChange(this.value);
            });

            // Event delegation pour les lignes de produit
            $(document).off('change', '.product-select, .product-quantity').on('change',
                '.product-select, .product-quantity',
                function() {
                    updateLineTotal(this);
                    updateCartSummary();
                });

            $(document).off('click', '.remove-line').on('click', '.remove-line', function() {
                $(this).closest('.product-line').remove();
                updateCartSummary();
            });

            // Gouvernorat/Ville
            $('#customer_governorate').off('change').on('change', function() {
                loadCities(this.value);
            });

            // Frais de livraison
            $('#shipping_cost').off('input').on('input', updateCartSummary);

            // Soumission du formulaire
            $('#orderForm').off('submit').on('submit', function(e) {
                e.preventDefault();

                if (!validateForm()) {
                    return;
                }

                // Désactiver le bouton de soumission
                const submitBtn = document.getElementById('submitBtn');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="loading-spinner"></span> Traitement...';
                }

                // Afficher le loader global
                showLoading();

                // Collecter les données du formulaire
                const formData = new FormData(this);

                // Ajouter l'identifiant de la file
                formData.append('queue', config.currentQueue);

                $.ajax({
                    url: this.action,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        hideLoading();
                        showNotification('Commande traitée avec succès', 'success');

                        // Mettre à jour les compteurs
                        loadQueueCounts();

                        // Charger la prochaine commande après un délai
                        setTimeout(function() {
                            loadQueue(config.currentQueue);
                        }, 1000);
                    },
                    error: function(xhr) {
                        hideLoading();

                        let errorMsg = 'Erreur lors du traitement de la commande';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }

                        showNotification(errorMsg, 'error');

                        // Réactiver le bouton
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            const submitText = document.getElementById('submitText');
                            submitBtn.innerHTML = submitText ? submitText.textContent : 'Enregistrer';
                        }
                    }
                });
            });
        }

        // Basculer l'affichage du panier
        function toggleCart() {
            const cartSection = document.getElementById('cartSection');
            const toggleBtn = document.getElementById('toggleCart');

            if (!cartSection || !toggleBtn) return;

            config.cartVisible = !config.cartVisible;

            if (config.cartVisible) {
                cartSection.style.display = 'block';
                toggleBtn.innerHTML = '<i class="fas fa-eye-slash me-1"></i> Masquer le panier';
            } else {
                cartSection.style.display = 'none';
                toggleBtn.innerHTML = '<i class="fas fa-eye me-1"></i> Voir le panier';
            }
        }

        // Basculer l'affichage de l'historique
        function toggleHistory() {
            const historySection = document.getElementById('historySection');

            if (!historySection) return;

            config.historyVisible = !config.historyVisible;

            if (config.historyVisible) {
                historySection.classList.add('show');
                if (!historySection.dataset.loaded) {
                    loadOrderHistory();
                }
            } else {
                historySection.classList.remove('show');
            }
        }

        // Charger l'historique de la commande
        function loadOrderHistory() {
            if (!config.currentOrderId) return;

            const timeline = document.getElementById('historyTimeline');
            if (!timeline) return;

            timeline.innerHTML =
                '<div class="text-center p-3"><i class="fas fa-spinner fa-spin me-2"></i> Chargement...</div>';

            $.ajax({
                url: "{{ url('/admin/orders/') }}/" + config.currentOrderId + "/history",
                success: function(html) {
                    timeline.innerHTML = html;
                    document.getElementById('historySection').dataset.loaded = 'true';
                },
                error: function() {
                    timeline.innerHTML =
                        '<div class="text-center text-danger p-3">Erreur lors du chargement de l\'historique</div>';
                }
            });
        }

        // Gestion du changement d'action
        function handleActionChange(action) {
            const conditionalSection = document.getElementById('conditionalSection');
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');

            if (!conditionalSection || !submitBtn || !submitText) return;

            // Effacer le contenu conditionnel
            conditionalSection.innerHTML = '';
            conditionalSection.style.display = 'none';

            // Réinitialiser le bouton
            submitBtn.className = 'btn-submit';
            submitText.textContent = 'Enregistrer';

            switch (action) {
                case 'call':
                    submitText.textContent = 'Enregistrer l\'appel';
                    submitBtn.style.backgroundColor = 'var(--info-color)';
                    break;

                case 'confirm':
                    createConfirmSection(conditionalSection);
                    submitText.textContent = 'Confirmer la commande';
                    submitBtn.style.backgroundColor = 'var(--success-color)';
                    break;

                case 'cancel':
                    submitText.textContent = 'Annuler la commande';
                    submitBtn.style.backgroundColor = 'var(--danger-color)';
                    break;

                case 'schedule':
                    createScheduleSection(conditionalSection);
                    submitText.textContent = 'Programmer la commande';
                    submitBtn.style.backgroundColor = 'var(--warning-color)';
                    break;
            }
        }

        // Créer la section de confirmation
        function createConfirmSection(container) {
            container.innerHTML = `
            <div class="conditional-section">
                <label for="confirmed_price" class="required">Prix confirmé (DT)</label>
                <input type="number" id="confirmed_price" name="confirmed_price" class="form-control" step="0.001" required>
                <small class="text-muted">Veuillez confirmer le prix total de la commande</small>
            </div>
        `;
            container.style.display = 'block';

            // Pré-remplir avec le total actuel
            const total = calculateTotal();
            container.querySelector('#confirmed_price').value = total.toFixed(3);
        }

        // Créer la section de programmation
        function createScheduleSection(container) {
            container.innerHTML = `
            <div class="conditional-section">
                <label for="scheduled_date" class="required">Date de livraison</label>
                <input type="text" id="scheduled_date" name="scheduled_date" class="form-control flatpickr" required>
                <small class="text-muted">Sélectionnez la date de livraison programmée</small>
            </div>
        `;
            container.style.display = 'block';

            // Initialiser le sélecteur de date
            flatpickr(container.querySelector('.flatpickr'), {
                locale: 'fr',
                dateFormat: 'Y-m-d',
                minDate: 'today'
            });
        }

        // Ajouter une ligne de produit
        function addProductLine() {
            const productLines = document.getElementById('productLines');
            if (!productLines) return;

            const lineNumber = config.productCounter++;
            const lineHtml = `
            <div class="product-line" data-line="${lineNumber}">
                <select class="form-control product-select" data-line="${lineNumber}" name="products[${lineNumber}][id]" required>
                    <option value="">Sélectionner un produit...</option>
                    <option value="new">➕ Nouveau produit</option>
                </select>
                <input type="number" class="form-control product-quantity" data-line="${lineNumber}" name="products[${lineNumber}][quantity]" value="1" min="1" required>
                <div class="line-total" data-line="${lineNumber}">0.000 DT</div>
                <button type="button" class="btn btn-link text-danger remove-line p-0">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;

            $(productLines).append(lineHtml);

            // Initialiser Select2
            $(`.product-select[data-line="${lineNumber}"]`).select2({
                theme: 'default',
                width: '100%'
            });

            // Charger les produits
            loadProductsForSelect(lineNumber);
        }

        // Charger les produits pour un select
        function loadProductsForSelect(lineNumber) {
            $.ajax({
                url: "{{ url('/admin/orders/search-products') }}",
                method: 'GET',
                success: function(products) {
                    const select = $(`.product-select[data-line="${lineNumber}"]`);

                    products.forEach(function(product) {
                        const option = new Option(
                            `${product.name} - ${formatPrice(product.price)} DT`,
                            product.id,
                            false,
                            false
                        );
                        $(option).data('price', product.price);
                        select.append(option);
                    });

                    // Repositionner l'option "Nouveau produit" à la fin
                    const newOption = select.find('option[value="new"]');
                    if (newOption.length) {
                        select.append(newOption.detach());
                    }
                }
            });
        }

        // Mise à jour des totaux de ligne
        function updateLineTotal(element) {
            const line = $(element).data('line');
            const select = $(`.product-select[data-line="${line}"]`);
            const quantity = parseInt($(`.product-quantity[data-line="${line}"]`).val()) || 1;
            const totalElement = $(`.line-total[data-line="${line}"]`);

            let price = 0;

            if (select.val() && select.val() !== 'new') {
                const selectedOption = select.find('option:selected');
                price = parseFloat(selectedOption.data('price')) || 0;
            }

            const total = price * quantity;
            totalElement.text(formatPrice(total) + ' DT');
        }

        // Mise à jour du résumé du panier
        function updateCartSummary() {
            let subtotal = 0;

            $('.product-line').each(function() {
                const line = $(this).data('line');
                const select = $(`.product-select[data-line="${line}"]`);
                const quantity = parseInt($(`.product-quantity[data-line="${line}"]`).val()) || 1;

                let price = 0;
                if (select.val() && select.val() !== 'new') {
                    const selectedOption = select.find('option:selected');
                    price = parseFloat(selectedOption.data('price')) || 0;
                }

                subtotal += price * quantity;
            });

            const shipping = parseFloat($('#shipping_cost').val()) || 0;
            const total = subtotal + shipping;

            $('#subtotal').text(formatPrice(subtotal) + ' DT');
            $('#shippingDisplay').text(formatPrice(shipping) + ' DT');
            $('#totalAmount').text(formatPrice(total) + ' DT');
        }

        // Calculer le total
        function calculateTotal() {
            let subtotal = 0;

            $('.product-line').each(function() {
                const line = $(this).data('line');
                const select = $(`.product-select[data-line="${line}"]`);
                const quantity = parseInt($(`.product-quantity[data-line="${line}"]`).val()) || 1;

                let price = 0;
                if (select.val() && select.val() !== 'new') {
                    const selectedOption = select.find('option:selected');
                    price = parseFloat(selectedOption.data('price')) || 0;
                }

                subtotal += price * quantity;
            });

            const shipping = parseFloat($('#shipping_cost').val()) || 0;
            return subtotal + shipping;
        }

        // Afficher l'écran d'absence de commandes
        function displayNoOrders(queue) {
            const container = document.getElementById(`${queue}Queue`);
            container.innerHTML = `
            <div class="no-orders">
                <div class="icon">
                    <i class="fas fa-mug-hot"></i>
                </div>
                <h3>Aucune commande à traiter</h3>
                <p>Il n'y a actuellement aucune commande à traiter dans cette file. Prenez une pause café! 😊</p>
            </div>
        `;

            // Réinitialiser le header
            updateHeader(null);
        }

        // Mettre à jour le header
        function updateHeader(order) {
            const idDisplay = document.getElementById('orderIdDisplay');
            const statusBadge = document.getElementById('orderStatus');
            const priorityBadge = document.getElementById('orderPriority');

            if (!idDisplay || !statusBadge || !priorityBadge) return;

            if (order) {
                idDisplay.textContent = `Commande #${order.id}`;

                statusBadge.textContent = order.status;
                statusBadge.className = `badge status-badge status-${order.status}`;

                priorityBadge.textContent = order.priority;
                priorityBadge.className = `badge status-badge priority-${order.priority}`;
            } else {
                idDisplay.textContent = 'Commande #---';

                statusBadge.textContent = '---';
                statusBadge.className = 'badge status-badge';

                priorityBadge.textContent = '---';
                priorityBadge.className = 'badge status-badge';
            }
        }

        // Mettre à jour le compteur d'une file
        function updateQueueCount(queue, count) {
            const badge = document.getElementById(`${queue}Count`);
            if (!badge) return;

            badge.textContent = count;

            if (count > 0) {
                badge.classList.remove('bg-secondary');
                if (queue === 'standard') {
                    badge.classList.add('bg-primary');
                } else if (queue === 'dated') {
                    badge.classList.add('bg-warning');
                } else if (queue === 'old') {
                    badge.classList.add('bg-danger');
                }

                if (count > 5) {
                    badge.classList.add('pulse');
                } else {
                    badge.classList.remove('pulse');
                }
            } else {
                badge.classList.remove('bg-primary', 'bg-warning', 'bg-danger', 'pulse');
                badge.classList.add('bg-secondary');
            }
        }

        // Charger les villes
        function loadCities(regionId, selectedCity = null) {
            if (!regionId) return;

            const citySelect = $('#customer_city');
            citySelect.empty().append('<option value="">Chargement...</option>').prop('disabled', true);

            $.ajax({
                url: "{{ url('/admin/get-cities') }}",
                data: {
                    region_id: regionId
                },
                success: function(cities) {
                    citySelect.empty().append('<option value="">Sélectionner une ville...</option>');

                    cities.forEach(function(city) {
                        const selected = selectedCity && city.id == selectedCity;
                        const option = new Option(city.name, city.id, selected, selected);
                        $(option).data('shipping_cost', city.shipping_cost || 0);
                        citySelect.append(option);
                    });

                    citySelect.prop('disabled', false).trigger('change');

                    // Si la ville a un frais de livraison, mettre à jour
                    const selectedOption = citySelect.find('option:selected');
                    if (selectedOption.length > 0) {
                        const shippingCost = $(selectedOption).data('shipping_cost');
                        if (shippingCost) {
                            $('#shipping_cost').val(shippingCost);
                            updateCartSummary();
                        }
                    }
                },
                error: function() {
                    citySelect.empty().append('<option value="">Erreur lors du chargement</option>');
                    showNotification('Erreur lors du chargement des villes', 'error');
                }
            });
        }

        // Validation du formulaire
        function validateForm() {
            const form = document.getElementById('orderForm');
            if (!form) return false;

            const action = document.getElementById('actionType')?.value;
            const notes = document.getElementById('notes')?.value;

            // L'action est obligatoire
            if (!action) {
                showNotification('Veuillez sélectionner une action', 'error');
                document.getElementById('actionType')?.focus();
                return false;
            }

            // Les notes sont obligatoires
            if (!notes?.trim()) {
                showNotification('Les notes sont obligatoires', 'error');
                document.getElementById('notes')?.focus();
                return false;
            }

            // Validation spécifique selon l'action
            if (action === 'confirm') {
                // Le prix confirmé est obligatoire
                const confirmedPrice = document.getElementById('confirmed_price');
                if (!confirmedPrice || !confirmedPrice.value) {
                    showNotification('Le prix confirmé est obligatoire', 'error');
                    confirmedPrice?.focus();
                    return false;
                }

                // Les informations client sont obligatoires
                const requiredFields = ['customer_name', 'customer_phone', 'customer_governorate', 'customer_city',
                    'customer_address'
                ];
                for (const field of requiredFields) {
                    const element = document.getElementById(field);
                    if (!element || !element.value) {
                        showNotification('Tous les champs client sont obligatoires pour une confirmation', 'error');
                        element?.focus();
                        return false;
                    }
                }
            } else if (action === 'schedule') {
                // La date programmée est obligatoire
                const scheduledDate = document.getElementById('scheduled_date');
                if (!scheduledDate || !scheduledDate.value) {
                    showNotification('La date programmée est obligatoire', 'error');
                    scheduledDate?.focus();
                    return false;
                }
            }

            // Au moins un produit est requis
            const productLines = document.querySelectorAll('.product-line');
            if (productLines.length === 0) {
                showNotification('La commande doit contenir au moins un produit', 'error');
                return false;
            }

            // Tous les produits doivent être sélectionnés
            for (const line of productLines) {
                const select = line.querySelector('.product-select');
                if (!select?.value || select.value === 'new') {
                    showNotification('Veuillez sélectionner tous les produits', 'error');
                    select?.focus();
                    return false;
                }
            }

            return true;
        }

        // Afficher/masquer le modal des raccourcis
        function toggleShortcuts() {
            const modal = document.getElementById('shortcutsModal');
            if (!modal) return;

            if (modal.style.display === 'block') {
                modal.style.display = 'none';
            } else {
                modal.style.display = 'block';
            }
        }

        // Afficher une notification
        function showNotification(message, type = 'info') {
            const area = document.getElementById('notificationArea');
            if (!area) return;

            const notification = document.createElement('div');
            notification.className = `message ${type}`;
            notification.textContent = message;

            area.appendChild(notification);

            // Afficher avec animation
            setTimeout(() => notification.classList.add('show'), 10);

            // Masquer après 3 secondes
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        // Gérer l'affichage du chargement
        function showLoading() {
            const overlay = document.getElementById('loadingOverlay');
            if (overlay) {
                overlay.style.display = 'flex';
            }
        }

        function hideLoading() {
            const overlay = document.getElementById('loadingOverlay');
            if (overlay) {
                overlay.style.display = 'none';
            }
        }

        // Utilitaires
        function formatPrice(price) {
            return parseFloat(price || 0).toFixed(3);
        }
    </script>
@endsection
