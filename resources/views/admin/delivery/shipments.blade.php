@extends('layouts.admin')

@section('title', 'Gestion des Expéditions')

@section('content')
<style>
    :root {
        --royal-blue: #1e40af;
        --royal-blue-dark: #1e3a8a;
        --royal-blue-light: #3b82f6;
        --royal-blue-ultra-light: #dbeafe;
        --success: #10b981;
        --success-light: #dcfce7;
        --warning: #f59e0b;
        --warning-light: #fef3c7;
        --danger: #ef4444;
        --danger-light: #fee2e2;
        --info: #06b6d4;
        --info-light: #cffafe;
        --light: #f8fafc;
        --dark: #374151;
        --border: #e5e7eb;
        --shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        --shadow-lg: 0 4px 8px rgba(0, 0, 0, 0.15);
        --shadow-xl: 0 8px 25px rgba(0, 0, 0, 0.15);
        --radius: 12px;
        --radius-lg: 16px;
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        --gradient-primary: linear-gradient(135deg, var(--royal-blue) 0%, var(--royal-blue-light) 100%);
        --gradient-success: linear-gradient(135deg, var(--success) 0%, #059669 100%);
        --gradient-warning: linear-gradient(135deg, var(--warning) 0%, #d97706 100%);
        --gradient-danger: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%);
    }

    * {
        box-sizing: border-box;
    }

    body {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        line-height: 1.6;
    }

    /* ===== CONTAINER PRINCIPAL ===== */
    .shipments-container {
        padding: 1rem;
        max-width: 1400px;
        margin: 0 auto;
        min-height: calc(100vh - 80px);
    }

    /* ===== HEADER MODERNE ===== */
    .page-header {
        background: var(--gradient-primary);
        border-radius: var(--radius-lg);
        padding: 2rem;
        margin-bottom: 2rem;
        color: white;
        box-shadow: var(--shadow-xl);
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
        z-index: 1;
    }

    .page-header-content {
        position: relative;
        z-index: 2;
    }

    .page-title {
        font-size: 2rem;
        font-weight: 800;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .page-subtitle {
        opacity: 0.9;
        font-size: 1.1rem;
        margin-bottom: 1.5rem;
        font-weight: 400;
    }

    .header-actions {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .btn-header {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border: 2px solid rgba(255, 255, 255, 0.3);
        padding: 0.75rem 1.5rem;
        border-radius: var(--radius);
        text-decoration: none;
        font-weight: 600;
        font-size: 0.95rem;
        transition: var(--transition);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        backdrop-filter: blur(10px);
    }

    .btn-header:hover {
        background: rgba(255, 255, 255, 0.3);
        color: white;
        text-decoration: none;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    /* ===== CARTES STATISTIQUES MODERNES ===== */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        box-shadow: var(--shadow);
        border: 1px solid var(--border);
        transition: var(--transition);
        position: relative;
        overflow: hidden;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-xl);
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: var(--gradient-primary);
    }

    .stat-card.success::before { background: var(--gradient-success); }
    .stat-card.warning::before { background: var(--gradient-warning); }
    .stat-card.danger::before { background: var(--gradient-danger); }

    .stat-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .stat-info h3 {
        font-size: 2.5rem;
        font-weight: 800;
        margin: 0;
        color: var(--dark);
        line-height: 1;
    }

    .stat-info p {
        margin: 0.5rem 0 0 0;
        color: #6b7280;
        font-weight: 600;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .stat-icon {
        width: 64px;
        height: 64px;
        border-radius: var(--radius);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        background: var(--royal-blue-ultra-light);
        color: var(--royal-blue);
    }

    .stat-icon.success { background: var(--success-light); color: var(--success); }
    .stat-icon.warning { background: var(--warning-light); color: var(--warning); }
    .stat-icon.danger { background: var(--danger-light); color: var(--danger); }

    /* ===== FILTRES MODERNES ===== */
    .filters-card {
        background: white;
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow);
        border: 1px solid var(--border);
    }

    .filters-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        align-items: end;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
    }

    .filter-label {
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }

    .filter-input,
    .filter-select {
        padding: 0.75rem 1rem;
        border: 2px solid var(--border);
        border-radius: var(--radius);
        font-size: 0.95rem;
        transition: var(--transition);
        background: white;
        width: 100%;
    }

    .filter-input:focus,
    .filter-select:focus {
        border-color: var(--royal-blue);
        box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
        outline: none;
    }

    .search-wrapper {
        position: relative;
    }

    .search-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        z-index: 2;
    }

    .search-input {
        padding-left: 3rem;
    }

    /* ===== BOUTONS MODERNES ===== */
    .btn-modern {
        padding: 0.75rem 1.5rem;
        border-radius: var(--radius);
        font-weight: 600;
        font-size: 0.95rem;
        text-decoration: none;
        text-align: center;
        transition: var(--transition);
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        line-height: 1;
    }

    .btn-modern:hover {
        transform: translateY(-2px);
        text-decoration: none;
    }

    .btn-modern:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none !important;
    }

    .btn-primary { background: var(--gradient-primary); color: white; box-shadow: 0 4px 12px rgba(30, 64, 175, 0.3); }
    .btn-success { background: var(--gradient-success); color: white; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3); }
    .btn-warning { background: var(--gradient-warning); color: white; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3); }
    .btn-danger { background: var(--gradient-danger); color: white; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3); }

    .btn-outline {
        background: transparent;
        color: var(--royal-blue);
        border: 2px solid var(--royal-blue);
        box-shadow: none;
    }

    .btn-outline:hover {
        background: var(--royal-blue);
        color: white;
    }

    .btn-group {
        display: flex;
        gap: 0.5rem;
    }

    /* ===== LISTE DES EXPÉDITIONS MODERNE ===== */
    .shipments-card {
        background: white;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow);
        border: 1px solid var(--border);
        overflow: hidden;
    }

    .shipments-header {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        padding: 1.5rem;
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .shipments-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--dark);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .shipments-count {
        background: var(--royal-blue);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 50px;
        font-size: 0.8rem;
        font-weight: 600;
        margin-left: 0.5rem;
    }

    .bulk-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    /* ===== TABLEAU RESPONSIVE MODERNE ===== */
    .shipments-table-container {
        overflow-x: auto;
        max-width: 100%;
    }

    .shipments-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
    }

    .shipments-table th {
        background: #f9fafb;
        padding: 1rem;
        text-align: left;
        font-weight: 600;
        color: var(--dark);
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 2px solid var(--border);
        white-space: nowrap;
    }

    .shipments-table td {
        padding: 1rem;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
    }

    .shipment-row {
        transition: var(--transition);
        cursor: pointer;
    }

    .shipment-row:hover {
        background: var(--royal-blue-ultra-light);
        transform: translateX(2px);
    }

    .shipment-row.selected {
        background: rgba(30, 64, 175, 0.1);
        border-left: 4px solid var(--royal-blue);
    }

    /* ===== ÉLÉMENTS DE TABLEAU ===== */
    .shipment-id {
        font-weight: 700;
        color: var(--royal-blue);
        font-size: 1.1rem;
    }

    .shipment-order {
        font-size: 0.85rem;
        color: #6b7280;
    }

    .recipient-info {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .recipient-name {
        font-weight: 600;
        color: var(--dark);
    }

    .recipient-contact {
        font-size: 0.85rem;
        color: #6b7280;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .carrier-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .carrier-icon {
        width: 40px;
        height: 40px;
        border-radius: var(--radius);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        color: white;
    }

    .carrier-icon.jax { background: var(--gradient-primary); }
    .carrier-icon.mes_colis { background: var(--gradient-success); }

    .carrier-details {
        display: flex;
        flex-direction: column;
    }

    .carrier-name {
        font-weight: 600;
        color: var(--dark);
    }

    .carrier-integration {
        font-size: 0.85rem;
        color: #6b7280;
    }

    .tracking-code {
        background: var(--royal-blue-ultra-light);
        color: var(--royal-blue);
        padding: 0.25rem 0.5rem;
        border-radius: var(--radius);
        font-family: 'Monaco', 'Consolas', monospace;
        font-size: 0.8rem;
        font-weight: 600;
        display: inline-block;
        margin-bottom: 0.25rem;
    }

    .tracking-link {
        color: var(--royal-blue);
        text-decoration: none;
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
        transition: var(--transition);
    }

    .tracking-link:hover {
        color: var(--royal-blue-dark);
    }

    .amount-info {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
    }

    .cod-amount {
        font-weight: 700;
        color: var(--success);
        font-size: 1.1rem;
    }

    .weight-info {
        font-size: 0.85rem;
        color: #6b7280;
    }

    /* ===== BADGES DE STATUT MODERNES ===== */
    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        white-space: nowrap;
    }

    .status-badge.created { background: #f3f4f6; color: #6b7280; }
    .status-badge.validated { background: var(--royal-blue-ultra-light); color: var(--royal-blue); }
    .status-badge.picked_up_by_carrier { background: var(--warning-light); color: var(--warning); }
    .status-badge.in_transit { background: var(--info-light); color: var(--info); }
    .status-badge.delivered { background: var(--success-light); color: var(--success); }
    .status-badge.in_return { background: var(--warning-light); color: var(--warning); }
    .status-badge.anomaly { background: var(--danger-light); color: var(--danger); }

    .status-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: currentColor;
        box-shadow: 0 0 0 2px rgba(255, 255, 255, 1);
    }

    /* ===== ACTIONS ===== */
    .action-buttons {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .action-btn {
        width: 36px;
        height: 36px;
        border-radius: var(--radius);
        border: 2px solid var(--border);
        background: white;
        color: #6b7280;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: var(--transition);
        cursor: pointer;
        font-size: 0.9rem;
    }

    .action-btn:hover {
        border-color: var(--royal-blue);
        color: var(--royal-blue);
        transform: translateY(-1px);
    }

    .action-btn.primary { border-color: var(--royal-blue); color: var(--royal-blue); }
    .action-btn.success { border-color: var(--success); color: var(--success); }
    .action-btn.info { border-color: var(--info); color: var(--info); }

    /* ===== CHECKBOX MODERNE ===== */
    .checkbox-modern {
        width: 18px;
        height: 18px;
        border: 2px solid var(--border);
        border-radius: 4px;
        background: white;
        cursor: pointer;
        position: relative;
        transition: var(--transition);
    }

    .checkbox-modern:checked {
        background: var(--royal-blue);
        border-color: var(--royal-blue);
    }

    .checkbox-modern:checked::after {
        content: '✓';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: white;
        font-size: 12px;
        font-weight: bold;
    }

    /* ===== ÉTATS VIDES ===== */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: #6b7280;
    }

    .empty-state-icon {
        font-size: 4rem;
        margin-bottom: 1.5rem;
        opacity: 0.5;
        color: var(--royal-blue);
    }

    .empty-state h3 {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 0.5rem;
    }

    .empty-state p {
        margin-bottom: 2rem;
        font-size: 1.1rem;
    }

    /* ===== LOADING ===== */
    .loading-state {
        text-align: center;
        padding: 4rem 2rem;
    }

    .loading-spinner {
        width: 48px;
        height: 48px;
        border: 4px solid var(--border);
        border-top: 4px solid var(--royal-blue);
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 1.5rem;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* ===== PAGINATION MODERNE ===== */
    .pagination-container {
        background: #f9fafb;
        padding: 1.5rem;
        border-top: 1px solid var(--border);
        display: flex;
        justify-content: between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .pagination-info {
        color: #6b7280;
        font-size: 0.9rem;
    }

    .pagination-controls {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    .page-btn {
        padding: 0.5rem 1rem;
        border: 2px solid var(--border);
        background: white;
        color: var(--dark);
        text-decoration: none;
        border-radius: var(--radius);
        font-weight: 600;
        transition: var(--transition);
        min-width: 44px;
        text-align: center;
    }

    .page-btn:hover {
        border-color: var(--royal-blue);
        color: var(--royal-blue);
        text-decoration: none;
    }

    .page-btn.active {
        background: var(--royal-blue);
        color: white;
        border-color: var(--royal-blue);
    }

    .page-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* ===== MODAL MODERNE ===== */
    .modal-modern .modal-content {
        border: none;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-xl);
        overflow: hidden;
    }

    .modal-modern .modal-header {
        background: var(--gradient-primary);
        color: white;
        border-bottom: none;
        padding: 1.5rem 2rem;
    }

    .modal-modern .modal-title {
        font-weight: 700;
        font-size: 1.25rem;
    }

    .modal-modern .modal-body {
        padding: 2rem;
    }

    .modal-modern .modal-footer {
        background: #f9fafb;
        border-top: 1px solid var(--border);
        padding: 1.5rem 2rem;
    }

    /* ===== RESPONSIVE MOBILE ===== */
    @media (max-width: 768px) {
        .shipments-container {
            padding: 0.5rem;
        }

        .page-header {
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .page-title {
            font-size: 1.5rem;
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }

        .header-actions {
            width: 100%;
            justify-content: stretch;
        }

        .btn-header {
            flex: 1;
            justify-content: center;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .stat-card {
            padding: 1rem;
        }

        .stat-content {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }

        .stat-info h3 {
            font-size: 2rem;
        }

        .filters-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .filter-input,
        .filter-select {
            font-size: 16px; /* Évite le zoom sur iOS */
        }

        .shipments-header {
            flex-direction: column;
            align-items: stretch;
            gap: 1rem;
        }

        .bulk-actions {
            width: 100%;
            justify-content: stretch;
        }

        .bulk-actions .btn-modern {
            flex: 1;
        }

        /* Table mobile : mode card */
        .shipments-table-container {
            display: none;
        }

        .shipments-mobile {
            display: block;
        }

        .pagination-container {
            flex-direction: column;
            text-align: center;
        }

        .pagination-controls {
            width: 100%;
            justify-content: center;
        }
    }

    @media (min-width: 769px) {
        .shipments-mobile {
            display: none;
        }
    }

    /* ===== CARTES MOBILE POUR EXPÉDITIONS ===== */
    .shipment-card-mobile {
        background: white;
        border-radius: var(--radius);
        padding: 1rem;
        margin-bottom: 1rem;
        box-shadow: var(--shadow);
        border: 1px solid var(--border);
        transition: var(--transition);
    }

    .shipment-card-mobile:hover {
        box-shadow: var(--shadow-lg);
        transform: translateY(-2px);
    }

    .shipment-card-mobile.selected {
        border-color: var(--royal-blue);
        background: var(--royal-blue-ultra-light);
    }

    .shipment-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }

    .shipment-card-title {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .shipment-card-body {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem;
        margin-bottom: 1rem;
        font-size: 0.9rem;
    }

    .shipment-card-field {
        display: flex;
        flex-direction: column;
    }

    .shipment-card-label {
        font-weight: 600;
        color: #6b7280;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.25rem;
    }

    .shipment-card-value {
        color: var(--dark);
        font-weight: 500;
    }

    .shipment-card-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 0.75rem;
        border-top: 1px solid var(--border);
    }

    /* ===== ANIMATIONS ===== */
    .fade-in {
        animation: fadeIn 0.4s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .slide-up {
        animation: slideUp 0.4s ease-out;
    }

    @keyframes slideUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* ===== TOAST NOTIFICATIONS ===== */
    .toast-notification {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        min-width: 320px;
        max-width: 480px;
        padding: 1rem 1.25rem;
        border-radius: var(--radius);
        color: white;
        font-weight: 600;
        box-shadow: var(--shadow-xl);
        transform: translateX(100%);
        transition: transform 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .toast-notification.show {
        transform: translateX(0);
    }

    .toast-notification.success { background: var(--gradient-success); }
    .toast-notification.warning { background: var(--gradient-warning); }
    .toast-notification.error { background: var(--gradient-danger); }
    .toast-notification.info { background: var(--gradient-primary); }

    .toast-close {
        background: none;
        border: none;
        color: white;
        font-size: 1.2rem;
        cursor: pointer;
        padding: 0;
        margin-left: auto;
        opacity: 0.8;
        transition: var(--transition);
    }

    .toast-close:hover {
        opacity: 1;
    }

    /* ===== UTILITAIRES ===== */
    .d-none { display: none !important; }
    .d-block { display: block !important; }
    .d-flex { display: flex !important; }
    .justify-content-between { justify-content: space-between !important; }
    .align-items-center { align-items: center !important; }
    .gap-1 { gap: 0.5rem !important; }
    .gap-2 { gap: 1rem !important; }
    .mb-0 { margin-bottom: 0 !important; }
    .mb-1 { margin-bottom: 0.5rem !important; }
    .mb-2 { margin-bottom: 1rem !important; }
    .text-center { text-align: center !important; }
    .font-weight-bold { font-weight: 700 !important; }
    .text-muted { color: #6b7280 !important; }
    .w-100 { width: 100% !important; }
</style>

<div class="shipments-container fade-in">
    <!-- Header Principal -->
    <div class="page-header">
        <div class="page-header-content">
            <h1 class="page-title">
                <i class="fas fa-shipping-fast"></i>
                <div>
                    <div>Gestion des Expéditions</div>
                    <div class="page-subtitle">Suivez vos expéditions en temps réel</div>
                </div>
            </h1>
            <div class="header-actions">
                <a href="{{ route('admin.delivery.index') }}" class="btn-header">
                    <i class="fas fa-arrow-left"></i>
                    Retour
                </a>
                <button class="btn-header" onclick="refreshAllTracking()" id="refreshAllBtn">
                    <i class="fas fa-sync" id="refreshIcon"></i>
                    <span id="refreshText">Actualiser Suivi</span>
                </button>
                <a href="{{ route('admin.delivery.preparation') }}" class="btn-header">
                    <i class="fas fa-plus"></i>
                    Nouvelle Expédition
                </a>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="stats-grid slide-up">
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-info">
                    <h3 id="stat-in-transit">0</h3>
                    <p>En Transit</p>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-truck-moving"></i>
                </div>
            </div>
        </div>
        
        <div class="stat-card success">
            <div class="stat-content">
                <div class="stat-info">
                    <h3 id="stat-delivered">0</h3>
                    <p>Livrées</p>
                </div>
                <div class="stat-icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        
        <div class="stat-card warning">
            <div class="stat-content">
                <div class="stat-info">
                    <h3 id="stat-in-return">0</h3>
                    <p>En Retour</p>
                </div>
                <div class="stat-icon warning">
                    <i class="fas fa-undo"></i>
                </div>
            </div>
        </div>
        
        <div class="stat-card danger">
            <div class="stat-content">
                <div class="stat-info">
                    <h3 id="stat-anomaly">0</h3>
                    <p>Anomalies</p>
                </div>
                <div class="stat-icon danger">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="filters-card slide-up">
        <div class="filters-grid">
            <div class="filter-group">
                <label class="filter-label">Recherche</label>
                <div class="search-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" 
                           class="filter-input search-input" 
                           placeholder="Numéro suivi, commande, client..."
                           id="searchInput">
                </div>
            </div>
            
            <div class="filter-group">
                <label class="filter-label">Statut</label>
                <select class="filter-select" id="statusFilter">
                    <option value="">Tous les statuts</option>
                    <option value="created">Créées</option>
                    <option value="validated">Validées</option>
                    <option value="picked_up_by_carrier">Récupérées</option>
                    <option value="in_transit">En transit</option>
                    <option value="delivered">Livrées</option>
                    <option value="in_return">En retour</option>
                    <option value="anomaly">Anomalies</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label class="filter-label">Transporteur</label>
                <select class="filter-select" id="carrierFilter">
                    <option value="">Tous transporteurs</option>
                    <option value="jax_delivery">JAX Delivery</option>
                    <option value="mes_colis">Mes Colis Express</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label class="filter-label">Période</label>
                <select class="filter-select" id="periodFilter">
                    <option value="">Toutes périodes</option>
                    <option value="today">Aujourd'hui</option>
                    <option value="yesterday">Hier</option>
                    <option value="week">Cette semaine</option>
                    <option value="month">Ce mois</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label class="filter-label">Actions</label>
                <div class="btn-group">
                    <button class="btn-modern btn-primary" onclick="refreshShipments()" id="refreshBtn">
                        <i class="fas fa-sync"></i>
                        <span>Actualiser</span>
                    </button>
                    <button class="btn-modern btn-outline" onclick="exportShipments()">
                        <i class="fas fa-download"></i>
                        <span>Export</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des Expéditions -->
    <div class="shipments-card slide-up">
        <div class="shipments-header">
            <div>
                <h2 class="shipments-title">
                    <i class="fas fa-list"></i>
                    Expéditions
                    <span class="shipments-count" id="shipmentsCount">0</span>
                </h2>
            </div>
            <div class="bulk-actions" id="bulkActions" style="display: none;">
                <button class="btn-modern btn-primary" onclick="trackSelected()">
                    <i class="fas fa-route"></i>
                    Suivre Sélection
                </button>
                <button class="btn-modern btn-success" onclick="markSelectedAsDelivered()">
                    <i class="fas fa-check"></i>
                    Marquer Livrées
                </button>
            </div>
        </div>

        <!-- État de chargement -->
        <div id="loadingState" class="loading-state">
            <div class="loading-spinner"></div>
            <p>Chargement des expéditions...</p>
        </div>

        <!-- État vide -->
        <div id="emptyState" class="empty-state d-none">
            <i class="fas fa-shipping-fast empty-state-icon"></i>
            <h3>Aucune expédition trouvée</h3>
            <p>Créez des enlèvements pour générer des expéditions</p>
            <a href="{{ route('admin.delivery.preparation') }}" class="btn-modern btn-primary">
                <i class="fas fa-plus"></i>
                Créer un Enlèvement
            </a>
        </div>

        <!-- Tableau Desktop -->
        <div class="shipments-table-container" id="tableContainer">
            <table class="shipments-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">
                            <input type="checkbox" class="checkbox-modern" id="selectAll" onchange="toggleSelectAll()">
                        </th>
                        <th>Expédition</th>
                        <th>Destinataire</th>
                        <th>Transporteur</th>
                        <th>Suivi</th>
                        <th>Montant</th>
                        <th>Statut</th>
                        <th>Dernière MAJ</th>
                        <th style="width: 140px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="shipmentsTableBody">
                    <!-- Les données seront injectées ici -->
                </tbody>
            </table>
        </div>

        <!-- Liste Mobile -->
        <div class="shipments-mobile" id="mobileContainer">
            <!-- Les cartes mobile seront injectées ici -->
        </div>

        <!-- Pagination -->
        <div class="pagination-container" id="paginationContainer" style="display: none;">
            <div class="pagination-info" id="paginationInfo">
                Affichage de 0 expédition(s)
            </div>
            <div class="pagination-controls" id="paginationControls">
                <!-- Les contrôles de pagination seront injectés ici -->
            </div>
        </div>
    </div>
</div>

<!-- Modal Détails Expédition -->
<div class="modal fade modal-modern" id="shipmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-shipping-fast"></i>
                    Détails de l'Expédition <span id="modalShipmentId"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Le contenu sera injecté dynamiquement -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modern btn-outline" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn-modern btn-primary" id="modalTrackBtn" onclick="trackShipmentFromModal()">
                    <i class="fas fa-sync"></i>
                    Actualiser Suivi
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// ===== VARIABLES GLOBALES =====
let shipments = [];
let filteredShipments = [];
let selectedShipments = [];
let currentShipment = null;
let currentPage = 1;
let isLoading = false;

// ===== INITIALISATION =====
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Page des expéditions initialisée');
    initializeEventListeners();
    loadShipments();
    setInterval(refreshStats, 30000); // Refresh stats every 30 seconds
});

function initializeEventListeners() {
    // Filtres
    document.getElementById('searchInput')?.addEventListener('input', debounce(filterShipments, 300));
    document.getElementById('statusFilter')?.addEventListener('change', filterShipments);
    document.getElementById('carrierFilter')?.addEventListener('change', filterShipments);
    document.getElementById('periodFilter')?.addEventListener('change', filterShipments);
}

// ===== UTILITAIRES =====
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function showToast(type, message, duration = 4000) {
    // Supprimer les toasts existants
    document.querySelectorAll('.toast-notification').forEach(toast => toast.remove());
    
    const toast = document.createElement('div');
    toast.className = `toast-notification ${type}`;
    
    const icon = type === 'success' ? 'check-circle' : 
                 type === 'error' ? 'exclamation-triangle' : 
                 type === 'warning' ? 'exclamation-triangle' : 'info-circle';
    
    toast.innerHTML = `
        <i class="fas fa-${icon}"></i>
        <span>${message}</span>
        <button class="toast-close" onclick="this.parentElement.remove()">&times;</button>
    `;
    
    document.body.appendChild(toast);
    
    // Animer l'entrée
    setTimeout(() => toast.classList.add('show'), 100);
    
    // Auto-suppression
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffInMinutes = Math.floor((now - date) / (1000 * 60));
    
    if (diffInMinutes < 1) return 'À l\'instant';
    if (diffInMinutes < 60) return `${diffInMinutes}min`;
    if (diffInMinutes < 1440) return `${Math.floor(diffInMinutes / 60)}h`;
    if (diffInMinutes < 10080) return `${Math.floor(diffInMinutes / 1440)}j`;
    
    return date.toLocaleDateString('fr-FR');
}

function formatCurrency(amount) {
    return parseFloat(amount).toFixed(3) + ' TND';
}

// ===== CHARGEMENT DES DONNÉES =====
async function loadShipments() {
    if (isLoading) return;
    
    isLoading = true;
    showLoadingState();
    
    try {
        const response = await fetch('/admin/delivery/shipments/list', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                'Accept': 'application/json'
            }
        });
        
        if (!response.ok) {
            throw new Error(`Erreur HTTP ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
            shipments = data.shipments || [];
            updateStats();
            filterShipments();
            console.log(`✅ ${shipments.length} expéditions chargées`);
        } else {
            throw new Error(data.message || 'Erreur de chargement');
        }
        
    } catch (error) {
        console.error('❌ Erreur chargement expéditions:', error);
        shipments = [];
        showEmptyState();
        showToast('error', 'Erreur lors du chargement des expéditions');
    } finally {
        isLoading = false;
        hideLoadingState();
    }
}

function showLoadingState() {
    document.getElementById('loadingState')?.classList.remove('d-none');
    document.getElementById('emptyState')?.classList.add('d-none');
    document.getElementById('tableContainer')?.classList.add('d-none');
    document.getElementById('mobileContainer')?.classList.add('d-none');
    document.getElementById('paginationContainer')?.style.setProperty('display', 'none');
}

function hideLoadingState() {
    document.getElementById('loadingState')?.classList.add('d-none');
}

function showEmptyState() {
    document.getElementById('emptyState')?.classList.remove('d-none');
    document.getElementById('tableContainer')?.classList.add('d-none');
    document.getElementById('mobileContainer')?.classList.add('d-none');
    document.getElementById('paginationContainer')?.style.setProperty('display', 'none');
}

// ===== FILTRAGE =====
function filterShipments() {
    const searchQuery = document.getElementById('searchInput')?.value.toLowerCase() || '';
    const statusFilter = document.getElementById('statusFilter')?.value || '';
    const carrierFilter = document.getElementById('carrierFilter')?.value || '';
    const periodFilter = document.getElementById('periodFilter')?.value || '';
    
    filteredShipments = shipments.filter(shipment => {
        // Filtre de recherche
        if (searchQuery) {
            const searchableText = [
                shipment.id?.toString(),
                shipment.order_id?.toString(),
                shipment.pos_barcode,
                shipment.recipient_info?.name,
                shipment.recipient_info?.phone
            ].filter(Boolean).join(' ').toLowerCase();
            
            if (!searchableText.includes(searchQuery)) {
                return false;
            }
        }
        
        // Filtre par statut
        if (statusFilter && shipment.status !== statusFilter) {
            return false;
        }
        
        // Filtre par transporteur
        if (carrierFilter && shipment.carrier_slug !== carrierFilter) {
            return false;
        }
        
        // Filtre par période
        if (periodFilter) {
            const shipmentDate = new Date(shipment.created_at);
            const now = new Date();
            
            switch (periodFilter) {
                case 'today':
                    if (shipmentDate.toDateString() !== now.toDateString()) return false;
                    break;
                case 'yesterday':
                    const yesterday = new Date(now);
                    yesterday.setDate(yesterday.getDate() - 1);
                    if (shipmentDate.toDateString() !== yesterday.toDateString()) return false;
                    break;
                case 'week':
                    const weekAgo = new Date(now);
                    weekAgo.setDate(weekAgo.getDate() - 7);
                    if (shipmentDate < weekAgo) return false;
                    break;
                case 'month':
                    const monthAgo = new Date(now);
                    monthAgo.setMonth(monthAgo.getMonth() - 1);
                    if (shipmentDate < monthAgo) return false;
                    break;
            }
        }
        
        return true;
    });
    
    displayShipments();
    updateStats();
}

// ===== AFFICHAGE =====
function displayShipments() {
    const tableBody = document.getElementById('shipmentsTableBody');
    const mobileContainer = document.getElementById('mobileContainer');
    const shipmentsCount = document.getElementById('shipmentsCount');
    
    if (shipmentsCount) {
        shipmentsCount.textContent = filteredShipments.length;
    }
    
    if (filteredShipments.length === 0) {
        showEmptyState();
        return;
    }
    
    // Affichage tableau desktop
    if (tableBody) {
        tableBody.innerHTML = filteredShipments.map(shipment => `
            <tr class="shipment-row ${selectedShipments.includes(shipment.id) ? 'selected' : ''}" 
                onclick="toggleShipmentSelection(${shipment.id})" 
                data-shipment-id="${shipment.id}">
                <td onclick="event.stopPropagation()">
                    <input type="checkbox" 
                           class="checkbox-modern" 
                           ${selectedShipments.includes(shipment.id) ? 'checked' : ''}
                           onchange="toggleShipmentSelection(${shipment.id})">
                </td>
                <td>
                    <div class="shipment-id">#${shipment.id}</div>
                    <div class="shipment-order">Commande #${shipment.order_id}</div>
                </td>
                <td>
                    <div class="recipient-info">
                        <div class="recipient-name">${shipment.recipient_info?.name || 'N/A'}</div>
                        <div class="recipient-contact">
                            <i class="fas fa-phone"></i>
                            ${shipment.recipient_info?.phone || 'N/A'}
                        </div>
                        <div class="recipient-contact">
                            <i class="fas fa-map-marker-alt"></i>
                            ${shipment.recipient_info?.city || 'N/A'}
                        </div>
                    </div>
                </td>
                <td>
                    <div class="carrier-info">
                        <div class="carrier-icon ${shipment.carrier_slug}">
                            <i class="fas ${getCarrierIcon(shipment.carrier_slug)}"></i>
                        </div>
                        <div class="carrier-details">
                            <div class="carrier-name">${getCarrierName(shipment.carrier_slug)}</div>
                            <div class="carrier-integration">${shipment.integration_name || 'Configuration'}</div>
                        </div>
                    </div>
                </td>
                <td>
                    ${shipment.pos_barcode ? `
                        <div class="tracking-code">${shipment.pos_barcode}</div>
                        <a href="#" class="tracking-link" onclick="event.stopPropagation(); trackShipment(${shipment.id})">
                            <i class="fas fa-route"></i>
                            Suivre
                        </a>
                    ` : '<span class="text-muted">Non assigné</span>'}
                </td>
                <td>
                    <div class="amount-info">
                        <div class="cod-amount">${formatCurrency(shipment.cod_amount)}</div>
                        <div class="weight-info">${shipment.weight || 0} kg</div>
                    </div>
                </td>
                <td>
                    <span class="status-badge ${shipment.status}">
                        <span class="status-indicator"></span>
                        ${getStatusLabel(shipment.status)}
                    </span>
                </td>
                <td>${formatDate(shipment.updated_at)}</td>
                <td onclick="event.stopPropagation()">
                    <div class="action-buttons">
                        <button class="action-btn primary" onclick="viewShipment(${shipment.id})" title="Voir détails">
                            <i class="fas fa-eye"></i>
                        </button>
                        ${shipment.pos_barcode ? `
                            <button class="action-btn info" onclick="trackShipment(${shipment.id})" title="Suivre">
                                <i class="fas fa-sync"></i>
                            </button>
                        ` : ''}
                        ${['in_transit', 'picked_up_by_carrier'].includes(shipment.status) ? `
                            <button class="action-btn success" onclick="markAsDelivered(${shipment.id})" title="Marquer livrée">
                                <i class="fas fa-check"></i>
                            </button>
                        ` : ''}
                        <button class="action-btn" onclick="contactCustomer('${shipment.recipient_info?.phone}')" title="Contacter">
                            <i class="fas fa-phone"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }
    
    // Affichage mobile
    if (mobileContainer) {
        mobileContainer.innerHTML = filteredShipments.map(shipment => `
            <div class="shipment-card-mobile ${selectedShipments.includes(shipment.id) ? 'selected' : ''}" 
                 data-shipment-id="${shipment.id}">
                <div class="shipment-card-header">
                    <div class="shipment-card-title">
                        <input type="checkbox" 
                               class="checkbox-modern" 
                               ${selectedShipments.includes(shipment.id) ? 'checked' : ''}
                               onchange="toggleShipmentSelection(${shipment.id})">
                        <div>
                            <div class="shipment-id">#${shipment.id}</div>
                            <div class="shipment-order">Commande #${shipment.order_id}</div>
                        </div>
                    </div>
                    <span class="status-badge ${shipment.status}">
                        <span class="status-indicator"></span>
                        ${getStatusLabel(shipment.status)}
                    </span>
                </div>
                
                <div class="shipment-card-body">
                    <div class="shipment-card-field">
                        <div class="shipment-card-label">Destinataire</div>
                        <div class="shipment-card-value">${shipment.recipient_info?.name || 'N/A'}</div>
                    </div>
                    <div class="shipment-card-field">
                        <div class="shipment-card-label">Téléphone</div>
                        <div class="shipment-card-value">${shipment.recipient_info?.phone || 'N/A'}</div>
                    </div>
                    <div class="shipment-card-field">
                        <div class="shipment-card-label">Transporteur</div>
                        <div class="shipment-card-value">${getCarrierName(shipment.carrier_slug)}</div>
                    </div>
                    <div class="shipment-card-field">
                        <div class="shipment-card-label">Montant</div>
                        <div class="shipment-card-value">${formatCurrency(shipment.cod_amount)}</div>
                    </div>
                    ${shipment.pos_barcode ? `
                        <div class="shipment-card-field">
                            <div class="shipment-card-label">Suivi</div>
                            <div class="shipment-card-value">
                                <div class="tracking-code">${shipment.pos_barcode}</div>
                            </div>
                        </div>
                    ` : ''}
                </div>
                
                <div class="shipment-card-actions">
                    <div class="action-buttons">
                        <button class="action-btn primary" onclick="viewShipment(${shipment.id})">
                            <i class="fas fa-eye"></i>
                        </button>
                        ${shipment.pos_barcode ? `
                            <button class="action-btn info" onclick="trackShipment(${shipment.id})">
                                <i class="fas fa-sync"></i>
                            </button>
                        ` : ''}
                        ${['in_transit', 'picked_up_by_carrier'].includes(shipment.status) ? `
                            <button class="action-btn success" onclick="markAsDelivered(${shipment.id})">
                                <i class="fas fa-check"></i>
                            </button>
                        ` : ''}
                    </div>
                    <button class="btn-modern btn-primary" onclick="contactCustomer('${shipment.recipient_info?.phone}')">
                        <i class="fas fa-phone"></i>
                        Contacter
                    </button>
                </div>
            </div>
        `).join('');
    }
    
    // Afficher les conteneurs
    document.getElementById('tableContainer')?.classList.remove('d-none');
    document.getElementById('mobileContainer')?.classList.remove('d-none');
    document.getElementById('emptyState')?.classList.add('d-none');
    
    updateBulkActions();
}

// ===== STATISTIQUES =====
function updateStats() {
    const stats = {
        in_transit: filteredShipments.filter(s => s.status === 'in_transit').length,
        delivered: filteredShipments.filter(s => s.status === 'delivered').length,
        in_return: filteredShipments.filter(s => s.status === 'in_return').length,
        anomaly: filteredShipments.filter(s => s.status === 'anomaly').length
    };
    
    document.getElementById('stat-in-transit').textContent = stats.in_transit;
    document.getElementById('stat-delivered').textContent = stats.delivered;
    document.getElementById('stat-in-return').textContent = stats.in_return;
    document.getElementById('stat-anomaly').textContent = stats.anomaly;
}

async function refreshStats() {
    try {
        const response = await fetch('/admin/delivery/api/stats', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                'Accept': 'application/json'
            }
        });
        
        if (response.ok) {
            const data = await response.json();
            if (data.success && data.stats.shipments) {
                const stats = data.stats.shipments;
                document.getElementById('stat-in-transit').textContent = stats.in_transit || 0;
                document.getElementById('stat-delivered').textContent = stats.delivered || 0;
                document.getElementById('stat-in-return').textContent = stats.in_return || 0;
                document.getElementById('stat-anomaly').textContent = stats.anomaly || 0;
            }
        }
    } catch (error) {
        console.warn('Erreur refresh stats:', error);
    }
}

// ===== SÉLECTION =====
function toggleShipmentSelection(shipmentId) {
    const index = selectedShipments.indexOf(shipmentId);
    if (index > -1) {
        selectedShipments.splice(index, 1);
    } else {
        selectedShipments.push(shipmentId);
    }
    
    updateShipmentSelection();
    updateBulkActions();
}

function toggleSelectAll() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const allVisible = filteredShipments.map(s => s.id);
    
    if (selectAllCheckbox?.checked) {
        // Ajouter toutes les expéditions visibles
        allVisible.forEach(id => {
            if (!selectedShipments.includes(id)) {
                selectedShipments.push(id);
            }
        });
    } else {
        // Retirer toutes les expéditions visibles
        selectedShipments = selectedShipments.filter(id => !allVisible.includes(id));
    }
    
    updateShipmentSelection();
    updateBulkActions();
}

function updateShipmentSelection() {
    // Mise à jour des checkboxes
    document.querySelectorAll('.checkbox-modern').forEach(checkbox => {
        if (checkbox.id === 'selectAll') return;
        
        const row = checkbox.closest('[data-shipment-id]');
        if (row) {
            const shipmentId = parseInt(row.dataset.shipmentId);
            checkbox.checked = selectedShipments.includes(shipmentId);
            
            if (selectedShipments.includes(shipmentId)) {
                row.classList.add('selected');
            } else {
                row.classList.remove('selected');
            }
        }
    });
    
    // Mise à jour du selectAll
    const selectAllCheckbox = document.getElementById('selectAll');
    if (selectAllCheckbox) {
        const visibleIds = filteredShipments.map(s => s.id);
        const selectedVisibleCount = selectedShipments.filter(id => visibleIds.includes(id)).length;
        
        if (selectedVisibleCount === 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        } else if (selectedVisibleCount === visibleIds.length) {
            selectAllCheckbox.checked = true;
            selectAllCheckbox.indeterminate = false;
        } else {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = true;
        }
    }
}

function updateBulkActions() {
    const bulkActions = document.getElementById('bulkActions');
    if (bulkActions) {
        bulkActions.style.display = selectedShipments.length > 0 ? 'flex' : 'none';
    }
}

// ===== ACTIONS SUR LES EXPÉDITIONS =====
function refreshShipments() {
    const refreshBtn = document.getElementById('refreshBtn');
    const originalContent = refreshBtn?.innerHTML;
    
    if (refreshBtn) {
        refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Chargement...</span>';
        refreshBtn.disabled = true;
    }
    
    loadShipments().finally(() => {
        if (refreshBtn) {
            refreshBtn.innerHTML = originalContent;
            refreshBtn.disabled = false;
        }
    });
}

async function refreshAllTracking() {
    const btn = document.getElementById('refreshAllBtn');
    const icon = document.getElementById('refreshIcon');
    const text = document.getElementById('refreshText');
    
    if (btn?.disabled) return;
    
    btn.disabled = true;
    icon?.classList.add('fa-spin');
    if (text) text.textContent = 'Actualisation...';
    
    try {
        const response = await fetch('/admin/delivery/api/track-all', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                'Accept': 'application/json'
            }
        });
        
        if (response.ok) {
            const data = await response.json();
            if (data.success) {
                showToast('success', data.message || 'Suivi actualisé avec succès');
                setTimeout(() => loadShipments(), 1000);
            } else {
                throw new Error(data.message || 'Erreur lors de l\'actualisation');
            }
        } else {
            throw new Error(`Erreur HTTP ${response.status}`);
        }
    } catch (error) {
        console.error('Erreur actualisation:', error);
        showToast('error', 'Erreur lors de l\'actualisation du suivi');
    } finally {
        btn.disabled = false;
        icon?.classList.remove('fa-spin');
        if (text) text.textContent = 'Actualiser Suivi';
    }
}

