@extends('layouts.admin')

@section('title', 'Créer une Commande')
@section('page-title', 'Créer une Nouvelle Commande')

@section('css')
<style>
    :root {
        --royal-blue: #1e3a8a;
        --royal-blue-light: #3b82f6;
        --royal-blue-dark: #1e40af;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --glass-bg: rgba(255, 255, 255, 0.98);
        --shadow: 0 4px 20px rgba(30, 58, 138, 0.12);
        --border-radius: 12px;
        --transition: all 0.3s ease;
    }

    body {
        background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
        font-family: 'Inter', sans-serif;
        min-height: 100vh;
    }

    .container-fluid {
        max-width: 1400px;
        margin: 0 auto;
        padding: 1.5rem;
    }

    /* Header moderne */
    .page-header {
        background: linear-gradient(135deg, var(--royal-blue) 0%, var(--royal-blue-dark) 100%);
        color: white;
        padding: 2rem;
        border-radius: var(--border-radius);
        margin-bottom: 2rem;
        box-shadow: var(--shadow);
        position: relative;
        overflow: hidden;
    }

    .page-header::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 200px;
        height: 200px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        transform: translate(50%, -50%);
    }

    .page-header h1 {
        font-size: 1.75rem;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        position: relative;
        z-index: 2;
    }

    /* Layout moderne */
    .main-layout {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 2rem;
        align-items: start;
    }

    @media (max-width: 1200px) {
        .main-layout {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
    }

    /* Formulaire client moderne */
    .client-form {
        background: var(--glass-bg);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        padding: 2rem;
        border: 1px solid rgba(30, 58, 138, 0.08);
        backdrop-filter: blur(20px);
    }

    .form-section-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--royal-blue-dark);
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding-bottom: 1rem;
        border-bottom: 3px solid #f1f5f9;
        position: relative;
    }

    .form-section-title::after {
        content: '';
        position: absolute;
        bottom: -3px;
        left: 0;
        width: 60px;
        height: 3px;
        background: linear-gradient(90deg, var(--royal-blue), var(--royal-blue-light));
        border-radius: 2px;
    }

    .form-grid {
        display: grid;
        gap: 1.25rem;
    }

    .form-grid.two-cols {
        grid-template-columns: 1fr 1fr;
    }

    @media (max-width: 768px) {
        .form-grid.two-cols {
            grid-template-columns: 1fr;
        }
    }

    .form-field {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .form-label {
        font-weight: 600;
        color: var(--royal-blue-dark);
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-label .required {
        color: var(--danger);
        font-size: 0.75rem;
    }

    .form-input {
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.875rem;
        font-size: 0.9rem;
        background: #fafbfc;
        transition: var(--transition);
        font-family: inherit;
    }

    .form-input:focus {
        border-color: var(--royal-blue-light);
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        background: white;
        outline: none;
        transform: translateY(-1px);
    }

    .form-input.has-duplicates {
        border-color: var(--warning);
        background: rgba(245, 158, 11, 0.05);
        animation: shake 0.5s ease-in-out;
    }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-2px); }
        75% { transform: translateX(2px); }
    }

    /* Indicateur de téléphone amélioré */
    .phone-field {
        position: relative;
    }

    .phone-indicator {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        font-size: 1rem;
        display: none;
        z-index: 10;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .phone-indicator.checking {
        display: flex;
        background: #f3f4f6;
        color: #6b7280;
        animation: pulse 1.5s infinite;
    }

    .phone-indicator.has-duplicates {
        display: flex;
        background: rgba(245, 158, 11, 0.2);
        color: var(--warning);
        animation: bounce 0.5s ease;
    }

    .phone-indicator.clean {
        display: flex;
        background: rgba(16, 185, 129, 0.2);
        color: var(--success);
    }

    @keyframes bounce {
        0%, 100% { transform: translateY(-50%) scale(1); }
        50% { transform: translateY(-50%) scale(1.1); }
    }

    /* Alert de doublons moderne */
    .duplicate-alert {
        margin-top: 1rem;
        padding: 1.25rem;
        border-radius: 10px;
        border: 2px solid var(--warning);
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.08) 0%, rgba(245, 158, 11, 0.03) 100%);
        display: none;
        animation: slideDown 0.4s ease;
        position: relative;
        overflow: hidden;
    }

    .duplicate-alert::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--warning), #f59e0b);
    }

    .duplicate-alert.show {
        display: block;
    }

    .duplicate-alert-content {
        font-size: 0.9rem;
        color: #92400e;
        margin-bottom: 1rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .duplicate-actions {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .btn-small {
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .btn-small:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
    }

    .btn-royal {
        background: linear-gradient(135deg, var(--royal-blue) 0%, var(--royal-blue-light) 100%);
        color: white;
    }

    .btn-success {
        background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
        color: white;
    }

    .btn-outline {
        background: white;
        color: var(--royal-blue);
        border: 2px solid var(--royal-blue);
    }

    .btn-outline:hover {
        background: var(--royal-blue);
        color: white;
    }

    /* Panier moderne */
    .cart-panel {
        background: var(--glass-bg);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        border: 1px solid rgba(30, 58, 138, 0.08);
        position: sticky;
        top: 1.5rem;
        height: fit-content;
        backdrop-filter: blur(20px);
        overflow: hidden;
    }

    .cart-header {
        background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
        color: white;
        padding: 1.25rem 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: relative;
    }

    .cart-header::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 100px;
        height: 100px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        transform: translate(30%, -30%);
    }

    .cart-title {
        font-size: 1.1rem;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        position: relative;
        z-index: 2;
    }

    .cart-count {
        background: rgba(255, 255, 255, 0.25);
        padding: 0.25rem 0.6rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 700;
        min-width: 24px;
        text-align: center;
    }

    /* Recherche produits moderne */
    .product-search {
        padding: 1.25rem;
        border-bottom: 1px solid rgba(241, 245, 249, 0.8);
    }

    .search-group {
        position: relative;
    }

    .search-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #6b7280;
        font-size: 0.9rem;
        z-index: 5;
    }

    .search-input {
        width: 100%;
        padding: 0.875rem 1rem 0.875rem 2.75rem;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 0.9rem;
        background: white;
        transition: var(--transition);
    }

    .search-input:focus {
        border-color: var(--royal-blue-light);
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        outline: none;
    }

    .suggestions {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 2px solid #e2e8f0;
        border-top: none;
        border-radius: 0 0 8px 8px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        z-index: 1000;
        max-height: 250px;
        overflow-y: auto;
        display: none;
    }

    .suggestion {
        padding: 1rem;
        cursor: pointer;
        border-bottom: 1px solid #f9fafb;
        transition: var(--transition);
        font-size: 0.9rem;
    }

    .suggestion:hover {
        background: #f8fafc;
        transform: translateX(4px);
    }

    .suggestion:last-child {
        border-bottom: none;
    }

    .product-ref {
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.75rem;
        color: var(--royal-blue);
        background: rgba(30, 58, 138, 0.1);
        padding: 0.2rem 0.5rem;
        border-radius: 4px;
        margin-left: 0.5rem;
        font-weight: 600;
    }

    /* Items du panier améliorés */
    .cart-items {
        padding: 1.25rem;
        min-height: 140px;
        max-height: 320px;
        overflow-y: auto;
    }

    .cart-empty {
        text-align: center;
        padding: 1.5rem;
        color: #6b7280;
    }

    .cart-item {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        padding: 1rem;
        background: #f8fafc;
        border-radius: 10px;
        margin-bottom: 0.75rem;
        border: 1px solid #f1f5f9;
        transition: var(--transition);
    }

    .cart-item:hover {
        border-color: var(--royal-blue-light);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        transform: translateY(-1px);
    }

    .item-info {
        flex: 1;
        min-width: 0;
    }

    .item-name {
        font-weight: 700;
        color: var(--royal-blue-dark);
        font-size: 0.9rem;
        margin-bottom: 0.25rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .item-price {
        color: #6b7280;
        font-size: 0.8rem;
        font-family: 'JetBrains Mono', monospace;
        line-height: 1.4;
    }

    .item-stock {
        font-size: 0.75rem;
        margin-top: 0.25rem;
    }

    .item-stock.sufficient {
        color: var(--success);
    }

    .item-stock.insufficient {
        color: var(--danger);
        font-weight: 600;
    }

    .quantity-control {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: white;
        border-radius: 6px;
        padding: 0.25rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .qty-btn {
        width: 28px;
        height: 28px;
        border: none;
        background: #f3f4f6;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 0.75rem;
        color: #6b7280;
        transition: var(--transition);
    }

    .qty-btn:hover {
        background: var(--royal-blue-light);
        color: white;
        transform: scale(1.05);
    }

    .qty-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }

    .qty-input {
        width: 40px;
        text-align: center;
        border: none;
        background: transparent;
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--royal-blue-dark);
    }

    .remove-btn {
        background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
        color: var(--danger);
        border: none;
        border-radius: 6px;
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 0.75rem;
        transition: var(--transition);
    }

    .remove-btn:hover {
        background: var(--danger);
        color: white;
        transform: scale(1.05);
    }

    /* Contrôles améliorés */
    .cart-summary {
        padding: 1.25rem;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-top: 1px solid rgba(241, 245, 249, 0.8);
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.75rem;
        font-size: 0.9rem;
    }

    .summary-row:last-child {
        margin-bottom: 0;
        font-weight: 800;
        font-size: 1rem;
        padding-top: 0.75rem;
        border-top: 2px solid #e2e8f0;
        color: var(--royal-blue-dark);
    }

    .summary-value {
        font-family: 'JetBrains Mono', monospace;
        font-weight: 700;
    }

    .order-controls {
        padding: 1.25rem;
        background: white;
        border-top: 1px solid rgba(241, 245, 249, 0.8);
    }

    .control-section {
        margin-bottom: 1.25rem;
    }

    .control-label {
        font-weight: 700;
        color: var(--royal-blue-dark);
        margin-bottom: 0.75rem;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .status-options {
        display: flex;
        gap: 0.75rem;
    }

    .status-option {
        flex: 1;
        padding: 0.75rem;
        border: 2px solid transparent;
        border-radius: 8px;
        cursor: pointer;
        text-align: center;
        font-size: 0.85rem;
        font-weight: 700;
        transition: var(--transition);
        position: relative;
        overflow: hidden;
    }

    .status-option::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
        transition: left 0.5s ease;
    }

    .status-option:hover::before {
        left: 100%;
    }

    .status-option.nouvelle {
        background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
        color: #6b7280;
    }

    .status-option.nouvelle.active {
        background: linear-gradient(135deg, var(--royal-blue) 0%, var(--royal-blue-dark) 100%);
        color: white;
        border-color: var(--royal-blue-dark);
        box-shadow: 0 4px 16px rgba(30, 58, 138, 0.3);
    }

    .status-option.confirmée {
        background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
        color: #059669;
    }

    .status-option.confirmée.active {
        background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
        color: white;
        border-color: #059669;
        box-shadow: 0 4px 16px rgba(16, 185, 129, 0.3);
    }

    /* Champ prix total conditionnel amélioré */
    .total-price-field {
        margin-top: 1rem;
        padding: 1.25rem;
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        border: 2px solid #0ea5e9;
        border-radius: 10px;
        display: none;
        animation: slideDown 0.4s ease;
        position: relative;
    }

    .total-price-field::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #0ea5e9, #0284c7);
        border-radius: 10px 10px 0 0;
    }

    .total-price-field.show {
        display: block;
    }

    .employee-select {
        width: 100%;
        padding: 0.75rem;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 0.9rem;
        background: white;
        transition: var(--transition);
    }

    .employee-select:focus {
        border-color: var(--royal-blue-light);
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        outline: none;
    }

    .action-buttons {
        display: flex;
        gap: 1rem;
        margin-top: 1.5rem;
    }

    .btn-cancel {
        flex: 1;
        background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
        color: #6b7280;
        border: none;
        border-radius: 8px;
        padding: 1rem;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: var(--transition);
    }

    .btn-cancel:hover {
        background: linear-gradient(135deg, #e5e7eb 0%, #d1d5db 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
    }

    .btn-save {
        flex: 2;
        background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
        color: white;
        border: none;
        border-radius: 8px;
        padding: 1rem;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: var(--transition);
        box-shadow: 0 4px 16px rgba(16, 185, 129, 0.3);
    }

    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(16, 185, 129, 0.4);
    }

    .btn-save:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }

    /* Priority badge automatique amélioré */
    .auto-priority {
        background: linear-gradient(135deg, #d4a147 0%, #b8941f 100%);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 700;
        margin-top: 0.75rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        animation: slideIn 0.4s ease;
        box-shadow: 0 4px 16px rgba(212, 161, 71, 0.3);
    }

    /* Stock warning */
    .stock-warning {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        border: 2px solid var(--warning);
        border-radius: 10px;
        padding: 1rem;
        margin: 1rem 0;
        display: none;
        animation: slideDown 0.4s ease;
    }

    .stock-warning.show {
        display: block;
    }

    .stock-warning-content {
        color: #92400e;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    /* Modal amélioré */
    .modal-content {
        border: none;
        border-radius: var(--border-radius);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
        backdrop-filter: blur(20px);
    }

    .modal-header {
        background: linear-gradient(135deg, var(--royal-blue) 0%, var(--royal-blue-dark) 100%);
        color: white;
        border: none;
        border-radius: var(--border-radius) var(--border-radius) 0 0;
        padding: 1.5rem;
    }

    .history-item {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 1.25rem;
        margin-bottom: 1rem;
        border-left: 4px solid var(--royal-blue-light);
        transition: var(--transition);
    }

    .history-item:hover {
        background: white;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        transform: translateY(-2px);
    }

    /* Animations */
    @keyframes slideDown {
        from { 
            opacity: 0; 
            transform: translateY(-20px); 
            max-height: 0;
        }
        to { 
            opacity: 1; 
            transform: translateY(0); 
            max-height: 500px;
        }
    }

    @keyframes slideIn {
        from { 
            opacity: 0; 
            transform: translateX(-20px); 
        }
        to { 
            opacity: 1; 
            transform: translateX(0); 
        }
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.6; }
    }

    /* Responsive amélioré */
    @media (max-width: 768px) {
        .container-fluid {
            padding: 1rem;
        }
        
        .page-header {
            padding: 1.5rem;
        }
        
        .client-form, .cart-panel {
            padding: 1.5rem;
        }
        
        .action-buttons {
            flex-direction: column;
        }
        
        .duplicate-actions {
            flex-direction: column;
        }

        .status-options {
            flex-direction: column;
        }
    }

    /* Messages d'erreur stylés */
    .invalid-feedback {
        color: var(--danger);
        font-size: 0.8rem;
        font-weight: 600;
        margin-top: 0.25rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .invalid-feedback::before {
        content: '⚠️';
        font-size: 0.7rem;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="page-header">
        <h1><i class="fas fa-plus-circle"></i> Créer une Nouvelle Commande</h1>
    </div>

    <form id="orderForm" action="{{ route('admin.orders.store') }}" method="POST">
        @csrf
        <div class="main-layout">
            <!-- Formulaire Client -->
            <div class="client-form">
                <div class="form-section-title">
                    <i class="fas fa-user"></i> Informations Client
                </div>
                
                <div class="form-grid">
                    <!-- Nom - Conditionnel selon statut -->
                    <div class="form-field">
                        <label for="customer_name" class="form-label">
                            <i class="fas fa-user"></i> 
                            Nom Complet 
                            <span class="required" id="name-required" style="display: none;">*</span>
                        </label>
                        <input type="text" class="form-input @error('customer_name') is-invalid @enderror" 
                               id="customer_name" name="customer_name" value="{{ old('customer_name') }}" 
                               placeholder="Nom et prénom du client">
                        @error('customer_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <!-- Téléphones -->
                    <div class="form-grid two-cols">
                        <div class="form-field">
                            <label for="customer_phone" class="form-label">
                                <i class="fas fa-phone"></i> Téléphone <span class="required">*</span>
                            </label>
                            <div class="phone-field">
                                <input type="tel" class="form-input @error('customer_phone') is-invalid @enderror" 
                                       id="customer_phone" name="customer_phone" value="{{ old('customer_phone') }}" 
                                       placeholder="Ex: +216 XX XXX XXX" required>
                                <div class="phone-indicator" id="phone-indicator">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </div>
                            </div>
                            @error('customer_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            
                            <!-- Alert de doublons TOUJOURS VISIBLE -->
                            <div class="duplicate-alert" id="duplicate-alert">
                                <div class="duplicate-alert-content" id="duplicate-alert-content">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Vérification des doublons en cours...
                                </div>
                                <div class="duplicate-actions" id="duplicate-actions" style="display: none;">
                                    <button type="button" class="btn-small btn-royal" id="view-history-btn">
                                        <i class="fas fa-history"></i> Voir Historique
                                    </button>
                                    <button type="button" class="btn-small btn-success" id="fill-data-btn">
                                        <i class="fas fa-fill-drip"></i> Pré-remplir
                                    </button>
                                    <button type="button" class="btn-small btn-outline" onclick="dismissAlert()">
                                        <i class="fas fa-times"></i> Ignorer
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Priorité automatique -->
                            <div class="auto-priority" id="auto-priority" style="display: none;">
                                <i class="fas fa-copy"></i> Priorité Doublons Activée
                            </div>
                        </div>
                        
                        <div class="form-field">
                            <label for="customer_phone_2" class="form-label">
                                <i class="fas fa-phone-alt"></i> Téléphone 2 (Optionnel)
                            </label>
                            <input type="tel" class="form-input @error('customer_phone_2') is-invalid @enderror" 
                                   id="customer_phone_2" name="customer_phone_2" value="{{ old('customer_phone_2') }}" 
                                   placeholder="Téléphone alternatif">
                            @error('customer_phone_2') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    
                    <!-- Localisation - Conditionnel selon statut -->
                    <div class="form-grid two-cols">
                        <div class="form-field">
                            <label for="customer_governorate" class="form-label">
                                <i class="fas fa-map-marked-alt"></i> 
                                Gouvernorat 
                                <span class="required" id="gov-required" style="display: none;">*</span>
                            </label>
                            <select class="form-input @error('customer_governorate') is-invalid @enderror" 
                                    id="customer_governorate" name="customer_governorate">
                                <option value="">Choisir...</option>
                                @if (isset($regions))
                                    @foreach ($regions as $region)
                                        <option value="{{ $region->id }}" {{ old('customer_governorate') == $region->id ? 'selected' : '' }}>
                                            {{ $region->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @error('customer_governorate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        
                        <div class="form-field">
                            <label for="customer_city" class="form-label">
                                <i class="fas fa-city"></i> 
                                Ville 
                                <span class="required" id="city-required" style="display: none;">*</span>
                            </label>
                            <select class="form-input @error('customer_city') is-invalid @enderror" 
                                    id="customer_city" name="customer_city">
                                <option value="">Choisir...</option>
                            </select>
                            @error('customer_city') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    
                    <!-- Adresse - Conditionnel selon statut -->
                    <div class="form-field">
                        <label for="customer_address" class="form-label">
                            <i class="fas fa-map-marker-alt"></i> 
                            Adresse 
                            <span class="required" id="address-required" style="display: none;">*</span>
                        </label>
                        <textarea class="form-input @error('customer_address') is-invalid @enderror" 
                                  id="customer_address" name="customer_address" rows="2" 
                                  placeholder="Adresse complète">{{ old('customer_address') }}</textarea>
                        @error('customer_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <!-- Notes -->
                    <div class="form-field">
                        <label for="notes" class="form-label">
                            <i class="fas fa-sticky-note"></i> Notes (Optionnel)
                        </label>
                        <textarea class="form-input @error('notes') is-invalid @enderror" 
                                  id="notes" name="notes" rows="2" 
                                  placeholder="Commentaires sur la commande">{{ old('notes') }}</textarea>
                        @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                
                <!-- Warning stock global -->
                <div class="stock-warning" id="global-stock-warning">
                    <div class="stock-warning-content">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>Stock insuffisant pour certains produits. Impossible de confirmer la commande.</span>
                    </div>
                </div>
            </div>

            <!-- Panier -->
            <div class="cart-panel">
                <div class="cart-header">
                    <div class="cart-title">
                        <i class="fas fa-shopping-cart"></i> Panier
                        <span class="cart-count" id="cart-count">0</span>
                    </div>
                </div>
                
                <!-- Recherche produits -->
                <div class="product-search">
                    <div class="search-group">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" class="search-input" id="product-search" 
                               placeholder="Rechercher par nom ou référence...">
                        <div class="suggestions" id="suggestions"></div>
                    </div>
                </div>
                
                <!-- Items -->
                <div class="cart-items" id="cart-items">
                    <div class="cart-empty" id="cart-empty">
                        <i class="fas fa-shopping-basket" style="font-size: 2rem; margin-bottom: 0.5rem; opacity: 0.5; color: var(--royal-blue);"></i>
                        <div style="font-weight: 700; margin-bottom: 0.25rem; font-size: 0.9rem;">Panier vide</div>
                        <div style="font-size: 0.75rem;">Recherchez des produits par nom ou référence</div>
                    </div>
                </div>
                
                <!-- Résumé -->
                <div class="cart-summary" id="cart-summary" style="display: none;">
                    <div class="summary-row">
                        <span><i class="fas fa-calculator"></i> Sous-total:</span>
                        <span class="summary-value" id="subtotal">0.000 TND</span>
                    </div>
                    <div class="summary-row">
                        <span><i class="fas fa-coins"></i> Total:</span>
                        <span class="summary-value" id="total">0.000 TND</span>
                    </div>
                </div>
                
                <!-- Contrôles -->
                <div class="order-controls">
                    <div class="control-section">
                        <div class="control-label">
                            <i class="fas fa-flag"></i> Statut de la Commande
                        </div>
                        <div class="status-options">
                            <div class="status-option nouvelle active" data-status="nouvelle">
                                <i class="fas fa-circle"></i> Nouvelle
                            </div>
                            <div class="status-option confirmée" data-status="confirmée">
                                <i class="fas fa-check-circle"></i> Confirmée
                            </div>
                        </div>
                        <input type="hidden" name="status" id="status" value="nouvelle">
                        <input type="hidden" name="priority" id="priority" value="normale">
                        
                        <!-- Champ prix total pour commandes confirmées -->
                        <div class="total-price-field" id="total-price-field">
                            <label for="total_price" class="control-label">
                                <i class="fas fa-euro-sign"></i> Prix Total Personnalisé <span class="required">*</span>
                            </label>
                            <input type="number" class="form-input" id="total_price" name="total_price" 
                                   step="0.001" min="0" placeholder="Obligatoire pour commande confirmée">
                            <small style="color: #6b7280; font-size: 0.8rem; margin-top: 0.5rem; display: block;">
                                <i class="fas fa-info-circle"></i> 
                                Obligatoire pour une commande confirmée
                            </small>
                        </div>
                    </div>
                    
                    <div class="control-section">
                        <label for="employee_id" class="control-label">
                            <i class="fas fa-user-tie"></i> Assigner à un Employé
                        </label>
                        <select class="employee-select" id="employee_id" name="employee_id">
                            <option value="">Aucun employé</option>
                            @if (isset($employees) && $employees->count() > 0)
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    
                    <div class="action-buttons">
                        <a href="{{ route('admin.orders.index') }}" class="btn-cancel">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                        <button type="submit" class="btn-save" id="save-btn">
                            <i class="fas fa-save"></i> Créer Commande
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div id="cart-data" style="display: none;"></div>
    </form>
</div>

<!-- Modal Historique -->
<div class="modal fade" id="historyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-history me-2"></i>Historique du Client
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="history-content">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2 text-muted">Chargement...</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let cart = [];
    let phoneTimeout;
    let hasExistingOrders = false;
    let latestClientData = null;

    console.log('🚀 Initialisation de la création de commande');

    // =========================
    // VÉRIFICATION TÉLÉPHONE CORRIGÉE ET AMÉLIORÉE
    // =========================
    $('#customer_phone').on('input', function() {
        const phone = $(this).val().trim();
        clearTimeout(phoneTimeout);
        
        // Afficher l'alert dès qu'on tape
        $('#duplicate-alert').addClass('show');
        $('#duplicate-actions').hide();
        
        if (phone.length >= 8) {
            phoneTimeout = setTimeout(() => checkPhone(phone), 800);
        } else {
            resetPhone();
            // NE PAS afficher le spinner avant 8 chiffres
            $('#phone-indicator').hide();
        }
    });

    function checkPhone(phone) {
        console.log('🔍 Vérification du téléphone:', phone);
        
        $('#phone-indicator').removeClass('has-duplicates clean').addClass('checking').show();
        $('#duplicate-alert-content').html(`
            <i class="fas fa-spinner fa-spin"></i>
            Vérification des doublons pour ${phone}...
        `);
        
        $.get('/admin/orders/check-phone-duplicates', { phone })
            .done(function(response) {
                console.log('✅ Réponse reçue:', response);
                $('#phone-indicator').removeClass('checking');
                
                if (response.has_duplicates && response.total_orders > 0) {
                    $('#phone-indicator').addClass('has-duplicates').html('<i class="fas fa-exclamation-triangle"></i>');
                    $('#customer_phone').addClass('has-duplicates');
                    showDuplicateAlert(response);
                    setAutoPriority(true);
                } else {
                    $('#phone-indicator').addClass('clean').html('<i class="fas fa-check"></i>');
                    $('#customer_phone').removeClass('has-duplicates');
                    showCleanAlert();
                    setAutoPriority(false);
                }
            })
            .fail(function(xhr) {
                console.error('❌ Erreur vérification téléphone:', xhr);
                $('#duplicate-alert-content').html(`
                    <i class="fas fa-exclamation-circle"></i>
                    Erreur lors de la vérification. Vérifiez votre connexion.
                `);
                resetPhone();
            });
    }

    function resetPhone() {
        $('#phone-indicator').removeClass('checking has-duplicates clean').hide();
        $('#customer_phone').removeClass('has-duplicates');
        $('#duplicate-alert').removeClass('show');
        setAutoPriority(false);
    }

    function showDuplicateAlert(response) {
        $('#duplicate-alert-content').html(`
            <i class="fas fa-exclamation-triangle"></i>
            <strong>${response.total_orders} commande(s)</strong> trouvée(s) pour ce numéro !
        `);
        $('#duplicate-actions').show();
        $('#duplicate-alert').addClass('show');
        hasExistingOrders = true;
        
        // Charger automatiquement les données du client
        loadClientDataForAutofill(response.orders[0]?.customer_phone || $('#customer_phone').val());
    }

    function showCleanAlert() {
        $('#duplicate-alert-content').html(`
            <i class="fas fa-check-circle"></i>
            Aucun doublon détecté pour ce numéro.
        `);
        $('#duplicate-actions').hide();
        $('#duplicate-alert').addClass('show');
        hasExistingOrders = false;
    }

    function setAutoPriority(isDuplicate) {
        if (isDuplicate) {
            $('#priority').val('urgente');
            $('#auto-priority').show();
        } else {
            $('#priority').val('normale');
            $('#auto-priority').hide();
        }
    }

    window.dismissAlert = function() {
        $('#duplicate-alert').removeClass('show');
        setAutoPriority(false);
    };

    // =========================
    // HISTORIQUE ET PRÉ-REMPLISSAGE FONCTIONNELS
    // =========================
    $('#view-history-btn').on('click', function() {
        console.log('📋 Affichage de l\'historique');
        const phone = $('#customer_phone').val().trim();
        if (phone) {
            loadHistory(phone);
            $('#historyModal').modal('show');
        }
    });

    $('#fill-data-btn').on('click', function() {
        console.log('📝 Pré-remplissage des données');
        if (latestClientData) {
            fillData(latestClientData);
            showNotification('success', '✅ Données pré-remplies avec succès !');
        } else {
            showNotification('warning', '⚠️ Aucune donnée disponible pour le pré-remplissage.');
        }
    });

    function loadClientDataForAutofill(phone) {
        if (!phone) return;
        
        $.get('/admin/orders/client-history', { phone })
            .done(function(response) {
                if (response.latest_order) {
                    latestClientData = response.latest_order;
                    console.log('💾 Données client chargées:', latestClientData);
                }
            })
            .fail(function() {
                console.warn('⚠️ Impossible de charger les données client');
            });
    }

    function loadHistory(phone) {
        $('#history-content').html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary"></div>
                <p class="mt-2">Chargement de l'historique...</p>
            </div>
        `);
        
        $.get('/admin/orders/client-history', { phone })
            .done(function(response) {
                let content = '';
                if (response.orders?.length) {
                    content += `<div class="alert alert-info"><strong>Total:</strong> ${response.orders.length} commande(s) trouvée(s)</div>`;
                    response.orders.forEach(order => {
                        content += `
                            <div class="history-item">
                                <div class="d-flex justify-content-between mb-2">
                                    <strong>Commande #${order.id}</strong>
                                    <span class="badge bg-${getStatusColor(order.status)}">${order.status}</span>
                                </div>
                                <div class="small text-muted">
                                    <strong>Client:</strong> ${order.customer_name || 'N/A'}<br>
                                    <strong>Montant:</strong> ${parseFloat(order.total_price).toFixed(3)} TND<br>
                                    <strong>Date:</strong> ${new Date(order.created_at).toLocaleDateString('fr-FR')}
                                </div>
                            </div>
                        `;
                    });
                } else {
                    content = '<div class="text-center py-4 text-muted">Aucun historique trouvé</div>';
                }
                $('#history-content').html(content);
            })
            .fail(function() {
                $('#history-content').html('<div class="alert alert-danger">Erreur de chargement</div>');
            });
    }

    function fillData(order) {
        if (!order) return;
        
        console.log('🔄 Remplissage des données:', order);
        
        $('#customer_name').val(order.customer_name || '');
        $('#customer_phone_2').val(order.customer_phone_2 || '');
        $('#customer_address').val(order.customer_address || '');
        
        if (order.customer_governorate) {
            $('#customer_governorate').val(order.customer_governorate).trigger('change');
            setTimeout(() => {
                if (order.customer_city) {
                    $('#customer_city').val(order.customer_city);
                }
            }, 1000);
        }
        
        // Animation de confirmation
        $('.form-input').addClass('border-success');
        setTimeout(() => $('.form-input').removeClass('border-success'), 2000);
    }

    function getStatusColor(status) {
        const colors = {
            'nouvelle': 'secondary',
            'confirmée': 'success',
            'annulée': 'danger',
            'datée': 'warning',
            'livrée': 'primary'
        };
        return colors[status] || 'secondary';
    }

    // =========================
    // RECHERCHE PRODUITS PAR NOM ET RÉFÉRENCE
    // =========================
    $('#product-search').on('input', function() {
        const query = $(this).val().trim();
        if (query.length >= 2) {
            searchProducts(query);
        } else {
            $('#suggestions').hide();
        }
    });

    function searchProducts(query) {
        console.log('🔎 Recherche produits:', query);
        
        $.get('/admin/orders/search-products', { search: query })
            .done(data => {
                console.log('📦 Produits trouvés:', data.length);
                showSuggestions(data);
            })
            .fail(error => {
                console.error('❌ Erreur recherche produits:', error);
                showSuggestions([]);
            });
    }

    function showSuggestions(products) {
        const suggestions = $('#suggestions').empty();
        
        if (products.length === 0) {
            suggestions.html('<div class="suggestion">Aucun produit trouvé</div>');
        } else {
            products.forEach(product => {
                const item = $(`
                    <div class="suggestion d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${product.name}</strong>
                            ${product.reference ? `<span class="product-ref">Réf: ${product.reference}</span>` : ''}
                            <br><small class="text-muted">Stock: ${product.stock} disponible(s)</small>
                        </div>
                        <div class="fw-bold text-success">${parseFloat(product.price).toFixed(3)} TND</div>
                    </div>
                `).on('click', () => {
                    addToCart(product);
                    $('#product-search').val('');
                    suggestions.hide();
                });
                suggestions.append(item);
            });
        }
        
        suggestions.show();
    }

    $(document).on('click', e => {
        if (!$(e.target).closest('.search-group').length) {
            $('#suggestions').hide();
        }
    });

    // =========================
    // GESTION PANIER AMÉLIORÉE AVEC STOCK
    // =========================
    function addToCart(product) {
        console.log('🛒 Ajout au panier:', product.name);
        
        const existing = cart.find(item => item.id === product.id);
        
        if (existing) {
            existing.quantity += 1;
            showNotification('info', `➕ Quantité augmentée pour ${product.name}`);
        } else {
            cart.push({
                id: product.id,
                name: product.name,
                reference: product.reference,
                price: parseFloat(product.price),
                quantity: 1,
                stock: product.stock
            });
            showNotification('success', `✅ ${product.name} ajouté au panier`);
        }
        
        updateCart();
    }

    function updateCart() {
        const items = $('#cart-items');
        const empty = $('#cart-empty');
        const summary = $('#cart-summary');
        
        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
        $('#cart-count').text(totalItems);
        
        items.find('.cart-item').remove();
        
        if (cart.length === 0) {
            empty.show();
            summary.hide();
        } else {
            empty.hide();
            summary.show();
            
            cart.forEach(item => {
                items.append(createCartItem(item));
            });
            
            updateSummary();
        }
        
        updateFormData();
        checkGlobalStock();
    }

    function createCartItem(item) {
        const stockSufficient = item.stock >= item.quantity;
        const stockClass = stockSufficient ? 'sufficient' : 'insufficient';
        const stockText = `Stock: ${item.stock} disponible(s)`;
        
        return $(`
            <div class="cart-item">
                <div class="item-info">
                    <div class="item-name">${item.name}</div>
                    <div class="item-price">
                        ${item.reference ? `Réf: ${item.reference} • ` : ''}
                        ${item.price.toFixed(3)} TND × ${item.quantity}
                    </div>
                    <div class="item-stock ${stockClass}">
                        <i class="fas ${stockSufficient ? 'fa-check' : 'fa-exclamation-triangle'}"></i>
                        ${stockText}
                    </div>
                </div>
                <div class="quantity-control">
                    <button type="button" class="qty-btn minus" ${item.quantity <= 1 ? 'disabled' : ''}>
                        <i class="fas fa-minus"></i>
                    </button>
                    <input type="number" class="qty-input" value="${item.quantity}" min="1" max="${item.stock}">
                    <button type="button" class="qty-btn plus" ${item.quantity >= item.stock ? 'disabled' : ''}>
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <button type="button" class="remove-btn"><i class="fas fa-trash"></i></button>
            </div>
        `).on('click', '.minus', () => updateQuantity(item.id, item.quantity - 1))
          .on('click', '.plus', () => updateQuantity(item.id, item.quantity + 1))
          .on('change', '.qty-input', function() { updateQuantity(item.id, parseInt($(this).val()) || 1); })
          .on('click', '.remove-btn', () => removeFromCart(item.id));
    }

    function updateQuantity(id, newQty) {
        const item = cart.find(i => i.id === id);
        if (item) {
            const oldQty = item.quantity;
            item.quantity = Math.max(1, Math.min(newQty, item.stock));
            
            if (item.quantity !== oldQty) {
                updateCart();
                showNotification('info', `📊 Quantité mise à jour: ${item.name}`);
            }
        }
    }

    function removeFromCart(id) {
        const item = cart.find(i => i.id === id);
        if (item) {
            cart = cart.filter(item => item.id !== id);
            updateCart();
            showNotification('warning', `🗑️ ${item.name} retiré du panier`);
        }
    }

    function updateSummary() {
        const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        $('#subtotal').text(`${subtotal.toFixed(3)} TND`);
        $('#total').text(`${subtotal.toFixed(3)} TND`);
    }

    function updateFormData() {
        const data = $('#cart-data').empty();
        cart.forEach((item, index) => {
            data.append(`<input type="hidden" name="products[${index}][id]" value="${item.id}">`);
            data.append(`<input type="hidden" name="products[${index}][quantity]" value="${item.quantity}">`);
        });
    }

    function checkGlobalStock() {
        const status = $('#status').val();
        let hasStockIssues = false;
        
        if (status === 'confirmée') {
            cart.forEach(item => {
                if (item.quantity > item.stock) {
                    hasStockIssues = true;
                }
            });
        }
        
        if (hasStockIssues) {
            $('#global-stock-warning').addClass('show');
            $('#save-btn').prop('disabled', true);
        } else {
            $('#global-stock-warning').removeClass('show');
            $('#save-btn').prop('disabled', false);
        }
    }

    // =========================
    // GESTION STATUT ET VALIDATION CONDITIONNELLE
    // =========================
    $('.status-option').on('click', function() {
        $('.status-option').removeClass('active');
        $(this).addClass('active');
        const status = $(this).data('status');
        $('#status').val(status);
        
        console.log('📋 Changement de statut:', status);
        
        if (status === 'confirmée') {
            $('#total-price-field').addClass('show');
            makeFieldsRequired();
            checkGlobalStock();
            showNotification('info', '⚠️ Statut confirmé: tous les champs sont maintenant obligatoires');
        } else {
            $('#total-price-field').removeClass('show');
            makeBasicFieldsRequired();
            $('#global-stock-warning').removeClass('show');
            $('#save-btn').prop('disabled', false);
            showNotification('info', '📝 Statut nouvelle: seuls le téléphone et les produits sont obligatoires');
        }
    });

    function makeFieldsRequired() {
        // Afficher les astérisques
        $('#name-required, #gov-required, #city-required, #address-required').show();
        // Marquer les champs comme requis
        $('#customer_name, #customer_governorate, #customer_city, #customer_address, #total_price').prop('required', true);
        
        // Animation visuelle
        $('.form-label').addClass('text-primary');
        setTimeout(() => $('.form-label').removeClass('text-primary'), 1000);
    }

    function makeBasicFieldsRequired() {
        // Masquer les astérisques
        $('#name-required, #gov-required, #city-required, #address-required').hide();
        // Retirer le requis
        $('#customer_name, #customer_governorate, #customer_city, #customer_address, #total_price').prop('required', false);
    }

    // =========================
    // CHARGEMENT DES VILLES
    // =========================
    $('#customer_governorate').on('change', function() {
        const regionId = $(this).val();
        const citySelect = $('#customer_city');
        
        if (regionId) {
            console.log('🏙️ Chargement des villes pour la région:', regionId);
            
            citySelect.html('<option value="">Chargement...</option>');
            
            $.get('/admin/orders/get-cities', { region_id: regionId })
                .done(cities => {
                    citySelect.html('<option value="">Choisir...</option>');
                    cities.forEach(city => {
                        citySelect.append(`<option value="${city.id}">${city.name}</option>`);
                    });
                    console.log('✅ Villes chargées:', cities.length);
                })
                .fail(() => {
                    citySelect.html('<option value="">Erreur de chargement</option>');
                    showNotification('error', '❌ Impossible de charger les villes');
                });
        } else {
            citySelect.html('<option value="">Choisir...</option>');
        }
    });

    // =========================
    // VALIDATION FINALE DU FORMULAIRE AMÉLIORÉE
    // =========================
    $('#orderForm').on('submit', function(e) {
        console.log('📤 Soumission du formulaire');
        
        const errors = [];
        const status = $('#status').val();
        
        // Validation téléphone (toujours obligatoire)
        if (!$('#customer_phone').val().trim()) {
            errors.push('Le numéro de téléphone est obligatoire');
        }
        
        // Validation produits (toujours obligatoire)
        if (cart.length === 0) {
            errors.push('Le panier est vide - Ajoutez au moins un produit');
        }

        // Validation conditionnelle selon le statut
        if (status === 'confirmée') {
            if (!$('#customer_name').val().trim()) {
                errors.push('Le nom complet est obligatoire pour une commande confirmée');
            }
            if (!$('#customer_governorate').val()) {
                errors.push('Le gouvernorat est obligatoire pour une commande confirmée');
            }
            if (!$('#customer_city').val()) {
                errors.push('La ville est obligatoire pour une commande confirmée');
            }
            if (!$('#customer_address').val().trim()) {
                errors.push('L\'adresse est obligatoire pour une commande confirmée');
            }
            if (!$('#total_price').val().trim()) {
                errors.push('Le prix total personnalisé est obligatoire pour une commande confirmée');
            }
            
            // Validation du stock OBLIGATOIRE pour commandes confirmées
            let stockErrors = [];
            let hasStockIssues = false;
            cart.forEach(item => {
                if (item.quantity > item.stock) {
                    stockErrors.push(`${item.name}: quantité demandée ${item.quantity}, stock disponible ${item.stock}`);
                    hasStockIssues = true;
                }
            });
            
            if (hasStockIssues) {
                errors.push('Stock insuffisant pour :\n' + stockErrors.join('\n'));
            }
        }

        if (errors.length > 0) {
            e.preventDefault();
            showNotification('error', errors.join('\n\n'));
            return false;
        }

        // Désactiver le bouton et changer le texte (PAS de confirmation pour nouvelle)
        $('#save-btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Création en cours...');
        
        showNotification('success', '🚀 Création de la commande en cours...');
    });

    // =========================
    // SYSTÈME DE NOTIFICATIONS
    // =========================
    function showNotification(type, message) {
        const colors = {
            success: '#10b981',
            error: '#ef4444',
            warning: '#f59e0b',
            info: '#3b82f6'
        };
        
        const icons = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };
        
        const notification = $(`
            <div style="
                position: fixed; 
                top: 20px; 
                right: 20px; 
                z-index: 9999; 
                padding: 1rem 1.5rem; 
                border-radius: 10px; 
                color: white; 
                font-weight: 600; 
                background: ${colors[type]}; 
                box-shadow: 0 8px 32px rgba(0,0,0,0.15);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255,255,255,0.1);
                max-width: 400px;
                word-wrap: break-word;
                white-space: pre-line;
            ">
                <i class="fas ${icons[type]}" style="margin-right: 0.5rem;"></i>
                ${message}
            </div>
        `);
        
        $('body').append(notification);
        
        // Animation d'entrée
        notification.css({
            transform: 'translateX(100%)',
            opacity: 0
        }).animate({
            transform: 'translateX(0)',
            opacity: 1
        }, 300);
        
        // Auto-suppression
        setTimeout(() => {
            notification.animate({
                transform: 'translateX(100%)',
                opacity: 0
            }, 300, function() {
                notification.remove();
            });
        }, type === 'error' ? 8000 : 4000);
    }

    // =========================
    // INITIALISATION
    // =========================
    console.log('✅ Initialisation terminée');
    
    // Focus sur le téléphone
    $('#customer_phone').focus();
    
    // Afficher l'aide utilisateur
    showNotification('info', '💡 Saisissez un numéro de téléphone pour vérifier les doublons automatiquement');
});
</script>
@endsection