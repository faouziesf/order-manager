@extends('layouts.admin')

@section('title', 'Modifier la Commande #' . str_pad($order->id, 6, '0', STR_PAD_LEFT))
@section('page-title', 'Modifier la Commande #' . str_pad($order->id, 6, '0', STR_PAD_LEFT))

@section('css')
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --success-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
        --danger-gradient: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        --warning-gradient: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        --info-gradient: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        --glass-bg: rgba(255, 255, 255, 0.95);
        --shadow-elevated: 0 8px 25px -8px rgba(0, 0, 0, 0.12);
        --border-radius: 12px;
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    body {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        font-family: 'Inter', sans-serif;
    }

    .page-container {
        background: var(--glass-bg);
        backdrop-filter: blur(20px);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-elevated);
        margin: 1rem;
        overflow: hidden;
        min-height: calc(100vh - 2rem);
    }

    .page-header {
        background: var(--primary-gradient);
        color: white;
        padding: 1.5rem 2rem;
        border-radius: var(--border-radius) var(--border-radius) 0 0;
        position: relative;
        overflow: hidden;
    }

    .page-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 100%;
        height: 200%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
        transform: rotate(15deg);
    }

    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: start;
        position: relative;
        z-index: 2;
    }

    @media (max-width: 1200px) {
        .header-content {
            flex-direction: column;
            gap: 1rem;
        }

        .header-actions {
            width: 100%;
            justify-content: center;
        }
    }

    .page-header h1 {
        font-size: 1.75rem;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .order-status-badge {
        padding: 0.375rem 0.75rem;
        border-radius: 15px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
    }

    .breadcrumb {
        background: transparent;
        margin: 0.5rem 0 0 0;
        padding: 0;
    }

    .breadcrumb-item a {
        color: rgba(255, 255, 255, 0.8);
        text-decoration: none;
    }

    .breadcrumb-item.active {
        color: white;
    }

    .header-actions {
        display: flex;
        gap: 0.75rem;
        align-items: center;
        flex-wrap: wrap;
    }

    .header-btn {
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: white;
        padding: 0.625rem 1.25rem;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: var(--transition);
        cursor: pointer;
        font-size: 0.875rem;
    }

    .header-btn:hover {
        background: rgba(255, 255, 255, 0.3);
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .header-btn.btn-call {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.8) 0%, rgba(5, 150, 105, 0.8) 100%);
    }

    .header-btn.btn-history {
        background: linear-gradient(135deg, rgba(139, 92, 246, 0.8) 0%, rgba(124, 58, 237, 0.8) 100%);
    }

    /* Layout Principal */
    .main-content {
        display: grid;
        grid-template-columns: 1fr 400px;
        gap: 2rem;
        padding: 2rem;
        min-height: calc(100vh - 200px);
    }

    @media (max-width: 1400px) {
        .main-content {
            grid-template-columns: 1fr 350px;
            gap: 1.5rem;
        }
    }

    @media (max-width: 1200px) {
        .main-content {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        
        .cart-section {
            position: static !important;
            width: 100% !important;
            max-width: none !important;
        }
    }

    @media (max-width: 768px) {
        .main-content {
            padding: 1rem;
            gap: 1rem;
        }
    }

    /* Formulaire Client */
    .customer-form {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    .form-header {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .form-header h3 {
        margin: 0;
        font-size: 1.125rem;
        font-weight: 600;
        color: #374151;
    }

    .form-header .icon {
        width: 32px;
        height: 32px;
        background: var(--primary-gradient);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.875rem;
    }

    .form-body {
        padding: 1.5rem;
    }

    .form-row {
        display: grid;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .form-row.cols-1 {
        grid-template-columns: 1fr;
    }

    .form-row.cols-2 {
        grid-template-columns: 1fr 1fr;
    }

    @media (max-width: 768px) {
        .form-row.cols-2 {
            grid-template-columns: 1fr;
        }
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-label {
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.375rem;
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        gap: 0.375rem;
    }

    .form-label .required {
        color: #ef4444;
        font-size: 0.75rem;
    }

    .form-control {
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        padding: 0.75rem;
        transition: var(--transition);
        font-size: 0.875rem;
        background: #fafafa;
        font-family: inherit;
    }

    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        background: white;
        outline: none;
    }

    .form-control:invalid {
        border-color: #ef4444;
    }

    .form-select {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 0.75rem center;
        background-repeat: no-repeat;
        background-size: 1.25em 1.25em;
        padding-right: 2.5rem;
    }

    /* Section Panier */
    .cart-section {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        height: fit-content;
        position: sticky;
        top: 1rem;
        width: 100%;
        max-width: 400px;
        transition: all 0.3s ease;
    }

    .cart-section.collapsed {
        max-width: 250px;
    }

    .cart-header {
        background: var(--success-gradient);
        color: white;
        padding: 1.25rem 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-radius: var(--border-radius) var(--border-radius) 0 0;
        cursor: pointer;
        user-select: none;
    }

    .cart-header h3 {
        margin: 0;
        font-size: 1.125rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        white-space: nowrap;
    }

    .cart-toggle-btn {
        background: transparent;
        border: none;
        color: white;
        font-size: 1.2rem;
        cursor: pointer;
        padding: 0.25rem;
        border-radius: 4px;
        transition: var(--transition);
    }

    .cart-toggle-btn:hover {
        background: rgba(255, 255, 255, 0.1);
    }

    .cart-body {
        max-height: 500px;
        overflow-y: auto;
        transition: all 0.3s ease;
    }

    .cart-section.collapsed .cart-body {
        display: none;
    }

    .cart-section.collapsed .cart-summary {
        display: none !important;
    }

    .cart-section.collapsed .order-controls {
        padding: 1rem;
    }

    .cart-section.collapsed .control-group {
        margin-bottom: 0.75rem;
    }

    .cart-section.collapsed .status-badges,
    .cart-section.collapsed .priority-badges {
        flex-direction: column;
        gap: 0.25rem;
    }

    .cart-section.collapsed .status-badge,
    .cart-section.collapsed .priority-badge {
        padding: 0.375rem 0.5rem;
        font-size: 0.7rem;
        text-align: center;
    }

    .cart-section.collapsed .action-buttons {
        flex-direction: column;
        gap: 0.5rem;
    }

    .product-search {
        padding: 1.25rem;
        border-bottom: 1px solid #e5e7eb;
        background: #f9fafb;
    }

    .search-input-group {
        position: relative;
    }

    .search-input-group input {
        padding-left: 2.5rem;
        background: white;
    }

    .search-input-group .search-icon {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: #6b7280;
        font-size: 0.875rem;
    }

    .product-suggestions {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #e5e7eb;
        border-top: none;
        border-radius: 0 0 8px 8px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        z-index: 1000;
        max-height: 200px;
        overflow-y: auto;
    }

    .suggestion-item {
        padding: 0.75rem;
        cursor: pointer;
        border-bottom: 1px solid #f3f4f6;
        transition: var(--transition);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .suggestion-item:hover {
        background: #f3f4f6;
    }

    .suggestion-item:last-child {
        border-bottom: none;
    }

    .cart-items {
        padding: 1rem;
        min-height: 150px;
    }

    .cart-empty {
        text-align: center;
        padding: 2rem 1rem;
        color: #6b7280;
    }

    .cart-empty i {
        font-size: 2rem;
        margin-bottom: 0.75rem;
        opacity: 0.5;
    }

    .cart-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem;
        background: #f9fafb;
        border-radius: 8px;
        margin-bottom: 0.75rem;
        border: 1px solid #e5e7eb;
        transition: var(--transition);
        animation: slideIn 0.3s ease-out;
    }

    .cart-item:hover {
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .item-info {
        flex: 1;
    }

    .item-name {
        font-weight: 600;
        color: #374151;
        font-size: 0.875rem;
        margin-bottom: 0.25rem;
    }

    .item-price {
        color: #6b7280;
        font-size: 0.75rem;
        font-family: monospace;
    }

    .quantity-control {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: white;
        border-radius: 6px;
        padding: 0.25rem;
    }

    .quantity-btn {
        width: 28px;
        height: 28px;
        border: none;
        background: #f3f4f6;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: var(--transition);
        color: #6b7280;
        font-size: 0.75rem;
    }

    .quantity-btn:hover {
        background: #e5e7eb;
        color: #374151;
    }

    .quantity-input {
        width: 40px;
        text-align: center;
        border: none;
        background: transparent;
        font-weight: 600;
        color: #374151;
        font-size: 0.875rem;
    }

    .remove-item {
        background: #fef2f2;
        color: #ef4444;
        border: none;
        border-radius: 6px;
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: var(--transition);
        font-size: 0.75rem;
    }

    .remove-item:hover {
        background: #fee2e2;
    }

    .cart-summary {
        padding: 1.25rem;
        background: #f9fafb;
        border-top: 1px solid #e5e7eb;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
        font-size: 0.875rem;
    }

    .summary-row:last-child {
        margin-bottom: 0;
        font-weight: 700;
        font-size: 1rem;
        color: #374151;
        padding-top: 0.75rem;
        border-top: 1px solid #e5e7eb;
    }

    .summary-label {
        color: #6b7280;
        font-weight: 500;
    }

    .summary-value {
        font-family: monospace;
        font-weight: 600;
        color: #374151;
    }

    /* Contrôles de commande */
    .order-controls {
        padding: 1.5rem;
        background: white;
        border-top: 1px solid #e5e7eb;
        border-radius: 0 0 var(--border-radius) var(--border-radius);
    }

    .control-group {
        margin-bottom: 1.25rem;
    }

    .control-group:last-child {
        margin-bottom: 0;
    }

    .control-label {
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
        display: block;
        font-size: 0.875rem;
    }

    .status-badges,
    .priority-badges {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .status-badge,
    .priority-badge {
        padding: 0.5rem 0.75rem;
        border-radius: 20px;
        border: 2px solid transparent;
        cursor: pointer;
        transition: var(--transition);
        font-weight: 500;
        font-size: 0.75rem;
        position: relative;
        overflow: hidden;
    }

    .status-badge.active,
    .priority-badge.active {
        color: white;
        transform: scale(1.05);
    }

    .status-nouvelle {
        background: #f3f4f6;
        color: #6b7280;
    }

    .status-nouvelle.active {
        background: var(--primary-gradient);
    }

    .status-confirmée {
        background: #ecfdf5;
        color: #059669;
    }

    .status-confirmée.active {
        background: var(--success-gradient);
    }

    .status-annulée {
        background: #fef2f2;
        color: #dc2626;
    }

    .status-annulée.active {
        background: var(--danger-gradient);
    }

    .status-datée {
        background: #fef3c7;
        color: #d97706;
    }

    .status-datée.active {
        background: var(--warning-gradient);
    }

    .status-en_route {
        background: #cffafe;
        color: #0891b2;
    }

    .status-en_route.active {
        background: var(--info-gradient);
    }

    .status-livrée {
        background: #f3e8ff;
        color: #8b5cf6;
    }

    .status-livrée.active {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    }

    .status-ancienne {
        background: #f8fafc;
        color: #64748b;
    }

    .status-ancienne.active {
        background: linear-gradient(135deg, #64748b 0%, #475569 100%);
    }

    .priority-normale {
        background: #f3f4f6;
        color: #6b7280;
    }

    .priority-normale.active {
        background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
    }

    .priority-urgente {
        background: #fef3c7;
        color: #d97706;
    }

    .priority-urgente.active {
        background: var(--warning-gradient);
    }

    .priority-vip {
        background: #fee2e2;
        color: #dc2626;
    }

    .priority-vip.active {
        background: var(--danger-gradient);
    }

    .action-buttons {
        display: flex;
        gap: 0.75rem;
        margin-top: 1.5rem;
    }

    .btn-save {
        flex: 1;
        background: var(--success-gradient);
        color: white;
        border: none;
        border-radius: 8px;
        padding: 0.875rem 1.5rem;
        font-weight: 600;
        font-size: 0.875rem;
        cursor: pointer;
        transition: var(--transition);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .btn-save:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
    }

    .btn-save:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .btn-secondary {
        background: #f3f4f6;
        color: #6b7280;
        border: none;
        border-radius: 8px;
        padding: 0.875rem 1.5rem;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        font-size: 0.875rem;
    }

    .btn-secondary:hover {
        background: #e5e7eb;
        color: #374151;
    }

    /* Modales */
    .modal-content {
        border: none;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-elevated);
        overflow: hidden;
    }

    .modal-header {
        background: var(--primary-gradient);
        color: white;
        border: none;
        padding: 1.25rem 1.5rem;
    }

    .modal-header .modal-title {
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .modal-header .btn-close {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        opacity: 1;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-header .btn-close:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    .modal-body {
        padding: 1.5rem;
    }

    .modal-footer {
        background: #f9fafb;
        border-top: 1px solid #e5e7eb;
        padding: 1.25rem 1.5rem;
    }

    /* Error states */
    .is-invalid {
        border-color: #ef4444 !important;
    }

    .invalid-feedback {
        display: block;
        width: 100%;
        margin-top: 0.25rem;
        font-size: 0.875rem;
        color: #ef4444;
    }

    /* Animations */
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Loading state */
    .loading {
        position: relative;
        pointer-events: none;
        opacity: 0.7;
    }

    .loading::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 16px;
        height: 16px;
        margin: -8px 0 0 -8px;
        border: 2px solid transparent;
        border-top: 2px solid #667eea;
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

    /* Responsive */
    @media (max-width: 768px) {
        .main-content {
            padding: 1rem;
            gap: 1rem;
        }

        .page-header {
            padding: 1.25rem 1.5rem;
        }

        .form-body {
            padding: 1.25rem;
        }

        .action-buttons {
            flex-direction: column;
            align-items: stretch;
        }

        .action-buttons .btn-save,
        .action-buttons .btn-secondary {
            width: 100%;
        }

        .header-actions {
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .header-btn {
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
        }
    }
</style>
@endsection

@section('content')
<div class="page-container">
    <!-- En-tête de page -->
    <div class="page-header">
        <div class="header-content">
            <div>
                <h1>
                    <i class="fas fa-edit"></i>
                    Commande #{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}
                    <span class="order-status-badge">
                        @switch($order->status)
                            @case('nouvelle')
                                <i class="fas fa-circle"></i>Nouvelle
                            @break
                            @case('confirmée')
                                <i class="fas fa-check-circle"></i>Confirmée
                            @break
                            @case('annulée')
                                <i class="fas fa-times-circle"></i>Annulée
                            @break
                            @case('datée')
                                <i class="fas fa-calendar-alt"></i>Datée
                            @break
                            @case('en_route')
                                <i class="fas fa-shipping-fast"></i>En Route
                            @break
                            @case('livrée')
                                <i class="fas fa-gift"></i>Livrée
                            @break
                            @case('ancienne')
                                <i class="fas fa-archive"></i>Ancienne
                            @break
                        @endswitch
                    </span>
                </h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">
                                <i class="fas fa-home"></i> Accueil
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.orders.index') }}">Commandes</a>
                        </li>
                        <li class="breadcrumb-item active">Modifier #{{ $order->id }}</li>
                    </ol>
                </nav>
            </div>

            <div class="header-actions">
                <button type="button" class="header-btn btn-call" data-bs-toggle="modal"
                    data-bs-target="#callAttemptModal">
                    <i class="fas fa-phone"></i>
                    Tentative d'Appel
                </button>
                <button type="button" class="header-btn btn-history" data-bs-toggle="modal"
                    data-bs-target="#historyModal">
                    <i class="fas fa-history"></i>
                    Historique
                </button>
            </div>
        </div>
    </div>

    <!-- Contenu principal -->
    <form id="orderForm" action="{{ route('admin.orders.update', $order) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="main-content">
            <!-- Formulaire Client -->
            <div class="customer-form">
                <div class="form-header">
                    <div class="icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>Informations Client</h3>
                </div>
                <div class="form-body">
                    <!-- Nom -->
                    <div class="form-row cols-1">
                        <div class="form-group">
                            <label for="customer_name" class="form-label">
                                <i class="fas fa-user"></i>
                                Nom Complet
                            </label>
                            <input type="text" class="form-control @error('customer_name') is-invalid @enderror"
                                id="customer_name" name="customer_name"
                                value="{{ old('customer_name', $order->customer_name) }}"
                                placeholder="Nom et prénom du client" autocomplete="name">
                            @error('customer_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Téléphones -->
                    <div class="form-row cols-2">
                        <div class="form-group">
                            <label for="customer_phone" class="form-label">
                                <i class="fas fa-phone"></i>
                                Téléphone Principal
                                <span class="required">*</span>
                            </label>
                            <input type="tel" class="form-control @error('customer_phone') is-invalid @enderror"
                                id="customer_phone" name="customer_phone"
                                value="{{ old('customer_phone', $order->customer_phone) }}"
                                placeholder="Ex: +216 XX XXX XXX" required autocomplete="tel">
                            @error('customer_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="customer_phone_2" class="form-label">
                                <i class="fas fa-phone-alt"></i>
                                Téléphone Secondaire
                            </label>
                            <input type="tel" class="form-control @error('customer_phone_2') is-invalid @enderror"
                                id="customer_phone_2" name="customer_phone_2"
                                value="{{ old('customer_phone_2', $order->customer_phone_2) }}"
                                placeholder="Téléphone alternatif" autocomplete="tel">
                            @error('customer_phone_2')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Gouvernorat et Ville -->
                    <div class="form-row cols-2">
                        <div class="form-group">
                            <label for="customer_governorate" class="form-label">
                                <i class="fas fa-map-marked-alt"></i>
                                Gouvernorat
                            </label>
                            <select class="form-select form-control @error('customer_governorate') is-invalid @enderror"
                                id="customer_governorate" name="customer_governorate">
                                <option value="">Choisir un gouvernorat</option>
                                @if (isset($regions))
                                    @foreach ($regions as $region)
                                        <option value="{{ $region->id }}"
                                            {{ old('customer_governorate', $order->customer_governorate) == $region->id ? 'selected' : '' }}>
                                            {{ $region->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @error('customer_governorate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="customer_city" class="form-label">
                                <i class="fas fa-city"></i>
                                Ville
                            </label>
                            <select class="form-select form-control @error('customer_city') is-invalid @enderror" id="customer_city"
                                name="customer_city">
                                <option value="">Choisir une ville</option>
                                @if (isset($cities))
                                    @foreach ($cities as $city)
                                        <option value="{{ $city->id }}"
                                            {{ old('customer_city', $order->customer_city) == $city->id ? 'selected' : '' }}>
                                            {{ $city->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @error('customer_city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Adresse -->
                    <div class="form-row cols-1">
                        <div class="form-group">
                            <label for="customer_address" class="form-label">
                                <i class="fas fa-map-marker-alt"></i>
                                Adresse Complète
                            </label>
                            <textarea class="form-control @error('customer_address') is-invalid @enderror" id="customer_address"
                                name="customer_address" rows="3" placeholder="Adresse détaillée du client" 
                                autocomplete="street-address">{{ old('customer_address', $order->customer_address) }}</textarea>
                            @error('customer_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Commentaires -->
                    <div class="form-row cols-1">
                        <div class="form-group">
                            <label for="notes" class="form-label">
                                <i class="fas fa-sticky-note"></i>
                                Commentaires
                            </label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3"
                                placeholder="Notes supplémentaires sur la commande">{{ old('notes', $order->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Champs conditionnels selon le statut -->
                    @if ($order->status === 'datée' || old('status') === 'datée')
                        <div class="form-row cols-1" id="scheduled-date-row">
                            <div class="form-group">
                                <label for="scheduled_date" class="form-label">
                                    <i class="fas fa-calendar-alt"></i>
                                    Date de Livraison Prévue
                                </label>
                                <input type="date" class="form-control @error('scheduled_date') is-invalid @enderror"
                                    id="scheduled_date" name="scheduled_date"
                                    value="{{ old('scheduled_date', $order->scheduled_date ? $order->scheduled_date->format('Y-m-d') : '') }}">
                                @error('scheduled_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    @endif

                    @if (in_array($order->status, ['confirmée', 'datée']) || in_array(old('status'), ['confirmée', 'datée']))
                        <div class="form-row cols-1" id="total-price-row">
                            <div class="form-group">
                                <label for="total_price" class="form-label">
                                    <i class="fas fa-dollar-sign"></i>
                                    Prix Total (optionnel)
                                </label>
                                <input type="number" class="form-control @error('total_price') is-invalid @enderror"
                                    id="total_price" name="total_price" step="0.001" min="0"
                                    value="{{ old('total_price', $order->total_price) }}"
                                    placeholder="Laisser vide pour calcul automatique">
                                @error('total_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Section Panier -->
            <div class="cart-section">
                <div class="cart-header" onclick="toggleCart()">
                    <h3>
                        <i class="fas fa-shopping-cart"></i>
                        <span>Panier </span>(<span id="cart-count">{{ $order->items->count() }}</span>)
                    </h3>
                    <button type="button" class="cart-toggle-btn" id="cart-toggle-btn">
                        <i class="fas fa-chevron-up"></i>
                    </button>
                </div>

                <div class="cart-body" id="cart-body">
                    <!-- Recherche de produits -->
                    <div class="product-search">
                        <div class="search-input-group">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" class="form-control" id="product-search"
                                placeholder="Rechercher un produit..." autocomplete="off">
                            <div class="product-suggestions" id="product-suggestions" style="display: none;"></div>
                        </div>
                    </div>

                    <!-- Liste des produits -->
                    <div class="cart-items" id="cart-items">
                        @if ($order->items->count() === 0)
                            <div class="cart-empty" id="cart-empty">
                                <i class="fas fa-shopping-basket"></i>
                                <h5>Panier vide</h5>
                                <p>Recherchez et ajoutez des produits</p>
                            </div>
                        @else
                            <div class="cart-empty" id="cart-empty" style="display: none;">
                                <i class="fas fa-shopping-basket"></i>
                                <h5>Panier vide</h5>
                                <p>Recherchez et ajoutez des produits</p>
                            </div>
                        @endif
                    </div>

                    <!-- Résumé du panier -->
                    <div class="cart-summary" id="cart-summary"
                        style="{{ $order->items->count() > 0 ? '' : 'display: none;' }}">
                        <div class="summary-row">
                            <span class="summary-label">Sous-total:</span>
                            <span class="summary-value" id="subtotal">{{ number_format($order->items->sum('total_price'), 3) }} TND</span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label">Total:</span>
                            <span class="summary-value" id="total">{{ number_format($order->total_price, 3) }} TND</span>
                        </div>
                    </div>
                </div>

                <!-- Contrôles de commande -->
                <div class="order-controls">
                    <div class="control-group">
                        <label class="control-label">Statut de la commande</label>
                        <div class="status-badges">
                            <div class="status-badge status-nouvelle {{ $order->status === 'nouvelle' ? 'active' : '' }}"
                                data-status="nouvelle">
                                <i class="fas fa-circle"></i> Nouvelle
                            </div>
                            <div class="status-badge status-confirmée {{ $order->status === 'confirmée' ? 'active' : '' }}"
                                data-status="confirmée">
                                <i class="fas fa-check-circle"></i> Confirmée
                            </div>
                            <div class="status-badge status-annulée {{ $order->status === 'annulée' ? 'active' : '' }}"
                                data-status="annulée">
                                <i class="fas fa-times-circle"></i> Annulée
                            </div>
                            <div class="status-badge status-datée {{ $order->status === 'datée' ? 'active' : '' }}"
                                data-status="datée">
                                <i class="fas fa-calendar-alt"></i> Datée
                            </div>
                            <div class="status-badge status-ancienne {{ $order->status === 'ancienne' ? 'active' : '' }}"
                                data-status="ancienne">
                                <i class="fas fa-archive"></i> Ancienne
                            </div>
                            <div class="status-badge status-en_route {{ $order->status === 'en_route' ? 'active' : '' }}"
                                data-status="en_route">
                                <i class="fas fa-shipping-fast"></i> En Route
                            </div>
                            <div class="status-badge status-livrée {{ $order->status === 'livrée' ? 'active' : '' }}"
                                data-status="livrée">
                                <i class="fas fa-gift"></i> Livrée
                            </div>
                        </div>
                        <input type="hidden" name="status" id="status" value="{{ $order->status }}">
                    </div>

                    <div class="control-group">
                        <label class="control-label">Priorité</label>
                        <div class="priority-badges">
                            <div class="priority-badge priority-normale {{ $order->priority === 'normale' ? 'active' : '' }}"
                                data-priority="normale">
                                <i class="fas fa-minus"></i> Normale
                            </div>
                            <div class="priority-badge priority-urgente {{ $order->priority === 'urgente' ? 'active' : '' }}"
                                data-priority="urgente">
                                <i class="fas fa-exclamation"></i> Urgente
                            </div>
                            <div class="priority-badge priority-vip {{ $order->priority === 'vip' ? 'active' : '' }}"
                                data-priority="vip">
                                <i class="fas fa-crown"></i> VIP
                            </div>
                        </div>
                        <input type="hidden" name="priority" id="priority" value="{{ $order->priority }}">
                    </div>

                    <div class="control-group">
                        <label for="employee_id" class="control-label">
                            <i class="fas fa-user-tie"></i>
                            Assigner à un employé
                        </label>
                        <select class="form-select form-control" id="employee_id" name="employee_id">
                            <option value="">Non assigné</option>
                            @if (isset($employees) && $employees->count() > 0)
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}"
                                        {{ $order->employee_id == $employee->id ? 'selected' : '' }}>
                                        {{ $employee->name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="action-buttons">
                        <a href="{{ route('admin.orders.index') }}" class="btn-secondary">
                            <i class="fas fa-arrow-left"></i> Retour
                        </a>
                        <button type="submit" class="btn-save" id="save-btn">
                            <i class="fas fa-save"></i> Enregistrer
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hidden inputs pour les données du panier -->
        <div id="cart-data" style="display: none;"></div>
    </form>
</div>

<!-- Modal Tentative d'Appel -->
<div class="modal fade" id="callAttemptModal" tabindex="-1" aria-labelledby="callAttemptModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="callAttemptModalLabel">
                    <i class="fas fa-phone"></i>
                    Nouvelle Tentative d'Appel
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="callAttemptForm" action="{{ route('admin.orders.recordAttempt', $order) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="attempt_notes" class="form-label">
                            <i class="fas fa-sticky-note"></i>
                            Notes sur la tentative d'appel
                            <span class="required">*</span>
                        </label>
                        <textarea class="form-control" id="attempt_notes" name="notes"
                            placeholder="Décrivez le résultat de votre appel (répondu, occupé, boîte vocale, etc.)" 
                            required rows="4"></textarea>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Information :</strong> Cette action incrémentera automatiquement le compteur de
                        tentatives et sera enregistrée dans l'historique de la commande.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Annuler
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Enregistrer la Tentative
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Historique -->
<div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="historyModalLabel">
                    <i class="fas fa-history"></i>
                    Historique de la Commande #{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="history-timeline" id="history-timeline">
                    <!-- Contenu chargé dynamiquement -->
                    <div class="text-center py-4">
                        <div class="loading"></div>
                        <p class="mt-3 text-muted">Chargement de l'historique...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Fermer
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Initialisation du panier depuis la base de données
    let cart = [];
    let searchTimeout;

    @if ($order->items->count() > 0)
        cart = [
            @foreach ($order->items as $item)
                {
                    id: {{ $item->product_id }},
                    name: "{{ addslashes($item->product->name ?? 'Produit supprimé') }}",
                    price: {{ (float) $item->unit_price }},
                    quantity: {{ $item->quantity }},
                    stock: {{ $item->product->stock ?? 0 }}
                }
                @if (!$loop->last)
                    ,
                @endif
            @endforeach
        ];
    @endif

    // Initialiser le panier au chargement
    updateCartDisplay();

    // =========================
    // TOGGLE DU PANIER
    // =========================
    window.toggleCart = function() {
        const cartSection = $('.cart-section');
        const toggleBtn = $('#cart-toggle-btn i');
        
        if (cartSection.hasClass('collapsed')) {
            cartSection.removeClass('collapsed');
            toggleBtn.removeClass('fa-chevron-down').addClass('fa-chevron-up');
        } else {
            cartSection.addClass('collapsed');
            toggleBtn.removeClass('fa-chevron-up').addClass('fa-chevron-down');
        }
    };

    // =========================
    // RECHERCHE DE PRODUITS
    // =========================
    $('#product-search').on('input', function() {
        const query = $(this).val().trim();

        clearTimeout(searchTimeout);
        $('#product-suggestions').hide();
        
        if (query.length >= 2) {
            searchTimeout = setTimeout(() => {
                searchProducts(query);
            }, 300);
        }
    });

    function searchProducts(query) {
        $.get('/admin/orders/search-products', { search: query })
            .done(function(data) {
                showProductSuggestions(data);
            })
            .fail(function() {
                console.error('Erreur lors de la recherche de produits');
                showProductSuggestions([]);
            });
    }

    function showProductSuggestions(products) {
        const suggestions = $('#product-suggestions');
        suggestions.empty();

        if (products.length === 0) {
            suggestions.html('<div class="suggestion-item">Aucun produit trouvé</div>');
        } else {
            products.forEach(product => {
                const item = $(`
                    <div class="suggestion-item" data-product-id="${product.id}">
                        <div>
                            <strong>${product.name}</strong>
                            <br><small class="text-muted">Stock: ${product.stock}</small>
                        </div>
                        <div class="text-success fw-bold">${parseFloat(product.price).toFixed(3)} TND</div>
                    </div>
                `);

                item.on('click', function() {
                    addToCart(product);
                    $('#product-search').val('');
                    suggestions.hide();
                });

                suggestions.append(item);
            });
        }

        suggestions.show();
    }

    // Masquer suggestions en cliquant ailleurs
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.search-input-group').length) {
            $('#product-suggestions').hide();
        }
    });

    // =========================
    // GESTION DU PANIER
    // =========================
    function addToCart(product) {
        const existingItem = cart.find(item => item.id === product.id);

        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            cart.push({
                id: product.id,
                name: product.name,
                price: parseFloat(product.price),
                quantity: 1,
                stock: product.stock
            });
        }

        updateCartDisplay();
    }

    function removeFromCart(productId) {
        cart = cart.filter(item => item.id !== productId);
        updateCartDisplay();
    }

    function updateQuantity(productId, newQuantity) {
        const item = cart.find(item => item.id === productId);
        if (item) {
            const validQuantity = Math.max(1, Math.min(newQuantity, item.stock));
            item.quantity = validQuantity;
            updateCartDisplay();
        }
    }

    function updateCartDisplay() {
        const cartItems = $('#cart-items');
        const cartEmpty = $('#cart-empty');
        const cartSummary = $('#cart-summary');
        const cartCount = $('#cart-count');

        cartCount.text(cart.length);

        if (cart.length === 0) {
            cartEmpty.show();
            cartSummary.hide();
            cartItems.find('.cart-item').remove();
        } else {
            cartEmpty.hide();
            cartSummary.show();

            // Supprimer les anciens items
            cartItems.find('.cart-item').remove();

            // Ajouter les nouveaux items
            cart.forEach(item => {
                const cartItem = createCartItemElement(item);
                cartItems.append(cartItem);
            });

            updateCartSummary();
        }

        updateFormData();
    }

    function createCartItemElement(item) {
        const element = $(`
            <div class="cart-item" data-product-id="${item.id}">
                <div class="item-info">
                    <div class="item-name">${item.name}</div>
                    <div class="item-price">${item.price.toFixed(3)} TND × ${item.quantity}</div>
                </div>
                <div class="quantity-control">
                    <button type="button" class="quantity-btn minus" data-action="minus">
                        <i class="fas fa-minus"></i>
                    </button>
                    <input type="number" class="quantity-input" value="${item.quantity}" min="1" max="${item.stock}">
                    <button type="button" class="quantity-btn plus" data-action="plus">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <button type="button" class="remove-item" data-action="remove">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `);

        // Event listeners
        element.find('.quantity-btn.minus').on('click', function() {
            updateQuantity(item.id, item.quantity - 1);
        });

        element.find('.quantity-btn.plus').on('click', function() {
            updateQuantity(item.id, item.quantity + 1);
        });

        element.find('.quantity-input').on('change', function() {
            const newQuantity = parseInt($(this).val()) || 1;
            updateQuantity(item.id, newQuantity);
        });

        element.find('.remove-item').on('click', function() {
            removeFromCart(item.id);
        });

        return element;
    }

    function updateCartSummary() {
        const subtotal = cart.reduce((sum, item) => {
            const itemTotal = (parseFloat(item.price) || 0) * (parseInt(item.quantity) || 0);
            return sum + itemTotal;
        }, 0);
        
        const total = subtotal;

        $('#subtotal').text(subtotal.toFixed(3) + ' TND');
        $('#total').text(total.toFixed(3) + ' TND');
    }

    function updateFormData() {
        const cartData = $('#cart-data');
        cartData.empty();

        cart.forEach((item, index) => {
            cartData.append(`
                <input type="hidden" name="products[${index}][id]" value="${item.id}">
                <input type="hidden" name="products[${index}][quantity]" value="${item.quantity}">
            `);
        });
    }

    // =========================
    // GESTION DES BADGES
    // =========================
    $('.status-badge').on('click', function() {
        $('.status-badge').removeClass('active');
        $(this).addClass('active');
        const status = $(this).data('status');
        $('#status').val(status);

        // Gestion conditionnelle des champs
        handleConditionalFields(status);
    });

    $('.priority-badge').on('click', function() {
        $('.priority-badge').removeClass('active');
        $(this).addClass('active');
        $('#priority').val($(this).data('priority'));
    });

    function handleConditionalFields(status) {
        // Gestion du champ date programmée
        if (status === 'datée') {
            if ($('#scheduled-date-row').length === 0) {
                const dateField = `
                    <div class="form-row cols-1" id="scheduled-date-row">
                        <div class="form-group">
                            <label for="scheduled_date" class="form-label">
                                <i class="fas fa-calendar-alt"></i>
                                Date de Livraison Prévue
                            </label>
                            <input type="date" class="form-control" id="scheduled_date" name="scheduled_date">
                        </div>
                    </div>
                `;
                $('#notes').closest('.form-row').after(dateField);
            }
        } else {
            $('#scheduled-date-row').remove();
        }

        // Gestion du champ prix total
        if (['confirmée', 'datée'].includes(status)) {
            if ($('#total-price-row').length === 0) {
                const priceField = `
                    <div class="form-row cols-1" id="total-price-row">
                        <div class="form-group">
                            <label for="total_price" class="form-label">
                                <i class="fas fa-dollar-sign"></i>
                                Prix Total (optionnel)
                            </label>
                            <input type="number" class="form-control" id="total_price" name="total_price" 
                                   step="0.001" min="0" placeholder="Laisser vide pour calcul automatique">
                        </div>
                    </div>
                `;
                $('#notes').closest('.form-row').after(priceField);
            }
        } else {
            $('#total-price-row').remove();
        }
    }

    // =========================
    // GESTION GÉOGRAPHIQUE
    // =========================
    $('#customer_governorate').on('change', function() {
        const regionId = $(this).val();
        const citySelect = $('#customer_city');

        citySelect.html('<option value="">Chargement...</option>').prop('disabled', true);

        if (regionId) {
            $.get('/admin/orders/get-cities', { region_id: regionId })
                .done(function(cities) {
                    citySelect.html('<option value="">Choisir une ville</option>');
                    cities.forEach(city => {
                        const selected = city.id == {{ $order->customer_city ?? 'null' }} ? 'selected' : '';
                        citySelect.append(`<option value="${city.id}" ${selected}>${city.name}</option>`);
                    });
                })
                .fail(function(xhr) {
                    console.error('Erreur lors du chargement des villes:', xhr);
                    citySelect.html('<option value="">Erreur de chargement</option>');
                })
                .always(function() {
                    citySelect.prop('disabled', false);
                });
        } else {
            citySelect.html('<option value="">Choisir une ville</option>').prop('disabled', false);
        }
    });

    // Charger les villes si un gouvernorat est sélectionné au chargement
    if ($('#customer_governorate').val()) {
        $('#customer_governorate').trigger('change');
    }

    // =========================
    // GESTION DES MODALES
    // =========================

    // Modal Historique
    $('#historyModal').on('show.bs.modal', function() {
        loadOrderHistory();
    });

    function loadOrderHistory() {
        const timeline = $('#history-timeline');

        $.get('{{ route('admin.orders.history-modal', $order) }}')
            .done(function(response) {
                timeline.html(response);
            })
            .fail(function() {
                timeline.html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        Erreur lors du chargement de l'historique
                    </div>
                `);
            });
    }

    // Modal Tentative d'Appel
    $('#callAttemptForm').on('submit', function(e) {
        e.preventDefault();

        const form = $(this);
        const notes = $('#attempt_notes').val().trim();

        if (!notes) {
            alert('Veuillez saisir des notes sur la tentative d\'appel.');
            return;
        }

        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Enregistrement...');

        $.post(form.attr('action'), form.serialize())
            .done(function(response) {
                $('#callAttemptModal').modal('hide');
                form[0].reset();

                // Afficher un message de succès
                const alert = $(`
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle"></i>
                        Tentative d'appel enregistrée avec succès
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `);
                $('.main-content').before(alert);

                // Masquer automatiquement après 5 secondes
                setTimeout(() => alert.fadeOut(), 5000);

                // Recharger la page pour mettre à jour les compteurs
                setTimeout(() => location.reload(), 2000);
            })
            .fail(function(xhr) {
                let errorMessage = 'Erreur lors de l\'enregistrement';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMessage = Object.values(xhr.responseJSON.errors).flat().join(', ');
                }
                
                alert('Erreur: ' + errorMessage);
            })
            .always(function() {
                submitBtn.prop('disabled', false).html(originalText);
            });
    });

    // =========================
    // VALIDATION DU FORMULAIRE
    // =========================
    $('#orderForm').on('submit', function(e) {
        const errors = [];

        // Vérifier le téléphone
        const phone = $('#customer_phone').val().trim();
        if (!phone) {
            errors.push('Le numéro de téléphone principal est obligatoire');
        }

        // Vérifier le panier
        if (cart.length === 0) {
            errors.push('Veuillez ajouter au moins un produit à la commande');
        }

        if (errors.length > 0) {
            e.preventDefault();
            alert('Erreurs:\n' + errors.join('\n'));
            return false;
        }

        // Désactiver le bouton pour éviter les double soumissions
        const submitBtn = $('#save-btn');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Enregistrement...');
        
        // Réactiver après 10 secondes en cas de problème
        setTimeout(() => {
            submitBtn.prop('disabled', false).html(originalText);
        }, 10000);
    });

    $('#customer_phone').focus();
});
</script>
@endsection