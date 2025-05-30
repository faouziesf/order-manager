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
        margin: var(--spacing-sm);
        min-height: calc(100vh - 140px);
        overflow: hidden;
    }

    /* =========================
       HEADER AVEC ONGLETS
    ========================= */
    .process-header {
        background: var(--process-primary);
        padding: var(--spacing-lg) var(--spacing-xl);
        position: relative;
        overflow: hidden;
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

    .process-title {
        color: white;
        font-size: 2.5rem;
        font-weight: 800;
        margin: 0 0 var(--spacing-md) 0;
        position: relative;
        z-index: 2;
        display: flex;
        align-items: center;
        gap: var(--spacing-md);
    }

    .process-subtitle {
        color: rgba(255, 255, 255, 0.9);
        font-size: 1.1rem;
        font-weight: 400;
        position: relative;
        z-index: 2;
        margin-bottom: var(--spacing-lg);
    }

    /* =========================
       ONGLETS MODERNES
    ========================= */
    .queue-tabs {
        display: flex;
        gap: var(--spacing-sm);
        position: relative;
        z-index: 2;
        margin-top: var(--spacing-lg);
    }

    .queue-tab {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: var(--border-radius-xl);
        padding: var(--spacing-md) var(--spacing-lg);
        color: rgba(255, 255, 255, 0.8);
        cursor: pointer;
        transition: var(--transition-smooth);
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
        font-weight: 600;
        font-size: 1.1rem;
        position: relative;
        overflow: hidden;
        min-width: 180px;
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
        transition: transform 0.4s ease;
        z-index: -1;
    }

    .queue-tab:hover::before,
    .queue-tab.active::before {
        transform: translateX(0);
    }

    .queue-tab:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-elevated);
        color: white;
    }

    .queue-tab.active {
        background: rgba(255, 255, 255, 0.25);
        color: white;
        border-color: rgba(255, 255, 255, 0.4);
        transform: translateY(-2px);
        box-shadow: var(--shadow-elevated);
    }

    .queue-icon {
        font-size: 1.3rem;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 12px;
    }

    .queue-badge {
        background: rgba(255, 255, 255, 0.9);
        color: #4f46e5;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 700;
        min-width: 28px;
        text-align: center;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .queue-tab.active .queue-badge {
        background: white;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }

    /* =========================
       CONTENU PRINCIPAL
    ========================= */
    .process-content {
        padding: var(--spacing-xl);
        display: grid;
        grid-template-columns: 1fr 400px;
        grid-template-rows: auto 1fr;
        gap: var(--spacing-xl);
        min-height: calc(100vh - 300px);
    }

    /* =========================
       ZONE COMMANDE (GAUCHE)
    ========================= */
    .order-zone {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-lg);
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
        padding: var(--spacing-lg);
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .order-id {
        font-size: 1.5rem;
        font-weight: 700;
        color: #374151;
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
    }

    .order-status {
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-nouvelle { background: linear-gradient(135deg, #ddd6fe 0%, #c4b5fd 100%); color: #5b21b6; }
    .status-datée { background: linear-gradient(135deg, #fef3c7 0%, #fde047 100%); color: #92400e; }
    .status-confirmée { background: linear-gradient(135deg, #dcfce7 0%, #86efac 100%); color: #166534; }
    .status-ancienne { background: linear-gradient(135deg, #fed7d7 0%, #fc8181 100%); color: #9b2c2c; }

    .order-meta {
        display: flex;
        gap: var(--spacing-lg);
        margin-top: var(--spacing-md);
        flex-wrap: wrap;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #6b7280;
        font-size: 0.95rem;
    }

    .meta-icon {
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f3f4f6;
        border-radius: 6px;
        color: #4b5563;
    }

    /* =========================
       FORMULAIRE CLIENT
    ========================= */
    .customer-form {
        padding: var(--spacing-lg);
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: var(--spacing-md);
        margin-bottom: var(--spacing-lg);
    }

    .form-group-full {
        grid-column: 1 / -1;
    }

    .form-label {
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.95rem;
    }

    .form-label .required {
        color: #ef4444;
        font-size: 0.9rem;
    }

    .form-control {
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        padding: 12px 16px;
        transition: var(--transition-smooth);
        font-size: 0.95rem;
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
        gap: var(--spacing-lg);
        grid-row: 1 / -1;
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
    }

    .cart-header {
        background: var(--process-success);
        color: white;
        padding: var(--spacing-lg);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .cart-title {
        font-size: 1.25rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
    }

    .cart-count {
        background: rgba(255, 255, 255, 0.2);
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .product-search {
        padding: var(--spacing-lg);
        border-bottom: 1px solid #e5e7eb;
        background: #f9fafb;
    }

    .search-wrapper {
        position: relative;
    }

    .search-input {
        width: 100%;
        padding: 12px 16px 12px 48px;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        background: white;
        font-size: 0.95rem;
        transition: var(--transition-smooth);
    }

    .search-input:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        outline: none;
    }

    .search-icon {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #6b7280;
        font-size: 1.1rem;
    }

    .search-suggestions {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #e5e7eb;
        border-top: none;
        border-radius: 0 0 12px 12px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        z-index: 100;
        max-height: 250px;
        overflow-y: auto;
        display: none;
    }

    .suggestion-item {
        padding: 12px 16px;
        cursor: pointer;
        border-bottom: 1px solid #f3f4f6;
        transition: var(--transition-smooth);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .suggestion-item:hover {
        background: #f3f4f6;
        transform: translateX(4px);
    }

    .suggestion-item:last-child {
        border-bottom: none;
    }

    /* =========================
       PRODUITS DU PANIER
    ========================= */
    .cart-items {
        flex: 1;
        padding: var(--spacing-lg);
        overflow-y: auto;
        max-height: 400px;
    }

    .cart-item {
        display: flex;
        align-items: center;
        gap: var(--spacing-md);
        padding: var(--spacing-md);
        background: #f9fafb;
        border-radius: 12px;
        margin-bottom: var(--spacing-sm);
        border: 1px solid #e5e7eb;
        transition: var(--transition-smooth);
        animation: slideInRight 0.3s ease-out;
    }

    .cart-item:hover {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
        background: white;
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(20px);
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
        margin-bottom: 4px;
        font-size: 0.95rem;
    }

    .item-price {
        color: #6b7280;
        font-size: 0.9rem;
        font-family: 'JetBrains Mono', monospace;
    }

    .quantity-controls {
        display: flex;
        align-items: center;
        gap: 8px;
        background: white;
        border-radius: 8px;
        padding: 4px;
        border: 1px solid #e5e7eb;
    }

    .qty-btn {
        width: 32px;
        height: 32px;
        border: none;
        background: #f3f4f6;
        border-radius: 6px;
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
        transform: scale(1.1);
    }

    .qty-input {
        width: 50px;
        text-align: center;
        border: none;
        background: transparent;
        font-weight: 600;
        color: #374151;
        font-size: 0.9rem;
    }

    .remove-btn {
        background: #fef2f2;
        color: #ef4444;
        border: none;
        border-radius: 8px;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: var(--transition-smooth);
    }

    .remove-btn:hover {
        background: #fee2e2;
        transform: scale(1.1);
    }

    .cart-empty {
        text-align: center;
        padding: var(--spacing-xl);
        color: #6b7280;
    }

    .cart-empty i {
        font-size: 3rem;
        margin-bottom: var(--spacing-md);
        opacity: 0.5;
    }

    /* =========================
       RÉSUMÉ PANIER
    ========================= */
    .cart-summary {
        padding: var(--spacing-lg);
        background: #f9fafb;
        border-top: 1px solid #e5e7eb;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: var(--spacing-sm);
        font-size: 0.95rem;
    }

    .summary-row:last-child {
        margin-bottom: 0;
        font-weight: 700;
        font-size: 1.1rem;
        color: #374151;
        padding-top: var(--spacing-sm);
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
        padding: var(--spacing-xl);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: var(--spacing-xl);
    }

    .action-buttons {
        display: flex;
        gap: var(--spacing-md);
        flex-wrap: wrap;
    }

    .action-btn {
        padding: 14px 24px;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        transition: var(--transition-smooth);
        display: flex;
        align-items: center;
        gap: 8px;
        position: relative;
        overflow: hidden;
        min-width: 140px;
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
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
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

    .no-order {
        text-align: center;
        padding: var(--spacing-xl) * 2;
        color: #6b7280;
        grid-column: 1 / -1;
    }

    .no-order i {
        font-size: 4rem;
        margin-bottom: var(--spacing-lg);
        opacity: 0.5;
    }

    .no-order h3 {
        font-size: 1.5rem;
        margin-bottom: var(--spacing-sm);
        color: #374151;
    }

    /* Debug info */
    .debug-info {
        position: fixed;
        top: 10px;
        right: 10px;
        background: rgba(0,0,0,0.8);
        color: white;
        padding: 10px;
        border-radius: 5px;
        font-size: 12px;
        z-index: 10000;
        max-width: 300px;
        font-family: monospace;
    }

    /* =========================
       RESPONSIVE
    ========================= */
    @media (max-width: 1400px) {
        .process-content {
            grid-template-columns: 1fr 350px;
            gap: var(--spacing-lg);
        }
    }

    @media (max-width: 1200px) {
        .process-content {
            grid-template-columns: 1fr;
            grid-template-rows: auto auto auto;
        }
        
        .cart-zone {
            grid-row: auto;
            flex-direction: row;
        }
        
        .cart-card {
            flex: 1;
            max-height: 400px;
        }
    }

    @media (max-width: 768px) {
        .process-content {
            padding: var(--spacing-md);
            gap: var(--spacing-md);
        }
        
        .queue-tabs {
            flex-direction: column;
            gap: var(--spacing-xs);
        }
        
        .queue-tab {
            min-width: auto;
        }
        
        .form-grid {
            grid-template-columns: 1fr;
        }
        
        .actions-zone {
            flex-direction: column;
            gap: var(--spacing-lg);
        }
        
        .action-buttons {
            width: 100%;
            justify-content: center;
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
        <h1 class="process-title">
            <i class="fas fa-headset"></i>
            Interface de Traitement
        </h1>
        <p class="process-subtitle">
            Gérez efficacement vos commandes avec une interface optimisée pour le contact client
        </p>
        
        <div class="queue-tabs">
            <div class="queue-tab active" data-queue="standard">
                <div class="queue-icon">
                    <i class="fas fa-phone"></i>
                </div>
                <div>
                    <div>File Standard</div>
                    <small>Nouvelles commandes</small>
                </div>
                <div class="queue-badge" id="standard-count">0</div>
            </div>
            
            <div class="queue-tab" data-queue="dated">
                <div class="queue-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div>
                    <div>File Datée</div>
                    <small>Rappels programmés</small>
                </div>
                <div class="queue-badge" id="dated-count">0</div>
            </div>
            
            <div class="queue-tab" data-queue="old">
                <div class="queue-icon">
                    <i class="fas fa-history"></i>
                </div>
                <div>
                    <div>File Ancienne</div>
                    <small>Commandes anciennes</small>
                </div>
                <div class="queue-badge" id="old-count">0</div>
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
        <div id="main-content" style="display: none;">
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
                    <div class="cart-header">
                        <div class="cart-title">
                            <i class="fas fa-shopping-cart"></i>
                            Panier
                        </div>
                        <div class="cart-count" id="cart-item-count">0 article(s)</div>
                    </div>

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

            <!-- Zone d'actions -->
            <div class="actions-zone slide-up">
                <div>
                    <h4 style="margin: 0; color: #374151; font-weight: 700;">Actions de traitement</h4>
                    <p style="margin: 8px 0 0 0; color: #6b7280;">Choisissez l'action appropriée selon la réponse du client</p>
                </div>
                
                <div class="action-buttons">
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
                    
                    <button class="action-btn btn-history" id="btn-history">
                        <i class="fas fa-history"></i>
                        <span>Historique</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Debug Info -->
<div id="debug-info" class="debug-info" style="display: none;">
    <div>Status: <span id="debug-status">Initializing...</span></div>
    <div>Current Queue: <span id="debug-queue">-</span></div>
    <div>Last Request: <span id="debug-request">-</span></div>
    <div>Last Response: <span id="debug-response">-</span></div>
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
    let debugMode = true; // Activer le mode debug
    
    // =========================
    // FONCTIONS DE DEBUG
    // =========================
    
    function debug(message, data = null) {
        if (debugMode) {
            console.log('🔍 DEBUG:', message, data || '');
            updateDebugInfo('status', message);
        }
    }
    
    function updateDebugInfo(key, value) {
        if (debugMode) {
            $(`#debug-${key}`).text(value);
            $('#debug-info').show();
        }
    }
    
    // =========================
    // INITIALISATION
    // =========================
    
    function initialize() {
        debug('Initialisation de l\'interface de traitement');
        
        // Vérifier jQuery et CSRF token
        if (typeof $ === 'undefined') {
            debug('ERREUR: jQuery non chargé!');
            return;
        }
        
        const csrfToken = $('meta[name="csrf-token"]').attr('content');
        if (!csrfToken) {
            debug('ERREUR: Token CSRF non trouvé!');
            return;
        }
        
        debug('jQuery et CSRF OK');
        updateDebugInfo('queue', currentQueue);
        
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
        debug('Configuration des event listeners');
        
        // Onglets de files
        $('.queue-tab').on('click', function() {
            const queue = $(this).data('queue');
            debug(`Clic sur onglet: ${queue}`);
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
        $('#btn-history').on('click', () => showHistoryModal());
        
        // Changement de gouvernorat
        $('#customer_governorate').on('change', function() {
            const regionId = $(this).val();
            loadCities(regionId);
        });
        
        debug('Event listeners configurés');
    }
    
    // =========================
    // GESTION DES FILES
    // =========================
    
    function loadQueueCounts() {
        debug('Chargement des compteurs de files');
        updateDebugInfo('request', 'GET /admin/process/counts');
        
        $.get('/admin/process/counts')
            .done(function(data) {
                debug('Compteurs chargés avec succès', data);
                updateDebugInfo('response', 'Compteurs OK');
                
                $('#standard-count').text(data.standard || 0);
                $('#dated-count').text(data.dated || 0);
                $('#old-count').text(data.old || 0);
            })
            .fail(function(xhr, status, error) {
                debug('ERREUR lors du chargement des compteurs', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    responseText: xhr.responseText,
                    error: error
                });
                updateDebugInfo('response', `ERREUR ${xhr.status}: ${xhr.statusText}`);
                showNotification('Erreur lors du chargement des compteurs', 'error');
            });
    }
    
    function switchQueue(queue) {
        debug(`Changement de file: ${currentQueue} -> ${queue}`);
        
        // Mettre à jour l'UI
        $('.queue-tab').removeClass('active');
        $(`.queue-tab[data-queue="${queue}"]`).addClass('active');
        
        currentQueue = queue;
        currentOrder = null;
        
        updateDebugInfo('queue', queue);
        
        // Réinitialiser l'affichage
        showLoading();
        
        // Charger la nouvelle file
        loadCurrentQueue();
    }
    
    function loadCurrentQueue() {
        debug(`Chargement de la file: ${currentQueue}`);
        updateDebugInfo('request', `GET /admin/process/${currentQueue}`);
        
        $.get(`/admin/process/${currentQueue}`)
            .done(function(data) {
                debug('Réponse de la file reçue', data);
                updateDebugInfo('response', data.hasOrder ? 'Commande trouvée' : 'Aucune commande');
                
                if (data.hasOrder) {
                    currentOrder = data.order;
                    displayOrder(data.order);
                    showMainContent();
                } else {
                    showNoOrderMessage();
                }
            })
            .fail(function(xhr, status, error) {
                debug('ERREUR lors du chargement de la file', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    responseText: xhr.responseText,
                    error: error
                });
                updateDebugInfo('response', `ERREUR ${xhr.status}: ${xhr.statusText}`);
                showNotification('Erreur lors du chargement de la commande', 'error');
                showNoOrderMessage();
            });
    }
    
    // =========================
    // AFFICHAGE DES COMMANDES
    // =========================
    
    function displayOrder(order) {
        debug('Affichage de la commande', order.id);
        
        // Informations de base
        $('#order-number').text(order.id);
        $('#order-date').text(formatDate(order.created_at));
        $('#order-attempts').text(`${order.attempts_count} tentative(s)`);
        $('#order-last-attempt').text(order.last_attempt_at ? formatDate(order.last_attempt_at) : 'Jamais');
        
        // Statut
        const statusElement = $('#order-status');
        statusElement.removeClass().addClass('order-status').addClass(`status-${order.status}`);
        statusElement.text(capitalizeFirst(order.status));
        
        // Formulaire client
        $('#customer_name').val(order.customer_name || '');
        $('#customer_phone').val(order.customer_phone || '');
        $('#customer_phone_2').val(order.customer_phone_2 || '');
        $('#customer_address').val(order.customer_address || '');
        
        // Région et ville
        if (order.customer_governorate) {
            $('#customer_governorate').val(order.customer_governorate);
            loadCities(order.customer_governorate, order.customer_city);
        }
        
        // Panier
        cartItems = order.items || [];
        updateCartDisplay();
        
        debug('Commande affichée avec succès');
    }
    
    function showMainContent() {
        debug('Affichage du contenu principal');
        $('#loading-message').hide();
        $('#no-order-message').hide();
        $('#main-content').show().addClass('fade-in');
    }
    
    function showNoOrderMessage() {
        debug('Affichage du message "aucune commande"');
        $('#loading-message').hide();
        $('#main-content').hide();
        $('#no-order-message').show().addClass('fade-in');
    }
    
    function showLoading() {
        debug('Affichage du chargement');
        $('#main-content').hide();
        $('#no-order-message').hide();
        $('#loading-message').show().addClass('fade-in');
    }
    
    // =========================
    // GESTION DU PANIER
    // =========================
    
    function updateCartDisplay() {
        debug('Mise à jour de l\'affichage du panier', `${cartItems.length} items`);
        
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
                    <div class="item-price">${parseFloat(item.unit_price).toFixed(3)} TND × ${item.quantity}</div>
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
        const subtotal = cartItems.reduce((sum, item) => sum + item.total_price, 0);
        const shipping = 0; // À calculer selon la logique métier
        const total = subtotal + shipping;
        
        $('#cart-subtotal').text(subtotal.toFixed(3) + ' TND');
        $('#cart-shipping').text(shipping.toFixed(3) + ' TND');
        $('#cart-total').text(total.toFixed(3) + ' TND');
    }
    
    // =========================
    // RECHERCHE DE PRODUITS
    // =========================
    
    function searchProducts(query) {
        debug(`Recherche de produits: "${query}"`);
        updateDebugInfo('request', `GET /admin/orders/search-products?search=${query}`);
        
        $.get('/admin/orders/search-products', { search: query })
            .done(function(products) {
                debug('Produits trouvés', products.length);
                updateDebugInfo('response', `${products.length} produits`);
                showSearchSuggestions(products);
            })
            .fail(function(xhr) {
                debug('Erreur lors de la recherche de produits', xhr);
                updateDebugInfo('response', `ERREUR ${xhr.status}`);
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
        debug('Chargement des régions');
        updateDebugInfo('request', 'GET /admin/orders/get-regions');
        
        $.get('/admin/orders/get-regions')
            .done(function(regions) {
                debug('Régions chargées', regions.length);
                updateDebugInfo('response', `${regions.length} régions`);
                
                const select = $('#customer_governorate');
                select.html('<option value="">Sélectionner un gouvernorat</option>');
                
                regions.forEach(region => {
                    select.append(`<option value="${region.id}">${region.name}</option>`);
                });
            })
            .fail(function(xhr) {
                debug('Erreur lors du chargement des régions', xhr);
                updateDebugInfo('response', `ERREUR ${xhr.status}`);
            });
    }
    
    function loadCities(regionId, selectedCityId = null) {
        if (!regionId) {
            $('#customer_city').html('<option value="">Sélectionner une ville</option>');
            return;
        }
        
        debug(`Chargement des villes pour région ${regionId}`);
        updateDebugInfo('request', `GET /admin/orders/get-cities?region_id=${regionId}`);
        
        $.get('/admin/orders/get-cities', { region_id: regionId })
            .done(function(cities) {
                debug('Villes chargées', cities.length);
                updateDebugInfo('response', `${cities.length} villes`);
                
                const select = $('#customer_city');
                select.html('<option value="">Sélectionner une ville</option>');
                
                cities.forEach(city => {
                    const selected = selectedCityId == city.id ? 'selected' : '';
                    select.append(`<option value="${city.id}" ${selected}>${city.name}</option>`);
                });
            })
            .fail(function(xhr) {
                debug('Erreur lors du chargement des villes', xhr);
                updateDebugInfo('response', `ERREUR ${xhr.status}`);
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
        
        debug(`Ouverture du modal d'action: ${action}`);
        
        // Préparer les données selon l'action
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
        const subtotal = cartItems.reduce((sum, item) => sum + item.total_price, 0);
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
    
    function showHistoryModal() {
        if (!currentOrder) {
            showNotification('Aucune commande sélectionnée', 'error');
            return;
        }
        
        debug(`Ouverture de l'historique pour la commande ${currentOrder.id}`);
        updateDebugInfo('request', `GET /admin/orders/${currentOrder.id}/history-modal`);
        
        // Charger l'historique
        $.get(`/admin/orders/${currentOrder.id}/history-modal`)
            .done(function(history) {
                debug('Historique chargé', history.length);
                updateDebugInfo('response', `${history.length} entrées`);
                displayOrderHistory(history);
                $('#historyModal').modal('show');
            })
            .fail(function(xhr) {
                debug('Erreur lors du chargement de l\'historique', xhr);
                updateDebugInfo('response', `ERREUR ${xhr.status}`);
                showNotification('Erreur lors du chargement de l\'historique', 'error');
            });
    }
    
    function displayOrderHistory(history) {
        const container = $('#history-content');
        container.empty();
        
        if (history.length === 0) {
            container.html(`
                <div class="text-center text-muted py-4">
                    <i class="fas fa-history fa-2x mb-3 opacity-50"></i>
                    <p>Aucun historique disponible pour cette commande</p>
                </div>
            `);
            return;
        }
        
        history.forEach(entry => {
            const entryElement = $(`
                <div class="history-entry border-start border-3 border-primary ps-3 mb-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="mb-0 fw-bold text-primary">${entry.action_label}</h6>
                        <small class="text-muted">${formatDate(entry.created_at)}</small>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">
                            Par ${entry.user_name || 'Système'} 
                            ${entry.status_before ? `• ${entry.status_before} → ${entry.status_after}` : ''}
                        </small>
                    </div>
                    ${entry.notes ? `<div class="bg-light p-2 rounded"><small>${entry.notes}</small></div>` : ''}
                </div>
            `);
            
            container.append(entryElement);
        });
    }
    
    // =========================
    // TRAITEMENT DES ACTIONS
    // =========================
    
    function processAction(action, formData) {
        if (!currentOrder) {
            showNotification('Aucune commande sélectionnée', 'error');
            return;
        }
        
        debug(`Traitement de l'action: ${action}`, formData);
        
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
        
        updateDebugInfo('request', `POST /admin/process/action/${currentOrder.id}`);
        
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
            debug('Action traitée avec succès', response);
            updateDebugInfo('response', 'Action OK');
            showNotification('Action traitée avec succès!', 'success');
            
            // Fermer les modales
            $('.modal').modal('hide');
            
            // Recharger les données
            setTimeout(() => {
                loadQueueCounts();
                loadCurrentQueue();
            }, 1000);
        })
        .fail(function(xhr, status, error) {
            debug('Erreur lors du traitement', {
                status: xhr.status,
                statusText: xhr.statusText,
                responseText: xhr.responseText,
                error: error
            });
            updateDebugInfo('response', `ERREUR ${xhr.status}`);
            
            let errorMessage = 'Erreur lors du traitement de l\'action';
            if (xhr.responseJSON && xhr.responseJSON.error) {
                errorMessage = xhr.responseJSON.error;
            }
            
            showNotification(errorMessage, 'error');
        })
        .always(function() {
            // Réactiver les boutons
            $('.action-btn').prop('disabled', false).removeClass('loading');
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
        debug(`Notification: ${type} - ${message}`);
        
        // Utiliser les alertes Bootstrap ou un système de notification personnalisé
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
        
        // Auto-hide après 5 secondes
        setTimeout(() => {
            alert.fadeOut(() => alert.remove());
        }, 5000);
    }
    
    // =========================
    // INITIALISATION
    // =========================
    
    // Démarrer l'application
    initialize();
    
    // Actualiser les compteurs toutes les 30 secondes
    setInterval(loadQueueCounts, 30000);
    
    // Exposer les fonctions pour les modales
    window.processAction = processAction;
    
    // Tests de connectivité au démarrage
    setTimeout(() => {
        debug('Test de connectivité API');
        $.get('/admin/process/test')
            .done(function(data) {
                debug('Test API réussi', data);
            })
            .fail(function(xhr) {
                debug('Test API échoué', xhr.status);
            });
    }, 2000);
});
</script>
@endsection