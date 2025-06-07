@extends('layouts.admin')

@section('title', 'Gestion des Commandes Doubles')

@section('css')
<style>
    /* Variables CSS pour le thème */
    :root {
        --primary: #6366f1;
        --primary-dark: #4f46e5;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --info: #3b82f6;
        --gray-50: #f9fafb;
        --gray-100: #f3f4f6;
        --gray-200: #e5e7eb;
        --gray-300: #d1d5db;
        --gray-400: #9ca3af;
        --gray-500: #6b7280;
        --gray-600: #4b5563;
        --gray-700: #374151;
        --gray-800: #1f2937;
        --gray-900: #111827;
        --white: #ffffff;
        --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
        --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        --radius: 0.5rem;
        --radius-lg: 0.75rem;
        --radius-xl: 1rem;
        --radius-2xl: 1.5rem;
    }

    body {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        color: var(--gray-700);
        line-height: 1.6;
        overflow-x: hidden;
    }

    .container-fluid {
        max-width: 1800px;
        margin: 0 auto;
        padding: 1rem 1.5rem 2rem;
    }

    /* Header optimisé pour une ligne */
    .page-header {
        background: linear-gradient(135deg, var(--white) 0%, #f8fafc 100%);
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-lg);
        padding: 1.5rem 2rem;
        margin-bottom: 1.5rem;
        border: 1px solid var(--gray-200);
        position: relative;
        overflow: hidden;
    }

    .page-header::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 150px;
        height: 150px;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        opacity: 0.05;
        border-radius: 50%;
        transform: translate(30%, -30%);
    }

    .header-content {
        position: relative;
        z-index: 1;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .header-icon {
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        border-radius: var(--radius-lg);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--white);
        font-size: 1.25rem;
        box-shadow: var(--shadow-md);
    }

    .header-text h1 {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--gray-900);
        margin-bottom: 0.25rem;
    }

    .header-text p {
        color: var(--gray-600);
        font-size: 0.875rem;
        margin: 0;
    }

    .header-actions {
        display: flex;
        gap: 0.75rem;
    }

    /* Dashboard Stats en une ligne */
    .stats-container {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.5rem;
        overflow-x: auto;
        padding-bottom: 0.5rem;
    }

    .stats-card {
        background: linear-gradient(135deg, var(--white) 0%, #f8fafc 100%);
        border-radius: var(--radius-xl);
        padding: 1.25rem 1.5rem;
        box-shadow: var(--shadow-md);
        border: 1px solid var(--gray-200);
        transition: all 0.3s ease;
        min-width: 200px;
        flex-shrink: 0;
        position: relative;
        overflow: hidden;
    }

    .stats-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }

    .stats-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--primary) 0%, var(--primary-dark) 100%);
    }

    .stats-card.warning::before {
        background: linear-gradient(90deg, var(--warning) 0%, #d97706 100%);
    }

    .stats-card.success::before {
        background: linear-gradient(90deg, var(--success) 0%, #059669 100%);
    }

    .stats-card.info::before {
        background: linear-gradient(90deg, var(--info) 0%, #1d4ed8 100%);
    }

    .stats-content {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .stats-icon {
        width: 40px;
        height: 40px;
        border-radius: var(--radius);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.125rem;
        color: var(--white);
        flex-shrink: 0;
    }

    .stats-icon.primary {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    }

    .stats-icon.warning {
        background: linear-gradient(135deg, var(--warning) 0%, #d97706 100%);
    }

    .stats-icon.success {
        background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
    }

    .stats-icon.info {
        background: linear-gradient(135deg, var(--info) 0%, #1d4ed8 100%);
    }

    .stats-text {
        flex: 1;
    }

    .stats-number {
        font-size: 1.75rem;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 0.25rem;
        color: var(--gray-900);
    }

    .stats-label {
        font-size: 0.8rem;
        color: var(--gray-600);
        font-weight: 500;
    }

    /* Toolbar simplifié pour recherche et actions */
    .unified-toolbar {
        background: linear-gradient(135deg, var(--white) 0%, #f8fafc 100%);
        border-radius: var(--radius-xl);
        padding: 1.25rem 1.75rem;
        margin-bottom: 1.5rem;
        box-shadow: var(--shadow-lg);
        border: 1px solid var(--gray-200);
    }

    .toolbar-main {
        display: flex;
        gap: 1.25rem;
        align-items: center;
        margin-bottom: 1rem;
        flex-wrap: wrap;
    }

    .search-section {
        flex: 1;
        min-width: 280px;
        position: relative;
    }

    .search-input {
        width: 100%;
        padding: 0.75rem 1rem 0.75rem 2.75rem;
        border: 2px solid var(--gray-200);
        border-radius: var(--radius-lg);
        font-size: 0.9rem;
        transition: all 0.3s ease;
        background: var(--white);
        color: var(--gray-800);
        box-shadow: var(--shadow-sm);
    }

    .search-input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgb(99 102 241 / 0.1);
        outline: none;
    }

    .search-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gray-400);
        font-size: 1rem;
    }

    .search-loading {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        display: none;
    }

    .main-actions {
        display: flex;
        gap: 0.75rem;
        align-items: center;
    }

    .filters-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .filters-left {
        display: flex;
        gap: 1rem;
        align-items: center;
        flex-wrap: wrap;
    }

    .filter-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .filter-label {
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--gray-700);
        white-space: nowrap;
    }

    .filter-select, .filter-input {
        padding: 0.5rem 0.75rem;
        border: 2px solid var(--gray-200);
        border-radius: var(--radius);
        font-size: 0.8rem;
        transition: all 0.2s ease;
        background: var(--white);
        min-width: 100px;
    }

    .filter-select:focus, .filter-input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgb(99 102 241 / 0.1);
        outline: none;
    }

    .filters-right {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .results-count {
        color: var(--gray-600);
        font-size: 0.8rem;
        font-weight: 500;
    }

    .btn-toolbar {
        padding: 0.625rem 1rem;
        border-radius: var(--radius);
        font-weight: 600;
        font-size: 0.8rem;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
        position: relative;
        overflow: hidden;
    }

    .btn-toolbar:hover {
        transform: translateY(-1px);
        box-shadow: var(--shadow-md);
    }

    .btn-toolbar:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }

    .btn-primary-action {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: var(--white);
        box-shadow: var(--shadow-sm);
    }

    .btn-success-action {
        background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
        color: var(--white);
        box-shadow: var(--shadow-sm);
    }

    .btn-warning-action {
        background: linear-gradient(135deg, var(--warning) 0%, #d97706 100%);
        color: var(--white);
        box-shadow: var(--shadow-sm);
    }

    .btn-outline {
        background: var(--white);
        color: var(--gray-700);
        border: 2px solid var(--gray-300);
        box-shadow: var(--shadow-sm);
    }

    .btn-outline:hover {
        border-color: var(--primary);
        color: var(--primary);
    }

    /* Table principale optimisée */
    .main-table {
        background: linear-gradient(135deg, var(--white) 0%, #f8fafc 100%);
        border-radius: var(--radius-xl);
        overflow: hidden;
        box-shadow: var(--shadow-lg);
        border: 1px solid var(--gray-200);
        height: calc(100vh - 400px);
        display: flex;
        flex-direction: column;
    }

    .table-header {
        background: linear-gradient(135deg, #f8fafc 0%, var(--gray-100) 100%);
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--gray-200);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-shrink: 0;
    }

    .table-title {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .table-title h6 {
        font-size: 1rem;
        font-weight: 700;
        color: var(--gray-900);
        margin: 0;
    }

    .table-title .badge {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: var(--white);
        padding: 0.25rem 0.5rem;
        border-radius: var(--radius);
        font-size: 0.7rem;
        font-weight: 600;
    }

    .table-controls {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .table-responsive {
        flex: 1;
        overflow-y: auto;
        min-height: 0;
    }

    .table {
        margin: 0;
        width: 100%;
    }

    .table thead th {
        background: linear-gradient(135deg, #f8fafc 0%, var(--gray-100) 100%);
        border: none;
        font-weight: 700;
        color: var(--gray-800);
        padding: 0.875rem 1rem;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        position: sticky;
        top: 0;
        z-index: 10;
        border-bottom: 2px solid var(--gray-200);
    }

    .table tbody td {
        border: none;
        padding: 1rem;
        vertical-align: middle;
        border-bottom: 1px solid var(--gray-100);
        font-size: 0.85rem;
    }

    .table tbody tr {
        transition: all 0.2s ease;
    }

    .table tbody tr:hover {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.02) 0%, rgba(99, 102, 241, 0.05) 100%);
    }

    .table tbody tr.table-active {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(99, 102, 241, 0.15) 100%);
    }

    /* Client info plus clair */
    .client-info {
        display: flex;
        flex-direction: column;
        gap: 0.3rem;
    }

    .client-phone {
        font-weight: 800;
        color: var(--gray-900);
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .client-name {
        color: var(--gray-600);
        font-size: 0.8rem;
        font-style: italic;
        padding-left: 1.2rem;
    }

    /* Badges plus clairs */
    .badge-count {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: var(--white);
        padding: 0.4rem 0.7rem;
        border-radius: var(--radius-lg);
        font-weight: 800;
        font-size: 0.85rem;
        box-shadow: var(--shadow-sm);
        min-width: 45px;
        text-align: center;
    }

    .amount-display {
        font-weight: 800;
        color: var(--success);
        font-size: 0.95rem;
    }

    .date-display {
        font-size: 0.8rem;
        color: var(--gray-600);
        font-weight: 500;
    }

    .priority-badge {
        padding: 0.4rem 0.8rem;
        border-radius: var(--radius-lg);
        font-size: 0.75rem;
        font-weight: 700;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        box-shadow: var(--shadow-sm);
    }

    .priority-doublé {
        background: linear-gradient(135deg, #d4a147 0%, #b8941f 100%);
        color: var(--white);
    }

    .auto-merge-indicator {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.25rem 0.6rem;
        background: rgba(16, 185, 129, 0.1);
        border: 1px solid rgba(16, 185, 129, 0.3);
        border-radius: var(--radius-lg);
        font-size: 0.7rem;
        color: #059669;
        font-weight: 600;
        margin-top: 0.3rem;
    }

    /* Actions group plus clair */
    .action-group {
        display: flex;
        gap: 0.5rem;
        align-items: center;
        justify-content: center;
    }

    .btn-action {
        padding: 0.6rem;
        border-radius: var(--radius-lg);
        font-size: 0.85rem;
        border: none;
        transition: all 0.3s ease;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        position: relative;
        overflow: hidden;
        box-shadow: var(--shadow-sm);
    }

    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .btn-merge {
        background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
        color: var(--white);
    }

    .btn-review {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: var(--white);
    }

    .btn-orders {
        background: linear-gradient(135deg, var(--info) 0%, #1d4ed8 100%);
        color: var(--white);
    }

    .btn-detail {
        background: linear-gradient(135deg, var(--warning) 0%, #d97706 100%);
        color: var(--white);
    }

    /* Checkbox pour sélection */
    .item-checkbox {
        width: 18px;
        height: 18px;
        accent-color: var(--primary);
        cursor: pointer;
    }

    /* Pagination optimisée */
    .pagination-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 1rem;
        background: linear-gradient(135deg, #f8fafc 0%, var(--gray-100) 100%);
        border-top: 1px solid var(--gray-200);
        flex-shrink: 0;
    }

    .pagination {
        display: flex;
        align-items: center;
        gap: 0.375rem;
    }

    .page-item {
        list-style: none;
    }

    .page-link {
        padding: 0.5rem 0.75rem;
        border: 2px solid var(--gray-200);
        border-radius: var(--radius);
        color: var(--gray-700);
        text-decoration: none;
        transition: all 0.2s ease;
        font-weight: 500;
        background: var(--white);
        font-size: 0.8rem;
    }

    .page-link:hover {
        border-color: var(--primary);
        color: var(--primary);
        transform: translateY(-1px);
        box-shadow: var(--shadow-sm);
    }

    .page-item.active .page-link {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        border-color: var(--primary);
        color: var(--white);
        box-shadow: var(--shadow-sm);
    }

    /* État vide optimisé */
    .empty-state {
        text-align: center;
        padding: 3rem 2rem;
        color: var(--gray-500);
    }

    .empty-state-icon {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        opacity: 0.6;
        color: var(--gray-400);
    }

    .empty-state h6 {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--gray-700);
        margin-bottom: 0.5rem;
    }

    .empty-state p {
        color: var(--gray-500);
        font-size: 0.85rem;
    }

    /* Modal Orders popup modernisé - UN SEUL BOUTON DE FERMETURE */
    .orders-popup {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.6);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        backdrop-filter: blur(4px);
    }

    .orders-popup.show {
        display: flex;
    }

    .orders-popup-content {
        background: var(--white);
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-xl);
        width: 90%;
        max-width: 800px;
        max-height: 80vh;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .orders-popup-header {
        background: linear-gradient(135deg, #f8fafc 0%, var(--gray-100) 100%);
        padding: 1.5rem 2rem;
        border-bottom: 1px solid var(--gray-200);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .orders-popup-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--gray-900);
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .orders-popup-actions {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .orders-popup-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        color: var(--gray-500);
        cursor: pointer;
        padding: 0.5rem;
        border-radius: var(--radius);
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
    }

    .orders-popup-close:hover {
        background: var(--gray-100);
        color: var(--gray-700);
    }

    .orders-popup-body {
        padding: 1.5rem 2rem;
        overflow-y: auto;
        flex: 1;
    }

    .orders-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .order-item {
        background: var(--gray-50);
        border: 1px solid var(--gray-200);
        border-radius: var(--radius);
        padding: 1rem;
        transition: all 0.2s ease;
    }

    .order-item:hover {
        background: var(--white);
        box-shadow: var(--shadow-sm);
    }

    .order-item-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }

    .order-item-title {
        font-weight: 700;
        color: var(--gray-900);
    }

    .order-item-amount {
        font-weight: 700;
        color: var(--success);
    }

    .order-item-details {
        font-size: 0.8rem;
        color: var(--gray-600);
        line-height: 1.4;
    }

    /* Loading overlay modernisé */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.6);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        backdrop-filter: blur(4px);
    }

    .loading-spinner {
        background: var(--white);
        padding: 2rem;
        border-radius: var(--radius-xl);
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1rem;
        box-shadow: var(--shadow-xl);
    }

    .spinner {
        width: 40px;
        height: 40px;
        border: 3px solid var(--gray-200);
        border-top: 3px solid var(--primary);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Responsive optimisations */
    @media (max-width: 1400px) {
        .container-fluid {
            padding: 1rem;
        }
        
        .stats-container {
            flex-wrap: wrap;
        }
        
        .stats-card {
            min-width: 180px;
        }
    }

    @media (max-width: 768px) {
        .header-content {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }

        .toolbar-main,
        .filters-toolbar {
            flex-direction: column;
            align-items: stretch;
            gap: 1rem;
        }

        .search-section {
            min-width: auto;
        }

        .filters-left,
        .filters-right {
            justify-content: center;
            flex-wrap: wrap;
        }

        .main-actions {
            justify-content: center;
        }

        .stats-container {
            flex-direction: column;
        }

        .stats-card {
            min-width: auto;
        }

        .main-table {
            height: calc(100vh - 450px);
        }

        .table-responsive {
            font-size: 0.8rem;
        }

        .action-group {
            flex-direction: column;
            gap: 0.25rem;
        }

        .orders-popup-content {
            width: 95%;
            max-height: 90vh;
        }

        .orders-popup-header {
            padding: 1rem 1.5rem;
        }

        .orders-popup-body {
            padding: 1rem 1.5rem;
        }
    }

    /* Améliorations UX */
    .highlight-new {
        animation: highlightPulse 2s ease-in-out;
    }

    @keyframes highlightPulse {
        0% { background-color: rgba(16, 185, 129, 0.1); }
        50% { background-color: rgba(16, 185, 129, 0.2); }
        100% { background-color: transparent; }
    }

    /* Checkbox pour sélection */
    .cursor-pointer {
        cursor: pointer;
    }

    .form-check-input {
        width: 18px;
        height: 18px;
        accent-color: var(--primary);
        cursor: pointer;
    }

    .text-sm {
        font-size: 0.8rem;
        font-weight: 500;
        color: var(--gray-700);
    }

    /* Scrollbar pour la table principale uniquement */
    .table-responsive::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    .table-responsive::-webkit-scrollbar-track {
        background: var(--gray-100);
        border-radius: 3px;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background: var(--gray-400);
        border-radius: 3px;
    }

    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: var(--gray-500);
    }

    /* Scrollbar pour le popup des commandes */
    .orders-popup-body::-webkit-scrollbar {
        width: 6px;
    }

    .orders-popup-body::-webkit-scrollbar-track {
        background: var(--gray-100);
        border-radius: 3px;
    }

    .orders-popup-body::-webkit-scrollbar-thumb {
        background: var(--gray-400);
        border-radius: 3px;
    }

    .orders-popup-body::-webkit-scrollbar-thumb:hover {
        background: var(--gray-500);
    }

    /* Animation de fermeture du popup */
    .orders-popup.hiding {
        animation: fadeOut 0.3s ease-out forwards;
    }

    @keyframes fadeOut {
        from {
            opacity: 1;
            backdrop-filter: blur(4px);
        }
        to {
            opacity: 0;
            backdrop-filter: blur(0px);
        }
    }

    /* Amélioration de l'accessibilité */
    .btn-action:focus,
    .btn-toolbar:focus,
    .orders-popup-close:focus {
        outline: 2px solid var(--primary);
        outline-offset: 2px;
    }

    /* État de chargement pour les boutons */
    .btn-loading {
        pointer-events: none;
        opacity: 0.7;
    }

    .btn-loading::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 16px;
        height: 16px;
        border: 2px solid transparent;
        border-top: 2px solid currentColor;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header optimisé -->
    <div class="page-header">
        <div class="header-content">
            <div class="header-left">
                <div class="header-icon">
                    <i class="fas fa-copy"></i>
                </div>
                <div class="header-text">
                    <h1>Gestion des Commandes Doubles</h1>
                    <p>Interface de détection, analyse et fusion des doublons</p>
                </div>
            </div>
            <div class="header-actions">
                <button class="btn-toolbar btn-warning-action" id="btnCheckDuplicates">
                    <i class="fas fa-search"></i>
                    Vérifier
                </button>
                <button class="btn-toolbar btn-success-action" id="btnAutoMerge">
                    <i class="fas fa-magic"></i>
                    Fusion Auto
                </button>
            </div>
        </div>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert">&times;</button>
        </div>
    @endif

    <!-- Dashboard Statistics en une ligne -->
    <div class="stats-container">
        <div class="stats-card primary">
            <div class="stats-content">
                <div class="stats-icon primary">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stats-text">
                    <div class="stats-number" id="totalDuplicates">{{ $stats['total_duplicates'] }}</div>
                    <div class="stats-label">Non Examinées</div>
                </div>
            </div>
        </div>
        
        <div class="stats-card success">
            <div class="stats-content">
                <div class="stats-icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stats-text">
                    <div class="stats-number" id="mergedToday">{{ $stats['merged_today'] }}</div>
                    <div class="stats-label">Fusionnées Aujourd'hui</div>
                </div>
            </div>
        </div>
        
        <div class="stats-card warning">
            <div class="stats-content">
                <div class="stats-icon warning">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stats-text">
                    <div class="stats-number" id="pendingReview">{{ $stats['pending_review'] }}</div>
                    <div class="stats-label">Clients en Attente</div>
                </div>
            </div>
        </div>
        
        <div class="stats-card info">
            <div class="stats-content">
                <div class="stats-icon info">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stats-text">
                    <div class="stats-number" id="autoMergeDelay">{{ $stats['auto_merge_delay'] }}h</div>
                    <div class="stats-label">Délai Auto-Fusion</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toolbar simplifié -->
    <div class="unified-toolbar">
        <div class="toolbar-main">
            <div class="search-section">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="search-input" id="searchInput" 
                       placeholder="Rechercher par téléphone ou nom du client...">
                <div class="search-loading" id="searchLoading">
                    <div class="spinner" style="width: 20px; height: 20px;"></div>
                </div>
            </div>
            
            <div class="main-actions">
                <button class="btn-toolbar btn-success-action" id="btnBulkMerge" disabled>
                    <i class="fas fa-compress-arrows-alt"></i>Fusionner
                </button>
                <button class="btn-toolbar btn-primary-action" id="btnBulkReview" disabled>
                    <i class="fas fa-check"></i>Examiner
                </button>
            </div>
        </div>
        
        <div class="filters-toolbar">
            <div class="filters-left">
                <div class="filter-item">
                    <span class="filter-label">Min commandes:</span>
                    <select class="filter-select" id="minOrders">
                        <option value="">Toutes</option>
                        <option value="2">2+</option>
                        <option value="3">3+</option>
                        <option value="4">4+</option>
                        <option value="5">5+</option>
                    </select>
                </div>
                <div class="filter-item">
                    <span class="filter-label">Montant min:</span>
                    <input type="number" class="filter-input" id="minAmount" placeholder="TND" style="min-width: 80px;">
                </div>
                <div class="filter-item">
                    <span class="filter-label">Trier par:</span>
                    <select class="filter-select" id="sortField">
                        <option value="latest_order_date">Date récente</option>
                        <option value="total_orders">Nb commandes</option>
                        <option value="total_amount">Montant</option>
                        <option value="customer_phone">Téléphone</option>
                    </select>
                </div>
            </div>
            
            <div class="filters-right">
                <span class="results-count" id="resultsCount"></span>
                <select class="filter-select" id="perPage">
                    <option value="15">15/page</option>
                    <option value="25">25/page</option>
                    <option value="50">50/page</option>
                </select>
                <button class="btn-toolbar btn-outline" id="btnRefresh">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Table principale optimisée -->
    <div class="main-table">
        <div class="table-header">
            <div class="table-title">
                <h6>
                    <i class="fas fa-users"></i>Clients avec Commandes Doubles
                </h6>
                <span class="badge" id="totalResultsBadge">0</span>
            </div>
            <div class="table-controls">
                <label class="d-flex align-items-center gap-2 cursor-pointer">
                    <input type="checkbox" id="selectAllCheckbox" class="form-check-input">
                    <span class="text-sm">Tout sélectionner</span>
                </label>
                <span class="results-count" id="selectedInfo" style="display: none;">
                    <i class="fas fa-check-square"></i>
                    <span id="selectedCount">0</span> sélectionné(s)
                </span>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th width="40">
                            <i class="fas fa-check"></i>
                        </th>
                        <th>Client</th>
                        <th>Commandes</th>
                        <th>Montant</th>
                        <th>Dernière</th>
                        <th>Statut</th>
                        <th width="140">Actions</th>
                    </tr>
                </thead>
                <tbody id="duplicatesTableBody">
                    <!-- Contenu chargé via AJAX -->
                </tbody>
            </table>
        </div>
        
        <div class="pagination-wrapper">
            <nav id="paginationNav">
                <!-- Pagination chargée via AJAX -->
            </nav>
        </div>
    </div>
</div>

<!-- Popup des commandes optimisé avec UN SEUL BOUTON DE FERMETURE -->
<div class="orders-popup" id="ordersPopup">
    <div class="orders-popup-content">
        <div class="orders-popup-header">
            <h5 class="orders-popup-title">
                <i class="fas fa-list-alt"></i>Liste des Commandes
            </h5>
            <button type="button" class="orders-popup-close" onclick="closeOrdersPopup()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="orders-popup-body" id="ordersPopupBody">
            <!-- Contenu chargé via AJAX -->
        </div>
    </div>
</div>

<!-- Modal Fusion -->
<div class="modal fade" id="mergeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-compress-arrows-alt"></i>Fusionner les Commandes
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Attention:</strong> Cette action va fusionner toutes les commandes éligibles de ce client.
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-semibold">Note de fusion:</label>
                    <textarea class="form-control" id="mergeNote" rows="3" 
                              placeholder="Indiquez la raison de cette fusion..."></textarea>
                </div>
                
                <div id="mergePreview">
                    <!-- Aperçu des commandes à fusionner -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-success" id="btnConfirmMerge">
                    <i class="fas fa-check me-2"></i>Confirmer
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner">
        <div class="spinner"></div>
        <p class="mb-0 fw-semibold">Traitement en cours...</p>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let currentPage = 1;
    let currentPhone = null;
    let searchTimeout = null;
    let selectedItems = new Set();
    
    // Charger les données initiales
    loadDuplicates();
    
    // Recherche en temps réel
    $('#searchInput').on('input', function() {
        clearTimeout(searchTimeout);
        const query = $(this).val();
        
        $('#searchLoading').show();
        
        searchTimeout = setTimeout(() => {
            loadDuplicates(1);
            $('#searchLoading').hide();
        }, 300);
    });
    
    // Événements de filtres
    $('#minOrders, #minAmount, #sortField').change(function() {
        loadDuplicates(1);
    });
    
    // Fonction pour charger les doublons
    function loadDuplicates(page = 1) {
        const filters = {
            page: page,
            per_page: $('#perPage').val(),
            search: $('#searchInput').val(),
            min_orders: $('#minOrders').val(),
            min_amount: $('#minAmount').val(),
            sort: $('#sortField').val(),
            direction: 'desc' // Toujours trier par ordre décroissant
        };
        
        $.get('/admin/duplicates/get', filters)
            .done(function(response) {
                renderTable(response);
                renderPagination(response);
                updateResultsCount(response);
                currentPage = page;
            })
            .fail(function() {
                showError('Erreur lors du chargement des données');
            });
    }
    
    // Rendu de table
    function renderTable(data) {
        const tbody = $('#duplicatesTableBody');
        tbody.empty();
        selectedItems.clear();
        updateBulkActions();
        
        if (data.data.length === 0) {
            tbody.append(`
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="fas fa-search"></i>
                            </div>
                            <h6>Aucune commande double trouvée</h6>
                            <p>Modifiez vos critères de recherche ou vérifiez les nouveaux doublons.</p>
                        </div>
                    </td>
                </tr>
            `);
            return;
        }
        
        data.data.forEach(function(duplicate) {
            const canAutoMerge = duplicate.can_auto_merge;
            const autoMergeIndicator = canAutoMerge 
                ? '<div class="auto-merge-indicator"><i class="fas fa-clock"></i>Éligible fusion auto</div>'
                : '';
            
            const row = `
                <tr data-phone="${duplicate.customer_phone}">
                    <td>
                        <input type="checkbox" class="item-checkbox" 
                               value="${duplicate.customer_phone}">
                    </td>
                    <td>
                        <div class="client-info">
                            <div class="client-phone">
                                <i class="fas fa-phone me-1"></i>${duplicate.customer_phone}
                            </div>
                            <div class="client-name">${duplicate.latest_order.customer_name || 'Nom non spécifié'}</div>
                        </div>
                    </td>
                    <td>
                        <span class="badge-count">${duplicate.total_orders}</span>
                    </td>
                    <td>
                        <div class="amount-display">${parseFloat(duplicate.total_amount).toFixed(3)} TND</div>
                    </td>
                    <td>
                        <div class="date-display">${formatDate(duplicate.latest_order_date)}</div>
                        ${autoMergeIndicator}
                    </td>
                    <td>
                        <span class="priority-badge priority-doublé">
                            <i class="fas fa-copy"></i>Doublon
                        </span>
                    </td>
                    <td>
                        <div class="action-group">
                            <button class="btn-action btn-merge" 
                                    onclick="openMergeModal('${duplicate.customer_phone}')"
                                    title="Fusionner les commandes">
                                <i class="fas fa-compress-arrows-alt"></i>
                            </button>
                            <button class="btn-action btn-review" 
                                    onclick="markAsReviewed('${duplicate.customer_phone}')"
                                    title="Marquer comme examiné">
                                <i class="fas fa-check"></i>
                            </button>
                            <button class="btn-action btn-orders" 
                                    onclick="showOrdersPopup('${duplicate.customer_phone}')"
                                    title="Liste des commandes">
                                <i class="fas fa-list-alt"></i>
                            </button>
                            <button class="btn-action btn-detail" 
                                    onclick="viewDetail('${duplicate.customer_phone}')"
                                    title="Voir détails complets">
                                <i class="fas fa-external-link-alt"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
        
        // Événements checkboxes
        $('.item-checkbox').change(function() {
            const phone = $(this).val();
            const row = $(this).closest('tr');
            
            if ($(this).is(':checked')) {
                selectedItems.add(phone);
                row.addClass('table-active');
            } else {
                selectedItems.delete(phone);
                row.removeClass('table-active');
            }
            updateBulkActions();
        });
    }
    
    // Mise à jour du compteur
    function updateResultsCount(data) {
        const count = data.total || 0;
        const text = count === 0 ? 'Aucun résultat' : 
                     count === 1 ? '1 résultat' : `${count} résultats`;
        $('#resultsCount').text(text);
        $('#totalResultsBadge').text(count);
    }
    
    // Rendu de pagination
    function renderPagination(data) {
        const nav = $('#paginationNav');
        nav.empty();
        
        if (data.last_page <= 1) return;
        
        let pagination = '<ul class="pagination">';
        
        if (data.current_page > 1) {
            pagination += `<li class="page-item">
                <a class="page-link" href="#" onclick="loadDuplicates(${data.current_page - 1})">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>`;
        }
        
        for (let i = Math.max(1, data.current_page - 2); i <= Math.min(data.last_page, data.current_page + 2); i++) {
            const active = i === data.current_page ? 'active' : '';
            pagination += `<li class="page-item ${active}">
                <a class="page-link" href="#" onclick="loadDuplicates(${i})">${i}</a>
            </li>`;
        }
        
        if (data.current_page < data.last_page) {
            pagination += `<li class="page-item">
                <a class="page-link" href="#" onclick="loadDuplicates(${data.current_page + 1})">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>`;
        }
        
        pagination += '</ul>';
        nav.html(pagination);
    }
    
    // Sélectionner tout amélioré
    $('#selectAllCheckbox').change(function() {
        const isChecked = $(this).is(':checked');
        $('.item-checkbox').prop('checked', isChecked);
        
        selectedItems.clear();
        if (isChecked) {
            $('.item-checkbox').each(function() {
                selectedItems.add($(this).val());
                $(this).closest('tr').addClass('table-active');
            });
        } else {
            $('.table tbody tr').removeClass('table-active');
        }
        updateBulkActions();
    });
    
    // Mise à jour des actions groupées
    function updateBulkActions() {
        const hasSelection = selectedItems.size > 0;
        $('#btnBulkMerge, #btnBulkReview').prop('disabled', !hasSelection);
        
        if (hasSelection) {
            $('#selectedInfo').show();
            $('#selectedCount').text(selectedItems.size);
            $('#btnBulkMerge').html(`<i class="fas fa-compress-arrows-alt"></i>Fusionner (${selectedItems.size})`);
            $('#btnBulkReview').html(`<i class="fas fa-check"></i>Examiner (${selectedItems.size})`);
        } else {
            $('#selectedInfo').hide();
            $('#btnBulkMerge').html('<i class="fas fa-compress-arrows-alt"></i>Fusionner');
            $('#btnBulkReview').html('<i class="fas fa-check"></i>Examiner');
        }
        
        // Mettre à jour le checkbox principal
        $('#selectAllCheckbox').prop('checked', selectedItems.size > 0 && 
            selectedItems.size === $('.item-checkbox').length);
    }
    
    // Actions groupées
    $('#btnBulkMerge').click(function() {
        if (selectedItems.size === 0) return;
        
        if (!confirm(`Fusionner ${selectedItems.size} groupe(s) de commandes ?`)) {
            return;
        }
        
        showLoading();
        
        const phones = Array.from(selectedItems);
        let completed = 0;
        
        phones.forEach(phone => {
            $.post('/admin/duplicates/merge', {
                customer_phone: phone,
                note: 'Fusion groupée',
                _token: '{{ csrf_token() }}'
            })
            .always(() => {
                completed++;
                if (completed === phones.length) {
                    hideLoading();
                    showSuccess(`${phones.length} groupe(s) fusionné(s)`);
                    loadDuplicates(currentPage);
                    refreshStats();
                }
            });
        });
    });
    
    $('#btnBulkReview').click(function() {
        if (selectedItems.size === 0) return;
        
        showLoading();
        
        const phones = Array.from(selectedItems);
        let completed = 0;
        
        phones.forEach(phone => {
            $.post('/admin/duplicates/mark-reviewed', {
                customer_phone: phone,
                _token: '{{ csrf_token() }}'
            })
            .always(() => {
                completed++;
                if (completed === phones.length) {
                    hideLoading();
                    showSuccess(`${phones.length} groupe(s) examiné(s)`);
                    loadDuplicates(currentPage); // REFRESH automatique après marquer comme examiné
                    refreshStats();
                }
            });
        });
    });
    
    // Actions principales
    $('#btnCheckDuplicates').click(function() {
        showLoading();
        
        $.post('/admin/duplicates/check', {
            _token: '{{ csrf_token() }}'
        })
        .done(function(response) {
            hideLoading();
            if (response.success) {
                showSuccess(response.message);
                loadDuplicates(currentPage);
                refreshStats();
            } else {
                showError(response.message);
            }
        })
        .fail(function() {
            hideLoading();
            showError('Erreur lors de la vérification');
        });
    });
    
    $('#btnAutoMerge').click(function() {
        showLoading();
        
        $.post('/admin/duplicates/auto-merge', {
            _token: '{{ csrf_token() }}'
        })
        .done(function(response) {
            hideLoading();
            if (response.success) {
                showSuccess(response.message);
                loadDuplicates(currentPage);
                refreshStats();
            } else {
                showError(response.message);
            }
        })
        .fail(function() {
            hideLoading();
            showError('Erreur lors de la fusion automatique');
        });
    });
    
    // Actualiser
    $('#btnRefresh').click(function() {
        loadDuplicates(currentPage);
    });
    
    // Changement de pagination
    $('#perPage').change(function() {
        loadDuplicates(1);
    });
    
    // Confirmer fusion
    $('#btnConfirmMerge').click(function() {
        if (!currentPhone) return;
        
        const note = $('#mergeNote').val();
        
        showLoading();
        
        $.post('/admin/duplicates/merge', {
            customer_phone: currentPhone,
            note: note,
            _token: '{{ csrf_token() }}'
        })
        .done(function(response) {
            hideLoading();
            $('#mergeModal').modal('hide');
            
            if (response.success) {
                showSuccess(response.message);
                loadDuplicates(currentPage);
                refreshStats();
            } else {
                showError(response.message);
            }
        })
        .fail(function() {
            hideLoading();
            showError('Erreur lors de la fusion');
        });
    });
    
    // Fonctions utilitaires
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('fr-FR', {
            day: '2-digit',
            month: '2-digit', 
            year: 'numeric'
        });
    }
    
    function showLoading() {
        $('#loadingOverlay').show();
    }
    
    function hideLoading() {
        $('#loadingOverlay').hide();
    }
    
    function showSuccess(message) {
        showAlert('success', 'fas fa-check-circle', message);
    }
    
    function showError(message) {
        showAlert('danger', 'fas fa-exclamation-circle', message);
    }
    
    function showAlert(type, icon, message) {
        const alertId = 'alert-' + Date.now();
        const alert = $(`
            <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                <i class="${icon} me-2"></i>${message}
                <button type="button" class="btn-close" onclick="$('#${alertId}').fadeOut()"></button>
            </div>
        `);
        
        $('body').append(alert);
        
        setTimeout(() => {
            alert.fadeOut(() => alert.remove());
        }, 5000);
    }
    
    function refreshStats() {
        $.get('/admin/duplicates/stats')
            .done(function(stats) {
                $('#totalDuplicates').text(stats.total_duplicates);
                $('#mergedToday').text(stats.merged_today);
                $('#pendingReview').text(stats.pending_review);
                $('#autoMergeDelay').text(stats.auto_merge_delay + 'h');
            });
    }
    
    // Auto-refresh
    setInterval(function() {
        refreshStats();
        if (selectedItems.size === 0) {
            loadDuplicates(currentPage);
        }
    }, 120000);
});

// Fonctions globales
function openMergeModal(phone) {
    currentPhone = phone;
    $('#mergeNote').val('');
    
    $.get('/admin/duplicates/history', { customer_phone: phone })
        .done(function(response) {
            let preview = `<h6>Commandes à fusionner:</h6><ul class="list-group">`;
            response.orders.forEach(function(order) {
                if (order.status === 'nouvelle' && order.is_duplicate && !order.reviewed_for_duplicates) {
                    preview += `<li class="list-group-item d-flex justify-content-between">
                        <span>Commande #${order.id}</span>
                        <strong>${parseFloat(order.total_price).toFixed(3)} TND</strong>
                    </li>`;
                }
            });
            preview += '</ul>';
            
            $('#mergePreview').html(preview);
            $('#mergeModal').modal('show');
        });
}

function markAsReviewed(phone) {
    if (!confirm('Marquer comme examiné ?')) return;
    
    // Ajouter classe de chargement au bouton
    const button = event.target.closest('.btn-review');
    button.classList.add('btn-loading');
    
    $.post('/admin/duplicates/mark-reviewed', {
        customer_phone: phone,
        _token: '{{ csrf_token() }}'
    })
    .done(function(response) {
        if (response.success) {
            showSuccess(response.message);
            // REFRESH automatique après marquer comme examiné
            loadDuplicates(currentPage);
            refreshStats();
        }
    })
    .fail(function() {
        showError('Erreur lors de la mise à jour');
    })
    .always(function() {
        button.classList.remove('btn-loading');
    });
}

function showOrdersPopup(phone) {
    // Stocker le téléphone pour le bouton détails complets
    window.currentPopupPhone = phone;
    
    $.get('/admin/duplicates/history', { customer_phone: phone })
        .done(function(response) {
            let content = `
                <div class="orders-list">
            `;
            
            response.orders.forEach(function(order) {
                const statusBadge = getStatusBadge(order.status);
                content += `
                    <div class="order-item">
                        <div class="order-item-header">
                            <div class="order-item-title">Commande #${order.id}</div>
                            <div class="order-item-amount">${parseFloat(order.total_price).toFixed(3)} TND</div>
                        </div>
                        <div class="order-item-details">
                            <strong>Date:</strong> ${formatDateSimple(order.created_at)} | 
                            <strong>Statut:</strong> ${statusBadge} | 
                            <strong>Produits:</strong> ${order.items ? order.items.length : 0}
                        </div>
                    </div>
                `;
            });
            
            content += '</div>';
            
            $('#ordersPopupBody').html(content);
            $('#ordersPopup').addClass('show');
        });
}

function viewDetailFromPopup() {
    if (window.currentPopupPhone) {
        window.open(`/admin/duplicates/detail/${encodeURIComponent(window.currentPopupPhone)}`, '_blank');
    }
}

function closeOrdersPopup() {
    const popup = $('#ordersPopup');
    popup.addClass('hiding');
    
    setTimeout(() => {
        popup.removeClass('show hiding');
    }, 300);
}

function viewDetail(phone) {
    window.open(`/admin/duplicates/detail/${encodeURIComponent(phone)}`, '_blank');
}

function getStatusBadge(status) {
    const badges = {
        'nouvelle': '<span class="badge bg-info">Nouvelle</span>',
        'confirmée': '<span class="badge bg-success">Confirmée</span>',
        'annulée': '<span class="badge bg-danger">Annulée</span>',
        'datée': '<span class="badge bg-warning">Datée</span>',
        'en_route': '<span class="badge bg-primary">En route</span>',
        'livrée': '<span class="badge bg-success">Livrée</span>'
    };
    
    return badges[status] || `<span class="badge bg-secondary">${status}</span>`;
}

function formatDateSimple(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR');
}

// Variables globales
let currentPhone = null;

// Fermer popup en cliquant à l'extérieur
$(document).click(function(e) {
    if ($(e.target).is('#ordersPopup')) {
        closeOrdersPopup();
    }
});

// Raccourcis clavier
$(document).keydown(function(e) {
    // Escape : Fermer popup ou effacer sélection
    if (e.key === 'Escape') {
        if ($('#ordersPopup').hasClass('show')) {
            closeOrdersPopup();
        } else {
            selectedItems.clear();
            $('.table tbody tr').removeClass('table-active');
            $('.item-checkbox').prop('checked', false);
            $('#selectAllCheckbox').prop('checked', false);
            updateBulkActions();
        }
    }
    
    // F5 : Actualiser
    if (e.key === 'F5') {
        e.preventDefault();
        loadDuplicates(currentPage);
    }
});

// Fonctions utilitaires globales
function showLoading() {
    $('#loadingOverlay').show();
}

function hideLoading() {
    $('#loadingOverlay').hide();
}

function showSuccess(message) {
    showAlert('success', 'fas fa-check-circle', message);
}

function showError(message) {
    showAlert('danger', 'fas fa-exclamation-circle', message);
}

function showAlert(type, icon, message) {
    const alertId = 'alert-' + Date.now();
    const alert = $(`
        <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <i class="${icon} me-2"></i>${message}
            <button type="button" class="btn-close" onclick="$('#${alertId}').fadeOut()"></button>
        </div>
    `);
    
    $('body').append(alert);
    
    setTimeout(() => {
        alert.fadeOut(() => alert.remove());
    }, 4000);
}

function refreshStats() {
    $.get('/admin/duplicates/stats')
        .done(function(stats) {
            $('#totalDuplicates').text(stats.total_duplicates);
            $('#mergedToday').text(stats.merged_today);
            $('#pendingReview').text(stats.pending_review);
            $('#autoMergeDelay').text(stats.auto_merge_delay + 'h');
        });
}

function loadDuplicates(page = 1) {
    const filters = {
        page: page,
        per_page: $('#perPage').val(),
        search: $('#searchInput').val(),
        min_orders: $('#minOrders').val(),
        min_amount: $('#minAmount').val(),
        sort: $('#sortField').val(),
        direction: 'desc' // Toujours trier par ordre décroissant
    };
    
    $.get('/admin/duplicates/get', filters)
        .done(function(response) {
            renderTable(response);
            renderPagination(response);
            updateResultsCount(response);
            currentPage = page;
        })
        .fail(function() {
            showError('Erreur lors du chargement des données');
        });
}
</script>
@endsection