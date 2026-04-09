@extends('confirmi.layouts.app')

@section('title', 'Traitement des Commandes')
@section('page-title', 'Interface de Traitement')

@section('css')
<link rel="stylesheet" href="{{ asset('css/responsive-system.css') }}">
<style>
    /* ══════════════════════════════════════════════════════
       PROCESS INTERFACE — Modern, theme-native CSS
       All colors use var() from the layout's theme system.
       NO hardcoded light-only values. Works in both modes.
       ══════════════════════════════════════════════════════ */

    /* Container principal */
    .process-container {
        background: var(--bg-card);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
        border: 1px solid var(--border);
        margin: 0.5rem;
        min-height: calc(100vh - 120px);
        overflow: hidden;
    }

    /* Header avec onglets */
    .process-header {
        background: linear-gradient(135deg, var(--accent), var(--accent-light));
        padding: 1rem 1.5rem;
        position: relative;
        overflow: visible;
        display: flex;
        align-items: center;
        gap: 2rem;
    }

    .process-icon {
        color: white;
        font-size: 2.5rem;
        position: relative;
        z-index: 2;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 56px;
        height: 56px;
        background: rgba(255, 255, 255, 0.12);
        border-radius: 14px;
        border: 1px solid rgba(255, 255, 255, 0.18);
    }

    /* Onglets modernes */
    .queue-tabs {
        display: flex;
        gap: 0.75rem;
        position: relative;
        z-index: 2;
        flex: 1;
        flex-wrap: wrap;
    }

    .queue-tab {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.18);
        border-radius: 12px;
        padding: 0.65rem 1rem;
        color: rgba(255, 255, 255, 0.75);
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 600;
        font-size: 0.88rem;
        position: relative;
        min-width: 130px;
        justify-content: center;
    }

    .queue-tab:hover {
        color: white;
        background: rgba(255, 255, 255, 0.18);
        transform: translateY(-2px);
    }

    .queue-tab.active {
        color: white;
        background: rgba(255, 255, 255, 0.22);
        border-color: rgba(255, 255, 255, 0.35);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .queue-icon {
        font-size: 1rem;
        width: 26px;
        height: 26px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.15);
        border-radius: 8px;
    }

    .queue-badge {
        background: rgba(255, 255, 255, 0.88);
        color: var(--accent);
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.78rem;
        font-weight: 700;
        min-width: 22px;
        text-align: center;
    }

    .queue-tab.active .queue-badge {
        background: white;
        animation: pulse 2s infinite;
    }

    .queue-tab[data-queue="restock"] .queue-badge {
        background: rgba(16, 185, 129, 0.85);
        color: white;
    }

    .queue-tab[data-queue="restock"].active .queue-badge {
        background: var(--success);
        color: white;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }

    /* Contenu principal */
    .process-content {
        padding: 1rem;
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 1rem;
        min-height: calc(100vh - 180px);
    }

    /* Zone commande */
    .order-zone {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .order-card {
        background: var(--bg-card);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow);
        border: 1px solid var(--border);
        overflow: hidden;
        transition: all 0.2s ease;
    }

    .order-card:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-1px);
    }

    .order-header {
        background: var(--bg-card-alt);
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .order-id {
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--text);
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .order-status {
        padding: 5px 14px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .status-nouvelle { background: var(--accent-bg); color: var(--accent); }
    .status-datée    { background: var(--warning-bg); color: #92400e; }
    .status-confirmée { background: var(--success-bg); color: #065f46; }
    .status-ancienne  { background: var(--danger-bg); color: #991b1b; }

    [data-theme="dark"] .status-nouvelle { background: rgba(37,99,235,0.15); color: #93c5fd; }
    [data-theme="dark"] .status-datée    { background: rgba(245,158,11,0.15); color: #fcd34d; }
    [data-theme="dark"] .status-confirmée { background: rgba(16,185,129,0.15); color: #6ee7b7; }
    [data-theme="dark"] .status-ancienne  { background: rgba(239,68,68,0.15); color: #fca5a5; }

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
        color: var(--text-secondary);
        font-size: 0.85rem;
    }

    .meta-icon {
        width: 18px;
        height: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--bg-hover);
        border-radius: 5px;
        color: var(--text-muted);
        font-size: 0.7rem;
    }

    /* Tags de la commande */
    .order-tags {
        display: flex;
        gap: 0.5rem;
        margin-top: 0.75rem;
        flex-wrap: wrap;
    }

    .order-tag {
        padding: 3px 10px;
        border-radius: 10px;
        font-size: 0.75rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .tag-priority-urgente { background: var(--danger-bg); color: var(--danger); border: 1px solid rgba(239,68,68,0.2); }
    .tag-priority-vip     { background: var(--warning-bg); color: #92400e; border: 1px solid rgba(245,158,11,0.2); }
    .tag-priority-normale { background: var(--accent-bg); color: var(--accent); border: 1px solid rgba(37,99,235,0.2); }
    .tag-assigned  { background: var(--success-bg); color: #065f46; border: 1px solid rgba(16,185,129,0.2); }
    .tag-suspended { background: var(--danger-bg); color: #991b1b; border: 1px solid rgba(239,68,68,0.2); }
    .tag-scheduled { background: var(--warning-bg); color: #92400e; border: 1px solid rgba(245,158,11,0.2); }

    [data-theme="dark"] .tag-priority-normale { background: rgba(37,99,235,0.15); color: #93c5fd; border-color: rgba(37,99,235,0.25); }
    [data-theme="dark"] .tag-priority-urgente { background: rgba(239,68,68,0.15); color: #fca5a5; border-color: rgba(239,68,68,0.25); }
    [data-theme="dark"] .tag-priority-vip     { background: rgba(245,158,11,0.15); color: #fcd34d; border-color: rgba(245,158,11,0.25); }
    [data-theme="dark"] .tag-assigned  { background: rgba(16,185,129,0.15); color: #6ee7b7; border-color: rgba(16,185,129,0.25); }
    [data-theme="dark"] .tag-suspended { background: rgba(239,68,68,0.15); color: #fca5a5; border-color: rgba(239,68,68,0.25); }
    [data-theme="dark"] .tag-scheduled { background: rgba(245,158,11,0.15); color: #fcd34d; border-color: rgba(245,158,11,0.25); }

    /* Alertes spéciales */
    .duplicate-alert, .restock-info {
        padding: 1rem;
        margin-bottom: 1rem;
        border-radius: 12px;
        display: none;
    }

    .duplicate-alert {
        background: var(--warning-bg);
        border: 2px solid rgba(245, 158, 11, 0.3);
    }

    .restock-info {
        background: var(--success-bg);
        border: 1px solid rgba(16, 185, 129, 0.3);
    }

    [data-theme="dark"] .duplicate-alert { background: rgba(245,158,11,0.08); border-color: rgba(245,158,11,0.2); }
    [data-theme="dark"] .restock-info    { background: rgba(16,185,129,0.08); border-color: rgba(16,185,129,0.2); }

    .duplicate-alert.show, .restock-info.show {
        display: block;
        animation: slideInDown 0.4s ease-out;
    }

    @keyframes slideInDown {
        from { opacity: 0; transform: translateY(-15px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .alert-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-weight: 600;
        margin-bottom: 0.75rem;
    }

    .duplicate-alert .alert-header { color: var(--warning); }
    .restock-info .alert-header    { color: var(--success); }

    .alert-message {
        background: var(--bg-card-alt);
        padding: 0.75rem;
        border-radius: 8px;
        color: var(--text);
        font-size: 0.88rem;
        line-height: 1.4;
        border-left: 4px solid var(--warning);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .restock-info .alert-message {
        border-left-color: var(--success);
    }

    /* Formulaire client */
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
        color: var(--text);
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.85rem;
    }

    .form-label .required {
        color: var(--danger);
        font-size: 0.85rem;
    }

    .form-control {
        border: 1.5px solid var(--input-border);
        border-radius: var(--radius);
        padding: 10px 14px;
        transition: all 0.2s ease;
        font-size: 0.88rem;
        background: var(--input-bg);
        color: var(--text);
        width: 100%;
    }

    .form-control:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 3px var(--accent-glow, rgba(37,99,235,0.1));
        background: var(--input-bg);
        color: var(--text);
        outline: none;
    }

    .form-control:disabled {
        background: var(--bg-card-alt);
        color: var(--text-muted);
        cursor: not-allowed;
    }

    .form-control::placeholder { color: var(--text-muted); }

    .form-control.is-invalid {
        border-color: var(--danger);
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }

    .phone-input-wrapper {
        display: flex;
        gap: 0.5rem;
        align-items: stretch;
    }

    .phone-input-wrapper .form-control {
        flex: 1;
    }

    .btn-call-direct {
        background: var(--success);
        color: white;
        border: none;
        border-radius: var(--radius);
        padding: 0;
        width: 46px;
        min-width: 46px;
        height: 46px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 1.15rem;
        flex-shrink: 0;
    }

    .btn-call-direct:hover { filter: brightness(1.1); transform: scale(1.05); }
    .btn-call-direct:active { transform: scale(0.95); }

    /* Queue selector dropdown pour mobile */
    .queue-selector-mobile {
        display: none;
        width: 100%;
    }

    .queue-select {
        width: 100%;
        padding: 0.7rem 1rem;
        border: 1.5px solid rgba(255, 255, 255, 0.25);
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.12);
        color: white;
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='white' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        padding-right: 2.5rem;
    }

    .queue-select option {
        background: var(--bg-card);
        color: var(--text);
        padding: 0.5rem;
    }

    /* Bouton historique dans le header */
    .btn-history-header {
        display: none;
        background: rgba(255, 255, 255, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.25);
        color: white;
        width: 42px;
        height: 42px;
        border-radius: 12px;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 1.05rem;
        transition: all 0.2s;
        flex-shrink: 0;
    }

    .btn-history-header:hover { background: rgba(255, 255, 255, 0.25); }
    .btn-history-header:active { transform: scale(0.95); }

    /* Panier collapsible */
    .cart-toggle {
        cursor: pointer;
        user-select: none;
    }

    .cart-toggle i.fa-chevron-down {
        transition: transform 0.3s ease;
    }

    .cart-card.collapsed .cart-toggle i.fa-chevron-down {
        transform: rotate(-180deg);
    }

    .cart-card.collapsed .cart-body {
        display: none;
    }

    /* Zone panier */
    .cart-zone {
        display: flex;
        flex-direction: column;
        position: relative;
    }

    .cart-card {
        background: var(--bg-card);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow);
        border: 1px solid var(--border);
        overflow: hidden;
        flex: 1;
        display: flex;
        flex-direction: column;
        transition: all 0.2s ease;
    }

    .cart-header {
        background: var(--success);
        color: white;
        padding: 0.7rem 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
    }

    .cart-title {
        font-size: 1rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .cart-count {
        background: rgba(255, 255, 255, 0.2);
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 0.78rem;
        font-weight: 600;
    }

    .cart-body {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .product-search {
        padding: 0.75rem;
        border-bottom: 1px solid var(--border);
        background: var(--bg-card-alt);
    }

    .search-wrapper {
        position: relative;
    }

    .search-input {
        width: 100%;
        padding: 9px 14px 9px 38px;
        border: 1.5px solid var(--input-border);
        border-radius: var(--radius);
        background: var(--input-bg);
        color: var(--text);
        font-size: 0.88rem;
        transition: all 0.2s;
    }

    .search-input::placeholder { color: var(--text-muted); }

    .search-input:focus {
        border-color: var(--success);
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        outline: none;
    }

    .search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        font-size: 0.9rem;
    }

    .search-suggestions {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-top: none;
        border-radius: 0 0 var(--radius) var(--radius);
        box-shadow: var(--shadow-lg);
        z-index: 100;
        max-height: 200px;
        overflow-y: auto;
        display: none;
    }

    .suggestion-item {
        padding: 10px 14px;
        cursor: pointer;
        border-bottom: 1px solid var(--border-light);
        transition: all 0.15s;
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: var(--text);
        font-size: 0.85rem;
    }

    .suggestion-item:hover {
        background: var(--bg-hover);
    }

    .suggestion-item:last-child {
        border-bottom: none;
    }

    /* Produits du panier */
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
        padding: 0.7rem;
        background: var(--bg-card-alt);
        border-radius: var(--radius);
        margin-bottom: 0.5rem;
        border: 1px solid var(--border);
        transition: all 0.2s;
        animation: slideInRight 0.3s ease-out;
    }

    .cart-item:hover {
        box-shadow: var(--shadow);
        background: var(--bg-hover);
    }

    @keyframes slideInRight {
        from { opacity: 0; transform: translateX(10px); }
        to   { opacity: 1; transform: translateX(0); }
    }

    .item-info { flex: 1; }

    .item-name {
        font-weight: 600;
        color: var(--text);
        margin-bottom: 2px;
        font-size: 0.85rem;
    }

    .item-price {
        color: var(--text-secondary);
        font-size: 0.78rem;
        font-family: 'JetBrains Mono', monospace;
    }

    .quantity-controls {
        display: flex;
        align-items: center;
        gap: 4px;
        background: var(--bg-card);
        border-radius: 6px;
        padding: 2px;
        border: 1px solid var(--border);
    }

    .qty-btn {
        width: 28px;
        height: 28px;
        border: none;
        background: var(--bg-hover);
        border-radius: 5px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.15s;
        color: var(--text-secondary);
    }

    .qty-btn:hover {
        background: var(--border);
        color: var(--text);
    }

    .qty-input {
        width: 42px;
        text-align: center;
        border: none;
        background: transparent;
        font-weight: 600;
        color: var(--text);
        font-size: 0.85rem;
    }

    .remove-btn {
        background: var(--danger-bg);
        color: var(--danger);
        border: none;
        border-radius: 6px;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.15s;
    }

    [data-theme="dark"] .remove-btn { background: rgba(239,68,68,0.12); color: #f87171; }
    .remove-btn:hover { filter: brightness(0.9); transform: scale(1.05); }

    .cart-empty {
        text-align: center;
        padding: 1.5rem;
        color: var(--text-muted);
    }

    .cart-empty i {
        font-size: 2.5rem;
        margin-bottom: 0.75rem;
        opacity: 0.4;
    }

    /* Résumé panier */
    .cart-summary {
        padding: 0.75rem;
        background: var(--bg-card-alt);
        border-top: 1px solid var(--border);
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
        font-size: 0.88rem;
    }

    .summary-row:last-child {
        margin-bottom: 0;
        font-weight: 700;
        font-size: 1rem;
        color: var(--text);
        padding-top: 0.5rem;
        border-top: 1px solid var(--border);
    }

    .summary-label {
        color: var(--text-secondary);
        font-weight: 500;
    }

    .summary-value {
        font-family: 'JetBrains Mono', monospace;
        font-weight: 600;
        color: var(--text);
    }

    /* Zone actions */
    .actions-zone {
        grid-column: 1 / -1;
        background: var(--bg-card);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow);
        border: 1px solid var(--border);
        padding: 1rem;
        display: flex;
        justify-content: center;
        align-items: center;
        margin-top: 0.5rem;
    }

    .action-buttons {
        display: flex;
        gap: 0.65rem;
        flex-wrap: wrap;
        justify-content: center;
    }

    .action-btn {
        padding: 10px 18px;
        border: none;
        border-radius: var(--radius);
        font-weight: 600;
        font-size: 0.88rem;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 6px;
        position: relative;
        overflow: hidden;
        min-width: 120px;
        justify-content: center;
    }

    .action-btn:hover {
        transform: translateY(-2px);
        filter: brightness(1.08);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
    }

    .action-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none !important;
        box-shadow: none !important;
        filter: none !important;
    }

    .action-btn.loading::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 18px;
        height: 18px;
        margin: -9px 0 0 -9px;
        border: 2px solid transparent;
        border-top: 2px solid currentColor;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    .action-btn.loading span { opacity: 0; }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .action-btn span { position: relative; z-index: 1; }

    .btn-call     { background: var(--warning); color: white; }
    .btn-confirm  { background: var(--success); color: white; }
    .btn-cancel   { background: var(--danger);  color: white; }
    .btn-schedule { background: var(--info);    color: white; }
    .btn-history  { background: var(--text-muted); color: white; }
    .btn-reactivate { background: #059669; color: white; }

    .no-order {
        text-align: center;
        padding: 3rem;
        color: var(--text-muted);
        grid-column: 1 / -1;
    }

    .no-order i {
        font-size: 3.5rem;
        margin-bottom: 1rem;
        opacity: 0.35;
    }

    .no-order h3 {
        font-size: 1.3rem;
        margin-bottom: 0.5rem;
        color: var(--text);
    }

    /* ═══ RESPONSIVE ═══ */

    @media (max-width: 1200px) {
        .process-content { grid-template-columns: 1fr; }
        .cart-card { max-height: 450px; }
        .cart-items { max-height: 280px; }
    }

    @media (max-width: 1024px) {
        .process-container { margin: 0.5rem; }
        .queue-tabs { gap: 0.5rem; }
        .queue-tab { min-width: 115px; font-size: 0.85rem; }
    }

    @media (max-width: 768px) {
        .process-container {
            margin: 0.35rem;
            border-radius: var(--radius-lg);
            min-height: calc(100vh - 100px);
        }

        .process-header {
            flex-direction: row;
            gap: 0.75rem;
            align-items: center;
            padding: 0.85rem 1rem;
            flex-wrap: wrap;
        }

        .queue-tabs { display: none !important; }

        .queue-selector-mobile {
            display: block;
            flex: 1;
            min-width: 180px;
        }

        .btn-history-header { display: flex; }
        #btn-history { display: none !important; }

        .action-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
            width: 100%;
        }

        .action-btn { min-height: 46px; padding: 0.7rem 0.5rem; font-size: 0.82rem; }
        #btn-reactivate { grid-column: 1 / -1; }

        .process-content { padding: 0.5rem; }

        .order-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
            padding: 0.75rem;
        }

        .order-id { font-size: 1.15rem; }
        .customer-form { padding: 0.75rem; }
        .form-grid { grid-template-columns: 1fr; gap: 0.65rem; }
        .form-control { padding: 0.6rem 0.85rem; min-height: 44px; }
        textarea.form-control { min-height: 90px; }

        .btn-call-direct {
            width: 44px; min-width: 44px; height: 44px; font-size: 1.1rem;
        }

        .cart-items { max-height: 220px; }
        .actions-zone { padding: 0.65rem; }
    }

    @media (max-width: 430px) {
        .process-container { margin: 0.25rem; border-radius: 14px; }
        .process-header { padding: 0.75rem; }
        .order-id { font-size: 1.05rem; }
        .form-control { min-height: 42px; font-size: 0.85rem; }
        .btn-call-direct { width: 42px; min-width: 42px; height: 42px; }
        .cart-items { max-height: 200px; }
        .action-btn { min-height: 46px; font-size: 0.82rem; }
    }

    @media (max-width: 374px) {
        .process-container { margin: 0.15rem; border-radius: 12px; }
        .process-icon { width: 42px; height: 42px; font-size: 1.3rem; }
        .order-id { font-size: 1rem; }
        .form-control { padding: 0.5rem 0.65rem; font-size: 0.8rem; min-height: 40px; }
        .btn-call-direct { width: 40px; min-width: 40px; height: 40px; font-size: 0.9rem; border-radius: 8px; }
        .cart-items { max-height: 180px; }
        .action-btn { font-size: 0.75rem; min-height: 42px; }
    }

    @media (max-height: 500px) and (orientation: landscape) {
        .process-container { min-height: auto; }
        .process-header { padding: 0.5rem 1rem; }
        .process-icon { width: 40px; height: 40px; font-size: 1.2rem; }
        .cart-items { max-height: 150px; }
        .no-order { padding: 1rem; }
        .no-order i { font-size: 2rem; margin-bottom: 0.5rem; }
    }

    @media (hover: none) and (pointer: coarse) {
        .queue-tab, .action-btn, .form-control, .qty-btn, .remove-btn, .suggestion-item {
            min-height: 44px; min-width: 44px;
        }
        .queue-tab:active, .action-btn:active, .qty-btn:active, .remove-btn:active {
            opacity: 0.7; transform: scale(0.97);
        }
        .queue-tab:hover, .action-btn:hover, .order-card:hover, .cart-item:hover {
            transform: none;
        }
    }

    /* Animations */
    .fade-in { animation: fadeIn 0.4s ease-out; }
    .slide-up { animation: slideUp 0.4s ease-out; }

    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

    /* Scrollbar */
    .cart-items::-webkit-scrollbar { width: 5px; }
    .cart-items::-webkit-scrollbar-track { background: var(--bg-card-alt); border-radius: 3px; }
    .cart-items::-webkit-scrollbar-thumb { background: var(--success); border-radius: 3px; }
</style>
@endsection

@section('content')
<div class="process-container">
    <!-- Header avec onglets des files -->
    <div class="process-header">
        <!-- Onglets pour desktop -->
        <div class="queue-tabs">
            <div class="queue-tab active" data-queue="standard">
                <div class="queue-icon">
                    <i class="fas fa-phone"></i>
                </div>
                <div>File Standard</div>
                <div class="queue-badge" id="standard-count">0</div>
            </div>

            <div class="queue-tab" data-queue="dated">
                <div class="queue-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div>File Datée</div>
                <div class="queue-badge" id="dated-count">0</div>
            </div>

            <div class="queue-tab" data-queue="old">
                <div class="queue-icon">
                    <i class="fas fa-history"></i>
                </div>
                <div>File Ancienne</div>
                <div class="queue-badge" id="old-count">0</div>
            </div>

            <div class="queue-tab" data-queue="restock">
                <div class="queue-icon">
                    <i class="fas fa-box-open"></i>
                </div>
                <div>Retour en Stock</div>
                <div class="queue-badge" id="restock-count">0</div>
            </div>
        </div>

        <!-- Dropdown pour mobile -->
        <div class="queue-selector-mobile">
            <select class="queue-select" id="queue-select-mobile">
                <option value="standard">📞 File Standard (<span id="standard-count-mobile">0</span>)</option>
                <option value="dated">📅 File Datée (<span id="dated-count-mobile">0</span>)</option>
                <option value="old">⏰ File Ancienne (<span id="old-count-mobile">0</span>)</option>
                <option value="restock">📦 Retour en Stock (<span id="restock-count-mobile">0</span>)</option>
            </select>
        </div>

        <!-- Bouton historique (mobile seulement) -->
        <button type="button" class="btn-history-header" id="btn-history-mobile" title="Historique">
            <i class="fas fa-history"></i>
        </button>
    </div>

    <!-- Contenu principal -->
    <div class="process-content" id="process-content">
        <!-- Message de chargement -->
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

        <!-- Contenu principal avec commande -->
        <div id="main-content" style="display: none; grid-column: 1 / -1;">
            <div class="process-content">
                <!-- Zone de la commande (gauche) -->
                <div class="order-zone">
                    <div class="order-card slide-up">
                        <!-- En-tête de la commande -->
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
                                <!-- Tags de la commande -->
                                <div class="order-tags" id="order-tags">
                                    <!-- Les tags seront ajoutés dynamiquement -->
                                </div>
                            </div>
                            <div class="order-status" id="order-status">Nouvelle</div>
                        </div>

                        <!-- Alerte pour les doublons -->
                        <div class="duplicate-alert" id="duplicate-alert">
                            <div class="alert-header">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span>Commandes doublons détectées</span>
                            </div>
                            <div class="alert-message">
                                <span id="duplicate-text">Ce client a d'autres commandes dans le système</span>
                                <button type="button" class="btn btn-sm btn-warning" onclick="showDuplicatesModal()">
                                    <i class="fas fa-eye"></i> Voir
                                </button>
                            </div>
                        </div>

                        <!-- Info retour en stock -->
                        <div class="restock-info" id="restock-info" style="display: none;">
                            <div class="alert-header">
                                <i class="fas fa-check-circle"></i>
                                <span>Commande retour en stock</span>
                            </div>
                            <div class="alert-message">
                                Cette commande était suspendue mais tous ses produits sont maintenant disponibles. 
                                Elle peut être traitée normalement ou réactivée définitivement.
                            </div>
                        </div>

                        <!-- Formulaire client -->
                        <div class="customer-form">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-user"></i>
                                        Nom complet <span class="required">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="customer_name" placeholder="Nom et prénom">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-phone"></i>
                                        Téléphone principal <span class="required">*</span>
                                    </label>
                                    <div class="phone-input-wrapper">
                                        <input type="tel" class="form-control" id="customer_phone" placeholder="12345678" maxlength="8">
                                        <button type="button" class="btn-call-direct" id="btn-call-customer" title="Appeler le client">
                                            <i class="fas fa-phone"></i>
                                        </button>
                                    </div>
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
                                        Gouvernorat <span class="required">*</span>
                                    </label>
                                    <select class="form-control" id="customer_governorate">
                                        <option value="">Sélectionner un gouvernorat</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-city"></i>
                                        Ville <span class="required">*</span>
                                    </label>
                                    <select class="form-control" id="customer_city">
                                        <option value="">Sélectionner une ville</option>
                                    </select>
                                </div>
                                
                                <div class="form-group form-group-full">
                                    <label class="form-label">
                                        <i class="fas fa-map-marker-alt"></i>
                                        Adresse complète <span class="required">*</span>
                                    </label>
                                    <textarea class="form-control" id="customer_address" rows="3" placeholder="Adresse détaillée"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Zone panier (droite) -->
                <div class="cart-zone">
                    <div class="cart-card slide-up" id="cart-card">
                        <div class="cart-header cart-toggle" id="cart-toggle">
                            <div class="cart-title">
                                <i class="fas fa-shopping-cart"></i>
                                Panier
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <div class="cart-count" id="cart-item-count">0 article(s)</div>
                                <i class="fas fa-chevron-down"></i>
                            </div>
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

                            <!-- Items du panier -->
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
                                    <span class="summary-label">Total produits:</span>
                                    <span class="summary-value" id="cart-total">0.000 TND</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Zone des actions -->
                <div class="actions-zone slide-up">
                    <div class="action-buttons" id="action-buttons">
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

<!-- Inclusion des modales -->
@include('admin.process.modals')

@endsection

@section('scripts')
<script>
// Shared state — accessible from modals script
var cartItems = [];
var currentOrder = null;
var currentQueue = 'standard';

$(document).ready(function() {
    let searchTimeout;
    let isLoadingQueue = false;
    let isLoadingCounts = false;
    
    // Initialisation
    function initialize() {
        if (typeof $ === 'undefined') {
            console.error('jQuery non chargé!');
            return;
        }
        
        const csrfToken = $('meta[name="csrf-token"]').attr('content');
        if (!csrfToken) {
            console.error('Token CSRF non trouvé!');
            return;
        }
        
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
        // Onglets de files (desktop)
        $('.queue-tab').on('click', function() {
            const queue = $(this).data('queue');
            if (queue !== currentQueue) {
                switchQueue(queue);
            }
        });

        // Dropdown mobile pour sélection de file
        $('#queue-select-mobile').on('change', function() {
            const queue = $(this).val();
            if (queue !== currentQueue) {
                switchQueue(queue);
            }
        });

        // Toggle panier (mobile)
        $('#cart-toggle').on('click', function() {
            $('#cart-card').toggleClass('collapsed');
        });

        // Bouton historique mobile
        $('#btn-history-mobile').on('click', function() {
            showHistoryModal();
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
        
        // Masquer suggestions
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.search-wrapper').length) {
                hideSearchSuggestions();
            }
        });
        
        // Boutons d'action avec pré-validation pour confirmer
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

        // Validation en temps réel des champs obligatoires
        $('#customer_name, #customer_governorate, #customer_city, #customer_address').on('input change', function() {
            validateField($(this));
        });

        // Validation du téléphone (8 chiffres exactement, pas d'espaces)
        $('#customer_phone').on('input', function() {
            let value = $(this).val();
            // Supprimer tous les caractères non numériques
            value = value.replace(/\D/g, '');
            // Limiter à 8 chiffres
            if (value.length > 8) {
                value = value.substring(0, 8);
            }
            $(this).val(value);
            validatePhoneField($(this));
        });

        // Bouton pour appeler directement le client
        $('#btn-call-customer').on('click', function() {
            const phone = $('#customer_phone').val().trim();
            if (phone && phone.length === 8) {
                window.location.href = 'tel:+216' + phone;
            } else {
                showNotification('Numéro de téléphone invalide (8 chiffres requis)', 'error');
            }
        });
    }
    
    // Fonction de validation d'un champ
    function validateField($field) {
        const value = $field.val().trim();
        const fieldId = $field.attr('id');

        let isValid = true;

        switch (fieldId) {
            case 'customer_name':
                isValid = value.length >= 2;
                break;
            case 'customer_governorate':
            case 'customer_city':
                isValid = value !== '';
                break;
            case 'customer_address':
                isValid = value.length >= 5;
                break;
        }

        if (isValid) {
            $field.removeClass('is-invalid');
        } else {
            $field.addClass('is-invalid');
        }

        return isValid;
    }

    // Fonction de validation du téléphone (8 chiffres exactement)
    function validatePhoneField($field) {
        const value = $field.val().trim();
        const isValid = /^\d{8}$/.test(value);

        if (isValid) {
            $field.removeClass('is-invalid');
        } else if (value.length > 0) {
            $field.addClass('is-invalid');
        }

        return isValid;
    }

    // Fonction de pré-validation avant d'afficher le modal de confirmation
    function validateBeforeConfirm() {
        let errors = [];
        let hasFieldErrors = false;
        
        // Validation des champs client obligatoires
        const customerName = $('#customer_name').val().trim();
        const customerPhone = $('#customer_phone').val().trim();
        const customerGovernorate = $('#customer_governorate').val();
        const customerCity = $('#customer_city').val();
        const customerAddress = $('#customer_address').val().trim();

        if (!customerName || customerName.length < 2) {
            errors.push('Le nom du client est obligatoire (minimum 2 caractères)');
            $('#customer_name').addClass('is-invalid');
            hasFieldErrors = true;
        } else {
            $('#customer_name').removeClass('is-invalid');
        }

        if (!customerPhone || !/^\d{8}$/.test(customerPhone)) {
            errors.push('Le téléphone doit contenir exactement 8 chiffres (pas d\'espaces)');
            $('#customer_phone').addClass('is-invalid');
            hasFieldErrors = true;
        } else {
            $('#customer_phone').removeClass('is-invalid');
        }
        
        if (!customerGovernorate) {
            errors.push('Le gouvernorat est obligatoire');
            $('#customer_governorate').addClass('is-invalid');
            hasFieldErrors = true;
        } else {
            $('#customer_governorate').removeClass('is-invalid');
        }
        
        if (!customerCity) {
            errors.push('La ville est obligatoire');
            $('#customer_city').addClass('is-invalid');
            hasFieldErrors = true;
        } else {
            $('#customer_city').removeClass('is-invalid');
        }
        
        if (!customerAddress || customerAddress.length < 5) {
            errors.push('L\'adresse doit contenir au moins 5 caractères');
            $('#customer_address').addClass('is-invalid');
            hasFieldErrors = true;
        } else {
            $('#customer_address').removeClass('is-invalid');
        }
        
        // Validation du panier
        if (!cartItems || cartItems.length === 0) {
            errors.push('Veuillez ajouter au moins un produit au panier');
        }
        
        // Vérification du stock disponible
        let stockErrors = [];
        if (cartItems && cartItems.length > 0) {
            cartItems.forEach(item => {
                if (item.product && item.product.stock < item.quantity) {
                    stockErrors.push(`Stock insuffisant pour ${item.product.name} (disponible: ${item.product.stock}, demandé: ${item.quantity})`);
                }
            });
        }
        
        // Afficher les erreurs
        if (errors.length > 0) {
            const errorMessage = '<div style="text-align: left;">' + errors.map(error => `• ${error}`).join('<br>') + '</div>';
            showNotification(errorMessage, 'error');
            
            // Focus sur le premier champ en erreur
            if (!customerName || customerName.length < 2) {
                $('#customer_name').focus();
            } else if (!customerGovernorate) {
                $('#customer_governorate').focus();
            } else if (!customerCity) {
                $('#customer_city').focus();
            } else if (!customerAddress || customerAddress.length < 5) {
                $('#customer_address').focus();
            }
            
            return false;
        }
        
        if (stockErrors.length > 0) {
            const stockMessage = '<div style="text-align: left;">' + stockErrors.map(error => `• ${error}`).join('<br>') + '</div>';
            showNotification(stockMessage, 'error');
            return false;
        }
        
        return true;
    }
    
    // Gestion des files
    function loadQueueCounts() {
        if (isLoadingCounts) return;

        isLoadingCounts = true;

        $.get('/confirmi/employee/process/counts')
            .done(function(data) {
                // Mise à jour des badges desktop
                $('#standard-count').text(data.standard || 0);
                $('#dated-count').text(data.dated || 0);
                $('#old-count').text(data.old || 0);
                $('#restock-count').text(data.restock || 0);

                // Mise à jour du dropdown mobile
                updateMobileQueueOptions(data);
            })
            .fail(function(xhr, status, error) {
                console.error('Erreur compteurs:', error);
                showNotification('Erreur lors du chargement des compteurs', 'error');
            })
            .always(function() {
                isLoadingCounts = false;
            });
    }

    function updateMobileQueueOptions(counts) {
        const select = $('#queue-select-mobile');
        select.html(`
            <option value="standard" ${currentQueue === 'standard' ? 'selected' : ''}>📞 File Standard (${counts.standard || 0})</option>
            <option value="dated" ${currentQueue === 'dated' ? 'selected' : ''}>📅 File Datée (${counts.dated || 0})</option>
            <option value="old" ${currentQueue === 'old' ? 'selected' : ''}>⏰ File Ancienne (${counts.old || 0})</option>
            <option value="restock" ${currentQueue === 'restock' ? 'selected' : ''}>📦 Retour en Stock (${counts.restock || 0})</option>
        `);
    }
    
    function switchQueue(queue) {
        if (isLoadingQueue) return;

        // Mettre à jour les onglets desktop
        $('.queue-tab').removeClass('active');
        $(`.queue-tab[data-queue="${queue}"]`).addClass('active');

        // Mettre à jour le dropdown mobile
        $('#queue-select-mobile').val(queue);

        currentQueue = queue;
        currentOrder = null;

        showLoading();
        setTimeout(() => loadCurrentQueue(), 100);
    }
    
    function loadCurrentQueue() {
        if (isLoadingQueue) return;
        
        isLoadingQueue = true;
        showLoading();
        
        const endpoint = `/confirmi/employee/process/api/${currentQueue}`;
        
        $.get(endpoint)
            .done(function(data) {
                if (data.hasOrder && data.order) {
                    currentOrder = data.order;
                    displayOrder(data.order);
                    showMainContent();
                } else {
                    showNoOrderMessage();
                }
            })
            .fail(function(xhr, status, error) {
                console.error('Erreur chargement file:', error);
                
                let errorMsg = 'Erreur lors du chargement de la commande';
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.error) {
                        errorMsg = response.error;
                    }
                } catch (e) {}
                
                showNotification(errorMsg, 'error');
                showNoOrderMessage();
            })
            .always(function() {
                isLoadingQueue = false;
            });
    }
    
    // Affichage des commandes
    function displayOrder(order) {
        if (!order || !order.id) {
            showNoOrderMessage();
            return;
        }
        
        try {
            // Informations de base
            $('#order-number').text(order.id || 'N/A');
            $('#order-date').text(formatDate(order.created_at));
            $('#order-attempts').text(`${order.attempts_count || 0} tentative(s)`);
            $('#order-last-attempt').text(order.last_attempt_at ? formatDate(order.last_attempt_at) : 'Jamais');
            
            // Statut
            const statusElement = $('#order-status');
            const status = order.status || 'nouvelle';
            statusElement.removeClass().addClass('order-status').addClass(`status-${status}`);
            statusElement.text(capitalizeFirst(status));
            
            // Afficher les tags
            displayOrderTags(order);
            
            // Affichage spécial selon le type de queue
            updateQueueSpecificDisplay(order);
            
            // Afficher les doublons si existants
            displayDuplicateInfo(order);
            
            // Formulaire client
            updateCustomerForm(order);
            
            // Panier
            updateCartFromOrder(order);
            
        } catch (error) {
            console.error('Erreur affichage commande:', error);
            showNotification('Erreur lors de l\'affichage de la commande', 'error');
            showNoOrderMessage();
        }
    }

    // Affichage des tags de la commande
    function displayOrderTags(order) {
        const tagsContainer = $('#order-tags');
        tagsContainer.empty();

        // Tag priorité
        if (order.priority && order.priority !== 'normale') {
            const priorityTag = $(`
                <div class="order-tag tag-priority-${order.priority}">
                    <i class="fas fa-${order.priority === 'urgente' ? 'exclamation-triangle' : order.priority === 'vip' ? 'crown' : 'flag'}"></i>
                    <span>${capitalizeFirst(order.priority)}</span>
                </div>
            `);
            tagsContainer.append(priorityTag);
        }

        // Tag assignation
        if (order.is_assigned && order.employee) {
            const assignedTag = $(`
                <div class="order-tag tag-assigned">
                    <i class="fas fa-user-check"></i>
                    <span>Assignée</span>
                </div>
            `);
            tagsContainer.append(assignedTag);
        }

        // Tag suspension
        if (order.is_suspended) {
            const suspendedTag = $(`
                <div class="order-tag tag-suspended">
                    <i class="fas fa-pause-circle"></i>
                    <span>Suspendue</span>
                </div>
            `);
            tagsContainer.append(suspendedTag);
        }

        // Tag planifiée
        if (order.scheduled_date) {
            const scheduledTag = $(`
                <div class="order-tag tag-scheduled">
                    <i class="fas fa-calendar-check"></i>
                    <span>Planifiée</span>
                </div>
            `);
            tagsContainer.append(scheduledTag);
        }
    }
    
    function displayDuplicateInfo(order) {
        const duplicateAlert = $('#duplicate-alert');
        const duplicateText = $('#duplicate-text');
        
        if (order.duplicate_info && order.duplicate_info.has_duplicates) {
            const count = order.duplicate_info.duplicates_count;
            duplicateText.text(`Ce client a ${count} autre${count > 1 ? 's' : ''} commande${count > 1 ? 's' : ''} dans le système`);
            duplicateAlert.addClass('show');
        } else {
            duplicateAlert.removeClass('show');
        }
    }
    
    function updateQueueSpecificDisplay(order) {
        if (currentQueue === 'restock' && order.is_suspended) {
            $('#restock-info').show().addClass('show');
            $('#btn-reactivate').show();
            $('#btn-call span').text('Reporter');
        } else {
            $('#restock-info').hide().removeClass('show');
            $('#btn-reactivate').hide();
            $('#btn-call span').text('Ne répond pas');
        }
    }
    
    function updateCustomerForm(order) {
        $('#customer_name').val(order.customer_name || '');

        // Nettoyer le numéro de téléphone (enlever espaces, +216, etc.)
        let phone = order.customer_phone || '';
        phone = phone.replace(/\D/g, ''); // Enlever tous les caractères non numériques
        if (phone.startsWith('216')) {
            phone = phone.substring(3); // Enlever le préfixe +216
        }
        if (phone.length > 8) {
            phone = phone.substring(0, 8); // Limiter à 8 chiffres
        }
        $('#customer_phone').val(phone);

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
    }
    
    function updateCartFromOrder(order) {
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
    
    // Gestion du panier
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
        let total = 0;
        
        if (cartItems && Array.isArray(cartItems)) {
            total = cartItems.reduce((sum, item) => {
                const itemTotal = parseFloat(item.total_price) || 0;
                return sum + itemTotal;
            }, 0);
        }
        
        $('#cart-total').text(total.toFixed(3) + ' TND');
    }
    
    // Recherche de produits
    function searchProducts(query) {
        $.get('/confirmi/employee/process/products/search', { search: query })
            .done(function(products) {
                showSearchSuggestions(products);
            })
            .fail(function(xhr) {
                console.error('Erreur recherche produits');
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
    
    // Gestion géographique
    function loadRegions() {
        $.get('/confirmi/employee/process/regions')
            .done(function(regions) {
                const select = $('#customer_governorate');
                select.html('<option value="">Sélectionner un gouvernorat</option>');
                
                regions.forEach(region => {
                    select.append(`<option value="${region.id}">${region.name}</option>`);
                });
            })
            .fail(function(xhr) {
                console.error('Erreur chargement régions');
            });
    }
    
    function loadCities(regionId, selectedCityId = null) {
        if (!regionId) {
            $('#customer_city').html('<option value="">Sélectionner une ville</option>');
            return;
        }
        
        $.get('/confirmi/employee/process/cities', { region_id: regionId })
            .done(function(cities) {
                const select = $('#customer_city');
                select.html('<option value="">Sélectionner une ville</option>');
                
                cities.forEach(city => {
                    const selected = selectedCityId == city.id ? 'selected' : '';
                    select.append(`<option value="${city.id}" ${selected}>${city.name}</option>`);
                });
            })
            .fail(function(xhr) {
                console.error('Erreur chargement villes');
            });
    }
    
    // Actions de traitement avec pré-validation pour confirmer
    function showActionModal(action) {
        if (!currentOrder) {
            showNotification('Aucune commande sélectionnée', 'error');
            return;
        }
        
        // Pré-validation spéciale pour l'action confirmer
        if (action === 'confirm') {
            if (!validateBeforeConfirm()) {
                return; // Arrêter ici si la validation échoue
            }
        }
        
        switch (action) {
            case 'call':
                $('#callModal').modal('show');
                break;
            case 'confirm':
                $('#confirmModal').modal('show');
                break;
            case 'cancel':
                $('#cancelModal').modal('show');
                break;
            case 'schedule':
                $('#scheduleModal').modal('show');
                break;
            case 'reactivate':
                $('#reactivateModal').modal('show');
                break;
        }
    }
    
    function showHistoryModal() {
        if (!currentOrder) {
            showNotification('Aucune commande sélectionnée', 'error');
            return;
        }
        
        $.get(`/confirmi/employee/process/orders/${currentOrder.id}/history-modal`)
            .done(function(history) {
                $('#history-content').html(history);
                $('#historyModal').modal('show');
            })
            .fail(function(xhr) {
                showNotification('Erreur lors du chargement de l\'historique', 'error');
            });
    }
    
    // Modal doublons
    window.showDuplicatesModal = function() {
        if (!currentOrder || !currentOrder.duplicate_info || !currentOrder.duplicate_info.has_duplicates) {
            showNotification('Aucun doublon détecté pour cette commande', 'error');
            return;
        }
        
        const duplicates = currentOrder.duplicate_info.duplicates;
        let modalContent = '<div class="table-responsive"><table class="table table-striped">';
        modalContent += '<thead><tr><th>ID</th><th>Statut</th><th>Client</th><th>Total</th><th>Date</th></tr></thead><tbody>';
        
        duplicates.forEach(duplicate => {
            const statusClass = `status-${duplicate.status}`;
            modalContent += `
                <tr>
                    <td><strong>#${duplicate.id}</strong></td>
                    <td><span class="badge ${statusClass}">${duplicate.status}</span></td>
                    <td>
                        <div><strong>${duplicate.customer_name || 'N/A'}</strong></div>
                        <div><small>${duplicate.customer_phone}</small></div>
                    </td>
                    <td><strong>${parseFloat(duplicate.total_price || 0).toFixed(3)} TND</strong></td>
                    <td><small>${duplicate.created_at}</small></td>
                </tr>
            `;
        });
        
        modalContent += '</tbody></table></div>';
        
        $('#duplicates-content').html(modalContent);
        $('#duplicatesModal').modal('show');
    }
    
    // Fonction globale pour les actions
    window.processAction = function(action, formData) {
        if (!currentOrder) {
            showNotification('Aucune commande sélectionnée', 'error');
            return;
        }
        
        const requestData = {
            action: action,
            queue: currentQueue,
            ...formData
        };
        
        // Ajouter les données du panier et client pour l'action confirm
        if (action === 'confirm') {
            const customerName = $('#customer_name').val().trim();
            const customerGovernorate = $('#customer_governorate').val();
            const customerCity = $('#customer_city').val();
            const customerAddress = $('#customer_address').val().trim();
            
            requestData.cart_items = cartItems;
            requestData.customer_name = customerName;
            requestData.customer_phone_2 = $('#customer_phone_2').val();
            requestData.customer_governorate = customerGovernorate;
            requestData.customer_city = customerCity;
            requestData.customer_address = customerAddress;
        }
        
        // Désactiver les boutons
        $('.action-btn').prop('disabled', true).addClass('loading');
        
        // Envoyer la requête
        $.ajax({
            url: `/confirmi/employee/process/action/${currentOrder.id}`,
            method: 'POST',
            data: requestData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        })
        .done(function(response) {
            showNotification('Action traitée avec succès!', 'success');
            $('.modal').modal('hide');
            
            setTimeout(() => {
                loadQueueCounts();
                setTimeout(() => loadCurrentQueue(), 500);
            }, 1000);
        })
        .fail(function(xhr, status, error) {
            let errorMessage = 'Erreur lors du traitement de l\'action';
            let isConfirmiLock = false;
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.locked_by_confirmi) {
                    isConfirmiLock = true;
                    errorMessage = '🔒 Commande verrouillée par Confirmi — passage à la suivante.';
                } else if (response.error) {
                    errorMessage = response.error;
                } else if (response.errors) {
                    const errors = Object.values(response.errors).flat();
                    errorMessage = errors.join(', ');
                }
            } catch (e) {}
            
            showNotification(errorMessage, isConfirmiLock ? 'warning' : 'error');
            
            if (isConfirmiLock) {
                $('.modal').modal('hide');
                setTimeout(() => loadCurrentQueue(), 1500);
            }
        })
        .always(function() {
            setTimeout(() => {
                $('.action-btn').prop('disabled', false).removeClass('loading');
            }, 1000);
        });
    };
    
    // Utilitaires
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
    
    window.showNotification = function(message, type = 'info') {
        const alertClass = {
            'success': 'alert-success',
            'error': 'alert-danger',
            'warning': 'alert-warning',
            'info': 'alert-info'
        }[type] || 'alert-info';
        
        const alert = $(`
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
                 style="top: 100px; right: 20px; z-index: 9999; min-width: 300px; max-width: 500px;">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        
        $('body').append(alert);
        
        setTimeout(() => {
            alert.fadeOut(() => alert.remove());
        }, type === 'error' ? 8000 : 5000);
    }
    
    // Initialisation
    initialize();
    
    // Actualiser les compteurs toutes les 60 secondes
    setInterval(function() {
        if (!isLoadingQueue && !isLoadingCounts) {
            loadQueueCounts();
        }
    }, 60000);
    
    // Actualiser la file courante toutes les 2 minutes
    setInterval(function() {
        if (!isLoadingQueue && !isLoadingCounts && $('.modal:visible').length === 0) {
            loadCurrentQueue();
        }
    }, 120000);
});
</script>
@endsection