async function trackShipment(shipmentId) {
    try {
        const response = await fetch(`/admin/delivery/shipments/${shipmentId}/track`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                'Accept': 'application/json'
            }
        });
        
        if (response.ok) {
            const data = await response.json();
            if (data.success) {
                showToast('success', 'Suivi mis à jour avec succès');
                setTimeout(() => loadShipments(), 1000);
            } else {
                throw new Error(data.message || 'Erreur lors du suivi');
            }
        } else {
            throw new Error(`Erreur HTTP ${response.status}`);
        }
    } catch (error) {
        console.error('Erreur suivi:', error);
        showToast('error', 'Erreur lors du suivi de l\'expédition');
    }
}

async function markAsDelivered(shipmentId) {
    if (!confirm('Marquer cette expédition comme livrée ?')) return;
    
    try {
        const response = await fetch(`/admin/delivery/shipments/${shipmentId}/mark-delivered`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                'Accept': 'application/json'
            }
        });
        
        if (response.ok) {
            const data = await response.json();
            if (data.success) {
                showToast('success', 'Expédition marquée comme livrée');
                setTimeout(() => loadShipments(), 1000);
            } else {
                throw new Error(data.message || 'Erreur lors du marquage');
            }
        } else {
            throw new Error(`Erreur HTTP ${response.status}`);
        }
    } catch (error) {
        console.error('Erreur marquage:', error);
        showToast('error', 'Erreur lors du marquage comme livrée');
    }
}

