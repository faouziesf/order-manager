@extends('layouts.admin')

@section('title', 'Traitement des Commandes')
@section('page-title', 'Interface de Traitement')

@section('css')
<style>
    :root {
        --process-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --process-success: linear-gradient(135deg, #10b981 0%, #059669 100%);
        --process-warning: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        --process-danger: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        --process-info: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        --process-restock: linear-gradient(135deg, #10b981 0%, #059669 100%);
        --glass-bg: rgba(255, 255, 255, 0.95);
        --glass-border: rgba(255, 255, 255, 0.2);
        --shadow-elevated: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        --border-radius-xl: 24px;
        --border-radius-2xl: 32px;
        --transition-smooth: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        --spacing-xs: 0.5rem;
        --spacing-sm: 1rem;
        --spacing-md: 1.5rem;
        --spacing-lg: 2rem;
        --spacing-xl: 3rem;
    }

    body {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        font-family: 'Inter', sans-serif;
        overflow-x: hidden;
    }

    /* =========================
       CONTAINER PRINCIPAL
    ========================= */
    .process-container {
        background: var(--glass-bg);
        backdrop-filter: blur(20px);
        border-radius: var(--border-radius-2xl);
        box-shadow: var(--shadow-elevated);
        border: 1px solid var(--glass-border);
        margin: 0.5rem;
        min-height: calc(100vh - 120px);
        overflow: hidden;
    }

    /* =========================
       HEADER AVEC ONGLETS
    ========================= */
    .process-header {
        background: var(--process-primary);
        padding: 1rem 1.5rem;
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        gap: var(--spacing-lg);
    }

    .process-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 100%;
        height: 200%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
        transform: rotate(15deg);
    }

    .process-icon {
        color: white;
        font-size: 2.5rem;
        position: relative;
        z-index: 2;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 60px;
        height: 60px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: var(--border-radius-xl);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    /* =========================
       ONGLETS MODERNES
    ========================= */
    .queue-tabs {
        display: flex;
        gap: var(--spacing-sm);
        position: relative;
        z-index: 2;
        flex: 1;
        flex-wrap: wrap;
    }

    .queue-tab {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: var(--border-radius-xl);
        padding: 0.75rem 1rem;
        color: rgba(255, 255, 255, 0.8);
        cursor: pointer;
        transition: var(--transition-smooth);
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
        font-weight: 600;
        font-size: 0.95rem;
        position: relative;
        overflow: hidden;
        min-width: 140px;
        justify-content: center;
    }

    .queue-tab::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.1);
        transform: translateX(-100%);
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: -1;
    }

    .queue-tab:hover::before,
    .queue-tab.active::before {
        transform: translateX(0);
    }

    .queue-tab:hover {
        color: white;
        transform: translateY(-3px);
        box-shadow: var(--shadow-elevated);
    }

    .queue-tab.active {
        color: white;
        background: rgba(255, 255, 255, 0.25);
        border-color: rgba(255, 255, 255, 0.4);
        transform: translateY(-2px);
        box-shadow: var(--shadow-elevated);
    }

    .queue-icon {
        font-size: 1.2rem;
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 10px;
    }

    .queue-badge {
        background: rgba(255, 255, 255, 0.9);
        color: #4f46e5;
        padding: 3px 10px;
        border-radius: 15px;
        font-size: 0.85rem;
        font-weight: 700;
        min-width: 24px;
        text-align: center;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .queue-tab.active .queue-badge {
        background: white;
        animation: pulse 2s infinite;
    }

    /* Badge spécial pour restock */
    .queue-tab[data-queue="restock"] .queue-badge {
        background: rgba(16, 185, 129, 0.9);
        color: white;
    }

    .queue-tab[data-queue="restock"].active .queue-badge {
        background: #10b981;
        color: white;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }

    /* =========================
       CONTENU PRINCIPAL
    ========================= */
    .process-content {
        padding: 1rem;
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 1rem;
        min-height: calc(100vh - 180px);
    }

    /* =========================
       ZONE COMMANDE (GAUCHE)
    ========================= */
    .order-zone {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .order-card {
        background: white;
        border-radius: var(--border-radius-xl);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
        overflow: hidden;
        transition: var(--transition-smooth);
    }

    .order-card:hover {
        box-shadow: var(--shadow-elevated);
        transform: translateY(-2px);
    }

    .order-header {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .order-id {
        font-size: 1.4rem;
        font-weight: 700;
        color: #374151;
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
    }

    .order-status {
        padding: 6px 14px;
        border-radius: 18px;
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-nouvelle { background: linear-gradient(135deg, #ddd6fe 0%, #c4b5fd 100%); color: #5b21b6; }
    .status-datée { background: linear-gradient(135deg, #fef3c7 0%, #fde047 100%); color: #92400e; }
    .status-confirmée { background: linear-gradient(135deg, #dcfce7 0%, #86efac 100%); color: #166534; }
    .status-ancienne { background: linear-gradient(135deg, #fed7d7 0%, #fc8181 100%); color: #9b2c2c; }

    .order-meta {
        display: flex;
        gap: 1rem;
        margin-top: 0.75rem;
        flex-wrap: wrap;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 6px;
        color: #6b7280;
        font-size: 0.9rem;
    }

    .meta-icon {
        width: 18px;
        height: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f3f4f6;
        border-radius: 5px;
        color: #4b5563;
    }

    /* Section spéciale pour les commandes de retour en stock */
    .restock-info {
        background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
        border: 1px solid #86efac;
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .restock-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        color: #065f46;
        font-weight: 600;
        margin-bottom: 0.75rem;
    }

    .restock-message {
        background: white;
        padding: 0.75rem;
        border-radius: 8px;
        color: #374151;
        font-size: 0.9rem;
        line-height: 1.4;
        border-left: 4px solid #10b981;
    }

    /* =========================
       FORMULAIRE CLIENT
    ========================= */
    .customer-form {
        padding: 1rem 1.5rem;
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .form-group-full {
        grid-column: 1 / -1;
    }

    .form-label {
        font-weight: 600;
        color: #374151;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.9rem;
    }

    .form-label .required {
        color: #ef4444;
        font-size: 0.85rem;
    }

    .form-control {
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        padding: 10px 14px;
        transition: var(--transition-smooth);
        font-size: 0.9rem;
        background: #fafafa;
        width: 100%;
    }

    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        background: white;
        outline: none;
        transform: translateY(-1px);
    }

    .form-control:disabled {
        background: #f9fafb;
        color: #9ca3af;
        cursor: not-allowed;
    }

    /* =========================
       ZONE PANIER (DROITE)
    ========================= */
    .cart-zone {
        display: flex;
        flex-direction: column;
        position: relative;
    }

    .cart-card {
        background: white;
        border-radius: var(--border-radius-xl);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
        overflow: hidden;
        flex: 1;
        display: flex;
        flex-direction: column;
        transition: var(--transition-smooth);
    }

    .cart-card.collapsed {
        height: 50px;
        overflow: hidden;
    }

    .cart-card.collapsed .cart-body {
        display: none;
    }

    .cart-header {
        background: var(--process-success);
        color: white;
        padding: 0.75rem 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
        position: relative;
        z-index: 1;
    }

    .cart-title {
        font-size: 1.1rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .cart-count {
        background: rgba(255, 255, 255, 0.2);
        padding: 3px 10px;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .cart-toggle {
        background: none;
        border: none;
        color: white;
        font-size: 1.1rem;
        cursor: pointer;
        transition: var(--transition-smooth);
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
    }

    .cart-toggle:hover {
        background: rgba(255, 255, 255, 0.1);
        transform: scale(1.1);
    }

    .cart-body {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .product-search {
        padding: 0.75rem;
        border-bottom: 1px solid #e5e7eb;
        background: #f9fafb;
    }

    .search-wrapper {
        position: relative;
    }

    .search-input {
        width: 100%;
        padding: 10px 14px 10px 40px;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        background: white;
        font-size: 0.9rem;
        transition: var(--transition-smooth);
    }

    .search-input:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        outline: none;
    }

    .search-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #6b7280;
        font-size: 1rem;
    }

    .search-suggestions {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #e5e7eb;
        border-top: none;
        border-radius: 0 0 10px 10px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        z-index: 100;
        max-height: 200px;
        overflow-y: auto;
        display: none;
    }

    .suggestion-item {
        padding: 10px 14px;
        cursor: pointer;
        border-bottom: 1px solid #f3f4f6;
        transition: var(--transition-smooth);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .suggestion-item:hover {
        background: #f3f4f6;
        transform: translateX(3px);
    }

    .suggestion-item:last-child {
        border-bottom: none;
    }

    /* =========================
       PRODUITS DU PANIER
    ========================= */
    .cart-items {
        flex: 1;
        padding: 0.75rem;
        overflow-y: auto;
        max-height: 250px;
    }

    .cart-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem;
        background: #f9fafb;
        border-radius: 10px;
        margin-bottom: 0.5rem;
        border: 1px solid #e5e7eb;
        transition: var(--transition-smooth);
        animation: slideInRight 0.3s ease-out;
    }

    .cart-item:hover {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        transform: translateY(-1px);
        background: white;
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(15px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .item-info {
        flex: 1;
    }

    .item-name {
        font-weight: 600;
        color: #374151;
        margin-bottom: 3px;
        font-size: 0.9rem;
    }

    .item-price {
        color: #6b7280;
        font-size: 0.8rem;
        font-family: 'JetBrains Mono', monospace;
    }

    .quantity-controls {
        display: flex;
        align-items: center;
        gap: 6px;
        background: white;
        border-radius: 6px;
        padding: 3px;
        border: 1px solid #e5e7eb;
    }

    .qty-btn {
        width: 28px;
        height: 28px;
        border: none;
        background: #f3f4f6;
        border-radius: 5px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: var(--transition-smooth);
        color: #6b7280;
    }

    .qty-btn:hover {
        background: #e5e7eb;
        color: #374151;
        transform: scale(1.05);
    }

    .qty-input {
        width: 45px;
        text-align: center;
        border: none;
        background: transparent;
        font-weight: 600;
        color: #374151;
        font-size: 0.85rem;
    }

    .remove-btn {
        background: #fef2f2;
        color: #ef4444;
        border: none;
        border-radius: 6px;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: var(--transition-smooth);
    }

    .remove-btn:hover {
        background: #fee2e2;
        transform: scale(1.05);
    }

    .cart-empty {
        text-align: center;
        padding: 1.5rem;
        color: #6b7280;
    }

    .cart-empty i {
        font-size: 2.5rem;
        margin-bottom: 0.75rem;
        opacity: 0.5;
    }

    /* =========================
       RÉSUMÉ PANIER
    ========================= */
    .cart-summary {
        padding: 0.75rem;
        background: #f9fafb;
        border-top: 1px solid #e5e7eb;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }

    .summary-row:last-child {
        margin-bottom: 0;
        font-weight: 700;
        font-size: 1rem;
        color: #374151;
        padding-top: 0.5rem;
        border-top: 1px solid #e5e7eb;
    }

    .summary-label {
        color: #6b7280;
        font-weight: 500;
    }

    .summary-value {
        font-family: 'JetBrains Mono', monospace;
        font-weight: 600;
        color: #374151;
    }

    /* =========================
       ZONE ACTIONS
    ========================= */
    .actions-zone {
        grid-column: 1 / -1;
        background: white;
        border-radius: var(--border-radius-xl);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
        padding: 1rem;
        display: flex;
        justify-content: center;
        align-items: center;
        margin-top: 0.5rem;
    }

    .action-buttons {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        justify-content: center;
    }

    .action-btn {
        padding: 10px 18px;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        transition: var(--transition-smooth);
        display: flex;
        align-items: center;
        gap: 6px;
        position: relative;
        overflow: hidden;
        min-width: 120px;
        justify-content: center;
    }

    .action-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.2);
        transform: translateX(-100%);
        transition: transform 0.3s ease;
        z-index: 0;
    }

    .action-btn:hover::before {
        transform: translateX(0);
    }

    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }

    .action-btn span {
        position: relative;
        z-index: 1;
    }

    .btn-call { background: var(--process-warning); color: white; }
    .btn-confirm { background: var(--process-success); color: white; }
    .btn-cancel { background: var(--process-danger); color: white; }
    .btn-schedule { background: var(--process-info); color: white; }
    .btn-history { background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%); color: white; }
    .btn-reactivate { background: var(--process-restock); color: white; }

    .no-order {
        text-align: center;
        padding: 3rem;
        color: #6b7280;
        grid-column: 1 / -1;
    }

    .no-order i {
        font-size: 3.5rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .no-order h3 {
        font-size: 1.4rem;
        margin-bottom: 0.5rem;
        color: #374151;
    }

    /* =========================
       RESPONSIVE
    ========================= */
    @media (max-width: 1200px) {
        .process-content {
            grid-template-columns: 1fr;
            grid-template-rows: auto auto;
        }
        
        .cart-zone {
            grid-row: auto;
        }
        
        .cart-card {
            max-height: 350px;
        }
    }

    @media (max-width: 768px) {
        .process-header {
            flex-direction: column;
            gap: 0.75rem;
            text-align: center;
            padding: 0.75rem 1rem;
        }

        .process-icon {
            width: 50px;
            height: 50px;
            font-size: 2rem;
        }

        .process-content {
            padding: 0.75rem;
            gap: 0.75rem;
        }
        
        .queue-tabs {
            flex-direction: column;
            gap: 0.5rem;
            width: 100%;
        }
        
        .queue-tab {
            min-width: auto;
            padding: 0.6rem 0.8rem;
            font-size: 0.9rem;
        }

        .queue-icon {
            width: 24px;
            height: 24px;
            font-size: 1.1rem;
        }
        
        .form-grid {
            grid-template-columns: 1fr;
        }
        
        .actions-zone {
            padding: 0.75rem;
        }
        
        .action-buttons {
            width: 100%;
            justify-content: center;
            gap: 0.5rem;
        }

        .action-btn {
            min-width: 100px;
            padding: 8px 14px;
            font-size: 0.85rem;
        }

        .cart-header {
            padding: 0.6rem 0.8rem;
        }

        .cart-title {
            font-size: 1rem;
        }

        .cart-count {
            font-size: 0.75rem;
            padding: 2px 8px;
        }
    }

    /* =========================
       ANIMATIONS
    ========================= */
    .fade-in {
        animation: fadeIn 0.5s ease-out;
    }

    .slide-up {
        animation: slideUp 0.5s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* =========================
       ÉTATS DE CHARGEMENT
    ========================= */
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
        width: 24px;
        height: 24px;
        margin: -12px 0 0 -12px;
        border: 3px solid transparent;
        border-top: 3px solid #667eea;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* =========================
       SCROLLBAR CUSTOM
    ========================= */
    .cart-items::-webkit-scrollbar {
        width: 6px;
    }

    .cart-items::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 3px;
    }

    .cart-items::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border-radius: 3px;
    }

    .cart-items::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
    }
</style>
@endsection

@section('content')
<div class="process-container">
    <!-- Header avec onglets -->
    <div class="process-header">
        <div class="process-icon">
            <i class="fas fa-headset"></i>
        </div>
        
        <div class="queue-tabs">
            <div class="queue-tab active" data-queue="standard">
                <div class="queue-icon">
                    <i class="fas fa-phone"></i>
                </div>
                <div>
                    <div>File Standard</div>
                </div>
                <div class="queue-badge" id="standard-count">0</div>
            </div>
            
            <div class="queue-tab" data-queue="dated">
                <div class="queue-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div>
                    <div>File Datée</div>
                </div>
                <div class="queue-badge" id="dated-count">0</div>
            </div>
            
            <div class="queue-tab" data-queue="old">
                <div class="queue-icon">
                    <i class="fas fa-history"></i>
                </div>
                <div>
                    <div>File Ancienne</div>
                </div>
                <div class="queue-badge" id="old-count">0</div>
            </div>
            
            <!-- ONGLET RETOUR EN STOCK CORRIGÉ -->
            <div class="queue-tab" data-queue="restock">
                <div class="queue-icon">
                    <i class="fas fa-box-open"></i>
                </div>
                <div>
                    <div>Retour en Stock</div>
                </div>
                <div class="queue-badge" id="restock-count">0</div>
            </div>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="process-content" id="process-content">
        <!-- Message de chargement initial -->
        <div class="no-order fade-in" id="loading-message">
            <i class="fas fa-spinner fa-spin"></i>
            <h3>Chargement en cours...</h3>
            <p>Préparation de l'interface de traitement</p>
        </div>
        
        <!-- Message aucune commande -->
        <div class="no-order fade-in" id="no-order-message" style="display: none;">
            <i class="fas fa-inbox"></i>
            <h3>Aucune commande disponible</h3>
            <p>Il n'y a aucune commande à traiter dans cette file pour le moment.</p>
        </div>

        <!-- Zone principale (visible quand une commande est chargée) -->
        <div id="main-content" style="display: none; grid-column: 1 / -1;">
            <div class="process-content">
                <!-- Zone de la commande (gauche) -->
                <div class="order-zone">
                    <!-- Carte informations commande -->
                    <div class="order-card slide-up">
                        <div class="order-header">
                            <div>
                                <div class="order-id">
                                    <i class="fas fa-shopping-basket"></i>
                                    Commande #<span id="order-number">-</span>
                                </div>
                                <div class="order-meta">
                                    <div class="meta-item">
                                        <div class="meta-icon"><i class="fas fa-calendar"></i></div>
                                        <span id="order-date">-</span>
                                    </div>
                                    <div class="meta-item">
                                        <div class="meta-icon"><i class="fas fa-redo"></i></div>
                                        <span id="order-attempts">0 tentative(s)</span>
                                    </div>
                                    <div class="meta-item">
                                        <div class="meta-icon"><i class="fas fa-clock"></i></div>
                                        <span id="order-last-attempt">Jamais</span>
                                    </div>
                                </div>
                            </div>
                            <div class="order-status" id="order-status">Nouvelle</div>
                        </div>

                        <!-- Section spéciale pour retour en stock -->
                        <div class="restock-info" id="restock-info" style="display: none;">
                            <div class="restock-header">
                                <i class="fas fa-check-circle"></i>
                                <span>Commande suspendue - Retour en stock</span>
                            </div>
                            <div class="restock-message">
                                Cette commande était suspendue mais tous ses produits sont maintenant disponibles en stock. 
                                Elle peut être traitée normalement ou réactivée définitivement.
                            </div>
                        </div>

                        <!-- Formulaire client -->
                        <div class="customer-form">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-user"></i>
                                        Nom complet
                                    </label>
                                    <input type="text" class="form-control" id="customer_name" placeholder="Nom et prénom">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-phone"></i>
                                        Téléphone principal <span class="required">*</span>
                                    </label>
                                    <input type="tel" class="form-control" id="customer_phone" placeholder="+216 XX XXX XXX" disabled>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-phone-alt"></i>
                                        Téléphone secondaire
                                    </label>
                                    <input type="tel" class="form-control" id="customer_phone_2" placeholder="Numéro alternatif">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-map-marked-alt"></i>
                                        Gouvernorat
                                    </label>
                                    <select class="form-control" id="customer_governorate">
                                        <option value="">Sélectionner un gouvernorat</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-city"></i>
                                        Ville
                                    </label>
                                    <select class="form-control" id="customer_city">
                                        <option value="">Sélectionner une ville</option>
                                    </select>
                                </div>
                                
                                <div class="form-group form-group-full">
                                    <label class="form-label">
                                        <i class="fas fa-map-marker-alt"></i>
                                        Adresse complète
                                    </label>
                                    <textarea class="form-control" id="customer_address" rows="3" placeholder="Adresse détaillée"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Zone panier (droite) -->
                <div class="cart-zone">
                    <div class="cart-card slide-up">
                        <div class="cart-header" onclick="toggleCart()">
                            <div class="cart-title">
                                <i class="fas fa-shopping-cart"></i>
                                Panier
                            </div>
                            <div class="cart-count" id="cart-item-count">0 article(s)</div>
                            <button class="cart-toggle" id="cart-toggle">
                                <i class="fas fa-chevron-up"></i>
                            </button>
                        </div>

                        <div class="cart-body">
                            <!-- Recherche de produits -->
                            <div class="product-search">
                                <div class="search-wrapper">
                                    <i class="fas fa-search search-icon"></i>
                                    <input type="text" class="search-input" id="product-search" 
                                           placeholder="Rechercher un produit à ajouter..." autocomplete="off">
                                    <div class="search-suggestions" id="search-suggestions"></div>
                                </div>
                            </div>

                            <!-- Produits du panier -->
                            <div class="cart-items" id="cart-items">
                                <div class="cart-empty" id="cart-empty">
                                    <i class="fas fa-shopping-basket"></i>
                                    <h4>Panier vide</h4>
                                    <p>Les produits de la commande apparaîtront ici</p>
                                </div>
                            </div>

                            <!-- Résumé du panier -->
                            <div class="cart-summary" id="cart-summary" style="display: none;">
                                <div class="summary-row">
                                    <span class="summary-label">Sous-total:</span>
                                    <span class="summary-value" id="cart-subtotal">0.000 TND</span>
                                </div>
                                <div class="summary-row">
                                    <span class="summary-label">Livraison:</span>
                                    <span class="summary-value" id="cart-shipping">0.000 TND</span>
                                </div>
                                <div class="summary-row">
                                    <span class="summary-label">Total:</span>
                                    <span class="summary-value" id="cart-total">0.000 TND</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Zone d'actions -->
                <div class="actions-zone slide-up">
                    <div class="action-buttons" id="action-buttons">
                        <!-- Boutons standard -->
                        <button class="action-btn btn-call" id="btn-call">
                            <i class="fas fa-phone-slash"></i>
                            <span>Ne répond pas</span>
                        </button>
                        
                        <button class="action-btn btn-confirm" id="btn-confirm">
                            <i class="fas fa-check-circle"></i>
                            <span>Confirmer</span>
                        </button>
                        
                        <button class="action-btn btn-cancel" id="btn-cancel">
                            <i class="fas fa-times-circle"></i>
                            <span>Annuler</span>
                        </button>
                        
                        <button class="action-btn btn-schedule" id="btn-schedule">
                            <i class="fas fa-calendar-plus"></i>
                            <span>Dater</span>
                        </button>
                        
                        <!-- Bouton spécial pour retour en stock -->
                        <button class="action-btn btn-reactivate" id="btn-reactivate" style="display: none;">
                            <i class="fas fa-play-circle"></i>
                            <span>Réactiver définitivement</span>
                        </button>
                        
                        <button class="action-btn btn-history" id="btn-history">
                            <i class="fas fa-history"></i>
                            <span>Historique</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modales -->
@include('admin.process.modals')

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let currentQueue = 'standard';
    let currentOrder = null;
    let cartItems = [];
    let searchTimeout;
    
    // =========================
    // INITIALISATION
    // =========================
    
    function initialize() {
        // Vérifier jQuery et CSRF token
        if (typeof $ === 'undefined') {
            console.error('jQuery non chargé!');
            return;
        }
        
        const csrfToken = $('meta[name="csrf-token"]').attr('content');
        if (!csrfToken) {
            console.error('Token CSRF non trouvé!');
            return;
        }
        
        // Configuration AJAX globale
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            }
        });
        
        setupEventListeners();
        loadQueueCounts();
        loadCurrentQueue();
        loadRegions();
    }
    
    function setupEventListeners() {
        // Onglets de files
        $('.queue-tab').on('click', function() {
            const queue = $(this).data('queue');
            if (queue !== currentQueue) {
                switchQueue(queue);
            }
        });
        
        // Recherche de produits
        $('#product-search').on('input', function() {
            const query = $(this).val().trim();
            clearTimeout(searchTimeout);
            
            if (query.length >= 2) {
                searchTimeout = setTimeout(() => searchProducts(query), 300);
            } else {
                hideSearchSuggestions();
            }
        });
        
        // Masquer suggestions en cliquant ailleurs
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.search-wrapper').length) {
                hideSearchSuggestions();
            }
        });
        
        // Boutons d'action
        $('#btn-call').on('click', () => showActionModal('call'));
        $('#btn-confirm').on('click', () => showActionModal('confirm'));
        $('#btn-cancel').on('click', () => showActionModal('cancel'));
        $('#btn-schedule').on('click', () => showActionModal('schedule'));
        $('#btn-reactivate').on('click', () => showActionModal('reactivate'));
        $('#btn-history').on('click', () => showHistoryModal());
        
        // Changement de gouvernorat
        $('#customer_governorate').on('change', function() {
            const regionId = $(this).val();
            loadCities(regionId);
        });
    }
    
    // =========================
    // GESTION DES FILES
    // =========================
    
    let isLoadingQueue = false; // Flag pour éviter les chargements multiples
    let isLoadingCounts = false; // Flag pour éviter les compteurs multiples
    
    function loadQueueCounts() {
        // Éviter les appels multiples
        if (isLoadingCounts) {
            console.log('Chargement des compteurs déjà en cours, ignoré');
            return;
        }
        
        isLoadingCounts = true;
        
        $.get('/admin/process/counts')
            .done(function(data) {
                console.log('Compteurs reçus:', data);
                $('#standard-count').text(data.standard || 0);
                $('#dated-count').text(data.dated || 0);
                $('#old-count').text(data.old || 0);
                $('#restock-count').text(data.restock || 0);
            })
            .fail(function(xhr, status, error) {
                console.error('Erreur compteurs:', error, xhr.responseText);
                showNotification('Erreur lors du chargement des compteurs', 'error');
            })
            .always(function() {
                isLoadingCounts = false;
            });
    }
    
    function switchQueue(queue) {
        console.log('Changement de file vers:', queue);
        
        // Éviter les changements multiples
        if (isLoadingQueue) {
            console.log('Changement de file déjà en cours, ignoré');
            return;
        }
        
        // Mettre à jour l'UI
        $('.queue-tab').removeClass('active');
        $(`.queue-tab[data-queue="${queue}"]`).addClass('active');
        
        currentQueue = queue;
        currentOrder = null;
        
        // Réinitialiser l'affichage
        showLoading();
        
        // Charger la nouvelle file avec délai pour éviter les conflits
        setTimeout(() => {
            loadCurrentQueue();
        }, 100);
    }
    
    function loadCurrentQueue() {
        console.log('Chargement de la file:', currentQueue);
        
        // Éviter les chargements multiples
        if (isLoadingQueue) {
            console.log('Chargement déjà en cours pour', currentQueue);
            return;
        }
        
        isLoadingQueue = true;
        
        $.get(`/admin/process/${currentQueue}`)
            .done(function(data) {
                console.log('Données reçues pour', currentQueue, ':', data);
                
                if (data.hasOrder && data.order) {
                    currentOrder = data.order;
                    displayOrder(data.order);
                    showMainContent();
                    console.log('Commande affichée:', currentOrder.id);
                } else {
                    console.log('Aucune commande disponible pour', currentQueue);
                    showNoOrderMessage();
                }
            })
            .fail(function(xhr, status, error) {
                console.error('Erreur lors du chargement de', currentQueue, ':', error);
                console.error('Réponse complète:', xhr.responseText);
                
                // Afficher l'erreur détaillée si disponible
                let errorMsg = 'Erreur lors du chargement de la commande';
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.error) {
                        errorMsg = response.error;
                    }
                } catch (e) {
                    // Ignorer les erreurs de parsing JSON
                }
                
                showNotification(errorMsg, 'error');
                showNoOrderMessage();
            })
            .always(function() {
                isLoadingQueue = false;
            });
    }
    
    // =========================
    // AFFICHAGE DES COMMANDES
    // =========================
    
    function displayOrder(order) {
        console.log('Affichage de la commande:', order);
        
        if (!order || !order.id) {
            console.error('Commande invalide reçue pour affichage');
            showNoOrderMessage();
            return;
        }
        
        // Informations de base
        $('#order-number').text(order.id);
        $('#order-date').text(formatDate(order.created_at));
        $('#order-attempts').text(`${order.attempts_count || 0} tentative(s)`);
        $('#order-last-attempt').text(order.last_attempt_at ? formatDate(order.last_attempt_at) : 'Jamais');
        
        // Statut
        const statusElement = $('#order-status');
        statusElement.removeClass().addClass('order-status').addClass(`status-${order.status}`);
        statusElement.text(capitalizeFirst(order.status));
        
        // Affichage spécial pour retour en stock
        if (currentQueue === 'restock') {
            $('#restock-info').show();
            $('#btn-reactivate').show();
            // Modifier le message pour retour en stock
            $('#btn-call span').text('Reporter');
            console.log('Interface restock activée pour commande:', order.id);
        } else {
            $('#restock-info').hide();
            $('#btn-reactivate').hide();
            $('#btn-call span').text('Ne répond pas');
        }
        
        // Formulaire client
        $('#customer_name').val(order.customer_name || '');
        $('#customer_phone').val(order.customer_phone || '');
        $('#customer_phone_2').val(order.customer_phone_2 || '');
        $('#customer_address').val(order.customer_address || '');
        
        // Région et ville
        if (order.customer_governorate) {
            $('#customer_governorate').val(order.customer_governorate);
            loadCities(order.customer_governorate, order.customer_city);
        } else {
            $('#customer_governorate').val('');
            $('#customer_city').html('<option value="">Sélectionner une ville</option>');
        }
        
        // Panier
        cartItems = [];
        if (order.items && Array.isArray(order.items)) {
            cartItems = order.items.map(item => ({
                product_id: item.product_id,
                quantity: parseFloat(item.quantity) || 0,
                unit_price: parseFloat(item.unit_price) || 0,
                total_price: parseFloat(item.total_price) || 0,
                product: item.product ? {
                    id: item.product.id,
                    name: item.product.name,
                    price: parseFloat(item.product.price) || 0,
                    stock: parseInt(item.product.stock) || 0
                } : null
            }));
        }
        
        updateCartDisplay();
        console.log('Commande affichée avec succès:', order.id, 'Items:', cartItems.length);
    }
    
    function showMainContent() {
        $('#loading-message').hide();
        $('#no-order-message').hide();
        $('#main-content').show().addClass('fade-in');
    }
    
    function showNoOrderMessage() {
        $('#loading-message').hide();
        $('#main-content').hide();
        $('#no-order-message').show().addClass('fade-in');
    }
    
    function showLoading() {
        $('#main-content').hide();
        $('#no-order-message').hide();
        $('#loading-message').show().addClass('fade-in');
    }
    
    // =========================
    // GESTION DU PANIER
    // =========================
    
    function updateCartDisplay() {
        const cartItemsContainer = $('#cart-items');
        const cartEmpty = $('#cart-empty');
        const cartSummary = $('#cart-summary');
        const cartCount = $('#cart-item-count');
        
        if (cartItems.length === 0) {
            cartEmpty.show();
            cartSummary.hide();
            cartCount.text('0 article');
        } else {
            cartEmpty.hide();
            cartSummary.show();
            cartCount.text(`${cartItems.length} article${cartItems.length > 1 ? 's' : ''}`);
            
            // Vider le conteneur
            cartItemsContainer.find('.cart-item').remove();
            
            // Ajouter les items
            cartItems.forEach(item => {
                const cartItemElement = createCartItemElement(item);
                cartItemsContainer.append(cartItemElement);
            });
            
            updateCartSummary();
        }
    }
    
    function createCartItemElement(item) {
        const element = $(`
            <div class="cart-item" data-product-id="${item.product_id}">
                <div class="item-info">
                    <div class="item-name">${item.product?.name || 'Produit inconnu'}</div>
                    <div class="item-price">${parseFloat(item.unit_price || 0).toFixed(3)} TND × ${item.quantity}</div>
                </div>
                <div class="quantity-controls">
                    <button type="button" class="qty-btn" data-action="decrease">
                        <i class="fas fa-minus"></i>
                    </button>
                    <input type="number" class="qty-input" value="${item.quantity}" min="1" max="${item.product?.stock || 999}">
                    <button type="button" class="qty-btn" data-action="increase">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <button type="button" class="remove-btn" data-action="remove">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `);
        
        // Event listeners
        element.find('.qty-btn[data-action="decrease"]').on('click', () => {
            updateItemQuantity(item.product_id, item.quantity - 1);
        });
        
        element.find('.qty-btn[data-action="increase"]').on('click', () => {
            updateItemQuantity(item.product_id, item.quantity + 1);
        });
        
        element.find('.qty-input').on('change', function() {
            const newQty = parseInt($(this).val()) || 1;
            updateItemQuantity(item.product_id, newQty);
        });
        
        element.find('.remove-btn').on('click', () => {
            removeFromCart(item.product_id);
        });
        
        return element;
    }
    
    function updateItemQuantity(productId, newQuantity) {
        const item = cartItems.find(i => i.product_id == productId);
        if (item) {
            const maxStock = item.product?.stock || 999;
            item.quantity = Math.max(1, Math.min(newQuantity, maxStock));
            item.total_price = item.quantity * item.unit_price;
            updateCartDisplay();
        }
    }
    
    function removeFromCart(productId) {
        cartItems = cartItems.filter(item => item.product_id != productId);
        updateCartDisplay();
    }
    
    function addToCart(product) {
        const existingItem = cartItems.find(item => item.product_id == product.id);
        
        if (existingItem) {
            existingItem.quantity += 1;
            existingItem.total_price = existingItem.quantity * existingItem.unit_price;
        } else {
            cartItems.push({
                product_id: product.id,
                quantity: 1,
                unit_price: parseFloat(product.price),
                total_price: parseFloat(product.price),
                product: {
                    id: product.id,
                    name: product.name,
                    price: product.price,
                    stock: product.stock
                }
            });
        }
        
        updateCartDisplay();
        hideSearchSuggestions();
        $('#product-search').val('');
        
        showNotification(`${product.name} ajouté au panier`, 'success');
    }
    
    function updateCartSummary() {
        let subtotal = 0;
        
        if (cartItems && Array.isArray(cartItems)) {
            subtotal = cartItems.reduce((sum, item) => {
                const itemTotal = parseFloat(item.total_price) || 0;
                return sum + itemTotal;
            }, 0);
        }
        
        const shipping = parseFloat(currentOrder?.shipping_cost) || 0;
        const total = subtotal + shipping;
        
        $('#cart-subtotal').text(subtotal.toFixed(3) + ' TND');
        $('#cart-shipping').text(shipping.toFixed(3) + ' TND');
        $('#cart-total').text(total.toFixed(3) + ' TND');
    }
    
    // =========================
    // TOGGLE PANIER
    // =========================
    
    window.toggleCart = function() {
        const cartCard = $('.cart-card');
        const cartToggle = $('#cart-toggle i');
        
        cartCard.toggleClass('collapsed');
        
        if (cartCard.hasClass('collapsed')) {
            cartToggle.removeClass('fa-chevron-up').addClass('fa-chevron-down');
        } else {
            cartToggle.removeClass('fa-chevron-down').addClass('fa-chevron-up');
        }
    }
    
    // =========================
    // RECHERCHE DE PRODUITS
    // =========================
    
    function searchProducts(query) {
        $.get('/admin/orders/search-products', { search: query })
            .done(function(products) {
                showSearchSuggestions(products);
            })
            .fail(function(xhr) {
                console.error('Erreur lors de la recherche de produits');
            });
    }
    
    function showSearchSuggestions(products) {
        const suggestions = $('#search-suggestions');
        suggestions.empty();
        
        if (products.length === 0) {
            suggestions.html('<div class="suggestion-item">Aucun produit trouvé</div>');
        } else {
            products.forEach(product => {
                const item = $(`
                    <div class="suggestion-item" data-product-id="${product.id}">
                        <div>
                            <strong>${product.name}</strong>
                            <br><small style="color: #6b7280;">Stock: ${product.stock}</small>
                        </div>
                        <div style="color: #10b981; font-weight: 600;">${parseFloat(product.price).toFixed(3)} TND</div>
                    </div>
                `);
                
                item.on('click', function() {
                    addToCart(product);
                });
                
                suggestions.append(item);
            });
        }
        
        suggestions.show();
    }
    
    function hideSearchSuggestions() {
        $('#search-suggestions').hide();
    }
    
    // =========================
    // GESTION GÉOGRAPHIQUE
    // =========================
    
    function loadRegions() {
        $.get('/admin/orders/get-regions')
            .done(function(regions) {
                const select = $('#customer_governorate');
                select.html('<option value="">Sélectionner un gouvernorat</option>');
                
                regions.forEach(region => {
                    select.append(`<option value="${region.id}">${region.name}</option>`);
                });
            })
            .fail(function(xhr) {
                console.error('Erreur lors du chargement des régions');
            });
    }
    
    function loadCities(regionId, selectedCityId = null) {
        if (!regionId) {
            $('#customer_city').html('<option value="">Sélectionner une ville</option>');
            return;
        }
        
        $.get('/admin/orders/get-cities', { region_id: regionId })
            .done(function(cities) {
                const select = $('#customer_city');
                select.html('<option value="">Sélectionner une ville</option>');
                
                cities.forEach(city => {
                    const selected = selectedCityId == city.id ? 'selected' : '';
                    select.append(`<option value="${city.id}" ${selected}>${city.name}</option>`);
                });
            })
            .fail(function(xhr) {
                console.error('Erreur lors du chargement des villes');
            });
    }
    
    // =========================
    // ACTIONS DE TRAITEMENT
    // =========================
    
    function showActionModal(action) {
        if (!currentOrder) {
            showNotification('Aucune commande sélectionnée', 'error');
            return;
        }
        
        switch (action) {
            case 'call':
                showCallModal();
                break;
            case 'confirm':
                showConfirmModal();
                break;
            case 'cancel':
                showCancelModal();
                break;
            case 'schedule':
                showScheduleModal();
                break;
            case 'reactivate':
                showReactivateModal();
                break;
        }
    }
    
    function showCallModal() {
        const modal = $('#callModal');
        $('#call-notes').val('');
        modal.modal('show');
    }
    
    function showConfirmModal() {
        // Validation des champs requis
        const requiredFields = ['customer_name', 'customer_phone', 'customer_address'];
        let isValid = true;
        let missingFields = [];
        
        requiredFields.forEach(field => {
            const value = $(`#${field}`).val().trim();
            if (!value) {
                isValid = false;
                missingFields.push(field);
            }
        });
        
        if (!isValid) {
            showNotification('Veuillez remplir tous les champs obligatoires avant de confirmer', 'error');
            return;
        }
        
        if (cartItems.length === 0) {
            showNotification('Veuillez ajouter au moins un produit au panier', 'error');
            return;
        }
        
        // Calculer le prix total
        const subtotal = cartItems.reduce((sum, item) => sum + (parseFloat(item.total_price) || 0), 0);
        $('#confirm-price').val(subtotal.toFixed(3));
        $('#confirm-notes').val('');
        
        const modal = $('#confirmModal');
        modal.modal('show');
    }
    
    function showCancelModal() {
        $('#cancel-notes').val('');
        const modal = $('#cancelModal');
        modal.modal('show');
    }
    
    function showScheduleModal() {
        $('#schedule-notes').val('');
        $('#schedule-date').val('');
        
        // Date minimum = demain
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        $('#schedule-date').attr('min', tomorrow.toISOString().split('T')[0]);
        
        const modal = $('#scheduleModal');
        modal.modal('show');
    }
    
    function showReactivateModal() {
        if (currentQueue !== 'restock') {
            showNotification('Cette action n\'est disponible que dans la file retour en stock', 'error');
            return;
        }
        
        $('#reactivate-notes').val('');
        const modal = $('#reactivateModal');
        modal.modal('show');
    }
    
    function showHistoryModal() {
        if (!currentOrder) {
            showNotification('Aucune commande sélectionnée', 'error');
            return;
        }
        
        // Charger l'historique
        $.get(`/admin/orders/${currentOrder.id}/history-modal`)
            .done(function(history) {
                $('#history-content').html(history);
                $('#historyModal').modal('show');
            })
            .fail(function(xhr) {
                showNotification('Erreur lors du chargement de l\'historique', 'error');
            });
    }
    
    // =========================
    // UTILITAIRES
    // =========================
    
    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        
        const date = new Date(dateString);
        const now = new Date();
        const diff = now - date;
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        
        if (days === 0) {
            return 'Aujourd\'hui à ' + date.toLocaleTimeString('fr-FR', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });
        } else if (days === 1) {
            return 'Hier à ' + date.toLocaleTimeString('fr-FR', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });
        } else if (days < 7) {
            return `Il y a ${days} jour${days > 1 ? 's' : ''}`;
        } else {
            return date.toLocaleDateString('fr-FR') + ' à ' + date.toLocaleTimeString('fr-FR', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });
        }
    }
    
    function capitalizeFirst(str) {
        if (!str) return '';
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
    
    function showNotification(message, type = 'info') {
        const alertClass = {
            'success': 'alert-success',
            'error': 'alert-danger',
            'warning': 'alert-warning',
            'info': 'alert-info'
        }[type] || 'alert-info';
        
        const alert = $(`
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
                 style="top: 100px; right: 20px; z-index: 9999; min-width: 300px;">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        
        $('body').append(alert);
        
        setTimeout(() => {
            alert.fadeOut(() => alert.remove());
        }, 5000);
    }
    
    // =========================
    // INITIALISATION
    // =========================
    
    // Démarrer l'application
    initialize();
    
    // Actualiser les compteurs toutes les 60 secondes (réduit la fréquence)
    setInterval(function() {
        // Ne pas actualiser pendant les chargements
        if (!isLoadingQueue && !isLoadingCounts) {
            loadQueueCounts();
        }
    }, 60000);
    
    // Actualiser la file courante toutes les 2 minutes
    setInterval(function() {
        // Ne pas actualiser pendant les chargements ou si une modal est ouverte
        if (!isLoadingQueue && !isLoadingCounts && $('.modal:visible').length === 0) {
            console.log('Actualisation automatique de la file', currentQueue);
            loadCurrentQueue();
        }
    }, 120000);
    
    // Exposer les fonctions pour les modales
    window.processAction = function(action, formData) {
        if (!currentOrder) {
            showNotification('Aucune commande sélectionnée', 'error');
            return;
        }
        
        console.log('Traitement action:', action, 'pour queue:', currentQueue, 'commande:', currentOrder.id);
        
        // Préparation des données
        const requestData = {
            action: action,
            queue: currentQueue,
            ...formData
        };
        
        // Ajouter les données du panier si nécessaire
        if (action === 'confirm') {
            requestData.cart_items = cartItems;
            
            // Données client
            requestData.customer_name = $('#customer_name').val();
            requestData.customer_phone_2 = $('#customer_phone_2').val();
            requestData.customer_governorate = $('#customer_governorate').val();
            requestData.customer_city = $('#customer_city').val();
            requestData.customer_address = $('#customer_address').val();
        }
        
        // Désactiver les boutons
        $('.action-btn').prop('disabled', true).addClass('loading');
        
        // Envoyer la requête
        $.ajax({
            url: `/admin/process/action/${currentOrder.id}`,
            method: 'POST',
            data: requestData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        })
        .done(function(response) {
            console.log('Action réussie:', response);
            showNotification('Action traitée avec succès!', 'success');
            
            // Fermer les modales
            $('.modal').modal('hide');
            
            // Attendre un peu avant de recharger pour éviter les conflits
            setTimeout(() => {
                console.log('Rechargement après action réussie');
                loadQueueCounts();
                
                // Recharger la file après un délai supplémentaire
                setTimeout(() => {
                    loadCurrentQueue();
                }, 500);
            }, 1000);
        })
        .fail(function(xhr, status, error) {
            console.error('Erreur action:', error);
            console.error('Réponse:', xhr.responseText);
            
            let errorMessage = 'Erreur lors du traitement de l\'action';
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.error) {
                    errorMessage = response.error;
                }
            } catch (e) {
                // Ignorer les erreurs de parsing
            }
            
            showNotification(errorMessage, 'error');
            
            // Réactiver les boutons en cas d'erreur
            $('.action-btn').prop('disabled', false).removeClass('loading');
        });
    };
});
</script>
@endsection