@extends('layouts.admin')

@section('title', 'Traitement des commandes')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    /* Styles copiés de edit.blade.php */
    .history-section {
        display: none;
        max-height: 600px;
        overflow-y: auto;
        margin-top: 20px;
        padding: 15px;
        background-color: #fff;
        border: 1px solid #e3e6f0;
        border-radius: 0.35rem;
        box-shadow: 0 0.15rem 1rem 0 rgba(58, 59, 69, 0.15);
    }

    .history-section.show {
        display: block;
        animation: fadeIn 0.3s;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .timeline {
        margin: 20px 0;
        padding: 0;
    }

    .timeline-container {
        position: relative;
        padding-left: 20px;
    }

    .timeline-container::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3px;
        background-color: #e0e0e0;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 20px;
        padding-left: 25px;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: -10px;
        top: 0;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background-color: #4e73df;
        border: 3px solid white;
        box-shadow: 0 0 0 1px #4e73df;
    }

    .timeline-item-content {
        position: relative;
        padding: 15px;
        border-radius: 5px;
        background-color: #f8f9fc;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .timeline-item-date {
        display: block;
        font-size: 0.8rem;
        color: #6c757d;
        margin-bottom: 5px;
    }

    .timeline-item-title {
        margin-bottom: 10px;
        font-weight: 600;
    }

    .timeline-item-user {
        font-size: 0.9rem;
        margin-bottom: 5px;
    }

    .timeline-item-status {
        font-size: 0.9rem;
        margin-bottom: 5px;
    }

    .timeline-item-notes {
        margin-top: 10px;
    }

    .timeline-item-notes p {
        margin: 5px 0 0;
        font-size: 0.9rem;
        padding: 8px;
        background-color: #fff;
        border-radius: 3px;
        border-left: 3px solid #4e73df;
    }

    .timeline-item-changes {
        margin-top: 10px;
        font-size: 0.9rem;
    }

    .timeline-item-changes ul {
        margin: 5px 0 0;
        padding-left: 20px;
    }

    .card {
        margin-bottom: 15px;
    }
    
    .form-group {
        margin-bottom: 10px;
    }
    
    .required-field::after {
        content: "*";
        color: red;
        margin-left: 4px;
    }
    
    /* Styles Select2 */
    .select2-container {
        width: 100% !important;
    }
    
    .select2-selection__rendered {
        line-height: 36px !important;
    }
    
    .select2-selection {
        height: 38px !important;
        border: 1px solid #d1d3e2 !important;
    }
    
    /* Ligne de produit */
    .product-line {
        border: 1px solid #e3e6f0;
        border-radius: 0.35rem;
        padding: 10px;
        margin-bottom: 10px;
        background-color: #f8f9fc;
        position: relative;
    }
    
    .product-line:hover {
        border-color: #d1d3e2;
        box-shadow: 0 0.15rem 0.5rem 0 rgba(58, 59, 69, 0.15);
    }
    
    .remove-line {
        position: absolute;
        top: 5px;
        right: 5px;
        color: #e74a3b;
        font-size: 1.2rem;
        cursor: pointer;
    }
    
    /* Status selector */
    .status-selectors {
        display: flex;
        gap: 15px;
    }
    
    .status-selector {
        display: flex;
        align-items: center;
    }
    
    .status-selector .status-label {
        margin-right: 5px;
        font-weight: 500;
    }
    
    /* Boutons */
    .btn-add-line {
        margin-bottom: 15px;
    }
    
    /* Résumé du panier */
    .cart-summary {
        background-color: #f8f9fc;
        border-radius: 0.5rem;
        padding: 10px;
        margin-top: 10px;
    }
    
    /* Badges */
    .status-badge {
        font-size: 0.85rem;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
    }
    
    .status-nouvelle { background-color: #3498db; color: white; }
    .status-confirmée { background-color: #2ecc71; color: white; }
    .status-annulée { background-color: #e74c3c; color: white; }
    .status-datée { background-color: #f39c12; color: white; }
    .status-en_route { background-color: #9b59b6; color: white; }
    .status-livrée { background-color: #27ae60; color: white; }
    
    .priority-normale { background-color: #95a5a6; color: white; }
    .priority-urgente { background-color: #e67e22; color: white; }
    .priority-vip { background-color: #c0392b; color: white; }
    
    /* Loader */
    .page-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.7);
        z-index: 9999;
        display: flex;
        justify-content: center;
        align-items: center;
        display: none;
    }
    
    /* Prix confirmé */
    .confirmed-price-section {
        background-color: #f0fff4;
        border: 1px solid #c6f6d5;
        border-radius: 0.35rem;
        padding: 10px;
        margin-top: 10px;
    }
    
    /* Section datée */
    .scheduled-date-section {
        background-color: #fef5e9;
        border: 1px solid #fbd38d;
        border-radius: 0.35rem;
        padding: 10px;
        margin-top: 10px;
        display: none;
    }
    
    /* Actions section */
    .actions-section {
        background-color: #f8f9fc;
        border: 1px solid #e3e6f0;
        border-radius: 0.35rem;
        padding: 15px;
        margin-bottom: 15px;
    }
    
    .action-label {
        font-weight: 600;
        margin-bottom: 8px;
    }
    
    /* Onglets */
    .nav-tabs {
        border-bottom: 2px solid #dee2e6;
        margin-bottom: 20px;
    }
    
    .nav-tabs .nav-link {
        border: none;
        color: #5a5c69;
        font-weight: 500;
        padding: 10px 20px;
    }
    
    .nav-tabs .nav-link.active {
        color: #4e73df;
        border-bottom: 2px solid #4e73df;
        background-color: transparent;
    }
    
    /* Loading indicator */
    .loading-indicator {
        text-align: center;
        padding: 50px;
    }
    
    /* No orders message */
    .no-orders-message {
        text-align: center;
        padding: 50px 20px;
    }
    
    .no-orders-message .icon {
        font-size: 80px;
        color: #d1d3e2;
        margin-bottom: 20px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Loader -->
    <div class="page-overlay" id="pageLoader">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Chargement...</span>
        </div>
    </div>

    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Traitement des commandes</h1>
        <div>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>
    
    <!-- Onglets de navigation -->
    <ul class="nav nav-tabs" id="queueTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link active" id="standard-tab" data-bs-toggle="tab" href="#standard" role="tab" aria-controls="standard" aria-selected="true">
                <i class="fas fa-list mr-1"></i> File Standard
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="dated-tab" data-bs-toggle="tab" href="#dated" role="tab" aria-controls="dated" aria-selected="false">
                <i class="fas fa-calendar-alt mr-1"></i> File Datée
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="old-tab" data-bs-toggle="tab" href="#old" role="tab" aria-controls="old" aria-selected="false">
                <i class="fas fa-history mr-1"></i> File Ancienne
            </a>
        </li>
    </ul>
    
    <!-- Contenu des onglets -->
    <div class="tab-content" id="queueTabsContent">
        <!-- File Standard -->
        <div class="tab-pane fade show active" id="standard" role="tabpanel" aria-labelledby="standard-tab">
            <div id="standardQueue">
                <div class="loading-indicator">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p class="mt-2">Chargement de la file standard...</p>
                </div>
            </div>
        </div>
        
        <!-- File Datée -->
        <div class="tab-pane fade" id="dated" role="tabpanel" aria-labelledby="dated-tab">
            <div id="datedQueue">
                <div class="loading-indicator">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p class="mt-2">Chargement de la file datée...</p>
                </div>
            </div>
        </div>
        
        <!-- File Ancienne -->
        <div class="tab-pane fade" id="old" role="tabpanel" aria-labelledby="old-tab">
            <div id="oldQueue">
                <div class="loading-indicator">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p class="mt-2">Chargement de la file ancienne...</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js"></script>
<script>
$(document).ready(function() {
    // Fonction pour montrer/cacher le loader
    function showLoader() {
        $('#pageLoader').fadeIn(100);
    }
    
    function hideLoader() {
        $('#pageLoader').fadeOut(100);
    }
    
    // Variables globales
    let currentQueue = 'standard';
    let lineCounter = 0;
    
    // Charger la première file au démarrage
    loadQueue('standard');
    
    // Gérer le changement d'onglet
    $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        currentQueue = e.target.id.replace('-tab', '');
        loadQueue(currentQueue);
    });
    
    // Fonction pour charger une file d'attente
    function loadQueue(queue) {
        const container = $('#' + queue + 'Queue');
        
        container.html('<div class="loading-indicator"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Chargement...</p></div>');
        
        $.ajax({
            url: '/admin/process/' + queue,
            method: 'GET',
            success: function(response) {
                if (response.hasOrder) {
                    // Charger le formulaire d'édition
                    loadOrderForm(response.order, queue);
                } else {
                    // Afficher le message "aucune commande"
                    container.html(`
                        <div class="no-orders-message">
                            <div class="icon">
                                <i class="fas fa-mug-hot"></i>
                            </div>
                            <h3>Aucune commande à traiter</h3>
                            <p>Il n'y a actuellement aucune commande à traiter dans cette file. Prenez une pause café!</p>
                        </div>
                    `);
                }
            },
            error: function() {
                container.html('<div class="alert alert-danger">Erreur lors du chargement de la file d\'attente.</div>');
            }
        });
    }
    
    // Fonction pour charger le formulaire d'une commande
    function loadOrderForm(order, queue) {
        showLoader();
        
        $.ajax({
            url: '/admin/process/' + queue + '/' + order.id + '/form',
            method: 'GET',
            success: function(html) {
                const container = $('#' + queue + 'Queue');
                
                // MODIFICATION 1: Nettoyer complètement le conteneur avant le chargement
                container.empty();
                container.html(html);
                
                // MODIFICATION 2: Attendre que le DOM soit mis à jour avant d'initialiser
                setTimeout(function() {
                    // MODIFICATION 3: Nettoyer les handlers globaux avant d'initialiser
                    cleanupGlobalHandlers();
                    
                    // Initialiser les composants dans le formulaire
                    initializeFormComponents(order.id);
                    
                    hideLoader();
                }, 50);
            },
            error: function() {
                $('#' + queue + 'Queue').html('<div class="alert alert-danger">Erreur lors du chargement du formulaire.</div>');
                hideLoader();
            }
        });
    }
    
    // MODIFICATION 4: Nouvelle fonction pour nettoyer les handlers globaux
    function cleanupGlobalHandlers() {
        // Nettoyer les handlers de produits
        $(document).off('click', '.btn-add-line');
        $(document).off('click', '.remove-line');
        $(document).off('change', '.product-select');
        $(document).off('change', '.product-quantity');
        
        // Nettoyer les handlers du formulaire
        $(document).off('submit', '#orderForm');
        
        // Nettoyer les Select2 existants
        $('.select2').each(function() {
            if ($(this).hasClass('select2-hidden-accessible')) {
                $(this).select2('destroy');
            }
        });
    }
    
    // Fonction pour initialiser les composants du formulaire
    function initializeFormComponents(orderId) {
        // Initialiser Select2
        $('.select2').select2({
            placeholder: "Sélectionner une option",
            allowClear: true
        });
        
        // Initialiser Flatpickr
        flatpickr(".flatpickr", {
            locale: "fr",
            dateFormat: "Y-m-d",
            minDate: "today",
            disableMobile: true
        });
        
        // Gérer le changement de statut
        $('#status').on('change', function() {
            const status = $(this).val();
            
            if (status === 'confirmée') {
                $('#confirmed-price-section').slideDown();
            } else {
                $('#confirmed-price-section').slideUp();
            }
            
            if (status === 'datée') {
                $('#scheduled-date-section').slideDown();
            } else {
                $('#scheduled-date-section').slideUp();
            }
        });
        
        // Gérer le changement d'action
        $('#action_type').on('change', function() {
            const action = $(this).val();
            let helpText = '';
            
            switch(action) {
                case 'call':
                    helpText = 'Veuillez indiquer le résultat de l\'appel.';
                    $('#increment_attempts').val(1);
                    break;
                case 'confirm':
                    helpText = 'Ajoutez des informations complémentaires sur la confirmation.';
                    $('#status').val('confirmée').trigger('change');
                    break;
                case 'cancel':
                    helpText = 'Veuillez indiquer la raison de l\'annulation.';
                    $('#status').val('annulée').trigger('change');
                    break;
                case 'schedule':
                    helpText = 'Ajoutez des informations sur la livraison programmée.';
                    $('#status').val('datée').trigger('change');
                    break;
                default:
                    helpText = 'Veuillez expliquer la raison de cette action.';
                    $('#increment_attempts').val(0);
            }
            
            $('#notes').siblings('small').text(helpText);
        });
        
        // Gérer le changement de gouvernorat
        $('#customer_governorate').on('change', function() {
            const regionId = $(this).val();
            loadCities(regionId);
        });
        
        // Charger les villes pour le gouvernorat sélectionné initialement
        if ($('#customer_governorate').val()) {
            loadCities($('#customer_governorate').val(), $('#customer_city').data('selected'));
        }
        
        // Gérer les lignes de produits
        initializeProductLines();
        
        // Gérer l'historique
        $('#toggleHistoryBtn').off('click').on('click', function() {
            $('#historySection').toggleClass('show');
            if ($('#historySection').hasClass('show')) {
                $('html, body').animate({
                    scrollTop: $('#historySection').offset().top - 20
                }, 500);
            }
        });
        
        $('#closeHistoryBtn').off('click').on('click', function() {
            $('#historySection').removeClass('show');
        });
        
        // Soumettre le formulaire
        $('#orderForm').on('submit', function(e) {
            e.preventDefault();
            
            if (validateForm()) {
                showLoader();
                
                // Pour l'action "call", envoyer une requête séparée
                if ($('#action_type').val() === 'call') {
                    $.ajax({
                        url: '/admin/orders/' + orderId + '/record-attempt',
                        method: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            notes: $('#notes').val()
                        },
                        success: function() {
                            hideLoader();
                            alert('Tentative d\'appel enregistrée avec succès.');
                            loadQueue(currentQueue);
                        },
                        error: function() {
                            hideLoader();
                            alert('Erreur lors de l\'enregistrement de la tentative.');
                        }
                    });
                } else {
                    // Soumettre le formulaire normal
                    this.submit();
                }
            }
        });
    }
    
    // Fonction pour charger les villes
    function loadCities(regionId, selectedCityId = null) {
        const citySelect = $('#customer_city');
        citySelect.prop('disabled', true).empty().append('<option value="">Chargement...</option>');
        
        $.ajax({
            url: '/admin/get-cities',
            data: { region_id: regionId },
            success: function(cities) {
                citySelect.empty().append('<option value="">Sélectionner une ville</option>');
                
                cities.forEach(function(city) {
                    const selected = selectedCityId && city.id == selectedCityId;
                    citySelect.append(new Option(city.name, city.id, selected, selected));
                });
                
                citySelect.prop('disabled', false).trigger('change');
                updateCartSummary();
            },
            error: function() {
                alert('Erreur lors du chargement des villes');
            }
        });
    }
    
    // Fonction pour initialiser les lignes de produits
    function initializeProductLines() {
        // MODIFICATION 5: Utiliser des handlers délégués avec des sélecteurs spécifiques
        $('#product-lines').off('click', '.btn-add-line').on('click', '.btn-add-line', function() {
            addProductLine();
        });
        
        $('#product-lines').off('click', '.remove-line').on('click', '.remove-line', function() {
            const lineIndex = $(this).data('line');
            $(`#product-line-${lineIndex}`).remove();
            updateCartSummary();
        });
        
        $('#product-lines').off('change', '.product-select').on('change', '.product-select', function() {
            const lineIndex = $(this).data('line');
            const selectedOption = $(this).find('option:selected');
            
            if (selectedOption.val() === 'new') {
                $('#current-line-index').val(lineIndex);
                $('#new_product_name').val('');
                $('#new_product_price').val('');
                $('#newProductModal').modal('show');
                $(this).val('').trigger('change');
            } else if (selectedOption.val()) {
                updateLineTotal(lineIndex);
            }
        });
        
        $('#product-lines').off('change', '.product-quantity').on('change', '.product-quantity', function() {
            const lineIndex = $(this).data('line');
            updateLineTotal(lineIndex);
        });
        
        // Initialiser Select2 pour les produits existants
        $('.product-select').select2({
            placeholder: "Sélectionner un produit",
            allowClear: true
        });
        
        // Enregistrer un nouveau produit
        $('#saveNewProduct').off('click').on('click', function() {
            const name = $('#new_product_name').val();
            const price = $('#new_product_price').val();
            const lineIndex = $('#current-line-index').val();
            
            if (name && price) {
                const newOption = new Option(`${name} - ${formatPrice(price)} DT [Nouveau]`, `new:${name}:${price}`, true, true);
                $(newOption).data('price', parseFloat(price));
                
                $(`#product-line-${lineIndex}`).append(`
                    <input type="hidden" name="products[${lineIndex}][is_new]" value="1">
                    <input type="hidden" name="products[${lineIndex}][name]" value="${name}">
                    <input type="hidden" name="products[${lineIndex}][price]" value="${price}">
                `);
                
                $(`#product-select-${lineIndex}`).append(newOption).trigger('change');
                
                const quantity = $(`#product-quantity-${lineIndex}`).val() || 1;
                const total = parseFloat(price) * quantity;
                
                $(`#line-total-${lineIndex}`).text(formatPrice(total) + ' DT');
                
                updateCartSummary();
                $('#newProductModal').modal('hide');
            } else {
                alert('Veuillez remplir tous les champs obligatoires');
            }
        });
        
        // Compter les lignes existantes
        lineCounter = $('.product-line').length;
        
        // Mettre à jour le résumé initial
        updateCartSummary();
    }
    
    // Fonction pour ajouter une ligne de produit
    function addProductLine() {
        const newLineHtml = `
            <div class="product-line" id="product-line-${lineCounter}">
                <span class="remove-line" data-line="${lineCounter}">❌</span>
                <div class="row">
                    <div class="col-md-7">
                        <div class="form-group">
                            <label for="product-select-${lineCounter}">Produit <span class="text-danger">*</span></label>
                            <select class="form-control product-select" id="product-select-${lineCounter}" name="products[${lineCounter}][id]" data-line="${lineCounter}" required>
                                <option value="">Sélectionner un produit</option>
                                <option value="new">➕ Ajouter un nouveau produit</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="product-quantity-${lineCounter}">Quantité <span class="text-danger">*</span></label>
                            <input type="number" class="form-control product-quantity" id="product-quantity-${lineCounter}" name="products[${lineCounter}][quantity]" value="1" min="1" data-line="${lineCounter}" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="text-right mb-1">
                            <span class="line-total" id="line-total-${lineCounter}">0.000 DT</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $('#product-lines').append(newLineHtml);
        
        // Charger les produits pour la nouvelle ligne
        loadProductsForSelect(`#product-select-${lineCounter}`);
        
        // Initialiser Select2 pour la nouvelle ligne
        $(`#product-select-${lineCounter}`).select2({
            placeholder: "Sélectionner un produit",
            allowClear: true
        });
        
        lineCounter++;
        updateCartSummary();
    }
    
    // Fonction pour charger les produits dans un select
    function loadProductsForSelect(selector) {
        $.ajax({
            url: '/admin/orders/search-products',
            method: 'GET',
            success: function(products) {
                const select = $(selector);
                
                products.forEach(product => {
                    select.append(new Option(
                        `${product.name} - ${formatPrice(product.price)} DT`,
                        product.id,
                        false,
                        false
                    )).find(`option[value="${product.id}"]`).data('price', product.price);
                });
            }
        });
    }
    
    // Fonction pour mettre à jour le total d'une ligne
    function updateLineTotal(lineIndex) {
        const selectEl = $(`#product-select-${lineIndex}`);
        
        if (selectEl.val() && selectEl.val() !== 'new') {
            let price;
            
            if (selectEl.val().startsWith('new:')) {
                const parts = selectEl.val().split(':');
                price = parseFloat(parts[2]);
            } else {
                price = selectEl.find('option:selected').data('price');
            }
            
            const quantity = $(`#product-quantity-${lineIndex}`).val() || 1;
            const total = price * quantity;
            
            $(`#line-total-${lineIndex}`).text(formatPrice(total) + ' DT');
            updateCartSummary();
        }
    }
    
    // Fonction pour mettre à jour le résumé du panier
    function updateCartSummary() {
        let subtotal = 0;
        
        $('.product-line').each(function() {
            const lineId = $(this).attr('id').replace('product-line-', '');
            const selectEl = $(`#product-select-${lineId}`);
            
            if (selectEl.val() && selectEl.val() !== 'new') {
                let price;
                
                if (selectEl.val().startsWith('new:')) {
                    const parts = selectEl.val().split(':');
                    price = parseFloat(parts[2]);
                } else {
                    price = selectEl.find('option:selected').data('price');
                }
                
                const quantity = $(`#product-quantity-${lineId}`).val() || 1;
                subtotal += price * quantity;
            }
        });
        
        const shipping = parseFloat($('#shipping_cost').val()) || 0;
        const total = subtotal + shipping;
        
        $('#subtotal').text(formatPrice(subtotal) + ' DT');
        $('#shipping').text(formatPrice(shipping) + ' DT');
        $('#total').text(formatPrice(total) + ' DT');
        
        if ($('#confirmed_price').val() == '0' || $('#confirmed_price').val() == '') {
            $('#confirmed_price').val(formatPrice(total));
        }
    }
    
    // Mettre à jour le résumé quand les frais de livraison changent
    $(document).on('change input', '#shipping_cost', function() {
        updateCartSummary();
    });
    
    // Fonction pour formater les prix
    function formatPrice(price) {
        return parseFloat(price).toFixed(3);
    }
    
    // Fonction de validation du formulaire
    function validateForm() {
        let hasProducts = false;
        
        $('.product-select').each(function() {
            if ($(this).val() && $(this).val() !== 'new') {
                hasProducts = true;
                return false;
            }
        });
        
        if (!hasProducts) {
            alert('Veuillez sélectionner au moins un produit.');
            return false;
        }
        
        const actionType = $('#action_type').val();
        const notes = $('#notes').val();
        
        if (actionType) {
            if (!notes) {
                alert('Veuillez entrer des notes pour expliquer cette action.');
                $('#notes').focus();
                return false;
            }
            
            switch(actionType) {
                case 'confirm':
                    if (!$('#customer_name').val() || !$('#customer_governorate').val() || 
                    !$('#customer_city').val() || !$('#customer_address').val()) {
                        alert('Pour une commande confirmée, tous les champs client sont obligatoires.');
                        return false;
                    }
                    
                    if (!$('#confirmed_price').val()) {
                        alert('Pour une commande confirmée, le prix confirmé est obligatoire.');
                        return false;
                    }
                    break;
                    
                case 'schedule':
                    if (!$('#scheduled_date').val()) {
                        alert('Pour une commande datée, la date de livraison est obligatoire.');
                        $('#scheduled_date').focus();
                        return false;
                    }
                    break;
            }
        } else {
            if ($('#status').val() === 'confirmée') {
                if (!$('#customer_name').val() || !$('#customer_governorate').val() || 
                    !$('#customer_city').val() || !$('#customer_address').val()) {
                    alert('Pour une commande confirmée, tous les champs client sont obligatoires.');
                    return false;
                }
                
                if (!$('#confirmed_price').val()) {
                    alert('Pour une commande confirmée, le prix confirmé est obligatoire.');
                    return false;
                }
            }
            
            if ($('#status').val() === 'datée' && !$('#scheduled_date').val()) {
                alert('Pour une commande datée, la date de livraison est obligatoire.');
                return false;
            }
        }
        
        return true;
    }
});
</script>
@endsection