function viewShipment(shipmentId) {
    const shipment = shipments.find(s => s.id === shipmentId);
    if (!shipment) return;
    
    currentShipment = shipment;
    
    document.getElementById('modalShipmentId').textContent = `#${shipment.id}`;
    document.getElementById('modalBody').innerHTML = generateShipmentDetailsHTML(shipment);
    
    const modal = new bootstrap.Modal(document.getElementById('shipmentModal'));
    modal.show();
}

function generateShipmentDetailsHTML(shipment) {
    return `
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="filters-card">
                    <h6 class="filter-label">
                        <i class="fas fa-info-circle text-primary"></i>
                        Informations Expédition
                    </h6>
                    <div class="shipment-card-body">
                        <div class="shipment-card-field">
                            <div class="shipment-card-label">ID Expédition</div>
                            <div class="shipment-card-value">#${shipment.id}</div>
                        </div>
                        <div class="shipment-card-field">
                            <div class="shipment-card-label">Commande</div>
                            <div class="shipment-card-value">#${shipment.order_id}</div>
                        </div>
                        <div class="shipment-card-field">
                            <div class="shipment-card-label">Transporteur</div>
                            <div class="shipment-card-value">${getCarrierName(shipment.carrier_slug)}</div>
                        </div>
                        <div class="shipment-card-field">
                            <div class="shipment-card-label">Statut</div>
                            <div class="shipment-card-value">
                                <span class="status-badge ${shipment.status}">
                                    <span class="status-indicator"></span>
                                    ${getStatusLabel(shipment.status)}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="filters-card">
                    <h6 class="filter-label">
                        <i class="fas fa-user text-success"></i>
                        Destinataire
                    </h6>
                    <div class="shipment-card-body">
                        <div class="shipment-card-field">
                            <div class="shipment-card-label">Nom</div>
                            <div class="shipment-card-value">${shipment.recipient_info?.name || 'N/A'}</div>
                        </div>
                        <div class="shipment-card-field">
                            <div class="shipment-card-label">Téléphone</div>
                            <div class="shipment-card-value">${shipment.recipient_info?.phone || 'N/A'}</div>
                        </div>
                        <div class="shipment-card-field">
                            <div class="shipment-card-label">Adresse</div>
                            <div class="shipment-card-value">${shipment.recipient_info?.address || 'N/A'}</div>
                        </div>
                        <div class="shipment-card-field">
                            <div class="shipment-card-label">Ville</div>
                            <div class="shipment-card-value">${shipment.recipient_info?.city || 'N/A'}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="filters-card">
                    <h6 class="filter-label">
                        <i class="fas fa-route text-info"></i>
                        Suivi
                    </h6>
                    <div class="shipment-card-body">
                        ${shipment.pos_barcode ? `
                            <div class="shipment-card-field">
                                <div class="shipment-card-label">Numéro de suivi</div>
                                <div class="shipment-card-value">
                                    <div class="tracking-code">${shipment.pos_barcode}</div>
                                </div>
                            </div>
                        ` : '<p class="text-muted">Aucun numéro de suivi assigné</p>'}
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="filters-card">
                    <h6 class="filter-label">
                        <i class="fas fa-money-bill text-success"></i>
                        Détails Financiers
                    </h6>
                    <div class="shipment-card-body">
                        <div class="shipment-card-field">
                            <div class="shipment-card-label">Montant COD</div>
                            <div class="shipment-card-value">${formatCurrency(shipment.cod_amount)}</div>
                        </div>
                        <div class="shipment-card-field">
                            <div class="shipment-card-label">Poids</div>
                            <div class="shipment-card-value">${shipment.weight || 0} kg</div>
                        </div>
                        <div class="shipment-card-field">
                            <div class="shipment-card-label">Nombre de pièces</div>
                            <div class="shipment-card-value">${shipment.nb_pieces || 1}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function trackShipmentFromModal() {
    if (currentShipment) {
        trackShipment(currentShipment.id);
    }
}

function contactCustomer(phone) {
    if (phone) {
        window.open(`tel:${phone}`);
    }
}

// ===== ACTIONS GROUPÉES =====
async function trackSelected() {
    if (selectedShipments.length === 0) {
        showToast('warning', 'Aucune expédition sélectionnée');
        return;
    }
    
    const trackableShipments = selectedShipments.filter(id => {
        const shipment = shipments.find(s => s.id === id);
        return shipment && shipment.pos_barcode && ['in_transit', 'picked_up_by_carrier'].includes(shipment.status);
    });
    
    if (trackableShipments.length === 0) {
        showToast('warning', 'Aucune expédition trackable sélectionnée');
        return;
    }
    
    showToast('info', `Actualisation du suivi de ${trackableShipments.length} expédition(s)...`);
    
    let updated = 0;
    for (const id of trackableShipments) {
        try {
            await trackShipment(id);
            updated++;
        } catch (error) {
            console.error(`Erreur suivi ${id}:`, error);
        }
    }
    
    showToast('success', `${updated}/${trackableShipments.length} expédition(s) mise(s) à jour`);
    setTimeout(() => loadShipments(), 1000);
}

async function markSelectedAsDelivered() {
    if (selectedShipments.length === 0) {
        showToast('warning', 'Aucune expédition sélectionnée');
        return;
    }
    
    const deliverableShipments = selectedShipments.filter(id => {
        const shipment = shipments.find(s => s.id === id);
        return shipment && ['in_transit', 'picked_up_by_carrier'].includes(shipment.status);
    });
    
    if (deliverableShipments.length === 0) {
        showToast('warning', 'Aucune expédition livrable sélectionnée');
        return;
    }
    
    if (!confirm(`Marquer ${deliverableShipments.length} expédition(s) comme livrée(s) ?`)) {
        return;
    }
    
    let delivered = 0;
    for (const id of deliverableShipments) {
        try {
            await markAsDelivered(id);
            delivered++;
        } catch (error) {
            console.error(`Erreur livraison ${id}:`, error);
        }
    }
    
    selectedShipments = [];
    updateShipmentSelection();
    updateBulkActions();
    
    showToast('success', `${delivered}/${deliverableShipments.length} expédition(s) marquée(s) comme livrée(s)`);
    setTimeout(() => loadShipments(), 1000);
}

// ===== EXPORT =====
function exportShipments() {
    showToast('info', 'Fonctionnalité d\'export bientôt disponible');
}

// ===== UTILITAIRES DE FORMATAGE =====
function getCarrierIcon(carrierSlug) {
    const icons = {
        'jax_delivery': 'fa-truck',
        'mes_colis': 'fa-shipping-fast'
    };
    return icons[carrierSlug] || 'fa-truck';
}

function getCarrierName(carrierSlug) {
    const names = {
        'jax_delivery': 'JAX Delivery',
        'mes_colis': 'Mes Colis Express'
    };
    return names[carrierSlug] || 'Transporteur Inconnu';
}

function getStatusLabel(status) {
    const labels = {
        'created': 'Créée',
        'validated': 'Validée',
        'picked_up_by_carrier': 'Récupérée',
        'in_transit': 'En Transit',
        'delivered': 'Livrée',
        'cancelled': 'Annulée',
        'in_return': 'En Retour',
        'anomaly': 'Anomalie'
    };
    return labels[status] || 'Inconnu';
}

console.log('✅ Scripts des expéditions chargés');
</script>
@endsection